<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('try_on_id')->nullable()->constrained('try_ons')->onDelete('set null');
            $table->string('image_url');
            $table->text('caption')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->boolean('is_sponsored')->default(false);
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['visibility', 'is_hidden', 'created_at']);
            $table->index('is_sponsored');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_posts');
    }
};
