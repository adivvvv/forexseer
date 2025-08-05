<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Events\RealTimeTickReceived;

class SubscribeRedisTicks extends Command
{
    protected $signature   = 'redis:listen-ticks';
    protected $description = 'Subscribe to Redis “ticks” channel and re-broadcast into Laravel.';

    public function handle()
    {
        $this->info('Subscribing to Redis channel: ticks');

        // use the dedicated subscriber connection
        Redis::connection('subscriber')
            ->subscribe(['ticks'], function ($message) {
                $data = json_decode($message, true);
                if (! is_array($data)) {
                    return;
                }
                event(new RealTimeTickReceived($data));
            });
    }
}