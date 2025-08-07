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
            $table->string('plate_number')->after('id');
            $table->timestamp('start_time')->useCurrent()->after('plate_number');
            $table->timestamp('end_time')->nullable()->after('start_time');
            $table->integer('duration_minutes')->nullable()->after('end_time');
            $table->decimal('amount_paid', 8, 2)->nullable()->after('duration_minutes');
            $table->boolean('printed')->default(false)->after('amount_paid');
            $table->foreignId('created_by')->constrained('users')->after('printed');

            // Add indexes for better performance
            $table->index('plate_number');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_sessions', function (Blueprint $table) {
            $table->dropIndex(['plate_number']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['end_time']);
            $table->dropIndex(['created_by']);

            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'plate_number',
                'customer_name',
                'customer_contact',
                'start_time',
                'end_time',
                'duration_minutes',
                'amount_paid',
                'printed',
                'created_by'
            ]);
        });
    }
};
