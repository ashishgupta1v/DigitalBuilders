<?php

declare(strict_types=1);

use App\Http\Controllers\Crm\CrmActivityController;
use App\Http\Controllers\Crm\CrmAiController;
use App\Http\Controllers\Crm\CrmAuthController;
use App\Http\Controllers\Crm\CrmDashboardController;
use App\Http\Controllers\Crm\CrmDealController;
use App\Http\Controllers\Crm\CrmEmailTrackingController;
use App\Http\Controllers\Crm\CrmInboundReplyController;
use App\Http\Controllers\Crm\CrmLeadController;
use App\Http\Controllers\Crm\CrmMarketIngestionController;
use App\Http\Controllers\Crm\CrmPaymentController;
use App\Http\Controllers\Crm\CrmProposalController;
use App\Http\Controllers\Crm\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

// Public Email Engagement Tracking (Opens, Clicks & Unsubscribe)
Route::prefix('crm')->group(function () {
    Route::get('/track/open/{token}', [CrmEmailTrackingController::class, 'trackOpen'])->name('crm.track.open');
    Route::get('/track/click/{token}', [CrmEmailTrackingController::class, 'trackClick'])->name('crm.track.click');
    Route::get('/unsubscribe/{token}', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'unsubscribe'])->name('crm.unsubscribe');
});

// Public CRM Auth & Password Recovery Routes
Route::middleware(['web'])->prefix('crm')->group(function () {
    Route::get('/login', [CrmAuthController::class, 'showLogin'])->name('crm.login');
    Route::post('/login', [CrmAuthController::class, 'login'])->middleware('throttle:10,1')->name('crm.login.submit');
    Route::post('/logout', [CrmAuthController::class, 'logout'])->name('crm.logout');

    // Security Question Hint Recovery & First-Time Activation
    Route::post('/password/question', [CrmAuthController::class, 'getSecurityQuestion'])->middleware('throttle:10,1')->name('crm.password.question');
    Route::post('/password/reset-question', [CrmAuthController::class, 'resetWithSecurityQuestion'])->middleware('throttle:10,1')->name('crm.password.reset-question');
    Route::post('/password/first-time-update', [CrmAuthController::class, 'firstTimePasswordUpdate'])->middleware('throttle:10,1')->name('crm.password.first-time-update');
});

// Public Client Proposal Review Portal & Payment Link Fallback
Route::middleware(['web'])->group(function () {
    Route::get('/proposal/{token}', [CrmProposalController::class, 'publicView'])->name('crm.proposal.public');
    Route::post('/proposal/{token}/accept', [CrmProposalController::class, 'accept'])->name('crm.proposal.accept');
    Route::post('/proposal/{token}/payment-link', [CrmProposalController::class, 'createClientPaymentLink'])->name('crm.proposal.payment-link');
    Route::post('/proposal/{token}/wire', [CrmProposalController::class, 'submitWirePayment'])->name('crm.proposal.wire');
    Route::get('/proposal/{token}/invoice', [CrmProposalController::class, 'invoice'])->name('crm.proposal.invoice');
    Route::get('/checkout/pay', function (\Illuminate\Http\Request $request) {
        $dealId = $request->query('deal');
        $deal = $dealId ? \App\Models\Deal::find($dealId) : null;
        if ($deal && $deal->proposal_token) {
            return redirect()->route('crm.proposal.public', ['token' => $deal->proposal_token]);
        }
        return redirect('/pricing')->with('info', 'For wire transfers or direct payment coordination, please connect with our team on WhatsApp.');
    })->name('crm.checkout.pay');
});

