<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

interface PostMeta {
    slug: string;
    title: string;
    date: string;
    author: string;
    category: string;
    tags: string[];
    excerpt: string;
    read_time: string;
    cover_image: string;
}

const props = defineProps<{
    posts: PostMeta[];
}>();

const selectedCategory = ref<string>('All');

const categories = computed(() => {
    const set = new Set<string>();
    props.posts.forEach((p) => set.add(p.category));
    return ['All', ...Array.from(set)];
});

const filteredPosts = computed(() => {
    if (selectedCategory.value === 'All') return props.posts;
    return props.posts.filter((p) => p.category === selectedCategory.value);
});
</script>

<template>
    <Head title="Engineering Insights & Architecture Blog — DigitalBuilders" />

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Cost Estimator
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Hero -->
            <div class="text-center max-w-3xl mx-auto">
                <span class="db-chip">Engineering Journal</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl">Architecture & Tech Insights</h1>
                <p class="mt-4 text-base text-slate-300">
                    Deep dives into scalable system architecture, high-performance web engineering, AI automation, and enterprise software patterns.
                </p>

                <!-- Category Filters -->
                <div class="mt-8 flex flex-wrap justify-center gap-2">
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="selectedCategory = cat"
                        class="rounded-full px-4 py-2 text-xs font-bold transition"
                        :class="selectedCategory === cat
                            ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] text-[#1a2231]'
                            : 'border border-[#b8c9e626] bg-[#27374d60] text-slate-300 hover:text-white'"
                    >
                        {{ cat }}
                    </button>
                </div>
            </div>

            <!-- Articles Grid -->
            <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="post in filteredPosts"
                    :key="post.slug"
                    class="db-mini flex flex-col rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-lg transition hover:-translate-y-1"
                >
                    <img :src="post.cover_image" :alt="post.title" class="h-48 w-full object-cover" />
                    <div class="flex flex-1 flex-col justify-between p-6">
                        <div>
                            <div class="flex items-center justify-between text-xs text-[#9dc5ff]">
                                <span class="font-bold uppercase tracking-wider">{{ post.category }}</span>
                                <span>{{ post.read_time }}</span>
                            </div>
                            <h2 class="mt-3 text-lg font-bold text-white leading-snug hover:text-[#b7d3ff]">
                                <Link :href="`/blog/${post.slug}`">{{ post.title }}</Link>
                            </h2>
                            <p class="mt-3 text-xs text-slate-300 leading-relaxed line-clamp-3">{{ post.excerpt }}</p>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-[#b8c9e622] pt-4 text-xs">
                            <span class="text-slate-400">By {{ post.author }}</span>
                            <Link :href="`/blog/${post.slug}`" class="font-bold text-[#9ba7ff] hover:text-[#c593ff]">Read Article →</Link>
                        </div>
                    </div>
                </article>
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
        radial-gradient(1200px 700px at -10% -5%, rgba(122, 196, 255, 0.28), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(197, 147, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
