<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'Custom Web Application Development',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Blazing-fast, enterprise-grade web applications engineered with modular monolith architecture and sub-100ms response times.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'Web Application Development Packages',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Launch Web Application',
                },
                priceCurrency: 'INR',
                price: '99000',
            },
        ],
    },
};

const breadcrumbSchema = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://www.digitalbuilders.in' },
        { '@type': 'ListItem', position: 2, name: 'Services', item: 'https://www.digitalbuilders.in/#services' },
        { '@type': 'ListItem', position: 3, name: 'Custom Web Applications', item: 'https://www.digitalbuilders.in/services/web-applications' },
    ],
};
</script>

<template>
    <Head title="Custom Web Application Development — DigitalBuilders">
        <meta name="description" content="Blazing-fast, enterprise-grade web applications built with Laravel 13, Vue 3, Inertia, and Modular Monolith architecture." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/web-applications" />
        <meta property="og:title" content="Custom Web Application Development | DigitalBuilders" />
        <meta property="og:description" content="Blazing-fast, enterprise-grade web applications engineered with modular monolith architecture and sub-100ms response times." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/web-applications" />
        <meta name="twitter:card" content="summary_large_image" />
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(serviceSchema) }}
        </component>
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(breadcrumbSchema) }}
        </component>
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full btn-primary px-4 py-2 text-xs font-semibold text-white sm:inline-flex">
                        Estimate Project Cost
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 01</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl lg:text-6xl leading-tight">
                    Custom Web Application Development
                </h1>
                <p class="mt-6 text-base text-muted-foreground sm:text-lg leading-relaxed">
                    Stop settling for fragile templates. We engineer custom modular monoliths that handle millions of requests with sub-100ms latency, zero tech debt, and bulletproof security.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="btn-primary rounded-full px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Schedule Architecture Review
                    </a>
                    <Link href="/estimator" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80">
                        Calculate Web App Scope
                    </Link>
                </div>
            </div>

            <!-- Tech Badges -->
            <div class="mt-16 text-center">
                <p class="text-xs uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400 font-bold">Core Stack & Ecosystem</p>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <span class="rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground">Laravel 13</span>
                    <span class="rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground">Vue 3 + TypeScript</span>
                    <span class="rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground">Inertia.js</span>
                    <span class="rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground">PostgreSQL (Neon)</span>
                    <span class="rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground">Redis & WebSockets</span>
                    <span class="rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground">Tailwind CSS</span>
                </div>
            </div>

            <!-- Architectural Capabilities -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">⚡</div>
                    <h3 class="text-xl font-bold text-card-foreground">Sub-100ms Response Times</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Optimized database indexing, Redis query caching, and lazy hydration ensure instantaneous page transitions.
                    </p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">🛡️</div>
                    <h3 class="text-xl font-bold text-card-foreground">Bank-Grade Security</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Automated SQL injection protection, CSRF hardening, rate limiting, and TOTP 2FA authorization.
                    </p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">🧩</div>
                    <h3 class="text-xl font-bold text-card-foreground">Modular Monolith Architecture</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Domain-Driven Design (DDD) with clear boundary interfaces so your code grows without becoming a mess.
                    </p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400 font-bold">Proven In Production</span>
                    <h2 class="mt-2 text-2xl font-black text-foreground sm:text-4xl">Featured Web Applications Delivered</h2>
                    <p class="mt-3 text-sm text-muted-foreground">Live production applications demonstrating our architectural reliability and scaling capability.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Habuilt -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/habuilt.jpg" alt="Habuilt Platform" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-sky-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">99.99% Uptime</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Wellness & Habits</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Habuilt</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">High-traffic atomic habit platform with 26-week progression tiers. Reduced server latencies by 70%.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/habuilt" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- GutTalks -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/guttalks.jpg" alt="GutTalks Clinic" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">10k+ Patients</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Telehealth Clinic</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">GutTalks</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Evidence-based gut health platform with ₹499 Root Rx booking & GutMap Complete™ sequencing tracker.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://guttalks.in/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/guttalks" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Ashish Gupta Hub -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/ashishgupta.jpg" alt="Ashish Gupta Hub" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-purple-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">$1M+ Savings</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider">Architecture Hub</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Ashish Gupta Hub</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Senior Full-Stack Architect portfolio demonstrating Domain-Driven Design (DDD) and real-time telemetry.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://ashishgupta.dev/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-purple-700 dark:text-purple-300 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/ashishgupta" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Process Flow -->
            <div class="mt-20 rounded-3xl border border-border bg-card text-card-foreground p-8 sm:p-12 shadow-lg">
                <h2 class="text-2xl font-black text-card-foreground sm:text-3xl">How We Build Your Web Application</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-4">
                    <div class="border-l-2 border-sky-500 pl-4">
                        <span class="text-xs font-bold text-sky-600 dark:text-sky-400">PHASE 01</span>
                        <h4 class="mt-1 font-bold text-card-foreground">Domain Blueprinting</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Database schema design, API contract definition, and UX wireframes.</p>
                    </div>
                    <div class="border-l-2 border-indigo-500 pl-4">
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">PHASE 02</span>
                        <h4 class="mt-1 font-bold text-card-foreground">Agile Sprints</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Weekly test-driven iterations with transparent staging environment access.</p>
                    </div>
                    <div class="border-l-2 border-purple-500 pl-4">
                        <span class="text-xs font-bold text-purple-600 dark:text-purple-400">PHASE 03</span>
                        <h4 class="mt-1 font-bold text-card-foreground">Load Testing & Audit</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Simulating peak traffic, memory leak detection, and security penetration audit.</p>
                    </div>
                    <div class="border-l-2 border-emerald-500 pl-4">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">PHASE 04</span>
                        <h4 class="mt-1 font-bold text-card-foreground">Zero-Downtime Launch</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Automated CI/CD deployment on Vercel/AWS with 30 days post-launch support.</p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>

        <StickyMobileCta />
    </div>
</template>
