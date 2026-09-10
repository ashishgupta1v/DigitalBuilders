<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_outreach_emails')) {
            Schema::create('crm_outreach_emails', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('set null');
                $table->string('tracking_token', 64)->unique()->index();
                $table->string('recipient_email', 255)->index();
                $table->string('recipient_name', 255)->nullable();
                $table->string('subject', 255);
                $table->text('body_text');
                $table->text('body_html');
                $table->unsignedTinyInteger('touchpoint_number')->nullable();
                $table->dateTime('sent_at');
                $table->dateTime('opened_at')->nullable();
                $table->unsignedInteger('open_count')->default(0);
                $table->dateTime('clicked_at')->nullable();
                $table->unsignedInteger('click_count')->default(0);
                $table->string('last_clicked_url', 500)->nullable();
                $table->string('status', 30)->default('sent')->index(); // sent, opened, clicked, bounced
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_outreach_emails');
    }
};
