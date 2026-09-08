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
            'indiamart' => '🇮🇳 IndiaMART Buy Lead',
            'upwork'    => '🌍 Upwork Verified Job',
            'job_board' => '💼 Tech Hiring Backlog',
            default     => '⚡ Inbound / Market Feed',
        };

        $budget = $req->formatted_amount;
        $name = htmlspecialchars((string) ($req->contact_name ?: 'Prospect'));
        $company = htmlspecialchars((string) ($req->contact_company ?: 'Direct Buyer'));
        $location = htmlspecialchars((string) ($req->location ?: 'India'));
        $caseStudy = htmlspecialchars((string) ($pitchData['case_study'] ?? 'DigitalBuilders'));

        $text = "🎯 *NEW HIGH-INTENT REQUIREMENT*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "• *Source:* {$sourceLabel}\n"
            . "• *Client:* {$name} ({$company})\n"
            . "• *Location:* {$location}\n"
            . "• *Budget Band:* `{$budget}`\n"
            . "• *Matched Proof:* `{$caseStudy}`\n\n"
            . "📋 *Raw Requirement:*\n"
            . "_{$req->raw_text}_\n\n"
            . "✍️ *Generated Pitch Preview:*\n"
            . "```\n" . substr($pitchData['short_pitch'], 0, 350) . "...\n```";

        $crmUrl = url('/crm');

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🚀 Approve & Send Pitch', 'callback_data' => "req:approve:{$req->id}"],
                    ['text' => '❌ Skip / Pass', 'callback_data' => "req:skip:{$req->id}"],
                ],
                [
                    ['text' => '📊 Open Executive CRM', 'url' => $crmUrl],
                ],
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
