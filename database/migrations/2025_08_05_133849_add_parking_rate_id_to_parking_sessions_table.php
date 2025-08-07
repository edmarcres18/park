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
        Schema::table('parking_sessions', function (Blueprint $table) {
            // Add parking_rate_id column if it doesn't exist
            if (!Schema::hasColumn('parking_sessions', 'parking_rate_id')) {
                $table->unsignedBigInteger('parking_rate_id')->nullable()->after('created_by');
                $table->foreign('parking_rate_id')->references('id')->on('parking_rates')->onDelete('set null');
                $table->index('parking_rate_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('parking_sessions', 'parking_rate_id')) {
                $table->dropForeign(['parking_rate_id']);
                $table->dropIndex(['parking_rate_id']);
                $table->dropColumn('parking_rate_id');
            }
        });
    }
};
