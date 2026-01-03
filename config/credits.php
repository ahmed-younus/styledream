<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Credit Packs
    |--------------------------------------------------------------------------
    |
    | Define the credit packs available for purchase. Each pack has:
    | - credits: Number of credits in the pack
    | - price: Price in cents (e.g., 299 = $2.99)
    | - currency: Currency code (default: usd)
    | - label: Display label for the pack
    | - popular: Whether to highlight this pack (optional)
    |
    */

    'packs' => [
        'small' => [
            'credits' => 10,
            'price' => 299,      // $2.99
            'currency' => 'usd',
            'label' => '10 Credits',
            'per_credit' => '$0.30/credit',
        ],
        'medium' => [
            'credits' => 50,
            'price' => 999,      // $9.99
            'currency' => 'usd',
            'label' => '50 Credits',
            'per_credit' => '$0.20/credit',
            'popular' => true,
        ],
        'large' => [
            'credits' => 100,
            'price' => 1499,     // $14.99
            'currency' => 'usd',
            'label' => '100 Credits',
            'per_credit' => '$0.15/credit',
        ],
        'mega' => [
            'credits' => 500,
            'price' => 4999,     // $49.99
            'currency' => 'usd',
            'label' => '500 Credits',
            'per_credit' => '$0.10/credit',
            'best_value' => true,
        ],
    ],
];
