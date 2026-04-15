<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
    ? 'inline-flex items-center rounded-full border border-[#b8c9e655] bg-[linear-gradient(95deg,rgba(122,196,255,0.2),rgba(155,167,255,0.16),rgba(197,147,255,0.2))] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-[#edf4ff] transition duration-200 ease-out'
    : 'inline-flex items-center rounded-full border border-transparent px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-[#b4c3de] transition duration-200 ease-out hover:border-[#b8c9e644] hover:bg-[#2b3a4f8f] hover:text-[#edf4ff]',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
