<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('outfit_post_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_reported')->default(false);
            $table->timestamps();

            $table->index(['outfit_post_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_comments');
    }
};
