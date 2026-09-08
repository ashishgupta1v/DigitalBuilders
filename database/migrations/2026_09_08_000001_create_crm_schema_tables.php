<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Organizations (Companies / Factories / Clinics / Startups)
        if (!Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('domain', 150)->nullable()->index();
                $table->string('industry', 80)->nullable()->index(); // e.g. manufacturing, retail, clinic, coaching, international
                $table->string('company_size', 50)->nullable();
                $table->string('gst_number', 50)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('country', 10)->default('IN');
                $table->string('website', 255)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email', 150)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Deals / Opportunities (Unified Pipeline with dual-currency INR/USD)
        if (!Schema::hasTable('deals')) {
            Schema::create('deals', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('set null');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('currency', 10)->default('INR'); // INR or USD
                $table->string('stage', 50)->default('new')->index(); // new, contacted, qualified, discovery_done, proposal_sent, negotiation, closed_won, closed_lost
                $table->unsignedTinyInteger('probability')->default(20);
                $table->date('expected_close_date')->nullable();
                $table->string('pricing_tier', 60)->nullable(); // starter, growth, enterprise, discovery_sprint, custom
                $table->text('scope_summary')->nullable();
                $table->string('loss_reason', 255)->nullable();
                $table->string('payment_status', 30)->default('unpaid'); // unpaid, partially_paid, paid
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->dateTime('closed_at')->nullable();
                $table->timestamps();

                $table->index(['stage', 'currency']);
            });
        }

        // 3. Activities (Timeline: WhatsApp, Email, Notes, Calls, Tasks, Stage Changes)
        if (!Schema::hasTable('activities')) {
            Schema::create('activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('type', 40)->index(); // note, call, whatsapp, email, meeting, stage_change, payment, task
                $table->string('subject', 255);
                $table->text('description')->nullable();
                $table->dateTime('due_date')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->unsignedTinyInteger('touchpoint_number')->nullable(); // 1 to 5 for the 5-touch cadence
                $table->json('metadata')->nullable(); // WhatsApp message text, email details, gateway payload, etc.
                $table->timestamps();

                $table->index(['lead_id', 'created_at']);
            });
        }

        // 4. Payments (Razorpay, Stripe & Manual Bank Wires with UTR)
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
                $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
                $table->string('gateway', 40)->default('razorpay'); // razorpay, stripe, bank_wire, manual
                $table->string('gateway_payment_id', 255)->nullable()->index();
                $table->string('gateway_link_id', 255)->nullable()->index();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 10)->default('INR');
                $table->string('status', 40)->default('created')->index(); // created, paid, failed, refunded
                $table->string('payment_method', 50)->nullable(); // upi, card, netbanking, wire
                $table->string('transaction_utr', 255)->nullable()->index();
                $table->text('notes')->nullable();
                $table->string('receipt_url', 500)->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('organizations');
    }
};
