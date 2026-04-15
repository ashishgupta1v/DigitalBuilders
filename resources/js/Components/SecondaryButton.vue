<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="db-action inline-flex items-center justify-center rounded-full border border-[#b8c9e640] bg-[#2b3a4fd9] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#e7efff] shadow-[0_10px_22px_rgba(12,17,25,0.28)] transition duration-200 ease-out hover:border-[#c3a8ff66] hover:bg-[#31435be6] focus:outline-none focus:ring-2 focus:ring-[#a6b0ff] focus:ring-offset-0 disabled:opacity-30"
    >
        <slot />
    </button>
</template>
