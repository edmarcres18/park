<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('amount_paid', 8, 2)->nullable();
            $table->boolean('printed')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('parking_rate_id')->nullable()->constrained('parking_rates');
            $table->timestamps();

            $table->index('plate_number');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};


