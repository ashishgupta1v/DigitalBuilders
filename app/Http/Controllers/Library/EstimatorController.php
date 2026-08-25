<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Modules\Library\Application\DTOs\CreateLeadDTO;
use App\Modules\Library\Application\UseCases\CreateLeadUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EstimatorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Estimator');
    }

    public function submitEstimate(Request $request, CreateLeadUseCase $useCase): RedirectResponse
    {
        // Honeypot anti-spam check
        if (! empty($request->input('_hp_company'))) {
            return back()->with('success', 'Your project estimate and inquiry have been received!');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'project_type' => ['required', 'string', 'max:100'],
            'estimated_budget' => ['nullable', 'string', 'max:100'],
            'estimated_timeline' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'array'],
            'description' => ['nullable', 'string', 'max:2000'],
            '_hp_company' => ['nullable', 'string', 'max:0'],
        ]);

        $fullDescription = "Estimated Budget: " . ($validated['estimated_budget'] ?? 'Not specified') . "\n"
            . "Estimated Timeline: " . ($validated['estimated_timeline'] ?? 'Not specified') . "\n"
            . "Selected Features: " . (isset($validated['features']) ? implode(', ', $validated['features']) : 'None') . "\n\n"
            . ($validated['description'] ?? '');

        $dto = CreateLeadDTO::fromArray([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'project_type' => $validated['project_type'],
            'description' => trim($fullDescription),
        ]);

        $useCase->execute($dto);

        return back()->with('success', 'Your project estimate and inquiry have been received! We will reach out with a detailed roadmap within 24 business hours.');
    }
}
