<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

interface ToastItem {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
}

const toasts = ref<ToastItem[]>([]);
let counter = 0;

function add(message: string, type: ToastItem['type'] = 'info') {
    const id = ++counter;
    toasts.value.push({ id, message, type });
    setTimeout(() => remove(id), 4500);
}

function remove(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

function onToast(event: Event) {
    const detail = (event as CustomEvent<{ message: string; type?: ToastItem['type'] }>).detail;
    add(detail.message, detail.type ?? 'info');
}

onMounted(() => {
    window.addEventListener('db:toast', onToast);
});

onBeforeUnmount(() => {
    window.removeEventListener('db:toast', onToast);
});

// expose for direct use
defineExpose({ add });
</script>

<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed bottom-5 right-5 z-[9000] flex flex-col gap-3">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto db-toast flex items-start gap-3 cursor-pointer"
                    @click="remove(toast.id)"
                >
                    <!-- Icon -->
                    <span class="mt-0.5 shrink-0 text-base" aria-hidden="true">
                        <span v-if="toast.type === 'success'" class="text-[var(--db-success)]">✓</span>
                        <span v-else-if="toast.type === 'error'" class="text-[var(--db-danger)]">✕</span>
                        <span v-else class="text-[var(--db-accent-c)]">ℹ</span>
                    </span>
                    <p class="text-sm leading-snug">{{ toast.message }}</p>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active {
    transition: all 0.32s cubic-bezier(0.22, 0.68, 0, 1.2);
}
.toast-leave-active {
    transition: all 0.24s ease-in;
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.95);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(20px) scale(0.95);
}
</style>
