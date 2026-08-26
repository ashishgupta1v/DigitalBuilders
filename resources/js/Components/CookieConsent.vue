<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getStoredConsent, saveConsent, type ConsentSettings } from '@/utils/analytics';

const isVisible = ref(false);
const isCustomizing = ref(false);

const consent = ref<ConsentSettings>({
    necessary: true,
    analytics: true,
    marketing: false,
    updatedAt: '',
});

onMounted(() => {
    const existing = getStoredConsent();
    if (!existing) {
        // Show after a brief delay so it doesn't block initial LCP render
        setTimeout(() => {
            isVisible.value = true;
        }, 1400);
    } else {
        consent.value = existing;
    }

    window.addEventListener('db:open-cookie-settings', () => {
        isVisible.value = true;
        isCustomizing.value = true;
    });
});

function acceptAll() {
    consent.value = saveConsent({ necessary: true, analytics: true, marketing: true });
    isVisible.value = false;
    isCustomizing.value = false;
}

function acceptEssential() {
    consent.value = saveConsent({ necessary: true, analytics: false, marketing: false });
    isVisible.value = false;
    isCustomizing.value = false;
}

function saveCustom() {
    consent.value = saveConsent({
        necessary: true,
        analytics: consent.value.analytics,
        marketing: consent.value.marketing,
    });
    isVisible.value = false;
    isCustomizing.value = false;
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-6"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-6"
    >
        <div
            v-if="isVisible"
            role="dialog"
            aria-modal="false"
            aria-labelledby="cookie-consent-title"
            aria-describedby="cookie-consent-desc"
            class="fixed bottom-4 right-4 left-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-md z-50 rounded-3xl border border-border bg-card/98 text-card-foreground backdrop-blur-2xl p-6 shadow-2xl"
        >
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 id="cookie-consent-title" class="text-sm font-bold text-card-foreground">Privacy & Cookie Preferences</h3>
                    <p id="cookie-consent-desc" class="mt-1 text-xs text-muted-foreground leading-relaxed">
                        We use essential cookies for session security, plus optional telemetry to improve our architecture blueprints (compliant with <strong>India DPDP Act 2023</strong> & <strong>GDPR</strong>).
                    </p>
                </div>
            </div>

            <!-- Customization Panel -->
            <div v-if="isCustomizing" class="mt-4 space-y-2.5 pt-3 border-t border-border text-xs">
                <label class="flex items-center justify-between p-2.5 rounded-xl bg-secondary/70 border border-border">
                    <div>
                        <span class="font-bold text-card-foreground">Strictly Necessary</span>
                        <p class="text-[11px] text-muted-foreground">Required for routing, security, and CSRF protection.</p>
                    </div>
                    <input type="checkbox" checked disabled class="rounded text-sky-600 focus:ring-sky-500 opacity-60" />
                </label>

                <label class="flex items-center justify-between p-2.5 rounded-xl bg-secondary/70 border border-border cursor-pointer hover:bg-secondary transition-colors">
                    <div>
                        <span class="font-bold text-card-foreground">Analytics & Performance</span>
                        <p class="text-[11px] text-muted-foreground">Anonymous page load and Web Vitals telemetry.</p>
                    </div>
                    <input v-model="consent.analytics" type="checkbox" class="rounded text-sky-600 focus:ring-sky-500 cursor-pointer" />
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <template v-if="!isCustomizing">
                    <button
                        @click="acceptAll"
                        class="btn-primary flex-1 min-h-[44px] rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-md cursor-pointer"
                    >
                        Accept All
                    </button>
                    <button
                        @click="acceptEssential"
                        class="flex-1 min-h-[44px] rounded-xl border border-border bg-secondary px-3 py-2.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition cursor-pointer"
                    >
                        Essential Only
                    </button>
                    <button
                        @click="isCustomizing = true"
                        class="w-full text-center text-[11px] text-muted-foreground hover:text-sky-600 dark:hover:text-sky-400 py-1.5 transition cursor-pointer"
                    >
                        Customize Preferences
                    </button>
                </template>
                <template v-else>
                    <button
                        @click="saveCustom"
                        class="btn-primary flex-1 min-h-[44px] rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-md cursor-pointer"
                    >
                        Save Preferences
                    </button>
                    <button
                        @click="isCustomizing = false"
                        class="min-h-[44px] rounded-xl border border-border bg-secondary px-4 py-2.5 text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 cursor-pointer"
                    >
                        Back
                    </button>
                </template>
            </div>
        </div>
    </Transition>
</template>
