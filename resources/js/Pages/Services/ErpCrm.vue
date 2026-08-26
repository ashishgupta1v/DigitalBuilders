<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'Enterprise ERP & Custom CRM Development',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Custom ERP and CRM operations engines engineered to unify inventory, logistics, sales pipelines, and customer support.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'Enterprise ERP & CRM Packages',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Launch Custom ERP / CRM System',
                },
                priceCurrency: 'INR',
                price: '299000',
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
        { '@type': 'ListItem', position: 3, name: 'Enterprise ERP & CRM', item: 'https://www.digitalbuilders.in/services/erp-crm' },
    ],
};
</script>

<template>
    <Head title="Enterprise ERP & CRM Systems — DigitalBuilders">
        <meta name="description" content="Custom ERP and CRM operations engines engineered to unify inventory, logistics, sales pipelines, and customer support." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/erp-crm" />
        <meta property="og:title" content="Enterprise ERP & Custom CRM Engineering | DigitalBuilders" />
        <meta property="og:description" content="Unify fragmented operational spreadsheets and software into a high-performance custom ERP/CRM dashboard." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/dhandadiary.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/erp-crm" />
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
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Estimate ERP Scope
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 04</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl lg:text-6xl leading-tight">
                    Enterprise ERP & CRM Software
                </h1>
                <p class="mt-6 text-base text-slate-300 sm:text-lg leading-relaxed">
                    Unify fragmented operational spreadsheets and legacy software into a single custom dashboard tailored to your company's exact operational workflows.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-6 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Consult on ERP Needs
                    </a>
                    <Link href="/estimator" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50">
                        Calculate ERP Scope
                    </Link>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">📦</div>
                    <h3 class="text-xl font-bold text-white">Inventory & Supply Chain</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Real-time stock tracking, multi-warehouse support, automated re-ordering signals.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">🎯</div>
                    <h3 class="text-xl font-bold text-white">Custom Lead Pipeline</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Kanban deal stages, automated email follow-ups, representative performance metrics.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">📊</div>
                    <h3 class="text-xl font-bold text-white">Executive BI Dashboards</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Real-time revenue, profit margin, operational bottlenecks, and automated CSV/PDF export.</p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-[#9dc5ff] font-bold">Custom Workflows In Production</span>
                    <h2 class="mt-2 text-2xl font-black text-white sm:text-4xl">Featured ERP & CRM Systems</h2>
                    <p class="mt-3 text-sm text-slate-300">Custom business operational systems replacing messy spreadsheets with automated execution pipelines.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Dhanda Diary -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/dhandadiary.jpg" alt="Dhanda Diary" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">DCR Compliance</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Business Execution CRM</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Dhanda Diary</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Centralized business management cockpit with daily routines, ApexCharts KPI trackers, and Kanban boards.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://dhandadiary.cloud/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/dhandadiary" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Garg Enterprises -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gargenterprises.jpg" alt="Garg Enterprises" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-indigo-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Dealer Ledger</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Distribution ERP</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Garg Enterprises</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Wholesale dealer credit ledger, outstanding balance sync, and dispatch management across 10,000+ SKUs.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <Link href="/portfolio/garg-enterprises" class="text-xs font-bold text-indigo-300 hover:text-white flex items-center gap-1">Read Case Study ↗</Link>
                                <a href="/#contact" class="text-xs font-bold text-slate-300 hover:text-white">Request Demo →</a>
                            </div>
                        </div>
                    </div>

                    <!-- Krishan Balram Gaushala -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gaushala.jpg" alt="Gaushala Connect" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-amber-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">80G Tax Engine</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Trust & Seva ERP</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Krishan Balram Gaushala</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Integrated donor management with instant 80G tax receipt PDF generation and automated WhatsApp blessings.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://krishanbalramgaushala.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-amber-300 hover:text-white flex items-center gap-1">Visit Live Portal ↗</a>
                                <Link href="/portfolio/gaushala" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
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
