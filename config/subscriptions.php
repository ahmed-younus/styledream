<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Define the subscription plans available. Each plan has:
    | - name: Display name
    | - price: Price in cents
    | - currency: Currency code
    | - stripe_price_id: The Stripe Price ID (create in Stripe Dashboard)
    | - credits_per_month: Credits added each billing cycle
    | - features: List of features for display
    |
    | IMPORTANT: You must create Products and Prices in Stripe Dashboard
    | and update the stripe_price_id values below.
    |
    | Steps to create in Stripe:
    | 1. Go to https://dashboard.stripe.com/test/products
    | 2. Create a new product for each plan
    | 3. Add a recurring price (monthly)
    | 4. Copy the Price ID (starts with price_) and paste below
    |
    */

    'plans' => [
        'pro' => [
            'name' => 'Pro',
            'price' => 999,          // $9.99
            'currency' => 'usd',
            'interval' => 'month',
            'stripe_price_id' => env('STRIPE_PRICE_PRO', 'price_REPLACE_WITH_ACTUAL_PRICE_ID'),
            'credits_per_month' => 100,
            'features' => [
                '100 credits/month',
                'HD quality results',
                'Priority processing',
                'Unlimited wardrobe',
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 2499,         // $24.99
            'currency' => 'usd',
            'interval' => 'month',
            'stripe_price_id' => env('STRIPE_PRICE_PREMIUM', 'price_REPLACE_WITH_ACTUAL_PRICE_ID'),
            'credits_per_month' => 500, // Essentially unlimited for most users
            'features' => [
                '500 credits/month',
                '4K quality results',
                'API access',
                'Priority support',
            ],
        ],
    ],
];
