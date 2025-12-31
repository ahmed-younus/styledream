<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('try_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('body_image_url');
            $table->string('garment_image_url');
            $table->string('result_image_url')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->integer('credits_used')->default(1);
            $table->string('garment_name')->nullable();
            $table->string('garment_brand')->nullable();
            $table->string('garment_category')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('try_ons');
    }
};
