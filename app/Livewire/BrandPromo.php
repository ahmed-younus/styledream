<?php

namespace App\Livewire;

use App\Models\BrandRegistration;
use App\Mail\BrandRegistrationNotification;
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

    public $submitted = false;
    public $isSubmitting = false;

    public function submit()
    {
        $this->validate();

        $this->isSubmitting = true;

        $registration = BrandRegistration::create([
            'brand_name' => $this->brandName,
            'website' => $this->website,
            'contact_email' => $this->contactEmail,
            'contact_name' => $this->contactName,
            'phone' => $this->phone,
            'message' => $this->message,
        ]);

        // Send notification email to admin
        try {
            $adminEmail = config('mail.admin_email', 'admin@styledream.com');
            Mail::to($adminEmail)->send(new BrandRegistrationNotification($registration));
        } catch (\Exception $e) {
            // Log error but don't fail the registration
            \Log::error('Failed to send brand registration email: ' . $e->getMessage());
        }

        $this->isSubmitting = false;
        $this->submitted = true;
    }

    public function resetForm()
    {
        $this->reset(['brandName', 'website', 'contactEmail', 'contactName', 'phone', 'message', 'submitted']);
    }

    public function render()
    {
        return view('livewire.brand-promo');
    }
}
