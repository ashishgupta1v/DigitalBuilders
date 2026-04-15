<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="db-shell">
        <div class="db-grid-overlay" />

        <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="db-reveal-fast mb-7 flex flex-col items-center">
                <Link href="/" class="db-action rounded-[1.75rem] border border-[#b8c9e640] bg-[#27374dd6] p-4 shadow-[0_16px_34px_rgba(10,16,24,0.3)]">
                    <ApplicationLogo class="h-16 w-16" />
                </Link>

                <p class="db-gradient-text mt-5 text-center text-sm font-semibold uppercase tracking-[0.24em]">
                    DigitalBuilders
                </p>
                <p class="mt-2 text-center text-xs uppercase tracking-[0.24em] text-[#b4c3de]">
                    Premium Software Architecture
                </p>
            </div>

            <div class="db-glass db-reveal w-full overflow-hidden rounded-[1.75rem] border border-[#b8c9e64d] px-6 py-7 sm:max-w-lg sm:px-8 sm:py-8">
                <slot />
            </div>

            <p class="mt-5 text-center text-[11px] uppercase tracking-[0.2em] text-[#9fb1cd]">
                Crafted with precision for high-conversion digital systems
            </p>
        </div>
    </div>
</template>
