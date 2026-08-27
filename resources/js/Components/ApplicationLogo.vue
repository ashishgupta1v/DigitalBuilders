<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    size?: 'sm' | 'md' | 'lg' | 'xl';
    showText?: boolean;
    textClass?: string;
    isLink?: boolean;
    href?: string;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    showText: true,
    textClass: '',
    isLink: false,
    href: '/',
});
</script>

<template>
    <component
        :is="isLink ? Link : 'div'"
        :href="isLink ? href : undefined"
        class="inline-flex items-center gap-2.5 group transition-opacity hover:opacity-95"
    >
        <img
            src="/images/db-logo.png"
            alt="DigitalBuilders Logo"
            :class="[
                'object-contain flex-shrink-0 transition-transform duration-300 group-hover:scale-105 filter-none dark:filter dark:drop-shadow-[0_0_10px_rgba(125,211,252,0.3)]',
                size === 'sm' ? 'h-7 w-7' : size === 'lg' ? 'h-11 w-11' : size === 'xl' ? 'h-16 w-16' : 'h-9 w-9 sm:h-10 sm:w-10'
            ]"
            onerror="this.style.display='none'"
        />
        <span
            v-if="showText"
            :class="[
                'db-brand-logo-text font-black tracking-tight',
                size === 'sm' ? 'text-base' : size === 'lg' ? 'text-2xl' : size === 'xl' ? 'text-3xl' : 'text-lg sm:text-xl',
                textClass
            ]"
            style="font-family: 'Libre Baskerville', Georgia, serif; font-weight: 700; letter-spacing: -0.02em;"
        >
            Digital Builders
        </span>
    </component>
</template>
