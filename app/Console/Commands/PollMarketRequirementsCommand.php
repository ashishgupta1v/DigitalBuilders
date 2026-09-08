<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MarketRequirement;
use App\Services\SalesFunnel\AiPitchGeneratorService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollMarketRequirementsCommand extends Command
{
    protected $signature = 'market:poll-requirements';
    protected $description = 'Poll marketplace feeds (Upwork RSS, Hacker News, Tech Job Boards) for new software requirements';

    public function handle(AiPitchGeneratorService $pitchGenerator, TelegramBotService $telegramBot): int
    {
        $this->info('Starting market requirement polling cycle...');

        // 1. Upwork RSS Public Feed Polling (Laravel / Vue / Full-Stack / Mobile App / AI MVP)
        $this->pollUpworkFeed($pitchGenerator, $telegramBot);

        // 2. Hacker News "Seeking Freelancer" Feed Polling
        $this->pollHnFeed($pitchGenerator, $telegramBot);

        $this->info('Market polling cycle completed successfully.');
        return Command::SUCCESS;
    }

    private function pollUpworkFeed(AiPitchGeneratorService $pitchGenerator, TelegramBotService $telegramBot): void
    {
        $feedUrl = env('UPWORK_RSS_FEED_URL');
        if (!$feedUrl) {
            $this->comment('UPWORK_RSS_FEED_URL not configured. Skipping live Upwork RSS fetch.');
            return;
        }

        try {
            $response = Http::timeout(12)->get($feedUrl);
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $link = (string) $item->link;
                        $title = (string) $item->title;
                        $description = strip_tags((string) $item->description);
                        $guid = (string) ($item->guid ?: md5($link));

                        if (MarketRequirement::where('source', 'upwork')->where('external_id', $guid)->exists()) {
                            continue;
                        }

                        $score = $pitchGenerator->scoreRelevance($description, 'USD', null, null);
                        if ($score < 60) continue; // Filter out low-relevance jobs

                        $pitchData = $pitchGenerator->generatePitch($description, null, null, 'USD');

                        $req = MarketRequirement::create([
                            'source'           => 'upwork',
                            'external_id'      => $guid,
                            'title'            => $title,
                            'raw_text'         => $description,
                            'budget_raw'       => '$3,000 – $8,000',
                            'estimated_amount' => 5000.00,
                            'currency'         => 'USD',
                            'location'         => 'Global (Upwork)',
                            'matched_segment'  => $pitchData['segment'],
                            'relevance_score'  => $score,
                            'pitch_draft'      => $pitchData['short_pitch'],
                            'status'           => 'qualified',
                            'metadata'         => ['url' => $link],
                        ]);

                        $telegramBot->sendOpportunityAlert($req, $pitchData);
                        $this->info("Ingested Upwork job: {$title} (Score: {$score})");
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Upwork RSS polling failed: ' . $e->getMessage());
        }
    }

    private function pollHnFeed(AiPitchGeneratorService $pitchGenerator, TelegramBotService $telegramBot): void
    {
        // Public HN Algolia API for tech founders hiring remote engineering teams
        try {
            $response = Http::timeout(10)->get('https://hn.algolia.com/api/v1/search_by_date', [
                'query' => 'Ask HN: Freelancer? Seeking Freelancer',
                'tags'  => 'ask_hn',
                'hitsPerPage' => 3,
            ]);

            if ($response->successful()) {
                $hits = $response->json('hits') ?? [];
                $this->comment('Checked HN Algolia feed. Active hits: ' . count($hits));
            }
        } catch (\Throwable $e) {
            Log::warning('HN Algolia polling failed: ' . $e->getMessage());
        }
    }
}
