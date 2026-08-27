<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CookieConsent from '@/Components/CookieConsent.vue';
import { trackEvent, trackWhatsAppClick, trackBookingCompleted } from '@/utils/analytics';
import { detectUserRegion, type RegionMode } from '@/utils/geo';

const activeTab = ref<'scheduler' | 'whatsapp'>('scheduler');
const isBooked = ref(false);
const activeRegion = ref<RegionMode>('INR');

// Date generation: next 5 business days
const availableDates = computed(() => {
    const dates = [];
    const now = new Date();
    let current = new Date(now);
    
    while (dates.length < 5) {
        current.setDate(current.getDate() + 1);
        const day = current.getDay();
        if (day !== 0 && day !== 6) {
            const dateStr = current.toISOString().split('T')[0];
            const formatted = current.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
            });
            dates.push({
                raw: dateStr,
                label: formatted,
            });
        }
    }
    return dates;
});

const timeSlots = [
    { id: '11:00 AM', label: '11:00 AM IST (Morning)', startHour: 11, startMin: 0 },
    { id: '03:00 PM', label: '03:00 PM IST (Afternoon)', startHour: 15, startMin: 0 },
    { id: '06:30 PM', label: '06:30 PM IST (Gulf / Evening)', startHour: 18, startMin: 30 },
    { id: '09:00 PM', label: '09:00 PM IST (US East 11:30 AM)', startHour: 21, startMin: 0 },
];

const selectedDate = ref(availableDates.value[0]?.raw || '');
const selectedTime = ref('03:00 PM');

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: 'web_app',
    source: 'booking',
    region: 'INR',
    description: '',
    _hp_company: '',
});

function handleBookSlot() {
    const slotInfo = `[Confirmed 30-Min Architecture Call] Date: ${selectedDate.value} at ${selectedTime.value}`;
    form.description = `${slotInfo} | Scope: ${form.description || 'System Architecture Review'}`;
    form.source = 'booking';
    form.region = activeRegion.value;

    form.post(route('library.leads.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isBooked.value = true;
            trackBookingCompleted('30min_architecture_session', activeRegion.value, {
                date: selectedDate.value,
                time: selectedTime.value,
            });
        },
        onError: () => {
            isBooked.value = true;
        },
    });
}

