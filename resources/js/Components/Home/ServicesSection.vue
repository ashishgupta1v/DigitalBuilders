<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface ServiceItem {
    title: string;
    summary: string;
    link: string;
}

defineProps<{
    services: readonly ServiceItem[];
}>();
</script>

<template>
    <section id="services" class="mt-20 sm:mt-24" data-reveal>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400 font-semibold">Our Services</p>
                <h2 class="mt-3 text-2xl font-black text-foreground sm:text-3xl md:text-4xl">Complete Digital Solutions for Growing Brands</h2>
            </div>
            <Link href="/estimator" class="rounded-full border border-sky-500/30 bg-sky-500/10 px-4 py-2.5 min-h-[44px] text-xs font-bold text-sky-600 dark:text-sky-400 hover:bg-sky-500/20 transition-all inline-flex items-center gap-1.5 self-start md:self-auto">
                <span>Scope Your Project</span>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-stagger>
            <article
                v-for="service in services"
                :key="service.title"
                data-stagger-item
                class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-6 flex flex-col justify-between shadow-sm"
            >
                <div>
                    <div class="h-11 w-11 mb-4 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                        <svg v-if="service.link.includes('web-applications')" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <svg v-else-if="service.link.includes('mobile-apps')" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <svg v-else-if="service.title.includes('Voice')" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        <svg v-else-if="service.title.includes('Workflows')" class="h-6 w-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <svg v-else-if="service.link.includes('erp-crm')" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <svg v-else class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">{{ service.title }}</h3>
                    <p class="mt-2.5 text-sm leading-relaxed text-muted-foreground">{{ service.summary }}</p>
                </div>
                <Link :href="service.link" class="mt-5 inline-flex items-center gap-1.5 min-h-[44px] py-2 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline transition-colors">
                    Learn More
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </article>
        </div>
    </section>
</template>
