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
        $this->assertStringContainsString('₹19,999', $content);
        $this->assertStringContainsString('₹79,000', $content);
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

    public function test_pricing_pdf_files_exist(): void
    {
        $this->assertFileExists(public_path('downloads/digitalbuilders-pricing-india-inr.pdf'));
        $this->assertFileExists(public_path('downloads/digitalbuilders-pricing-international-usd.pdf'));
    }

    public function test_brochures_accessible_via_http_route(): void
    {
        $resInrPdf = $this->get('/downloads/digitalbuilders-pricing-india-inr.pdf');
        $resInrPdf->assertStatus(200);
        $resInrPdf->assertHeader('Content-Type', 'application/pdf');

        $resUsdPdf = $this->get('/downloads/digitalbuilders-pricing-international-usd.pdf');
        $resUsdPdf->assertStatus(200);
        $resUsdPdf->assertHeader('Content-Type', 'application/pdf');

        $resInr = $this->get('/downloads/digitalbuilders-pricing-india-inr.html');
        $resInr->assertStatus(200);
        $resInr->assertSee('India Price Book');

        $resUsd = $this->get('/downloads/digitalbuilders-pricing-international-usd.html');
        $resUsd->assertStatus(200);
        $resUsd->assertSee('International Price Book');
    }

    public function test_sitemap_contains_pricing_page(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee('/pricing');
    }
}
