<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Library\Infrastructure\Mail\LeadAutoReplyMail;
use App\Modules\Library\Infrastructure\Mail\NewLeadMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_submission_triggers_admin_and_auto_reply_emails(): void
    {
        Mail::fake();

        $response = $this->post('/library/contact', [
            'name' => 'John Architect',
            'email' => 'john.architect@example.com',
            'phone' => '+1 555 123 4567',
            'project_type' => 'saas',
            'description' => 'Need multi-tenant billing engine with Stripe.',
            '_hp_company' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        Mail::assertSent(NewLeadMail::class);
        Mail::assertSent(LeadAutoReplyMail::class, function ($mail) {
            return $mail->hasTo('john.architect@example.com');
        });
    }

    public function test_estimator_submission_triggers_auto_reply(): void
    {
        Mail::fake();

        $response = $this->post('/estimator/submit', [
            'name' => 'Sara CTO',
            'email' => 'sara@growthtech.io',
            'phone' => '+971 50 123 4567',
            'project_type' => 'ai_solutions',
            'estimated_budget' => '$7,900 - $13,000 (USD)',
            'estimated_timeline' => '25 - 40 Days',
            'features' => ['AI Voice Agents', 'RAG Copilot'],
            'description' => 'Looking for voice assistant integration.',
            '_hp_company' => '',
        ]);

        $response->assertSessionHasNoErrors();

        Mail::assertSent(NewLeadMail::class);
        Mail::assertSent(LeadAutoReplyMail::class, function ($mail) {
            return $mail->hasTo('sara@growthtech.io');
        });
    }

    public function test_honeypot_drops_spam_without_sending_emails(): void
    {
        Mail::fake();

        $response = $this->post('/library/contact', [
            'name' => 'Spam Bot',
            'email' => 'bot@spammer.org',
            'phone' => '+1234567890',
            'project_type' => 'web_app',
            '_hp_company' => 'Bot Corp Ltd',
        ]);

        $response->assertRedirect();
        Mail::assertNothingSent();
    }
}
