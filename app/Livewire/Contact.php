<?php

namespace App\Livewire;

use App\Models\ContactInquiry;
use App\Models\Setting;
use App\Mail\ContactNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

#[Layout('layouts.app')]
#[Title('Contact Us')]
class Contact extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    #[Rule('required|string|max:255')]
    public string $subject = '';

    #[Rule('required|string|max:2000')]
    public string $message = '';

    public string $turnstileToken = '';
    public bool $submitted = false;
    public bool $isSubmitting = false;
    public string $error = '';

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
                $this->dispatch('resetTurnstile');
                return;
            }
        }

        // Create inquiry
        $inquiry = ContactInquiry::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        // Send notification email
        try {
            $adminEmail = Setting::get('admin_email', 'info@stylely.ai');
            Mail::to($adminEmail)->send(new ContactNotification($inquiry));
        } catch (\Exception $e) {
            \Log::error('Failed to send contact notification to ' . ($adminEmail ?? 'unknown') . ': ' . $e->getMessage());
        }

        $this->isSubmitting = false;
        $this->submitted = true;
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'subject', 'message', 'submitted', 'error', 'turnstileToken']);
        $this->dispatch('resetTurnstile');
    }

    public function render()
    {
        return view('livewire.contact', [
            'turnstileSiteKey' => Setting::get('turnstile_site_key', ''),
        ]);
    }
}
