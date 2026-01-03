<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * Supported currencies with their symbols and country mappings
     */
    public const CURRENCIES = [
        'usd' => [
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'countries' => ['US', 'USA'],
        ],
        'gbp' => [
            'code' => 'GBP',
            'symbol' => '£',
            'name' => 'British Pound',
            'countries' => ['GB', 'UK', 'GBR'],
        ],
        'eur' => [
            'code' => 'EUR',
            'symbol' => '€',
            'name' => 'Euro',
            'countries' => ['DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'PT', 'IE', 'GR', 'FI', 'LU', 'MT', 'CY', 'SK', 'SI', 'EE', 'LV', 'LT'],
        ],
    ];

    /**
     * Default currency
     */
    public const DEFAULT_CURRENCY = 'usd';

    /**
     * Get all supported currencies
     */
    public static function getSupportedCurrencies(): array
    {
        return self::CURRENCIES;
    }

    /**
     * Get currency symbol
     */
    public static function getSymbol(string $currency): string
    {
        return self::CURRENCIES[$currency]['symbol'] ?? '$';
    }

    /**
     * Get currency name
     */
    public static function getName(string $currency): string
    {
        return self::CURRENCIES[$currency]['name'] ?? 'US Dollar';
    }

    /**
     * Get current user's currency
     */
    public static function getUserCurrency(): string
    {
        // 1. Check if user is logged in and has preference
        if (auth()->check()) {
            try {
                $userCurrency = auth()->user()->currency;
                if ($userCurrency) {
                    return $userCurrency;
                }
            } catch (\Exception $e) {
                // Column might not exist yet (migration not run)
            }
        }

        // 2. Check session
        if (session()->has('currency')) {
            return session()->get('currency');
        }

        // 3. Detect from IP
        $detected = self::detectCurrencyFromIp();
        session()->put('currency', $detected);

        return $detected;
    }

    /**
     * Set user's currency preference
     */
    public static function setUserCurrency(string $currency): void
    {
        if (!isset(self::CURRENCIES[$currency])) {
            $currency = self::DEFAULT_CURRENCY;
        }

        session()->put('currency', $currency);

        if (auth()->check()) {
            try {
                auth()->user()->update(['currency' => $currency]);
            } catch (\Exception $e) {
                // Column might not exist yet (migration not run)
                \Log::debug('Currency column not available', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Detect currency from IP address
     */
    public static function detectCurrencyFromIp(): string
    {
        try {
            $ip = request()->ip();

            // Skip for localhost
            if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
                return self::DEFAULT_CURRENCY;
            }

            // Cache the result for 24 hours
            $cacheKey = 'ip_currency_' . md5($ip);

            return Cache::remember($cacheKey, 86400, function () use ($ip) {
                // Using ip-api.com (free, no API key needed, 45 req/min limit)
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=countryCode");

                if ($response->successful()) {
                    $countryCode = $response->json('countryCode');
                    return self::getCurrencyFromCountry($countryCode);
                }

                return self::DEFAULT_CURRENCY;
            });
        } catch (\Exception $e) {
            return self::DEFAULT_CURRENCY;
        }
    }

    /**
     * Get currency from country code
     */
    public static function getCurrencyFromCountry(?string $countryCode): string
    {
        if (!$countryCode) {
            return self::DEFAULT_CURRENCY;
        }

        foreach (self::CURRENCIES as $currency => $data) {
            if (in_array($countryCode, $data['countries'])) {
                return $currency;
            }
        }

        return self::DEFAULT_CURRENCY;
    }

    /**
     * Format price for display
     */
    public static function formatPrice(int $cents, string $currency): string
    {
        $symbol = self::getSymbol($currency);
        $amount = $cents / 100;

        // Format based on currency
        if ($currency === 'eur') {
            // European format: €9,99
            return $symbol . number_format($amount, 2, ',', '.');
        }

        // US/UK format: $9.99 or £9.99
        return $symbol . number_format($amount, 2, '.', ',');
    }

    /**
     * Format price without decimals for round numbers
     */
    public static function formatPriceClean(int $cents, string $currency): string
    {
        $symbol = self::getSymbol($currency);
        $amount = $cents / 100;

        // If it's a whole number, show without decimals
        if ($amount == floor($amount)) {
            return $symbol . number_format($amount, 0);
        }

        return self::formatPrice($cents, $currency);
    }
}
