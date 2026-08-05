<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(BlogService $service): Response
    {
        $posts = $service->getAllPosts();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $slug, BlogService $service): Response
    {
        $post = $service->getPostBySlug($slug);

        if (! $post) {
            abort(404, 'Blog article not found.');
        }

        $allPosts = $service->getAllPosts();
        $related = array_values(array_filter($allPosts, fn ($p) => $p['slug'] !== $slug));

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'related' => array_slice($related, 0, 2),
        ]);
    }
}
