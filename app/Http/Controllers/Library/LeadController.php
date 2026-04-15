<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\StoreLeadRequest;
use App\Modules\Library\Application\DTOs\CreateLeadDTO;
use App\Modules\Library\Application\Interfaces\MarkdownRendererInterface;
use App\Modules\Library\Application\UseCases\CreateLeadUseCase;
use App\Modules\Library\Application\UseCases\ListLeadsUseCase;
use App\Modules\Library\Domain\Repositories\LeadRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    private const VALID_STATUSES = ['new', 'contacted', 'converted'];

    public function index(Request $request, ListLeadsUseCase $useCase): Response
    {
        $filters = [
            'status' => $request->query('status', ''),
            'search' => $request->query('search', ''),
        ];

        return Inertia::render('Library/Leads', [
            'leads'   => $useCase->execute(array_filter($filters)),
            'filters' => $filters,
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

    public function updateStatus(Request $request, int $id, LeadRepositoryInterface $repository): RedirectResponse
    {
        $status = $request->input('status');

        if (! in_array($status, self::VALID_STATUSES, true)) {
            return back()->withErrors(['status' => 'Invalid status value.']);
        }

        $repository->updateStatus($id, $status);

        return back()->with('success', 'Lead status updated.');
    }

    public function export(ListLeadsUseCase $useCase): HttpResponse
    {
        $leads = $useCase->execute();

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="leads.csv"'];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Project Type', 'Status', 'Description', 'Submitted At']);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->projectTypeLabel,
                    $lead->status,
                    $lead->description ?? '',
                    $lead->createdAt ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
