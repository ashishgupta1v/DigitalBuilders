<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CrmOutreachEmail;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CrmEmailTrackingController extends Controller
{
    public function __construct(
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * 1x1 Transparent pixel endpoint for tracking email opens.
     */
    public function trackOpen(Request $request, string $token): Response
    {
        $email = CrmOutreachEmail::with(['lead', 'deal'])->where('tracking_token', $token)->first();

        if ($email) {
            $isFirstOpen = is_null($email->opened_at);

            $email->increment('open_count');
            $email->update([
                'opened_at' => $email->opened_at ?: now(),
                'status'    => ($email->status === 'sent') ? 'opened' : $email->status,
            ]);

            // Auto-advance deal stage to contacted if still in new inbound
            if ($email->deal && in_array($email->deal->stage, ['new', 'inbound'], true)) {
                $email->deal->update([
                    'stage'       => 'contacted',
                    'probability' => 25,
                ]);

                if ($email->lead) {
                    $email->lead->update([
                        'stage'             => 'contacted',
                        'last_contact_date' => now(),
                    ]);
                }
            }

            // Log activity on the lead's timeline
            try {
                Activity::create([
                    'lead_id'           => $email->lead_id,
                    'deal_id'           => $email->deal_id,
                    'type'              => 'email',
                    'subject'           => "Outreach Email Opened (View #{$email->open_count})",
                    'description'       => "Prospect opened '{$email->subject}'. Stage confirmed as Contacted.",
                    'touchpoint_number' => $email->touchpoint_number,
                    'metadata'          => [
                        'ip'         => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 255),
                        'opened_at'  => now()->toIso8601String(),
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to log email open activity: ' . $e->getMessage());
            }

            // Telegram notification on first open and high engagement (3+ opens)
            if ($isFirstOpen || $email->open_count === 3 || $email->open_count === 5) {
                $recipient = $email->recipient_name ? "{$email->recipient_name} ({$email->recipient_email})" : $email->recipient_email;
                $company = $email->lead?->company ? " [{$email->lead->company}]" : '';
                $opens = $email->open_count;

                $text = "🔥 *OUTREACH EMAIL OPENED!* (Open #{$opens})\n"
                    . "━━━━━━━━━━━━━━━━━━━━\n"
                    . "• *Prospect:* {$recipient}{$company}\n"
                    . "• *Subject:* `{$email->subject}`\n"
                    . "• *Touchpoint:* #" . ($email->touchpoint_number ?: 1) . "\n\n"
                    . "👉 *Action:* View prospect in Cockpit: " . url('/crm');

                $this->telegramBot->sendMessage($text);
            }
        }

        // 1x1 Transparent PNG binary (43 bytes)
        $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return response($transparentPng, 200, [
            'Content-Type'   => 'image/png',
            'Content-Length' => strlen($transparentPng),
            'Cache-Control'  => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma'         => 'no-cache',
            'Expires'        => '0',
        ]);
    }

    /**
     * Click redirector for link engagement tracking.
     */
    public function trackClick(Request $request, string $token): RedirectResponse
    {
        $rawTarget = $request->query('url', 'https://www.digitalbuilders.in');
        $targetUrl = filter_var($rawTarget, FILTER_VALIDATE_URL) ? (string) $rawTarget : 'https://www.digitalbuilders.in';

        // Restrict scheme to http / https to prevent javascript: or file: attacks
        $scheme = parse_url($targetUrl, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            $targetUrl = 'https://www.digitalbuilders.in';
        }

        $email = CrmOutreachEmail::with(['lead', 'deal'])->where('tracking_token', $token)->first();

        if ($email) {
            $email->increment('click_count');
            $email->update([
                'clicked_at'       => $email->clicked_at ?: now(),
                'last_clicked_url' => substr($targetUrl, 0, 500),
                'status'           => 'clicked',
            ]);

            // Boost deal probability on engagement
            if ($email->deal && $email->deal->probability < 40) {
                $email->deal->update(['probability' => 35]);
            }

            try {
                Activity::create([
                    'lead_id'           => $email->lead_id,
                    'deal_id'           => $email->deal_id,
                    'type'              => 'email',
                    'subject'           => 'Prospect Clicked Link in Email',
                    'description'       => "Clicked target link: {$targetUrl}",
                    'touchpoint_number' => $email->touchpoint_number,
                    'metadata'          => [
                        'target_url' => $targetUrl,
                        'ip'         => $request->ip(),
                        'clicked_at' => now()->toIso8601String(),
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to log email click activity: ' . $e->getMessage());
            }

            $recipient = $email->recipient_name ? "{$email->recipient_name} ({$email->recipient_email})" : $email->recipient_email;
            $text = "⚡ *HIGH ENGAGEMENT: LINK CLICKED!*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "• *Prospect:* {$recipient}\n"
                . "• *Clicked:* `{$targetUrl}`\n"
                . "• *Subject:* `{$email->subject}`\n\n"
                . "👉 Follow up on WhatsApp or call now: " . url('/crm');

            $this->telegramBot->sendMessage($text);
        }

        return redirect()->away($targetUrl);
    }
}
