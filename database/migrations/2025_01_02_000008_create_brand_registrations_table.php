<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('website');
            $table->string('contact_email');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('contact_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_registrations');
    }
};
