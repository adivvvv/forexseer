<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    use HasFactory;

    // Tell Laravel the PK column is instrument_id
    protected $primaryKey = 'instrument_id';
    
    protected $fillable = [
        'cftc_name',
        'cftc_name_scrapping',
        'our_symbol',
        'alt_symbol',
        'active_from',
        'active_to',
    ];

    // Relationship: an instrument has many CFTC reports
    public function reports()
    {
        return $this->hasMany(CFTCReport::class, 'instrument_id', 'instrument_id');
    }
}
