<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MarketRequirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CleanIrrelevantMarketRequirementsSeeder extends Seeder
{
    /**
     * Purge corporate full-time jobs, non-target technologies, and irrelevant job-board scrapings.
     */
    public function run(): void
    {
        $irrelevantKeywords = [
            'network engineer',
            'mule esb',
            'solutions consultant',
            'sap consultant',
            'salesforce admin',
            'lifelancer',
            'cobol',
            'devops sysadmin',
            'system administrator',
            'scrum master',
            'product manager',
            'account executive',
            'customer success',
            'qa engineer',
            'test automation',
        ];

        $query = MarketRequirement::query()
            ->where(function ($q) use ($irrelevantKeywords) {
                foreach ($irrelevantKeywords as $kw) {
                    $q->orWhere('title', 'like', "%{$kw}%")
                      ->orWhere('raw_text', 'like', "%{$kw}%");
                }
            })
            ->whereNull('deal_id');

        $count = $query->count();
        $query->delete();

        // Also clean up any un-decoded HTML entities in remaining records
        $remaining = MarketRequirement::all();
        foreach ($remaining as $req) {
            $cleanedTitle = html_entity_decode((string) $req->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $cleanedRaw = html_entity_decode((string) $req->raw_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($cleanedTitle !== $req->title || $cleanedRaw !== $req->raw_text) {
                $req->update([
                    'title' => $cleanedTitle,
                    'raw_text' => $cleanedRaw,
                ]);
            }
        }

        $this->command->info("Purged {$count} irrelevant market requirements and sanitized remaining records.");
    }
}
