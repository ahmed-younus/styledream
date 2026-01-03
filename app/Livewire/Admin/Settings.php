<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Models\AdminActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Settings')]
class Settings extends Component
{
    public string $activeTab = 'api';
    public array $settings = [];
    public string $testEmailTo = '';

    public function mount()
    {
        Setting::initializeDefaults();
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->settings = [
            'google_ai_api_key' => Setting::get('google_ai_api_key', ''),
            'stripe_public_key' => Setting::get('stripe_public_key', ''),
            'stripe_secret_key' => Setting::get('stripe_secret_key', ''),
            'stripe_webhook_secret' => Setting::get('stripe_webhook_secret', ''),
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

    public function saveGeneralSettings()
    {
        Setting::set('site_name', $this->settings['site_name'], 'general');
        Setting::set('maintenance_mode', $this->settings['maintenance_mode'], 'general', 'boolean');
        Setting::set('signup_credits', $this->settings['signup_credits'], 'features', 'number');
        Setting::set('daily_free_credits', $this->settings['daily_free_credits'], 'features', 'number');

        auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'General settings updated');
        $this->dispatch('notify', message: 'General settings saved successfully');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
