<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

interface Message {
    id: number;
    sender: 'bot' | 'user';
    text: string;
    options?: Array<{ label: string; action: string }>;
}

const isOpen = ref(false);
const hasUnread = ref(true);

const messages = ref<Message[]>([
    {
        id: 1,
        sender: 'bot',
        text: 'Hello! I am the DigitalBuilders AI Assistant. How can I assist with your software project today?',
        options: [
            { label: 'Explore Live Case Studies (9 Apps)', action: 'case_studies' },
            { label: 'What services do you offer?', action: 'services' },
            { label: '📄 Download 2026 Price Book (PDF)', action: 'brochure_download' },
            { label: 'Calculate Project Scope', action: 'estimator_scroll' },
            { label: 'Chat on WhatsApp with Lead Architect', action: 'whatsapp_chat' },
        ],
    },
]);

const chatContainer = ref<HTMLElement | null>(null);
const showLeadCapture = ref(false);

const leadForm = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: 'web_app',
    description: '',
    _hp_company: '',
});

function toggleWidget() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        hasUnread.value = false;
        scrollToBottom();
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Escape' && isOpen.value) {
        isOpen.value = false;
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const customInput = ref('');
const isThinking = ref(false);

async function sendCustomMessage() {
    const text = customInput.value.trim();
    if (!text || isThinking.value) return;

    customInput.value = '';
    messages.value.push({ id: Date.now(), sender: 'user', text });
    isThinking.value = true;
    scrollToBottom();

    try {
        const history = messages.value.slice(-6).map((m) => ({ sender: m.sender, text: m.text }));
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

        let res = await fetch('/ajax/ai-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text, history }),
        });

        if (!res.ok) {
            // Fallback to /api/ai-chat
            res = await fetch('/api/ai-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text, history }),
            });
        }

        if (res.ok) {
            const data = await res.json();
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: data.response || "How can I assist you with your project today?",
                options: [
                    { label: 'View Cost Estimator', action: 'pricing' },
                    { label: 'Book a Discovery Call', action: 'lead_form' },
                ],
            });
        } else {
            throw new Error('API Error');
        }
    } catch {
        messages.value.push({
            id: Date.now() + 1,
            sender: 'bot',
            text: "Thanks for your inquiry! We engineer custom Web Apps, Mobile Apps, AI Agents, ERP/CRM, and SaaS platforms with sub-100ms response times. Would you like to estimate your project cost or speak with our Lead Architect?",
            options: [
                { label: 'View Cost Estimator', action: 'pricing' },
                { label: 'Book a Discovery Call', action: 'lead_form' },
            ],
        });
    } finally {
        isThinking.value = false;
        scrollToBottom();
    }
}

