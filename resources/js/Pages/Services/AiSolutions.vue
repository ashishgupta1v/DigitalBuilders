<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'AI Voice Agents & Agentic Workflows',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Autonomous AI voice bots, RAG pipelines, and WhatsApp agentic workflows with sub-300ms latency and CRM integration.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'AI Agent & RAG Pipeline Packages',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'AI Agent Integration',
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
        { '@type': 'ListItem', position: 3, name: 'AI Voice Agents & Workflows', item: 'https://www.digitalbuilders.in/services/ai-solutions' },
    ],
};
</script>

<template>
    <Head title="AI Voice Agents & Agentic Workflows — DigitalBuilders">
        <meta name="description" content="Autonomous AI voice bots, RAG pipelines, and WhatsApp agentic workflows engineered for sub-300ms response times." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/ai-solutions" />
        <meta property="og:title" content="AI Voice Agents & Agentic Workflows | DigitalBuilders" />
        <meta property="og:description" content="Transform manual operations into 24/7 autonomous intelligence with custom WebRTC voice bots, retrieval-augmented generation, and smart CRM triggers." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/zoeticoach.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/ai-solutions" />
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

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full btn-primary px-4 py-2 text-xs font-semibold text-white sm:inline-flex">
                        Estimate AI Scope
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 03</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl lg:text-6xl leading-tight">
                    AI Voice Agents & Agentic Workflows
                </h1>
                <p class="mt-6 text-base text-muted-foreground sm:text-lg leading-relaxed">
                    Transform manual support & sales operations into 24/7 autonomous intelligence. We build custom WebRTC voice bots, retrieval-augmented generation (RAG), and smart CRM triggers.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="btn-primary rounded-full px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Build Your AI Agent
                    </a>
                    <Link href="/estimator" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80">
                        Calculate AI Scope
                    </Link>
                </div>
            </div>

            <!-- Capabilities -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">🎙️</div>
                    <h3 class="text-xl font-bold text-card-foreground">Sub-300ms Voice Latency</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Human-like conversational voice agents using streaming WebRTC STT and TTS engines.</p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">🧠</div>
                    <h3 class="text-xl font-bold text-card-foreground">Enterprise RAG Pipelines</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Query your private database, PDFs, and ERP records accurately without data hallucination.</p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">⚡</div>
                    <h3 class="text-xl font-bold text-card-foreground">Automated Actions</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">AI agents execute actual business actions: log leads, book calendar slots, query shipment status.</p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-sky-700 dark:text-sky-400 font-bold">Autonomous Systems Delivered</span>
                    <h2 class="mt-2 text-2xl font-black text-foreground sm:text-4xl">Featured AI & Automated Implementations</h2>
                    <p class="mt-3 text-sm text-muted-foreground">Production AI pipelines driving quantifiable retention, diagnostics intake, and automated user engagement.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- ZoetiCoach AI -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/zoeticoach.jpg" alt="ZoetiCoach AI" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-cyan-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">RAG + pgvector</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">AI Accountability Agent</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">ZoetiCoach AI</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">OpenAI RAG agent processing natural-language WhatsApp voice/text check-ins, reducing client dropout by 65%.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/zoeticoach" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- GutTalks AI Intake -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/guttalks.jpg" alt="GutTalks Clinical Intake" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Automated Intake</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Clinical Telehealth</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">GutTalks Diagnostics</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Intelligent medical intake routing matching patients to Root Rx doctors and sequencing test kits.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://guttalks.in/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/guttalks" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- GauSeva Connect Meta Cloud API -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gaushala.jpg" alt="GauSeva Connect" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-amber-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Meta Cloud Webhook</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Automated Devotee CRM</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">GauSeva Connect</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Scheduled Meta WhatsApp Cloud API webhooks delivering personalized temple blessings on donor birthdays/anniversaries.</p>
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
