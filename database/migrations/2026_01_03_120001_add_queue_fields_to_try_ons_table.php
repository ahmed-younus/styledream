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
            // Store all garment URLs as JSON array for multi-garment support
            $table->json('garment_urls')->nullable()->after('garment_image_url');

            // Track queue job attempts
            $table->unsignedTinyInteger('attempts')->default(0)->after('status');

            // Track when processing started (for timeout detection)
            $table->timestamp('processing_started_at')->nullable()->after('attempts');

            // Add index for queue processing
            $table->index(['status', 'attempts', 'created_at'], 'try_ons_queue_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('try_ons', function (Blueprint $table) {
            $table->dropIndex('try_ons_queue_index');
            $table->dropColumn(['garment_urls', 'attempts', 'processing_started_at']);
        });
    }
};
