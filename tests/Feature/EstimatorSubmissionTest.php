<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatorSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_estimator_page_loads_successfully(): void
    {
        $response = $this->get('/estimator');
        $response->assertStatus(200);
    }

    public function test_estimator_submission_stores_lead_with_valid_enum(): void
    {
        $response = $this->post(route('estimator.submit'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+91 9876543210',
            'project_type' => 'web_app',
            'estimated_budget' => '₹1,50,000 - ₹2,50,000',
            'estimated_timeline' => '4-6 weeks',
            'features' => ['User Authentication', 'Payment Gateway'],
            'description' => 'Looking for an MVP build.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'project_type' => 'web_app',
        ]);
    }

    public function test_estimator_submission_handles_human_label_without_exception(): void
    {
        $response = $this->post(route('estimator.submit'), [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+91 9876543211',
            'project_type' => 'Web Application',
            'estimated_budget' => '₹1,20,000',
            'estimated_timeline' => '3-4 weeks',
            'features' => ['User Authentication'],
            'description' => 'Testing label mapping.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'project_type' => 'web_app',
        ]);
    }

    public function test_estimator_submission_silently_drops_bot_with_honeypot(): void
    {
        $response = $this->post(route('estimator.submit'), [
            'name' => 'Spam Bot',
            'email' => 'bot@spammer.com',
            'phone' => '+1234567890',
            'project_type' => 'web_app',
            '_hp_company' => 'Acme Spam LLC',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('leads', [
            'name' => 'Spam Bot',
            'email' => 'bot@spammer.com',
        ]);
    }

    public function test_estimator_submission_auto_ingests_into_crm_pipeline_with_deal_and_scoring(): void
    {
        $response = $this->post(route('estimator.submit'), [
            'name' => 'Rahul Sharma',
            'email' => 'rahul@apextech.in',
            'phone' => '+91 9811223344',
            'project_type' => 'erp_crm',
            'estimated_budget' => '₹2,50,000 - ₹3,50,000 (INR)',
            'estimated_timeline' => '6 - 8 Weeks',
            'features' => ['Enterprise ERP', 'Inventory Sync', 'WhatsApp Bots'],
            'description' => 'Need custom ERP system for 3 warehouses and automated GST invoicing.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'name' => 'Rahul Sharma',
            'email' => 'rahul@apextech.in',
            'source' => 'estimator_calculator',
            'segment' => 'manufacturer',
            'stage' => 'new',
        ]);

        $lead = \App\Modules\Library\Infrastructure\Persistence\Models\LeadModel::where('email', 'rahul@apextech.in')->first();
        $this->assertNotNull($lead);
        $this->assertGreaterThanOrEqual(85, $lead->score);

        $this->assertDatabaseHas('deals', [
            'lead_id' => $lead->id,
            'stage' => 'new',
            'currency' => 'INR',
            'amount' => 300000.00, // Average of 250k and 350k
        ]);

        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'stage_change',
            'subject' => 'Estimator Inbound Lead Captured',
        ]);
    }
}
