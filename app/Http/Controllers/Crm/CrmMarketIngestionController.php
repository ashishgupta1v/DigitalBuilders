<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\MarketRequirement;
use App\Services\SalesFunnel\AiPitchGeneratorService;
use App\Services\SalesFunnel\InternationalLeadScraperService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmMarketIngestionController extends Controller
{
    public function __construct(
        private AiPitchGeneratorService $pitchGenerator,
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * Handle incoming IndiaMART Lead Manager Push API webhook.
     */
    public function handleIndiaMartPush(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('IndiaMART Push Webhook received: ', $data);

        $externalId = (string) ($data['QUERY_ID'] ?? $data['query_id'] ?? $data['UNIQUE_QUERY_ID'] ?? uniqid('im_'));
        $name = trim((string) ($data['SENDER_NAME'] ?? $data['sender_name'] ?? 'IndiaMART Inquirer'));
        $phone = trim((string) ($data['GLUSR_USR_PH_MOBILE'] ?? $data['sender_mobile'] ?? $data['SENDER_MOBILE'] ?? ''));
        $email = trim((string) ($data['SENDER_EMAIL'] ?? $data['sender_email'] ?? ''));
        $company = trim((string) ($data['SENDER_COMPANY'] ?? $data['sender_company'] ?? ''));
        $city = trim((string) ($data['SENDER_CITY'] ?? $data['sender_city'] ?? 'Global'));
        $product = trim((string) ($data['PRODUCT_NAME'] ?? $data['product_name'] ?? 'Software Solution'));
        $message = trim((string) ($data['ENQ_MESSAGE'] ?? $data['enq_message'] ?? $data['QUERY_MODID'] ?? 'Inquiry via Webhook'));

        $fullScopeText = "Product: {$product}. Inquiry: {$message}";

        $existing = MarketRequirement::where('source', 'indiamart')
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            return response()->json([
                'success'        => true,
                'message'        => 'Requirement already ingested.',
                'requirement_id' => $existing->id,
            ], 200);
        }

        $pitchData = $this->pitchGenerator->generateAiPitch($fullScopeText, $name, $company, 'indiamart', 'INR');
        $score = $pitchData['relevance_score'] ?? 75;
        $amount = (float) ($pitchData['estimated_amount'] ?? 185000.00);

        $result = DB::transaction(function () use ($externalId, $name, $phone, $email, $company, $city, $fullScopeText, $pitchData, $score, $amount, $data) {
            $req = MarketRequirement::create([
                'source'           => 'indiamart',
                'external_id'      => $externalId,
                'title'            => $name . ' — Inquiry: ' . substr($data['PRODUCT_NAME'] ?? 'Custom Software', 0, 50),
                'raw_text'         => $fullScopeText,
                'budget_raw'       => $pitchData['budget_range'],
                'estimated_amount' => $amount,
                'currency'         => 'INR',
                'contact_name'     => $name,
                'contact_email'    => $email ?: null,
                'contact_phone'    => $phone ?: null,
                'contact_company'  => $company ?: null,
                'location'         => $city,
                'matched_segment'  => $pitchData['segment'],
                'relevance_score'  => $score,
                'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
                'status'           => 'qualified',
                'metadata'         => $data,
            ]);

            $lead = Lead::create([
                'name'             => $name,
                'email'            => $email ?: null,            // no fake email injected
                'phone'            => $phone,
                'company'          => $company ?: null,          // no 'Direct Client' placeholder
                'project_type'     => $data['PRODUCT_NAME'] ?? 'Custom Software',
                'segment'          => $pitchData['segment'],
                'source'           => 'indiamart',
                'status'           => 'new',
                'stage'            => 'new',
                'score'            => $score,
                'touchpoint_count' => 0,
                'next_action_date' => now(),
                'next_action_note' => 'Send Outreach Email / Proposal',
                'description'      => $fullScopeText,
            ]);

            $deal = Deal::create([
                'title'               => ($company ?: $name) . ' — ' . ($data['PRODUCT_NAME'] ?? 'Software Architecture'),
                'lead_id'             => $lead->id,
                'amount'              => $amount,
                'currency'            => 'INR',
                'stage'               => 'new',
                'probability'         => 20,
                'scope_summary'       => $fullScopeText,
                'expected_close_date' => now()->addDays(21),
            ]);
            $deal->getOrCreateProposalToken();

            $req->update([
                'lead_id' => $lead->id,
                'deal_id' => $deal->id,
            ]);

            Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'Lead Ingested from Inbound Channel',
                'description'       => "Source: Inbound Webhook. Estimated USD value: {$deal->formatted_amount}.",
                'touchpoint_number' => 0,
            ]);

            return (object) ['lead_id' => $lead->id, 'deal_id' => $deal->id, 'req_id' => $req->id];
        });

        $this->telegramBot->sendOpportunityAlert(
            MarketRequirement::find($result->req_id),
            $pitchData
        );

        return response()->json([
            'success'        => true,
            'requirement_id' => $result->req_id,
            'lead_id'        => $result->lead_id,
            'deal_id'        => $result->deal_id,
            'segment'        => $pitchData['segment'],
        ], 201);
    }

    /**
     * Smart Ingest: Analyze any pasted URL (Upwork, LinkedIn, job link) or raw scope text with OpenAI.
     */
    public function smartIngest(Request $request, InternationalLeadScraperService $scraper): JsonResponse
    {
        $validated = $request->validate([
            'input'           => ['required', 'string', 'min:10'],
            'source'          => ['nullable', 'string', 'max:50'],
            'title'           => ['nullable', 'string', 'max:255'],
            'contact_name'    => ['nullable', 'string', 'max:150'],
            'contact_company' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $result = $scraper->extractFromUrlOrText(
                $validated['input'],
                $validated['source'] ?? null,
                $validated['title'] ?? null,
                $validated['contact_name'] ?? null,
                $validated['contact_company'] ?? null
            );

            /** @var MarketRequirement $req */
            $req = $result['requirement'];
            $pitchData = $result['pitch_data'];

            return response()->json([
                'success'     => true,
                'message'     => 'RFP successfully analyzed and ingested with AI!',
                'requirement' => [
                    'id'                     => $req->id,
                    'source'                 => $req->source,
                    'title'                  => $req->title,
                    'raw_text'               => $req->raw_text,
                    'budget'                 => $req->formatted_amount,
                    'estimated_amount'       => (float) $req->estimated_amount,
                    'currency'               => $req->currency,
                    'relevance_score'        => $req->relevance_score,
                    'matched_segment'        => $req->matched_segment,
                    'pitch_draft'            => $req->pitch_draft,
                    'upwork_proposal'        => $pitchData['upwork_proposal'] ?? $req->pitch_draft,
                    'email_pitch'            => $pitchData['email_pitch'] ?? null,
                    'email_subject'          => $pitchData['email_subject'] ?? null,
                    'linkedin_dm'            => $pitchData['linkedin_dm'] ?? null,
                    'detected_tech_stack'    => $pitchData['detected_tech_stack'] ?? [],
                    'suggested_architecture' => $pitchData['suggested_architecture'] ?? null,
                    'client_pain_points'     => $pitchData['client_pain_points'] ?? [],
                ],
                'pitch_data'  => $pitchData,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Smart Ingest error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze requirement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * General external requirement ingestion.
     */
    public function handleExternalRequirement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source'           => ['required', 'string', 'max:50'],
            'external_id'      => ['nullable', 'string', 'max:150'],
            'title'            => ['required', 'string', 'max:255'],
            'raw_text'         => ['required', 'string'],
            'budget_raw'       => ['nullable', 'string', 'max:100'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'contact_name'     => ['nullable', 'string', 'max:150'],
            'contact_email'    => ['nullable', 'email', 'max:150'],
            'contact_phone'    => ['nullable', 'string', 'max:50'],
            'contact_company'  => ['nullable', 'string', 'max:150'],
            'location'         => ['nullable', 'string', 'max:100'],
        ]);

        $currency = strtoupper($validated['currency'] ?? 'USD');
        $externalId = $validated['external_id'] ?? md5($validated['title'] . $validated['raw_text']);

        $existing = MarketRequirement::where('source', $validated['source'])
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'message' => 'Already ingested', 'requirement_id' => $existing->id], 200);
        }

        $pitchData = $this->pitchGenerator->generateAiPitch(
            $validated['raw_text'],
            $validated['contact_name'] ?? null,
            $validated['contact_company'] ?? null,
            $validated['source'],
            $currency,
            $validated['budget_raw'] ?? null
        );

        // Use AI-estimated amount only if explicitly returned; do not inject phantom values
        $estimatedAmount = isset($pitchData['estimated_amount']) ? (float) $pitchData['estimated_amount'] : null;

        $req = MarketRequirement::create([
            'source'           => $validated['source'],
            'external_id'      => $externalId,
            'title'            => $validated['title'],
            'raw_text'         => $validated['raw_text'],
            'budget_raw'       => $validated['budget_raw'] ?? $pitchData['budget_range'],
            'estimated_amount' => $estimatedAmount,
            'currency'         => $currency,
            'contact_name'     => $validated['contact_name'] ?? null,
            'contact_email'    => $validated['contact_email'] ?? null,
            'contact_phone'    => $validated['contact_phone'] ?? null,
            'contact_company'  => $validated['contact_company'] ?? null,
            'location'         => $validated['location'] ?? 'Remote (US/Global)',
            'matched_segment'  => $pitchData['segment'],
            'relevance_score'  => $pitchData['relevance_score'] ?? 75,
            'pitch_draft'      => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
            'status'           => 'qualified',
            'metadata'         => [
                'email_pitch'             => $pitchData['email_pitch'] ?? null,
                'email_subject'           => $pitchData['email_subject'] ?? null,
                'linkedin_dm'             => $pitchData['linkedin_dm'] ?? null,
                'detected_tech_stack'     => $pitchData['detected_tech_stack'] ?? [],
                'suggested_architecture'  => $pitchData['suggested_architecture'] ?? null,
                'client_pain_points'      => $pitchData['client_pain_points'] ?? [],
            ],
        ]);

        $this->telegramBot->sendOpportunityAlert($req, $pitchData);

        return response()->json([
            'success'        => true,
            'requirement_id' => $req->id,
            'segment'        => $pitchData['segment'],
            'score'          => $req->relevance_score,
            'pitch'          => $req->pitch_draft,
        ], 201);
    }

    /**
     * Trigger live polling across all configured international channels from the CRM cockpit.
     */
    public function pollLive(InternationalLeadScraperService $scraper): JsonResponse
    {
        try {
            $stats = $scraper->pollAll();
            return response()->json([
                'success' => true,
                'message' => "Live scanning complete! Ingested {$stats['total']} fresh international opportunities.",
                'stats'   => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error during live market polling: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Polling failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Purge all dismissed or low-relevance junk requirements.
     */
    public function purgeJunk(): JsonResponse
    {
        $deleted = MarketRequirement::query()
            ->where(function ($q) {
                $q->where('status', 'rejected')
                  ->orWhere('relevance_score', '<', 50);
            })
            ->whereNull('deal_id')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully purged {$deleted} junk and dismissed items.",
            'count'   => $deleted,
        ]);
    }

    /**
     * Dismiss / Archive an RFP from the active stream.
     */
    public function dismiss(int $id): JsonResponse
    {
        $req = MarketRequirement::findOrFail($id);
        $req->update([
            'status'           => 'rejected',
            'rejection_reason' => 'Dismissed by founder in Cockpit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RFP dismissed from active stream.',
        ]);
    }

    /**
     * Convert an RFP directly into an active CRM Lead & Deal in USD.
     */
    public function convertToDeal(Request $request, int $id): JsonResponse
    {
        $req = MarketRequirement::findOrFail($id);
        $stage  = (string) $request->input('stage', 'new');

        // Accept user-supplied contact info (from ConvertToDealModal), fallback to scraped data only
        $name    = $request->input('contact_name')    ?: $req->contact_name    ?: null;
        $company = $request->input('contact_company') ?: $req->contact_company ?: null;
        $email   = $request->input('contact_email')   ?: $req->contact_email   ?: null;
        $phone   = $request->input('contact_phone')   ?: $req->contact_phone   ?: null;
        $currency = 'USD';

        // Amount: use user-supplied override, then AI estimate; null if genuinely unknown
        $rawAmount = $request->input('amount');
        $amount = $rawAmount !== null
            ? (float) $rawAmount
            : (isset($req->estimated_amount) && $req->estimated_amount > 0 ? (float) $req->estimated_amount : null);

        $deal = DB::transaction(function () use ($req, $stage, $name, $company, $email, $phone, $currency, $amount) {
            $lead = Lead::create([
                'name'             => $name,
                'email'            => $email,           // null-safe – no fake email injected
                'phone'            => $phone,           // null-safe – no placeholder phone injected
                'company'          => $company,
                'segment'          => $req->matched_segment ?: 'saas_ai',
                'source'           => $req->source,
                'status'           => 'active',
                'score'            => max(80, (int) $req->relevance_score),
                'touchpoint_count' => 1,
                'last_contact_date'=> now(),
                'next_action_date' => now()->addDays(2),
                'next_action_note' => 'Qualify contact details and send initial proposal.',
                'description'      => $req->raw_text,
            ]);

            $deal = Deal::create([
                'lead_id'             => $lead->id,
                'title'               => substr($req->title ?: "SaaS MVP Architecture for {$company}", 0, 190),
                'amount'              => $amount,
                'currency'            => $currency,
                'stage'               => $stage,
                'probability'         => $stage === 'proposal_sent' ? 65 : 40,
                'scope_summary'       => substr($req->raw_text, 0, 800),
                'pricing_tier'        => 'growth',
                'expected_close_date' => now()->addDays(21),
            ]);
            $deal->getOrCreateProposalToken();

            $req->update([
                'status'     => 'converted',
                'lead_id'    => $lead->id,
                'deal_id'    => $deal->id,
                'pitched_at' => now(),
            ]);

            Activity::create([
                'lead_id'      => $lead->id,
                'deal_id'      => $deal->id,
                'type'         => 'note',
                'subject'      => "Converted from {$req->source} Opportunity",
                'description'  => "Converted from Market Requirement #{$req->id}.\n\nAI Proposal Draft:\n{$req->pitch_draft}",
                'completed_at' => now(),
            ]);

            return $deal;
        });

        return response()->json([
            'success'        => true,
            'message'        => 'RFP successfully converted to active CRM deal in USD!',
            'deal_id'        => $deal->id,
            'proposal_token' => $deal->proposal_token,
        ]);
    }
}
