<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class PricingService
{
    /**
     * Get all subscription plans
     */
    public static function getPlans(): array
    {
        return Cache::remember('pricing_plans', 300, function () {
            $savedPlans = Setting::get('subscription_plans');
            return $savedPlans ?: config('subscriptions.plans', []);
        });
    }

    /**
     * Get a specific subscription plan
     */
    public static function getPlan(string $key): ?array
    {
        $plans = self::getPlans();
        return $plans[$key] ?? null;
    }

    /**
     * Get plan price for a specific currency
     */
    public static function getPlanPrice(string $key, string $currency = 'usd'): int
    {
        $plan = self::getPlan($key);
        if (!$plan) {
            return 0;
        }

        // New multi-currency format
        if (isset($plan['prices']) && is_array($plan['prices'])) {
            return $plan['prices'][$currency] ?? $plan['prices']['usd'] ?? 0;
        }

        // Old single-price format (fallback)
        return $plan['price'] ?? 0;
    }

    /**
     * Get plan Stripe price ID for a specific currency
     */
    public static function getPlanStripePriceId(string $key, string $currency = 'usd'): ?string
    {
        $plan = self::getPlan($key);
        if (!$plan) {
            return null;
        }

        // New multi-currency format
        if (isset($plan['stripe_price_ids']) && is_array($plan['stripe_price_ids'])) {
            return $plan['stripe_price_ids'][$currency] ?? $plan['stripe_price_ids']['usd'] ?? null;
        }

        // Old single-price format (fallback)
        return $plan['stripe_price_id'] ?? null;
    }

    /**
     * Get all credit packs
     */
    public static function getCreditPacks(): array
    {
        return Cache::remember('pricing_credit_packs', 300, function () {
            $savedPacks = Setting::get('credit_packs');
            return $savedPacks ?: config('credits.packs', []);
        });
    }

    /**
     * Get a specific credit pack
     */
    public static function getCreditPack(string $key): ?array
    {
        $packs = self::getCreditPacks();
        return $packs[$key] ?? null;
    }

    /**
     * Get credit pack price for a specific currency
     */
    public static function getPackPrice(string $key, string $currency = 'usd'): int
    {
        $pack = self::getCreditPack($key);
        if (!$pack) {
            return 0;
        }

        // New multi-currency format
        if (isset($pack['prices']) && is_array($pack['prices'])) {
            return $pack['prices'][$currency] ?? $pack['prices']['usd'] ?? 0;
        }

        // Old single-price format (fallback)
        return $pack['price'] ?? 0;
    }

    /**
     * Get plans formatted for display with specific currency
     */
    public static function getPlansForCurrency(string $currency = 'usd'): array
    {
        $plans = self::getPlans();
        $formatted = [];

        foreach ($plans as $key => $plan) {
            $formatted[$key] = $plan;

            // Add resolved price for the currency
            if (isset($plan['prices']) && is_array($plan['prices'])) {
                $formatted[$key]['price'] = $plan['prices'][$currency] ?? $plan['prices']['usd'] ?? 0;
            }

            // Add resolved Stripe price ID for the currency
            if (isset($plan['stripe_price_ids']) && is_array($plan['stripe_price_ids'])) {
                $formatted[$key]['stripe_price_id'] = $plan['stripe_price_ids'][$currency] ?? $plan['stripe_price_ids']['usd'] ?? '';
            }
        }

        return $formatted;
    }

    /**
     * Get credit packs formatted for display with specific currency
     */
    public static function getCreditPacksForCurrency(string $currency = 'usd'): array
    {
        $packs = self::getCreditPacks();
        $formatted = [];

        foreach ($packs as $key => $pack) {
            $formatted[$key] = $pack;

            // Add resolved price for the currency
            if (isset($pack['prices']) && is_array($pack['prices'])) {
                $formatted[$key]['price'] = $pack['prices'][$currency] ?? $pack['prices']['usd'] ?? 0;
            }

            // Calculate per-credit price for the currency
            $price = $formatted[$key]['price'] ?? 0;
            $credits = $pack['credits'] ?? 1;
            $symbol = CurrencyService::getSymbol($currency);
            $formatted[$key]['per_credit'] = $symbol . number_format($price / 100 / $credits, 2) . '/credit';
        }

        return $formatted;
    }

    /**
     * Clear pricing cache
     */
    public static function clearCache(): void
    {
        // Clear PricingService cache
        Cache::forget('pricing_plans');
        Cache::forget('pricing_credit_packs');

        // Also clear Setting cache to ensure fresh data
        Cache::forget('setting_subscription_plans');
        Cache::forget('setting_credit_packs');

        // Clear any other potential caches
        Cache::forget('pricing:plans:usd');
        Cache::forget('pricing:plans:gbp');
        Cache::forget('pricing:plans:eur');
        Cache::forget('pricing:packs:usd');
        Cache::forget('pricing:packs:gbp');
        Cache::forget('pricing:packs:eur');
    }

    /**
     * Force clear ALL cache (use sparingly)
     */
    public static function flushAllCache(): void
    {
        Cache::flush();
    }
}
