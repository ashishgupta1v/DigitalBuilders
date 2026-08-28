<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { trackContactSubmit, trackWhatsAppClick, trackPhoneClick, trackEmailClick, trackEvent } from '@/utils/analytics';

const page = usePage();
const flashError = computed(() => (page.props as any).flash?.error as string | undefined);

const projectTypes = [
    { value: 'web_app', label: 'Custom Web Application' },
    { value: 'mobile_app', label: 'Mobile App (iOS & Android)' },
    { value: 'erp_crm', label: 'Enterprise ERP / CRM' },
    { value: 'saas', label: 'SaaS Platform' },
    { value: 'ai_solutions', label: 'AI Voice/Chat Agents & Workflows' },
    { value: 'digital_presence', label: 'Digital Presence & Landing Architecture' },
    { value: 'other', label: 'Other Architecture Project' },
] as const;

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: 'web_app',
    description: '',
    _hp_company: '',
});

onMounted(() => {
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const service = params.get('service');
        const tier = params.get('tier');
        const region = params.get('region');

        if (service === 'discovery_sprint') {
            form.project_type = 'web_app';
            form.description = `[Architecture Discovery Sprint inquiry · Region: ${region?.toUpperCase() ?? 'INR'}]`;
        } else if (service === 'amc_care') {
            form.project_type = 'other';
            form.description = `[Enterprise Care SLA / AMC · Tier: ${tier?.toUpperCase() ?? 'Business'} · Region: ${region?.toUpperCase() ?? 'INR'}]`;
        } else if (service === 'seo_retainer') {
            form.project_type = 'other';
            form.description = `[Ongoing SEO & Content Retainer · Region: ${region?.toUpperCase() ?? 'INR'}]`;
        } else if (service === 'digital_presence') {
            form.project_type = 'digital_presence';
            if (tier) {
                form.description = `[Selected Scope: Digital Presence (${tier.toUpperCase()}) · Region: ${region?.toUpperCase() ?? 'INR'}]`;
            }
        } else if (service && projectTypes.some((p) => p.value === service)) {
            form.project_type = service;
            if (tier) {
                form.description = `[Selected Scope: ${tier.toUpperCase()} Tier · Region: ${region?.toUpperCase() ?? 'INR'}]`;
            }
        }

        // Auto-scroll to #contact if hash is present or service parameter was passed
        if (window.location.hash === '#contact' || service) {
            setTimeout(() => {
                const el = document.getElementById('contact');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 120);
        }
    }
});

const submissionError = ref<string | null>(null);

const fallbackWhatsAppUrl = computed(() => {
    const lines = [
        'Hi Ashish, I encountered an issue submitting the contact form on DigitalBuilders:',
        `• Name: ${form.name || 'Not provided'}`,
        `• Email: ${form.email || 'Not provided'}`,
        `• Phone: ${form.phone || 'Not provided'}`,
        `• Project: ${form.project_type || 'web_app'}`,
        form.description ? `• Details: ${form.description}` : '',
    ].filter(Boolean).join('\n');

    return 'https://wa.me/919087021592?text=' + encodeURIComponent(lines);
});

function submitLead() {
    if (form.processing) return;
    submissionError.value = null;

    const submittedType = form.project_type;

    form.post(route('library.leads.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            submissionError.value = null;
            trackContactSubmit(submittedType);
            form.reset();
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: 'Inquiry received! We will respond within 24 business hours.', type: 'success' },
            }));
        },
        onError: (errors) => {
            if (errors && Object.keys(errors).length > 0) {
                const firstKey = Object.keys(errors)[0];
                const msg = errors.message || errors.error || errors[firstKey];
                submissionError.value = typeof msg === 'string' ? msg : 'Please correct the highlighted fields and try again.';
            } else {
                submissionError.value = 'Submission could not be completed. You may have reached the rate limit — please wait 60 seconds or send your inquiry directly via WhatsApp.';
            }
        },
    });
}
</script>

