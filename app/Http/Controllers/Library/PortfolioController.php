<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\View;

class PortfolioController extends Controller
{
    private function renderWithMeta(string $component, array $meta): Response
    {
        View::share('pageMeta', $meta);
        return Inertia::render($component, ['meta' => $meta]);
    }

    public function habuilt(): Response
    {
        return $this->renderWithMeta('Portfolio/Habuilt', [
            'title' => 'Habuilt Case Study — 50 Habits, 4 Progression Tiers & 99.99% Uptime | DigitalBuilders',
            'description' => "How DigitalBuilders stabilized Habuilt's high-traffic wellness platform, reducing server latency by 70% with 26-week progression tiers and mobile deep linking.",
            'image' => asset('images/portfolio/habuilt.jpg'),
            'url' => url('/portfolio/habuilt'),
            'type' => 'article',
        ]);
    }

    public function dhandadiary(): Response
    {
        return $this->renderWithMeta('Portfolio/DhandaDiary', [
            'title' => 'Dhanda Diary Case Study — Daily Compliance & Execution Cockpit SaaS | DigitalBuilders',
            'description' => 'How DigitalBuilders engineered a real-time execution cockpit with Daily Compliance Reports (DCR), ApexCharts KPI telemetry, and <50ms sync latency.',
            'image' => asset('images/portfolio/dhandadiary.jpg'),
            'url' => url('/portfolio/dhandadiary'),
            'type' => 'article',
        ]);
    }

    public function zoeticoach(): Response
    {
        return $this->renderWithMeta('Portfolio/ZoetiCoach', [
            'title' => 'ZoetiCoach AI Case Study — WhatsApp-Native Coaching ERP & RAG Agent | DigitalBuilders',
            'description' => 'How DigitalBuilders engineered a WhatsApp-first B2B2C accountability SaaS with OpenAI RAG habit verification, reducing client drop-off by 65%.',
            'image' => asset('images/portfolio/zoeticoach.jpg'),
            'url' => url('/portfolio/zoeticoach'),
            'type' => 'article',
        ]);
    }

    public function guttalks(): Response
    {
        return $this->renderWithMeta('Portfolio/GutTalks', [
            'title' => 'GutTalks Case Study — Telehealth Portal, Root Rx & GutMap Complete™ | DigitalBuilders',
            'description' => 'How DigitalBuilders built an evidence-based gut health telehealth platform with ₹499 Root Rx booking and at-home microbiome sequencing for 10k+ clients.',
            'image' => asset('images/portfolio/guttalks.jpg'),
            'url' => url('/portfolio/guttalks'),
            'type' => 'article',
        ]);
    }

    public function myastrova(): Response
    {
        return $this->renderWithMeta('Portfolio/MyAstrova', [
            'title' => 'MyAstrova Case Study — Vedic AstroTech Engine & Energized Crystal Mall | DigitalBuilders',
            'description' => 'How DigitalBuilders engineered a sub-200ms mathematical ephemeris Kundli engine, live astrologer call/chat routing, and crystal remedy e-commerce.',
            'image' => asset('images/portfolio/myastrova.jpg'),
            'url' => url('/portfolio/myastrova'),
            'type' => 'article',
        ]);
    }

    public function gaushala(): Response
    {
        return $this->renderWithMeta('Portfolio/Gaushala', [
            'title' => 'Krishan Balram Gaushala Case Study — GauSeva Connect & WhatsApp API | DigitalBuilders',
            'description' => 'How DigitalBuilders built GauSeva Connect: automated daily WhatsApp birthday blessings via Meta Cloud API and instant 80G tax exemption PDF receipts.',
            'image' => asset('images/portfolio/gaushala.jpg'),
            'url' => url('/portfolio/gaushala'),
            'type' => 'article',
        ]);
    }

    public function sportsClub(): Response
    {
        return $this->renderWithMeta('Portfolio/SportsClub', [
            'title' => 'SportsEntertainmentClub Case Study — Court Booking & Digital QR Pass App | DigitalBuilders',
            'description' => 'How DigitalBuilders engineered a 60 FPS mobile app with atomic slot reservation concurrency locks, digital QR member passes, and tournament brackets.',
            'image' => asset('images/portfolio/sportsclub.jpg'),
            'url' => url('/portfolio/sports-club'),
            'type' => 'article',
        ]);
    }

    public function gargEnterprises(): Response
    {
        return $this->renderWithMeta('Portfolio/GargEnterprises', [
            'title' => 'Garg Enterprises Case Study — B2B Wholesale Ordering & Ledger App | DigitalBuilders',
            'description' => 'How DigitalBuilders built a rugged Android B2B ordering app with offline SQLite sync, dealer credit ledger, and 1-tap GST invoice downloads for 10k+ SKUs.',
            'image' => asset('images/portfolio/gargenterprises.jpg'),
            'url' => url('/portfolio/garg-enterprises'),
            'type' => 'article',
        ]);
    }

    public function ashishgupta(): Response
    {
        return $this->renderWithMeta('Portfolio/AshishGupta', [
            'title' => 'Ashish Gupta Hub Case Study — Senior Full-Stack Architect Showcase | DigitalBuilders',
            'description' => 'High-performance engineering showcase demonstrating Domain-Driven Design (DDD), legacy modernization, and $1M+ cloud savings on the VILT stack.',
            'image' => asset('images/portfolio/ashishgupta.jpg'),
            'url' => url('/portfolio/ashishgupta'),
            'type' => 'article',
        ]);
    }

    public function ssknitwear(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('portfolio.ashishgupta', [], 301);
    }
}

