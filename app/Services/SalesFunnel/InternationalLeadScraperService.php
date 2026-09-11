<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

use App\Models\MarketRequirement;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InternationalLeadScraperService
{
    private const NEGATIVE_KEYWORDS = [
        'logo design',
        'graphic design',
        'video editor',
        'video editing',
        'voiceover',
        'voice over',
        'tutor',
        'homework',
        'assignment',
        'virtual assistant',
        'data entry',
        'wordpress fix',
        'css tweak',
        'cheap',
        '$10',
        '$20',
        '$50',
        'junior',
        'júnior',
        'intern',
        'internship',
        'trainee',
        'entry level',
        'vaga afirmativa',
        'aprendiz',
        '.net',
        'c#',
        'c++',
        'angular',
        'cobol',
        'salesforce admin',
        'sap consultant',
        'equity only',
        'unpaid',
        'cofounder no salary',
        'desenvolvedor',
        'desenvolvedora',
        'estágio',
        'mule esb',
        'network engineer',
        'solutions consultant',
        'w2 only',
        '401k',
        'healthcare benefits',
        'relocation assistance',
    ];

    public function __construct(
        private AiPitchGeneratorService $pitchGenerator,
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * Run all international scrapers and return summary count of ingested leads.
     */
    public function pollAll(): array
    {
        $stats = [
            'hn'             => $this->pollHackerNews(),
            'upwork'         => $this->pollUpwork(),
            'github'         => $this->pollGitHubDiscussions(),
            'jobicy'         => $this->pollJobicy(),
            'arbeitnow'      => $this->pollArbeitnow(),
            'producthunt'    => $this->pollProductHuntLaunches(),
            'indiehackers'   => $this->pollIndieHackers(),
            'wellfound'      => $this->pollWellfound(),
            'substack'       => $this->pollSubstackBuildInPublic(),
            'weworkremotely' => $this->pollWeWorkRemotely(),
            'remoteok'       => $this->pollRemoteOk(),
            'remotive'       => $this->pollRemotive(),
            'himalayas'      => $this->pollHimalayas(),
            'reddit'         => $this->pollReddit(),
            'reddit_startup' => $this->pollRedditStartupSignals(),
        ];

        $stats['total'] = array_sum($stats);
        return $stats;
    }

    /**
     * Poll Hacker News Algolia Search API for live "SEEKING FREELANCER" and direct founder posts,
     * including targeting the official monthly "Ask HN: Freelancer? Seeking Freelancer?" megathread.
     */
    public function pollHackerNews(): int
    {
        $ingested = 0;

        try {
            $url = 'https://hn.algolia.com/api/v1/search_by_date?' . http_build_query([
                'tags'        => 'comment',
                'query'       => 'SEEKING FREELANCER OR "hire developer" OR "contract developer" OR "freelance project"',
                'hitsPerPage' => 20,
            ]);

            $response = Http::withoutVerifying()->timeout(10)->get($url);
            $hits = $response->successful() ? ($response->json('hits') ?? []) : [];

            // Also check current month's official "Ask HN: Freelancer? Seeking Freelancer?" thread
            try {
                $monthlyUrl = 'https://hn.algolia.com/api/v1/search?' . http_build_query([
                    'tags'        => 'story',
                    'query'       => 'Ask HN: Freelancer? Seeking Freelancer?',
                    'hitsPerPage' => 1,
                ]);
                $storyResp = Http::withoutVerifying()->timeout(8)->get($monthlyUrl);
                if ($storyResp->successful()) {
                    $storyId = $storyResp->json('hits.0.objectID');
                    if ($storyId) {
                        $commentsUrl = 'https://hn.algolia.com/api/v1/search_by_date?' . http_build_query([
                            'tags'        => "comment,story_{$storyId}",
                            'query'       => 'SEEKING FREELANCER',
                            'hitsPerPage' => 20,
                        ]);
                        $cResp = Http::withoutVerifying()->timeout(8)->get($commentsUrl);
                        if ($cResp->successful() && !empty($cResp->json('hits'))) {
                            $hits = array_merge($hits, $cResp->json('hits'));
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::info('HN monthly thread fetch skipped: ' . $e->getMessage());
            }

            foreach ($hits as $hit) {
                $commentId = (string) ($hit['objectID'] ?? '');
                $rawText = (string) ($hit['comment_text'] ?? '');
                $cleanText = trim(html_entity_decode(strip_tags($rawText), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $author = (string) ($hit['author'] ?? 'HN Founder');
                $storyTitle = trim(html_entity_decode((string) ($hit['story_title'] ?? 'Hacker News'), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                if (!$commentId || strlen($cleanText) < 60) {
                    continue;
                }

                if (MarketRequirement::where('source', 'hackernews')->where('external_id', $commentId)->exists()) {
                    continue;
                }

                if (!$this->passesTier1Filters($cleanText)) {
                    continue;
                }

                // Extract company or email if present
                preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $cleanText, $emailMatches);
                $extractedEmail = $emailMatches[0] ?? null;

                $budget = $this->extractBudget($cleanText, '$4,000 – $8,000');
                $score = $this->pitchGenerator->scoreRelevance($cleanText, $budget['raw'], null, $extractedEmail);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($cleanText, $author, null, 'hackernews', 'USD', $budget['raw']);

                $req = MarketRequirement::create([
                    'source'           => 'hackernews',
                    'external_id'      => $commentId,
                    'title'            => substr("HN: {$storyTitle} (by {$author})", 0, 190),
                    'raw_text'         => $cleanText,
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                    'currency'         => 'USD',
                    'contact_name'     => $author,
                    'contact_email'    => $extractedEmail,
                    'location'         => 'Remote (US/Global)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'hn_url'                  => "https://news.ycombinator.com/item?id={$commentId}",
                        'author'                  => $author,
                        'story_id'                => $hit['story_id'] ?? null,
                        'email_pitch'             => $pitchData['email_pitch'] ?? null,
                        'email_subject'           => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                        'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Hacker News polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Upwork RSS feed or public search feeds.
     */
    public function pollUpwork(): int
    {
        $feedUrl = config('services.crm.upwork_feed_url') ?? env('UPWORK_RSS_FEED_URL');
        // If not configured, use curated public query for Vue, React, Laravel, MVP contract search
        if (!$feedUrl) {
            $feedUrl = 'https://www.upwork.com/ab/feed/jobs/rss?q=full+stack+OR+laravel+OR+vue+OR+react+OR+mvp+OR+saas&sort=recency';
        }

        $ingested = 0;

        try {
            $response = Http::timeout(12)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'     => 'application/rss+xml, application/xml, text/xml',
            ])->get($feedUrl);

            if (!$response->successful()) {
                return 0;
            }

            $xml = @simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) {
                return 0;
            }

            foreach ($xml->channel->item as $item) {
                $link = (string) $item->link;
                $title = trim(html_entity_decode((string) $item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $description = trim(html_entity_decode(strip_tags((string) $item->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $guid = (string) ($item->guid ?: md5($link));

                if (MarketRequirement::where('source', 'upwork')->where('external_id', $guid)->exists()) {
                    continue;
                }

                $fullText = "{$title}\n\n{$description}";
                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $budget = $this->extractBudget($fullText, '$3,500 – $8,000');
                $score = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, null, 'upwork', 'USD', $budget['raw']);

                $req = MarketRequirement::create([
                    'source'           => 'upwork',
                    'external_id'      => $guid,
                    'title'            => substr("Upwork: {$title}", 0, 190),
                    'raw_text'         => substr($description, 0, 3000),
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                    'currency'         => 'USD',
                    'location'         => 'Remote (Upwork Global)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                     => $link,
                        'email_pitch'             => $pitchData['email_pitch'] ?? null,
                        'email_subject'           => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                        'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Upwork polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll WeWorkRemotely RSS feeds strictly for contract/freelance scopes.
     */
    public function pollWeWorkRemotely(): int
    {
        $ingested = 0;
        $feeds = [
            'https://weworkremotely.com/categories/remote-full-stack-programming-jobs.rss',
            'https://weworkremotely.com/categories/remote-back-end-programming-jobs.rss',
        ];

        foreach ($feeds as $feedUrl) {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DigitalBuilders/1.0',
                ])->get($feedUrl);

                if (!$response->successful()) {
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->channel->item)) {
                    continue;
                }

                foreach ($xml->channel->item as $item) {
                    $link = (string) $item->link;
                    $title = trim(html_entity_decode((string) $item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $description = trim(html_entity_decode(strip_tags((string) $item->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $guid = (string) ($item->guid ?: md5($link));

                    if (MarketRequirement::where('source', 'weworkremotely')->where('external_id', $guid)->exists()) {
                        continue;
                    }

                    // Strict contract requirement: Skip permanent full-time employment
                    $isContract = preg_match('/\b(contract|contractor|freelance|part-time|consultant|project|mvp)\b/i', $title . ' ' . $description);
                    if (!$isContract) {
                        continue;
                    }

                    if (!$this->passesTier1Filters($title . ' ' . $description)) {
                        continue;
                    }

                    $budget = $this->extractBudget($description, '$5,000 – $10,000');
                    $score = $this->pitchGenerator->scoreRelevance($description, $budget['raw'], null, null);
                    if ($score < 50) {
                        continue;
                    }

                    $company = null;
                    if (str_contains($title, ':')) {
                        [$company, ] = explode(':', $title, 2);
                        $company = trim($company);
                    }

                    $pitchData = $this->pitchGenerator->generateAiPitch($description, null, $company, 'weworkremotely', 'USD', $budget['raw']);

                    $req = MarketRequirement::create([
                        'source'           => 'weworkremotely',
                        'external_id'      => $guid,
                        'title'            => substr("WWR Contract: {$title}", 0, 190),
                        'raw_text'         => substr($description, 0, 3000),
                        'budget_raw'       => $budget['raw'],
                        'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                        'currency'         => 'USD',
                        'contact_company'  => $company,
                        'location'         => 'Remote (US/EU/Global)',
                        'matched_segment'  => $pitchData['segment'],
                        'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                        'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                        'status'           => 'qualified',
                        'metadata'         => [
                            'url'                     => $link,
                            'company'                 => $company,
                            'email_pitch'             => $pitchData['email_pitch'] ?? null,
                            'email_subject'           => $pitchData['email_subject'] ?? null,
                            'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                            'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                            'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                        ],
                    ]);

                    $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                    $ingested++;
                }
            } catch (\Throwable $e) {
                Log::warning('WeWorkRemotely polling exception: ' . $e->getMessage());
            }
        }

        return $ingested;
    }

    /**
     * Poll RemoteOK API strictly for contract / freelance roles.
     */
    public function pollRemoteOk(): int
    {
        $ingested = 0;

        try {
            $response = Http::timeout(12)->withHeaders([
                'User-Agent' => 'DigitalBuilders/1.0 (LeadHunter; founder@digitalbuilders.in)',
            ])->get('https://remoteok.com/api');

            if (!$response->successful()) {
                return 0;
            }

            $jobs = $response->json() ?? [];
            if (!is_array($jobs)) {
                return 0;
            }

            $items = array_slice($jobs, 1, 30);

            foreach ($items as $job) {
                $id = (string) ($job['id'] ?? '');
                $position = trim(html_entity_decode((string) ($job['position'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $company = trim(html_entity_decode((string) ($job['company'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $description = trim(html_entity_decode(strip_tags((string) ($job['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $url = (string) ($job['url'] ?? '');
                $tags = implode(', ', (array) ($job['tags'] ?? []));
                $location = (string) ($job['location'] ?? 'Remote (Global)');

                if (!$id || !$position || MarketRequirement::where('source', 'remoteok')->where('external_id', $id)->exists()) {
                    continue;
                }

                $fullText = "{$position} at {$company}. Tags: {$tags}. {$description}";

                // Strict contract / freelance requirement
                if (!preg_match('/\b(contract|contractor|freelance|consultant|project|mvp|part-time)\b/i', $fullText)) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $salaryMin = (float) ($job['salary_min'] ?? 0);
                $salaryMax = (float) ($job['salary_max'] ?? 0);
                $budgetRaw = '$5,000 – $10,000';
                $amount = 6500.00;

                if ($salaryMin > 0 && $salaryMax > 0) {
                    $budgetRaw = '$' . number_format($salaryMin) . ' – $' . number_format($salaryMax);
                    $amount = round(($salaryMin + $salaryMax) / 2);
                }

                $score = $this->pitchGenerator->scoreRelevance($fullText, $budgetRaw, null, null);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, $company, 'remoteok', 'USD', $budgetRaw);

                $req = MarketRequirement::create([
                    'source'           => 'remoteok',
                    'external_id'      => $id,
                    'title'            => substr("RemoteOK Contract: {$position} — {$company}", 0, 190),
                    'raw_text'         => substr($description, 0, 3000),
                    'budget_raw'       => $budgetRaw,
                    'estimated_amount' => min($amount, 15000.00),
                    'currency'         => 'USD',
                    'contact_company'  => $company,
                    'location'         => $location,
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                     => $url,
                        'company'                 => $company,
                        'tags'                    => $job['tags'] ?? [],
                        'email_pitch'             => $pitchData['email_pitch'] ?? null,
                        'email_subject'           => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                        'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('RemoteOK polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Remotive API strictly for contract / freelance roles.
     */
    public function pollRemotive(): int
    {
        $ingested = 0;

        try {
            $response = Http::timeout(12)->withHeaders([
                'User-Agent' => 'DigitalBuilders/1.0 (LeadHunter; founder@digitalbuilders.in)',
            ])->get('https://remotive.com/api/remote-jobs', [
                'category' => 'software-dev',
                'limit'    => 25,
            ]);

            if (!$response->successful()) {
                return 0;
            }

            $jobs = $response->json('jobs') ?? [];
            if (!is_array($jobs)) {
                return 0;
            }

            foreach ($jobs as $job) {
                $id = (string) ($job['id'] ?? '');
                $title = trim(html_entity_decode((string) ($job['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $company = trim(html_entity_decode((string) ($job['company_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $description = trim(html_entity_decode(strip_tags((string) ($job['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $url = (string) ($job['url'] ?? '');
                $salary = (string) ($job['salary'] ?? '');
                $jobType = strtolower((string) ($job['job_type'] ?? ''));
                $location = (string) ($job['candidate_required_location'] ?? 'Remote (Global)');

                if (!$id || !$title || MarketRequirement::where('source', 'remotive')->where('external_id', $id)->exists()) {
                    continue;
                }

                $fullText = "{$title} at {$company}. {$description}";

                // Strict contract / freelance requirement
                $isContract = ($jobType === 'contract' || $jobType === 'freelance' || preg_match('/\b(contract|contractor|freelance|consultant|project|mvp)\b/i', $fullText));
                if (!$isContract) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $budget = $this->extractBudget($salary . ' ' . $description, '$5,000 – $12,000');
                $score = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, $company, 'remotive', 'USD', $budget['raw']);

                $req = MarketRequirement::create([
                    'source'           => 'remotive',
                    'external_id'      => $id,
                    'title'            => substr("Remotive Contract: {$title} — {$company}", 0, 190),
                    'raw_text'         => substr($description, 0, 3000),
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => min($budget['amount'], 18000.00),
                    'currency'         => 'USD',
                    'contact_company'  => $company,
                    'location'         => $location,
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                     => $url,
                        'company'                 => $company,
                        'email_pitch'             => $pitchData['email_pitch'] ?? null,
                        'email_subject'           => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                        'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Remotive polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Himalayas public API strictly for contract / freelance developer roles.
     */
    public function pollHimalayas(): int
    {
        $ingested = 0;

        try {
            $response = Http::timeout(12)->withHeaders([
                'User-Agent' => 'DigitalBuilders/1.0 (LeadHunter; founder@digitalbuilders.in)',
            ])->get('https://himalayas.app/jobs/api', [
                'limit' => 25,
            ]);

            if (!$response->successful()) {
                return 0;
            }

            $jobs = $response->json('jobs') ?? [];
            if (!is_array($jobs)) {
                return 0;
            }

            foreach ($jobs as $job) {
                $guid = (string) ($job['guid'] ?? $job['applicationLink'] ?? '');
                $cleanTitle = trim(html_entity_decode((string) ($job['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cleanCompany = trim(html_entity_decode((string) ($job['companyName'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cleanDescription = trim(html_entity_decode(strip_tags((string) ($job['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $url = (string) ($job['applicationLink'] ?? '');

                if (!$guid || !$cleanTitle || MarketRequirement::where('source', 'himalayas')->where('external_id', $guid)->exists()) {
                    continue;
                }

                $fullText = "{$cleanTitle} at {$cleanCompany}. {$cleanDescription}";

                // Strict contract filter
                if (!preg_match('/\b(contract|contractor|freelance|consultant|project|mvp|part-time)\b/i', $fullText)) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $minSalary = (float) ($job['minSalary'] ?? 0);
                $maxSalary = (float) ($job['maxSalary'] ?? 0);
                $budgetRaw = '$6,000 – $14,000';
                $amount = 8000.00;

                if ($minSalary > 0 && $maxSalary > 0) {
                    $budgetRaw = '$' . number_format($minSalary) . ' – $' . number_format($maxSalary);
                    $amount = round(($minSalary + $maxSalary) / 2);
                }

                $score = $this->pitchGenerator->scoreRelevance($fullText, $budgetRaw, null, null);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, $cleanCompany, 'himalayas', 'USD', $budgetRaw);

                $req = MarketRequirement::create([
                    'source'           => 'himalayas',
                    'external_id'      => substr($guid, 0, 190),
                    'title'            => substr("Himalayas: {$cleanTitle} — {$cleanCompany}", 0, 190),
                    'raw_text'         => substr($cleanDescription, 0, 3000),
                    'budget_raw'       => $budgetRaw,
                    'estimated_amount' => min($amount, 20000.00),
                    'currency'         => 'USD',
                    'contact_company'  => $cleanCompany,
                    'location'         => 'Remote (Global)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                     => $url,
                        'company'                 => $cleanCompany,
                        'email_pitch'             => $pitchData['email_pitch'] ?? null,
                        'email_subject'           => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                        'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Himalayas polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Reddit r/forhire and r/freelance_forhire if credentials configured.
     */
    public function pollReddit(): int
    {
        $clientId = config('services.reddit.client_id') ?? env('REDDIT_CLIENT_ID');
        $clientSecret = config('services.reddit.client_secret') ?? env('REDDIT_CLIENT_SECRET');

        if (!$clientId || !$clientSecret) {
            return 0;
        }

        $ingested = 0;

        try {
            $authResponse = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->withHeaders(['User-Agent' => 'DigitalBuildersBot/1.0'])
                ->post('https://www.reddit.com/api/v1/access_token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$authResponse->successful()) {
                return 0;
            }

            $token = $authResponse->json('access_token');
            if (!$token) {
                return 0;
            }

            $subreddits = ['forhire', 'freelance_forhire'];
            foreach ($subreddits as $sub) {
                $postsResponse = Http::withToken($token)
                    ->withHeaders(['User-Agent' => 'DigitalBuildersBot/1.0'])
                    ->get("https://oauth.reddit.com/r/{$sub}/new", ['limit' => 15]);

                if (!$postsResponse->successful()) {
                    continue;
                }

                $children = $postsResponse->json('data.children') ?? [];
                foreach ($children as $child) {
                    $post = $child['data'] ?? [];
                    $id = (string) ($post['id'] ?? '');
                    $title = trim(html_entity_decode((string) ($post['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $selftext = trim(html_entity_decode((string) ($post['selftext'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $author = (string) ($post['author'] ?? 'Redditor');
                    $permalink = 'https://reddit.com' . ($post['permalink'] ?? '');

                    if (!preg_match('/\[hiring\]/i', $title) && !preg_match('/hiring/i', (string) ($post['link_flair_text'] ?? ''))) {
                        continue;
                    }

                    if (MarketRequirement::where('source', 'reddit')->where('external_id', $id)->exists()) {
                        continue;
                    }

                    $fullText = "{$title}\n\n{$selftext}";
                    if (!$this->passesTier1Filters($fullText)) {
                        continue;
                    }

                    $budget = $this->extractBudget($fullText, '$3,000 – $7,000');
                    $score = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                    if ($score < 50) {
                        continue;
                    }

                    $pitchData = $this->pitchGenerator->generateAiPitch($fullText, $author, null, 'reddit', 'USD', $budget['raw']);

                    $req = MarketRequirement::create([
                        'source'           => 'reddit',
                        'external_id'      => $id,
                        'title'            => substr("Reddit r/{$sub}: {$title}", 0, 190),
                        'raw_text'         => $fullText,
                        'budget_raw'       => $budget['raw'],
                        'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                        'currency'         => 'USD',
                        'contact_name'     => $author,
                        'location'         => "Reddit (r/{$sub})",
                        'matched_segment'  => $pitchData['segment'],
                        'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                        'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                        'status'           => 'qualified',
                        'metadata'         => [
                            'url'                     => $permalink,
                            'subreddit'               => $sub,
                            'author'                  => $author,
                            'email_pitch'             => $pitchData['email_pitch'] ?? null,
                            'email_subject'           => $pitchData['email_subject'] ?? null,
                            'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                            'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                            'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                        ],
                    ]);

                    $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                    $ingested++;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Reddit polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Smart URL or Raw Text Ingestion Engine.
     * Takes any pasted URL (Upwork, LinkedIn, Twitter/X, job board) or raw RFP scope,
     * extracts text, runs OpenAI analysis, and saves a qualified market requirement.
     */
    public function extractFromUrlOrText(
        string $input,
        ?string $source = null,
        ?string $manualTitle = null,
        ?string $contactName = null,
        ?string $contactCompany = null
    ): array {
        $trimmed = trim($input);
        $isUrl = (bool) preg_match('/^https?:\/\//i', $trimmed);
        $extractedText = $trimmed;
        $url = null;

        if ($isUrl) {
            $url = $trimmed;
            try {
                $resp = Http::timeout(10)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])->get($trimmed);

                if ($resp->successful()) {
                    $html = $resp->body();
                    // Remove scripts, styles
                    $cleanHtml = preg_replace('/<(script|style)[^>]*?>.*?<\/\\1>/si', '', $html);
                    $extractedText = trim(html_entity_decode(strip_tags($cleanHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    // Collapse excessive newlines/spaces
                    $extractedText = preg_replace('/\s+/', ' ', $extractedText);
                    $extractedText = substr($extractedText, 0, 4000);
                }
            } catch (\Throwable $e) {
                Log::warning("Could not fetch remote URL {$trimmed}: " . $e->getMessage());
            }
        }

        $detectedSource = $source ?: ($isUrl ? (str_contains($url, 'upwork.com') ? 'upwork' : (str_contains($url, 'linkedin.com') ? 'linkedin' : 'custom_url')) : 'direct_rfp');
        $title = $manualTitle ?: (substr($extractedText, 0, 70) . '...');

        $budget = $this->extractBudget($extractedText, '$4,500 – $9,000');
        $pitchData = $this->pitchGenerator->generateAiPitch(
            $extractedText,
            $contactName,
            $contactCompany,
            $detectedSource,
            'USD',
            $budget['raw']
        );

        $externalId = 'custom_' . md5($trimmed . microtime());

        $req = MarketRequirement::create([
            'source'           => $detectedSource,
            'external_id'      => $externalId,
            'title'            => substr($title, 0, 190),
            'raw_text'         => substr($extractedText, 0, 3000),
            'budget_raw'       => $pitchData['budget_range'] ?? $budget['raw'],
            'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
            'currency'         => 'USD',
            'contact_name'     => $contactName,
            'contact_company'  => $contactCompany,
            'location'         => 'Remote (US/Global)',
            'matched_segment'  => $pitchData['segment'],
            'relevance_score'  => $pitchData['relevance_score'] ?? 80,
            'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
            'status'           => 'qualified',
            'metadata'         => [
                'url'                     => $url,
                'email_pitch'             => $pitchData['email_pitch'] ?? null,
                'email_subject'           => $pitchData['email_subject'] ?? null,
                'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                'client_pain_points'      => $pitchData['client_pain_points'] ?? [],
            ],
        ]);

        return [
            'requirement' => $req,
            'pitch_data'  => $pitchData,
        ];
    }

    /**
     * Tier 1 Strict Filter: Rejects junk gigs, full-time employment, and non-target tech.
     */
    public function passesTier1Filters(string $text): bool
    {
        $normalized = strtolower($text);

        // 1. Negative keywords filter
        foreach (self::NEGATIVE_KEYWORDS as $badWord) {
            if (str_contains($normalized, $badWord)) {
                return false;
            }
        }

        // 2. Minimum technical scope keywords for DigitalBuilders
        $hasTechKeywords = preg_match(
            '/\b(software|developer|engineer|full-stack|fullstack|frontend|backend|web app|mobile app|pwa|mvp|saas|portal|crm|erp|vue|react|next|nuxt|laravel|python|node|api|database|ai|automation|fastapi|django)\b/i',
            $text
        );

        return (bool) $hasTechKeywords;
    }

    /**
     * Extract budget information from text.
     */
    public function extractBudget(string $text, string $defaultRange = '$4,000 – $8,000'): array
    {
        if (preg_match('/\$([0-9]{1,3}(?:,[0-9]{3})+|[0-9]+(?:\.[0-9]{2})?)\s*(?:k\b|grand)?/i', $text, $matches)) {
            $val = (float) str_replace(',', '', $matches[1]);
            if (stripos($matches[0], 'k') !== false) {
                $val *= 1000;
            }

            if (preg_match('/\/(?:hr|hour)\b/i', $text) && $val < 300) {
                $estSprint = round($val * 80);
                return [
                    'amount'    => (float) $estSprint,
                    'raw'       => "\${$val}/hr (~$" . number_format($estSprint) . ' sprint)',
                    'is_hourly' => true,
                ];
            }

            if ($val >= 1000) {
                return [
                    'amount'    => $val,
                    'raw'       => '$' . number_format($val),
                    'is_hourly' => false,
                ];
            }
        }

        return [
            'amount'    => null,   // No budget found in RFP text; do not inject fake values
            'raw'       => $defaultRange,
            'is_hourly' => false,
        ];
    }

    /**
     * Poll public GitHub Discussions & Issues for developer contract / freelance requirements.
     */
    public function pollGitHubDiscussions(): int
    {
        $ingested = 0;

        try {
            $query = 'is:issue is:open ("contract developer" OR "hire developer" OR "freelance developer" OR "bounty")';
            $url = 'https://api.github.com/search/issues?' . http_build_query([
                'q'        => $query,
                'sort'     => 'created',
                'order'    => 'desc',
                'per_page' => 15,
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'DigitalBuilders-Lead-Hunter/1.0',
                    'Accept'     => 'application/vnd.github.v3+json',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('GitHub RFP poll returned status ' . $response->status());
                return 0;
            }

            $items = $response->json('items') ?? [];

            foreach ($items as $item) {
                $externalId = 'gh_' . ($item['id'] ?? uniqid());
                $title = (string) ($item['title'] ?? 'Developer Requirement');
                $body = (string) ($item['body'] ?? '');
                $author = (string) ($item['user']['login'] ?? 'GitHub User');
                $htmlUrl = (string) ($item['html_url'] ?? '');

                $fullText = $title . "\n\n" . $body;

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $existing = MarketRequirement::where('source', 'github')
                    ->where('external_id', $externalId)
                    ->first();

                if ($existing) {
                    continue;
                }

                $budget = $this->extractBudget($fullText, '$3,500 – $7,500');
                $pitchData = $this->pitchGenerator->generateAiPitch(
                    $fullText,
                    $author,
                    'GitHub Open Source / Startup',
                    'github',
                    'USD',
                    $budget['raw']
                );

                $techTags = $this->extractTechTags($fullText);

                MarketRequirement::create([
                    'source'           => 'github',
                    'external_id'      => $externalId,
                    'title'            => substr($title, 0, 190),
                    'raw_text'         => substr($fullText, 0, 3000),
                    'budget_raw'       => $pitchData['budget_range'] ?? $budget['raw'],
                    'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                    'currency'         => 'USD',
                    'contact_name'     => $author,
                    'contact_company'  => 'GitHub Project (' . ($item['repository_url'] ? basename(dirname((string) $item['repository_url'])) : 'Community') . ')',
                    'location'         => 'Global / Remote',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? 75,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                    => $htmlUrl,
                        'tech_tags'              => $techTags,
                        'email_pitch'            => $pitchData['email_pitch'] ?? null,
                        'email_subject'          => $pitchData['email_subject'] ?? ("Regarding your requirement: " . substr($title, 0, 50)),
                        'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'    => $pitchData['detected_tech_stack'] ?? $techTags,
                        'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                        'client_pain_points'     => $pitchData['client_pain_points'] ?? [],
                    ],
                ]);

                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::error('GitHub poll error: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Jobicy API for contract / freelance developer requirements.
     */
    public function pollJobicy(): int
    {
        $ingested = 0;

        try {
            $response = Http::withoutVerifying()->timeout(12)->withHeaders([
                'User-Agent' => 'DigitalBuilders/1.0 (LeadHunter; founder@digitalbuilders.in)',
            ])->get('https://jobicy.com/api/v2/remote-jobs', [
                'count'    => 30,
                'industry' => 'engineering',
            ]);

            if (!$response->successful()) {
                return 0;
            }

            $jobs = $response->json('jobs') ?? [];
            if (!is_array($jobs)) {
                return 0;
            }

            foreach ($jobs as $job) {
                $id = (string) ($job['id'] ?? '');
                $title = trim(html_entity_decode((string) ($job['jobTitle'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $company = trim(html_entity_decode((string) ($job['companyName'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $description = trim(html_entity_decode(strip_tags((string) ($job['jobDescription'] ?? $job['jobExcerpt'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $url = (string) ($job['url'] ?? '');
                $jobType = implode(' ', (array) ($job['jobType'] ?? []));
                $location = (string) ($job['jobGeo'] ?? 'Remote (Global)');

                if (!$id || !$title || MarketRequirement::where('source', 'jobicy')->where('external_id', $id)->exists()) {
                    continue;
                }

                $fullText = "{$title} at {$company}. {$jobType}. {$description}";

                // Filter for contract, freelance, or software project scopes
                $isContract = preg_match('/\b(contract|contractor|freelance|part-time|project|consultant|mvp)\b/i', $fullText);
                if (!$isContract) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $salaryMin = (float) ($job['salaryMin'] ?? 0);
                $budgetRaw = '$5,000 – $10,000';
                $amount = 6500.00;
                if ($salaryMin > 1000) {
                    $budgetRaw = '$' . number_format($salaryMin) . '+';
                    $amount = min($salaryMin, 25000.00);
                }

                $score = $this->pitchGenerator->scoreRelevance($fullText, $budgetRaw, null, null);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, $company, 'jobicy', 'USD', $budgetRaw);
                $techTags = $this->extractTechTags($fullText);

                $req = MarketRequirement::create([
                    'source'           => 'jobicy',
                    'external_id'      => $id,
                    'title'            => substr("Jobicy: {$title} — {$company}", 0, 190),
                    'raw_text'         => substr($description, 0, 3000),
                    'budget_raw'       => $budgetRaw,
                    'estimated_amount' => $amount,
                    'currency'         => 'USD',
                    'contact_company'  => $company,
                    'location'         => $location,
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                    => $url,
                        'company'                => $company,
                        'tech_tags'              => $techTags,
                        'email_pitch'            => $pitchData['email_pitch'] ?? null,
                        'email_subject'          => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'    => $techTags,
                        'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Jobicy polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Arbeitnow API for contract and freelance software engineering projects.
     */
    public function pollArbeitnow(): int
    {
        $ingested = 0;

        try {
            $response = Http::withoutVerifying()->timeout(12)->withHeaders([
                'User-Agent' => 'DigitalBuilders/1.0 (LeadHunter; founder@digitalbuilders.in)',
            ])->get('https://www.arbeitnow.com/api/job-board-api');

            if (!$response->successful()) {
                return 0;
            }

            $items = $response->json('data') ?? [];
            if (!is_array($items)) {
                return 0;
            }

            $slice = array_slice($items, 0, 35);
            foreach ($slice as $job) {
                $slug = (string) ($job['slug'] ?? '');
                $title = trim(html_entity_decode((string) ($job['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $company = trim(html_entity_decode((string) ($job['company_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $description = trim(html_entity_decode(strip_tags((string) ($job['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $url = (string) ($job['url'] ?? '');
                $jobTypes = implode(' ', (array) ($job['job_types'] ?? []));
                $tags = implode(', ', (array) ($job['tags'] ?? []));
                $location = (string) ($job['location'] ?? 'Remote (Global)');

                if (!$slug || !$title || MarketRequirement::where('source', 'arbeitnow')->where('external_id', $slug)->exists()) {
                    continue;
                }

                $fullText = "{$title} at {$company}. Tags: {$tags}. Types: {$jobTypes}. {$description}";

                // Filter for contract/freelance/consulting projects
                if (!preg_match('/\b(contract|contractor|freelance|part-time|consultant|project|mvp)\b/i', $fullText)) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $budget = $this->extractBudget($fullText, '$4,500 – $9,500');
                $score = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                if ($score < 50) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, $company, 'arbeitnow', 'USD', $budget['raw']);
                $techTags = $this->extractTechTags($fullText);

                $req = MarketRequirement::create([
                    'source'           => 'arbeitnow',
                    'external_id'      => $slug,
                    'title'            => substr("Arbeitnow: {$title} — {$company}", 0, 190),
                    'raw_text'         => substr($description, 0, 3000),
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'] ?? 6500.00,
                    'currency'         => 'USD',
                    'contact_company'  => $company,
                    'location'         => $location,
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                    => $url,
                        'company'                => $company,
                        'tech_tags'              => $techTags,
                        'email_pitch'            => $pitchData['email_pitch'] ?? null,
                        'email_subject'          => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'    => $techTags,
                        'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Arbeitnow polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Product Hunt RSS for newly launched software startups needing v2 scaling & mobile engineering.
     */
    public function pollProductHuntLaunches(): int
    {
        $ingested = 0;

        try {
            $response = Http::withoutVerifying()->timeout(12)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->get('https://www.producthunt.com/feed');

            if (!$response->successful()) {
                return 0;
            }

            $xml = @simplexml_load_string($response->body());
            if (!$xml || !isset($xml->entry)) {
                return 0;
            }

            $count = 0;
            foreach ($xml->entry as $entry) {
                $productName = trim((string) ($entry->title ?? ''));
                $url = (string) ($entry->link->attributes()['href'] ?? $entry->link['href'] ?? '');
                $rawContent = trim((string) ($entry->content ?? ''));
                $cleanContent = trim(html_entity_decode(strip_tags($rawContent), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cleanContent = trim(preg_replace('/\b(Discussion\s*\|\s*Link)\b/i', '', $cleanContent));
                $guid = 'ph_' . md5($productName . $url);

                if (!$productName || strlen($cleanContent) < 10 || MarketRequirement::where('source', 'producthunt')->where('external_id', $guid)->exists()) {
                    continue;
                }

                $fullText = "{$productName}: {$cleanContent}";

                // Filter for software, apps, tools, platforms, AI, and SaaS
                if (!preg_match('/\b(app|platform|saas|ai|software|tool|bot|portal|dashboard|builder|analytics|automation|mobile|web|cloud|api|workspace|credit|design)\b/i', $fullText)) {
                    continue;
                }

                $techTags = $this->extractTechTags($fullText);
                $budgetRaw = '$4,500 – $8,500 (V2 Architecture Sprint)';
                $amount = 5500.00;

                $pitchData = $this->pitchGenerator->generateAiPitch(
                    "Startup launched on Product Hunt: {$productName}. Concept: {$cleanContent}. Needs v2 architecture sprint, mobile apps, and high-concurrency cloud scaling.",
                    $productName . ' Founder',
                    $productName,
                    'producthunt',
                    'USD',
                    $budgetRaw
                );

                $req = MarketRequirement::create([
                    'source'           => 'producthunt',
                    'external_id'      => substr($guid, 0, 190),
                    'title'            => substr("Product Hunt Launch: {$productName}", 0, 190),
                    'raw_text'         => substr($cleanContent, 0, 3000),
                    'budget_raw'       => $budgetRaw,
                    'estimated_amount' => $amount,
                    'currency'         => 'USD',
                    'contact_company'  => $productName,
                    'location'         => 'Global (Product Hunt Launch)',
                    'matched_segment'  => 'saas_ai',
                    'relevance_score'  => 85,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                    => $url,
                        'product_name'           => $productName,
                        'tech_tags'              => $techTags,
                        'email_pitch'            => $pitchData['email_pitch'] ?? null,
                        'email_subject'          => "Scaling architecture & mobile v2 for {$productName} (congrats on launch!)",
                        'linkedin_dm'            => "Congrats on the {$productName} launch! We help newly launched SaaS teams build their mobile apps and scale backend concurrency in 4-week sprints. Let's connect!",
                        'detected_tech_stack'    => $techTags,
                        'suggested_architecture' => 'High-concurrency PostgreSQL backend with Redis caching and native iOS/Android PWA apps.',
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
                $count++;
                if ($count >= 5) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Product Hunt polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Extract technology tags from raw RFP text.
     */
    public function extractTechTags(string $text): array
    {
        $tags = [];
        $textLower = strtolower($text);

        $stackMap = [
            'Laravel'    => ['laravel', 'artisan', 'eloquent', 'blade', 'livewire'],
            'Vue'        => ['vue', 'vuejs', 'vue.js', 'vue 3', 'pinia', 'inertia', 'vite'],
            'React'      => ['react', 'reactjs', 'nextjs', 'next.js', 'typescript'],
            'Python/AI'  => ['python', 'fastapi', 'django', 'langchain', 'openai', 'llm', 'machine learning', 'ai/ml', 'pytorch', 'rag'],
            'Mobile'     => ['flutter', 'react native', 'ios', 'android', 'swift', 'kotlin'],
            'Full-Stack' => ['fullstack', 'full-stack', 'full stack', 'backend', 'frontend', 'api integration', 'microservices'],
            'Database'   => ['postgresql', 'postgres', 'mysql', 'supabase', 'redis', 'dynamodb', 'mongodb'],
        ];

        foreach ($stackMap as $label => $keywords) {
            foreach ($keywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $textLower)) {
                    $tags[] = $label;
                    break;
                }
            }
        }

        return !empty($tags) ? array_values(array_unique($tags)) : ['Full-Stack'];
    }

    /**
     * Poll Indie Hackers "developer wanted" posts via their RSS/public feed.
     * Targets solo SaaS founders actively seeking a technical co-founder or dev partner.
     */
    public function pollIndieHackers(): int
    {
        $ingested = 0;

        try {
            $posts = [];

            // Try public RSS for developers-wanted group
            $rssResp = Http::withoutVerifying()->timeout(12)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 DigitalBuilders/1.0',
                'Accept'     => 'application/rss+xml, application/xml, text/xml',
            ])->get('https://www.indiehackers.com/group/developers-wanted/feed');

            if ($rssResp->successful()) {
                $xml = @simplexml_load_string($rssResp->body());
                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $link   = (string) $item->link;
                        $title  = trim(html_entity_decode((string) $item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        $desc   = trim(html_entity_decode(strip_tags((string) $item->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        $posts[] = [
                            'id'      => md5($link),
                            'title'   => $title,
                            'content' => $desc,
                            'url'     => $link,
                            'author'  => 'IH Founder',
                        ];
                    }
                }
            }

            foreach ($posts as $post) {
                $id      = (string) ($post['id'] ?? '');
                $title   = (string) ($post['title'] ?? '');
                $content = (string) ($post['content'] ?? '');
                $url     = (string) ($post['url'] ?? '');
                $author  = (string) ($post['author'] ?? 'IH Founder');

                if (!$id || !$title || MarketRequirement::where('source', 'indiehackers')->where('external_id', $id)->exists()) {
                    continue;
                }

                $fullText = "{$title}\n\n{$content}";

                if (!preg_match('/\b(developer|engineer|co-founder|dev partner|agency|build|backend|frontend|fullstack)\b/i', $fullText)) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $budget    = $this->extractBudget($fullText, '$3,000 – $8,000');
                $score     = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                if ($score < 45) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch($fullText, $author, null, 'indiehackers', 'USD', $budget['raw']);
                $techTags  = $this->extractTechTags($fullText);

                $req = MarketRequirement::create([
                    'source'           => 'indiehackers',
                    'external_id'      => substr($id, 0, 150),
                    'title'            => substr("IndieHackers: {$title}", 0, 190),
                    'raw_text'         => substr($content, 0, 3000),
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                    'currency'         => 'USD',
                    'contact_name'     => $author,
                    'location'         => 'Global / Remote (Indie Hackers)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                    => $url,
                        'author'                 => $author,
                        'tech_tags'              => $techTags,
                        'email_pitch'            => $pitchData['email_pitch'] ?? null,
                        'email_subject'          => $pitchData['email_subject'] ?? null,
                        'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                        'detected_tech_stack'    => $pitchData['detected_tech_stack'] ?? $techTags,
                        'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('IndieHackers polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll Wellfound (AngelList) RSS for contract / freelance funded-startup tech roles.
     * Targets Seed / Series A startups posting for contractor or consultant engineering help.
     */
    public function pollWellfound(): int
    {
        $ingested = 0;

        $feedUrls = [
            'https://wellfound.com/jobs.rss?role=Software+Engineer&remote=true&job_type=Contract',
            'https://wellfound.com/jobs.rss?role=Full+Stack+Engineer&remote=true&job_type=Contract',
        ];

        foreach ($feedUrls as $feedUrl) {
            try {
                $response = Http::withoutVerifying()->timeout(12)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DigitalBuilders/1.0',
                    'Accept'     => 'application/rss+xml, application/xml, text/xml',
                ])->get($feedUrl);

                if (!$response->successful()) {
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->channel->item)) {
                    continue;
                }

                foreach ($xml->channel->item as $item) {
                    $link        = (string) $item->link;
                    $title       = trim(html_entity_decode((string) $item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $description = trim(html_entity_decode(strip_tags((string) $item->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $guid        = (string) ($item->guid ?: md5($link));

                    if (MarketRequirement::where('source', 'wellfound')->where('external_id', $guid)->exists()) {
                        continue;
                    }

                    $fullText = "{$title}\n\n{$description}";

                    if (!preg_match('/\b(contract|contractor|freelance|consultant|project|part-time|mvp)\b/i', $fullText)) {
                        continue;
                    }

                    if (!$this->passesTier1Filters($fullText)) {
                        continue;
                    }

                    $budget = $this->extractBudget($fullText, '$6,000 – $14,000');
                    $score  = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                    if ($score < 50) {
                        continue;
                    }

                    $company = null;
                    if (preg_match('/[-–—]\s*(.+)$/', $title, $m)) {
                        $company = trim($m[1]);
                    }

                    $pitchData = $this->pitchGenerator->generateAiPitch($fullText, null, $company, 'wellfound', 'USD', $budget['raw']);
                    $techTags  = $this->extractTechTags($fullText);

                    $req = MarketRequirement::create([
                        'source'           => 'wellfound',
                        'external_id'      => substr($guid, 0, 150),
                        'title'            => substr("Wellfound: {$title}", 0, 190),
                        'raw_text'         => substr($description, 0, 3000),
                        'budget_raw'       => $budget['raw'],
                        'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                        'currency'         => 'USD',
                        'contact_company'  => $company,
                        'location'         => 'Remote (Funded Startup / Global)',
                        'matched_segment'  => $pitchData['segment'],
                        'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                        'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                        'status'           => 'qualified',
                        'metadata'         => [
                            'url'                    => $link,
                            'company'                => $company,
                            'tech_tags'              => $techTags,
                            'email_pitch'            => $pitchData['email_pitch'] ?? null,
                            'email_subject'          => $pitchData['email_subject'] ?? null,
                            'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                            'detected_tech_stack'    => $pitchData['detected_tech_stack'] ?? $techTags,
                            'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                        ],
                    ]);

                    $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                    $ingested++;
                }
            } catch (\Throwable $e) {
                Log::warning('Wellfound polling exception: ' . $e->getMessage());
            }
        }

        return $ingested;
    }

    /**
     * Poll Substack #buildinpublic tag feed for founders announcing new builds.
     * First-mover outreach window is 72h after the post — zero competition at this stage.
     */
    public function pollSubstackBuildInPublic(): int
    {
        $ingested = 0;

        try {
            $response = Http::withoutVerifying()->timeout(12)->withHeaders([
                'User-Agent' => 'DigitalBuilders/1.0 (LeadHunter; founder@digitalbuilders.in)',
                'Accept'     => 'application/json',
            ])->get('https://substack.com/api/v1/reader/feed/tag/buildinpublic', ['limit' => 20]);

            $posts = [];
            if ($response->successful()) {
                $posts = $response->json('posts') ?? $response->json() ?? [];
            }

            if (!is_array($posts)) {
                return 0;
            }

            foreach (array_slice($posts, 0, 15) as $post) {
                $id      = (string) ($post['id'] ?? md5((string) ($post['canonical_url'] ?? uniqid())));
                $title   = trim(html_entity_decode((string) ($post['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $excerpt = trim(html_entity_decode(strip_tags((string) ($post['subtitle'] ?? $post['truncated_body_text'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $url     = (string) ($post['canonical_url'] ?? '');
                $author  = (string) ($post['publisherName'] ?? $post['author'] ?? 'Substack Founder');

                if (!$id || !$title || MarketRequirement::where('source', 'substack')->where('external_id', $id)->exists()) {
                    continue;
                }

                $fullText = "{$title}\n\n{$excerpt}";

                if (!preg_match('/\b(building|launched|shipping|mvp|saas|app|product|startup|tool|platform|api|software)\b/i', $fullText)) {
                    continue;
                }

                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $budget    = $this->extractBudget($fullText, '$3,500 – $7,000');
                $score     = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                if ($score < 40) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generateAiPitch(
                    "Founder building in public: {$title}. {$excerpt}. They just announced their build and may need backend scaling, mobile apps, or technical co-founder guidance.",
                    $author, null, 'substack', 'USD', $budget['raw']
                );
                $techTags = $this->extractTechTags($fullText);

                $req = MarketRequirement::create([
                    'source'           => 'substack',
                    'external_id'      => substr($id, 0, 150),
                    'title'            => substr("Substack BIP: {$title}", 0, 190),
                    'raw_text'         => substr($excerpt, 0, 3000),
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                    'currency'         => 'USD',
                    'contact_name'     => $author,
                    'location'         => 'Global (Build-in-Public Community)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'                    => $url,
                        'author'                 => $author,
                        'tech_tags'              => $techTags,
                        'email_pitch'            => $pitchData['email_pitch'] ?? null,
                        'email_subject'          => "Loved your #buildinpublic post — here's how we can accelerate {$title}",
                        'linkedin_dm'            => "Saw your #buildinpublic post about \"{$title}\" — congrats on shipping! We help founders scale from MVP to production in 4-week sprints. Would love to connect!",
                        'detected_tech_stack'    => $pitchData['detected_tech_stack'] ?? $techTags,
                        'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                    ],
                ]);

                $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                $ingested++;
            }
        } catch (\Throwable $e) {
            Log::warning('Substack BIP polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }

    /**
     * Poll r/startups, r/SaaS, r/Entrepreneur for dev-seeking and hiring signals.
     * Complements pollReddit() which only covers r/forhire.
     */
    public function pollRedditStartupSignals(): int
    {
        $clientId     = config('services.reddit.client_id') ?? env('REDDIT_CLIENT_ID');
        $clientSecret = config('services.reddit.client_secret') ?? env('REDDIT_CLIENT_SECRET');

        if (!$clientId || !$clientSecret) {
            return 0;
        }

        $ingested = 0;

        try {
            $authResponse = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->withHeaders(['User-Agent' => 'DigitalBuildersBot/1.0'])
                ->post('https://www.reddit.com/api/v1/access_token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$authResponse->successful()) {
                return 0;
            }

            $token = $authResponse->json('access_token');
            if (!$token) {
                return 0;
            }

            $subreddits   = ['startups', 'SaaS', 'Entrepreneur'];
            $hiringPattern = '/\b(looking for|need|hiring|seeking|want to hire|find a developer|need a developer|build an app|build an mvp|need tech|technical co-founder|dev shop|development agency|outsource)\b/i';

            foreach ($subreddits as $sub) {
                $postsResponse = Http::withToken($token)
                    ->withHeaders(['User-Agent' => 'DigitalBuildersBot/1.0'])
                    ->get("https://oauth.reddit.com/r/{$sub}/new", ['limit' => 20]);

                if (!$postsResponse->successful()) {
                    continue;
                }

                $children = $postsResponse->json('data.children') ?? [];
                foreach ($children as $child) {
                    $post      = $child['data'] ?? [];
                    $id        = (string) ($post['id'] ?? '');
                    $title     = trim(html_entity_decode((string) ($post['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $selftext  = trim(html_entity_decode((string) ($post['selftext'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $author    = (string) ($post['author'] ?? 'Redditor');
                    $permalink = 'https://reddit.com' . ($post['permalink'] ?? '');

                    if (!$id || MarketRequirement::where('source', 'reddit_startup')->where('external_id', $id)->exists()) {
                        continue;
                    }

                    $fullText = "{$title}\n\n{$selftext}";

                    if (!preg_match($hiringPattern, $fullText)) {
                        continue;
                    }

                    if (!$this->passesTier1Filters($fullText)) {
                        continue;
                    }

                    $budget = $this->extractBudget($fullText, '$3,000 – $7,000');
                    $score  = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                    if ($score < 45) {
                        continue;
                    }

                    $pitchData = $this->pitchGenerator->generateAiPitch($fullText, $author, null, 'reddit_startup', 'USD', $budget['raw']);
                    $techTags  = $this->extractTechTags($fullText);

                    $req = MarketRequirement::create([
                        'source'           => 'reddit_startup',
                        'external_id'      => $id,
                        'title'            => substr("r/{$sub}: {$title}", 0, 190),
                        'raw_text'         => substr($fullText, 0, 3000),
                        'budget_raw'       => $budget['raw'],
                        'estimated_amount' => $pitchData['estimated_amount'] ?? $budget['amount'],
                        'currency'         => 'USD',
                        'contact_name'     => $author,
                        'location'         => "Reddit (r/{$sub})",
                        'matched_segment'  => $pitchData['segment'],
                        'relevance_score'  => $pitchData['relevance_score'] ?? $score,
                        'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                        'status'           => 'qualified',
                        'metadata'         => [
                            'url'                    => $permalink,
                            'subreddit'              => $sub,
                            'author'                 => $author,
                            'tech_tags'              => $techTags,
                            'email_pitch'            => $pitchData['email_pitch'] ?? null,
                            'email_subject'          => $pitchData['email_subject'] ?? null,
                            'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                            'detected_tech_stack'    => $pitchData['detected_tech_stack'] ?? $techTags,
                            'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                        ],
                    ]);

                    $this->telegramBot->sendOpportunityAlert($req, $pitchData);
                    $ingested++;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Reddit startup signals polling exception: ' . $e->getMessage());
        }

        return $ingested;
    }
}
