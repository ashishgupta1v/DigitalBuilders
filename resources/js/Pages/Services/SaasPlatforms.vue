<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'Multi-Tenant SaaS Platform Engineering',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Multi-tenant SaaS platform engineering with subscription billing, tenant data isolation, and global auto-scaling.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'SaaS Platform Engineering Packages',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Launch SaaS Platform',
                },
                priceCurrency: 'INR',
                price: '249000',
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
        { '@type': 'ListItem', position: 3, name: 'SaaS Platform Engineering', item: 'https://www.digitalbuilders.in/services/saas-platforms' },
    ],
};
</script>

<template>
    <Head title="SaaS Platform Engineering — DigitalBuilders">
        <meta name="description" content="Multi-tenant SaaS platform engineering with subscription billing, tenant data isolation, and global auto-scaling." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/saas-platforms" />
        <meta property="og:title" content="High-Scale SaaS Platform Engineering | DigitalBuilders" />
        <meta property="og:description" content="Multi-tenant SaaS platform engineering with subscription billing, tenant data isolation, and global auto-scaling." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/dhandadiary.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/saas-platforms" />
        <meta name="twitter:card" content="summary_large_image" />
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(serviceSchema) }}
        </component>
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(breadcrumbSchema) }}
        </component>
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Estimate SaaS Scope
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 05</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl lg:text-6xl leading-tight">
                    SaaS Platform Architecture
                </h1>
                <p class="mt-6 text-base text-slate-300 sm:text-lg leading-relaxed">
                    Build B2B and B2C SaaS platforms engineered for global scale, strict multi-tenant isolation, automated recurring billing, and 99.99% system availability.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-6 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Launch Your SaaS Idea
                    </a>
                    <Link href="/estimator" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50">
                        Calculate SaaS Scope
                    </Link>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">🏢</div>
                    <h3 class="text-xl font-bold text-white">Multi-Tenant Isolation</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Strict tenant data boundaries, custom subdomains, and granular team permission roles.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">💳</div>
                    <h3 class="text-xl font-bold text-white">Automated Billing</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Stripe & Razorpay recurring subscriptions, usage-based metering, and self-serve client billing portal.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">📈</div>
                    <h3 class="text-xl font-bold text-white">Auto-Scaling Infrastructure</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Serverless deployment, Redis queue workers, CDN edge caching, and automated error logging.</p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-[#9dc5ff] font-bold">Production SaaS Proven</span>
                    <h2 class="mt-2 text-2xl font-black text-white sm:text-4xl">Featured SaaS Implementations</h2>
                    <p class="mt-3 text-sm text-slate-300">Live multi-tenant platforms delivering real business metrics across finance, fitness, and wellness.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Dhanda Diary -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/dhandadiary.jpg" alt="Dhanda Diary SaaS" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">&lt;50ms Sync</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Business Cockpit SaaS</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Dhanda Diary</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Execution cockpit with Daily Compliance Reports (DCR), ApexCharts KPI telemetry, and VAPID Push.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://dhandadiary.cloud/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/dhandadiary" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- ZoetiCoach AI -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/zoeticoach.jpg" alt="ZoetiCoach AI" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-cyan-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">-65% Dropout</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider">Coaching Accountability</span>
                                <h3 class="mt-1 text-xl font-bold text-white">ZoetiCoach AI</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">WhatsApp-first B2B2C coaching accountability SaaS with OpenAI RAG habit verification and event ledgers.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-cyan-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/zoeticoach" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Habuilt -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/habuilt.jpg" alt="Habuilt Platform" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-sky-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">99.99% Uptime</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-sky-400 uppercase tracking-wider">Habit Platform</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Habuilt</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">High-traffic atomic habit platform with 26-week progression tiers. Reduced server latencies by 70%.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-sky-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/habuilt" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-[#b8c9e633] bg-[#233246d9] py-8 text-center text-xs text-slate-400">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>

        <StickyMobileCta />
    </div>
</template>

<style scoped>
.site-bg {
    background:
        radial-gradient(1200px 700px at -10% -5%, rgba(122, 196, 255, 0.28), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(197, 147, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
