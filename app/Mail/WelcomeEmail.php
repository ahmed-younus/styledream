<?php

namespace App\Mail;

use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $subject = EmailTemplateService::renderSubject('welcome', [
            'user_name' => $this->user->name,
        ]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
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
        return EmailTemplateService::isActive('welcome');
    }
}
