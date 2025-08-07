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
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable(); // GPS accuracy in meters
            $table->decimal('altitude', 8, 2)->nullable(); // Altitude in meters
            $table->decimal('speed', 8, 2)->nullable(); // Speed in m/s
            $table->decimal('heading', 8, 2)->nullable(); // Heading in degrees
            $table->string('location_source')->default('gps'); // gps, network, passive, fused
            $table->string('address')->nullable(); // Reverse geocoded address
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('session_id')->nullable(); // Track location per session
            $table->boolean('is_active')->default(true); // Current active location
            $table->timestamp('location_timestamp'); // When location was recorded
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'location_timestamp']);
            $table->index(['user_id', 'is_active']);
            $table->index('location_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
