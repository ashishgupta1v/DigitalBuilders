<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    public function habuilt(): Response
    {
        return Inertia::render('Portfolio/Habuilt');
    }

    public function zoeticoach(): Response
    {
        return Inertia::render('Portfolio/ZoetiCoach');
    }

    public function ssknitwear(): Response
    {
        return Inertia::render('Portfolio/SSKnitwear');
    }
}
