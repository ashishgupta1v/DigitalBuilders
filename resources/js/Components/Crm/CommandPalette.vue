<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import {
  Search, Globe, Kanban, Users, Send, Sparkles, Plus, RefreshCw,
  Download, ShieldCheck, SunMoon, ExternalLink, ArrowRight, CornerDownLeft, X,
  Command, Layers, CheckCircle2
} from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  rfps: any[]
  deals: any[]
  leads: any[]
  activeTab?: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'selectRfp', rfp: any): void
  (e: 'selectDeal', deal: any): void
  (e: 'selectLead', leadId: number): void
  (e: 'action', actionKey: string): void
  (e: 'switchTab', tabKey: string): void
}>()

const searchInput = ref<HTMLInputElement | null>(null)
const query = ref('')
const selectedIndex = ref(0)

const systemActions = [
  { id: 'act-new-lead', type: 'action', title: 'Add New Lead / Prospect', shortcut: 'N', icon: Plus, action: 'new-lead', category: 'Actions' },
  { id: 'act-sync-feeds', type: 'action', title: 'Sync Live Feeds (15+ Sources)', shortcut: 'S', icon: RefreshCw, action: 'sync-feeds', category: 'Actions' },
  { id: 'act-tab-hunter', type: 'tab', title: 'Jump to RFP Hunter', shortcut: '1', icon: Globe, tab: 'hunter', category: 'Navigation' },
  { id: 'act-tab-deals', type: 'tab', title: 'Jump to Deals Pipeline', shortcut: '2', icon: Kanban, tab: 'deals', category: 'Navigation' },
  { id: 'act-tab-leads', type: 'tab', title: 'Jump to Leads Directory', shortcut: '3', icon: Users, tab: 'leads', category: 'Navigation' },
  { id: 'act-tab-campaigns', type: 'tab', title: 'Jump to Outbound Campaigns', shortcut: '4', icon: Send, tab: 'campaigns', category: 'Navigation' },
  { id: 'act-tab-studio', type: 'tab', title: 'Jump to AI Proposal Studio', shortcut: '5', icon: Sparkles, tab: 'studio', category: 'Navigation' },
  { id: 'act-theme-toggle', type: 'action', title: 'Toggle Theme (Dark / Light)', shortcut: 'T', icon: SunMoon, action: 'toggle-theme', category: 'Preferences' },
  { id: 'act-security', type: 'action', title: 'Founder Security & Credentials', shortcut: 'sec', icon: ShieldCheck, action: 'security', category: 'Preferences' },
  { id: 'act-export', type: 'action', title: 'Export Leads to CSV', shortcut: 'exp', icon: Download, action: 'export-csv', category: 'Data' },
]

