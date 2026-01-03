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
        Schema::create('credit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('pack'); // small, medium, large, mega
            $table->integer('credits');
            $table->integer('amount'); // in cents
            $table->string('currency', 3)->default('usd');
            $table->string('stripe_session_id')->unique();
            $table->string('stripe_payment_intent')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_purchases');
    }
};
