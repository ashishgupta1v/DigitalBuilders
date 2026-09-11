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
                ['Hacker News (Seeking Freelancer)', $stats['hn'] ?? 0],
                ['Product Hunt (Top Launch MVPs)', $stats['producthunt'] ?? 0],
                ['Jobicy (Remote Tech Contracts)', $stats['jobicy'] ?? 0],
                ['Arbeitnow (European & Global Tech)', $stats['arbeitnow'] ?? 0],
                ['WeWorkRemotely (Full-Stack / Contracts)', $stats['weworkremotely'] ?? 0],
                ['RemoteOK (Funded Startups)', $stats['remoteok'] ?? 0],
                ['Remotive (Global Software RFPs)', $stats['remotive'] ?? 0],
                ['Himalayas (International Dev Contracts)', $stats['himalayas'] ?? 0],
                ['Upwork (Personal RSS Stream)', $stats['upwork'] ?? 0],
                ['Reddit (r/forhire & r/freelance_forhire)', $stats['reddit'] ?? 0],
                ['Total Newly Ingested', $stats['total'] ?? 0],
            ]
        );

        $this->info('Market polling cycle completed successfully.');
        return Command::SUCCESS;
    }
}
