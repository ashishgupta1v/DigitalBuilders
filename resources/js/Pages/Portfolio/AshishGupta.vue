<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Ashish Gupta Hub Case Study — Senior Full-Stack Architect Showcase | DigitalBuilders">
        <meta name="description" content="High-performance engineering showcase demonstrating Domain-Driven Design (DDD), legacy modernization, and $1M+ cloud savings on the VILT stack." />
        <meta property="og:title" content="Ashish Gupta Hub Case Study — Senior Full-Stack Architect Showcase | DigitalBuilders" />
        <meta property="og:description" content="High-performance engineering showcase demonstrating Domain-Driven Design (DDD), legacy modernization, and $1M+ cloud savings on the VILT stack." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/ashishgupta.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/ashishgupta" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Ashish Gupta Hub Case Study — Architecture Showcase | DigitalBuilders" />
        <meta name="twitter:description" content="Senior Full-Stack Architect portfolio demonstrating Domain-Driven Design and $1M+ cloud savings." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/ashishgupta.jpg" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="https://ashishgupta.dev/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-sky-500/20 border border-sky-500/40 px-4 py-2 text-xs font-semibold text-sky-300 hover:bg-sky-500/30 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · Enterprise Architecture & VILT Stack</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    Ashish Gupta Architecture Hub
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    Senior Full-Stack Architect Showcase, Domain-Driven Design & Enterprise Modernization
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/ashishgupta.jpg"
                    alt="Ashish Gupta Portfolio & Architecture Hub Snapshot"
                    class="w-full h-auto object-cover object-top"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">9+ Yrs</p>
                    <p class="mt-1 text-xs text-slate-300">Enterprise IT Experience</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">$1M+</p>
                    <p class="mt-1 text-xs text-slate-300">Cloud Cost Savings Delivered</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">&lt;0.5s</p>
                    <p class="mt-1 text-xs text-slate-300">Initial Page Load Benchmark</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">VILT</p>
                    <p class="mt-1 text-xs text-slate-300">Vue 3, Inertia, Laravel, Tailwind</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Engineering Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Enterprise clients and technical founders needed transparent, verified proof of modern architectural competence — specifically breaking down monolithic legacy systems in healthcare, aviation, and e-commerce into high-resilience, cost-efficient modular engines.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        DigitalBuilders engineered a high-velocity VILT stack hub featuring live GitHub metrics, interactive architecture models, visitor telemetry, and an interactive terminal console.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>Domain-Driven Design (DDD):</strong> Clear bounded contexts, repository layers, and transactional outbox patterns.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>Zero-Bloat Performance:</strong> Self-hosted typography, SVG telemetry charts, and sub-0.5s time-to-interactive.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-400 shrink-0" />
                            <span><strong>PWA Offline Support:</strong> Service worker caching guaranteeing instant loads on high-latency mobile networks.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Laravel 12/13</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Vue 3</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Inertia.js</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Tailwind CSS</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Filament CMS</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">PWA Offline</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "Engineering excellence is about building scalable, resilient systems that eliminate complexity. Every application in our portfolio is engineered for zero technical debt and high business impact."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Ashish Gupta, Lead Architect & Founder, DigitalBuilders</p>
                </div>
            </div>

            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Need an Enterprise Software Architect?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://ashishgupta.dev/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50 inline-flex items-center gap-2">
                        Visit ashishgupta.dev
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
        radial-gradient(1200px 700px at -10% -5%, rgba(122, 196, 255, 0.28), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(197, 147, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
