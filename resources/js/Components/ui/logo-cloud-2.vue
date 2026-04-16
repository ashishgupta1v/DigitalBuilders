<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

type Logo = {
    src: string;
    alt: string;
    width?: number;
    height?: number;
};

defineProps<{
    className?: string;
}>();

const logos: Logo[] = [
    { src: 'https://svgl.app/library/nvidia-wordmark-light.svg', alt: 'Nvidia Logo' },
    { src: 'https://svgl.app/library/supabase_wordmark_light.svg', alt: 'Supabase Logo' },
    { src: 'https://svgl.app/library/github_wordmark_light.svg', alt: 'GitHub Logo' },
    { src: 'https://svgl.app/library/openai_wordmark_light.svg', alt: 'OpenAI Logo' },
    { src: 'https://svgl.app/library/turso-wordmark-light.svg', alt: 'Turso Logo' },
    { src: 'https://svgl.app/library/clerk-wordmark-light.svg', alt: 'Clerk Logo' },
    { src: 'https://svgl.app/library/claude-ai-wordmark-icon_light.svg', alt: 'Claude AI Logo' },
    { src: 'https://svgl.app/library/vercel_wordmark.svg', alt: 'Vercel Logo' },
];
</script>

<template>
    <div :class="cn('relative grid grid-cols-2 overflow-hidden rounded-2xl border border-[#b8c9e633] md:grid-cols-4', className)">
        <div class="pointer-events-none absolute -top-px left-1/2 w-screen -translate-x-1/2 border-t border-[#b8c9e633]" />

        <div
            v-for="(logo, idx) in logos"
            :key="logo.alt"
            :class="cn(
                'relative flex items-center justify-center border-[#b8c9e633] bg-[#223246cc] px-4 py-8 md:p-8',
                idx % 2 === 0 ? 'bg-[#27374de0]' : '',
                idx < 4 ? 'border-b' : '',
                idx % 4 !== 3 ? 'md:border-r' : '',
                idx % 2 === 0 ? 'border-r md:border-r' : '',
            )"
        >
            <img
                :alt="logo.alt"
                :height="logo.height ?? 20"
                :src="logo.src"
                :width="logo.width ?? 160"
                class="pointer-events-none h-4 select-none brightness-0 invert md:h-5"
                loading="lazy"
            />

            <Plus
                v-if="idx === 0 || idx === 2"
                class="absolute -bottom-[12px] -right-[12px] z-10 size-6 text-[#bfd2ff]"
                :stroke-width="1"
            />

            <Plus
                v-if="idx === 2"
                class="absolute -bottom-[12px] -left-[12px] z-10 hidden size-6 text-[#bfd2ff] md:block"
                :stroke-width="1"
            />

            <Plus
                v-if="idx === 4"
                class="absolute -bottom-[12px] -right-[12px] z-10 size-6 text-[#bfd2ff] md:hidden"
                :stroke-width="1"
            />
        </div>

        <div class="pointer-events-none absolute -bottom-px left-1/2 w-screen -translate-x-1/2 border-b border-[#b8c9e633]" />
    </div>
</template>
