<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class ProcessDailyCadenceCommand extends Command
{
    protected $signature = 'crm:process-cadence';
    protected $description = 'Process 5-touch sales cadences and prepare daily outreach queues';

    public function handle(): int
    {
        $this->info('Processing 5-touch sales cadence...');

        $leadsNeedingAction = Lead::whereNotNull('next_action_date')
            ->where('next_action_date', '<=', now())
            ->whereNotIn('stage', ['won', 'lost', 'converted'])
            ->get();

        $count = 0;
        foreach ($leadsNeedingAction as $lead) {
            $touch = (int) $lead->touchpoint_count;
            if ($touch === 0) {
                $lead->update([
                    'next_action_note' => 'Touch 1: Send Value Proposition on WhatsApp/Email',
                ]);
            } elseif ($touch === 1) {
                $lead->update([
                    'next_action_note' => 'Touch 2: Share Case Study & Architecture Diagram',
                ]);
            } elseif ($touch === 2) {
                $lead->update([
                    'next_action_note' => 'Touch 3: Proposal Review & Timeline Check',
                ]);
            } elseif ($touch === 3) {
                $lead->update([
                    'next_action_note' => 'Touch 4: Executive Follow-up / Clarify Scope',
                ]);
            } elseif ($touch >= 4) {
                $lead->update([
                    'next_action_note' => 'Touch 5: Final Breakup / Polite Archive Check',
                ]);
            }
            $count++;
        }

        $this->info("Processed {$count} leads in daily cadence.");
        return Command::SUCCESS;
    }
}
