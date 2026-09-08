<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

class AiPitchGeneratorService
{
    /**
     * Case studies corpus directly sourced from live production portfolio.
     */
    private const CASE_STUDIES = [
        'manufacturing' => [
            'client'       => 'Garg Enterprises',
            'location'     => 'Ludhiana, Punjab',
            'industry'     => 'Packaging & Industrial Manufacturing',
            'challenge'    => 'Manual paper challans, 14% dispatch error rate, and zero inventory synchronization with Tally ERP.',
            'solution'     => 'Built a rugged, offline-first mobile dispatch & barcode scanner PWA with bidirectional Tally accounting sync.',
            'metric'       => 'Dispatch error rate dropped from 14% to 0.0%, saving ₹18 Lakhs annually in misrouted freight.',
            'proof_url'    => 'https://www.digitalbuilders.in/portfolio/garg-enterprises',
            'hook'         => 'We built an offline-first barcode dispatch and Tally sync system for Garg Enterprises in Ludhiana, cutting dispatch errors from 14% to zero.',
        ],
        'edtech' => [
            'client'       => 'ZoetiCoach',
            'location'     => 'India / Singapore',
            'industry'     => 'Education Technology & Academic Coaching',
            'challenge'    => 'Tutors lost 25% of student renewals due to poor homework accountability and manual tracking.',
            'solution'     => 'Architected automated WhatsApp homework bot, parent progress dashboards, and instant submission grading.',
            'metric'       => 'Supports 400+ active online educators with 99.8% WhatsApp message delivery and 42% higher retention.',
            'proof_url'    => 'https://www.digitalbuilders.in/portfolio/zoeticoach',
            'hook'         => 'We built the student accountability and WhatsApp automation infrastructure for ZoetiCoach, serving 400+ tutors with 99.8% message delivery.',
        ],
        'ecommerce' => [
            'client'       => 'MyAstrova Mall',
            'location'     => 'New Delhi, India',
            'industry'     => 'D2C Consumer Goods & Spiritual Commerce',
            'challenge'    => 'Frequent checkout abandonment, slow catalog filtering, and fragile payment gateway webhooks under traffic surges.',
            'solution'     => 'Engineered an ultra-fast Next.js store with Razorpay UPI/Card checkout, dynamic inventory reserve, and automated WhatsApp order notifications.',
            'metric'       => 'Handles thousands of concurrent checkouts with zero double-billing and sub-800ms page transitions.',
            'proof_url'    => 'https://www.digitalbuilders.in/portfolio/myastrova',
            'hook'         => 'We engineered MyAstrova’s high-traffic e-commerce store with instant Razorpay checkout, sub-second product filters, and real-time WhatsApp order tracking.',
        ],
        'saas_ai' => [
            'client'       => 'Habuilt',
            'location'     => 'India & Global',
            'industry'     => 'Habit Building & Live Telehealth Streaming',
            'challenge'    => 'Spikes of 65,000 concurrent morning attendees crashing legacy database servers and WebSocket channels.',
            'solution'     => 'Engineered high-concurrency Redis caching layer, horizontally scalable backend, and resilient real-time attendance pipelines.',
            'metric'       => '99.99% system uptime through live 65k concurrent participant sessions every single morning.',
            'proof_url'    => 'https://www.digitalbuilders.in/portfolio/habuilt',
            'hook'         => 'We built the high-concurrency backend for Habuilt, scaling live sessions to 65,000 concurrent daily active participants with 99.99% uptime.',
        ],
        'general' => [
            'client'       => 'DigitalBuilders Production Systems',
            'location'     => 'India & International',
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

        if (preg_match('/\b(saas|ai|machine learning|automation|portal|dashboard|bot|api|workflow|mvp|concurrency|scalab|cloud|flutter|react native)\b/i', $normalized)) {
            return 'saas_ai';
        }

        return 'general';
    }

    /**
     * Calculate an intent / relevance score (0 - 100).
     */
    public function scoreRelevance(string $text, ?string $budget, ?string $phone, ?string $email): int
    {
        $score = 50;

        // Has contact information
        if (!empty($phone)) $score += 15;
        if (!empty($email)) $score += 10;

        // Has explicit budget mentioned
        if (!empty($budget) && $budget !== 'N/A') {
            $score += 10;
        }

        // Detailed scope text
        $length = strlen(trim($text));
        if ($length > 60) $score += 10;
        if ($length > 150) $score += 5;

        // Specific technical keywords indicate high buying readiness
        if (preg_match('/\b(urgently|immediate|quote|timeline|budget|proposal|need developer|hire|fixed price)\b/i', $text)) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Generate a personalized, human founder pitch tailored to the requirement.
     */
    public function generatePitch(
        string $requirementText,
        ?string $contactName = null,
        ?string $company = null,
        ?string $currency = 'INR',
        ?string $budget = null
    ): array {
        $segment = $this->classifySegment($requirementText);
        $case = self::CASE_STUDIES[$segment] ?? self::CASE_STUDIES['general'];

        $greetingName = !empty($contactName) ? trim($contactName) : 'there';
        $companyContext = !empty($company) ? " for {$company}" : '';
        $isUsd = strtoupper((string) $currency) === 'USD';

        // Est. timeline and budget band
        $timeline = '4 to 6 weeks';
        $budgetRange = $isUsd ? '$4,500 – $8,500' : '₹1.5L – ₹3.5L';

        // 1. WhatsApp / Short Pitch (Punchy, 4-5 sentences, human, no fluff)
        $shortPitch = "Hi {$greetingName},\n\n"
            . "I'm Ashish, founder and lead architect at DigitalBuilders. Saw your requirement{$companyContext}.\n\n"
            . "{$case['hook']}\n\n"
            . "For your scope, we can ship a production Phase 1 in {$timeline} (typically {$budgetRange}, fixed price with 100% source code ownership and 60 days warranty).\n\n"
            . "Would you like to see a live architecture diagram or 5-min demo of how we built this?\n\n"
            . "You can pick a 15-min slot directly on my calendar: https://www.digitalbuilders.in/book\n"
            . "Or feel free to reply right here with your preferred time.";

        // 2. Email Pitch (Detailed, structured, authoritative)
        $subject = "Architecture & Timeline Proposal for {$greetingName}" . ($company ? " — {$company}" : '');
        $emailPitch = "Hi {$greetingName},\n\n"
            . "I hope you are doing well.\n\n"
            . "My name is Ashish Gupta — I'm the lead engineer and founder at DigitalBuilders (https://www.digitalbuilders.in). We design and engineer scalable web platforms, mobile apps, and custom business ERPs for high-growth businesses.\n\n"
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
            . "If this aligns with your vision, you can book a 15-minute technical discovery session directly on my calendar here: https://www.digitalbuilders.in/book\n\n"
            . "Looking forward to building together.\n\n"
            . "Best regards,\n"
            . "Ashish Gupta\n"
            . "Founder & Lead Architect — DigitalBuilders\n"
            . "Direct: +91 90870 21592 | https://www.digitalbuilders.in";

        return [
            'segment'      => $segment,
            'case_study'   => $case['client'],
            'short_pitch'  => $shortPitch,
            'email_pitch'  => $emailPitch,
            'email_subject'=> $subject,
            'budget_range' => $budgetRange,
            'timeline'     => $timeline,
        ];
    }
}
