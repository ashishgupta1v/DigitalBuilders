<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import {
  FileText, CheckCircle2, Printer, ExternalLink,
  ShieldCheck, ArrowRight, MessageSquare, Sparkles, Building2,
  CreditCard, Copy, Check, Receipt, Lock, ChevronDown, ChevronUp, AlertCircle
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
    public_url?: string
  }
  milestones?: Record<string, {
    key: string
    title: string
    percentage: number
    amount: number
    formatted: string
    savings?: string
    description: string
    recommended?: boolean
  }>
  payments?: Array<{
    id: number
    amount: number
    currency: string
    status: string
    gateway: string
    payment_method?: string
    transaction_utr?: string | null
    created_at?: string
    paid_at?: string | null
  }>
  bank_details?: {
    account_name: string
    bank_name: string
    account_no: string
    ifsc_code: string
    swift_code?: string
    upi_id?: string
    gstin?: string
  }
  app_meta?: {
    founder_name?: string
    founder_title?: string
    founder_email?: string
    founder_phone?: string
    company_name?: string
    website_url?: string
  }
}>()

const accepting = ref(false)
const accepted = ref(!!props.deal.proposal_accepted_at)

// Plan Selection (Default: kickoff_40)
const selectedPlan = ref<string>('kickoff_40')
const isGeneratingLink = ref(false)
const checkoutUrl = ref<string | null>(null)
const checkoutError = ref<string | null>(null)

// Bank Wire State
const showWireSection = ref(false)
const wireUtr = ref('')
const wireNotes = ref('')
const isSubmittingWire = ref(false)
const wireSubmitted = ref(false)
const wireSuccessMsg = ref('')
const wireError = ref<string | null>(null)
const copiedField = ref<string | null>(null)

const activeMilestone = computed(() => {
  if (!props.milestones) return null
  return props.milestones[selectedPlan.value] || props.milestones['kickoff_40']
})

const copyText = (text: string, field: string) => {
  navigator.clipboard.writeText(text)
  copiedField.value = field
  setTimeout(() => {
    copiedField.value = null
  }, 2000)
}

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
      const founder = props.app_meta?.founder_name || 'Founder'
      const phoneDigits = (props.app_meta?.founder_phone || '919087021592').replace(/[^0-9]/g, '')
      const msg = `Hi ${founder}, I have reviewed and accepted the proposal for *${props.deal.title}*. Let's schedule the Phase 1 kickoff sprint!`
      window.open(`https://wa.me/${phoneDigits}?text=${encodeURIComponent(msg)}`, '_blank')
    }
  } catch (err) {
    console.error('Failed to accept proposal', err)
  } finally {
    accepting.value = false
  }
}

const generatePaymentLink = async () => {
  isGeneratingLink.value = true
  checkoutError.value = null
  try {
    const res = await fetch(`/proposal/${props.proposal.token}/payment-link`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        plan_type: selectedPlan.value,
      }),
    })
    const data = await res.json()
    if (data.success && data.checkout_url) {
      checkoutUrl.value = data.checkout_url
      window.open(data.checkout_url, '_blank')
    } else {
      checkoutError.value = data.message || 'Unable to generate direct payment link. Please proceed via bank wire.'
    }
  } catch (err: any) {
    checkoutError.value = err.message || 'Connection error while contacting payment gateway.'
  } finally {
    isGeneratingLink.value = false
  }
}

