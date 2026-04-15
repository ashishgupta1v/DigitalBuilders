<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { animate, inView, stagger } from 'motion';
import { onMounted } from 'vue';

type MotionAnimate = (
    target: Element | NodeListOf<Element>,
    keyframes: Record<string, unknown>,
    options?: Record<string, unknown>,
) => void;

const motionAnimate = animate as unknown as MotionAnimate;

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: '',
    description: '',
});

const projectTypes = [
    { value: 'web_app', label: 'Web Application' },
    { value: 'mobile_app', label: 'Mobile App' },
    { value: 'erp_crm', label: 'ERP / CRM' },
    { value: 'saas', label: 'SaaS Platform' },
    { value: 'ai_solutions', label: 'AI Solutions' },
    { value: 'other', label: 'Other' },
];

const services = [
    {
        title: 'Custom Web Application Development',
        summary: 'Blazing-fast, scalable web applications engineered with modern architecture.',
    },
    {
        title: 'Mobile App Development (iOS and Android)',
        summary: 'Native-feeling, robust mobile apps connecting brand and customers.',
    },
    {
        title: 'AI Voice Agents and Chatbots',
        summary: 'Autonomous conversational agents that capture leads and reduce manual support load.',
    },
    {
        title: 'AI Development and Workflows',
        summary: 'Custom enterprise automation and retrieval-enhanced AI systems.',
    },
    {
        title: 'Enterprise-Grade Systems (ERP and CRM)',
        summary: 'Centralized software to streamline operations and unify data.',
    },
];

const studies = [
    {
        client: 'Habuilt',
        result: 'High-traffic platform stabilized and accelerated through architecture-first engineering.',
    },
    {
        client: 'ZoetiCouch',
        result: 'Fragmented operations unified into a custom CRM with AI workflow automation.',
    },
    {
        client: 'SSKnitwear',
        result: 'Legacy brand modernized with a high-performance enterprise storefront.',
    },
];

function submitLead() {
    form.post(route('library.leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('description');
        },
    });
}

onMounted(() => {
    const hero = document.querySelector('[data-hero-title]');

    if (hero) {
        motionAnimate(
            hero,
            { opacity: [0, 1], transform: ['translateY(24px)', 'translateY(0px)'] },
            { duration: 0.7, ease: 'ease-out' },
        );
    }

    inView('[data-reveal]', (element) => {
        motionAnimate(
            element,
            { opacity: [0, 1], transform: ['translateY(28px)', 'translateY(0px)'] },
            { duration: 0.65, ease: 'ease-out' },
        );
    });

    inView('[data-stagger]', (element) => {
        const items = element.querySelectorAll('[data-stagger-item]');

        motionAnimate(
            items,
            { opacity: [0, 1], transform: ['translateY(18px)', 'translateY(0px)'] },
            { duration: 0.55, delay: stagger(0.08), ease: 'ease-out' },
        );
    });
});
</script>

