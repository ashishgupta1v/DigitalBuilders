<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#ff97b2]">
            {{ message }}
        </p>
    </div>
</template>
