<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_templates', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('id');
            $table->boolean('is_active')->default(true)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_templates', function (Blueprint $table) {
            $table->dropColumn(['is_default', 'is_active']);
        });
    }
};
