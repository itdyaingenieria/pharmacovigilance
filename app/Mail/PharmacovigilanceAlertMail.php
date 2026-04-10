<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PharmacovigilanceAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $payload) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pharmacovigilance Alert: Medication Lot Notice',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pharmacovigilance-alert',
            with: [
                'payload' => $this->payload,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
