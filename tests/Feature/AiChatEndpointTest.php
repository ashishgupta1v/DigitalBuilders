<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_ai_chat_returns_contextual_response_for_mobile(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'How much does a mobile app cost?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
        $this->assertStringContainsString('mobile', strtolower($response->json('response')));
    }

    public function test_api_ai_chat_returns_contextual_response_for_pricing(): void
    {
        $response = $this->postJson('/api/ai-chat', [
            'message' => 'What are your rates and pricing tiers?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
        $this->assertStringContainsString('pricing', strtolower($response->json('response')));
    }

    public function test_ai_chat_handles_blockchain_inquiry(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'Do you build blockchain microservices in Rust?',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('Rust', $response->json('response'));
    }

    public function test_ai_chat_handles_amc_and_maintenance_inquiry(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'Do you offer AMC or post-launch maintenance plans?',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('Basic Care', $response->json('response'));
        $this->assertStringContainsString('warranty', strtolower($response->json('response')));
    }

    public function test_ai_chat_handles_booking_and_calendar_inquiry(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'Can I book a consultation or schedule a strategy call?',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('/book', strtolower($response->json('response')));
    }

    public function test_ai_chat_handles_nda_inquiry(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'Do you sign an NDA before we share our proprietary system requirements?',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('nda', strtolower($response->json('response')));
    }

    public function test_ai_chat_handles_refund_and_cancellation_inquiry(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'What is your refund policy or cancellation terms?',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('milestone', strtolower($response->json('response')));
    }

    public function test_ai_chat_handles_code_ownership_inquiry(): void
    {
        $response = $this->postJson('/ajax/ai-chat', [
            'message' => 'Who owns the code and intellectual property after completion?',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('ownership', strtolower($response->json('response')));
    }

    public function test_testimonials_endpoints_work(): void
    {
        $resAjax = $this->getJson('/ajax/testimonials');
        $resAjax->assertStatus(200);

        $resApi = $this->getJson('/api/testimonials');
        $resApi->assertStatus(200);
    }
}
