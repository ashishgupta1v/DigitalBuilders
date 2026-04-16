<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { animate, inView, stagger } from 'motion';
import { onMounted, ref } from 'vue';
import LogoCloudDemo from '@/Components/ui/logo-cloud-2-demo.vue';

type MotionAnimate = (
    target: Element | NodeListOf<Element>,
    keyframes: Record<string, unknown>,
    options?: Record<string, unknown>,
) => void;

const motionAnimate = animate as unknown as MotionAnimate;

defineProps<{
    canLogin?: boolean;
}>();

const mobileMenuOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: '',
    description: '',
});

const projectTypes: Array<{ value: string; label: string }> = [
    { value: 'web_app', label: 'Web Application' },
    { value: 'mobile_app', label: 'Mobile App' },
    { value: 'erp_crm', label: 'ERP / CRM' },
    { value: 'saas', label: 'SaaS Platform' },
    { value: 'ai_solutions', label: 'AI Solutions' },
    { value: 'other', label: 'Other' },
] as const;

const services: Array<{ title: string; summary: string }> = [
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
        summary: 'Practical AI workflows to automate repetitive tasks and improve speed.',
    },
    {
        title: 'Enterprise-Grade Systems (ERP and CRM)',
        summary: 'Centralized ERP and CRM systems to keep your data, sales, and operations in one place.',
    },
] as const;

type CaseStudy = {
    id: string;
    client: string;
    tldr: string;
    problem: string;
    challenge: string;
    architectureActions: string[];
    businessImpact: string[];
    quote: string;
    quoteAuthor: string;
};

const studies: CaseStudy[] = [
    {
        id: 'a',
        client: 'Habuilt',
        tldr: 'A high-traffic consumer platform stabilized and accelerated through elite engineering.',
        problem: 'Needed a consumer-facing platform that could handle high-volume traffic and complex interactions.',
        challenge: 'Severe latency during spikes caused bounce and capped growth.',
        architectureActions: [
            'Built a high-performance application for speed and reliability.',
            'Implemented modular monolith separation between heavy backend processing and user interface.',
            'Optimized data access and automated caching for faster retrieval.',
        ],
        businessImpact: [
            'Resilient performance under traffic surges.',
            'Secure, scalable foundation for expansion.',
            'Improved retention through seamless interactions.',
        ],
        quote: 'The new architecture is incredibly fast and highly stable under pressure.',
        quoteAuthor: 'Operations Lead, Habuilt',
    },
    {
        id: 'b',
        client: 'ZoetiCouch',
        tldr: 'Fragmented operations unified into a custom CRM with AI workflow automation.',
        problem: 'Inventory, customer relationships, and sales channels were disconnected.',
        challenge: 'Manual entry, lost leads, delayed fulfillment, and scaling limits.',
        architectureActions: [
            'Designed a centralized custom CRM and ERP platform.',
            'Added AI-driven workflow automations for lead sync.',
            'Unified all data streams into one interface.',
        ],
        businessImpact: [
            'Removed hundreds of hours of manual admin work.',
            'Enabled real-time inventory and sales visibility.',
            'Streamlined follow-ups with automated lead capture.',
        ],
        quote: 'Managing our operations is finally unified; the automation saves us daily.',
        quoteAuthor: 'Director, ZoetiCouch',
    },
    {
        id: 'c',
        client: 'SSKnitwear',
        tldr: 'A legacy brand modernized through a high-performance enterprise storefront.',
        problem: 'Needed a modern digital presence to compete in B2B and B2C.',
        challenge: 'Outdated storefront failed premium positioning and made bulk ordering hard.',
        architectureActions: [
            'Designed an enterprise-grade storefront focused on speed and visual quality.',
            'Built a highly responsive premium interface.',
            'Added robust ordering and catalog logic for complex localized requirements.',
        ],
        businessImpact: [
            'Digital experience now reflects brand legacy.',
            'Bulk ordering simplified for B2B partners.',
            'Opened scalable revenue streams.',
        ],
        quote: 'A digital storefront that finally matches the premium quality of our legacy products.',
        quoteAuthor: 'Operations Lead, SSKnitwear',
    },
];

