<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\View;

class ServiceController extends Controller
{
    private function renderWithMeta(string $component, array $meta): Response
    {
        View::share('pageMeta', $meta);
        return Inertia::render($component, ['meta' => $meta]);
    }

    public function webApp(): Response
    {
        return $this->renderWithMeta('Services/WebApp', [
            'title' => 'Custom Web Application Development — Laravel 13, Vue 3 & Inertia.js | DigitalBuilders',
            'description' => 'Architecting blazing-fast, high-concurrency custom web applications and client portals with sub-100ms response times, modular monoliths, and zero legacy debt.',
            'image' => asset('images/portfolio/habuilt.jpg'),
            'url' => url('/services/web-applications'),
            'type' => 'website',
        ]);
    }

    public function mobileApp(): Response
    {
        return $this->renderWithMeta('Services/MobileApp', [
            'title' => 'Native & Cross-Platform Mobile App Development — iOS & Android | DigitalBuilders',
            'description' => 'Engineering 60 FPS fluid mobile applications in Flutter, React Native, and Kotlin Native with offline SQLite synchronization, biometric security, and native device integrations.',
            'image' => asset('images/portfolio/sportsclub.jpg'),
            'url' => url('/services/mobile-apps'),
            'type' => 'website',
        ]);
    }

    public function aiSolutions(): Response
    {
        return $this->renderWithMeta('Services/AiSolutions', [
            'title' => 'Autonomous AI Agents, RAG Pipelines & Voice Automation | DigitalBuilders',
            'description' => 'Deploying enterprise-grade conversational AI agents, WhatsApp automated qualification workflows, and zero-hallucination pgvector vector search embedded into your business stack.',
            'image' => asset('images/portfolio/zoeticoach.jpg'),
            'url' => url('/services/ai-solutions'),
            'type' => 'website',
        ]);
    }

    public function erpCrm(): Response
    {
        return $this->renderWithMeta('Services/ErpCrm', [
            'title' => 'Enterprise ERP, Custom CRM & Multi-Tenant Business Portals | DigitalBuilders',
            'description' => 'Architecting centralized business ERP systems, daily compliance ledgers, inventory reconciliation, GST invoicing, and custom multi-branch operational software.',
            'image' => asset('images/portfolio/gargenterprises.jpg'),
            'url' => url('/services/erp-crm'),
            'type' => 'website',
        ]);
    }

    public function saasPlatforms(): Response
    {
        return $this->renderWithMeta('Services/SaasPlatforms', [
            'title' => 'High-Scale Multi-Tenant SaaS Platform Engineering | DigitalBuilders',
            'description' => 'Engineering high-throughput SaaS architectures with automated recurring Stripe & Razorpay billing, webhook queues, tenant isolation, and granular RBAC security.',
            'image' => asset('images/portfolio/dhandadiary.jpg'),
            'url' => url('/services/saas-platforms'),
            'type' => 'website',
        ]);
    }

    public function growth(): Response
    {
        return $this->renderWithMeta('Services/Growth', [
            'title' => 'Strategic Growth, Technical SEO & Customer Acquisition | DigitalBuilders',
            'description' => 'Great software is step one. Getting customers is step two. We help your website, app, or store get seen, get enquiries, and convert paying customers with technical SEO, AI content engines, and WhatsApp lead nurture automation.',
            'image' => asset('images/portfolio/habuilt.jpg'),
            'url' => url('/services/growth'),
            'type' => 'website',
        ]);
    }

    public function pricing(): Response
    {
        return $this->renderWithMeta('Pricing', [
            'title' => 'Transparent Milestone-Based Pricing & Enterprise Care SLAs | DigitalBuilders',
            'description' => 'Fixed-scope, transparent milestone pricing for enterprise web applications, mobile platforms, and AI systems. Explore Launch (₹1.49L), Scale (₹3.49L), and Enterprise tiers with 30-day warranty.',
            'image' => asset('images/portfolio/habuilt.jpg'),
            'url' => url('/pricing'),
            'type' => 'website',
        ]);
    }

    public function book(): Response
    {
        return $this->renderWithMeta('Book', [
            'title' => 'Book a Free Architecture Consultation | DigitalBuilders',
            'description' => 'Schedule a 30-minute 1-on-1 technical discovery consultation with Lead Architect Ashish Gupta. Zero-obligation system bottleneck audit, stack evaluation, and fixed roadmap.',
            'image' => asset('images/portfolio/habuilt.jpg'),
            'url' => url('/book'),
            'type' => 'website',
        ]);
    }
}

