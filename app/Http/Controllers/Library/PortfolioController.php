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

    public function dhandadiary(): Response
    {
        return Inertia::render('Portfolio/DhandaDiary');
    }

    public function zoeticoach(): Response
    {
        return Inertia::render('Portfolio/ZoetiCoach');
    }

    public function guttalks(): Response
    {
        return Inertia::render('Portfolio/GutTalks');
    }

    public function myastrova(): Response
    {
        return Inertia::render('Portfolio/MyAstrova');
    }

    public function gaushala(): Response
    {
        return Inertia::render('Portfolio/Gaushala');
    }

    public function sportsClub(): Response
    {
        return Inertia::render('Portfolio/SportsClub');
    }

    public function gargEnterprises(): Response
    {
        return Inertia::render('Portfolio/GargEnterprises');
    }

    public function ssknitwear(): Response
    {
        return Inertia::render('Portfolio/SSKnitwear');
    }
}
