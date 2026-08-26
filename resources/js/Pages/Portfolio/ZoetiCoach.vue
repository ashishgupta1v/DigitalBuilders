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

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-emerald-500/20 border border-emerald-500/40 px-4 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/30 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · WhatsApp-First AI Coaching SaaS</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    ZoetiCoach AI
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    WhatsApp-Native Accountability SaaS, Autonomous RAG Verification & Event-Sourced Ledgers
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/zoeticoach.jpg"
                    alt="ZoetiCoach AI WhatsApp Coaching SaaS Platform Snapshot"
                    class="w-full h-auto object-cover object-top"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">65%</p>
                    <p class="mt-1 text-xs text-slate-300">Client Dropout Reduction</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">100%</p>
                    <p class="mt-1 text-xs text-slate-300">WhatsApp Check-in Automation</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">pgvector</p>
                    <p class="mt-1 text-xs text-slate-300">OpenAI RAG Habit Engine</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">4x</p>
                    <p class="mt-1 text-xs text-slate-300">Operational Velocity</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Accountability Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Professional fitness, mindset, and performance coaches lose over 40% of clients between weekly check-in calls due to lack of real-time accountability. Requiring clients to download separate mobile apps created friction and rapid abandonment.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        DigitalBuilders engineered a WhatsApp-native accountability SaaS where clients interact purely through natural chat, voice notes, and photo uploads with zero app installation required.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>OpenAI RAG Verification Pipeline:</strong> pgvector embeddings dynamically cross-reference submitted habit proof (photos, logs) against custom coach protocols without hallucinations.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>Event-Sourced Financial & Cohort Ledger:</strong> Immutable ledger recording client habits, subscription payments, and coach payout splits.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-400 shrink-0" />
                            <span><strong>Multi-Coach Portal:</strong> Executive Vue 3 dashboard providing coaches with cohort analytics, retention signals, and automated milestone flags.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Laravel 13</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Vue 3</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">pgvector</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">OpenAI RAG</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">WhatsApp Cloud API</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Modular Monolith</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "ZoetiCoach eliminates client drop-off completely. The AI verification over WhatsApp feels like having a personal assistant working 24/7 for every client. DigitalBuilders delivered ahead of schedule with zero legacy tech debt."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Rajesh Sharma, Managing Director, ZoetiCoach AI</p>
                </div>
            </div>

            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Building an AI Agent or WhatsApp-Native Platform?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50 inline-flex items-center gap-2">
                        Explore ZoetiCoach Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-8 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Schedule AI Architecture Review
                    </a>
                </div>
            </div>
        </main>

        <footer class="border-t border-[#b8c9e633] bg-[#233246d9] py-8 text-center text-xs text-slate-400">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
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
