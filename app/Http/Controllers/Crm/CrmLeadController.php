<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Mail\CrmOutreachMail;
use App\Models\Activity;
use App\Models\CrmOutreachEmail;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\MarketRequirement;
use App\Models\Organization;
use App\Models\Payment;
use App\Services\SalesFunnel\CrmLeadEnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            'outreach_emails' => CrmOutreachEmail::where('lead_id', $lead->id)
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($email) => [
                    'id'                => $email->id,
                    'subject'           => $email->subject,
                    'touchpoint_number' => $email->touchpoint_number,
                    'sent_at'           => $email->sent_at?->format('d M, h:i A'),
                    'opened_at'         => $email->opened_at?->format('d M, h:i A'),
                    'open_count'        => (int) $email->open_count,
                    'clicked_at'        => $email->clicked_at?->format('d M, h:i A'),
                    'click_count'       => (int) $email->click_count,
                    'last_clicked_url'  => $email->last_clicked_url,
                    'status'            => $email->status,
                ]),
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

    public function sendOutreachEmail(Request $request, int $id): JsonResponse
    {
        $lead = Lead::with(['deals' => fn($q) => $q->latest()->limit(1)])->findOrFail($id);

        if (empty($lead->email)) {
            return response()->json(['error' => 'This lead does not have a valid email address.'], 422);
        }

        $validated = $request->validate([
            'subject'           => ['required', 'string', 'max:255'],
            'body'              => ['nullable', 'string', 'max:10000'],
            'body_text'         => ['nullable', 'string', 'max:10000'],
            'touchpoint_number' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $body = $validated['body'] ?? $validated['body_text'] ?? null;
        if (empty($body)) {
            return response()->json(['error' => 'Email body content cannot be empty.'], 422);
        }

        $deal = $lead->deals->first();
        $trackingToken = bin2hex(random_bytes(16));
        $touchNumber = (int) ($validated['touchpoint_number'] ?? max(1, ($lead->touchpoint_count ?? 0) + 1));

        $outreach = CrmOutreachEmail::create([
            'lead_id'           => $lead->id,
            'deal_id'           => $deal?->id,
            'tracking_token'    => $trackingToken,
            'recipient_email'   => $lead->email,
            'recipient_name'    => $lead->name,
            'subject'           => $validated['subject'],
            'body_text'         => $body,
            'body_html'         => $body,
            'touchpoint_number' => $touchNumber,
            'sent_at'           => now(),
            'status'            => 'sent',
        ]);

        try {
            Mail::to($lead->email)->send(new CrmOutreachMail(
                outreachSubject: $validated['subject'],
                bodyContent: $body,
                trackingToken: $trackingToken,
                recipientName: $lead->name,
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch outreach email: ' . $e->getMessage());
            return response()->json(['error' => 'Mail delivery failed: ' . $e->getMessage()], 500);
        }

        $lead->increment('touchpoint_count');
        $lead->update([
            'last_contact_date' => now(),
            'next_action_date'  => now()->addDays(2),
            'next_action_note'  => "Touch " . min(5, $touchNumber + 1) . ": Follow-up after email",
        ]);

        if ($deal && in_array($deal->stage, ['new', 'inbound'], true)) {
            $deal->update([
                'stage'       => 'contacted',
                'probability' => 25,
            ]);
            $lead->update(['stage' => 'contacted']);
        }

        Activity::create([
            'lead_id'           => $lead->id,
            'deal_id'           => $deal?->id,
            'user_id'           => $request->user()?->id,
            'type'              => 'email',
            'subject'           => "Outreach Email Dispatched (Touch #{$touchNumber})",
            'description'       => "Subject: \"{$validated['subject']}\"\nRecipient: {$lead->email}",
            'touchpoint_number' => $touchNumber,
            'metadata'          => [
                'outreach_id'    => $outreach->id,
                'tracking_token' => $trackingToken,
            ],
        ]);

        return response()->json([
            'success'          => true,
            'message'          => "Tracked email dispatched to {$lead->email}!",
            'outreach_id'      => $outreach->id,
            'touchpoint_count' => $lead->fresh()->touchpoint_count,
            'deal_stage'       => $deal?->fresh()->stage,
        ]);
    }

    /**
     * Resolve company domain, tech stack profile, and executive intelligence for a lead.
     */
    public function enrich(int $id, CrmLeadEnrichmentService $enrichmentService): JsonResponse
    {
        $lead = Lead::query()->with('organization')->findOrFail($id);
        $dossier = $enrichmentService->enrichLead($lead);

        // Save domain on organization if missing
        if (!empty($dossier['domain']) && $lead->organization && empty($lead->organization->domain)) {
            $lead->organization->update(['domain' => $dossier['domain']]);
        }

        $summary = $lead->ai_summary ?: '';
        $stackStr = implode(', ', $dossier['suggested_stack'] ?? []);
        $enrichmentNote = "\n\n[Lead Intelligence Dossier - " . now()->format('d M Y') . "]\n"
            . "• Domain: " . ($dossier['domain'] ?? 'N/A') . "\n"
            . "• Suggested Architecture: {$stackStr}\n"
            . "• LinkedIn: " . ($dossier['linkedin_company_url'] ?? 'N/A');

        $lead->update([
            'ai_summary' => trim($summary . $enrichmentNote),
        ]);

        Activity::create([
            'lead_id'     => $lead->id,
            'type'        => 'note',
            'subject'     => 'Executive Domain & Intelligence Dossier Compiled',
            'description' => "Enriched with domain '{$dossier['domain']}' and suggested architecture profile: {$stackStr}.",
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Lead intelligence dossier enriched successfully!',
            'dossier'    => $dossier,
            'ai_summary' => $lead->ai_summary,
        ]);
    }

    /**
     * Scan database for possible duplicate leads matching phone, email, domain, or company name.
     */
    public function checkDuplicates(int $id): JsonResponse
    {
        $lead = Lead::with('organization')->findOrFail($id);
        $cleanPhone = preg_replace('/\D+/', '', (string) $lead->phone);
        $last10Phone = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : null;

        $emailDomain = null;
        if (!empty($lead->email) && str_contains($lead->email, '@')) {
            $emailParts = explode('@', $lead->email);
            $domainCandidate = strtolower(trim($emailParts[1] ?? ''));
            $publicProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com', 'protonmail.com', 'zoho.com', 'aol.com', 'mail.com'];
            if (!in_array($domainCandidate, $publicProviders, true) && strlen($domainCandidate) > 3) {
                $emailDomain = $domainCandidate;
            }
        }

        $companyNormalized = trim((string) ($lead->company ?? $lead->organization?->name ?? ''));

        $query = Lead::query()
            ->where('id', '!=', $lead->id)
            ->where('status', '!=', 'archived');

        $query->where(function ($q) use ($last10Phone, $emailDomain, $companyNormalized, $lead) {
            $hasCondition = false;

            if ($last10Phone) {
                $q->orWhere('phone', 'like', "%{$last10Phone}%");
                $hasCondition = true;
            }

            if (!empty($lead->email)) {
                $q->orWhere('email', strtolower(trim($lead->email)));
                $hasCondition = true;
            }

            if ($emailDomain) {
                $q->orWhere('email', 'like', "%@{$emailDomain}");
                $hasCondition = true;
            }

            if (strlen($companyNormalized) >= 4) {
                $q->orWhere('company', 'like', "%{$companyNormalized}%");
                $hasCondition = true;
            }

            if (!$hasCondition) {
                $q->whereRaw('0 = 1');
            }
        });

        $duplicates = $query->with(['organization', 'deals'])->limit(10)->get()->map(function ($dup) use ($last10Phone, $lead, $companyNormalized, $emailDomain) {
            $reasons = [];
            if (!empty($lead->email) && !empty($dup->email) && strtolower(trim($dup->email)) === strtolower(trim($lead->email))) {
                $reasons[] = 'Exact Email Match';
            } elseif ($emailDomain && !empty($dup->email) && str_ends_with(strtolower(trim($dup->email)), '@' . $emailDomain)) {
                $reasons[] = 'Company Domain Match (' . $emailDomain . ')';
            }

            $dupPhone = preg_replace('/\D+/', '', (string) $dup->phone);
            if ($last10Phone && str_contains($dupPhone, $last10Phone)) {
                $reasons[] = 'Phone Match (***' . substr($last10Phone, -4) . ')';
            }

            if (strlen($companyNormalized) >= 4 && stripos((string) $dup->company, $companyNormalized) !== false) {
                $reasons[] = 'Company Name Match';
            }

            return [
                'id'          => $dup->id,
                'name'        => $dup->name,
                'company'     => $dup->company ?? $dup->organization?->name ?? 'N/A',
                'email'       => $dup->email,
                'phone'       => $dup->phone,
                'stage'       => $dup->stage,
                'score'       => (int) ($dup->score ?? 50),
                'created_at'  => $dup->created_at->format('d M Y'),
                'reasons'     => !empty($reasons) ? $reasons : ['Potential Overlap'],
                'deals_count' => $dup->deals->count(),
            ];
        });

        return response()->json([
            'count'      => $duplicates->count(),
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * Merge a duplicate lead into this master lead.
     */
    public function merge(Request $request, int $id): JsonResponse
    {
        $masterLead = Lead::findOrFail($id);

        $validated = $request->validate([
            'duplicate_lead_id' => ['required', 'integer', 'different:id', 'exists:leads,id'],
        ]);

        $duplicateLead = Lead::with(['deals', 'activities', 'notes'])->findOrFail($validated['duplicate_lead_id']);

        DB::transaction(function () use ($masterLead, $duplicateLead, $request) {
            // 1. Backfill any missing contact fields on master
            $updates = [];
            if (empty($masterLead->email) && !empty($duplicateLead->email)) {
                $updates['email'] = $duplicateLead->email;
            }
            if (empty($masterLead->phone) && !empty($duplicateLead->phone)) {
                $updates['phone'] = $duplicateLead->phone;
            }
            if (empty($masterLead->company) && !empty($duplicateLead->company)) {
                $updates['company'] = $duplicateLead->company;
            }
            if (empty($masterLead->role_title) && !empty($duplicateLead->role_title)) {
                $updates['role_title'] = $duplicateLead->role_title;
            }
            if (empty($masterLead->organization_id) && !empty($duplicateLead->organization_id)) {
                $updates['organization_id'] = $duplicateLead->organization_id;
            }
            if (($duplicateLead->score ?? 0) > ($masterLead->score ?? 0)) {
                $updates['score'] = $duplicateLead->score;
            }
            $updates['touchpoint_count'] = ($masterLead->touchpoint_count ?? 0) + ($duplicateLead->touchpoint_count ?? 0);

            if (!empty($updates)) {
                $masterLead->update($updates);
            }

            // 2. Re-point deals
            Deal::where('lead_id', $duplicateLead->id)->update(['lead_id' => $masterLead->id]);

            // 3. Re-point activities
            Activity::where('lead_id', $duplicateLead->id)->update(['lead_id' => $masterLead->id]);

            // 4. Re-point notes
            LeadNote::where('lead_id', $duplicateLead->id)->update(['lead_id' => $masterLead->id]);

            // 5. Re-point outreach emails
            CrmOutreachEmail::where('lead_id', $duplicateLead->id)->update(['lead_id' => $masterLead->id]);

            // 6. Re-point payments
            Payment::where('lead_id', $duplicateLead->id)->update(['lead_id' => $masterLead->id]);

            // 7. Re-point market requirements if any
            MarketRequirement::where('lead_id', $duplicateLead->id)->update(['lead_id' => $masterLead->id]);

            // 8. Archive the duplicate record
            $duplicateLead->update([
                'status'           => 'archived',
                'next_action_note' => "Merged into Master Lead #{$masterLead->id} on " . now()->format('d M Y H:i'),
            ]);

            // 9. Log activity on master lead
            Activity::create([
                'lead_id'     => $masterLead->id,
                'user_id'     => $request->user()?->id,
                'type'        => 'note',
                'subject'     => "Merged Duplicate Lead #{$duplicateLead->id}",
                'description' => "Consolidated record for '{$duplicateLead->name}' ({$duplicateLead->email}, {$duplicateLead->company}) into this lead. Reassigned all deals, activities, notes, and outreach tracking.",
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Lead #{$duplicateLead->id} successfully consolidated into Lead #{$masterLead->id}.",
            'master'  => $masterLead->fresh(['deals', 'activities', 'notes']),
        ]);
    }
}
