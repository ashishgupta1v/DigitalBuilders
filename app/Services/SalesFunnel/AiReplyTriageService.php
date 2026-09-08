<?php

declare(strict_types=1);

namespace App\Services\SalesFunnel;

class AiReplyTriageService
{
    /**
     * Triage an incoming reply and draft a context-aware response.
     */
    public function triageReply(string $incomingMessage, string $clientName, string $dealTitle): array
    {
        $normalized = strtolower($incomingMessage);

        // 1. Objection: Budget / Price Too High
        if (preg_match('/\b(expensive|budget is lower|high cost|price|discount|cheaper|too much|negotiate)\b/i', $normalized)) {
            return [
                'intent'   => 'price_objection',
                'action'   => 'counter_scope',
                'response' => "Hi {$clientName},\n\nUnderstood on the budget. We don't cut corners on architecture or security, but we frequently phase scopes so you can launch fast without heavy upfront capital.\n\nWe can deliver Phase 1 core MVP first (essential workflows only), get your initial users onboarded, and fund Phase 2 from revenue.\n\nLet's jump on a quick 10-minute call to trim the non-essential modules: https://www.digitalbuilders.in/book",
            ];
        }

        // 2. Objection: Timeline / Urgency
        if (preg_match('/\b(too long|faster|urgent|asap|earlier|weeks|quick|immediate)\b/i', $normalized)) {
            return [
                'intent'   => 'timeline_urgency',
                'action'   => 'sprint_acceleration',
                'response' => "Hi {$clientName},\n\nWe can accelerate. Because we build on battle-tested architectural boilerplates (pre-wired auth, database migrations, CI/CD, and payment gateways), we can compress Phase 1 into a dedicated 3-week rapid sprint.\n\nWhen is your hard launch deadline? Let's align on what must go live on Day 1: https://www.digitalbuilders.in/book",
            ];
        }

        // 3. Positive: Ready for Call / Meeting
        if (preg_match('/\b(call|meet|zoom|google meet|discuss|talk|available|time|schedule)\b/i', $normalized)) {
            return [
                'intent'   => 'meeting_request',
                'action'   => 'calendar_link',
                'response' => "Hi {$clientName},\n\nFantastic! You can pick a 15-min or 30-min slot that fits your schedule directly on my calendar here:\n\n👉 https://www.digitalbuilders.in/book\n\nLooking forward to speaking and reviewing your architecture.",
            ];
        }

        // 4. Inquisitive: Requesting Portfolio / Live Demos
        if (preg_match('/\b(portfolio|sample|demo|work|experience|case study|reference)\b/i', $normalized)) {
            return [
                'intent'   => 'portfolio_request',
                'action'   => 'portfolio_links',
                'response' => "Hi {$clientName},\n\nHere are three of our live production systems relevant to your scope:\n\n1. Garg Enterprises (Industrial ERP & Barcode Dispatch): https://www.digitalbuilders.in/portfolio/garg-enterprises\n2. Habuilt (Live Habit Platform, 65k concurrent users): https://www.digitalbuilders.in/portfolio/habuilt\n3. ZoetiCoach (EdTech & WhatsApp Automation): https://www.digitalbuilders.in/portfolio/zoeticoach\n\nAll our case studies with verified architectural metrics are live at: https://www.digitalbuilders.in/#case-studies",
            ];
        }

        // 5. Negative: Not Interested / Unsubscribe
        if (preg_match('/\b(not interested|stop|unsubscribe|remove|no thanks|already hired|found someone)\b/i', $normalized)) {
            return [
                'intent'   => 'lost',
                'action'   => 'graceful_exit',
                'response' => "Understood, {$clientName}. Thanks for letting us know, and wishing you every success with your build. If you ever need an architecture review down the road, our engineering team is here at digitalbuilders.in.",
            ];
        }

        // Default: General Technical Inquiry
        return [
            'intent'   => 'general_inquiry',
            'action'   => 'clarify_and_call',
            'response' => "Hi {$clientName},\n\nThanks for your note regarding {$dealTitle}. Yes, we can accommodate that seamlessly. Would 3:00 PM IST or 6:30 PM IST work better for a quick 10-minute technical sync? You can also pick a direct time slot here: https://www.digitalbuilders.in/book",
        ];
    }
}
