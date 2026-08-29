<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { detectUserRegion, REGIONS, type RegionMode, saveUserRegion } from '@/utils/geo';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CookieConsent from '@/Components/CookieConsent.vue';

const page = usePage();
const serverGeoRegion = computed(() => (page.props as any).geo?.region as string | undefined);

const activeRegion = ref<RegionMode>('INR');

onMounted(() => {
    const detected = detectUserRegion(serverGeoRegion.value);
    activeRegion.value = detected;
});

function switchRegion(region: RegionMode) {
    activeRegion.value = region;
    saveUserRegion(region);
}

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'Customer Acquisition & Growth Engineering',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Engineering-led customer acquisition, technical SEO, AI content engines, WhatsApp lead automation, and conversion rate optimization.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'Growth & Customer Acquisition Retainers',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Grow Starter Retainer',
                },
                priceCurrency: 'INR',
                price: '14999',
            },
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Grow Business Retainer',
                },
                priceCurrency: 'INR',
                price: '29999',
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
        { '@type': 'ListItem', position: 3, name: 'Growth & Marketing', item: 'https://www.digitalbuilders.in/services/growth' },
    ],
};
</script>

<template>
    <Head title="Strategic Growth, Technical SEO & Customer Acquisition — DigitalBuilders">
        <meta name="description" content="We don't just build your software — we help you get customers to it. Technical SEO, AI content engines, WhatsApp lead nurture, and performance ads." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/growth" />
        <meta property="og:title" content="Strategic Growth & Customer Acquisition | DigitalBuilders" />
        <meta property="og:description" content="Great software is step one. Getting customers is step two. We help your website, app, or store actually get seen, get enquiries, and turn them into paying customers." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/growth" />
        <meta name="twitter:card" content="summary_large_image" />
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(serviceSchema) }}
        </component>
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(breadcrumbSchema) }}
        </component>
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <!-- Accessible Skip to Main Content Link -->
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
                    <Link href="/pricing" class="hidden text-sm font-medium text-muted-foreground hover:text-foreground md:inline-flex items-center min-h-[44px] px-2">Pricing</Link>
                    <Link href="/book" class="btn-primary inline-flex items-center justify-center rounded-full px-4 py-2 min-h-[44px] text-xs font-semibold text-white shadow transition hover:scale-105">
                        Free Growth Check
                    </Link>
                </nav>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">GROW · GET CUSTOMERS TO WHAT WE BUILD</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl lg:text-6xl leading-tight">
                    Great software is step one.<br class="hidden sm:inline" /> Getting customers is step two.
                </h1>
                <p class="mt-6 text-base text-muted-foreground sm:text-lg leading-relaxed max-w-3xl mx-auto">
                    We help your website, app, or store actually get seen, capture high-intent enquiries, and turn them into paying customers — measured transparently every month, with zero marketing fluff.
                </p>

                <!-- Launch Bundle Callout Banner -->
                <div class="mt-8 p-4 rounded-2xl bg-gradient-to-r from-sky-500/15 via-indigo-500/15 to-purple-500/15 border border-sky-500/30 text-xs sm:text-sm font-semibold text-foreground flex flex-col sm:flex-row items-center justify-center gap-2 shadow-sm">
                    <span class="text-sky-600 dark:text-sky-400 font-bold">🎁 Launch Bundle Special:</span>
                    <span>Pair any Build package with 3 months of Grow and get <strong>1 Month of Grow completely FREE</strong>.</span>
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <Link href="/book" class="btn-primary rounded-full px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.02] inline-flex items-center gap-2">
                        <span>Book a Free 15-Min Growth Check</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <a href="https://wa.me/919087021592?text=Hi%20Ashish,%20I'd%20like%20to%20discuss%20a%20Grow%20customer%20acquisition%20retainer%20for%20my%20business." target="_blank" rel="noopener noreferrer" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-500 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                        <span>Chat on WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- The 5 Plain-Language Grow Pillars -->
            <div class="mt-16 sm:mt-24">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <span class="db-badge-indigo mb-2">The 5 Growth Systems</span>
                    <h2 class="text-2xl sm:text-4xl font-black text-foreground">How We Bring Customers to Your Platform</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Engineering-driven customer acquisition systems that build predictable organic and paid pipeline.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Pillar 1 -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xl mb-4 border border-sky-500/20">
                                🔍
                            </div>
                            <span class="text-xs uppercase font-extrabold tracking-wider text-sky-700 dark:text-sky-400">Pillar 01</span>
                            <h3 class="mt-1 text-xl font-bold text-foreground">Get Found on Google</h3>
                            <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                                When someone in your area or industry searches for what you sell, you should show up. We configure your Google Business profile, local citations, and on-page schema so the right buyers find you first.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-border space-y-1.5 text-xs text-foreground/90">
                            <p><strong class="text-sky-600 dark:text-sky-400">You get:</strong> Google Business setup · Local SEO citations · JSON-LD schema · Monthly Search Console rank report.</p>
                            <p class="font-mono text-[11px] text-muted-foreground">Under the hood: Technical & Content SEO</p>
                        </div>
                    </div>

                    <!-- Pillar 2 -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl mb-4 border border-indigo-500/20">
                                ✍️
                            </div>
                            <span class="text-xs uppercase font-extrabold tracking-wider text-indigo-700 dark:text-indigo-400">Pillar 02</span>
                            <h3 class="mt-1 text-xl font-bold text-foreground">AI Content Engine</h3>
                            <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                                Blogs, product guides, and case studies written with our AI pipeline and reviewed by human editors. Keep your brand ranking and publishing consistently without hiring an expensive writing team.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-border space-y-1.5 text-xs text-foreground/90">
                            <p><strong class="text-indigo-600 dark:text-indigo-400">You get:</strong> 4 to 8 keyword-targeted articles/mo · Automated metadata · Case study teardowns · Social snippets.</p>
                            <p class="font-mono text-[11px] text-muted-foreground">Under the hood: AI-Leveraged Editorial Pipeline</p>
                        </div>
                    </div>

                    <!-- Pillar 3 -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xl mb-4 border border-purple-500/20">
                                🎯
                            </div>
                            <span class="text-xs uppercase font-extrabold tracking-wider text-purple-700 dark:text-purple-400">Pillar 03</span>
                            <h3 class="mt-1 text-xl font-bold text-foreground">Run Ads That Bring Leads</h3>
                            <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                                Google Search and Meta Performance campaigns set up and managed to bring qualified enquiries — you only pay platforms for the real clicks, with zero wasted budget.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-border space-y-1.5 text-xs text-foreground/90">
                            <p><strong class="text-purple-600 dark:text-purple-400">You get:</strong> High-intent keyword targeting · Ad creative copy · Conversion API (CAPI) tracking · Weekly bid optimization.</p>
                            <p class="font-mono text-[11px] text-muted-foreground">Under the hood: Dedicated Media Buyer + CAPI</p>
                        </div>
                    </div>

                    <!-- Pillar 4 -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl mb-4 border border-emerald-500/20">
                                💬
                            </div>
                            <span class="text-xs uppercase font-extrabold tracking-wider text-emerald-700 dark:text-emerald-400">Pillar 04</span>
                            <h3 class="mt-1 text-xl font-bold text-foreground">Never Lose a Lead</h3>
                            <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                                Automated follow-ups on WhatsApp and email so every website lead gets an immediate reply in seconds — even at 2 AM — before they reach out to a competitor.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-border space-y-1.5 text-xs text-foreground/90">
                            <p><strong class="text-emerald-600 dark:text-emerald-400">You get:</strong> WhatsApp Cloud API bot · Instant lead qualification · Staff push alerts · Multi-step email nurture drips.</p>
                            <p class="font-mono text-[11px] text-muted-foreground">Under the hood: CRM & WhatsApp Cloud Webhooks</p>
                        </div>
                    </div>

                    <!-- Pillar 5 -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between group md:col-span-2 lg:col-span-2">
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl mb-4 border border-amber-500/20">
                                📈
                            </div>
                            <span class="text-xs uppercase font-extrabold tracking-wider text-amber-700 dark:text-amber-400">Pillar 05</span>
                            <h3 class="mt-1 text-xl font-bold text-foreground">Turn Visitors Into Enquiries</h3>
                            <p class="mt-2 text-sm text-muted-foreground leading-relaxed max-w-2xl">
                                Getting traffic is only half the battle. We engineer fast, dedicated landing pages with smart enquiry forms, heatmaps, and conversion tracking that turn visitors into booked phone calls and demo requests.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-border space-y-1.5 text-xs text-foreground/90">
                            <p><strong class="text-amber-600 dark:text-amber-400">You get:</strong> Custom high-converting landing pages · A/B headline split tests · Heatmap & scroll tracking · Core Web Vitals <0.5s.</p>
                            <p class="font-mono text-[11px] text-muted-foreground">Under the hood: Conversion Rate Optimization (CRO) + Plausible Telemetry</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retainer Pricing Section -->
            <div class="mt-20 sm:mt-28">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-border pb-6">
                    <div>
                        <span class="db-chip">Monthly Retainers (MRR)</span>
                        <h2 class="mt-2 text-2xl sm:text-4xl font-black text-foreground">Transparent Growth Plans</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Choose the growth tier that fits your stage. Cancel anytime at renewal with zero lock-in.</p>
                    </div>

                    <!-- Currency Switcher -->
                    <div class="p-1 rounded-full bg-secondary border border-border inline-flex items-center gap-1 self-start sm:self-auto">
                        <button
                            type="button"
                            @click="switchRegion('INR')"
                            class="px-3 py-1 text-xs font-bold rounded-full transition-colors cursor-pointer"
                            :class="activeRegion === 'INR' ? 'btn-primary text-white shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            🇮🇳 India (₹)
                        </button>
                        <button
                            type="button"
                            @click="switchRegion('GULF')"
                            class="px-3 py-1 text-xs font-bold rounded-full transition-colors cursor-pointer"
                            :class="activeRegion === 'GULF' ? 'btn-primary text-white shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            🇦🇪 Gulf ($)
                        </button>
                        <button
                            type="button"
                            @click="switchRegion('USD')"
                            class="px-3 py-1 text-xs font-bold rounded-full transition-colors cursor-pointer"
                            :class="activeRegion === 'USD' ? 'btn-primary text-white shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            🌍 Global ($)
                        </button>
                    </div>
                </div>

                <!-- 3 Retainer Cards Grid -->
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <!-- Starter -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-foreground">Grow Starter</h3>
                                <span class="rounded-full bg-secondary border border-border px-2.5 py-0.5 text-[10px] font-semibold text-muted-foreground">Organic Focus</span>
                            </div>
                            <div class="mt-4 font-mono text-3xl font-black text-foreground">
                                <span v-if="activeRegion === 'INR'">₹14,999 <span class="text-xs font-normal text-muted-foreground">/ mo + GST</span></span>
                                <span v-else-if="activeRegion === 'GULF'">$349 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                                <span v-else>$449 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground">Best for getting found locally and building consistent organic search traffic.</p>
                            <ul class="mt-6 space-y-2.5 text-xs text-foreground/90">
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Google Business setup & Local SEO</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> AI Content Engine (4 articles/month)</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Core Web Vitals & schema fixes</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Monthly Search Console audit & report</li>
                            </ul>
                        </div>
                        <a :href="`/#contact?service=grow_starter&region=${encodeURIComponent(activeRegion.toLowerCase())}`" class="mt-8 inline-flex w-full items-center justify-center rounded-xl border border-border bg-secondary py-3 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-colors">
                            Select Grow Starter
                        </a>
                    </div>

                    <!-- Business ★ -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between border-2 border-sky-500 bg-sky-50/40 dark:bg-sky-950/20 shadow-xl relative">
                        <div class="absolute -top-3 right-6 rounded-full bg-sky-500 px-3 py-0.5 text-[10px] font-bold text-white shadow-sm">
                            Most Popular ★
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-foreground">Grow Business</h3>
                                <span class="rounded-full bg-sky-500/20 px-2.5 py-0.5 text-[10px] font-bold text-sky-700 dark:text-sky-300">Paid + Organic</span>
                            </div>
                            <div class="mt-4 font-mono text-3xl font-black text-foreground">
                                <span v-if="activeRegion === 'INR'">₹29,999 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo + GST</span></span>
                                <span v-else-if="activeRegion === 'GULF'">$699 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo</span></span>
                                <span v-else>$899 <span class="text-xs font-normal text-sky-700 dark:text-sky-300">/ mo</span></span>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground">Best for actively bringing customer enquiries via managed Google/Meta ads and automated follow-ups.</p>
                            <ul class="mt-6 space-y-2.5 text-xs text-foreground/90">
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Everything in Starter plan</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Google Search & Meta Ads active management</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> 1 High-converting landing page / month</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> WhatsApp & email automated follow-up bot</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> 8 AI-assisted content pieces/month</li>
                            </ul>
                        </div>
                        <a :href="`/#contact?service=grow_business&region=${encodeURIComponent(activeRegion.toLowerCase())}`" class="mt-8 inline-flex w-full items-center justify-center rounded-xl btn-primary py-3 text-xs font-bold text-white shadow-md">
                            Select Grow Business
                        </a>
                    </div>

                    <!-- Enterprise -->
                    <div class="db-bento-card p-6 sm:p-8 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-foreground">Grow Enterprise</h3>
                                <span class="rounded-full bg-secondary border border-border px-2.5 py-0.5 text-[10px] font-semibold text-muted-foreground">Full-Funnel</span>
                            </div>
                            <div class="mt-4 font-mono text-3xl font-black text-foreground">
                                <span v-if="activeRegion === 'INR'">From ₹59,999 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                                <span v-else-if="activeRegion === 'GULF'">From $1,399 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                                <span v-else>From $1,799 <span class="text-xs font-normal text-muted-foreground">/ mo</span></span>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground">Best for established enterprises scaling aggressive multi-channel funnels and custom analytics.</p>
                            <ul class="mt-6 space-y-2.5 text-xs text-foreground/90">
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Everything in Business plan</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Full-funnel CRO & multi-channel campaigns</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Custom real-time Acquisition Dashboard (ApexCharts)</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Dedicated Growth Strategist & bi-weekly syncs</li>
                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✓</span> Competitor keyword & funnel reverse-engineering</li>
                            </ul>
                        </div>
                        <a :href="`/#contact?service=grow_enterprise&region=${encodeURIComponent(activeRegion.toLowerCase())}`" class="mt-8 inline-flex w-full items-center justify-center rounded-xl border border-border bg-secondary py-3 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-colors">
                            Custom Growth Strategy
                        </a>
                    </div>
                </div>

                <!-- Honest Ad Spend Notice -->
                <div class="mt-6 p-4 rounded-2xl border border-border bg-card/60 text-xs text-muted-foreground text-center">
                    💡 <strong class="text-foreground">Honest transparency:</strong> Platform ad spend is billed directly by Google/Meta to your company account — you retain 100% ownership of your data and ad accounts. Retainers cover strategy, setup, creative copy, CAPI tracking, and weekly optimization.
                </div>
            </div>

            <!-- À-la-carte One-Time Growth Services -->
            <div class="mt-16 sm:mt-20">
                <h3 class="text-xl font-bold text-foreground mb-4">À-la-Carte Growth Modules (One-Time)</h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="p-4 rounded-2xl border border-border bg-card/80 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-foreground">Technical SEO Audit</h4>
                            <p class="mt-1 text-xs text-muted-foreground">Deep crawl, schema markup, and speed bottleneck audit.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between font-mono text-xs font-bold text-sky-600 dark:text-sky-400">
                            <span>{{ activeRegion === 'INR' ? '₹9,999' : '$299' }}</span>
                            <span class="text-[10px] text-muted-foreground font-sans">One-Time</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-border bg-card/80 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-foreground">AI Content Pack</h4>
                            <p class="mt-1 text-xs text-muted-foreground">10 keyword-mapped, human-polished blog articles.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between font-mono text-xs font-bold text-sky-600 dark:text-sky-400">
                            <span>{{ activeRegion === 'INR' ? '₹7,999' : '$249' }}</span>
                            <span class="text-[10px] text-muted-foreground font-sans">One-Time</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-border bg-card/80 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-foreground">Ad Campaign Setup</h4>
                            <p class="mt-1 text-xs text-muted-foreground">Google/Meta account setup, CAPI tracking & initial copy.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between font-mono text-xs font-bold text-sky-600 dark:text-sky-400">
                            <span>{{ activeRegion === 'INR' ? '₹14,999' : '$449' }}</span>
                            <span class="text-[10px] text-muted-foreground font-sans">One-Time</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-border bg-card/80 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-foreground">WhatsApp CRM Bot</h4>
                            <p class="mt-1 text-xs text-muted-foreground">WhatsApp Cloud API automated qualification funnel.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between font-mono text-xs font-bold text-sky-600 dark:text-sky-400">
                            <span>{{ activeRegion === 'INR' ? '₹19,999' : '$599' }}</span>
                            <span class="text-[10px] text-muted-foreground font-sans">One-Time</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-border bg-card/80 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-foreground">Landing Page CRO</h4>
                            <p class="mt-1 text-xs text-muted-foreground">1 Custom high-converting landing page with analytics.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between font-mono text-xs font-bold text-sky-600 dark:text-sky-400">
                            <span>{{ activeRegion === 'INR' ? '₹12,999' : '$399' }}</span>
                            <span class="text-[10px] text-muted-foreground font-sans">One-Time</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action Banner -->
            <div class="mt-20 rounded-3xl border border-sky-500/30 bg-gradient-to-br from-card via-card to-sky-500/10 p-8 sm:p-12 text-center max-w-4xl mx-auto shadow-xl">
                <span class="db-chip">No-Obligation Discovery</span>
                <h2 class="mt-3 text-2xl sm:text-4xl font-black text-foreground">
                    Not sure where to start?
                </h2>
                <p class="mt-3 text-sm sm:text-base text-muted-foreground max-w-xl mx-auto leading-relaxed">
                    Book a free 15-minute growth check. We will audit your current website visibility, keyword rankings, and lead capture process and give you actionable next steps.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <Link href="/book" class="btn-primary rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-105 inline-flex items-center gap-2">
                        <span>Book Your Free Growth Check</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <Link href="/estimator" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-2">
                        <span>Calculate Scope on Estimator</span>
                    </Link>
                </div>
            </div>
        </main>

        <StickyMobileCta />
        <CookieConsent />
    </div>
</template>
