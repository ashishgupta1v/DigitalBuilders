<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { animate, inView, stagger } from 'motion';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import HeroCanvas from '@/Components/HeroCanvas.vue';
import ProjectEstimator from '@/Components/ProjectEstimator.vue';
import TestimonialsCarousel from '@/Components/TestimonialsCarousel.vue';
import AiAssistantWidget from '@/Components/AiAssistantWidget.vue';

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
const showBackToTop = ref(false);
const isDarkMode = ref(true);
const selectedCategory = ref<string>('all');
const activeModalStudy = ref<CaseStudy | null>(null);

function openStudyModal(study: CaseStudy) {
    activeModalStudy.value = study;
    document.body.style.overflow = 'hidden';
}

function closeStudyModal() {
    activeModalStudy.value = null;
    document.body.style.overflow = '';
}

function toggleTheme() {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.removeAttribute('data-theme');
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
        localStorage.removeItem('db-theme');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
        localStorage.setItem('db-theme', 'light');
    }
}

const handleScroll = () => {
    showBackToTop.value = window.scrollY > 600;
};

const handleScrollProgress = () => {
    const scrolled = window.scrollY;
    const docHeight = document.body.scrollHeight - window.innerHeight;
    const pct = docHeight > 0 ? (scrolled / docHeight) * 100 : 0;
    document.documentElement.style.setProperty('--db-scroll', `${pct}%`);
};

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

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

const services: Array<{ title: string; summary: string; link: string }> = [
    {
        title: 'Custom Web Application Development',
        summary: 'Blazing-fast, scalable web applications engineered with modern Laravel + Vue architecture.',
        link: '/services/web-applications',
    },
    {
        title: 'Mobile App Development (iOS & Android)',
        summary: 'Native-feeling, robust mobile apps connecting your brand and customers seamlessly.',
        link: '/services/mobile-apps',
    },
    {
        title: 'AI Voice Agents & Chatbots',
        summary: 'Autonomous conversational agents that capture leads and reduce manual support load.',
        link: '/services/ai-solutions',
    },
    {
        title: 'AI Development & Workflows',
        summary: 'Practical AI workflows to automate repetitive tasks and dramatically improve speed.',
        link: '/services/ai-solutions',
    },
    {
        title: 'Enterprise ERP & CRM Systems',
        summary: 'Centralized ERP and CRM systems to keep your data, sales, and operations in one place.',
        link: '/services/erp-crm',
    },
    {
        title: 'High-Scale SaaS Platforms',
        summary: 'Multi-tenant SaaS with subscription engines, billing, and role-based access built to scale.',
        link: '/services/saas-platforms',
    },
] as const;

type CaseStudy = {
    id: string;
    client: string;
    portfolioSlug: string;
    category: 'saas' | 'health' | 'consumer' | 'mobile' | 'ngo';
    categoryLabel: string;
    liveUrl: string | null;
    isMobile: boolean;
    image: string;
    metricBadge: string;
    techStack: string[];
    tldr: string;
    problem: string;
    challenge: string;
    architectureActions: string[];
    businessImpact: string[];
    quote: string;
    quoteAuthor: string;
};

const categories = [
    { id: 'all', label: 'All Projects' },
    { id: 'saas', label: 'SaaS & FinTech' },
    { id: 'health', label: 'Health & Telehealth' },
    { id: 'consumer', label: 'Consumer & AstroTech' },
    { id: 'mobile', label: 'Mobile Apps' },
    { id: 'ngo', label: 'Trust & NGO' },
] as const;

