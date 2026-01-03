<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_outfits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('outfit_post_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('try_on_id')->nullable()->constrained('try_ons')->onDelete('set null');
            $table->string('image_url');
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->json('garment_data')->nullable(); // Store garment info for re-try
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_outfits');
    }
};
