<script setup lang="ts">
import ProjectEstimator from '@/Components/ProjectEstimator.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Interactive Project Estimator — DigitalBuilders" />

    <div class="db-shell bg-background text-foreground min-h-screen">
        <!-- Accessible Skip Link (WCAG 2.4.1) -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-5 focus:py-2.5 focus:bg-primary focus:text-white focus:rounded-full focus:shadow-2xl focus:font-bold focus:outline-none focus:ring-4 focus:ring-sky-400"
        >
            Skip to main content
        </a>

        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Header -->
        <SiteHeader active-route="/estimator" />

        <main id="main-content" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span class="db-chip">Precision Scope & Investment Calculator</span>
                <h1 class="mt-4 text-3xl font-black text-foreground sm:text-5xl">Architectural Cost & Timeline Estimator</h1>
                <p class="mx-auto mt-4 max-w-2xl text-base text-muted-foreground">
                    Get an immediate ballpark calculation based on your system scale, platform requirements, and custom feature modules.
                </p>
            </div>

            <div class="rounded-3xl border border-border bg-card text-card-foreground p-6 sm:p-8 lg:p-10 shadow-xl">
                <ProjectEstimator :is-standalone="true" />
            </div>
        </main>

        <footer class="border-t border-border bg-card/60 py-8 text-center text-xs text-muted-foreground">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>
