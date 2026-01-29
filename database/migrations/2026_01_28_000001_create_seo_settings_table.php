<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique(); // home, pricing, studio, etc.
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        // Insert default SEO settings for all pages
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
            DB::table('seo_settings')->insert(array_merge($page, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
