<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

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

const readingProgress = ref(0);
const copied = ref(false);

const canonicalUrl = computed(() => `https://www.digitalbuilders.in/blog/${props.post.slug}`);

const jsonLd = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'BlogPosting',
    mainEntityOfPage: {
        '@type': 'WebPage',
        '@id': canonicalUrl.value,
    },
    headline: props.post.title,
    description: props.post.excerpt,
    image: props.post.cover_image,
    author: {
        '@type': 'Person',
        name: props.post.author,
        url: 'https://ashishgupta.dev',
    },
    publisher: {
        '@type': 'Organization',
        name: 'DigitalBuilders',
        logo: {
            '@type': 'ImageObject',
            url: 'https://www.digitalbuilders.in/images/db-logo.png',
        },
    },
    datePublished: props.post.date,
    dateModified: props.post.date,
    articleSection: props.post.category,
    keywords: props.post.tags.join(', '),
}));

const breadcrumbJsonLd = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
        {
            '@type': 'ListItem',
            position: 1,
            name: 'Home',
            item: 'https://www.digitalbuilders.in',
        },
        {
            '@type': 'ListItem',
            position: 2,
            name: 'Engineering Insights',
            item: 'https://www.digitalbuilders.in/blog',
        },
        {
            '@type': 'ListItem',
            position: 3,
            name: props.post.title,
            item: canonicalUrl.value,
        },
    ],
}));

function handleScroll() {
    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (totalHeight > 0) {
        readingProgress.value = Math.min(100, Math.max(0, (window.scrollY / totalHeight) * 100));
    }
}

function copyArticleUrl() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    });
}

function enhanceCodeBlocks() {
    const preBlocks = document.querySelectorAll('.db-prose pre');
    preBlocks.forEach((pre) => {
        if (pre.querySelector('.code-copy-btn')) return;

        const container = document.createElement('div');
        container.className = 'relative group';
        pre.parentNode?.insertBefore(container, pre);
        container.appendChild(pre);

        const btn = document.createElement('button');
        btn.className = 'code-copy-btn absolute top-3 right-3 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 text-xs px-2.5 py-1 font-medium transition opacity-0 group-hover:opacity-100 backdrop-blur-md';
        btn.textContent = 'Copy';
        btn.onclick = () => {
            const codeText = pre.querySelector('code')?.textContent || pre.textContent || '';
            navigator.clipboard.writeText(codeText).then(() => {
                btn.textContent = 'Copied!';
                btn.classList.add('text-emerald-400');
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.classList.remove('text-emerald-400');
                }, 2000);
            });
        };
        container.appendChild(btn);
    });
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    enhanceCodeBlocks();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head :title="`${props.post.title} — DigitalBuilders Blog`">
        <meta name="description" :content="props.post.excerpt" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:title" :content="props.post.title" />
        <meta property="og:description" :content="props.post.excerpt" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:type" content="article" />
        <meta property="og:image" :content="props.post.cover_image" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="props.post.title" />
        <meta name="twitter:description" :content="props.post.excerpt" />
        <meta name="twitter:image" :content="props.post.cover_image" />
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(jsonLd) }}
        </component>
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(breadcrumbJsonLd) }}
        </component>
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <!-- Reading Progress Bar -->
        <div
            class="fixed top-0 left-0 h-1 bg-[linear-gradient(90deg,#0284c7,#4f46e5,#7c3aed)] z-[60] transition-all duration-75"
            :style="{ width: `${readingProgress}%` }"
        />

        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/blog" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">← All Articles</Link>
                    <button
                        @click="copyArticleUrl"
                        class="rounded-full border border-white/20 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/10 transition-colors inline-flex items-center gap-1.5"
                    >
                        <span>{{ copied ? 'Link Copied!' : 'Share Article' }}</span>
                    </button>
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
            <div class="mt-8 overflow-hidden rounded-3xl border border-[#b8c9e633] shadow-2xl">
                <img :src="props.post.cover_image" :alt="props.post.title" class="h-64 w-full object-cover sm:h-96" />
            </div>

            <!-- Content Rendered HTML -->
            <article class="db-prose mt-10 rounded-3xl border border-[#b8c9e626] bg-[#27374d80] p-6 sm:p-10 shadow-lg" v-html="props.post.content_html" />

            <!-- Tags -->
            <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-[#b8c9e622] pt-6">
                <span class="text-xs font-bold text-slate-400">Tags:</span>
                <span v-for="tag in props.post.tags" :key="tag" class="rounded-full bg-[#27374d] px-3 py-1 text-xs text-[#b7d3ff]">
                    #{{ tag }}
                </span>
            </div>

            <!-- Related Articles -->
            <div v-if="props.related && props.related.length > 0" class="mt-12">
                <h3 class="text-lg font-bold text-white mb-4">Recommended Reads</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Link
                        v-for="rel in props.related"
                        :key="rel.slug"
                        :href="`/blog/${rel.slug}`"
                        class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-5 flex flex-col justify-between hover:border-sky-500/50 transition-all"
                    >
                        <div>
                            <h4 class="font-bold text-white text-base">{{ rel.title }}</h4>
                            <p class="mt-2 text-xs text-slate-300 line-clamp-2">{{ rel.excerpt }}</p>
                        </div>
                        <span class="mt-4 text-xs font-bold text-sky-400">Read Article →</span>
                    </Link>
                </div>
            </div>

            <!-- Author & CTA Box -->
            <div class="mt-12 rounded-3xl border border-[#9ba7ff44] bg-[linear-gradient(135deg,#243449,#1b2737)] p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6 shadow-xl">
                <div class="shrink-0 text-center sm:text-left">
                    <div class="h-16 w-16 mx-auto sm:mx-0 rounded-full bg-[linear-gradient(95deg,#7ac4ff,#c593ff)] flex items-center justify-center font-black text-xl text-[#1a2231]">
                        AG
                    </div>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="text-lg font-bold text-white">Written by Ashish Gupta</h3>
                    <p class="mt-1 text-xs text-slate-300">Lead Digital Architect & Founder at DigitalBuilders. Specializing in enterprise monoliths, AI voice agents, and cloud systems.</p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="/#contact" class="inline-block text-xs font-bold text-[#9ba7ff] hover:text-[#c593ff]">Schedule Architecture Audit →</a>
                        <span class="text-slate-600">•</span>
                        <a href="/feed.xml" target="_blank" class="text-xs font-semibold text-slate-400 hover:text-white">RSS Feed ↗</a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-[#b8c9e633] bg-[#111827e6] py-8 text-center text-xs text-slate-400">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>

<style>
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
