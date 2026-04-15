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

    <div class="min-h-screen bg-white">
        <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
            <div
                ref="containerRef"
                class="prose prose-lg max-w-none"
                v-html="sanitizedHtml"
            />
        </div>
    </div>
</template>
