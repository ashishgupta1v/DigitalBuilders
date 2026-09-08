<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Deal $deal)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎉 Proposal Accepted: {$this->deal->title} ({$this->deal->formatted_amount})",
        );
    }

    public function content(): Content
    {
        $clientName = htmlspecialchars((string) ($this->deal->lead?->name ?? 'Valued Client'), ENT_QUOTES, 'UTF-8');
        $company = htmlspecialchars((string) ($this->deal->organization?->name ?? $this->deal->lead?->company ?? 'Client Organization'), ENT_QUOTES, 'UTF-8');
        $dealTitle = htmlspecialchars((string) $this->deal->title, ENT_QUOTES, 'UTF-8');
        $amount = htmlspecialchars((string) $this->deal->formatted_amount, ENT_QUOTES, 'UTF-8');
        $currency = htmlspecialchars((string) $this->deal->currency, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars((string) ($this->deal->lead?->phone ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string) ($this->deal->lead?->email ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $crmUrl = url('/crm');

        $html = <<<HTML
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 28px; color: #1e293b; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 9999px; border: 1px solid #a7f3d0;">
            Milestone Achieved
        </span>
        <h2 style="color: #0f172a; margin: 12px 0 0 0; font-size: 20px; font-weight: 700;">
            🎉 Proposal Accepted Online!
        </h2>
    </div>

    <p style="font-size: 15px; line-height: 1.6; color: #334155; margin: 0 0 16px 0;">
        <strong>{$clientName}</strong> from <strong>{$company}</strong> has formally accepted the engineering scope and proposal for <strong>{$dealTitle}</strong>.
    </p>

    <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 16px 18px; margin: 20px 0; border-radius: 6px;">
        <p style="margin: 4px 0; font-size: 14px;"><strong>Target Investment:</strong> <span style="font-size: 16px; color: #059669; font-weight: 700;">{$amount} {$currency}</span></p>
        <p style="margin: 4px 0; font-size: 14px;"><strong>Contact Phone:</strong> {$phone}</p>
        <p style="margin: 4px 0; font-size: 14px;"><strong>Contact Email:</strong> {$email}</p>
        <p style="margin: 4px 0; font-size: 14px; color: #64748b;"><strong>Status:</strong> Advanced to Negotiation (85% win probability)</p>
    </div>

    <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
        Recommended Next Action: Review kickoff timeline on WhatsApp, generate advance kickoff deposit link, and prepare initial sprint board.
    </p>

    <div style="margin-top: 24px;">
        <a href="{$crmUrl}" style="background: #0284c7; color: #ffffff; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; display: inline-block;">
            Open Executive CRM Cockpit &rarr;
        </a>
    </div>
</div>
HTML;

        return new Content(htmlString: $html);
    }
}
