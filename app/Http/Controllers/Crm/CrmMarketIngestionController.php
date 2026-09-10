<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\MarketRequirement;
use App\Services\SalesFunnel\AiPitchGeneratorService;
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
     * Documentation: https://help.indiamart.com/knowledge-base/integration-of-indiamarts-lead-manager-crm-push-api-with-third-party-crms
     */
    public function handleIndiaMartPush(Request $request): JsonResponse
    {
        // IndiaMART pushes either JSON or form-urlencoded parameters
        $data = $request->all();
        Log::info('IndiaMART Push Webhook received: ', $data);

        // Extract key parameters from IndiaMART payload specification
        $externalId = (string) ($data['QUERY_ID'] ?? $data['query_id'] ?? $data['UNIQUE_QUERY_ID'] ?? uniqid('im_'));
        $name = trim((string) ($data['SENDER_NAME'] ?? $data['sender_name'] ?? 'IndiaMART Inquirer'));
        $phone = trim((string) ($data['GLUSR_USR_PH_MOBILE'] ?? $data['sender_mobile'] ?? $data['SENDER_MOBILE'] ?? ''));
        $email = trim((string) ($data['SENDER_EMAIL'] ?? $data['sender_email'] ?? ''));
        $company = trim((string) ($data['SENDER_COMPANY'] ?? $data['sender_company'] ?? ''));
        $city = trim((string) ($data['SENDER_CITY'] ?? $data['sender_city'] ?? 'India'));
        $product = trim((string) ($data['PRODUCT_NAME'] ?? $data['product_name'] ?? 'Software Solution'));
        $message = trim((string) ($data['ENQ_MESSAGE'] ?? $data['enq_message'] ?? $data['QUERY_MODID'] ?? 'Inquiry via IndiaMART'));

        $fullScopeText = "Product: {$product}. Inquiry: {$message}";

        // Idempotency: Prevent duplicate ingestion of the exact same inquiry ID
        $existing = MarketRequirement::where('source', 'indiamart')
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Requirement already ingested.',
                'requirement_id' => $existing->id,
            ], 200);
        }

        // Score relevance and generate pitch
        $score = $this->pitchGenerator->scoreRelevance($fullScopeText, null, $phone, $email);
        $pitchData = $this->pitchGenerator->generatePitch($fullScopeText, $name, $company, 'INR');

        $amount = 185000.00;
        if ($pitchData['segment'] === 'manufacturing') {
            $amount = 250000.00;
        } elseif ($pitchData['segment'] === 'ecommerce') {
            $amount = 160000.00;
        }

        $result = DB::transaction(function () use ($externalId, $name, $phone, $email, $company, $city, $fullScopeText, $pitchData, $score, $amount, $data) {
            // 1. Create Market Requirement Record
            $req = MarketRequirement::create([
                'source'           => 'indiamart',
                'external_id'      => $externalId,
                'title'            => $name . ' — IndiaMART: ' . substr($data['PRODUCT_NAME'] ?? 'Software', 0, 50),
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
                'pitch_draft'      => $pitchData['short_pitch'],
                'status'           => 'qualified',
                'metadata'         => $data,
            ]);

            // 2. Ingest into CRM Lead Engine
            $lead = Lead::create([
                'name'             => $name,
                'email'            => $email ?: ('im-' . substr($phone, -6) . '@digitalbuilders.in'),
                'phone'            => $phone,
                'company'          => $company ?: 'IndiaMART Buyer',
                'project_type'     => $data['PRODUCT_NAME'] ?? 'Custom Software',
                'description'      => $fullScopeText,
                'segment'          => $pitchData['segment'],
                'source'           => 'indiamart',
                'region'           => 'IN',
                'stage'            => 'new',
                'score'            => $score,
                'touchpoint_count' => 0,
                'next_action_date' => now(),
                'next_action_note' => 'Dispatch Tailored Touch 1 Pitch on WhatsApp/Call',
                'estimated_value'  => $pitchData['budget_range'],
            ]);

            // 3. Create Corresponding Deal in CRM Pipeline
            $deal = Deal::create([
                'title'               => $name . ' — ' . ($data['PRODUCT_NAME'] ?? 'Custom ERP/App'),
                'lead_id'             => $lead->id,
                'amount'              => $amount,
                'currency'            => 'INR',
                'stage'               => 'new',
                'probability'         => 15,
                'expected_close_date' => now()->addDays(21),
                'scope_summary'       => $fullScopeText,
            ]);

            $req->update([
                'lead_id' => $lead->id,
                'deal_id' => $deal->id,
            ]);

            Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'IndiaMART Lead Ingested into Pipeline',
                'description'       => "Auto-matched to {$pitchData['case_study']} case study. Scored: {$score} pts.",
                'touchpoint_number' => 0,
            ]);

            return $req;
        });

        // 4. Send Instant Push Notification Card to Founder on Telegram with 1-Tap Approval
        $this->telegramBot->sendOpportunityAlert($result, $pitchData);

        return response()->json([
            'success'        => true,
            'message'        => 'IndiaMART inquiry ingested and scored successfully.',
            'requirement_id' => $result->id,
            'lead_id'        => $result->lead_id,
            'deal_id'        => $result->deal_id,
            'segment'        => $pitchData['segment'],
        ], 201);
    }

    /**
     * General external requirement ingestion (for Upwork, RSS feeds, or manual triggers).
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

        $currency = strtoupper($validated['currency'] ?? 'INR');
        $externalId = $validated['external_id'] ?? md5($validated['title'] . $validated['raw_text']);

        // Check duplicate
        $existing = MarketRequirement::where('source', $validated['source'])
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'message' => 'Already ingested', 'requirement_id' => $existing->id], 200);
        }

        $score = $this->pitchGenerator->scoreRelevance(
            $validated['raw_text'],
            $validated['budget_raw'] ?? null,
            $validated['contact_phone'] ?? null,
            $validated['contact_email'] ?? null
        );

        $pitchData = $this->pitchGenerator->generatePitch(
            $validated['raw_text'],
            $validated['contact_name'] ?? null,
            $validated['contact_company'] ?? null,
            $currency,
            $validated['budget_raw'] ?? null
        );

        $estimatedAmount = ($currency === 'USD') ? 5000.00 : 185000.00;

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
            'location'         => $validated['location'] ?? 'Global',
            'matched_segment'  => $pitchData['segment'],
            'relevance_score'  => $score,
            'pitch_draft'      => $pitchData['short_pitch'],
            'status'           => 'qualified',
        ]);

        // Push alert to Telegram
        $this->telegramBot->sendOpportunityAlert($req, $pitchData);

        return response()->json([
            'success'        => true,
            'requirement_id' => $req->id,
            'segment'        => $pitchData['segment'],
            'score'          => $score,
            'pitch'          => $pitchData['upwork_proposal'] ?? $pitchData['short_pitch'],
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
                'message' => "Polling cycle complete. Newly ingested: {$stats['total']} RFPs.",
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
     * Convert an RFP directly into an active CRM Lead & Deal.
     */
    public function convertToDeal(Request $request, int $id): JsonResponse
    {
        $req = MarketRequirement::findOrFail($id);
        $stage = (string) $request->input('stage', 'proposal_sent');

        $name = $req->contact_name ?: ($req->contact_company ?: 'RFP Prospect');
        $company = $req->contact_company ?: 'Direct Client';
        $email = $req->contact_email ?: ('rfp-' . $req->id . '@digitalbuilders.in');
        $currency = $req->currency ?: 'USD';
        $amount = (float) ($req->estimated_amount ?: ($currency === 'USD' ? 5000.00 : 185000.00));

        $deal = DB::transaction(function () use ($req, $stage, $name, $company, $email, $currency, $amount) {
            $lead = Lead::create([
                'name'             => $name,
                'email'            => $email,
                'phone'            => $req->contact_phone,
                'company'          => $company,
                'segment'          => $req->matched_segment ?: 'general',
                'status'           => 'active',
                'score'            => max(75, (int) $req->relevance_score),
                'touchpoint_count' => 1,
                'last_contact_date'=> now(),
                'next_action_date' => now()->addDays(2),
                'next_action_note' => 'Follow up on proposal (Touch 2)',
            ]);

            $deal = Deal::create([
                'lead_id'             => $lead->id,
                'title'               => substr($req->title ?: "Project for {$company}", 0, 190),
                'amount'              => $amount,
                'currency'            => $currency,
                'stage'               => $stage,
                'probability'         => $stage === 'proposal_sent' ? 60 : 35,
                'scope_summary'       => substr($req->raw_text, 0, 500),
                'expected_close_date' => now()->addDays(14),
            ]);

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
                'subject'      => "Converted from {$req->source} RFP",
                'body'         => "Converted from Market Requirement #{$req->id}.\n\nPitch Draft:\n{$req->pitch_draft}",
                'performed_at' => now(),
            ]);

            return $deal;
        });

        return response()->json([
            'success' => true,
            'message' => 'RFP successfully converted to active CRM deal!',
            'deal_id' => $deal->id,
        ]);
    }
}

