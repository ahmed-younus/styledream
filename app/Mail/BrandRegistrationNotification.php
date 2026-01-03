<?php

namespace App\Mail;

use App\Models\BrandRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BrandRegistrationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BrandRegistration $registration
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Brand Registration: ' . $this->registration->brand_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.brand-registration',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
