<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import {
  FileText, CheckCircle2, Share2, Printer, ExternalLink,
  ShieldCheck, ArrowRight, MessageSquare, Sparkles, Building2
} from 'lucide-vue-next'

const props = defineProps<{
  deal: {
    id: number
    title: string
    amount: number
    formatted_amount: string
    currency: string
    stage: string
    pricing_tier?: string
    proposal_sent_at?: string
    proposal_accepted_at?: string
  }
  lead: {
    name: string
    company: string
    role_title: string
  }
  proposal: {
    token: string
    content: string
    html: string
  }
}>()

const accepting = ref(false)
const accepted = ref(!!props.deal.proposal_accepted_at)

const acceptProposal = async () => {
  if (accepting.value || accepted.value) return
  accepting.value = true
  try {
    const res = await fetch(`/proposal/${props.proposal.token}/accept`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    if (data.success) {
      accepted.value = true
      // Open WhatsApp confirmation directly
      const msg = `Hi Ashish, I have reviewed and accepted the proposal for *${props.deal.title}*. Let's schedule the Phase 1 kickoff sprint!`
      window.open(`https://wa.me/919087021592?text=${encodeURIComponent(msg)}`, '_blank')
    }
  } catch (err) {
    console.error('Failed to accept proposal', err)
  } finally {
    accepting.value = false
  }
}

const printProposal = () => {
  window.print()
}
</script>

<template>
  <Head :title="`Engineering Scope & Proposal — ${deal.title}`" />

  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-purple-500 selection:text-white print:bg-white print:text-black transition-colors duration-300">
    <!-- Top Executive Header (Hidden on print) -->
    <header class="border-b border-slate-200 dark:border-slate-800/80 bg-white/85 dark:bg-slate-900/80 backdrop-blur-md sticky top-0 z-40 print:hidden transition-colors duration-300">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <ApplicationLogo :is-link="true" href="/" />
          <span class="text-[10px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 truncate hidden xs:inline-flex">
            Official Scope & Proposal
          </span>
        </div>

        <div class="flex items-center gap-2.5 sm:gap-3">
          <ThemeToggle />

          <button
            @click="printProposal"
            type="button"
            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer"
          >
            <Printer class="w-3.5 h-3.5" />
            <span>Print / PDF</span>
          </button>

          <a
            href="https://wa.me/919087021592"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600/10 hover:bg-emerald-600/20 dark:bg-emerald-600/20 dark:hover:bg-emerald-600/30 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-semibold transition"
          >
            <MessageSquare class="w-3.5 h-3.5" />
            <span>Chat with Architect</span>
          </a>
        </div>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10 print:py-0 print:px-0">
      <!-- Proposal Document Card -->
      <article class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-xl dark:shadow-2xl backdrop-blur-sm print:bg-white print:border-none print:shadow-none print:p-0 transition-colors duration-300">
        <!-- Letterhead Header -->
        <div class="border-b border-slate-200 dark:border-slate-800 pb-8 mb-8 flex flex-wrap items-start justify-between gap-6 print:border-slate-300">
          <div>
            <span class="text-[11px] font-mono uppercase tracking-widest text-purple-600 dark:text-purple-400 font-bold">
              Engineering Architecture Proposal
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1.5 tracking-tight print:text-black">
              {{ deal.title }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex items-center gap-2 print:text-slate-600">
              <span>Prepared for <strong class="text-slate-700 dark:text-slate-200">{{ lead.company }}</strong></span>
              <span>•</span>
              <span>Attn: {{ lead.name }} ({{ lead.role_title }})</span>
            </p>
          </div>

          <div class="text-right flex flex-col sm:items-end">
            <div class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400">Total Investment</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 font-mono print:text-emerald-700">
              {{ deal.formatted_amount }}
            </div>
            <div class="mt-2">
              <span
                v-if="accepted"
                class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>Proposal Accepted</span>
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-500/30"
              >
                <Sparkles class="w-3.5 h-3.5" />
                <span>Active Proposal</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Rendered Proposal Markdown HTML Body -->
        <div
          class="public-proposal-prose prose max-w-none text-slate-800 dark:text-slate-300 dark:prose-invert text-sm leading-relaxed print:text-black print:prose-neutral"
          v-html="proposal.html"
        ></div>

        <!-- Document Sign-off Confirmation Section -->
        <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800 print:border-slate-300">
          <div v-if="accepted" class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="w-6 h-6" />
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900 dark:text-white print:text-black">Proposal Formally Accepted</h4>
              <p class="text-xs text-slate-600 dark:text-slate-300 print:text-slate-600 mt-0.5">
                Thank you, {{ lead.name }}! Our lead architect Ashish has been notified and our engineering team is preparing your Phase 1 repository and staging environment.
              </p>
            </div>
          </div>

          <div v-else class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 print:hidden">
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">Ready to proceed with this scope?</h4>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Click below to accept this proposal and lock in our upcoming engineering sprint.
              </p>
            </div>

            <button
              type="button"
              @click="acceptProposal"
              :disabled="accepting"
              class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 disabled:opacity-50 text-white font-bold text-sm transition flex items-center justify-center gap-2 shadow-xl shadow-purple-500/20 cursor-pointer"
            >
              <span>{{ accepting ? 'Confirming...' : 'Accept Proposal & Begin Kickoff →' }}</span>
            </button>
          </div>
        </div>
      </article>

      <!-- DigitalBuilders Guarantee Banner (Hidden on print) -->
      <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400 print:hidden">
        <p>DigitalBuilders • 100% Code Ownership • 30-Day Hypercare Warranty • Zero Vendor Lock-in</p>
        <p class="mt-1">For urgent queries, reach Ashish directly at <a href="mailto:ashish@digitalbuilders.in" class="text-purple-600 dark:text-purple-400 hover:underline">ashish@digitalbuilders.in</a></p>
      </div>
    </main>
  </div>
</template>

<style>
/* Public Proposal HTML Styling */
.public-proposal-prose h1 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.75rem;
  margin-bottom: 1.25rem;
}
.dark .public-proposal-prose h1 {
  color: #f8fafc;
  border-bottom: 1px solid #334155;
}
.public-proposal-prose h2 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
  margin-top: 2rem;
  margin-bottom: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.35rem;
}
.dark .public-proposal-prose h2 {
  color: #e2e8f0;
  border-bottom: 1px solid #1e293b;
}
.public-proposal-prose p {
  margin-bottom: 0.85rem;
  line-height: 1.65;
}
.public-proposal-prose ul {
  list-style-type: disc;
  padding-left: 1.25rem;
  margin-bottom: 1rem;
}
.public-proposal-prose li {
  margin-bottom: 0.45rem;
}
.public-proposal-prose table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
  font-size: 0.85rem;
}
.public-proposal-prose th {
  background-color: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 0.65rem 0.85rem;
  text-align: left;
  font-weight: 700;
  color: #0f172a;
}
.dark .public-proposal-prose th {
  background-color: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  color: #f1f5f9;
}
.public-proposal-prose td {
  border: 1px solid #cbd5e1;
  padding: 0.65rem 0.85rem;
}
.dark .public-proposal-prose td {
  border: 1px solid #334155;
}
.public-proposal-prose tr:nth-child(even) td {
  background-color: #f8fafc;
}
.dark .public-proposal-prose tr:nth-child(even) td {
  background-color: rgba(15, 23, 42, 0.4);
}
.public-proposal-prose blockquote {
  border-left: 3px solid #9333ea;
  background: #faf5ff;
  padding: 0.85rem 1.15rem;
  border-radius: 0 0.75rem 0.75rem 0;
  margin: 1rem 0;
  color: #475569;
  font-style: italic;
}
.dark .public-proposal-prose blockquote {
  border-left: 3px solid #a855f7;
  background: rgba(168, 85, 247, 0.08);
  color: #cbd5e1;
}

@media print {
  body {
    background-color: #ffffff !important;
    color: #0f172a !important;
  }
  .public-proposal-prose h1, .public-proposal-prose h2, .public-proposal-prose p, .public-proposal-prose li, .public-proposal-prose td {
    color: #0f172a !important;
  }
  .public-proposal-prose h1 {
    border-bottom: 2px solid #0f172a !important;
  }
  .public-proposal-prose h2 {
    border-bottom: 1px solid #cbd5e1 !important;
  }
  .public-proposal-prose th {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
    border: 1px solid #cbd5e1 !important;
  }
  .public-proposal-prose td {
    border: 1px solid #cbd5e1 !important;
  }
  .public-proposal-prose blockquote {
    border-left: 3px solid #7c3aed !important;
    background: #faf5ff !important;
    color: #334155 !important;
  }
}
</style>
