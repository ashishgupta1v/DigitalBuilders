<script setup lang="ts">
import ProjectEstimator from '@/Components/ProjectEstimator.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Interactive Project Estimator — DigitalBuilders" />

    <div class="db-shell bg-background text-foreground min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Simple Nav -->
        <header class="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <ApplicationLogo :is-link="true" href="/" />
                <div class="flex items-center gap-4">
                    <Link href="/pricing" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline">View Full Price Book</Link>
                    <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground">← Home</Link>
                    <a href="/#contact" class="hidden rounded-full btn-primary px-4 py-2 text-xs font-semibold text-white sm:inline-flex">
                        Contact Sales
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
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
