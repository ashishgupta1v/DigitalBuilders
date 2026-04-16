<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const flash = computed(() => (usePage().props as Record<string, any>).flash as Record<string, string> | undefined);
const isLoading = ref(false);

onMounted(() => {
    router.on('start', () => {
        isLoading.value = true;
    });
    router.on('finish', () => {
        isLoading.value = false;
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-[#e7efff]">
                    Command Dashboard
                </h2>
                <span class="db-chip">Live workspace</span>
            </div>
        </template>

        <!-- Flash toast trigger -->
        <div v-if="flash?.success" class="sr-only" :data-flash-msg="flash.success" />

        <div class="py-8 sm:py-12">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                <section class="db-panel db-reveal rounded-[1.5rem] p-5 sm:p-7 lg:col-span-2">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#b9cae6]">Mission overview</p>
                    <h3 class="mt-3 text-2xl font-semibold leading-tight text-white sm:text-3xl">Your premium operating surface is now active.</h3>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-[#bfd0eb]">
                        This dashboard follows the DigitalBuilders visual language with elevated surfaces, nuanced gradients, and cohesive animation behavior.
                    </p>
                    <div class="db-divider my-6" />
                    <div class="grid gap-4 sm:grid-cols-3">
                        <article class="db-mini db-glass rounded-2xl px-4 py-4">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#bfd0eb]">Velocity</p>
                            <p class="mt-2 text-2xl font-bold text-white">+28%</p>
                        </article>
                        <article class="db-mini db-glass rounded-2xl px-4 py-4">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#bfd0eb]">Signals</p>
                            <p class="mt-2 text-2xl font-bold text-white">142</p>
                        </article>
                        <article class="db-mini db-glass rounded-2xl px-4 py-4">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#bfd0eb]">Stability</p>
                            <p class="mt-2 text-2xl font-bold text-white">99.9%</p>
                        </article>
                    </div>
                </section>

                <aside class="db-panel db-reveal-fast rounded-[1.5rem] p-5 sm:p-7" style="animation-delay: 0.12s">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#b9cae6]">Status</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">Authenticated</h3>
                    <p class="mt-4 text-sm leading-relaxed text-[#bfd0eb]">
                        You are logged in and operating within the secured control deck.
                    </p>
                    <div class="db-mini mt-6 rounded-2xl border border-[#b8c9e63d] bg-[linear-gradient(95deg,rgba(122,196,255,0.16),rgba(155,167,255,0.14),rgba(197,147,255,0.16))] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#dce7fb]">
                        Experience upgraded
                    </div>

                    <!-- Skeleton loading state -->
                    <div v-if="isLoading" class="mt-6 space-y-3" aria-busy="true" aria-label="Loading...">
                        <div class="db-skeleton h-4 w-3/4 rounded-lg" />
                        <div class="db-skeleton h-4 w-1/2 rounded-lg" />
                        <div class="db-skeleton h-4 w-5/6 rounded-lg" />
                    </div>
                    <div v-else class="mt-6 space-y-3">
                        <p class="text-sm text-[#7a95b8]">Status: All systems operational</p>
                        <p class="text-sm text-[#7a95b8]">Last sync: Just now</p>
                        <p class="text-sm text-[#7a95b8]">Connected clients: 12</p>
                    </div>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
