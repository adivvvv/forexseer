<?php
// app/Console/Commands/ListenEodhdCrypto.php

namespace App\Console\Commands;

use App\Services\CandleBuilder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;

class ListenEodhdCrypto extends Command
{
    protected $signature   = 'eodhd:listen-crypto';
    protected $description = 'Stream crypto ticks from EODHD and persist last & open prices';

    private ?\Ratchet\Client\WebSocket $conn = null;
    private ?\React\EventLoop\LoopInterface $loop = null;
    private string $url = '';
    private string $subscribeList = '';
    private int $backoff = 1;           // seconds (exponential up to 30s)
    private float $lastTickAt = 0.0;    // monotonic seconds
    private bool $dirsEnsured = false;

    public function __construct(private readonly CandleBuilder $candles)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->streamByType('crypto', 'crypto');
    }

    protected function streamByType(string $type, string $endpoint)
    {
        $token   = config('services.eodhd.api_token');
        $assets  = config('assets');
        $symbols = [];

        foreach ($assets as $key => $cfg) {
            if (($cfg['type'] ?? null) === $type) {
                $symbols[] = $cfg['data_symbol'];
            }
        }

        if (empty($symbols)) {
            $this->error("No assets configured for type: {$type}");
            return;
        }

        $this->subscribeList = implode(',', $symbols);
        $this->url           = "wss://ws.eodhistoricaldata.com/ws/{$endpoint}?api_token={$token}";

        $this->loop = Loop::get();

        // watchdog: if no tick for 25s, reconnect
        $this->lastTickAt = \microtime(true);
        $this->loop->addPeriodicTimer(5, function () {
            if (\microtime(true) - $this->lastTickAt > 25) {
                $this->warn('Inactivity watchdog tripped (no ticks >25s). Reconnecting…');
                $this->safeClose();
                $this->scheduleReconnect(0);
            }
        });

        // CONNECT WebSocket
        $this->connect();

        // NEW: once/second, flush DB batch + write throttled current-candle JSONs + close stale buckets
        $this->loop->addPeriodicTimer(1, function () {
            $this->candles->tickSecond();
        });

        $this->loop->run();
    }

    private function connect(): void
    {
        $this->info("Connecting to {$this->url}…");
        $connector = new Connector($this->loop);

        $connector($this->url)->then(
            function (\Ratchet\Client\WebSocket $conn) {
                $this->conn = $conn;
                $this->backoff = 1; // reset backoff on successful connect

                $this->info("✅ Connected! Subscribing to: {$this->subscribeList}");
                $conn->send(json_encode([
                    'action'  => 'subscribe',
                    'symbols' => $this->subscribeList,
                ]));

                $conn->on('message', function ($msg) {
                    try {
                        $this->lastTickAt = \microtime(true);
                        $this->handleTick($msg);
                    } catch (\Throwable $e) {
                        Log::error('Tick handling failed: '.$e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                });

                $conn->on('close', function ($code = null, $reason = null) {
                    $this->warn("Connection closed ({$code}): {$reason}");
                    $this->conn = null;
                    $this->scheduleReconnect($this->backoff);
                    // exponential backoff up to 30s
                    $this->backoff = min($this->backoff * 2, 30);
                });
            },
            function (\Exception $e) {
                $this->error('WebSocket connect error: ' . $e->getMessage());
                $this->scheduleReconnect($this->backoff);
                $this->backoff = min($this->backoff * 2, 30);
            }
        );
    }

    private function scheduleReconnect(int $inSeconds): void
    {
        if (!$this->loop) {
            return;
        }
        $delay = max(0, $inSeconds);
        $this->loop->addTimer($delay, function () {
            $this->connect();
        });
    }

    private function safeClose(): void
    {
        try {
            if ($this->conn) {
                $this->conn->close();
            }
        } catch (\Throwable $e) {
            // ignore
        } finally {
            $this->conn = null;
        }
    }

    protected function handleTick(mixed $msg): void
    {
        $payload = $this->payloadToString($msg);

        // parse JSON (multiple frames from provider may arrive; handle line-delimited)
        foreach (preg_split('/\r?\n/', $payload, -1, PREG_SPLIT_NO_EMPTY) as $line) {
            $data = json_decode($line, true);

            // ignore non‐tick payloads (e.g., pings or control frames)
            if (!is_array($data) || !isset($data['t'])) {
                continue;
            }

            // broadcast raw tick for downstream consumers
            Redis::publish('raw-ticks', $line);

            $symbol   = $data['s'] ?? 'UNKNOWN';      // e.g., "BTC-USD"
            $price    = $data['a'] ?? $data['p'] ?? 0;
            $tsMillis = (int)($data['t']);

            $fileKey = str_replace('-', '_', $symbol);

            if (!$this->dirsEnsured) {
                $this->ensureLocalDir('private/last_prices');
                $this->ensureLocalDir('private/open_prices');
                $this->dirsEnsured = true;
            }

            // keep a simple "last price" JSON (for widgets)
            Storage::disk('local')->put("private/last_prices/{$fileKey}.json", json_encode([
                'price'     => round($price, (int)config("assets.{$this->lookupDataKey($symbol)}.decimals", 4)),
                'timestamp' => Carbon::now('UTC')->toIso8601String(),
            ]));

            // open-of-day capture
            $this->captureOpen($symbol, $price);

            // feed the candle engine (RAM current + DB batch on roll)
            $this->candles->ingestTick($symbol, (float)$price, $tsMillis);

            // console noise for debugging
            $ts      = intdiv($tsMillis, 1000);
            $timeStr = gmdate('H:i:s', $ts);
            $this->info("Tick @ {$timeStr}Z: {$symbol} = {$price}");
        }
    }

    protected function captureOpen(string $symbol, $price): void
    {
        $assets = config('assets');

        $entry = collect($assets)->first(function ($cfg, $key) use ($symbol) {
            return ($cfg['data_symbol'] ?? null) === $symbol;
        });

        if (!$entry) {
            return;
        }

        $tz       = $entry['timezone'];
        $openTime = $entry['open_time']; // "00:00:00"
        $now      = Carbon::now($z = $tz);
        $today    = $now->toDateString();
        $fileKey  = str_replace('-', '_', $symbol);

        $openFile = "private/open_prices/{$fileKey}_{$today}.json";

        if (
            !Storage::disk('local')->exists($openFile) &&
            $now->format('H:i:s') >= $openTime
        ) {
            Storage::disk('local')->put($openFile, json_encode([
                'open' => round($price, (int)$entry['decimals']),
            ]));
        }
    }

    protected function lookupDataKey(string $dataSymbol): string
    {
        $assets = config('assets');
        foreach ($assets as $key => $cfg) {
            if (($cfg['data_symbol'] ?? null) === $dataSymbol) {
                return $key;
            }
        }
        return '';
    }

    private function ensureLocalDir(string $relativeDir): void
    {
        $abs = storage_path('app/'.$relativeDir);
        File::ensureDirectoryExists($abs, 0755, true);
    }

    /**
     * Normalize Ratchet message to a string payload (handles objects/binary).
     */
    private function payloadToString(mixed $msg): string
    {
        if (is_string($msg)) {
            return $msg;
        }
        if (is_object($msg)) {
            if (method_exists($msg, 'getPayload')) {
                $p = $msg->getPayload();
                return is_string($p) ? $p : (string)$p;
            }
            if (method_exists($msg, '__toString')) {
                return (string)$msg;
            }
        }
        return is_scalar($msg) ? (string)$msg : json_encode($msg);
    }
}