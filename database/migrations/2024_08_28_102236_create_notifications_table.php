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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('general'); // general, parking, system, alert
            $table->json('data')->nullable(); // Additional data for the notification
            $table->timestamp('scheduled_at')->nullable(); // When to send the notification
            $table->timestamp('sent_at')->nullable(); // When the notification was actually sent
            $table->string('status')->default('pending'); // pending, sent, failed, cancelled
            $table->string('target_audience')->default('all'); // all, attendants, customers, role_based
            $table->json('target_users')->nullable(); // Specific user IDs if not targeting all
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'scheduled_at']);
            $table->index(['target_audience', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
