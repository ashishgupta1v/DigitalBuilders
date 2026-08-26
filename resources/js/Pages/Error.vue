<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status: number;
}>();

const title = computed(() => {
    return {
        403: '403: Forbidden Access',
        404: '404: Architecture Page Not Found',
        419: '419: Session Expired',
        500: '500: Internal Server Error',
        503: '503: Service Unavailable / Maintenance',
    }[props.status] || `${props.status}: Unexpected Error`;
});

const description = computed(() => {
    return {
        403: 'Sorry, you do not have permission to access this secure resource.',
        404: 'The system blueprint or page you are looking for has been relocated or archived.',
        419: 'Your security token or session has expired due to inactivity. Please reload the page to continue.',
        500: 'Our engineering systems caught an unexpected exception. Our team has been alerted.',
        503: 'DigitalBuilders is currently undergoing scheduled platform upgrades. We will be right back.',
    }[props.status] || 'An unexpected error occurred while processing your request.';
});

function reloadPage() {
    window.location.reload();
}
</script>

<template>
    <Head :title="title" />

    <div class="relative min-h-screen flex items-center justify-center bg-[#070b14] text-slate-100 px-4 py-12 selection:bg-sky-500 selection:text-white font-sans overflow-hidden">
        <!-- Ambient Grid & Glow -->
        <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none" />
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-sky-600/10 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 w-full max-w-lg rounded-3xl border border-slate-800 bg-[#0f172a]/90 backdrop-blur-xl p-8 sm:p-10 text-center shadow-2xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-500/10 border border-sky-500/30 text-sky-400 mb-6">
                <svg v-if="props.status === 419" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg v-else-if="props.status === 404" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <svg v-else class="h-8 w-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>

            <p class="text-xs font-black tracking-[0.25em] text-sky-400 uppercase">System Status {{ props.status }}</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-black text-white tracking-tight">{{ title }}</h1>
            <p class="mt-4 text-sm text-slate-400 leading-relaxed">{{ description }}</p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button
                    v-if="props.status === 419"
                    @click="reloadPage"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-full bg-[linear-gradient(95deg,#0284c7_0%,#4f46e5_48%,#7c3aed_100%)] px-6 py-3 text-xs font-bold text-white shadow-lg hover:scale-[1.02] cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reload & Refresh Session
                </button>
                <Link
                    href="/"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-full border border-slate-700 bg-slate-800/80 px-6 py-3 text-xs font-bold text-slate-200 hover:bg-slate-700 transition"
                >
                    Return to Homepage
                </Link>
                <a
                    href="https://wa.me/919087021592?text=Hi%20Ashish,%20I%20encountered%20an%20error%20on%20DigitalBuilders"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-5 py-3 text-xs font-bold text-emerald-300 hover:bg-emerald-500/20 transition"
                >
                    Support WhatsApp
                </a>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800/80 text-xs text-slate-500">
                DigitalBuilders Enterprise Resilience · Incident Trace ID: #{{ Math.random().toString(36).substring(2, 9).toUpperCase() }}
            </div>
        </div>
    </div>
</template>