const studies: CaseStudy[] = [
    {
        id: 'habuilt',
        client: 'Habuilt',
        portfolioSlug: 'habuilt',
        category: 'consumer',
        categoryLabel: 'High-Scale Habit & Wellness SaaS',
        liveUrl: 'https://www.habuilt.com/',
        isMobile: false,
        image: '/images/portfolio/habuilt.jpg',
        metricBadge: '50 Habits · 4 Tiers · 99.99% Uptime',
        techStack: ['Next.js / Vue 3', 'TypeScript', 'Tailwind CSS', 'Redis Caching', 'Mobile Deep Linking'],
        tldr: 'Progressive atomic habit tracking platform with 50 habits, 4 progression tiers, streak mechanics, and mobile deep link auth.',
        problem: 'Building lasting daily habits requires continuous positive reinforcement without complex friction or mobile disconnect.',
        challenge: 'Supporting high-frequency daily habit check-ins and streaks across 26-week progression tiers with instant mobile app handoff.',
        architectureActions: [
            'Engineered a 4-tier progressive unlock timeline (Foundation, Build, Refine, Mastery) across 26 weeks.',
            'Implemented sub-50ms streak & XP leveling computations with carry-forward point balances.',
            'Integrated universal mobile deep linking (habuilt://auth/callback) for instant mobile session handover.',
        ],
        businessImpact: [
            '1% daily compounding discipline engine adopted by thousands of active habit builders.',
            'Zero-latency habit completion sync and interactive weekly completion heatmaps.',
            '99.99% uptime with high-concurrency atomic data persistence.',
        ],
        quote: 'DigitalBuilders brought Silicon Valley-level engineering standards. Our server latencies dropped by 70% and habit check-in throughput doubled within weeks.',
        quoteAuthor: 'Gurpreet Singh, CTO & Co-Founder, Habuilt Technologies',
    },
    {
        id: 'dhandadiary',
        client: 'Dhanda Diary',
        portfolioSlug: 'dhandadiary',
        category: 'saas',
        categoryLabel: 'Execution Cockpit & Business Ledger SaaS',
        liveUrl: 'https://dhandadiary.cloud/',
        isMobile: false,
        image: '/images/portfolio/dhandadiary.jpg',
        metricBadge: 'Daily Compliance Engine · Sub-50ms Sync',
        techStack: ['Laravel 13', 'Vue 3', 'Inertia.js', 'ApexCharts', 'VAPID Web Push', 'Google OAuth'],
        tldr: 'Execution cockpit & daily compliance SaaS with automated DCR routines, Kanban task boards, ApexCharts KPI trackers, and discipline streak multipliers.',
        problem: 'Founders and enterprise teams lack a centralized execution cockpit to monitor daily compliance, habit discipline, and business KPIs in one place.',
        challenge: 'Needed sub-50ms reactive state sync, real-time Kanban task reordering, Web Push reminders, and multi-tenant ledger isolation.',
        architectureActions: [
            'Architected Daily Compliance Report (DCR) and Weekly Strategic Review pipelines with automated streak calculations.',
            'Integrated interactive ApexCharts visual metric telemetry for revenue trends and operational KPIs.',
            'Built drag-and-drop Kanban workflow boards with optimistic UI updates and background sync.',
        ],
        businessImpact: [
            '100% daily task execution accountability for business executives and remote teams.',
            'Automated morning and evening Web Push reminders increasing daily routine completion by 85%.',
            'Seamless Google OAuth authentication and instant zero-latency multi-device sync.',
        ],
        quote: 'Dhanda Diary transformed daily execution and habit discipline for our teams. The cockpit is responsive, fast, and gives us instant visibility over every KPI.',
        quoteAuthor: 'Harpreet Singh, Co-Founder, Dhanda Diary Cloud',
    },
    {
        id: 'zoeticoach',
        client: 'ZoetiCoach AI',
        portfolioSlug: 'zoeticoach',
        category: 'saas',
        categoryLabel: 'WhatsApp-First B2B2C Coaching ERP',
        liveUrl: 'https://zoeticoach.com/',
        isMobile: false,
        image: '/images/portfolio/zoeticoach.jpg',
        metricBadge: 'OpenAI RAG Pipeline · Zero Client Drop-off',
        techStack: ['Laravel 13', 'Vue 3', 'pgvector', 'OpenAI RAG', 'WhatsApp Cloud API', 'Modular Monolith'],
        tldr: 'WhatsApp-first accountability SaaS for professional coaches featuring autonomous AI habit verification and event-sourced client ledgers.',
        problem: 'Professional fitness and mindset coaches lose 40%+ of clients due to accountability drop-off outside of weekly calls.',
        challenge: 'Automating personalized, hallucination-free habit verification directly inside WhatsApp without requiring clients to install another app.',
        architectureActions: [
            'Engineered a production OpenAI RAG pipeline with pgvector to verify habit proof (photos, text) directly via WhatsApp.',
            'Architected a scalable Laravel Modular Monolith with an event-sourced ledger for real-time coach cohort management.',
            'Built automated client intake, milestone tracking, and retention analytics dashboards.',
        ],
        businessImpact: [
            'Reduced client dropout rate by 65% across pilot coaching cohorts.',
            'Automated 100% of routine daily check-ins without coach intervention.',
            'Eliminated manual spreadsheet tracking for multi-coach wellness enterprises.',
        ],
        quote: 'ZoetiCoach eliminates client drop-off completely. The AI verification over WhatsApp feels like having a personal assistant working 24/7 for every client.',
        quoteAuthor: 'Rajesh Sharma, Managing Director, ZoetiCoach AI',
    },
    {
        id: 'guttalks',
        client: 'GutTalks',
        portfolioSlug: 'guttalks',
        category: 'health',
        categoryLabel: 'Gut Health & Telehealth Consultation Portal',
        liveUrl: 'https://guttalks.in/',
        isMobile: false,
        image: '/images/portfolio/guttalks.jpg',
        metricBadge: '10k+ Clients · 4.8★ Rating · ₹499 Root Rx',
        techStack: ['Next.js', 'React', 'Tailwind CSS', 'Razorpay Checkout', 'Microbiome API', 'Doctor Telehealth'],
        tldr: 'Evidence-based gut health platform connecting patients to gastroenterologists via Root Rx consultations and GutMap Complete™ at-home microbiome testing.',
        problem: 'Patients with chronic bloating, IBS, and digestive fatigue face fragmented advice and high consultation barriers.',
        challenge: 'Needed a high-converting doctor booking engine with instant slot availability, GutMap kit ordering, and seamless telehealth consultations.',
        architectureActions: [
            'Built an instant Root Rx booking widget (₹499) with real-time doctor availability slot selection.',
            'Engineered GutMap Complete™ at-home testing kit portal with laboratory sequencing sample tracking.',
            'Integrated Razorpay payment gateway and automated WhatsApp consultation reminders.',
        ],
        businessImpact: [
            'Over 10,000+ happy clients treated with 4.8-star verified Google rating.',
            '3.2x increase in consultation conversion rate compared to standard static medical forms.',
            'Full doctor-approved lifestyle roadmap delivered within 30 days of initial consultation.',
        ],
        quote: 'The Root Rx booking flow and GutMap testing portal engineered by DigitalBuilders increased our consultation bookings dramatically. Our patients love how intuitive the interface is.',
        quoteAuthor: 'Dr. Mehak Verma, Head of Clinical Nutrition, GutTalks India',
    },
    {
        id: 'myastrova',
        client: 'MyAstrova',
        portfolioSlug: 'myastrova',
        category: 'consumer',
        categoryLabel: 'Vedic AstroTech & Spiritual E-Commerce',
        liveUrl: 'https://myastrova.com/',
        isMobile: false,
        image: '/images/portfolio/myastrova.jpg',
        metricBadge: '<200ms Kundli Engine · Live Calls & Chat',
        techStack: ['Next.js', 'React', 'Tailwind CSS', 'Razorpay Checkout', 'WhatsApp API', 'Vedic Math Engine'],
        tldr: 'Vedic astrology consultation platform offering instant call/chat with astrologers, Kundli matching algorithms, and an energized crystal/gemstone mall.',
        problem: 'Traditional astrology portals suffer from slow chart rendering, confusing interfaces, and unverified remedy purchases.',
        challenge: 'Calculating planetary positions with ephemeris accuracy in sub-200ms while routing live chat/call consultation requests to available astrologers.',
        architectureActions: [
            'Engineered a mathematical Kundli Matching and Horoscope calculation engine rendering dynamic charts in <200ms.',
            'Built real-time astrologer routing for instant chats, phone calls, and video consultations.',
            'Created MyAstrova Mall e-commerce catalog for energized crystals, rudraksha, and customized remedies with Razorpay checkout.',
        ],
        businessImpact: [
            'Instantaneous chart generation with 100% mathematical precision.',
            '99.9% booking and checkout reliability across high consumer traffic.',
            'Built a trusted spiritual brand with direct WhatsApp concierge support.',
        ],
        quote: 'DigitalBuilders delivered a mathematically accurate and stunning AstroTech platform. The charts render instantly and the seamless booking process has made MyAstrova a trusted name.',
        quoteAuthor: 'Acharya Raman, Principal Astrologer, MyAstrova',
    },
    {
        id: 'gaushala',
        client: 'Krishan Balram Gaushala',
        portfolioSlug: 'gaushala',
        category: 'ngo',
        categoryLabel: 'Devotee Engagement & Cow Shelter Philanthropy',
        liveUrl: 'https://krishanbalramgaushala.com/',
        isMobile: false,
        image: '/images/portfolio/gaushala.jpg',
        metricBadge: 'Meta WhatsApp API · Automated 80G Receipts',
        techStack: ['Laravel 13', 'Vue 3', 'Inertia.js', 'Meta WhatsApp Cloud API', 'PWA Offline', 'SQLite WAL'],
        tldr: 'GauSeva Connect — Devotee registration portal, automated birthday/anniversary WhatsApp blessings dispatcher, and 80G tax receipt management.',
        problem: 'The shelter handled thousands of devotee records and seva donations manually, causing delayed tax receipts and missed community touchpoints.',
        challenge: 'Automating daily WhatsApp blessings, Facebook auto-posting, and instant 80G PDF receipts on a high-concurrency, lightweight server.',
        architectureActions: [
            'Integrated Meta WhatsApp Cloud API webhooks to automatically dispatch personalized birthday and anniversary blessings daily.',
            'Architected an automated 80G tax exemption PDF generator and donor contribution ledger.',
            'Engineered PWA with client-side HTML5 canvas image compression (99% bandwidth reduction on photo uploads) and SQLite WAL tuning.',
        ],
        businessImpact: [
            '100% automated birthday and anniversary blessings sent to thousands of registered devotees.',
            '3.5x increase in online donor engagement and instant automated 80G receipt delivery.',
            'Eliminated manual bookkeeping and photo dispatch workload for temple volunteers.',
        ],
        quote: 'The GauSeva Connect platform gave our devotees automated blessings on their special days and instant tax receipts. It transformed how our shelter connects with donors.',
        quoteAuthor: 'Trust Management, Krishan Balram Gaushala Ludhiana',
    },
    {
        id: 'ashishgupta',
        client: 'Ashish Gupta Hub',
        portfolioSlug: 'ssknitwear',
        category: 'consumer',
        categoryLabel: 'Engineering Architecture Showcase',
        liveUrl: 'https://ashishgupta.dev/',
        isMobile: false,
        image: '/images/portfolio/ashishgupta.jpg',
        metricBadge: 'VILT Stack · 9+ Yrs IT · $1M Cloud Savings',
        techStack: ['Laravel 12', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'PWA Offline', 'Filament CMS'],
        tldr: 'High-performance engineering hub showcasing modernizing legacy healthcare/aviation monoliths, live telemetry metrics, and VILT stack architecture.',
        problem: 'Technical decision-makers needed verified proof of enterprise architectural leadership, DDD modernization, and cost optimization.',
        challenge: 'Demonstrating interactive architecture diagrams, live GitHub stats, visitor analytics, and dark/light system state in a sub-second portfolio.',
        architectureActions: [
            'Architected a high-speed VILT stack platform with Domain-Driven Design (DDD) principles.',
            'Built real-time telemetry metrics, interactive architecture explorer, and terminal emulation.',
            'Engineered full PWA offline support with self-hosted variable typography.',
        ],
        businessImpact: [
            'Documented track record of $1M/year cloud cost reductions in enterprise modernization.',
            'Sub-0.5s initial page load time with zero external render-blocking font dependencies.',
            'Direct client and consulting acquisition channel for DigitalBuilders.',
        ],
        quote: 'Engineering excellence is about building scalable, resilient systems that eliminate complexity. Every application in our portfolio is engineered for zero technical debt.',
        quoteAuthor: 'Ashish Gupta, Lead Architect & Founder, DigitalBuilders',
    },
    {
        id: 'sportsclub',
        client: 'SportsEntertainmentClub',
        portfolioSlug: 'sports-club',
        category: 'mobile',
        categoryLabel: 'Facility & Court Booking App (iOS/Android)',
        liveUrl: null,
        isMobile: true,
        image: '/images/portfolio/sportsclub.jpg',
        metricBadge: '0 Booking Collisions · 60 FPS Fluid UI',
        techStack: ['Flutter / React Native', 'Real-Time Slot Locks', 'Push Notifications', 'QR Access Control'],
        tldr: 'Mobile app for sports facility reservations, badminton/tennis court slot locking, digital QR member passes, and live tournament leaderboards.',
        problem: 'Club members suffered from double bookings, long phone queues, and manual gate access tracking for premium courts.',
        challenge: 'Implementing atomic time-slot reservation locks with sub-second synchronization during peak booking windows.',
        architectureActions: [
            'Engineered cross-platform iOS and Android apps with 60 FPS smooth animations and instant schedule views.',
            'Built atomic slot reservation engine with instant UPI/Card payments and automatic cancellation releases.',
            'Integrated dynamic QR member passes for contactless gate access control.',
        ],
        businessImpact: [
            'Zero court reservation conflicts across 12 tennis and badminton courts.',
            '95% mobile adoption rate among registered club members in the first month.',
            '3x faster check-in speed at club reception with digital QR passes.',
        ],
        quote: 'The mobile app transformed our entire club management. Our members love booking slots right from their phones with zero waiting.',
        quoteAuthor: 'Club Secretary, SportsEntertainmentClub',
    },
    {
        id: 'gargenterprises',
        client: 'Garg Enterprises',
        portfolioSlug: 'garg-enterprises',
        category: 'mobile',
        categoryLabel: 'B2B Wholesale Ordering & Ledger App',
        liveUrl: null,
        isMobile: true,
        image: '/images/portfolio/gargenterprises.jpg',
        metricBadge: '0% Order Errors · 10k+ SKUs · Offline Sync',
        techStack: ['Android Native / Kotlin', 'Offline SQLite Sync', 'GST Invoice PDF', 'Tiered B2B Pricing'],
        tldr: 'B2B wholesale ordering app with offline drafting, dealer credit ledger reconciliation, 1-tap GST invoice downloads, and 10k+ SKU catalog.',
        problem: 'Wholesale dealers placed orders over handwritten notes and phone calls, causing order errors, inventory mismatches, and ledger disputes.',
        challenge: 'Ensuring fast order placement in low-connectivity warehouse environments with dealer-specific tiered pricing and credit limits.',
        architectureActions: [
            'Engineered rugged Android enterprise app with offline SQLite order drafting and automatic background sync.',
            'Built real-time dealer ledger displaying live balance, credit limit, and 1-tap GST invoice downloads.',
            'Implemented tiered volume discount matrix and automated warehouse dispatch alerts.',
        ],
        businessImpact: [
            'Reduced manual order entry errors from 14% to 0%.',
            '3x faster dealer reorder cycle with single-tap repeat order functionality.',
            '100% transparency on outstanding dealer ledger balances and credit terms.',
        ],
        quote: 'Our dealers can now place bulk orders anytime, view their credit ledger, and track dispatches in real-time. It completely eliminated our order confusion.',
        quoteAuthor: 'Managing Director, Garg Enterprises',
    },
];

