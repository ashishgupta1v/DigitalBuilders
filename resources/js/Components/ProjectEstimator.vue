<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    isStandalone?: boolean;
}>();

interface ProjectOption {
    id: string;
    label: string;
    basePriceUsd: number;
    basePriceInr: number;
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
    priceUsd: number;
    priceInr: number;
    days: number;
}

const PROJECT_TYPES: ProjectOption[] = [
    { id: 'web_app', label: 'Web Application', basePriceUsd: 1500, basePriceInr: 125000, baseDays: 14, description: 'Modern SPA/SSR web app with API integration' },
    { id: 'mobile_app', label: 'Mobile App (iOS & Android)', basePriceUsd: 2500, basePriceInr: 200000, baseDays: 21, description: 'Cross-platform native mobile experience' },
    { id: 'saas', label: 'SaaS Platform', basePriceUsd: 3500, basePriceInr: 290000, baseDays: 30, description: 'Multi-tenant platform with subscriptions' },
    { id: 'erp_crm', label: 'Enterprise ERP / CRM', basePriceUsd: 4500, basePriceInr: 375000, baseDays: 45, description: 'Custom operations engine & workflow system' },
    { id: 'ai_solutions', label: 'AI Voice/Chat Agent', basePriceUsd: 2000, basePriceInr: 165000, baseDays: 14, description: 'Autonomous agent & RAG pipeline' },
];

const SCALE_OPTIONS: ScaleOption[] = [
    { id: 'mvp', label: 'MVP / Startup', multiplier: 1.0, description: 'Fast validation, essential features, high performance' },
    { id: 'growth', label: 'Growth Business', multiplier: 1.4, description: 'Scalable architecture, enhanced security & monitoring' },
    { id: 'enterprise', label: 'Enterprise High-Scale', multiplier: 1.9, description: '99.99% uptime, microservices/modular monolith, multi-region' },
];

const FEATURES_LIST: FeatureOption[] = [
    { id: 'auth_oauth', label: 'Advanced Auth & OAuth / 2FA', priceUsd: 250, priceInr: 20000, days: 2 },
    { id: 'stripe_payments', label: 'Payments & Subscriptions (Stripe/Razorpay)', priceUsd: 400, priceInr: 33000, days: 3 },
    { id: 'realtime_ws', label: 'Real-time WebSockets & Live Push', priceUsd: 450, priceInr: 38000, days: 4 },
    { id: 'ai_copilot', label: 'AI Copilot / LLM Integration', priceUsd: 600, priceInr: 50000, days: 5 },
    { id: 'admin_analytics', label: 'Custom Admin Dashboard & Analytics', priceUsd: 500, priceInr: 42000, days: 4 },
    { id: 'i18n_multi_lang', label: 'Multi-language (i18n) Support', priceUsd: 300, priceInr: 25000, days: 2 },
];

const currency = ref<'INR' | 'USD'>('INR');
const selectedType = ref<string>('web_app');
const selectedScale = ref<string>('growth');
const selectedFeatures = ref<string[]>(['auth_oauth', 'admin_analytics']);

const showLeadModal = ref(false);

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

const calculatedTotal = computed(() => {
    let base = currency.value === 'INR' ? currentProjectTypeObj.value.basePriceInr : currentProjectTypeObj.value.basePriceUsd;

    const featureSum = selectedFeatures.value.reduce((acc, featId) => {
        const feat = FEATURES_LIST.find((f) => f.id === featId);
        if (!feat) return acc;
        return acc + (currency.value === 'INR' ? feat.priceInr : feat.priceUsd);
    }, 0);

    const subtotal = (base + featureSum) * currentScaleObj.value.multiplier;
    const min = Math.round(subtotal * 0.9);
    const max = Math.round(subtotal * 1.15);

    return { min, max };
});

const calculatedDays = computed(() => {
    let days = currentProjectTypeObj.value.baseDays;
    selectedFeatures.value.forEach((featId) => {
        const feat = FEATURES_LIST.find((f) => f.id === featId);
        if (feat) days += feat.days;
    });

    days = Math.round(days * (selectedScale.value === 'enterprise' ? 1.3 : 1.0));
    const weeksMin = Math.max(1, Math.floor(days / 7));
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

function openInquiryModal() {
    const symbol = currency.value === 'INR' ? '₹' : '$';
    form.project_type = currentProjectTypeObj.value.id;
    form.estimated_budget = `${symbol}${calculatedTotal.value.min.toLocaleString()} - ${symbol}${calculatedTotal.value.max.toLocaleString()} (${currency.value})`;
    form.estimated_timeline = calculatedDays.value;
    form.features = selectedFeatures.value.map((fId) => FEATURES_LIST.find((f) => f.id === fId)?.label ?? fId);
    showLeadModal.value = true;
}

function submitInquiry() {
    form.post(route('estimator.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            showLeadModal.value = false;
            form.reset();
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: 'Estimate inquiry submitted successfully! We will contact you within 24 hours.', type: 'success' },
            }));
        },
    });
}
function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Escape' && showLeadModal.value) {
        showLeadModal.value = false;
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

function formatMoney(val: number): string {
    const symbol = currency.value === 'INR' ? '₹' : '$';
    return `${symbol}${val.toLocaleString()}`;
}
</script>

<template>
    <div :class="props.isStandalone ? '' : 'db-antigravity-card rounded-3xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#172230] p-6 sm:p-8 lg:p-10 shadow-lg'">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="db-chip">Interactive Scope & Cost Engine</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Project Cost & Timeline Estimator</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Configure your project requirements to calculate instant ballpark estimates.</p>
            </div>
            <!-- Currency Toggle -->
            <div class="flex items-center self-start rounded-full border border-slate-300 dark:border-[#b8c9e640] bg-slate-100 dark:bg-[#101824] p-1">
                <button
                    @click="currency = 'INR'"
                    class="rounded-full px-4 py-1 text-xs font-bold transition-all cursor-pointer"
                    :class="currency === 'INR' ? 'bg-[linear-gradient(95deg,#0284c7,#4f46e5)] text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    ₹ INR
                </button>
                <button
                    @click="currency = 'USD'"
                    class="rounded-full px-4 py-1 text-xs font-bold transition-all cursor-pointer"
                    :class="currency === 'USD' ? 'bg-[linear-gradient(95deg,#0284c7,#4f46e5)] text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    $ USD
                </button>
            </div>
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
                                +{{ formatMoney(currency === 'INR' ? feat.priceInr : feat.priceUsd) }}
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
                        <p class="mt-1.5 text-xs text-slate-600 dark:text-slate-400">Includes system architecture, testing & 30 days post-launch warranty.</p>
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
                        class="db-action w-full rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3.5 text-center text-sm font-bold text-white dark:text-[#1a2231] transition hover:scale-[1.01] shadow-lg cursor-pointer"
                    >
                        Request Formal Proposal for this Scope →
                    </button>
                    <p class="text-center text-[11px] text-slate-500 dark:text-slate-400">No obligation. Guaranteed 24 business hour response.</p>
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
                        <button type="button" @click="showLeadModal = false" class="flex-1 rounded-full border border-slate-300 dark:border-white/20 px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-4 py-2.5 text-xs font-bold text-white dark:text-[#1a2231] hover:scale-[1.01] disabled:opacity-50 cursor-pointer">
                            {{ form.processing ? 'Submitting...' : 'Send Inquiry' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
