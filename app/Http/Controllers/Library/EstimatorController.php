<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Modules\Library\Application\DTOs\CreateLeadDTO;
use App\Modules\Library\Application\UseCases\CreateLeadUseCase;
use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
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

        $leadDTO = $useCase->execute($dto);

        // Auto-ingest into Growth CRM Pipeline with lead scoring, segment mapping & deal generation
        if ($leadDTO->id) {
            $estimatedBudgetString = $validated['estimated_budget'] ?? '';
            $currency = 'INR';
            if (str_contains(strtoupper($estimatedBudgetString), '$') || str_contains(strtoupper($estimatedBudgetString), 'USD')) {
                $currency = 'USD';
            } elseif (str_contains(strtoupper($estimatedBudgetString), 'AED') || str_contains(strtoupper($estimatedBudgetString), 'GULF')) {
                $currency = 'AED';
            }

            // Extract numeric amount from budget (e.g. "₹1,50,000 - ₹2,50,000" -> average 200,000)
            $amount = 149000.0;
            if ($currency === 'USD') {
                $amount = 4500.0;
            } elseif ($currency === 'AED') {
                $amount = 4000.0;
            }

            preg_match_all('/[\d,]+/', $estimatedBudgetString, $matches);
            if (! empty($matches[0])) {
                $numbers = array_values(array_filter(array_map(function ($val) {
                    $clean = (float) str_replace(',', '', $val);
                    return $clean > 0 ? $clean : null;
                }, $matches[0])));

                if (count($numbers) >= 2) {
                    $amount = round(($numbers[0] + $numbers[1]) / 2, 2);
                } elseif (count($numbers) === 1) {
                    $amount = round($numbers[0], 2);
                }
            }

            // Map segment based on project type & region
            $pt = strtolower($validated['project_type']);
            $segment = match (true) {
                str_contains($pt, 'erp') || str_contains($pt, 'crm') => 'manufacturer',
                str_contains($pt, 'saas') || str_contains($pt, 'ai') || $currency === 'USD' => 'international',
                str_contains($pt, 'grow') || str_contains($pt, 'market') || str_contains($pt, 'presence') || str_contains($pt, 'ecom') => 'local_sme',
                default => 'startup',
            };

            // Automated Lead Scoring (70 - 95 pts)
            $score = 70;
            if (! empty($validated['phone'])) {
                $score += 10;
            }
            if (! empty($validated['estimated_budget'])) {
                $score += 5;
            }
            if (! empty($validated['features']) && count($validated['features']) >= 2) {
                $score += 5;
            }
            if (! empty($validated['description']) && strlen($validated['description']) > 15) {
                $score += 5;
            }

            $region = match ($currency) {
                'USD' => 'US',
                'AED' => 'AE',
                default => 'IN',
            };

            LeadModel::where('id', $leadDTO->id)->update([
                'segment'           => $segment,
                'source'            => 'estimator_calculator',
                'region'            => $region,
                'stage'             => 'new',
                'score'             => $score,
                'touchpoint_count'  => 0,
                'next_action_date'  => now(),
                'next_action_note'  => 'Send Estimator Follow-up on WhatsApp',
                'estimated_value'   => $estimatedBudgetString ?: null,
            ]);

            $deal = Deal::create([
                'title'               => $leadDTO->name . ' — ' . ($leadDTO->projectTypeLabel ?? 'Calculated Estimate'),
                'lead_id'             => $leadDTO->id,
                'amount'              => $amount,
                'currency'            => $currency,
                'stage'               => 'new',
                'probability'         => Deal::DEFAULT_PROBABILITIES['new'] ?? 10,
                'expected_close_date' => now()->addDays(21),
                'scope_summary'       => "Interactive Estimator submission.\nTimeline: " . ($validated['estimated_timeline'] ?? 'Not specified') . "\nModules: " . (! empty($validated['features']) ? implode(', ', $validated['features']) : 'Core platform'),
            ]);

            Activity::create([
                'lead_id'           => $leadDTO->id,
                'deal_id'           => $deal->id,
                'type'              => 'stage_change',
                'subject'           => 'Estimator Inbound Lead Captured',
                'description'       => "Interactive Estimator budget: {$estimatedBudgetString}. Auto-scored {$score}/100 and placed in Sales Queue.",
                'touchpoint_number' => 0,
            ]);
        }

        return back()->with('success', 'Your project estimate and inquiry have been received! We will reach out with a detailed roadmap within 24 business hours.');
    }
}
