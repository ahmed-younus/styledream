<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialActivationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Stylely - Your Free Trial is Active!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial-activation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