function handleOptionClick(action: string, label: string) {
    // Add user message
    messages.value.push({ id: Date.now(), sender: 'user', text: label });

    setTimeout(() => {
        if (action === 'services') {
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'We engineer 5 core digital systems:\n1. Custom Web Applications (Laravel, Vue 3, Inertia, React, Next.js)\n2. Mobile Apps (iOS & Android native feeling in Flutter / React Native)\n3. Autonomous AI Voice Agents & RAG Pipelines\n4. Custom Enterprise ERP & CRM systems\n5. Scalable SaaS Platforms.',
                options: [
                    { label: 'View Cost Estimator', action: 'pricing' },
                    { label: 'Request a Project Quote', action: 'lead_form' },
                ],
            });
        } else if (action === 'pricing') {
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'We maintain decoupled regional price books with transparent milestone payments: 🇮🇳 India (Launch tiers starting from ₹99,000) and 🌍 International (starting from $3,500 US/Global & $2,500 Gulf). View our complete price book or customize your scope!',
                options: [
                    { label: 'View 2026 Price Book (/pricing)', action: 'pricing_page' },
                    { label: 'Calculate Scope in Estimator', action: 'estimator_scroll' },
                    { label: 'Book Discovery Call', action: 'lead_form' },
                ],
            });
        } else if (action === 'pricing_page') {
            window.location.href = '/pricing';
        } else if (action === 'founder') {
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'Ashish Gupta is our Lead Digital Architect & Founder with over 10+ years in enterprise IT. He brings Silicon Valley engineering discipline and AI automation expertise to ambitious businesses.',
                options: [
                    { label: 'View Ashish\'s Portfolio', action: 'portfolio_link' },
                    { label: 'Chat on WhatsApp with Ashish', action: 'whatsapp_chat' },
                    { label: 'Schedule Strategy Session', action: 'lead_form' },
                ],
            });
        } else if (action === 'case_studies') {
            const el = document.getElementById('portfolio');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
                isOpen.value = false;
            } else {
                window.location.href = '/#portfolio';
            }
        } else if (action === 'whatsapp_chat') {
            window.open('https://wa.me/919087021592?text=' + encodeURIComponent('Hi Ashish, I was chatting with the DigitalBuilders AI and would like to discuss my project directly.'), '_blank');
        } else if (action === 'estimator_scroll') {
            const el = document.getElementById('estimator');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
                isOpen.value = false;
            } else {
                window.location.href = '/estimator';
            }
        } else if (action === 'brochure_download') {
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'You can view, print, or download our official 15-page 2026 Architectural Price Book & Service Catalogue directly below:\n\n• 🇮🇳 India Edition (INR ₹)\n• 🌍 International Edition (USD $)',
                options: [
                    { label: '🇮🇳 India Price Book (INR)', action: 'open_brochure_inr' },
                    { label: '🌍 Global Price Book (USD)', action: 'open_brochure_usd' },
                ],
            });
        } else if (action === 'open_brochure_inr') {
            window.open('/downloads/digitalbuilders-pricing-india-inr.html', '_blank');
        } else if (action === 'open_brochure_usd') {
            window.open('/downloads/digitalbuilders-pricing-international-usd.html', '_blank');
        } else if (action === 'portfolio_link') {
            window.open('https://ashishgupta.dev', '_blank');
        } else if (action === 'lead_form') {
            showLeadCapture.value = true;
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'Great! Please fill in your contact details below, and Ashish will personally review your request within 24 business hours.',
            });
        }
        scrollToBottom();
    }, 300);
}

function submitLeadFromChat() {
    leadForm.description = `[Captured via AI Chat Assistant] ${leadForm.description}`;
    leadForm.post(route('library.leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showLeadCapture.value = false;
            leadForm.reset();
            messages.value.push({
                id: Date.now(),
                sender: 'bot',
                text: 'Thank you! Your inquiry has been logged. We will reach out via email/phone within 24 business hours.',
            });
            scrollToBottom();
        },
    });
}
</script>

