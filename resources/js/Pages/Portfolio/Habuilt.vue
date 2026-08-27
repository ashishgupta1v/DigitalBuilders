<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="Habuilt Case Study — Progressive Habit Tracking Platform | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered a progressive atomic habit tracking web platform with 50 habits, 4 tiers, streak mechanics, and mobile deep link auth." />
        <meta property="og:title" content="Habuilt Case Study — Progressive Habit Tracking Platform | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders engineered a progressive atomic habit tracking web platform with 50 habits, 4 tiers, streak mechanics, and mobile deep link auth." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/habuilt" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Habuilt Case Study — Habit Platform | DigitalBuilders" />
        <meta name="twitter:description" content="Progressive atomic habit tracking platform with 50 habits, 4 progression tiers, and mobile deep linking." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-secondary border border-border px-4 py-2 text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-1.5">
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
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl leading-tight">
                    Habuilt Technologies
                </h1>
                <p class="mt-4 text-lg font-semibold text-sky-700 dark:text-sky-400">
                    50 Habits · 4 Progression Tiers · 26-Week Atomic Discipline Engine
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-border shadow-2xl bg-card">
                <img
                    src="/images/portfolio/habuilt.webp"
                    alt="Habuilt SaaS Web App Architecture Overview"
                    class="w-full h-auto object-cover object-top"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">50 Habits</p>
                    <p class="mt-1 text-xs text-muted-foreground">4 Progression Tiers</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-sky-700 dark:text-sky-400">70%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Server Latency Reduction</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">2x</p>
                    <p class="mt-1 text-xs text-muted-foreground">Database Query Throughput</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">99.99%</p>
                    <p class="mt-1 text-xs text-muted-foreground">System Availability</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">The Habit Progression Challenge</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Habuilt needed a high-volume atomic habit building architecture based on the "1% better every day" methodology. As user engagement scaled rapidly, the legacy database suffered from lock contention during daily peak check-in windows, and mobile app deep linking required seamless zero-friction handshakes.
                    </p>
                </div>

                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        DigitalBuilders engineered a high-throughput progressive tier unlock engine (Foundation, Build, Refine, Mastery) with sub-50ms streak XP computations, Redis query caching, and universal mobile deep linking protocols.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-card-foreground">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0" />
                            <span><strong>4-Tier 26-Week Progression:</strong> Structured roadmap unlocking atomic micro-habits based on validated streaks and user XP.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500 shrink-0" />
                            <span><strong>Universal Deep Linking:</strong> Seamless auth callback handler (habuilt://auth/callback) bridging mobile web sessions to native mobile apps.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-500 shrink-0" />
                            <span><strong>Multi-Tier Redis Caching:</strong> Sub-50ms user state retrieval eliminating database bottlenecks during morning and evening rush hours.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Next.js / Vue 3</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">TypeScript</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Tailwind CSS</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Redis Caching</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Mobile Deep Linking</span>
                    </div>
                </div>

                <!-- Verified Quote -->
                <div class="rounded-3xl border border-border bg-secondary/60 p-8 shadow-md">
                    <blockquote class="text-base italic text-card-foreground sm:text-lg">
                        "DigitalBuilders re-architected our high-traffic platform from the ground up. Ashish brought Silicon Valley-level engineering standards. Our server latencies dropped by 70% and database throughput doubled within weeks."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-card-foreground">— Gurpreet Singh, CTO & Co-Founder, Habuilt Technologies</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-foreground">Building a High-Growth Consumer Wellness Platform?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-2">
                        Visit Habuilt Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="btn-primary rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Book an Architecture Audit
                    </a>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
