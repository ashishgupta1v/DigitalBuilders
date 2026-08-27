<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import emblaCarouselVue from 'embla-carousel-vue';

interface TestimonialItem {
    id: number;
    client_name: string;
    company: string;
    role?: string;
    avatar?: string;
    rating: number;
    content: string;
    project_type?: string;
    metric_highlight?: string;
}

const testimonials = ref<TestimonialItem[]>([
    {
        id: 1,
        client_name: 'Gurpreet Singh',
        company: 'Habuilt Technologies',
        role: 'CTO & Co-Founder',
        avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=250',
        rating: 5,
        content: 'DigitalBuilders re-architected our high-traffic platform from the ground up. Ashish brought Silicon Valley-level engineering standards. Our server latencies dropped by 70% and database throughput doubled within weeks.',
        project_type: 'Custom Web Application',
        metric_highlight: '70% Latency Reduction',
    },
    {
        id: 2,
        client_name: 'Rajesh Sharma',
        company: 'ZoetiCoach AI',
        role: 'Managing Director',
        avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=250',
        rating: 5,
        content: 'We needed a complex custom ERP system integrated with automated AI habit verification. DigitalBuilders delivered ahead of schedule with zero legacy tech debt. Their architecture-first mindset is unmatched.',
        project_type: 'ERP / CRM & AI Automation',
        metric_highlight: '4x Operational Velocity',
    },
    {
        id: 3,
        client_name: 'Harpreet Singh',
        company: 'Dhanda Diary Cloud',
        role: 'Co-Founder & Product Lead',
        avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=250',
        rating: 5,
        content: 'Dhanda Diary transformed daily execution and habit discipline for our teams. The cockpit is responsive, fast (<50ms sync), and gives us instant visibility over every operational KPI.',
        project_type: 'Execution Cockpit & Compliance SaaS',
        metric_highlight: '+85% Routine Compliance',
    },
    {
        id: 4,
        client_name: 'Vikramaditya Verma',
        company: 'OmniFlow SaaS',
        role: 'Product Director',
        avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=250',
        rating: 5,
        content: 'Working with DigitalBuilders felt like having a Principal Architect embedded in our team. Their code quality, test coverage, and documentation set a gold standard for our engineering org.',
        project_type: 'SaaS Platform Architecture',
        metric_highlight: '99.99% System Uptime',
    },
]);

const [emblaRef, emblaApi] = emblaCarouselVue({
    loop: true,
    align: 'start',
    slidesToScroll: 1,
});

const selectedIndex = ref(0);
let autoPlayTimer: ReturnType<typeof setInterval> | null = null;

function onSelect() {
    if (!emblaApi.value) return;
    selectedIndex.value = emblaApi.value.selectedScrollSnap();
}

function scrollPrev() {
    emblaApi.value?.scrollPrev();
}

function scrollNext() {
    emblaApi.value?.scrollNext();
}

function scrollTo(index: number) {
    emblaApi.value?.scrollTo(index);
}

function startAutoPlay() {
    const prefersReducedMotion = typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    stopAutoPlay();
    autoPlayTimer = setInterval(() => {
        if (emblaApi.value) {
            emblaApi.value.scrollNext();
        }
    }, 6000);
}

function stopAutoPlay() {
    if (autoPlayTimer) {
        clearInterval(autoPlayTimer);
        autoPlayTimer = null;
    }
}

onMounted(() => {
    fetchTestimonials();
    if (emblaApi.value) {
        emblaApi.value.on('select', onSelect);
        onSelect();
    }
    startAutoPlay();
});

onBeforeUnmount(() => {
    stopAutoPlay();
});

async function fetchTestimonials() {
    try {
        const res = await fetch('/ajax/testimonials');
        if (res.ok) {
            const data = await res.json();
            if (Array.isArray(data) && data.length > 0) {
                testimonials.value = data;
                emblaApi.value?.reInit();
            }
        }
    } catch {
        // Fallback gracefully
    }
}
</script>

