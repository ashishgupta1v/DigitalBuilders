<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(): JsonResponse
    {
        $testimonials = Testimonial::query()
            ->where('is_featured', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($testimonials);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'url', 'max:500'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'max:2000'],
            'project_type' => ['nullable', 'string', 'max:255'],
            'metric_highlight' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        Testimonial::create($validated);

        return back()->with('success', 'Testimonial created successfully.');
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'url', 'max:500'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'max:2000'],
            'project_type' => ['nullable', 'string', 'max:255'],
            'metric_highlight' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $testimonial->update($validated);

        return back()->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted successfully.');
    }
}
