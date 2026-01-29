<?php

namespace App\Mail;

use App\Models\CreditPurchase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public CreditPurchase $purchase
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Stylely Credit Purchase Confirmation',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.purchase-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
