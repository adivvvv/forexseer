<?php
// app/Console/Commands/PruneCandles.php

namespace App\Console\Commands;

use App\Models\Candle;
use Illuminate\Console\Command;

class PruneCandles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candles:prune {--days=30}';
    protected $description = 'Delete candles older than N days (default 30).';
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $days = (int)$this->option('days');
        $cut  = now()->subDays($days);

        $deleted = Candle::where('start_at', '<', $cut)->delete();
        $this->info("Deleted {$deleted} rows older than {$days} days.");

        return self::SUCCESS;
    }
}
