<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | CRM Dynamic Executive Identity & Agency Metadata
    |--------------------------------------------------------------------------
    |
    | Centralized settings for founder representation, agency branding,
    | booking calendar links, and outbound outreach signatures.
    |
    */

    'founder_name'     => env('CRM_FOUNDER_NAME', 'Ashish Gupta'),
    'founder_title'    => env('CRM_FOUNDER_TITLE', 'Principal Software Architect & Founder'),
    'founder_email'    => env('CRM_FOUNDER_EMAIL', env('MAIL_FROM_ADDRESS', 'ashish@digitalbuilders.in')),
    'founder_phone'    => env('CRM_FOUNDER_PHONE', '+91 90870 21592'),
    'company_name'     => env('CRM_COMPANY_NAME', config('app.name', 'DigitalBuilders')),
    'website_url'      => env('CRM_WEBSITE_URL', config('app.url', 'https://www.digitalbuilders.in')),
    'booking_url'      => env('CRM_BOOKING_URL', 'https://www.digitalbuilders.in/book'),
    'estimator_url'    => env('CRM_ESTIMATOR_URL', 'https://www.digitalbuilders.in/estimator'),
    'default_currency' => env('CRM_DEFAULT_CURRENCY', 'USD'),
    
    /*
    |--------------------------------------------------------------------------
    | Internal Founder Email Exclusions
    |--------------------------------------------------------------------------
    |
    | Comma-separated or array list of internal staff/founder emails to exclude
    | from public customer lead queues and automated outreach cadences.
    |
    */
    'founder_emails'   => array_values(array_filter(array_map('trim', explode(',', (string) env('CRM_FOUNDER_EMAILS', ''))))),
];
