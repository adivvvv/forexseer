<?php
// app/Console/Commands/SubscribeRedisTicks.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Events\RealTimeTickReceived;

class SubscribeRedisTicks extends Command
{
    protected $signature   = 'redis:listen-ticks';
    protected $description = 'Subscribe to Redis “raw-ticks” and broadcast RealTimeTickReceived events (resilient).';

    public function handle(): int
    {
        $channel = 'raw-ticks';
        $this->info("Subscribing to Redis channel: {$channel}");

        $backoff = 1; // seconds, grows up to 30s

        while (true) {
            try {
                // Get a dedicated connection (defined in config/database.php as "subscriber")
                $conn   = Redis::connection('subscriber');
                $client = $conn->client(); // Predis\ClientInterface

                // NOTE: For truly idle-safe behavior, set read_write_timeout=0 in config (see below).
                // This loop will still auto-reconnect if Redis restarts or the socket drops.

                $pubsub = $client->pubSubLoop();
                $pubsub->subscribe($channel);

                $this->info('✅ Subscribed. Waiting for ticks…');
                $backoff = 1; // reset backoff on successful subscribe

                foreach ($pubsub as $message) {
                    // Kinds: subscribe, message, unsubscribe, psubscribe, punsubscribe, pong
                    if (!isset($message->kind)) {
                        continue;
                    }

                    if ($message->kind === 'subscribe') {
                        $this->info("Subscribed to {$message->channel}");
                        continue;
                    }

                    if ($message->kind === 'message') {
                        $payload = (string)($message->payload ?? '');
                        $this->info("⚡️ Raw tick in: {$payload}");

                        $tick = json_decode($payload, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($tick)) {
                            event(new RealTimeTickReceived($tick));
                        } else {
                            Log::warning('Tick JSON decode failed', ['payload' => $payload]);
                        }
                        continue;
                    }

                    if ($message->kind === 'pong') {
                        // optional: Log::debug('Redis pong');
                        continue;
                    }

                    // ignore other kinds quietly
                }

                // If the foreach exits, ensure we unsubscribe and try again
                try { $pubsub->unsubscribe(); } catch (\Throwable $e) { /* ignore */ }
            } catch (\Throwable $e) {
                Log::warning('Redis subscriber error: '.$e->getMessage());
                $this->warn("Redis connection lost, retrying in {$backoff}s…");
                sleep($backoff);
                $backoff = min($backoff * 2, 30);
            }
        }

        // unreachable
        // return self::SUCCESS;
    }
}