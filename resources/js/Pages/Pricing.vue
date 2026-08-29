<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { detectUserRegion, REGIONS, type RegionMode, saveUserRegion } from '@/utils/geo';
import CookieConsent from '@/Components/CookieConsent.vue';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { trackBrochureDownload, trackWhatsAppClick, trackPricingRegionViewed, trackTierSelected } from '@/utils/analytics';

const page = usePage();
const serverGeoRegion = computed(() => (page.props as any).geo?.region as string | undefined);

const activeRegion = ref<RegionMode>('INR');
const isAutoDetected = ref(true);

function openCookieSettings() {
    window.dispatchEvent(new CustomEvent('db:open-cookie-settings'));
}

onMounted(() => {
    const detected = detectUserRegion(serverGeoRegion.value);
    activeRegion.value = detected;
    trackPricingRegionViewed(detected);
});

function switchRegion(region: RegionMode) {
    activeRegion.value = region;
    isAutoDetected.value = false;
    saveUserRegion(region);
    trackPricingRegionViewed(region);
}

const currentRegionInfo = computed(() => REGIONS[activeRegion.value]);

interface ServiceTier {
    name: string;
    badge?: string;
    popular?: boolean;
    priceInr: string;
    mrpInr?: string;
    priceGulf: string;
    priceUsd: string;
    timeline: string;
    deliverables: string[];
    idealFor: string;
}

interface ArchitecturalService {
    id: string;
    title: string;
    category: string;
    description: string;
    icon: string;
    tiers: ServiceTier[];
}

