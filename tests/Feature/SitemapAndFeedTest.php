<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SitemapAndFeedTest extends TestCase
{
    public function test_sitemap_xml_returns_valid_xml_with_canonical_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString('/portfolio/ashishgupta', $content);
        $this->assertStringNotContainsString('/portfolio/ssknitwear', $content);
        $this->assertStringContainsString('/feed.xml', $content);
    }

    public function test_feed_xml_returns_valid_rss_feed(): void
    {
        $response = $this->get('/feed.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<rss version="2.0"', $content);
        $this->assertStringContainsString('<title>DigitalBuilders Blog', $content);
        $this->assertStringContainsString('<atom:link', $content);
    }
}
