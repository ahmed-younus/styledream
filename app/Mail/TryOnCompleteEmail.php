<?php

namespace App\Mail;

use App\Models\TryOn;
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
        return new Envelope(
            subject: "Your Try-On is Ready - {$garmentName}",
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
}
