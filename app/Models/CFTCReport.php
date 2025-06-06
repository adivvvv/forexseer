<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CFTCReport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'instrument_id',
        'report_date',
        'producer_long',
        'producer_short',
        'swap_long',
        'swap_short',
        'managed_long',
        'managed_short',
        'otherreport_long',
        'otherreport_short',
        'nonreportable_long',
        'nonreportable_short',
        'open_interest',
    ];

    // If you want to define the inverse relation:
    public function instrument()
    {
        return $this->belongsTo(Instrument::class, 'instrument_id', 'instrument_id');
    }
}
