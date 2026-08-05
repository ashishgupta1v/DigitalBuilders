<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    project_type: '',
    estimated_budget: '',
    estimated_timeline: '',
    features: [] as string[],
    description: '',
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
    form.project_type = currentProjectTypeObj.value.label;
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

function formatMoney(val: number): string {
    const symbol = currency.value === 'INR' ? '₹' : '$';
    return `${symbol}${val.toLocaleString()}`;
}
</script>

<template>
    <div :class="props.isStandalone ? '' : 'db-antigravity-card rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-6 sm:p-8 lg:p-10 shadow-[0_20px_50px_rgba(10,16,24,0.35)]'">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="db-chip">Interactive Scope & Cost Engine</span>
                <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl">Project Cost & Timeline Estimator</h2>
                <p class="mt-1 text-sm text-slate-300">Configure your project requirements to calculate instant ballpark estimates.</p>
            </div>
            <!-- Currency Toggle -->
            <div class="flex items-center self-start rounded-full border border-[#b8c9e640] bg-[#1a2533] p-1">
                <button
                    @click="currency = 'INR'"
                    class="rounded-full px-3 py-1 text-xs font-bold transition"
                    :class="currency === 'INR' ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] text-[#1a2231]' : 'text-slate-400 hover:text-white'"
                >₹ INR</button>
                <button
                    @click="currency = 'USD'"
                    class="rounded-full px-3 py-1 text-xs font-bold transition"
                    :class="currency === 'USD' ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] text-[#1a2231]' : 'text-slate-400 hover:text-white'"
                >$ USD</button>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
            <!-- Controls -->
            <div class="space-y-6">
                <!-- 1. Project Type -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-[0.16em] text-[#9dc5ff]">1. Select Project Type</label>
                    <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                        <button
                            v-for="p in PROJECT_TYPES"
                            :key="p.id"
                            type="button"
                            @click="selectedType = p.id"
                            class="flex flex-col items-start rounded-2xl border p-4 text-left transition"
                            :class="selectedType === p.id
                                ? 'border-[#9ba7ff] bg-[#2d3f57] text-white shadow-[0_0_20px_rgba(155,167,255,0.25)]'
                                : 'border-[#b8c9e626] bg-[#1f2d3f90] text-slate-300 hover:border-[#b8c9e650]'"
                        >
                            <span class="text-sm font-bold text-white">{{ p.label }}</span>
                            <span class="mt-1 text-xs text-slate-400 leading-snug">{{ p.description }}</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Scale & Complexity -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-[0.16em] text-[#9dc5ff]">2. Select System Scale</label>
                    <div class="mt-3 grid gap-2.5 sm:grid-cols-3">
                        <button
                            v-for="s in SCALE_OPTIONS"
                            :key="s.id"
                            type="button"
                            @click="selectedScale = s.id"
                            class="flex flex-col items-start rounded-xl border p-3 text-left transition"
                            :class="selectedScale === s.id
                                ? 'border-[#9ba7ff] bg-[#2d3f57] text-white'
                                : 'border-[#b8c9e626] bg-[#1f2d3f90] text-slate-300 hover:border-[#b8c9e650]'"
                        >
                            <span class="text-xs font-bold text-white">{{ s.label }}</span>
                            <span class="mt-1 text-[11px] text-slate-400">{{ s.description }}</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Key Modules & Features -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-[0.16em] text-[#9dc5ff]">3. Additional Modules & Capabilities</label>
                    <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                        <button
                            v-for="f in FEATURES_LIST"
                            :key="f.id"
                            type="button"
                            @click="toggleFeature(f.id)"
                            class="flex items-center justify-between rounded-xl border p-3 text-left transition"
                            :class="selectedFeatures.includes(f.id)
                                ? 'border-[#c593ff] bg-[#342e47] text-white'
                                : 'border-[#b8c9e626] bg-[#1f2d3f90] text-slate-300 hover:border-[#b8c9e650]'"
                        >
                            <span class="text-xs font-medium text-white">{{ f.label }}</span>
                            <span
                                class="ml-2 shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold"
                                :class="selectedFeatures.includes(f.id) ? 'bg-[#c593ff] text-black' : 'bg-[#2b3a4f] text-slate-400'"
                            >{{ selectedFeatures.includes(f.id) ? '✓ Added' : '+ Add' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Live Calculation Summary Card -->
            <div class="db-dark-card flex flex-col justify-between rounded-2xl border border-[#9ba7ff44] bg-[linear-gradient(160deg,#243449,#1b2737)] p-6 shadow-[0_16px_40px_rgba(0,0,0,0.3)]">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9dc5ff]">Estimated Investment</p>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-white sm:text-4xl">
                            {{ formatMoney(calculatedTotal.min) }} — {{ formatMoney(calculatedTotal.max) }}
                        </span>
                        <p class="mt-1 text-xs text-slate-400">Includes system architecture, testing & 30 days post-launch support.</p>
                    </div>

                    <div class="my-6 h-px bg-[#b8c9e622]" />

                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Estimated Timeline:</span>
                            <span class="font-bold text-white">{{ calculatedDays }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Selected Type:</span>
                            <span class="font-bold text-[#b7d3ff]">{{ currentProjectTypeObj.label }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Scale Tier:</span>
                            <span class="font-bold text-[#b7d3ff]">{{ currentScaleObj.label }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Modules Selected:</span>
                            <span class="font-bold text-[#c593ff]">{{ selectedFeatures.length }} Features</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <button
                        @click="openInquiryModal"
                        class="w-full rounded-full border border-[#b8c9e640] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3.5 text-center text-sm font-bold text-[#1a2231] transition hover:brightness-110 shadow-lg"
                    >
                        Request Formal Proposal for this Scope →
                    </button>
                    <p class="text-center text-[11px] text-slate-400">No obligation. Guaranteed 24 business hour response.</p>
                </div>
            </div>
        </div>

        <!-- Modal overlay for submitting estimate inquiry -->
        <div v-if="showLeadModal" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/75 p-4 backdrop-blur-md">
            <div class="w-full max-w-lg rounded-3xl border border-[#b8c9e640] bg-[#1f2d3f] p-6 shadow-2xl sm:p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Submit Estimate Inquiry</h3>
                    <button @click="showLeadModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>
                <p class="mt-2 text-xs text-slate-300">
                    We'll attach your configured estimate ({{ form.estimated_budget }}, {{ form.estimated_timeline }}) directly to your inquiry.
                </p>

                <form @submit.prevent="submitInquiry" class="mt-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-200">Full Name *</label>
                        <input v-model="form.name" required type="text" placeholder="First and Last Name" class="mt-1 w-full rounded-xl border border-[#b8c9e633] bg-[#27374d] px-4 py-2.5 text-sm text-white focus:border-[#9ba7ff] focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-200">Email Address *</label>
                        <input v-model="form.email" required type="email" placeholder="you@company.com" class="mt-1 w-full rounded-xl border border-[#b8c9e633] bg-[#27374d] px-4 py-2.5 text-sm text-white focus:border-[#9ba7ff] focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-200">Phone Number *</label>
                        <input v-model="form.phone" required type="text" placeholder="+91 XXXXX XXXXX" class="mt-1 w-full rounded-xl border border-[#b8c9e633] bg-[#27374d] px-4 py-2.5 text-sm text-white focus:border-[#9ba7ff] focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-200">Additional Project Notes (Optional)</label>
                        <textarea v-model="form.description" rows="3" placeholder="Tell us more about your timeline or target launch date..." class="mt-1 w-full rounded-xl border border-[#b8c9e633] bg-[#27374d] px-4 py-2.5 text-sm text-white focus:border-[#9ba7ff] focus:outline-none"></textarea>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" @click="showLeadModal = false" class="flex-1 rounded-full border border-white/20 px-4 py-2.5 text-xs font-bold text-slate-300 hover:text-white">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-4 py-2.5 text-xs font-bold text-[#1a2231] hover:brightness-110 disabled:opacity-50">
                            {{ form.processing ? 'Submitting...' : 'Send Inquiry' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
