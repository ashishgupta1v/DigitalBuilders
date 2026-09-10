<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Services\SalesFunnel\AiReplyTriageService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CrmInboundReplyController extends Controller
{
    public function __construct(
        private AiReplyTriageService $replyTriage,
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * Triage an incoming message for a specific lead directly from the CRM cockpit.
     */
    public function triageLeadReply(Request $request, int $leadId): JsonResponse
    {
        $message = $request->input('reply_text') ?? $request->input('message');
        if (empty($message) || !is_string($message) || strlen(trim($message)) < 3) {
            return response()->json(['error' => 'A reply message of at least 3 characters is required.'], 422);
        }

        $lead = Lead::with(['deals' => fn($q) => $q->latest()->limit(1)])->findOrFail($leadId);
        $deal = $lead->deals->first();
        $dealTitle = $deal?->title ?: ($lead->company ?: $lead->name . ' Project');

        $triageResult = $this->replyTriage->triageReply(
            incomingMessage: trim($message),
            clientName: $lead->name,
            dealTitle: $dealTitle
        );

        $intent = $triageResult['intent'] ?? 'general_inquiry';
        $suggestedResponse = $triageResult['response'] ?? '';
        $action = $triageResult['action'] ?? 'clarify_and_call';
        $confidence = $triageResult['confidence'] ?? 0.95;

        if ($request->boolean('apply_stage_action', true)) {
            // Apply automated stage adjustments based on intent
            if ($intent === 'meeting_request' && $deal) {
                $deal->update(['stage' => 'discovery_done', 'probability' => 60]);
                $lead->update(['stage' => 'discovery_done', 'next_action_note' => 'Conduct Architecture Discovery Sync']);
            } elseif ($intent === 'price_objection') {
                $lead->update(['objection_flag' => 'price_objection']);
            } elseif ($intent === 'lost' && $deal) {
                $deal->update(['stage' => 'closed_lost', 'loss_reason' => 'Client expressed no interest']);
                $lead->update(['status' => 'archived', 'stage' => 'closed_lost']);
            }

            Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal?->id,
                'type'              => 'note',
                'subject'           => "Prospect Reply Triaged: " . ucwords(str_replace('_', ' ', $intent)),
                'description'       => "Message snippet: \"" . substr(trim($message), 0, 150) . "...\"\nTriage Action: {$action}",
                'touchpoint_number' => (int) $lead->touchpoint_count,
                'metadata'          => [
                    'intent'              => $intent,
                    'action'              => $action,
                    'suggested_response'  => $suggestedResponse,
                    'battlecard_response' => $suggestedResponse,
                ],
            ]);
        }

        return response()->json([
            'success'             => true,
            'lead_id'             => $lead->id,
            'intent'              => $intent,
            'action'              => $action,
            'confidence'          => $confidence,
            'suggested_response'  => $suggestedResponse,
            'battlecard_response' => $suggestedResponse,
            'deal_stage'          => $deal?->fresh()->stage,
        ]);
    }

    /**
     * Inbound webhook handler for external reply integrations (Email webhooks / WhatsApp).
     */
    public function handleExternalWebhook(Request $request): JsonResponse
    {
        $expectedSecret = env('CRM_WEBHOOK_SECRET');
        $providedSecret = $request->header('X-CRM-SECRET') ?? $request->query('secret');

        if (!empty($expectedSecret) && $providedSecret !== $expectedSecret) {
            return response()->json(['error' => 'Unauthorized webhook signature.'], 401);
        }

        $senderEmail = $request->input('from_email') ?? $request->input('sender');
        $senderPhone = $request->input('from_phone');
        $incomingText = (string) ($request->input('body') ?? $request->input('message') ?? $request->input('text') ?? '');

        if (empty($incomingText)) {
            return response()->json(['error' => 'Empty message body.'], 422);
        }

        // Find lead by email or phone
        $lead = null;
        if ($senderEmail) {
            $lead = Lead::where('email', $senderEmail)->latest()->first();
        }
        if (!$lead && $senderPhone) {
            $cleanDigits = preg_replace('/[^0-9]/', '', $senderPhone);
            $lead = Lead::where('phone', 'like', "%{$cleanDigits}%")->latest()->first();
        }

        if (!$lead) {
            Log::info('CRM Inbound Reply Webhook: No existing lead found for ' . ($senderEmail ?: $senderPhone));
            return response()->json(['message' => 'No matching lead found.'], 200);
        }

        $deal = $lead->deals()->latest()->first();
        $dealTitle = $deal?->title ?: ($lead->company ?: $lead->name);

        $triage = $this->replyTriage->triageReply($incomingText, $lead->name, $dealTitle);

        Activity::create([
            'lead_id'           => $lead->id,
            'deal_id'           => $deal?->id,
            'type'              => 'email',
            'subject'           => "Inbound Reply Received: " . ucwords(str_replace('_', ' ', $triage['intent'])),
            'description'       => $incomingText,
            'touchpoint_number' => (int) $lead->touchpoint_count,
            'metadata'          => $triage,
        ]);

        // Send Telegram alert to Ashish with 1-tap recommended response
        $intentLabel = strtoupper(str_replace('_', ' ', $triage['intent']));
        $text = "📩 *INBOUND REPLY TRIAGED: {$intentLabel}*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "• *Prospect:* {$lead->name} (" . ($lead->company ?: 'Direct') . ")\n"
            . "• *Email:* {$lead->email}\n\n"
            . "💬 *Prospect Wrote:*\n_{$incomingText}_\n\n"
            . "🤖 *Suggested Battlecard Response:*\n```\n{$triage['response']}\n```\n\n"
            . "👉 Open Lead Cockpit: " . url('/crm');

        $this->telegramBot->sendMessage($text);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'triage'  => $triage,
        ]);
    }
}
