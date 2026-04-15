<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\StoreLeadRequest;
use App\Modules\Library\Application\DTOs\CreateLeadDTO;
use App\Modules\Library\Application\Interfaces\MarkdownRendererInterface;
use App\Modules\Library\Application\UseCases\CreateLeadUseCase;
use App\Modules\Library\Application\UseCases\ListLeadsUseCase;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(ListLeadsUseCase $useCase): Response
    {
        return Inertia::render('Library/Leads', [
            'leads' => $useCase->execute(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Library/ContactForm');
    }

    public function store(StoreLeadRequest $request, CreateLeadUseCase $useCase): RedirectResponse
    {
        $dto = CreateLeadDTO::fromArray($request->validated());
        $useCase->execute($dto);

        return back()
            ->with('success', 'Thank you! Your inquiry has been received. We\'ll respond within 24 business hours.');
    }

    public function docs(MarkdownRendererInterface $renderer): Response
    {
        $readmePath = base_path('README.md');

        $markdown = file_exists($readmePath)
            ? file_get_contents($readmePath)
            : '# No README found';

        return Inertia::render('Library/Docs', [
            'html' => $renderer->toHtml($markdown),
        ]);
    }
}
