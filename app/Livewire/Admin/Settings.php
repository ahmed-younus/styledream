<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Models\SeoSetting;
use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Settings')]
class Settings extends Component
{
    public string $activeTab = 'api';
    public array $settings = [];
    public array $seoSettings = [];
    public string $testEmailTo = '';

    public function mount()
    {
        Setting::initializeDefaults();
        SeoSetting::initializeDefaults();
        $this->loadSettings();
        $this->loadSeoSettings();
    }

    protected function loadSettings()
    {
        $this->settings = [
            'google_ai_api_key' => Setting::get('google_ai_api_key', ''),
            'stripe_public_key' => Setting::get('stripe_public_key', ''),
            'stripe_secret_key' => Setting::get('stripe_secret_key', ''),
            'stripe_webhook_secret' => Setting::get('stripe_webhook_secret', ''),
            'turnstile_site_key' => Setting::get('turnstile_site_key', ''),
            'turnstile_secret_key' => Setting::get('turnstile_secret_key', ''),
            'smtp_host' => Setting::get('smtp_host', ''),
            'smtp_port' => Setting::get('smtp_port', '587'),
            'smtp_username' => Setting::get('smtp_username', ''),
            'smtp_password' => Setting::get('smtp_password', ''),
            'smtp_encryption' => Setting::get('smtp_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', 'StyleDream'),
            'site_name' => Setting::get('site_name', 'StyleDream'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'signup_credits' => Setting::get('signup_credits', 3),
            'daily_free_credits' => Setting::get('daily_free_credits', 1),
        ];
    }

    public function saveApiSettings()
    {
        Setting::set('google_ai_api_key', $this->settings['google_ai_api_key'], 'api', 'password', true);
        Setting::set('stripe_public_key', $this->settings['stripe_public_key'], 'api', 'password', true);
        Setting::set('stripe_secret_key', $this->settings['stripe_secret_key'], 'api', 'password', true);
        Setting::set('stripe_webhook_secret', $this->settings['stripe_webhook_secret'], 'api', 'password', true);
        Setting::set('turnstile_site_key', $this->settings['turnstile_site_key'], 'api', 'text');
        Setting::set('turnstile_secret_key', $this->settings['turnstile_secret_key'], 'api', 'password', true);

        auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'API settings updated');
        $this->dispatch('notify', message: 'API settings saved successfully');
    }

    public function saveSmtpSettings()
    {
        Setting::set('smtp_host', $this->settings['smtp_host'], 'smtp');
        Setting::set('smtp_port', $this->settings['smtp_port'], 'smtp', 'number');
        Setting::set('smtp_username', $this->settings['smtp_username'], 'smtp');
        Setting::set('smtp_password', $this->settings['smtp_password'], 'smtp', 'password', true);
        Setting::set('smtp_encryption', $this->settings['smtp_encryption'], 'smtp');
        Setting::set('mail_from_address', $this->settings['mail_from_address'], 'smtp');
        Setting::set('mail_from_name', $this->settings['mail_from_name'], 'smtp');

        auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'SMTP settings updated');
        $this->dispatch('notify', message: 'SMTP settings saved successfully');
    }

    public function sendTestEmail()
    {
        $this->validate([
            'testEmailTo' => 'required|email',
        ]);

        try {
            // Configure mail settings dynamically
            config([
                'mail.mailers.smtp.host' => $this->settings['smtp_host'],
                'mail.mailers.smtp.port' => $this->settings['smtp_port'],
                'mail.mailers.smtp.username' => $this->settings['smtp_username'],
                'mail.mailers.smtp.password' => $this->settings['smtp_password'],
                'mail.mailers.smtp.encryption' => $this->settings['smtp_encryption'],
                'mail.from.address' => $this->settings['mail_from_address'],
                'mail.from.name' => $this->settings['mail_from_name'],
            ]);

            // Send test email
            \Illuminate\Support\Facades\Mail::raw(
                "This is a test email from StyleDream.\n\nIf you received this email, your SMTP configuration is working correctly!\n\n---\nSent from StyleDream Admin Panel",
                function ($message) {
                    $message->to($this->testEmailTo)
                        ->subject('Test Email - StyleDream SMTP Configuration');
                }
            );

            auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'Test email sent to ' . $this->testEmailTo);
            $this->dispatch('notify', message: 'Test email sent successfully to ' . $this->testEmailTo, type: 'success');
            $this->testEmailTo = '';
        } catch (\Exception $e) {
            \Log::error('Test email failed: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Failed to send test email: ' . $e->getMessage(), type: 'error');
        }
    }

    public function saveGeneralSettings()
    {
        Setting::set('site_name', $this->settings['site_name'], 'general');
        Setting::set('maintenance_mode', $this->settings['maintenance_mode'], 'general', 'boolean');
        Setting::set('signup_credits', $this->settings['signup_credits'], 'features', 'number');
        Setting::set('daily_free_credits', $this->settings['daily_free_credits'], 'features', 'number');

        auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'General settings updated');
        $this->dispatch('notify', message: 'General settings saved successfully');
    }

    protected function loadSeoSettings()
    {
        // Check if table exists before querying
        if (!Schema::hasTable('seo_settings')) {
            return;
        }

        $seoPages = SeoSetting::all();
        foreach ($seoPages as $page) {
            $this->seoSettings[$page->page_key] = [
                'meta_title' => $page->meta_title ?? '',
                'meta_description' => $page->meta_description ?? '',
                'meta_keywords' => $page->meta_keywords ?? '',
            ];
        }
    }

    public function saveSeoSettings()
    {
        // Check if table exists before saving
        if (!Schema::hasTable('seo_settings')) {
            $this->dispatch('notify', message: 'SEO settings table not found. Please run migrations.', type: 'error');
            return;
        }

        foreach ($this->seoSettings as $pageKey => $data) {
            SeoSetting::where('page_key', $pageKey)->update([
                'meta_title' => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'meta_keywords' => $data['meta_keywords'],
            ]);
            SeoSetting::clearCache($pageKey);
        }

        auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'SEO settings updated');
        $this->dispatch('notify', message: 'SEO settings saved successfully');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
