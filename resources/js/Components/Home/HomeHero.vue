<script setup lang="ts">
import HeroCanvas from '@/Components/HeroCanvas.vue';

defineProps<{
    isDarkMode: boolean;
}>();
</script>

<template>
    <div>
        <!-- Hero Section with Canvas Particle Network -->
        <section class="relative mt-4 overflow-hidden rounded-3xl border border-border bg-card p-6 pt-12 sm:p-10 sm:pt-16 lg:p-14 lg:pt-20 shadow-xl">
            <HeroCanvas :is-dark-mode="isDarkMode" />
            <div class="relative z-10">
                <p class="db-chip mb-4">Enterprise Architecture</p>
                <h1
                    data-hero-title
                    class="max-w-4xl text-3xl font-black leading-tight text-foreground sm:text-4xl md:text-5xl lg:text-6xl"
                    style="font-family: 'Libre Baskerville', Georgia, serif; font-weight: 700;"
                >
                    We Build Your
                    <span class="db-gradient-text">Digital Future.</span>
                </h1>
                <p
                    class="mt-5 max-w-2xl text-base leading-relaxed text-muted-foreground md:text-lg"
                    style="font-family: 'Outfit', sans-serif; font-weight: 300;"
                >
                    Stop settling for standard templates. Get enterprise-grade web, mobile, and AI architecture engineered to scale your business.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="#contact" class="btn-primary inline-flex w-full items-center justify-center rounded-full px-7 py-3.5 min-h-[44px] text-sm font-bold text-white shadow-lg transition-transform hover:scale-[1.02] sm:w-auto">
                        Book a Discovery Call
                    </a>
                    <a
                        href="/downloads/digitalbuilders-pricing-india-inr.html"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-sky-500/40 bg-sky-500/10 text-sky-700 dark:text-sky-300 hover:bg-sky-500/20 px-6 py-3.5 min-h-[44px] text-sm font-bold transition-all shadow-sm sm:w-auto"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download 2026 Brochure (PDF)</span>
                    </a>
                    <a href="#portfolio" class="inline-flex w-full items-center justify-center rounded-full border border-border bg-secondary text-secondary-foreground hover:bg-secondary/80 px-7 py-3.5 min-h-[44px] text-sm font-semibold transition-colors sm:w-auto">
                        View Our Portfolio
                    </a>
                </div>
                <p class="mt-5 text-sm font-medium text-foreground/90">Bringing Silicon Valley engineering discipline and AI automation right here to Ludhiana.</p>
            </div>
        </section>

        <!-- Key Architectural Pillars -->
        <section class="mt-12 grid gap-5 md:grid-cols-3" data-stagger data-reveal>
            <h2 class="sr-only">Core Architectural Capabilities</h2>
            <article data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="h-10 w-10 mb-4 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Staff Engineer Architecture</h3>
                    <p class="mt-2.5 text-sm text-muted-foreground leading-relaxed">Engineered with domain-driven modular monoliths for zero technical debt and sub-100ms response times.</p>
                </div>
            </article>
            <article data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="h-10 w-10 mb-4 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Autonomous AI Integration</h3>
                    <p class="mt-2.5 text-sm text-muted-foreground leading-relaxed">Voice agents, intelligent lead qualification, and automated workflow pipelines fitted seamlessly into your stack.</p>
                </div>
            </article>
            <article data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="h-10 w-10 mb-4 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Production-Grade Delivery</h3>
                    <p class="mt-2.5 text-sm text-muted-foreground leading-relaxed">Transparent weekly sprint demos, full test coverage, zero-downtime deployment, and 30-day post-launch warranty.</p>
                </div>
            </article>
        </section>

        <!-- Stats Bar Grid -->
        <section class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger data-reveal>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 text-center sm:text-left shadow-sm">
                <p class="text-3xl font-black db-gradient-text" data-counter="10" data-suffix="+">10+</p>
                <p class="mt-2 text-sm font-semibold text-card-foreground">Years Enterprise Experience</p>
                <p class="mt-1 text-xs text-muted-foreground">Enterprise software & cloud systems</p>
            </div>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 text-center sm:text-left shadow-sm">
                <p class="text-3xl font-black db-gradient-text" data-counter="25" data-suffix="+">25+</p>
                <p class="mt-2 text-sm font-semibold text-card-foreground">Projects Delivered</p>
                <p class="mt-1 text-xs text-muted-foreground">Web, mobile, AI & ERP platforms</p>
            </div>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 text-center sm:text-left shadow-sm">
                <p class="text-3xl font-black db-gradient-text" data-counter="99.9" data-suffix="%">99.9%</p>
                <p class="mt-2 text-sm font-semibold text-card-foreground">Architecture Uptime</p>
                <p class="mt-1 text-xs text-muted-foreground">Resilient infrastructure scaling</p>
            </div>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 text-center sm:text-left shadow-sm">
                <p class="text-3xl font-black db-gradient-text">100%</p>
                <p class="mt-2 text-sm font-semibold text-card-foreground">Sprint Delivery Rate</p>
                <p class="mt-1 text-xs text-muted-foreground">Zero broken deadlines</p>
            </div>
        </section>
    </div>
</template>
