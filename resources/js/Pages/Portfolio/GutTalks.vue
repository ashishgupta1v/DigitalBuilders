<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="GutTalks Case Study — Gut Health Consultation Platform | DigitalBuilders">
        <meta name="description" content="How DigitalBuilders engineered a seamless gut health consultation and clinical assessment booking platform with dynamic medical questionnaires." />
        <meta property="og:title" content="GutTalks Case Study — Gut Health Consultation Platform | DigitalBuilders" />
        <meta property="og:description" content="How DigitalBuilders engineered a seamless gut health consultation and clinical assessment booking platform with dynamic medical questionnaires." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/guttalks.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/portfolio/guttalks" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="GutTalks Case Study — Telehealth Platform | DigitalBuilders" />
        <meta name="twitter:description" content="Clinical consultation and gut health assessment booking platform." />
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/portfolio/guttalks.jpg" />
    </Head>

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <a href="https://guttalks.in/" target="_blank" rel="noopener noreferrer" class="rounded-full bg-secondary border border-border px-4 py-2 text-xs font-semibold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-1.5">
                        Visit Live App
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="db-chip">Case Study · Telehealth & HealthTech</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl leading-tight">
                    GutTalks
                </h1>
                <p class="mt-4 text-lg font-semibold text-sky-700 dark:text-sky-400">
                    Telehealth Consultation & Interactive Dietary Health Assessment Engine
                </p>
            </div>

            <!-- Screenshot Hero Container -->
            <div class="mt-8 overflow-hidden rounded-3xl border border-border shadow-2xl bg-card">
                <img
                    src="/images/portfolio/guttalks.webp"
                    alt="GutTalks Portal Snapshot"
                    class="w-full h-auto object-cover object-top"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <!-- Key Metrics Bar -->
            <div class="mt-12 grid gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">10k+</p>
                    <p class="mt-1 text-xs text-muted-foreground">Patients & Clients Consulted</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-sky-700 dark:text-sky-400">4.8 ★</p>
                    <p class="mt-1 text-xs text-muted-foreground">Verified Google Reviews</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">₹499</p>
                    <p class="mt-1 text-xs text-muted-foreground">Instant Root Rx Telehealth</p>
                </div>
                <div class="rounded-2xl border border-border bg-card text-card-foreground p-6 shadow-sm">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">3.2x</p>
                    <p class="mt-1 text-xs text-muted-foreground">Consultation Conversion Rate</p>
                </div>
            </div>

            <!-- Breakdown Cards -->
            <div class="mt-12 space-y-8">
                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">The Clinical Challenge</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        Patients suffering from chronic bloating, IBS, acidity, and fatigue were overwhelmed by fragmented medical advice and high consultation barriers. GutTalks required an accessible, doctor-led clinical platform that provides instant ₹499 Root Rx appointments, at-home microbiome sequencing (GutMap Complete™), and personalized 30-day recovery roadmaps.
                    </p>
                </div>

                <div class="rounded-3xl border border-border bg-card text-card-foreground p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-card-foreground">Our Architectural Solution</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                        We engineered an ultra-clean, high-converting Telehealth & E-Commerce platform combining clinical intake, kit delivery tracking, and nutritionist booking.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-sm text-card-foreground">
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0" />
                            <span><strong>Root Rx 1-on-1 Consultation Engine:</strong> Frictionless ₹499 doctor booking with automated calendar slot synchronization and WhatsApp consultation links.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500 shrink-0" />
                            <span><strong>GutMap Complete™ Diagnostic Tracking:</strong> Interactive at-home microbiome kit ordering, logistics tracking, and structured lab report viewer.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="mt-1 h-2 w-2 rounded-full bg-purple-500 shrink-0" />
                            <span><strong>Curated Probiotic & Supplement Store:</strong> Razorpay-powered checkout for clinician-vetted digestive health remedies and gut cleanses.</span>
                        </li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Next.js</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">React</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Tailwind CSS</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Razorpay Checkout</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">Doctor Telehealth API</span>
                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-bold text-secondary-foreground border border-border">WhatsApp Reminders</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-border bg-secondary/60 p-8 shadow-md">
                    <blockquote class="text-base italic text-card-foreground sm:text-lg">
                        "The Root Rx booking flow and GutMap testing portal engineered by DigitalBuilders increased our consultation bookings dramatically. Our patients love how intuitive the interface is, and our doctors receive pre-qualified medical history before every call."
                    </blockquote>
                    <p class="mt-4 text-xs font-bold text-card-foreground">— Dr. Mehak Verma, Head of Clinical Nutrition, GutTalks India</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-foreground">Launching a HealthTech or Consultation Portal?</h3>
                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <a href="https://guttalks.in/" target="_blank" rel="noopener noreferrer" class="rounded-full border border-border bg-secondary px-6 py-3.5 text-sm font-bold text-secondary-foreground hover:bg-secondary/80 inline-flex items-center gap-2">
                        Explore GutTalks Live
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="/#contact" class="btn-primary rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:scale-[1.01]">
                        Book Architecture Consultation
                    </a>
                </div>
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