const SERVICES: ArchitecturalService[] = [
    {
        id: 'digital_presence',
        title: 'Digital Presence & Landing Architecture',
        category: 'Brand & Funnel Architecture',
        description: 'Ultra-fast, high-converting digital storefronts, corporate landing hubs, and interactive lead funnels.',
        icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
        tiers: [
            {
                name: 'Launch (Starter)',
                priceInr: '₹19,999',
                mrpInr: '₹29,999',
                priceGulf: '$450',
                priceUsd: '$600',
                timeline: '5–7 Days',
                idealFor: 'High-impact landing page, lead capture & brand positioning',
                deliverables: [
                    'High-Conversion Single Page / Landing Page',
                    'Interactive Hero & Mobile-First Responsive Design',
                    'Direct WhatsApp & Lead Capture Form Integration',
                    'Local SEO & Structured Metadata Schema',
                    'Sub-0.5s PageSpeed Optimization',
                ],
            },
            {
                name: 'Growth (Business)',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹44,999',
                mrpInr: '₹59,999',
                priceGulf: '$950',
                priceUsd: '$1,300',
                timeline: '2 Weeks',
                idealFor: 'Multi-page corporate authority site with CMS & dynamic catalog',
                deliverables: [
                    'Up to 5 Custom Engineered Pages',
                    'Filament / Admin CMS for In-House Content Updates',
                    'Interactive Product/Service Matrix',
                    'Automated Email & WhatsApp Inquiry Triggers',
                    'Advanced Analytics & Heatmap Setup',
                    '30-Day Post-Launch Support',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: 'On Request',
                priceGulf: 'Custom',
                priceUsd: 'Custom',
                timeline: '3–4 Weeks',
                idealFor: 'Multi-brand conglomerates with internationalization needs',
                deliverables: [
                    'Full Multi-Brand Corporate Architecture',
                    'Headless CMS & Custom Micro-Animations',
                    'Multi-Language (i18n) & Regional Routing',
                    'Enterprise Security & DDoS Protection',
                    'Custom CRM & ERP Data Integrations',
                ],
            },
        ],
    },
    {
        id: 'web_app',
        title: 'Enterprise Web Application',
        category: 'Web Architecture',
        description: 'Scalable SPA/SSR portals, administrative hubs, internal tooling, and customer-facing workflows.',
        icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        tiers: [
            {
                name: 'Launch',
                priceInr: '₹79,000',
                mrpInr: '₹99,000',
                priceGulf: '$2,500',
                priceUsd: '$3,500',
                timeline: '2–3 Weeks',
                idealFor: 'Validating high-speed workflows & MVP portals',
                deliverables: [
                    'Core Reactive UI (Vue 3 / React)',
                    'Robust Backend API & SQLite/Postgres',
                    'Standard Auth & Role Management',
                    'Mobile-Responsive Layout',
                    'Zero-Downtime Deployment Setup',
                ],
            },
            {
                name: 'Growth',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹1,49,000',
                mrpInr: '₹1,79,000',
                priceGulf: '$4,500',
                priceUsd: '$6,500',
                timeline: '4–6 Weeks',
                idealFor: 'Scaling businesses requiring automated workflows',
                deliverables: [
                    'Advanced Domain-Driven Architecture',
                    'Granular RBAC & Multi-Role Permissions',
                    'Automated Email/SMS/WhatsApp Triggers',
                    'Redis Caching & Queue Optimization',
                    'Staging + Production CI/CD Pipeline',
                    '30-Day Stabilization Warranty',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: '₹2,49,000',
                mrpInr: '₹2,99,000',
                priceGulf: '$7,900',
                priceUsd: '$11,000',
                timeline: '8–10 Weeks',
                idealFor: 'High-throughput platforms with 99.99% SLA needs',
                deliverables: [
                    'Modular Hexagonal Monolith / Microservices',
                    'Audit Logging & Compliance Security',
                    'Distributed Caching & High-Availability DB',
                    'Full Automated Test Suite (Unit + E2E)',
                    'Dedicated Load Testing & Performance Tuning',
                    '60-Day Priority Support & SLA',
                ],
            },
        ],
    },
    {
        id: 'ai_solutions',
        title: 'AI Voice & Autonomous Chat Agents',
        category: 'Applied AI Systems',
        description: 'Sub-second inbound/outbound telephony agents, private document RAG pipelines, and automated intelligence.',
        icon: 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
        tiers: [
            {
                name: 'Launch',
                priceInr: '₹99,000',
                mrpInr: '₹1,29,000',
                priceGulf: '$3,200',
                priceUsd: '$4,500',
                timeline: '2–3 Weeks',
                idealFor: 'Automating standard lead intake & FAQ chat',
                deliverables: [
                    'Custom LLM System Prompt Engineering',
                    'Web Chat Widget with Streaming Responses',
                    'Lead Extraction & CRM Syncing',
                    'Fallback Human Escalation Protocol',
                    'Basic Usage & Token Cost Analytics',
                ],
            },
            {
                name: 'Growth',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹1,79,000',
                mrpInr: '₹2,19,000',
                priceGulf: '$5,500',
                priceUsd: '$7,900',
                timeline: '4–6 Weeks',
                idealFor: 'Real-time Voice Calling & Deep Document RAG',
                deliverables: [
                    'Ultra-Low Latency Voice Agent (WebRTC / Twilio)',
                    'Vector Database RAG with PDF/Doc Chunking',
                    'Dynamic Tool Calling (DB Booking & Lookup)',
                    'Multi-Turn Memory & Semantic Guardrails',
                    'Automated Voice Call Transcription & Summaries',
                    '30-Day Post-Launch SLA Warranty',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: '₹2,99,000',
                mrpInr: '₹3,49,000',
                priceGulf: '$9,500',
                priceUsd: '$13,000',
                timeline: '7–9 Weeks',
                idealFor: 'Multi-lingual omni-channel autonomous AI workforce',
                deliverables: [
                    'Multi-Agent Collaborative Orchestration',
                    'Enterprise ERP/CRM Bi-directional Sync',
                    'Custom Fine-Tuned Domain Embeddings',
                    'Strict HIPAA / PII Data Sanitization',
                    'High-Concurrency Telephony Load Balancing',
                    'Full Source Code & Self-Hosted Deployment',
                ],
            },
        ],
    },
    {
        id: 'mobile_app',
        title: 'Mobile App (iOS & Android)',
        category: 'Cross-Platform Mobile',
        description: 'Performant cross-platform mobile apps built with React Native / Flutter with native device integration.',
        icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        tiers: [
            {
                name: 'Launch',
                priceInr: '₹1,19,000',
                mrpInr: '₹1,49,000',
                priceGulf: '$4,200',
                priceUsd: '$6,000',
                timeline: '3–4 Weeks',
                idealFor: 'MVP apps ready for Apple App Store & Google Play',
                deliverables: [
                    'Unified iOS & Android Codebase',
                    'Push Notifications (FCM / APNs)',
                    'Biometric Authentication (FaceID / TouchID)',
                    'API Integration & Offline Data Cache',
                    'Store Submission & Approval Support',
                ],
            },
            {
                name: 'Growth',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹1,99,000',
                mrpInr: '₹2,49,000',
                priceGulf: '$7,200',
                priceUsd: '$10,000',
                timeline: '5–7 Weeks',
                idealFor: 'E-commerce, logistics, and real-time user apps',
                deliverables: [
                    'Offline-First SQLite Sync Engine',
                    'In-App Payments & Subscription Billing',
                    'Real-Time Live Location / Tracking',
                    'Deep Linking & Universal Routing',
                    'Crashlytics & Performance Telemetry',
                    '30-Day App Store Guarantee',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: '₹3,29,000',
                mrpInr: '₹3,99,000',
                priceGulf: '$11,500',
                priceUsd: '$16,000',
                timeline: '8–12 Weeks',
                idealFor: 'Field-force enterprise apps with Bluetooth/Hardware',
                deliverables: [
                    'Custom Native Modules (C++ / Swift / Kotlin)',
                    'Bluetooth / Hardware Device Integration',
                    'Enterprise MDM Distribution Support',
                    'End-to-End Encrypted Local Storage',
                    'Automated Fastlane CI/CD Build Pipelines',
                    'Dedicated Production Release Manager',
                ],
            },
        ],
    },
    {
        id: 'saas',
        title: 'Multi-Tenant SaaS Platform',
        category: 'SaaS Engineering',
        description: 'Complete multi-tenant software platforms with subscription management, tenant isolation, and metered billing.',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        tiers: [
            {
                name: 'Launch',
                priceInr: '₹1,99,000',
                mrpInr: '₹2,49,000',
                priceGulf: '$6,500',
                priceUsd: '$9,000',
                timeline: '4–6 Weeks',
                idealFor: 'Early-stage founders validating micro-SaaS under ₹2L',
                deliverables: [
                    'Multi-Tenant Schema Architecture',
                    'Stripe / Razorpay Tiered Billing',
                    'Customer Self-Service Onboarding',
                    'Admin Superuser Control Panel',
                    'Transactional Email Infrastructure',
                ],
            },
            {
                name: 'Growth',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹3,19,000',
                mrpInr: '₹3,99,000',
                priceGulf: '$9,900',
                priceUsd: '$14,000',
                timeline: '7–10 Weeks',
                idealFor: 'Commercial SaaS ready for high customer volume',
                deliverables: [
                    'Metered / Usage-Based Billing Engine',
                    'Team Collaboration & Role Hierarchy',
                    'Custom Domain / White-label Mapping',
                    'Audit Trail & Event Streaming',
                    'Public API & Webhook Dispatcher',
                    '30-Day Post-Launch SLA Warranty',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: '₹4,99,000',
                mrpInr: '₹5,99,000',
                priceGulf: '$15,500',
                priceUsd: '$22,000',
                timeline: '12–16 Weeks',
                idealFor: 'B2B enterprise SaaS with SAML/SSO & compliance',
                deliverables: [
                    'Enterprise SAML / Okta / Azure AD SSO',
                    'Isolated Tenant Database Architecture',
                    'SOC2 / ISO27001 Readiness Hardening',
                    'Multi-Region Disaster Recovery Setup',
                    'White-Glove Architecture Blueprinting',
                    'Dedicated Senior Staff Engineer SLA',
                ],
            },
        ],
    },
    {
        id: 'erp_crm',
        title: 'Custom Enterprise ERP / CRM',
        category: 'Enterprise Operations',
        description: 'Tailor-made manufacturing, inventory, dealer distribution, and WhatsApp-native business operation engines.',
        icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
        tiers: [
            {
                name: 'Launch',
                priceInr: '₹2,49,000',
                mrpInr: '₹2,99,000',
                priceGulf: '$7,900',
                priceUsd: '$11,000',
                timeline: '5–7 Weeks',
                idealFor: 'Replacing unorganized spreadsheets & legacy software',
                deliverables: [
                    'Inventory & Stock Movement Engine',
                    'Order Lifecycle & Invoice Generation',
                    'Customer / Vendor Directory & Ledger',
                    'Staff Activity Logging & Permissions',
                    'Automated Daily PDF Management Reports',
                ],
            },
            {
                name: 'Growth',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹3,79,000',
                mrpInr: '₹4,49,000',
                priceGulf: '$12,000',
                priceUsd: '$16,500',
                timeline: '8–12 Weeks',
                idealFor: 'Multi-branch operations, manufacturing & dealer networks',
                deliverables: [
                    'Multi-Warehouse & Multi-Branch Sync',
                    'WhatsApp ERP Bots & Order Dispatch Alerts',
                    'GST / E-Way Bill / Tally Data Bridges',
                    'Dynamic Dealer Tiered Pricing Matrix',
                    'Barcoding / QR Tracking Workflow',
                    '30-Day Onsite/Remote Training & Warranty',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: '₹5,99,000',
                mrpInr: '₹6,99,000',
                priceGulf: '$18,500',
                priceUsd: '$26,000',
                timeline: '12–16 Weeks',
                idealFor: 'Large-scale industrial conglomerates & multi-company setups',
                deliverables: [
                    'Multi-company, multi-branch, multi-currency architecture',
                    'Offline support so billing never stops when internet does',
                    'SSO, maker-checker approvals and full audit trail',
                    'Manufacturing / job-work, BOM and costing modules',
                    'Tally, e-way bill, banking and BI tool integrations',
                    '99.99% uptime target, priority SLA and on-site training',
                ],
            },
        ],
    },
    {
        id: 'ecommerce',
        title: 'E-Commerce Store & D2C Architecture',
        category: 'Digital Commerce',
        description: 'A store built for how Indian D2C sellers actually operate — COD-friendly checkout, WhatsApp order alerts, and real-time inventory sync.',
        icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        tiers: [
            {
                name: 'Launch',
                priceInr: '₹99,000',
                mrpInr: '₹1,29,000',
                priceGulf: '$3,200',
                priceUsd: '$4,500',
                timeline: '3–4 Weeks',
                idealFor: 'Brands taking their first catalogue online',
                deliverables: [
                    'Product catalogue, variants, search and filters',
                    'Cart, checkout & payment gateway (Razorpay / Stripe)',
                    'COD support with shipping zone and rate rules',
                    'Order management dashboard with status updates',
                    'WhatsApp order confirmation & shipping alerts',
                    'Mobile-first storefront tuned for sub-second speed',
                ],
            },
            {
                name: 'Growth',
                popular: true,
                badge: '★ Most Popular',
                priceInr: '₹1,69,000',
                mrpInr: '₹1,99,000',
                priceGulf: '$5,200',
                priceUsd: '$7,500',
                timeline: '5–6 Weeks',
                idealFor: 'Stores with repeat customers and rising order volume',
                deliverables: [
                    'Multiple payment methods, wallets and UPI intents',
                    'Coupons, discounts & abandoned-cart recovery emails',
                    'Customer accounts with order history & tracking',
                    'Real-time inventory sync across catalogue & stock',
                    'Reviews, ratings & related-product recommendations',
                    'Sales & cohort dashboards for repeat-purchase tracking',
                ],
            },
            {
                name: 'Enterprise',
                priceInr: '₹2,79,000',
                mrpInr: '₹3,49,000',
                priceGulf: '$8,900',
                priceUsd: '$12,500',
                timeline: '8–10 Weeks',
                idealFor: 'Multi-warehouse brands selling across channels',
                deliverables: [
                    'Multi-warehouse inventory with location-wise stock',
                    'Marketplace feeds for Amazon, Flipkart and Meta Shops',
                    'Loyalty points, referral rewards & subscription orders',
                    'Multi-currency checkout for export/NRI customers',
                    'ERP/accounting integration for stock & GST reconciliation',
                    '99.99% uptime target with priority SLA at peak sale days',
                ],
            },
        ],
    },
];

interface AddonModule {
    title: string;
    description: string;
    priceInr: string;
    mrpInr?: string;
    priceGulf: string;
    priceUsd: string;
    timeline: string;
}

const ADDON_MODULES: AddonModule[] = [
    {
        title: 'Payments & Subscriptions',
        description: 'Razorpay, Stripe, Cashfree, automated GST invoicing, webhooks & recurring card charges.',
        priceInr: '+₹19,000',
        mrpInr: '+₹25,000',
        priceGulf: '+$500',
        priceUsd: '+$700',
        timeline: '+3 Days',
    },
    {
        title: 'Real-time WebSockets & Push',
        description: 'Instant live updates, driver/courier live tracking, live order status, and interactive chat.',
        priceInr: '+₹25,000',
        mrpInr: '+₹30,000',
        priceGulf: '+$600',
        priceUsd: '+$800',
        timeline: '+4 Days',
    },
    {
        title: 'AI Copilot / LLM RAG Pipeline',
        description: 'Custom OpenAI/Claude embeddings, private document vector querying, and automated summaries.',
        priceInr: '+₹35,000',
        mrpInr: '+₹40,000',
        priceGulf: '+$900',
        priceUsd: '+$1,200',
        timeline: '+5 Days',
    },
    {
        title: 'Multi-Language (i18n & RTL)',
        description: 'Multi-locale localization (Hindi, Punjabi, Arabic RTL, English switcher) with zero layout shifts.',
        priceInr: '+₹15,000',
        mrpInr: '+₹20,000',
        priceGulf: '+$400',
        priceUsd: '+$500',
        timeline: '+2 Days',
    },
    {
        title: 'WhatsApp Catalog Setup',
        description: 'Green-tick ready product catalogue inside WhatsApp Business, linked to your inventory. (Up to ₹15,000 for large catalogues).',
        priceInr: '+₹12,000',
        mrpInr: '+₹15,000',
        priceGulf: '+$300',
        priceUsd: '+$400',
        timeline: '+2 Days',
    },
    {
        title: 'Data Migration & Sanitization',
        description: 'Move records cleanly off Tally, Excel or paper registers into your new system. (Up to ₹40,000 for complex legacy datasets).',
        priceInr: '+₹15,000',
        mrpInr: '+₹25,000',
        priceGulf: '+$400',
        priceUsd: '+$500',
        timeline: '+3 Days',
    },
];

function getTierPrice(tier: ServiceTier): string {
    if (activeRegion.value === 'INR') return tier.priceInr;
    if (activeRegion.value === 'GULF') return tier.priceGulf;
    return tier.priceUsd;
}

function getAddonPrice(addon: AddonModule): string {
    if (activeRegion.value === 'INR') return addon.priceInr;
    if (activeRegion.value === 'GULF') return addon.priceGulf;
    return addon.priceUsd;
}
</script>

<template>
    <Head>
        <title>Enterprise Software & AI Architecture Pricing (2026 Price Book) — DigitalBuilders</title>
        <meta name="description" content="Transparent, decoupled pricing for enterprise web applications, AI voice agents, cross-platform mobile apps, SaaS, and custom ERP systems. View INR and USD price books." />
        <link rel="canonical" href="https://www.digitalbuilders.in/pricing" />
        <meta property="og:title" content="Transparent Milestone-Based Pricing & Enterprise Care SLAs | DigitalBuilders" />
        <meta property="og:description" content="Fixed-scope, transparent milestone pricing for enterprise web applications, mobile platforms, and AI systems. Explore Launch, Scale, and Enterprise tiers with 30-day warranty." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/pricing" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Enterprise Software & AI Architecture Pricing (2026 Price Book) | DigitalBuilders" />
        <meta name="twitter:description" content="Fixed-scope, transparent milestone pricing for enterprise web applications, mobile platforms, and AI systems." />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <!-- Accessible Skip to Main Content Link (WCAG 2.4.1) -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-5 focus:py-2.5 focus:bg-primary focus:text-white focus:rounded-full focus:shadow-2xl focus:font-bold focus:outline-none focus:ring-4 focus:ring-sky-400"
        >
            Skip to main content
        </a>

        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header Navigation -->
        <header class="sticky top-0 z-50 border-b border-border bg-background/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <nav aria-label="Primary navigation" class="flex items-center gap-3 sm:gap-5">
                    <Link href="/#services" class="hidden text-xs font-semibold text-muted-foreground hover:text-foreground md:inline-flex items-center min-h-[44px] px-2 transition-colors">Services</Link>
                    <Link href="/#portfolio" class="hidden text-xs font-semibold text-muted-foreground hover:text-foreground md:inline-flex items-center min-h-[44px] px-2 transition-colors">Portfolio</Link>
                    <Link href="/estimator" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline min-h-[44px] inline-flex items-center px-2">Estimator</Link>
                    <Link href="/book" class="btn-primary inline-flex items-center justify-center rounded-full px-4 py-2 min-h-[44px] text-xs font-bold text-white shadow transition hover:scale-105">
                        Book Discovery
                    </Link>
                </nav>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <!-- Hero Header -->
            <div class="text-center max-w-3xl mx-auto">
                <span class="db-chip">Decoupled Regional Price Books · FY 2026-27</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl leading-tight">
                    Transparent Engineering Architecture Pricing
                </h1>
                <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
                    Zero boilerplate templates. Zero currency exchange markups. Fixed written scope, milestone releases, and direct collaboration with staff engineers.
                </p>

                <!-- Regional Switcher Bar -->
                <div class="mt-8 inline-flex flex-col items-center">
                    <div class="p-1.5 rounded-full bg-secondary/80 border border-border shadow-lg flex items-center gap-1.5 max-w-full overflow-x-auto">
                        <button
                            v-for="region in Object.values(REGIONS)"
                            :key="region.id"
                            @click="switchRegion(region.id)"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 min-h-[44px] rounded-full text-xs font-bold transition-all cursor-pointer whitespace-nowrap',
                                activeRegion === region.id
                                    ? 'bg-gradient-to-r from-sky-500 to-indigo-600 text-white shadow-md scale-[1.02]'
                                    : 'text-slate-800 dark:text-slate-200 font-bold hover:bg-accent/80 hover:text-foreground',
                            ]"
                        >
                            <span class="text-sm">{{ region.flag }}</span>
                            <span>{{ region.shortLabel }}</span>
                            <span class="rounded bg-black/20 dark:bg-white/10 px-1.5 py-0.5 text-[10px] font-mono">{{ region.currencyCode }}</span>
                        </button>
                    </div>

                    <!-- Region Context Subtitle -->
                    <div class="mt-3 flex items-center justify-center gap-2 text-xs text-muted-foreground">
                        <span v-if="isAutoDetected" class="inline-flex items-center gap-1 text-sky-700 dark:text-sky-400 font-medium">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Auto-localized for {{ currentRegionInfo.label }}
                        </span>
                        <span v-else class="text-muted-foreground font-medium">
                            Displaying: {{ currentRegionInfo.label }}
                        </span>
                        <span>•</span>
                        <span class="text-muted-foreground">{{ currentRegionInfo.taxNote }}</span>
                    </div>
                </div>
            </div>

            <!-- Value Anchor Banner -->
            <div class="mt-10 mx-auto max-w-4xl rounded-2xl border border-sky-500/30 bg-sky-500/10 p-5 backdrop-blur-md text-center">
                <p class="text-sm font-semibold text-sky-800 dark:text-sky-200">
                    💡 <span class="font-bold text-foreground">{{ activeRegion === 'INR' ? '"Sasta roye baar-baar, achha roye ek baar."' : 'Silicon Valley Engineering Quality without Agency Overhead' }}</span>
                    — Every tier is engineered cleanly with domain-driven code, 100% intellectual property ownership, and a 30-day post-launch warranty.
                </p>
            </div>

            <!-- Rate Card Quick Download / Print Bar -->
            <div class="mt-6 mx-auto max-w-4xl flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-card p-4 backdrop-blur-md shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-sky-500/10 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-card-foreground">Need an offline printable rate card or proposal brochure?</p>
                        <p class="text-[11px] text-muted-foreground">Download our official 2026 architectural specification brochure in HTML/PDF format.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="/downloads/digitalbuilders-pricing-india-inr.pdf"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="trackBrochureDownload('inr')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-sky-500/40 bg-sky-500/10 px-3.5 py-2 text-xs font-bold text-sky-700 dark:text-sky-300 hover:bg-sky-500/20 transition-all"
                    >
                        <span>🇮🇳 India Rate Card (PDF)</span>
                    </a>
                    <a
                        href="/downloads/digitalbuilders-pricing-international-usd.pdf"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="trackBrochureDownload('usd')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-500/40 bg-indigo-500/10 px-3.5 py-2 text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-500/20 transition-all"
                    >
                        <span>🌐 International Rate Card (PDF)</span>
                    </a>
                </div>
            </div>

            <!-- Core Architectural Services Section -->
            <div class="mt-16 space-y-16">
                <div v-for="service in SERVICES" :key="service.id" class="db-bento-card p-6 sm:p-8 shadow-xl">
                    <!-- Service Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-border pb-6">
                        <div class="flex items-start gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-sky-500/10 text-sky-700 dark:text-sky-400 flex items-center justify-center shrink-0">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="service.icon" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs uppercase tracking-widest font-bold text-sky-700 dark:text-sky-400">{{ service.category }}</span>
                                <h2 class="text-2xl font-black text-card-foreground sm:text-3xl">{{ service.title }}</h2>
                                <p class="mt-1 text-sm text-muted-foreground">{{ service.description }}</p>
                            </div>
                        </div>
                        <Link
                            :href="`/estimator?type=${service.id}&region=${activeRegion.toLowerCase()}`"
                            class="inline-flex items-center gap-1.5 self-start md:self-auto rounded-full border border-sky-500/30 bg-sky-500/10 px-4 py-2 text-xs font-bold text-sky-700 dark:text-sky-400 hover:bg-sky-500/20 transition-colors"
                        >
                            <span>Customize in Estimator</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>

                    <!-- 3-Tier Grid -->
                    <div class="mt-8 grid gap-6 md:grid-cols-3">
                        <div
                            v-for="tier in service.tiers"
                            :key="tier.name"
                            :class="[
                                'relative flex flex-col justify-between rounded-2xl p-6 transition-all duration-200',
                                tier.popular
                                    ? 'border-2 border-sky-500 bg-sky-50/60 dark:bg-sky-950/20 shadow-xl shadow-sky-500/10 scale-[1.02]'
                                    : 'border border-border bg-card/60 dark:bg-card/40 hover:border-sky-500/40 shadow-sm',
                            ]"
                        >
                            <!-- Badge -->
                            <div v-if="tier.badge" class="absolute -top-3 right-5 rounded-full bg-gradient-to-r from-sky-500 to-indigo-600 px-3 py-0.5 text-[11px] font-extrabold text-white shadow-md">
                                {{ tier.badge }}
                            </div>

                            <div>
                                <div class="flex items-baseline justify-between">
                                    <h3 class="text-lg font-bold text-card-foreground">{{ tier.name }}</h3>
                                    <span class="text-xs font-mono text-muted-foreground">{{ tier.timeline }}</span>
                                </div>

                                <div class="mt-4 flex flex-wrap items-baseline gap-2">
                                    <span v-if="activeRegion === 'INR' && tier.mrpInr" class="font-mono text-base text-muted-foreground line-through opacity-70">
                                        {{ tier.mrpInr }}
                                    </span>
                                    <span class="font-mono text-3xl font-black text-card-foreground sm:text-4xl">
                                        {{ getTierPrice(tier) }}
                                    </span>
                                    <span class="text-xs text-muted-foreground font-medium">all-inclusive</span>
                                </div>

                                <p class="mt-2 text-xs text-sky-700 dark:text-sky-400 font-medium">{{ tier.idealFor }}</p>

                                <div class="mt-6 border-t border-border pt-5">
                                    <p class="text-[11px] uppercase tracking-wider font-bold text-muted-foreground mb-3">Key Deliverables:</p>
                                    <ul class="space-y-2 text-xs text-card-foreground/90 font-medium">
                                        <li v-for="item in tier.deliverables" :key="item" class="flex items-start gap-2">
                                            <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ item }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-8 pt-4 border-t border-border">
                                <a
                                    :href="`/?service=${encodeURIComponent(service.id)}&tier=${encodeURIComponent(tier.name.toLowerCase())}&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`"
                                    :class="[
                                        'w-full min-h-[44px] inline-flex items-center justify-center rounded-xl text-xs font-bold transition-all cursor-pointer',
                                        tier.popular
                                            ? 'btn-primary text-white shadow-lg'
                                            : 'border border-slate-300 dark:border-slate-700 bg-secondary text-secondary-foreground font-bold hover:border-sky-500/70 hover:bg-secondary/80 shadow-sm',
                                    ]"
                                >
                                    <span>Select {{ tier.name }} Tier</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add-on Modules (Layer 2) -->
            <div class="mt-20">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="db-chip">Layer 2 · À La Carte Expansion</span>
                    <h2 class="mt-3 text-2xl font-black text-foreground sm:text-4xl">Modular Add-on Capabilities</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Bolt specialized enterprise capabilities onto any core build with transparent, fixed pricing.
                    </p>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="addon in ADDON_MODULES"
                        :key="addon.title"
                        class="rounded-2xl border border-border bg-card p-5 flex flex-col justify-between shadow-sm hover:border-sky-500/40 transition-colors"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-card-foreground">{{ addon.title }}</h3>
                                <span class="text-[10px] font-mono text-muted-foreground">{{ addon.timeline }}</span>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground leading-relaxed">{{ addon.description }}</p>
                        </div>
                        <div class="mt-5 pt-3 border-t border-border flex items-center justify-between">
                            <div class="flex items-baseline gap-1.5">
                                <span v-if="activeRegion === 'INR' && addon.mrpInr" class="font-mono text-xs text-muted-foreground line-through opacity-70">
                                    {{ addon.mrpInr }}
                                </span>
                                <span class="font-mono text-base font-black text-sky-600 dark:text-sky-400">{{ getAddonPrice(addon) }}</span>
                            </div>
                            <span class="text-[10px] text-muted-foreground">fixed add-on</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layer 3: AMC & Ongoing Retainers -->
            <div class="mt-20 rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-10 backdrop-blur-md shadow-xl">
                <div class="max-w-3xl">
                    <span class="db-chip">Layer 3 · Peace of Mind</span>
                    <h2 class="mt-3 text-2xl font-black text-card-foreground sm:text-3xl">
                        {{ activeRegion === 'INR' ? 'Annual Maintenance Contracts (AMC Plans)' : 'Ongoing Engineering Care (Monthly Retainers)' }}
                    </h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{
                            activeRegion === 'INR'
                                ? 'Culturally native annual AMC plans protecting your software uptime, security updates, and regular feature evolutions.'
                                : 'Predictable monthly engineering retainers providing direct senior developer availability and SLA guarantees.'
                        }}
                    </p>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div class="rounded-2xl border border-border bg-card/60 dark:bg-card/40 p-6 flex flex-col justify-between shadow-sm">
                        <div>
                            <h3 class="text-lg font-bold text-card-foreground">Basic {{ activeRegion === 'INR' ? 'AMC' : 'Care' }}</h3>
                            <div class="mt-3 font-mono text-2xl font-black text-card-foreground">
                                <span v-if="activeRegion === 'INR'">
                                    <span class="text-sm text-muted-foreground line-through mr-1 opacity-70">₹29,000</span>
                                    ₹19,000 <span class="text-xs font-normal text-muted-foreground">/ yr (₹1,583/mo)</span>
                                </span>
                                <span v-else-if="activeRegion === 'GULF'">$90 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                                <span v-else>$120 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                            </div>
                            <ul class="mt-5 space-y-2 text-xs text-muted-foreground">
                                <li class="flex items-center gap-2">✓ Monthly server & OS security patches</li>
                                <li class="flex items-center gap-2">✓ Automated daily offsite database backups</li>
                                <li class="flex items-center gap-2">✓ SSL certificate renewal & DNS monitor</li>
                                <li class="flex items-center gap-2">✓ 48-hour issue turnaround SLA</li>
                            </ul>
                        </div>
                        <a :href="`/?service=amc_care&tier=basic&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-border bg-secondary py-2.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-colors">
                            Select Basic
                        </a>
                    </div>

                    <div class="rounded-2xl border-2 border-sky-500 bg-sky-50/60 dark:bg-sky-950/20 p-6 flex flex-col justify-between shadow-lg scale-[1.01]">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-card-foreground">Business {{ activeRegion === 'INR' ? 'AMC' : 'Care' }}</h3>
                                <span class="rounded bg-sky-500/20 px-2 py-0.5 text-[10px] font-bold text-sky-700 dark:text-sky-300">Recommended</span>
                            </div>
                            <div class="mt-3 font-mono text-2xl font-black text-card-foreground">
                                <span v-if="activeRegion === 'INR'">
                                    <span class="text-sm text-muted-foreground line-through mr-1 opacity-70">₹79,000</span>
                                    ₹49,000 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ yr (₹4,083/mo)</span>
                                </span>
                                <span v-else-if="activeRegion === 'GULF'">$250 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo</span></span>
                                <span v-else>$350 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo</span></span>
                            </div>
                            <ul class="mt-5 space-y-2 text-xs text-card-foreground/90">
                                <li class="flex items-center gap-2">✓ Everything in Basic tier</li>
                                <li class="flex items-center gap-2">✓ 8–10 hours of active feature iterations/mo</li>
                                <li class="flex items-center gap-2">✓ 99.9% automated uptime monitoring</li>
                                <li class="flex items-center gap-2">✓ 12-hour priority emergency SLA</li>
                            </ul>
                        </div>
                        <a :href="`/?service=amc_care&tier=business&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="mt-6 inline-flex w-full items-center justify-center rounded-xl btn-primary py-2.5 text-xs font-bold text-white shadow-md">
                            Select Business
                        </a>
                    </div>

                    <div class="rounded-2xl border border-border bg-card/60 dark:bg-card/40 p-6 flex flex-col justify-between shadow-sm">
                        <div>
                            <h3 class="text-lg font-bold text-card-foreground">Enterprise SLA</h3>
                            <div class="mt-3 font-mono text-2xl font-black text-card-foreground">
                                <span v-if="activeRegion === 'INR'">
                                    <span class="text-sm text-muted-foreground line-through mr-1 opacity-70">From ₹1.99L</span>
                                    From ₹1,49,000 <span class="text-xs font-normal text-muted-foreground">/ yr</span>
                                </span>
                                <span v-else-if="activeRegion === 'GULF'">From $600 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                                <span v-else>From $850 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                            </div>
                            <ul class="mt-5 space-y-2 text-xs text-muted-foreground">
                                <li class="flex items-center gap-2">✓ Dedicated named Staff Engineer</li>
                                <li class="flex items-center gap-2">✓ 24/7 critical incident response</li>
                                <li class="flex items-center gap-2">✓ 1-hour critical response SLA</li>
                                <li class="flex items-center gap-2">✓ Custom disaster recovery drills</li>
                            </ul>
                        </div>
                        <a :href="`/?service=amc_care&tier=enterprise&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-border bg-secondary py-2.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-colors">
                            Custom SLA
                        </a>
                    </div>
                </div>

                <!-- Annual prepay note -->
                <div class="mt-6 rounded-2xl border border-border bg-card/60 p-4 text-center text-xs text-muted-foreground">
                    💡 <strong class="text-foreground">Annual prepay = 2 months free.</strong> Care plans start after your 30-day post-launch warranty ends. All plans are cancellable at renewal, and unused change hours roll over one month.
                </div>

                <!-- Build + Grow: Strategic Growth Retainers Band -->
                <div class="mt-10 rounded-3xl border border-sky-500/30 bg-gradient-to-br from-card via-card to-sky-500/5 p-6 sm:p-8 shadow-lg">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-border pb-6">
                        <div>
                            <span class="db-badge-sky mb-2">Build + Grow Pillar</span>
                            <h3 class="text-xl sm:text-2xl font-black text-card-foreground">Strategic Growth & Customer Acquisition Retainers</h3>
                            <p class="mt-1.5 text-xs sm:text-sm text-muted-foreground max-w-2xl">
                                "We don't just build your software — we help you get customers to it." Engineering-led SEO, AI content engines, WhatsApp automation, and high-conversion performance marketing.
                            </p>
                        </div>
                        <a :href="`/?service=growth_retainer&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="btn-primary inline-flex items-center gap-2 self-start md:self-auto rounded-full px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:scale-105">
                            <span>Book Growth Discovery</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- 3 Growth Retainer Tiers Grid -->
                    <div class="mt-6 grid gap-5 md:grid-cols-3">
                        <!-- Tier 1: Growth Starter -->
                        <div class="rounded-2xl border border-border bg-card/80 p-6 flex flex-col justify-between shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-bold text-card-foreground">Grow Starter</h4>
                                    <span class="rounded-full bg-secondary border border-border px-2.5 py-0.5 text-[10px] font-semibold text-muted-foreground">Organic Focus</span>
                                </div>
                                <div class="mt-3 font-mono text-2xl font-black text-card-foreground">
                                    <span v-if="activeRegion === 'INR'">
                                        ₹14,999 <span class="text-xs font-normal text-muted-foreground">/ mo + GST</span>
                                    </span>
                                    <span v-else-if="activeRegion === 'GULF'">
                                        $349 <span class="text-xs font-normal text-muted-foreground">/ mo</span>
                                    </span>
                                    <span v-else>
                                        $449 <span class="text-xs font-normal text-muted-foreground">/ mo</span>
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-muted-foreground">For local businesses, clinics, and trade brands getting found by local customers.</p>
                                <ul class="mt-4 space-y-2 text-xs text-card-foreground/90">
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Google Business Profile setup & Local SEO</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> AI Content Engine: 4 keyword-targeted articles/mo</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Core Web Vitals & structured schema fixes</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Monthly Search Console ranking & traffic report</li>
                                </ul>
                            </div>
                            <a :href="`/?service=growth_retainer&tier=starter&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-border bg-secondary py-2.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-colors">
                                Select Starter
                            </a>
                        </div>

                        <!-- Tier 2: Growth Business ★ -->
                        <div class="rounded-2xl border-2 border-sky-500 bg-sky-50/60 dark:bg-sky-950/20 p-6 flex flex-col justify-between shadow-lg relative">
                            <div class="absolute -top-3 right-4 rounded-full bg-sky-500 px-3 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                Most Popular ★
                            </div>
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-bold text-card-foreground">Grow Business</h4>
                                    <span class="rounded-full bg-sky-500/20 px-2 py-0.5 text-[10px] font-bold text-sky-700 dark:text-sky-300">Paid + Organic</span>
                                </div>
                                <div class="mt-3 font-mono text-2xl font-black text-card-foreground">
                                    <span v-if="activeRegion === 'INR'">
                                        ₹29,999 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo + GST</span>
                                    </span>
                                    <span v-else-if="activeRegion === 'GULF'">
                                        $699 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo</span>
                                    </span>
                                    <span v-else>
                                        $899 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo</span>
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-muted-foreground">For scaling stores, B2B companies, and SaaS actively driving customer enquiries.</p>
                                <ul class="mt-4 space-y-2 text-xs text-card-foreground/90">
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Everything in Starter tier</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Google Search & Meta Performance Ads management</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> 1 High-converting landing page / month (CRO)</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> WhatsApp & email automated lead qualification bot</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> 8 AI-assisted authoritative content pieces/mo</li>
                                </ul>
                            </div>
                            <a :href="`/?service=growth_retainer&tier=business&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="mt-6 inline-flex w-full items-center justify-center rounded-xl btn-primary py-2.5 text-xs font-bold text-white shadow-md">
                                Select Business Growth
                            </a>
                        </div>

                        <!-- Tier 3: Growth Enterprise -->
                        <div class="rounded-2xl border border-border bg-card/80 p-6 flex flex-col justify-between shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-bold text-card-foreground">Grow Enterprise</h4>
                                    <span class="rounded-full bg-secondary border border-border px-2.5 py-0.5 text-[10px] font-semibold text-muted-foreground">Full-Funnel</span>
                                </div>
                                <div class="mt-3 font-mono text-2xl font-black text-card-foreground">
                                    <span v-if="activeRegion === 'INR'">
                                        From ₹59,999 <span class="text-xs font-normal text-muted-foreground">/ mo</span>
                                    </span>
                                    <span v-else-if="activeRegion === 'GULF'">
                                        From $1,399 <span class="text-xs font-normal text-muted-foreground">/ mo</span>
                                    </span>
                                    <span v-else>
                                        From $1,799 <span class="text-xs font-normal text-muted-foreground">/ mo</span>
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-muted-foreground">For multi-city enterprises and high-scale platforms demanding full-funnel growth engineering.</p>
                                <ul class="mt-4 space-y-2 text-xs text-muted-foreground">
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Full-funnel omnichannel growth architecture</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Custom real-time Acquisition Dashboard (ApexCharts)</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Dedicated Growth Strategist & bi-weekly sprint syncs</li>
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Competitor keyword & funnel reverse-engineering</li>
                                </ul>
                            </div>
                            <a :href="`/?service=growth_retainer&tier=enterprise&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`" class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-border bg-secondary py-2.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-colors">
                                Custom Enterprise Growth
                            </a>
                        </div>
                    </div>

                    <!-- À-la-carte One-Time Growth Modules Strip -->
                    <div class="mt-6 pt-6 border-t border-border/60">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">À-la-Carte Growth Modules (One-Time)</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                            <div class="p-3 rounded-xl border border-border bg-secondary/40">
                                <span class="font-bold text-foreground block">SEO Audit</span>
                                <span class="font-mono text-sky-600 dark:text-sky-400 font-bold">{{ activeRegion === 'INR' ? '₹9,999' : '$299' }}</span>
                            </div>
                            <div class="p-3 rounded-xl border border-border bg-secondary/40">
                                <span class="font-bold text-foreground block">AI Content (10)</span>
                                <span class="font-mono text-sky-600 dark:text-sky-400 font-bold">{{ activeRegion === 'INR' ? '₹7,999' : '$249' }}</span>
                            </div>
                            <div class="p-3 rounded-xl border border-border bg-secondary/40">
                                <span class="font-bold text-foreground block">Ad Setup</span>
                                <span class="font-mono text-sky-600 dark:text-sky-400 font-bold">{{ activeRegion === 'INR' ? '₹14,999' : '$449' }}</span>
                            </div>
                            <div class="p-3 rounded-xl border border-border bg-secondary/40">
                                <span class="font-bold text-foreground block">WhatsApp Bot</span>
                                <span class="font-mono text-sky-600 dark:text-sky-400 font-bold">{{ activeRegion === 'INR' ? '₹19,999' : '$599' }}</span>
                            </div>
                            <div class="p-3 rounded-xl border border-border bg-secondary/40 col-span-2 sm:col-span-1">
                                <span class="font-bold text-foreground block">Landing Page</span>
                                <span class="font-mono text-sky-600 dark:text-sky-400 font-bold">{{ activeRegion === 'INR' ? '₹12,999' : '$399' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Honest notice & launch bundle -->
                    <div class="mt-5 p-4 rounded-2xl bg-secondary/60 border border-border text-xs text-muted-foreground space-y-1">
                        <p>💡 <strong class="text-foreground">Honest Transparency:</strong> Platform ad spend is billed directly by Google/Meta to your account. Retainers include transparent monthly reports, strategic reviews, and no long-term lock-in (cancel anytime at renewal).</p>
                        <p class="text-sky-600 dark:text-sky-400 font-semibold">🎁 <strong>Launch Bundle:</strong> Pair any Build package with 3 months of Grow and get 1 Month of Grow completely FREE.</p>
                    </div>
                </div>
            </div>

            <!-- Commercial Milestones & Infrastructure Note (Layer 4) -->
            <div class="mt-20 rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-10 backdrop-blur-md shadow-xl">
                <div class="max-w-3xl">
                    <span class="db-chip">Commercial Integrity</span>
                    <h2 class="mt-3 text-2xl font-black text-card-foreground sm:text-3xl">Staged Milestone Payments & Terms</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Simple, predictable, and written down in a legally binding proposal. You pay as milestones are verified on staging.
                    </p>
                </div>

                <!-- 3 Staged Milestones -->
                <div class="mt-8 grid gap-5 md:grid-cols-3">
                    <div class="rounded-2xl border border-border bg-card/60 p-5">
                        <div class="font-mono text-3xl font-black text-sky-700 dark:text-sky-400">40%</div>
                        <span class="mt-1 block text-xs font-bold uppercase tracking-wider text-card-foreground">At Project Kickoff</span>
                        <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                            Reserves your dedicated sprint slot. Scope signed, Domain-Driven architecture approved, development begins immediately.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-border bg-card/60 p-5">
                        <div class="font-mono text-3xl font-black text-sky-700 dark:text-sky-400">40%</div>
                        <span class="mt-1 block text-xs font-bold uppercase tracking-wider text-card-foreground">At Staging Demo</span>
                        <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                            Complete working system running on live staging environment, thoroughly tested and accepted by your stakeholders.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-border bg-card/60 p-5">
                        <div class="font-mono text-3xl font-black text-emerald-600 dark:text-emerald-400">20%</div>
                        <span class="mt-1 block text-xs font-bold uppercase tracking-wider text-card-foreground">At Launch & Handover</span>
                        <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                            Zero-downtime production deployment, full source code repository transfer, documentation, and live staff training complete.
                        </p>
                    </div>
                </div>

                <!-- Infrastructure Notice (Amber) -->
                <div class="mt-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-card-foreground">
                    <div class="flex items-start gap-3">
                        <div class="h-6 w-6 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                                Infrastructure & Third-Party Costs Notice
                            </h4>
                            <p class="mt-1 text-xs text-muted-foreground leading-relaxed">
                                Hosting servers (AWS, DigitalOcean, Vercel, Neon), domain names, SSL, payment-gateway gateway fees, SMS/WhatsApp API charges (Meta Cloud API), and AI tokens (OpenAI/Anthropic) are billed directly to you. We set these accounts up in <strong class="text-foreground">your name</strong> so you own and control 100% of your data and assets.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 3 Legal Pillars -->
                <div class="mt-6 grid gap-4 sm:grid-cols-3 pt-6 border-t border-border text-xs">
                    <div class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-card-foreground block">Free Mutual NDA</strong>
                            <span class="text-muted-foreground">Signed before the first technical deep-dive call. Your proprietary IP stays protected.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-card-foreground block">100% Code Ownership</strong>
                            <span class="text-muted-foreground">Full Git repository access, architecture docs, and IP rights transfer on final release.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <strong class="text-card-foreground block">Govt Registered MSME</strong>
                            <span class="text-muted-foreground">Official Udyam Reg: <strong class="text-foreground">UDYAM-PB-12-0217716</strong>. GST invoices provided.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Downloadable PDF Brochures Section -->
            <div class="mt-20 rounded-3xl border border-sky-500/30 bg-gradient-to-br from-sky-900/30 via-card to-indigo-900/30 p-6 sm:p-10 shadow-2xl">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="max-w-2xl">
                        <span class="db-chip">Executive Rate Cards</span>
                        <h2 class="mt-3 text-2xl font-black text-card-foreground sm:text-3xl">Download Official Price Book PDFs</h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Need a clean, shareable 1-page document for your board, finance team, or partners? Download or print our official rate cards instantly.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                        <a
                            href="/downloads/digitalbuilders-pricing-india-inr.pdf"
                            target="_blank"
                            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-2xl bg-secondary border border-border px-6 py-3.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-all shadow-sm"
                        >
                            <span class="text-base">🇮🇳</span>
                            <span>India Price Card (PDF)</span>
                            <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        <a
                            href="/downloads/digitalbuilders-pricing-international-usd.pdf"
                            target="_blank"
                            class="btn-primary inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-2xl px-6 py-3.5 text-xs font-bold text-white shadow-lg"
                        >
                            <span class="text-base">🌍</span>
                            <span>International Price Card (PDF)</span>
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Architecture Discovery Sprint Callout -->
            <div class="mt-16 rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                        <span class="text-xs uppercase tracking-wider font-bold text-emerald-700 dark:text-emerald-400">Zero-Risk Entry On-Ramp</span>
                    </div>
                    <h3 class="mt-2 text-xl font-bold text-card-foreground sm:text-2xl">Architecture Discovery & Tech Blueprint Sprint</h3>
                    <p class="mt-1 text-xs sm:text-sm text-muted-foreground max-w-2xl">
                        Unsure of your complete database schema, cloud requirements, or AI feasibility? Book a 1-week deep architectural sprint for
                        <strong class="text-sky-700 dark:text-sky-300 font-mono">
                            <span v-if="activeRegion === 'INR'">
                                <span class="text-xs text-muted-foreground line-through mr-1 opacity-70">₹25,000</span>
                                ₹19,000
                            </span>
                            <span v-else-if="activeRegion === 'GULF'">$500</span>
                            <span v-else>$750</span>
                        </strong>.
                        <strong>100% credited</strong> toward your build contract upon kickoff.
                    </p>
                </div>
                <a
                    :href="`/?service=discovery_sprint&region=${encodeURIComponent(activeRegion.toLowerCase())}#contact`"
                    class="btn-primary shrink-0 inline-flex items-center justify-center rounded-full px-6 py-3 min-h-[44px] text-xs font-bold text-white shadow-lg transition hover:scale-105"
                >
                    Book Discovery Sprint
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-20 border-t border-border bg-secondary/50 py-8 text-center text-xs text-muted-foreground">
            <div class="mx-auto max-w-7xl px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>© {{ new Date().getFullYear() }} DigitalBuilders · Architectural Price Book</p>
                <div class="flex flex-wrap items-center gap-4">
                    <Link href="/" class="hover:text-foreground transition-colors">Home</Link>
                    <Link href="/estimator" class="hover:text-foreground transition-colors">Estimator</Link>
                    <Link href="/library/privacy-policy" class="hover:text-foreground transition-colors">Privacy Policy</Link>
                    <Link href="/library/terms-of-service" class="hover:text-foreground transition-colors">Terms of Service</Link>
                    <button type="button" @click="openCookieSettings" class="hover:text-foreground transition-colors cursor-pointer">Cookie Settings</button>
                </div>
            </div>
        </footer>

        <!-- High-Conversion Sticky Mobile CTA Bar -->
        <StickyMobileCta />

        <!-- Privacy-First Cookie Consent Banner -->
        <CookieConsent />
    </div>
</template>
