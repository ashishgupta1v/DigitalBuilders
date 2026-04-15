<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import DOMPurify from 'dompurify';
import { useMotion } from '@vueuse/motion';

const props = defineProps<{
    html: string;
}>();

const sanitizedHtml = ref('');
const containerRef = ref<HTMLElement | null>(null);

onMounted(() => {
    sanitizedHtml.value = DOMPurify.sanitize(props.html);

    if (containerRef.value) {
        useMotion(containerRef, {
            initial: { opacity: 0, y: 40 },
            enter: { opacity: 1, y: 0, transition: { duration: 600 } },
        });
    }
});
</script>

<template>
    <Head title="Documentation" />

    <div class="db-shell min-h-screen text-[#e7efff]">
        <div class="db-grid-overlay" />
        <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#bcd0ef]">Knowledge surface</p>
                    <h1 class="mt-2 text-3xl font-semibold text-white">Documentation</h1>
                </div>
                <span class="db-chip">Sanitized render</span>
            </div>

            <div
                ref="containerRef"
                class="db-panel db-reveal prose prose-invert prose-lg max-w-none rounded-[1.5rem] p-7"
                v-html="sanitizedHtml"
            />
        </div>
    </div>
</template>
