<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Credit Packs
    |--------------------------------------------------------------------------
    |
    | Define the credit packs available for purchase. Each pack has:
    | - credits: Number of credits in the pack
    | - prices: Price in cents for each currency (usd, gbp, eur)
    | - label: Display label for the pack
    | - popular: Whether to highlight this pack (optional)
    | - best_value: Whether to show as best value (optional)
    |
    */

    'packs' => [
        'small' => [
            'credits' => 10,
            'prices' => [
                'usd' => 299,    // $2.99
                'gbp' => 249,    // £2.49
                'eur' => 279,    // €2.79
            ],
            'label' => '10 Credits',
        ],
        'medium' => [
            'credits' => 50,
            'prices' => [
                'usd' => 999,    // $9.99
                'gbp' => 799,    // £7.99
                'eur' => 899,    // €8.99
            ],
            'label' => '50 Credits',
            'popular' => true,
        ],
        'large' => [
            'credits' => 100,
            'prices' => [
                'usd' => 1499,   // $14.99
                'gbp' => 1199,   // £11.99
                'eur' => 1399,   // €13.99
            ],
            'label' => '100 Credits',
        ],
        'mega' => [
            'credits' => 500,
            'prices' => [
                'usd' => 4999,   // $49.99
                'gbp' => 3999,   // £39.99
                'eur' => 4499,   // €44.99
            ],
            'label' => '500 Credits',
            'best_value' => true,
        ],
    ],
];
