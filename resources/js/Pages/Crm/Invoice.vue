<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import {
  Printer, ArrowLeft, Building2, CheckCircle2,
  Clock, AlertCircle, ShieldCheck, CreditCard, Sparkles, Copy
} from 'lucide-vue-next'

const props = defineProps<{
  is_public: boolean
  proposal_url?: string | null
  invoice_number: string
  issue_date: string
  due_date: string
  status_stamp: string // 'PAID IN FULL' | 'DEPOSIT RECEIVED' | 'VERIFICATION PENDING' | 'PAYMENT DUE'
  deal: {
    id: number
    title: string
    stage: string
    amount: number
    formatted_amount: string
    currency: string
    scope_summary?: string
  }
  client: {
    name: string
    company: string
    email?: string
    phone?: string
    address?: string
    gst_number?: string | null
  }
  seller: {
    name: string
    brand: string
    lead_contact: string
    email: string
    phone: string
    website: string
    gstin: string
    address: string
  }
  bank_details: {
    account_name: string
    bank_name: string
    account_no: string
    ifsc_code: string
    swift_code?: string
    upi_id?: string
  }
  financials: {
    subtotal: number
    gst_rate: number
    gst_amount: number
    total_amount: number
    paid_amount: number
    balance_due: number
    currency: string
    currency_sym: string
  }
  payments: Array<{
    id: number
    amount: number
    currency: string
    gateway: string
    status: string
    payment_method?: string
    transaction_utr?: string | null
    notes?: string | null
    paid_at?: string | null
    created_at?: string
  }>
}>()

const formatMoney = (val: number) => {
  return `${props.financials.currency_sym}${new Intl.NumberFormat('en-IN', { maximumFractionDigits: 2 }).format(val)}`
}

const printInvoice = () => {
  window.print()
}

const copyToClipboard = (text: string) => {
  navigator.clipboard.writeText(text)
}

const statusBadgeClass = computed(() => {
  switch (props.status_stamp) {
    case 'PAID IN FULL':
      return 'border-emerald-500 text-emerald-600 dark:text-emerald-400 bg-emerald-500/10'
    case 'DEPOSIT RECEIVED':
      return 'border-purple-500 text-purple-600 dark:text-purple-400 bg-purple-500/10'
    case 'VERIFICATION PENDING':
      return 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/10'
    default:
      return 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-500/10'
  }
})
</script>

