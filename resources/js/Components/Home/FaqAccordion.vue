<script setup lang="ts">
import { vAutoAnimate } from '@formkit/auto-animate/vue';

const faqs = [
    {
        q: 'How long does a typical web application project take?',
        a: 'Timelines vary by architectural scope. A standard web application typically takes 3–6 weeks. Enterprise-grade platforms with custom ERP/CRM integrations take 4–8 weeks. We break every project into weekly sprint milestones with live review demos from day one.',
    },
    {
        q: 'Do you work with startups, or only established companies?',
        a: 'We work with both. For startups, we focus on building a scalable MVP that won\'t need to be rebuilt as you grow. For established companies, we focus on performance, security, and integrating with existing systems.',
    },
    {
        q: 'What makes DigitalBuilders different from other agencies?',
        a: 'We operate with a Staff Engineer mindset — we design systems before writing a single line of code. Unlike most agencies, we don\'t use fragile templates or boilerplate. Every system we build is custom-engineered for your specific domain and scale requirements.',
    },
    {
        q: 'Can you integrate AI into my existing application?',
        a: 'Yes. We specialize in practical AI integrations — not just chatbots. This includes AI voice agents, automated lead qualification, intelligent document processing, workflow automation, and custom LLM-powered features fitted into your existing stack.',
    },
    {
        q: 'What happens after the project is launched?',
        a: 'Every project includes a 30-day post-launch warranty where we fix any bugs at no charge. After that, we offer flexible retainer plans for ongoing feature development, monitoring, and performance optimization.',
    },
    {
        q: 'How do you handle pricing and payment milestones?',
        a: 'We use decoupled, fixed regional price books with transparent milestone disbursements (₹21k booking -> 40/30/30 milestones in India; 50/50 via Stripe/Wire internationally). No surprise invoices, no currency exchange markups. India builds start at ₹99,000 + GST; International builds start at $3,500 ($2,500 for Gulf). You can explore our complete 2026 Price Book on /pricing or configure custom modules in our /estimator.',
    },
] as const;
</script>

<template>
    <section id="faq" class="mt-20 sm:mt-24" data-reveal>
        <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">Common Questions</p>
        <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl md:text-4xl">Frequently Asked Questions</h2>
        <div v-auto-animate class="mt-8 space-y-3" data-stagger>
            <details
                v-for="faq in faqs"
                :key="faq.q"
                data-stagger-item
                class="group rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#1a2534] overflow-hidden shadow-sm"
            >
                <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 text-sm font-semibold text-slate-900 dark:text-white hover:text-sky-600 dark:hover:text-[#b7d3ff] transition-colors list-none">
                    {{ faq.q }}
                    <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-[#9dc5ff] transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="border-t border-slate-100 dark:border-[#b8c9e622] px-5 pb-5 pt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    {{ faq.a }}
                </div>
            </details>
        </div>
    </section>
</template>

<style scoped>
details summary::-webkit-details-marker { display: none; }
</style>
