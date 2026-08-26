<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PricingPageTest extends TestCase
{
    public function test_pricing_page_renders_successfully(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Pricing'));
    }

    public function test_india_pricing_brochure_file_exists(): void
    {
        $filePath = public_path('downloads/digitalbuilders-pricing-india-inr.html');
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);
        $this->assertStringContainsString('India Price Book', $content);
        $this->assertStringContainsString('₹99,000', $content);
        $this->assertStringContainsString('₹1,79,000', $content);
    }

    public function test_international_pricing_brochure_file_exists(): void
    {
        $filePath = public_path('downloads/digitalbuilders-pricing-international-usd.html');
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);
        $this->assertStringContainsString('International Price Book', $content);
        $this->assertStringContainsString('$3,500', $content);
        $this->assertStringContainsString('$6,500', $content);
        $this->assertStringContainsString('Gulf', $content);
    }

    public function test_sitemap_contains_pricing_page(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee('/pricing');
    }
}
