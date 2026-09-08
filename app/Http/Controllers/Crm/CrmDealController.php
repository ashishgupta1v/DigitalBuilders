<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmDealController extends Controller
{
    public function updateStage(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'stage'       => ['required', 'string', 'in:new,contacted,qualified,discovery_done,proposal_sent,negotiation,closed_won,closed_lost'],
            'loss_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $deal = Deal::with('lead')->findOrFail($id);
        $oldStage = $deal->stage;
        $newStage = $validated['stage'];

        if ($oldStage === $newStage) {
            return response()->json(['success' => true, 'deal' => $deal]);
        }

        DB::transaction(function () use ($deal, $oldStage, $newStage, $validated, $request) {
            $probability = Deal::DEFAULT_PROBABILITIES[$newStage] ?? $deal->probability;

            $updateData = [
                'stage'       => $newStage,
                'probability' => $probability,
            ];

            if ($newStage === 'closed_won') {
                $updateData['closed_at'] = now();
                $updateData['loss_reason'] = null;
                if ($deal->lead) {
                    $deal->lead->update(['status' => 'converted', 'stage' => 'closed_won']);
                }
            } elseif ($newStage === 'closed_lost') {
                $updateData['loss_reason'] = $validated['loss_reason'] ?? 'Unresponsive / Budget mismatch';
                if ($deal->lead) {
                    $deal->lead->update(['status' => 'archived', 'stage' => 'closed_lost']);
                }
            } else {
                if ($deal->lead) {
                    $deal->lead->update(['stage' => $newStage]);
                }
            }

            $deal->update($updateData);

            $oldStageLabel = Deal::STAGES[$oldStage] ?? $oldStage;
            $newStageLabel = Deal::STAGES[$newStage] ?? $newStage;

            Activity::create([
                'lead_id'     => $deal->lead_id,
                'deal_id'     => $deal->id,
                'user_id'     => $request->user()?->id,
                'type'        => 'stage_change',
                'subject'     => "Deal Stage Advanced: {$oldStageLabel} → {$newStageLabel}",
                'description' => "Probability updated to {$probability}%. Target value: {$deal->formatted_amount}.",
            ]);
        });

        return response()->json([
            'success'          => true,
            'deal_id'          => $deal->id,
            'stage'            => $newStage,
            'probability'      => $deal->probability,
            'formatted_amount' => $deal->formatted_amount,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $deal = Deal::findOrFail($id);

        $validated = $request->validate([
            'title'               => ['sometimes', 'string', 'max:255'],
            'amount'              => ['sometimes', 'numeric', 'min:0'],
            'currency'            => ['sometimes', 'string', 'in:INR,USD'],
            'probability'         => ['sometimes', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'pricing_tier'        => ['nullable', 'string'],
            'scope_summary'       => ['nullable', 'string'],
        ]);

        $deal->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'deal' => $deal]);
        }

        return back()->with('success', 'Deal details updated.');
    }
}
