<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="AI Voice Agents & Autonomous AI Solutions — DigitalBuilders">
        <meta name="description" content="Custom AI voice agents, LLM copilots, vector retrieval RAG pipelines, and automated business workflows." />
        <meta property="og:title" content="Autonomous AI Voice Agents & RAG Workflows | DigitalBuilders" />
        <meta property="og:description" content="Custom AI voice agents, LLM copilots, vector retrieval RAG pipelines, and automated business workflows." />
        <meta property="og:image" content="https://www.digitalbuilders.in/images/portfolio/zoeticoach.jpg" />
        <meta property="og:url" content="https://www.digitalbuilders.in/services/ai-solutions" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="db-shell site-bg text-[var(--db-text)] min-h-screen">
        <div class="db-progress" />
        <div class="db-grid-overlay" />

        <header class="sticky top-0 z-50 border-b border-[#b8c9e633] bg-[var(--db-nav-bg)] backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-5 sm:py-4 lg:px-8">
                <Link href="/" class="db-gradient-text text-lg font-semibold tracking-wide">DigitalBuilders</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-medium text-slate-300 hover:text-white">← Home</Link>
                    <Link href="/estimator" class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white/50 sm:inline-flex">
                        Estimate AI Scope
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <span class="db-chip">Core Pillar 03</span>
                <h1 class="mt-4 text-3xl font-black text-white sm:text-5xl lg:text-6xl leading-tight">
                    AI Voice Agents & Agentic Workflows
                </h1>
                <p class="mt-6 text-base text-slate-300 sm:text-lg leading-relaxed">
                    Transform manual support & sales operations into 24/7 autonomous intelligence. We build custom WebRTC voice bots, retrieval-augmented generation (RAG), and smart CRM triggers.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="/#contact" class="rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] px-6 py-3.5 text-sm font-bold text-[#1a2231] hover:brightness-110 shadow-lg">
                        Build Your AI Agent
                    </a>
                    <Link href="/estimator" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white hover:border-white/50">
                        Calculate AI Scope
                    </Link>
                </div>
            </div>

            <!-- Capabilities -->
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">🎙️</div>
                    <h3 class="text-xl font-bold text-white">Sub-300ms Voice Latency</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Human-like conversational voice agents using streaming WebRTC STT and TTS engines.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">🧠</div>
                    <h3 class="text-xl font-bold text-white">Enterprise RAG Pipelines</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">Query your private database, PDFs, and ERP records accurately without data hallucination.</p>
                </div>
                <div class="rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] p-8">
                    <div class="text-2xl mb-3">⚡</div>
                    <h3 class="text-xl font-bold text-white">Automated Actions</h3>
                    <p class="mt-3 text-sm text-slate-300 leading-relaxed">AI agents execute actual business actions: log leads, book calendar slots, query shipment status.</p>
                </div>
            </div>

            <!-- Featured Case Studies -->
            <div class="mt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-xs uppercase tracking-[0.2em] text-[#9dc5ff] font-bold">Autonomous Systems Delivered</span>
                    <h2 class="mt-2 text-2xl font-black text-white sm:text-4xl">Featured AI & Automated Implementations</h2>
                    <p class="mt-3 text-sm text-slate-300">Production AI pipelines driving quantifiable retention, diagnostics intake, and automated user engagement.</p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- ZoetiCoach AI -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/zoeticoach.jpg" alt="ZoetiCoach AI" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-cyan-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">RAG + pgvector</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider">AI Accountability Agent</span>
                                <h3 class="mt-1 text-xl font-bold text-white">ZoetiCoach AI</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">OpenAI RAG agent processing natural-language WhatsApp voice/text check-ins, reducing client dropout by 65%.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://zoeticoach.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-cyan-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/zoeticoach" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- GutTalks AI Intake -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/guttalks.jpg" alt="GutTalks Clinical Intake" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-emerald-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Automated Intake</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Clinical Telehealth</span>
                                <h3 class="mt-1 text-xl font-bold text-white">GutTalks Diagnostics</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Intelligent medical intake routing matching patients to Root Rx doctors and sequencing test kits.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://guttalks.in/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-300 hover:text-white flex items-center gap-1">Visit Live App ↗</a>
                                <Link href="/portfolio/guttalks" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- GauSeva Connect Meta Cloud API -->
                    <div class="group rounded-3xl border border-[#b8c9e633] bg-[#27374dcb] overflow-hidden shadow-xl flex flex-col">
                        <div class="relative h-48 overflow-hidden bg-slate-900">
                            <img src="/images/portfolio/gaushala.jpg" alt="GauSeva Connect" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <span class="absolute top-3 right-3 rounded-full bg-amber-500/90 backdrop-blur px-3 py-1 text-[11px] font-bold text-white shadow">Meta Cloud Webhook</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Automated Devotee CRM</span>
                                <h3 class="mt-1 text-xl font-bold text-white">GauSeva Connect</h3>
                                <p class="mt-2 text-xs text-slate-300 leading-relaxed">Scheduled Meta WhatsApp Cloud API webhooks delivering personalized temple blessings on donor birthdays/anniversaries.</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <a href="https://krishanbalramgaushala.com/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-amber-300 hover:text-white flex items-center gap-1">Visit Live Portal ↗</a>
                                <Link href="/portfolio/gaushala" class="text-xs font-bold text-slate-300 hover:text-white">Case Study →</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
