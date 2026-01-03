<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;

class CreateStripeProducts extends Command
{
    protected $signature = 'stripe:create-products';
    protected $description = 'Create Stripe products and prices for subscriptions';

    public function handle()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->info('Creating Stripe products...');

        // Create Pro Plan
        $proProduct = Product::create([
            'name' => 'Pro Plan',
            'description' => 'StyleDream Pro - 100 credits/month, HD quality, Priority processing',
        ]);

        $proPrice = Price::create([
            'product' => $proProduct->id,
            'unit_amount' => 999, // $9.99
            'currency' => 'usd',
            'recurring' => ['interval' => 'month'],
        ]);

        $this->info("✓ Pro Plan created");
        $this->line("  Product ID: {$proProduct->id}");
        $this->line("  Price ID: {$proPrice->id}");

        // Create Premium Plan
        $premiumProduct = Product::create([
            'name' => 'Premium Plan',
            'description' => 'StyleDream Premium - 500 credits/month, 4K quality, API access, Priority support',
        ]);

        $premiumPrice = Price::create([
            'product' => $premiumProduct->id,
            'unit_amount' => 2499, // $24.99
            'currency' => 'usd',
            'recurring' => ['interval' => 'month'],
        ]);

        $this->info("✓ Premium Plan created");
        $this->line("  Product ID: {$premiumProduct->id}");
        $this->line("  Price ID: {$premiumPrice->id}");

        $this->newLine();
        $this->warn('Add these to your .env file:');
        $this->line("STRIPE_PRICE_PRO={$proPrice->id}");
        $this->line("STRIPE_PRICE_PREMIUM={$premiumPrice->id}");

        return Command::SUCCESS;
    }
}
