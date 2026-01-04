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
        Schema::table('try_ons', function (Blueprint $table) {
            $table->unsignedTinyInteger('queue_position')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('try_ons', function (Blueprint $table) {
            $table->dropColumn('queue_position');
        });
    }
};
