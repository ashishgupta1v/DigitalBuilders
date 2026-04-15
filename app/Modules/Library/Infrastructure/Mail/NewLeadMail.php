<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Mail;

use App\Modules\Library\Domain\Entities\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private Lead $lead,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Lead: {$this->lead->name()} — {$this->lead->projectType()->label()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-lead',
            with: [
                'leadName'    => $this->lead->name(),
                'leadEmail'   => $this->lead->email()->value(),
                'leadPhone'   => $this->lead->phone()->value(),
                'projectType' => $this->lead->projectType()->label(),
                'description' => $this->lead->description(),
                'submittedAt' => $this->lead->createdAt()?->format('d M Y, h:i A'),
            ],
        );
    }
}
