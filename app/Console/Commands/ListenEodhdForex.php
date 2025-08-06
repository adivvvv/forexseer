<?php
// app/Console/Commands/ListenEodhdForex.php

namespace App\Console\Commands;

class ListenEodhdForex extends ListenEodhdCrypto
{
    protected $signature   = 'eodhd:listen-forex';
    protected $description = 'Stream forex ticks from EODHD';

    public function handle()
    {
        $this->streamByType('forex', 'forex');
    }
}