const filteredItems = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) {
    return systemActions.slice(0, 8)
  }

  const results: any[] = []

  // 1. System Actions
  const matchedActions = systemActions.filter(a => 
    a.title.toLowerCase().includes(q) || 
    (a.action && a.action.toLowerCase().includes(q)) ||
    (a.shortcut && a.shortcut.toLowerCase() === q)
  )
  results.push(...matchedActions)

  // 2. RFP Market Requirements (max 6)
  if (props.rfps?.length) {
    const matchedRfps = props.rfps
      .filter(r => 
        (r.title && r.title.toLowerCase().includes(q)) ||
        (r.source && r.source.toLowerCase().includes(q)) ||
        (r.budget && r.budget.toLowerCase().includes(q)) ||
        (r.contact_company && r.contact_company.toLowerCase().includes(q))
      )
      .slice(0, 6)
      .map(r => ({
        id: `rfp-${r.id}`,
        type: 'rfp',
        title: r.title,
        subtitle: `${r.source.toUpperCase()} • ${r.budget || 'Custom'} • Match ${r.relevance_score}%`,
        icon: Globe,
        raw: r,
        category: 'RFP Opportunities',
      }))
    results.push(...matchedRfps)
  }

  // 3. Deals Pipeline (max 5)
  if (props.deals?.length) {
    const matchedDeals = props.deals
      .filter(d => 
        (d.title && d.title.toLowerCase().includes(q)) ||
        (d.contact_name && d.contact_name.toLowerCase().includes(q)) ||
        (d.company && d.company.toLowerCase().includes(q))
      )
      .slice(0, 5)
      .map(d => ({
        id: `deal-${d.id}`,
        type: 'deal',
        title: d.title || d.company || 'Deal',
        subtitle: `$${Number(d.deal_amount_usd || 0).toLocaleString()} • Stage: ${d.stage || 'lead'}`,
        icon: Kanban,
        raw: d,
        category: 'Pipeline Deals',
      }))
    results.push(...matchedDeals)
  }

  // 4. Leads Directory (max 6)
  if (props.leads?.length) {
    const matchedLeads = props.leads
      .filter(l => 
        (l.name && l.name.toLowerCase().includes(q)) ||
        (l.company && l.company.toLowerCase().includes(q)) ||
        (l.email && l.email.toLowerCase().includes(q))
      )
      .slice(0, 6)
      .map(l => ({
        id: `lead-${l.id}`,
        type: 'lead',
        title: l.name,
        subtitle: `${l.company || 'Direct'} • ${l.email || 'No email'} • ${l.deal_amount || '$0'}`,
        icon: Users,
        raw: l,
        category: 'Contacts & Leads',
      }))
    results.push(...matchedLeads)
  }

  return results.slice(0, 15)
})

