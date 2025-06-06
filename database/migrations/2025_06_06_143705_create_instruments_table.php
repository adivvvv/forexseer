<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::create('instruments', function (Blueprint $table) {

            // Use instrument_id instead of id()
            $table->id('instrument_id');

            // Exact CFTC “short” name, e.g. "Euro FX", "Gold", "S&P 500"
            $table->string('cftc_name')->unique();

            // The raw placeholder as it appears in the .txt files,
            // e.g. "GOLD - COMMODITY EXCHANGE INC.", 
            // "BRITISH POUND - CHICAGO MERCANTILE EXCHANGE"
            $table->string('cftc_name_scrapping')->nullable();

            // Your primary trading symbol, e.g. "6E", "GC", "ES"
            $table->string('our_symbol')->unique();

            // Alternative or CFD symbol (if you need), e.g. "EURUSD", "XAUUSD", "US500"
            $table->string('alt_symbol')->nullable();

            // When this instrument first appears in CFTC data
            $table->date('active_from')->nullable();

            // If/when it was delisted (nullable if still active)
            $table->date('active_to')->nullable();

            $table->timestamps();
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('instruments');
    }
};