const googleCalendarUrl = computed(() => {
    const slot = timeSlots.find((s) => s.id === selectedTime.value) || timeSlots[1];
    const dateParts = selectedDate.value.split('-');
    if (dateParts.length < 3) return '#';

    const year = parseInt(dateParts[0], 10);
    const month = parseInt(dateParts[1], 10) - 1;
    const day = parseInt(dateParts[2], 10);

    const startUtc = new Date(Date.UTC(year, month, day, slot.startHour - 5, slot.startMin - 30));
    const endUtc = new Date(startUtc.getTime() + 30 * 60 * 1000);

    const formatCalDate = (d: Date) => d.toISOString().replace(/-|:|\.\d+/g, '');
    const dates = `${formatCalDate(startUtc)}/${formatCalDate(endUtc)}`;

    const title = encodeURIComponent('30-Min System Architecture Session — DigitalBuilders');
    const details = encodeURIComponent('1-on-1 discovery with Lead Architect Ashish Gupta.\nMeeting Link / Call: WhatsApp +91 9087021592 or Google Meet.');
    const location = encodeURIComponent('Google Meet / Video Call');

    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${dates}&details=${details}&location=${location}`;
});

function handleWhatsAppDirect() {
    trackWhatsAppClick('book_page_fast_track', {
        intent: 'direct_architect_consultation',
    });
    trackBookingCompleted('whatsapp_fast_track', 'direct');
}

onMounted(() => {
    activeRegion.value = detectUserRegion();
    form.region = activeRegion.value;
    trackEvent('book_page_viewed', { path: '/book' });
});
</script>

<template>
    <Head>
        <title>Book a Free Architecture Consultation — DigitalBuilders</title>
        <meta name="description" content="Schedule a 30-minute 1-on-1 technical discovery consultation with Lead Architect Ashish Gupta. Zero-obligation system bottleneck audit and fixed roadmap." />
        <meta property="og:title" content="Book a Free Architecture Consultation — DigitalBuilders" />
        <meta property="og:description" content="Schedule a 30-minute 1-on-1 technical discovery consultation with Lead Architect Ashish Gupta. System audit, roadmap & fixed pricing." />
        <meta property="og:image" content="/images/habuilt.jpg" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="min-h-screen bg-background text-foreground selection:bg-primary/20 selection:text-primary relative overflow-hidden flex flex-col justify-between">
        <!-- Accessible Skip to Content -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-primary focus:text-primary-foreground focus:rounded-xl focus:shadow-2xl focus:outline-none focus:ring-2 focus:ring-ring">
            Skip to booking content
        </a>

        <!-- Ambient Background Glows -->
        <div class="pointer-events-none absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-b from-sky-500/10 via-indigo-500/5 to-transparent blur-3xl" />
        <div class="pointer-events-none absolute top-1/2 -right-40 w-96 h-96 bg-purple-500/10 blur-3xl" />

        <!-- Header -->
        <header class="relative z-20 border-b border-border/80 bg-background/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-3 transition-opacity hover:opacity-90 min-h-[44px]">
                    <ApplicationLogo class="h-9 w-auto" />
                    <div class="flex flex-col">
                        <span class="text-base font-black tracking-tight text-foreground">DigitalBuilders</span>
                        <span class="text-[10px] uppercase font-mono tracking-widest text-muted-foreground">Architect Booking</span>
                    </div>
                </Link>

                <div class="flex items-center gap-3">
                    <Link
                        href="/"
                        class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors px-3 py-2 rounded-xl hover:bg-secondary min-h-[44px]"
                    >
                        ← Back to Home
                    </Link>
                    <a
                        href="https://wa.me/919087021592?text=Hi%20Ashish,%20I'd%20like%20to%20schedule%20an%20architecture%20consultation"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="handleWhatsAppDirect"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#25d366]/15 hover:bg-[#25d366]/25 border border-[#25d366]/40 text-[#128c7e] dark:text-[#25d366] px-3.5 py-2 text-xs font-bold transition min-h-[44px]"
                    >
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>WhatsApp Fast-Track</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main id="main-content" class="relative z-10 mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8 flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                
                <!-- Left Column: Architect Profile & Value Proposition -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2">
                            <span class="db-badge-emerald">Free 30-Min Discovery</span>
                            <span class="text-xs font-mono text-muted-foreground">Direct · Confidential</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-foreground">
                            Let's architect your high-scale software.
                        </h1>
                        <p class="text-sm sm:text-base text-muted-foreground leading-relaxed">
                            Book a 1-on-1 technical discovery call with Lead Architect <strong class="text-foreground">Ashish Gupta</strong>. We’ll audit your bottlenecks, evaluate stack trade-offs, and define a fixed-scope roadmap.
                        </p>
                    </div>

                    <!-- Trust Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3.5">
                        <div class="rounded-2xl border border-border bg-card p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-700 dark:text-sky-400 font-bold">
                                    🛡️
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-card-foreground">100% Free Mutual NDA</h2>
                                    <p class="text-xs text-muted-foreground">Your IP and architecture ideas remain strictly protected.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-border bg-card p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold">
                                    ⚡
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-card-foreground">System Audit & Scope Map</h2>
                                    <p class="text-xs text-muted-foreground">Receive a concrete execution blueprint with milestone pricing.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-border bg-card p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-bold">
                                    👨‍💻
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-card-foreground">Founder & Senior Architect</h2>
                                    <p class="text-xs text-muted-foreground">No sales reps or account managers. Real engineering leadership.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verifiable Proof Strip -->
                    <div class="rounded-2xl border border-border/80 bg-secondary/30 p-5 space-y-3">
                        <span class="text-[11px] font-mono uppercase tracking-widest text-muted-foreground">Proven Production Track Record</span>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-xl bg-background/80 border border-border">
                                <span class="block text-base font-black text-sky-600 dark:text-sky-400">99.99%</span>
                                <span class="text-[10px] text-muted-foreground">Uptime Architecture</span>
                            </div>
                            <div class="p-2 rounded-xl bg-background/80 border border-border">
                                <span class="block text-base font-black text-emerald-600 dark:text-emerald-400">14%→0%</span>
                                <span class="text-[10px] text-muted-foreground">ERP Error Reductions</span>
                            </div>
                            <div class="p-2 rounded-xl bg-background/80 border border-border">
                                <span class="block text-base font-black text-indigo-600 dark:text-indigo-400">3.2×</span>
                                <span class="text-[10px] text-muted-foreground">Booking Conversion</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Booking Slot Picker & Form -->
                <div class="lg:col-span-7">
                    <div class="relative overflow-hidden rounded-3xl border border-border bg-card text-card-foreground shadow-2xl">
                        <!-- Top Gradient Stripe -->
                        <div class="h-1.5 w-full bg-gradient-to-r from-sky-500 via-indigo-500 to-purple-500" />

                        <!-- Method Switcher Tab Bar -->
                        <div class="flex items-center justify-between border-b border-border p-4 sm:p-6 bg-secondary/20">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="activeTab = 'scheduler'"
                                    :class="[
                                        'px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 min-h-[44px]',
                                        activeTab === 'scheduler'
                                            ? 'btn-primary text-white shadow-md'
                                            : 'bg-secondary text-secondary-foreground hover:text-foreground'
                                    ]"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Pick Date & Time Slot</span>
                                </button>
                                <button
                                    type="button"
                                    @click="activeTab = 'whatsapp'"
                                    :class="[
                                        'px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 min-h-[44px]',
                                        activeTab === 'whatsapp'
                                            ? 'bg-[#25d366] text-white shadow-md'
                                            : 'bg-secondary text-secondary-foreground hover:text-foreground'
                                    ]"
                                >
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    <span>Instant WhatsApp</span>
                                </button>
                            </div>
                            <span class="hidden sm:inline-block text-[11px] font-mono text-muted-foreground">Direct Confirmation</span>
                        </div>

                        <!-- Scheduler View -->
                        <div v-show="activeTab === 'scheduler'" class="p-6 sm:p-8">
                            <form v-if="!isBooked" @submit.prevent="handleBookSlot" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-foreground mb-2">
                                        1. Select Preferred Date
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                        <button
                                            v-for="d in availableDates"
                                            :key="d.raw"
                                            type="button"
                                            @click="selectedDate = d.raw"
                                            :class="[
                                                'p-3 rounded-xl border text-xs font-medium transition cursor-pointer text-center min-h-[44px]',
                                                selectedDate === d.raw
                                                    ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm'
                                                    : 'border-border bg-secondary/40 text-muted-foreground hover:text-foreground'
                                            ]"
                                        >
                                            {{ d.label }}
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-foreground mb-2">
                                        2. Select Time Window
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <button
                                            v-for="slot in timeSlots"
                                            :key="slot.id"
                                            type="button"
                                            @click="selectedTime = slot.id"
                                            :class="[
                                                'p-3 rounded-xl border text-xs font-medium transition cursor-pointer text-left flex items-center justify-between min-h-[44px]',
                                                selectedTime === slot.id
                                                    ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm'
                                                    : 'border-border bg-secondary/40 text-muted-foreground hover:text-foreground'
                                            ]"
                                        >
                                            <span>{{ slot.label }}</span>
                                            <span v-if="selectedTime === slot.id" class="text-primary font-bold">●</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label for="page_book_name" class="block text-[11px] font-semibold text-muted-foreground mb-1">Your Full Name *</label>
                                        <input
                                            id="page_book_name"
                                            v-model="form.name"
                                            type="text"
                                            required
                                            placeholder="e.g. John Doe"
                                            class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                                        />
                                    </div>
                                    <div>
                                        <label for="page_book_email" class="block text-[11px] font-semibold text-muted-foreground mb-1">Work Email *</label>
                                        <input
                                            id="page_book_email"
                                            v-model="form.email"
                                            type="email"
                                            required
                                            placeholder="john@company.com"
                                            class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="page_book_phone" class="block text-[11px] font-semibold text-muted-foreground mb-1">Phone / WhatsApp *</label>
                                        <input
                                            id="page_book_phone"
                                            v-model="form.phone"
                                            type="tel"
                                            required
                                            placeholder="+91 98765 43210"
                                            class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                                        />
                                    </div>
                                    <div>
                                        <label for="page_book_type" class="block text-[11px] font-semibold text-muted-foreground mb-1">Project Category</label>
                                        <select
                                            id="page_book_type"
                                            v-model="form.project_type"
                                            class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                                        >
                                            <option value="web_app">Web Application</option>
                                            <option value="mobile_app">Mobile App (iOS/Android)</option>
                                            <option value="erp_crm">ERP / Custom CRM</option>
                                            <option value="saas">High-Scale SaaS Platform</option>
                                            <option value="ai_solutions">AI Agent & LLM Architecture</option>
                                            <option value="other">Architecture & Performance Audit</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="page_book_desc" class="block text-[11px] font-semibold text-muted-foreground mb-1">Brief Project Goal or Bottleneck</label>
                                    <input
                                        id="page_book_desc"
                                        v-model="form.description"
                                        type="text"
                                        placeholder="e.g. Scaling Postgres database or building MVP from scratch"
                                        class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                                    />
                                </div>

                                <div class="pt-2">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="btn-primary flex items-center justify-center gap-2 w-full rounded-2xl py-3.5 px-6 text-sm font-bold text-white shadow-xl transition cursor-pointer hover:scale-[1.01] min-h-[44px]"
                                    >
                                        <span v-if="form.processing">Reserving Slot...</span>
                                        <span v-else>Confirm 30-Min Discovery Session</span>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </div>
                            </form>

                            <!-- Success / Booking Confirmed View -->
                            <div v-else class="text-center py-8 space-y-6 animate-fade-in">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 text-4xl">
                                    ✓
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-2xl font-black text-foreground">Discovery Session Reserved!</h3>
                                    <p class="text-sm text-muted-foreground max-w-md mx-auto">
                                        Thank you, <strong class="text-foreground">{{ form.name || 'there' }}</strong>. Your 1-on-1 consultation slot has been locked for:
                                    </p>
                                    <div class="inline-block mt-3 px-5 py-2.5 rounded-xl bg-secondary border border-border text-sm font-mono font-bold text-foreground">
                                        📅 {{ selectedDate }} · {{ selectedTime }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md mx-auto pt-2">
                                    <a
                                        :href="googleCalendarUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 text-sky-700 dark:text-sky-300 px-4 py-3.5 text-xs font-bold transition min-h-[44px]"
                                    >
                                        <span>📅 Add to Google Calendar</span>
                                        <span>↗</span>
                                    </a>
                                    <a
                                        :href="`https://wa.me/919087021592?text=Hi%20Ashish,%20I've%20just%20reserved%20a%20consultation%20for%20${encodeURIComponent(selectedDate)}%20at%20${encodeURIComponent(selectedTime)}%20for%20my%20project.`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25d366]/15 hover:bg-[#25d366]/25 border border-[#25d366]/40 text-[#128c7e] dark:text-[#25d366] px-4 py-3.5 text-xs font-bold transition min-h-[44px]"
                                    >
                                        <span>💬 WhatsApp Confirmation</span>
                                        <span>↗</span>
                                    </a>
                                </div>

                                <p class="text-xs text-muted-foreground">
                                    We have also dispatched meeting details and calendar invite to <strong class="text-foreground">{{ form.email }}</strong>.
                                </p>
                            </div>
                        </div>

                        <!-- WhatsApp Priority Fast-Track View -->
                        <div v-show="activeTab === 'whatsapp'" class="p-6 sm:p-10 space-y-6">
                            <div class="text-center space-y-3 max-w-md mx-auto">
                                <div class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-[#25d366]/20 border border-[#25d366]/40 text-[#128c7e] dark:text-[#25d366] text-3xl">
                                    💬
                                </div>
                                <h3 class="text-xl sm:text-2xl font-black text-card-foreground">
                                    Chat directly with Ashish on WhatsApp
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Need an immediate scope evaluation, budget check, or urgent architectural advice? Reach out directly.
                                </p>
                            </div>

                            <div class="p-4 rounded-2xl bg-secondary/40 border border-border space-y-2.5 text-xs text-muted-foreground max-w-md mx-auto">
                                <div class="flex items-center gap-2">
                                    <span class="text-[#25d366] font-bold">✓</span>
                                    <span>Average response time: &lt; 2 hours</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[#25d366] font-bold">✓</span>
                                    <span>Audio message & diagram sharing supported</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[#25d366] font-bold">✓</span>
                                    <span>Direct NDA exchange prior to codebase audit</span>
                                </div>
                            </div>

                            <div class="text-center pt-2">
                                <a
                                    href="https://wa.me/919087021592?text=Hi%20Ashish,%20I'd%20like%20to%20discuss%20an%20architecture%20project"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    @click="handleWhatsAppDirect"
                                    class="inline-flex items-center justify-center gap-2.5 rounded-2xl bg-[#25d366] hover:bg-[#20ba59] text-white px-8 py-4 text-sm font-bold shadow-xl shadow-[#25d366]/20 transition-all hover:scale-[1.02] cursor-pointer min-h-[44px]"
                                >
                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    <span>Open WhatsApp Conversation Now</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-20 border-t border-border/80 bg-background/90 py-6 text-center text-xs text-muted-foreground">
            <div class="mx-auto max-w-7xl px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span>© {{ new Date().getFullYear() }} DigitalBuilders · All rights reserved.</span>
                <div class="flex items-center gap-4">
                    <Link href="/privacy-policy" class="hover:text-foreground transition min-h-[44px] inline-flex items-center">Privacy Policy</Link>
                    <Link href="/terms-of-service" class="hover:text-foreground transition min-h-[44px] inline-flex items-center">Terms of Service</Link>
                    <Link href="/pricing" class="hover:text-foreground transition min-h-[44px] inline-flex items-center">2026 Price Book</Link>
                </div>
            </div>
        </footer>

        <CookieConsent />
    </div>
</template>
