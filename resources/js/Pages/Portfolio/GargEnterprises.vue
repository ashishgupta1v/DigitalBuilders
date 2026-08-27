<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="Garg Enterprises Case Study — B2B Wholesale Ordering App | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders built an offline-first Android enterprise ordering app with credit ledger reconciliation and 1-tap GST invoice downloads for 10k+ SKUs." />
        <meta property="og:title" content="Garg Enterprises Case Study — B2B Wholesale Ordering App | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders built an offline-first Android enterprise ordering app with credit ledger reconciliation and 1-tap GST invoice downloads for 10k+ SKUs." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/gargenterprises.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/garg-enterprises" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Garg Enterprises Case Study — B2B Wholesale App | DigitalBuilders" />
        <meta name="twitter:description" content="Offline-first Android wholesale ordering app with live dealer ledgers and 1-tap GST invoice downloads." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/gargenterprises.jpg" />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <a href="/#contact" class="rounded-full bg-secondary border border-border px-4 py-2 text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-1.5">
                        Request APK / Demo
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · Enterprise B2B Android App</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl leading-tight">
                    Garg Enterprises
                </h1>
                <p class="mt-4 text-lg font-semibold text-sky-700 dark:text-sky-400">
                    B2B Wholesale Ordering, Dealer Ledger Sync & Automated Warehouse Dispatch App
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-border shadow-2xl bg-card">
                <img
                    src="/images/portfolio/gargenterprises.webp"
                    alt="Garg Enterprises B2B Wholesale App Snapshot"
                    class="w-full h-auto object-cover object-top"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">0%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Manual Order Entry Errors</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-sky-700 dark:text-sky-400">3x</p>
                    <p class="mt-1 text-xs text-muted-foreground">Dealer Reorder Turnaround Speed</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">10k+ SKUs</p>
                    <p class="mt-1 text-xs text-muted-foreground">Wholesale Product Catalog</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">Offline</p>
                    <p class="mt-1 text-xs text-muted-foreground">SQLite Draft & Auto-Sync</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">The Distribution Challenge</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Wholesale distributors like Garg Enterprises were dependent on handwritten slips and WhatsApp messages for bulk dealer orders, resulting in frequent inventory mismatches, delayed dispatches, and disputed credit balances in warehouse conditions with intermittent connectivity.
                    </p>
                </div>

                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        We developed a rugged, offline-first Android enterprise application. Dealers can browse tiered wholesale catalogs, check live inventory quantities, place bulk reorders in seconds, and track invoice ledgers with automatic background sync.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-card-foreground">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0" />
                            <span><strong>Offline SQLite Order Engine:</strong> Draft bulk POs in offline basements and automatically sync when network is restored.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500 shrink-0" />
                            <span><strong>Dealer Credit Ledger & GST Invoices:</strong> Instant view of available credit limit, pending invoices, and 1-tap PDF invoice downloads.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-500 shrink-0" />
                            <span><strong>Dynamic Volume Pricing Matrix:</strong> Tiered discounts applied automatically per dealer category and order quantity thresholds.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Android / Kotlin</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Offline SQLite Sync</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">GST Invoice PDF</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Tiered B2B Pricing</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-border bg-secondary/60 p-8 shadow-md">
                    <blockquote class="text-base italic text-card-foreground sm:text-lg">
                        "Our dealers can now place bulk orders anytime, view their credit ledger, and track dispatches in real-time. It completely eliminated our order confusion and streamlined our warehouse."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-card-foreground">— Managing Director, Garg Enterprises</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-foreground">Need a Custom B2B or Enterprise Mobile App?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="btn-primary rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Request B2B App Architecture Review
                    </a>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
