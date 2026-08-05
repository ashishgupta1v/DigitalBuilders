<?php

declare(strict_types=1);

namespace App\Services;

class LeadScoringService
{
    private const PUBLIC_EMAIL_DOMAINS = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com', 'aol.com', 'mail.ru', 'protonmail.com'
    ];

    /**
     * Compute a 0–100 quality score for a lead.
     *
     * @param array<string, mixed> $leadData
     * @return int Score between 0 and 100
     */
    public function calculateScore(array $leadData): int
    {
        $score = 0;

        // 1. Corporate Email Domain Analysis (+25 pts)
        $email = $leadData['email'] ?? '';
        if ($email && str_contains($email, '@')) {
            $domain = strtolower(explode('@', $email)[1] ?? '');
            if (! in_array($domain, self::PUBLIC_EMAIL_DOMAINS, true)) {
                $score += 25; // Corporate domain indicator
            } else {
                $score += 10;
            }
        }

        // 2. Project Type Value (+25 pts max)
        $projectType = $leadData['project_type'] ?? '';
        $score += match ($projectType) {
            'erp_crm', 'Enterprise ERP / CRM' => 25,
            'saas', 'SaaS Platform Architecture' => 25,
            'ai_solutions', 'AI Voice Agents and Chatbots' => 22,
            'mobile_app', 'Mobile App Development (iOS and Android)' => 20,
            'web_app', 'Custom Web Application Development' => 18,
            default => 15,
        };

        // 3. Pre-Calculated Budget/Estimate Indicator (+25 pts)
        $description = $leadData['description'] ?? '';
        if (str_contains($description, 'Estimated Budget:') || str_contains($description, '₹') || str_contains($description, '$')) {
            $score += 25;
        } elseif (strlen($description) > 100) {
            $score += 15;
        }

        // 4. Phone Number & Inquiry Completeness (+25 pts)
        $phone = trim($leadData['phone'] ?? '');
        if (! empty($phone) && strlen($phone) >= 8) {
            $score += 15;
        }
        if (! empty($leadData['name']) && str_contains(trim($leadData['name']), ' ')) {
            $score += 10; // Full name provided
        }

        return min(100, max(15, $score));
    }
}
