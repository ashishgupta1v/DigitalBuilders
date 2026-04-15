<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
    ? 'block w-full rounded-xl border border-[#b8c9e655] bg-[linear-gradient(95deg,rgba(122,196,255,0.2),rgba(155,167,255,0.16),rgba(197,147,255,0.2))] px-4 py-2 text-start text-sm font-semibold uppercase tracking-[0.18em] text-[#e7efff] transition duration-200 ease-out'
    : 'block w-full rounded-xl border border-transparent px-4 py-2 text-start text-sm font-semibold uppercase tracking-[0.18em] text-[#b4c3de] transition duration-200 ease-out hover:border-[#b8c9e644] hover:bg-[#27364a] hover:text-[#e7efff]',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
