<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Habuilt Case Study — DigitalBuilders">
        <meta name="description" content="How DigitalBuilders re-architected Habuilt's high-traffic platform to reduce server latencies by 70% and double database throughput." />
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
                        Estimate Similar Project
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center">
                <span class="db-chip">Case Study · Web Architecture</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    Habuilt Technologies
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    High-Traffic Platform Stabilized & Accelerated Through Architecture-First Engineering
                </p>
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-3 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">70%</p>
                    <p class="mt-1 text-xs text-slate-300">Server Latency Reduction</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#9ba7ff]">2x</p>
                    <p class="mt-1 text-xs text-slate-300">Database Query Throughput</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">99.99%</p>
                    <p class="mt-1 text-xs text-slate-300">System Availability</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Operational Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Habuilt was experiencing severe performance bottlenecks during peak traffic surges. The legacy codebase suffered from un-indexed database queries, monolithic thread blocking, and un-optimized asset pipelines that degraded user experience.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        DigitalBuilders implemented a Modular Monolith architecture. We separated domain modules, added Redis query caching, refactored N+1 database queries, and implemented Vite bundle optimization.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Laravel</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Vue 3</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">PostgreSQL</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Redis Caching</span>
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
                <h3 class="text-2xl font-black text-white">Facing Similar Bottlenecks?</h3>
                <a href="/#contact" class="mt-6 inline-block rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-8 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                    Book an Architecture Audit with Us
                </a>
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
