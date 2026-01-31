<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_registrations', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('brand_registrations', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }
};
