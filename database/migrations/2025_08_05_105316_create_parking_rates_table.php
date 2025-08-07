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
        Schema::create('parking_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('rate_type', ['hourly', 'minutely'])->default('hourly');
            $table->decimal('rate_amount', 8, 2);
            $table->integer('grace_period')->nullable()->comment('Grace period in minutes');
            $table->boolean('is_active')->default(false);
            $table->string('name')->nullable()->comment('Optional rate name/description');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index for performance
            $table->index('is_active');
            $table->index('rate_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_rates');
    }
};
