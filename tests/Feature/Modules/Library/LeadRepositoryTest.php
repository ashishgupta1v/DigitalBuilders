<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Library;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_can_be_stored_via_model(): void
    {
        $lead = LeadModel::factory()->create([
            'name'         => 'Test User',
            'email'        => 'test@example.com',
            'project_type' => 'web_app',
        ]);

        $this->assertDatabaseHas('leads', [
            'id'    => $lead->id,
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_contact_form_page_loads(): void
    {
        $response = $this->get(route('library.leads.create'));
        $response->assertStatus(200);
    }

    public function test_lead_submission_validates_required_fields(): void
    {
        $response = $this->post(route('library.leads.store'), []);
        $response->assertSessionHasErrors(['name', 'email', 'phone', 'project_type']);
    }

    public function test_lead_submission_stores_valid_data(): void
    {
        $response = $this->post(route('library.leads.store'), [
            'name'         => 'Ashish Gupta',
            'email'        => 'hello@digitalbuilders.in',
            'phone'        => '+91 90870 21592',
            'project_type' => 'ai_solutions',
            'description'  => 'Need AI workflow automation.',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('leads', [
            'name'         => 'Ashish Gupta',
            'email'        => 'hello@digitalbuilders.in',
            'project_type' => 'ai_solutions',
        ]);
    }
}