const filteredStudies = computed(() => {
    if (selectedCategory.value === 'all') {
        return studies;
    }
    return studies.filter((study) => study.category === selectedCategory.value);
});

const faqs: Array<{ q: string; a: string }> = [
    {
        q: 'How long does a typical web application project take?',
        a: 'Timelines vary by complexity. A standard web application typically takes 4–8 weeks. Enterprise-grade platforms with custom ERP/CRM integrations can take 3–6 months. We break every project into clear sprints so you see progress weekly from day one.',
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
        q: 'How do you handle pricing?',
        a: 'We offer project-based pricing for well-defined scopes, and time-and-materials for evolving projects. Use our Project Cost Estimator to get a ballpark, then book a free 30-minute strategy session to discuss specifics.',
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
    // Restore saved theme
    const savedTheme = localStorage.getItem('db-theme');
    if (savedTheme === 'light') {
        isDarkMode.value = false;
        document.documentElement.setAttribute('data-theme', 'light');
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    } else {
        isDarkMode.value = true;
        document.documentElement.removeAttribute('data-theme');
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
    }

    // Scroll listener for back-to-top + scroll progress
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('scroll', handleScrollProgress, { passive: true });

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

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('scroll', handleScrollProgress);
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
        <header class="sticky top-0 z-50 border-b border-[#b8c9e622] bg-[var(--db-nav-bg)] backdrop-blur-xl transition-colors duration-300">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-3.5 lg:px-8">
                <!-- Logo: icon + brand name balanced -->
                <a href="#top" class="flex items-center gap-2.5 group">
                    <img
                        src="/images/db-logo.png"
                        alt="DigitalBuilders Logo"
                        class="h-10 w-10 object-contain flex-shrink-0 transition-transform duration-300 group-hover:scale-105 filter drop-shadow-[0_0_8px_rgba(125,211,252,0.4)]"
                        onerror="this.style.display='none'"
                    />
                    <span
                        class="db-brand-logo-text text-xl font-black tracking-tight"
                        style="font-family: 'Libre Baskerville', Georgia, serif; font-weight: 700; letter-spacing: -0.02em;"
                    >Digital Builders</span>
                </a>

                <nav class="hidden items-center gap-1.5 text-sm font-medium md:flex lg:gap-2">
                    <a href="#services" class="px-3 py-1.5 text-slate-400 transition-all duration-200 hover:text-white">Services</a>
                    <a href="#portfolio" class="px-3 py-1.5 text-slate-400 transition-all duration-200 hover:text-white">Portfolio</a>
                    <a href="/blog" class="px-3 py-1.5 text-slate-400 transition-all duration-200 hover:text-white">Blog</a>
                    <a href="#about" class="px-3 py-1.5 text-slate-400 transition-all duration-200 hover:text-white">About</a>
                    <a href="#contact" class="px-3 py-1.5 text-slate-400 transition-all duration-200 hover:text-white">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    <!-- Theme Toggle -->
                    <button
                        @click="toggleTheme"
                        class="hidden h-9 w-9 items-center justify-center rounded-full border border-white/10 text-slate-400 transition hover:text-white sm:inline-flex"
                        :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                    >
                        <svg v-if="isDarkMode" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    </button>
                    <Link v-if="canLogin" :href="route('login')" class="hidden rounded-full border border-white/15 px-4 py-1.5 text-xs font-semibold text-slate-300 transition hover:border-white/40 hover:text-white sm:inline-flex">
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
                        <Link href="/blog" @click="mobileMenuOpen = false" class="text-[var(--db-muted)] transition hover:text-[var(--db-text)]">Blog</Link>
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

        <main id="top" class="mx-auto max-w-7xl px-4 pb-12 sm:px-5 sm:pb-16 lg:px-8">
            <!-- Hero Section with Canvas Particle Network -->
            <section class="relative mt-4 overflow-hidden rounded-3xl border border-[#b8c9e625] bg-[var(--db-card-gradient)] p-6 pt-12 sm:p-10 sm:pt-16 lg:p-14 lg:pt-20 shadow-[0_20px_50px_rgba(0,0,0,0.2)]">
                <HeroCanvas :is-dark-mode="isDarkMode" />
                <div class="relative z-10">
                    <p class="db-chip mb-4">Enterprise Architecture</p>
                    <h1
                        data-hero-title
                        class="max-w-4xl text-3xl font-black leading-tight text-[var(--db-text)] sm:text-4xl md:text-5xl lg:text-6xl"
                        style="font-family: 'Libre Baskerville', Georgia, serif; font-weight: 700;"
                    >
                        We Build Your
                        <span class="db-gradient-text">Digital Future.</span>
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-300 dark:text-slate-300 md:text-lg"
                        style="font-family: 'Outfit', sans-serif; font-weight: 300;">
                        Stop settling for standard templates. Get enterprise-grade web, mobile, and AI architecture engineered to scale your business.
                    </p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <a href="#contact" class="db-action inline-flex w-full items-center justify-center rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-7 py-3.5 text-sm font-bold text-white dark:text-[#1a2231] shadow-[0_4px_20px_rgba(2,132,199,0.25)] transition-all hover:scale-[1.02] sm:w-auto">
                            Book a Discovery Call
                        </a>
                        <a href="#portfolio" class="inline-flex w-full items-center justify-center rounded-full border border-slate-300 dark:border-white/20 bg-white/60 dark:bg-white/5 backdrop-blur-md px-7 py-3.5 text-sm font-semibold text-slate-800 dark:text-white transition-all hover:border-slate-400 dark:hover:border-white/40 hover:bg-white/80 dark:hover:bg-white/10 sm:w-auto">
                            View Our Portfolio
                        </a>
                    </div>
                    <p class="mt-5 text-sm text-slate-400">Bringing Silicon Valley engineering discipline and AI automation right here to Ludhiana.</p>
                </div>
            </section>

            <!-- Key Architectural Pillars -->
            <section class="mt-12 grid gap-5 md:grid-cols-3" data-stagger data-reveal>
                <article data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="h-10 w-10 mb-4 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--db-text)]">Staff Engineer Architecture</h3>
                        <p class="mt-2.5 text-sm text-slate-300 leading-relaxed">Engineered with domain-driven modular monoliths for zero technical debt and sub-100ms response times.</p>
                    </div>
                </article>
                <article data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="h-10 w-10 mb-4 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--db-text)]">Autonomous AI Integration</h3>
                        <p class="mt-2.5 text-sm text-slate-300 leading-relaxed">Voice agents, intelligent lead qualification, and automated workflow pipelines fitted seamlessly into your stack.</p>
                    </div>
                </article>
                <article data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="h-10 w-10 mb-4 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--db-text)]">Production-Grade Delivery</h3>
                        <p class="mt-2.5 text-sm text-slate-300 leading-relaxed">Transparent weekly sprint demos, full test coverage, zero-downtime deployment, and 30-day post-launch warranty.</p>
                    </div>
                </article>
            </section>

            <!-- Why DigitalBuilders Authority Section -->
            <section class="mt-16 sm:mt-20" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">Why DigitalBuilders</p>
                <h2 class="mt-3 text-2xl font-black text-[var(--db-text)] sm:text-3xl md:text-4xl">Strong Tech. Real Business Results.</h2>
                <p class="mt-4 max-w-4xl text-slate-300 leading-relaxed">
                    We do not build short-term templates. We bring a Staff Engineer mindset to your business, engineering robust systems built to withstand high traffic, complex data pipelines, and rapid scaling.
                </p>
            </section>

            <!-- Stats Bar Grid -->
            <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger data-reveal>
                <div data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 text-center sm:text-left">
                    <p class="text-3xl font-black db-gradient-text" data-counter="8" data-suffix="+">8+</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--db-text)]">Years IT Experience</p>
                    <p class="mt-1 text-xs text-slate-400">Enterprise software & cloud systems</p>
                </div>
                <div data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 text-center sm:text-left">
                    <p class="text-3xl font-black db-gradient-text" data-counter="25" data-suffix="+">25+</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--db-text)]">Projects Delivered</p>
                    <p class="mt-1 text-xs text-slate-400">Web, mobile, AI & ERP platforms</p>
                </div>
                <div data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 text-center sm:text-left">
                    <p class="text-3xl font-black db-gradient-text" data-counter="99.9" data-suffix="%">99.9%</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--db-text)]">Architecture Uptime</p>
                    <p class="mt-1 text-xs text-slate-400">Resilient infrastructure scaling</p>
                </div>
                <div data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 text-center sm:text-left">
                    <p class="text-3xl font-black db-gradient-text">100%</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--db-text)]">Sprint Delivery Rate</p>
                    <p class="mt-1 text-xs text-slate-400">Zero broken deadlines</p>
                </div>
            </section>

            <!-- Services Section -->
            <section id="services" class="mt-20 sm:mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">Our Services</p>
                <h2 class="mt-3 text-2xl font-black text-[var(--db-text)] sm:text-3xl md:text-4xl">Complete Digital Solutions for Growing Brands</h2>
                <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-stagger>
                    <article v-for="service in services" :key="service.title" data-stagger-item class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 flex flex-col justify-between">
                        <div>
                            <div class="h-11 w-11 mb-4 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                                <svg v-if="service.link.includes('web-applications')" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <svg v-else-if="service.link.includes('mobile-apps')" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <svg v-else-if="service.title.includes('Voice')" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                <svg v-else-if="service.title.includes('Workflows')" class="h-6 w-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <svg v-else-if="service.link.includes('erp-crm')" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                <svg v-else class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-[var(--db-text)]">{{ service.title }}</h3>
                            <p class="mt-2.5 text-sm leading-relaxed text-slate-300">{{ service.summary }}</p>
                        </div>
                        <a :href="service.link" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-[#9ba7ff] hover:text-indigo-600 dark:hover:text-[#c593ff] transition-colors">
                            Learn More
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </article>
                </div>
            </section>

            <!-- Case Studies & Delivered Work Section -->
            <section id="portfolio" class="mt-20 sm:mt-24" data-reveal>
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">Delivered Work & Case Studies</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl md:text-4xl">Production Applications Built for Scale</h2>
                        <p class="mt-3 max-w-2xl text-sm text-slate-600 dark:text-slate-300">
                            Explore live web platforms, cloud SaaS engines, and enterprise mobile apps engineered by DigitalBuilders with zero technical debt.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link href="/estimator" class="rounded-full border border-sky-500/30 bg-sky-500/10 px-4 py-2 text-xs font-bold text-sky-600 dark:text-sky-300 hover:bg-sky-500/20 transition-all inline-flex items-center gap-1.5">
                            <span>Cost Estimator</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </div>

                <!-- Interactive Category Filter Tabs -->
                <div class="mt-8 flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-[#b8c9e625] pb-4">
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all duration-200 flex items-center gap-2 cursor-pointer"
                        :class="[
                            selectedCategory === cat.id
                                ? 'bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_100%)] text-white dark:text-[#1a2231] shadow-md scale-105'
                                : 'bg-slate-100 dark:bg-[#1f2d3f] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#27374d] hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <span>{{ cat.label }}</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px]" :class="selectedCategory === cat.id ? 'bg-white/20 dark:bg-black/20' : 'bg-slate-200 dark:bg-[#16222f]'">
                            {{ cat.id === 'all' ? studies.length : studies.filter(s => s.category === cat.id).length }}
                        </span>
                    </button>
                </div>

                <!-- Projects Grid / Scroll Container -->
                <div class="case-scroll mt-8 flex gap-6 overflow-x-auto pb-6" data-stagger>
                    <article
                        v-for="study in filteredStudies"
                        :key="study.id"
                        data-stagger-item
                        class="case-card snap-start rounded-3xl border border-slate-200 dark:border-[#b8c9e640] bg-white dark:bg-[linear-gradient(165deg,#1c2838_0%,#121b27_100%)] p-6 sm:p-7 flex flex-col justify-between shadow-lg transition-all duration-300 hover:border-sky-500/50"
                    >
                        <div>
                            <!-- Header Bar with Live Indicator Badge -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span v-if="!study.isMobile" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                        Live Web App
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/15 border border-indigo-500/30 px-2.5 py-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse" />
                                        Mobile App (iOS/Android)
                                    </span>
                                    <span class="hidden sm:inline-block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        {{ study.categoryLabel }}
                                    </span>
                                </div>
                                <span class="rounded-full border border-slate-300 dark:border-[#b8c9e633] bg-slate-100 dark:bg-[#1f2d3f] px-3 py-1 text-xs font-bold text-slate-800 dark:text-[#d0ddff]">
                                    {{ study.client }}
                                </span>
                            </div>

                            <!-- Title & Metric Highlight -->
                            <h3 class="mt-4 text-xl font-extrabold text-slate-900 dark:text-white leading-snug">
                                {{ study.client }}
                            </h3>
                            <div class="mt-2 inline-flex items-center rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60 px-3 py-1 text-xs font-bold text-sky-700 dark:text-[#8fc3ff]">
                                ⚡ {{ study.metricBadge }}
                            </div>

                            <!-- Live Architecture Snapshot Image -->
                            <div class="relative mt-4 aspect-video w-full overflow-hidden rounded-2xl border border-slate-200/80 dark:border-white/10 bg-slate-900/60 group cursor-pointer" @click="openStudyModal(study)">
                                <img
                                    :src="study.image"
                                    :alt="study.client"
                                    class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-between p-3">
                                    <span class="text-[11px] font-bold text-white bg-black/60 backdrop-blur-md px-2.5 py-1 rounded-full border border-white/20">
                                        Live UI Snapshot
                                    </span>
                                    <span class="text-[11px] font-semibold text-sky-300 bg-sky-950/80 px-2 py-0.5 rounded-full">
                                        Click to Expand ↗
                                    </span>
                                </div>
                            </div>

                            <p class="mt-4 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                {{ study.tldr }}
                            </p>

                            <!-- Tech Stack Pills -->
                            <div class="mt-4 flex flex-wrap gap-1.5">
                                <span
                                    v-for="tech in study.techStack"
                                    :key="tech"
                                    class="rounded-md bg-slate-100 dark:bg-[#1a2638] border border-slate-200 dark:border-white/10 px-2.5 py-0.5 text-[11px] font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{ tech }}
                                </span>
                            </div>

                            <!-- Architecture & Impact Preview -->
                            <div class="mt-5 space-y-2 rounded-2xl border border-slate-100 dark:border-white/5 bg-slate-50/60 dark:bg-[#152130] p-4 text-xs text-slate-700 dark:text-slate-300">
                                <div class="flex items-start gap-2">
                                    <span class="font-bold text-slate-900 dark:text-sky-300 shrink-0">Solution:</span>
                                    <span>{{ study.architectureActions[0] }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-300 shrink-0">Impact:</span>
                                    <span>{{ study.businessImpact[0] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex flex-wrap items-center gap-2.5 pt-4 border-t border-slate-100 dark:border-white/10">
                            <!-- Direct Live Link or Demo Trigger -->
                            <a
                                v-if="study.liveUrl"
                                :href="study.liveUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_100%)] px-4 py-2 text-xs font-bold text-white dark:text-[#1a2231] shadow transition hover:scale-105"
                            >
                                <span>Visit Live App</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <a
                                v-else
                                href="#contact"
                                class="inline-flex items-center gap-1.5 rounded-full bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow transition hover:scale-105"
                            >
                                <span>Request APK / Demo</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </a>

                            <!-- Quick View Modal Trigger -->
                            <button
                                @click="openStudyModal(study)"
                                class="inline-flex items-center gap-1 rounded-full border border-slate-300 dark:border-white/20 bg-white dark:bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-100 dark:hover:bg-white/10"
                            >
                                <span>Quick View</span>
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>

                            <!-- Deep Page Case Study Link -->
                            <Link
                                :href="`/portfolio/${study.portfolioSlug}`"
                                class="inline-flex items-center gap-1 rounded-full px-3 py-2 text-xs font-bold text-sky-600 dark:text-[#9ba7ff] hover:text-indigo-600 dark:hover:text-[#c593ff] transition-colors ml-auto"
                            >
                                <span>Full Case Study</span>
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </Link>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Testimonials Section -->
            <section id="testimonials" class="mt-16 sm:mt-20" data-reveal>
                <TestimonialsCarousel />
            </section>

            <!-- Project Cost Estimator Section -->
            <section id="estimator" class="mt-16 sm:mt-20" data-reveal>
                <ProjectEstimator />
            </section>

            <!-- About Section & Process -->
            <section id="about" class="mt-20 grid gap-6 sm:mt-24 sm:gap-8 lg:grid-cols-[1.2fr_1fr]" data-reveal>
                <div class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dcb] p-6 sm:p-8">
                    <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">About Us</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Ashish Gupta</h2>
                    <p class="mt-1 text-sm font-semibold text-indigo-600 dark:text-[#d8c3ff]">Lead Digital Architect · Founder</p>
                    <p class="mt-4 text-slate-600 dark:text-slate-300 leading-relaxed">
                        Over 8 years in enterprise IT designing and deploying complex large-scale software systems. DigitalBuilders was founded to deliver production-grade architecture, not fragile templates.
                    </p>
                    <!-- Social Links -->
                    <div class="mt-6 flex items-center gap-3">
                        <a href="https://www.linkedin.com/in/ashishgupta1v/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 dark:border-[#b8c9e633] text-slate-500 dark:text-slate-400 hover:border-[#0a66c2] hover:text-[#0a66c2] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://github.com/ashishgupta1v" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 dark:border-[#b8c9e633] text-slate-500 dark:text-slate-400 hover:border-slate-900 dark:hover:border-white hover:text-slate-900 dark:hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                        <a href="https://wa.me/919087021592" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 dark:border-[#b8c9e633] text-slate-500 dark:text-slate-400 hover:border-[#25d366] hover:text-[#25d366] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="https://ashishgupta.dev" target="_blank" rel="noopener noreferrer" aria-label="Personal Website" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 dark:border-[#b8c9e633] text-slate-500 dark:text-slate-400 hover:border-sky-500 hover:text-sky-500 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </a>
                    </div>
                </div>
                <div class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-6 sm:p-8">
                    <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">How We Work</p>
                    <ol class="mt-4 space-y-4 text-sm text-slate-700 dark:text-slate-200">
                        <li><span class="font-bold text-slate-900 dark:text-white">01 Understand Your Needs</span> — We discuss goals, challenges, and priorities.</li>
                        <li><span class="font-bold text-slate-900 dark:text-white">02 Plan the Right Solution</span> — We design the system before development starts.</li>
                        <li><span class="font-bold text-slate-900 dark:text-white">03 Build and Deliver</span> — We deliver in clear phases with regular updates.</li>
                        <li><span class="font-bold text-slate-900 dark:text-white">04 Support & Scale</span> — Post-launch monitoring, 30-day warranty, and ongoing support.</li>
                    </ol>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="mt-20 sm:mt-24" data-reveal>
                <p class="text-sm uppercase tracking-[0.2em] text-sky-600 dark:text-[#9dc5ff] font-semibold">Common Questions</p>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl md:text-4xl">Frequently Asked Questions</h2>
                <div class="mt-8 space-y-3" data-stagger>
                    <details v-for="faq in faqs" :key="faq.q" data-stagger-item class="group rounded-2xl border border-slate-200 dark:border-[#b8c9e633] bg-white dark:bg-[#1a2534] overflow-hidden shadow-sm">
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

            <!-- Ready to Architect Callout Banner -->
            <section class="mt-20 rounded-2xl border border-sky-200 dark:border-[#b8c9e655] bg-[linear-gradient(120deg,rgba(2,132,199,0.08),rgba(124,58,237,0.08))] dark:bg-[linear-gradient(120deg,rgba(122,196,255,0.16),rgba(197,147,255,0.16))] p-6 text-center sm:mt-24 sm:p-10" data-reveal>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl md:text-4xl">Ready to Architect Your Solution?</h2>
                <p class="mx-auto mt-3 max-w-3xl text-sm sm:text-base text-slate-600 dark:text-slate-200">Stop settling. Start building. Engineer the digital systems your business needs.</p>
                <a href="#contact" class="db-action mt-7 inline-flex w-full items-center justify-center rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-7 py-3.5 text-sm font-bold text-white dark:text-[#1a2231] shadow-lg transition hover:scale-[1.02] sm:w-auto">
                    Schedule Your Strategy Session
                </a>
            </section>

            <!-- Contact Section -->
            <section id="contact" class="mt-20 grid gap-6 sm:mt-24 sm:gap-8 lg:grid-cols-[1.15fr_0.85fr]" data-reveal>
                <div class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-6 sm:p-8">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Let's Connect</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Your business requires software that works as hard as you do. Let's map your bottlenecks into a robust digital solution.</p>

                    <form class="mt-8 space-y-5" @submit.prevent="submitLead">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Full Name</label>
                            <input v-model="form.name" type="text" placeholder="First and Last Name" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e640] bg-white dark:bg-[#192434] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-sky-500 focus:outline-none" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500 dark:text-red-300">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email Address</label>
                            <input v-model="form.email" type="email" placeholder="you@company.com" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e640] bg-white dark:bg-[#192434] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-sky-500 focus:outline-none" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500 dark:text-red-300">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Phone Number</label>
                            <input v-model="form.phone" type="text" placeholder="+91 XXXXX XXXXX" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e640] bg-white dark:bg-[#192434] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-sky-500 focus:outline-none" />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500 dark:text-red-300">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Project Type</label>
                            <select v-model="form.project_type" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e640] bg-white dark:bg-[#192434] px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-sky-500 focus:outline-none">
                                <option disabled value="">Select a project type</option>
                                <option v-for="type in projectTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                            <p v-if="form.errors.project_type" class="mt-1 text-xs text-red-500 dark:text-red-300">{{ form.errors.project_type }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Project Description (Optional)</label>
                            <textarea v-model="form.description" rows="4" placeholder="Briefly describe your core operational challenge or project vision..." class="mt-2 w-full rounded-xl border border-slate-300 dark:border-[#b8c9e640] bg-white dark:bg-[#192434] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-sky-500 focus:outline-none"></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500 dark:text-red-300">{{ form.errors.description }}</p>
                        </div>

                        <button :disabled="form.processing" class="db-action w-full rounded-full border border-transparent bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3.5 text-sm font-bold text-white dark:text-[#1a2231] transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-70">
                            {{ form.processing ? 'Submitting...' : 'Request a Project Quote' }}
                        </button>

                        <p v-if="form.recentlySuccessful" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-200">
                            Thank you! Your inquiry has been received. We'll respond within 24 business hours.
                        </p>

                        <p class="text-xs text-slate-500 dark:text-slate-400">Your data is secure. We review all inquiries and respond within 24 business hours.</p>
                    </form>
                </div>

                <aside class="space-y-4">
                    <div class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-6 sm:p-8">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Get In Touch</h3>
                        <a href="tel:+919087021592" class="mt-4 flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-colors">
                            <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-[#9dc5ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +91 90870 21592
                        </a>
                        <a href="mailto:hello@digitalbuilders.in" class="mt-3 flex items-center gap-2.5 break-all text-sm text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-colors">
                            <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-[#9dc5ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            hello@digitalbuilders.in
                        </a>
                        <p class="mt-3 flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300">
                            <svg class="h-4 w-4 shrink-0 text-sky-600 dark:text-[#9dc5ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Ludhiana, Punjab, India
                        </p>
                        <!-- WhatsApp Quick Contact -->
                        <a href="https://wa.me/919087021592?text=Hi%20DigitalBuilders!%20I'm%20interested%20in%20discussing%20a%20project." target="_blank" rel="noopener noreferrer" class="mt-5 flex items-center justify-center gap-2 rounded-full bg-[#25d366] px-5 py-3 text-sm font-bold text-white shadow-[0_4px_20px_rgba(37,211,102,0.25)] hover:bg-[#20ba5a] transition-all hover:scale-[1.02]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Chat on WhatsApp
                        </a>
                    </div>
                    <div class="db-antigravity-card rounded-2xl border border-[#b8c9e633] bg-[#27374dde] p-6 sm:p-8">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Quick Access</h3>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Explore more projects and technical work by our founder Ashish Gupta.</p>
                        <a href="https://ashishgupta.dev" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-sky-600 dark:text-[#c8d6ff] hover:text-sky-700 dark:hover:text-white transition-colors">
                            View Founder Portfolio (ashishgupta.dev)
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </aside>
            </section>
        </main>

        <!-- Quick-View Case Study Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="activeModalStudy"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10 bg-black/80 backdrop-blur-md overflow-y-auto"
                    @click.self="closeStudyModal"
                >
                    <div class="relative w-full max-w-3xl rounded-3xl border border-slate-200 dark:border-[#b8c9e640] bg-white dark:bg-[#182332] p-6 sm:p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
                        <!-- Close button -->
                        <button
                            @click="closeStudyModal"
                            class="absolute top-5 right-5 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/20 transition-colors"
                            aria-label="Close modal"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <!-- Header -->
                        <div class="pr-10">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-sky-500/15 border border-sky-500/30 px-3 py-1 text-xs font-bold text-sky-600 dark:text-sky-300">
                                    {{ activeModalStudy.categoryLabel }}
                                </span>
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    ⚡ {{ activeModalStudy.metricBadge }}
                                </span>
                            </div>
                            <h2 class="mt-3 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                {{ activeModalStudy.client }}
                            </h2>
                            <p class="mt-2 text-sm sm:text-base text-slate-600 dark:text-slate-300">
                                {{ activeModalStudy.tldr }}
                            </p>
                        </div>

                        <!-- Live Snapshot Image Preview -->
                        <div class="mt-5 relative aspect-video w-full overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10 shadow-lg bg-slate-950">
                            <img
                                :src="activeModalStudy.image"
                                :alt="activeModalStudy.client"
                                class="h-full w-full object-cover object-top"
                            />
                            <div class="absolute bottom-3 left-3 bg-black/75 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold text-white border border-white/20">
                                Production Interface Snapshot
                            </div>
                        </div>

                        <!-- Tech Stack Tags -->
                        <div class="mt-5 flex flex-wrap gap-2">
                            <span
                                v-for="tech in activeModalStudy.techStack"
                                :key="tech"
                                class="rounded-lg bg-slate-100 dark:bg-[#1f2d3f] border border-slate-200 dark:border-white/10 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ tech }}
                            </span>
                        </div>

                        <!-- Problem & Challenge -->
                        <div class="mt-6 space-y-3 rounded-2xl bg-slate-50 dark:bg-[#141e2b] p-4 text-sm text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-white/5">
                            <div>
                                <strong class="text-slate-900 dark:text-white font-bold">The Problem: </strong>
                                {{ activeModalStudy.problem }}
                            </div>
                            <div>
                                <strong class="text-slate-900 dark:text-white font-bold">The Challenge: </strong>
                                {{ activeModalStudy.challenge }}
                            </div>
                        </div>

                        <!-- Architecture Actions -->
                        <div class="mt-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">Our Architectural Solution</h4>
                            <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                                <li
                                    v-for="(action, idx) in activeModalStudy.architectureActions"
                                    :key="idx"
                                    class="flex items-start gap-2.5"
                                >
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-500" />
                                    <span>{{ action }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Business Impact -->
                        <div class="mt-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Measurable Business Impact</h4>
                            <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                                <li
                                    v-for="(impact, idx) in activeModalStudy.businessImpact"
                                    :key="idx"
                                    class="flex items-start gap-2.5"
                                >
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500" />
                                    <span>{{ impact }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Client Quote -->
                        <blockquote class="mt-6 rounded-2xl border border-purple-200 dark:border-purple-900/50 bg-purple-50 dark:bg-[#201830] p-4 text-sm text-purple-900 dark:text-purple-200 italic">
                            "{{ activeModalStudy.quote }}"
                            <footer class="mt-2 text-xs font-bold text-purple-700 dark:text-purple-300 not-italic">
                                — {{ activeModalStudy.quoteAuthor }}
                            </footer>
                        </blockquote>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-200 dark:border-white/10">
                            <div class="flex flex-wrap items-center gap-2">
                                <a
                                    v-if="activeModalStudy.liveUrl"
                                    :href="activeModalStudy.liveUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_100%)] dark:bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_100%)] px-5 py-2.5 text-xs font-bold text-white dark:text-[#1a2231] shadow transition hover:scale-105"
                                >
                                    <span>Visit Live Application</span>
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <Link
                                    :href="`/portfolio/${activeModalStudy.portfolioSlug}`"
                                    @click="closeStudyModal"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 dark:border-white/20 bg-slate-100 dark:bg-white/5 px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-white transition hover:bg-slate-200 dark:hover:bg-white/10"
                                >
                                    <span>Open Dedicated Case Study Page</span>
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                            </div>
                            <button
                                @click="closeStudyModal"
                                class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors"
                            >
                                Close Preview
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Floating AI Assistant Widget -->
        <AiAssistantWidget />

        <!-- Floating WhatsApp CTA -->
        <a
            href="https://wa.me/919087021592?text=Hi%20DigitalBuilders!%20I%27m%20interested%20in%20discussing%20a%20project."
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Chat on WhatsApp"
            class="whatsapp-fab"
        >
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <span>WhatsApp</span>
        </a>

        <!-- Back to Top Button -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition-all duration-200" leave-to-class="opacity-0 translate-y-4">
            <button
                v-if="showBackToTop"
                @click="scrollToTop"
                aria-label="Back to top"
                class="fixed bottom-24 right-5 z-40 flex h-10 w-10 items-center justify-center rounded-full border border-[#b8c9e633] bg-[#27374dde] text-[#9dc5ff] shadow-lg backdrop-blur-md transition hover:border-[#9ba7ff] hover:text-white"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            </button>
        </Transition>

        <footer class="border-t border-[#b8c9e633] bg-[#111827e6] backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-5 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Brand -->
                    <div>
                        <a href="#top" class="flex items-center gap-2.5">
                            <img src="/images/db-logo.png" alt="DigitalBuilders Logo" class="h-8 w-8 object-contain" onerror="this.style.display='none'" />
                            <span class="db-brand-logo-text text-lg font-black tracking-tight">Digital Builders</span>
                        </a>
                        <p class="mt-2 max-w-xs text-xs text-slate-500">Enterprise Software & AI Architecture. Based in Ludhiana, Punjab, India.</p>
                    </div>
                    <!-- Social Links -->
                    <div class="flex items-center gap-3">
                        <a href="https://www.linkedin.com/in/ashishgupta1v/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="flex h-9 w-9 items-center justify-center rounded-full border border-[#b8c9e622] text-slate-500 hover:border-[#0a66c2] hover:text-[#0a66c2] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://github.com/ashishgupta1v" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="flex h-9 w-9 items-center justify-center rounded-full border border-[#b8c9e622] text-slate-500 hover:border-white hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                        <a href="https://wa.me/919087021592" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-full border border-[#b8c9e622] text-slate-500 hover:border-[#25d366] hover:text-[#25d366] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>
                </div>
                <div class="mt-8 border-t border-[#b8c9e615] pt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500">
                    <p>© {{ new Date().getFullYear() }} Balaji Enterprises. All rights reserved.</p>
                    <div class="flex flex-wrap gap-4">
                        <Link href="/library/privacy-policy" class="hover:text-slate-300 transition-colors">Privacy Policy</Link>
                        <Link href="/library/terms-of-service" class="hover:text-slate-300 transition-colors">Terms of Service</Link>
                        <Link href="/blog" class="hover:text-slate-300 transition-colors">Blog</Link>
                        <a href="mailto:hello@digitalbuilders.in" class="hover:text-slate-300 transition-colors">hello@digitalbuilders.in</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.site-bg {
    background: var(--db-body-bg);
    color: var(--db-text);
    transition: background-color 0.35s ease, color 0.35s ease;
    font-family: 'Outfit', sans-serif;
    font-weight: 300;
}

html {
    scroll-behavior: smooth;
}

/* Brand name gradient in header */
.db-brand-logo-text {
    background: linear-gradient(100deg, #7ac4ff 0%, #a78bfa 50%, #c084fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
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

/* WhatsApp Floating Action Button (Positioned Bottom-Left to avoid collision with AI Assistant) */
.whatsapp-fab {
    position: fixed;
    bottom: 1.5rem;
    left: 1.25rem;
    z-index: 50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #25d366;
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.7rem 1.1rem 0.7rem 0.9rem;
    border-radius: 9999px;
    box-shadow: 0 4px 24px rgba(37, 211, 102, 0.4);
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    animation: wa-bounce 2s ease-in-out 3s 2;
}

.whatsapp-fab:hover {
    background-color: #20ba5a;
    transform: scale(1.05);
    box-shadow: 0 6px 32px rgba(37, 211, 102, 0.55);
}

@keyframes wa-bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

/* Card hover lift effect */
.db-antigravity-card {
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}
.db-antigravity-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(122, 196, 255, 0.12);
    border-color: rgba(184, 201, 230, 0.5);
}

/* FAQ summary marker hidden */
details summary::-webkit-details-marker { display: none; }
</style>
