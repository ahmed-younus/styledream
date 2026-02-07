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

class SubscriptionReactivatedEmail extends Mailable
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
        return new Envelope(
            subject: 'Welcome Back! Your Stylely Subscription is Active',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-reactivated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
