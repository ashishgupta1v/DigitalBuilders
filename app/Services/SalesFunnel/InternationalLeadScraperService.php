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
            'weworkremotely' => $this->pollWeWorkRemotely(),
            'remoteok'       => $this->pollRemoteOk(),
            'remotive'       => $this->pollRemotive(),
            'himalayas'      => $this->pollHimalayas(),
            'reddit'         => $this->pollReddit(),
        ];

        $stats['total'] = array_sum($stats);
        return $stats;
    }

    /**
     * Poll Hacker News Algolia Search API for live "SEEKING FREELANCER" and direct founder posts.
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

            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return 0;
            }

            $hits = $response->json('hits') ?? [];
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
}
