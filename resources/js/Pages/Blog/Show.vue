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

    <div class="db-shell bg-background text-foreground min-h-screen">
        <!-- Accessible Skip to Main Content Link (WCAG 2.4.1) -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-5 focus:py-2.5 focus:bg-primary focus:text-white focus:rounded-full focus:shadow-2xl focus:font-bold focus:outline-none focus:ring-4 focus:ring-sky-400"
        >
            Skip to main content
        </a>

        <!-- Reading Progress Bar -->
        <div
            class="fixed top-0 left-0 h-1 bg-[linear-gradient(90deg,#0284c7,#4f46e5,#7c3aed)] z-[60] transition-all duration-75"
            :style="{ width: `${readingProgress}%` }"
        />

        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <nav aria-label="Primary navigation" class="flex items-center gap-4">
                    <Link href="/blog" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors min-h-[44px] inline-flex items-center px-2">← All Articles</Link>
                    <button
                        @click="copyArticleUrl"
                        class="rounded-full border border-border bg-secondary px-3 py-1.5 min-h-[44px] text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 transition-colors inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>{{ copied ? 'Link Copied!' : 'Share Article' }}</span>
                    </button>
                    <Link href="/estimator" class="hidden rounded-full btn-primary px-4 py-2 min-h-[44px] text-xs font-semibold text-white sm:inline-flex items-center justify-center">
                        Cost Estimator
                    </Link>
                </nav>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Article Header -->
            <div class="text-center">
                <span class="db-chip">{{ props.post.category }}</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-4xl md:text-5xl leading-tight">
                    {{ props.post.title }}
                </h1>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-muted-foreground">
                    <span>By <strong class="text-foreground">{{ props.post.author }}</strong></span>
                    <span>•</span>
                    <span>Published {{ props.post.date }}</span>
                    <span>•</span>
                    <span class="text-sky-700 dark:text-sky-400 font-semibold">{{ props.post.read_time }}</span>
                </div>
            </div>

            <!-- Cover Image -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-border shadow-2xl">
                <img :src="props.post.cover_image" :alt="props.post.title" class="h-64 w-full object-cover sm:h-96" />
            </div>

            <!-- Content Rendered HTML -->
            <article class="db-prose mt-10 rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-10 shadow-lg" v-html="props.post.content_html" />

            <!-- Tags -->
            <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-border pt-6">
                <span class="text-xs font-bold text-muted-foreground">Tags:</span>
                <span v-for="tag in props.post.tags" :key="tag" class="rounded-full bg-secondary px-3 py-1 text-xs text-secondary-foreground font-medium">
                    #{{ tag }}
                </span>
            </div>

            <!-- Related Articles -->
            <div v-if="props.related && props.related.length > 0" class="mt-12">
                <h3 class="text-lg font-bold text-foreground mb-4">Recommended Reads</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Link
                        v-for="rel in props.related"
                        :key="rel.slug"
                        :href="`/blog/${rel.slug}`"
                        class="db-antigravity-card rounded-2xl border border-border bg-card text-card-foreground p-5 flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm"
                    >
                        <div>
                            <h4 class="font-bold text-card-foreground text-base">{{ rel.title }}</h4>
                            <p class="mt-2 text-xs text-muted-foreground line-clamp-2">{{ rel.excerpt }}</p>
                        </div>
                        <span class="mt-4 text-xs font-bold text-sky-700 dark:text-sky-400">Read Article →</span>
                    </Link>
                </div>
            </div>

            <!-- Author & CTA Box -->
            <div class="mt-12 rounded-3xl border border-border bg-secondary/60 p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6 shadow-xl">
                <div class="shrink-0 text-center sm:text-left">
                    <div class="h-16 w-16 mx-auto sm:mx-0 rounded-full btn-primary flex items-center justify-center font-black text-xl text-white">
                        AG
                    </div>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="text-lg font-bold text-card-foreground">Written by Ashish Gupta</h3>
                    <p class="mt-1 text-xs text-muted-foreground">Lead Digital Architect & Founder at DigitalBuilders. Specializing in enterprise monoliths, AI voice agents, and cloud systems.</p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="/#contact" class="inline-block text-xs font-bold text-sky-700 dark:text-sky-400 hover:underline">Schedule Architecture Audit →</a>
                        <span class="text-muted-foreground">•</span>
                        <a href="/feed.xml" target="_blank" class="text-xs font-semibold text-muted-foreground hover:text-foreground">RSS Feed ↗</a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>

<style>
.db-prose h1, .db-prose h2, .db-prose h3 {
    color: var(--color-foreground, currentColor);
    font-family: 'Space Grotesk', sans-serif;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}
.db-prose h1 { font-size: 1.8rem; font-weight: 800; }
.db-prose h2 { font-size: 1.4rem; font-weight: 700; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; }
.db-prose h3 { font-size: 1.1rem; font-weight: 600; color: var(--color-primary); }
.db-prose p { color: var(--color-card-foreground, currentColor); opacity: 0.9; line-height: 1.8; margin-bottom: 1.25rem; font-size: 0.95rem; }
.db-prose ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; color: var(--color-card-foreground, currentColor); }
.db-prose li { margin-bottom: 0.5rem; }
.db-prose code { background: var(--color-secondary); padding: 0.2rem 0.4rem; border-radius: 0.4rem; color: var(--color-foreground); font-size: 0.85rem; }
.db-prose pre { background: var(--color-secondary); padding: 1rem; border-radius: 0.8rem; overflow-x: auto; border: 1px solid var(--color-border); margin-bottom: 1.25rem; }
.db-prose hr { border-color: var(--color-border); margin: 2rem 0; }
</style>
