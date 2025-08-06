<?php
// app/Console/Commands/ListenEodhdCrypto.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use React\EventLoop\Loop;
use Ratchet\Client\Connector;

class ListenEodhdCrypto extends Command
{
    protected $signature   = 'eodhd:listen-crypto';
    protected $description = 'Stream crypto ticks from EODHD and persist last & open prices';

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
            if ($cfg['type'] === $type) {
                $symbols[] = $cfg['data_symbol'];
            }
        }

        if (empty($symbols)) {
            $this->error("No assets configured for type: {$type}");
            return;
        }

        $list = implode(',', $symbols);
        $url  = "wss://ws.eodhistoricaldata.com/ws/{$endpoint}?api_token={$token}";

        $this->info("Connecting to {$url}…");
        $loop      = Loop::get();
        $connector = new Connector($loop);

        $connector($url)
            ->then(function(\Ratchet\Client\WebSocket $conn) use ($list) {
                $this->info("✅ Connected! Subscribing to: {$list}");
                $conn->send(json_encode([
                    'action'  => 'subscribe',
                    'symbols' => $list,
                ]));

                $conn->on('message', function($msg) {
                    $this->handleTick($msg);
                });

                $conn->on('close', function($code = null, $reason = null) {
                    $this->warn("Connection closed ({$code}): {$reason}");
                });
            }, function(\Exception $e) {
                $this->error('WebSocket error: ' . $e->getMessage());
            });

        $loop->run();
    }

    protected function handleTick(string $msg)
    {
        $data = json_decode($msg, true);

        // 1) ignore non‐tick payloads
        if (! isset($data['t'])) {
            Log::debug('EODHD control message:', $data);
            return;
        }

        // 2) broadcast raw‐tick for downstream
        Redis::publish('raw-ticks', $msg);

        // 3) parse
        $symbol  = $data['s'] ?? 'UNKNOWN';          // e.g. "BTC-USD"
        $price   = $data['a'] ?? $data['p'] ?? 0;    // ask or last
        $fileKey = str_replace('-', '_', $symbol);

        // A) persist last price
        Storage::disk('local')->put("private/last_prices/{$fileKey}.json", json_encode([
            'price'     => round($price, config("assets.{$this->lookupDataKey($symbol)}.decimals", 4)),
            'timestamp' => Carbon::now()->toIso8601String(),
        ]));

        // B) capture open‐of‐day
        $this->captureOpen($symbol, $price);

        // C) simple console info
        $ts      = intdiv($data['t'], 1000);
        $timeStr = date('H:i:s', $ts);
        $this->info("Tick @ {$timeStr}: {$symbol} = {$price}");
    }

    protected function captureOpen(string $symbol, $price)
    {
        $assets = config('assets');

        // find config entry by data_symbol
        $entry = collect($assets)->first(function($cfg, $key) use ($symbol) {
            return $cfg['data_symbol'] === $symbol;
        });

        if (! $entry) {
            return;
        }

        $tz       = $entry['timezone'];
        $openTime = $entry['open_time'];         // e.g. "00:00:00"
        $now      = Carbon::now($tz);
        $today    = $now->toDateString();        // "YYYY-MM-DD"
        $fileKey  = str_replace('-', '_', $symbol);
        $openFile = "private/open_prices/{$fileKey}_{$today}.json";

        if (
            ! Storage::disk('local')->exists($openFile) &&
            $now->format('H:i:s') >= $openTime
        ) {
            Storage::disk('local')->put($openFile, json_encode([
                'open' => round($price, $entry['decimals']),
            ]));
        }
    }

    protected function lookupDataKey(string $dataSymbol): string
    {
        // reverse‐lookup to find the asset key in config/assets.php
        $assets = config('assets');
        foreach ($assets as $key => $cfg) {
            if ($cfg['data_symbol'] === $dataSymbol) {
                return $key;
            }
        }
        return ''; // fallback
    }
}