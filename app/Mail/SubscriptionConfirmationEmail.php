<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\User;
use App\Services\PricingService;
use App\Services\EmailTemplateService;
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
        $subject = EmailTemplateService::renderSubject('subscription-confirmation', [
            'user_name' => $this->user->name,
            'plan_name' => $planName,
        ]);

        return new Envelope(
            subject: $subject,
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

    /**
     * Check if this email should be sent
     */
    public static function shouldSend(): bool
    {
        return EmailTemplateService::isActive('subscription-confirmation');
    }
}
