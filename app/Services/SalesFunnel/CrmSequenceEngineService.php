<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

use App\Mail\CrmOutreachMail;
use App\Models\Activity;
use App\Models\CrmOutreachEmail;
use App\Models\CrmSequence;
use App\Models\CrmSequenceStep;
use App\Models\Lead;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CrmSequenceEngineService
{
    public function __construct(
        private AiPitchGeneratorService $pitchGenerator,
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * Retrieve existing sequence for lead or generate a new AI 4-step sequence.
     */
    public function getOrGenerateSequence(Lead $lead): CrmSequence
    {
        $existing = CrmSequence::where('lead_id', $lead->id)
            ->with(['steps' => fn($q) => $q->orderBy('step_number', 'asc')])
            ->latest()
            ->first();

        if ($existing && $existing->steps->isNotEmpty()) {
            return $existing;
        }

        // Generate 4-step cadence
        $cadence = $this->pitchGenerator->generateFullCadence($lead);
        $deal = $lead->deals()->first();

        $sequence = CrmSequence::create([
            'lead_id'      => $lead->id,
            'deal_id'      => $deal?->id,
            'name'         => '4-Step Cadence: ' . ($lead->company ?: $lead->name),
            'status'       => 'draft',
            'current_step' => 1,
            'total_steps'  => count($cadence),
        ]);

        foreach ($cadence as $stepData) {
            CrmSequenceStep::create([
                'sequence_id' => $sequence->id,
                'step_number' => (int) $stepData['step_number'],
                'delay_days'  => (int) $stepData['delay_days'],
                'step_type'   => 'email',
                'title'       => (string) ($stepData['title'] ?? "Touch {$stepData['step_number']}"),
                'subject'     => (string) $stepData['subject'],
                'body_text'   => (string) $stepData['body_text'],
                'status'      => 'pending',
            ]);
        }

        return $sequence->load(['steps' => fn($q) => $q->orderBy('step_number', 'asc')]);
    }

    /**
     * One-click approve and start sequence: dispatches Step 1 immediately and schedules Steps 2-4.
     */
    public function approveAndStartSequence(CrmSequence $sequence, ?array $customSteps = null): bool
    {
        $lead = $sequence->lead;
        if (!$lead || empty($lead->email)) {
            throw new \InvalidArgumentException('Lead must have a valid email address to start an outbound sequence.');
        }

        // Update custom step edits if provided from UI
        if (!empty($customSteps)) {
            foreach ($customSteps as $stepUpdate) {
                if (isset($stepUpdate['id'])) {
                    CrmSequenceStep::where('id', $stepUpdate['id'])
                        ->where('sequence_id', $sequence->id)
                        ->update([
                            'subject'    => $stepUpdate['subject'] ?? '',
                            'body_text'  => $stepUpdate['body_text'] ?? '',
                            'delay_days' => (int) ($stepUpdate['delay_days'] ?? 0),
                        ]);
                }
            }
        }

        $steps = $sequence->steps()->orderBy('step_number', 'asc')->get();
        if ($steps->isEmpty()) {
            return false;
        }

        $step1 = $steps->firstWhere('step_number', 1);
        if (!$step1) {
            return false;
        }

        // 1. Dispatch Step 1 immediately
        $trackingToken = bin2hex(random_bytes(16));
        $deal = $sequence->deal ?: $lead->deals()->first();

        $outreach = CrmOutreachEmail::create([
            'lead_id'           => $lead->id,
            'deal_id'           => $deal?->id,
            'tracking_token'    => $trackingToken,
            'recipient_email'   => $lead->email,
            'recipient_name'    => $lead->name,
            'subject'           => $step1->subject,
            'body_text'         => $step1->body_text,
            'body_html'         => $step1->body_text,
            'touchpoint_number' => 1,
            'sent_at'           => now(),
            'status'            => 'sent',
        ]);

        try {
            Mail::to($lead->email)->send(new CrmOutreachMail(
                outreachSubject: $step1->subject,
                bodyContent: $step1->body_text,
                trackingToken: $trackingToken,
                recipientName: $lead->name,
            ));
        } catch (\Throwable $e) {
            Log::error("Failed to send sequence step 1 to {$lead->email}: " . $e->getMessage());
        }

        $step1->update([
            'status'            => 'sent',
            'sent_at'           => now(),
            'outreach_email_id' => $outreach->id,
        ]);

        // 2. Schedule remaining steps (2, 3, 4)
        foreach ($steps as $step) {
            if ($step->step_number > 1) {
                $days = $step->delay_days ?: match ($step->step_number) {
                    2 => 3,
                    3 => 7,
                    4 => 11,
                    default => $step->step_number * 3,
                };

                $step->update([
                    'status'       => 'scheduled',
                    'scheduled_at' => now()->addDays($days),
                ]);
            }
        }

        // 3. Mark sequence active
        $sequence->update([
            'status'       => 'active',
            'current_step' => 2,
            'started_at'   => now(),
            'stopped_at'   => null,
            'stop_reason'  => null,
        ]);

        // 4. Update lead and deal stages
        $lead->increment('touchpoint_count');
        $lead->update([
            'stage'             => 'contacted',
            'last_contact_date' => now(),
            'next_action_date'  => now()->addDays(3),
            'next_action_note'  => 'Touch 2: Technical value drop scheduled',
        ]);

        if ($deal && in_array($deal->stage, ['new', 'inbound'], true)) {
            $deal->update([
                'stage'       => 'contacted',
                'probability' => 25,
            ]);
        }

        // 5. Activity log
        try {
            Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal?->id,
                'type'              => 'email',
                'subject'           => 'Outbound Cadence Launched (Touch #1 Sent)',
                'description'       => "Subject: \"{$step1->subject}\"\nRecipient: {$lead->email}\nFollow-up Steps 2–4 scheduled.",
                'touchpoint_number' => 1,
                'metadata'          => [
                    'sequence_id' => $sequence->id,
                    'outreach_id' => $outreach->id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create sequence launch activity: ' . $e->getMessage());
        }

        // 6. Telegram notification
        try {
            $company = $lead->company ? " [{$lead->company}]" : '';
            $msg = "🚀 *OUTBOUND CAMPAIGN LAUNCHED!*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "• *Prospect:* {$lead->name}{$company}\n"
                . "• *Email:* `{$lead->email}`\n"
                . "• *Step 1 Sent:* `{$step1->subject}`\n"
                . "• *Cadence:* 4 automated touchpoints over 11 days\n\n"
                . "👉 View Campaign in Cockpit: " . url('/crm?tab=campaigns');
            $this->telegramBot->sendMessage($msg);
        } catch (\Throwable $e) {
            Log::warning('Telegram sequence alert failed: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Dispatch due scheduled sequence steps (invoked every 10 min by scheduler).
     */
    public function dispatchDueSteps(): int
    {
        $dueSteps = CrmSequenceStep::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->with(['sequence.lead', 'sequence.deal'])
            ->get();

        $dispatched = 0;

        foreach ($dueSteps as $step) {
            $sequence = $step->sequence;
            if (!$sequence || $sequence->status !== 'active') {
                continue;
            }

            $lead = $sequence->lead;
            if (!$lead || empty($lead->email)) {
                $step->update(['status' => 'skipped']);
                continue;
            }

            // Halt if unsubscribed or already converted
            if ($lead->unsubscribed_at !== null) {
                $step->update(['status' => 'skipped']);
                $sequence->update([
                    'status'      => 'opted_out',
                    'stopped_at'  => now(),
                    'stop_reason' => 'Prospect unsubscribed',
                ]);
                continue;
            }

            if ($lead->stage === 'won' || ($sequence->deal && $sequence->deal->stage === 'won')) {
                $step->update(['status' => 'skipped']);
                $sequence->update([
                    'status'      => 'completed',
                    'stopped_at'  => now(),
                    'stop_reason' => 'Deal won',
                ]);
                continue;
            }

            $deal = $sequence->deal ?: $lead->deals()->first();
            $trackingToken = bin2hex(random_bytes(16));

            $outreach = CrmOutreachEmail::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal?->id,
                'tracking_token'    => $trackingToken,
                'recipient_email'   => $lead->email,
                'recipient_name'    => $lead->name,
                'subject'           => $step->subject,
                'body_text'         => $step->body_text,
                'body_html'         => $step->body_text,
                'touchpoint_number' => $step->step_number,
                'sent_at'           => now(),
                'status'            => 'sent',
            ]);

            try {
                Mail::to($lead->email)->send(new CrmOutreachMail(
                    outreachSubject: $step->subject,
                    bodyContent: $step->body_text,
                    trackingToken: $trackingToken,
                    recipientName: $lead->name,
                ));
            } catch (\Throwable $e) {
                Log::error("Failed to send scheduled step {$step->step_number} to {$lead->email}: " . $e->getMessage());
            }

            $step->update([
                'status'            => 'sent',
                'sent_at'           => now(),
                'outreach_email_id' => $outreach->id,
            ]);

            $isLastStep = ($step->step_number >= $sequence->total_steps);
            if ($isLastStep) {
                $sequence->update([
                    'status'       => 'completed',
                    'current_step' => $step->step_number,
                    'stopped_at'   => now(),
                    'stop_reason'  => 'All 4 sequence steps completed',
                ]);
            } else {
                $sequence->update([
                    'current_step' => $step->step_number + 1,
                ]);
            }

            $lead->increment('touchpoint_count');
            $lead->update([
                'last_contact_date' => now(),
                'next_action_date'  => $isLastStep ? null : now()->addDays(4),
                'next_action_note'  => $isLastStep ? 'Sequence finished' : "Touch " . ($step->step_number + 1) . " scheduled",
            ]);

            try {
                Activity::create([
                    'lead_id'           => $lead->id,
                    'deal_id'           => $deal?->id,
                    'type'              => 'email',
                    'subject'           => "Outbound Step Dispatched (Touch #{$step->step_number})",
                    'description'       => "Subject: \"{$step->subject}\"\nRecipient: {$lead->email}",
                    'touchpoint_number' => $step->step_number,
                    'metadata'          => [
                        'sequence_id' => $sequence->id,
                        'outreach_id' => $outreach->id,
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to log step dispatch activity: ' . $e->getMessage());
            }

            $dispatched++;
        }

        return $dispatched;
    }

    /**
     * Mark a lead as replied, automatically halting their active sequence.
     */
    public function markAsReplied(Lead $lead, string $note = ''): void
    {
        $sequences = CrmSequence::where('lead_id', $lead->id)
            ->whereIn('status', ['active', 'paused', 'draft'])
            ->get();

        foreach ($sequences as $seq) {
            $seq->update([
                'status'      => 'replied',
                'stopped_at'  => now(),
                'stop_reason' => $note ?: 'Prospect responded to outreach',
            ]);

            $seq->steps()->where('status', 'scheduled')->update([
                'status' => 'skipped',
            ]);
        }

        $lead->update([
            'stage'             => 'contacted',
            'last_contact_date' => now(),
            'next_action_note'  => 'Replied: ' . ($note ?: 'In conversation'),
        ]);

        $deal = $lead->deals()->first();
        if ($deal && $deal->probability < 40) {
            $deal->update([
                'stage'       => 'contacted',
                'probability' => 40,
            ]);
        }

        try {
            Activity::create([
                'lead_id'     => $lead->id,
                'deal_id'     => $deal?->id,
                'type'        => 'meeting',
                'subject'     => 'Prospect Replied — Sequence Stopped',
                'description' => "Sequence halted automatically. Note: " . ($note ?: 'Prospect replied to email outreach.'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log replied activity: ' . $e->getMessage());
        }

        try {
            $company = $lead->company ? " [{$lead->company}]" : '';
            $text = "🎉 *PROSPECT REPLIED!*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "• *Prospect:* {$lead->name}{$company}\n"
                . "• *Email:* `{$lead->email}`\n"
                . "• *Action:* Active sequence paused/stopped automatically.\n\n"
                . "👉 Follow up in Cockpit: " . url('/crm');
            $this->telegramBot->sendMessage($text);
        } catch (\Throwable $e) {
            Log::warning('Telegram reply alert failed: ' . $e->getMessage());
        }
    }

    /**
     * Unsubscribe a lead via public token link.
     */
    public function unsubscribe(string $token): bool
    {
        $email = CrmOutreachEmail::where('tracking_token', $token)->first();
        $lead = $email?->lead;

        if (!$lead) {
            return false;
        }

        $lead->update([
            'unsubscribed_at' => now(),
        ]);

        CrmSequence::where('lead_id', $lead->id)
            ->whereIn('status', ['active', 'paused', 'draft'])
            ->update([
                'status'      => 'opted_out',
                'stopped_at'  => now(),
                'stop_reason' => 'Unsubscribed via email link',
            ]);

        CrmSequenceStep::whereHas('sequence', fn($q) => $q->where('lead_id', $lead->id))
            ->where('status', 'scheduled')
            ->update(['status' => 'skipped']);

        try {
            Activity::create([
                'lead_id'     => $lead->id,
                'type'        => 'email',
                'subject'     => 'Lead Unsubscribed from Outbound',
                'description' => 'Prospect clicked unsubscribe link. All upcoming follow-ups cancelled.',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log unsubscribe activity: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Aggregated metrics and queue for the Campaigns tab.
     */
    public function getCampaignStats(): array
    {
        $totalSequences = CrmSequence::count();
        $activeSequences = CrmSequence::where('status', 'active')->count();
        $repliedSequences = CrmSequence::where('status', 'replied')->count();

        $totalEmailsSent = CrmOutreachEmail::count();
        $openedEmails = CrmOutreachEmail::whereNotNull('opened_at')->orWhere('open_count', '>', 0)->count();
        $clickedEmails = CrmOutreachEmail::whereNotNull('clicked_at')->orWhere('click_count', '>', 0)->count();

        $openRate = $totalEmailsSent > 0 ? round(($openedEmails / $totalEmailsSent) * 100, 1) : 0.0;
        $clickRate = $totalEmailsSent > 0 ? round(($clickedEmails / $totalEmailsSent) * 100, 1) : 0.0;
        $replyRate = $totalSequences > 0 ? round(($repliedSequences / $totalSequences) * 100, 1) : 0.0;

        // Next 12 scheduled touches in the queue
        $upcomingQueue = CrmSequenceStep::where('status', 'scheduled')
            ->whereHas('sequence', fn($q) => $q->where('status', 'active'))
            ->with(['sequence.lead'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(12)
            ->get()
            ->map(function ($step) {
                return [
                    'id'           => $step->id,
                    'sequence_id'  => $step->sequence_id,
                    'lead_id'      => $step->sequence?->lead_id,
                    'lead_name'    => $step->sequence?->lead?->name ?? 'Prospect',
                    'company'      => $step->sequence?->lead?->company ?? 'Company',
                    'email'        => $step->sequence?->lead?->email ?? '',
                    'step_number'  => $step->step_number,
                    'title'        => $step->title,
                    'subject'      => $step->subject,
                    'scheduled_at' => $step->scheduled_at?->toIso8601String(),
                    'scheduled_for_human' => $step->scheduled_at?->diffForHumans() ?? 'Soon',
                ];
            })
            ->all();

        return [
            'total_sequences'   => $totalSequences,
            'active_sequences'  => $activeSequences,
            'replied_sequences' => $repliedSequences,
            'total_emails_sent' => $totalEmailsSent,
            'open_rate'         => $openRate,
            'click_rate'        => $clickRate,
            'reply_rate'        => $replyRate,
            'upcoming_queue'    => $upcomingQueue,
        ];
    }
}
