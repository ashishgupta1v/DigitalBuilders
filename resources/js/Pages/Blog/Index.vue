<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { trackNewsletterSignup } from '@/utils/analytics';

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
const subscriberEmail = ref('');
const subscribeStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');

const categories = computed(() => {
    const set = new Set<string>();
    props.posts.forEach((p) => set.add(p.category));
    return ['All', ...Array.from(set)];
});

const filteredPosts = computed(() => {
    if (selectedCategory.value === 'All') return props.posts;
    return props.posts.filter((p) => p.category === selectedCategory.value);
});

async function handleSubscribe() {
    if (!subscriberEmail.value || !subscriberEmail.value.includes('@')) return;
    subscribeStatus.value = 'loading';

    try {
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
        const res = await fetch('/library/contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: 'Newsletter Subscriber',
                email: subscriberEmail.value,
                phone: '+91 00000 00000',
                project_type: 'other',
                source: 'newsletter',
                description: '[Architecture Digest & Engineering Newsletter Subscription]',
            }),
        });

        if (res.ok) {
            subscribeStatus.value = 'success';
            trackNewsletterSignup();
            subscriberEmail.value = '';
        } else {
            subscribeStatus.value = 'error';
        }
    } catch {
        subscribeStatus.value = 'error';
    }
}
</script>

<template>
    <Head title="Engineering Insights & Architecture Blog — DigitalBuilders">
        <meta name="description" content="Deep dives into scalable system architecture, high-concurrency Laravel and Vue 3 engineering, autonomous AI agents, and enterprise software patterns." />
        <link rel="canonical" href="https://www.digitalbuilders.in/blog" />
        <meta property="og:title" content="Engineering Insights & Architecture Blog | DigitalBuilders" />
        <meta property="og:description" content="Deep dives into scalable system architecture, high-concurrency Laravel and Vue 3 engineering, autonomous AI agents, and enterprise software patterns." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/habuilt.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/blog" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <!-- Accessible Skip to Main Content Link (WCAG 2.4.1) -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-5 focus:py-2.5 focus:bg-primary focus:text-white focus:rounded-full focus:shadow-2xl focus:font-bold focus:outline-none focus:ring-4 focus:ring-sky-400"
        >
            Skip to main content
        </a>

        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <nav aria-label="Primary navigation" class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground min-h-[44px] inline-flex items-center px-2">← Home</Link>
                    <Link href="/pricing" class="hidden sm:inline-flex items-center text-sm font-medium text-muted-foreground hover:text-foreground min-h-[44px] px-2">Pricing</Link>
                    <Link href="/estimator" class="hidden rounded-full btn-primary px-4 py-2 min-h-[44px] text-xs font-semibold text-white sm:inline-flex items-center justify-center">
                        Cost Estimator
                    </Link>
                </nav>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Hero -->
            <div class="text-center max-w-3xl mx-auto">
                <span class="db-chip">Engineering Journal</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl">Architecture & Tech Insights</h1>
                <p class="mt-4 text-base text-muted-foreground">
                    Deep dives into scalable system architecture, high-performance web engineering, AI automation, and enterprise software patterns.
                </p>

                <!-- Category Filters (≥44px Touch Targets) -->
                <div class="mt-8 flex flex-wrap justify-center gap-2">
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="selectedCategory = cat"
                        :class="[
                            'rounded-full px-4 py-2 min-h-[44px] inline-flex items-center text-xs font-bold transition-all cursor-pointer',
                            selectedCategory === cat
                                ? 'bg-primary text-white shadow-md'
                                : 'bg-card border border-border text-muted-foreground hover:text-foreground hover:border-primary/50',
                        ]"
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
                    class="db-mini flex flex-col rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-lg transition hover:-translate-y-1"
                >
                    <img :src="post.cover_image" :alt="post.title" class="h-48 w-full object-cover" />
                    <div class="flex flex-1 flex-col justify-between p-6">
                        <div>
                            <div class="flex items-center justify-between text-xs text-sky-700 dark:text-sky-400">
                                <span class="font-bold uppercase tracking-wider">{{ post.category }}</span>
                                <span>{{ post.read_time }}</span>
                            </div>
                            <h2 class="mt-3 text-lg font-bold text-card-foreground leading-snug hover:text-primary transition-colors">
                                <Link :href="`/blog/${post.slug}`">{{ post.title }}</Link>
                            </h2>
                            <p class="mt-3 text-xs text-muted-foreground leading-relaxed line-clamp-3">{{ post.excerpt }}</p>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-border pt-4 text-xs">
                            <span class="text-muted-foreground">By {{ post.author }}</span>
                            <Link :href="`/blog/${post.slug}`" class="font-bold text-sky-700 dark:text-sky-400 hover:underline min-h-[44px] inline-flex items-center">Read Article →</Link>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Newsletter / Architecture Digest Lead Capture Box -->
            <section class="mt-16 rounded-3xl border border-border bg-gradient-to-br from-card via-card to-sky-500/5 p-8 sm:p-12 text-center max-w-3xl mx-auto shadow-xl">
                <span class="db-chip">Bi-Weekly Dispatch</span>
                <h3 class="mt-3 text-2xl font-bold text-card-foreground sm:text-3xl">Subscribe to the Architecture Digest</h3>
                <p class="mt-3 text-sm text-muted-foreground max-w-xl mx-auto">
                    Curated technical teardowns on high-concurrency Laravel systems, Vue 3 reactivity patterns, pgvector AI pipelines, and zero-downtime deployments. Zero spam.
                </p>

                <div v-if="subscribeStatus === 'success'" class="mt-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-sm font-semibold">
                    ✓ You're subscribed! Welcome to the DigitalBuilders Architecture Digest.
                </div>

                <form v-else @submit.prevent="handleSubscribe" class="mt-6 flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    <input
                        v-model="subscriberEmail"
                        type="email"
                        required
                        placeholder="your.email@company.com"
                        class="flex-1 rounded-full border border-border bg-background px-5 py-3 min-h-[44px] text-sm text-foreground placeholder:text-muted-foreground/60 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                    />
                    <button
                        type="submit"
                        :disabled="subscribeStatus === 'loading'"
                        class="btn-primary rounded-full px-6 py-3 min-h-[44px] text-xs font-bold text-white shadow-md transition hover:scale-105 disabled:opacity-50 cursor-pointer"
                    >
                        {{ subscribeStatus === 'loading' ? 'Subscribing...' : 'Join 2,500+ Engineers' }}
                    </button>
                </form>
            </section>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground mt-12">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
