<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
}>();
</script>

<template>
    <Head title="Welcome" />

    <div class="db-shell text-[#e7efff]">
        <div class="db-grid-overlay" />

        <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-5 py-16 lg:px-8">
            <section class="db-panel db-reveal w-full rounded-[2rem] p-8 sm:p-10 lg:p-12">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <span class="db-chip">Premium application shell</span>
                    <p class="text-xs uppercase tracking-[0.22em] text-[#b7c9e6]">
                        Laravel {{ laravelVersion }} - PHP {{ phpVersion }}
                    </p>
                </div>

                <h1 class="mt-6 text-4xl font-semibold leading-tight text-white sm:text-5xl lg:max-w-4xl">
                    DigitalBuilders control deck is now fully aligned with Hero Modern design language.
                </h1>

                <p class="mt-5 max-w-3xl text-sm leading-relaxed text-[#bfd0eb] sm:text-base">
                    The entire application uses cohesive gradients, elevated surfaces, animated reveal patterns, and premium typography inspired by your hero-modern component.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        :href="$page.props.auth.user ? route('dashboard') : '/'"
                        class="db-action inline-flex items-center rounded-full border border-[#b8c9e655] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3 text-xs font-bold uppercase tracking-[0.18em] text-[#1a2231]"
                    >
                        {{ $page.props.auth.user ? 'Open Dashboard' : 'Open Home' }}
                    </Link>

                    <Link
                        v-if="canLogin && !$page.props.auth.user"
                        :href="route('login')"
                        class="db-action inline-flex items-center rounded-full border border-[#b8c9e640] bg-[#2b3a4fd9] px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#e7efff]"
                    >
                        Login
                    </Link>

                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="route('register')"
                        class="db-action inline-flex items-center rounded-full border border-[#b8c9e640] bg-[#2b3a4fd9] px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#e7efff]"
                    >
                        Register
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>
