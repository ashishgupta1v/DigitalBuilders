<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Models\MarketRequirement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    private ?string $botToken;
    private ?string $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN');
        $this->chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');
    }

    /**
     * Dispatch an interactive approval card for a market requirement.
     */
    public function sendOpportunityAlert(MarketRequirement $req, array $pitchData): bool
    {
        if (!$this->botToken || !$this->chatId) {
            Log::info("TelegramBotService: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID not configured. Alert logged locally for req #{$req->id}");
            return false;
        }

        $sourceLabel = match ($req->source) {
            'indiamart'      => '🇮🇳 IndiaMART Buy Lead',
            'upwork'         => '🟢 Upwork Verified RFP',
            'hackernews'     => '🟠 Hacker News (Founder RFP)',
            'weworkremotely' => '💼 WeWorkRemotely (Contract)',
            'remoteok'       => '🚀 RemoteOK (Funded Startup)',
            'remotive'       => '🌐 Remotive (Software RFP)',
            'himalayas'      => '🏔️ Himalayas (Tech Contract)',
            'reddit'         => '🔴 Reddit (r/forhire)',
            'job_board'      => '💼 Tech Hiring Backlog',
            default          => '⚡ Inbound / Market Feed',
        };

        $budget = $req->formatted_amount;
        $name = htmlspecialchars((string) ($req->contact_name ?: 'Prospect'));
        $company = htmlspecialchars((string) ($req->contact_company ?: 'Direct Buyer'));
        $location = htmlspecialchars((string) ($req->location ?: 'Global'));
        $caseStudy = htmlspecialchars((string) ($pitchData['case_study'] ?? 'DigitalBuilders'));

        $proposalPreview = $pitchData['upwork_proposal'] ?? $pitchData['reddit_dm'] ?? $pitchData['short_pitch'];

        $text = "🎯 *NEW HIGH-INTENT INTERNATIONAL RFP*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "• *Source:* {$sourceLabel}\n"
            . "• *Client:* {$name}" . ($company !== 'Direct Buyer' ? " ({$company})" : "") . "\n"
            . "• *Location:* {$location}\n"
            . "• *Budget Band:* `{$budget}`\n"
            . "• *Matched Proof:* `{$caseStudy}`\n\n"
            . "📋 *Raw Requirement:*\n"
            . "_{$req->raw_text}_\n\n"
            . "✍️ *One-Click Copyable Pitch:*\n"
            . "```\n" . substr($proposalPreview, 0, 500) . "\n```";

        $crmUrl = url('/crm');
        $originUrl = $req->metadata['url'] ?? $req->metadata['hn_url'] ?? null;

        $actionRow = [
            ['text' => '🚀 Approve & Mark Pitched', 'callback_data' => "req:approve:{$req->id}"],
            ['text' => '❌ Skip', 'callback_data' => "req:skip:{$req->id}"],
        ];

        $linksRow = [];
        if ($originUrl) {
            $linksRow[] = ['text' => '🔗 Open Original Post', 'url' => $originUrl];
        }
        $linksRow[] = ['text' => '📊 Open CRM Cockpit', 'url' => $crmUrl];

        $keyboard = [
            'inline_keyboard' => [
                $actionRow,
                $linksRow,
            ],
        ];

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id'                  => $this->chatId,
                'text'                     => $text,
                'parse_mode'               => 'Markdown',
                'disable_web_page_preview' => true,
                'reply_markup'             => json_encode($keyboard),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Failed to send Telegram opportunity alert: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a general text notification or morning summary.
     */
    public function sendMessage(string $text): bool
    {
        if (!$this->botToken || !$this->chatId) {
            Log::info("TelegramBotService: Message logged (no token): {$text}");
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id'                  => $this->chatId,
                'text'                     => $text,
                'parse_mode'               => 'Markdown',
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("TelegramBotService sendMessage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Answer a callback query (e.g. button click toast).
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text): void
    {
        if (!$this->botToken) return;

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text'              => $text,
                'show_alert'        => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Telegram answerCallbackQuery failed: " . $e->getMessage());
        }
    }
}
