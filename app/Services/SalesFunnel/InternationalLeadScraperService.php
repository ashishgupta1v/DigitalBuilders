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
            'weworkremotely' => $this->pollWeWorkRemotely(),
            'remoteok'       => $this->pollRemoteOk(),
            'upwork'         => $this->pollUpwork(),
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
            // Query live comments matching high-intent freelance and hiring tags
            $url = 'https://hn.algolia.com/api/v1/search_by_date?' . http_build_query([
                'tags'        => 'comment',
                'query'       => 'SEEKING FREELANCER OR contractor OR "hire developer"',
                'hitsPerPage' => 15,
            ]);

            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return 0;
            }

            $hits = $response->json('hits') ?? [];
            foreach ($hits as $hit) {
                $commentId = (string) ($hit['objectID'] ?? '');
                $rawText = (string) ($hit['comment_text'] ?? '');
                $cleanText = strip_tags(html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5));
                $author = (string) ($hit['author'] ?? 'HN Founder');
                $storyTitle = (string) ($hit['story_title'] ?? 'Hacker News');

                if (!$commentId || strlen($cleanText) < 60) {
                    continue;
                }

                if (MarketRequirement::where('source', 'hackernews')->where('external_id', $commentId)->exists()) {
                    continue;
                }

                if (!$this->passesTier1Filters($cleanText)) {
                    continue;
                }

                $budget = $this->extractBudget($cleanText, '$3,500 – $8,000');
                $score = $this->pitchGenerator->scoreRelevance($cleanText, $budget['raw'], null, null);
                if ($score < 55) {
                    continue;
                }

                // Extract company or email if present
                preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $cleanText, $emailMatches);
                $extractedEmail = $emailMatches[0] ?? null;

                $pitchData = $this->pitchGenerator->generatePitch($cleanText, $author, null, 'USD');

                $req = MarketRequirement::create([
                    'source'           => 'hackernews',
                    'external_id'      => $commentId,
                    'title'            => substr("HN RFP: {$storyTitle} (by {$author})", 0, 190),
                    'raw_text'         => $cleanText,
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $budget['amount'],
                    'currency'         => 'USD',
                    'contact_name'     => $author,
                    'contact_email'    => $extractedEmail,
                    'location'         => 'Global (Hacker News)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'hn_url'     => "https://news.ycombinator.com/item?id={$commentId}",
                        'author'     => $author,
                        'story_id'   => $hit['story_id'] ?? null,
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
     * Poll WeWorkRemotely RSS feeds for full-stack, front-end, and back-end contracts.
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
                    $title = (string) $item->title;
                    $description = strip_tags((string) $item->description);
                    $guid = (string) ($item->guid ?: md5($link));

                    if (MarketRequirement::where('source', 'weworkremotely')->where('external_id', $guid)->exists()) {
                        continue;
                    }

                    if (!$this->passesTier1Filters($title . ' ' . $description)) {
                        continue;
                    }

                    // Look for contract/freelance or high-value contract opportunities
                    $isContract = preg_match('/\b(contract|contractor|freelance|part-time|consultant|project)\b/i', $title . ' ' . $description);
                    if (!$isContract) {
                        // Skip permanent full-time employment listings without contract scope
                        continue;
                    }

                    $budget = $this->extractBudget($description, '$5,000 – $10,000');
                    $score = $this->pitchGenerator->scoreRelevance($description, $budget['raw'], null, null);
                    if ($score < 55) {
                        continue;
                    }

                    // Extract company from "Company Name: Job Title" format common in WWR
                    $company = null;
                    if (str_contains($title, ':')) {
                        [$company, ] = explode(':', $title, 2);
                        $company = trim($company);
                    }

                    $pitchData = $this->pitchGenerator->generatePitch($description, null, $company, 'USD');

                    $req = MarketRequirement::create([
                        'source'           => 'weworkremotely',
                        'external_id'      => $guid,
                        'title'            => substr("WWR: {$title}", 0, 190),
                        'raw_text'         => $description,
                        'budget_raw'       => $budget['raw'],
                        'estimated_amount' => $budget['amount'],
                        'currency'         => 'USD',
                        'contact_company'  => $company,
                        'location'         => 'Remote (Global)',
                        'matched_segment'  => $pitchData['segment'],
                        'relevance_score'  => $score,
                        'pitch_draft'      => $pitchData['email_pitch'] ?? $pitchData['short_pitch'],
                        'status'           => 'qualified',
                        'metadata'         => [
                            'url'     => $link,
                            'company' => $company,
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
     * Poll RemoteOK API for funded tech startups with engineering contracts.
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

            // Slice top 30 freshest jobs (index 0 is API metadata)
            $items = array_slice($jobs, 1, 30);

            foreach ($items as $job) {
                $id = (string) ($job['id'] ?? '');
                $position = (string) ($job['position'] ?? '');
                $company = (string) ($job['company'] ?? '');
                $description = strip_tags((string) ($job['description'] ?? ''));
                $url = (string) ($job['url'] ?? '');
                $tags = implode(', ', (array) ($job['tags'] ?? []));
                $location = (string) ($job['location'] ?? 'Remote (Global)');

                if (!$id || !$position || MarketRequirement::where('source', 'remoteok')->where('external_id', $id)->exists()) {
                    continue;
                }

                $fullText = "{$position} at {$company}. Tags: {$tags}. {$description}";
                if (!$this->passesTier1Filters($fullText)) {
                    continue;
                }

                $isRelevant = preg_match('/\b(vue|react|laravel|full-stack|fullstack|node|python|mobile|pwa|mvp|ai|saas)\b/i', $fullText);
                if (!$isRelevant) {
                    continue;
                }

                $salaryMin = (float) ($job['salary_min'] ?? 0);
                $salaryMax = (float) ($job['salary_max'] ?? 0);
                $budgetRaw = '$4,000 – $9,000';
                $amount = 6500.00;

                if ($salaryMin > 0 && $salaryMax > 0) {
                    $budgetRaw = '$' . number_format($salaryMin) . ' – $' . number_format($salaryMax);
                    $amount = round(($salaryMin + $salaryMax) / 2);
                }

                $score = $this->pitchGenerator->scoreRelevance($fullText, $budgetRaw, null, null);
                if ($score < 55) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generatePitch($fullText, null, $company, 'USD');

                $req = MarketRequirement::create([
                    'source'           => 'remoteok',
                    'external_id'      => $id,
                    'title'            => substr("RemoteOK: {$position} — {$company}", 0, 190),
                    'raw_text'         => substr($description, 0, 3000),
                    'budget_raw'       => $budgetRaw,
                    'estimated_amount' => min($amount, 15000.00),
                    'currency'         => 'USD',
                    'contact_company'  => $company,
                    'location'         => $location,
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $score,
                    'pitch_draft'      => $pitchData['email_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => [
                        'url'     => $url,
                        'company' => $company,
                        'tags'    => $job['tags'] ?? [],
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
     * Poll Upwork personal RSS feed if configured.
     */
    public function pollUpwork(): int
    {
        $feedUrl = config('services.crm.upwork_feed_url') ?? env('UPWORK_RSS_FEED_URL');
        if (!$feedUrl) {
            return 0;
        }

        $ingested = 0;

        try {
            $response = Http::timeout(12)->get($feedUrl);
            if (!$response->successful()) {
                return 0;
            }

            $xml = @simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) {
                return 0;
            }

            foreach ($xml->channel->item as $item) {
                $link = (string) $item->link;
                $title = (string) $item->title;
                $description = strip_tags((string) $item->description);
                $guid = (string) ($item->guid ?: md5($link));

                if (MarketRequirement::where('source', 'upwork')->where('external_id', $guid)->exists()) {
                    continue;
                }

                if (!$this->passesTier1Filters($title . ' ' . $description)) {
                    continue;
                }

                $budget = $this->extractBudget($description, '$3,000 – $8,000');
                $score = $this->pitchGenerator->scoreRelevance($description, $budget['raw'], null, null);
                if ($score < 55) {
                    continue;
                }

                $pitchData = $this->pitchGenerator->generatePitch($description, null, null, 'USD');

                $req = MarketRequirement::create([
                    'source'           => 'upwork',
                    'external_id'      => $guid,
                    'title'            => substr("Upwork: {$title}", 0, 190),
                    'raw_text'         => $description,
                    'budget_raw'       => $budget['raw'],
                    'estimated_amount' => $budget['amount'],
                    'currency'         => 'USD',
                    'location'         => 'Global (Upwork)',
                    'matched_segment'  => $pitchData['segment'],
                    'relevance_score'  => $score,
                    'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                    'status'           => 'qualified',
                    'metadata'         => ['url' => $link],
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
     * Poll Reddit r/forhire and r/freelance_forhire if credentials or access configured.
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
            // Obtain Reddit App-Only OAuth Token
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
                    $title = (string) ($post['title'] ?? '');
                    $selftext = (string) ($post['selftext'] ?? '');
                    $author = (string) ($post['author'] ?? 'Redditor');
                    $permalink = 'https://reddit.com' . ($post['permalink'] ?? '');

                    // Must be a [Hiring] post
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

                    $budget = $this->extractBudget($fullText, '$2,500 – $6,000');
                    $score = $this->pitchGenerator->scoreRelevance($fullText, $budget['raw'], null, null);
                    if ($score < 55) {
                        continue;
                    }

                    $pitchData = $this->pitchGenerator->generatePitch($fullText, $author, null, 'USD');

                    $req = MarketRequirement::create([
                        'source'           => 'reddit',
                        'external_id'      => $id,
                        'title'            => substr("Reddit r/{$sub}: {$title}", 0, 190),
                        'raw_text'         => $fullText,
                        'budget_raw'       => $budget['raw'],
                        'estimated_amount' => $budget['amount'],
                        'currency'         => 'USD',
                        'contact_name'     => $author,
                        'location'         => "Reddit (r/{$sub})",
                        'matched_segment'  => $pitchData['segment'],
                        'relevance_score'  => $score,
                        'pitch_draft'      => $pitchData['reddit_dm'] ?? $pitchData['short_pitch'],
                        'status'           => 'qualified',
                        'metadata'         => [
                            'url'       => $permalink,
                            'subreddit' => $sub,
                            'author'    => $author,
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
     * Tier 1 High-Value Filter Engine: rejects low-value gigs and irrelevant tasks.
     */
    public function passesTier1Filters(string $text): bool
    {
        $normalized = strtolower($text);

        // 1. Rejection: Negative Keywords
        foreach (self::NEGATIVE_KEYWORDS as $badWord) {
            if (str_contains($normalized, $badWord)) {
                return false;
            }
        }

        // 2. Minimum technical scope keywords
        $hasTechKeywords = preg_match(
            '/\b(software|developer|engineer|full-stack|fullstack|frontend|backend|web app|mobile app|pwa|mvp|saas|portal|crm|erp|vue|react|laravel|python|node|api|database|ai|automation)\b/i',
            $text
        );

        return (bool) $hasTechKeywords;
    }

    /**
     * Extract budget information from text.
     */
    private function extractBudget(string $text, string $defaultRange = '$3,500 – $8,000'): array
    {
        // Check for explicit USD amounts like $4,000 or $5k
        if (preg_match('/\$([0-9]{1,3}(?:,[0-9]{3})+|[0-9]+(?:\.[0-9]{2})?)\s*(?:k\b|grand)?/i', $text, $matches)) {
            $val = (float) str_replace(',', '', $matches[1]);
            if (stripos($matches[0], 'k') !== false) {
                $val *= 1000;
            }

            // If hourly rate ($40/hr)
            if (preg_match('/\/(?:hr|hour)\b/i', $text) && $val < 300) {
                $estMonthly = round($val * 80); // ~80 hrs sprint
                return [
                    'amount'    => (float) $estMonthly,
                    'raw'       => "\${$val}/hr (~$" . number_format($estMonthly) . ' sprint)',
                    'is_hourly' => true,
                ];
            }

            if ($val >= 1500) {
                return [
                    'amount'    => $val,
                    'raw'       => '$' . number_format($val),
                    'is_hourly' => false,
                ];
            }
        }

        return [
            'amount'    => 5000.00,
            'raw'       => $defaultRange,
            'is_hourly' => false,
        ];
    }
}
