<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Habuilt Case Study — 50 Habits, 4 Tiers & 99.99% Uptime | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders stabilized Habuilt's high-traffic wellness platform, reducing server latency by 70% with 26-week progression tiers and mobile deep linking." />
        <meta property="og:title" content="Habuilt Case Study — 50 Habits, 4 Progression Tiers & 99.99% Uptime | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders stabilized Habuilt's high-traffic wellness platform, reducing server latency by 70% with 26-week progression tiers and mobile deep linking." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/habuilt" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Habuilt Case Study — 50 Habits, 4 Progression Tiers | DigitalBuilders" />
        <meta name="twitter:description" content="High-traffic atomic habit platform stabilized with 70% lower latency and 99.99% uptime." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-sky-500/20 border border-sky-500/40 px-4 py-2 text-xs font-semibold text-sky-300 hover:bg-sky-500/30 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center">
                <span class="db-chip">Case Study · High-Scale Habit & Wellness SaaS</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    Habuilt Technologies
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    50 Habits · 4 Progression Tiers · 26-Week Atomic Discipline Engine
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/habuilt.jpg"
                    alt="Habuilt Atomic Habits Platform Snapshot"
                    class="w-full h-auto object-cover object-top"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">50 Habits</p>
                    <p class="mt-1 text-xs text-slate-300">4 Progression Tiers</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">70%</p>
                    <p class="mt-1 text-xs text-slate-300">Server Latency Reduction</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">2x</p>
                    <p class="mt-1 text-xs text-slate-300">Database Query Throughput</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">99.99%</p>
                    <p class="mt-1 text-xs text-slate-300">System Availability</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Habit Progression Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Habuilt needed a high-volume atomic habit building architecture based on the "1% better every day" methodology. As user engagement scaled rapidly, the legacy database suffered from lock contention during daily peak check-in windows, and mobile app deep linking required seamless zero-friction handshakes.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        DigitalBuilders engineered a high-throughput progressive tier unlock engine (Foundation, Build, Refine, Mastery) with sub-50ms streak XP computations, Redis query caching, and universal mobile deep linking protocols.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>4-Tier 26-Week Progression:</strong> Structured roadmap unlocking atomic micro-habits based on validated streaks and user XP.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>Universal Deep Linking:</strong> Seamless auth callback handler (habuilt://auth/callback) bridging mobile web sessions to native mobile apps.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-400 shrink-0" />
                            <span><strong>Multi-Tier Redis Caching:</strong> Sub-50ms user state retrieval eliminating database bottlenecks during morning and evening rush hours.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Next.js / Vue 3</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">TypeScript</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Tailwind CSS</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Redis Caching</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Mobile Deep Linking</span>
                    </div>
                </div>

                <!-- Verified Quote -->
                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "DigitalBuilders re-architected our high-traffic platform from the ground up. Ashish brought Silicon Valley-level engineering standards. Our server latencies dropped by 70% and database throughput doubled within weeks."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Gurpreet Singh, CTO & Co-Founder, Habuilt Technologies</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Building a High-Growth Consumer Wellness Platform?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50 inline-flex items-center gap-2">
                        Visit Habuilt Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-8 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Book an Architecture Audit
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
