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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('parking_sessions', 'plate_number')) {
                $table->string('plate_number')->after('id');
            }
            if (!Schema::hasColumn('parking_sessions', 'start_time')) {
                $table->timestamp('start_time')->useCurrent()->after('plate_number');
            }
            if (!Schema::hasColumn('parking_sessions', 'end_time')) {
                $table->timestamp('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('parking_sessions', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('parking_sessions', 'amount_paid')) {
                $table->decimal('amount_paid', 8, 2)->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('parking_sessions', 'printed')) {
                $table->boolean('printed')->default(false)->after('amount_paid');
            }
            if (!Schema::hasColumn('parking_sessions', 'created_by')) {
                $table->foreignId('created_by')->constrained('users')->after('printed');
            }

            // Add indexes for better performance (only if they don't exist)
            $indexExists = collect(Schema::getIndexes('parking_sessions'))->pluck('name')->toArray();
            if (!in_array('parking_sessions_plate_number_index', $indexExists) && Schema::hasColumn('parking_sessions', 'plate_number')) {
                $table->index('plate_number');
            }
            if (!in_array('parking_sessions_start_time_index', $indexExists) && Schema::hasColumn('parking_sessions', 'start_time')) {
                $table->index('start_time');
            }
            if (!in_array('parking_sessions_end_time_index', $indexExists) && Schema::hasColumn('parking_sessions', 'end_time')) {
                $table->index('end_time');
            }
            if (!in_array('parking_sessions_created_by_index', $indexExists) && Schema::hasColumn('parking_sessions', 'created_by')) {
                $table->index('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_sessions', function (Blueprint $table) {
            // Drop indexes if they exist
            $indexExists = collect(Schema::getIndexes('parking_sessions'))->pluck('name')->toArray();
            if (in_array('parking_sessions_plate_number_index', $indexExists)) {
                $table->dropIndex(['plate_number']);
            }
            if (in_array('parking_sessions_start_time_index', $indexExists)) {
                $table->dropIndex(['start_time']);
            }
            if (in_array('parking_sessions_end_time_index', $indexExists)) {
                $table->dropIndex(['end_time']);
            }
            if (in_array('parking_sessions_created_by_index', $indexExists)) {
                $table->dropIndex(['created_by']);
            }

            // Drop foreign key constraint if it exists
            if (Schema::hasColumn('parking_sessions', 'created_by')) {
                $table->dropForeign(['created_by']);
            }

            // Drop columns if they exist
            $columnsToCheck = [
                'plate_number', 'customer_name', 'customer_contact', 'start_time',
                'end_time', 'duration_minutes', 'amount_paid', 'printed', 'created_by'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('parking_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
