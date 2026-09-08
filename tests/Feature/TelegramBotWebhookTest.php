<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\MarketRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramBotWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_callback_approves_pitch_and_advances_deal(): void
    {
        $lead = Lead::create([
            'name'             => 'Karan Malhotra',
            'phone'            => '+91 99887 66554',
            'touchpoint_count' => 0,
            'stage'            => 'new',
        ]);

        $deal = Deal::create([
            'title'       => 'Karan Malhotra — Warehouse Portal',
            'lead_id'     => $lead->id,
            'amount'      => 220000,
            'currency'    => 'INR',
            'stage'       => 'new',
            'probability' => 15,
        ]);

        $req = MarketRequirement::create([
            'source'           => 'indiamart',
            'external_id'      => 'IM_TEST_101',
            'title'            => 'Warehouse Portal',
            'raw_text'         => 'Need dispatch barcode system',
            'estimated_amount' => 220000,
            'status'           => 'qualified',
            'lead_id'          => $lead->id,
            'deal_id'          => $deal->id,
        ]);

        $payload = [
            'callback_query' => [
                'id'   => 'cq_12345',
                'data' => "req:approve:{$req->id}",
            ],
        ];

        $response = $this->postJson('/api/crm/webhooks/telegram', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'action' => 'approved']);

        // Assert requirement status updated
        $req->refresh();
        $this->assertEquals('pitched', $req->status);
        $this->assertNotNull($req->pitched_at);

        // Assert deal advanced to contacted with 25% probability
        $deal->refresh();
        $this->assertEquals('contacted', $deal->stage);
        $this->assertEquals(25, $deal->probability);

        // Assert lead touchpoint incremented
        $lead->refresh();
        $this->assertEquals(1, $lead->touchpoint_count);
        $this->assertEquals('contacted', $lead->stage);

        $this->assertDatabaseHas('activities', [
            'deal_id' => $deal->id,
            'subject' => 'Pitch Approved via Telegram 1-Tap',
        ]);
    }

    public function test_telegram_callback_rejects_requirement(): void
    {
        $req = MarketRequirement::create([
            'source'           => 'indiamart',
            'external_id'      => 'IM_TEST_202',
            'title'            => 'Low Value Website Request',
            'raw_text'         => 'Need 1 page static site for Rs 2000',
            'estimated_amount' => 2000,
            'status'           => 'qualified',
        ]);

        $payload = [
            'callback_query' => [
                'id'   => 'cq_67890',
                'data' => "req:skip:{$req->id}",
            ],
        ];

        $response = $this->postJson('/api/crm/webhooks/telegram', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'action' => 'skipped']);

        $req->refresh();
        $this->assertEquals('rejected', $req->status);
    }
}
