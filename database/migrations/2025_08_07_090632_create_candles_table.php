<?php
// database/migrations/2025_08_07_090632_create_candles_table.php

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
        Schema::create('candles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('asset_key', 50);
            $table->enum('interval', ['15s','1m','5m','15m','30m','1h']);
            $table->timestamp('start_at');
            $table->decimal('open', 18, 8);
            $table->decimal('high', 18, 8);
            $table->decimal('low', 18, 8);
            $table->decimal('close', 18, 8);
            $table->unsignedBigInteger('volume')->default(0);
            $table->timestamps();

            // prevent duplicates & speed lookups
            $table->unique(['asset_key','interval','start_at']);
            $table->index(['asset_key','interval','start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candles');
    }
};