<template>
    <section
        aria-label="Client Testimonials and Social Proof"
        class="db-bento-card p-6 sm:p-8 lg:p-10 shadow-xl"
        @mouseenter="stopAutoPlay"
        @mouseleave="startAutoPlay"
    >
        <!-- Client Logo & Trust Marquee Ribbon -->
        <div class="mb-8 border-b border-border/80 pb-6">
            <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground font-semibold text-center mb-4">
                Trusted by Forward-Thinking Brands & Modern Enterprises
            </p>
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 opacity-80">
                <span class="text-sm sm:text-base font-extrabold text-foreground tracking-tight">HABUILT<span class="text-sky-500">.</span></span>
                <span class="text-sm sm:text-base font-extrabold text-foreground tracking-tight">Zoeti<span class="text-indigo-500">Coach</span></span>
                <span class="text-sm sm:text-base font-extrabold text-foreground tracking-tight">Gut<span class="text-emerald-500">Talks</span></span>
                <span class="text-sm sm:text-base font-extrabold text-foreground tracking-tight">Dhanda<span class="text-emerald-500">Diary</span></span>
                <span class="text-sm sm:text-base font-extrabold text-foreground tracking-tight">My<span class="text-purple-500">Astrova</span></span>
                <span class="text-sm sm:text-base font-extrabold text-foreground tracking-tight">Garg<span class="text-amber-500">Enterprises</span></span>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="db-badge-emerald">Verified Enterprise Testimonials</span>
                <h2 class="mt-3 text-2xl font-black text-card-foreground sm:text-3xl">What Digital Leaders Say</h2>
                <p class="mt-1 text-sm text-muted-foreground">Empirical feedback from business founders & tech executives.</p>
            </div>

            <!-- Controls (44px tap targets) -->
            <div class="flex items-center gap-2 self-start">
                <button
                    type="button"
                    @click="scrollPrev"
                    class="flex h-11 w-11 min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-full border border-border text-muted-foreground transition hover:border-primary hover:text-foreground focus-visible:ring-2 focus-visible:ring-primary"
                    aria-label="Previous testimonial slide"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button
                    type="button"
                    @click="scrollNext"
                    class="flex h-11 w-11 min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-full border border-border text-muted-foreground transition hover:border-primary hover:text-foreground focus-visible:ring-2 focus-visible:ring-primary"
                    aria-label="Next testimonial slide"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Embla Viewport -->
        <div class="mt-8 overflow-hidden" ref="emblaRef">
            <div class="flex touch-pan-y gap-6 select-none">
                <div
                    v-for="(item, idx) in testimonials"
                    :key="item.id || idx"
                    class="min-w-0 flex-[0_0_100%] space-y-6"
                >
                    <!-- Metric Chip + Rating -->
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-1 text-amber-500 text-sm" aria-label="5 star rating">
                            <span v-for="star in 5" :key="star">★</span>
                        </div>
                        <span
                            v-if="item.metric_highlight"
                            class="inline-flex items-center gap-1.5 rounded-full border border-purple-500/30 bg-purple-500/10 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300"
                        >
                            <svg class="h-3.5 w-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Impact: {{ item.metric_highlight }}
                        </span>
                    </div>

                    <!-- Quote Content -->
                    <blockquote class="text-base italic leading-relaxed text-card-foreground sm:text-lg">
                        "{{ item.content }}"
                    </blockquote>

                    <!-- Author Info -->
                    <div class="flex items-center justify-between border-t border-border pt-4">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="item.avatar"
                                :src="item.avatar"
                                :alt="item.client_name"
                                class="h-12 w-12 rounded-full object-cover border border-border"
                                loading="lazy"
                                decoding="async"
                                width="48"
                                height="48"
                            />
                            <div>
                                <p class="text-sm font-bold text-card-foreground">{{ item.client_name }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300">{{ item.role }} · <span class="text-sky-700 dark:text-sky-300 font-semibold">{{ item.company }}</span></p>
                            </div>
                        </div>
                        <span class="hidden text-xs text-slate-600 dark:text-slate-300 sm:inline-block font-medium">
                            Project: {{ item.project_type }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dots Indicator with Accessible 44px Touch Targets -->
        <div class="mt-8 flex items-center justify-center gap-1">
            <button
                v-for="(_, idx) in testimonials"
                :key="idx"
                type="button"
                @click="scrollTo(idx)"
                class="flex h-11 w-11 min-h-[44px] min-w-[44px] items-center justify-center rounded-full transition-all cursor-pointer focus-visible:ring-2 focus-visible:ring-primary"
                :aria-label="`Go to slide ${idx + 1}`"
            >
                <span
                    class="h-2.5 rounded-full transition-all"
                    :class="selectedIndex === idx ? 'w-8 bg-primary' : 'w-2.5 bg-muted-foreground/30 hover:bg-muted-foreground/60'"
                />
            </button>
        </div>
    </section>
</template>
