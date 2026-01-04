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
            $table->timestamp('viewed_at')->nullable()->after('result_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('try_ons', function (Blueprint $table) {
            $table->dropColumn('viewed_at');
        });
    }
};
