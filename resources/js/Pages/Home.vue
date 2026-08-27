<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { animate, inView, stagger } from 'motion';
import { onMounted, onUnmounted, ref } from 'vue';

import HomeHero from '@/Components/Home/HomeHero.vue';
import ServicesSection from '@/Components/Home/ServicesSection.vue';
import PortfolioSection from '@/Components/Home/PortfolioSection.vue';
import ArchitectureComparison from '@/Components/Home/ArchitectureComparison.vue';
import TechEcosystem from '@/Components/Home/TechEcosystem.vue';
import FaqAccordion from '@/Components/Home/FaqAccordion.vue';
import ContactSection from '@/Components/Home/ContactSection.vue';
import TestimonialsCarousel from '@/Components/TestimonialsCarousel.vue';
import ProjectEstimator from '@/Components/ProjectEstimator.vue';
import AiAssistantWidget from '@/Components/AiAssistantWidget.vue';
import CookieConsent from '@/Components/CookieConsent.vue';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { trackWhatsAppClick } from '@/utils/analytics';

type MotionAnimate = (
    target: Element | NodeListOf<Element>,
    keyframes: Record<string, unknown>,
    options?: Record<string, unknown>,
) => void;

const motionAnimate = animate as unknown as MotionAnimate;



const mobileMenuOpen = ref(false);
const showBackToTop = ref(false);
const isDarkMode = ref(false);

function toggleTheme() {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
        localStorage.setItem('db-theme', 'dark');
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

function openCookieSettings() {
    window.dispatchEvent(new CustomEvent('db:open-cookie-settings'));
}

const services = [
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

const canonicalUrl = 'https://www.digitalbuilders.in/';

onMounted(() => {
    // Restore saved theme (default Light)
    const savedTheme = localStorage.getItem('db-theme');
    if (savedTheme === 'dark') {
        isDarkMode.value = true;
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
    } else {
        isDarkMode.value = false;
        document.documentElement.setAttribute('data-theme', 'light');
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    }

    // Scroll listeners
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('scroll', handleScrollProgress, { passive: true });

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
        // Hero title animation - subtle transform only, keeping opacity 1 for instant LCP paint!
        const hero = document.querySelector('[data-hero-title]');
        if (hero) {
            motionAnimate(
                hero,
                { transform: ['translateY(12px)', 'translateY(0px)'] },
                { duration: 0.35, ease: 'ease-out' },
            );
        }

        // Reveal section animations
        inView('[data-reveal]', (element) => {
            motionAnimate(
                element,
                { opacity: [0.3, 1], transform: ['translateY(18px)', 'translateY(0px)'] },
                { duration: 0.45, ease: 'ease-out' },
            );
        });

        // Staggered item animations
        inView('[data-stagger]', (element) => {
            const items = element.querySelectorAll('[data-stagger-item]');
            if (items.length > 0) {
                motionAnimate(
                    items,
                    { opacity: [0.3, 1], transform: ['translateY(12px)', 'translateY(0px)'] },
                    { duration: 0.35, delay: stagger(0.05), ease: 'ease-out' },
                );
            }
        });
    }

    // Animated counter values
    const animatedCounters = new Set<Element>();
    inView('[data-counter]', (el: Element) => {
        if (animatedCounters.has(el)) return;
        animatedCounters.add(el);

        const htmlEl = el as HTMLElement;
        const rawTarget = htmlEl.dataset.counter ?? '0';
        const isDecimal = rawTarget.includes('.');
        const target = parseFloat(rawTarget);
        const suffix = htmlEl.dataset.suffix ?? '';

        if (prefersReducedMotion) {
            htmlEl.textContent = isDecimal ? target.toFixed(1) + suffix : Math.round(target) + suffix;
            return;
        }

        const duration = 1200;
        const startTime = performance.now();

        function updateCounter(currentTime: number) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out quad
            const eased = 1 - (1 - progress) * (1 - progress);
            const current = target * eased;

            htmlEl.textContent = isDecimal ? current.toFixed(1) + suffix : Math.round(current) + suffix;

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                htmlEl.textContent = isDecimal ? target.toFixed(1) + suffix : Math.round(target) + suffix;
            }
        }

        requestAnimationFrame(updateCounter);
    });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('scroll', handleScrollProgress);
});
</script>

