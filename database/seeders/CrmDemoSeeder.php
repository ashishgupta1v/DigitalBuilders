<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Founder / Admin User Exists
        $admin = User::firstOrCreate(
            ['email' => 'ashish@digitalbuilders.in'],
            [
                'name'              => 'Ashish Gupta',
                'password'          => Hash::make('password'),
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->is_admin) {
            $admin->update(['is_admin' => true]);
        }

        // Also elevate existing Ashish accounts to admin
        User::whereIn('email', ['ashishgupta1v@gmail.com', 'ashishg7555@gmail.com'])->update(['is_admin' => true]);

        // Also create a standard demo login admin@digitalbuilders.in
        $demoAdmin = User::firstOrCreate(
            ['email' => 'admin@digitalbuilders.in'],
            [
                'name'              => 'Lead Architect',
                'password'          => Hash::make('password'),
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Scenario 1: Garg Enterprises (Industrial Manufacturer, Ludhiana)
        $org1 = Organization::firstOrCreate(
            ['name' => 'Garg Packaging Mills'],
            [
                'domain'       => 'gargpackaging.com',
                'industry'     => 'manufacturer',
                'company_size' => '50-200 employees',
                'city'         => 'Ludhiana',
                'state'        => 'Punjab',
                'country'      => 'IN',
                'phone'        => '+91 98765 43210',
                'gst_number'   => '03AAAAA1234A1Z5',
                'notes'        => 'Heavy industrial packaging supplier. Needs to replace paper slips with Android B2B order app.',
            ]
        );

        $lead1 = Lead::firstOrCreate(
            ['phone' => '+91 98765 43210'],
            [
                'organization_id'   => $org1->id,
                'name'              => 'Rajesh Garg',
                'company'           => 'Garg Packaging Mills',
                'role_title'        => 'Managing Director',
                'email'             => 'rajesh@gargpackaging.com',
                'project_type'      => 'Custom ERP & B2B Order App',
                'segment'           => 'manufacturer',
                'source'            => 'local_network',
                'status'            => 'in_progress',
                'stage'             => 'proposal_sent',
                'score'             => 88,
                'touchpoint_count'  => 3,
                'last_contact_date' => now()->subDays(1),
                'next_action_date'  => now()->addHours(6),
                'next_action_note'  => 'Follow up on sent ₹3,79,000 Scope Proposal & Walkthrough demo',
                'description'       => 'Orders taken on handwritten slips and WhatsApp audio notes. Dispatches have a 14% error rate. Need digital order entry.',
            ]
        );

        $deal1 = Deal::firstOrCreate(
            ['title' => 'Garg Enterprises — B2B Order App & Dispatch ERP'],
            [
                'lead_id'             => $lead1->id,
                'organization_id'     => $org1->id,
                'amount'              => 379000,
                'currency'            => 'INR',
                'stage'               => 'proposal_sent',
                'probability'         => 75,
                'pricing_tier'        => 'growth',
                'expected_close_date' => now()->addDays(10),
                'scope_summary'       => 'Offline-first B2B order taking app with live Tally sync and 1-tap WhatsApp GST invoice generation.',
            ]
        );

        Activity::create([
            'lead_id'           => $lead1->id,
            'deal_id'           => $deal1->id,
            'user_id'           => $admin->id,
            'type'              => 'whatsapp',
            'subject'           => 'Touch 1: WhatsApp Intro & Ludhiana Dispatch Hook',
            'description'       => 'Shared hook regarding 0% dispatch errors at Garg Enterprises.',
            'touchpoint_number' => 1,
            'completed_at'      => now()->subDays(5),
        ]);

        Activity::create([
            'lead_id'           => $lead1->id,
            'deal_id'           => $deal1->id,
            'user_id'           => $admin->id,
            'type'              => 'meeting',
            'subject'           => 'Discovery Call Completed: Factory Floor Workflow',
            'description'       => 'Met at factory office. Identified 4 main bottlenecks: paper slips, manual tally entry, lack of dealer credit ledger.',
            'touchpoint_number' => 2,
            'completed_at'      => now()->subDays(2),
        ]);

        // 3. Scenario 2: GutTalks Clinic & Telehealth (Clinic / Healthcare)
        $org2 = Organization::firstOrCreate(
            ['name' => 'GutTalks Integrative Health Clinic'],
            [
                'domain'       => 'guttalks.in',
                'industry'     => 'clinic',
                'company_size' => '10-25 clinicians',
                'city'         => 'Bangalore',
                'state'        => 'Karnataka',
                'country'      => 'IN',
                'phone'        => '+91 99887 76655',
            ]
        );

        $lead2 = Lead::firstOrCreate(
            ['phone' => '+91 99887 76655'],
            [
                'organization_id'   => $org2->id,
                'name'              => 'Dr. Shreya Verma',
                'company'           => 'GutTalks Clinic',
                'role_title'        => 'Medical Director',
                'email'             => 'dr.shreya@guttalks.in',
                'project_type'      => 'Telehealth Portal & Microbiome Kit Tracker',
                'segment'           => 'clinic',
                'source'            => 'website_estimator',
                'status'            => 'in_progress',
                'stage'             => 'qualified',
                'score'             => 78,
                'touchpoint_count'  => 2,
                'last_contact_date' => now()->subDays(2),
                'next_action_date'  => now()->subHours(3), // OVERDUE for action queue demo
                'next_action_note'  => 'Touch 3: Send Telehealth Case Study & Architecture Guide',
                'description'       => 'High patient no-shows (28%). Reception overwhelmed with calls. Needs automated WhatsApp reminders & slot booking.',
            ]
        );

        $deal2 = Deal::firstOrCreate(
            ['title' => 'GutTalks — Patient Telehealth & WhatsApp Check-in Portal'],
            [
                'lead_id'             => $lead2->id,
                'organization_id'     => $org2->id,
                'amount'              => 149000,
                'currency'            => 'INR',
                'stage'               => 'qualified',
                'probability'         => 40,
                'pricing_tier'        => 'starter',
                'expected_close_date' => now()->addDays(14),
                'scope_summary'       => 'Frictionless patient booking with automated WhatsApp confirmation and test kit status tracking.',
            ]
        );

        // 4. Scenario 3: International Founder USD (US SaaS)
        $org3 = Organization::firstOrCreate(
            ['name' => 'FitPulse AI Labs LLC'],
            [
                'domain'       => 'fitpulse.ai',
                'industry'     => 'international',
                'company_size' => 'Seed Stage (YC W26)',
                'city'         => 'San Francisco',
                'state'        => 'CA',
                'country'      => 'US',
                'phone'        => '+1 415 890 1234',
            ]
        );

        $lead3 = Lead::firstOrCreate(
            ['phone' => '+1 415 890 1234'],
            [
                'organization_id'   => $org3->id,
                'name'              => 'Marcus Vance',
                'company'           => 'FitPulse AI Labs',
                'role_title'        => 'Co-Founder & CEO',
                'email'             => 'marcus@fitpulse.ai',
                'project_type'      => 'SaaS Platform & AI Engine',
                'segment'           => 'international',
                'source'            => 'linkedin',
                'status'            => 'in_progress',
                'stage'             => 'negotiation',
                'score'             => 95,
                'touchpoint_count'  => 4,
                'last_contact_date' => now()->subHours(18),
                'next_action_date'  => now()->addDays(1),
                'next_action_note'  => 'Review revised master services agreement & 40% Stripe kickoff link',
                'description'       => 'Burned by previous offshore agency with spaghetti code. Needs Staff Architect to rebuild real-time telemetry and API.',
            ]
        );

        $deal3 = Deal::firstOrCreate(
            ['title' => 'FitPulse — High-Throughput Wellness SaaS Monolith'],
            [
                'lead_id'             => $lead3->id,
                'organization_id'     => $org3->id,
                'amount'              => 11000,
                'currency'            => 'USD',
                'stage'               => 'negotiation',
                'probability'         => 85,
                'pricing_tier'        => 'enterprise',
                'expected_close_date' => now()->addDays(5),
                'scope_summary'       => 'Sub-100ms response time modular Laravel 13 & Vue 3 web app with PostgreSQL and Redis cluster.',
            ]
        );

        // 5. Scenario 4: New Inbound Lead (Needs Touch 1 today)
        $lead4 = Lead::firstOrCreate(
            ['phone' => '+91 98141 55443'],
            [
                'name'              => 'Vikram Singhania',
                'company'           => 'Singhania Hosiery & Garments',
                'role_title'        => 'Director',
                'email'             => 'vikram@singhaniatex.com',
                'project_type'      => 'B2B Wholesale Ordering System',
                'segment'           => 'retail',
                'source'            => 'website_contact',
                'status'            => 'new',
                'stage'             => 'new',
                'score'             => 72,
                'touchpoint_count'  => 0,
                'next_action_date'  => now(),
                'next_action_note'  => 'Send Touch 1 on WhatsApp: Ludhiana dispatch proof',
                'description'       => 'Submitted quote request on website for B2B portal with UPI integration.',
            ]
        );

        Deal::firstOrCreate(
            ['title' => 'Singhania Garments — Wholesale Catalog & UPI App'],
            [
                'lead_id'             => $lead4->id,
                'amount'              => 199000,
                'currency'            => 'INR',
                'stage'               => 'new',
                'probability'         => 15,
                'expected_close_date' => now()->addDays(25),
            ]
        );

        // 6. Scenario 5: Closed Won Deal with Payment (Proof of revenue)
        $org5 = Organization::firstOrCreate(
            ['name' => 'Ludhiana Precision Tools'],
            [
                'domain'       => 'ludhianatools.in',
                'industry'     => 'manufacturer',
                'city'         => 'Ludhiana',
                'state'        => 'Punjab',
                'country'      => 'IN',
                'phone'        => '+91 98150 99881',
            ]
        );

        $lead5 = Lead::firstOrCreate(
            ['phone' => '+91 98150 99881'],
            [
                'organization_id'   => $org5->id,
                'name'              => 'Sunil Chopra',
                'company'           => 'Ludhiana Precision Tools',
                'email'             => 'sunil@ludhianatools.in',
                'project_type'      => 'Dealer Inventory & Ledger Portal',
                'segment'           => 'manufacturer',
                'source'            => 'referral',
                'status'            => 'converted',
                'stage'             => 'closed_won',
                'score'             => 100,
                'touchpoint_count'  => 4,
                'last_contact_date' => now()->subDays(3),
            ]
        );

        $deal5 = Deal::firstOrCreate(
            ['title' => 'Ludhiana Tools — Custom Dealer ERP & Sync'],
            [
                'lead_id'             => $lead5->id,
                'organization_id'     => $org5->id,
                'amount'              => 299000,
                'currency'            => 'INR',
                'stage'               => 'closed_won',
                'probability'         => 100,
                'payment_status'      => 'paid',
                'amount_paid'         => 299000,
                'closed_at'           => now()->subDays(2),
                'expected_close_date' => now()->subDays(2),
            ]
        );

        Payment::firstOrCreate(
            ['transaction_utr' => 'HDFC000123987654'],
            [
                'deal_id'         => $deal5->id,
                'lead_id'         => $lead5->id,
                'gateway'         => 'bank_wire',
                'amount'          => 299000,
                'currency'        => 'INR',
                'status'          => 'paid',
                'payment_method'  => 'wire',
                'transaction_utr' => 'HDFC000123987654',
                'notes'           => '100% advance project fee credited via RTGS.',
                'paid_at'         => now()->subDays(2),
            ]
        );
    }
}
