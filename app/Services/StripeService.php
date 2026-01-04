<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Price;
use Stripe\Product;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected static bool $initialized = false;

    /**
     * Initialize Stripe API
     */
    protected static function init(): void
    {
        if (!self::$initialized) {
            Stripe::setApiKey(config('services.stripe.secret'));
            self::$initialized = true;
        }
    }

    /**
     * Create or get a Stripe Product for subscription plans
     */
    public static function getOrCreateProduct(string $planKey, string $planName): ?string
    {
        self::init();

        $productId = "styledream_{$planKey}";

        try {
            // Try to retrieve existing product
            $product = Product::retrieve($productId);

            // Update name if different
            if ($product->name !== $planName) {
                Product::update($productId, ['name' => $planName]);
            }

            return $productId;
        } catch (\Exception $e) {
            // Product doesn't exist, create it
            try {
                $product = Product::create([
                    'id' => $productId,
                    'name' => $planName,
                    'description' => "StyleDream {$planName} Subscription Plan",
                ]);
                return $product->id;
            } catch (\Exception $createError) {
                Log::error('Stripe: Failed to create product', [
                    'plan' => $planKey,
                    'error' => $createError->getMessage(),
                ]);
                return null;
            }
        }
    }

    /**
     * Create a recurring price for a subscription plan
     */
    public static function createSubscriptionPrice(
        string $productId,
        int $amount,
        string $currency
    ): ?string {
        self::init();

        if ($amount <= 0) {
            return '';
        }

        try {
            $price = Price::create([
                'product' => $productId,
                'unit_amount' => $amount,
                'currency' => strtolower($currency),
                'recurring' => [
                    'interval' => 'month',
                    'interval_count' => 1,
                ],
            ]);

            Log::info('Stripe: Created subscription price', [
                'price_id' => $price->id,
                'product' => $productId,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return $price->id;
        } catch (\Exception $e) {
            Log::error('Stripe: Failed to create price', [
                'product' => $productId,
                'amount' => $amount,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if a Stripe Price matches the expected amount and currency
     */
    public static function verifyPrice(string $priceId, int $expectedAmount, string $currency): bool
    {
        self::init();

        try {
            $price = Price::retrieve($priceId);
            return $price->unit_amount === $expectedAmount
                && strtolower($price->currency) === strtolower($currency)
                && $price->active;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get price details from Stripe
     */
    public static function getPrice(string $priceId): ?array
    {
        self::init();

        try {
            $price = Price::retrieve($priceId);
            return [
                'id' => $price->id,
                'amount' => $price->unit_amount,
                'currency' => $price->currency,
                'active' => $price->active,
                'product' => $price->product,
                'recurring' => $price->recurring ? [
                    'interval' => $price->recurring->interval,
                    'interval_count' => $price->recurring->interval_count,
                ] : null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Archive (deactivate) an old price
     */
    public static function archivePrice(string $priceId): bool
    {
        self::init();

        try {
            Price::update($priceId, ['active' => false]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create or update subscription prices for a plan
     * Returns array of price IDs for each currency
     */
    public static function syncPlanPrices(
        string $planKey,
        string $planName,
        array $prices,
        array $existingPriceIds = []
    ): array {
        $productId = self::getOrCreateProduct($planKey, $planName);

        if (!$productId) {
            return $existingPriceIds;
        }

        $newPriceIds = [];
        $currencies = ['usd', 'gbp', 'eur'];

        foreach ($currencies as $currency) {
            $amount = $prices[$currency] ?? 0;
            $existingPriceId = $existingPriceIds[$currency] ?? '';

            // Skip if amount is 0 (free plan)
            if ($amount <= 0) {
                $newPriceIds[$currency] = '';
                continue;
            }

            // Check if existing price is still valid
            if (!empty($existingPriceId) && self::verifyPrice($existingPriceId, $amount, $currency)) {
                $newPriceIds[$currency] = $existingPriceId;
                continue;
            }

            // Create new price
            $newPriceId = self::createSubscriptionPrice($productId, $amount, $currency);

            if ($newPriceId) {
                $newPriceIds[$currency] = $newPriceId;

                // Archive old price if it exists and is different
                if (!empty($existingPriceId) && $existingPriceId !== $newPriceId) {
                    self::archivePrice($existingPriceId);
                }
            } else {
                // Keep existing if creation failed
                $newPriceIds[$currency] = $existingPriceId;
            }
        }

        return $newPriceIds;
    }
}
