<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import {
  TrendingUp, Users, DollarSign, AlertCircle, Plus, Upload, Search,
  Filter, LayoutGrid, List, MessageSquare, LogOut, CheckCircle2,
  Sparkles, ExternalLink, ShieldAlert, ArrowRight, Building2, Check, Clock, X
} from 'lucide-vue-next'

import KanbanColumn from '@/Components/Crm/KanbanColumn.vue'
import LeadDrawer from '@/Components/Crm/LeadDrawer.vue'
import ScriptGeneratorModal from '@/Components/Crm/ScriptGeneratorModal.vue'
import QuickAddModal from '@/Components/Crm/QuickAddModal.vue'
import CsvImportModal from '@/Components/Crm/CsvImportModal.vue'
import ProposalModal from '@/Components/Crm/ProposalModal.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'

const props = defineProps<{
  telemetry: {
    total_pipeline_inr: number
    total_pipeline_usd: number
    won_revenue_inr: number
    won_revenue_usd: number
    active_deals_count: number
    win_rate: number
    overdue_count: number
  }
  action_queue: any[]
  stages: Record<string, any>
  all_deals: any[]
  filters: {
    segment: string
    currency: string
    search: string
    view: string
  }
}>()

// State
const searchQuery = ref(props.filters.search || '')
const selectedSegment = ref(props.filters.segment || 'all')
const selectedCurrency = ref(props.filters.currency || 'all')
const viewMode = ref<'kanban' | 'stream' | 'table'>(props.filters.view === 'table' ? 'table' : 'kanban')
const showMobileSearch = ref(false)

// Flat deals array for mobile and stream triage
const flatDeals = computed(() => {
  const list: any[] = []
  for (const stageKey in props.stages) {
    const stage = props.stages[stageKey]
    const deals = stage?.deals || []
    deals.forEach((d: any) => {
      list.push({
        ...d,
        stage_key: stageKey,
        stage_name: stage?.name || stageKey,
      })
    })
  }
  return list
})

const openWhatsApp = (url?: string) => {
  if (url) window.open(url, '_blank')
}

// Action Queue Dismissed in current session
const dismissedQueueIds = ref<number[]>([])
const activeQueue = computed(() => {
  return props.action_queue.filter(item => !dismissedQueueIds.value.includes(item.id))
})

// Modals & Drawer State
const selectedDealId = ref<number | null>(null)
const selectedLeadForDrawer = ref<number | null>(null)
const showDrawer = ref(false)

const showQuickAdd = ref(false)
const quickAddStage = ref('new')

const showCsvImport = ref(false)

const showScriptModal = ref(false)
const activeLeadForScript = ref<any>(null)
const scriptInitialTouch = ref(1)

const showProposalModal = ref(false)
const selectedDealForProposal = ref<number | null>(null)

const openProposalModal = (dealId: number) => {
  selectedDealForProposal.value = dealId
  showProposalModal.value = true
}

// Floating Executive Toast Notification
const toast = ref<{ message: string; id: number } | null>(null)
const triggerToast = (msg: string) => {
  const toastId = Date.now()
  toast.value = { message: msg, id: toastId }
  setTimeout(() => {
    if (toast.value?.id === toastId) {
      toast.value = null
    }
  }, 3500)
}

const segments = [
  { key: 'all', label: 'All Niches' },
  { key: 'manufacturer', label: '🏭 Industrial Manufacturers' },
  { key: 'retail', label: '🛍️ Retail & D2C' },
  { key: 'clinic', label: '🩺 Clinics & Telehealth' },
  { key: 'coaching', label: '🎓 Coaching & Academies' },
  { key: 'startup', label: '🚀 Tech Startups & MVPs' },
  { key: 'international', label: '🌐 International USD' },
]

const applyFilters = () => {
  router.get(
    '/crm',
    {
      segment: selectedSegment.value,
      currency: selectedCurrency.value,
      search: searchQuery.value,
      view: viewMode.value,
    },
    { preserveState: true, replace: true }
  )
}

const onSegmentSelect = (seg: string) => {
  selectedSegment.value = seg
  applyFilters()
}

const onCurrencySelect = (curr: string) => {
  selectedCurrency.value = curr
  applyFilters()
}

