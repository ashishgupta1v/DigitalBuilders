<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="db-input rounded-xl border border-[#b8c9e633] bg-[#223144cc] text-[#e7efff] shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] placeholder:text-[#9bb0d1] focus:border-[#97a7ff] focus:ring-[#97a7ff]"
        v-model="model"
        ref="input"
    />
</template>
