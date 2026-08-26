<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StickyMobileCta from '@/Components/StickyMobileCta.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    serviceType: 'Cross-Platform & Native Mobile App Development',
    provider: {
        '@type': 'ProfessionalService',
        name: 'DigitalBuilders',
        url: 'https://www.digitalbuilders.in',
    },
    description: 'Native-feeling, high-performance mobile applications for iOS and Android engineered for fluid 60fps performance and offline synchronization.',
    areaServed: ['IN', 'US', 'GB', 'AE', 'CA', 'AU'],
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'Mobile App Development Packages',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Launch Mobile App (iOS & Android)',
                },
                priceCurrency: 'INR',
                price: '149000',
            },
        ],
    },
};

const breadcrumbSchema = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://www.digitalbuilders.in' },
        { '@type': 'ListItem', position: 2, name: 'Services', item: 'https://www.digitalbuilders.in/#services' },
        { '@type': 'ListItem', position: 3, name: 'Mobile App Development', item: 'https://www.digitalbuilders.in/services/mobile-apps' },
    ],
};
</script>

<template>
    <Head title="Mobile App Development (iOS & Android) — DigitalBuilders">
        <meta name="description" content="Native-feeling, high-performance mobile applications for iOS and Android engineered for flawless 60fps responsiveness." />
        <link rel="canonical" href="https://www.digitalbuilders.in/services/mobile-apps" />
        <meta property="og:title" content="Mobile App Development (iOS & Android) | DigitalBuilders" />
        <meta property="og:description" content="Native-feeling, high-performance mobile applications for iOS and Android engineered for fluid 60fps performance and offline synchronization." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/sportsclub.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/mobile-apps" />
        <meta name="twitter:card" content="summary_large_image" />
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(serviceSchema) }}
        </component>
        <component is="script" type="application/ld+json">
            {{ JSON.stringify(breadcrumbSchema) }}
        </component>
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full btn-primary px-4 py-2 text-xs font-semibold text-white sm:inline-flex">
                        Estimate Mobile App Cost
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 02</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl lg:text-6xl leading-tight">
                    Mobile App Development (iOS & Android)
                </h1>
                <p class="mt-6 text-base text-muted-foreground sm:text-lg leading-relaxed">
                    Native-feeling, ultra-responsive mobile applications engineered for fluid 60fps performance, offline synchronization, and seamless backend API integration.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="btn-primary rounded-full px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Discuss Your Mobile App
                    </a>
                    <Link href="/estimator" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80">
                        Calculate Mobile App Scope
                    </Link>
                </div>
            </div>

            <!-- Capabilities Grid -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">📱</div>
                    <h3 class="text-xl font-bold text-card-foreground">Cross-Platform Mastery</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Single codebase for iOS and Android with zero compromise on native look, feel, and performance.</p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">📶</div>
                    <h3 class="text-xl font-bold text-card-foreground">Offline-First Architecture</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Local database storage and optimistic background sync when internet connection drops.</p>
                </div>
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-lg">
                    <div class="text-2xl mb-3">🔔</div>
                    <h3 class="text-xl font-bold text-card-foreground">Push & Real-time Sync</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">FCM push notifications, background location tracking, and real-time WebSocket data updates.</p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400 font-bold">Mobile Engineering In Action</span>
                    <h2 class="mt-2 text-2xl font-black text-foreground sm:text-4xl">Featured Mobile Applications</h2>
                    <p class="mt-3 text-sm text-muted-foreground">Fluid cross-platform apps engineered for offline speed, concurrency safety, and instant feedback.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Sports Club -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/sportsclub.jpg" alt="Sports Club App" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-cyan-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">0 Collision Locks</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">Facility & Club App</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">SportsEntertainmentClub</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">60 FPS mobile app with atomic slot booking locks, digital QR passes, and live tournament draws.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <Link href="/portfolio/sports-club" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">Read Case Study ↗</Link>
                                <a href="/#contact" class="text-xs font-bold text-muted-foreground hover:text-foreground">Request APK →</a>
                            </div>
                        </div>
                    </div>

                    <!-- Garg Enterprises -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gargenterprises.jpg" alt="Garg Enterprises B2B App" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-indigo-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Offline SQLite</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">B2B Wholesale App</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Garg Enterprises</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Rugged B2B wholesale ordering app with offline SQLite cart drafting and 1-tap GST invoices for 10k+ SKUs.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <Link href="/portfolio/garg-enterprises" class="text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:underline flex items-center gap-1">Read Case Study ↗</Link>
                                <a href="/#contact" class="text-xs font-bold text-muted-foreground hover:text-foreground">Request APK →</a>
                            </div>
                        </div>
                    </div>

                    <!-- Habuilt Mobile Deep Linking -->
                    <div class="group rounded-3xl border border-border bg-card text-card-foreground overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/habuilt.jpg" alt="Habuilt Universal Sync" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-sky-600/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Deep Linking</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Habit Tracking</span>
                                <h3 class="mt-1 text-xl font-bold text-card-foreground">Habuilt Mobile Sync</h3>
                                <p class="mt-2 text-xs text-muted-foreground leading-relaxed">Seamless universal mobile authentication deep linking with sub-100ms streak data syncing.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                                <a href="https://www.habuilt.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/habuilt" class="text-xs font-bold text-muted-foreground hover:text-foreground">Case Study →</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>

        <StickyMobileCta />
    </div>
</template>
