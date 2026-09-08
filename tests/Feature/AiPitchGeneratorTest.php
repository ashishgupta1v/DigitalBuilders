<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SalesFunnel\AiPitchGeneratorService;
use App\Services\SalesFunnel\AiReplyTriageService;
use Tests\TestCase;

class AiPitchGeneratorTest extends TestCase
{
    public function test_ai_pitch_generator_classifies_segments_correctly(): void
    {
        $service = new AiPitchGeneratorService();

        // Manufacturing / ERP
        $this->assertEquals(
            'manufacturing',
            $service->classifySegment('Need factory dispatch system with barcode scanners and Tally integration')
        );

        // EdTech / Tutors
        $this->assertEquals(
            'edtech',
            $service->classifySegment('Looking for automated WhatsApp homework reminders for online tutors')
        );

        // E-Commerce
        $this->assertEquals(
            'ecommerce',
            $service->classifySegment('Need high speed D2C Shopify alternative with Razorpay checkout')
        );

        // SaaS / AI
        $this->assertEquals(
            'saas_ai',
            $service->classifySegment('Building an AI agent dashboard with scalable microservices')
        );
    }

    public function test_ai_pitch_generator_produces_anchored_pitch(): void
    {
        $service = new AiPitchGeneratorService();

        $pitch = $service->generatePitch(
            'Factory warehouse barcode dispatch and Tally ERP sync',
            'Sanjay Kapoor',
            'Kapoor Packaging Ltd',
            'INR'
        );

        $this->assertEquals('manufacturing', $pitch['segment']);
        $this->assertEquals('Garg Enterprises', $pitch['case_study']);
        $this->assertStringContainsString('Garg Enterprises', $pitch['short_pitch']);
        $this->assertStringContainsString('https://www.digitalbuilders.in/book', $pitch['short_pitch']);
        $this->assertStringContainsString('Ashish Gupta', $pitch['email_pitch']);
        $this->assertStringContainsString('+91 90870 21592', $pitch['email_pitch']);
    }

    public function test_ai_reply_triage_detects_objections_and_actions(): void
    {
        $triage = new AiReplyTriageService();

        // Price Objection
        $price = $triage->triageReply('The quote is too expensive for our current budget', 'Vikram', 'ERP Build');
        $this->assertEquals('price_objection', $price['intent']);
        $this->assertStringContainsString('Phase 1 core MVP', $price['response']);

        // Meeting Request
        $meeting = $triage->triageReply('Can we schedule a Google Meet call tomorrow at 3 PM?', 'Vikram', 'ERP Build');
        $this->assertEquals('meeting_request', $meeting['intent']);
        $this->assertStringContainsString('https://www.digitalbuilders.in/book', $meeting['response']);

        // Portfolio Request
        $portfolio = $triage->triageReply('Can you send some sample case studies and past work?', 'Vikram', 'ERP Build');
        $this->assertEquals('portfolio_request', $portfolio['intent']);
        $this->assertStringContainsString('Garg Enterprises', $portfolio['response']);
    }
}
