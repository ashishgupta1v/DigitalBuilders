<script setup lang="ts">
const comparisons = [
    {
        feature: 'Architecture Pattern',
        digitalBuilders: 'Domain-Driven Modular Monolith (Clean Bounded Contexts)',
        legacy: 'Spaghetti monolith or microservice sprawl',
    },
    {
        feature: 'API & Query Performance',
        digitalBuilders: 'Sub-100ms response times, optimized indexing & Redis caching',
        legacy: 'Unindexed slow queries, N+1 bottlenecks (>800ms)',
    },
    {
        feature: 'Cloud Hosting Costs',
        digitalBuilders: 'Efficient scale on single/dual server pairs ($50–$200/mo)',
        legacy: 'Over-provisioned bloated cloud bills ($1,000+/mo)',
    },
    {
        feature: 'Frontend Reactivity',
        digitalBuilders: 'VILT Stack (Vue 3 + Inertia.js + TypeScript) — zero SPA hydration drag',
        legacy: 'Heavy client bundle overhead or reload lag',
    },
    {
        feature: 'AI Capabilities',
        digitalBuilders: 'Direct RAG pipelines, pgvector embeddings, autonomous WhatsApp bots',
        legacy: 'Basic ungrounded LLM wrappers with hallucinations',
    },
    {
        feature: 'Delivery & Warranty',
        digitalBuilders: 'Weekly live sprint demos, full test suite, 30-day post-launch warranty',
        legacy: 'Vague milestone delays and untested handoffs',
    },
] as const;
</script>

<template>
    <section class="mt-20 sm:mt-24" data-reveal>
        <div class="text-center max-w-3xl mx-auto">
            <p class="text-sm uppercase tracking-[0.2em] text-sky-700 dark:text-sky-400 font-semibold">Engineering Philosophy</p>
            <h2 class="mt-3 text-2xl font-black text-foreground sm:text-3xl md:text-4xl">
                The DigitalBuilders Standard vs Traditional Agencies
            </h2>
            <p class="mt-3 text-sm text-muted-foreground">
                Why ambitious founders and enterprises trust our modular systems over bloated agency templates.
            </p>
        </div>

        <div class="mt-10 overflow-hidden rounded-3xl border border-border bg-card text-card-foreground shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-secondary text-xs uppercase font-bold tracking-wider text-secondary-foreground">
                            <th class="p-4 sm:p-5">System Dimension</th>
                            <th class="p-4 sm:p-5 text-sky-800 dark:text-sky-300 bg-sky-500/10 border-x border-sky-500/20">
                                ⚡ DigitalBuilders Approach
                            </th>
                            <th class="p-4 sm:p-5 text-muted-foreground">Typical Agency / Template</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="(item, idx) in comparisons"
                            :key="idx"
                            class="hover:bg-muted/40 transition-colors"
                        >
                            <td class="p-4 sm:p-5 font-bold text-card-foreground">
                                {{ item.feature }}
                            </td>
                            <td class="p-4 sm:p-5 font-semibold text-card-foreground bg-sky-500/[0.04] border-x border-sky-500/20">
                                <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 mr-1.5 font-bold">✓</span>
                                {{ item.digitalBuilders }}
                            </td>
                            <td class="p-4 sm:p-5 text-muted-foreground">
                                <span class="inline-flex items-center gap-1.5 text-rose-500 mr-1.5 font-bold">✕</span>
                                {{ item.legacy }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
