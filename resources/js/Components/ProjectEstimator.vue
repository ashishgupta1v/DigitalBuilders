<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { detectUserRegion, REGIONS, type RegionMode, saveUserRegion } from '@/utils/geo';
import { trackEstimatorSubmit, trackEstimatorConfigured, trackWhatsAppClick } from '@/utils/analytics';

const props = defineProps<{
    isStandalone?: boolean;
}>();

interface ProjectOption {
    id: string;
    label: string;
    basePriceInr: number;
    basePriceGulf: number;
    basePriceUsd: number;
    baseDays: number;
    description: string;
}

interface ScaleOption {
    id: string;
    label: string;
    multiplier: number;
    description: string;
}

interface FeatureOption {
    id: string;
    label: string;
    priceInr: number;
    priceGulf: number;
    priceUsd: number;
    days: number;
}

const PROJECT_TYPES: ProjectOption[] = [
    {
        id: 'web_app',
        label: 'Web Application',
        basePriceInr: 99000,
        basePriceGulf: 2500,
        basePriceUsd: 3500,
        baseDays: 14,
        description: 'Modern reactive SPA/SSR platform with robust API & RBAC',
    },
    {
        id: 'ai_solutions',
        label: 'AI Voice/Chat Agent',
        basePriceInr: 129000,
        basePriceGulf: 3200,
        basePriceUsd: 4500,
        baseDays: 14,
        description: 'Sub-second inbound/outbound telephony & RAG embeddings',
    },
    {
        id: 'mobile_app',
        label: 'Mobile App (iOS & Android)',
        basePriceInr: 149000,
        basePriceGulf: 4200,
        basePriceUsd: 6000,
        baseDays: 21,
        description: 'Cross-platform native mobile experience with offline sync',
    },
    {
        id: 'saas',
        label: 'SaaS Platform',
        basePriceInr: 249000,
        basePriceGulf: 6500,
        basePriceUsd: 9000,
        baseDays: 30,
        description: 'Multi-tenant subscription architecture with customer billing',
    },
    {
        id: 'erp_crm',
        label: 'Enterprise ERP / CRM',
        basePriceInr: 299000,
        basePriceGulf: 7900,
        basePriceUsd: 11000,
        baseDays: 45,
        description: 'Custom inventory, WhatsApp ERP bots, multi-warehouse & GST engine',
    },
];

const SCALE_OPTIONS: ScaleOption[] = [
    {
        id: 'mvp',
        label: '🚀 Launch / MVP',
        multiplier: 1.0,
        description: 'Validate & ship essential core workflows with high performance',
    },
    {
        id: 'growth',
        label: '📈 Growth Business ★',
        multiplier: 1.55,
        description: 'Scale-ready, staging + production CI/CD, Redis & 30-day warranty',
    },
    {
        id: 'enterprise',
        label: '🏢 Enterprise Scale',
        multiplier: 2.35,
        description: '99.99% SLA, microservices/modular monolith, SSO & compliance',
    },
];

const FEATURES_LIST: FeatureOption[] = [
    {
        id: 'auth_oauth',
        label: 'Advanced Auth & Multi-Provider SSO / 2FA',
        priceInr: 15000,
        priceGulf: 300,
        priceUsd: 450,
        days: 2,
    },
    {
        id: 'stripe_payments',
        label: 'Payments & Subscriptions (Razorpay/Stripe)',
        priceInr: 25000,
        priceGulf: 500,
        priceUsd: 700,
        days: 3,
    },
    {
        id: 'realtime_ws',
        label: 'Real-time WebSockets & Live Push Alerts',
        priceInr: 30000,
        priceGulf: 600,
        priceUsd: 800,
        days: 4,
    },
    {
        id: 'ai_copilot',
        label: 'AI Copilot / LLM RAG Vector Search',
        priceInr: 40000,
        priceGulf: 900,
        priceUsd: 1200,
        days: 5,
    },
    {
        id: 'admin_analytics',
        label: 'Executive Analytics & Management Reports',
        priceInr: 25000,
        priceGulf: 400,
        priceUsd: 600,
        days: 4,
    },
    {
        id: 'i18n_multi_lang',
        label: 'Multi-Language (i18n / Arabic RTL)',
        priceInr: 20000,
        priceGulf: 400,
        priceUsd: 500,
        days: 2,
    },
];

