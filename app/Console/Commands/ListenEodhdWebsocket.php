<?php
// app/Console/Commands/ListenEodhdWebsocket.php

// This class is disabled in favor of the more generic ListenEodhdCrypto command and ListenEodhdForex and ListenEodhdUs subclasses.

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use React\EventLoop\Loop;
use Ratchet\Client\Connector;

class ListenEodhdWebsocket extends Command
{
    protected $signature = 'eodhd:listen
        {--endpoint=forex : Which endpoint to hit (us, us-quote, forex, crypto)}
        {--symbols=EURUSD,GBPUSD : Comma-separated tickers}';

    protected $description = 'Connect to EODHD websocket and stream real‐time data';

    public function handle()
    {
        $token    = config('services.eodhd.api_token');
        $endpoint = $this->option('endpoint');
        $symbols  = $this->option('symbols');

        // Grab the global ReactPHP event loop
        $loop      = Loop::get();
        $connector = new Connector($loop);
        $url       = "wss://ws.eodhistoricaldata.com/ws/{$endpoint}?api_token={$token}";

        $this->info("Connecting to {$url}…");

        $connector($url)
            ->then(function(\Ratchet\Client\WebSocket $conn) use ($symbols) {
                $this->info("✅ Connected! Subscribing to {$symbols}");

                $conn->send(json_encode([
                    'action'  => 'subscribe',
                    'symbols' => $symbols,
                ]));

                $conn->on('message', function($msg) {
                    $data = json_decode($msg, true);

                    // Ignore control/ACK messages without a timestamp
                    if (! isset($data['t'])) {
                        Log::debug('EODHD control message (no timestamp):', $data);
                        return;
                    }

                    // Publish the raw tick to Redis for downstream consumers
                    Redis::publish('raw-ticks', json_encode($data));

                    // Parse and display a real tick
                    $ts      = (int)($data['t'] / 1000);
                    $timeStr = date('H:i:s', $ts);
                    $symbol  = $data['s'] ?? 'UNKNOWN';
                    $price   = $data['a'] ?? $data['p'] ?? 'n/a';

                    $this->info("Tick @ {$timeStr}: {$symbol} = {$price}");
                    Log::debug('EODHD tick:', $data);
                });

                $conn->on('close', function($code = null, $reason = null) {
                    $this->warn("Connection closed ({$code}): {$reason}");
                });
            }, function(\Exception $e) {
                $this->error('WebSocket connection error: ' . $e->getMessage());
            });

        // Start the loop (will block here, streaming indefinitely)
        $loop->run();
    }
}
