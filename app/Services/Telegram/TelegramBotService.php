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
     * Dispatch an immediate high-priority alert for inbound website leads (Estimator, Contact Form).
     */
    public function sendInboundLeadAlert(
        string $name,
        string $email,
        string $phone,
        string $source,
        string $projectType,
        ?string $budget = null,
        ?string $timeline = null,
        array $features = [],
        ?string $notes = null
    ): bool {
        if (!$this->botToken || !$this->chatId) {
            Log::info("TelegramBotService: Lead alert logged locally (no token): {$name} - {$email}");
            return false;
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        $waPhone = ltrim($cleanPhone, '+');
        // If 10 digits starting with 6-9, assume India (+91)
        if (strlen($waPhone) === 10 && preg_match('/^[6-9]/', $waPhone)) {
            $waPhone = '91' . $waPhone;
        }

        $founderName = config('crm.founder_name', 'Founder');
        $founderTitle = config('crm.founder_title', 'Principal Architect');
        $companyName = config('crm.company_name', config('app.name', 'DigitalBuilders'));
        $waGreeting = "Hi " . strtok($name, ' ') . ", this is {$founderName}, {$founderTitle} at {$companyName}. I just reviewed your " . $projectType . " project inquiry on our portal. Are you available for a quick 5-min architecture sync today?";
        $waUrl = "https://wa.me/{$waPhone}?text=" . rawurlencode($waGreeting);

        $featureList = !empty($features) ? implode(', ', $features) : 'Custom Architecture';
        $budgetDisplay = $budget ?: 'Scope-derived';

        $text = "🚨 *NEW INBOUND CLIENT INQUIRY*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "• *Client:* `{$name}`\n"
            . "• *Phone:* `{$phone}`\n"
            . "• *Email:* `{$email}`\n"
            . "• *Source:* *{$source}*\n"
            . "• *Project Type:* {$projectType}\n"
            . "• *Estimated Budget:* `{$budgetDisplay}`\n"
            . ($timeline ? "• *Target Timeline:* {$timeline}\n" : "")
            . "• *Selected Features:* {$featureList}\n"
            . ($notes ? "• *Brief:* _{$notes}_\n" : "")
            . "\n⚡ *Speed-to-lead rule: Reach out within 5 minutes for 21x qualification rate.*";

        $crmUrl = url('/crm');
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💬 1-Tap WhatsApp Lead', 'url' => $waUrl],
                    ['text' => '📊 Open CRM Cockpit', 'url' => $crmUrl],
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
            Log::error("Failed to send Telegram inbound lead alert: " . $e->getMessage());
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