const onSearchInput = () => {
  applyFilters()
}

const openLeadDetails = (dealId: number) => {
  // Find lead ID from deal
  for (const stKey in props.stages) {
    const d = props.stages[stKey].deals.find((deal: any) => deal.id === dealId)
    if (d?.lead?.id) {
      selectedLeadForDrawer.value = d.lead.id
      showDrawer.value = true
      return
    }
  }
}

const openQuickTouch = (dealOrLead: any, touchNum?: number) => {
  const leadObj = dealOrLead.lead || dealOrLead
  activeLeadForScript.value = leadObj
  scriptInitialTouch.value = touchNum || (leadObj.touchpoint_count ? leadObj.touchpoint_count + 1 : 1)
  showScriptModal.value = true
}

const onMoveDealStage = async (dealId: number, targetStage: string) => {
  try {
    const res = await fetch(`/crm/deals/${dealId}/stage`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ stage: targetStage }),
    })
    const data = await res.json()
    if (data.success) {
      triggerToast(`Deal moved to ${targetStage.replace('_', ' ').toUpperCase()}`)
      router.reload()
    }
  } catch (e) {
    console.error('Failed to move stage', e)
  }
}

const onTouchLogged = (result?: any) => {
  triggerToast(`Touch logged via ${result?.channel || 'WhatsApp'} for ${result?.leadName || 'prospect'}!`)
  router.reload()
}

const dismissQueueItem = (leadId: number) => {
  dismissedQueueIds.value.push(leadId)
  triggerToast('Marked completed in daily queue')
}

const logout = () => {
  router.post('/crm/logout')
}

const getStageBadgeClass = (stage: string) => {
  switch (stage) {
    case 'new': return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
    case 'contacted': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
    case 'qualified': return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20'
    case 'discovery_done': return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
    case 'proposal_sent': return 'bg-orange-500/10 text-orange-400 border-orange-500/20'
    case 'negotiation': return 'bg-purple-500/10 text-purple-400 border-purple-500/20'
    case 'closed_won': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
    case 'closed_lost': return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
    default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20'
  }
}
</script>

