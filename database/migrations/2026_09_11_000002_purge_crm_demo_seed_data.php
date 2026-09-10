<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations: permanently purge all mock demo entities from CRM.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $demoEmails = [
                'rajesh@gargpackaging.com',
                'dr.shreya@guttalks.in',
                'marcus@fitpulse.ai',
                'vikram@singhaniatex.com',
                'sunil@ludhianatools.in',
            ];

            $demoOrgNames = [
                'Garg Packaging Mills',
                'GutTalks Clinic',
                'FitPulse AI Labs',
                'Singhania Hosiery & Garments',
                'Ludhiana Precision Tools',
            ];

            // 1. Identify demo leads
            $demoLeadIds = Lead::query()
                ->whereIn('email', $demoEmails)
                ->orWhereIn('company', $demoOrgNames)
                ->pluck('id')
                ->toArray();

            // 2. Identify demo organizations
            $demoOrgIds = Organization::query()
                ->whereIn('name', $demoOrgNames)
                ->pluck('id')
                ->toArray();

            // 3. Identify demo deals
            $demoDealIds = Deal::query()
                ->whereIn('lead_id', $demoLeadIds)
                ->orWhereIn('organization_id', $demoOrgIds)
                ->pluck('id')
                ->toArray();

            // 4. Delete payments & activities tied to demo deals or leads
            Payment::query()->whereIn('deal_id', $demoDealIds)->delete();
            Activity::query()
                ->whereIn('lead_id', $demoLeadIds)
                ->orWhereIn('deal_id', $demoDealIds)
                ->delete();

            // 5. Delete demo deals
            Deal::query()->whereIn('id', $demoDealIds)->delete();

            // 6. Delete demo leads
            Lead::query()->whereIn('id', $demoLeadIds)->delete();

            // 7. Delete demo organizations
            Organization::query()->whereIn('id', $demoOrgIds)->delete();

            // 8. Delete generic demo admin account if it exists
            User::query()->where('email', 'admin@digitalbuilders.in')->delete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally irreversible to prevent mock data restoration in production
    }
};
