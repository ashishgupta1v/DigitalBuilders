<?php

declare(strict_types=1);

namespace App\Services;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;
use Parsedown;

class BlogService
{
    private string $contentPath;
    private Parsedown $parsedown;

    public function __construct()
    {
        $this->contentPath = resource_path('markdown/blog');
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(false);
    }

    /**
     * Get all blog posts with metadata.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllPosts(): array
    {
        return Cache::remember('blog_posts_all', 3600, function (): array {
            if (! is_dir($this->contentPath)) {
                return [];
            }

            $files = glob($this->contentPath . '/*.md');
            if (! $files) {
                return [];
            }

            $posts = [];
            foreach ($files as $file) {
                $post = $this->parseFile($file);
                if ($post) {
                    $posts[] = $post;
                }
            }

            // Sort posts by date descending
            usort($posts, fn ($a, $b) => strcmp($b['date'], $a['date']));

            return $posts;
        });
    }

    /**
     * Find a post by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getPostBySlug(string $slug): ?array
    {
        return Cache::remember("blog_post_{$slug}", 3600, function () use ($slug): ?array {
            $filePath = $this->contentPath . '/' . $slug . '.md';
            if (! file_exists($filePath)) {
                return null;
            }

            return $this->parseFile($filePath, includeHtml: true);
        });
    }

    /**
     * Generate standard RSS 2.0 XML feed for blog articles.
     */
    public function generateRssFeed(): string
    {
        return Cache::remember('blog_rss_feed_xml', 3600, function (): string {
            $posts = $this->getAllPosts();
            $baseUrl = rtrim(config('app.url', 'https://www.digitalbuilders.in'), '/');
            $feedUrl = $baseUrl . '/feed.xml';
            $nowRfc = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::RSS);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
            $xml .= "  <channel>\n";
            $xml .= "    <title>DigitalBuilders Blog — Enterprise Software &amp; AI Architecture</title>\n";
            $xml .= "    <link>{$baseUrl}/blog</link>\n";
            $xml .= "    <description>In-depth architectural breakdowns, DDD case studies, AI workflows, and software engineering insights by DigitalBuilders.</description>\n";
            $xml .= "    <language>en-in</language>\n";
            $xml .= "    <lastBuildDate>{$nowRfc}</lastBuildDate>\n";
            $xml .= "    <atom:link href=\"{$feedUrl}\" rel=\"self\" type=\"application/rss+xml\" />\n";

            foreach ($posts as $post) {
                $postUrl = $baseUrl . '/blog/' . htmlspecialchars($post['slug']);
                $title = htmlspecialchars((string) $post['title']);
                $excerpt = htmlspecialchars((string) ($post['excerpt'] ?? ''));
                $author = htmlspecialchars((string) ($post['author'] ?? 'Ashish Gupta'));
                $category = htmlspecialchars((string) ($post['category'] ?? 'Engineering'));

                try {
                    $pubDate = (new DateTime((string) $post['date'], new DateTimeZone('UTC')))->format(DateTime::RSS);
                } catch (\Throwable) {
                    $pubDate = $nowRfc;
                }

                $xml .= "    <item>\n";
                $xml .= "      <title>{$title}</title>\n";
                $xml .= "      <link>{$postUrl}</link>\n";
                $xml .= "      <guid isPermaLink=\"true\">{$postUrl}</guid>\n";
                $xml .= "      <pubDate>{$pubDate}</pubDate>\n";
                $xml .= "      <author>hello@digitalbuilders.in ({$author})</author>\n";
                $xml .= "      <category>{$category}</category>\n";
                $xml .= "      <description>{$excerpt}</description>\n";
                $xml .= "    </item>\n";
            }

            $xml .= "  </channel>\n";
            $xml .= '</rss>';

            return $xml;
        });
    }

    /**
     * Parse a markdown file into structured metadata and content.
     *
     * @return array<string, mixed>|null
     */
    private function parseFile(string $filePath, bool $includeHtml = false): ?array
    {
        $raw = file_get_contents($filePath);
        if (! $raw) {
            return null;
        }

        $slug = basename($filePath, '.md');
        $meta = [
            'slug' => $slug,
            'title' => 'Untitled Post',
            'date' => '2026-01-01',
            'author' => 'Ashish Gupta',
            'category' => 'Engineering',
            'tags' => ['Architecture'],
            'excerpt' => '',
            'read_time' => '5 min read',
            'cover_image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&q=80&w=1200',
        ];

        // Match YAML frontmatter between --- and ---
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)/s', $raw, $matches)) {
            $frontmatter = $matches[1];
            $body = $matches[2];

            foreach (explode("\n", $frontmatter) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }

                if (str_contains($line, ':')) {
                    [$key, $value] = explode(':', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'");

                    if ($key === 'tags') {
                        $meta['tags'] = array_map('trim', explode(',', $value));
                    } else {
                        $meta[$key] = $value;
                    }
                }
            }
        } else {
            $body = $raw;
        }

        // Calculate reading time
        $wordCount = str_word_count(strip_tags($body));
        $meta['read_time'] = ceil($wordCount / 200) . ' min read';

        if ($includeHtml) {
            $meta['content_html'] = $this->parsedown->text($body);
        }

        return $meta;
    }
}
