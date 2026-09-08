<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmProposalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lead $lead;
    private Deal $deal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email'    => 'ashish@digitalbuilders.in',
            'is_admin' => true,
        ]);

        $this->lead = Lead::create([
            'name'             => 'Rajesh Garg',
            'email'            => 'rajesh@gargpackaging.com',
            'phone'            => '+91 98765 43210',
            'company'          => 'Garg Packaging Mills',
            'role_title'       => 'Managing Director',
            'segment'          => 'manufacturer',
            'stage'            => 'discovery_done',
            'score'            => 85,
            'touchpoint_count' => 1,
        ]);

        $this->deal = Deal::create([
            'title'               => 'Garg Packaging Mills — Custom ERP & Warehouse System',
            'lead_id'             => $this->lead->id,
            'amount'              => 379000.00,
            'currency'            => 'INR',
            'stage'               => 'discovery_done',
            'probability'         => 60,
            'expected_close_date' => now()->addDays(20),
            'scope_summary'       => "Multi-warehouse raw material tracking, WhatsApp dispatch notifications, and GST invoice engine.",
        ]);
    }

    public function test_admin_can_generate_and_view_deal_proposal(): void
    {
        $response = $this->actingAs($this->admin)->getJson("/crm/deals/{$this->deal->id}/proposal");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'deal_id',
            'title',
            'amount',
            'formatted_amount',
            'currency',
            'stage',
            'proposal_content',
            'proposal_html',
            'proposal_token',
            'public_url',
            'client_name',
            'company_name',
        ]);

        $this->assertStringContainsString('Engineering Proposal', $response->json('proposal_content'));
        $this->assertStringContainsString('Garg Packaging Mills', $response->json('proposal_content'));
        $this->assertStringContainsString('Rajesh Garg', $response->json('proposal_content'));
    }

    public function test_admin_can_save_custom_proposal_markdown(): void
    {
        $customMarkdown = "# Custom ERP Scope\n\n- Real-time stock audit\n- GST automated filings";

        $response = $this->actingAs($this->admin)->postJson("/crm/deals/{$this->deal->id}/proposal", [
            'proposal_content' => $customMarkdown,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->deal->refresh();
        $this->assertEquals($customMarkdown, $this->deal->proposal_content);
    }

    public function test_admin_can_mark_proposal_sent_and_advance_stage(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/crm/deals/{$this->deal->id}/proposal/send");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stage'   => 'proposal_sent',
        ]);

        $this->deal->refresh();
        $this->assertEquals('proposal_sent', $this->deal->stage);
        $this->assertEquals(75, $this->deal->probability);
        $this->assertNotNull($this->deal->proposal_sent_at);

        $this->lead->refresh();
        $this->assertEquals(2, $this->lead->touchpoint_count);

        $this->assertDatabaseHas('activities', [
            'deal_id' => $this->deal->id,
            'type'    => 'stage_change',
            'subject' => 'Formal Engineering Proposal Dispatched',
        ]);
    }

    public function test_public_client_can_view_proposal_via_token(): void
    {
        $token = $this->deal->getOrCreateProposalToken();

        $response = $this->get("/proposal/{$token}");
        $response->assertStatus(200);

        $this->deal->refresh();
        $this->assertNotNull($this->deal->proposal_viewed_at);

        $this->assertDatabaseHas('activities', [
            'deal_id' => $this->deal->id,
            'subject' => 'Client Opened Web Proposal',
        ]);
    }

    public function test_client_can_accept_proposal_online(): void
    {
        $token = $this->deal->getOrCreateProposalToken();

        $response = $this->postJson("/proposal/{$token}/accept");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->deal->refresh();
        $this->assertNotNull($this->deal->proposal_accepted_at);
        $this->assertEquals('negotiation', $this->deal->stage);

        $this->assertDatabaseHas('activities', [
            'deal_id' => $this->deal->id,
            'subject' => 'Client Accepted Proposal Online',
        ]);
    }
}
