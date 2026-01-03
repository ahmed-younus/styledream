<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Define the subscription plans available. Each plan has:
    | - name: Display name
    | - prices: Price in cents for each currency (usd, gbp, eur)
    | - stripe_price_ids: Stripe Price IDs for each currency
    | - credits_per_month: Credits added each billing cycle
    | - description: Short description of the plan
    | - button_text: Text for the action button
    | - badge: Optional badge (e.g., 'Popular', 'Best Value')
    | - features: List of features for display
    |
    | IMPORTANT: You must create Products and Prices in Stripe Dashboard
    | for each currency and update the stripe_price_ids values below.
    |
    | Steps to create in Stripe:
    | 1. Go to https://dashboard.stripe.com/test/products
    | 2. Create a new product for each plan
    | 3. Add recurring prices for USD, GBP, EUR (monthly)
    | 4. Copy each Price ID and paste below
    |
    */

    'plans' => [
        'free' => [
            'name' => 'Free',
            'prices' => [
                'usd' => 0,
                'gbp' => 0,
                'eur' => 0,
            ],
            'stripe_price_ids' => [
                'usd' => '',
                'gbp' => '',
                'eur' => '',
            ],
            'credits_per_month' => 0,
            'description' => 'Perfect for trying out StyleDream',
            'button_text' => 'Current Plan',
            'badge' => '',
            'features' => [
                '5 credits on signup',
                '1 free credit daily',
                'Basic AI try-on',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'prices' => [
                'usd' => 999,    // $9.99
                'gbp' => 799,    // £7.99
                'eur' => 899,    // €8.99
            ],
            'stripe_price_ids' => [
                'usd' => env('STRIPE_PRICE_PRO_USD', 'price_REPLACE_WITH_USD_PRICE_ID'),
                'gbp' => env('STRIPE_PRICE_PRO_GBP', 'price_REPLACE_WITH_GBP_PRICE_ID'),
                'eur' => env('STRIPE_PRICE_PRO_EUR', 'price_REPLACE_WITH_EUR_PRICE_ID'),
            ],
            'credits_per_month' => 100,
            'description' => 'For fashion enthusiasts',
            'button_text' => 'Upgrade to Pro',
            'badge' => 'Popular',
            'features' => [
                '100 credits/month',
                'HD quality results',
                'Priority processing',
                'Unlimited wardrobe',
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'prices' => [
                'usd' => 2499,   // $24.99
                'gbp' => 1999,   // £19.99
                'eur' => 2299,   // €22.99
            ],
            'stripe_price_ids' => [
                'usd' => env('STRIPE_PRICE_PREMIUM_USD', 'price_REPLACE_WITH_USD_PRICE_ID'),
                'gbp' => env('STRIPE_PRICE_PREMIUM_GBP', 'price_REPLACE_WITH_GBP_PRICE_ID'),
                'eur' => env('STRIPE_PRICE_PREMIUM_EUR', 'price_REPLACE_WITH_EUR_PRICE_ID'),
            ],
            'credits_per_month' => 500,
            'description' => 'For power users',
            'button_text' => 'Go Premium',
            'badge' => '',
            'features' => [
                '500 credits/month',
                '4K quality results',
                'API access',
                'Priority support',
            ],
        ],
    ],
];
