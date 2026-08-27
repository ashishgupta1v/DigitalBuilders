<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface ServiceItem {
    title: string;
    summary: string;
    link: string;
}

defineProps<{
    services: readonly ServiceItem[];
}>();
</script>

<template>
    <section id="services" class="mt-20 sm:mt-24" data-reveal>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <span class="db-badge-sky mb-3">Enterprise Practice Areas</span>
                <h2 class="mt-2 text-2xl font-black text-foreground sm:text-3xl md:text-4xl">Architectural Services Built for Scalability</h2>
            </div>
            <Link href="/estimator" class="rounded-full border border-sky-500/30 bg-sky-500/10 px-5 py-2.5 min-h-[44px] text-xs font-bold text-sky-700 dark:text-sky-300 hover:bg-sky-500/20 transition-all inline-flex items-center gap-2 self-start md:self-auto shadow-sm">
                <span>Scope Your Project</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
        </div>

        <!-- Asymmetric Bento Grid Layout -->
        <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3" data-stagger>
            <!-- Bento Card 1: Web Applications (Featured Span 2 on md/lg) -->
            <article data-stagger-item class="db-bento-card p-8 flex flex-col justify-between md:col-span-2 group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 via-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-500/20 to-indigo-500/15 border border-sky-500/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="h-7 w-7 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="db-badge-emerald">Sub-100ms Latency</span>
                    </div>
                    <h3 class="text-xl font-bold text-card-foreground">Custom Web Applications & Portals</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed max-w-2xl">High-concurrency web platforms engineered with Laravel, Vue 3, and Inertia.js. Domain-driven architecture designed to handle thousands of concurrent transactions with zero technical debt.</p>
                </div>
                <div class="mt-8 pt-4 border-t border-border/80 flex items-center justify-between">
                    <Link href="/services/web-applications" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 dark:text-sky-400 hover:underline">
                        Explore Web Architecture
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <span class="text-xs text-muted-foreground font-mono">Vue 3 · Inertia · Laravel</span>
                </div>
            </article>

            <!-- Bento Card 2: Mobile Apps -->
            <article data-stagger-item class="db-bento-card p-8 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/15 border border-indigo-500/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="h-7 w-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="db-badge-indigo">60 FPS Fluid UI</span>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Native & Cross-Platform Mobile Apps</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Smooth iOS & Android apps built with Flutter, React Native, or Kotlin Native with offline SQLite sync and biometric security.</p>
                </div>
                <div class="mt-8 pt-4 border-t border-border/80 flex items-center justify-between">
                    <Link href="/services/mobile-apps" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 dark:text-indigo-400 hover:underline">
                        Explore Mobile Apps
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <span class="text-xs text-muted-foreground font-mono">iOS & Android</span>
                </div>
            </article>

            <!-- Bento Card 3: Autonomous AI & Voice Agents -->
            <article data-stagger-item class="db-bento-card p-8 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/15 border border-purple-500/30 text-purple-700 dark:text-purple-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="h-7 w-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        </div>
                        <span class="db-badge-purple">RAG + WhatsApp</span>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Voice AI & Autonomous Agents</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Enterprise conversational AI agents, WhatsApp automated qualification workflows, and zero-hallucination vector search.</p>
                </div>
                <div class="mt-8 pt-4 border-t border-border/80 flex items-center justify-between">
                    <Link href="/services/ai-solutions" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-700 dark:text-purple-400 hover:underline">
                        Explore AI Agents
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <span class="text-xs text-muted-foreground font-mono">pgvector · OpenAI</span>
                </div>
            </article>

            <!-- Bento Card 4: ERP & CRM Monoliths -->
            <article data-stagger-item class="db-bento-card p-8 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="h-7 w-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </div>
                        <span class="db-badge-emerald">GST & Multi-Branch</span>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">Enterprise ERP & CRM Systems</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">Centralized business ERPs, daily compliance ledgers, inventory reconciliation, and custom invoicing workflows.</p>
                </div>
                <div class="mt-8 pt-4 border-t border-border/80 flex items-center justify-between">
                    <Link href="/services/erp-crm" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                        Explore ERP Solutions
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <span class="text-xs text-muted-foreground font-mono">Multi-Tenant</span>
                </div>
            </article>

            <!-- Bento Card 5: High-Scale SaaS Platforms -->
            <article data-stagger-item class="db-bento-card p-8 flex flex-col justify-between group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <div>
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="h-7 w-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <span class="db-badge-sky">Multi-Tenant SaaS</span>
                    </div>
                    <h3 class="text-lg font-bold text-card-foreground">High-Scale SaaS Platforms</h3>
                    <p class="mt-3 text-sm text-muted-foreground leading-relaxed">SaaS platforms with automated recurring billing, Stripe/Razorpay checkouts, webhook queues, and granular RBAC.</p>
                </div>
                <div class="mt-8 pt-4 border-t border-border/80 flex items-center justify-between">
                    <Link href="/services/saas-platforms" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-400 hover:underline">
                        Explore SaaS Platforms
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <span class="text-xs text-muted-foreground font-mono">Stripe · Razorpay</span>
                </div>
            </article>
        </div>

        <!-- Architectural Specification & Price Book Download Card -->
        <div class="mt-8 db-bento-card p-6 sm:p-8 flex flex-col lg:flex-row items-center justify-between gap-6 shadow-lg" data-stagger-item>
            <div class="flex items-center gap-5">
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-500/20 to-indigo-500/20 border border-sky-500/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-black text-2xl shrink-0 shadow-sm">
                    📄
                </div>
                <div>
                    <span class="db-badge-sky mb-1.5">Printable Brochure</span>
                    <h3 class="text-base sm:text-lg font-bold text-foreground">2026 Architectural Price Book & Service Catalogue</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-0.5">Explore full system deliverables, add-on modules, SLAs, and fixed pricing in a clean printable format.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto shrink-0">
                <a
                    href="/downloads/digitalbuilders-pricing-india-inr.html"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-full border border-sky-500/40 bg-sky-500/10 px-5 py-2.5 text-xs font-bold text-sky-700 dark:text-sky-300 hover:bg-sky-500/20 transition-all shadow-sm cursor-pointer"
                >
                    <span>🇮🇳 India (INR ₹)</span>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <a
                    href="/downloads/digitalbuilders-pricing-international-usd.html"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-primary inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-full px-5 py-2.5 text-xs font-bold text-white transition-all shadow-sm cursor-pointer"
                >
                    <span>🌐 International (USD $)</span>
                    <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>
    </section>
</template>