const canonicalUrl = 'https://www.digitalbuilders.in/';

function submitLead() {
    form.post(route('library.leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}

onMounted(() => {
    // Hero title animation
    const hero = document.querySelector('[data-hero-title]');
    if (hero) {
        motionAnimate(
            hero,
            { opacity: [0, 1], transform: ['translateY(24px)', 'translateY(0px)'] },
            { duration: 0.7, ease: 'ease-out' },
        );
    }

    // Reveal section animations (lazy loaded)
    inView('[data-reveal]', (element) => {
        motionAnimate(
            element,
            { opacity: [0, 1], transform: ['translateY(28px)', 'translateY(0px)'] },
            { duration: 0.65, ease: 'ease-out' },
        );
    });

    // Staggered item animations
    inView('[data-stagger]', (element) => {
        const items = element.querySelectorAll('[data-stagger-item]');
        if (items.length > 0) {
            motionAnimate(
                items,
                { opacity: [0, 1], transform: ['translateY(18px)', 'translateY(0px)'] },
                { duration: 0.55, delay: stagger(0.08), ease: 'ease-out' },
            );
        }
    });

    // Animated counter values (only initialize visible counters)
    inView('[data-counter]', (el: Element) => {
        const htmlEl = el as HTMLElement;
        const target = parseInt(htmlEl.dataset.counter ?? '0', 10);
        const suffix = htmlEl.dataset.suffix ?? '';
        const start = Date.now();
        const duration = 1400;
        
        const updateCounter = () => {
            const elapsed = Date.now() - start;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(easeOut * target);
            htmlEl.textContent = `${current}${suffix}`;
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        };
        
        requestAnimationFrame(updateCounter);
    });
});
</script>

<template>
        <Head title="DigitalBuilders — Enterprise Web, Mobile & AI Architecture">
        <meta head-key="description" name="description" content="DigitalBuilders delivers enterprise-grade web applications, mobile apps, and AI solutions engineered for scale. Based in Ludhiana, Punjab, India." />
        <meta head-key="robots" name="robots" content="index, follow" />
        <link head-key="canonical" rel="canonical" :href="canonicalUrl" />
        <meta head-key="og-title" property="og:title" content="DigitalBuilders — Enterprise Web, Mobile & AI Architecture" />
        <meta head-key="og-description" property="og:description" content="Custom software engineered with a Staff Engineer mindset. Web apps, mobile apps, AI agents, ERP/CRM — built to scale your business." />
        <meta head-key="og-type" property="og:type" content="website" />
        <meta head-key="og-url" property="og:url" :content="canonicalUrl" />
        <meta head-key="og-locale" property="og:locale" content="en_IN" />
        <meta head-key="twitter-card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter-title" name="twitter:title" content="DigitalBuilders" />
        <meta head-key="twitter-description" name="twitter:description" content="Enterprise-grade web, mobile, and AI architecture for ambitious businesses." />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)]">
        <div class="db-progress" />
        <div class="db-grid-overlay" />
        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <a href="#top" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</a>

                <nav class="hidden items-center gap-5 text-sm font-medium md:flex lg:gap-6">
                    <a href="#services" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Services</a>
                    <a href="#portfolio" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Portfolio</a>
                    <a href="#about" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">About</a>
                    <a href="#contact" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    <Link v-if="canLogin" :href="route('login')" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-[var(--db-muted)] transition hover:border-white/50 hover:text-[var(--db-text)] sm:inline-flex">
                        Log in
                    </Link>
                    <!-- Mobile hamburger -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-[#b8c9e633] text-[var(--db-muted)] transition hover:text-[var(--db-text)] md:hidden focus:outline-none"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="Toggle menu"
                    >
                        <svg v-if="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Mobile menu drawer -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-3"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="opacity-0 -translate-y-3"
            >
                <div v-if="mobileMenuOpen" class="border-t border-[#b8c9e622] bg-[var(--db-nav-bg)] px-4 pb-5 pt-4 sm:px-5 md:hidden">
                    <nav class="flex flex-col gap-3 text-sm font-medium">
                        <a href="#services" @click="mobileMenuOpen = false" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Services</a>
                        <a href="#portfolio" @click="mobileMenuOpen = false" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Portfolio</a>
                        <a href="#about" @click="mobileMenuOpen = false" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">About</a>
                        <a href="#contact" @click="mobileMenuOpen = false" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Contact</a>
                    </nav>
                    <div v-if="canLogin" class="mt-4 flex flex-col gap-2 border-t border-[#b8c9e622] pt-4">
                        <Link :href="route('login')" class="inline-flex w-full items-center justify-center rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-[var(--db-muted)] transition hover:border-white/50 hover:text-[var(--db-text)]">
                            Log in
                        </Link>
                    </div>
                </div>
            </Transition>
        </header>

        <main id="top" class="mx-auto max-w-7xl px-4 pb-16 sm:px-5 sm:pb-20 lg:px-8">
            <section class="pt-12 sm:pt-16 lg:pt-20">
                <p class="db-chip mb-5">
                    Enterprise Architecture
                </p>
                <h1 data-hero-title class="max-w-4xl text-3xl font-black leading-tight text-white sm:text-4xl md:text-6xl">
                    We Build Your Digital Future.
                </h1>
                <p class="mt-6 max-w-3xl text-base leading-relaxed text-slate-300 md:text-lg">
                    Stop settling for standard web design. Get enterprise-grade web, mobile, and AI architecture engineered to scale your business.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="#contact" class="inline-flex w-full items-center justify-center rounded-full border border-[#b8c9e640] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3 text-sm font-bold text-[#1a2231] transition hover:brightness-110 sm:w-auto">Book a Discovery Call</a>
                    <a href="#portfolio" class="inline-flex w-full items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-bold text-white transition hover:border-white/50 sm:w-auto">View Our Portfolio</a>
                </div>
                <p class="mt-6 text-sm text-slate-400">Bringing Silicon Valley engineering discipline and AI automation right here to Ludhiana.</p>
            </section>

            <section class="mt-16 grid gap-4 md:grid-cols-3" data-stagger data-reveal>
                <article data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 shadow-[0_18px_30px_rgba(10,16,24,0.28)]">
                    <h3 class="text-lg font-bold text-[#b7d3ff]">Clean Information Hierarchy</h3>
                    <p class="mt-3 text-sm text-slate-300">Sections move users from discovery to trust to conversion without friction.</p>
                </article>
                <article data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 shadow-[0_18px_30px_rgba(10,16,24,0.28)]">
                    <h3 class="text-lg font-bold text-[#b7d3ff]">Smooth User Experience</h3>
                    <p class="mt-3 text-sm text-slate-300">Clean animations and flow keep the experience easy and professional.</p>
                </article>
                <article data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 shadow-[0_18px_30px_rgba(10,16,24,0.28)]">
                    <h3 class="text-lg font-bold text-[#b7d3ff]">Conversion-Ready Structure</h3>
                    <p class="mt-3 text-sm text-slate-300">Value, proof, authority, and CTA blocks are sequenced intentionally.</p>
                </article>
            </section>

            <section class="mt-16 sm:mt-20" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-[#9dc5ff]">Why DigitalBuilders</p>
                <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl md:text-4xl">Strong Tech. Real Business Results.</h2>
                <p class="mt-5 max-w-4xl text-slate-300">
                    We do not build short-term templates. We bring a Staff Engineer mindset to your business, handling growth, engineering robust systems built to withstand high traffic, complex data pipelines, and rapid local market scaling.
                </p>
                <ul class="mt-6 grid gap-3 text-sm text-slate-200 sm:grid-cols-3">
                    <li class="db-mini rounded-xl border border-[#b8c9e633] bg-[#27374dcc] px-4 py-3">Custom Software for Your Business with Modular Monoliths</li>
                    <li class="db-mini rounded-xl border border-[#b8c9e633] bg-[#27374dcc] px-4 py-3">Clean and Maintainable Code with Zero Legacy Tech Debt</li>
                    <li class="db-mini rounded-xl border border-[#b8c9e633] bg-[#27374dcc] px-4 py-3">Autonomous AI Agent Integration</li>
                </ul>
            </section>

            <section class="mt-20 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger data-reveal>
                <div data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-white" data-counter="8" data-suffix="+">8+</p>
                    <p class="mt-2 text-sm text-slate-300">Years Experience</p>
                </div>
                <div data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-white" data-counter="25" data-suffix="+">25+</p>
                    <p class="mt-2 text-sm text-slate-300">Projects Delivered</p>
                </div>
                <div data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-white" data-counter="10" data-suffix="+">10+</p>
                    <p class="mt-2 text-sm text-slate-300">Active Clients</p>
                </div>
                <div data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                    <p class="text-3xl font-black text-white">100%</p>
                    <p class="mt-2 text-sm text-slate-300">Engineering Mastery</p>
                </div>
            </section>

            <section id="services" class="mt-20 sm:mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-[#9dc5ff]">Our Services</p>
                <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl md:text-4xl">Complete Digital Solutions for Growing Brands</h2>
                <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-stagger>
                    <article v-for="service in services" :key="service.title" data-stagger-item class="rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6">
                        <h3 class="text-lg font-bold text-white">{{ service.title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-300">{{ service.summary }}</p>
                    </article>
                </div>
            </section>

            <section id="portfolio" class="mt-20 sm:mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-[#9dc5ff]">Case Studies</p>
                <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl md:text-4xl">Real Results from Real Client Projects</h2>
                <p class="mt-4 max-w-3xl text-sm text-slate-300">
                    Swipe horizontally to explore each project in detail.
                </p>

                <div class="case-scroll mt-8 flex gap-5 overflow-x-auto pb-5" data-stagger>
                    <article
                        v-for="study in studies"
                        :key="study.client"
                        data-stagger-item
                        class="case-card snap-start rounded-3xl border border-[#b8c9e640] bg-[linear-gradient(165deg,#27374de6_0%,#1f2d3fe8_100%)] p-4 sm:p-6"
                    >

                        <div class="mt-5 flex items-center justify-between gap-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9dc5ff]">Project {{ study.id.toUpperCase() }}</p>
                            <span class="rounded-full border border-[#b8c9e633] px-3 py-1 text-xs text-[#d0ddff]">{{ study.client }}</span>
                        </div>

                        <p class="mt-4 text-sm font-semibold text-white">{{ study.tldr }}</p>

                        <div class="mt-4 space-y-2 text-sm text-slate-300">
                            <p><span class="font-semibold text-[#dce6ff]">Problem:</span> {{ study.problem }}</p>
                            <p><span class="font-semibold text-[#dce6ff]">Challenge:</span> {{ study.challenge }}</p>
                        </div>

                        <div class="mt-5 rounded-2xl border border-[#9ba7ff33] bg-[#1d2a3f9a] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#a8bbff]">Architecture Actions</p>
                            <ul class="mt-3 space-y-2 text-sm text-slate-200">
                                <li v-for="(item, idx) in study.architectureActions" :key="`${study.client}-arch-${idx}`" class="flex gap-2">
                                    <span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-[#7ac4ff]" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-4 rounded-2xl border border-[#8bd5b333] bg-[#1e2f409c] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9feac5]">Business Impact</p>
                            <ul class="mt-3 space-y-2 text-sm text-slate-200">
                                <li v-for="(item, idx) in study.businessImpact" :key="`${study.client}-impact-${idx}`" class="flex gap-2">
                                    <span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-[#8bd5b3]" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>

                        <blockquote class="mt-5 rounded-2xl border border-[#c593ff44] bg-[#2a26459e] p-4 text-sm text-[#e4d3ff]">
                            "{{ study.quote }}"
                            <footer class="mt-2 text-xs text-[#d3c3ff]">{{ study.quoteAuthor }}</footer>
                        </blockquote>
                    </article>
                </div>
            </section>

            <section id="collaboration" class="mt-20 sm:mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-[#9dc5ff]">Collaboration Network</p>
                <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl md:text-4xl">Acceleration Platforms</h2>
                <p class="mt-4 max-w-3xl text-sm text-slate-300">
                    We work with modern AI, cloud, and developer platforms to speed up delivery and build reliable products.
                </p>
                <div class="mt-8">
                    <LogoCloudDemo />
                    <br>
                </div>

                <div class="mt-8 rounded-2xl border border-[#b8c9e640] bg-[#27374d7f] p-5 text-center sm:p-6">
                    <p class="text-lg font-semibold text-white sm:text-xl">Ready to architect your success story?</p>
                    <a href="#contact" class="mt-4 inline-flex w-full items-center justify-center rounded-full border border-[#b8c9e640] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3 text-sm font-bold text-[#1a2231] transition hover:brightness-110 sm:w-auto">Start Your Project</a>
                </div>
            </section>

            <section id="about" class="mt-20 grid gap-6 sm:mt-24 sm:gap-8 lg:grid-cols-[1.2fr_1fr]" data-reveal>
                <div class="db-mini rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-5 sm:p-7">
                    <p class="text-sm uppercase tracking-[0.2em] text-[#9dc5ff]">About Us</p>
                    <h2 class="mt-3 text-2xl font-black text-white sm:text-3xl">Ashish Gupta</h2>
                    <p class="mt-1 text-sm text-[#d8c3ff]">Lead Digital Architect · Founder</p>
                    <p class="mt-5 text-slate-300">
                        Over 8 years in enterprise IT designing and deploying complex large-scale software systems. DigitalBuilders was founded to deliver production-grade architecture, not fragile templates.
                    </p>
                </div>
                <div class="db-mini rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-5 sm:p-7">
                    <p class="text-sm uppercase tracking-[0.2em] text-[#9dc5ff]">How We Work</p>
                    <ol class="mt-4 space-y-4 text-sm text-slate-200">
                        <li><span class="font-bold text-white">01 Understand Your Needs</span> — We discuss goals, challenges, and priorities.</li>
                        <li><span class="font-bold text-white">02 Plan the Right Solution</span> — We design the system before development starts.</li>
                        <li><span class="font-bold text-white">03 Build and Deliver</span> — We deliver in clear phases with regular updates.</li>
                    </ol>
                </div>
            </section>

            <section class="db-mini mt-20 rounded-2xl border border-[#b8c9e655] bg-[linear-gradient(120deg,rgba(122,196,255,0.16),rgba(197,147,255,0.16))] p-5 text-center sm:mt-24 sm:p-8" data-reveal>
                <h2 class="text-2xl font-black text-white sm:text-3xl md:text-4xl">Ready to Architect Your Solution?</h2>
                <p class="mx-auto mt-4 max-w-3xl text-slate-200">Stop settling. Start building. Engineer the digital systems your business needs.</p>
                <a href="#contact" class="mt-7 inline-flex w-full items-center justify-center rounded-full border border-[#b8c9e640] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3 text-sm font-bold text-[#1a2231] transition hover:brightness-110 sm:w-auto">Schedule Your Strategy Session</a>
            </section>

            <section id="contact" class="mt-20 grid gap-6 sm:mt-24 sm:gap-8 lg:grid-cols-[1.15fr_0.85fr]" data-reveal>
                <div class="db-mini rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-5 sm:p-7">
                    <h2 class="text-2xl font-black text-white sm:text-3xl">Let's Connect</h2>
                    <p class="mt-4 text-slate-300">Your business requires software that works as hard as you do. Let's map your bottlenecks into a robust digital solution.</p>

                    <form class="mt-8 space-y-5" @submit.prevent="submitLead">
                        <div>
                            <label class="block text-sm font-medium text-slate-200">Full Name</label>
                            <input v-model="form.name" type="text" placeholder="First and Last Name" class="mt-2 w-full rounded-xl border border-[#b8c9e640] bg-[#1f2d3fe6] px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#9ba7ff] focus:outline-none" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-300">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Email Address</label>
                            <input v-model="form.email" type="email" placeholder="you@company.com" class="mt-2 w-full rounded-xl border border-[#b8c9e640] bg-[#1f2d3fe6] px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#9ba7ff] focus:outline-none" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-300">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Phone Number</label>
                            <input v-model="form.phone" type="text" placeholder="+91 XXXXX XXXXX" class="mt-2 w-full rounded-xl border border-[#b8c9e640] bg-[#1f2d3fe6] px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#9ba7ff] focus:outline-none" />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-300">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Project Type</label>
                            <select v-model="form.project_type" class="mt-2 w-full rounded-xl border border-[#b8c9e640] bg-[#1f2d3fe6] px-4 py-3 text-sm text-white focus:border-[#9ba7ff] focus:outline-none">
                                <option disabled value="">Select a project type</option>
                                <option v-for="type in projectTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                            <p v-if="form.errors.project_type" class="mt-1 text-xs text-red-300">{{ form.errors.project_type }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Project Description (Optional)</label>
                            <textarea v-model="form.description" rows="4" placeholder="Briefly describe your core operational challenge or project vision..." class="mt-2 w-full rounded-xl border border-[#b8c9e640] bg-[#1f2d3fe6] px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#9ba7ff] focus:outline-none"></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-300">{{ form.errors.description }}</p>
                        </div>

                        <button :disabled="form.processing" class="rounded-full border border-[#b8c9e640] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3 text-sm font-bold text-[#1a2231] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-70">
                            {{ form.processing ? 'Submitting...' : 'Request a Project Quote' }}
                        </button>

                        <p v-if="form.recentlySuccessful" class="rounded-lg border border-emerald-300/40 bg-emerald-300/10 px-3 py-2 text-sm text-emerald-200">
                            Thank you! Your inquiry has been received. We'll respond within 24 business hours.
                        </p>

                        <p class="text-xs text-slate-400">Your data is secure. We review all inquiries and respond within 24 business hours.</p>
                    </form>
                </div>

                <aside class="space-y-4">
                    <div class="db-mini rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-5 sm:p-7">
                        <h3 class="text-xl font-bold text-white">Get In Touch</h3>
                        <p class="mt-4 text-sm text-slate-300">Phone: +91 90870 21592</p>
                        <p class="mt-2 break-all text-sm text-slate-300">Email: hello@digitalbuilders.in</p>
                        <p class="mt-2 text-sm text-slate-300">Location: Ludhiana, Punjab, India</p>
                    </div>
                    <div class="db-mini rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-5 sm:p-7">
                        <h3 class="text-xl font-bold text-white">Quick Access</h3>
                        <p class="mt-4 text-sm text-slate-300">Visit ashgpt.dev to explore more projects and technical work.</p>
                        <a href="https://www.ashgpt.dev/" target="_blank" rel="noopener noreferrer" class="mt-4 inline-block text-sm font-semibold text-[#c8d6ff] hover:text-[#e8efff]">View More Projects</a>
                    </div>
                </aside>
            </section>
        </main>

        <footer class="border-t border-[#b8c9e633] bg-[#233246d9]">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 text-center text-sm text-slate-400 sm:px-5 lg:px-8 lg:flex-row lg:items-center lg:justify-between lg:text-left">
                <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
                <p>Designed and Engineered by Ashish Gupta</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.site-bg {
    background:
        radial-gradient(1200px 700px at -10% -5%, rgba(122, 196, 255, 0.28), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(197, 147, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
    font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
}

html {
    scroll-behavior: smooth;
}

[data-reveal],
[data-stagger-item],
[data-hero-title] {
    opacity: 0;
}

.case-scroll {
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
    scrollbar-color: rgba(154, 173, 255, 0.8) rgba(39, 55, 77, 0.4);
}

.case-scroll::-webkit-scrollbar {
    height: 10px;
}

.case-scroll::-webkit-scrollbar-track {
    background: rgba(39, 55, 77, 0.35);
    border-radius: 999px;
}

.case-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, rgba(122, 196, 255, 0.85), rgba(197, 147, 255, 0.9));
    border-radius: 999px;
}

.case-card {
    min-width: min(90vw, 560px);
    scroll-snap-align: start;
}
</style>
