<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="SportsEntertainmentClub Mobile App Case Study — DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered a cross-platform mobile application for facility booking, membership management, and tournament scheduling." />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="/#contact" class="rounded-full bg-cyan-500/20 border border-cyan-500/40 px-4 py-2 text-xs font-semibold text-cyan-300 hover:bg-cyan-500/30 inline-flex items-center gap-1.5">
                        Request APK / Demo
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · Native Mobile App (iOS & Android)</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl leading-tight">
                    SportsEntertainmentClub App
                </h1>
                <p class="mt-4 text-lg font-semibold text-[#b7d3ff]">
                    Cross-Platform Member Experience, Instant Court Booking & Automated Facility Management
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/20 shadow-2xl bg-slate-950">
                <img
                    src="/images/portfolio/sportsclub.jpg"
                    alt="SportsEntertainmentClub Mobile App Booking Snapshot"
                    class="w-full h-auto object-cover object-top"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-cyan-400">0</p>
                    <p class="mt-1 text-xs text-slate-300">Booking Double-Collisions</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-[#7ac4ff]">95%</p>
                    <p class="mt-1 text-xs text-slate-300">Member Mobile Adoption</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-emerald-400">60 FPS</p>
                    <p class="mt-1 text-xs text-slate-300">Fluid UI Animation Benchmark</p>
                </div>
                <div class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-amber-400">3x</p>
                    <p class="mt-1 text-xs text-slate-300">Reception Check-in Speed</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">The Facility Management Challenge</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        Managing court reservations across tennis, badminton, and squash facilities, tournament brackets, and membership dues manually caused long reception queues, phone booking logjams, and double-booking disputes during peak morning and evening hours.
                    </p>
                </div>

                <div class="rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-8">
                    <h3 class="text-xl font-bold text-white">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                        DigitalBuilders engineered a high-performance cross-platform mobile application (iOS & Android) with atomic time-slot reservation locks, digital QR member passes, and tournament brackets.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-cyan-400 shrink-0" />
                            <span><strong>Atomic Concurrency Slot Locks:</strong> Prevents dual reservations on high-demand badminton and tennis courts with automatic timeout releases.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400 shrink-0" />
                            <span><strong>Digital QR Member Wallet:</strong> Contactless gate check-in pass accelerating front-desk entry by 3x.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400 shrink-0" />
                            <span><strong>Live Tournament Schedules:</strong> Real-time tournament match fixtures, scoreboards, and automated push notification alerts.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Flutter / React Native</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Real-Time Slot Locks</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">Push Notifications</span>
                        <span class="rounded-full bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-[#9ba7ff]">QR Access Control</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-8">
                    <blockquote class="text-base italic text-slate-100 sm:text-lg">
                        "The mobile app transformed our entire club management. Our members love booking badminton, tennis, and swimming slots right from their phones with zero waiting."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-white">— Club Secretary, SportsEntertainmentClub</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white">Looking for a High-Performance Mobile App?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-8 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Request Mobile App Demo & Architecture Plan
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
        radial-gradient(1200px 700px at -10% -5%, rgba(6, 182, 212, 0.22), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(99, 102, 241, 0.20), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
