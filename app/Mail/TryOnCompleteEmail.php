<?php

namespace App\Mail;

use App\Models\TryOn;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TryOnCompleteEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TryOn $tryOn
    ) {}

    public function envelope(): Envelope
    {
        $garmentName = $this->tryOn->garment_name ?? 'Your outfit';
        $subject = EmailTemplateService::renderSubject('tryon-complete', [
            'user_name' => $this->tryOn->user->name ?? 'User',
            'garment_name' => $garmentName,
        ]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tryon-complete',
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
        return EmailTemplateService::isActive('tryon-complete');
    }
}
