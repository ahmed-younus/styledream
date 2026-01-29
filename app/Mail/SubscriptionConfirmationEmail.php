<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public array $planDetails;

    public function __construct(
        public User $user,
        public Subscription $subscription
    ) {
        $this->planDetails = PricingService::getPlan($subscription->plan) ?? [];
    }

    public function envelope(): Envelope
    {
        $planName = ucfirst($this->subscription->plan);
        return new Envelope(
            subject: "Welcome to Stylely {$planName} - Subscription Confirmed",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
