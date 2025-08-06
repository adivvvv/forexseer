<?php
// app/Console/Commands/SubscribeRedisTicks.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Events\RealTimeTickReceived;
use Illuminate\Support\Facades\Redis;

class SubscribeRedisTicks extends Command
{
    protected $signature   = 'redis:listen-ticks';
    protected $description = 'Subscribe to Redis “ticks” channel and re-broadcast into Laravel.';

    public function handle()
{
    $this->info('Subscribing to Redis channel: raw-ticks');

    Redis::connection('subscriber')->subscribe(['raw-ticks'], function ($msg) {
        $tick = json_decode($msg, true);
        $this->info("⚡️ Raw tick in: {$msg}");

        // Broadcast cleanly on public "ticks" channel
        event(new RealTimeTickReceived($tick));
    });
}
}