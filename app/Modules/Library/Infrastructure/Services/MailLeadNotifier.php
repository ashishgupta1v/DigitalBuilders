<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Services;

use App\Modules\Library\Application\Interfaces\LeadNotifierInterface;
use App\Modules\Library\Domain\Entities\Lead;
use App\Modules\Library\Infrastructure\Mail\LeadAutoReplyMail;
use App\Modules\Library\Infrastructure\Mail\NewLeadMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class MailLeadNotifier implements LeadNotifierInterface
{
    public function notify(Lead $lead): void
    {
        // 1. Admin Email Notification
        try {
            $to = config('mail.lead_inbox', 'ashishgupta1v@gmail.com');
            Mail::to($to)->send(new NewLeadMail($lead));
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin lead email notification: ' . $e->getMessage());
        }

        // 2. Client Auto-Reply Email
        try {
            $clientEmail = $lead->email()->value();
            if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($clientEmail)->send(new LeadAutoReplyMail($lead));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send client auto-reply email: ' . $e->getMessage());
        }

        // 3. Optional Slack/Discord/Telegram Webhook
        try {
            $webhookUrl = config('services.lead_webhook_url') ?? env('LEAD_WEBHOOK_URL');
            if (! empty($webhookUrl) && is_string($webhookUrl)) {
                Http::timeout(5)->post($webhookUrl, [
                    'text' => sprintf(
                        "🚀 *New Lead on DigitalBuilders!*\n• *Name:* %s\n• *Email:* %s\n• *Phone:* %s\n• *Type:* %s\n• *Notes:* %s",
                        $lead->name(),
                        $lead->email()->value(),
                        $lead->phone()->value(),
                        $lead->projectType()->label(),
                        $lead->description() ?? 'N/A',
                    ),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send lead webhook notification: ' . $e->getMessage());
        }
    }
}

