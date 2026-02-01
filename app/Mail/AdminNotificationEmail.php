<?php

namespace App\Mail;

use App\Models\Setting;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $templateName;
    public string $originalSubject;
    public array $data;

    public function __construct(
        string $templateName,
        string $originalSubject,
        array $data = []
    ) {
        $this->templateName = $templateName;
        $this->originalSubject = $originalSubject;
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[ADMIN COPY] {$this->originalSubject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-notification',
            with: [
                'templateName' => $this->templateName,
                'data' => $this->data,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    /**
     * Send admin copy of an email if enabled
     */
    public static function sendIfEnabled(string $templateName, string $originalSubject, array $data = []): void
    {
        if (!EmailTemplateService::shouldSendAdminCopy($templateName)) {
            return;
        }

        $adminEmail = Setting::get('admin_email', 'info@stylely.ai');

        if (!$adminEmail) {
            return;
        }

        try {
            \Mail::to($adminEmail)->send(new self($templateName, $originalSubject, $data));
        } catch (\Exception $e) {
            \Log::error('Failed to send admin copy email', [
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
