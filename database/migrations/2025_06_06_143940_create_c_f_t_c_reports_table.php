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
        Schema::create('cftc_reports', function (Blueprint $table) {
            $table->id();

            // Now references instruments.instrument_id
            $table->foreignId('instrument_id')
                  ->constrained('instruments', 'instrument_id')
                  ->onDelete('cascade');

            // The Friday “as‐of” date of this weekly report
            $table->date('report_date');

            //
            // Disaggregated CoT columns (all BIGINT to accommodate large values)
            //
            // 1) Producer/Merchant/Processor/User (Commercial)
            $table->bigInteger('producer_long')->nullable();
            $table->bigInteger('producer_short')->nullable();

            // 2) Swap Dealers (Commercial)
            $table->bigInteger('swap_long')->nullable();
            $table->bigInteger('swap_short')->nullable();

            // 3) Managed Money (Noncommercial)
            $table->bigInteger('managed_long')->nullable();
            $table->bigInteger('managed_short')->nullable();

            // 4) Other Reportables (Noncommercial)
            $table->bigInteger('otherreport_long')->nullable();
            $table->bigInteger('otherreport_short')->nullable();

            // 5) Nonreportable (Small/“retail”) positions
            $table->bigInteger('nonreportable_long')->nullable();
            $table->bigInteger('nonreportable_short')->nullable();

            // 6) Total Open Interest
            $table->bigInteger('open_interest')->nullable();

            $table->timestamps();

            // Enforce one row per instrument_id + report_date
            $table->unique(['instrument_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cftc_reports');
    }
};
