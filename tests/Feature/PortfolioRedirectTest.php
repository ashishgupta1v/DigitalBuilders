<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioRedirectTest extends TestCase
{
    public function test_ashishgupta_portfolio_page_loads_successfully(): void
    {
        $response = $this->get('/portfolio/ashishgupta');
        $response->assertStatus(200);
    }

    public function test_ssknitwear_legacy_url_permanently_redirects_to_ashishgupta(): void
    {
        $response = $this->get('/portfolio/ssknitwear');
        $response->assertStatus(301);
        $response->assertRedirect('/portfolio/ashishgupta');
    }
}