<template>
    <Head title="DigitalBuilders" />

    <div class="site-bg text-slate-100">
        <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/85 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
                <a href="#top" class="text-lg font-semibold tracking-wide text-orange-300">DigitalBuilders</a>

                <nav class="hidden items-center gap-6 text-sm font-medium lg:flex">
                    <a href="#services" class="text-slate-300 transition hover:text-white">Services</a>
                    <a href="#portfolio" class="text-slate-300 transition hover:text-white">Portfolio</a>
                    <a href="#about" class="text-slate-300 transition hover:text-white">About</a>
                    <a href="#contact" class="text-slate-300 transition hover:text-white">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    <Link v-if="canLogin" :href="route('login')" class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:border-white/50 hover:text-white">
                        Log in
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="rounded-full bg-orange-500 px-4 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">
                        Register
                    </Link>
                </div>
            </div>
        </header>

        <main id="top" class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
            <section class="pt-16 lg:pt-20">
                <p class="mb-5 inline-flex rounded-full border border-orange-300/35 bg-orange-300/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-orange-200">
                    Enterprise Architecture · Ludhiana, Punjab
                </p>
                <h1 data-hero-title class="max-w-4xl text-4xl font-black leading-tight text-white md:text-6xl">
                    We Build Your Digital Future.
                </h1>
                <p class="mt-6 max-w-3xl text-base leading-relaxed text-slate-300 md:text-lg">
                    Stop settling for standard web design. Get enterprise-grade web, mobile, and AI architecture engineered to scale your business.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="#contact" class="rounded-full bg-orange-500 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-orange-400">Book a Discovery Call</a>
                    <a href="#portfolio" class="rounded-full border border-white/25 px-6 py-3 text-sm font-bold text-white transition hover:border-white/50">View Our Portfolio</a>
                </div>
                <p class="mt-6 text-sm text-slate-400">Bringing Silicon Valley engineering discipline and AI automation right here to Ludhiana.</p>
            </section>

            <section class="mt-16 grid gap-4 md:grid-cols-3" data-stagger data-reveal>
                <article data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <h3 class="text-lg font-bold text-orange-200">Clean Information Hierarchy</h3>
                    <p class="mt-3 text-sm text-slate-300">Sections move users from discovery to trust to conversion without friction.</p>
                </article>
                <article data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <h3 class="text-lg font-bold text-orange-200">Unified Motion Choreography</h3>
                    <p class="mt-3 text-sm text-slate-300">One animation language across all sections keeps the journey coherent.</p>
                </article>
                <article data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <h3 class="text-lg font-bold text-orange-200">Conversion-Ready Structure</h3>
                    <p class="mt-3 text-sm text-slate-300">Value, proof, authority, and CTA blocks are sequenced intentionally.</p>
                </article>
            </section>

            <section class="mt-20" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-orange-300">Why DigitalBuilders</p>
                <h2 class="mt-3 text-3xl font-black text-white md:text-4xl">Architecture First. Business Impact Always.</h2>
                <p class="mt-5 max-w-4xl text-slate-300">
                    Most agencies build fragile templates. We bring a Staff Engineer mindset to your business, engineering robust systems built to withstand high traffic, complex data pipelines, and rapid local market scaling.
                </p>
                <ul class="mt-6 grid gap-3 text-sm text-slate-200 sm:grid-cols-3">
                    <li class="rounded-xl border border-white/10 bg-slate-900/65 px-4 py-3">Custom Modular Monoliths</li>
                    <li class="rounded-xl border border-white/10 bg-slate-900/65 px-4 py-3">Zero Legacy Tech Debt</li>
                    <li class="rounded-xl border border-white/10 bg-slate-900/65 px-4 py-3">Autonomous AI Agent Integration</li>
                </ul>
            </section>

            <section class="mt-20 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger data-reveal>
                <div data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-3xl font-black text-white">8+</p>
                    <p class="mt-2 text-sm text-slate-300">Years Experience</p>
                </div>
                <div data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-3xl font-black text-white">25+</p>
                    <p class="mt-2 text-sm text-slate-300">Projects Delivered</p>
                </div>
                <div data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-3xl font-black text-white">10+</p>
                    <p class="mt-2 text-sm text-slate-300">Active Clients</p>
                </div>
                <div data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-3xl font-black text-white">100%</p>
                    <p class="mt-2 text-sm text-slate-300">Engineering Mastery</p>
                </div>
            </section>

            <section id="services" class="mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-orange-300">Our Services</p>
                <h2 class="mt-3 text-3xl font-black text-white md:text-4xl">Elite Digital Ecosystems Engineered For Scale</h2>
                <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-stagger>
                    <article v-for="service in services" :key="service.title" data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                        <h3 class="text-lg font-bold text-white">{{ service.title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-300">{{ service.summary }}</p>
                    </article>
                </div>
            </section>

            <section id="portfolio" class="mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-orange-300">Case Studies</p>
                <h2 class="mt-3 text-3xl font-black text-white md:text-4xl">Proof Through Architecture Outcomes</h2>
                <div class="mt-8 space-y-4" data-stagger>
                    <article v-for="study in studies" :key="study.client" data-stagger-item class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-orange-200">{{ study.client }}</p>
                        <p class="mt-3 text-base text-slate-200">{{ study.result }}</p>
                    </article>
                </div>
            </section>

            <section id="about" class="mt-24 grid gap-8 lg:grid-cols-[1.2fr_1fr]" data-reveal>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-7">
                    <p class="text-sm uppercase tracking-[0.2em] text-orange-300">About Us</p>
                    <h2 class="mt-3 text-3xl font-black text-white">Ashish Gupta</h2>
                    <p class="mt-1 text-sm text-orange-100">Lead Digital Architect · Founder</p>
                    <p class="mt-5 text-slate-300">
                        Over 8 years in enterprise IT designing and deploying complex large-scale software systems. DigitalBuilders was founded to deliver production-grade architecture, not fragile templates.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-7">
                    <p class="text-sm uppercase tracking-[0.2em] text-orange-300">How We Work</p>
                    <ol class="mt-4 space-y-4 text-sm text-slate-200">
                        <li><span class="font-bold text-white">01 Discovery and Audit</span> — Identify highest ROI opportunities.</li>
                        <li><span class="font-bold text-white">02 System Architecture</span> — Blueprint systems before coding.</li>
                        <li><span class="font-bold text-white">03 Disciplined Delivery</span> — Execute with tests and transparent milestones.</li>
                    </ol>
                </div>
            </section>

            <section class="mt-24 rounded-2xl border border-orange-300/35 bg-orange-400/10 p-8 text-center" data-reveal>
                <h2 class="text-3xl font-black text-white md:text-4xl">Ready to Architect Your Solution?</h2>
                <p class="mx-auto mt-4 max-w-3xl text-slate-200">Stop settling. Start building. Engineer the digital systems your business needs.</p>
                <a href="#contact" class="mt-7 inline-flex rounded-full bg-orange-500 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-orange-400">Schedule Your Strategy Session</a>
            </section>

            <section id="contact" class="mt-24 grid gap-8 lg:grid-cols-[1.15fr_0.85fr]" data-reveal>
                <div class="rounded-2xl border border-white/10 bg-slate-900/75 p-7">
                    <h2 class="text-3xl font-black text-white">Let's Connect</h2>
                    <p class="mt-4 text-slate-300">Your business requires software that works as hard as you do. Let's map your bottlenecks into a robust digital solution.</p>

                    <form class="mt-8 space-y-5" @submit.prevent="submitLead">
                        <div>
                            <label class="block text-sm font-medium text-slate-200">Full Name</label>
                            <input v-model="form.name" type="text" placeholder="First and Last Name" class="mt-2 w-full rounded-xl border border-white/15 bg-slate-950/80 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-orange-300 focus:outline-none" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-300">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Corporate Email</label>
                            <input v-model="form.email" type="email" placeholder="you@company.com" class="mt-2 w-full rounded-xl border border-white/15 bg-slate-950/80 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-orange-300 focus:outline-none" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-300">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Phone Number</label>
                            <input v-model="form.phone" type="text" placeholder="+91 XXXXX XXXXX" class="mt-2 w-full rounded-xl border border-white/15 bg-slate-950/80 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-orange-300 focus:outline-none" />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-300">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Project Type</label>
                            <select v-model="form.project_type" class="mt-2 w-full rounded-xl border border-white/15 bg-slate-950/80 px-4 py-3 text-sm text-white focus:border-orange-300 focus:outline-none">
                                <option disabled value="">Select a project type</option>
                                <option v-for="type in projectTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                            <p v-if="form.errors.project_type" class="mt-1 text-xs text-red-300">{{ form.errors.project_type }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Project Description (Optional)</label>
                            <textarea v-model="form.description" rows="4" placeholder="Briefly describe your core operational challenge or project vision..." class="mt-2 w-full rounded-xl border border-white/15 bg-slate-950/80 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-orange-300 focus:outline-none"></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-300">{{ form.errors.description }}</p>
                        </div>

                        <button :disabled="form.processing" class="rounded-full bg-orange-500 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-70">
                            {{ form.processing ? 'Submitting...' : 'Request a Project Quote' }}
                        </button>

                        <p v-if="form.recentlySuccessful" class="rounded-lg border border-emerald-300/40 bg-emerald-300/10 px-3 py-2 text-sm text-emerald-200">
                            Thank you! Your inquiry has been received. We'll respond within 24 business hours.
                        </p>

                        <p class="text-xs text-slate-400">Your data is secure. We review all inquiries and respond within 24 business hours.</p>
                    </form>
                </div>

                <aside class="space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-900/75 p-7">
                        <h3 class="text-xl font-bold text-white">Get In Touch</h3>
                        <p class="mt-4 text-sm text-slate-300">Phone: +91 90870 21592</p>
                        <p class="mt-2 text-sm text-slate-300">Email: hello@digitalbuilders.in</p>
                        <p class="mt-2 text-sm text-slate-300">Location: Ludhiana, Punjab, India</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-slate-900/75 p-7">
                        <h3 class="text-xl font-bold text-white">Quick Access</h3>
                        <p class="mt-4 text-sm text-slate-300">Visit ashgpt.dev to view the complete architectural portfolio.</p>
                        <a href="https://www.ashgpt.dev/" target="_blank" class="mt-4 inline-block text-sm font-semibold text-orange-200 hover:text-orange-100">View Full Portfolio</a>
                    </div>
                </aside>
            </section>
        </main>

        <footer class="border-t border-white/10 bg-slate-950/85">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-slate-400 lg:px-8 lg:flex-row lg:items-center lg:justify-between">
                <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
                <p>Designed and Engineered by Ashish Gupta</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.site-bg {
    background:
        radial-gradient(1200px 700px at -10% -5%, rgba(251, 113, 30, 0.28), transparent 55%),
        radial-gradient(800px 600px at 110% 40%, rgba(249, 115, 22, 0.16), transparent 52%),
        linear-gradient(180deg, #020617 0%, #0b1220 55%, #050912 100%);
    font-family: Sora, Manrope, "Segoe UI", sans-serif;
}

html {
    scroll-behavior: smooth;
}

[data-reveal],
[data-stagger-item],
[data-hero-title] {
    opacity: 0;
}
</style>
