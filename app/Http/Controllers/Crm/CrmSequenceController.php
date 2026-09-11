<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmSequence;
use App\Models\CrmSequenceStep;
use App\Models\Lead;
use App\Services\SalesFunnel\CrmSequenceEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CrmSequenceController extends Controller
{
    public function __construct(
        private CrmSequenceEngineService $sequenceEngine,
    ) {}

    /**
     * Retrieve or generate the 4-step sequence for a lead.
     */
    public function show(Lead $lead): JsonResponse
    {
        $sequence = $this->sequenceEngine->getOrGenerateSequence($lead);

        return response()->json([
            'sequence' => $sequence,
            'lead'     => $lead->only(['id', 'name', 'company', 'email', 'stage', 'touchpoint_count']),
        ]);
    }

    /**
     * Force re-generate AI 4-step cadence for a lead.
     */
    public function regenerate(Lead $lead): JsonResponse
    {
        // Delete draft sequence if exists
        $existing = CrmSequence::where('lead_id', $lead->id)->where('status', 'draft')->first();
        if ($existing) {
            $existing->delete();
        }

        $sequence = $this->sequenceEngine->getOrGenerateSequence($lead);

        return response()->json([
            'success'  => true,
            'sequence' => $sequence,
        ]);
    }

    /**
     * One-click approve and start the sequence (dispatches Step 1 immediately).
     */
    public function start(Request $request, CrmSequence $sequence): JsonResponse
    {
        $customSteps = $request->input('steps');

        try {
            $success = $this->sequenceEngine->approveAndStartSequence($sequence, is_array($customSteps) ? $customSteps : null);

            return response()->json([
                'success'  => $success,
                'message'  => 'Campaign launched! Touch #1 has been dispatched.',
                'sequence' => $sequence->fresh(['steps' => fn($q) => $q->orderBy('step_number', 'asc')]),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to launch sequence: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Pause an active sequence.
     */
    public function pause(CrmSequence $sequence): JsonResponse
    {
        $sequence->update([
            'status'      => 'paused',
            'stop_reason' => 'Paused by user',
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Sequence paused.',
            'sequence' => $sequence->fresh(['steps']),
        ]);
    }

    /**
     * Resume a paused sequence.
     */
    public function resume(CrmSequence $sequence): JsonResponse
    {
        $sequence->update([
            'status'      => 'active',
            'stop_reason' => null,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Sequence resumed.',
            'sequence' => $sequence->fresh(['steps']),
        ]);
    }

    /**
     * Mark a lead as replied, halting all upcoming touches.
     */
    public function markReplied(Request $request, Lead $lead): JsonResponse
    {
        $note = (string) $request->input('note', 'Prospect replied directly');
        $this->sequenceEngine->markAsReplied($lead, $note);

        return response()->json([
            'success' => true,
            'message' => 'Prospect marked as replied. Outbound sequence stopped.',
        ]);
    }

    /**
     * Update an individual sequence step's content or timing.
     */
    public function updateStep(Request $request, CrmSequence $sequence, CrmSequenceStep $step): JsonResponse
    {
        if ($step->sequence_id !== $sequence->id) {
            return response()->json(['error' => 'Step does not belong to sequence'], 404);
        }

        $validated = $request->validate([
            'subject'    => ['required', 'string', 'max:255'],
            'body_text'  => ['required', 'string', 'max:10000'],
            'delay_days' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        $step->update($validated);

        return response()->json([
            'success' => true,
            'step'    => $step,
        ]);
    }

    /**
     * Public 1-click unsubscribe endpoint.
     */
    public function unsubscribe(string $token): Response
    {
        $success = $this->sequenceEngine->unsubscribe($token);

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Unsubscribed — DigitalBuilders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #090d16; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { max-width: 480px; width: 100%; background: #0f172a; border: 1px solid #1e293b; border-radius: 16px; padding: 36px 30px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .badge { display: inline-flex; width: 52px; height: 52px; border-radius: 50%; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); align-items: center; justify-content: center; font-size: 24px; margin-bottom: 20px; }
        h1 { font-size: 22px; font-weight: 700; margin: 0 0 10px; color: #ffffff; }
        p { font-size: 14px; color: #94a3b8; line-height: 1.6; margin: 0 0 24px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; background: #2563eb; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">✓</div>
        <h1>You've Been Unsubscribed</h1>
        <p>You will not receive any further automated follow-ups regarding this requirement. We respect your inbox and appreciate your time.</p>
        <a href="https://www.digitalbuilders.in" class="btn">Return to DigitalBuilders</a>
    </div>
</body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