watch(() => props.show, (isVisible) => {
  if (isVisible) {
    query.value = ''
    selectedIndex.value = 0
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
})

watch(filteredItems, () => {
  selectedIndex.value = 0
})

const handleKeyDown = (e: KeyboardEvent) => {
  if (!props.show) return

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    if (filteredItems.value.length > 0) {
      selectedIndex.value = (selectedIndex.value + 1) % filteredItems.value.length
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    if (filteredItems.value.length > 0) {
      selectedIndex.value = (selectedIndex.value - 1 + filteredItems.value.length) % filteredItems.value.length
    }
  } else if (e.key === 'Enter') {
    e.preventDefault()
    executeSelected()
  } else if (e.key === 'Escape') {
    e.preventDefault()
    emit('close')
  }
}

const executeItem = (item: any) => {
  if (!item) return
  if (item.type === 'action') {
    emit('action', item.action)
  } else if (item.type === 'tab') {
    emit('switchTab', item.tab)
  } else if (item.type === 'rfp') {
    emit('selectRfp', item.raw)
  } else if (item.type === 'deal') {
    emit('selectDeal', item.raw)
  } else if (item.type === 'lead') {
    emit('selectLead', item.raw.id)
  }
  emit('close')
}

const executeSelected = () => {
  const item = filteredItems.value[selectedIndex.value]
  executeItem(item)
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-slate-950/70 dark:bg-black/80 backdrop-blur-md transition-all duration-200"
    @click.self="emit('close')"
    @keydown="handleKeyDown"
  >
    <div
      class="relative w-full max-w-2xl bg-white dark:bg-[#0c101c] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl shadow-purple-500/10 dark:shadow-purple-950/30 overflow-hidden flex flex-col transition-all duration-200 animate-in fade-in zoom-in-95"
    >
      <!-- Search Input Header -->
      <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40">
        <Command class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0" />
        <input
          ref="searchInput"
          v-model="query"
          type="text"
          placeholder="Type a command or search RFPs, Deals, Leads... (or Esc to close)"
          class="w-full bg-transparent text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none"
        />
        <div class="flex items-center gap-1.5 shrink-0">
          <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
            ESC
          </span>
          <button
            type="button"
            @click="emit('close')"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Quick Suggestion Pills -->
      <div v-if="!query" class="px-4 py-2 border-b border-slate-100 dark:border-slate-800/50 flex items-center gap-2 overflow-x-auto text-[11px] text-slate-500 dark:text-slate-400">
        <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider shrink-0">Quick Jumps:</span>
        <button
          type="button"
          @click="query = 'hunter'"
          class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-850 hover:bg-purple-500/10 hover:text-purple-600 dark:hover:text-purple-400 transition cursor-pointer shrink-0"
        >
          🌐 RFPs
        </button>
        <button
          type="button"
          @click="query = 'deals'"
          class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-850 hover:bg-purple-500/10 hover:text-purple-600 dark:hover:text-purple-400 transition cursor-pointer shrink-0"
        >
          💼 Pipeline
        </button>
        <button
          type="button"
          @click="query = 'campaigns'"
          class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-850 hover:bg-purple-500/10 hover:text-purple-600 dark:hover:text-purple-400 transition cursor-pointer shrink-0"
        >
          🚀 Outbound
        </button>
        <button
          type="button"
          @click="query = 'new'"
          class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-850 hover:bg-purple-500/10 hover:text-purple-600 dark:hover:text-purple-400 transition cursor-pointer shrink-0"
        >
          ➕ Add Lead
        </button>
      </div>

      <!-- Results List -->
      <div class="max-h-[380px] overflow-y-auto p-2 space-y-1 custom-scrollbar">
        <div v-if="filteredItems.length === 0" class="py-12 text-center text-xs text-slate-500 dark:text-slate-400">
          <Search class="w-6 h-6 mx-auto mb-2 text-slate-400 dark:text-slate-600 opacity-60" />
          No results found for "<span class="font-semibold text-slate-700 dark:text-slate-300">{{ query }}</span>".
        </div>

        <template v-else>
          <button
            v-for="(item, idx) in filteredItems"
            :key="item.id"
            type="button"
            @click="executeItem(item)"
            @mouseenter="selectedIndex = idx"
            class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between text-left transition-all cursor-pointer group"
            :class="selectedIndex === idx
              ? 'bg-purple-600/10 dark:bg-purple-500/15 border border-purple-500/30 text-purple-900 dark:text-purple-100'
              : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-800 dark:text-slate-200 border border-transparent'"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div
                class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                :class="selectedIndex === idx
                  ? 'bg-purple-600 text-white shadow-sm'
                  : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
              >
                <component :is="item.icon || Layers" class="w-3.5 h-3.5" />
              </div>

              <div class="min-w-0">
                <div class="text-xs font-bold truncate leading-tight flex items-center gap-2">
                  <span>{{ item.title }}</span>
                  <span
                    v-if="item.category"
                    class="text-[9px] px-1.5 py-0.2 rounded font-mono font-medium text-slate-400 dark:text-slate-500 bg-slate-200/50 dark:bg-slate-800/80 border border-slate-300/40 dark:border-slate-700/50"
                  >
                    {{ item.category }}
                  </span>
                </div>
                <div v-if="item.subtitle" class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5 font-mono">
                  {{ item.subtitle }}
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <span
                v-if="item.shortcut"
                class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-200/70 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/60 dark:border-slate-700"
              >
                {{ item.shortcut }}
              </span>
              <CornerDownLeft
                v-if="selectedIndex === idx"
                class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400 animate-pulse"
              />
            </div>
          </button>
        </template>
      </div>

      <!-- Footer Info -->
      <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/50 flex items-center justify-between text-[11px] text-slate-400">
        <div class="flex items-center gap-3">
          <span class="flex items-center gap-1">
            <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-[10px] font-mono font-bold">↑</kbd>
            <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-[10px] font-mono font-bold">↓</kbd>
            <span>Navigate</span>
          </span>
          <span class="flex items-center gap-1">
            <kbd class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-[10px] font-mono font-bold">↵</kbd>
            <span>Select</span>
          </span>
        </div>
        <div class="font-mono text-[10px] text-purple-600 dark:text-purple-400 font-bold">
          ⌘K Cockpit Hub
        </div>
      </div>
    </div>
  </div>
</template>
