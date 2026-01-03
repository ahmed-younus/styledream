<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    // Groups
    const GROUP_GENERAL = 'general';
    const GROUP_API = 'api';
    const GROUP_SMTP = 'smtp';
    const GROUP_PRICING = 'pricing';
    const GROUP_FEATURES = 'features';

    // Types
    const TYPE_TEXT = 'text';
    const TYPE_NUMBER = 'number';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_PASSWORD = 'password';
    const TYPE_TEXTAREA = 'textarea';

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            $value = $setting->value;

            // Decrypt if encrypted
            if ($setting->is_encrypted && $value) {
                try {
                    $value = Crypt::decryptString($value);
                } catch (\Exception $e) {
                    return $default;
                }
            }

            // Cast based on type
            return match ($setting->type) {
                self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                self::TYPE_NUMBER => is_numeric($value) ? (float) $value : $default,
                self::TYPE_JSON => json_decode($value, true) ?? $default,
                default => $value,
            };
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null, bool $encrypted = false): static
    {
        // Convert value to string for storage
        $storedValue = match (true) {
            is_array($value) => json_encode($value),
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };

        // Encrypt if needed
        if ($encrypted && $storedValue) {
            $storedValue = Crypt::encryptString($storedValue);
        }

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'group' => $group ?? self::GROUP_GENERAL,
                'type' => $type ?? self::TYPE_TEXT,
                'is_encrypted' => $encrypted,
            ]
        );

        // Clear cache
        Cache::forget("setting_{$key}");

        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => static::get($setting->key)];
            })
            ->toArray();
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        static::all()->each(function ($setting) {
            Cache::forget("setting_{$setting->key}");
        });
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults(): void
    {
        $defaults = [
            // General
            ['key' => 'site_name', 'value' => 'StyleDream', 'group' => 'general', 'type' => 'text', 'label' => 'Site Name'],
            ['key' => 'site_tagline', 'value' => 'AI Virtual Try-On', 'group' => 'general', 'type' => 'text', 'label' => 'Site Tagline'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general', 'type' => 'boolean', 'label' => 'Maintenance Mode'],
            ['key' => 'default_language', 'value' => 'en', 'group' => 'general', 'type' => 'text', 'label' => 'Default Language'],

            // API Keys
            ['key' => 'google_ai_api_key', 'value' => '', 'group' => 'api', 'type' => 'password', 'label' => 'Google AI API Key', 'is_encrypted' => true],
            ['key' => 'stripe_public_key', 'value' => '', 'group' => 'api', 'type' => 'password', 'label' => 'Stripe Public Key', 'is_encrypted' => true],
            ['key' => 'stripe_secret_key', 'value' => '', 'group' => 'api', 'type' => 'password', 'label' => 'Stripe Secret Key', 'is_encrypted' => true],
            ['key' => 'stripe_webhook_secret', 'value' => '', 'group' => 'api', 'type' => 'password', 'label' => 'Stripe Webhook Secret', 'is_encrypted' => true],

            // SMTP
            ['key' => 'smtp_host', 'value' => '', 'group' => 'smtp', 'type' => 'text', 'label' => 'SMTP Host'],
            ['key' => 'smtp_port', 'value' => '587', 'group' => 'smtp', 'type' => 'number', 'label' => 'SMTP Port'],
            ['key' => 'smtp_username', 'value' => '', 'group' => 'smtp', 'type' => 'text', 'label' => 'SMTP Username'],
            ['key' => 'smtp_password', 'value' => '', 'group' => 'smtp', 'type' => 'password', 'label' => 'SMTP Password', 'is_encrypted' => true],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'group' => 'smtp', 'type' => 'text', 'label' => 'SMTP Encryption'],
            ['key' => 'mail_from_address', 'value' => 'hello@styledream.com', 'group' => 'smtp', 'type' => 'text', 'label' => 'From Email Address'],
            ['key' => 'mail_from_name', 'value' => 'StyleDream', 'group' => 'smtp', 'type' => 'text', 'label' => 'From Name'],

            // Features
            ['key' => 'signup_credits', 'value' => '3', 'group' => 'features', 'type' => 'number', 'label' => 'Free Credits on Signup'],
            ['key' => 'daily_free_credits', 'value' => '1', 'group' => 'features', 'type' => 'number', 'label' => 'Daily Free Credits'],
            ['key' => 'max_clothing_items', 'value' => '5', 'group' => 'features', 'type' => 'number', 'label' => 'Max Clothing Items per Try-On'],
        ];

        foreach ($defaults as $setting) {
            static::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
