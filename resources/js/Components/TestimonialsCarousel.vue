<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

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

const currentIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

function next() {
    currentIndex.value = (currentIndex.value + 1) % testimonials.value.length;
}

function prev() {
    currentIndex.value = (currentIndex.value - 1 + testimonials.value.length) % testimonials.value.length;
}

function goTo(idx: number) {
    currentIndex.value = idx;
}

function startTimer() {
    const prefersReducedMotion = typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    if (!timer) {
        timer = setInterval(next, 6500);
    }
}

function stopTimer() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

onMounted(() => {
    fetchTestimonials();
    startTimer();
});

onBeforeUnmount(() => {
    stopTimer();
});

async function fetchTestimonials() {
    try {
        const res = await fetch('/ajax/testimonials');
        if (res.ok) {
            const data = await res.json();
            if (Array.isArray(data) && data.length > 0) {
                testimonials.value = data;
            }
        }
    } catch {
        // Fallback gracefully to static array
    }
}
</script>

<template>
    <div
        class="db-mini rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-6 sm:p-8 lg:p-10 shadow-[0_20px_50px_rgba(10,16,24,0.3)]"
        @mouseenter="stopTimer"
        @mouseleave="startTimer"
    >
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="db-chip">Verified Social Proof</span>
                <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl">What Digital Leaders Say</h2>
                <p class="mt-1 text-sm text-slate-300">Empirical feedback from business founders & tech executives.</p>
            </div>

            <!-- Controls (44px tap targets) -->
            <div class="flex items-center gap-2 self-start">
                <button
                    @click="prev"
                    class="flex h-11 w-11 min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-full border border-[#b8c9e633] text-slate-300 transition hover:border-[#9ba7ff] hover:text-white focus-visible:ring-2 focus-visible:ring-sky-400"
                    aria-label="Previous testimonial"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button
                    @click="next"
                    class="flex h-11 w-11 min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-full border border-[#b8c9e633] text-slate-300 transition hover:border-[#9ba7ff] hover:text-white focus-visible:ring-2 focus-visible:ring-sky-400"
                    aria-label="Next testimonial"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Carousel Content Area -->
        <div class="mt-8 relative overflow-hidden min-h-[220px]">
            <Transition
                mode="out-in"
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-x-8"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="opacity-0 -translate-x-8"
            >
                <div :key="currentIndex" class="space-y-6">
                    <!-- Metric Chip + Rating -->
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            <span v-for="star in 5" :key="star">★</span>
                        </div>
                        <span
                            v-if="testimonials[currentIndex].metric_highlight"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#c593ff44] bg-[#c593ff18] px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-[#d8c3ff]"
                        >
                            <svg class="h-3.5 w-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Impact: {{ testimonials[currentIndex].metric_highlight }}
                        </span>
                    </div>

                    <!-- Quote Content -->
                    <blockquote class="text-base italic leading-relaxed text-slate-100 sm:text-lg">
                        "{{ testimonials[currentIndex].content }}"
                    </blockquote>

                    <!-- Author Info -->
                    <div class="flex items-center justify-between border-t border-[#b8c9e622] pt-4">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="testimonials[currentIndex].avatar"
                                :src="testimonials[currentIndex].avatar"
                                :alt="testimonials[currentIndex].client_name"
                                class="h-12 w-12 rounded-full object-cover border border-[#9ba7ff44]"
                                loading="lazy"
                                decoding="async"
                                width="48"
                                height="48"
                            />
                            <div>
                                <p class="text-sm font-bold text-white">{{ testimonials[currentIndex].client_name }}</p>
                                <p class="text-xs text-slate-400">{{ testimonials[currentIndex].role }} · <span class="text-[#b7d3ff]">{{ testimonials[currentIndex].company }}</span></p>
                            </div>
                        </div>
                        <span class="hidden text-xs text-slate-400 sm:inline-block">
                            Project: {{ testimonials[currentIndex].project_type }}
                        </span>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- Dots Indicator with Accessible 44px Touch Targets -->
        <div class="mt-8 flex items-center justify-center gap-1">
            <button
                v-for="(_, idx) in testimonials"
                :key="idx"
                @click="goTo(idx)"
                class="flex h-11 w-11 min-h-[44px] min-w-[44px] items-center justify-center rounded-full transition-all cursor-pointer focus-visible:ring-2 focus-visible:ring-sky-400"
                :aria-label="`Go to slide ${idx + 1}`"
            >
                <span
                    class="h-2.5 rounded-full transition-all"
                    :class="currentIndex === idx ? 'w-8 bg-[#9ba7ff]' : 'w-2.5 bg-[#b8c9e644] hover:bg-slate-400'"
                />
            </button>
        </div>
    </div>
</template>
