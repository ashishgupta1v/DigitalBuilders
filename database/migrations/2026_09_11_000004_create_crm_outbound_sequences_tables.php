<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure leads table has country, unsubscribed_at, and enrichment_data
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'country')) {
                $table->string('country', 10)->nullable()->default('IN')->after('region');
            }
            if (!Schema::hasColumn('leads', 'unsubscribed_at')) {
                $table->dateTime('unsubscribed_at')->nullable()->after('last_contact_date');
            }
            if (!Schema::hasColumn('leads', 'enrichment_data')) {
                $table->text('enrichment_data')->nullable()->after('ai_summary');
            }
        });

        // 2. Create crm_sequences table
        if (!Schema::hasTable('crm_sequences')) {
            Schema::create('crm_sequences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
                $table->string('name', 255)->default('Standard 4-Step Outbound Cadence');
                $table->string('status', 30)->default('draft')->index(); // draft, active, paused, completed, replied, opted_out
                $table->unsignedTinyInteger('current_step')->default(1);
                $table->unsignedTinyInteger('total_steps')->default(4);
                $table->dateTime('started_at')->nullable();
                $table->dateTime('stopped_at')->nullable();
                $table->string('stop_reason', 255)->nullable();
                $table->timestamps();
            });
        }

        // 3. Create crm_sequence_steps table
        if (!Schema::hasTable('crm_sequence_steps')) {
            Schema::create('crm_sequence_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sequence_id')->constrained('crm_sequences')->onDelete('cascade');
                $table->unsignedTinyInteger('step_number'); // 1, 2, 3, 4
                $table->unsignedSmallInteger('delay_days')->default(0); // 0, 3, 7, 11
                $table->string('step_type', 50)->default('email');
                $table->string('title', 255)->default('Outreach Touchpoint');
                $table->string('subject', 255);
                $table->text('body_text');
                $table->text('body_html')->nullable();
                $table->dateTime('scheduled_at')->nullable()->index();
                $table->dateTime('sent_at')->nullable();
                $table->foreignId('outreach_email_id')->nullable()->constrained('crm_outreach_emails')->onDelete('set null');
                $table->string('status', 30)->default('pending')->index(); // pending, scheduled, sent, skipped, failed
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_sequence_steps');
        Schema::dropIfExists('crm_sequences');

        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('leads', 'unsubscribed_at')) {
                $table->dropColumn('unsubscribed_at');
            }
            if (Schema::hasColumn('leads', 'enrichment_data')) {
                $table->dropColumn('enrichment_data');
            }
        });
    }
};
