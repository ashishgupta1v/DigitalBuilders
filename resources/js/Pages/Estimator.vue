<script setup lang="ts">
import ProjectEstimator from '@/Components/ProjectEstimator.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Interactive Project Estimator — DigitalBuilders" />

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <!-- Simple Nav -->
        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/pricing" class="text-xs font-semibold text-sky-400 hover:text-sky-300">View Full Price Book</Link>
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <a href="/#contact" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Contact Sales
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span class="db-chip">Precision Scope & Investment Calculator</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl">Architectural Cost & Timeline Estimator</h1>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-300">
                    Get an immediate ballpark calculation based on your system scale, platform requirements, and custom feature modules.
                </p>
            </div>

            <ProjectEstimator :is-standalone="true" />
        </main>

        <footer class="border-t border-[#b8c9e633] bg-[#233246d9] py-8 text-center text-xs text-slate-400">
            <p>© {{ new Date().getFullYear() }} DigitalBuilders. All rights reserved.</p>
        </footer>
    </div>
</template>

<style scoped>
.site-bg {
    background:
        radial-gradient(1200px 700px at -10% -5%, rgba(122, 196, 255, 0.28), transparent 55%),
        radial-gradient(900px 700px at 105% 10%, rgba(197, 147, 255, 0.25), transparent 50%),
        linear-gradient(180deg, #1f2b3b 0%, #1c2938 56%, #1e2a3a 100%);
}
</style>
