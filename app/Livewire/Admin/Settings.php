<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Models\SeoSetting;
use App\Models\AdminActivityLog;
use App\Services\EmailTemplateService;
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

    // Email Templates
    public array $emailTemplates = [];
    public ?string $editingTemplate = null;
    public string $editSubject = '';
    public string $editBody = '';
    public bool $editIsActive = true;
    public bool $editSendAdminCopy = false;
    public string $previewMode = 'light';

    // Test Email Modal
    public ?string $testingTemplate = null;
    public string $testEmailAddress = '';

    public function mount()
    {
        Setting::initializeDefaults();
        SeoSetting::initializeDefaults();
        $this->loadSettings();
        $this->loadSeoSettings();
        $this->loadEmailTemplates();
    }

    protected function loadEmailTemplates()
    {
        $this->emailTemplates = EmailTemplateService::getAllTemplates();
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
            'mail_from_name' => Setting::get('mail_from_name', 'Stylely'),
            'admin_email' => Setting::get('admin_email', 'info@stylely.ai'),
            'site_name' => Setting::get('site_name', 'Stylely'),
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
        Setting::set('admin_email', $this->settings['admin_email'], 'smtp');

        // Clear mail manager cache so new settings take effect immediately
        app()->forgetInstance('mail.manager');

        auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'SMTP settings updated');
        $this->dispatch('notify', message: 'SMTP settings saved successfully');
    }

    public function sendTestEmail()
    {
        $this->validate([
            'testEmailTo' => 'required|email',
        ]);

        // Clear previous results
        session()->forget(['testEmailResult', 'testEmailSuccess', 'testEmailTip']);

        // Validate SMTP settings before attempting
        $missingFields = [];
        if (empty($this->settings['smtp_host'])) $missingFields[] = 'SMTP Host';
        if (empty($this->settings['smtp_port'])) $missingFields[] = 'Port';
        if (empty($this->settings['mail_from_address'])) $missingFields[] = 'From Address';

        if (!empty($missingFields)) {
            session()->flash('testEmailResult', 'Missing required fields: ' . implode(', ', $missingFields));
            session()->flash('testEmailSuccess', false);
            session()->flash('testEmailTip', 'Please fill in all required SMTP settings and save them before testing.');
            return;
        }

        try {
            // Configure mail settings dynamically
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $this->settings['smtp_host'],
                'mail.mailers.smtp.port' => (int) $this->settings['smtp_port'],
                'mail.mailers.smtp.username' => $this->settings['smtp_username'],
                'mail.mailers.smtp.password' => $this->settings['smtp_password'],
                'mail.mailers.smtp.encryption' => $this->settings['smtp_encryption'] ?: 'tls',
                'mail.from.address' => $this->settings['mail_from_address'],
                'mail.from.name' => $this->settings['mail_from_name'] ?: 'Stylely',
            ]);

            // Clear the mail manager cache to use new config
            app()->forgetInstance('mail.manager');

            // Send test email
            \Illuminate\Support\Facades\Mail::raw(
                "This is a test email from Stylely.\n\nIf you received this email, your SMTP configuration is working correctly!\n\nSMTP Host: {$this->settings['smtp_host']}\nPort: {$this->settings['smtp_port']}\nFrom: {$this->settings['mail_from_address']}\n\n---\nSent from Stylely Admin Panel at " . now()->format('Y-m-d H:i:s'),
                function ($message) {
                    $message->to($this->testEmailTo)
                        ->subject('Test Email - Stylely SMTP Configuration');
                }
            );

            auth('admin')->user()->logActivity(AdminActivityLog::ACTION_SETTINGS_CHANGED, null, null, null, null, 'Test email sent to ' . $this->testEmailTo);

            session()->flash('testEmailResult', "Test email sent to {$this->testEmailTo}. Please check your inbox (and spam folder).");
            session()->flash('testEmailSuccess', true);
            $this->dispatch('notify', message: 'Test email sent successfully!', type: 'success');
            $this->testEmailTo = '';

        } catch (\Swift_TransportException $e) {
            $this->handleSmtpError($e);
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $this->handleSmtpError($e);
        } catch (\Exception $e) {
            \Log::error('Test email failed: ' . $e->getMessage(), ['exception' => $e]);
            session()->flash('testEmailResult', $e->getMessage());
            session()->flash('testEmailSuccess', false);
            session()->flash('testEmailTip', 'Check your SMTP settings and try again. Make sure to save settings before testing.');
            $this->dispatch('notify', message: 'Failed to send test email', type: 'error');
        }
    }

    protected function handleSmtpError(\Exception $e)
    {
        $errorMessage = $e->getMessage();
        $tip = '';

        \Log::error('SMTP Error: ' . $errorMessage, ['exception' => $e]);

        // Parse common SMTP errors and provide helpful tips
        if (str_contains($errorMessage, 'Connection refused') || str_contains($errorMessage, 'Connection could not be established')) {
            $tip = "Cannot connect to SMTP server. Check if the host '{$this->settings['smtp_host']}' and port '{$this->settings['smtp_port']}' are correct. Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted).";
        } elseif (str_contains($errorMessage, 'Authentication') || str_contains($errorMessage, '535') || str_contains($errorMessage, 'credentials')) {
            $tip = "Authentication failed. Check your username and password. For Gmail, you need to use an 'App Password' (not your regular password). For other providers, check their SMTP documentation.";
        } elseif (str_contains($errorMessage, 'SSL') || str_contains($errorMessage, 'TLS') || str_contains($errorMessage, 'certificate')) {
            $tip = "SSL/TLS error. Try changing encryption from 'tls' to 'ssl' or vice versa. Port 587 typically uses TLS, port 465 uses SSL.";
        } elseif (str_contains($errorMessage, 'timed out') || str_contains($errorMessage, 'timeout')) {
            $tip = "Connection timed out. The SMTP server may be slow or unreachable. Check your internet connection and verify the host address.";
        } elseif (str_contains($errorMessage, '550') || str_contains($errorMessage, 'relay')) {
            $tip = "The server rejected the email. Make sure the 'From Address' is valid and you're authorized to send from this address.";
        } elseif (str_contains($errorMessage, 'getaddrinfo') || str_contains($errorMessage, 'No such host')) {
            $tip = "Cannot find the SMTP server. Check if the hostname is spelled correctly. Example: 'smtp.gmail.com' for Gmail.";
        } else {
            $tip = "Check all your SMTP settings. Common issues: incorrect host, wrong port, invalid credentials, or firewall blocking the connection.";
        }

        session()->flash('testEmailResult', $errorMessage);
        session()->flash('testEmailSuccess', false);
        session()->flash('testEmailTip', $tip);
        $this->dispatch('notify', message: 'Failed to send test email', type: 'error');
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

    // Email Template Methods
    public function editEmailTemplate(string $name)
    {
        $template = $this->emailTemplates[$name] ?? null;
        if (!$template) return;

        $this->editingTemplate = $name;
        $this->editSubject = $template['subject'] ?? '';
        $this->editBody = $template['body'] ?? '';
        $this->editIsActive = $template['is_active'] ?? true;
        $this->editSendAdminCopy = $template['send_admin_copy'] ?? false;
    }

    public function saveEmailTemplate()
    {
        if (!$this->editingTemplate) return;

        EmailTemplateService::saveTemplate($this->editingTemplate, [
            'subject' => $this->editSubject,
            'body' => $this->editBody,
            'is_active' => $this->editIsActive,
            'send_admin_copy' => $this->editSendAdminCopy,
        ]);

        $this->loadEmailTemplates();
        auth('admin')->user()->logActivity(
            AdminActivityLog::ACTION_SETTINGS_CHANGED,
            null, null, null, null,
            'Email template updated: ' . $this->editingTemplate
        );

        $this->cancelEditTemplate();
        $this->dispatch('notify', message: 'Email template saved successfully');
    }

    public function cancelEditTemplate()
    {
        $this->editingTemplate = null;
        $this->editSubject = '';
        $this->editBody = '';
        $this->editIsActive = true;
        $this->editSendAdminCopy = false;
    }

    public function resetEmailTemplate(string $name)
    {
        EmailTemplateService::resetToDefault($name);
        $this->loadEmailTemplates();

        auth('admin')->user()->logActivity(
            AdminActivityLog::ACTION_SETTINGS_CHANGED,
            null, null, null, null,
            'Email template reset to default: ' . $name
        );

        $this->dispatch('notify', message: 'Email template reset to default');
    }

    public function toggleEmailActive(string $name)
    {
        $template = $this->emailTemplates[$name] ?? null;
        if (!$template) return;

        $newStatus = !($template['is_active'] ?? true);

        EmailTemplateService::saveTemplate($name, [
            'subject' => $template['subject'],
            'body' => $template['body'],
            'is_active' => $newStatus,
            'send_admin_copy' => $template['send_admin_copy'] ?? false,
        ]);

        $this->loadEmailTemplates();
        $this->dispatch('notify', message: 'Email template ' . ($newStatus ? 'enabled' : 'disabled'));
    }

    public function toggleAdminCopy(string $name)
    {
        $template = $this->emailTemplates[$name] ?? null;
        if (!$template) return;

        $newStatus = !($template['send_admin_copy'] ?? false);

        EmailTemplateService::saveTemplate($name, [
            'subject' => $template['subject'],
            'body' => $template['body'],
            'is_active' => $template['is_active'] ?? true,
            'send_admin_copy' => $newStatus,
        ]);

        $this->loadEmailTemplates();
        $this->dispatch('notify', message: 'Admin copy ' . ($newStatus ? 'enabled' : 'disabled'));
    }

    public function openTestEmailModal(string $templateName)
    {
        $this->testingTemplate = $templateName;
        $this->testEmailAddress = $this->settings['admin_email'] ?? '';
    }

    public function cancelTestEmail()
    {
        $this->testingTemplate = null;
        $this->testEmailAddress = '';
    }

    public function sendTemplateTestEmail()
    {
        if (!$this->testingTemplate) {
            $this->dispatch('notify', message: 'No template selected', type: 'error');
            return;
        }

        $this->validate([
            'testEmailAddress' => 'required|email',
        ], [
            'testEmailAddress.required' => 'Please enter an email address',
            'testEmailAddress.email' => 'Please enter a valid email address',
        ]);

        $toEmail = $this->testEmailAddress;
        $templateName = $this->testingTemplate;

        try {
            // Send the test email using the appropriate Mailable
            switch ($templateName) {
                case 'welcome':
                    $mockUser = new \App\Models\User([
                        'name' => 'Test User',
                        'email' => $toEmail,
                    ]);
                    $mockUser->id = 1;
                    \Mail::to($toEmail)->send(new \App\Mail\WelcomeEmail($mockUser));
                    break;

                case 'password-reset':
                    \Mail::to($toEmail)->send(new \App\Mail\PasswordResetEmail(
                        config('app.url') . '/reset-password/test-token-123',
                        'Test User'
                    ));
                    break;

                case 'purchase-confirmation':
                    $mockUser = new \App\Models\User(['name' => 'Test User', 'email' => $toEmail]);
                    $mockUser->id = 1;
                    $mockPurchase = new \App\Models\CreditPurchase();
                    $mockPurchase->pack = 'starter';
                    $mockPurchase->credits = 10;
                    $mockPurchase->amount = 199;
                    $mockPurchase->currency = 'usd';
                    $mockPurchase->stripe_session_id = 'test_session_123';
                    $mockPurchase->created_at = now();
                    \Mail::to($toEmail)->send(new \App\Mail\PurchaseConfirmationEmail($mockUser, $mockPurchase));
                    break;

                case 'subscription-confirmation':
                    $mockUser = new \App\Models\User(['name' => 'Test User', 'email' => $toEmail]);
                    $mockUser->id = 1;
                    $mockSubscription = new \App\Models\Subscription();
                    $mockSubscription->plan = 'pro';
                    $mockSubscription->status = 'active';
                    $mockSubscription->current_period_end = now()->addMonth();
                    \Mail::to($toEmail)->send(new \App\Mail\SubscriptionConfirmationEmail($mockUser, $mockSubscription));
                    break;

                case 'tryon-complete':
                    $this->sendGenericTestEmail($templateName, $toEmail);
                    break;

                case 'contact-notification':
                    $mockInquiry = new \App\Models\ContactInquiry();
                    $mockInquiry->name = 'Test Customer';
                    $mockInquiry->email = 'testcustomer@example.com';
                    $mockInquiry->subject = 'Test Inquiry Subject';
                    $mockInquiry->message = 'This is a test message from the email template testing system.';
                    $mockInquiry->created_at = now();
                    \Mail::to($toEmail)->send(new \App\Mail\ContactNotification($mockInquiry));
                    break;

                case 'brand-registration':
                    $mockRegistration = new \App\Models\BrandRegistration();
                    $mockRegistration->brand_name = 'Test Brand Co.';
                    $mockRegistration->website = 'https://testbrand.com';
                    $mockRegistration->contact_email = 'contact@testbrand.com';
                    $mockRegistration->contact_name = 'John Test';
                    $mockRegistration->phone = '+1 234 567 8900';
                    $mockRegistration->message = 'This is a test brand registration message.';
                    $mockRegistration->created_at = now();
                    \Mail::to($toEmail)->send(new \App\Mail\BrandRegistrationNotification($mockRegistration));
                    break;

                default:
                    $this->sendGenericTestEmail($templateName, $toEmail);
            }

            $this->dispatch('notify', message: 'Test email sent to ' . $toEmail);
            $this->cancelTestEmail();

        } catch (\Exception $e) {
            \Log::error('Failed to send test email', [
                'template' => $templateName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', message: 'Failed to send test email: ' . $e->getMessage(), type: 'error');
        }
    }

    protected function sendGenericTestEmail(string $templateName, string $toEmail)
    {
        // Send a generic test email using raw markdown
        $template = EmailTemplateService::getTemplate($templateName);
        $subject = '[TEST] ' . ($template['subject'] ?? 'Test Email');

        \Mail::raw("This is a test email for template: {$templateName}", function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)->subject($subject);
        });
    }

    protected function getMockDataForTemplate(string $templateName): array
    {
        return match($templateName) {
            'welcome' => ['user_name' => 'Test User', 'signup_credits' => 5],
            'password-reset' => ['user_name' => 'Test User', 'reset_url' => config('app.url') . '/reset'],
            'purchase-confirmation' => ['user_name' => 'Test User', 'credits' => 10, 'amount' => 199],
            'subscription-confirmation' => ['user_name' => 'Test User', 'plan_name' => 'Pro'],
            'tryon-complete' => ['user_name' => 'Test User', 'garment_name' => 'Test Outfit'],
            default => ['user_name' => 'Test User'],
        };
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