const submitWirePayment = async () => {
  if (!wireUtr.value || isSubmittingWire.value) return
  isSubmittingWire.value = true
  wireError.value = null
  try {
    const targetAmount = activeMilestone.value ? activeMilestone.value.amount : (props.deal.amount * 0.40)
    const res = await fetch(`/proposal/${props.proposal.token}/wire`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        transaction_utr: wireUtr.value,
        amount: targetAmount,
        plan_type: selectedPlan.value,
        notes: wireNotes.value,
      }),
    })
    const data = await res.json()
    if (data.success) {
      wireSubmitted.value = true
      wireSuccessMsg.value = data.message || 'Transaction reference recorded! Our engineering leadership has been notified.'
      accepted.value = true
    } else {
      wireError.value = data.message || 'Failed to submit bank wire transaction reference.'
    }
  } catch (err: any) {
    wireError.value = err.message || 'Network error while submitting wire reference.'
  } finally {
    isSubmittingWire.value = false
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

        <div class="flex items-center gap-2 sm:gap-2.5">
          <ThemeToggle />

          <!-- Proforma Invoice Link -->
          <a
            :href="`/proposal/${proposal.token}/invoice`"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition"
          >
            <Receipt class="w-3.5 h-3.5 text-purple-500" />
            <span class="hidden sm:inline">Proforma Invoice</span>
            <span class="sm:hidden">Invoice</span>
          </a>

          <button
            @click="printProposal"
            type="button"
            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer"
          >
            <Printer class="w-3.5 h-3.5" />
            <span>Print</span>
          </button>

          <a
            :href="'https://wa.me/' + ((app_meta?.founder_phone || '919087021592').replace(/[^0-9]/g, ''))"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600/10 hover:bg-emerald-600/20 dark:bg-emerald-600/20 dark:hover:bg-emerald-600/30 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-semibold transition"
          >
            <MessageSquare class="w-3.5 h-3.5" />
            <span class="hidden md:inline">Chat with Architect</span>
            <span class="md:hidden">WhatsApp</span>
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

        <!-- Self-Serve Commercial Milestone & Payment Checkout (Hidden in print) -->
        <div v-if="milestones" class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800 print:hidden">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <CreditCard class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                <span>Select Your Preferred Kickoff Milestone</span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Lock in dedicated architecture bandwidth with an instant deposit or direct corporate wire.
              </p>
            </div>
          </div>

          <!-- 3 Milestone Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mb-6">
            <div
              v-for="m in milestones"
              :key="m.key"
              @click="selectedPlan = m.key"
              class="relative rounded-2xl p-4 border transition-all cursor-pointer flex flex-col justify-between"
              :class="selectedPlan === m.key
                ? 'border-purple-500 dark:border-purple-400 bg-purple-50/50 dark:bg-purple-950/20 shadow-md ring-2 ring-purple-500/20'
                : 'border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-700'"
            >
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <span
                    v-if="m.recommended"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-600 text-white uppercase tracking-wider"
                  >
                    Recommended
                  </span>
                  <span
                    v-else-if="m.savings"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white uppercase tracking-wider"
                  >
                    Save {{ m.savings }}
                  </span>
                  <span v-else class="text-[10px] font-mono text-slate-400">
                    {{ m.percentage }}% Milestone
                  </span>

                  <span
                    class="w-4 h-4 rounded-full border flex items-center justify-center"
                    :class="selectedPlan === m.key ? 'border-purple-600 bg-purple-600 text-white' : 'border-slate-400'"
                  >
                    <Check v-if="selectedPlan === m.key" class="w-2.5 h-2.5 stroke-[3]" />
                  </span>
                </div>

                <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                  {{ m.title }}
                </h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                  {{ m.description }}
                </p>
              </div>

              <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-slate-800/80 flex items-baseline justify-between">
                <span class="text-[10px] uppercase font-bold text-slate-400">Deposit Due</span>
                <span class="text-lg font-black font-mono text-slate-900 dark:text-white">
                  {{ m.formatted }}
                </span>
              </div>
            </div>
          </div>

          <!-- Checkout & Remittance Action Center -->
          <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-purple-50/30 dark:from-slate-950 dark:to-purple-950/20 border border-slate-200 dark:border-slate-800">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-mono text-purple-600 dark:text-purple-400 uppercase font-bold">Selected Deposit</span>
                  <span class="text-sm font-black font-mono text-slate-900 dark:text-white">
                    {{ activeMilestone?.formatted }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Instant receipt and automated onboarding workspace created upon transaction.
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <!-- Direct Online Checkout Button -->
                <button
                  type="button"
                  @click="generatePaymentLink"
                  :disabled="isGeneratingLink"
                  class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-purple-500/20 transition cursor-pointer"
                >
                  <Lock class="w-3.5 h-3.5" />
                  <span>{{ isGeneratingLink ? 'Initiating...' : 'Pay Online (Card / UPI / NetBanking)' }}</span>
                </button>

                <!-- Bank Wire Toggle Button -->
                <button
                  type="button"
                  @click="showWireSection = !showWireSection"
                  class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer"
                >
                  <Building2 class="w-3.5 h-3.5 text-slate-400" />
                  <span>Bank Wire / UTR</span>
                  <component :is="showWireSection ? ChevronUp : ChevronDown" class="w-3.5 h-3.5 ml-0.5" />
                </button>
              </div>
            </div>

            <!-- Error Banner -->
            <div v-if="checkoutError" class="mt-3 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-xs text-red-600 dark:text-red-400 flex items-center gap-2">
              <AlertCircle class="w-4 h-4 shrink-0" />
              <span>{{ checkoutError }}</span>
            </div>

            <!-- Expandable Corporate Bank Coordinates & UTR Claim Drawer -->
            <div v-if="showWireSection" class="mt-5 pt-5 border-t border-slate-200 dark:border-slate-800 space-y-4">
              <div v-if="bank_details" class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1.5">
                  <div class="text-[10px] uppercase font-bold text-slate-400 font-sans">RTGS / NEFT / IMPS Coordinates</div>
                  <div class="flex justify-between">
                    <span class="text-slate-400">Account:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ bank_details.account_name }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-400">Bank:</span>
                    <span class="font-semibold">{{ bank_details.bank_name }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-slate-400">Acc No:</span>
                    <span class="font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1">
                      {{ bank_details.account_no }}
                      <button @click="copyText(bank_details.account_no, 'acc')" class="hover:text-purple-500 cursor-pointer">
                        <Check v-if="copiedField === 'acc'" class="w-3 h-3 text-emerald-500" />
                        <Copy v-else class="w-3 h-3" />
                      </button>
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-slate-400">IFSC:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                      {{ bank_details.ifsc_code }}
                      <button @click="copyText(bank_details.ifsc_code, 'ifsc')" class="hover:text-purple-500 cursor-pointer">
                        <Check v-if="copiedField === 'ifsc'" class="w-3 h-3 text-emerald-500" />
                        <Copy v-else class="w-3 h-3" />
                      </button>
                    </span>
                  </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1.5">
                  <div class="text-[10px] uppercase font-bold text-slate-400 font-sans">Instant UPI / QR Transfer</div>
                  <div class="flex justify-between items-center">
                    <span class="text-slate-400">UPI ID:</span>
                    <span class="font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1">
                      {{ bank_details.upi_id }}
                      <button @click="copyText(bank_details.upi_id || '', 'upi')" class="hover:text-purple-500 cursor-pointer">
                        <Check v-if="copiedField === 'upi'" class="w-3 h-3 text-emerald-500" />
                        <Copy v-else class="w-3 h-3" />
                      </button>
                    </span>
                  </div>
                  <div v-if="bank_details.swift_code" class="flex justify-between">
                    <span class="text-slate-400">SWIFT:</span>
                    <span class="font-semibold">{{ bank_details.swift_code }}</span>
                  </div>
                  <p class="text-[10px] text-slate-400 font-sans mt-2">
                    Once the wire is transferred, paste the 12-digit UTR / Reference number below to lock your sprint.
                  </p>
                </div>
              </div>

              <!-- UTR Submission Form -->
              <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div v-if="wireSubmitted" class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                  <CheckCircle2 class="w-5 h-5 shrink-0" />
                  <div>
                    <span class="font-bold">Transaction Reference Logged!</span>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">{{ wireSuccessMsg }}</p>
                  </div>
                </div>

                <div v-else class="space-y-3">
                  <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                      <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Bank Transaction Ref / UTR No. *
                      </label>
                      <input
                        v-model="wireUtr"
                        type="text"
                        placeholder="e.g. 425112349876 or CMS481920"
                        class="w-full px-3 py-2 rounded-xl text-xs font-mono border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                      />
                    </div>

                    <div class="sm:w-48">
                      <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Amount Paid
                      </label>
                      <div class="px-3 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800/80 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700">
                        {{ activeMilestone?.formatted }}
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between gap-3 pt-1">
                    <p class="text-[10px] text-slate-400">
                      Our finance team verifies incoming RTGS/NEFT batches immediately upon submission.
                    </p>

                    <button
                      type="button"
                      @click="submitWirePayment"
                      :disabled="!wireUtr || isSubmittingWire"
                      class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-40 text-white text-xs font-bold transition flex items-center gap-1.5 shrink-0 cursor-pointer"
                    >
                      <span>{{ isSubmittingWire ? 'Submitting...' : 'Submit UTR Reference' }}</span>
                    </button>
                  </div>

                  <div v-if="wireError" class="text-xs text-red-500 mt-1">
                    {{ wireError }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Document Sign-off Confirmation Section -->
        <div class="mt-10 pt-8 border-t border-slate-200 dark:border-slate-800 print:border-slate-300">
          <div v-if="accepted" class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="w-6 h-6" />
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900 dark:text-white print:text-black">Proposal Formally Accepted</h4>
              <p class="text-xs text-slate-600 dark:text-slate-300 print:text-slate-600 mt-0.5">
                Thank you, {{ lead.name }}! Our {{ app_meta?.founder_title || 'lead architect' }} {{ app_meta?.founder_name || 'Founder' }} has been notified and our engineering team is preparing your Phase 1 repository and staging environment.
              </p>
            </div>
          </div>

          <div v-else class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 print:hidden">
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">Authorize & Lock Upcoming Sprint</h4>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Click below to formally accept this proposal and reserve our core engineering team.
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

      <!-- Engineering Guarantee Banner (Hidden on print) -->
      <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400 print:hidden">
        <p>{{ app_meta?.company_name || 'DigitalBuilders' }} • 100% Code Ownership • 30-Day Hypercare Warranty • Zero Vendor Lock-in</p>
        <p class="mt-1">For urgent queries, reach {{ app_meta?.founder_name || 'our team' }} directly at <a :href="'mailto:' + (app_meta?.founder_email || 'ashish@digitalbuilders.in')" class="text-purple-600 dark:text-purple-400 hover:underline">{{ app_meta?.founder_email || 'ashish@digitalbuilders.in' }}</a></p>
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
