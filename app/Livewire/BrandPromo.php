<?php

namespace App\Livewire;

use App\Models\BrandRegistration;
use App\Models\Setting;
use App\Mail\BrandRegistrationNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

#[Layout('layouts.app')]
#[Title('For Brands')]
class BrandPromo extends Component
{
    #[Rule('required|string|max:255')]
    public $brandName = '';

    #[Rule('required|url|max:255')]
    public $website = '';

    #[Rule('required|email|max:255')]
    public $contactEmail = '';

    #[Rule('nullable|string|max:255')]
    public $contactName = '';

    #[Rule('nullable|string|max:50')]
    public $phone = '';

    #[Rule('nullable|string|max:1000')]
    public $message = '';

    #[Rule('required|string|max:2')]
    public $country = '';

    public $turnstileToken = '';
    public $submitted = false;
    public $isSubmitting = false;
    public $error = '';

    public function submit()
    {
        $this->validate();
        $this->error = '';
        $this->isSubmitting = true;

        // Verify Turnstile if configured
        $secretKey = Setting::get('turnstile_secret_key');
        if ($secretKey && $this->turnstileToken) {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $this->turnstileToken,
            ]);

            if (!$response->json('success')) {
                $this->isSubmitting = false;
                $this->error = 'Captcha verification failed. Please try again.';
                $this->dispatch('resetBrandsTurnstile');
                return;
            }
        }

        $registration = BrandRegistration::create([
            'brand_name' => $this->brandName,
            'website' => $this->website,
            'contact_email' => $this->contactEmail,
            'contact_name' => $this->contactName,
            'phone' => $this->phone,
            'message' => $this->message,
            'country_code' => $this->country,
        ]);

        // Send notification email to admin
        try {
            $adminEmail = Setting::get('admin_email', 'info@stylely.ai');
            Mail::to($adminEmail)->send(new BrandRegistrationNotification($registration));
        } catch (\Exception $e) {
            // Log error but don't fail the registration
            \Log::error('Failed to send brand registration email to ' . ($adminEmail ?? 'unknown') . ': ' . $e->getMessage());
        }

        $this->isSubmitting = false;
        $this->submitted = true;
    }

    public function resetForm()
    {
        $this->reset(['brandName', 'website', 'contactEmail', 'contactName', 'phone', 'message', 'country', 'submitted', 'error', 'turnstileToken']);
        $this->dispatch('resetBrandsTurnstile');
    }

    private function getCountries()
    {
        return [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'IN' => 'India',
            'PK' => 'Pakistan',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'JP' => 'Japan',
            'CN' => 'China',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'NL' => 'Netherlands',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
        ];
    }

    public function render()
    {
        return view('livewire.brand-promo', [
            'turnstileSiteKey' => Setting::get('turnstile_site_key', ''),
            'countries' => $this->getCountries(),
        ]);
    }
}
