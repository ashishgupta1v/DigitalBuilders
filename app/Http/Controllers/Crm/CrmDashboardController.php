<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\MarketRequirement;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $segment = $request->query('segment', 'all');
        $search = trim((string) $request->query('search', ''));
        $activeTab = $request->query('tab', 'hunter'); // 'hunter', 'deals', 'leads', 'studio'

        // 1. Calculate USD-focused Telemetry KPIs
        $openDeals = Deal::query()->whereNotIn('stage', ['closed_won', 'closed_lost']);
        $wonDeals = Deal::query()->where('stage', 'closed_won');
        $lostDealsCount = Deal::query()->where('stage', 'closed_lost')->count();
        $wonDealsCount = $wonDeals->count();

        // Standardize pipeline metrics in USD
        $totalPipelineUsd = (clone $openDeals)->where('currency', 'USD')->sum('amount');
        $wonRevenueUsd = (clone $wonDeals)->where('currency', 'USD')->sum('amount');
        
        // If INR deals exist, calculate approximate USD equivalent (85 INR = 1 USD)
        $inrOpen = (clone $openDeals)->where('currency', 'INR')->sum('amount');
        $inrWon = (clone $wonDeals)->where('currency', 'INR')->sum('amount');
        $totalPipelineUsd += round($inrOpen / 85.0);
        $wonRevenueUsd += round($inrWon / 85.0);

        $activeDealsCount = $openDeals->count();
        $closedTotal = $wonDealsCount + $lostDealsCount;
        $winRate = $closedTotal > 0 ? (int) round(($wonDealsCount / $closedTotal) * 100) : 0;
        $avgDealSize = $activeDealsCount > 0 ? round($totalPipelineUsd / $activeDealsCount) : 5500;

        $overdueCount = Lead::query()
            ->whereNotNull('next_action_date')
            ->where('next_action_date', '<=', now()->endOfDay())
            ->whereNotIn('status', ['converted', 'archived'])
            ->count();

        // 2. Daily Action Queue (High-Priority International Follow-ups)
        $actionQueue = Lead::query()
            ->with(['organization', 'deals' => fn($q) => $q->latest()->limit(1)])
            ->whereNotIn('status', ['converted', 'archived'])
            ->where(function ($query) {
                $query->where('next_action_date', '<=', now()->addHours(12))
                    ->orWhereNull('next_action_date')
                    ->orWhere('score', '>=', 70);
            })
            ->orderBy('score', 'desc')
            ->orderBy('next_action_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($lead) {
                $latestDeal = $lead->deals->first();
                return [
                    'id'               => $lead->id,
                    'name'             => $lead->name,
                    'company'          => $lead->company ?? $lead->organization?->name ?? 'Direct Client',
                    'phone'            => $lead->phone,
                    'email'            => $lead->email,
                    'segment'          => $lead->segment ?? 'general',
                    'score'            => (int) ($lead->score ?? 50),
                    'touchpoint_count' => (int) ($lead->touchpoint_count ?? 0),
                    'next_action_date' => $lead->next_action_date?->toIso8601String(),
                    'next_action_note' => $lead->next_action_note ?? 'Send Touch 1 Proposal',
                    'is_overdue'       => $lead->next_action_date && $lead->next_action_date->isPast(),
                    'deal_value'       => $latestDeal ? $latestDeal->formatted_amount : '$5,500',
                    'deal_id'          => $latestDeal?->id,
                ];
            });

        // 3. Deals Pipeline grouped by Stages
        $dealsQuery = Deal::query()
            ->with([
                'lead:id,name,email,phone,company,segment,score,touchpoint_count,next_action_date,next_action_note,status',
                'organization:id,name,city,country,industry',
            ]);

        if ($segment !== 'all') {
            $dealsQuery->whereHas('lead', function ($q) use ($segment) {
                $q->where('segment', $segment);
            });
        }

        if ($search !== '') {
            $dealsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('scope_summary', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($lq) use ($search) {
                      $lq->where('name', 'like', "%{$search}%")
                         ->orWhere('company', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $allDeals = $dealsQuery->latest('updated_at')->get();

        $stagesList = [
            'new'            => ['key' => 'new', 'name' => 'New Inbound RFP', 'color' => 'blue'],
            'contacted'      => ['key' => 'contacted', 'name' => 'Outreach Sent', 'color' => 'indigo'],
            'qualified'      => ['key' => 'qualified', 'name' => 'Qualified ($ USD)', 'color' => 'cyan'],
            'discovery_done' => ['key' => 'discovery_done', 'name' => 'Discovery Call Booked', 'color' => 'amber'],
            'proposal_sent'  => ['key' => 'proposal_sent', 'name' => 'Proposal Sent', 'color' => 'orange'],
            'negotiation'    => ['key' => 'negotiation', 'name' => 'Negotiation', 'color' => 'purple'],
            'closed_won'     => ['key' => 'closed_won', 'name' => 'Closed Won ($)', 'color' => 'emerald'],
            'closed_lost'    => ['key' => 'closed_lost', 'name' => 'Closed Lost', 'color' => 'rose'],
        ];

        $groupedDeals = [];
        foreach ($stagesList as $stageKey => $stageMeta) {
            $stageDeals = $allDeals->where('stage', $stageKey)->values();
            $groupedDeals[$stageKey] = [
                'meta'        => $stageMeta,
                'count'       => $stageDeals->count(),
                'total_usd'   => $stageDeals->sum(function ($d) {
                    return $d->currency === 'USD' ? (float) $d->amount : round((float) $d->amount / 85.0);
                }),
                'deals'       => $stageDeals->map(function ($deal) {
                    return [
                        'id'                  => $deal->id,
                        'title'               => $deal->title,
                        'amount'              => (float) $deal->amount,
                        'formatted_amount'    => $deal->formatted_amount,
                        'currency'            => $deal->currency ?: 'USD',
                        'stage'               => $deal->stage,
                        'probability'         => (int) $deal->probability,
                        'expected_close_date' => $deal->expected_close_date?->format('Y-m-d'),
                        'pricing_tier'        => $deal->pricing_tier,
                        'payment_status'      => $deal->payment_status,
                        'amount_paid'         => (float) $deal->amount_paid,
                        'formatted_paid'      => $deal->formatted_amount_paid,
                        'pending_balance'     => $deal->pending_balance,
                        'scope_summary'       => $deal->scope_summary,
                        'proposal_token'      => $deal->proposal_token,
                        'updated_at'          => $deal->updated_at->diffForHumans(),
                        'lead' => $deal->lead ? [
                            'id'               => $deal->lead->id,
                            'name'             => $deal->lead->name,
                            'company'          => $deal->lead->company,
                            'phone'            => $deal->lead->phone,
                            'email'            => $deal->lead->email,
                            'score'            => (int) ($deal->lead->score ?? 50),
                            'segment'          => $deal->lead->segment ?? 'general',
                            'touchpoint_count' => (int) ($deal->lead->touchpoint_count ?? 0),
                            'next_action_date' => $deal->lead->next_action_date?->format('d M'),
                            'is_overdue'       => $deal->lead->next_action_date && $deal->lead->next_action_date->isPast(),
                        ] : null,
                        'organization' => $deal->organization ? [
                            'id'       => $deal->organization->id,
                            'name'     => $deal->organization->name,
                            'city'     => $deal->organization->city,
                            'country'  => $deal->organization->country,
                            'industry' => $deal->organization->industry,
                        ] : null,
                    ];
                }),
            ];
        }

        // 4. Leads Directory Data (Full management table)
        $leadsQuery = Lead::query()
            ->with(['organization', 'deals' => fn($q) => $q->latest()])
            ->latest();

        if ($segment !== 'all') {
            $leadsQuery->where('segment', $segment);
        }

        if ($search !== '') {
            $leadsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allLeads = $leadsQuery->limit(100)->get()->map(function ($lead) {
            $latestDeal = $lead->deals->first();
            return [
                'id'                => $lead->id,
                'name'              => $lead->name,
                'company'           => $lead->company ?? $lead->organization?->name ?? 'Direct Client',
                'email'             => $lead->email,
                'phone'             => $lead->phone,
                'segment'           => $lead->segment ?? 'general',
                'status'            => $lead->status ?? 'new',
                'stage'             => $latestDeal?->stage ?? $lead->stage ?? 'new',
                'score'             => (int) ($lead->score ?? 50),
                'touchpoint_count'  => (int) ($lead->touchpoint_count ?? 0),
                'deal_id'           => $latestDeal?->id,
                'deal_amount'       => $latestDeal ? $latestDeal->formatted_amount : '$5,500',
                'currency'          => $latestDeal?->currency ?? 'USD',
                'last_contact_date' => $lead->last_contact_date?->format('d M Y'),
                'next_action_date'  => $lead->next_action_date?->format('d M Y'),
                'next_action_note'  => $lead->next_action_note,
                'created_at'        => $lead->created_at->diffForHumans(),
            ];
        });

        // 5. Market Requirements / Lead Hunter Stream (Sanitized & Enriched)
        $marketRequirements = MarketRequirement::query()
            ->whereIn('status', ['qualified', 'pending'])
            ->latest()
            ->limit(40)
            ->get()
            ->map(function ($req) {
                $meta = $req->metadata ?? [];
                return [
                    'id'                     => $req->id,
                    'source'                 => $req->source,
                    'title'                  => html_entity_decode((string) $req->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'raw_text'               => html_entity_decode((string) $req->raw_text, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'budget'                 => $req->formatted_amount,
                    'currency'               => $req->currency ?: 'USD',
                    'contact_name'           => $req->contact_name,
                    'contact_company'        => $req->contact_company,
                    'contact_email'          => $req->contact_email,
                    'contact_phone'          => $req->contact_phone,
                    'location'               => $req->location,
                    'matched_segment'        => $req->matched_segment,
                    'relevance_score'        => (int) $req->relevance_score,
                    'pitch_draft'            => $req->pitch_draft,
                    'status'                 => $req->status,
                    'url'                    => $meta['url'] ?? $meta['hn_url'] ?? null,
                    'upwork_proposal'        => $req->pitch_draft,
                    'email_pitch'            => $meta['email_pitch'] ?? null,
                    'email_subject'          => $meta['email_subject'] ?? ('Technical Proposal for ' . ($req->contact_company ?: 'Your Project')),
                    'linkedin_dm'            => $meta['linkedin_dm'] ?? null,
                    'detected_tech_stack'    => $meta['detected_tech_stack'] ?? [],
                    'suggested_architecture' => $meta['suggested_architecture'] ?? null,
                    'client_pain_points'     => $meta['client_pain_points'] ?? [],
                    'created_at'             => $req->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Crm/Dashboard', [
            'telemetry' => [
                'total_pipeline_usd' => (float) $totalPipelineUsd,
                'won_revenue_usd'    => (float) $wonRevenueUsd,
                'active_deals_count' => $activeDealsCount,
                'win_rate'           => $winRate,
                'overdue_count'      => $overdueCount,
                'avg_deal_size'      => (float) $avgDealSize,
            ],
            'action_queue'        => $actionQueue,
            'market_requirements' => $marketRequirements,
            'stages'              => $groupedDeals,
            'all_deals'           => $allDeals,
            'all_leads'           => $allLeads,
            'filters'             => [
                'segment' => $segment,
                'search'  => $search,
                'tab'     => $activeTab,
            ],
        ]);
    }
}
