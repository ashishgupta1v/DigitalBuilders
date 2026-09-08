<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SalesFunnel\InternationalLeadScraperService;
use Illuminate\Console\Command;

class PollMarketRequirementsCommand extends Command
{
    protected $signature = 'market:poll-requirements';
    protected $description = 'Poll international marketplace feeds (Hacker News, WeWorkRemotely, RemoteOK, Upwork, Reddit) for new software RFPs';

    public function handle(InternationalLeadScraperService $leadScraper): int
    {
        $this->info('Starting 24/7 International Lead Hunter polling cycle...');

        $stats = $leadScraper->pollAll();

        $this->table(
            ['Source', 'Ingested Qualified RFPs'],
            [
                ['Hacker News (Seeking Freelancer)', $stats['hn']],
                ['WeWorkRemotely (Full-Stack / Contracts)', $stats['weworkremotely']],
                ['RemoteOK (Funded Startups)', $stats['remoteok']],
                ['Upwork (Personal RSS Stream)', $stats['upwork']],
                ['Reddit (r/forhire & r/freelance_forhire)', $stats['reddit']],
                ['Total Newly Ingested', $stats['total']],
            ]
        );

        $this->info('Market polling cycle completed successfully.');
        return Command::SUCCESS;
    }
}
