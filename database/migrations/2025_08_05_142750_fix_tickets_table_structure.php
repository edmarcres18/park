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
        Schema::dropIfExists('tickets');

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('parking_session_id')->constrained('parking_sessions')->onDelete('cascade');
            $table->string('plate_number');
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->decimal('rate', 8, 2)->default(0);
            $table->string('parking_slot')->nullable();
            $table->boolean('is_printed')->default(false);
            $table->json('qr_data')->nullable();
            $table->string('barcode')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('ticket_number');
            $table->index('plate_number');
            $table->index('time_in');
            $table->index('parking_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
