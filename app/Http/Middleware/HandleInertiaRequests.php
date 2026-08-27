<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $countryHeader = (string) ($request->header('x-vercel-ip-country') 
            ?: $request->header('cf-ipcountry') 
            ?: $request->header('x-country-code') 
            ?: '');
        
        $countryCode = strtoupper(trim($countryHeader));
        $gulfCountries = ['AE', 'SA', 'QA', 'KW', 'OM', 'BH'];

        $detectedRegion = null;
        if ($countryCode === 'IN') {
            $detectedRegion = 'INR';
        } elseif (in_array($countryCode, $gulfCountries, true)) {
            $detectedRegion = 'GULF';
        } elseif (!empty($countryCode)) {
            $detectedRegion = 'USD';
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'geo' => [
                'country' => $countryCode ?: null,
                'region' => $detectedRegion,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
