<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { trackBrochureDownload, trackEvent } from '@/utils/analytics';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const isSubmitted = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: 'web_app',
    source: 'brochure',
    region: 'INR',
    description: '[Requested 2026 Price Book via Lead Magnet Modal]',
    _hp_company: '',
});

function handleClose() {
    emit('close');
}

function handleDirectDownload(edition: 'inr' | 'usd') {
    trackBrochureDownload(edition, edition === 'inr' ? 'INR' : 'USD');
    trackEvent('lead_magnet_direct_download', { edition });
    const url = edition === 'inr'
        ? '/downloads/digitalbuilders-pricing-india-inr.pdf'
        : '/downloads/digitalbuilders-pricing-international-usd.pdf';
    window.open(url, '_blank');
}

function submitLeadAndDownload(edition: 'inr' | 'usd') {
    form.region = edition === 'inr' ? 'INR' : 'USD';
    form.source = 'brochure';
    
    if (form.email || form.phone) {
        form.post(route('library.leads.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                isSubmitted.value = true;
                handleDirectDownload(edition);
            },
            onError: () => {
                // Fallback to direct download anyway so the user is never blocked
                handleDirectDownload(edition);
            },
        });
    } else {
        handleDirectDownload(edition);
    }
}
</script>

<template>
    <Modal :show="show" max-width="lg" @close="handleClose">
        <div class="relative overflow-hidden rounded-3xl border border-border bg-card p-6 sm:p-8 text-card-foreground shadow-2xl">
            <!-- Top Accent Gradient -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-purple-500" />

            <!-- Close Button -->
            <button
                type="button"
                @click="handleClose"
                class="absolute top-5 right-5 flex h-9 w-9 items-center justify-center rounded-full border border-border bg-secondary text-muted-foreground hover:text-foreground transition cursor-pointer"
                aria-label="Close brochure modal"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-500/20 to-indigo-500/20 border border-sky-500/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold text-2xl shadow-sm">
                    📄
                </div>
                <span class="db-badge-sky mt-4 mb-1">Official Technical Guide</span>
                <h2 class="text-xl sm:text-2xl font-black text-card-foreground">
                    2026 Architectural Price Book & Specification Catalogue
                </h2>
                <p class="mt-2 text-xs sm:text-sm text-muted-foreground max-w-md mx-auto">
                    Get instant access to our 15-page architectural specification brochure detailing modules, SLAs, deliverables, and fixed milestone pricing.
                </p>
            </div>

            <!-- Quick Download Buttons (Zero Friction) -->
            <div class="mt-6 space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground text-center">
                    Select Your Edition for Instant View / Download
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="handleDirectDownload('inr')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-sky-500/40 bg-sky-500/10 px-4 py-3.5 text-xs font-bold text-sky-800 dark:text-sky-300 hover:bg-sky-500/20 transition shadow-sm cursor-pointer"
                    >
                        <span>🇮🇳 India Edition (INR ₹)</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </button>
                    <button
                        type="button"
                        @click="handleDirectDownload('usd')"
                        class="btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-xs font-bold text-white transition shadow-sm cursor-pointer"
                    >
                        <span>🌍 Global Edition (USD $)</span>
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </button>
                </div>
            </div>

            <!-- Optional Email Delivery Box -->
            <div class="mt-6 pt-5 border-t border-border/80">
                <div class="flex items-center gap-2 mb-2 text-xs font-semibold text-card-foreground">
                    <span>✉️</span>
                    <span>Want updates & custom project estimates sent to your inbox?</span>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="Enter your work email (optional)"
                        class="flex-1 rounded-xl border border-border bg-secondary/50 px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="submitLeadAndDownload('inr')"
                        class="rounded-xl border border-border bg-secondary hover:bg-secondary/80 text-foreground font-bold px-4 py-2.5 text-xs transition cursor-pointer"
                    >
                        Send Copy
                    </button>
                </div>
                <p class="mt-2 text-[10px] text-muted-foreground text-center">
                    🔒 Zero spam. We adhere strictly to DPDP Act 2023 & GDPR principles.
                </p>
            </div>
        </div>
    </Modal>
</template>
