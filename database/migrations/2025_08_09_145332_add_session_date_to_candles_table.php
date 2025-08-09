<?php
// database/migrations/2025_08_09_145332_add_session_date_to_candles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candles', function (Blueprint $table) {
            $table->date('session_date')->nullable()->after('start_at');
            $table->index(['asset_key', 'session_date', 'interval'], 'candles_session_idx');
        });
    }

    public function down(): void
    {
        Schema::table('candles', function (Blueprint $table) {
            $table->dropIndex('candles_session_idx');
            $table->dropColumn('session_date');
        });
    }
};