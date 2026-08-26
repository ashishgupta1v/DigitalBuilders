<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { trackWhatsAppClick, trackEvent } from '@/utils/analytics';

const isVisible = ref(false);

function handleScroll() {
    if (typeof window === 'undefined') return;
    isVisible.value = window.scrollY > 320;
}

function onWhatsAppClick() {
    trackWhatsAppClick('sticky_mobile_cta', {
        page_location: window.location.pathname,
    });
}

function onEstimateClick() {
    trackEvent('cta_click', {
        source: 'sticky_mobile_cta',
        target: 'estimator_or_contact',
    });
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <aside
            v-if="isVisible"
            role="region"
            aria-label="Quick mobile contact actions"
            class="fixed bottom-0 left-0 right-0 z-40 block md:hidden border-t border-border bg-card/95 backdrop-blur-xl px-4 py-3 shadow-[0_-10px_30px_rgba(0,0,0,0.2)] pb-[max(0.75rem,env(safe-area-inset-bottom))]"
        >
            <div class="mx-auto flex max-w-lg items-center justify-between gap-3">
                <!-- Direct WhatsApp Fast Action -->
                <a
                    href="https://wa.me/919087021592?text=Hi%20Ashish,%20I'm%20on%20DigitalBuilders%20and%20would%20like%20to%20discuss%20a%20project."
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="onWhatsAppClick"
                    class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-[#25d366] px-4 py-3 min-h-[46px] text-xs font-bold text-white shadow-[0_4px_15px_rgba(37,211,102,0.3)] transition-transform active:scale-95"
                >
                    <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/>
                    </svg>
                    <span>WhatsApp Us</span>
                </a>

                <!-- Get Estimate / Quote Action -->
                <a
                    href="#contact"
                    @click="onEstimateClick"
                    class="btn-primary flex flex-1 items-center justify-center gap-1.5 rounded-2xl px-4 py-3 min-h-[46px] text-xs font-bold text-white shadow-[0_4px_15px_rgba(2,132,199,0.25)] transition-transform active:scale-95 text-center"
                >
                    <span>Get Free Quote &rarr;</span>
                </a>
            </div>
        </aside>
    </Transition>
</template>