<template>
  <Head title="Executive Sales Cockpit — DigitalBuilders" />

  <div class="crm-cockpit min-h-screen bg-slate-50 dark:bg-[#070b14] text-slate-900 dark:text-slate-100 flex flex-col font-sans selection:bg-sky-500 selection:text-white transition-colors duration-200">
    <!-- Top Executive Nav Header -->
    <header class="h-16 px-3 sm:px-6 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md sticky top-0 z-30 transition-colors duration-200">
      <div class="max-w-[1720px] mx-auto w-full h-full flex items-center justify-between gap-2 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-6 min-w-0">
        <Link href="/crm" class="flex items-center gap-2.5 group shrink-0">
          <img
            src="/images/db-logo.png"
            alt="DigitalBuilders Logo"
            class="h-9 w-9 sm:h-10 sm:w-10 object-contain flex-shrink-0 transition-transform duration-300 group-hover:scale-105 filter-none dark:drop-shadow-[0_0_12px_rgba(56,189,248,0.35)]"
          />
          <div class="hidden xs:block">
            <div class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
              Digital<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-indigo-600 dark:from-cyan-400 dark:to-indigo-300">Builders</span>
              <span class="text-[9px] sm:text-[10px] px-1.5 py-0.2 rounded bg-sky-500/10 text-sky-700 dark:text-cyan-400 font-bold border border-sky-500/20 dark:border-cyan-500/20">CRM</span>
            </div>
            <div class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 hidden sm:block">Founder Sales Engine</div>
          </div>
        </Link>

        <!-- Live Search Bar (Desktop / Tablet) -->
        <div class="relative w-44 md:w-64 lg:w-80 hidden sm:block">
          <Search class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            type="text"
            placeholder="Search prospect, factory, phone..."
            class="w-full pl-9 pr-3 py-1.5 bg-slate-100 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-sky-500 focus:border-sky-500 transition"
          />
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
        <!-- Mobile Search Toggle Button -->
        <button
          type="button"
          @click="showMobileSearch = !showMobileSearch"
          class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-850 transition sm:hidden cursor-pointer"
          title="Toggle Search"
        >
          <Search class="w-4 h-4" />
        </button>

        <!-- Quick Add Lead Button -->
        <button
          type="button"
          @click="showQuickAdd = true"
          class="px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-1.5 transition cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5" />
          <span class="hidden xs:inline">Ingest Lead</span>
          <span class="xs:hidden">Lead</span>
        </button>

        <!-- CSV Bulk Import Button -->
        <button
          type="button"
          @click="showCsvImport = true"
          class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 dark:bg-slate-850 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700/60 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer hidden md:flex"
        >
          <Upload class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
          <span>Import CSV</span>
        </button>

        <!-- Universal Theme Toggle in CRM Header! -->
        <ThemeToggle size="sm" />

        <!-- View Public Site Link -->
        <a
          href="/"
          target="_blank"
          class="p-1.5 sm:p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-850 transition"
          title="Open Public Website"
        >
          <ExternalLink class="w-4 h-4" />
        </a>

        <!-- Logout Button -->
        <button
          type="button"
          @click="logout"
          title="Sign out of CRM"
          class="p-1.5 sm:p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-500/10 transition cursor-pointer"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
      </div>
    </header>

    <!-- Mobile Collapsible Search Bar -->
    <div
      v-if="showMobileSearch"
      class="sm:hidden px-4 py-2.5 bg-white/95 dark:bg-slate-950/95 border-b border-slate-200 dark:border-slate-800/80 sticky top-16 z-20 transition-all shadow-md"
    >
      <div class="relative w-full">
        <Search class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input
          v-model="searchQuery"
          @input="onSearchInput"
          type="text"
          placeholder="Search prospect, factory, phone..."
          class="w-full pl-9 pr-8 py-2 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs text-slate-900 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-sky-500 focus:border-sky-500"
        />
        <button
          v-if="searchQuery"
          @click="searchQuery = ''; applyFilters()"
          class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 dark:hover:text-white p-1"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Main Workspace -->
    <main class="flex-1 max-w-[1720px] mx-auto w-full p-3.5 sm:p-5 md:p-6 space-y-5 sm:space-y-6 overflow-x-hidden">
      <!-- 1. Executive Telemetry KPI Strip -->
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">
        <!-- Active Pipeline INR -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 hover:border-slate-300 dark:hover:border-slate-700 border border-slate-200/80 dark:border-slate-800/80 shadow-sm dark:shadow-md transition-colors flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] sm:text-xs font-semibold mb-1">
            <span class="truncate">Domestic Pipeline</span>
            <span class="text-[10px] sm:text-xs px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold shrink-0">₹ INR</span>
          </div>
          <div class="text-lg sm:text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight truncate font-mono tabular-nums">
            ₹{{ Number(telemetry.total_pipeline_inr).toLocaleString('en-IN') }}
          </div>
          <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">Open Indian high-ticket deals</div>
        </div>

        <!-- Active Pipeline USD -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 hover:border-slate-300 dark:hover:border-slate-700 border border-slate-200/80 dark:border-slate-800/80 shadow-sm dark:shadow-md transition-colors flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] sm:text-xs font-semibold mb-1">
            <span class="truncate">International Deals</span>
            <span class="text-[10px] sm:text-xs px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold shrink-0">$ USD</span>
          </div>
          <div class="text-lg sm:text-xl md:text-2xl font-extrabold text-purple-600 dark:text-purple-300 tracking-tight truncate font-mono tabular-nums">
            ${{ Number(telemetry.total_pipeline_usd).toLocaleString() }}
          </div>
          <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">US & Gulf architecture retainers</div>
        </div>

        <!-- Won Closed Revenue -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 hover:border-slate-300 dark:hover:border-slate-700 border border-slate-200/80 dark:border-slate-800/80 shadow-sm dark:shadow-md transition-colors flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] sm:text-xs font-semibold mb-1">
            <span class="truncate">Closed Won Revenue</span>
            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500 dark:text-emerald-400 shrink-0" />
          </div>
          <div class="text-lg sm:text-xl md:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight truncate font-mono tabular-nums">
            ₹{{ Number(telemetry.won_revenue_inr).toLocaleString('en-IN') }}
          </div>
          <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">Win Rate: {{ telemetry.win_rate }}%</div>
        </div>

        <!-- Active Deals Count -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 hover:border-slate-300 dark:hover:border-slate-700 border border-slate-200/80 dark:border-slate-800/80 shadow-sm dark:shadow-md transition-colors flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] sm:text-xs font-semibold mb-1">
            <span class="truncate">Active Pipeline</span>
            <Users class="w-3.5 h-3.5 text-sky-600 dark:text-cyan-400 shrink-0" />
          </div>
          <div class="text-lg sm:text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight truncate font-mono tabular-nums">
            {{ telemetry.active_deals_count }} Deals
          </div>
          <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">Across 7 sales stages</div>
        </div>

        <!-- Overdue Action Cadence Alerts -->
        <div class="col-span-2 md:col-span-1 xl:col-span-1 p-3.5 sm:p-4 rounded-2xl border shadow-sm dark:shadow-md transition-colors flex flex-col justify-between"
          :class="telemetry.overdue_count > 0 ? 'border-rose-300 dark:border-rose-500/30 bg-rose-50/80 dark:bg-rose-950/10' : 'bg-white dark:bg-slate-900/80 border-slate-200/80 dark:border-slate-800/80'"
        >
          <div class="flex items-center justify-between text-[11px] sm:text-xs font-semibold mb-1"
            :class="telemetry.overdue_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400'"
          >
            <span class="truncate">Follow-up Cadence</span>
            <AlertCircle class="w-3.5 h-3.5 shrink-0" />
          </div>
          <div class="text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight truncate font-mono tabular-nums"
            :class="telemetry.overdue_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-slate-200'"
          >
            {{ telemetry.overdue_count }} Due Today
          </div>
          <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">Non-negotiable 60m outreach</div>
        </div>
      </div>

      <!-- 2. Daily 60-Minute Action Queue (Horizontal Quick-Action Strip) -->
      <div v-if="action_queue.length > 0" class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-sky-50/80 via-white to-purple-50/80 dark:from-cyan-950/20 dark:via-slate-900/90 dark:to-purple-950/20 border border-sky-200 dark:border-cyan-500/20 shadow-sm dark:shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
          <div class="flex items-center gap-2 sm:gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-sky-500 dark:bg-cyan-400 animate-pulse shrink-0"></span>
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider truncate">
              Daily 60-Minute Founder Action Queue
            </h3>
            <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-700 dark:text-cyan-400 font-bold border border-sky-500/20 dark:border-cyan-500/20 shrink-0">
              {{ activeQueue.length }} Pending
            </span>
          </div>
          <span class="text-[11px] text-slate-500 dark:text-slate-400 hidden lg:inline">
            Rule: 100 conversations → 20 discovery calls → 2 closed projects (~₹5L MRR)
          </span>
        </div>

        <div v-if="activeQueue.length === 0" class="p-4 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
          🎉 All actions completed for today! Great work founder.
        </div>

        <div v-else class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar snap-x snap-mandatory">
          <div
            v-for="item in activeQueue"
            :key="item.id"
            class="flex-shrink-0 w-[82vw] max-w-[300px] sm:w-76 p-3.5 rounded-xl bg-white dark:bg-slate-950/90 border border-slate-200 dark:border-slate-800 hover:border-sky-400 dark:hover:border-cyan-500/40 shadow-sm transition flex flex-col justify-between snap-start"
          >
            <div>
              <div class="flex items-center justify-between gap-1 mb-1">
                <span class="text-xs font-bold text-slate-900 dark:text-white line-clamp-1">{{ item.name }}</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 shrink-0">
                  {{ item.score }} pts
                </span>
              </div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">{{ item.company }}</div>
              <div class="text-[10px] text-sky-700 dark:text-cyan-300 font-medium mt-1 line-clamp-1">
                👉 {{ item.next_action_note }}
              </div>
            </div>

            <div class="pt-2.5 mt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
              <span class="text-xs font-extrabold text-slate-900 dark:text-slate-200 font-mono tabular-nums">{{ item.deal_value }}</span>
              <div class="flex items-center gap-1.5">
                <button
                  type="button"
                  @click="openQuickTouch(item)"
                  class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer"
                >
                  <MessageSquare class="w-3 h-3" />
                  <span>Touch</span>
                </button>
                <button
                  type="button"
                  @click="dismissQueueItem(item.id)"
                  title="Mark action completed"
                  class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition cursor-pointer"
                >
                  <Check class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Filter Tabs & View Toggle Bar -->
      <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pt-1">
        <!-- Segment Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto max-w-full pb-1.5 custom-scrollbar shrink">
          <button
            v-for="seg in segments"
            :key="seg.key"
            type="button"
            @click="onSegmentSelect(seg.key)"
            class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer shrink-0"
            :class="selectedSegment === seg.key
              ? 'bg-sky-500/10 dark:bg-cyan-500/20 text-sky-700 dark:text-cyan-300 border border-sky-500/30 dark:border-cyan-500/40 shadow-sm'
              : 'bg-white dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-850 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 border border-slate-200 dark:border-slate-800'"
          >
            {{ seg.label }}
          </button>
        </div>

        <!-- Currency & View Switcher -->
        <div class="flex items-center justify-between md:justify-end gap-2 shrink-0">
          <!-- Currency filter -->
          <div class="flex rounded-xl bg-slate-200/70 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-0.5 text-xs font-bold">
            <button
              @click="onCurrencySelect('all')"
              class="px-2 sm:px-2.5 py-1 rounded-lg transition cursor-pointer"
              :class="selectedCurrency === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
            >
              All
            </button>
            <button
              @click="onCurrencySelect('inr')"
              class="px-2 sm:px-2.5 py-1 rounded-lg transition cursor-pointer"
              :class="selectedCurrency === 'inr' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
            >
              ₹ INR
            </button>
            <button
              @click="onCurrencySelect('usd')"
              class="px-2 sm:px-2.5 py-1 rounded-lg transition cursor-pointer"
              :class="selectedCurrency === 'usd' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
            >
              $ USD
            </button>
          </div>

          <!-- View Toggle -->
          <div class="flex rounded-xl bg-slate-200/70 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-0.5 text-xs">
            <button
              @click="viewMode = 'kanban'"
              title="Kanban Board View"
              class="p-1.5 rounded-lg transition cursor-pointer"
              :class="viewMode === 'kanban' ? 'bg-white dark:bg-slate-800 text-sky-600 dark:text-cyan-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
            >
              <LayoutGrid class="w-4 h-4" />
            </button>
            <button
              @click="viewMode = 'stream'"
              title="Card Stream View"
              class="p-1.5 rounded-lg transition cursor-pointer"
              :class="viewMode === 'stream' ? 'bg-white dark:bg-slate-800 text-sky-600 dark:text-cyan-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
            >
              <Clock class="w-4 h-4" />
            </button>
            <button
              @click="viewMode = 'table'"
              title="Table View"
              class="p-1.5 rounded-lg transition cursor-pointer"
              :class="viewMode === 'table' ? 'bg-white dark:bg-slate-800 text-sky-600 dark:text-cyan-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
            >
              <List class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Fast Mode Switcher (Kanban Swipe vs List Stream) -->
      <div class="sm:hidden flex items-center justify-between gap-2 p-1 bg-slate-200/80 dark:bg-slate-900 border border-slate-300/80 dark:border-slate-800 rounded-xl text-xs font-bold">
        <button
          type="button"
          @click="viewMode = 'kanban'"
          class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition cursor-pointer min-h-[44px]"
          :class="viewMode === 'kanban' ? 'bg-white dark:bg-slate-800 text-sky-600 dark:text-cyan-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
        >
          <LayoutGrid class="w-4 h-4" />
          <span>Kanban Swipe</span>
        </button>
        <button
          type="button"
          @click="viewMode = 'stream'"
          class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition cursor-pointer min-h-[44px]"
          :class="viewMode === 'stream' ? 'bg-white dark:bg-slate-800 text-sky-600 dark:text-cyan-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
        >
          <List class="w-4 h-4" />
          <span>List Stream ({{ flatDeals.length }})</span>
        </button>
      </div>

      <!-- 4. Main CRM View: Kanban Board, Stream View, or High-Density Table -->
      <!-- A. Kanban View -->
      <div v-if="viewMode === 'kanban'" class="space-y-2.5">
        <!-- Mobile Stage Indicator Pills -->
        <div class="sm:hidden flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-[11px] font-bold">
          <span class="text-slate-400 text-[10px] uppercase tracking-wider shrink-0 mr-1">Stages:</span>
          <span
            v-for="(stVal, stK) in stages"
            :key="stK"
            class="px-2.5 py-1 rounded-lg shrink-0 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-1"
          >
            <span>{{ stVal.name }}</span>
            <span class="font-mono text-[10px] text-sky-600 dark:text-cyan-400 font-bold">({{ stVal.deals?.length || 0 }})</span>
          </span>
        </div>

        <div class="flex gap-3 sm:gap-4 overflow-x-auto pb-4 custom-scrollbar snap-x sm:snap-none">
          <KanbanColumn
            v-for="(stageData, stageKey) in stages"
            :key="stageKey"
            :stage-key="String(stageKey)"
            :stage-data="stageData"
            @move-stage="onMoveDealStage"
            @select-deal="openLeadDetails"
            @quick-touch="openQuickTouch"
            @proposal="openProposalModal"
            @add-deal="(st) => { quickAddStage = st; showQuickAdd = true }"
          />
        </div>
      </div>

      <!-- B. Stream Card View (Mobile & Triage Optimized) -->
      <div v-else-if="viewMode === 'stream'" class="space-y-3">
        <div v-if="flatDeals.length === 0" class="p-8 text-center bg-white dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-500">
          No deals matching current filters.
        </div>
        <div
          v-for="deal in flatDeals"
          :key="deal.id"
          class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800/90 hover:border-sky-400 dark:hover:border-cyan-500/40 shadow-sm transition flex flex-col gap-3"
        >
          <div class="flex items-start justify-between gap-2 cursor-pointer" @click="openLeadDetails(deal.id)">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider border"
                  :class="getStageBadgeClass(deal.stage_key)"
                >
                  {{ deal.stage_name }}
                </span>
                <span v-if="deal.is_overdue" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                  Overdue
                </span>
              </div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ deal.title }}</h4>
              <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ deal.lead?.company || deal.organization?.name || 'Direct Contact' }}</p>
            </div>
            <div class="text-right shrink-0">
              <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100 font-mono tabular-nums db-price">
                {{ deal.formatted_value || (deal.currency === 'USD' ? `$${Number(deal.value).toLocaleString()}` : `₹${Number(deal.value).toLocaleString('en-IN')}`) }}
              </div>
              <div class="text-[10px] text-slate-400 font-medium font-mono tabular-nums">{{ deal.probability }}% win prob</div>
            </div>
          </div>

          <!-- Quick Action Bar (Touch-Friendly 44px+ Buttons) -->
          <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <button
                v-if="deal.lead?.whatsapp_url"
                type="button"
                @click.stop="openWhatsApp(deal.lead.whatsapp_url)"
                class="min-h-[44px] px-3.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
              >
                <MessageSquare class="w-4 h-4" />
                <span>WhatsApp</span>
              </button>
              <button
                type="button"
                @click.stop="openProposalModal(deal.id)"
                class="min-h-[44px] px-3.5 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 text-sky-700 dark:text-cyan-400 border border-sky-500/30 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
              >
                <Sparkles class="w-4 h-4" />
                <span>Proposal</span>
              </button>
            </div>

            <button
              type="button"
              @click.stop="openLeadDetails(deal.id)"
              class="min-h-[44px] px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold flex items-center gap-1 transition cursor-pointer"
            >
              <span>Details</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- B. Table View -->
      <div v-else class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/70 overflow-hidden shadow-sm dark:shadow-xl">
        <div class="overflow-x-auto custom-scrollbar">
          <table class="w-full text-left text-xs min-w-[760px]">
            <thead class="bg-slate-50 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
              <tr>
                <th class="p-3.5">Deal & Company</th>
                <th class="p-3.5">Niche Segment</th>
                <th class="p-3.5">Pipeline Stage</th>
                <th class="p-3.5">Target Value</th>
                <th class="p-3.5">Probability</th>
                <th class="p-3.5">Cadence</th>
                <th class="p-3.5">Next Action</th>
                <th class="p-3.5 text-right">Quick Outreach</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-700 dark:text-slate-200">
              <tr
                v-for="deal in all_deals"
                :key="deal.id"
                @click="openLeadDetails(deal.id)"
                class="hover:bg-slate-50 dark:hover:bg-slate-850/60 transition cursor-pointer"
              >
                <td class="p-3.5">
                  <div class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ deal.title }}</div>
                  <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                    <Building2 class="w-3 h-3 text-slate-400 dark:text-slate-500" />
                    <span>{{ deal.lead?.company || deal.organization?.name || 'Direct' }}</span>
                  </div>
                </td>
                <td class="p-3.5">
                  <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    {{ deal.lead?.segment || 'General' }}
                  </span>
                </td>
                <td class="p-3.5">
                  <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border" :class="getStageBadgeClass(deal.stage)">
                    {{ deal.stage.replace('_', ' ') }}
                  </span>
                </td>
                <td class="p-3.5 font-extrabold text-slate-900 dark:text-white font-mono tabular-nums">
                  {{ deal.formatted_amount }}
                </td>
                <td class="p-3.5 text-slate-500 dark:text-slate-400 font-mono">
                  {{ deal.probability }}%
                </td>
                <td class="p-3.5 text-slate-600 dark:text-slate-300">
                  <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[11px] font-mono border border-slate-200 dark:border-slate-700">
                    T{{ deal.lead?.touchpoint_count || 0 }}/5
                  </span>
                </td>
                <td class="p-3.5 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                  <div class="text-[11px] truncate">{{ deal.lead?.next_action_note || '—' }}</div>
                </td>
                <td class="p-3.5 text-right space-x-1.5" @click.stop>
                  <button
                    type="button"
                    @click="openProposalModal(deal.id)"
                    class="px-2.5 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-400 border border-purple-500/20 font-bold text-xs transition cursor-pointer"
                    title="Open Scope Proposal"
                  >
                    Proposal
                  </button>
                  <button
                    type="button"
                    @click="openQuickTouch(deal)"
                    class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 font-bold text-xs transition cursor-pointer"
                  >
                    Touch
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Global Floating Executive Toast Notification -->
    <div
      v-if="toast"
      class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-slate-900/95 border border-cyan-500/40 text-slate-100 text-xs shadow-2xl shadow-cyan-950/40 backdrop-blur-md animate-in fade-in slide-in-from-bottom-2 duration-200"
    >
      <div class="w-2 h-2 rounded-full bg-cyan-400 animate-ping" />
      <span class="font-semibold text-white">{{ toast.message }}</span>
      <button @click="toast = null" class="text-slate-400 hover:text-white ml-2">
        <X class="w-3.5 h-3.5" />
      </button>
    </div>

    <!-- Modals & Drawers -->
    <LeadDrawer
      :show="showDrawer"
      :lead-id="selectedLeadForDrawer"
      @close="showDrawer = false"
      @updated="(msg) => { triggerToast(msg || 'Updated successfully'); router.reload(); }"
      @open-outreach="openQuickTouch"
      @open-proposal="openProposalModal"
    />

    <ProposalModal
      :show="showProposalModal"
      :deal-id="selectedDealForProposal"
      @close="showProposalModal = false"
      @proposal-sent="(data) => { triggerToast('Proposal dispatched! Moved to Proposal Sent'); router.reload(); }"
    />

    <ScriptGeneratorModal
      :show="showScriptModal"
      :lead="activeLeadForScript"
      :initial-touch="scriptInitialTouch"
      @close="showScriptModal = false"
      @touch-logged="onTouchLogged"
    />

    <QuickAddModal
      :show="showQuickAdd"
      :default-stage="quickAddStage"
      @close="showQuickAdd = false"
      @created="() => { triggerToast('New lead added to pipeline!'); router.reload(); }"
    />

    <CsvImportModal
      :show="showCsvImport"
      @close="showCsvImport = false"
      @imported="() => { triggerToast('CSV imported into sales pipeline!'); router.reload(); }"
    />
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(15, 23, 42, 0.6);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(51, 65, 85, 0.8);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(100, 116, 139, 1);
}
</style>
