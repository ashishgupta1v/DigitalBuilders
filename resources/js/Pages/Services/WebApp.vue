<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Custom Web Application Development — DigitalBuilders">
        <meta name="description" content="Blazing-fast, enterprise-grade web applications built with Laravel 13, Vue 3, Inertia, and Modular Monolith architecture." />
        <meta property="og:title" content="Custom Web Application Development | DigitalBuilders" />
        <meta property="og:description" content="Blazing-fast, enterprise-grade web applications engineered with modular monolith architecture and sub-100ms response times." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/web-applications" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Estimate Project Cost
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 01</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl lg:text-6xl leading-tight">
                    Custom Web Application Development
                </h1>
                <p class="mt-6 text-base text-slate-300 sm:text-lg leading-relaxed">
                    Stop settling for fragile templates. We engineer custom modular monoliths that handle millions of requests with sub-100ms latency, zero tech debt, and bulletproof security.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-6 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Schedule Architecture Review
                    </a>
                    <Link href="/estimator" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50">
                        Calculate Web App Scope
                    </Link>
                </div>
            </div>

            <!-- Tech Badges -->
            <div class="mt-16 text-center">
                <p class="text-xs uppercase tracking-[0.2em] text-[#9dc5ff]">Core Stack & Ecosystem</p>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <span class="rounded-xl border border-[#b8c9e633] bg-[#27374d80] px-4 py-2.5 text-xs font-bold text-white">Laravel 13</span>
                    <span class="rounded-xl border border-[#b8c9e633] bg-[#27374d80] px-4 py-2.5 text-xs font-bold text-white">Vue 3 + TypeScript</span>
                    <span class="rounded-xl border border-[#b8c9e633] bg-[#27374d80] px-4 py-2.5 text-xs font-bold text-white">Inertia.js</span>
                    <span class="rounded-xl border border-[#b8c9e633] bg-[#27374d80] px-4 py-2.5 text-xs font-bold text-white">PostgreSQL (Neon)</span>
                    <span class="rounded-xl border border-[#b8c9e633] bg-[#27374d80] px-4 py-2.5 text-xs font-bold text-white">Redis & WebSockets</span>
                    <span class="rounded-xl border border-[#b8c9e633] bg-[#27374d80] px-4 py-2.5 text-xs font-bold text-white">Tailwind CSS</span>
                </div>
            </div>

            <!-- Architectural Capabilities -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8 shadow-lg">
                    <div class="text-2xl mb-3">⚡</div>
                    <h3 class="text-xl font-bold text-white">Sub-100ms Response Times</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Optimized database indexing, Redis query caching, and lazy hydration ensure instantaneous page transitions.
                    </p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8 shadow-lg">
                    <div class="text-2xl mb-3">🛡️</div>
                    <h3 class="text-xl font-bold text-white">Bank-Grade Security</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Automated SQL injection protection, CSRF hardening, rate limiting, and TOTP 2FA authorization.
                    </p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8 shadow-lg">
                    <div class="text-2xl mb-3">🧩</div>
                    <h3 class="text-xl font-bold text-white">Modular Monolith Architecture</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Domain-Driven Design (DDD) with clear boundary interfaces so your code grows without becoming a mess.
                    </p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-[#9dc5ff] font-bold">Proven In Production</span>
                    <h2 class="mt-2 text-2xl font-black text-white sm:text-4xl">Featured Web Applications Delivered</h2>
                    <p class="mt-3 text-sm text-slate-300">Live production applications demonstrating our architectural reliability and scaling capability.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Habuilt -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/habuilt.jpg" alt="Habuilt Platform" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-sky-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">99.99% Uptime</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-sky-400 uppercase tracking-wider">Wellness & Habits</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Habuilt</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">High-traffic atomic habit platform with 26-week progression tiers. Reduced server latencies by 70%.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-sky-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/habuilt" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- GutTalks -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/guttalks.jpg" alt="GutTalks Clinic" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">10k+ Patients</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Telehealth Clinic</span>
                                <h3 class="mt-1 text-xl font-bold text-white">GutTalks</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Evidence-based gut health platform with ₹499 Root Rx booking & GutMap Complete™ sequencing tracker.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://guttalks.in/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/guttalks" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Ashish Gupta Hub -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/ashishgupta.jpg" alt="Ashish Gupta Hub" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-purple-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">$1M+ Savings</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-purple-400 uppercase tracking-wider">Architecture Hub</span>
                                <h3 class="mt-1 text-xl font-bold text-white">Ashish Gupta Hub</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Senior Full-Stack Architect portfolio demonstrating Domain-Driven Design (DDD) and real-time telemetry.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://ashishgupta.dev/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-purple-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/ashishgupta" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Process Flow -->
            <div class="mt-20 rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8 sm:p-12">
                <h2 class="text-2xl font-black text-white sm:text-3xl">How We Build Your Web Application</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-4">
                    <div class="border-l-2 border-[#7ac4ff] pl-4">
                        <span class="text-xs font-bold text-[#7ac4ff]">PHASE 01</span>
                        <h4 class="mt-1 font-bold text-white">Domain Blueprinting</h4>
                        <p class="mt-2 text-xs text-slate-300">Database schema design, API contract definition, and UX wireframes.</p>
                    </div>
                    <div class="border-l-2 border-[#9ba7ff] pl-4">
                        <span class="text-xs font-bold text-[#9ba7ff]">PHASE 02</span>
                        <h4 class="mt-1 font-bold text-white">Agile Sprints</h4>
                        <p class="mt-2 text-xs text-slate-300">Weekly test-driven iterations with transparent staging environment access.</p>
                    </div>
                    <div class="border-l-2 border-[#c593ff] pl-4">
                        <span class="text-xs font-bold text-[#c593ff]">PHASE 03</span>
                        <h4 class="mt-1 font-bold text-white">Load Testing & Audit</h4>
                        <p class="mt-2 text-xs text-slate-300">Simulating peak traffic, memory leak detection, and security penetration audit.</p>
                    </div>
                    <div class="border-l-2 border-emerald-400 pl-4">
                        <span class="text-xs font-bold text-emerald-400">PHASE 04</span>
                        <h4 class="mt-1 font-bold text-white">Zero-Downtime Launch</h4>
                        <p class="mt-2 text-xs text-slate-300">Automated CI/CD deployment on Vercel/AWS with 30 days post-launch support.</p>
                    </div>
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
