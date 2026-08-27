<?php

namespace Tests\Feature;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAndPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_page_renders_successfully(): void
    {
        $response = $this->get('/book');

        $response->assertStatus(200);
        $response->assertSee('Book a Free Architecture Consultation', false);
    }

    public function test_lead_store_persists_growth_pipeline_fields_and_utm_attribution(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+14155552671',
            'project_type' => 'saas',
            'description' => 'Looking to build a multi-tenant logistics dashboard.',
            'source' => 'booking',
            'region' => 'USD',
            'stage' => 'qualified',
            'estimated_value' => '$15,000',
            'utm_source' => 'linkedin',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'q3_architect_push',
        ];

        $response = $this->post(route('library.leads.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'email' => 'john.doe@example.com',
            'source' => 'booking',
            'region' => 'USD',
            'stage' => 'qualified',
            'estimated_value' => '$15,000',
            'utm_source' => 'linkedin',
            'utm_campaign' => 'q3_architect_push',
        ]);
    }

    public function test_geo_detection_returns_inr_for_india_ip(): void
    {
        $response = $this->withHeaders([
            'x-vercel-ip-country' => 'IN',
        ])->get('/pricing');

        $response->assertStatus(200);
        $pageProps = $response->viewData('page')['props'] ?? [];
        $this->assertEquals('IN', $pageProps['geo']['country'] ?? null);
        $this->assertEquals('INR', $pageProps['geo']['region'] ?? null);
    }

    public function test_geo_detection_returns_gulf_for_ae_ip(): void
    {
        $response = $this->withHeaders([
            'x-vercel-ip-country' => 'AE',
        ])->get('/pricing');

        $response->assertStatus(200);
        $pageProps = $response->viewData('page')['props'] ?? [];
        $this->assertEquals('AE', $pageProps['geo']['country'] ?? null);
        $this->assertEquals('GULF', $pageProps['geo']['region'] ?? null);
    }
}
