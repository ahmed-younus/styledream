<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetUrl,
        public string $userName
    ) {}

    public function envelope(): Envelope
    {
        $subject = EmailTemplateService::renderSubject('password-reset', [
            'user_name' => $this->userName,
        ]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-reset',
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
        return EmailTemplateService::isActive('password-reset');
    }
}
