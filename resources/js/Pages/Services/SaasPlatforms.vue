<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="SaaS Platform Engineering — DigitalBuilders">
        <meta name="description" content="Multi-tenant SaaS platform engineering with subscription billing, tenant data isolation, and global auto-scaling." />
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
                        Estimate SaaS Scope
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 05</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl lg:text-6xl leading-tight">
                    SaaS Platform Architecture
                </h1>
                <p class="mt-6 text-base text-slate-300 sm:text-lg leading-relaxed">
                    Build B2B and B2C SaaS platforms engineered for global scale, strict multi-tenant isolation, automated recurring billing, and 99.99% system availability.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-6 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Launch Your SaaS Idea
                    </a>
                    <Link href="/estimator" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50">
                        Calculate SaaS Scope
                    </Link>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">🏢</div>
                    <h3 class="text-xl font-bold text-white">Multi-Tenant Isolation</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Strict tenant data boundaries, custom subdomains, and granular team permission roles.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">💳</div>
                    <h3 class="text-xl font-bold text-white">Automated Billing</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Stripe & Razorpay recurring subscriptions, usage-based metering, and self-serve client billing portal.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">📈</div>
                    <h3 class="text-xl font-bold text-white">Auto-Scaling Infrastructure</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Serverless deployment, Redis queue workers, CDN edge caching, and automated error logging.</p>
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
