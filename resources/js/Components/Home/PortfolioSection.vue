<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import CaseStudyModal, { type CaseStudy } from '@/Components/Home/CaseStudyModal.vue';
import { vAutoAnimate } from '@formkit/auto-animate/vue';

const selectedCategory = ref<string>('all');
const activeModalStudy = ref<CaseStudy | null>(null);

function openStudyModal(study: CaseStudy) {
    activeModalStudy.value = study;
    document.body.style.overflow = 'hidden';
}

function closeStudyModal() {
    activeModalStudy.value = null;
    document.body.style.overflow = '';
}

const categories = [
    { id: 'all', label: 'All Projects' },
    { id: 'saas', label: 'SaaS & FinTech' },
    { id: 'health', label: 'Health & Telehealth' },
    { id: 'consumer', label: 'Consumer & AstroTech' },
    { id: 'mobile', label: 'Mobile Apps' },
    { id: 'ngo', label: 'Trust & NGO' },
] as const;

const studies: CaseStudy[] = [
    {
        id: 'habuilt',
        client: 'Habuilt',
        portfolioSlug: 'habuilt',
        category: 'consumer',
        categoryLabel: 'High-Scale Habit & Wellness SaaS',
        liveUrl: 'https://www.habuilt.com/',
        isMobile: false,
        image: '/images/portfolio/habuilt.jpg',
        metricBadge: '50 Habits · 4 Tiers · 99.99% Uptime',
        techStack: ['Next.js / Vue 3', 'TypeScript', 'Tailwind CSS', 'Redis Caching', 'Mobile Deep Linking'],
        tldr: 'Progressive atomic habit tracking platform with 50 habits, 4 progression tiers, streak mechanics, and mobile deep link auth.',
        problem: 'Building lasting daily habits requires continuous positive reinforcement without complex friction or mobile disconnect.',
        challenge: 'Supporting high-frequency daily habit check-ins and streaks across 26-week progression tiers with instant mobile app handoff.',
        architectureActions: [
            'Engineered a 4-tier progressive unlock timeline (Foundation, Build, Refine, Mastery) across 26 weeks.',
            'Implemented sub-50ms streak & XP leveling computations with carry-forward point balances.',
            'Integrated universal mobile deep linking (habuilt://auth/callback) for instant mobile session handover.',
        ],
        businessImpact: [
            '1% daily compounding discipline engine adopted by thousands of active habit builders.',
            'Zero-latency habit completion sync and interactive weekly completion heatmaps.',
            '99.99% uptime with high-concurrency atomic data persistence.',
        ],
        quote: 'DigitalBuilders brought Silicon Valley-level engineering standards. Our server latencies dropped by 70% and habit check-in throughput doubled within weeks.',
        quoteAuthor: 'Gurpreet Singh, CTO & Co-Founder, Habuilt Technologies',
    },
    {
        id: 'dhandadiary',
        client: 'Dhanda Diary',
        portfolioSlug: 'dhandadiary',
        category: 'saas',
        categoryLabel: 'Execution Cockpit & Business Ledger SaaS',
        liveUrl: 'https://dhandadiary.cloud/',
        isMobile: false,
        image: '/images/portfolio/dhandadiary.jpg',
        metricBadge: 'Daily Compliance Engine · Sub-50ms Sync',
        techStack: ['Laravel 13', 'Vue 3', 'Inertia.js', 'ApexCharts', 'VAPID Web Push', 'Google OAuth'],
        tldr: 'Execution cockpit & daily compliance SaaS with automated DCR routines, Kanban task boards, ApexCharts KPI trackers, and discipline streak multipliers.',
        problem: 'Founders and enterprise teams lack a centralized execution cockpit to monitor daily compliance, habit discipline, and business KPIs in one place.',
        challenge: 'Needed sub-50ms reactive state sync, real-time Kanban task reordering, Web Push reminders, and multi-tenant ledger isolation.',
        architectureActions: [
            'Architected Daily Compliance Report (DCR) and Weekly Strategic Review pipelines with automated streak calculations.',
            'Integrated interactive ApexCharts visual metric telemetry for revenue trends and operational KPIs.',
            'Built drag-and-drop Kanban workflow boards with optimistic UI updates and background sync.',
        ],
        businessImpact: [
            '100% daily task execution accountability for business executives and remote teams.',
            'Automated morning and evening Web Push reminders increasing daily routine completion by 85%.',
            'Seamless Google OAuth authentication and instant zero-latency multi-device sync.',
        ],
        quote: 'Dhanda Diary transformed daily execution and habit discipline for our teams. The cockpit is responsive, fast, and gives us instant visibility over every KPI.',
        quoteAuthor: 'Harpreet Singh, Co-Founder, Dhanda Diary Cloud',
    },
    {
        id: 'zoeticoach',
        client: 'ZoetiCoach AI',
        portfolioSlug: 'zoeticoach',
        category: 'saas',
        categoryLabel: 'WhatsApp-First B2B2C Coaching ERP',
        liveUrl: 'https://zoeticoach.com/',
        isMobile: false,
        image: '/images/portfolio/zoeticoach.jpg',
        metricBadge: 'OpenAI RAG Pipeline · Zero Client Drop-off',
        techStack: ['Laravel 13', 'Vue 3', 'pgvector', 'OpenAI RAG', 'WhatsApp Cloud API', 'Modular Monolith'],
        tldr: 'WhatsApp-first accountability SaaS for professional coaches featuring autonomous AI habit verification and event-sourced client ledgers.',
        problem: 'Professional fitness and mindset coaches lose 40%+ of clients due to accountability drop-off outside of weekly calls.',
        challenge: 'Automating personalized, hallucination-free habit verification directly inside WhatsApp without requiring clients to install another app.',
        architectureActions: [
            'Engineered a production OpenAI RAG pipeline with pgvector to verify habit proof (photos, text) directly via WhatsApp.',
            'Architected a scalable Laravel Modular Monolith with an event-sourced ledger for real-time coach cohort management.',
            'Built automated client intake, milestone tracking, and retention analytics dashboards.',
        ],
        businessImpact: [
            'Reduced client dropout rate by 65% across pilot coaching cohorts.',
            'Automated 100% of routine daily check-ins without coach intervention.',
            'Eliminated manual spreadsheet tracking for multi-coach wellness enterprises.',
        ],
        quote: 'ZoetiCoach eliminates client drop-off completely. The AI verification over WhatsApp feels like having a personal assistant working 24/7 for every client.',
        quoteAuthor: 'Rajesh Sharma, Managing Director, ZoetiCoach AI',
    },
    {
        id: 'guttalks',
        client: 'GutTalks',
        portfolioSlug: 'guttalks',
        category: 'health',
        categoryLabel: 'Gut Health & Telehealth Consultation Portal',
        liveUrl: 'https://guttalks.in/',
        isMobile: false,
        image: '/images/portfolio/guttalks.jpg',
        metricBadge: '10k+ Clients · 4.8★ Rating · ₹499 Root Rx',
        techStack: ['Next.js', 'React', 'Tailwind CSS', 'Razorpay Checkout', 'Microbiome API', 'Doctor Telehealth'],
        tldr: 'Evidence-based gut health platform connecting patients to gastroenterologists via Root Rx consultations and GutMap Complete™ at-home microbiome testing.',
        problem: 'Patients with chronic bloating, IBS, and digestive fatigue face fragmented advice and high consultation barriers.',
        challenge: 'Needed a high-converting doctor booking engine with instant slot availability, GutMap kit ordering, and seamless telehealth consultations.',
        architectureActions: [
            'Built an instant Root Rx booking widget (₹499) with real-time doctor availability slot selection.',
            'Engineered GutMap Complete™ at-home testing kit portal with laboratory sequencing sample tracking.',
            'Integrated Razorpay payment gateway and automated WhatsApp consultation reminders.',
        ],
        businessImpact: [
            'Over 10,000+ happy clients treated with 4.8-star verified Google rating.',
            '3.2x increase in consultation conversion rate compared to standard static medical forms.',
            'Full doctor-approved lifestyle roadmap delivered within 30 days of initial consultation.',
        ],
        quote: 'The Root Rx booking flow and GutMap testing portal engineered by DigitalBuilders increased our consultation bookings dramatically. Our patients love how intuitive the interface is.',
        quoteAuthor: 'Dr. Mehak Verma, Head of Clinical Nutrition, GutTalks India',
    },
    {
        id: 'myastrova',
        client: 'MyAstrova',
        portfolioSlug: 'myastrova',
        category: 'consumer',
        categoryLabel: 'Vedic AstroTech & Spiritual E-Commerce',
        liveUrl: 'https://myastrova.com/',
        isMobile: false,
        image: '/images/portfolio/myastrova.jpg',
        metricBadge: '<200ms Kundli Engine · Live Calls & Chat',
        techStack: ['Next.js', 'React', 'Tailwind CSS', 'Razorpay Checkout', 'WhatsApp API', 'Vedic Math Engine'],
        tldr: 'Vedic astrology consultation platform offering instant call/chat with astrologers, Kundli matching algorithms, and an energized crystal/gemstone mall.',
        problem: 'Traditional astrology portals suffer from slow chart rendering, confusing interfaces, and unverified remedy purchases.',
        challenge: 'Calculating planetary positions with ephemeris accuracy in sub-200ms while routing live chat/call consultation requests to available astrologers.',
        architectureActions: [
            'Engineered a mathematical Kundli Matching and Horoscope calculation engine rendering dynamic charts in <200ms.',
            'Built real-time astrologer routing for instant chats, phone calls, and video consultations.',
            'Created MyAstrova Mall e-commerce catalog for energized crystals, rudraksha, and customized remedies with Razorpay checkout.',
        ],
        businessImpact: [
            'Instantaneous chart generation with 100% mathematical precision.',
            '99.9% booking and checkout reliability across high consumer traffic.',
            'Built a trusted spiritual brand with direct WhatsApp concierge support.',
        ],
        quote: 'DigitalBuilders delivered a mathematically accurate and stunning AstroTech platform. The charts render instantly and the seamless booking process has made MyAstrova a trusted name.',
        quoteAuthor: 'Acharya Raman, Principal Astrologer, MyAstrova',
    },
    {
        id: 'gaushala',
        client: 'Krishan Balram Gaushala',
        portfolioSlug: 'gaushala',
        category: 'ngo',
        categoryLabel: 'Devotee Engagement & Cow Shelter Philanthropy',
        liveUrl: 'https://krishanbalramgaushala.com/',
        isMobile: false,
        image: '/images/portfolio/gaushala.jpg',
        metricBadge: 'Meta WhatsApp API · Automated 80G Receipts',
        techStack: ['Laravel 13', 'Vue 3', 'Inertia.js', 'Meta WhatsApp Cloud API', 'PWA Offline', 'SQLite WAL'],
        tldr: 'GauSeva Connect — Devotee registration portal, automated birthday/anniversary WhatsApp blessings dispatcher, and 80G tax receipt management.',
        problem: 'The shelter handled thousands of devotee records and seva donations manually, causing delayed tax receipts and missed community touchpoints.',
        challenge: 'Automating daily WhatsApp blessings, Facebook auto-posting, and instant 80G PDF receipts on a high-concurrency, lightweight server.',
        architectureActions: [
            'Integrated Meta WhatsApp Cloud API webhooks to automatically dispatch personalized birthday and anniversary blessings daily.',
            'Architected an automated 80G tax exemption PDF generator and donor contribution ledger.',
            'Engineered PWA with client-side HTML5 canvas image compression (99% bandwidth reduction on photo uploads) and SQLite WAL tuning.',
        ],
        businessImpact: [
            '100% automated birthday and anniversary blessings sent to thousands of registered devotees.',
            '3.5x increase in online donor engagement and instant automated 80G receipt delivery.',
            'Eliminated manual bookkeeping and photo dispatch workload for temple volunteers.',
        ],
        quote: 'The GauSeva Connect platform gave our devotees automated blessings on their special days and instant tax receipts. It transformed how our shelter connects with donors.',
        quoteAuthor: 'Trust Management, Krishan Balram Gaushala Ludhiana',
    },
    {
        id: 'ashishgupta',
        client: 'Ashish Gupta Hub',
        portfolioSlug: 'ashishgupta',
        category: 'consumer',
        categoryLabel: 'Engineering Architecture Showcase',
        liveUrl: 'https://ashishgupta.dev/',
        isMobile: false,
        image: '/images/portfolio/ashishgupta.jpg',
        metricBadge: 'VILT Stack · 9+ Yrs IT · $1M Cloud Savings',
        techStack: ['Laravel 12/13', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'PWA Offline', 'Filament CMS'],
        tldr: 'High-performance engineering hub showcasing modernizing legacy healthcare/aviation monoliths, live telemetry metrics, and VILT stack architecture.',
        problem: 'Technical decision-makers needed verified proof of enterprise architectural leadership, DDD modernization, and cost optimization.',
        challenge: 'Demonstrating interactive architecture diagrams, live GitHub stats, visitor analytics, and dark/light system state in a sub-second portfolio.',
        architectureActions: [
            'Architected a high-speed VILT stack platform with Domain-Driven Design (DDD) principles.',
            'Built real-time telemetry metrics, interactive architecture explorer, and terminal emulation.',
            'Engineered full PWA offline support with self-hosted variable typography.',
        ],
        businessImpact: [
            'Documented track record of $1M/year cloud cost reductions in enterprise modernization.',
            'Sub-0.5s initial page load time with zero external render-blocking font dependencies.',
            'Direct client and consulting acquisition channel for DigitalBuilders.',
        ],
        quote: 'Engineering excellence is about building scalable, resilient systems that eliminate complexity. Every application in our portfolio is engineered for zero technical debt.',
        quoteAuthor: 'Ashish Gupta, Lead Architect & Founder, DigitalBuilders',
    },
    {
        id: 'sportsclub',
        client: 'SportsEntertainmentClub',
        portfolioSlug: 'sports-club',
        category: 'mobile',
        categoryLabel: 'Facility & Court Booking App (iOS/Android)',
        liveUrl: null,
        isMobile: true,
        image: '/images/portfolio/sportsclub.jpg',
        metricBadge: '0 Booking Collisions · 60 FPS Fluid UI',
        techStack: ['Flutter / React Native', 'Real-Time Slot Locks', 'Push Notifications', 'QR Access Control'],
        tldr: 'Mobile app for sports facility reservations, badminton/tennis court slot locking, digital QR member passes, and live tournament leaderboards.',
        problem: 'Club members suffered from double bookings, long phone queues, and manual gate access tracking for premium courts.',
        challenge: 'Implementing atomic time-slot reservation locks with sub-second synchronization during peak booking windows.',
        architectureActions: [
            'Engineered cross-platform iOS and Android apps with 60 FPS smooth animations and instant schedule views.',
            'Built atomic slot reservation engine with instant UPI/Card payments and automatic cancellation releases.',
            'Integrated dynamic QR member passes for contactless gate access control.',
        ],
        businessImpact: [
            'Zero court reservation conflicts across 12 tennis and badminton courts.',
            '95% mobile adoption rate among registered club members in the first month.',
            '3x faster check-in speed at club reception with digital QR passes.',
        ],
        quote: 'The mobile app transformed our entire club management. Our members love booking slots right from their phones with zero waiting.',
        quoteAuthor: 'Club Secretary, SportsEntertainmentClub',
    },
    {
        id: 'gargenterprises',
        client: 'Garg Enterprises',
        portfolioSlug: 'garg-enterprises',
        category: 'mobile',
        categoryLabel: 'B2B Wholesale Ordering & Ledger App',
        liveUrl: null,
        isMobile: true,
        image: '/images/portfolio/gargenterprises.jpg',
        metricBadge: '0% Order Errors · 10k+ SKUs · Offline Sync',
        techStack: ['Android Native / Kotlin', 'Offline SQLite Sync', 'GST Invoice PDF', 'Tiered B2B Pricing'],
        tldr: 'B2B wholesale ordering app with offline drafting, dealer credit ledger reconciliation, 1-tap GST invoice downloads, and 10k+ SKU catalog.',
        problem: 'Wholesale dealers placed orders over handwritten notes and phone calls, causing order errors, inventory mismatches, and ledger disputes.',
        challenge: 'Ensuring fast order placement in low-connectivity warehouse environments with dealer-specific tiered pricing and credit limits.',
        architectureActions: [
            'Engineered rugged Android enterprise app with offline SQLite order drafting and automatic background sync.',
            'Built real-time dealer ledger displaying live balance, credit limit, and 1-tap GST invoice downloads.',
            'Implemented tiered volume discount matrix and automated warehouse dispatch alerts.',
        ],
        businessImpact: [
            'Reduced manual order entry errors from 14% to 0%.',
            '3x faster dealer reorder cycle with single-tap repeat order functionality.',
            '100% transparency on outstanding dealer ledger balances and credit terms.',
        ],
        quote: 'Our dealers can now place bulk orders anytime, view their credit ledger, and track dispatches in real-time. It completely eliminated our order confusion.',
        quoteAuthor: 'Managing Director, Garg Enterprises',
    },
];

