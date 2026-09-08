<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmAiController extends Controller
{
    public function recommendNextAction(Request $request, int $leadId): JsonResponse
    {
        $lead = Lead::with(['deals' => fn($q) => $q->latest()->limit(1), 'activities' => fn($q) => $q->latest()->limit(5)])->findOrFail($leadId);
        $deal = $lead->deals->first();
        $touchCount = (int) ($lead->touchpoint_count ?? 0);
        $segment = $lead->segment ?? 'general';

        $daysSinceContact = $lead->last_contact_date 
            ? (int) now()->diffInDays($lead->last_contact_date) 
            : (int) now()->diffInDays($lead->created_at);

        $recommendation = [
            'priority'     => 'medium',
            'suggested_touch' => min(5, $touchCount + 1),
            'action_type'  => 'whatsapp',
            'headline'     => '',
            'reasoning'    => '',
            'playbook_ref' => '',
        ];

        if ($lead->stage === 'new' || $touchCount === 0) {
            $recommendation['priority'] = 'high';
            $recommendation['suggested_touch'] = 1;
            $recommendation['headline'] = "Dispatch Touch 1 Hook ({$lead->name})";
            $recommendation['reasoning'] = "Lead is newly captured. First response within 2 hours boosts conversion rate by 391%.";
            $recommendation['playbook_ref'] = "Playbook Section 1: {$segment} hook + proof asset";
        } elseif ($lead->stage === 'proposal_sent') {
            $recommendation['priority'] = 'high';
            $recommendation['action_type'] = 'call';
            $recommendation['headline'] = 'Follow Up on Sent Scope & Proposal';
            $recommendation['reasoning'] = "Proposal sent {$deal?->formatted_amount}. Call Rajesh/founder to walk through timeline & kickoff deposit.";
            $recommendation['playbook_ref'] = 'Playbook Section 3: Objection Handling Battlecard';
        } elseif ($touchCount === 1) {
            $recommendation['headline'] = 'Send Touch 2: 2026 Price Book & Scope Estimator';
            $recommendation['reasoning'] = "Anchor value transparently. Share digitalbuilders.in/estimator so client ballparks budget.";
            $recommendation['playbook_ref'] = 'Playbook Section 2: Touchpoint 2 (Day 2)';
        } elseif ($touchCount === 2) {
            $recommendation['headline'] = 'Send Touch 3: Architecture Proof & Case Study';
            $recommendation['reasoning'] = "Build deep technical trust. Send relevant case study (e.g. Garg Enterprises / GutTalks / Habuilt).";
            $recommendation['playbook_ref'] = 'Playbook Section 2: Touchpoint 3 (Day 5)';
        } elseif ($touchCount === 3) {
            $recommendation['headline'] = 'Touch 4: Voice Note / Priority Check-in';
            $recommendation['action_type'] = 'call';
            $recommendation['reasoning'] = "Check if streamlining their dispatch/booking bottleneck is still active for this quarter.";
            $recommendation['playbook_ref'] = 'Playbook Section 2: Touchpoint 4 (Day 9)';
        } else {
            $recommendation['headline'] = 'Touch 5: Send Friendly Breakup Note';
            $recommendation['reasoning'] = "Breakup notes achieve 40%+ reply rate by removing pressure and creating scarcity.";
            $recommendation['playbook_ref'] = 'Playbook Section 2: Touchpoint 5 (Day 14)';
        }

        return response()->json([
            'success'        => true,
            'lead_id'        => $lead->id,
            'score'          => (int) ($lead->score ?? 60),
            'recommendation' => $recommendation,
        ]);
    }

    public function generateScript(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id'   => ['required', 'exists:leads,id'],
            'type'      => ['required', 'string', 'in:touchpoint,objection,custom'],
            'touch'     => ['nullable', 'integer', 'min:1', 'max:5'],
            'objection' => ['nullable', 'string'],
        ]);

        $lead = Lead::with(['organization', 'deals' => fn($q) => $q->latest()->limit(1)])->findOrFail($validated['lead_id']);
        $deal = $lead->deals->first();

        $name = trim((string) $lead->name);
        $company = $lead->company ?? $lead->organization?->name ?? 'your company';
        $phone = preg_replace('/[^0-9]/', '', (string) $lead->phone);
        if (!str_starts_with($phone, '91') && strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        $segment = $lead->segment ?? 'manufacturer';
        $type = $validated['type'];
        $touch = (int) ($validated['touch'] ?? 1);
        $objection = $validated['objection'] ?? '';

        $script = '';
        $subject = '';

        // Deterministic Battlecard & Cadence Engine (matches SALES_KIT_AND_PITCH_PLAYBOOK.md)
        if ($type === 'objection') {
            if (str_contains(strtolower($objection), 'price') || str_contains(strtolower($objection), 'expensive') || str_contains(strtolower($objection), '25000')) {
                $subject = "Addressing budget & quality guarantee";
                $script = "Hi {$name}, I completely understand budget is important.\n\nThe reality of ₹25,000 software is that it's usually built on fragile, bloated plugins with zero automated tests. Six months down the line when it crashes during a peak sale, you end up spending double to rebuild from scratch — 'sasta roye baar-baar'.\n\nWith us, you get clean, proprietary architecture personally reviewed by a 10+ year Staff Architect, complete IP ownership, and a 30-day warranty. If you want to de-risk, we can start with our ₹19,000 Architecture Discovery Sprint, which is 100% credited to the build contract once we kick off.\n\nWould you like me to reserve a 15-min discovery slot this Thursday?";
            } elseif (str_contains(strtolower($objection), 'inhouse') || str_contains(strtolower($objection), 'vendor') || str_contains(strtolower($objection), 'agency')) {
                $subject = "Collaborating alongside your internal IT / vendor";
                $script = "Hi {$name}, having internal technical help or an existing vendor is fantastic! Most of our enterprise clients actually keep their existing team focused on daily maintenance, while bringing us in as a Specialized Architecture Strike Team to design high-throughput order pipelines, ERP modules, or mobile apps in 3-4 weeks.\n\nWe don't replace your team; we give them a rock-solid, production-grade foundation with 100% automated test coverage. Would you be open to a 15-min technical review call with your team?";
            } elseif (str_contains(strtolower($objection), 'proposal') || str_contains(strtolower($objection), 'email')) {
                $subject = "Preparing fixed-price scope proposal for {$company}";
                $script = "Hi {$name}, I'd be glad to send over a written proposal! In custom software engineering, sending a quote without a 15-minute discovery call usually results in inaccurate assumptions or inflated buffer budgets.\n\nLet's do a quick 15-minute screen share to map your exact database tables and dispatch workflows. Right after the call, I'll deliver a comprehensive, fixed-scope architecture proposal with milestone deliverables within 24 hours. Does Thursday at 3 PM work for you?";
            } elseif (str_contains(strtolower($objection), 'think') || str_contains(strtolower($objection), 'later')) {
                $subject = "Follow up on your timeline";
                $script = "Hi {$name}, totally fair! Usually when founders say they need to think about it, it comes down to one of three things: price, timeline, or trust.\n\nWhich of those is the biggest open question for you right now?";
            } else {
                $subject = "Rate Card & Screen Walkthrough";
                $script = "Hi {$name}, sending across our Rate Card right now! [digitalbuilders.in/pricing].\n\nLet's jump on a quick 10-minute screen share on Thursday at 3 PM so I can show you the exact screen mockups for {$company}. Does 3 PM work, or is morning better?";
            }
        } else {
            // 5-Touch Follow-up Cadence
            if ($touch === 1) {
                $subject = "Streamlining operations for {$company}";
                if ($segment === 'manufacturer') {
                    $script = "Hi {$name}, saw you run {$company}. Are you still managing dealer dispatches on WhatsApp chats and handwritten slips? We built a 1-tap ordering app for Garg Enterprises in Ludhiana that dropped their dispatch mistakes from 14% down to zero. Would you be open to a 15-min look at how it works? (digitalbuilders.in/portfolio/garg-enterprises)";
                } elseif ($segment === 'retail') {
                    $script = "Hi {$name}, let's get {$company} taking orders online and directly on WhatsApp with instant UPI checkout—so your catalog keeps selling even when physical shutters are down. Takes 3 weeks to go live. Want to see a live demo? (digitalbuilders.in/estimator)";
                } elseif ($segment === 'clinic') {
                    $script = "Hi Dr. {$name}, we built GutTalks telehealth clinic a frictionless booking and automated WhatsApp reminder system that cut no-shows and tripled repeat consultations. Can I send you a 2-minute video walkthrough? (digitalbuilders.in/portfolio/guttalks)";
                } elseif ($segment === 'coaching') {
                    $script = "Hi {$name}, replace paper fee registers and parent enquiry diaries with a single branded mobile app for attendance, automated fee reminders on WhatsApp, and student progress reports. (digitalbuilders.in/services/mobile-apps)";
                } else {
                    $script = "Hi {$name}, I noticed you're building {$company}. As a Senior Digital Architect with 10+ years of experience, I personally architect and ship modular web applications (Laravel 13, Vue 3, PostgreSQL) with sub-100ms response times and 100% test coverage. Every project includes weekly live staging demos and a 30-day post-launch warranty. (digitalbuilders.in/pricing)";
                }
            } elseif ($touch === 2) {
                $subject = "DigitalBuilders 2026 Price Book & Scope Estimator";
                $script = "Hi {$name}, Ashish here from DigitalBuilders. Just sharing our 2026 Price Book & Scope Estimator in case you want to ballpark numbers for {$company}: https://www.digitalbuilders.in/estimator.\n\nHave 10 mins this week to review your bottleneck?";
            } elseif ($touch === 3) {
                $subject = "Architecture guide on eliminating errors";
                $script = "Hi {$name}, thought of your workflow — we just published an architecture guide on how teams are eliminating dispatch & booking errors: https://www.digitalbuilders.in/blog.\n\nWorth a quick 3-minute read.";
            } elseif ($touch === 4) {
                $subject = "Checking in on quarterly priority";
                $script = "Hi {$name}, Ashish here. Dropping a quick note to see if solving software bottlenecks for {$company} is still a priority for this quarter, or if you'd like to revisit next month?";
            } else {
                $subject = "Closing the loop on {$company}";
                $script = "Hi {$name}, haven't heard back, so I assume this isn't a priority right now. I'll take a step back so I don't clutter your inbox. If you ever want to streamline {$company}'s operations with a fixed written scope, feel free to reach back out anytime!";
            }
        }

        // Optional OpenAI enhancement if API key is present
        $apiKey = env('OPENAI_API_KEY');
        if ($apiKey && $request->boolean('use_ai', false)) {
            try {
                $res = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(8)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => "You are an elite B2B sales copywriter for DigitalBuilders software engineering studio. Refine this message to be ultra-crisp, authoritative, empathetic, and strictly under 110 words."],
                        ['role' => 'user', 'content' => "Prospect: {$name}, Company: {$company}, Segment: {$segment}. Base script:\n{$script}"],
                    ],
                    'max_tokens' => 200,
                    'temperature' => 0.5,
                ]);

                if ($res->successful()) {
                    $aiText = $res->json('choices.0.message.content');
                    if (!empty($aiText)) {
                        $script = trim($aiText);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI script enhancement failed, using playbook draft: ' . $e->getMessage());
            }
        }

        $whatsappUrl = "https://wa.me/{$phone}?text=" . rawurlencode($script);

        return response()->json([
            'success'      => true,
            'touch'        => $touch,
            'lead_id'      => $lead->id,
            'recipient'    => $name,
            'phone'        => $phone,
            'subject'      => $subject,
            'script'       => $script,
            'whatsapp_url' => $whatsappUrl,
        ]);
    }
}
