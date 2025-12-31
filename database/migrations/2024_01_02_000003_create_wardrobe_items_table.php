<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wardrobe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('image_url');
            $table->string('original_url')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('category')->default('other');
            $table->integer('try_on_count')->default(0);
            $table->timestamp('last_tried_at')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'is_favorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wardrobe_items');
    }
};
