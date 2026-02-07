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

class SubscriptionCancellationEmail extends Mailable
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
            subject: 'Your Stylely Subscription Will End on ' . $this->subscription->current_period_end->format('M d, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-cancellation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
