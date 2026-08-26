<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="Dhanda Diary Case Study — Operations & Compliance SaaS | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered a real-time business execution cockpit with offline-capable event logging, sub-50ms query latency, and automated ledger sync." />
        <meta property="og:title" content="Dhanda Diary Case Study — Operations & Compliance SaaS | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders engineered a real-time business execution cockpit with offline-capable event logging, sub-50ms query latency, and automated ledger sync." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/dhandadiary.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/dhandadiary" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Dhanda Diary Case Study — Operations SaaS | DigitalBuilders" />
        <meta name="twitter:description" content="High-concurrency operations cockpit and compliance management engine." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/dhandadiary.jpg" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="https://dhandadiary.cloud/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-emerald-500/20 border border-emerald-500/40 px-4 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/30 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · FinTech & Cloud SaaS</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    Dhanda Diary
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    Multi-Tenant Cloud Accounting & Daily Business Ledger Platform
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/dhandadiary.jpg"
                    alt="Dhanda Diary Execution Cockpit UI Snapshot"
                    class="w-full h-auto object-cover object-top"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">100%</p>
                    <p class="mt-1 text-xs text-slate-300">Daily Execution Accountability</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">&lt;50ms</p>
                    <p class="mt-1 text-xs text-slate-300">Reactive State Sync</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">ApexCharts</p>
                    <p class="mt-1 text-xs text-slate-300">Real-Time KPI Telemetry</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">Web Push</p>
                    <p class="mt-1 text-xs text-slate-300">Automated DCR Reminders</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Operational Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Growing businesses, agencies, and distributed teams struggled with scattered task management, inconsistent daily compliance, and lack of real-time visibility into executive KPIs. Founders were forced to manually chase team check-ins across multiple disconnected messaging apps and spreadsheets.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        We architected a unified, lightning-fast "Execution Cockpit" built on the modern VILT stack (Vue 3, Inertia.js, Laravel 13, Tailwind CSS).
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>Daily Compliance Reports (DCR):</strong> Structured morning goals and evening reflection check-ins with automated completion streaks.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>Interactive KPI Telemetry:</strong> High-precision ApexCharts dashboards tracking weekly revenue, pipeline conversions, and output metrics.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-400 shrink-0" />
                            <span><strong>Optimistic Kanban Boards:</strong> Drag-and-drop workflow task management with instant state mutations and zero server-roundtrip latency.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-amber-400 shrink-0" />
                            <span><strong>VAPID Web Push Automation:</strong> Browser-native push notification dispatch ensuring 85%+ on-time daily report submissions.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Laravel 13</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Vue 3</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Inertia.js</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">ApexCharts</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Google OAuth</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">VAPID Web Push</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "Dhanda Diary transformed daily execution and habit discipline for our entire business network. The platform is responsive, fast, and gives us instant visibility over every team metric with zero operational friction."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Harpreet Singh, Co-Founder, Dhanda Diary Cloud</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Building an Enterprise SaaS or Management Cockpit?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://dhandadiary.cloud/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50 inline-flex items-center gap-2">
                        Open Dhanda Diary Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-8 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Schedule Architecture Review
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
        radial-gradient(1200px 700px at -10% -5%, rgba(16, 185, 129, 0.22), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(122, 196, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
