<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CrmOutreachMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $outreachSubject,
        public string $bodyContent,
        public string $trackingToken,
        public ?string $recipientName = null,
        public string $senderName = 'Ashish Gupta',
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS', 'ashish@digitalbuilders.in');
        $fromAddress = trim(str_replace(['"', "'", '\\'], '', (string) $fromAddress));

        return new Envelope(
            from: new Address($fromAddress, 'Ashish Gupta — DigitalBuilders'),
            subject: $this->outreachSubject,
        );
    }

    public function content(): Content
    {
        $trackingPixelUrl = url("/crm/track/open/{$this->trackingToken}");
        $baseUrl = url('/');

        // Wrap plain text line breaks into paragraphs
        $paragraphs = array_filter(array_map('trim', explode("\n", $this->bodyContent)));
        $formattedHtml = '';

        foreach ($paragraphs as $para) {
            // Check for markdown-like links or plain URLs and rewrite them with click tracker
            $paraHtml = htmlspecialchars($para, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            // Convert URL strings to tracked links
            $paraHtml = preg_replace_callback(
                '/https?:\/\/[^\s<"\'\)]+/',
                function ($matches) {
                    $rawUrl = $matches[0];
                    $trackedUrl = url("/crm/track/click/{$this->trackingToken}?url=" . urlencode($rawUrl));
                    return "<a href=\"{$trackedUrl}\" style=\"color: #0284c7; text-decoration: underline; font-weight: 600;\">{$rawUrl}</a>";
                },
                $paraHtml
            );

            $formattedHtml .= "<p style=\"margin: 0 0 16px 0; line-height: 1.65; color: #334155; font-size: 15px;\">{$paraHtml}</p>";
        }

        $homeTracked = url("/crm/track/click/{$this->trackingToken}?url=" . urlencode('https://www.digitalbuilders.in'));
        $calendarTracked = url("/crm/track/click/{$this->trackingToken}?url=" . urlencode('https://www.digitalbuilders.in/book'));

        $fullHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; margin: 0; padding: 32px 16px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);">
        {$formattedHtml}

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; line-height: 1.6;">
            <strong style="color: #0f172a; font-size: 15px; display: block; margin-bottom: 2px;">Ashish Gupta</strong>
            <span style="color: #475569; font-weight: 600;">Principal Architect &amp; Founder</span> · <a href="{$homeTracked}" style="color: #0284c7; text-decoration: none; font-weight: 700;">DigitalBuilders</a><br>
            <span style="font-size: 12px; color: #64748b;">Enterprise Web Architecture · Production ERPs · Cloud Infrastructure</span>
            <div style="margin-top: 10px; display: flex; gap: 14px; font-size: 12px;">
                <a href="{$homeTracked}" style="color: #0284c7; text-decoration: none; font-weight: 600;">🌐 digitalbuilders.in</a>
                &nbsp;·&nbsp;
                <a href="{$calendarTracked}" style="color: #0284c7; text-decoration: none; font-weight: 600;">📅 Book 15-Min Technical Sync</a>
            </div>
        </div>

        <!-- 1x1 Engagement Tracking Pixel -->
        <img src="{$trackingPixelUrl}" width="1" height="1" alt="" style="display:none;width:1px;height:1px;max-height:1px;max-width:1px;opacity:0;border:0;" />
    </div>
</body>
</html>
HTML;

        return new Content(htmlString: $fullHtml);
    }
}