// Protected Executive CRM Cockpit
Route::middleware(['web', 'crm.admin'])->prefix('crm')->name('crm.')->group(function () {
    // Cockpit Telemetry & Kanban Dashboard
    Route::get('/', [CrmDashboardController::class, 'index'])->name('dashboard');

    // Lead & Prospect Management
    Route::get('/leads', [CrmLeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{id}', [CrmLeadController::class, 'show'])->name('leads.show');
    Route::post('/leads', [CrmLeadController::class, 'store'])->name('leads.store');
    Route::patch('/leads/{id}', [CrmLeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{id}', [CrmLeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/bulk', [CrmLeadController::class, 'bulkAction'])->name('leads.bulk');
    Route::post('/leads/import', [CrmLeadController::class, 'importCsv'])->name('leads.import');
    Route::post('/leads/{id}/send-email', [CrmLeadController::class, 'sendOutreachEmail'])->name('leads.send-email');
    Route::post('/leads/{id}/triage-reply', [CrmInboundReplyController::class, 'triageLeadReply'])->name('leads.triage-reply');
    Route::post('/leads/{id}/enrich', [CrmLeadController::class, 'enrich'])->name('leads.enrich');
    Route::get('/leads/{id}/duplicates', [CrmLeadController::class, 'checkDuplicates'])->name('leads.duplicates');
    Route::post('/leads/{id}/merge', [CrmLeadController::class, 'merge'])->name('leads.merge');

    // Outbound Campaigns & Automated Sequences
    Route::get('/leads/{lead}/sequence', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'show'])->name('leads.sequence');
    Route::post('/leads/{lead}/sequence/regenerate', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'regenerate'])->name('leads.sequence.regenerate');
    Route::post('/sequences/{sequence}/start', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'start'])->name('sequences.start');
    Route::post('/sequences/{sequence}/pause', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'pause'])->name('sequences.pause');
    Route::post('/sequences/{sequence}/resume', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'resume'])->name('sequences.resume');
    Route::post('/leads/{lead}/mark-replied', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'markReplied'])->name('leads.mark-replied');
    Route::put('/sequences/{sequence}/steps/{step}', [\App\Http\Controllers\Crm\CrmSequenceController::class, 'updateStep'])->name('sequences.steps.update');

    // Deal & Pipeline Mechanics
    Route::post('/deals/{id}/stage', [CrmDealController::class, 'updateStage'])->name('deals.stage');
    Route::patch('/deals/{id}', [CrmDealController::class, 'update'])->name('deals.update');
    Route::get('/deals/{id}/invoice', [CrmProposalController::class, 'dealInvoice'])->name('deals.invoice');

    // Activities & 5-Touch Cadence Engine
    Route::post('/activities', [CrmActivityController::class, 'store'])->name('activities.store');
    Route::post('/activities/touchpoint', [CrmActivityController::class, 'logTouchpoint'])->name('activities.touchpoint');

    // AI Sales Co-Pilot
    Route::get('/ai/recommend/{id}', [CrmAiController::class, 'recommendNextAction'])->name('ai.recommend');
    Route::post('/ai/script', [CrmAiController::class, 'generateScript'])->name('ai.script');

    // Payment Links & Wire Logging
    Route::post('/deals/{id}/payment-link', [CrmPaymentController::class, 'createPaymentLink'])->name('payments.link');
    Route::post('/deals/{id}/wire', [CrmPaymentController::class, 'recordWire'])->name('payments.wire');

    // Executive Proposal Generator & Editor
    Route::get('/deals/{id}/proposal', [CrmProposalController::class, 'show'])->name('deals.proposal');
    Route::post('/deals/{id}/proposal', [CrmProposalController::class, 'save'])->name('deals.proposal.save');
    Route::post('/deals/{id}/proposal/send', [CrmProposalController::class, 'send'])->name('deals.proposal.send');

    // Market Requirements & Lead Hunter Actions
    Route::post('/market/poll', [CrmMarketIngestionController::class, 'pollLive'])->name('market.poll');
    Route::post('/market/smart-ingest', [CrmMarketIngestionController::class, 'smartIngest'])->name('market.smart-ingest');
    Route::post('/market/purge-junk', [CrmMarketIngestionController::class, 'purgeJunk'])->name('market.purge-junk');
    Route::post('/market/ingest-custom', [CrmMarketIngestionController::class, 'handleExternalRequirement'])->name('market.ingest-custom');
    Route::post('/market/requirements/{id}/dismiss', [CrmMarketIngestionController::class, 'dismiss'])->name('market.dismiss');
    Route::post('/market/requirements/{id}/convert', [CrmMarketIngestionController::class, 'convertToDeal'])->name('market.convert');

    // In-Cockpit Security & Password Update
    Route::post('/profile/password', [CrmAuthController::class, 'updatePassword'])->name('profile.password');
});

// Public Ingestion & Webhook Endpoints
Route::prefix('api/crm')->name('api.crm.')->group(function () {
    Route::post('/leads/webhook', [CrmLeadController::class, 'handleExternalWebhook'])->name('leads.webhook');
    Route::post('/webhooks/razorpay', [CrmPaymentController::class, 'handleRazorpayWebhook'])->name('webhooks.razorpay');
    Route::post('/webhooks/stripe', [CrmPaymentController::class, 'handleStripeWebhook'])->name('webhooks.stripe');
    Route::post('/ingest/indiamart', [CrmMarketIngestionController::class, 'handleIndiaMartPush'])->name('ingest.indiamart');
    Route::post('/ingest/requirement', [CrmMarketIngestionController::class, 'handleExternalRequirement'])->name('ingest.requirement');
    Route::post('/webhooks/telegram', [TelegramWebhookController::class, 'handle'])->name('webhooks.telegram');
    Route::post('/webhooks/reply', [CrmInboundReplyController::class, 'handleExternalWebhook'])->name('webhooks.reply');
});