<template>
    <div>
        <!-- Ready to Architect Callout Banner -->
        <section class="mt-20 rounded-3xl border border-sky-500/30 bg-sky-500/10 p-6 text-center sm:mt-24 sm:p-10 shadow-lg" data-reveal>
            <h2 class="text-2xl font-black text-foreground sm:text-3xl md:text-4xl">Ready to Architect Your Solution?</h2>
            <p class="mx-auto mt-3 max-w-3xl text-sm sm:text-base text-muted-foreground">Stop settling. Start building. Engineer the resilient digital systems your business needs.</p>
            <a href="#contact" class="btn-primary mt-7 inline-flex w-full items-center justify-center rounded-full px-7 py-3.5 min-h-[44px] text-sm font-bold text-white shadow-lg transition hover:scale-[1.02] sm:w-auto">
                Schedule Your Strategy Session
            </a>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="mt-20 grid gap-6 sm:mt-24 sm:gap-8 lg:grid-cols-[1.15fr_0.85fr]" data-reveal>
            <div class="db-antigravity-card rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 shadow-xl">
                <h2 class="text-2xl font-black text-card-foreground sm:text-3xl">Let's Connect</h2>
                <p class="mt-3 text-sm text-muted-foreground">Your business requires software that works as hard as you do. Let's map your bottlenecks into a robust digital solution.</p>

                <!-- General / Rate-Limit Error Alert Banner -->
                <div
                    v-if="submissionError || flashError"
                    role="alert"
                    aria-live="assertive"
                    class="mt-6 rounded-2xl border border-red-500/40 bg-red-500/10 p-5 text-sm text-red-900 dark:text-red-200 space-y-3 shadow-inner"
                >
                    <div class="flex items-start gap-2.5">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <p class="font-bold text-base">{{ submissionError || flashError }}</p>
                            <p class="mt-1 text-xs text-red-800 dark:text-red-300 leading-relaxed">
                                Your typed information has been kept intact. If the network or rate limit interrupted submission, you can immediately send your inquiry to Lead Architect Ashish Gupta via WhatsApp:
                            </p>
                        </div>
                    </div>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a
                            :href="fallbackWhatsAppUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2.5 text-xs transition shadow-md"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            Send Inquiry Directly on WhatsApp
                        </a>
                        <a
                            href="mailto:hello@digitalbuilders.in"
                            class="inline-flex items-center gap-1.5 text-xs text-red-900 dark:text-red-200 underline font-semibold hover:text-red-950 dark:hover:text-white"
                        >
                            Or email hello@digitalbuilders.in
                        </a>
                    </div>
                </div>

                <form id="home-contact-form" name="home_contact_form" action="/library/contact" method="post" class="mt-6 space-y-5" @submit.prevent="submitLead">
                    <!-- Honeypot field for bot suppression -->
                    <input v-model="form._hp_company" type="text" name="_hp_company" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true" style="display:none !important;" />

                    <div>
                        <label for="contact-name" class="block text-sm font-medium text-card-foreground">Full Name *</label>
                        <input
                            id="contact-name"
                            name="name"
                            v-model="form.name"
                            @input="form.clearErrors('name'); submissionError = null"
                            required
                            type="text"
                            autocomplete="name"
                            placeholder="First and Last Name"
                            :aria-invalid="!!form.errors.name"
                            :aria-describedby="form.errors.name ? 'contact-name-error' : undefined"
                            class="mt-2 w-full rounded-xl border border-border bg-secondary/50 px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                        <p v-if="form.errors.name" id="contact-name-error" role="alert" aria-live="polite" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="contact-email" class="block text-sm font-medium text-card-foreground">Email Address *</label>
                            <span class="text-[11px] text-muted-foreground">For formal proposal & scope doc</span>
                        </div>
                        <input
                            id="contact-email"
                            name="email"
                            v-model="form.email"
                            @input="form.clearErrors('email'); submissionError = null"
                            required
                            type="email"
                            autocomplete="email"
                            placeholder="you@company.com"
                            :aria-invalid="!!form.errors.email"
                            :aria-describedby="form.errors.email ? 'contact-email-error' : undefined"
                            class="mt-2 w-full rounded-xl border border-border bg-secondary/50 px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                        <p v-if="form.errors.email" id="contact-email-error" role="alert" aria-live="polite" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="contact-phone" class="block text-sm font-medium text-card-foreground">Phone Number / WhatsApp *</label>
                            <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium">For direct WhatsApp updates</span>
                        </div>
                        <input
                            id="contact-phone"
                            name="phone"
                            v-model="form.phone"
                            @input="form.clearErrors('phone'); submissionError = null"
                            required
                            type="tel"
                            autocomplete="tel"
                            placeholder="+91 XXXXX XXXXX"
                            :aria-invalid="!!form.errors.phone"
                            :aria-describedby="form.errors.phone ? 'contact-phone-error' : undefined"
                            class="mt-2 w-full rounded-xl border border-border bg-secondary/50 px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                        <p v-if="form.errors.phone" id="contact-phone-error" role="alert" aria-live="polite" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label for="contact-project-type" class="block text-sm font-medium text-card-foreground">Project Type *</label>
                        <select
                            id="contact-project-type"
                            name="project_type"
                            v-model="form.project_type"
                            @change="form.clearErrors('project_type'); submissionError = null"
                            required
                            class="mt-2 w-full rounded-xl border border-border bg-secondary/50 px-4 py-3 text-sm text-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        >
                            <option v-for="type in projectTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </select>
                        <p v-if="form.errors.project_type" id="contact-type-error" role="alert" aria-live="polite" class="mt-1 text-xs text-red-500">{{ form.errors.project_type }}</p>
                    </div>

                    <div>
                        <label for="contact-description" class="block text-sm font-medium text-card-foreground">Project Description (Optional)</label>
                        <textarea
                            id="contact-description"
                            name="description"
                            v-model="form.description"
                            @input="form.clearErrors('description'); submissionError = null"
                            rows="4"
                            placeholder="Briefly describe your core operational challenge or project vision..."
                            class="mt-2 w-full rounded-xl border border-border bg-secondary/50 px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        ></textarea>
                        <p v-if="form.errors.description" id="contact-desc-error" role="alert" aria-live="polite" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <button
                        :disabled="form.processing"
                        type="submit"
                        class="btn-primary w-full rounded-full px-6 py-3.5 min-h-[44px] text-sm font-bold text-white transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-70 shadow-lg cursor-pointer"
                    >
                        {{ form.processing ? 'Submitting Inquiry...' : 'Request a Project Quote' }}
                    </button>

                    <div class="flex items-center justify-center gap-3 text-[11px] text-muted-foreground text-center">
                        <span>🔒 100% Free Mutual NDA</span>
                        <span>•</span>
                        <span>⚡ 24-Hr Turnaround</span>
                        <span>•</span>
                        <span>🛡️ Zero Spam</span>
                    </div>

                    <div v-if="form.recentlySuccessful" role="alert" aria-live="polite" class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-800 dark:text-emerald-200 space-y-3">
                        <p class="font-semibold flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Thank you! Your project inquiry has been received. We'll review and respond within 24 business hours.
                        </p>
                        <a :href="'https://wa.me/919087021592?text=' + encodeURIComponent('Hi Ashish, I just submitted a project inquiry on DigitalBuilders regarding ' + form.project_type + '. Let\'s connect!')" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-[#25d366] px-4 py-2.5 min-h-[44px] text-xs font-bold text-white shadow hover:bg-[#20ba5a] transition">
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                            Want faster response? Chat directly with Ashish on WhatsApp ↗
                        </a>
                    </div>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="db-antigravity-card rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 shadow-xl">
                    <h3 class="text-xl font-bold text-card-foreground">Get In Touch</h3>
                    <a href="tel:+919087021592" @click="trackPhoneClick()" class="mt-4 flex items-center gap-2.5 min-h-[44px] py-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +91 90870 21592
                    </a>
                    <a href="mailto:hello@digitalbuilders.in" @click="trackEmailClick()" class="mt-1 flex items-center gap-2.5 min-h-[44px] py-1 break-all text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        hello@digitalbuilders.in
                    </a>
                    <p class="mt-1 flex items-center gap-2.5 min-h-[44px] py-1 text-sm text-muted-foreground">
                        <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Ludhiana, Punjab, India
                    </p>
                    <!-- WhatsApp Quick Contact -->
                    <a href="https://wa.me/919087021592?text=Hi%20DigitalBuilders!%20I'm%20interested%20in%20discussing%20a%20project." target="_blank" rel="noopener noreferrer" @click="trackWhatsAppClick('contact_direct')" class="mt-4 flex items-center justify-center gap-2 min-h-[44px] rounded-full bg-[#25d366] px-5 py-3 text-sm font-bold text-white shadow-[0_4px_20px_rgba(37,211,102,0.25)] hover:bg-[#20ba5a] transition-all hover:scale-[1.02]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Chat on WhatsApp
                    </a>
                </div>
                <!-- Calendar Strategy Call Card -->
                <div class="db-antigravity-card rounded-3xl border border-sky-500/40 bg-sky-500/10 p-6 sm:p-8 shadow-xl">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-ping" />
                        <span class="text-xs uppercase font-extrabold tracking-wider text-sky-700 dark:text-sky-400">Instant Architecture Scoping</span>
                    </div>
                    <h3 class="mt-2 text-xl font-bold text-card-foreground">Book a Strategy Session</h3>
                    <p class="mt-2 text-xs sm:text-sm text-muted-foreground leading-relaxed">
                        Prefer an interactive deep-dive? Book a 30-minute system architecture review directly with Lead Architect Ashish Gupta.
                    </p>
                    <div class="mt-4 flex flex-col gap-2.5">
                        <a
                            href="https://cal.com/digitalbuilders/30min"
                            target="_blank"
                            rel="noopener noreferrer"
                            @click="trackEvent('book_meeting_click', { method: 'cal_com', location: 'contact_section' })"
                            class="btn-primary inline-flex w-full items-center justify-center gap-2 min-h-[44px] rounded-full px-5 py-3 text-xs font-bold text-white shadow-md transition hover:scale-[1.02]"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Pick a Time on Cal.com (Instant Slot)</span>
                        </a>
                        <a
                            href="https://wa.me/919087021592?text=Hi%20Ashish,%20I'd%20like%20to%20schedule%20a%2030-minute%20system%20architecture%20strategy%20session."
                            target="_blank"
                            rel="noopener noreferrer"
                            @click="trackWhatsAppClick('strategy_session')"
                            class="inline-flex w-full items-center justify-center gap-2 min-h-[44px] rounded-full border border-border bg-card px-5 py-2.5 text-xs font-bold text-card-foreground hover:bg-secondary transition shadow-sm"
                        >
                            <svg class="h-4 w-4 text-emerald-500 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                            <span>Or Schedule via WhatsApp</span>
                        </a>
                    </div>
                </div>

                <!-- Official 2026 Brochure Download Card -->
                <div class="db-antigravity-card rounded-3xl border border-sky-500/30 bg-card text-card-foreground p-6 sm:p-8 shadow-xl">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📄</span>
                        <span class="text-xs uppercase font-extrabold tracking-wider text-sky-700 dark:text-sky-400">Official Specification</span>
                    </div>
                    <h3 class="mt-2 text-xl font-bold text-card-foreground">2026 Price Book Brochure</h3>
                    <p class="mt-2 text-xs sm:text-sm text-muted-foreground leading-relaxed">
                        Need an offline copy for your board or technical team? Download or print our full 15-page architectural specification brochure.
                    </p>
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <a
                            href="/downloads/digitalbuilders-pricing-india-inr.pdf"
                            target="_blank"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full border border-sky-500/40 bg-sky-500/10 px-4 py-2.5 text-xs font-bold text-sky-700 dark:text-sky-300 hover:bg-sky-500/20 transition-all shadow-sm"
                        >
                            <span>🇮🇳 India Price Card (PDF)</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <a
                            href="/downloads/digitalbuilders-pricing-international-usd.pdf"
                            target="_blank"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full border border-border bg-secondary px-4 py-2.5 text-xs font-bold text-secondary-foreground hover:bg-secondary/80 transition-all shadow-sm"
                        >
                            <span>🌍 Global Price Card (PDF)</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

                <div class="db-antigravity-card rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 shadow-xl">
                    <h3 class="text-xl font-bold text-card-foreground">Quick Access</h3>
                    <p class="mt-3 text-sm text-muted-foreground">Explore more projects and technical work by our founder Ashish Gupta.</p>
                    <a href="https://ashishgupta.dev" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-1.5 min-h-[44px] py-1 text-sm font-bold text-sky-700 dark:text-sky-400 hover:underline transition-colors">
                        View Founder Portfolio (ashishgupta.dev)
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </aside>
        </section>
    </div>
</template>
