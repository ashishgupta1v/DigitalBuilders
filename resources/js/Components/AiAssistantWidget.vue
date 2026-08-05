<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

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
            { label: 'What services do you offer?', action: 'services' },
            { label: 'How much does a project cost?', action: 'pricing' },
            { label: 'Who is Ashish Gupta?', action: 'founder' },
            { label: 'Book a Discovery Call', action: 'lead_form' },
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
        const res = await fetch('/api/ai-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({ message: text, history }),
        });

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
            text: "Thanks for your inquiry! Would you like to estimate your project cost or book a discovery call?",
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
                text: 'We engineer 5 core digital systems:\n1. Custom Web Applications (Laravel, Vue, React, Next.js)\n2. Mobile Apps (iOS & Android native feeling)\n3. Autonomous AI Voice Agents & Chatbots\n4. Custom Enterprise ERP & CRM systems\n5. SaaS Platforms built to scale.',
                options: [
                    { label: 'View Cost Estimator', action: 'pricing' },
                    { label: 'Request a Project Quote', action: 'lead_form' },
                ],
            });
        } else if (action === 'pricing') {
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'Our custom software projects typically start around ₹1,25,000 ($1,500 USD) for MVPs, and scaled enterprise architecture ranges from ₹3,500,000+. You can use our interactive estimator on this page for an exact breakdown!',
                options: [
                    { label: 'Calculate My Scope Now', action: 'estimator_scroll' },
                    { label: 'Connect with Lead Architect', action: 'lead_form' },
                ],
            });
        } else if (action === 'founder') {
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'Ashish Gupta is our Lead Digital Architect & Founder with over 8+ years in enterprise IT. He brings Silicon Valley engineering discipline and AI automation expertise to ambitious businesses.',
                options: [
                    { label: 'View Ashish\'s Portfolio', action: 'portfolio_link' },
                    { label: 'Schedule Strategy Session', action: 'lead_form' },
                ],
            });
        } else if (action === 'estimator_scroll') {
            const el = document.getElementById('estimator');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
                isOpen.value = false;
            }
        } else if (action === 'portfolio_link') {
            window.open('https://www.ashgpt.dev/', '_blank');
        } else if (action === 'lead_form') {
            showLeadCapture.value = true;
            messages.value.push({
                id: Date.now() + 1,
                sender: 'bot',
                text: 'Great! Please fill in your contact details below, and Ashish will personally review your request within 24 business hours.',
            });
        }
        scrollToBottom();
    }, 400);
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
    <div class="fixed bottom-24 right-6 z-[8000]">
        <button
            @click="toggleWidget"
            class="relative flex h-14 w-14 cursor-pointer items-center justify-center rounded-full border border-[#9ba7ff66] bg-[linear-gradient(135deg,#24354a,#1a2636)] shadow-[0_8px_25px_rgba(155,167,255,0.35)] transition-all hover:scale-110"
            aria-label="Open AI Assistant"
        >
            <span v-if="hasUnread" class="absolute -right-1 -top-1 flex h-4 w-4">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#c593ff] opacity-75"></span>
                <span class="relative inline-flex h-4 w-4 rounded-full bg-[#c593ff]"></span>
            </span>
            <svg class="h-6 w-6 text-[#9ba7ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            class="db-ai-widget-panel fixed bottom-24 right-6 z-[8500] flex h-[540px] w-[360px] flex-col rounded-3xl border border-[#b8c9e640] bg-[#1a2534] shadow-[0_20px_50px_rgba(0,0,0,0.5)] backdrop-blur-xl sm:w-[400px]"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-[#b8c9e622] bg-[#223145] px-5 py-4 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[linear-gradient(95deg,#7ac4ff,#c593ff)] text-xs font-black text-[#1a2231]">
                        AI
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">DigitalBuilders AI</h4>
                        <p class="text-[11px] text-emerald-400">● Powered by OpenAI</p>
                    </div>
                </div>
                <button @click="isOpen = false" class="cursor-pointer text-slate-400 hover:text-white">✕</button>
            </div>

            <!-- Messages Area -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 text-xs">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex flex-col"
                    :class="msg.sender === 'user' ? 'items-end' : 'items-start'"
                >
                    <div
                        class="max-w-[85%] rounded-2xl px-4 py-2.5 leading-relaxed whitespace-pre-line"
                        :class="msg.sender === 'user'
                            ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] text-[#1a2231] font-medium'
                            : 'border border-[#b8c9e626] bg-[#243449] text-slate-200'"
                    >
                        {{ msg.text }}
                    </div>

                    <!-- Option Chips -->
                    <div v-if="msg.options && msg.options.length" class="mt-2.5 flex flex-wrap gap-1.5">
                        <button
                            v-for="opt in msg.options"
                            :key="opt.action"
                            @click="handleOptionClick(opt.action, opt.label)"
                            class="cursor-pointer rounded-full border border-[#9ba7ff44] bg-[#27374d80] px-3 py-1.5 text-[11px] font-semibold text-[#b7d3ff] transition hover:border-[#9ba7ff] hover:bg-[#2d3f57] hover:text-white"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- Thinking indicator -->
                <div v-if="isThinking" class="flex items-center gap-1.5 text-slate-400 text-xs py-1">
                    <span class="h-2 w-2 rounded-full bg-[#9ba7ff] animate-ping"></span>
                    <span>DigitalBuilders AI is thinking...</span>
                </div>

                <!-- Embedded Lead Capture Form inside Chat -->
                <div v-if="showLeadCapture" class="rounded-2xl border border-[#c593ff44] bg-[#27263c] p-4 text-xs space-y-2.5">
                    <p class="font-bold text-white">Quick Discovery Intake</p>
                    <input v-model="leadForm.name" type="text" placeholder="Your Name *" class="w-full rounded-xl border border-[#b8c9e633] bg-[#1a2534] px-3 py-2 text-white placeholder:text-slate-500 focus:outline-none" />
                    <input v-model="leadForm.email" type="email" placeholder="Corporate Email *" class="w-full rounded-xl border border-[#b8c9e633] bg-[#1a2534] px-3 py-2 text-white placeholder:text-slate-500 focus:outline-none" />
                    <input v-model="leadForm.phone" type="text" placeholder="Phone Number *" class="w-full rounded-xl border border-[#b8c9e633] bg-[#1a2534] px-3 py-2 text-white placeholder:text-slate-500 focus:outline-none" />
                    <select v-model="leadForm.project_type" class="w-full rounded-xl border border-[#b8c9e633] bg-[#1a2534] px-3 py-2 text-white focus:outline-none">
                        <option value="web_app">Web Application</option>
                        <option value="mobile_app">Mobile App</option>
                        <option value="ai_solutions">AI Solution</option>
                        <option value="erp_crm">ERP / CRM</option>
                        <option value="saas">SaaS Platform</option>
                    </select>
                    <button
                        @click="submitLeadFromChat"
                        :disabled="leadForm.processing"
                        class="w-full cursor-pointer rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] py-2 font-bold text-[#1a2231] hover:brightness-110 disabled:opacity-50"
                    >
                        {{ leadForm.processing ? 'Submitting...' : 'Submit Inquiry' }}
                    </button>
                </div>
            </div>

            <!-- Freeform Input Bar -->
            <div class="border-t border-[#b8c9e622] bg-[#17212d] p-2.5 rounded-b-3xl">
                <form @submit.prevent="sendCustomMessage" class="flex items-center gap-2">
                    <input
                        v-model="customInput"
                        type="text"
                        placeholder="Ask AI anything about your project..."
                        class="flex-1 rounded-xl border border-[#b8c9e633] bg-[#1a2534] px-3.5 py-2 text-xs text-white placeholder:text-slate-400 focus:border-[#9ba7ff] focus:outline-none"
                    />
                    <button
                        type="submit"
                        :disabled="!customInput.trim() || isThinking"
                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] text-[#1a2231] transition hover:brightness-110 disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </Transition>
</template>
