<?php

declare(strict_types=1);

namespace App\Services;

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
    }

    /**
     * Find a post by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getPostBySlug(string $slug): ?array
    {
        $filePath = $this->contentPath . '/' . $slug . '.md';
        if (! file_exists($filePath)) {
            return null;
        }

        return $this->parseFile($filePath, includeHtml: true);
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
