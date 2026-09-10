<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

use App\Models\Lead;

class CrmLeadEnrichmentService
{
    /**
     * Enriches a lead with company domain intelligence, likely tech stack, and executive search queries.
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
            $freemail = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com', 'proton.me', 'protonmail.com', 'mail.com'];
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

        $searchQuery = urlencode($companyRaw ?: ($domain ?: ''));

        // 3. Generate 1-tap intelligence links
        $dossier = [
            'domain'                 => $domain,
            'company_name'           => $companyRaw ?: ($lead->name . ' Ventures'),
            'segment'                => $lead->segment ?? 'general',
            'linkedin_company_url'   => "https://www.linkedin.com/search/results/companies/?keywords={$searchQuery}",
            'linkedin_exec_url'      => "https://www.linkedin.com/search/results/people/?keywords=" . urlencode(($companyRaw ?: '') . ' Founder OR CEO OR CTO'),
            'twitter_search_url'     => "https://x.com/search?q=" . urlencode(($companyRaw ?: '') . ' "build" OR "developer" OR "hiring"'),
            'crunchbase_url'         => "https://www.crunchbase.com/textsearch?q={$searchQuery}",
            'whois_url'              => $domain ? "https://who.is/whois/{$domain}" : null,
            'enriched_at'            => now()->toIso8601String(),
        ];

        // 4. Infer suggested tech stack based on project type & segment
        $stack = ['Laravel 11', 'Vue 3 (Inertia)', 'PostgreSQL', 'TailwindCSS'];
        $descLower = strtolower((string) ($lead->description . ' ' . $lead->project_type));

        if (str_contains($descLower, 'python') || str_contains($descLower, 'ai') || str_contains($descLower, 'ml') || str_contains($descLower, 'llm')) {
            $stack[] = 'FastAPI / Python ML Microservice';
        }
        if (str_contains($descLower, 'mobile') || str_contains($descLower, 'flutter') || str_contains($descLower, 'app')) {
            $stack[] = 'Flutter / React Native';
        }
        if (str_contains($descLower, 'ecommerce') || str_contains($descLower, 'shop') || str_contains($descLower, 'cart')) {
            $stack[] = 'Razorpay / Stripe Webhooks';
            $stack[] = 'Redis High-Throughput Queue';
        }

        $dossier['suggested_stack'] = $stack;

        return $dossier;
    }
}
