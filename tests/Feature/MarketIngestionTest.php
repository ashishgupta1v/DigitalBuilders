<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\MarketRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_indiamart_push_api_ingests_lead_and_deal(): void
    {
        $payload = [
            'QUERY_ID'            => 'IM_ENQ_987654321',
            'SENDER_NAME'         => 'Ramanathan Iyer',
            'GLUSR_USR_PH_MOBILE' => '+91 98401 23456',
            'SENDER_EMAIL'        => 'ramanathan@iyertextiles.com',
            'SENDER_COMPANY'      => 'Iyer Spinning & Textile Mills',
            'SENDER_CITY'         => 'Coimbatore',
            'PRODUCT_NAME'        => 'Textile Mill Inventory & Dispatch ERP System',
            'ENQ_MESSAGE'         => 'Need barcode scanning for fabric rolls and Tally accounting synchronization urgently.',
        ];

        $response = $this->postJson('/api/crm/ingest/indiamart', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'segment' => 'manufacturing',
        ]);

        // Verify Market Requirement record
        $this->assertDatabaseHas('market_requirements', [
            'source'          => 'indiamart',
            'external_id'     => 'IM_ENQ_987654321',
            'contact_name'    => 'Ramanathan Iyer',
            'matched_segment' => 'manufacturing',
            'status'          => 'qualified',
        ]);

        // Verify Lead creation
        $this->assertDatabaseHas('leads', [
            'name'    => 'Ramanathan Iyer',
            'company' => 'Iyer Spinning & Textile Mills',
            'source'  => 'indiamart',
            'segment' => 'manufacturing',
        ]);

        // Verify Deal creation
        $this->assertDatabaseHas('deals', [
            'stage'    => 'new',
            'currency' => 'INR',
        ]);

        // Test Idempotency: Re-sending identical inquiry ID returns success without duplication
        $duplicateResponse = $this->postJson('/api/crm/ingest/indiamart', $payload);
        $duplicateResponse->assertStatus(200);
        $duplicateResponse->assertJson(['success' => true, 'message' => 'Requirement already ingested.']);

        $this->assertEquals(1, MarketRequirement::where('external_id', 'IM_ENQ_987654321')->count());
    }

    public function test_external_requirement_endpoint_ingests_and_scores(): void
    {
        $payload = [
            'source'          => 'upwork',
            'external_id'     => 'UPW_778899',
            'title'           => 'Need Senior Architect for High-Traffic React & Laravel Platform',
            'raw_text'        => 'We are scaling a live wellness streaming app with 50,000 active users. Need Redis caching and backend concurrency tuning.',
            'budget_raw'      => '$6,000 - $10,000',
            'currency'        => 'USD',
            'contact_name'    => 'David Miller',
            'contact_company' => 'Aura Health LLC',
            'location'        => 'United States',
        ];

        $response = $this->postJson('/api/crm/ingest/requirement', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'segment' => 'saas_ai',
        ]);

        $this->assertDatabaseHas('market_requirements', [
            'source'          => 'upwork',
            'external_id'     => 'UPW_778899',
            'matched_segment' => 'saas_ai',
            'currency'        => 'USD',
        ]);
    }
}
