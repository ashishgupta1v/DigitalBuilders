<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Modules\Library\Application\Interfaces\MarkdownRendererInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmProposalController extends Controller
{
    public function __construct(
        private MarkdownRendererInterface $markdownRenderer,
    ) {}

    /**
     * Fetch existing proposal or dynamically generate a tailored executive proposal draft.
     */
    public function show(int $id): JsonResponse
    {
        $deal = Deal::query()->with(['lead', 'organization'])->findOrFail($id);

        $token = $deal->getOrCreateProposalToken();
        $content = $deal->proposal_content;

        if (empty($content)) {
            $content = $this->buildDefaultProposalMarkdown($deal);
        }

        return response()->json([
            'deal_id'              => $deal->id,
            'title'                => $deal->title,
            'amount'               => (float) $deal->amount,
            'formatted_amount'     => $deal->formatted_amount,
            'currency'             => $deal->currency,
            'stage'                => $deal->stage,
            'proposal_content'     => $content,
            'proposal_html'        => $this->markdownRenderer->toHtml($content),
            'proposal_token'       => $token,
            'public_url'           => url("/proposal/{$token}"),
            'proposal_sent_at'     => $deal->proposal_sent_at?->format('d M Y, h:i A'),
            'proposal_viewed_at'   => $deal->proposal_viewed_at?->format('d M Y, h:i A'),
            'proposal_accepted_at' => $deal->proposal_accepted_at?->format('d M Y, h:i A'),
            'client_name'          => $deal->lead->name ?? 'Valued Client',
            'client_phone'         => $deal->lead->phone ?? '',
            'company_name'         => $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Partner',
        ]);
    }

    /**
     * Save custom edits made to the proposal Markdown.
     */
    public function save(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'proposal_content' => ['required', 'string'],
        ]);

        $deal = Deal::query()->findOrFail($id);
        $deal->update([
            'proposal_content' => $validated['proposal_content'],
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Proposal saved successfully!',
            'proposal_html' => $this->markdownRenderer->toHtml($validated['proposal_content']),
        ]);
    }

    /**
     * Mark proposal as sent to the client, update deal stage and probability, and log activity.
     */
    public function send(Request $request, int $id): JsonResponse
    {
        $deal = Deal::query()->with('lead')->findOrFail($id);
        $token = $deal->getOrCreateProposalToken();

        $deal->update([
            'proposal_sent_at' => now(),
            'stage'            => 'proposal_sent',
            'probability'      => Deal::DEFAULT_PROBABILITIES['proposal_sent'] ?? 75,
        ]);

        if ($deal->lead) {
            $deal->lead->increment('touchpoint_count');
            $deal->lead->update([
                'last_contact_date' => now(),
                'next_action_date'  => now()->addDays(2),
                'next_action_note'  => 'Follow-up on Proposal Review (Touch 3)',
                'stage'             => 'proposal_sent',
            ]);

            Activity::create([
                'lead_id'           => $deal->lead->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'Formal Engineering Proposal Dispatched',
                'description'       => "Client proposal sent via WhatsApp/Email for {$deal->title}. Target Investment: {$deal->formatted_amount}.",
                'touchpoint_number' => (int) $deal->lead->touchpoint_count,
            ]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Proposal dispatched! Deal moved to Proposal Sent (75% probability).',
            'stage'      => 'proposal_sent',
            'public_url' => url("/proposal/{$token}"),
        ]);
    }

    /**
     * Public client view for the proposal (/proposal/{token}).
     */
    public function publicView(string $token): Response
    {
        $deal = Deal::query()
            ->with(['lead', 'organization'])
            ->where('proposal_token', $token)
            ->firstOrFail();

        // Record first viewed timestamp
        if (is_null($deal->proposal_viewed_at)) {
            $deal->update(['proposal_viewed_at' => now()]);

            if ($deal->lead) {
                Activity::create([
                    'lead_id'     => $deal->lead->id,
                    'deal_id'     => $deal->id,
                    'type'        => 'meeting',
                    'subject'     => 'Client Opened Web Proposal',
                    'description' => "Client opened the proposal portal for {$deal->title}.",
                ]);
            }
        }

        $content = $deal->proposal_content ?: $this->buildDefaultProposalMarkdown($deal);
        $html = $this->markdownRenderer->toHtml($content);

        return Inertia::render('Crm/PublicProposal', [
            'deal' => [
                'id'                   => $deal->id,
                'title'                => $deal->title,
                'amount'               => (float) $deal->amount,
                'formatted_amount'     => $deal->formatted_amount,
                'currency'             => $deal->currency,
                'stage'                => $deal->stage,
                'pricing_tier'         => $deal->pricing_tier,
                'proposal_sent_at'     => $deal->proposal_sent_at?->format('d M Y'),
                'proposal_accepted_at' => $deal->proposal_accepted_at?->format('d M Y, h:i A'),
            ],
            'lead' => [
                'name'       => $deal->lead->name ?? 'Valued Client',
                'company'    => $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Partner',
                'role_title' => $deal->lead->role_title ?? 'Decision Maker',
            ],
            'proposal' => [
                'token'   => $token,
                'content' => $content,
                'html'    => $html,
            ],
        ]);
    }

    /**
     * Client accepts the proposal via the public portal.
     */
    public function accept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $deal = Deal::query()->with('lead')->where('proposal_token', $token)->firstOrFail();

        $deal->update([
            'proposal_accepted_at' => now(),
            'stage'                => 'negotiation',
            'probability'          => 85,
        ]);

        if ($deal->lead) {
            $deal->lead->update([
                'stage'            => 'negotiation',
                'next_action_date' => now(),
                'next_action_note' => 'Prepare Kickoff Invoice / Advance Deposit',
            ]);

            Activity::create([
                'lead_id'     => $deal->lead->id,
                'deal_id'     => $deal->id,
                'type'        => 'stage_change',
                'subject'     => 'Client Accepted Proposal Online',
                'description' => "Proposal formally accepted online by {$deal->lead->name} for {$deal->title}.",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proposal accepted successfully! Our lead architect will reach out with kickoff details.',
            ]);
        }

        return back()->with('success', 'Proposal accepted successfully! We look forward to building together.');
    }

    /**
     * Synthesizes an executive Markdown proposal tailored to the deal, lead, and organization.
     */
    private function buildDefaultProposalMarkdown(Deal $deal): string
    {
        $clientName = $deal->lead->name ?? 'Valued Client';
        $companyName = $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Client';
        $roleTitle = $deal->lead->role_title ?? 'Decision Maker';
        $segment = ucwords(str_replace('_', ' ', $deal->lead->segment ?? 'Custom Solution'));
        $dateStr = now()->format('d F Y');
        $currency = $deal->currency;
        $amount = (float) $deal->amount;
        $formattedAmount = $deal->formatted_amount;

        // Commercial Milestones (40% / 40% / 20%)
        $sym = $currency === 'INR' ? '₹' : '$';
        $m1 = $sym . number_format($amount * 0.40);
        $m2 = $sym . number_format($amount * 0.40);
        $m3 = $sym . number_format($amount * 0.20);

        // Scope description lines
        $scopeNotes = $deal->scope_summary
            ? "> " . str_replace("\n", "\n> ", trim($deal->scope_summary))
            : "> Production-grade full-stack engineering, custom business logic, automated workflows, and high-performance database design.";

        return <<<MARKDOWN
# Engineering Proposal & Scope of Work

**Prepared For:** {$clientName} ({$roleTitle}) | **{$companyName}**  
**Project:** {$deal->title}  
**Date:** {$dateStr}  
**Prepared By:** Ashish Gupta, Principal Architect @ [DigitalBuilders](https://digitalbuilders.in)  
**Total Investment:** **{$formattedAmount} ({$currency})**  

---

## 1. Executive Summary & Objective

DigitalBuilders is pleased to submit this formal proposal to engineer, architect, and deploy the **{$deal->title}** for **{$companyName}**.

Our primary mission is to deliver an enterprise-grade digital solution tailored to the **{$segment}** vertical that completely eliminates operational friction, accelerates transaction speed, and gives your business an unfair competitive advantage.

{$scopeNotes}

---

## 2. Technical Architecture & Deliverables

Our engineering approach leverages high-throughput, modern application architecture with zero bloat:

- **Backend Foundation**: Laravel 12/13 High-Throughput Framework, clean Domain-Driven Design (DDD), automated background queues, and strict PostgreSQL/MySQL database modeling.
- **Client Experience**: Reactive Vue 3 + Inertia.js frontend with Tailwind CSS — delivers sub-second SPA responsiveness with seamless server hydration.
- **Automated Workflow & Messaging**: Deep WhatsApp Business API triggers, transactional email pipelines, and real-time event notifications.
- **Enterprise Security & Governance**: Multi-tenant RBAC (Role-Based Access Control), HTTPS/TLS hardening, automated daily database backups, and OWASP-compliant security protocols.

---

## 3. Commercial Investment & Milestone Schedule

Total Project Fee: **{$formattedAmount} ({$currency})**

| Milestone | Scope & Deliverables | Share | Amount |
|---|---|---|---|
| **Phase 1: Kickoff & Core Architecture** | System blueprint, database ERD, interactive UI staging prototype, and CI/CD environment setup | 40% | **{$m1}** |
| **Phase 2: Core Engineering & Modules** | Full functional development of custom business workflows, automated messaging & API integrations | 40% | **{$m2}** |
| **Phase 3: UAT, Hardening & Go-Live** | End-to-end user acceptance testing, security audit, DNS cutover, 100% code handover & team walkthrough | 20% | **{$m3}** |

*Commercial Terms: Direct Bank Wire (RTGS / NEFT / IMPS), UPI, Razorpay, or International Wire (Stripe).*

---

## 4. Guarantees & Intellectual Property

1. **100% Intellectual Property Handover**: Upon final milestone completion, all custom source code, documentation, and database schemas are 100% owned by **{$companyName}**. Zero perpetual royalties, zero vendor lock-in.
2. **30-Day Hypercare Warranty**: Dedicated zero-cost bug fixes, performance monitoring, and architecture support for 30 days post-launch.
3. **99.9% Uptime Guarantee**: Built on hardened, container-ready architecture designed for institutional reliability.

---

## 5. Acceptance & Authorization

To authorize this proposal and initiate the Phase 1 kickoff sprint, confirm acceptance via the online portal or directly via WhatsApp.

**DigitalBuilders Engineering**  
Ashish Gupta — Principal Software Architect  
Direct WhatsApp: [+91 90870 21592](https://wa.me/919087021592)  
Email: [ashish@digitalbuilders.in](mailto:ashish@digitalbuilders.in)  
Website: [https://digitalbuilders.in](https://digitalbuilders.in)
MARKDOWN;
    }
}
