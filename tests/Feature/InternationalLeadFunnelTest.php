<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MarketRequirement;
use App\Models\User;
use App\Services\SalesFunnel\AiPitchGeneratorService;
use App\Services\SalesFunnel\InternationalLeadScraperService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InternationalLeadFunnelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tier1_filters_reject_junk_and_accept_high_value_rfps(): void
    {
        $pitchGenerator = new AiPitchGeneratorService();
        $telegramBot = new TelegramBotService();
        $scraper = new InternationalLeadScraperService($pitchGenerator, $telegramBot);

        // Negative keywords should fail
        $this->assertFalse($scraper->passesTier1Filters('Need cheap logo design for my startup $20'));
        $this->assertFalse($scraper->passesTier1Filters('Need simple wordpress fix and css tweak'));
        $this->assertFalse($scraper->passesTier1Filters('Video editor needed for youtube channel'));

        // Positive engineering RFPs should pass
        $this->assertTrue($scraper->passesTier1Filters('Hiring full-stack engineer for SaaS MVP with Laravel, Vue and AI agent integration ($5,000 budget)'));
        $this->assertTrue($scraper->passesTier1Filters('Looking for senior backend developer to build custom ERP portal with PostgreSQL'));
    }

    public function test_ai_pitch_generator_produces_upwork_and_reddit_pitches_with_dual_cta(): void
    {
        $pitchGenerator = new AiPitchGeneratorService();
        $result = $pitchGenerator->generatePitch(
            'Need a full-stack developer to build an interactive SaaS platform with real-time dashboards and Stripe checkout.',
            'David',
            'NovaTech Labs',
            'USD',
            '$5,000'
        );

        $this->assertArrayHasKey('upwork_proposal', $result);
        $this->assertArrayHasKey('reddit_dm', $result);
        $this->assertArrayHasKey('short_pitch', $result);
        $this->assertArrayHasKey('email_pitch', $result);

        // Verify Dual CTA in Upwork proposal
        $this->assertStringContainsString('https://www.digitalbuilders.in/book', $result['upwork_proposal']);
        $this->assertStringContainsString('https://www.digitalbuilders.in/estimator', $result['upwork_proposal']);

        // Verify Dual CTA in Reddit DM
        $this->assertStringContainsString('https://www.digitalbuilders.in/book', $result['reddit_dm']);
        $this->assertStringContainsString('https://www.digitalbuilders.in/estimator', $result['reddit_dm']);
    }

    public function test_hacker_news_polling_ingests_and_notifies(): void
    {
        Http::fake([
            'https://hn.algolia.com/api/v1/search_by_date*' => Http::response([
                'hits' => [
                    [
                        'objectID'     => 'hn_mock_12345',
                        'comment_text' => 'SEEKING FREELANCER | CloudScale Inc (Remote US/EU) | We need a full-stack developer to architect our SaaS customer portal in Vue 3 and Laravel. Budget is $6,000 - $8,000 fixed price. Email us at founders@cloudscale.io with your portfolio.',
                        'author'       => 'cloudscalefounder',
                        'story_title'  => 'Ask HN: Who is hiring? (September 2026)',
                        'story_id'     => 99999,
                    ]
                ]
            ], 200),
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $pitchGenerator = new AiPitchGeneratorService();
        $telegramBot = new TelegramBotService();
        $scraper = new InternationalLeadScraperService($pitchGenerator, $telegramBot);

        $ingested = $scraper->pollHackerNews();

        $this->assertEquals(1, $ingested);
        $this->assertDatabaseHas('market_requirements', [
            'source'        => 'hackernews',
            'external_id'   => 'hn_mock_12345',
            'currency'      => 'USD',
            'contact_name'  => 'cloudscalefounder',
            'contact_email' => 'founders@cloudscale.io',
            'status'        => 'qualified',
        ]);
    }

    public function test_remotive_polling_ingests_and_notifies(): void
    {
        Http::fake([
            'https://remotive.com/api/remote-jobs*' => Http::response([
                'jobs' => [
                    [
                        'id'                          => 771122,
                        'title'                       => 'Senior Full Stack Laravel & Vue Architect',
                        'company_name'                => 'SaaS Rocket Ltd',
                        'candidate_required_location' => 'USA / Europe Remote',
                        'salary'                      => '$8,000 - $12,000 monthly',
                        'description'                 => 'We are looking for an expert fullstack software engineer to scale our cloud SaaS platform built on Laravel and Vue.js.',
                        'url'                         => 'https://remotive.com/job/771122',
                    ]
                ]
            ], 200),
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $pitchGenerator = new AiPitchGeneratorService();
        $telegramBot = new TelegramBotService();
        $scraper = new InternationalLeadScraperService($pitchGenerator, $telegramBot);

        $ingested = $scraper->pollRemotive();

        $this->assertEquals(1, $ingested);
        $this->assertDatabaseHas('market_requirements', [
            'source'          => 'remotive',
            'external_id'     => '771122',
            'contact_company' => 'SaaS Rocket Ltd',
            'currency'        => 'USD',
            'status'          => 'qualified',
        ]);
    }

    public function test_himalayas_polling_ingests_and_notifies(): void
    {
        Http::fake([
            'https://himalayas.app/jobs/api*' => Http::response([
                'jobs' => [
                    [
                        'guid'            => 'https://himalayas.app/jobs/himalaya-mock-99',
                        'applicationLink' => 'https://himalayas.app/jobs/himalaya-mock-99',
                        'title'           => 'Fullstack Web App Developer',
                        'companyName'     => 'Atlas Global',
                        'minSalary'       => 7000,
                        'maxSalary'       => 15000,
                        'description'     => 'Seeking contract full-stack developer with Vue, TypeScript and Node experience to build customer dashboard.',
                    ]
                ]
            ], 200),
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $pitchGenerator = new AiPitchGeneratorService();
        $telegramBot = new TelegramBotService();
        $scraper = new InternationalLeadScraperService($pitchGenerator, $telegramBot);

        $ingested = $scraper->pollHimalayas();

        $this->assertEquals(1, $ingested);
        $this->assertDatabaseHas('market_requirements', [
            'source'          => 'himalayas',
            'external_id'     => 'https://himalayas.app/jobs/himalaya-mock-99',
            'contact_company' => 'Atlas Global',
            'currency'        => 'USD',
            'status'          => 'qualified',
        ]);
    }

    public function test_poll_market_requirements_command_executes(): void
    {
        Http::fake([
            'https://hn.algolia.com/*'     => Http::response(['hits' => []], 200),
            'https://remoteok.com/*'       => Http::response([], 200),
            'https://remotive.com/*'       => Http::response(['jobs' => []], 200),
            'https://himalayas.app/*'      => Http::response(['jobs' => []], 200),
            'https://weworkremotely.com/*' => Http::response('', 200),
        ]);

        $this->artisan('market:poll-requirements')
            ->assertExitCode(0);
    }

    public function test_crm_dashboard_receives_market_requirements(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        MarketRequirement::create([
            'source'           => 'hackernews',
            'external_id'      => 'test_ext_001',
            'title'            => 'HN: Need SaaS Fullstack Dev',
            'raw_text'         => 'Looking for a senior engineer to ship our MVP.',
            'budget_raw'       => '$5,000',
            'estimated_amount' => 5000.00,
            'currency'         => 'USD',
            'location'         => 'US',
            'matched_segment'  => 'saas_ai',
            'relevance_score'  => 85,
            'pitch_draft'      => 'Hi there, saw your post...',
            'status'           => 'qualified',
        ]);

        $response = $this->actingAs($admin)->get('/crm');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Crm/Dashboard')
                ->has('market_requirements', 1)
        );
    }
}
