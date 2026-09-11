<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CrmSequenceStep;
use App\Services\SalesFunnel\CrmSequenceEngineService;
use Illuminate\Console\Command;

class ProcessCrmSequencesCommand extends Command
{
    protected $signature = 'crm:process-sequences {--dry-run : Check due steps without sending}';

    protected $description = 'Scan and dispatch due outbound sequence emails for active CRM campaigns';

    public function handle(CrmSequenceEngineService $sequenceEngine): int
    {
        $this->info('⚡ Checking for due CRM outbound sequence steps...');

        if ($this->option('dry-run')) {
            $dueCount = CrmSequenceStep::where('status', 'scheduled')
                ->where('scheduled_at', '<=', now())
                ->whereHas('sequence', fn($q) => $q->where('status', 'active'))
                ->count();

            $this->info("🔍 [DRY-RUN] Found {$dueCount} due steps ready to dispatch.");
            return Command::SUCCESS;
        }

        $dispatched = $sequenceEngine->dispatchDueSteps();

        $this->info("✅ Successfully processed {$dispatched} due sequence steps.");

        return Command::SUCCESS;
    }
}