<template>
    <!-- Floating Trigger Badge -->
    <div class="fixed bottom-20 md:bottom-6 right-4 sm:right-6 z-[8000]">
        <button
            @click="toggleWidget"
            class="relative flex h-14 w-14 cursor-pointer items-center justify-center rounded-full border border-sky-500/40 bg-card text-foreground shadow-2xl transition-all hover:scale-110"
            :aria-expanded="isOpen"
            aria-label="Open AI Assistant"
        >
            <span v-if="hasUnread" class="absolute -right-1 -top-1 flex h-4 w-4">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-purple-500 opacity-75"></span>
                <span class="relative inline-flex h-4 w-4 rounded-full bg-purple-500"></span>
            </span>
            <svg class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4z"/>
            </svg>
        </button>
    </div>

    <!-- Chat Modal Window -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-6 scale-95"
        leave-active-class="transition-all duration-200 ease-in"
        leave-to-class="opacity-0 translate-y-6 scale-95"
    >
        <div
            v-if="isOpen"
            role="dialog"
            aria-label="DigitalBuilders AI Assistant"
            class="db-ai-widget-panel fixed bottom-20 md:bottom-6 right-4 sm:right-6 z-[8500] flex h-[540px] max-h-[calc(100vh-6rem)] w-[calc(100vw-2rem)] max-w-[380px] sm:w-[400px] flex-col rounded-3xl border border-border bg-card text-card-foreground shadow-2xl backdrop-blur-xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-border bg-secondary/80 px-5 py-4 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full btn-primary text-xs font-black text-white">
                        AI
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-card-foreground">DigitalBuilders AI</h4>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">● Active Assistant</p>
                    </div>
                </div>
                <button @click="isOpen = false" aria-label="Close AI Assistant" class="cursor-pointer text-muted-foreground hover:text-foreground">✕</button>
            </div>

            <!-- Messages Area -->
            <div ref="chatContainer" aria-live="polite" class="flex-1 overflow-y-auto p-4 space-y-3 text-xs">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex flex-col"
                    :class="msg.sender === 'user' ? 'items-end' : 'items-start'"
                >
                    <div
                        class="max-w-[85%] rounded-2xl px-4 py-2.5 leading-relaxed whitespace-pre-line"
                        :class="msg.sender === 'user'
                            ? 'btn-primary text-white font-medium'
                            : 'border border-border bg-secondary/70 text-card-foreground'"
                    >
                        {{ msg.text }}
                    </div>

                    <!-- Option Chips -->
                    <div v-if="msg.options && msg.options.length" class="mt-2.5 flex flex-wrap gap-1.5">
                        <button
                            v-for="opt in msg.options"
                            :key="opt.action"
                            @click="handleOptionClick(opt.action, opt.label)"
                            class="cursor-pointer rounded-full border border-border bg-secondary px-3 py-1.5 text-[11px] font-semibold text-secondary-foreground transition hover:bg-secondary/80 hover:text-foreground"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- Thinking indicator -->
                <div v-if="isThinking" class="flex items-center gap-1.5 text-muted-foreground text-xs py-1">
                    <span class="h-2 w-2 rounded-full bg-sky-500 animate-ping"></span>
                    <span>DigitalBuilders AI is thinking...</span>
                </div>

                <!-- Embedded Lead Capture Form inside Chat -->
                <div v-if="showLeadCapture" class="rounded-2xl border border-purple-500/30 bg-purple-500/10 p-4 text-xs space-y-2.5">
                    <p class="font-bold text-card-foreground">Quick Discovery Intake</p>
                    <input v-model="leadForm.name" type="text" placeholder="Your Name *" aria-label="Your Name" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none" />
                    <input v-model="leadForm.email" type="email" placeholder="Corporate Email *" aria-label="Corporate Email" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none" />
                    <input v-model="leadForm.phone" type="text" placeholder="Phone Number *" aria-label="Phone Number" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none" />
                    <select v-model="leadForm.project_type" aria-label="Project Type" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-foreground focus:ring-2 focus:ring-primary focus:outline-none">
                        <option value="web_app">Web Application</option>
                        <option value="mobile_app">Mobile App</option>
                        <option value="ai_solutions">AI Solution</option>
                        <option value="erp_crm">ERP / CRM</option>
                        <option value="saas">SaaS Platform</option>
                    </select>
                    <button
                        @click="submitLeadFromChat"
                        :disabled="leadForm.processing"
                        class="btn-primary w-full cursor-pointer rounded-full py-2 font-bold text-white hover:scale-[1.01] disabled:opacity-50"
                    >
                        {{ leadForm.processing ? 'Submitting...' : 'Submit Inquiry' }}
                    </button>
                </div>
            </div>

            <!-- Freeform Input Bar -->
            <div class="border-t border-border bg-secondary/40 p-2.5 rounded-b-3xl">
                <form @submit.prevent="sendCustomMessage" class="flex items-center gap-2">
                    <input
                        v-model="customInput"
                        type="text"
                        placeholder="Ask AI anything about your project..."
                        aria-label="Chat input message"
                        class="flex-1 rounded-xl border border-border bg-background px-3.5 py-2 text-xs text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                    />
                    <button
                        type="submit"
                        :disabled="!customInput.trim() || isThinking"
                        aria-label="Send message"
                        class="btn-primary flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-white transition hover:scale-105 disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </Transition>
</template>
