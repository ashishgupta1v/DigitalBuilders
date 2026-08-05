<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface PostDetail {
    slug: string;
    title: string;
    date: string;
    author: string;
    category: string;
    tags: string[];
    excerpt: string;
    read_time: string;
    cover_image: string;
    content_html: string;
}

const props = defineProps<{
    post: PostDetail;
    related: Array<{ slug: string; title: string; excerpt: string }>;
}>();
</script>

<template>
    <Head :title="`${props.post.title} — DigitalBuilders Blog`">
        <meta name="description" :content="props.post.excerpt" />
        <meta property="og:title" :content="props.post.title" />
        <meta property="og:description" :content="props.post.excerpt" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/blog" class="text-sm font-medium text-slate-300 hover:text-white">← All Articles</Link>
                    <Link href="/estimator" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Cost Estimator
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Article Header -->
            <div class="text-center">
                <span class="db-chip">{{ props.post.category }}</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-4xl md:text-5xl leading-tight">
                    {{ props.post.title }}
                </h1>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-slate-400">
                    <span>By <strong class="text-white">{{ props.post.author }}</strong></span>
                    <span>•</span>
                    <span>Published {{ props.post.date }}</span>
                    <span>•</span>
                    <span class="text-[#9dc5ff] font-semibold">{{ props.post.read_time }}</span>
                </div>
            </div>

            <!-- Cover Image -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-[#b8c9e633]">
                <img :src="props.post.cover_image" :alt="props.post.title" class="h-64 w-full object-cover sm:h-96" />
            </div>

            <!-- Content Rendered HTML -->
            <article class="db-prose mt-10 rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-6 sm:p-10" v-html="props.post.content_html" />

            <!-- Tags -->
            <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-[#b8c9e622] pt-6">
                <span class="text-xs font-bold text-slate-400">Tags:</span>
                <span v-for="tag in props.post.tags" :key="tag" class="rounded-full bg-[#27374d] px-3 py-1 text-xs text-[#b7d3ff]">
                    #{{ tag }}
                </span>
            </div>

            <!-- Author & CTA Box -->
            <div class="mt-12 rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6">
                <div class="shrink-0 text-center sm:text-left">
                    <div class="h-16 w-16 mx-auto sm:mx-0 rounded-full bg-[linear-gradient(95deg,#7ac4ff,#c593ff)] flex items-center justify-center font-black text-xl text-[#1a2231]">
                        AG
                    </div>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="text-lg font-bold text-white">Written by Ashish Gupta</h3>
                    <p class="mt-1 text-xs text-slate-300">Lead Digital Architect & Founder at DigitalBuilders. Specializing in enterprise monoliths, AI voice agents, and cloud systems.</p>
                    <a href="/#contact" class="mt-4 inline-block text-xs font-bold text-[#9ba7ff] hover:text-[#c593ff]">Schedule Architecture Audit →</a>
                </div>
            </div>
        </main>

        <footer class="border-t border-[#b8c9e633] bg-[#233246d9] py-8 text-center text-xs text-slate-400">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>

<style>
/* Scoped typography styles for parsedown HTML output */
.db-prose h1, .db-prose h2, .db-prose h3 {
    color: #ffffff;
    font-family: 'Space Grotesk', sans-serif;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}
.db-prose h1 { font-size: 1.8rem; font-weight: 800; }
.db-prose h2 { font-size: 1.4rem; font-weight: 700; border-bottom: 1px solid rgba(184,201,230,0.15); padding-bottom: 0.5rem; }
.db-prose h3 { font-size: 1.1rem; font-weight: 600; color: #b7d3ff; }
.db-prose p { color: #d5e3f8; line-height: 1.8; margin-bottom: 1.25rem; font-size: 0.95rem; }
.db-prose ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; color: #d5e3f8; }
.db-prose li { margin-bottom: 0.5rem; }
.db-prose code { background: #1a2534; padding: 0.2rem 0.4rem; border-radius: 0.4rem; color: #c593ff; font-size: 0.85rem; }
.db-prose pre { background: #16202c; padding: 1rem; border-radius: 0.8rem; overflow-x: auto; border: 1px solid rgba(184,201,230,0.2); margin-bottom: 1.25rem; }
.db-prose hr { border-color: rgba(184,201,230,0.2); margin: 2rem 0; }
.site-bg {
    background:
        radial-gradient(1200px 700px at -10% -5%, rgba(122, 196, 255, 0.28), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(197, 147, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
