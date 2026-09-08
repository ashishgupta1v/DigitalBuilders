<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $segment = $request->query('segment', 'all');
        $currency = $request->query('currency', 'all');
        $search = trim((string) $request->query('search', ''));
        $viewMode = $request->query('view', 'kanban'); // 'kanban' or 'table'

        // 1. Calculate Telemetry KPIs
        $openDeals = Deal::query()->whereNotIn('stage', ['closed_won', 'closed_lost']);
        $wonDeals = Deal::query()->where('stage', 'closed_won');
        $lostDealsCount = Deal::query()->where('stage', 'closed_lost')->count();
        $wonDealsCount = $wonDeals->count();

        $totalPipelineInr = (clone $openDeals)->where('currency', 'INR')->sum('amount');
        $totalPipelineUsd = (clone $openDeals)->where('currency', 'USD')->sum('amount');
        $wonRevenueInr = (clone $wonDeals)->where('currency', 'INR')->sum('amount');
        $wonRevenueUsd = (clone $wonDeals)->where('currency', 'USD')->sum('amount');
        $activeDealsCount = $openDeals->count();

        $closedTotal = $wonDealsCount + $lostDealsCount;
        $winRate = $closedTotal > 0 ? (int) round(($wonDealsCount / $closedTotal) * 100) : 0;

        $overdueCount = Lead::query()
            ->whereNotNull('next_action_date')
            ->where('next_action_date', '<=', now()->endOfDay())
            ->whereNotIn('status', ['converted', 'archived'])
            ->count();

        // 2. Fetch Daily Action Queue (Top Priority Outreach & Overdue Follow-ups)
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
                    'company'          => $lead->company ?? $lead->organization?->name ?? 'Direct Inquiry',
                    'phone'            => $lead->phone,
                    'email'            => $lead->email,
                    'segment'          => $lead->segment ?? 'general',
                    'score'            => (int) ($lead->score ?? 50),
                    'touchpoint_count' => (int) ($lead->touchpoint_count ?? 0),
                    'next_action_date' => $lead->next_action_date?->toIso8601String(),
                    'next_action_note' => $lead->next_action_note ?? 'Initiate Touch 1 follow-up',
                    'is_overdue'       => $lead->next_action_date && $lead->next_action_date->isPast(),
                    'deal_value'       => $latestDeal ? $latestDeal->formatted_amount : '₹1,49,000',
                    'deal_id'          => $latestDeal?->id,
                ];
            });

        // 3. Fetch Deals grouped by Stages
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

        if ($currency !== 'all') {
            $dealsQuery->where('currency', strtoupper($currency));
        }

        if ($search !== '') {
            $dealsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('scope_summary', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($lq) use ($search) {
                      $lq->where('name', 'like', "%{$search}%")
                         ->orWhere('company', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('organization', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $allDeals = $dealsQuery->latest('updated_at')->get();

        $stagesList = [
            'new'            => ['key' => 'new', 'name' => 'New Inbound', 'color' => 'blue'],
            'contacted'      => ['key' => 'contacted', 'name' => 'Contacted (Touch 1-2)', 'color' => 'indigo'],
            'qualified'      => ['key' => 'qualified', 'name' => 'Qualified', 'color' => 'cyan'],
            'discovery_done' => ['key' => 'discovery_done', 'name' => 'Discovery Done', 'color' => 'amber'],
            'proposal_sent'  => ['key' => 'proposal_sent', 'name' => 'Proposal Sent', 'color' => 'orange'],
            'negotiation'    => ['key' => 'negotiation', 'name' => 'Negotiation', 'color' => 'purple'],
            'closed_won'     => ['key' => 'closed_won', 'name' => 'Closed Won', 'color' => 'emerald'],
            'closed_lost'    => ['key' => 'closed_lost', 'name' => 'Closed Lost', 'color' => 'rose'],
        ];

        $groupedDeals = [];
        foreach ($stagesList as $stageKey => $stageMeta) {
            $stageDeals = $allDeals->where('stage', $stageKey)->values();
            $groupedDeals[$stageKey] = [
                'meta'        => $stageMeta,
                'count'       => $stageDeals->count(),
                'total_inr'   => $stageDeals->where('currency', 'INR')->sum('amount'),
                'total_usd'   => $stageDeals->where('currency', 'USD')->sum('amount'),
                'deals'       => $stageDeals->map(function ($deal) {
                    return [
                        'id'                  => $deal->id,
                        'title'               => $deal->title,
                        'amount'              => (float) $deal->amount,
                        'formatted_amount'    => $deal->formatted_amount,
                        'currency'            => $deal->currency,
                        'stage'               => $deal->stage,
                        'probability'         => (int) $deal->probability,
                        'expected_close_date' => $deal->expected_close_date?->format('Y-m-d'),
                        'pricing_tier'        => $deal->pricing_tier,
                        'payment_status'      => $deal->payment_status,
                        'amount_paid'         => (float) $deal->amount_paid,
                        'formatted_paid'      => $deal->formatted_amount_paid,
                        'pending_balance'     => $deal->pending_balance,
                        'scope_summary'       => $deal->scope_summary,
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
                            'industry' => $deal->organization->industry,
                        ] : null,
                    ];
                }),
            ];
        }

        return Inertia::render('Crm/Dashboard', [
            'telemetry' => [
                'total_pipeline_inr' => (float) $totalPipelineInr,
                'total_pipeline_usd' => (float) $totalPipelineUsd,
                'won_revenue_inr'    => (float) $wonRevenueInr,
                'won_revenue_usd'    => (float) $wonRevenueUsd,
                'active_deals_count' => $activeDealsCount,
                'win_rate'           => $winRate,
                'overdue_count'      => $overdueCount,
            ],
            'action_queue'  => $actionQueue,
            'stages'        => $groupedDeals,
            'all_deals'     => $allDeals,
            'filters'       => [
                'segment'  => $segment,
                'currency' => $currency,
                'search'   => $search,
                'view'     => $viewMode,
            ],
        ]);
    }
}
