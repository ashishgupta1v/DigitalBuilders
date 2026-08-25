<script setup lang="ts">
import HeroCanvas from '@/Components/HeroCanvas.vue';

defineProps<{
    isDarkMode: boolean;
}>();
</script>

<template>
    <div>
        <!-- Hero Section with Canvas Particle Network -->
        <section class="relative mt-4 overflow-hidden rounded-3xl border border-[#b8c9e625] bg-[var(--db-card-gradient)] p-6 pt-12 sm:p-10 sm:pt-16 lg:p-14 lg:pt-20 shadow-[0_20px_50px_rgba(0,0,0,0.2)]">
            <HeroCanvas :is-dark-mode="isDarkMode" />
            <div class="relative z-10">
                <p class="db-chip mb-4">Enterprise Architecture</p>
                <h1
                    data-hero-title
                    class="max-w-4xl text-3xl font-black leading-tight text-[var(--db-text)] sm:text-4xl md:text-5xl lg:text-6xl"
                    style="font-family: 'Libre Baskerville', Georgia, serif; font-weight: 700;"
                >
                    We Build Your
                    <span class="db-gradient-text">Digital Future.</span>
                </h1>
                <p
                    class="mt-5 max-w-2xl text-base leading-relaxed text-slate-700 dark:text-slate-300 md:text-lg"
                    style="font-family: 'Outfit', sans-serif; font-weight: 300;"
                >
                    Stop settling for standard templates. Get enterprise-grade web, mobile, and AI architecture engineered to scale your business.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="#contact" class="db-action inline-flex w-full items-center justify-center rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-7 py-3.5 text-sm font-bold text-white dark:text-[#1a2231] shadow-[0_4px_20px_rgba(2,132,199,0.25)] transition-all hover:scale-[1.02] sm:w-auto">
                        Book a Discovery Call
                    </a>
                    <a href="#portfolio" class="inline-flex w-full items-center justify-center rounded-full border border-slate-300 dark:border-white/20 bg-white/60 dark:bg-white/5 backdrop-blur-md px-7 py-3.5 text-sm font-semibold text-slate-800 dark:text-white transition-all hover:border-slate-400 dark:hover:border-white/40 hover:bg-white/80 dark:hover:bg-white/10 sm:w-auto">
                        View Our Portfolio
                    </a>
                </div>
                <p class="mt-5 text-sm text-slate-500 dark:text-slate-400">Bringing Silicon Valley engineering discipline and AI automation right here to Ludhiana.</p>
            </div>
        </section>

        <!-- Key Architectural Pillars -->
        <section class="mt-12 grid gap-5 md:grid-cols-3" data-stagger data-reveal>
            <article data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 shadow-md flex flex-col justify-between">
                <div>
                    <div class="h-10 w-10 mb-4 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Staff Engineer Architecture</h3>
                    <p class="mt-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">Engineered with domain-driven modular monoliths for zero technical debt and sub-100ms response times.</p>
                </div>
            </article>
            <article data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 shadow-md flex flex-col justify-between">
                <div>
                    <div class="h-10 w-10 mb-4 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Autonomous AI Integration</h3>
                    <p class="mt-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">Voice agents, intelligent lead qualification, and automated workflow pipelines fitted seamlessly into your stack.</p>
                </div>
            </article>
            <article data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 shadow-md flex flex-col justify-between">
                <div>
                    <div class="h-10 w-10 mb-4 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Production-Grade Delivery</h3>
                    <p class="mt-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">Transparent weekly sprint demos, full test coverage, zero-downtime deployment, and 30-day post-launch warranty.</p>
                </div>
            </article>
        </section>

        <!-- Stats Bar Grid -->
        <section class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger data-reveal>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 text-center sm:text-left shadow-md">
                <p class="text-3xl font-black db-gradient-text" data-counter="8" data-suffix="+">8+</p>
                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Years IT Experience</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enterprise software & cloud systems</p>
            </div>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 text-center sm:text-left shadow-md">
                <p class="text-3xl font-black db-gradient-text" data-counter="25" data-suffix="+">25+</p>
                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Projects Delivered</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Web, mobile, AI & ERP platforms</p>
            </div>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 text-center sm:text-left shadow-md">
                <p class="text-3xl font-black db-gradient-text" data-counter="99.9" data-suffix="%">99.9%</p>
                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Architecture Uptime</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Resilient infrastructure scaling</p>
            </div>
            <div data-stagger-item class="db-antigravity-card rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#27374dcb] p-6 text-center sm:text-left shadow-md">
                <p class="text-3xl font-black db-gradient-text">100%</p>
                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Sprint Delivery Rate</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Zero broken deadlines</p>
            </div>
        </section>
    </div>
</template>
