<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmPaymentController extends Controller
{
    public function createPaymentLink(Request $request, int $dealId): JsonResponse
    {
        $validated = $request->validate([
            'amount'      => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
            'percentage'  => ['nullable', 'integer', 'in:20,40,50,100'],
        ]);

        $deal = Deal::with(['lead', 'organization'])->findOrFail($dealId);
        $amount = (float) $validated['amount'];
        $currency = strtoupper((string) $deal->currency);
        $desc = $validated['description'] ?? ($deal->title . ' — Kickoff Deposit');

        $payment = Payment::create([
            'deal_id'        => $deal->id,
            'lead_id'        => $deal->lead_id,
            'gateway'        => ($currency === 'USD') ? 'stripe' : 'razorpay',
            'amount'         => $amount,
            'currency'       => $currency,
            'status'         => 'created',
            'notes'          => $desc,
        ]);

        $paymentUrl = null;

        // 1. Razorpay Integration for INR
        if ($currency === 'INR') {
            $keyId = env('RAZORPAY_KEY_ID');
            $keySecret = env('RAZORPAY_KEY_SECRET');

            if ($keyId && $keySecret) {
                try {
                    $response = Http::withBasicAuth($keyId, $keySecret)
                        ->timeout(10)
                        ->post('https://api.razorpay.com/v1/payment_links', [
                            'amount'          => (int) ($amount * 100), // Razorpay accepts in paise
                            'currency'        => 'INR',
                            'description'     => $desc,
                            'customer'        => [
                                'name'    => $deal->lead?->name ?? 'Client',
                                'email'   => $deal->lead?->email,
                                'contact' => $deal->lead?->phone,
                            ],
                            'notify'          => ['sms' => false, 'email' => false],
                            'reminder_enable' => false,
                            'reference_id'    => 'DB-PAY-' . $payment->id,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $paymentUrl = $data['short_url'] ?? null;
                        $payment->update([
                            'gateway_link_id' => $data['id'] ?? null,
                            'receipt_url'     => $paymentUrl,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Razorpay payment link creation failed: ' . $e->getMessage());
                }
            }

            if (!$paymentUrl) {
                // DigitalBuilders Direct Payment Portal Link
                $paymentUrl = url("/checkout/pay?deal={$deal->id}&pay_id={$payment->id}&amount={$amount}");
                $payment->update(['receipt_url' => $paymentUrl]);
            }
        } else {
            // 2. Stripe Integration for USD
            $stripeSecret = env('STRIPE_SECRET_KEY');
            if ($stripeSecret) {
                try {
                    $response = Http::withToken($stripeSecret)
                        ->asForm()
                        ->timeout(10)
                        ->post('https://api.stripe.com/v1/payment_links', [
                            'line_items[0][price_data][currency]'     => 'usd',
                            'line_items[0][price_data][unit_amount]' => (int) ($amount * 100),
                            'line_items[0][price_data][product_data][name]' => $desc,
                            'line_items[0][quantity]'                => 1,
                            'metadata[deal_id]'                      => $deal->id,
                            'metadata[payment_id]'                   => $payment->id,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $paymentUrl = $data['url'] ?? null;
                        $payment->update([
                            'gateway_link_id' => $data['id'] ?? null,
                            'receipt_url'     => $paymentUrl,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Stripe payment link creation failed: ' . $e->getMessage());
                }
            }

            if (!$paymentUrl) {
                $paymentUrl = url("/checkout/pay?deal={$deal->id}&pay_id={$payment->id}&amount={$amount}");
                $payment->update(['receipt_url' => $paymentUrl]);
            }
        }

        Activity::create([
            'lead_id'     => $deal->lead_id,
            'deal_id'     => $deal->id,
            'user_id'     => $request->user()?->id,
            'type'        => 'payment',
            'subject'     => "Payment Link Generated: {$payment->currency} " . number_format($amount, 0),
            'description' => "Generated deposit link for {$desc}.\nLink: {$paymentUrl}",
            'metadata'    => [
                'payment_id'  => $payment->id,
                'amount'      => $amount,
                'currency'    => $currency,
                'payment_url' => $paymentUrl,
            ],
        ]);

        return response()->json([
            'success'     => true,
            'payment_id'  => $payment->id,
            'payment_url' => $paymentUrl,
            'amount'      => $amount,
            'currency'    => $currency,
        ]);
    }

    public function recordWire(Request $request, int $dealId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'amount'          => ['required', 'numeric', 'min:1'],
            'transaction_utr' => ['required', 'string', 'max:255'],
            'payment_method'  => ['required', 'string', 'in:wire,rtgs,neft,imps,upi_direct,cheque,cash'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $deal = Deal::with('lead')->findOrFail($dealId);
        $amount = (float) $validated['amount'];

        DB::transaction(function () use ($deal, $amount, $validated, $request) {
            $payment = Payment::create([
                'deal_id'         => $deal->id,
                'lead_id'         => $deal->lead_id,
                'gateway'         => 'bank_wire',
                'amount'          => $amount,
                'currency'        => $deal->currency,
                'status'          => 'paid',
                'payment_method'  => $validated['payment_method'],
                'transaction_utr' => $validated['transaction_utr'],
                'notes'           => $validated['notes'] ?? 'Direct Bank Wire / NEFT transfer verified',
                'paid_at'         => now(),
            ]);

            $newPaid = (float) $deal->amount_paid + $amount;
            $deal->amount_paid = $newPaid;

            if ($newPaid >= (float) $deal->amount) {
                $deal->payment_status = 'paid';
                $deal->stage = 'closed_won';
                $deal->closed_at = now();
                $deal->probability = 100;
                if ($deal->lead) {
                    $deal->lead->update(['status' => 'converted', 'stage' => 'closed_won']);
                }
            } else {
                $deal->payment_status = 'partially_paid';
            }
            $deal->save();

            Activity::create([
                'lead_id'     => $deal->lead_id,
                'deal_id'     => $deal->id,
                'user_id'     => $request->user()?->id,
                'type'        => 'payment',
                'subject'     => "Payment Recorded: {$deal->currency} " . number_format($amount, 0) . " (UTR: {$validated['transaction_utr']})",
                'description' => "Verified via {$validated['payment_method']}. Total paid so far: {$deal->formatted_amount_paid} of {$deal->formatted_amount}.",
                'metadata'    => [
                    'payment_id'      => $payment->id,
                    'transaction_utr' => $validated['transaction_utr'],
                    'method'          => $validated['payment_method'],
                ],
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'stage'          => $deal->stage,
                'payment_status' => $deal->payment_status,
                'amount_paid'    => $deal->amount_paid,
                'formatted_paid' => $deal->formatted_amount_paid,
            ]);
        }

        return back()->with('success', 'Payment verified and recorded. Pipeline updated.');
    }

    public function handleRazorpayWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = $payload['event'] ?? '';

        Log::info('Razorpay CRM Webhook received: ' . $event);

        if (in_array($event, ['payment_link.paid', 'payment.captured', 'order.paid'], true)) {
            $entity = $payload['payload']['payment_link']['entity'] ?? $payload['payload']['payment']['entity'] ?? [];
            $linkId = $entity['id'] ?? null;
            $paymentId = $entity['payment_id'] ?? $entity['id'] ?? null;
            $amountPaid = isset($entity['amount_paid']) ? ($entity['amount_paid'] / 100) : (isset($entity['amount']) ? $entity['amount'] / 100 : 0);

            $payment = Payment::where('gateway_link_id', $linkId)
                ->orWhere('gateway_payment_id', $paymentId)
                ->latest()
                ->first();

            if ($payment) {
                $payment->update([
                    'status'             => 'paid',
                    'gateway_payment_id' => $paymentId,
                    'paid_at'            => now(),
                ]);

                $deal = Deal::find($payment->deal_id);
                if ($deal) {
                    $deal->amount_paid = (float) $deal->amount_paid + $amountPaid;
                    if ($deal->amount_paid >= (float) $deal->amount) {
                        $deal->payment_status = 'paid';
                        $deal->stage = 'closed_won';
                        $deal->closed_at = now();
                    } else {
                        $deal->payment_status = 'partially_paid';
                    }
                    $deal->save();

                    Activity::create([
                        'lead_id'     => $deal->lead_id,
                        'deal_id'     => $deal->id,
                        'type'        => 'payment',
                        'subject'     => "Razorpay Webhook: Payment of ₹" . number_format($amountPaid, 0) . " Confirmed",
                        'description' => "Payment ID: {$paymentId}. Deal automatically updated to {$deal->stage}.",
                    ]);
                }
            }
        }

        return response()->json(['status' => 'processed']);
    }

    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $type = $payload['type'] ?? '';

        Log::info('Stripe CRM Webhook received: ' . $type);

        if ($type === 'checkout.session.completed' || $type === 'payment_intent.succeeded') {
            $object = $payload['data']['object'] ?? [];
            $dealId = $object['metadata']['deal_id'] ?? null;
            $paymentId = $object['metadata']['payment_id'] ?? null;
            $amount = isset($object['amount_total']) ? ($object['amount_total'] / 100) : (isset($object['amount']) ? $object['amount'] / 100 : 0);

            if ($paymentId) {
                $payment = Payment::find($paymentId);
                if ($payment) {
                    $payment->update(['status' => 'paid', 'paid_at' => now()]);
                }
            }

            if ($dealId) {
                $deal = Deal::find($dealId);
                if ($deal) {
                    $deal->amount_paid = (float) $deal->amount_paid + $amount;
                    if ($deal->amount_paid >= (float) $deal->amount) {
                        $deal->payment_status = 'paid';
                        $deal->stage = 'closed_won';
                        $deal->closed_at = now();
                    } else {
                        $deal->payment_status = 'partially_paid';
                    }
                    $deal->save();
                }
            }
        }

        return response()->json(['status' => 'processed']);
    }
}
