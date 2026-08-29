<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(Request $request, BlogService $blogService): Response
    {
        $host = $request->getHost();
        if ($host === 'localhost' || $host === '127.0.0.1') {
            $baseUrl = rtrim($request->schemeAndHttpHost(), '/');
        } else {
            $baseUrl = 'https://www.digitalbuilders.in';
        }
        $now = date('c');

        $urls = [
            [
                'loc' => $baseUrl . '/',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            // Service Landing Pages
            [
                'loc' => $baseUrl . '/services/web-applications',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => $baseUrl . '/services/mobile-apps',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => $baseUrl . '/services/ai-solutions',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => $baseUrl . '/services/erp-crm',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => $baseUrl . '/services/saas-platforms',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => $baseUrl . '/services/growth',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            // 9 Production Case Studies
            [
                'loc' => $baseUrl . '/portfolio/habuilt',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/dhandadiary',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/zoeticoach',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/guttalks',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/myastrova',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/gaushala',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/sports-club',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/garg-enterprises',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/portfolio/ashishgupta',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            // Estimator, Pricing, Blog Hub & RSS Feed
            [
                'loc' => $baseUrl . '/pricing',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/estimator',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => $baseUrl . '/blog',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/feed.xml',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/library/contact',
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
        ];

        // Append Blog Articles dynamically
        try {
            $posts = $blogService->getAllPosts();
            foreach ($posts as $post) {
                if (isset($post['slug'])) {
                    $urls[] = [
                        'loc' => $baseUrl . '/blog/' . $post['slug'],
                        'lastmod' => isset($post['date']) ? date('c', strtotime($post['date'])) : $now,
                        'changefreq' => 'monthly',
                        'priority' => '0.7',
                    ];
                }
            }
        } catch (\Throwable) {
            // Fallback gracefully
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
