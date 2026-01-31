<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureMailFromDatabase();
    }

    /**
     * Configure mail settings from database.
     */
    protected function configureMailFromDatabase(): void
    {
        // Skip if running in console (migrations, etc.) or if settings table doesn't exist
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (!Schema::hasTable('settings')) {
                return;
            }

            // Set app name from database settings (used in emails)
            $siteName = Setting::get('site_name', 'Stylely');
            config(['app.name' => $siteName]);

            $smtpHost = Setting::get('smtp_host');

            // Only configure if SMTP settings are saved in database
            if ($smtpHost) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $smtpHost,
                    'mail.mailers.smtp.port' => (int) Setting::get('smtp_port', 587),
                    'mail.mailers.smtp.username' => Setting::get('smtp_username'),
                    'mail.mailers.smtp.password' => Setting::get('smtp_password'),
                    'mail.mailers.smtp.encryption' => Setting::get('smtp_encryption', 'tls'),
                    'mail.from.address' => Setting::get('mail_from_address', config('mail.from.address')),
                    'mail.from.name' => Setting::get('mail_from_name', config('mail.from.name')),
                    'mail.admin_email' => Setting::get('admin_email', 'info@stylely.ai'),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
            \Log::debug('Could not load mail settings from database: ' . $e->getMessage());
        }
    }
}
