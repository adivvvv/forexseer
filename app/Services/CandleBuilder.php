<?php
// app/Services/CandleBuilder.php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CandleBuilder
{
    /**
     * Intervals we build, with their length in seconds.
     */
    private array $intervals = [
        '15s' => 15,
        '1m'  => 60,
        '5m'  => 300,
        '15m' => 900,
        '30m' => 1800,
        '1h'  => 3600,
    ];

    /** In-RAM working state: $state[asset][interval] = ['start'=>int epoch, 'open','high','low','close','vol'] */
    private array $state = [];

    /** Throttle current-candle JSON writes: $lastJsonAt[asset][interval] = float microtime */
    private array $lastJsonAt = [];

    /** Batch of finalized rows to upsert into DB every second */
    private array $batch = [];

    /** Cache whether candles.session_date exists */
    private ?bool $hasSessionDate = null;

    /** Throttling knobs */
    private int $jsonThrottleMs = 1000;     // write current JSON at most once per second
    private int $maxBatchSize   = 500;      // upsert chunk size

    public function ingestTick(string $dataSymbol, $price, int $timestampMs): void
    {
        $assetKey = $this->lookupAssetKey($dataSymbol);
        if ($assetKey === null) {
            return;
        }

        $p = (float) $price;
        $tsSec = intdiv($timestampMs, 1000);

        foreach ($this->intervals as $interval => $sec) {
            $bucketStart = intdiv($tsSec, $sec) * $sec;
            $this->accumulate($assetKey, $interval, $sec, $bucketStart, $p);
        }
    }

    /**
     * Accumulate a tick into the in-RAM candle; finalize previous bucket if needed.
     */
    private function accumulate(string $asset, string $interval, int $sec, int $bucketStart, float $price): void
    {
        $cur = $this->state[$asset][$interval] ?? null;

        if (!$cur) {
            // first tick in this bucket
            $this->state[$asset][$interval] = [
                'start' => $bucketStart,
                'open'  => $price,
                'high'  => $price,
                'low'   => $price,
                'close' => $price,
                'vol'   => 1,
            ];
            return;
        }

        if ($cur['start'] === $bucketStart) {
            // same bucket → mutate
            if ($price > $cur['high']) $cur['high'] = $price;
            if ($price < $cur['low'])  $cur['low']  = $price;
            $cur['close'] = $price;
            $cur['vol']   = (int)$cur['vol'] + 1;
            $this->state[$asset][$interval] = $cur;
        } else {
            // bucket advanced → finalize previous and start new
            $this->queueFinalize($asset, $interval, $cur);
            $this->state[$asset][$interval] = [
                'start' => $bucketStart,
                'open'  => $price,
                'high'  => $price,
                'low'   => $price,
                'close' => $price,
                'vol'   => 1,
            ];
        }
    }

    /**
     * Called by the listener once per second.
     * - Writes throttled “current candle” JSONs
     * - Flushes any batched DB rows
     * - Optionally closes stale candles that crossed a boundary without new ticks
     */
    public function tickSecond(): void
    {
        $nowMs = (int) \floor(\microtime(true) * 1000);

        // 1) Throttled current-candle JSON (for realtime UI)
        foreach ($this->state as $asset => $byInterval) {
            foreach ($byInterval as $interval => $c) {
                $last = $this->lastJsonAt[$asset][$interval] ?? 0.0;
                if (($nowMs - (int)($last * 1000)) >= $this->jsonThrottleMs) {
                    $this->writeCurrentJson($asset, $interval, $c);
                    $this->lastJsonAt[$asset][$interval] = \microtime(true);
                }
            }
        }

        // 2) Flush DB batch
        $this->flushBatch();

        // 3) Close stale buckets (grace ~2s beyond end)
        $this->flushStale(2);
    }

    /**
     * Push a closed candle into the DB batch.
     */
    private function queueFinalize(string $asset, string $interval, array $c): void
    {
        $startAtIso = Carbon::createFromTimestamp($c['start'], 'UTC')->toDateTimeString();
        $decimals   = (int) config("assets.{$asset}.decimals", 6);

        $row = [
            'asset_key'  => $asset,
            'interval'   => $interval,
            'start_at'   => $startAtIso,
            'open'       => round((float)$c['open'],  $decimals),
            'high'       => round((float)$c['high'],  $decimals),
            'low'        => round((float)$c['low'],   $decimals),
            'close'      => round((float)$c['close'], $decimals),
            'volume'     => (int)$c['vol'],
            'updated_at' => now('UTC')->toDateTimeString(),
            'created_at' => now('UTC')->toDateTimeString(),
        ];

        if ($this->hasSessionDate()) {
            $row['session_date'] = $this->computeSessionDate($asset, $startAtIso);
        }

        $this->batch[] = $row;

        // prevent unbounded growth in extreme cases
        if (count($this->batch) >= $this->maxBatchSize) {
            $this->flushBatch();
        }
    }

    /**
     * Upsert all queued rows in a single DB call (chunked).
     */
    private function flushBatch(): void
    {
        if (empty($this->batch)) return;

        try {
            $chunks = array_chunk($this->batch, $this->maxBatchSize);
            foreach ($chunks as $chunk) {
                DB::table('candles')->upsert(
                    $chunk,
                    ['asset_key', 'interval', 'start_at'],
                    // update columns (exclude PK + created_at)
                    ['open','high','low','close','volume','session_date','updated_at']
                );
            }
            if (config('app.debug')) {
                Log::debug('Candles upserted', ['rows' => count($this->batch)]);
            }
        } catch (\Throwable $e) {
            Log::error('Batch upsert failed: '.$e->getMessage());
        } finally {
            $this->batch = [];
        }
    }

    /**
     * Close buckets that definitely ended without more ticks.
     * Only checks the in-RAM state (no disk scanning).
     */
    public function flushStale(int $graceSeconds = 2): void
    {
        $now = \time();
        foreach ($this->state as $asset => $byInterval) {
            foreach ($byInterval as $interval => $c) {
                $seconds = $this->intervals[$interval] ?? null;
                if (!$seconds) continue;

                $end = $c['start'] + $seconds;
                if ($now > ($end + $graceSeconds)) {
                    // close and immediately re-open a placeholder starting at end;
                    // it will be overwritten on next real tick
                    $this->queueFinalize($asset, $interval, $c);
                    // keep the current state as "ended" so JSON still shows last candle progressing
                    // but do not roll to a new unknown price bucket
                }
            }
        }
    }

    /**
     * Write current candle JSON (throttled by caller).
     */
    private function writeCurrentJson(string $asset, string $interval, array $c): void
    {
        $path = "private/candles_current/{$asset}/{$interval}.json";
        $abs  = storage_path('app/'.$path);
        $dir  = \dirname($abs);
        File::ensureDirectoryExists($dir, 0755);

        $payload = [
            'asset_key' => $asset,
            'interval'  => $interval,
            'start_at'  => Carbon::createFromTimestamp($c['start'], 'UTC')->toIso8601String(),
            'open'      => $c['open'],
            'high'      => $c['high'],
            'low'       => $c['low'],
            'close'     => $c['close'],
            'volume'    => $c['vol'],
            'last_update' => now('UTC')->toIso8601String(),
        ];

        $tmp = $abs.'.tmp';
        \file_put_contents($tmp, json_encode($payload, JSON_UNESCAPED_SLASHES));
        @\rename($tmp, $abs);
    }

    private function computeSessionDate(string $assetKey, string $startAtIsoUtc): string
    {
        $tz       = (string) config("assets.{$assetKey}.timezone", 'UTC');
        $openTime = (string) config("assets.{$assetKey}.open_time", '00:00:00');

        $local = Carbon::parse($startAtIsoUtc, 'UTC')->setTimezone($tz);
        if ($local->format('H:i:s') < $openTime) {
            return $local->copy()->subDay()->toDateString();
        }
        return $local->toDateString();
    }

    private function lookupAssetKey(string $dataSymbol): ?string
    {
        $assets = config('assets');
        foreach ($assets as $key => $cfg) {
            if (($cfg['data_symbol'] ?? null) === $dataSymbol) {
                return $key;
            }
        }
        return null;
    }

    private function hasSessionDate(): bool
    {
        if ($this->hasSessionDate === null) {
            $this->hasSessionDate = Schema::hasColumn('candles', 'session_date');
        }
        return $this->hasSessionDate;
    }
}