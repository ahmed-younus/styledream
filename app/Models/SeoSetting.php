<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SeoSetting extends Model
{
    protected $fillable = [
        'page_key',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
    ];

    /**
     * Get SEO settings for a page
     */
    public static function getForPage(string $pageKey): ?self
    {
        try {
            // Check if table exists (cached check)
            if (!Cache::remember('seo_table_exists', 3600, fn() => Schema::hasTable('seo_settings'))) {
                return null;
            }

            return Cache::remember("seo_{$pageKey}", 3600, function () use ($pageKey) {
                return static::where('page_key', $pageKey)->first();
            });
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clear cache for a page
     */
    public static function clearCache(string $pageKey): void
    {
        Cache::forget("seo_{$pageKey}");
    }

    /**
     * Get all SEO settings
     */
    public static function getAllPages(): array
    {
        return static::all()->keyBy('page_key')->toArray();
    }

    /**
     * Initialize default SEO settings for all pages
     */
    public static function initializeDefaults(): void
    {
        if (!Schema::hasTable('seo_settings')) {
            return;
        }

        $pages = [
            ['page_key' => 'home', 'meta_title' => 'Stylely - AI Virtual Try-On', 'meta_description' => 'Try on clothes virtually with AI. See how outfits look on you before buying.'],
            ['page_key' => 'pricing', 'meta_title' => 'Pricing - Stylely', 'meta_description' => 'Affordable credit packs for virtual try-ons. Start free today.'],
            ['page_key' => 'studio', 'meta_title' => 'Try-On Studio - Stylely', 'meta_description' => 'Upload your photo and try on any clothing item virtually.'],
            ['page_key' => 'wardrobe', 'meta_title' => 'My Wardrobe - Stylely', 'meta_description' => 'Save and organize your favorite clothing items.'],
            ['page_key' => 'feed', 'meta_title' => 'Style Feed - Stylely', 'meta_description' => 'Discover trending outfits and get inspired by the community.'],
            ['page_key' => 'brands', 'meta_title' => 'For Brands - Stylely', 'meta_description' => 'Partner with Stylely to offer virtual try-on to your customers.'],
            ['page_key' => 'about', 'meta_title' => 'About Us - Stylely', 'meta_description' => 'Learn about Stylely and our mission to transform online shopping.'],
            ['page_key' => 'contact', 'meta_title' => 'Contact Us - Stylely', 'meta_description' => 'Get in touch with the Stylely team.'],
            ['page_key' => 'login', 'meta_title' => 'Sign In - Stylely', 'meta_description' => 'Sign in to your Stylely account.'],
            ['page_key' => 'register', 'meta_title' => 'Get Started - Stylely', 'meta_description' => 'Create your free Stylely account and start trying on clothes virtually.'],
            ['page_key' => 'terms', 'meta_title' => 'Terms of Service - Stylely', 'meta_description' => 'Read our terms and conditions.'],
            ['page_key' => 'privacy', 'meta_title' => 'Privacy Policy - Stylely', 'meta_description' => 'Learn how we protect your privacy and data.'],
        ];

        foreach ($pages as $page) {
            static::firstOrCreate(
                ['page_key' => $page['page_key']],
                $page
            );
        }
    }
}
