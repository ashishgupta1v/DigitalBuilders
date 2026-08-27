<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="ZoetiCoach AI Case Study — WhatsApp-Native Coaching ERP & RAG Agent | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered a WhatsApp-first B2B2C accountability SaaS with OpenAI RAG habit verification, reducing client drop-off by 65%." />
        <meta property="og:title" content="ZoetiCoach AI Case Study — WhatsApp-Native Coaching ERP & RAG Agent | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders engineered a WhatsApp-first B2B2C accountability SaaS with OpenAI RAG habit verification, reducing client drop-off by 65%." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/zoeticoach.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/zoeticoach" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="ZoetiCoach AI Case Study — WhatsApp Coaching ERP | DigitalBuilders" />
        <meta name="twitter:description" content="WhatsApp-native habit verification SaaS powered by OpenAI RAG and event-sourced ledgers." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/zoeticoach.jpg" />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-secondary border border-border px-4 py-2 text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · WhatsApp-First AI Coaching SaaS</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl leading-tight">
                    ZoetiCoach AI
                </h1>
                <p class="mt-4 text-lg font-semibold text-sky-700 dark:text-sky-400">
                    WhatsApp-Native Accountability SaaS, Autonomous RAG Verification & Event-Sourced Ledgers
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-border shadow-2xl bg-card">
                <img
                    src="/images/portfolio/zoeticoach.webp"
                    alt="ZoetiCoach AI WhatsApp Coaching System Snapshot"
                    class="w-full h-auto object-cover object-top"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">65%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Client Dropout Reduction</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-sky-700 dark:text-sky-400">100%</p>
                    <p class="mt-1 text-xs text-muted-foreground">WhatsApp Check-in Automation</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">pgvector</p>
                    <p class="mt-1 text-xs text-muted-foreground">OpenAI RAG Habit Engine</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">4x</p>
                    <p class="mt-1 text-xs text-muted-foreground">Operational Velocity</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">The Accountability Challenge</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Professional fitness, mindset, and performance coaches lose over 40% of clients between weekly check-in calls due to lack of real-time accountability. Requiring clients to download separate mobile apps created friction and rapid abandonment.
                    </p>
                </div>

                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        DigitalBuilders engineered a WhatsApp-native accountability SaaS where clients interact purely through natural chat, voice notes, and photo uploads with zero app installation required.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-card-foreground">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0" />
                            <span><strong>OpenAI RAG Verification Pipeline:</strong> pgvector embeddings dynamically cross-reference submitted habit proof (photos, logs) against custom coach protocols without hallucinations.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500 shrink-0" />
                            <span><strong>Event-Sourced Financial & Cohort Ledger:</strong> Immutable ledger recording client habits, subscription payments, and coach payout splits.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-500 shrink-0" />
                            <span><strong>Multi-Coach Portal:</strong> Executive Vue 3 dashboard providing coaches with cohort analytics, retention signals, and automated milestone flags.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Laravel 13</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Vue 3</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">pgvector</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">OpenAI RAG</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">WhatsApp Cloud API</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Modular Monolith</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-border bg-secondary/60 p-8 shadow-md">
                    <blockquote class="text-base italic text-card-foreground sm:text-lg">
                        "ZoetiCoach eliminates client drop-off completely. The AI verification over WhatsApp feels like having a personal assistant working 24/7 for every client. DigitalBuilders delivered ahead of schedule with zero legacy tech debt."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-card-foreground">— Rajesh Sharma, Managing Director, ZoetiCoach AI</p>
                </div>
            </div>

            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-foreground">Building an AI Agent or WhatsApp-Native Platform?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-2">
                        Explore ZoetiCoach Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="btn-primary rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Schedule AI Architecture Review
                    </a>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
