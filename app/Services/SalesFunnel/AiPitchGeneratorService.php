<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiPitchGeneratorService
{
    /**
     * Case studies corpus directly sourced from live production portfolio.
     */
    private const CASE_STUDIES = [
        'manufacturing' => [
            'client'       => 'Garg Enterprises',
            'location'     => 'Industrial Manufacturing',
            'industry'     => 'Packaging & Industrial Manufacturing',
            'challenge'    => 'Manual paper challans, 14% dispatch error rate, and zero inventory synchronization with ERP.',
            'solution'     => 'Built a rugged, offline-first mobile dispatch & barcode scanner PWA with bidirectional ERP sync.',
            'metric'       => 'Dispatch error rate dropped from 14% to 0.0%, saving $25,000+ annually in misrouted freight.',
            'proof_url'    => 'https://www.digitalbuilders.in',
            'hook'         => 'We built an offline-first barcode dispatch and inventory system, cutting dispatch errors from 14% to zero.',
        ],
        'edtech' => [
            'client'       => 'ZoetiCoach',
            'location'     => 'International / EdTech',
            'industry'     => 'Education Technology & Academic Coaching',
            'challenge'    => 'Tutors lost 25% of student renewals due to poor homework accountability and manual tracking.',
            'solution'     => 'Architected automated WhatsApp/Email homework bot, parent progress dashboards, and instant submission grading.',
            'metric'       => 'Supports 400+ active online educators with 99.8% message delivery and 42% higher retention.',
            'proof_url'    => 'https://www.digitalbuilders.in',
            'hook'         => 'We built the student accountability and automation infrastructure for ZoetiCoach, serving 400+ tutors with 99.8% message delivery.',
        ],
        'ecommerce' => [
            'client'       => 'MyAstrova Mall',
            'location'     => 'D2C Consumer Brands',
            'industry'     => 'D2C Consumer Goods & High-Traffic Commerce',
            'challenge'    => 'Frequent checkout abandonment, slow catalog filtering, and fragile payment gateway webhooks under traffic surges.',
            'solution'     => 'Engineered an ultra-fast Next.js store with Stripe/UPI checkout, dynamic inventory reserve, and automated order notifications.',
            'metric'       => 'Handles thousands of concurrent checkouts with zero double-billing and sub-800ms page transitions.',
            'proof_url'    => 'https://www.digitalbuilders.in',
            'hook'         => 'We engineered a high-traffic e-commerce store with instant checkout, sub-second product filters, and real-time order tracking.',
        ],
        'saas_ai' => [
            'client'       => 'Habuilt',
            'location'     => 'US / Global SaaS',
            'industry'     => 'High-Concurrency SaaS & AI Automation',
            'challenge'    => 'Spikes of 65,000 concurrent morning attendees crashing legacy database servers and WebSocket channels.',
            'solution'     => 'Engineered high-concurrency Redis caching layer, horizontally scalable backend, and resilient real-time attendance pipelines.',
            'metric'       => '99.99% system uptime through live 65k concurrent participant sessions every single day.',
            'proof_url'    => 'https://www.digitalbuilders.in',
            'hook'         => 'We built the high-concurrency backend for Habuilt, scaling live sessions to 65,000 concurrent daily active participants with 99.99% uptime.',
        ],
        'general' => [
            'client'       => 'DigitalBuilders Production Systems',
            'location'     => 'US & Europe Startups',
            'industry'     => 'Full-Stack Web & Mobile Architectures',
            'challenge'    => 'Businesses needing reliable, scalable custom software shipped fast without agency bloat.',
            'solution'     => 'Founder-led engineering sprints with complete source code ownership, CI/CD pipelines, and 60 days post-launch warranty.',
            'metric'       => '18+ enterprise production platforms shipped with 100% on-time milestone delivery.',
            'proof_url'    => 'https://www.digitalbuilders.in',
            'hook'         => 'We specialize in shipping production-grade platforms in 4–6 week fixed-price sprints with complete source code ownership.',
        ],
    ];

    /**
     * Classify requirement text into an industry segment.
     */
    public function classifySegment(string $text): string
    {
        $normalized = strtolower($text);

        if (preg_match('/\b(factory|manufactur|dispatch|warehouse|inventory|tally|barcode|challan|distributor|dealer|packaging|mill|textile|plant|supply chain)\b/i', $normalized)) {
            return 'manufacturing';
        }

        if (preg_match('/\b(tutor|student|homework|coaching|edtech|course|school|teacher|exam|quiz|learning|lms|education)\b/i', $normalized)) {
            return 'edtech';
        }

        if (preg_match('/\b(ecommerce|e-commerce|shop|store|catalog|cart|checkout|d2c|products|cod|razorpay|stripe|retail|fashion|order)\b/i', $normalized)) {
            return 'ecommerce';
        }

        if (preg_match('/\b(saas|ai|machine learning|automation|portal|dashboard|bot|api|workflow|mvp|concurrency|scalab|cloud|flutter|react native|next\.js|vue|laravel)\b/i', $normalized)) {
            return 'saas_ai';
        }

        return 'general';
    }

    /**
     * Calculate an intent / relevance score (0 - 100) — multi-factor v2.
     *
     * Score breakdown:
     *  - Budget tier   (40 pts): $10k+ = 40, $5k–10k = 30, $2k–5k = 20, hourly/custom = 10
     *  - Tech match    (30 pts): overlap with DigitalBuilders core stack
     *  - Intent signal (20 pts): urgency keywords (urgent, ASAP, immediately, this week)
     *  - Contactability (10 pts): verified email or phone present
     */
    public function scoreRelevance(string $text, ?string $budget, ?string $phone, ?string $email): int
    {
        $normalized = strtolower($text);

        // Hard Penalty: Reject full-time corporate employment signals
        if (preg_match('/\b(w2 only|full time employee|permanent role|401k|healthcare benefits|dental benefits|relocation assistance|on-site only)\b/i', $normalized)) {
            return 20;
        }

        // Hard Penalty: Reject non-contract / permanent full-time postings
        if (preg_match('/\b(full-time|full time)\b/i', $normalized) && !preg_match('/\b(contract|freelance|consultant|project|mvp|part-time)\b/i', $normalized)) {
            return 25;
        }

        $score = 0;

        // Factor 1: Budget tier (max 40 pts)
        if (!empty($budget) && $budget !== 'N/A') {
            preg_match_all('/\$([0-9,]+(?:\.[0-9]{2})?)\s*(?:k\b)?/i', $budget, $amounts);
            $parsedAmounts = array_map(fn($v) => (float) str_replace(',', '', $v) * (stripos($budget, 'k') !== false && (float) str_replace(',', '', $v) < 1000 ? 1000 : 1), $amounts[1] ?? []);
            $maxAmount = !empty($parsedAmounts) ? max($parsedAmounts) : 0;
            if ($maxAmount >= 10000)     $score += 40;
            elseif ($maxAmount >= 5000)  $score += 30;
            elseif ($maxAmount >= 2000)  $score += 20;
            elseif ($maxAmount > 0)      $score += 10;
            else                         $score += 5; // Budget exists but unparseable
        }

        // Factor 2: Tech stack match (max 30 pts)
        $techHits = 0;
        $coreStack = ['vue', 'react', 'next\.js', 'nuxt', 'laravel', 'php', 'node', 'typescript', 'python', 'fastapi', 'django', 'tailwind', 'postgres', 'postgresql', 'redis', 'pwa', 'ai agent', 'llm', 'flutter', 'mobile app', 'react native'];
        foreach ($coreStack as $tech) {
            if (preg_match('/\b' . $tech . '\b/i', $normalized)) {
                $techHits++;
            }
        }
        $score += min(30, $techHits * 8);

        // Factor 3: High-intent contract/urgency signals (max 20 pts)
        $intentScore = 0;
        if (preg_match('/\b(contract|contractor|freelance|freelancer|fixed price|milestone|mvp|project-based|seeking developer|hire developer|build mvp|looking for agency|looking for developer|rfp)\b/i', $normalized)) {
            $intentScore += 12;
        }
        if (preg_match('/\b(urgent|asap|immediately|this week|need now|quickly|fast|deadline|launch)\b/i', $normalized)) {
            $intentScore += 8;
        }
        $score += min(20, $intentScore);

        // Factor 4: Contactability (max 10 pts)
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $score += 10;
        } elseif (!empty($phone)) {
            $score += 5;
        }

        // Bonus: Detailed scope description
        $len = strlen(trim($text));
        if ($len > 300) $score += 3;
        if ($len > 800) $score += 2;

        return min($score, 99);
    }

    /**
     * Build proof-of-work context string by mapping detected tech tags to live DigitalBuilders case studies.
     * This is injected into AI pitch prompts to produce hyper-personalized, evidence-backed proposals.
     */
    public function buildProofOfWorkContext(array $techTags): string
    {
        $tagStr = implode(' ', $techTags);

        // Python/AI/FastAPI/Concurrency → Habuilt case study
        if (preg_match('/\b(python|fastapi|ai|llm|concurrency|redis|scalab|high.traffic|websocket)\b/i', $tagStr)) {
            $case = self::CASE_STUDIES['saas_ai'];
            return "PROOF: We built {$case['client']} — {$case['metric']} ({$case['proof_url']})";
        }

        // Vue/Laravel/ERP/PWA/Manufacturing → Garg Enterprises
        if (preg_match('/\b(laravel|vue|erp|pwa|inventory|barcode|dispatch|manufacturing|offline)\b/i', $tagStr)) {
            $case = self::CASE_STUDIES['manufacturing'];
            return "PROOF: We built {$case['client']} — {$case['metric']} ({$case['proof_url']})";
        }

        // EdTech/WhatsApp Bot/Tutoring → ZoetiCoach
        if (preg_match('/\b(edtech|education|tutor|whatsapp|bot|student|coaching|lms|automation)\b/i', $tagStr)) {
            $case = self::CASE_STUDIES['edtech'];
            return "PROOF: We built {$case['client']} — {$case['metric']} ({$case['proof_url']})";
        }

        // E-commerce/Stripe/Next.js → MyAstrova
        if (preg_match('/\b(ecommerce|stripe|shopify|next\.js|checkout|payment|d2c|store|catalog)\b/i', $tagStr)) {
            $case = self::CASE_STUDIES['ecommerce'];
            return "PROOF: We built {$case['client']} — {$case['metric']} ({$case['proof_url']})";
        }

        // Default general case
        $case = self::CASE_STUDIES['general'];
        return "PROOF: {$case['hook']} ({$case['proof_url']})";
    }


    /**
     * Generate an AI-powered proposal and outreach package using OpenAI GPT-4o-mini,
     * falling back to local heuristic generation if the API call fails.
     */
    public function generateAiPitch(
        string $requirementText,
        ?string $contactName = null,
        ?string $company = null,
        ?string $source = null,
        ?string $currency = 'USD',
        ?string $budget = null
    ): array {
        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');

        // If no OpenAI API key, fallback immediately
        if (empty($apiKey)) {
            return $this->generatePitch($requirementText, $contactName, $company, $currency, $budget);
        }

        try {
            // Build dynamic proof-of-work context from the requirement text
            $founderName = config('crm.founder_name', 'Founder');
            $founderTitle = config('crm.founder_title', 'Founder & Lead Software Architect');
            $companyName = config('crm.company_name', config('app.name', 'DigitalBuilders'));
            $websiteUrl = rtrim((string) config('crm.website_url', config('app.url', 'https://www.digitalbuilders.in')), '/');
            $estimatorUrl = config('crm.estimator_url', $websiteUrl . '/estimator');
            $bookingUrl = config('crm.booking_url', $websiteUrl . '/book');

            $scraper       = app(\App\Services\SalesFunnel\InternationalLeadScraperService::class);
            $detectedTags  = $scraper->extractTechTags($requirementText);
            $proofContext  = $this->buildProofOfWorkContext($detectedTags);

            $prompt = <<<PROMPT
You are {$founderName}, {$founderTitle} at {$companyName} ({$websiteUrl}).
{$companyName} is a boutique software engineering studio that builds high-performance web applications, SaaS MVPs, custom portals, and AI agent integrations for US/EU startups and founders in 4-6 week fixed-price sprints.

{$proofContext}

Analyze this client project requirement / RFP:
Context:
- Client / Contact Name: {$contactName}
- Company: {$company}
- Source Platform: {$source}
- Budget Hint: {$budget}
- Requirement Text:
"{$requirementText}"

Respond ONLY with a valid JSON object matching this exact schema:
{
  "is_relevant": true,
  "relevance_score": 85,
  "matched_segment": "saas_ai",
  "detected_tech_stack": ["string"],
  "client_pain_points": ["string"],
  "suggested_architecture": "string (1-2 sentences)",
  "estimated_budget_usd": 6500,
  "budget_range": "$5,000 – $8,000",
  "timeline": "4 to 6 weeks",
  "upwork_proposal": "Winning, conversational, hook-first proposal under 160 words with bullet points answering the client's core problem, mentioning the proof point above, and ending with dual CTA: (1) {$bookingUrl} (15-min calendar slot) and (2) {$estimatorUrl} (instant sprint estimator). Signed off by {$founderName}.",
  "cold_email_subject": "Punchy, personalized subject line for cold email",
  "cold_email_body": "3-paragraph authoritative executive cold email to founder/stakeholder with the same dual CTAs and citing the specific proof point.",
  "linkedin_dm": "Under 280-character high-value LinkedIn connection note or message."
}
PROMPT;

            $response = Http::timeout(12)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => config('services.openai.model', 'gpt-4o-mini'),
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => "You are an elite software sales architect at {$companyName}. Output only valid JSON without markdown fences."
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.4,
                    'max_tokens'  => 1200,
                ]);

            if ($response->successful()) {
                $rawContent = trim($response->json('choices.0.message.content') ?? '');
                // Clean potential markdown backticks
                $cleanedJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawContent);
                $decoded = json_decode($cleanedJson, true);

                if (is_array($decoded) && !empty($decoded['upwork_proposal'])) {
                    $segment = $decoded['matched_segment'] ?? $this->classifySegment($requirementText);
                    return [
                        'segment'                => $segment,
                        'relevance_score'        => (int) ($decoded['relevance_score'] ?? 80),
                        'detected_tech_stack'    => $decoded['detected_tech_stack'] ?? [],
                        'client_pain_points'     => $decoded['client_pain_points'] ?? [],
                        'suggested_architecture' => $decoded['suggested_architecture'] ?? 'Custom Full-Stack Architecture on Vue 3 & Laravel.',
                        'short_pitch'            => $decoded['upwork_proposal'],
                        'upwork_proposal'        => $decoded['upwork_proposal'],
                        'email_pitch'            => $decoded['cold_email_body'] ?? '',
                        'email_subject'          => $decoded['cold_email_subject'] ?? 'Technical Proposal for ' . ($company ?: 'Your Project'),
                        'linkedin_dm'            => $decoded['linkedin_dm'] ?? '',
                        'reddit_dm'              => $decoded['linkedin_dm'] ?? '',
                        'budget_range'           => $decoded['budget_range'] ?? '$5,000 – $10,000',
                        'estimated_amount'       => isset($decoded['estimated_budget_usd']) ? (float) $decoded['estimated_budget_usd'] : null,
                        'timeline'               => $decoded['timeline'] ?? '4 to 6 weeks',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI pitch generation failed, falling back to local generator: ' . $e->getMessage());
        }

        // Graceful fallback to local generator
        return $this->generatePitch($requirementText, $contactName, $company, $currency, $budget);
    }

    /**
     * Local deterministic proposal generator (offline / fallback mode).
     */
    public function generatePitch(
        string $requirementText,
        ?string $contactName = null,
        ?string $company = null,
        ?string $currency = 'USD',
        ?string $budget = null
    ): array {
        $segment = $this->classifySegment($requirementText);
        $case = self::CASE_STUDIES[$segment] ?? self::CASE_STUDIES['general'];

        $founderName = config('crm.founder_name', 'Founder');
        $founderTitle = config('crm.founder_title', 'Lead Architect');
        $companyName = config('crm.company_name', config('app.name', 'DigitalBuilders'));
        $websiteUrl = rtrim((string) config('crm.website_url', config('app.url', 'https://www.digitalbuilders.in')), '/');
        $estimatorUrl = config('crm.estimator_url', $websiteUrl . '/estimator');
        $bookingUrl = config('crm.booking_url', $websiteUrl . '/book');
        $phone = config('crm.founder_phone', '+91 90870 21592');

        $greetingName = !empty($contactName) ? trim($contactName) : 'there';
        $companyContext = !empty($company) ? " for {$company}" : '';
        $isUsd = strtoupper((string) $currency) === 'USD';

        // Est. timeline and budget band
        $timeline = '4 to 6 weeks';
        $budgetRange = $isUsd ? '$4,500 – $8,500' : '₹1.5L – ₹3.5L';
        $estimatedAmount = $isUsd ? 6500.00 : 185000.00;

        // 1. Upwork / International RFP Proposal (Under 160 words, punchy, Dual CTA)
        $upworkProposal = "Hi {$greetingName},\n\n"
            . "I'm {$founderName}, {$founderTitle} at {$companyName} ({$websiteUrl}). Saw your requirement{$companyContext}.\n\n"
            . "Here is how we recently solved this exact challenge:\n"
            . "• Client: {$case['client']} ({$case['location']})\n"
            . "• Solution: {$case['solution']}\n"
            . "• Verified Result: {$case['metric']}\n"
            . "• Case Proof: {$case['proof_url']}\n\n"
            . "For your scope, we can engineer and ship Phase 1 in {$timeline} ({$budgetRange} fixed-price milestone with 100% source code ownership and 60 days warranty included).\n\n"
            . "Next Steps & Dual CTA:\n"
            . "1. Pick a 15-min technical discovery slot (auto-converts to your timezone): {$bookingUrl}\n"
            . "2. Or calculate your exact feature scope and sprint estimate instantly: {$estimatorUrl}\n\n"
            . "Happy to answer any technical questions right here.\n"
            . "Best,\n{$founderName} | {$founderTitle}, {$companyName}";

        // 2. Email Pitch (Detailed, structured, authoritative, Dual CTA)
        $subject = "Architecture & Timeline Proposal for {$greetingName}" . ($company ? " — {$company}" : '');
        $emailPitch = "Hi {$greetingName},\n\n"
            . "I hope you are doing well.\n\n"
            . "My name is {$founderName} — I'm the {$founderTitle} at {$companyName} ({$websiteUrl}). We design and engineer scalable web platforms, SaaS MVPs, and custom business portals for high-growth tech startups.\n\n"
            . "I came across your requirement and wanted to reach out directly with our proven technical approach:\n\n"
            . "### How We Solved This Recently:\n"
            . "• Client: {$case['client']} ({$case['location']})\n"
            . "• Challenge: {$case['challenge']}\n"
            . "• Engineered Solution: {$case['solution']}\n"
            . "• Verified Outcome: {$case['metric']}\n"
            . "• Case Study Link: {$case['proof_url']}\n\n"
            . "### Proposed Timeline & Delivery Terms:\n"
            . "• Phase 1 MVP Delivery: {$timeline}\n"
            . "• Estimated Investment Band: {$budgetRange} (Fixed Milestone Pricing)\n"
            . "• IP & Code Ownership: 100% transferred to you upon completion\n"
            . "• Post-Launch Warranty: 60 days dedicated bug-fix and deployment support included\n\n"
            . "### Next Steps:\n"
            . "1. Book a 15-minute technical discovery session directly on my calendar: {$bookingUrl}\n"
            . "2. Or configure your feature scope and generate an instant PDF sprint breakdown: {$estimatorUrl}\n\n"
            . "Looking forward to building together.\n\n"
            . "Best regards,\n"
            . "{$founderName}\n"
            . "{$founderTitle} — {$companyName}\n"
            . "Direct: {$websiteUrl}\n"
            . "Direct Phone / WhatsApp: {$phone}";

        // 3. LinkedIn / X DM
        $linkedinDm = "Hey {$greetingName} — saw your project post{$companyContext}. I'm {$founderName}, {$founderTitle} at {$companyName}. We specialize in taking SaaS MVPs and custom web portals from spec to production in {$timeline} with 100% code ownership. Check our work and book a 15-min chat: {$bookingUrl}";

        // 4. Reddit DM (with Dual CTA)
        $redditDm = "Hey {$greetingName} — saw your project post{$companyContext}. I'm {$founderName}, {$founderTitle} at {$companyName}. We specialize in taking SaaS MVPs and custom web portals from spec to production in {$timeline} with 100% code ownership. Check our work and book a 15-min chat: {$bookingUrl} or calculate your sprint scope: {$estimatorUrl}";

        return [
            'segment'                => $segment,
            'case_study'             => $case['client'],
            'short_pitch'            => $upworkProposal,
            'upwork_proposal'        => $upworkProposal,
            'email_pitch'            => $emailPitch,
            'email_subject'          => $subject,
            'linkedin_dm'            => $linkedinDm,
            'reddit_dm'              => $redditDm,
            'budget_range'           => $budgetRange,
            'estimated_amount'       => $estimatedAmount,
            'timeline'               => $timeline,
            'detected_tech_stack'    => ['Vue 3 / React', 'Laravel / Node.js', 'PostgreSQL'],
            'client_pain_points'     => ['Needs reliable fixed-price sprint delivery', 'Requires full code ownership & post-launch warranty'],
            'suggested_architecture' => 'Modular full-stack architecture with REST/GraphQL APIs, reactive UI, and automated CI/CD deployment.',
        ];
    }

    /**
     * Generate a 4-step B2B outbound cadence personalized for a lead.
     *
     * Cadence:
     * - Step 1 (Day 0): Relevant Tech Pitch + Case Study Proof + Dual CTA
     * - Step 2 (Day 3): Technical Architecture Value Drop & Pitfall Prevention
     * - Step 3 (Day 7): Executive Check-In / Quick Bump
     * - Step 4 (Day 11): Graceful Breakup & Open Door
     *
     * @return array<int, array{step_number: int, delay_days: int, title: string, subject: string, body_text: string}>
     */
    public function generateFullCadence(\App\Models\Lead $lead): array
    {
        $contactName = trim((string) ($lead->name ?: 'there'));
        $firstName = explode(' ', $contactName)[0];
        $company = trim((string) ($lead->company ?: ''));
        $companyContext = !empty($company) ? " at {$company}" : '';
        $fullText = ($lead->description ?: '') . ' ' . ($lead->ai_summary ?: '') . ' ' . ($lead->project_type ?: '');

        $segment = $this->classifySegment($fullText);
        $case = self::CASE_STUDIES[$segment] ?? self::CASE_STUDIES['general'];

        $scraper = app(\App\Services\SalesFunnel\InternationalLeadScraperService::class);
        $detectedTags = $scraper->extractTechTags($fullText);
        $techSummary = !empty($detectedTags) ? implode(', ', array_slice($detectedTags, 0, 3)) : 'scalable web architecture';

        $founderName = config('crm.founder_name', 'Founder');
        $founderTitle = config('crm.founder_title', 'Lead Architect');
        $companyName = config('crm.company_name', config('app.name', 'DigitalBuilders'));
        $websiteUrl = rtrim((string) config('crm.website_url', config('app.url', 'https://www.digitalbuilders.in')), '/');
        $estimatorUrl = config('crm.estimator_url', $websiteUrl . '/estimator');
        $bookingUrl = config('crm.booking_url', $websiteUrl . '/book');
        $phone = config('crm.founder_phone', '+91 90870 21592');

        // Check if OpenAI is available for customized dynamic generation
        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        if (!empty($apiKey)) {
            try {
                $proofContext = $this->buildProofOfWorkContext($detectedTags);
                $prompt = <<<PROMPT
You are {$founderName}, {$founderTitle} at {$companyName} ({$websiteUrl}).
We build production web platforms, SaaS MVPs, and AI integrations in 4-6 week fixed-price sprints.

{$proofContext}

Generate a 4-step B2B cold email outbound cadence for this lead:
- Recipient Name: {$contactName} (First Name: {$firstName})
- Company: {$company}
- Project Context: {$fullText}
- Tech Stack: {$techSummary}

Return ONLY a JSON array of exactly 4 objects matching this format:
[
  {
    "step_number": 1,
    "delay_days": 0,
    "title": "Touch 1: Pitch & Case Study",
    "subject": "Punchy subject line referencing their tech or company",
    "body_text": "High-conviction intro under 130 words citing our case study proof and ending with calendar CTA {$bookingUrl}"
  },
  {
    "step_number": 2,
    "delay_days": 3,
    "title": "Touch 2: Technical Value Drop",
    "subject": "Re: [same subject as step 1]",
    "body_text": "Architectural advice under 110 words sharing a specific recommendation for scaling {$techSummary} and avoiding tech debt"
  },
  {
    "step_number": 3,
    "delay_days": 7,
    "title": "Touch 3: Executive Bump",
    "subject": "Re: [same subject as step 1]",
    "body_text": "Short 3-sentence check-in asking if they resolved engineering bandwidth for {$company}"
  },
  {
    "step_number": 4,
    "delay_days": 11,
    "title": "Touch 4: Graceful Breakup",
    "subject": "Re: [same subject as step 1]",
    "body_text": "Polite breakup email closing loop for now but leaving the door open"
  }
]
PROMPT;

                $response = Http::timeout(25)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => 'gpt-4o-mini',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'You are an elite B2B sales development copywriter for a high-end engineering studio.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.4,
                ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    $cleanJson = trim((string) preg_replace('/^```(?:json)?|```$/im', '', (string) $content));
                    $decoded = json_decode($cleanJson, true);
                    if (is_array($decoded) && count($decoded) === 4) {
                        return $decoded;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI full cadence generation failed, falling back to local generator: ' . $e->getMessage());
            }
        }

        // High-conviction deterministic 4-step sequence fallback
        $baseSubject = !empty($company)
            ? "Quick idea on {$company}'s {$techSummary} architecture"
            : "Idea on your {$techSummary} architecture";

        return [
            [
                'step_number' => 1,
                'delay_days'  => 0,
                'title'       => 'Touch 1: Pitch & Case Study',
                'subject'     => $baseSubject,
                'body_text'   => "Hi {$firstName},\n\n"
                    . "I saw your requirements{$companyContext} around {$techSummary}.\n\n"
                    . "I'm {$founderName}, {$founderTitle} at {$companyName} ({$websiteUrl}). We engineer high-performance web platforms and MVPs in 4–6 week fixed-price sprints.\n\n"
                    . "Recently, we solved a very similar architecture challenge for {$case['client']} — {$case['metric']}.\n\n"
                    . "Would you be open to a 15-minute technical discovery call this week to review your architecture and sprint roadmap?\n\n"
                    . "👉 Pick a time that fits your timezone: {$bookingUrl}\n"
                    . "Or scope your sprint budget instantly: {$estimatorUrl}\n\n"
                    . "Best regards,\n{$founderName} | {$founderTitle}, {$companyName}\n{$phone}",
            ],
            [
                'step_number' => 2,
                'delay_days'  => 3,
                'title'       => 'Touch 2: Technical Architecture Value Drop',
                'subject'     => "Re: {$baseSubject}",
                'body_text'   => "Hi {$firstName},\n\n"
                    . "Following up on my note below with a quick technical takeaway from our work on {$techSummary}:\n\n"
                    . "When scaling {$techSummary} under production load, teams frequently run into database bottlenecking and un-indexed relation queries. At {$case['client']}, we implemented {$case['solution']}, which delivered {$case['metric']}.\n\n"
                    . "If you'd like me to do a quick 10-minute audit of your architecture or spec before you begin building, I'd be happy to share notes:\n"
                    . "{$bookingUrl}\n\n"
                    . "Best,\n{$founderName}",
            ],
            [
                'step_number' => 3,
                'delay_days'  => 7,
                'title'       => 'Touch 3: Executive Check-in / Quick Bump',
                'subject'     => "Re: {$baseSubject}",
                'body_text'   => "Hi {$firstName},\n\n"
                    . "Quick check-in — wanted to see if you have already lined up engineering resources for this{$companyContext}, or if you're still evaluating technical partners?\n\n"
                    . "We have an engineering sprint opening starting next week and can ship Phase 1 with 100% code ownership and a 60-day warranty.\n\n"
                    . "Let me know if a 10-min chat makes sense: {$bookingUrl}\n\n"
                    . "Best,\n{$founderName}",
            ],
            [
                'step_number' => 4,
                'delay_days'  => 11,
                'title'       => 'Touch 4: Graceful Breakup & Open Door',
                'subject'     => "Re: {$baseSubject}",
                'body_text'   => "Hi {$firstName},\n\n"
                    . "I'm guessing your priorities may have shifted or you're already sorted on engineering for {$company}, so I won't follow up further.\n\n"
                    . "If you ever need senior sprint execution, custom MVP delivery, or an architectural second opinion down the road, please feel free to reach out anytime.\n\n"
                    . "Wishing you great success with the platform.\n\n"
                    . "Best regards,\n{$founderName}\n{$founderTitle}, {$companyName}\n{$websiteUrl}",
            ],
        ];
    }
}
