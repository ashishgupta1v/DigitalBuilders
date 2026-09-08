<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->index(); // 'indiamart', 'upwork', 'hacker_news', 'job_board', 'inbound_website'
            $table->string('external_id', 150)->nullable()->index(); // Unique ID from source
            $table->string('title')->nullable();
            $table->text('raw_text');
            $table->string('budget_raw', 100)->nullable();
            $table->decimal('estimated_amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_company', 150)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('matched_segment', 50)->nullable()->index(); // 'manufacturing', 'edtech', 'ecommerce', 'saas_ai', 'general'
            $table->integer('relevance_score')->default(50)->index();
            $table->text('pitch_draft')->nullable();
            $table->string('status', 30)->default('pending')->index(); // 'pending', 'qualified', 'pitched', 'rejected', 'converted'
            $table->string('rejection_reason', 255)->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained('deals')->nullOnDelete();
            $table->timestamp('pitched_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_requirements');
    }
};
