<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="block w-full rounded-xl px-4 py-2.5 text-start text-xs font-semibold uppercase tracking-[0.18em] text-[#d9e5fa] transition duration-200 ease-out hover:bg-[#31435b] hover:text-white focus:bg-[#31435b] focus:text-white focus:outline-none"
    >
        <slot />
    </Link>
</template>
