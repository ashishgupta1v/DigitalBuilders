<script setup lang="ts">
import HeroCanvas from '@/Components/HeroCanvas.vue';

defineProps<{
    isDarkMode: boolean;
}>();

const emit = defineEmits<{
    (e: 'open-booking'): void;
    (e: 'open-brochure'): void;
}>();
</script>

<template>
    <div>
        <!-- Hero Section with Canvas Particle Network -->
        <section class="relative mt-4 overflow-hidden rounded-3xl border border-border bg-card p-6 pt-12 sm:p-10 sm:pt-16 lg:p-14 lg:pt-20 shadow-xl">
            <HeroCanvas :is-dark-mode="isDarkMode" />
            <div class="relative z-10">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="db-badge-sky">Custom Software & AI Studio</span>
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-900 dark:text-amber-300">
                        <span class="text-amber-500 font-bold">★★★★★</span>
                        <span class="font-bold text-foreground">5.0 ★ Client Satisfaction Score</span>
                        <span class="hidden sm:inline text-muted-foreground">• On-Time Delivery</span>
                    </div>
                </div>
                <h1
                    data-hero-title
                    class="max-w-4xl text-3xl font-black leading-tight text-foreground sm:text-4xl md:text-5xl lg:text-6xl"
                    style="font-family: 'Libre Baskerville', Georgia, serif; font-weight: 700;"
                >
                    We Build Software That
                    <span class="db-gradient-text">Grows Your Business.</span>
                </h1>
                <p
                    class="mt-5 max-w-2xl text-base leading-relaxed text-muted-foreground md:text-lg"
                    style="font-family: 'Outfit', sans-serif; font-weight: 300;"
                >
                    Stop settling for standard templates. Get custom web applications, mobile apps, and smart business automation built to scale your business.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        @click="emit('open-booking')"
                        class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-full px-7 py-3.5 min-h-[44px] text-sm font-bold text-white shadow-lg transition-transform hover:scale-[1.02] sm:w-auto cursor-pointer"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Book Instant Strategy Session</span>
                    </button>
                    <button
                        type="button"
                        @click="emit('open-brochure')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-sky-500/40 bg-sky-500/10 text-sky-800 dark:text-sky-300 hover:bg-sky-500/20 px-6 py-3.5 min-h-[44px] text-sm font-bold transition-all shadow-sm sm:w-auto cursor-pointer"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>2026 Price Book (PDF)</span>
                    </button>
                    <a href="#portfolio" class="inline-flex w-full items-center justify-center rounded-full border border-border bg-secondary text-secondary-foreground hover:bg-secondary/80 px-7 py-3.5 min-h-[44px] text-sm font-semibold transition-colors sm:w-auto">
                        View Live Portfolio
                    </a>
                </div>
                <p class="mt-5 text-sm font-medium text-foreground/90">Bringing Silicon Valley engineering discipline and AI automation right here to Ludhiana.</p>
            </div>
        </section>

        <!-- Key Architectural Pillars -->
        <section class="mt-12 grid gap-5 md:grid-cols-3" data-stagger data-reveal>
            <h2 class="sr-only">What We Build For You</h2>
            <article data-stagger-item class="db-bento-card p-7 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="h-12 w-12 mb-5 rounded-2xl bg-gradient-to-br from-sky-500/20 to-indigo-500/10 border border-sky-500/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Software Built for Your Business</h3>
                    <p class="mt-2.5 text-sm text-muted-foreground leading-relaxed">Custom-crafted web & mobile systems built around your exact workflows — fast, secure, and made to grow without slowdowns.</p>
                </div>
            </article>
            <article data-stagger-item class="db-bento-card p-7 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="h-12 w-12 mb-5 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/10 border border-indigo-500/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">AI & Smart Automation</h3>
                    <p class="mt-2.5 text-sm text-muted-foreground leading-relaxed">Intelligent voice agents, 24/7 WhatsApp lead routing, and automated business workflows that eliminate hours of repetitive manual work.</p>
                </div>
            </article>
            <article data-stagger-item class="db-bento-card p-7 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="h-12 w-12 mb-5 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-700 dark:text-purple-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Predictable On-Time Delivery</h3>
                    <p class="mt-2.5 text-sm text-muted-foreground leading-relaxed">Weekly live sprint demos, full test coverage, zero-downtime deployment, and a complimentary 30-day post-launch warranty.</p>
                </div>
            </article>
        </section>

        <!-- Stats Bento Grid -->
        <section class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger data-reveal>
            <div data-stagger-item class="db-bento-card p-6 text-center sm:text-left">
                <div class="flex items-baseline gap-2 justify-center sm:justify-start">
                    <p class="text-3xl sm:text-4xl font-black db-gradient-text" data-counter="10" data-suffix="+">10+</p>
                </div>
                <div class="mt-2 h-1 w-12 rounded-full bg-gradient-to-r from-sky-500 to-indigo-500 mx-auto sm:mx-0" />
                <p class="mt-3 text-sm font-bold text-card-foreground">Years Enterprise Experience</p>
                <p class="mt-1 text-xs text-muted-foreground">Enterprise software & cloud systems</p>
            </div>
            <div data-stagger-item class="db-bento-card p-6 text-center sm:text-left">
                <div class="flex items-baseline gap-2 justify-center sm:justify-start">
                    <p class="text-3xl sm:text-4xl font-black db-gradient-text" data-counter="25" data-suffix="+">25+</p>
                </div>
                <div class="mt-2 h-1 w-12 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 mx-auto sm:mx-0" />
                <p class="mt-3 text-sm font-bold text-card-foreground">Projects Delivered</p>
                <p class="mt-1 text-xs text-muted-foreground">Web, mobile, AI & ERP platforms</p>
            </div>
            <div data-stagger-item class="db-bento-card p-6 text-center sm:text-left">
                <div class="flex items-baseline gap-2 justify-center sm:justify-start">
                    <p class="text-3xl sm:text-4xl font-black db-gradient-text" data-counter="99.9" data-suffix="%">99.9%</p>
                </div>
                <div class="mt-2 h-1 w-12 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 mx-auto sm:mx-0" />
                <p class="mt-3 text-sm font-bold text-card-foreground">System Uptime</p>
                <p class="mt-1 text-xs text-muted-foreground">Resilient cloud infrastructure</p>
            </div>
            <div data-stagger-item class="db-bento-card p-6 text-center sm:text-left">
                <div class="flex items-baseline gap-2 justify-center sm:justify-start">
                    <p class="text-3xl sm:text-4xl font-black db-gradient-text">100%</p>
                </div>
                <div class="mt-2 h-1 w-12 rounded-full bg-gradient-to-r from-amber-500 to-pink-500 mx-auto sm:mx-0" />
                <p class="mt-3 text-sm font-bold text-card-foreground">Sprint Delivery Rate</p>
                <p class="mt-1 text-xs text-muted-foreground">Weekly demos & on-time releases</p>
            </div>
        </section>
    </div>
</template>
