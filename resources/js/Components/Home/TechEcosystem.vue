<script setup lang="ts">
const techStack = [
    { name: 'Laravel 13', category: 'Backend Engine', desc: 'Resilient modular monolith & async queue workers' },
    { name: 'Vue 3 & TypeScript', category: 'Frontend UI', desc: 'Type-safe reactive state with Composition API' },
    { name: 'Inertia.js', category: 'Bridge Layer', desc: 'Seamless monolithic routing without client SPA bloat' },
    { name: 'PostgreSQL & Neon', category: 'Database', desc: 'Serverless branching, JSONB indexes & ACID scale' },
    { name: 'pgvector & OpenAI', category: 'AI & Embeddings', desc: 'Vector similarity search & context-grounded RAG' },
    { name: 'Redis & WebSockets', category: 'Real-time Sync', desc: 'Sub-50ms live push telemetry and atomic locking' },
    { name: 'Tailwind CSS', category: 'Design System', desc: 'High-performance dark/light utility tokens' },
    { name: 'Flutter & React Native', category: 'Mobile Apps', desc: '60 FPS cross-platform native iOS & Android' },
] as const;
</script>

<template>
    <section class="mt-20 sm:mt-24" data-reveal>
        <div class="text-center max-w-3xl mx-auto">
            <p class="text-sm uppercase tracking-[0.2em] text-sky-700 dark:text-sky-400 font-semibold">Technology Matrix</p>
            <h2 class="mt-3 text-2xl font-black text-foreground sm:text-3xl md:text-4xl">
                Engineered with Modern, Battle-Tested Tools
            </h2>
            <p class="mt-3 text-sm text-muted-foreground">
                We select technologies that maximize developer velocity, ensure sub-100ms response times, and reduce long-term maintenance overhead.
            </p>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger>
            <div
                v-for="tech in techStack"
                :key="tech.name"
                data-stagger-item
                class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-5 shadow-sm flex flex-col justify-between"
            >
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700 dark:text-sky-400">
                        {{ tech.category }}
                    </span>
                    <h3 class="mt-1.5 text-base font-bold text-card-foreground">
                        {{ tech.name }}
                    </h3>
                    <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                        {{ tech.desc }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
