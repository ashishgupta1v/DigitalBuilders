<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Garg Enterprises Case Study — B2B Wholesale Ordering & Ledger App | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders built a rugged Android B2B ordering app with offline SQLite sync, dealer credit ledger, and 1-tap GST invoice downloads for 10k+ SKUs." />
        <meta property="og:title" content="Garg Enterprises Case Study — B2B Wholesale Ordering & Ledger App | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders built a rugged Android B2B ordering app with offline SQLite sync, dealer credit ledger, and 1-tap GST invoice downloads for 10k+ SKUs." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/gargenterprises.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/garg-enterprises" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Garg Enterprises Case Study — B2B Android App | DigitalBuilders" />
        <meta name="twitter:description" content="B2B wholesale ordering app with offline SQLite drafting, dealer credit ledger, and GST PDF downloads." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/gargenterprises.jpg" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="/#contact" class="rounded-full bg-indigo-500/20 border border-indigo-500/40 px-4 py-2 text-xs font-semibold text-indigo-300 hover:bg-indigo-500/30 inline-flex items-center gap-1.5">
                        Request APK / Demo
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · Enterprise B2B Android App</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    Garg Enterprises
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    B2B Wholesale Ordering, Dealer Ledger Sync & Automated Warehouse Dispatch App
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/gargenterprises.jpg"
                    alt="Garg Enterprises B2B Mobile App Snapshot"
                    class="w-full h-auto object-cover object-top"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">0%</p>
                    <p class="mt-1 text-xs text-slate-300">Manual Order Entry Errors</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">3x</p>
                    <p class="mt-1 text-xs text-slate-300">Dealer Reorder Turnaround Speed</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">10k+ SKUs</p>
                    <p class="mt-1 text-xs text-slate-300">Wholesale Product Catalog</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">Offline</p>
                    <p class="mt-1 text-xs text-slate-300">SQLite Draft & Auto-Sync</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Distribution Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Wholesale distributors like Garg Enterprises were dependent on handwritten slips and WhatsApp messages for bulk dealer orders, resulting in frequent inventory mismatches, delayed dispatches, and disputed credit balances in warehouse conditions with intermittent connectivity.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        We developed a rugged, offline-first Android enterprise application. Dealers can browse tiered wholesale catalogs, check live inventory quantities, place bulk reorders in seconds, and track invoice ledgers with automatic background sync.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>Offline SQLite Order Engine:</strong> Draft bulk POs in offline basements and automatically sync when network is restored.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>Dealer Credit Ledger & GST Invoices:</strong> Instant view of available credit limit, pending invoices, and 1-tap PDF invoice downloads.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-400 shrink-0" />
                            <span><strong>Dynamic Volume Pricing Matrix:</strong> Tiered discounts applied automatically per dealer category and order quantity thresholds.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Android / Kotlin</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Offline SQLite Sync</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">GST Invoice PDF</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Tiered B2B Pricing</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "Our dealers can now place bulk orders anytime, view their credit ledger, and track dispatches in real-time. It completely eliminated our order confusion and streamlined our warehouse."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Managing Director, Garg Enterprises</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Need a Custom B2B or Enterprise Mobile App?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-8 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Request B2B App Architecture Review
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
        radial-gradient(1200px 700px at -10% -5%, rgba(99, 102, 241, 0.22), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(14, 165, 233, 0.20), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
