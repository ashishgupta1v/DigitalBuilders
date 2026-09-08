<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = withDefaults(
    defineProps<{
        isHome?: boolean;
        activeRoute?: string;
    }>(),
    {
        isHome: false,
        activeRoute: '',
    }
);

const emit = defineEmits<{
    (e: 'open-booking'): void;
    (e: 'open-brochure'): void;
}>();

const mobileMenuOpen = ref(false);
const servicesDropdownOpen = ref(false);

const servicesList = [
    { title: 'Custom Web Apps', href: '/services/web-applications', desc: 'Modern Laravel 13 + Vue 3 architecture' },
    { title: 'Mobile Apps (iOS & Android)', href: '/services/mobile-apps', desc: 'High-performance native mobile apps' },
    { title: 'AI Solutions & Agents', href: '/services/ai-solutions', desc: 'Autonomous conversational agents & RAG' },
    { title: 'Enterprise ERP & CRM', href: '/services/erp-crm', desc: 'High-density operational cockpits' },
    { title: 'SaaS Platforms', href: '/services/saas-platforms', desc: 'Multi-tenant subscription systems' },
    { title: 'Growth Engineering', href: '/services/growth', desc: 'Conversion rate & programmatic scale' },
];

function handleBookClick() {
    mobileMenuOpen.value = false;
    emit('open-booking');
}

