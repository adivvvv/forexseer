<?php
// app/Console/Commands/ListenEodhdUs.php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListenEodhdUs extends ListenEodhdCrypto
{
    protected $signature   = 'eodhd:listen-us';
    protected $description = 'Stream US stock ticks from EODHD';

    public function handle()
    {
        // EOD HD uses "us" for the US stocks endpoint
        $this->streamByType('us', 'us');
    }
}