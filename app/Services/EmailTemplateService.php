<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class EmailTemplateService
{
    /**
     * Default templates configuration
     */
    protected static array $defaults = [
        'welcome' => [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to Stylely - Your AI Fashion Journey Begins!',
            'body' => "# Welcome {{user_name}}!\n\nWe're thrilled to have you join the Stylely community! Your AI-powered fashion journey starts now.\n\n**Your free credits:** {{signup_credits}} credits\n\nUse them to virtually try on any outfit and discover your perfect style.\n\n@component('mail::button', ['url' => '{{app_url}}/studio'])\nStart Exploring\n@endcomponent\n\nNeed help getting started? Check out our quick guide or reach out to our support team.\n\nHappy styling!",
            'variables' => ['user_name', 'signup_credits', 'app_url'],
            'is_active' => true,
            'send_admin_copy' => false,
        ],
        'password-reset' => [
            'name' => 'Password Reset',
            'subject' => 'Reset Your Stylely Password',
            'body' => "# Reset Your Password\n\nHi {{user_name}},\n\nWe received a request to reset your password. Click the button below to create a new one:\n\n@component('mail::button', ['url' => '{{reset_url}}', 'color' => 'primary'])\nReset Password\n@endcomponent\n\n@component('mail::panel')\n**Security Note:** This link will expire in 60 minutes. If you didn't request this reset, please ignore this email or contact support if you have concerns.\n@endcomponent\n\nStay secure!",
            'variables' => ['user_name', 'reset_url'],
            'is_active' => true,
            'send_admin_copy' => false,
        ],
        'tryon-complete' => [
            'name' => 'Try-On Complete',
            'subject' => 'Your Try-On is Ready - {{garment_name}}',
            'body' => "# Your Try-On is Ready!\n\nHi {{user_name}},\n\nGreat news! Your virtual try-on is complete and ready to view.\n\n@component('mail::panel')\n**Outfit Details:**\n- Item: {{garment_name}}\n- Brand: {{garment_brand}}\n- Category: {{garment_category}}\n@endcomponent\n\n@component('mail::button', ['url' => '{{app_url}}/my-outfits'])\nView Your Try-On\n@endcomponent\n\nLove the look? Save it to your wardrobe or share it with friends!\n\nHappy styling!",
            'variables' => ['user_name', 'garment_name', 'garment_brand', 'garment_category', 'app_url'],
            'is_active' => true,
            'send_admin_copy' => false,
        ],
        'purchase-confirmation' => [
            'name' => 'Credit Purchase',
            'subject' => 'Your Stylely Credit Purchase Confirmation',
            'body' => "# Purchase Confirmed!\n\nHi {{user_name}},\n\nThank you for your purchase! Your credits have been added to your account.\n\n@component('mail::panel')\n**Order Summary:**\n- Pack: {{pack_name}}\n- Credits: {{credits}}\n- Amount: {{currency}}{{amount}}\n- Date: {{purchase_date}}\n- Transaction ID: {{transaction_id}}\n@endcomponent\n\n@component('mail::button', ['url' => '{{app_url}}/studio'])\nStart Using Your Credits\n@endcomponent\n\nThank you for choosing Stylely!",
            'variables' => ['user_name', 'pack_name', 'credits', 'amount', 'currency', 'purchase_date', 'transaction_id', 'app_url'],
            'is_active' => true,
            'send_admin_copy' => true,
        ],
        'subscription-confirmation' => [
            'name' => 'Subscription Confirmation',
            'subject' => 'Welcome to Stylely {{plan_name}} - Subscription Confirmed',
            'body' => "# Welcome to {{plan_name}}!\n\nHi {{user_name}},\n\nThank you for subscribing to Stylely {{plan_name}}! Your account has been upgraded.\n\n@component('mail::panel')\n**Your Plan Benefits:**\n- Monthly Credits: {{credits_per_month}}\n- Next Billing: {{next_billing}}\n{{features}}\n@endcomponent\n\n@component('mail::button', ['url' => '{{app_url}}/studio'])\nStart Creating\n@endcomponent\n\nManage your subscription anytime from your [Billing Portal]({{app_url}}/billing).\n\nThank you for choosing Stylely!",
            'variables' => ['user_name', 'plan_name', 'credits_per_month', 'next_billing', 'features', 'app_url'],
            'is_active' => true,
            'send_admin_copy' => true,
        ],
        'contact-notification' => [
            'name' => 'Contact Form (Admin)',
            'subject' => 'New Contact Inquiry: {{subject}}',
            'body' => "# New Contact Form Submission\n\nYou have received a new contact inquiry.\n\n@component('mail::panel')\n**From:** {{sender_name}}\n**Email:** {{sender_email}}\n**Subject:** {{subject}}\n**Date:** {{submitted_at}}\n@endcomponent\n\n**Message:**\n\n{{message}}\n\n@component('mail::button', ['url' => '{{app_url}}/admin/dashboard'])\nView in Dashboard\n@endcomponent",
            'variables' => ['sender_name', 'sender_email', 'subject', 'message', 'submitted_at', 'app_url'],
            'is_active' => true,
            'send_admin_copy' => false,
        ],
        'brand-registration' => [
            'name' => 'Brand Registration (Admin)',
            'subject' => 'New Brand Registration: {{brand_name}}',
            'body' => "# New Brand Registration\n\nA new brand has registered on Stylely.\n\n@component('mail::panel')\n**Brand Details:**\n- Brand Name: {{brand_name}}\n- Website: {{website}}\n- Contact: {{contact_name}}\n- Email: {{contact_email}}\n- Phone: {{phone}}\n- Registered: {{registered_at}}\n@endcomponent\n\n**Message:**\n\n{{message}}\n\n@component('mail::button', ['url' => '{{app_url}}/admin/dashboard'])\nReview Registration\n@endcomponent",
            'variables' => ['brand_name', 'website', 'contact_name', 'contact_email', 'phone', 'message', 'registered_at', 'app_url'],
            'is_active' => true,
            'send_admin_copy' => false,
        ],
    ];

    /**
     * Get template configuration
     */
    public static function getTemplate(string $name): array
    {
        $cacheKey = "email_template_{$name}";

        return Cache::remember($cacheKey, 300, function () use ($name) {
            // Try to get from database
            $saved = Setting::get("email_template_{$name}");

            if ($saved && is_array($saved)) {
                return array_merge(self::getDefaults($name), $saved);
            }

            return self::getDefaults($name);
        });
    }

    /**
     * Get all templates
     */
    public static function getAllTemplates(): array
    {
        $templates = [];

        foreach (array_keys(self::$defaults) as $name) {
            $templates[$name] = self::getTemplate($name);
        }

        return $templates;
    }

    /**
     * Get default template configuration
     */
    public static function getDefaults(string $name): array
    {
        return self::$defaults[$name] ?? [
            'name' => ucfirst(str_replace('-', ' ', $name)),
            'subject' => 'Notification from Stylely',
            'body' => '',
            'variables' => [],
            'is_active' => true,
            'send_admin_copy' => false,
        ];
    }

    /**
     * Save template to database
     */
    public static function saveTemplate(string $name, array $data): void
    {
        Setting::set(
            "email_template_{$name}",
            $data,
            'email_templates',
            'json'
        );

        // Clear cache
        Cache::forget("email_template_{$name}");
    }

    /**
     * Reset template to defaults
     */
    public static function resetToDefault(string $name): void
    {
        Setting::where('key', "email_template_{$name}")->delete();
        Cache::forget("email_template_{$name}");
    }

    /**
     * Render subject with variables
     */
    public static function renderSubject(string $name, array $data): string
    {
        $template = self::getTemplate($name);
        return self::replaceVariables($template['subject'], $data);
    }

    /**
     * Render body with variables
     */
    public static function renderBody(string $name, array $data): string
    {
        $template = self::getTemplate($name);
        return self::replaceVariables($template['body'], $data);
    }

    /**
     * Check if template is active
     */
    public static function isActive(string $name): bool
    {
        $template = self::getTemplate($name);
        return $template['is_active'] ?? true;
    }

    /**
     * Check if admin copy should be sent
     */
    public static function shouldSendAdminCopy(string $name): bool
    {
        $template = self::getTemplate($name);
        return $template['send_admin_copy'] ?? false;
    }

    /**
     * Get available variables for a template
     */
    public static function getVariables(string $name): array
    {
        $template = self::getTemplate($name);
        return $template['variables'] ?? [];
    }

    /**
     * Replace variables in text
     */
    protected static function replaceVariables(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            // Handle both {{var}} and {{ var }} formats
            $text = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/', $value, $text);
        }

        return $text;
    }

    /**
     * Clear all template cache
     */
    public static function clearCache(): void
    {
        foreach (array_keys(self::$defaults) as $name) {
            Cache::forget("email_template_{$name}");
        }
    }
}