const currency = ref<RegionMode>('INR');
const selectedType = ref<string>('web_app');
const selectedScale = ref<string>('growth');
const selectedFeatures = ref<string[]>(['stripe_payments', 'admin_analytics']);

const showLeadModal = ref(false);
const modalSubmissionError = ref<string | null>(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: 'web_app',
    estimated_budget: '',
    estimated_timeline: '',
    features: [] as string[],
    description: '',
    _hp_company: '',
});

const currentProjectTypeObj = computed(() =>
    PROJECT_TYPES.find((p) => p.id === selectedType.value) ?? PROJECT_TYPES[0]
);

const currentScaleObj = computed(() =>
    SCALE_OPTIONS.find((s) => s.id === selectedScale.value) ?? SCALE_OPTIONS[1]
);

const currentRegionInfo = computed(() => REGIONS[currency.value]);

function getBasePrice(proj: ProjectOption): number {
    if (currency.value === 'INR') return proj.basePriceInr;
    if (currency.value === 'GULF') return proj.basePriceGulf;
    return proj.basePriceUsd;
}

function getFeaturePrice(feat: FeatureOption): number {
    if (currency.value === 'INR') return feat.priceInr;
    if (currency.value === 'GULF') return feat.priceGulf;
    return feat.priceUsd;
}

const calculatedTotal = computed(() => {
    const base = getBasePrice(currentProjectTypeObj.value);

    const featureSum = selectedFeatures.value.reduce((acc, featId) => {
        const feat = FEATURES_LIST.find((f) => f.id === featId);
        if (!feat) return acc;
        return acc + getFeaturePrice(feat);
    }, 0);

    const subtotal = (base + featureSum) * currentScaleObj.value.multiplier;
    const min = Math.round(subtotal * 0.95);
    const max = Math.round(subtotal * 1.12);

    return { min, max };
});

const calculatedDays = computed(() => {
    let days = currentProjectTypeObj.value.baseDays;
    selectedFeatures.value.forEach((featId) => {
        const feat = FEATURES_LIST.find((f) => f.id === featId);
        if (feat) days += feat.days;
    });

    days = Math.round(days * (selectedScale.value === 'enterprise' ? 1.3 : 1.0));
    const weeksMin = Math.max(2, Math.floor(days / 7));
    const weeksMax = Math.ceil(days / 7) + 1;

    return `${weeksMin} - ${weeksMax} Weeks`;
});

function toggleFeature(id: string) {
    if (selectedFeatures.value.includes(id)) {
        selectedFeatures.value = selectedFeatures.value.filter((item) => item !== id);
    } else {
        selectedFeatures.value.push(id);
    }
}

function setRegion(r: RegionMode) {
    currency.value = r;
    saveUserRegion(r);
}

function openInquiryModal() {
    modalSubmissionError.value = null;
    const symbol = currentRegionInfo.value.currencySymbol;
    form.project_type = currentProjectTypeObj.value.id;
    form.estimated_budget = `${symbol}${calculatedTotal.value.min.toLocaleString()} - ${symbol}${calculatedTotal.value.max.toLocaleString()} (${currency.value})`;
    form.estimated_timeline = calculatedDays.value;
    form.features = selectedFeatures.value.map((fId) => FEATURES_LIST.find((f) => f.id === fId)?.label ?? fId);
    showLeadModal.value = true;
    trackEstimatorConfigured(form.project_type, form.estimated_budget, form.estimated_timeline);
}

const modalFallbackWhatsAppUrl = computed(() => {
    const lines = [
        'Hi Ashish, I configured an estimate on DigitalBuilders and want to discuss it directly:',
        `• Name: ${form.name || 'Not provided'}`,
        `• Email: ${form.email || 'Not provided'}`,
        `• Phone: ${form.phone || 'Not provided'}`,
        `• Project: ${currentProjectTypeObj.value.label}`,
        `• Budget: ${form.estimated_budget}`,
        `• Timeline: ${form.estimated_timeline}`,
        `• Selected Modules: ${selectedFeatures.value.join(', ') || 'Core only'}`,
        form.description ? `• Notes: ${form.description}` : '',
    ].filter(Boolean).join('\n');

    return 'https://wa.me/919087021592?text=' + encodeURIComponent(lines);
});

