<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmLeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $segment = $request->query('segment', 'all');
        $status = $request->query('status', 'all');

        $query = Lead::query()
            ->with(['organization', 'deals' => fn($q) => $q->latest()])
            ->latest();

        if ($segment !== 'all') {
            $query->where('segment', $segment);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(30);

        return response()->json($leads);
    }

    public function show(int $id): JsonResponse
    {
        $lead = Lead::query()
            ->with([
                'organization',
                'deals' => fn($q) => $q->with(['payments'])->latest(),
                'activities' => fn($q) => $q->latest(),
                'notes',
            ])
            ->findOrFail($id);

        return response()->json([
            'lead' => [
                'id'                => $lead->id,
                'name'              => $lead->name,
                'email'             => $lead->email,
                'phone'             => $lead->phone,
                'company'           => $lead->company ?? $lead->organization?->name,
                'role_title'        => $lead->role_title,
                'segment'           => $lead->segment ?? 'general',
                'project_type'      => $lead->project_type,
                'source'            => $lead->source ?? 'direct',
                'region'            => $lead->region ?? 'IN',
                'status'            => $lead->status ?? 'new',
                'stage'             => $lead->stage ?? 'new',
                'score'             => (int) ($lead->score ?? 50),
                'touchpoint_count'  => (int) ($lead->touchpoint_count ?? 0),
                'last_contact_date' => $lead->last_contact_date?->toIso8601String(),
                'next_action_date'  => $lead->next_action_date?->format('Y-m-d H:i'),
                'next_action_note'  => $lead->next_action_note,
                'ai_summary'        => $lead->ai_summary,
                'objection_flag'    => $lead->objection_flag,
                'description'       => $lead->description,
                'created_at'        => $lead->created_at->format('d M Y, h:i A'),
            ],
            'organization' => $lead->organization ? [
                'id'           => $lead->organization->id,
                'name'         => $lead->organization->name,
                'domain'       => $lead->organization->domain,
                'industry'     => $lead->organization->industry,
                'company_size' => $lead->organization->company_size,
                'city'         => $lead->organization->city,
                'gst_number'   => $lead->organization->gst_number,
            ] : null,
            'deals' => $lead->deals->map(function ($deal) {
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
                    'payments'            => $deal->payments,
                ];
            }),
            'activities' => $lead->activities->map(function ($activity) {
                return [
                    'id'                => $activity->id,
                    'type'              => $activity->type,
                    'subject'           => $activity->subject,
                    'description'       => $activity->description,
                    'touchpoint_number' => $activity->touchpoint_number,
                    'metadata'          => $activity->metadata,
                    'created_at'        => $activity->created_at->diffForHumans(),
                    'created_at_raw'    => $activity->created_at->format('d M Y, H:i'),
                ];
            }),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'max:30'],
            'company'      => ['nullable', 'string', 'max:255'],
            'role_title'   => ['nullable', 'string', 'max:100'],
            'segment'      => ['required', 'string', 'in:manufacturer,retail,clinic,coaching,international,startup,local_sme,general'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'deal_amount'  => ['nullable', 'numeric', 'min:0'],
            'currency'     => ['nullable', 'string', 'in:INR,USD'],
            'pricing_tier' => ['nullable', 'string'],
            'stage'        => ['nullable', 'string', 'in:new,contacted,qualified,discovery_done,proposal_sent,negotiation,closed_won,closed_lost'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'score'        => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $orgId = null;
            if (!empty($validated['company'])) {
                $org = Organization::firstOrCreate(
                    ['name' => trim($validated['company'])],
                    [
                        'industry' => $validated['segment'] ?? 'general',
                        'phone'    => $validated['phone'],
                        'email'    => $validated['email'] ?? null,
                    ]
                );
                $orgId = $org->id;
            }

            // Calculate starting qualification score
            $score = $validated['score'] ?? 60;
            $dealAmount = isset($validated['deal_amount']) && $validated['deal_amount'] !== '' && (float) $validated['deal_amount'] > 0 
                ? (float) $validated['deal_amount'] 
                : 0.0;
            $currency = $validated['currency'] ?? ($validated['segment'] === 'international' ? 'USD' : 'INR');
            $initialStage = $validated['stage'] ?? 'new';

            if ($dealAmount > 0) {
                if ($currency === 'INR' && $dealAmount >= 249000) {
                    $score += 15;
                } elseif ($currency === 'USD' && $dealAmount >= 5000) {
                    $score += 20;
                }
            }

            $lead = Lead::create([
                'organization_id'   => $orgId,
                'name'              => $validated['name'],
                'company'           => $validated['company'] ?? null,
                'role_title'        => $validated['role_title'] ?? null,
                'email'             => $validated['email'] ?? null,
                'phone'             => $validated['phone'],
                'project_type'      => $validated['project_type'] ?? 'Custom Architecture & Software',
                'segment'           => $validated['segment'],
                'source'            => 'manual_entry',
                'status'            => 'new',
                'stage'             => $initialStage,
                'score'             => min(100, $score),
                'touchpoint_count'  => 0,
                'next_action_date'  => now(),
                'next_action_note'  => 'Send Touch 1 Outreach',
                'description'       => $validated['description'] ?? null,
            ]);

            $dealTitle = ($validated['company'] ?: $validated['name']) . ' — ' . ($validated['project_type'] ?: 'Architecture Build');

            $deal = Deal::create([
                'title'               => $dealTitle,
                'lead_id'             => $lead->id,
                'organization_id'     => $orgId,
                'amount'              => $dealAmount,
                'currency'            => $currency,
                'stage'               => $initialStage,
                'probability'         => Deal::STAGE_PROBABILITIES[$initialStage] ?? 15,
                'scope_summary'       => $validated['description'] ?? 'Initial inquiry via Quick Add',
                'expected_close_date' => now()->addDays(21),
            ]);

            Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal->id,
                'user_id'           => $request->user()?->id,
                'type'              => 'stage_change',
                'subject'           => 'Lead Created & Ingested into Pipeline',
                'description'       => "Added to New Inbound stage with target value {$deal->formatted_amount}.",
                'touchpoint_number' => 0,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'lead_id' => $lead->id, 'deal_id' => $deal->id]);
            }

            return back()->with('success', "Lead {$lead->name} successfully added to sales pipeline.");
        });
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'email'            => ['sometimes', 'email', 'max:255'],
            'phone'            => ['sometimes', 'string', 'max:30'],
            'company'          => ['nullable', 'string', 'max:255'],
            'role_title'       => ['nullable', 'string', 'max:100'],
            'segment'          => ['sometimes', 'string'],
            'score'            => ['nullable', 'integer', 'min:0', 'max:100'],
            'next_action_date' => ['nullable', 'date'],
            'next_action_note' => ['nullable', 'string', 'max:255'],
            'objection_flag'   => ['nullable', 'string', 'max:100'],
            'status'           => ['sometimes', 'string'],
        ]);

        $lead->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'lead' => $lead]);
        }

        return back()->with('success', 'Lead details updated.');
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
            'segment' => ['required', 'string'],
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        if (!$header || count($rows) === 0) {
            return back()->withErrors(['file' => 'The uploaded CSV appears to be empty.']);
        }

        $importedCount = 0;

        DB::transaction(function () use ($rows, $header, $request, &$importedCount) {
            $headerLower = array_map(fn($col) => strtolower(trim((string) $col)), $header);
            
            $nameIdx = array_search('name', $headerLower, true);
            if ($nameIdx === false) $nameIdx = array_search('contact name', $headerLower, true);
            if ($nameIdx === false) $nameIdx = array_search('lead name', $headerLower, true);
            if ($nameIdx === false) $nameIdx = 0;

            $phoneIdx = array_search('phone', $headerLower, true);
            if ($phoneIdx === false) $phoneIdx = array_search('mobile', $headerLower, true);
            if ($phoneIdx === false) $phoneIdx = array_search('whatsapp', $headerLower, true);

            $emailIdx = array_search('email', $headerLower, true);
            $companyIdx = array_search('company', $headerLower, true);
            if ($companyIdx === false) $companyIdx = array_search('unit', $headerLower, true);

            $amountIdx = array_search('amount', $headerLower, true);
            if ($amountIdx === false) $amountIdx = array_search('estimated scope', $headerLower, true);

            foreach ($rows as $row) {
                if (empty($row) || !isset($row[$nameIdx]) || empty(trim($row[$nameIdx]))) {
                    continue;
                }

                $name = trim($row[$nameIdx]);
                $phone = ($phoneIdx !== false && isset($row[$phoneIdx]) && trim($row[$phoneIdx]) !== '') ? trim($row[$phoneIdx]) : null;
                $email = ($emailIdx !== false && isset($row[$emailIdx]) && filter_var(trim($row[$emailIdx]), FILTER_VALIDATE_EMAIL)) 
                    ? trim($row[$emailIdx]) 
                    : null;
                $company = ($companyIdx !== false && isset($row[$companyIdx]) && trim($row[$companyIdx]) !== '') ? trim($row[$companyIdx]) : null;
                $amount = ($amountIdx !== false && isset($row[$amountIdx])) ? (float) preg_replace('/[^0-9.]/', '', $row[$amountIdx]) : 0.0;

                $segment = $request->input('segment', 'manufacturer');
                $currency = ($segment === 'international') ? 'USD' : 'INR';

                $orgId = null;
                if ($company) {
                    $org = Organization::firstOrCreate(
                        ['name' => $company],
                        ['industry' => $segment, 'phone' => $phone, 'email' => $email]
                    );
                    $orgId = $org->id;
                }

                $lead = Lead::create([
                    'organization_id'  => $orgId,
                    'name'             => $name,
                    'company'          => $company,
                    'email'            => $email,
                    'phone'            => $phone,
                    'project_type'     => 'Custom Software System',
                    'segment'          => $segment,
                    'source'           => 'csv_import',
                    'status'           => 'new',
                    'stage'            => 'new',
                    'score'            => 65,
                    'touchpoint_count' => 0,
                    'next_action_date' => now(),
                    'next_action_note' => 'Touch 1: Send Value Proposition & Portfolio',
                ]);

                Deal::create([
                    'title'               => ($company ?: $name) . ' — ERP / App System',
                    'lead_id'             => $lead->id,
                    'organization_id'     => $orgId,
                    'amount'              => $amount,
                    'currency'            => $currency,
                    'stage'               => 'new',
                    'probability'         => 15,
                    'expected_close_date' => now()->addDays(30),
                ]);

                $importedCount++;
            }
        });

        return back()->with('success', "Successfully imported {$importedCount} prospect leads into the pipeline.");
    }

    public function handleExternalWebhook(Request $request): JsonResponse
    {
        $expectedSecret = env('CRM_WEBHOOK_SECRET');
        $providedSecret = $request->header('X-CRM-SECRET') ?? $request->query('secret');

        if (!empty($expectedSecret) && $providedSecret !== $expectedSecret) {
            return response()->json(['error' => 'Unauthorized webhook signature.'], 401);
        }

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'max:30'],
            'company'      => ['nullable', 'string', 'max:255'],
            'role_title'   => ['nullable', 'string', 'max:100'],
            'segment'      => ['nullable', 'string'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'deal_amount'  => ['nullable', 'numeric'],
            'currency'     => ['nullable', 'string'],
            'source'       => ['nullable', 'string'],
            'notes'        => ['nullable', 'string'],
        ]);

        $segment = $validated['segment'] ?? 'general';
        $currency = $validated['currency'] ?? ($segment === 'international' ? 'USD' : 'INR');
        $dealAmount = isset($validated['deal_amount']) && $validated['deal_amount'] !== '' && (float) $validated['deal_amount'] > 0 
            ? (float) $validated['deal_amount'] 
            : 0.0;

        return DB::transaction(function () use ($validated, $segment, $currency, $dealAmount) {
            $orgId = null;
            if (!empty($validated['company'])) {
                $org = Organization::firstOrCreate(
                    ['name' => trim($validated['company'])],
                    ['industry' => $segment, 'phone' => $validated['phone'], 'email' => $validated['email'] ?? null]
                );
                $orgId = $org->id;
            }

            $lead = Lead::create([
                'organization_id'   => $orgId,
                'name'              => $validated['name'],
                'company'           => $validated['company'] ?? null,
                'role_title'        => $validated['role_title'] ?? null,
                'email'             => $validated['email'] ?? null,
                'phone'             => $validated['phone'],
                'project_type'      => $validated['project_type'] ?? 'External Webhook Ingestion',
                'segment'           => $segment,
                'source'            => $validated['source'] ?? 'external_webhook',
                'status'            => 'new',
                'stage'             => 'new',
                'score'             => 70,
                'touchpoint_count'  => 0,
                'next_action_date'  => now(),
                'next_action_note'  => 'Send Touch 1 Outreach',
                'description'       => $validated['notes'] ?? null,
            ]);

            $deal = Deal::create([
                'title'               => ($validated['company'] ?: $validated['name']) . ' — ' . ($validated['project_type'] ?: 'Software Solution'),
                'lead_id'             => $lead->id,
                'organization_id'     => $orgId,
                'amount'              => $dealAmount,
                'currency'            => $currency,
                'stage'               => 'new',
                'probability'         => 15,
                'expected_close_date' => now()->addDays(21),
            ]);

            Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'Lead Ingested via External Webhook',
                'description'       => "Source: {$lead->source}. Queued for Touch 1 outreach.",
                'touchpoint_number' => 0,
            ]);

            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
                'deal_id' => $deal->id,
                'message' => 'Lead successfully ingested into CRM pipeline.',
            ], 201);
        });
    }

    public function destroy(int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $name = $lead->name;
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => "Lead {$name} deleted successfully.",
        ]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_ids'   => ['required', 'array'],
            'lead_ids.*' => ['integer'],
            'action'     => ['required', 'string', 'in:delete,update_status,update_stage'],
            'value'      => ['nullable', 'string'],
        ]);

        $ids = $validated['lead_ids'];
        $action = $validated['action'];
        $value = $validated['value'] ?? '';

        if ($action === 'delete') {
            $count = Lead::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => "Deleted {$count} leads."]);
        }

        if ($action === 'update_status') {
            $count = Lead::whereIn('id', $ids)->update(['status' => $value]);
            return response()->json(['success' => true, 'message' => "Updated status for {$count} leads."]);
        }

        if ($action === 'update_stage') {
            $count = Lead::whereIn('id', $ids)->update(['stage' => $value]);
            return response()->json(['success' => true, 'message' => "Updated stage for {$count} leads."]);
        }

        return response()->json(['success' => false, 'message' => 'Unknown action.'], 400);
    }
}
