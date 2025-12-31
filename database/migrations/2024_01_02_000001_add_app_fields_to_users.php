<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_url')->nullable()->after('password');
            $table->string('google_id')->nullable()->unique()->after('avatar_url');
            $table->integer('credits')->default(5)->after('google_id');
            $table->string('subscription_tier')->default('free')->after('credits');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_tier');
            $table->string('stripe_customer_id')->nullable()->after('subscription_ends_at');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            $table->integer('current_streak')->default(0)->after('stripe_subscription_id');
            $table->timestamp('last_credit_claimed_at')->nullable()->after('current_streak');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_url', 'google_id', 'credits', 'subscription_tier',
                'subscription_ends_at', 'stripe_customer_id', 'stripe_subscription_id',
                'current_streak', 'last_credit_claimed_at'
            ]);
        });
    }
};
