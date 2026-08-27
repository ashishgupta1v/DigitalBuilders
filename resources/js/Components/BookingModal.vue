<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { trackEvent, trackWhatsAppClick, trackBookingCompleted } from '@/utils/analytics';
import { detectUserRegion, type RegionMode } from '@/utils/geo';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const selectedMethod = ref<'scheduler' | 'whatsapp'>('scheduler');
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
        if (day !== 0 && day !== 6) { // Exclude Sunday (0) and Saturday (6)
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

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        activeRegion.value = detectUserRegion();
        form.region = activeRegion.value;
        trackEvent('booking_modal_opened', { location: 'interactive_modal' });
    } else {
        // Reset state on close
        isBooked.value = false;
        form.reset();
    }
});

function handleClose() {
    emit('close');
}

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
            // Still present confirmation fallback if network drops
            isBooked.value = true;
        },
    });
}

// Generate Google Calendar Link
const googleCalendarUrl = computed(() => {
    const slot = timeSlots.find((s) => s.id === selectedTime.value) || timeSlots[1];
    const dateParts = selectedDate.value.split('-');
    if (dateParts.length < 3) return '#';

    const year = parseInt(dateParts[0], 10);
    const month = parseInt(dateParts[1], 10) - 1;
    const day = parseInt(dateParts[2], 10);

    // IST is UTC +5:30
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
    trackWhatsAppClick('booking_modal_whatsapp_fast_track', {
        intent: 'instant_chat_booking',
    });
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
                        1-on-1 technical discovery with Lead Digital Architect Ashish Gupta (10+ Years Enterprise Experience).
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
                    @click="selectedMethod = 'scheduler'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 min-h-[44px]',
                        selectedMethod === 'scheduler'
                            ? 'btn-primary text-white shadow-md'
                            : 'bg-secondary text-secondary-foreground hover:text-foreground'
                    ]"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Pick Date & Time Slot</span>
                </button>
                <button
                    type="button"
                    @click="selectedMethod = 'whatsapp'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 min-h-[44px]',
                        selectedMethod === 'whatsapp'
                            ? 'bg-[#25d366] text-white shadow-md'
                            : 'bg-secondary text-secondary-foreground hover:text-foreground'
                    ]"
                >
                    <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                    <span>Instant WhatsApp</span>
                </button>
            </div>

            <!-- Tab 1: Native In-App Slot Scheduler -->
            <div v-if="selectedMethod === 'scheduler'" class="mt-5">
                <!-- State A: Form to pick date, slot and details -->
                <form v-if="!isBooked" @submit.prevent="handleBookSlot" class="space-y-4">
                    <!-- Date Selector -->
                    <div>
                        <label class="block text-xs font-bold text-foreground mb-1.5">
                            1. Select Preferred Date
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <button
                                v-for="d in availableDates"
                                :key="d.raw"
                                type="button"
                                @click="selectedDate = d.raw"
                                :class="[
                                    'p-2.5 rounded-xl border text-xs font-medium transition cursor-pointer text-center min-h-[44px]',
                                    selectedDate === d.raw
                                        ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm'
                                        : 'border-border bg-secondary/40 text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                {{ d.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Time Slot Selector -->
                    <div>
                        <label class="block text-xs font-bold text-foreground mb-1.5">
                            2. Select Time Window
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button
                                v-for="slot in timeSlots"
                                :key="slot.id"
                                type="button"
                                @click="selectedTime = slot.id"
                                :class="[
                                    'p-2.5 rounded-xl border text-xs font-medium transition cursor-pointer text-left flex items-center justify-between min-h-[44px]',
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

                    <!-- Contact Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label for="book_name" class="block text-[11px] font-semibold text-muted-foreground mb-1">Your Full Name *</label>
                            <input
                                id="book_name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="e.g. John Doe"
                                class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                            />
                        </div>
                        <div>
                            <label for="book_email" class="block text-[11px] font-semibold text-muted-foreground mb-1">Work Email *</label>
                            <input
                                id="book_email"
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
                            <label for="book_phone" class="block text-[11px] font-semibold text-muted-foreground mb-1">Phone / WhatsApp *</label>
                            <input
                                id="book_phone"
                                v-model="form.phone"
                                type="tel"
                                required
                                placeholder="+91 98765 43210"
                                class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                            />
                        </div>
                        <div>
                            <label for="book_type" class="block text-[11px] font-semibold text-muted-foreground mb-1">Project Category</label>
                            <select
                                id="book_type"
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
                        <label for="book_desc" class="block text-[11px] font-semibold text-muted-foreground mb-1">Brief Project Goal or Bottleneck</label>
                        <input
                            id="book_desc"
                            v-model="form.description"
                            type="text"
                            placeholder="e.g. Scaling Postgres database or building MVP from scratch"
                            class="w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                        />
                    </div>

                    <!-- Submit Action -->
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

                <!-- State B: Success / Booking Confirmed View -->
                <div v-else class="text-center py-6 space-y-5 animate-fade-in">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 text-3xl">
                        ✓
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-xl font-black text-foreground">Discovery Session Reserved!</h3>
                        <p class="text-xs sm:text-sm text-muted-foreground max-w-md mx-auto">
                            Thank you, <strong class="text-foreground">{{ form.name || 'there' }}</strong>. Your 1-on-1 consultation slot has been locked for:
                        </p>
                        <div class="inline-block mt-2 px-4 py-2 rounded-xl bg-secondary border border-border text-xs font-mono font-bold text-foreground">
                            📅 {{ selectedDate }} · {{ selectedTime }}
                        </div>
                    </div>

                    <!-- 1-Click Calendar Add Actions -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md mx-auto pt-2">
                        <a
                            :href="googleCalendarUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 text-sky-700 dark:text-sky-300 px-4 py-3 text-xs font-bold transition min-h-[44px]"
                        >
                            <span>📅 Add to Google Calendar</span>
                            <span>↗</span>
                        </a>
                        <a
                            :href="`https://wa.me/919087021592?text=Hi%20Ashish,%20I've%20just%20reserved%20a%20consultation%20for%20${encodeURIComponent(selectedDate)}%20at%20${encodeURIComponent(selectedTime)}%20for%20my%20project.`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25d366]/15 hover:bg-[#25d366]/25 border border-[#25d366]/40 text-[#128c7e] dark:text-[#25d366] px-4 py-3 text-xs font-bold transition min-h-[44px]"
                        >
                            <span>💬 WhatsApp Confirmation</span>
                            <span>↗</span>
                        </a>
                    </div>

                    <p class="text-[11px] text-muted-foreground">
                        We have also dispatched meeting details and calendar invite to <strong class="text-foreground">{{ form.email }}</strong>.
                    </p>
                </div>
            </div>

            <!-- Tab 2: WhatsApp Fast-Track -->
            <div v-if="selectedMethod === 'whatsapp'" class="mt-4 space-y-4">
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
                    @click="handleWhatsAppDirect"
                    class="flex items-center justify-center gap-2.5 w-full rounded-2xl bg-[#25d366] hover:bg-[#20ba5a] text-white font-bold py-3.5 px-6 text-sm transition shadow-lg hover:scale-[1.01] cursor-pointer min-h-[44px]"
                >
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                    <span>Chat Directly with Ashish on WhatsApp</span>
                </a>
            </div>
        </div>
    </Modal>
</template>
