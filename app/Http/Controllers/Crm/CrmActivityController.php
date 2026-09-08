<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmActivityController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'lead_id'     => ['required', 'exists:leads,id'],
            'deal_id'     => ['nullable', 'exists:deals,id'],
            'type'        => ['required', 'string', 'in:note,call,meeting,email,whatsapp,task'],
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date'    => ['nullable', 'date'],
        ]);

        $activity = Activity::create([
            'lead_id'      => $validated['lead_id'],
            'deal_id'      => $validated['deal_id'] ?? null,
            'user_id'      => $request->user()?->id,
            'type'         => $validated['type'],
            'subject'      => $validated['subject'],
            'description'  => $validated['description'] ?? null,
            'due_date'     => $validated['due_date'] ?? null,
            'completed_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'activity' => [
                    'id'             => $activity->id,
                    'type'           => $activity->type,
                    'subject'        => $activity->subject,
                    'description'    => $activity->description,
                    'created_at'     => $activity->created_at->diffForHumans(),
                    'created_at_raw' => $activity->created_at->format('d M Y, H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Activity logged successfully.');
    }

    public function logTouchpoint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id'     => ['required', 'exists:leads,id'],
            'channel'     => ['required', 'string', 'in:whatsapp,email,call'],
            'touch_number'=> ['required', 'integer', 'min:1', 'max:5'],
            'message'     => ['nullable', 'string', 'max:5000'],
            'subject'     => ['nullable', 'string', 'max:255'],
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);
        $touch = (int) $validated['touch_number'];
        $channel = $validated['channel'];

        return DB::transaction(function () use ($lead, $touch, $channel, $validated, $request) {
            // Calculate Next Action Date & Note based on the 5-Touch Cadence
            $nextDate = null;
            $nextNote = null;

            if ($touch === 1) {
                $nextDate = now()->addDays(2);
                $nextNote = 'Touch 2: Share Price Book & Scope Estimator';
            } elseif ($touch === 2) {
                $nextDate = now()->addDays(3);
                $nextNote = 'Touch 3: Send Ludhiana Case Study / Architecture Guide';
            } elseif ($touch === 3) {
                $nextDate = now()->addDays(4);
                $nextNote = 'Touch 4: Check-in Call / Voice Note on Priority';
            } elseif ($touch === 4) {
                $nextDate = now()->addDays(5);
                $nextNote = 'Touch 5: Polite Breakup / Priority Reset';
            } else {
                $nextDate = now()->addDays(30);
                $nextNote = 'Cadence Completed — Placed in Monthly Nurture Loop';
            }

            $lead->update([
                'touchpoint_count'  => max($lead->touchpoint_count ?? 0, $touch),
                'last_contact_date' => now(),
                'next_action_date'  => $nextDate,
                'next_action_note'  => $nextNote,
                'stage'             => ($lead->stage === 'new') ? 'contacted' : $lead->stage,
            ]);

            // If an active deal is in 'new' stage, advance to 'contacted'
            $deal = Deal::where('lead_id', $lead->id)->where('stage', 'new')->latest()->first();
            if ($deal) {
                $deal->update([
                    'stage'       => 'contacted',
                    'probability' => 25,
                ]);
            }

            $subject = $validated['subject'] ?? "Touch {$touch} completed via " . ucfirst($channel);

            $activity = Activity::create([
                'lead_id'           => $lead->id,
                'deal_id'           => $deal?->id,
                'user_id'           => $request->user()?->id,
                'type'              => $channel,
                'subject'           => $subject,
                'description'       => $validated['message'] ?? "Touch {$touch} dispatched via {$channel}.",
                'touchpoint_number' => $touch,
                'completed_at'      => now(),
                'metadata'          => [
                    'channel'      => $channel,
                    'touch_number' => $touch,
                    'sent_at'      => now()->toIso8601String(),
                ],
            ]);

            return response()->json([
                'success'          => true,
                'touch_number'     => $touch,
                'touchpoint_count' => $lead->touchpoint_count,
                'next_action_date' => $nextDate?->format('d M Y'),
                'next_action_note' => $nextNote,
                'activity' => [
                    'id'                => $activity->id,
                    'type'              => $activity->type,
                    'subject'           => $activity->subject,
                    'description'       => $activity->description,
                    'touchpoint_number' => $activity->touchpoint_number,
                    'created_at'        => $activity->created_at->diffForHumans(),
                    'created_at_raw'    => $activity->created_at->format('d M Y, H:i'),
                ],
            ]);
        });
    }
}
