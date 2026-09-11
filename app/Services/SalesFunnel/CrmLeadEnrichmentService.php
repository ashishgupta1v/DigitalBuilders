<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmLeadEnrichmentService
{
    /**
     * Enriches a lead with company domain intelligence, live website scraping,
     * OpenAI structured dossier synthesis, and 1-tap executive search links.
     */
    public function enrichLead(Lead $lead): array
    {
        $companyRaw = trim((string) ($lead->company ?: ($lead->organization?->name ?: '')));
        $email = trim((string) $lead->email);
        $domain = null;

        // 1. Extract domain from email if not a generic freemail provider
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $email);
            $emailDomain = strtolower($parts[1] ?? '');
            $freemail = [
                'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com',
                'proton.me', 'protonmail.com', 'mail.com', 'aol.com', 'zoho.com'
            ];
            if (!in_array($emailDomain, $freemail, true)) {
                $domain = $emailDomain;
            }
        }

        // 2. Fallback to clean domain slug from company name
        if (!$domain && $companyRaw) {
            $cleanName = preg_replace('/\b(pvt|ltd|inc|llc|gmbh|technologies|solutions|labs|co|corp|corporation)\b/i', '', $companyRaw);
            $slug = preg_replace('/[^a-z0-9]/', '', strtolower((string) $cleanName));
            if (strlen($slug) >= 3) {
                $domain = $slug . '.com';
            }
        }

        // 3. Live website scraping (title, meta description, generator, server headers)
        $scrapedMeta = $this->scrapeDomainMetadata($domain);

        // 4. OpenAI structured synthesis or fallback heuristic
        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        $aiDossier = null;

        if (!empty($apiKey)) {
            $aiDossier = $this->synthesizeWithOpenAi($apiKey, $lead, $companyRaw, $domain, $scrapedMeta);
        }

        // Fallback heuristics if OpenAI was skipped or failed
        if (!$aiDossier) {
            $aiDossier = $this->synthesizeWithHeuristics($lead, $companyRaw, $scrapedMeta);
        }

        $searchQuery = urlencode($companyRaw ?: ($domain ?: $lead->name));

        // 5. Assemble comprehensive executive intelligence dossier
        $dossier = [
            'domain'                 => $domain,
            'company_name'           => $companyRaw ?: ($lead->name . ' Ventures'),
            'company_summary'        => $aiDossier['company_summary'] ?? 'Specialized digital venture operating in modern tech sectors.',
            'industry'               => $aiDossier['industry'] ?? ($lead->segment ? ucfirst($lead->segment) : 'Software & Technology'),
            'estimated_team_size'    => $aiDossier['estimated_team_size'] ?? '1-15 (Agile Core)',
            'detected_tech_stack'    => $aiDossier['detected_tech_stack'] ?? ['Laravel 11', 'Vue 3', 'PostgreSQL', 'TailwindCSS'],
            'suggested_stack'        => $aiDossier['detected_tech_stack'] ?? ['Laravel 11', 'Vue 3', 'PostgreSQL', 'TailwindCSS'],
            'client_pain_points'     => $aiDossier['client_pain_points'] ?? ['Scalable software architecture', 'Speed-to-market execution'],
            'recommended_hook'       => $aiDossier['recommended_hook'] ?? "Noticed your focus on high-performance architecture and wanted to share our sprint framework.",
            'website_title'          => $scrapedMeta['title'] ?? null,
            'website_meta'           => $scrapedMeta['description'] ?? null,
            'linkedin_company_url'   => "https://www.linkedin.com/search/results/companies/?keywords={$searchQuery}",
            'linkedin_exec_url'      => "https://www.linkedin.com/search/results/people/?keywords=" . urlencode(($companyRaw ?: '') . ' Founder OR CEO OR CTO'),
            'twitter_search_url'     => "https://x.com/search?q=" . urlencode(($companyRaw ?: '') . ' "build" OR "developer" OR "hiring"'),
            'crunchbase_url'         => "https://www.crunchbase.com/textsearch?q={$searchQuery}",
            'whois_url'              => $domain ? "https://who.is/whois/{$domain}" : null,
            'builtwith_url'          => $domain ? "https://builtwith.com/{$domain}" : null,
            'enriched_at'            => now()->toIso8601String(),
            'is_domain_verified'     => !empty($scrapedMeta['is_live']),
        ];

        // 6. Persist to Lead model
        $lead->enrichment_data = $dossier;

        // Auto-enhance lead score if valid intelligence was resolved
        $currentScore = (int) ($lead->score ?? 50);
        if (!empty($scrapedMeta['is_live']) && $currentScore < 85) {
            $currentScore = min(95, $currentScore + 10);
            $lead->score = $currentScore;
        }

        // Build neat human-readable AI summary
        $stackPills = implode(', ', $dossier['detected_tech_stack']);
        $pains = implode(' • ', $dossier['client_pain_points']);
        $summaryText = "🏢 {$dossier['company_name']} ({$dossier['industry']})\n"
            . "🌐 Domain: " . ($domain ?: 'Unverified') . " | Size: {$dossier['estimated_team_size']}\n"
            . "⚡ Core Stack: {$stackPills}\n"
            . "🎯 Pain Points: {$pains}\n"
            . "💡 Outreach Hook: {$dossier['recommended_hook']}";

        $lead->ai_summary = $summaryText;
        $lead->save();

        return $dossier;
    }

    /**
     * Perform lightweight HEAD / GET check on target domain to extract metadata.
     */
    private function scrapeDomainMetadata(?string $domain): array
    {
        if (!$domain || strlen($domain) < 4) {
            return ['is_live' => false];
        }

        try {
            $url = 'https://' . $domain;
            $response = Http::timeout(3)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 DigitalBuildersBot/1.0',
                    'Accept'     => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (!$response->successful() && $response->status() !== 403) {
                // Try http fallback
                $response = Http::timeout(2)->get('http://' . $domain);
            }

            if (!$response->successful()) {
                return ['is_live' => false];
            }

            $html = (string) $response->body();
            $title = null;
            $description = null;

            if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $m)) {
                $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/si', $html, $m)) {
                $description = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            } elseif (preg_match('/<meta[^>]+content=["\'](.*?)["\'][^>]+name=["\']description["\']/si', $html, $m)) {
                $description = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            $serverHeader = $response->header('Server');
            $poweredBy = $response->header('X-Powered-By');

            return [
                'is_live'       => true,
                'title'         => $title ? substr($title, 0, 150) : null,
                'description'   => $description ? substr($description, 0, 300) : null,
                'server_header' => $serverHeader,
                'powered_by'    => $poweredBy,
            ];
        } catch (\Throwable $e) {
            return ['is_live' => false];
        }
    }

    /**
     * Structured OpenAI extraction using GPT-4o-mini.
     */
    private function synthesizeWithOpenAi(string $apiKey, Lead $lead, string $company, ?string $domain, array $meta): ?array
    {
        try {
            $context = [
                'contact_name'       => $lead->name,
                'company'            => $company ?: ($domain ?: $lead->name),
                'domain'             => $domain,
                'lead_description'   => $lead->description,
                'project_type'       => $lead->project_type,
                'website_title'      => $meta['title'] ?? null,
                'website_meta'       => $meta['description'] ?? null,
            ];

            $prompt = <<<PROMPT
You are a senior enterprise solution architect at DigitalBuilders (https://www.digitalbuilders.in).
Analyze this prospective client data to compile an executive intelligence dossier for high-conversion founder-to-founder outreach:

Client Data:
Company / Project: {$context['company']}
Domain: {$context['domain']}
Contact Name: {$context['contact_name']}
Inquiry Description: {$context['lead_description']}
Project Category: {$context['project_type']}
Website Scraped Title: {$context['website_title']}
Website Scraped Description: {$context['website_meta']}

Respond ONLY with valid JSON in this exact structure:
{
  "company_summary": "1-2 concise sentences summarizing the client's business model and core offering.",
  "industry": "Specific industry classification (e.g., E-Commerce & Retail, HealthTech, B2B SaaS, FinTech, Logistics)",
  "estimated_team_size": "Estimated team bracket (e.g. 1-10 (Early Stage), 11-50 (Scaling), 50+)",
  "detected_tech_stack": ["Array of 4-6 recommended or detected tech stack components e.g. Laravel 11, Vue 3, PostgreSQL, TailwindCSS, AWS, Python/AI"],
  "client_pain_points": ["2-3 specific pain points the client is likely experiencing"],
  "recommended_hook": "1 personalized opening hook for an executive email to the founder mentioning their scope."
}
PROMPT;

            $response = Http::timeout(8)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => config('services.openai.model', 'gpt-4o-mini'),
                    'messages'    => [
                        ['role' => 'system', 'content' => 'You are an elite software sales architect. Respond only with valid JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens'  => 600,
                ]);

            if ($response->successful()) {
                $raw = trim($response->json('choices.0.message.content') ?? '');
                $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $raw);
                $decoded = json_decode($cleanJson, true);

                if (is_array($decoded) && !empty($decoded['detected_tech_stack'])) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("OpenAI lead enrichment timed out or failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Local heuristic synthesis when OpenAI is unavailable.
     */
    private function synthesizeWithHeuristics(Lead $lead, string $company, array $meta): array
    {
        $descLower = strtolower((string) ($lead->description . ' ' . $lead->project_type . ' ' . ($meta['description'] ?? '')));
        $stack = ['Laravel 11', 'Vue 3', 'PostgreSQL', 'TailwindCSS'];
        $pains = ['Scalable architecture', 'Fast turnaround execution'];
        $industry = 'Custom Software & SaaS';

        if (str_contains($descLower, 'python') || str_contains($descLower, 'ai') || str_contains($descLower, 'ml') || str_contains($descLower, 'llm')) {
            $stack[] = 'FastAPI / Python ML Microservice';
            $pains[] = 'LLM prompt orchestration & latency';
            $industry = 'AI / Machine Learning';
        }

        if (str_contains($descLower, 'mobile') || str_contains($descLower, 'flutter') || str_contains($descLower, 'app') || str_contains($descLower, 'ios')) {
            $stack[] = 'Flutter / Cross-Platform Mobile';
            $pains[] = 'Offline sync & store submission';
            $industry = 'Mobile Application';
        }

        if (str_contains($descLower, 'ecommerce') || str_contains($descLower, 'shop') || str_contains($descLower, 'cart') || str_contains($descLower, 'payment')) {
            $stack[] = 'Stripe / Razorpay Checkout';
            $stack[] = 'Redis High-Throughput Cache';
            $pains[] = 'Checkout drop-off & payment reliability';
            $industry = 'E-Commerce & Digital Store';
        }

        if (str_contains($descLower, 'health') || str_contains($descLower, 'clinic') || str_contains($descLower, 'patient')) {
            $industry = 'HealthTech & Wellness';
            $pains[] = 'Data privacy & compliance';
        }

        $hook = "Saw your requirement for " . ($lead->project_type ? str_replace('_', ' ', $lead->project_type) : 'custom software') . " and wanted to share our sprint architecture.";

        return [
            'company_summary'     => ($meta['description'] ?? null) ?: "Specialized venture in {$industry}, scaling digital operations.",
            'industry'            => $industry,
            'estimated_team_size' => '1-20 (Growing Venture)',
            'detected_tech_stack' => array_values(array_unique($stack)),
            'client_pain_points'  => array_slice($pains, 0, 3),
            'recommended_hook'    => $hook,
        ];
    }
}
