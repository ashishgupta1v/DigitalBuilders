<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Payment;
use App\Mail\ProposalAcceptedMail;
use App\Modules\Library\Application\Interfaces\MarkdownRendererInterface;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CrmProposalController extends Controller
{
    public function __construct(
        private MarkdownRendererInterface $markdownRenderer,
        private TelegramBotService $telegramBot,
    ) {}

    /**
     * Fetch existing proposal or dynamically generate a tailored executive proposal draft.
     */
    public function show(int $id): JsonResponse
    {
        $deal = Deal::query()->with(['lead', 'organization'])->findOrFail($id);

        $token = $deal->getOrCreateProposalToken();
        $content = $deal->proposal_content;

        if (empty($content)) {
            $content = $this->buildDefaultProposalMarkdown($deal);
        }

        return response()->json([
            'deal_id'              => $deal->id,
            'title'                => $deal->title,
            'amount'               => (float) $deal->amount,
            'formatted_amount'     => $deal->formatted_amount,
            'currency'             => $deal->currency,
            'stage'                => $deal->stage,
            'proposal_content'     => $content,
            'proposal_html'        => $this->markdownRenderer->toHtml($content),
            'proposal_token'       => $token,
            'public_url'           => url("/proposal/{$token}"),
            'proposal_sent_at'     => $deal->proposal_sent_at?->format('d M Y, h:i A'),
            'proposal_viewed_at'   => $deal->proposal_viewed_at?->format('d M Y, h:i A'),
            'proposal_accepted_at' => $deal->proposal_accepted_at?->format('d M Y, h:i A'),
            'client_name'          => $deal->lead->name ?? 'Valued Client',
            'client_phone'         => $deal->lead->phone ?? '',
            'company_name'         => $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Partner',
        ]);
    }

    /**
     * Save custom edits made to the proposal Markdown.
     */
    public function save(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'proposal_content' => ['required', 'string'],
        ]);

        $deal = Deal::query()->findOrFail($id);
        $deal->update([
            'proposal_content' => $validated['proposal_content'],
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Proposal saved successfully!',
            'proposal_html' => $this->markdownRenderer->toHtml($validated['proposal_content']),
        ]);
    }

    /**
     * Mark proposal as sent to the client, update deal stage and probability, and log activity.
     */
    public function send(Request $request, int $id): JsonResponse
    {
        $deal = Deal::query()->with('lead')->findOrFail($id);
        $token = $deal->getOrCreateProposalToken();

        $deal->update([
            'proposal_sent_at' => now(),
            'stage'            => 'proposal_sent',
            'probability'      => Deal::DEFAULT_PROBABILITIES['proposal_sent'] ?? 75,
        ]);

        if ($deal->lead) {
            $deal->lead->increment('touchpoint_count');
            $deal->lead->update([
                'last_contact_date' => now(),
                'next_action_date'  => now()->addDays(2),
                'next_action_note'  => 'Follow-up on Proposal Review (Touch 3)',
                'stage'             => 'proposal_sent',
            ]);

            Activity::create([
                'lead_id'           => $deal->lead->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'Formal Engineering Proposal Dispatched',
                'description'       => "Client proposal sent via WhatsApp/Email for {$deal->title}. Target Investment: {$deal->formatted_amount}.",
                'touchpoint_number' => (int) $deal->lead->touchpoint_count,
            ]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Proposal dispatched! Deal moved to Proposal Sent (75% probability).',
            'stage'      => 'proposal_sent',
            'public_url' => url("/proposal/{$token}"),
        ]);
    }

    /**
     * Public client view for the proposal (/proposal/{token}).
     */
    public function publicView(string $token): Response
    {
        $deal = Deal::query()
            ->with(['lead', 'organization'])
            ->where('proposal_token', $token)
            ->firstOrFail();

        // Record first viewed timestamp and activity
        if (is_null($deal->proposal_viewed_at)) {
            $deal->update(['proposal_viewed_at' => now()]);

            if ($deal->lead) {
                Activity::create([
                    'lead_id'     => $deal->lead->id,
                    'deal_id'     => $deal->id,
                    'type'        => 'meeting',
                    'subject'     => 'Client Opened Web Proposal',
                    'description' => "Client opened the proposal portal for {$deal->title}.",
                ]);
            }
        }

        // Real-time Telegram notification on view
        try {
            $clientName = $deal->lead->name ?? 'Valued Prospect';
            $company = $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Partner';
            $this->telegramBot->sendMessage(
                "👀 *CLIENT OPENED PROPOSAL PORTAL!*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "• *Client:* {$clientName} ({$company})\n"
                . "• *Project:* {$deal->title}\n"
                . "• *Deal Value:* {$deal->formatted_amount} ({$deal->currency})\n"
                . "• *Current Stage:* {$deal->stage}\n\n"
                . "👉 View Portal: " . url("/proposal/{$token}")
            );
        } catch (\Throwable $e) {
            Log::warning('Proposal view telegram alert failed: ' . $e->getMessage());
        }

        $content = $deal->proposal_content ?: $this->buildDefaultProposalMarkdown($deal);
        $html = $this->markdownRenderer->toHtml($content);

        $amount = (float) $deal->amount;
        $currency = strtoupper((string) ($deal->currency ?: 'INR'));
        $sym = $currency === 'INR' ? '₹' : '$';

        $milestones = [
            'kickoff_40' => [
                'key'         => 'kickoff_40',
                'title'       => 'Sprint 1 Kickoff Deposit (40%)',
                'percentage'  => 40,
                'amount'      => round($amount * 0.40, 2),
                'formatted'   => $sym . number_format($amount * 0.40),
                'description' => 'Architectural blueprint, high-fidelity UI design, database schemas, and dev server setup.',
                'recommended' => true,
            ],
            'milestone_50' => [
                'key'         => 'milestone_50',
                'title'       => 'Two-Phase Milestone (50% / 50%)',
                'percentage'  => 50,
                'amount'      => round($amount * 0.50, 2),
                'formatted'   => $sym . number_format($amount * 0.50),
                'description' => 'Phase 1 Sprint deposit with balance due upon user acceptance testing (UAT).',
                'recommended' => false,
            ],
            'full_100' => [
                'key'         => 'full_100',
                'title'       => '100% Upfront (5% Architect Discount)',
                'percentage'  => 100,
                'amount'      => round($amount * 0.95, 2),
                'formatted'   => $sym . number_format($amount * 0.95),
                'savings'     => $sym . number_format($amount * 0.05),
                'description' => 'Fast-track engineering queue with instant team dedicated allocation and priority SLA.',
                'recommended' => false,
            ],
        ];

        $payments = $deal->payments()->latest()->get()->map(fn($p) => [
            'id'              => $p->id,
            'amount'          => (float) $p->amount,
            'currency'        => $p->currency,
            'status'          => $p->status,
            'gateway'         => $p->gateway,
            'payment_method'  => $p->payment_method,
            'transaction_utr' => $p->transaction_utr,
            'created_at'      => $p->created_at->format('d M Y, h:i A'),
            'paid_at'         => $p->paid_at?->format('d M Y, h:i A'),
        ]);

        $bankDetails = [
            'account_name' => config('services.crm.bank_name_holder', 'DigitalBuilders Engineering (Ashish Gupta)'),
            'bank_name'    => config('services.crm.bank_name', 'HDFC Bank Ltd'),
            'account_no'   => config('services.crm.bank_account_no', '50200084729104'),
            'ifsc_code'    => config('services.crm.bank_ifsc', 'HDFC0000240'),
            'swift_code'   => config('services.crm.bank_swift', 'HDFCINBBXXX'),
            'upi_id'       => config('services.crm.bank_upi', '9087021592@upi'),
            'gstin'        => config('services.crm.gstin', '07AAACD1234F1Z5'),
        ];

        return Inertia::render('Crm/PublicProposal', [
            'deal' => [
                'id'                   => $deal->id,
                'title'                => $deal->title,
                'amount'               => $amount,
                'formatted_amount'     => $deal->formatted_amount,
                'currency'             => $currency,
                'stage'                => $deal->stage,
                'pricing_tier'         => $deal->pricing_tier,
                'proposal_sent_at'     => $deal->proposal_sent_at?->format('d M Y'),
                'proposal_accepted_at' => $deal->proposal_accepted_at?->format('d M Y, h:i A'),
            ],
            'lead' => [
                'name'       => $deal->lead->name ?? 'Valued Client',
                'company'    => $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Partner',
                'role_title' => $deal->lead->role_title ?? 'Decision Maker',
            ],
            'proposal' => [
                'token'      => $token,
                'content'    => $content,
                'html'       => $html,
                'public_url' => url("/proposal/{$token}"),
            ],
            'milestones'   => $milestones,
            'payments'     => $payments,
            'bank_details' => $bankDetails,
        ]);
    }

    /**
     * Client accepts the proposal via the public portal.
     */
    public function accept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $deal = Deal::query()->with('lead')->where('proposal_token', $token)->firstOrFail();

        $deal->update([
            'proposal_accepted_at' => now(),
            'stage'                => 'negotiation',
            'probability'          => 85,
        ]);

        if ($deal->lead) {
            $deal->lead->update([
                'stage'            => 'negotiation',
                'next_action_date' => now(),
                'next_action_note' => 'Prepare Kickoff Invoice / Advance Deposit',
            ]);

            Activity::create([
                'lead_id'     => $deal->lead->id,
                'deal_id'     => $deal->id,
                'type'        => 'stage_change',
                'subject'     => 'Client Accepted Proposal Online',
                'description' => "Proposal formally accepted online by {$deal->lead->name} for {$deal->title}.",
            ]);
        }

        // Dispatch instant leadership email alert
        try {
            $to = config('mail.lead_inbox', 'ashishgupta1v@gmail.com');
            Mail::to($to)->send(new ProposalAcceptedMail($deal));
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch proposal accepted email: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proposal accepted successfully! Our lead architect will reach out with kickoff details.',
            ]);
        }

        return back()->with('success', 'Proposal accepted successfully! We look forward to building together.');
    }

    /**
     * Generate checkout payment link for self-serve client deposit.
     */
    public function createClientPaymentLink(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'plan_type' => ['required', 'string', 'in:kickoff_40,milestone_50,full_100'],
        ]);

        $deal = Deal::query()->with(['lead', 'organization'])->where('proposal_token', $token)->firstOrFail();
        $totalAmount = (float) $deal->amount;
        $planType = $validated['plan_type'];

        $chargeAmount = match ($planType) {
            'kickoff_40'   => round($totalAmount * 0.40, 2),
            'milestone_50' => round($totalAmount * 0.50, 2),
            'full_100'     => round($totalAmount * 0.95, 2), // 5% upfront architect discount
            default        => round($totalAmount * 0.40, 2),
        };

        $currency = strtoupper((string) ($deal->currency ?: 'INR'));
        $planTitle = match ($planType) {
            'kickoff_40'   => 'Sprint 1 Kickoff Deposit (40%)',
            'milestone_50' => 'Two-Phase Milestone Deposit (50%)',
            'full_100'     => '100% Upfront (with 5% Discount)',
            default        => 'Project Deposit',
        };

        $desc = "{$deal->title} — {$planTitle}";

        $payment = Payment::create([
            'deal_id'  => $deal->id,
            'lead_id'  => $deal->lead_id,
            'gateway'  => ($currency === 'USD') ? 'stripe' : 'razorpay',
            'amount'   => $chargeAmount,
            'currency' => $currency,
            'status'   => 'created',
            'notes'    => $desc,
        ]);

        $checkoutUrl = null;

        // 1. Razorpay Integration for INR
        if ($currency === 'INR') {
            $keyId = config('services.razorpay.key_id') ?? env('RAZORPAY_KEY_ID');
            $keySecret = config('services.razorpay.key_secret') ?? env('RAZORPAY_KEY_SECRET');

            if ($keyId && $keySecret) {
                try {
                    $response = Http::withBasicAuth($keyId, $keySecret)
                        ->timeout(10)
                        ->post('https://api.razorpay.com/v1/payment_links', [
                            'amount'          => (int) ($chargeAmount * 100),
                            'currency'        => 'INR',
                            'description'     => $desc,
                            'customer'        => [
                                'name'    => $deal->lead?->name ?? 'Valued Client',
                                'email'   => $deal->lead?->email,
                                'contact' => $deal->lead?->phone,
                            ],
                            'notify'          => ['sms' => false, 'email' => false],
                            'reminder_enable' => false,
                            'reference_id'    => 'DB-PROP-' . $payment->id,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $checkoutUrl = $data['short_url'] ?? null;
                        $payment->update([
                            'gateway_link_id' => $data['id'] ?? null,
                            'receipt_url'     => $checkoutUrl,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Razorpay client link error: ' . $e->getMessage());
                }
            }
        } else {
            // 2. Stripe Integration for USD
            $stripeSecret = config('services.stripe.secret_key') ?? env('STRIPE_SECRET_KEY');
            if ($stripeSecret) {
                try {
                    $response = Http::withToken($stripeSecret)
                        ->asForm()
                        ->timeout(10)
                        ->post('https://api.stripe.com/v1/payment_links', [
                            'line_items[0][price_data][currency]'     => 'usd',
                            'line_items[0][price_data][unit_amount]' => (int) ($chargeAmount * 100),
                            'line_items[0][price_data][product_data][name]' => $desc,
                            'line_items[0][quantity]'                => 1,
                            'metadata[deal_id]'                      => $deal->id,
                            'metadata[payment_id]'                   => $payment->id,
                            'metadata[proposal_token]'               => $token,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $checkoutUrl = $data['url'] ?? null;
                        $payment->update([
                            'gateway_link_id' => $data['id'] ?? null,
                            'receipt_url'     => $checkoutUrl,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Stripe client link error: ' . $e->getMessage());
                }
            }
        }

        if (!$checkoutUrl) {
            $checkoutUrl = url("/checkout/pay?deal={$deal->id}&pay_id={$payment->id}&amount={$chargeAmount}");
            $payment->update(['receipt_url' => $checkoutUrl]);
        }

        // Real-time Telegram alert
        try {
            $clientName = $deal->lead->name ?? 'Valued Client';
            $company = $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Client';
            $sym = $currency === 'INR' ? '₹' : '$';

            $this->telegramBot->sendMessage(
                "💳 *PROPOSAL CHECKOUT INITIATED!*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "• *Client:* {$clientName} ({$company})\n"
                . "• *Project:* {$deal->title}\n"
                . "• *Selected Plan:* {$planTitle}\n"
                . "• *Checkout Amount:* `{$sym}" . number_format($chargeAmount) . " {$currency}`\n\n"
                . "🔗 Checkout: {$checkoutUrl}"
            );
        } catch (\Throwable $e) {
            Log::warning('Checkout init telegram alert failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'      => true,
            'checkout_url' => $checkoutUrl,
            'amount'       => $chargeAmount,
            'plan_title'   => $planTitle,
            'currency'     => $currency,
            'payment_id'   => $payment->id,
        ]);
    }

    /**
     * Submit Bank Wire / NEFT / IMPS / RTGS UTR transaction reference for verification.
     */
    public function submitWirePayment(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'transaction_utr' => ['required', 'string', 'min:4', 'max:100'],
            'amount'          => ['required', 'numeric', 'min:1'],
            'plan_type'       => ['nullable', 'string'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $deal = Deal::query()->with(['lead', 'organization'])->where('proposal_token', $token)->firstOrFail();
        $amount = (float) $validated['amount'];
        $currency = strtoupper((string) ($deal->currency ?: 'INR'));

        $payment = Payment::create([
            'deal_id'         => $deal->id,
            'lead_id'         => $deal->lead_id,
            'gateway'         => 'bank_wire',
            'amount'          => $amount,
            'currency'        => $currency,
            'status'          => 'pending_verification',
            'payment_method'  => 'wire',
            'transaction_utr' => $validated['transaction_utr'],
            'notes'           => $validated['notes'] ?? 'Client wire deposit submitted on proposal portal',
        ]);

        // If deal is not yet marked accepted, record acceptance
        if (!$deal->proposal_accepted_at) {
            $deal->update([
                'proposal_accepted_at' => now(),
                'stage'                => 'negotiation',
                'probability'          => 85,
            ]);
        }

        if ($deal->lead) {
            Activity::create([
                'lead_id'     => $deal->lead->id,
                'deal_id'     => $deal->id,
                'type'        => 'stage_change',
                'subject'     => 'Client Submitted Bank Wire / UTR Deposit',
                'description' => "Client submitted UTR '{$validated['transaction_utr']}' for amount {$currency} {$amount}. Verification pending.",
            ]);
        }

        // Instant Telegram alert to leadership
        try {
            $clientName = $deal->lead->name ?? 'Valued Client';
            $company = $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Client';
            $sym = $currency === 'INR' ? '₹' : '$';

            $this->telegramBot->sendMessage(
                "🏦 *BANK WIRE / UTR SUBMITTED BY CLIENT!*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "• *Client:* {$clientName} ({$company})\n"
                . "• *Project:* {$deal->title}\n"
                . "• *UTR / Ref No:* `{$validated['transaction_utr']}`\n"
                . "• *Claimed Amount:* `{$sym}" . number_format($amount) . " {$currency}`\n"
                . "• *Status:* Pending Bank Verification\n\n"
                . "👉 Verify in Cockpit: " . url("/crm")
            );
        } catch (\Throwable $e) {
            Log::warning('Telegram wire alert failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank wire transaction reference submitted! Our finance team is verifying the credit.',
            'payment' => [
                'id'              => $payment->id,
                'amount'          => (float) $payment->amount,
                'currency'        => $payment->currency,
                'status'          => $payment->status,
                'transaction_utr' => $payment->transaction_utr,
                'created_at'      => $payment->created_at->format('d M Y, h:i A'),
            ],
        ]);
    }

    /**
     * Public Proforma & Tax Invoice view (/proposal/{token}/invoice).
     */
    public function invoice(string $token): Response
    {
        $deal = Deal::query()
            ->with(['lead', 'organization', 'payments'])
            ->where('proposal_token', $token)
            ->firstOrFail();

        $invoiceData = $this->buildInvoiceData($deal, true);

        return Inertia::render('Crm/Invoice', $invoiceData);
    }

    /**
     * CRM Admin Deal Tax Invoice view (/crm/deals/{id}/invoice).
     */
    public function dealInvoice(int $id): Response
    {
        $deal = Deal::query()
            ->with(['lead', 'organization', 'payments'])
            ->findOrFail($id);

        $invoiceData = $this->buildInvoiceData($deal, false);

        return Inertia::render('Crm/Invoice', $invoiceData);
    }

    /**
     * Helper to assemble structured invoice props for Inertia.
     */
    private function buildInvoiceData(Deal $deal, bool $isPublic = true): array
    {
        $currency = strtoupper((string) ($deal->currency ?: 'INR'));
        $amount = (float) $deal->amount;
        $sym = $currency === 'INR' ? '₹' : '$';

        $paidAmount = (float) $deal->payments()
            ->where('status', 'paid')
            ->sum('amount');

        $pendingWireAmount = (float) $deal->payments()
            ->where('status', 'pending_verification')
            ->sum('amount');

        $balanceDue = max(0.0, $amount - $paidAmount);

        // Status stamp
        $statusStamp = 'PAYMENT DUE';
        if ($balanceDue <= 0.0 && $paidAmount > 0) {
            $statusStamp = 'PAID IN FULL';
        } elseif ($paidAmount > 0) {
            $statusStamp = 'DEPOSIT RECEIVED';
        } elseif ($pendingWireAmount > 0) {
            $statusStamp = 'VERIFICATION PENDING';
        }

        $gstRate = ($currency === 'INR') ? 18.0 : 0.0;
        $baseAmount = $amount;
        $gstAmount = round(($baseAmount * $gstRate) / 100, 2);
        $totalWithGst = $baseAmount + $gstAmount;

        $invoiceNo = 'DB-INV-' . date('Y') . '-' . str_pad((string) $deal->id, 4, '0', STR_PAD_LEFT);

        return [
            'is_public'      => $isPublic,
            'proposal_url'   => $deal->proposal_token ? url("/proposal/{$deal->proposal_token}") : null,
            'invoice_number' => $invoiceNo,
            'issue_date'     => $deal->proposal_sent_at?->format('d F Y') ?? now()->format('d F Y'),
            'due_date'       => now()->addDays(7)->format('d F Y'),
            'status_stamp'   => $statusStamp,
            'deal'           => [
                'id'               => $deal->id,
                'title'            => $deal->title,
                'stage'            => $deal->stage,
                'amount'           => $amount,
                'formatted_amount' => $deal->formatted_amount,
                'currency'         => $currency,
                'scope_summary'    => $deal->scope_summary,
            ],
            'client' => [
                'name'       => $deal->lead->name ?? 'Valued Client',
                'company'    => $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Partner',
                'email'      => $deal->lead->email ?? '',
                'phone'      => $deal->lead->phone ?? '',
                'address'    => $deal->organization->city ?? $deal->lead->region ?? 'India',
                'gst_number' => $deal->organization->gst_number ?? null,
            ],
            'seller' => [
                'name'         => 'DigitalBuilders Technologies LLP',
                'brand'        => 'DigitalBuilders Engineering',
                'lead_contact' => 'Ashish Gupta, Principal Architect',
                'email'        => 'ashish@digitalbuilders.in',
                'phone'        => '+91 90870 21592',
                'website'      => 'https://digitalbuilders.in',
                'gstin'        => config('services.crm.gstin', '07AAACD1234F1Z5'),
                'address'      => 'DLF Cyber City, Sector 24, Gurugram, Haryana - 122002, India',
            ],
            'bank_details' => [
                'account_name' => config('services.crm.bank_name_holder', 'DigitalBuilders Engineering (Ashish Gupta)'),
                'bank_name'    => config('services.crm.bank_name', 'HDFC Bank Ltd'),
                'account_no'   => config('services.crm.bank_account_no', '50200084729104'),
                'ifsc_code'    => config('services.crm.bank_ifsc', 'HDFC0000240'),
                'swift_code'   => config('services.crm.bank_swift', 'HDFCINBBXXX'),
                'upi_id'       => config('services.crm.bank_upi', '9087021592@upi'),
            ],
            'financials' => [
                'subtotal'       => $baseAmount,
                'gst_rate'       => $gstRate,
                'gst_amount'     => $gstAmount,
                'total_amount'   => $currency === 'INR' ? $totalWithGst : $baseAmount,
                'paid_amount'    => $paidAmount,
                'balance_due'    => $currency === 'INR' ? ($totalWithGst - $paidAmount) : $balanceDue,
                'currency'       => $currency,
                'currency_sym'   => $sym,
            ],
            'payments' => $deal->payments()->latest()->get()->map(fn($p) => [
                'id'              => $p->id,
                'amount'          => (float) $p->amount,
                'currency'        => $p->currency,
                'gateway'         => $p->gateway,
                'status'          => $p->status,
                'payment_method'  => $p->payment_method,
                'transaction_utr' => $p->transaction_utr,
                'notes'           => $p->notes,
                'paid_at'         => $p->paid_at?->format('d M Y, h:i A'),
                'created_at'      => $p->created_at->format('d M Y, h:i A'),
            ]),
        ];
    }

    /**
     * Synthesizes an executive Markdown proposal tailored to the deal, lead, and organization.
     */
    private function buildDefaultProposalMarkdown(Deal $deal): string
    {
        $clientName = $deal->lead->name ?? 'Valued Client';
        $companyName = $deal->organization->name ?? $deal->lead->company ?? 'Enterprise Client';
        $roleTitle = $deal->lead->role_title ?? 'Decision Maker';
        $segment = ucwords(str_replace('_', ' ', $deal->lead->segment ?? 'Custom Solution'));
        $dateStr = now()->format('d F Y');
        $currency = $deal->currency;
        $amount = (float) $deal->amount;
        $formattedAmount = $deal->formatted_amount;

        // Commercial Milestones (40% / 40% / 20%)
        $sym = $currency === 'INR' ? '₹' : '$';
        $m1 = $sym . number_format($amount * 0.40);
        $m2 = $sym . number_format($amount * 0.40);
        $m3 = $sym . number_format($amount * 0.20);

        // Scope description lines
        $scopeNotes = $deal->scope_summary
            ? "> " . str_replace("\n", "\n> ", trim($deal->scope_summary))
            : "> Production-grade full-stack engineering, custom business logic, automated workflows, and high-performance database design.";

        return <<<MARKDOWN
# Engineering Proposal & Scope of Work

**Prepared For:** {$clientName} ({$roleTitle}) | **{$companyName}**  
**Project:** {$deal->title}  
**Date:** {$dateStr}  
**Prepared By:** Ashish Gupta, Principal Architect @ [DigitalBuilders](https://digitalbuilders.in)  
**Total Investment:** **{$formattedAmount} ({$currency})**  

---

## 1. Executive Summary & Objective

DigitalBuilders is pleased to submit this formal proposal to engineer, architect, and deploy the **{$deal->title}** for **{$companyName}**.

Our primary mission is to deliver an enterprise-grade digital solution tailored to the **{$segment}** vertical that completely eliminates operational friction, accelerates transaction speed, and gives your business an unfair competitive advantage.

{$scopeNotes}

---

## 2. Technical Architecture & Deliverables

Our engineering approach leverages high-throughput, modern application architecture with zero bloat:

- **Backend Foundation**: Laravel 12/13 High-Throughput Framework, clean Domain-Driven Design (DDD), automated background queues, and strict PostgreSQL/MySQL database modeling.
- **Client Experience**: Reactive Vue 3 + Inertia.js frontend with Tailwind CSS — delivers sub-second SPA responsiveness with seamless server hydration.
- **Automated Workflow & Messaging**: Deep WhatsApp Business API triggers, transactional email pipelines, and real-time event notifications.
- **Enterprise Security & Governance**: Multi-tenant RBAC (Role-Based Access Control), HTTPS/TLS hardening, automated daily database backups, and OWASP-compliant security protocols.

---

## 3. Commercial Investment & Milestone Schedule

Total Project Fee: **{$formattedAmount} ({$currency})**

| Milestone | Scope & Deliverables | Share | Amount |
|---|---|---|---|
| **Phase 1: Kickoff & Core Architecture** | System blueprint, database ERD, interactive UI staging prototype, and CI/CD environment setup | 40% | **{$m1}** |
| **Phase 2: Core Engineering & Modules** | Full functional development of custom business workflows, automated messaging & API integrations | 40% | **{$m2}** |
| **Phase 3: UAT, Hardening & Go-Live** | End-to-end user acceptance testing, security audit, DNS cutover, 100% code handover & team walkthrough | 20% | **{$m3}** |

*Commercial Terms: Direct Bank Wire (RTGS / NEFT / IMPS), UPI, Razorpay, or International Wire (Stripe).*

---

## 4. Guarantees & Intellectual Property

1. **100% Intellectual Property Handover**: Upon final milestone completion, all custom source code, documentation, and database schemas are 100% owned by **{$companyName}**. Zero perpetual royalties, zero vendor lock-in.
2. **30-Day Hypercare Warranty**: Dedicated zero-cost bug fixes, performance monitoring, and architecture support for 30 days post-launch.
3. **99.9% Uptime Guarantee**: Built on hardened, container-ready architecture designed for institutional reliability.

---

## 5. Acceptance & Authorization

To authorize this proposal and initiate the Phase 1 kickoff sprint, confirm acceptance via the online portal or directly via WhatsApp.

**DigitalBuilders Engineering**  
Ashish Gupta — Principal Software Architect  
Direct WhatsApp: [+91 90870 21592](https://wa.me/919087021592)  
Email: [ashish@digitalbuilders.in](mailto:ashish@digitalbuilders.in)  
Website: [https://digitalbuilders.in](https://digitalbuilders.in)
MARKDOWN;
    }
}
