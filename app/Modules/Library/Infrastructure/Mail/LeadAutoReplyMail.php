<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Mail;

use App\Modules\Library\Domain\Entities\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class LeadAutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your inquiry — DigitalBuilders Architecture Team',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lead_auto_reply',
            with: [
                'name' => $this->lead->name(),
                'projectType' => $this->lead->projectType()->label(),
                'description' => $this->lead->description(),
            ],
        );
    }
}
