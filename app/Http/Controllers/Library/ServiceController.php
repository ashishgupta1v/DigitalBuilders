<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function webApp(): Response
    {
        return Inertia::render('Services/WebApp');
    }

    public function mobileApp(): Response
    {
        return Inertia::render('Services/MobileApp');
    }

    public function aiSolutions(): Response
    {
        return Inertia::render('Services/AiSolutions');
    }

    public function erpCrm(): Response
    {
        return Inertia::render('Services/ErpCrm');
    }

    public function saasPlatforms(): Response
    {
        return Inertia::render('Services/SaasPlatforms');
    }
}
