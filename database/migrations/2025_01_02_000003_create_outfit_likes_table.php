<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('outfit_post_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'outfit_post_id']);
            $table->index('outfit_post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_likes');
    }
};
