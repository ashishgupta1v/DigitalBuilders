<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'Enterprise ERP & CRM Software Development',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Custom enterprise ERP and CRM systems tailored to unique business operations, supply chain workflows, and executive analytics.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'ERP & CRM Development Packages',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Enterprise ERP System',
                },
                priceCurrency: 'INR',
                price: '199000',
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
        { '@type': 'ListItem', position: 3, name: 'ERP & CRM Systems', item: 'https://www.digitalbuilders.in/services/erp-crm' },
    ],
};
</script>

<template>
    <Head title="Enterprise ERP & CRM Software — DigitalBuilders">
        <meta name="description" content="Custom enterprise ERP and CRM systems tailored to your unique operational workflows, supply chain, and executive analytics." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/erp-crm" />
        <meta property="og:title" content="Custom Enterprise ERP & CRM Software | DigitalBuilders" />
        <meta property="og:description" content="Unify fragmented operational spreadsheets and legacy software into a single custom dashboard tailored to your company's exact operational workflows." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/gargenterprises.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/erp-crm" />
        <meta name="twitter:card" content="summary_large_image" />
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(serviceSchema) }}
        </component>
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(breadcrumbSchema) }}
        </component>
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

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <nav aria-label="Primary navigation" class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground min-h-[44px] inline-flex items-center px-2">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full btn-primary px-4 py-2 min-h-[44px] text-xs font-semibold text-white sm:inline-flex items-center justify-center">
                        Estimate ERP Scope
                    </Link>
                </nav>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 04</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl lg:text-6xl leading-tight">
                    Enterprise ERP & CRM Software
                </h1>
                <p class="mt-6 text-base text-muted-foreground sm:text-lg leading-relaxed">
                    Unify fragmented operational spreadsheets and legacy software into a single custom dashboard tailored to your company's exact operational workflows.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="btn-primary rounded-full px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Consult on ERP Needs
                    </a>
                    <Link href="/estimator" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80">
                        Calculate ERP Scope
                    </Link>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">📦</div>
                    <h3 class="text-xl font-bold text-card-foreground">Inventory & Supply Chain</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Real-time stock tracking, multi-warehouse support, automated re-ordering signals.</p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">🎯</div>
                    <h3 class="text-xl font-bold text-card-foreground">Custom Lead Pipeline</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Kanban deal stages, automated email follow-ups, representative performance metrics.</p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">📊</div>
                    <h3 class="text-xl font-bold text-card-foreground">Executive BI Dashboards</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Real-time revenue, profit margin, operational bottlenecks, and automated CSV/PDF export.</p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-sky-700 dark:text-sky-400 font-bold">Custom Workflows In Production</span>
                    <h2 class="mt-2 text-2xl font-black text-foreground sm:text-4xl">Featured ERP & CRM Systems</h2>
                    <p class="mt-3 text-sm text-muted-foreground">Custom business operational systems replacing messy spreadsheets with automated execution pipelines.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Dhanda Diary -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/dhandadiary.webp" alt="Dhanda Diary" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">ERP Ledger</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Business Ledger SaaS</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Dhanda Diary</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Multi-tenant business ERP with automated DCR routines, ApexCharts KPIs, and discipline streak multipliers.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://dhandadiary.cloud/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/dhandadiary" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Garg Enterprises -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gargenterprises.webp" alt="Garg Enterprises" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-sky-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">10k+ SKUs</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-sky-700 dark:text-sky-400 uppercase tracking-wider">B2B Wholesale Ordering</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Garg Enterprises</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Android wholesale ordering app with offline drafting, dealer credit reconciliation, and 1-tap GST PDFs.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <span class="text-xs font-bold text-muted-foreground">Android Native</span>
                                <Link href="/portfolio/garg-enterprises" class="text-xs font-bold text-sky-700 dark:text-sky-400 hover:text-sky-600">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Krishan Balram Gaushala -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gaushala.webp" alt="Gaushala Connect" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-amber-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">80G Tax Engine</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Trust & Seva ERP</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Krishan Balram Gaushala</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Integrated donor management with instant 80G tax receipt PDF generation and automated WhatsApp blessings.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://krishanbalramgaushala.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-amber-700 dark:text-amber-300 hover:underline flex items-center gap-1">Visit Live Portal ↗</a>
                                <Link href="/portfolio/gaushala" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
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