function handleBrochureClick() {
    mobileMenuOpen.value = false;
    emit('open-brochure');
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-border bg-[var(--db-nav-bg)] backdrop-blur-xl transition-colors duration-300">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-3.5 lg:px-8">
            <!-- Brand Logo -->
            <div class="flex items-center gap-6">
                <ApplicationLogo :is-link="true" :href="isHome ? '#top' : '/'" size="md" />
            </div>

            <!-- Desktop Navigation Links -->
            <nav aria-label="Primary navigation" class="hidden items-center gap-1 text-sm font-medium lg:flex xl:gap-2">
                <!-- Services Menu (Dropdown on Hover / Focus) -->
                <div
                    class="relative"
                    @mouseenter="servicesDropdownOpen = true"
                    @mouseleave="servicesDropdownOpen = false"
                >
                    <component
                        :is="isHome ? 'a' : Link"
                        :href="isHome ? '#services' : '/services/web-applications'"
                        class="px-3 py-2 min-h-[44px] inline-flex items-center gap-1 rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                        :class="{ 'text-foreground font-semibold': activeRoute.startsWith('/services') }"
                    >
                        <span>Services</span>
                        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': servicesDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </component>

                    <!-- Dropdown Panel -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 translate-y-1 scale-95"
                        leave-active-class="transition duration-150 ease-in"
                        leave-to-class="opacity-0 translate-y-1 scale-95"
                    >
                        <div
                            v-if="servicesDropdownOpen"
                            class="absolute left-0 top-full mt-1 w-80 rounded-2xl border border-border bg-card/95 p-3 shadow-xl backdrop-blur-xl z-50"
                        >
                            <div class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground px-3 py-1.5">
                                Engineering Capabilities
                            </div>
                            <div class="space-y-1 mt-1">
                                <Link
                                    v-for="svc in servicesList"
                                    :key="svc.href"
                                    :href="svc.href"
                                    class="group flex flex-col rounded-xl px-3 py-2 text-xs transition-colors hover:bg-accent/80"
                                >
                                    <span class="font-semibold text-foreground group-hover:text-sky-600 dark:group-hover:text-sky-400">
                                        {{ svc.title }}
                                    </span>
                                    <span class="text-[11px] text-muted-foreground line-clamp-1">
                                        {{ svc.desc }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Process / How It Works -->
                <a
                    v-if="isHome"
                    href="#process"
                    class="px-3 py-2 min-h-[44px] inline-flex items-center rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                >
                    How It Works
                </a>

                <!-- Portfolio / Case Studies -->
                <component
                    :is="isHome ? 'a' : Link"
                    :href="isHome ? '#portfolio' : '/#portfolio'"
                    class="px-3 py-2 min-h-[44px] inline-flex items-center rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                    :class="{ 'text-foreground font-semibold': activeRoute.startsWith('/portfolio') }"
                >
                    Portfolio
                </component>

                <!-- Pricing -->
                <Link
                    href="/pricing"
                    class="px-3 py-2 min-h-[44px] inline-flex items-center rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                    :class="{ 'text-sky-600 dark:text-sky-400 font-bold': activeRoute === '/pricing' }"
                >
                    Pricing
                </Link>

                <!-- Estimator -->
                <Link
                    href="/estimator"
                    class="px-3 py-2 min-h-[44px] inline-flex items-center rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                    :class="{ 'text-sky-600 dark:text-sky-400 font-bold': activeRoute === '/estimator' }"
                >
                    Estimator
                </Link>

                <!-- Price Book CTA -->
                <button
                    type="button"
                    @click="isHome ? emit('open-brochure') : null"
                    class="px-3 py-2 min-h-[44px] text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50 inline-flex items-center gap-1.5 cursor-pointer rounded-lg"
                >
                    <component
                        :is="isHome ? 'span' : Link"
                        :href="isHome ? undefined : '/pricing'"
                        class="inline-flex items-center gap-1.5"
                    >
                        <span>Price Book</span>
                        <span class="rounded bg-sky-500/10 text-sky-700 dark:text-sky-400 text-[10px] font-bold px-1.5 py-0.5">PDF</span>
                    </component>
                </button>

                <!-- Blog -->
                <Link
                    href="/blog"
                    class="px-3 py-2 min-h-[44px] inline-flex items-center rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                    :class="{ 'text-foreground font-semibold': activeRoute.startsWith('/blog') }"
                >
                    Blog
                </Link>

                <!-- Contact -->
                <component
                    :is="isHome ? 'a' : Link"
                    :href="isHome ? '#contact' : '/#contact'"
                    class="px-3 py-2 min-h-[44px] inline-flex items-center rounded-lg text-muted-foreground transition-colors duration-200 hover:text-foreground hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
                >
                    Contact
                </component>
            </nav>

            <!-- Right Controls: CTA Button, Universal Theme Toggle, Hamburger Button -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Schedule Call Button -->
                <component
                    :is="isHome ? 'button' : Link"
                    :type="isHome ? 'button' : undefined"
                    :href="isHome ? undefined : '/book'"
                    @click="isHome ? emit('open-booking') : undefined"
                    class="btn-primary hidden sm:inline-flex items-center gap-1.5 rounded-full px-4 py-2 min-h-[44px] text-xs font-bold text-white shadow-md transition hover:scale-105 cursor-pointer"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Schedule Call</span>
                </component>

                <!-- Universal Theme Toggle (Sun/Moon) -->
                <ThemeToggle size="md" />

                <!-- Mobile Hamburger Button (>=44px touch target) -->
                <button
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="flex h-11 w-11 min-h-[44px] min-w-[44px] items-center justify-center rounded-full border border-border text-muted-foreground transition hover:text-foreground lg:hidden focus:outline-none cursor-pointer"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle navigation menu"
                >
                    <svg v-if="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Slide-Down Drawer -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-3"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="opacity-0 -translate-y-3"
        >
            <div
                v-if="mobileMenuOpen"
                class="border-t border-border bg-card/95 backdrop-blur-2xl px-4 pb-6 pt-4 sm:px-6 lg:hidden max-h-[calc(100vh-64px)] overflow-y-auto shadow-2xl"
            >
                <nav aria-label="Mobile navigation" class="flex flex-col gap-1 text-sm font-medium">
                    <!-- Home Link -->
                    <Link
                        href="/"
                        @click="mobileMenuOpen = false"
                        class="min-h-[44px] flex items-center px-3 rounded-lg text-foreground hover:bg-accent/80 transition"
                    >
                        Home
                    </Link>

                    <!-- Services Expandable -->
                    <div class="py-1">
                        <div class="px-3 text-xs font-bold uppercase tracking-wider text-muted-foreground py-1.5">
                            Engineering Services
                        </div>
                        <div class="grid grid-cols-1 gap-0.5 pl-2">
                            <Link
                                v-for="svc in servicesList"
                                :key="svc.href"
                                :href="svc.href"
                                @click="mobileMenuOpen = false"
                                class="min-h-[44px] flex items-center px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-accent/60 transition text-xs font-medium"
                            >
                                • {{ svc.title }}
                            </Link>
                        </div>
                    </div>

                    <!-- Portfolio -->
                    <component
                        :is="isHome ? 'a' : Link"
                        :href="isHome ? '#portfolio' : '/#portfolio'"
                        @click="mobileMenuOpen = false"
                        class="min-h-[44px] flex items-center px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-accent/80 transition"
                    >
                        Portfolio & Case Studies
                    </component>

                    <!-- Pricing -->
                    <Link
                        href="/pricing"
                        @click="mobileMenuOpen = false"
                        class="min-h-[44px] flex items-center justify-between px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-accent/80 transition"
                    >
                        <span>Pricing Models</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-sky-500/10 text-sky-700 dark:text-sky-400">Fixed Scope</span>
                    </Link>

                    <!-- Estimator -->
                    <Link
                        href="/estimator"
                        @click="mobileMenuOpen = false"
                        class="min-h-[44px] flex items-center px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-accent/80 transition"
                    >
                        Project Scope Estimator
                    </Link>

                    <!-- Price Book Download -->
                    <button
                        type="button"
                        @click="isHome ? handleBrochureClick() : undefined"
                        class="min-h-[44px] text-muted-foreground hover:text-foreground hover:bg-accent/80 px-3 rounded-lg transition flex items-center justify-between text-left"
                    >
                        <component
                            :is="isHome ? 'span' : Link"
                            :href="isHome ? undefined : '/pricing'"
                            @click="isHome ? undefined : (mobileMenuOpen = false)"
                            class="flex items-center justify-between w-full"
                        >
                            <span>2026 Enterprise Price Book</span>
                            <span class="rounded bg-sky-500/10 text-sky-700 dark:text-sky-400 text-[10px] font-bold px-2 py-0.5">PDF</span>
                        </component>
                    </button>

                    <!-- Blog -->
                    <Link
                        href="/blog"
                        @click="mobileMenuOpen = false"
                        class="min-h-[44px] flex items-center px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-accent/80 transition"
                    >
                        Engineering Blog & Insights
                    </Link>

                    <!-- Schedule Action Button in Mobile Drawer -->
                    <div class="mt-4 pt-4 border-t border-border space-y-2">
                        <component
                            :is="isHome ? 'button' : Link"
                            :type="isHome ? 'button' : undefined"
                            :href="isHome ? undefined : '/book'"
                            @click="isHome ? handleBookClick() : (mobileMenuOpen = false)"
                            class="btn-primary w-full min-h-[48px] rounded-full flex items-center justify-center gap-2 text-center text-xs font-bold text-white shadow-md cursor-pointer"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Schedule Architecture Session</span>
                        </component>

                        <div class="flex items-center justify-between px-2 pt-2 text-xs text-muted-foreground">
                            <span>Direct Line: <a href="tel:+919988212000" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">+91 99882 12000</a></span>
                            <span class="text-[11px] font-mono">15-min Discovery</span>
                        </div>
                    </div>
                </nav>
            </div>
        </Transition>
    </header>
</template>
