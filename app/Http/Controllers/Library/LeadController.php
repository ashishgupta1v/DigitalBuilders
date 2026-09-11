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
    private const VALID_STATUSES = ['new', 'contacted', 'proposal', 'converted', 'archived'];

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

    public function store(StoreLeadRequest $request, CreateLeadUseCase $useCase): mixed
    {
        $validated = $request->validated();
        $dto = CreateLeadDTO::fromArray($validated);
        $leadDTO = $useCase->execute($dto);

        // Enrich created LeadModel with growth CRM pipeline fields & UTM attribution
        if ($leadDTO->id) {
            $estimatedVal = $validated['estimated_value'] ?? null;
            $amount = 0.0;
            $currency = 'INR';
            if ($estimatedVal) {
                $numericVal = (float) preg_replace('/[^0-9.]/', '', $estimatedVal);
                if ($numericVal > 0) $amount = $numericVal;
                if (str_contains(strtoupper((string) $estimatedVal), '$') || str_contains(strtoupper((string) $estimatedVal), 'USD')) {
                    $currency = 'USD';
                }
            }

            $stage = $validated['stage'] ?? 'new';

            \App\Modules\Library\Infrastructure\Persistence\Models\LeadModel::where('id', $leadDTO->id)->update([
                'source'           => $validated['source'] ?? 'website_contact',
                'region'           => $validated['region'] ?? 'IN',
                'stage'            => $stage,
                'score'            => 75,
                'touchpoint_count' => 0,
                'next_action_date' => now(),
                'next_action_note' => 'Send Touch 1 on WhatsApp',
                'estimated_value'  => $estimatedVal,
                'utm_source'       => $validated['utm_source'] ?? null,
                'utm_medium'       => $validated['utm_medium'] ?? null,
                'utm_campaign'     => $validated['utm_campaign'] ?? null,
                'utm_content'      => $validated['utm_content'] ?? null,
                'utm_term'         => $validated['utm_term'] ?? null,
            ]);

            $deal = \App\Models\Deal::create([
                'title'               => $leadDTO->name . ' — ' . ($leadDTO->projectTypeLabel ?? 'Custom Solution'),
                'lead_id'             => $leadDTO->id,
                'amount'              => $amount,
                'currency'            => $currency,
                'stage'               => $stage,
                'probability'         => \App\Models\Deal::DEFAULT_PROBABILITIES[$stage] ?? 15,
                'expected_close_date' => now()->addDays(21),
                'scope_summary'       => $validated['description'] ?? 'Inbound website inquiry',
            ]);

            \App\Models\Activity::create([
                'lead_id'           => $leadDTO->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'Website Inbound Inquiry Captured',
                'description'       => "Project Type: {$leadDTO->projectTypeLabel}. Ingested into CRM with target value {$deal->formatted_amount}.",
                'touchpoint_number' => 0,
            ]);

            // Instant Telegram Alert for Speed-to-Lead Response
            try {
                app(\App\Services\Telegram\TelegramBotService::class)->sendInboundLeadAlert(
                    name: $leadDTO->name,
                    email: $leadDTO->email,
                    phone: $leadDTO->phone,
                    source: 'Website Contact Form (/contact)',
                    projectType: $leadDTO->projectTypeLabel ?? 'Custom Solution',
                    budget: $estimatedVal ?: ($amount > 0 ? "{$currency} " . number_format($amount) : null),
                    timeline: null,
                    features: [],
                    notes: $validated['description'] ?? null
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Contact form Telegram alert error: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Thank you! Your inquiry has been received. We'll respond within 24 business hours.",
                'lead_id' => $leadDTO->id,
            ]);
        }

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

    public function getNotes(int $id): \Illuminate\Http\JsonResponse
    {
        $notes = \App\Models\LeadNote::query()
            ->where('lead_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notes);
    }

    public function addNote(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        \App\Models\LeadNote::create([
            'lead_id' => $id,
            'user_id' => $user?->id,
            'author_name' => $user?->name ?? 'Admin',
            'note' => $validated['note'],
        ]);

        \App\Modules\Library\Infrastructure\Persistence\Models\LeadModel::where('id', $id)->increment('notes_count');

        return back()->with('success', 'Note added to lead.');
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
