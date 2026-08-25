<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

export type CaseStudy = {
    id: string;
    client: string;
    portfolioSlug: string;
    category: 'saas' | 'health' | 'consumer' | 'mobile' | 'ngo';
    categoryLabel: string;
    liveUrl: string | null;
    isMobile: boolean;
    image: string;
    metricBadge: string;
    techStack: string[];
    tldr: string;
    problem: string;
    challenge: string;
    architectureActions: string[];
    businessImpact: string[];
    quote: string;
    quoteAuthor: string;
};

const props = defineProps<{
    study: CaseStudy | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const modalContainer = ref<HTMLElement | null>(null);

function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.study) {
        emit('close');
    }
}

watch(() => props.study, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            modalContainer.value?.focus();
        }, 50);
    }
});

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="study"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-case-study-title"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10 bg-black/80 backdrop-blur-md overflow-y-auto"
                @click.self="emit('close')"
            >
                <div
                    ref="modalContainer"
                    tabindex="-1"
                    class="relative w-full max-w-3xl rounded-3xl border border-slate-200 dark:border-[#b8c9e640] bg-white dark:bg-[#182332] p-6 sm:p-8 shadow-2xl max-h-[90vh] overflow-y-auto outline-none"
                >
                    <!-- Close button -->
                    <button
                        @click="emit('close')"
                        class="absolute top-5 right-5 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/20 transition-colors cursor-pointer"
                        aria-label="Close case study preview modal"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <!-- Header -->
                    <div class="pr-10">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-sky-500/15 border border-sky-500/30 px-3 py-1 text-xs font-bold text-sky-600 dark:text-sky-300">
                                {{ study.categoryLabel }}
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                ⚡ {{ study.metricBadge }}
                            </span>
                        </div>
                        <h2 id="modal-case-study-title" class="mt-3 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            {{ study.client }}
                        </h2>
                        <p class="mt-2 text-sm sm:text-base text-slate-600 dark:text-slate-300">
                            {{ study.tldr }}
                        </p>
                    </div>

                    <!-- Live Snapshot Image Preview -->
                    <div class="mt-5 relative aspect-video w-full overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10 shadow-lg bg-slate-950">
                        <img
                            :src="study.image"
                            :alt="`${study.client} production interface preview`"
                            class="h-full w-full object-cover object-top"
                        />
                        <div class="absolute bottom-3 left-3 bg-black/75 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold text-white border border-white/20">
                            Production Interface Snapshot
                        </div>
                    </div>

                    <!-- Tech Stack Tags -->
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span
                            v-for="tech in study.techStack"
                            :key="tech"
                            class="rounded-lg bg-slate-100 dark:bg-[#1f2d3f] border border-slate-200 dark:border-white/10 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ tech }}
                        </span>
                    </div>

                    <!-- Problem & Challenge -->
                    <div class="mt-6 space-y-3 rounded-2xl bg-slate-50 dark:bg-[#141e2b] p-4 text-sm text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-white/5">
                        <div>
                            <strong class="text-slate-900 dark:text-white font-bold">The Problem: </strong>
                            {{ study.problem }}
                        </div>
                        <div>
                            <strong class="text-slate-900 dark:text-white font-bold">The Challenge: </strong>
                            {{ study.challenge }}
                        </div>
                    </div>

                    <!-- Architecture Actions -->
                    <div class="mt-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">Our Architectural Solution</h3>
                        <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li
                                v-for="(action, idx) in study.architectureActions"
                                :key="idx"
                                class="flex items-start gap-2.5"
                            >
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-500" />
                                <span>{{ action }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Business Impact -->
                    <div class="mt-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Measurable Business Impact</h3>
                        <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li
                                v-for="(impact, idx) in study.businessImpact"
                                :key="idx"
                                class="flex items-start gap-2.5"
                            >
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500" />
                                <span>{{ impact }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Client Quote -->
                    <blockquote class="mt-6 rounded-2xl border border-purple-200 dark:border-purple-900/50 bg-purple-50 dark:bg-[#201830] p-4 text-sm text-purple-900 dark:text-purple-200 italic">
                        "{{ study.quote }}"
                        <footer class="mt-2 text-xs font-bold text-purple-700 dark:text-purple-300 not-italic">
                            — {{ study.quoteAuthor }}
                        </footer>
                    </blockquote>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-200 dark:border-white/10">
                        <div class="flex flex-wrap items-center gap-2">
                            <a
                                v-if="study.liveUrl"
                                :href="study.liveUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_100%)] px-5 py-2.5 text-xs font-bold text-white dark:text-[#1a2231] shadow transition hover:scale-105"
                            >
                                <span>Visit Live Application</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <Link
                                :href="`/portfolio/${study.portfolioSlug}`"
                                @click="emit('close')"
                                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 dark:border-white/20 bg-slate-100 dark:bg-white/5 px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-white transition hover:bg-slate-200 dark:hover:bg-white/10"
                            >
                                <span>Open Dedicated Case Study Page</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </Link>
                        </div>
                        <button
                            @click="emit('close')"
                            class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors cursor-pointer"
                        >
                            Close Preview
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
