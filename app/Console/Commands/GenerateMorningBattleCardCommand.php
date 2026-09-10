<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\MarketRequirement;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Console\Command;

class GenerateMorningBattleCardCommand extends Command
{
    protected $signature = 'crm:morning-battlecard';
    protected $description = 'Generate daily 08:00 AM Executive Battle Card digest and send to founder on Telegram';

    public function handle(TelegramBotService $telegramBot): int
    {
        $this->info('Generating Morning Battle Card digest...');

        $openDeals = Deal::whereNotIn('stage', ['closed_won', 'closed_lost']);
        $totalPipelineInr = (clone $openDeals)->where('currency', 'INR')->sum('amount');
        $totalPipelineUsd = (clone $openDeals)->where('currency', 'USD')->sum('amount');
        $dealCount = (clone $openDeals)->count();

        // Overdue Follow-ups
        $overdueLeads = Lead::whereNotNull('next_action_date')
            ->where('next_action_date', '<=', now()->endOfDay())
            ->whereNotIn('stage', ['closed_won', 'closed_lost', 'won', 'lost', 'converted'])
            ->whereNotIn('status', ['converted', 'archived'])
            ->orderBy('next_action_date', 'asc')
            ->limit(5)
            ->get();

        // Un-pitched qualified requirements
        $pendingReqs = MarketRequirement::where('status', 'qualified')
            ->orderBy('relevance_score', 'desc')
            ->limit(5)
            ->get();

        $text = "🌅 *DIGITALBUILDERS MORNING BATTLE CARD*\n"
            . "📅 " . now()->format('l, d M Y') . "\n"
            . "━━━━━━━━━━━━━━━━━━━━\n\n"
            . "📊 *Active Pipeline Telemetry:*\n"
            . "• Deals Active: *{$dealCount}*\n"
            . "• Total Pipeline: *₹" . number_format((float) $totalPipelineInr, 0) . "* + *$" . number_format((float) $totalPipelineUsd, 0) . "*\n\n";

        if ($pendingReqs->isNotEmpty()) {
            $text .= "🎯 *New Requirements Waiting for Approval (" . $pendingReqs->count() . "):*\n";
            foreach ($pendingReqs as $idx => $req) {
                $num = $idx + 1;
                $source = strtoupper($req->source);
                $title = substr($req->title ?: $req->raw_text, 0, 45);
                $text .= "{$num}. [{$source}] *{$title}* (`{$req->formatted_amount}`)\n";
            }
            $text .= "\n";
        }

        if ($overdueLeads->isNotEmpty()) {
            $text .= "⏳ *Today's Cadence Touchpoints (" . $overdueLeads->count() . "):*\n";
            foreach ($overdueLeads as $idx => $lead) {
                $num = $idx + 1;
                $touch = $lead->touchpoint_count + 1;
                $text .= "{$num}. *{$lead->name}* ({$lead->company}) — Touch {$touch}: {$lead->next_action_note}\n";
            }
            $text .= "\n";
        }

        $text .= "🚀 *Action:* Open CRM Cockpit to review and dispatch: " . url('/crm');

        $telegramBot->sendMessage($text);
        $this->info('Morning Battle Card dispatched.');

        return Command::SUCCESS;
    }
}
