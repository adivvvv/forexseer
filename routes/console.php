<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('cftc:import-live')
    ->hourlyAt(45)
    ->withoutOverlapping()
    ->runInBackground();


Schedule::command('candles:prune')
    ->dailyAt('02:00')         // pick your time
    ->timezone('UTC')          // or 'Europe/Warsaw'
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->description('Delete candles older than 30 days');
    