function submitInquiry() {
    if (form.processing) return;
    modalSubmissionError.value = null;

    const projId = currentProjectTypeObj.value.id;
    const budgetVal = form.estimated_budget;

    form.post(route('estimator.submit'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            modalSubmissionError.value = null;
            trackEstimatorSubmit(projId, budgetVal);
            showLeadModal.value = false;
            form.reset();
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: 'Estimate inquiry submitted successfully! We will contact you within 24 hours.', type: 'success' },
            }));
        },
        onError: (errors) => {
            if (errors && Object.keys(errors).length > 0) {
                const firstKey = Object.keys(errors)[0];
                const msg = errors.message || errors.error || errors[firstKey];
                modalSubmissionError.value = typeof msg === 'string' ? msg : 'Please review and correct the fields above.';
            } else {
                modalSubmissionError.value = 'Submission could not be completed. You may have reached the rate limit — please send your estimate directly via WhatsApp.';
            }
        },
    });
}

function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Escape' && showLeadModal.value) {
        showLeadModal.value = false;
    }
}

onMounted(() => {
    const detected = detectUserRegion();
    currency.value = detected;

    // Check query parameters if arriving with preset service or region
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const typeParam = params.get('type');
        const regionParam = params.get('region')?.toUpperCase();

        if (typeParam && PROJECT_TYPES.some((p) => p.id === typeParam)) {
            selectedType.value = typeParam;
        }
        if (regionParam && (regionParam === 'INR' || regionParam === 'GULF' || regionParam === 'USD')) {
            currency.value = regionParam as RegionMode;
        }
    }

    window.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

function formatMoney(val: number): string {
    const symbol = currentRegionInfo.value.currencySymbol;
    return `${symbol}${val.toLocaleString()}`;
}
</script>

<template>
    <div :class="props.isStandalone ? '' : 'db-antigravity-card rounded-3xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#172230] p-6 sm:p-8 lg:p-10 shadow-lg'">
        <!-- Header & Price Book Switcher -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="db-chip">Decoupled Regional Price Book</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Project Cost & Timeline Estimator</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Instant scope ballpark with zero foreign exchange currency markups.</p>
            </div>

            <!-- 3-Way Regional Book Switcher -->
            <div class="flex flex-wrap items-center self-start rounded-full border border-slate-300 dark:border-[#b8c9e640] bg-slate-100 dark:bg-[#101824] p-1 gap-1">
                <button
                    v-for="region in Object.values(REGIONS)"
                    :key="region.id"
                    type="button"
                    @click="setRegion(region.id)"
                    :class="[
                        'rounded-full px-3.5 py-1 text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5',
                        currency === region.id
                            ? 'bg-[linear-gradient(95deg,#0284c7,#4f46e5)] text-white shadow'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white',
                    ]"
                >
                    <span>{{ region.flag }}</span>
                    <span>{{ region.currencyCode }}</span>
                </button>
            </div>
        </div>

        <!-- Full Price Book Link Banner -->
        <div class="mt-5 flex items-center justify-between rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60 px-4 py-2.5 text-xs text-sky-800 dark:text-sky-300">
            <span>📍 Active Price Book: <strong>{{ currentRegionInfo.label }}</strong> ({{ currentRegionInfo.taxNote }})</span>
            <Link href="/pricing" class="font-bold underline hover:text-sky-950 dark:hover:text-white shrink-0 ml-2">
                View Full Price Book →
            </Link>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.5fr_1fr]">
            <!-- Controls Column -->
            <div class="space-y-6">
                <!-- 1. Project Type Selector -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">1. Select Architecture Archetype</label>
                    <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        <button
                            v-for="opt in PROJECT_TYPES"
                            :key="opt.id"
                            type="button"
                            @click="selectedType = opt.id"
                            class="flex flex-col items-start rounded-2xl border p-3.5 text-left transition-all cursor-pointer"
                            :class="selectedType === opt.id
                                ? 'border-sky-500 dark:border-[#9ba7ff] bg-sky-50 dark:bg-[#24354a] shadow-[0_0_15px_rgba(2,132,199,0.15)] dark:shadow-[0_0_15px_rgba(155,167,255,0.2)]'
                                : 'border-slate-200 dark:border-[#b8c9e622] bg-slate-50 dark:bg-[#121c27] hover:border-slate-300 dark:hover:border-[#b8c9e644]'"
                        >
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ opt.label }}</span>
                            <span class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ opt.description }}</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Scale Tier Selector -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">2. Select Scale & Complexity Tier</label>
                    <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        <button
                            v-for="scale in SCALE_OPTIONS"
                            :key="scale.id"
                            type="button"
                            @click="selectedScale = scale.id"
                            class="flex flex-col items-start rounded-2xl border p-3 text-left transition-all cursor-pointer"
                            :class="selectedScale === scale.id
                                ? 'border-sky-500 dark:border-[#9ba7ff] bg-sky-50 dark:bg-[#24354a] shadow-[0_0_15px_rgba(2,132,199,0.15)] dark:shadow-[0_0_15px_rgba(155,167,255,0.2)]'
                                : 'border-slate-200 dark:border-[#b8c9e622] bg-slate-50 dark:bg-[#121c27] hover:border-slate-300 dark:hover:border-[#b8c9e644]'"
                        >
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ scale.label }}</span>
                            <span class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">{{ scale.description }}</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Add-on Architectural Modules -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">3. Architectural Modules & Add-ons</label>
                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div
                            v-for="feat in FEATURES_LIST"
                            :key="feat.id"
                            @click="toggleFeature(feat.id)"
                            class="flex items-center justify-between rounded-xl border p-2.5 transition-all cursor-pointer select-none"
                            :class="selectedFeatures.includes(feat.id)
                                ? 'border-purple-400 dark:border-[#c593ff] bg-purple-50 dark:bg-[#231b33]'
                                : 'border-slate-200 dark:border-[#b8c9e618] bg-slate-50 dark:bg-[#101720] hover:border-slate-300 dark:hover:border-[#b8c9e633]'"
                        >
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-4 w-4 items-center justify-center rounded border"
                                    :class="selectedFeatures.includes(feat.id) ? 'border-purple-600 bg-purple-600 dark:border-[#c593ff] dark:bg-[#c593ff] text-white dark:text-[#1a2231]' : 'border-slate-300 dark:border-slate-600'"
                                >
                                    <span v-if="selectedFeatures.includes(feat.id)" class="text-[10px] font-black">✓</span>
                                </div>
                                <span class="text-xs text-slate-800 dark:text-slate-200">{{ feat.label }}</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                +{{ formatMoney(getFeaturePrice(feat)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Calculation Summary Card -->
            <div class="flex flex-col justify-between rounded-2xl border border-sky-200 dark:border-[#9ba7ff44] bg-[linear-gradient(160deg,#f0f9ff,#e0e7ff)] dark:bg-[linear-gradient(160deg,#1c2838,#121b27)] p-6 shadow-md">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-700 dark:text-[#9dc5ff]">Estimated Investment</p>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-slate-900 dark:text-white sm:text-4xl">
                            {{ formatMoney(calculatedTotal.min) }} — {{ formatMoney(calculatedTotal.max) }}
                        </span>
                        <p class="mt-1.5 text-xs text-slate-600 dark:text-slate-400">Fixed all-inclusive scope · Full test coverage · 30-day post-launch warranty.</p>
                    </div>

                    <div class="my-6 h-px bg-slate-300/60 dark:bg-[#b8c9e622]" />

                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Estimated Timeline:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ calculatedDays }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Selected Type:</span>
                            <span class="font-bold text-sky-700 dark:text-[#b7d3ff]">{{ currentProjectTypeObj.label }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Scale Tier:</span>
                            <span class="font-bold text-sky-700 dark:text-[#b7d3ff]">{{ currentScaleObj.label }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Modules Selected:</span>
                            <span class="font-bold text-purple-700 dark:text-[#c593ff]">{{ selectedFeatures.length }} Features</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <button
                        @click="openInquiryModal"
                        class="db-action w-full min-h-[44px] rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3.5 text-center text-sm font-bold text-white dark:text-[#1a2231] transition hover:scale-[1.01] shadow-lg cursor-pointer"
                    >
                        Request Formal Proposal for this Scope →
                    </button>
                    <p class="text-center text-[11px] text-slate-500 dark:text-slate-400">Guaranteed response from Lead Architect within 24 hours.</p>
                </div>
            </div>
        </div>

        <!-- Modal overlay for submitting estimate inquiry -->
        <div
            v-if="showLeadModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="estimator-inquiry-modal-title"
            class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-md"
            @click.self="showLeadModal = false"
        >
            <div class="w-full max-w-lg rounded-3xl border border-slate-200 dark:border-[#b8c9e640] bg-white dark:bg-[#1f2d3f] p-6 shadow-2xl sm:p-8">
                <div class="flex items-center justify-between">
                    <h3 id="estimator-inquiry-modal-title" class="text-xl font-bold text-slate-900 dark:text-white">Submit Estimate Inquiry</h3>
                    <button @click="showLeadModal = false" aria-label="Close modal" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                </div>
                <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                    We'll attach your configured estimate ({{ form.estimated_budget }}, {{ form.estimated_timeline }}) directly to your inquiry.
                </p>

                <!-- Modal Error Alert Banner -->
                <div
                    v-if="modalSubmissionError"
                    role="alert"
                    aria-live="assertive"
                    class="mt-4 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-xs text-rose-800 dark:text-rose-200 space-y-2.5"
                >
                    <div class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="font-semibold leading-relaxed">{{ modalSubmissionError }}</p>
                    </div>
                    <div class="pt-1">
                        <a
                            :href="modalFallbackWhatsAppUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-[11px] transition shadow"
                        >
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            Send Estimate on WhatsApp
                        </a>
                    </div>
                </div>

                <form @submit.prevent="submitInquiry" class="mt-6 space-y-4">
                    <input v-model="form._hp_company" type="text" name="_hp_company" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true" style="display:none !important;" />
                    <div>
                        <label for="estimator-name" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Full Name *</label>
                        <input
                            id="estimator-name"
                            name="name"
                            v-model="form.name"
                            required
                            type="text"
                            autocomplete="name"
                            placeholder="First and Last Name"
                            class="mt-1 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e633] bg-slate-50 dark:bg-[#27374d] px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-sky-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label for="estimator-email" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Email Address *</label>
                        <input
                            id="estimator-email"
                            name="email"
                            v-model="form.email"
                            required
                            type="email"
                            autocomplete="email"
                            placeholder="you@company.com"
                            class="mt-1 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e633] bg-slate-50 dark:bg-[#27374d] px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-sky-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label for="estimator-phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Phone Number *</label>
                        <input
                            id="estimator-phone"
                            name="phone"
                            v-model="form.phone"
                            required
                            type="tel"
                            autocomplete="tel"
                            placeholder="+91 XXXXX XXXXX"
                            class="mt-1 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e633] bg-slate-50 dark:bg-[#27374d] px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-sky-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label for="estimator-description" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Additional Project Notes (Optional)</label>
                        <textarea
                            id="estimator-description"
                            name="description"
                            v-model="form.description"
                            rows="3"
                            placeholder="Tell us more about your timeline or target launch date..."
                            class="mt-1 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e633] bg-slate-50 dark:bg-[#27374d] px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-sky-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" @click="showLeadModal = false" class="flex-1 min-h-[44px] rounded-full border border-slate-300 dark:border-white/20 px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 min-h-[44px] rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-4 py-2.5 text-xs font-bold text-white dark:text-[#1a2231] hover:scale-[1.01] disabled:opacity-50 cursor-pointer">
                            {{ form.processing ? 'Submitting...' : 'Send Inquiry' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
