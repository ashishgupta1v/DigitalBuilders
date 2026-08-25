<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="MyAstrova Case Study — DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered an AstroTech computational platform with automated kundali generation, daily horoscopes, and live Vedic consultations." />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="https://myastrova.com/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-purple-500/20 border border-purple-500/40 px-4 py-2 text-xs font-semibold text-purple-300 hover:bg-purple-500/30 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · AstroTech & Consumer Platform</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    MyAstrova
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    AstroTech Computational Engine, Kundali Generation & Live Astrologer Consultations
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/myastrova.jpg"
                    alt="MyAstrova Vedic AstroTech & Crystal Mall Portal Snapshot"
                    class="w-full h-auto object-cover object-top"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#c593ff]">&lt;200ms</p>
                    <p class="mt-1 text-xs text-slate-300">Kundli Ephemeris Computation</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">Live</p>
                    <p class="mt-1 text-xs text-slate-300">Call, Chat & Meet Astrologers</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">99.9%</p>
                    <p class="mt-1 text-xs text-slate-300">Booking & Checkout Uptime</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">E-Commerce</p>
                    <p class="mt-1 text-xs text-slate-300">Energized Crystal Mall</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Computational Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Traditional astrology portals suffer from slow chart rendering, inaccurate planetary math, and confusing consultation booking flows. MyAstrova needed a high-performance, modern platform that combines sub-second Kundli matching with direct 1-on-1 astrologer calling, live chat, and a verified energized crystal e-commerce store.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        DigitalBuilders engineered a mathematical ephemeris calculation service capable of rendering dynamic birth charts (Kundli) on the fly, automated daily horoscope generation, and an astrologer scheduling engine with multi-gateway payments.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-400 shrink-0" />
                            <span><strong>Ephemeris Kundli Engine:</strong> High-precision planetary transit calculations rendering dynamic astrological birth charts in &lt;200ms.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>Live Astrologer Routing:</strong> Instant connection channel for phone calls, live chat sessions, and scheduled video consultations.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>MyAstrova Mall E-Commerce:</strong> Curated catalog for certified gemstones, energized crystals, and Rudraksha with Razorpay payment gateway.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Next.js</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">React</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Tailwind CSS</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Vedic Math Engine</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Razorpay Checkout</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">WhatsApp API</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "DigitalBuilders delivered a mathematically accurate and stunning AstroTech platform. The charts render instantly and the seamless booking process has made MyAstrova a trusted household name for Vedic consultations and crystal remedies."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Acharya Raman, Principal Astrologer, MyAstrova</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Building an AstroTech or Consumer Commerce Platform?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://myastrova.com/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50 inline-flex items-center gap-2">
                        Open MyAstrova Live
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
        radial-gradient(1200px 700px at -10% -5%, rgba(168, 85, 247, 0.25), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(59, 130, 246, 0.22), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