<template>
  <Head :title="`Tax Invoice ${invoice_number} — ${deal.title}`" />

  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 print:bg-white print:text-black py-6 sm:py-10 transition-colors duration-300">
    <!-- Top Action Toolbar (Hidden in print) -->
    <header class="max-w-4xl mx-auto px-4 mb-6 flex items-center justify-between print:hidden">
      <div class="flex items-center gap-3">
        <a
          v-if="is_public && proposal_url"
          :href="proposal_url"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Back to Proposal</span>
        </a>
        <Link
          v-else
          href="/crm"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>CRM Cockpit</span>
        </Link>
      </div>

      <div class="flex items-center gap-2 sm:gap-3">
        <ThemeToggle />

        <button
          @click="printInvoice"
          type="button"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-sm transition cursor-pointer"
        >
          <Printer class="w-3.5 h-3.5" />
          <span>Print / PDF</span>
        </button>
      </div>
    </header>

    <!-- Main Printable Invoice Sheet -->
    <main class="max-w-4xl mx-auto px-4 sm:px-0">
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 sm:p-12 shadow-xl dark:shadow-2xl print:shadow-none print:border-none print:p-0 print:bg-white transition-colors duration-300">
        <!-- Sheet Header -->
        <div class="border-b border-slate-200 dark:border-slate-800 pb-8 flex flex-col sm:flex-row sm:items-start justify-between gap-6 print:border-slate-300">
          <div>
            <div class="flex items-center gap-3">
              <ApplicationLogo :is-link="false" />
              <div>
                <span class="text-xs font-mono uppercase tracking-wider text-purple-600 dark:text-purple-400 font-bold block">
                  {{ seller.brand }}
                </span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                  GSTIN: {{ seller.gstin }}
                </span>
              </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 max-w-sm leading-relaxed print:text-slate-600">
              {{ seller.address }}
            </p>
          </div>

          <div class="sm:text-right">
            <div class="inline-block px-3 py-1 rounded-full border text-xs font-mono font-black uppercase tracking-widest mb-2" :class="statusBadgeClass">
              {{ status_stamp }}
            </div>
            <h1 class="text-2xl font-black font-mono text-slate-900 dark:text-white print:text-black">
              {{ invoice_number }}
            </h1>
            <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5 mt-1 font-mono print:text-slate-600">
              <div>Invoice Date: <strong class="text-slate-700 dark:text-slate-200 print:text-black">{{ issue_date }}</strong></div>
              <div>Due Date: <strong class="text-slate-700 dark:text-slate-200 print:text-black">{{ due_date }}</strong></div>
            </div>
          </div>
        </div>

        <!-- Billed To & Contact Coordinates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-200 dark:border-slate-800 print:border-slate-300">
          <div>
            <div class="text-[10px] uppercase font-mono font-bold tracking-widest text-slate-400 mb-2">
              Billed To (Client / Organization)
            </div>
            <div class="text-base font-bold text-slate-900 dark:text-white print:text-black">
              {{ client.company }}
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 print:text-slate-700">
              Attn: {{ client.name }}
            </div>
            <div v-if="client.email" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              {{ client.email }}
            </div>
            <div v-if="client.address" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              {{ client.address }}
            </div>
            <div v-if="client.gst_number" class="text-xs font-mono text-purple-600 dark:text-purple-400 mt-1 font-semibold">
              Client GSTIN: {{ client.gst_number }}
            </div>
          </div>

          <div class="sm:text-right">
            <div class="text-[10px] uppercase font-mono font-bold tracking-widest text-slate-400 mb-2">
              Engineering Lead & Contact
            </div>
            <div class="text-sm font-bold text-slate-900 dark:text-white print:text-black">
              {{ seller.lead_contact }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Email: {{ seller.email }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              WhatsApp / Direct: {{ seller.phone }}
            </div>
            <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">
              Web: {{ seller.website }}
            </div>
          </div>
        </div>

        <!-- Scope & Services Table -->
        <div class="py-6 border-b border-slate-200 dark:border-slate-800 print:border-slate-300">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-mono uppercase text-[10px] tracking-wider print:border-slate-300">
                <th class="py-2.5">Item</th>
                <th class="py-2.5">Deliverable / Scope of Work</th>
                <th class="py-2.5 text-right">Fee ({{ financials.currency }})</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 print:divide-slate-200">
              <tr>
                <td class="py-4 font-mono font-bold text-slate-500">01</td>
                <td class="py-4 pr-4">
                  <div class="font-bold text-slate-900 dark:text-white text-sm print:text-black">
                    {{ deal.title }}
                  </div>
                  <div class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-relaxed print:text-slate-600">
                    {{ deal.scope_summary || 'Custom full-stack software development, automated business logic, enterprise database architecture, and production cloud infrastructure.' }}
                  </div>
                </td>
                <td class="py-4 text-right font-mono font-bold text-slate-900 dark:text-white print:text-black whitespace-nowrap">
                  {{ formatMoney(financials.subtotal) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Financial Breakdown -->
        <div class="py-6 border-b border-slate-200 dark:border-slate-800 flex justify-end print:border-slate-300">
          <div class="w-full sm:w-72 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600 dark:text-slate-400 print:text-slate-700">
              <span>Subtotal Fee:</span>
              <span class="font-mono font-semibold">{{ formatMoney(financials.subtotal) }}</span>
            </div>

            <div v-if="financials.gst_rate > 0" class="flex justify-between text-slate-600 dark:text-slate-400 print:text-slate-700">
              <span>GST (18% IGST / CGST+SGST):</span>
              <span class="font-mono font-semibold">{{ formatMoney(financials.gst_amount) }}</span>
            </div>

            <div v-else class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400 italic">
              <span>Export of Services:</span>
              <span>0% GST (Zero Rated)</span>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-800 pt-2 flex justify-between font-bold text-sm text-slate-900 dark:text-white print:text-black">
              <span>Total Contract Value:</span>
              <span class="font-mono">{{ formatMoney(financials.total_amount) }}</span>
            </div>

            <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-medium">
              <span>Total Credited Payments:</span>
              <span class="font-mono">- {{ formatMoney(financials.paid_amount) }}</span>
            </div>

            <div class="border-t-2 border-slate-900 dark:border-slate-100 pt-2 flex justify-between font-black text-base text-slate-900 dark:text-white print:text-black">
              <span>Balance Due:</span>
              <span class="font-mono text-purple-600 dark:text-purple-400 print:text-black">
                {{ formatMoney(financials.balance_due) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Recorded Payments Ledger (if any payments logged) -->
        <div v-if="payments && payments.length > 0" class="py-6 border-b border-slate-200 dark:border-slate-800 print:border-slate-300">
          <div class="text-[10px] uppercase font-mono font-bold tracking-widest text-slate-400 mb-3">
            Payment Records & Wire Receipts
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="text-slate-400 font-mono uppercase text-[10px] border-b border-slate-200 dark:border-slate-800">
                  <th class="py-2">Date</th>
                  <th class="py-2">Method</th>
                  <th class="py-2">UTR / Transaction Ref</th>
                  <th class="py-2">Status</th>
                  <th class="py-2 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono text-[11px]">
                <tr v-for="p in payments" :key="p.id">
                  <td class="py-2 text-slate-500">{{ p.paid_at || p.created_at }}</td>
                  <td class="py-2 uppercase text-slate-700 dark:text-slate-300">{{ p.gateway }} ({{ p.payment_method || 'Online' }})</td>
                  <td class="py-2 text-slate-900 dark:text-white font-semibold">{{ p.transaction_utr || '—' }}</td>
                  <td class="py-2">
                    <span
                      v-if="p.status === 'paid'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/30"
                    >
                      VERIFIED
                    </span>
                    <span
                      v-else-if="p.status === 'pending_verification'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/30"
                    >
                      PENDING VERIFICATION
                    </span>
                    <span
                      v-else
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-600 border border-slate-500/30"
                    >
                      {{ p.status }}
                    </span>
                  </td>
                  <td class="py-2 text-right font-bold text-slate-900 dark:text-white">
                    {{ financials.currency_sym }}{{ new Intl.NumberFormat('en-IN').format(p.amount) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Corporate Banking & Remittance Coordinates -->
        <div class="py-8 grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-950/60 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 mt-6 print:bg-white print:border print:border-slate-300">
          <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white mb-2">
              <Building2 class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              <span>Corporate Bank Coordinates (RTGS / NEFT / IMPS)</span>
            </div>
            <div class="space-y-1 text-xs font-mono text-slate-600 dark:text-slate-300 print:text-slate-800">
              <div class="flex justify-between">
                <span class="text-slate-400">Account Name:</span>
                <span class="font-bold">{{ bank_details.account_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Bank Name:</span>
                <span class="font-semibold">{{ bank_details.bank_name }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-400">Account No:</span>
                <span class="font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                  {{ bank_details.account_no }}
                  <button @click="copyToClipboard(bank_details.account_no)" class="hover:text-purple-500 print:hidden cursor-pointer">
                    <Copy class="w-3 h-3" />
                  </button>
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">IFSC Code:</span>
                <span class="font-bold">{{ bank_details.ifsc_code }}</span>
              </div>
              <div v-if="bank_details.swift_code" class="flex justify-between">
                <span class="text-slate-400">SWIFT / BIC:</span>
                <span>{{ bank_details.swift_code }}</span>
              </div>
            </div>
          </div>

          <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white mb-2">
              <CreditCard class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              <span>UPI & Instant QR Transfer</span>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 print:border-slate-300">
              <div class="text-[11px] text-slate-500 dark:text-slate-400">DigitalBuilders Direct VPA:</div>
              <div class="text-sm font-mono font-bold text-slate-900 dark:text-white mt-0.5 flex items-center justify-between">
                <span>{{ bank_details.upi_id }}</span>
                <button
                  @click="copyToClipboard(bank_details.upi_id || '')"
                  class="text-xs px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 hover:bg-purple-500/20 font-sans print:hidden cursor-pointer"
                >
                  Copy VPA
                </button>
              </div>
              <p class="text-[10px] text-slate-400 mt-1.5 leading-tight">
                Supports Google Pay, PhonePe, Paytm, and all institutional BHIM UPI apps.
              </p>
            </div>
          </div>
        </div>

        <!-- Authorized Signature & Guarantees -->
        <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 print:border-slate-300">
          <div class="text-[11px] text-slate-500 dark:text-slate-400 max-w-sm space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300 print:text-black">
              <ShieldCheck class="w-3.5 h-3.5 text-purple-600" />
              <span>{{ seller.brand || 'Engineering' }} Guarantee</span>
            </div>
            <p>100% intellectual property handover upon milestone completion. Includes 30 days of hypercare warranty and zero vendor lock-in.</p>
          </div>

          <div class="text-left sm:text-right font-mono">
            <div class="text-sm font-bold text-slate-900 dark:text-white print:text-black">
              {{ seller.lead_contact || 'Principal Architect' }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
              Founder & Lead Architect
            </div>
            <div class="text-[10px] text-purple-600 dark:text-purple-400 font-bold uppercase tracking-wider mt-0.5">
              {{ seller.name }}
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
