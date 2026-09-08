<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\MarketRequirement;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * Handle incoming webhook updates from Telegram Bot (e.g. inline button clicks).
     */
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();
        Log::info('Telegram Webhook update received: ', $update);

        // Check if this update is a callback query (inline button click)
        if (isset($update['callback_query'])) {
            $cq = $update['callback_query'];
            $cqId = (string) ($cq['id'] ?? '');
            $data = (string) ($cq['data'] ?? '');

            // Format: req:approve:{id} or req:skip:{id}
            if (str_starts_with($data, 'req:approve:')) {
                $id = (int) str_replace('req:approve:', '', $data);
                $req = MarketRequirement::with('deal.lead')->find($id);

                if ($req) {
                    $req->update([
                        'status'     => 'pitched',
                        'pitched_at' => now(),
                    ]);

                    if ($req->deal) {
                        $req->deal->update([
                            'stage'       => 'contacted',
                            'probability' => 25,
                        ]);

                        if ($req->deal->lead) {
                            $req->deal->lead->increment('touchpoint_count');
                            $req->deal->lead->update([
                                'stage'             => 'contacted',
                                'last_contact_date' => now(),
                                'next_action_date'  => now()->addDays(2),
                                'next_action_note'  => 'Send Value Add / Architecture Review (Touch 2)',
                            ]);
                        }

                        Activity::create([
                            'lead_id'           => $req->deal->lead_id,
                            'deal_id'           => $req->deal->id,
                            'type'              => 'stage_change',
                            'subject'           => 'Pitch Approved via Telegram 1-Tap',
                            'description'       => "Founder approved and dispatched pitch for {$req->deal->title}. Stage advanced to Contacted.",
                            'touchpoint_number' => 1,
                        ]);
                    }

                    $this->telegramBot->answerCallbackQuery($cqId, '✅ Approved! Deal moved to Contacted.');
                    return response()->json(['success' => true, 'action' => 'approved']);
                }
            } elseif (str_starts_with($data, 'req:skip:')) {
                $id = (int) str_replace('req:skip:', '', $data);
                $req = MarketRequirement::find($id);

                if ($req) {
                    $req->update([
                        'status'           => 'rejected',
                        'rejection_reason' => 'Skipped by founder via Telegram',
                    ]);

                    $this->telegramBot->answerCallbackQuery($cqId, '❌ Requirement skipped.');
                    return response()->json(['success' => true, 'action' => 'skipped']);
                }
            }

            $this->telegramBot->answerCallbackQuery($cqId, 'Acknowledged.');
        }

        return response()->json(['status' => 'ok']);
    }
}
