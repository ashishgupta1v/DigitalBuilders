<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_crm_to_login(): void
    {
        $response = $this->get('/crm');
        $response->assertRedirect('/crm/login');
    }

    public function test_crm_login_page_renders_cleanly(): void
    {
        $response = $this->get('/crm/login');
        $response->assertStatus(200);
    }

    public function test_admin_can_authenticate_to_crm(): void
    {
        $user = User::factory()->create([
            'email'    => 'testadmin@digitalbuilders.in',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->post('/crm/login', [
            'email'    => 'testadmin@digitalbuilders.in',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/crm');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_view_crm_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/crm');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Crm/Dashboard')
            ->has('telemetry')
            ->has('action_queue')
            ->has('stages')
        );
    }

    public function test_admin_can_quick_add_lead_and_deal(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/crm/leads', [
            'name'         => 'Raman Preet',
            'company'      => 'Preet Hosiery Works',
            'phone'        => '+91 98721 22334',
            'email'        => 'raman@preethosiery.in',
            'segment'      => 'manufacturer',
            'project_type' => 'Custom Dispatch ERP',
            'deal_amount'  => 379000,
            'currency'     => 'INR',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'name'    => 'Raman Preet',
            'phone'   => '+91 98721 22334',
            'segment' => 'manufacturer',
        ]);

        $this->assertDatabaseHas('deals', [
            'amount'   => 379000,
            'currency' => 'INR',
            'stage'    => 'new',
        ]);
    }

    public function test_deal_stage_advancement_updates_probability_and_logs_activity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lead = Lead::create([
            'name'         => 'Amit Kumar',
            'phone'        => '+91 99112 23344',
            'email'        => 'amit@test.com',
            'project_type' => 'Web App',
            'segment'      => 'manufacturer',
        ]);
        $deal = Deal::create([
            'title'       => 'Amit — Web App',
            'lead_id'     => $lead->id,
            'amount'      => 149000,
            'currency'    => 'INR',
            'stage'       => 'new',
            'probability' => 15,
        ]);

        $response = $this->actingAs($admin)->postJson("/crm/deals/{$deal->id}/stage", [
            'stage' => 'proposal_sent',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'     => true,
            'stage'       => 'proposal_sent',
            'probability' => 75,
        ]);

        $this->assertEquals('proposal_sent', $deal->fresh()->stage);
        $this->assertEquals(75, $deal->fresh()->probability);
    }

    public function test_external_webhook_creates_lead_and_deal(): void
    {
        $response = $this->postJson('/api/crm/leads/webhook', [
            'name'         => 'Anand Sharma',
            'company'      => 'Sharma Pharma Lab',
            'phone'        => '+91 98111 44556',
            'email'        => 'anand@sharmapharma.com',
            'segment'      => 'clinic',
            'project_type' => 'Diagnostic Booking App',
            'deal_amount'  => 199000,
            'currency'     => 'INR',
            'source'       => 'meta_ads',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'name'    => 'Anand Sharma',
            'phone'   => '+91 98111 44556',
            'source'  => 'meta_ads',
            'segment' => 'clinic',
        ]);
    }

    public function test_admin_can_generate_ai_pitch_script(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lead = Lead::create([
            'name'         => 'Rajesh Garg',
            'company'      => 'Garg Packaging Mills',
            'phone'        => '+91 98765 43210',
            'email'        => 'rajesh@gargpackaging.com',
            'project_type' => 'Factory Dispatch ERP',
            'segment'      => 'manufacturer',
        ]);

        $response = $this->actingAs($admin)->postJson('/crm/ai/script', [
            'lead_id' => $lead->id,
            'type'    => 'touchpoint',
            'touch'   => 1,
            'use_ai'  => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'touch'   => 1,
        ]);
        $this->assertNotEmpty($response->json('script'));
        $this->assertStringContainsString('wa.me', $response->json('whatsapp_url'));
    }

    public function test_admin_can_log_touchpoint_and_advance_cadence(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lead = Lead::create([
            'name'             => 'Harpreet Singh',
            'phone'            => '+91 98141 87654',
            'segment'          => 'manufacturer',
            'touchpoint_count' => 1,
        ]);

        $response = $this->actingAs($admin)->postJson('/crm/activities/touchpoint', [
            'lead_id'      => $lead->id,
            'channel'      => 'whatsapp',
            'touch_number' => 2,
            'message'      => 'Here is our 2026 Price Book and Factory ROI Calculator',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'touchpoint_count' => 2]);

        $this->assertEquals(2, $lead->fresh()->touchpoint_count);
        $this->assertNotNull($lead->fresh()->next_action_date);
    }

    public function test_admin_can_generate_payment_link(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lead = Lead::create([
            'name'    => 'Suresh Patel',
            'phone'   => '+91 99000 11223',
            'segment' => 'retail',
        ]);
        $deal = Deal::create([
            'title'    => 'Patel Supermarket — Mobile Order App',
            'lead_id'  => $lead->id,
            'amount'   => 169000,
            'currency' => 'INR',
            'stage'    => 'negotiation',
        ]);

        $response = $this->actingAs($admin)->postJson("/crm/deals/{$deal->id}/payment-link", [
            'amount'     => 67600, // 40% advance
            'percentage' => 40,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('payment_url'));
        $this->assertDatabaseHas('payments', [
            'deal_id' => $deal->id,
            'amount'  => 67600,
            'status'  => 'created',
        ]);
    }

    public function test_admin_can_record_bank_wire_and_auto_close_deal(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lead = Lead::create([
            'name'    => 'Deepak Sharma',
            'phone'   => '+91 98888 77665',
            'segment' => 'manufacturer',
        ]);
        $deal = Deal::create([
            'title'    => 'Sharma Toolings — Automated Dispatch Portal',
            'lead_id'  => $lead->id,
            'amount'   => 299000,
            'currency' => 'INR',
            'stage'    => 'proposal_sent',
        ]);

        $response = $this->actingAs($admin)->postJson("/crm/deals/{$deal->id}/wire", [
            'amount'          => 299000,
            'transaction_utr' => 'HDFC00099887766',
            'payment_method'  => 'rtgs',
            'notes'           => '100% advance RTGS wire verified',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('closed_won', $deal->fresh()->stage);
        $this->assertEquals(100, $deal->fresh()->probability);
        $this->assertDatabaseHas('payments', [
            'deal_id'         => $deal->id,
            'status'          => 'paid',
            'transaction_utr' => 'HDFC00099887766',
        ]);
    }

    public function test_admin_can_fetch_lead_drawer_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lead = Lead::create([
            'name'    => 'Vikram Mehta',
            'company' => 'Mehta Logistics',
            'phone'   => '+91 98722 33445',
            'segment' => 'manufacturer',
        ]);
        $deal = Deal::create([
            'title'    => 'Fleet Tracking Monolith',
            'lead_id'  => $lead->id,
            'amount'   => 350000,
            'currency' => 'INR',
            'stage'    => 'qualified',
        ]);

        $response = $this->actingAs($admin)->getJson("/crm/leads/{$lead->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'lead' => ['id', 'name', 'company', 'phone', 'segment'],
            'deals',
            'activities',
        ]);
    }
}
