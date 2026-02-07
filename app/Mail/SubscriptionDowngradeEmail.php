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

class SubscriptionDowngradeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public array $currentPlanDetails;
    public array $newPlanDetails;

    public function __construct(
        public User $user,
        public Subscription $subscription,
        public string $newPlan
    ) {
        $this->currentPlanDetails = PricingService::getPlan($subscription->plan) ?? [];
        $this->newPlanDetails = PricingService::getPlan($newPlan) ?? [];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Stylely Plan Change is Scheduled',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-downgrade',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
