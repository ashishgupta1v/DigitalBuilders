<script setup lang="ts">
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { trackEvent, trackWhatsAppClick } from '@/utils/analytics';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const selectedMethod = ref<'cal' | 'whatsapp'>('cal');
const iframeLoading = ref(true);

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        trackEvent('booking_modal_opened', { location: 'interactive_modal' });
    }
});

function handleClose() {
    emit('close');
}

function handleIframeLoaded() {
    iframeLoading.value = false;
}
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="handleClose">
        <div class="relative overflow-hidden rounded-3xl border border-border bg-card p-6 sm:p-8 text-card-foreground shadow-2xl">
            <!-- Top Gradient Accent Line -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-purple-500" />

            <!-- Close Button -->
            <button
                type="button"
                @click="handleClose"
                class="absolute top-5 right-5 flex h-9 w-9 items-center justify-center rounded-full border border-border bg-secondary text-muted-foreground hover:text-foreground transition cursor-pointer"
                aria-label="Close booking modal"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Header -->
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-sky-500/20 to-indigo-500/20 border border-sky-500/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold text-xl shrink-0">
                    📅
                </div>
                <div>
                    <span class="db-badge-emerald mb-1">Direct Architect Booking</span>
                    <h2 class="text-xl sm:text-2xl font-black text-card-foreground">
                        Schedule a 30-Min Architecture Session
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-muted-foreground">
                        1-on-1 discovery with Lead Digital Architect Ashish Gupta (10+ Years Enterprise Experience).
                    </p>
                </div>
            </div>

            <!-- Value Highlights Pills -->
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs text-muted-foreground">
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-secondary/50 border border-border">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">✓</span>
                    <span>100% Free Mutual NDA</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-secondary/50 border border-border">
                    <span class="text-sky-600 dark:text-sky-400 font-bold">✓</span>
                    <span>System Bottleneck Audit</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-secondary/50 border border-border">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                    <span>Fixed Scope & Timeline</span>
                </div>
            </div>

            <!-- Method Switcher Tabs -->
            <div class="mt-6 flex items-center gap-2 border-b border-border pb-3">
                <button
                    type="button"
                    @click="selectedMethod = 'cal'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2',
                        selectedMethod === 'cal'
                            ? 'btn-primary text-white shadow-md'
                            : 'bg-secondary text-secondary-foreground hover:text-foreground'
                    ]"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Instant Calendar Slot (Cal.com)</span>
                </button>
                <button
                    type="button"
                    @click="selectedMethod = 'whatsapp'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2',
                        selectedMethod === 'whatsapp'
                            ? 'bg-[#25d366] text-white shadow-md'
                            : 'bg-secondary text-secondary-foreground hover:text-foreground'
                    ]"
                >
                    <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                    <span>WhatsApp Fast-Track</span>
                </button>
            </div>

            <!-- Tab 1: Cal.com Embed / Direct Link -->
            <div v-show="selectedMethod === 'cal'" class="mt-4">
                <div class="relative min-h-[380px] w-full rounded-2xl border border-border bg-background overflow-hidden flex flex-col items-center justify-center p-4 text-center">
                    <iframe
                        src="https://cal.com/digitalbuilders/30min?embed=true"
                        title="Cal.com Booking Interface"
                        class="w-full h-[380px] border-0 rounded-2xl"
                        @load="handleIframeLoaded"
                    />
                    <div class="mt-3 pt-3 border-t border-border w-full flex items-center justify-between text-xs text-muted-foreground">
                        <span>Prefer opening in a separate window?</span>
                        <a
                            href="https://cal.com/digitalbuilders/30min"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-bold text-sky-600 dark:text-sky-400 hover:underline inline-flex items-center gap-1"
                        >
                            Open Cal.com Fullscreen ↗
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tab 2: WhatsApp Fast-Track -->
            <div v-show="selectedMethod === 'whatsapp'" class="mt-4 space-y-4">
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-5 text-sm text-emerald-950 dark:text-emerald-200 leading-relaxed">
                    <p class="font-bold text-base">💬 Need an immediate response?</p>
                    <p class="mt-1 text-xs">
                        Skip calendar booking and message Lead Architect Ashish Gupta directly on WhatsApp with your project scope. Typically answered within 1–2 hours during business hours.
                    </p>
                </div>
                <a
                    href="https://wa.me/919087021592?text=Hi%20Ashish,%20I'd%20like%20to%20schedule%20a%2030-minute%20system%20architecture%20session%20for%20my%20project."
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="trackWhatsAppClick('booking_modal_direct')"
                    class="flex items-center justify-center gap-2.5 w-full rounded-full bg-[#25d366] hover:bg-[#20ba5a] text-white font-bold py-3.5 px-6 text-sm transition shadow-lg hover:scale-[1.02] cursor-pointer"
                >
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                    <span>Chat Directly with Ashish on WhatsApp</span>
                </a>
            </div>
        </div>
    </Modal>
</template>
