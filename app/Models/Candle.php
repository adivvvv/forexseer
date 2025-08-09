<?php
// app/Models/Candle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candle extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_key',
        'interval',
        'start_at',
        'session_date',
        'open',
        'high',
        'low',
        'close',
        'volume',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'session_date' => 'date',
        'open'     => 'decimal:8',
        'high'     => 'decimal:8',
        'low'      => 'decimal:8',
        'close'    => 'decimal:8',
        'volume'   => 'integer',
    ];


}