const filteredStudies = computed(() => {
    if (selectedCategory.value === 'all') {
        return studies;
    }
    return studies.filter((study) => study.category === selectedCategory.value);
});
</script>

<template>
    <section id="portfolio" class="mt-20 sm:mt-24" data-reveal>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-700 dark:text-[#9dc5ff] font-semibold">Delivered Work & Case Studies</p>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl md:text-4xl">Production Applications Built for Scale</h2>
                <p class="mt-3 max-w-2xl text-sm text-slate-600 dark:text-slate-300">
                    Explore live web platforms, cloud SaaS engines, and enterprise mobile apps engineered by DigitalBuilders with zero technical debt.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link href="/estimator" class="rounded-full border border-sky-500/30 bg-sky-500/10 px-4 py-2 text-xs font-bold text-sky-700 dark:text-sky-400 hover:bg-sky-500/20 transition-all inline-flex items-center gap-1.5">
                    <span>Cost Estimator</span>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>
        </div>

        <!-- Interactive Category Filter Tabs -->
        <div class="mt-8 flex flex-wrap items-center gap-2 border-b border-border pb-4">
            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="selectedCategory = cat.id"
                class="rounded-full px-4 py-2 text-xs font-bold transition-all duration-200 flex items-center gap-2 cursor-pointer"
                :class="[
                    selectedCategory === cat.id
                        ? 'btn-primary text-white shadow-md scale-105'
                        : 'bg-secondary text-secondary-foreground hover:bg-secondary/80 hover:text-foreground'
                ]"
            >
                <span>{{ cat.label }}</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px]" :class="selectedCategory === cat.id ? 'bg-white/20' : 'bg-background text-muted-foreground'">
                    {{ cat.id === 'all' ? studies.length : studies.filter(s => s.category === cat.id).length }}
                </span>
            </button>
        </div>

        <!-- Projects Grid / Scroll Container with Smooth Transition Animation -->
        <div v-auto-animate class="case-scroll mt-8 flex gap-6 overflow-x-auto pb-6" data-stagger>
            <article
                v-for="study in filteredStudies"
                :key="study.id"
                data-stagger-item
                class="case-card snap-start rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-7 flex flex-col justify-between shadow-lg transition-all duration-300 hover:border-sky-500/50"
            >
                <div>
                    <!-- Header Bar with Live Indicator Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span v-if="!study.isMobile" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-1 text-[11px] font-bold text-emerald-800 dark:text-emerald-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                Live Web App
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/15 border border-indigo-500/30 px-2.5 py-1 text-[11px] font-bold text-indigo-800 dark:text-indigo-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse" />
                                Mobile App (iOS/Android)
                            </span>
                            <span class="hidden sm:inline-block text-[11px] font-medium text-muted-foreground">
                                {{ study.categoryLabel }}
                            </span>
                        </div>
                        <span class="rounded-full border border-border bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground">
                            {{ study.client }}
                        </span>
                    </div>

                    <!-- Title & Metric Highlight -->
                    <h3 class="mt-4 text-xl font-extrabold text-card-foreground leading-snug">
                        {{ study.client }}
                    </h3>
                    <div class="mt-2 inline-flex items-center rounded-xl bg-sky-500/10 border border-sky-500/30 px-3 py-1 text-xs font-bold text-sky-800 dark:text-sky-300">
                        ⚡ {{ study.metricBadge }}
                    </div>

                    <!-- Live Architecture Snapshot Image -->
                    <div class="relative mt-4 aspect-video w-full overflow-hidden rounded-2xl border border-border bg-slate-900 group cursor-pointer" @click="openStudyModal(study)">
                        <img
                            :src="study.image"
                            :alt="study.client"
                            class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                            decoding="async"
                            width="600"
                            height="338"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-between p-3">
                            <span class="text-[11px] font-bold text-white bg-black/60 backdrop-blur-md px-2.5 py-1 rounded-full border border-white/20">
                                Live UI Snapshot
                            </span>
                            <span class="text-[11px] font-semibold text-sky-300 bg-sky-950/80 px-2 py-0.5 rounded-full">
                                Click to Expand ↗
                            </span>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-muted-foreground leading-relaxed">
                        {{ study.tldr }}
                    </p>

                    <!-- Tech Stack Pills -->
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        <span
                            v-for="tech in study.techStack"
                            :key="tech"
                            class="rounded-md bg-secondary border border-border px-2.5 py-0.5 text-[11px] font-medium text-secondary-foreground"
                        >
                            {{ tech }}
                        </span>
                    </div>

                    <!-- Architecture & Impact Preview -->
                    <div class="mt-5 space-y-2 rounded-2xl border border-border bg-secondary/40 p-4 text-xs text-muted-foreground">
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-card-foreground shrink-0">Solution:</span>
                            <span>{{ study.architectureActions[0] }}</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-emerald-800 dark:text-emerald-300 shrink-0">Impact:</span>
                            <span class="text-card-foreground/90">{{ study.businessImpact[0] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-wrap items-center gap-2.5 pt-4 border-t border-border">
                    <!-- Direct Live Link or Demo Trigger -->
                    <a
                        v-if="study.liveUrl"
                        :href="study.liveUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-primary inline-flex items-center gap-1.5 min-h-[44px] rounded-full px-4 py-2.5 text-xs font-bold text-white shadow transition hover:scale-105"
                    >
                        <span>Visit Live App</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a
                        v-else
                        href="#contact"
                        class="inline-flex items-center gap-1.5 min-h-[44px] rounded-full bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow transition hover:scale-105"
                    >
                        <span>Request APK / Demo</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </a>

                    <!-- Quick View Modal Trigger -->
                    <button
                        @click="openStudyModal(study)"
                        class="inline-flex items-center gap-1 min-h-[44px] rounded-full border border-border bg-secondary px-3.5 py-2.5 text-xs font-semibold text-secondary-foreground transition hover:bg-secondary/80 cursor-pointer"
                    >
                        <span>Quick View</span>
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>

                    <!-- Deep Page Case Study Link -->
                    <Link
                        :href="`/portfolio/${study.portfolioSlug}`"
                        class="inline-flex items-center gap-1 min-h-[44px] rounded-full px-3 py-2 text-xs font-bold text-sky-700 dark:text-sky-400 hover:underline transition-colors ml-auto"
                    >
                        <span>Full Case Study</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
            </article>
        </div>

        <CaseStudyModal :study="activeModalStudy" @close="closeStudyModal" />
    </section>
</template>

<style scoped>
.case-scroll {
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
    scrollbar-color: rgba(154, 173, 255, 0.8) rgba(39, 55, 77, 0.4);
}

.case-scroll::-webkit-scrollbar {
    height: 10px;
}

.case-scroll::-webkit-scrollbar-track {
    background: rgba(39, 55, 77, 0.35);
    border-radius: 999px;
}

.case-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, rgba(122, 196, 255, 0.85), rgba(197, 147, 255, 0.9));
    border-radius: 999px;
}

.case-card {
    min-width: min(90vw, 560px);
    scroll-snap-align: start;
}
</style>
