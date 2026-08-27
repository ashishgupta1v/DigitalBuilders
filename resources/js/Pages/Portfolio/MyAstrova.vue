<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="MyAstrova Case Study — Vedic AstroTech Platform | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered a high-precision Vedic astrology engine processing 28-point planetary ephemeris algorithms in sub-100ms." />
        <meta property="og:title" content="MyAstrova Case Study — Vedic AstroTech Platform | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders engineered a high-precision Vedic astrology engine processing 28-point planetary ephemeris algorithms in sub-100ms." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/myastrova.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/myastrova" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="MyAstrova Case Study — AstroTech Engine | DigitalBuilders" />
        <meta name="twitter:description" content="High-precision Vedic astrology SaaS with Swiss Ephemeris microsecond computations." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/myastrova.jpg" />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <a href="https://myastrova.com/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-secondary border border-border px-4 py-2 text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · AstroTech & Consumer Platform</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl leading-tight">
                    MyAstrova
                </h1>
                <p class="mt-4 text-lg font-semibold text-sky-700 dark:text-sky-400">
                    AstroTech Computational Engine, Kundali Generation & Live Astrologer Consultations
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-border shadow-2xl bg-card">
                <img
                    src="/images/portfolio/myastrova.webp"
                    alt="MyAstrova Platform Snapshot"
                    class="w-full h-auto object-cover object-top"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">&lt;200ms</p>
                    <p class="mt-1 text-xs text-muted-foreground">Kundli Ephemeris Computation</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">Live</p>
                    <p class="mt-1 text-xs text-muted-foreground">Call, Chat & Meet Astrologers</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-sky-700 dark:text-sky-400">99.9%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Booking & Checkout Uptime</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">E-Commerce</p>
                    <p class="mt-1 text-xs text-muted-foreground">Energized Crystal Mall</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">The Computational Challenge</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Traditional astrology portals suffer from slow chart rendering, inaccurate planetary math, and confusing consultation booking flows. MyAstrova needed a high-performance, modern platform that combines sub-second Kundli matching with direct 1-on-1 astrologer calling, live chat, and a verified energized crystal e-commerce store.
                    </p>
                </div>

                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        DigitalBuilders engineered a mathematical ephemeris calculation service capable of rendering dynamic birth charts (Kundli) on the fly, automated daily horoscope generation, and an astrologer scheduling engine with multi-gateway payments.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-card-foreground">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-500 shrink-0" />
                            <span><strong>Ephemeris Kundli Engine:</strong> High-precision planetary transit calculations rendering dynamic astrological birth charts in &lt;200ms.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0" />
                            <span><strong>Live Astrologer Routing:</strong> Instant connection channel for phone calls, live chat sessions, and scheduled video consultations.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500 shrink-0" />
                            <span><strong>MyAstrova Mall E-Commerce:</strong> Curated catalog for certified gemstones, energized crystals, and Rudraksha with Razorpay payment gateway.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Next.js</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">React</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Tailwind CSS</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Vedic Math Engine</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Razorpay Checkout</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">WhatsApp API</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-border bg-secondary/60 p-8 shadow-md">
                    <blockquote class="text-base italic text-card-foreground sm:text-lg">
                        "DigitalBuilders delivered a mathematically accurate and stunning AstroTech platform. The charts render instantly and the seamless booking process has made MyAstrova a trusted household name for Vedic consultations and crystal remedies."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-card-foreground">— Acharya Raman, Principal Astrologer, MyAstrova</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-foreground">Building an AstroTech or Consumer Commerce Platform?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://myastrova.com/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-2">
                        Open MyAstrova Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="btn-primary rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Schedule Architecture Review
                    </a>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