<template>
    <Head title="DigitalBuilders — Enterprise Web, Mobile & AI Architecture" />

    <div class="relative min-h-screen bg-background text-foreground transition-colors duration-300">
        <!-- Scroll Progress Bar -->
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Navigation Header -->
        <header class="sticky top-0 z-50 border-b border-border bg-[var(--db-nav-bg)] backdrop-blur-xl transition-colors duration-300">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-3.5 lg:px-8">
                <!-- Logo: icon + brand name -->
                <ApplicationLogo :is-link="true" href="#top" />

                <nav class="hidden items-center gap-1.5 text-sm font-medium lg:flex xl:gap-2">
                    <a href="#services" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground">Services</a>
                    <a href="#portfolio" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground">Portfolio</a>
                    <Link href="/pricing" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground">Pricing</Link>
                    <a href="/downloads/digitalbuilders-pricing-india-inr.html" target="_blank" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground inline-flex items-center gap-1.5">
                        <span>Brochure</span>
                        <span class="rounded bg-sky-500/10 text-sky-700 dark:text-sky-400 text-[10px] font-bold px-1.5 py-0.5">PDF</span>
                    </a>
                    <Link href="/blog" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground">Blog</Link>
                    <a href="#about" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground">About</a>
                    <a href="#contact" class="px-3 py-1.5 text-muted-foreground transition-all duration-200 hover:text-foreground">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    <!-- Theme Toggle -->
                    <button
                        @click="toggleTheme"
                        class="hidden h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:text-foreground sm:inline-flex cursor-pointer"
                        :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                    >
                        <svg v-if="isDarkMode" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    </button>
                    <!-- Mobile hamburger -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:text-foreground lg:hidden focus:outline-none cursor-pointer"
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
                <div v-if="mobileMenuOpen" class="border-t border-border bg-card px-4 pb-5 pt-4 sm:px-5 lg:hidden">
                    <nav class="flex flex-col gap-3 text-sm font-medium">
                        <a href="#services" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground">Services</a>
                        <a href="#portfolio" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground">Portfolio</a>
                        <Link href="/pricing" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground">Pricing</Link>
                        <a href="/downloads/digitalbuilders-pricing-india-inr.html" target="_blank" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground flex items-center justify-between">
                            <span>Brochure (PDF)</span>
                            <span class="rounded bg-sky-500/10 text-sky-700 dark:text-sky-400 text-[10px] font-bold px-2 py-0.5">Download</span>
                        </a>
                        <Link href="/blog" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground">Blog</Link>
                        <a href="#about" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground">About</a>
                        <a href="#contact" @click="mobileMenuOpen = false" class="text-muted-foreground transition hover:text-foreground">Contact</a>
                    </nav>
                </div>
            </Transition>
        </header>

        <main id="top" class="mx-auto max-w-7xl px-4 pb-12 sm:px-5 sm:pb-16 lg:px-8">
            <!-- 1. Hero & Core Pillars -->
            <HomeHero :is-dark-mode="isDarkMode" />

            <!-- 2. Services Section -->
            <ServicesSection :services="services" />

            <!-- 3. Portfolio & Case Studies Showcase -->
            <PortfolioSection />

            <!-- 4. Architecture Standards & Comparison -->
            <ArchitectureComparison />

            <!-- 5. Battle-Tested Technology Matrix -->
            <TechEcosystem />

            <!-- 6. Client Testimonials -->
            <section id="testimonials" class="mt-20 sm:mt-24" data-reveal>
                <TestimonialsCarousel />
            </section>

            <!-- 7. Interactive Project Cost Estimator -->
            <section id="estimator" class="mt-20 sm:mt-24" data-reveal>
                <ProjectEstimator />
            </section>

            <!-- 8. About & Process Section -->
            <section id="about" class="mt-20 grid gap-6 sm:mt-24 sm:gap-8 lg:grid-cols-[1.2fr_1fr]" data-reveal>
                <div class="db-antigravity-card rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 shadow-xl">
                    <p class="text-sm uppercase tracking-[0.2em] text-sky-700 dark:text-sky-400 font-semibold">About Us</p>
                    <h2 class="mt-2 text-2xl font-black text-foreground sm:text-3xl">Ashish Gupta</h2>
                    <p class="mt-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400">Lead Digital Architect · Founder</p>
                    <p class="mt-4 text-muted-foreground leading-relaxed">
                        Over 10+ years in enterprise IT designing and deploying complex large-scale software systems. DigitalBuilders was founded to deliver production-grade architecture, not fragile templates.
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <a href="https://www.linkedin.com/in/ashishgupta1v/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-[#0a66c2] hover:text-[#0a66c2] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://github.com/ashishgupta1v" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-foreground hover:text-foreground transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                        <a href="https://wa.me/919087021592" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-[#25d366] hover:text-[#25d366] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="https://ashishgupta.dev" target="_blank" rel="noopener noreferrer" aria-label="Personal Website" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-sky-500 hover:text-sky-500 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </a>
                    </div>
                </div>
                <div class="db-antigravity-card rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 shadow-xl">
                    <p class="text-sm uppercase tracking-[0.2em] text-sky-700 dark:text-sky-400 font-semibold">How We Work</p>
                    <ol class="mt-4 space-y-4 text-sm text-card-foreground">
                        <li><span class="font-bold text-foreground">01 Understand Your Needs</span> — We discuss goals, challenges, and priorities.</li>
                        <li><span class="font-bold text-foreground">02 Plan the Right Solution</span> — We design the system before development starts.</li>
                        <li><span class="font-bold text-foreground">03 Build and Deliver</span> — We deliver in clear phases with regular updates.</li>
                        <li><span class="font-bold text-foreground">04 Support & Scale</span> — Post-launch monitoring, 30-day warranty, and ongoing support.</li>
                    </ol>
                </div>
            </section>

            <!-- 9. FAQ Section -->
            <FaqAccordion />

            <!-- 10. Contact & Discovery Form -->
            <ContactSection />
        </main>

        <!-- Floating AI Assistant Widget -->
        <AiAssistantWidget />

        <!-- Floating WhatsApp CTA (Desktop / Tablet) -->
        <a
            href="https://wa.me/919087021592?text=Hi%20DigitalBuilders!%20I%27m%20interested%20in%20discussing%20a%20project."
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Chat on WhatsApp"
            @click="trackWhatsAppClick('floating_fab')"
            class="whatsapp-fab"
        >
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <span>WhatsApp</span>
        </a>

        <!-- Back to Top Button (Stacked above AI widget) -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition-all duration-200" leave-to-class="opacity-0 translate-y-4">
            <button
                v-if="showBackToTop"
                @click="scrollToTop"
                aria-label="Back to top"
                class="fixed bottom-36 md:bottom-24 right-5 sm:right-7 z-40 flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card text-foreground shadow-lg backdrop-blur-md transition hover:border-sky-500 hover:text-sky-500 cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            </button>
        </Transition>

        <!-- Footer -->
        <footer class="border-t border-border bg-secondary/50 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-5 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <ApplicationLogo :is-link="true" href="#top" size="sm" />
                        <p class="mt-2 max-w-xs text-xs text-muted-foreground">Enterprise Software & AI Architecture. Based in Ludhiana, Punjab, India.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="https://linkedin.com/in/ashishgupta1v" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-[#0077b5] hover:text-[#0077b5] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <a href="https://github.com/ashishgupta1v" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-foreground hover:text-foreground transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                        <a href="https://wa.me/919087021592" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" @click="trackWhatsAppClick('footer_social')" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-[#25d366] hover:text-[#25d366] transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="https://ashishgupta.dev" target="_blank" rel="noopener noreferrer" aria-label="Personal Website" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground hover:border-sky-500 hover:text-sky-500 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </a>
                    </div>
                </div>
                <div class="mt-8 border-t border-border pt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-muted-foreground">
                    <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
                    <div class="flex flex-wrap gap-4">
                        <Link href="/pricing" class="hover:text-foreground transition-colors">Pricing</Link>
                        <a href="/downloads/digitalbuilders-pricing-india-inr.html" target="_blank" class="hover:text-foreground transition-colors">Price Book (PDF)</a>
                        <Link href="/estimator" class="hover:text-foreground transition-colors">Estimator</Link>
                        <Link href="/blog" class="hover:text-foreground transition-colors">Blog</Link>
                        <Link href="/library/privacy-policy" class="hover:text-foreground transition-colors">Privacy Policy</Link>
                        <Link href="/library/terms-of-service" class="hover:text-foreground transition-colors">Terms of Service</Link>
                        <button type="button" @click="openCookieSettings" class="hover:text-foreground transition-colors cursor-pointer text-left">Cookie Settings</button>
                        <a href="mailto:hello@digitalbuilders.in" class="hover:text-foreground transition-colors">hello@digitalbuilders.in</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- High-Conversion Sticky Mobile CTA Bar -->
        <StickyMobileCta />

        <!-- Privacy-First Cookie Consent Banner -->
        <CookieConsent />
    </div>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}

.whatsapp-fab {
    position: fixed;
    bottom: 1.5rem;
    left: 1.5rem;
    z-index: 50;
    display: none;
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

@media (min-width: 768px) {
    .whatsapp-fab {
        display: flex;
    }
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

.db-antigravity-card {
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}
.db-antigravity-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(122, 196, 255, 0.12);
    border-color: rgba(184, 201, 230, 0.5);
}
</style>
