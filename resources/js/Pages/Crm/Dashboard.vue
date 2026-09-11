<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import {
  TrendingUp, Users, DollarSign, AlertCircle, Plus, Upload, Search,
  Filter, LayoutGrid, List, MessageSquare, LogOut, CheckCircle2,
  Sparkles, ExternalLink, ShieldAlert, ShieldCheck, ArrowRight, Building2, Check, Clock, X,
  Globe, Copy, RefreshCw, Trash2, Kanban, Send, Mail, Calendar, Layers,
  ChevronRight, ArrowUpRight, CheckSquare, Square, Eye, MousePointerClick,
  Command, Keyboard, Columns, Maximize2
} from 'lucide-vue-next'

import KanbanColumn from '@/Components/Crm/KanbanColumn.vue'
import LeadDrawer from '@/Components/Crm/LeadDrawer.vue'
import ScriptGeneratorModal from '@/Components/Crm/ScriptGeneratorModal.vue'
import QuickAddModal from '@/Components/Crm/QuickAddModal.vue'
import CsvImportModal from '@/Components/Crm/CsvImportModal.vue'
import ProposalModal from '@/Components/Crm/ProposalModal.vue'
import CustomRfpModal from '@/Components/Crm/CustomRfpModal.vue'
import PitchPreviewModal from '@/Components/Crm/PitchPreviewModal.vue'
import SecuritySettingsModal from '@/Components/Crm/SecuritySettingsModal.vue'
import ConvertToDealModal from '@/Components/Crm/ConvertToDealModal.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import CommandPalette from '@/Components/Crm/CommandPalette.vue'
import KeyboardShortcutsModal from '@/Components/Crm/KeyboardShortcutsModal.vue'
import FloatingShortcutDock from '@/Components/Crm/FloatingShortcutDock.vue'
import RfpInspectorPane from '@/Components/Crm/RfpInspectorPane.vue'
import LeadInspectorPane from '@/Components/Crm/LeadInspectorPane.vue'

const showSecurityModal = ref(false)

const props = defineProps<{
  telemetry: {
    total_pipeline_usd: number
    won_revenue_usd: number
    active_deals_count: number
    win_rate: number
    overdue_count: number
    avg_deal_size?: number
    avg_velocity_days?: number
    location_label?: string
    total_leads_count?: number
    source_analytics?: Array<{
      source: string
      label: string
      total_leads: number
      deals_count: number
      won_count: number
      lost_count: number
      won_usd: number
      win_rate: number
    }>
    total_pipeline_inr?: number
    won_revenue_inr?: number
  }
  action_queue: any[]
  market_requirements?: any[]
  stages: Record<string, any>
  all_deals: any[]
  all_leads?: any[]
  filters: {
    segment: string
    search: string
    tab?: string
  }
  campaigns_stats?: {
    total_sequences: number
    active_sequences: number
    replied_sequences: number
    total_emails_sent: number
    open_rate: number
    click_rate: number
    reply_rate: number
    upcoming_queue: Array<{
      id: number
      sequence_id: number
      lead_id: number
      lead_name: string
      company: string
      email: string
      step_number: number
      title: string
      subject: string
      scheduled_at: string
      scheduled_for_human: string
    }>
  }
  app_meta: {
    app_name: string
    founder_name: string
    founder_email: string
    booking_url: string
    website_url: string
    active_sources_count: number
  }
}>()

// Navigation & Active Tab
const activeMainTab = ref<'hunter' | 'deals' | 'leads' | 'campaigns' | 'studio'>(
  (props.filters.tab as any) || 'hunter'
)

// Filters & Search
const searchQuery = ref(props.filters.search || '')
const selectedSegment = ref(props.filters.segment || 'all')
const showMobileSearch = ref(false)

// Action Queue
const dismissedQueueIds = ref<number[]>([])
const activeQueue = computed(() => {
  return props.action_queue.filter(item => !dismissedQueueIds.value.includes(item.id))
})

// Computed: grammatically correct deal/lead count labels
const dealCountLabel = computed(() => {
  const n = props.telemetry.active_deals_count
  return `${n} ${n === 1 ? 'Deal' : 'Deals'}`
})

const leadCountLabel = computed(() => {
  const n = props.all_leads?.length || 0
  return `${n} ${n === 1 ? 'Contact' : 'Contacts'}`
})

const leadsLocationLabel = computed(() => {
  return props.telemetry.location_label || 'Global founders'
})

const overdueSubtitle = computed(() => {
  const n = props.telemetry.overdue_count
  if (n === 0) return 'All follow-ups on schedule'
  if (n === 1) return '1 lead needs immediate outreach'
  return `${n} leads overdue — respond within 60 mins`
})

// RFP Hunter State
const isPolling = ref(false)
const selectedSourceFilter = ref('all')
const selectedStackFilter = ref('all')
const selectedBudgetTier = ref('all')
const dismissedReqIds = ref<number[]>([])
const purgingJunk = ref(false)

const techStackTabs = [
  { key: 'all', label: 'All Stack' },
  { key: 'Laravel', label: '🔴 Laravel' },
  { key: 'Vue', label: '🟢 Vue' },
  { key: 'React', label: '🔵 React' },
  { key: 'Python/AI', label: '🤖 Python/AI' },
  { key: 'Mobile', label: '📱 Mobile' },
  { key: 'Full-Stack', label: '⚡ Full-Stack' },
  { key: 'Database', label: '🗄️ Database' },
]

const budgetTierTabs = [
  { key: 'all', label: 'All Budgets' },
  { key: 'high', label: '💎 High Value ($5k+)' },
  { key: 'mid', label: '🚀 Sprint ($2k–$5k)' },
  { key: 'low', label: '⚡ Hourly / Custom' },
]

const sourceTabs = [
  { key: 'all', label: 'All Sources' },
  { key: 'producthunt', label: '😸 Product Hunt' },
  { key: 'indiehackers', label: '🧑‍💻 Indie Hackers' },
  { key: 'wellfound', label: '🦄 Wellfound' },
  { key: 'substack', label: '📰 Substack BIP' },
  { key: 'jobicy', label: '💼 Jobicy' },
  { key: 'arbeitnow', label: '🌐 Arbeitnow' },
  { key: 'upwork', label: '🟢 Upwork' },
  { key: 'hackernews', label: '🟠 Hacker News' },
  { key: 'github', label: '⚫ GitHub' },
  { key: 'reddit', label: '🔴 Reddit Hire' },
  { key: 'reddit_startup', label: '🚀 Reddit Startup' },
  { key: 'remoteok', label: '🌎 RemoteOK' },
  { key: 'weworkremotely', label: '💼 WeWork' },
  { key: 'remotive', label: '🌐 Remotive' },
]

const filteredMarketRequirements = computed(() => {
  const list = props.market_requirements || []
  return list
    .filter((req: any) => !dismissedReqIds.value.includes(req.id))
    .filter((req: any) => {
      if (selectedSourceFilter.value === 'all') return true
      if (selectedSourceFilter.value === 'upwork') return ['upwork', 'direct', 'custom_url', 'direct_rfp'].includes(req.source)
      return req.source === selectedSourceFilter.value
    })
    .filter((req: any) => {
      if (selectedStackFilter.value === 'all') return true
      const tags: string[] = req.tech_tags || req.detected_tech_stack || []
      return tags.includes(selectedStackFilter.value)
    })
    .filter((req: any) => {
      if (selectedBudgetTier.value === 'all') return true
      const amt = req.estimated_amount || 0
      if (selectedBudgetTier.value === 'high') return amt >= 5000
      if (selectedBudgetTier.value === 'mid') return amt >= 2000 && amt < 5000
      if (selectedBudgetTier.value === 'low') return amt < 2000 || amt === 0
      return true
    })
})

// Modals & Drawer State
const selectedReqForPitch = ref<any | null>(null)
const showPitchModal = ref(false)
const selectedLeadForDrawer = ref<number | null>(null)
const showDrawer = ref(false)
const showQuickAdd = ref(false)
const showCsvImport = ref(false)
const showProposalModal = ref(false)
const selectedDealForProposal = ref<number | null>(null)
const showConvertModal = ref(false)
const selectedReqForConvert = ref<any | null>(null)

// Toast State
const toast = ref<{ message: string; id: number } | null>(null)
const triggerToast = (msg: string) => {
  const toastId = Date.now()
  toast.value = { message: msg, id: toastId }
  setTimeout(() => {
    if (toast.value?.id === toastId) toast.value = null
  }, 3500)
}

// Studio State
const studioInput = ref('')
const studioContactName = ref('')
const studioCompanyName = ref('')
const studioSource = ref('upwork')
const isAnalyzing = ref(false)
const studioResult = ref<any>(null)
const activeStudioTab = ref<'upwork' | 'email' | 'linkedin'>('upwork')
const copiedStudioPitch = ref(false)

// Leads Directory State
const leadSearch = ref('')
const leadSegmentFilter = ref('all')
const selectedLeadIds = ref<number[]>([])

const filteredLeads = computed(() => {
  let list = props.all_leads || []
  if (leadSegmentFilter.value !== 'all') {
    list = list.filter(l => l.segment === leadSegmentFilter.value)
  }
  if (leadSearch.value.trim() !== '') {
    const q = leadSearch.value.toLowerCase()
    list = list.filter(l =>
      (l.name && l.name.toLowerCase().includes(q)) ||
      (l.company && l.company.toLowerCase().includes(q)) ||
      (l.email && l.email.toLowerCase().includes(q))
    )
  }
  return list
})

const isAllLeadsSelected = computed(() => {
  return filteredLeads.value.length > 0 && selectedLeadIds.value.length === filteredLeads.value.length
})

const toggleSelectAllLeads = () => {
  if (isAllLeadsSelected.value) {
    selectedLeadIds.value = []
  } else {
    selectedLeadIds.value = filteredLeads.value.map(l => l.id)
  }
}

const toggleLeadSelection = (id: number) => {
  const idx = selectedLeadIds.value.indexOf(id)
  if (idx > -1) {
    selectedLeadIds.value.splice(idx, 1)
  } else {
    selectedLeadIds.value.push(id)
  }
}

// Actions
const applyFilters = () => {
  router.get(
    '/crm',
    {
      segment: selectedSegment.value,
      search: searchQuery.value,
      tab: activeMainTab.value,
    },
    { preserveState: true, replace: true }
  )
}

const switchMainTab = (tabKey: 'hunter' | 'deals' | 'leads' | 'campaigns' | 'studio') => {
  activeMainTab.value = tabKey
  applyFilters()
}

const pollFeedsLive = async () => {
  if (isPolling.value) return
  isPolling.value = true
  const sourceCount = props.app_meta?.active_sources_count || 15
  triggerToast(`Scanning ${sourceCount} live feeds for fresh contracts — Product Hunt, Indie Hackers, HackerNews, Reddit, Wellfound...`)
  try {
    const res = await fetch('/crm/market/poll', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
    })
    const data = await res.json()
    if (data.success) {
      triggerToast(data.message || 'Market polling complete!')
      router.reload({ only: ['market_requirements'] })
    } else {
      triggerToast(data.message || 'Market polling failed')
    }
  } catch (e) {
    console.error(e)
    triggerToast('Network error while polling feeds')
  } finally {
    isPolling.value = false
  }
}

const purgeJunkReqs = async () => {
  if (!confirm('Clean out dismissed and low-relevance items from the feed?')) return
  purgingJunk.value = true
  try {
    const res = await fetch('/crm/market/purge-junk', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
    })
    const data = await res.json()
    if (data.success) {
      triggerToast(data.message)
      router.reload({ only: ['market_requirements'] })
    }
  } catch (e) {
    console.error(e)
  } finally {
    purgingJunk.value = false
  }
}

const dismissReq = async (reqId: number) => {
  dismissedReqIds.value.push(reqId)
  triggerToast('RFP dismissed from active feed')
  try {
    await fetch(`/crm/market/requirements/${reqId}/dismiss`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
    })
  } catch (e) {
    console.error(e)
  }
}

const convertReqToDeal = (req: any) => {
  selectedReqForConvert.value = req
  showConvertModal.value = true
}

const onConvertSuccess = (dealId: number) => {
  showConvertModal.value = false
  selectedReqForConvert.value = null
  triggerToast('RFP converted to active CRM deal!')
  router.reload({ only: ['stages', 'telemetry', 'action_queue', 'all_deals', 'all_leads', 'market_requirements'] })
}

const copiedReqId = ref<number | null>(null)
const copyReqPitch = async (req: any) => {
  const text = req.upwork_proposal || req.pitch_draft
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    copiedReqId.value = req.id
    setTimeout(() => {
      if (copiedReqId.value === req.id) copiedReqId.value = null
    }, 2500)
    triggerToast('Proposal copied to clipboard!')
  } catch (err) {
    console.error(err)
  }
}

const openPitchPreview = (req: any) => {
  selectedReqForPitch.value = req
  showPitchModal.value = true
}

const openLeadDetails = (leadId: number) => {
  selectedLeadForDrawer.value = leadId
  showDrawer.value = true
}

const openProposalModal = (dealId: number) => {
  selectedDealForProposal.value = dealId
  showProposalModal.value = true
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
    console.error(e)
  }
}

const dismissQueueItem = (leadId: number) => {
  dismissedQueueIds.value.push(leadId)
  triggerToast('Marked completed in daily queue')
}

// Studio Run Smart Ingest
const runSmartIngest = async () => {
  if (!studioInput.value.trim() || studioInput.value.length < 10) {
    triggerToast('Please provide a job URL or scope text (min 10 chars)')
    return
  }
  isAnalyzing.value = true
  triggerToast('Running OpenAI analysis on project scope...')
  try {
    const res = await fetch('/crm/market/smart-ingest', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        input: studioInput.value,
        source: studioSource.value,
        contact_name: studioContactName.value || null,
        contact_company: studioCompanyName.value || null,
      }),
    })
    const data = await res.json()
    if (data.success) {
      studioResult.value = data
      triggerToast('AI analysis complete & opportunity saved!')
      router.reload({ only: ['market_requirements', 'all_deals', 'stages'] })
    } else {
      triggerToast(data.message || 'Analysis failed')
    }
  } catch (e) {
    console.error(e)
    triggerToast('Error during AI analysis')
  } finally {
    isAnalyzing.value = false
  }
}

const copyActiveStudioPitch = async () => {
  if (!studioResult.value?.pitch_data) return
  const p = studioResult.value.pitch_data
  let text = p.upwork_proposal
  if (activeStudioTab.value === 'email') text = `Subject: ${p.email_subject}\n\n${p.email_pitch}`
  if (activeStudioTab.value === 'linkedin') text = p.linkedin_dm
  await navigator.clipboard.writeText(text)
  copiedStudioPitch.value = true
  setTimeout(() => { copiedStudioPitch.value = false }, 2500)
  triggerToast('Copied proposal draft!')
}

// Leads Directory Actions
const deleteLead = async (id: number) => {
  if (!confirm('Are you sure you want to delete this prospect?')) return
  try {
    const res = await fetch(`/crm/leads/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
    })
    const data = await res.json()
    if (data.success) {
      triggerToast(data.message)
      router.reload({ only: ['all_leads', 'stages', 'telemetry'] })
    }
  } catch (e) {
    console.error(e)
  }
}

const bulkDeleteLeads = async () => {
  if (!selectedLeadIds.value.length) return
  if (!confirm(`Permanently delete ${selectedLeadIds.value.length} selected leads?`)) return
  try {
    const res = await fetch('/crm/leads/bulk', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        lead_ids: selectedLeadIds.value,
        action: 'delete',
      }),
    })
    const data = await res.json()
    if (data.success) {
      triggerToast(data.message)
      selectedLeadIds.value = []
      router.reload({ only: ['all_leads', 'stages', 'telemetry'] })
    }
  } catch (e) {
    console.error(e)
  }
}

const sendDirectMailto = (lead: any) => {
  if (!lead.email) return
  const founderName = props.app_meta?.founder_name || 'Ashish Gupta'
  const bookingUrl = props.app_meta?.booking_url || 'https://www.digitalbuilders.in/book'
  const websiteUrl = props.app_meta?.website_url || 'https://www.digitalbuilders.in'
  const company = lead.company || lead.name
  const subject = encodeURIComponent(`Architecture & Timeline Proposal for ${company}`)
  const body = encodeURIComponent(`Hi ${lead.name},\n\nI'm ${founderName}, founder & lead architect at ${props.app_meta?.app_name || 'DigitalBuilders'} (${websiteUrl}).\n\nWanted to connect regarding your custom software scope.\n\nFeel free to pick a 15-min discovery slot on my calendar: ${bookingUrl}\n\nBest regards,\n${founderName}`)
  window.open(`mailto:${lead.email}?subject=${subject}&body=${body}`, '_blank')
}

const logout = () => {
  router.post('/crm/logout')
}

// =========================================================================
// POWER-USER COCKPIT & HOTKEY ENGINE
// =========================================================================
const showCommandPalette = ref(false)
const showShortcutsModal = ref(false)
const isSplitView = ref(true)

const selectedRfpIndex = ref(0)
const selectedLeadIndex = ref(0)

const focusedRfp = computed(() => {
  const list = filteredMarketRequirements.value
  if (!list.length) return null
  const idx = Math.min(Math.max(0, selectedRfpIndex.value), list.length - 1)
  return list[idx] || null
})

const focusedLead = computed(() => {
  const list = filteredLeads.value
  if (!list.length) return null
  const idx = Math.min(Math.max(0, selectedLeadIndex.value), list.length - 1)
  return list[idx] || null
})

const toggleSplitView = () => {
  isSplitView.value = !isSplitView.value
  try {
    localStorage.setItem('crm_split_view', String(isSplitView.value))
  } catch (e) {}
  triggerToast(isSplitView.value ? '⚡ Split Cockpit Mode Enabled' : '🖥️ Full-Width Mode Enabled')
}

const stepSelection = (delta: number) => {
  if (activeMainTab.value === 'hunter') {
    const len = filteredMarketRequirements.value.length
    if (len === 0) return
    let next = selectedRfpIndex.value + delta
    if (next < 0) next = len - 1
    if (next >= len) next = 0
    selectedRfpIndex.value = next
    nextTick(() => {
      const el = document.getElementById(`rfp-item-${filteredMarketRequirements.value[next]?.id}`)
      el?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    })
  } else if (activeMainTab.value === 'leads') {
    const len = filteredLeads.value.length
    if (len === 0) return
    let next = selectedLeadIndex.value + delta
    if (next < 0) next = len - 1
    if (next >= len) next = 0
    selectedLeadIndex.value = next
    nextTick(() => {
      const el = document.getElementById(`lead-item-${filteredLeads.value[next]?.id}`)
      el?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    })
  }
}

const handleHotkeyConvert = () => {
  if (activeMainTab.value === 'hunter' && focusedRfp.value) {
    convertReqToDeal(focusedRfp.value)
  } else if (activeMainTab.value === 'leads' && focusedLead.value) {
    selectedReqForConvert.value = {
      title: `${focusedLead.value.company || focusedLead.value.name} — Custom Scope`,
      estimated_amount: focusedLead.value.deal_amount ? parseInt(focusedLead.value.deal_amount.replace(/[^0-9]/g, '')) || 5000 : 5000,
      contact_name: focusedLead.value.name,
      contact_company: focusedLead.value.company,
      contact_email: focusedLead.value.email,
      source: focusedLead.value.segment || 'direct',
      raw_text: focusedLead.value.notes || 'Lead converted to active deal pipeline.',
    }
    showConvertModal.value = true
  }
}

const handleHotkeyOutreach = () => {
  if (activeMainTab.value === 'hunter' && focusedRfp.value) {
    openPitchPreview(focusedRfp.value)
  } else if (activeMainTab.value === 'leads' && focusedLead.value) {
    openLeadDetails(focusedLead.value.id)
  }
}

const handleHotkeyPitch = () => {
  if (activeMainTab.value === 'hunter' && focusedRfp.value) {
    copyReqPitch(focusedRfp.value)
  }
}

const handleHotkeyDismiss = () => {
  if (activeMainTab.value === 'hunter' && focusedRfp.value) {
    dismissReq(focusedRfp.value.id)
  } else if (activeMainTab.value === 'leads' && focusedLead.value) {
    deleteLead(focusedLead.value.id)
  }
}

const handleHotkeyEnrich = async () => {
  if (activeMainTab.value === 'leads' && focusedLead.value) {
    triggerToast(`Enriching dossier for ${focusedLead.value.name}...`)
    try {
      const res = await fetch(`/crm/leads/${focusedLead.value.id}/enrich`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
      })
      const data = await res.json()
      if (data.success) {
        triggerToast('Lead dossier enriched!')
        router.reload({ only: ['all_leads'] })
      }
    } catch (e) {
      console.error(e)
    }
  }
}

const enrichLeadFromInspector = async (leadId: number) => {
  triggerToast('Enriching contact intelligence dossier...')
  try {
    const res = await fetch(`/crm/leads/${leadId}/enrich`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    if (data.success) {
      triggerToast('Intelligence dossier enriched!')
      router.reload({ only: ['all_leads'] })
    }
  } catch (e) {
    console.error(e)
  }
}

const handleHotkeyInspect = () => {
  if (activeMainTab.value === 'hunter' && focusedRfp.value) {
    openPitchPreview(focusedRfp.value)
  } else if (activeMainTab.value === 'leads' && focusedLead.value) {
    openLeadDetails(focusedLead.value.id)
  }
}

const exportLeadsCsv = () => {
  const leads = props.all_leads || []
  if (!leads.length) {
    triggerToast('No leads available to export')
    return
  }
  const headers = ['ID', 'Name', 'Company', 'Email', 'Phone', 'Segment', 'Score', 'Deal Amount', 'Status']
  const rows = leads.map(l => [
    l.id,
    `"${(l.name || '').replace(/"/g, '""')}"`,
    `"${(l.company || '').replace(/"/g, '""')}"`,
    `"${(l.email || '').replace(/"/g, '""')}"`,
    `"${(l.phone || '').replace(/"/g, '""')}"`,
    `"${(l.segment || '').replace(/"/g, '""')}"`,
    l.score || 0,
    `"${(l.deal_amount || '').replace(/"/g, '""')}"`,
    `"${(l.status || '').replace(/"/g, '""')}"`
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `digitalbuilders_leads_${new Date().toISOString().slice(0,10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  triggerToast(`Exported ${leads.length} leads to CSV`)
}

const handleCommandAction = (actionKey: string) => {
  if (actionKey === 'new-lead') {
    showQuickAdd.value = true
  } else if (actionKey === 'sync-feeds') {
    pollFeedsLive()
  } else if (actionKey === 'toggle-theme') {
    const isDark = document.documentElement.classList.contains('dark')
    if (isDark) {
      document.documentElement.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    } else {
      document.documentElement.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    }
    triggerToast(`Theme switched to ${isDark ? 'Light' : 'Dark'} mode`)
  } else if (actionKey === 'security') {
    showSecurityModal.value = true
  } else if (actionKey === 'export-csv') {
    exportLeadsCsv()
  }
}

const handleCommandSelectRfp = (rfp: any) => {
  switchMainTab('hunter')
  const idx = filteredMarketRequirements.value.findIndex(r => r.id === rfp.id)
  if (idx !== -1) {
    selectedRfpIndex.value = idx
  }
  nextTick(() => {
    const el = document.getElementById(`rfp-item-${rfp.id}`)
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  })
}

const handleCommandSelectDeal = (deal: any) => {
  switchMainTab('deals')
}

const handleCommandSelectLead = (leadId: number) => {
  openLeadDetails(leadId)
}

const handleGlobalKeyDown = (e: KeyboardEvent) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    showCommandPalette.value = !showCommandPalette.value
    return
  }

  const target = e.target as HTMLElement
  if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable)) {
    return
  }

  if (e.key === 'Escape') {
    if (showCommandPalette.value) { showCommandPalette.value = false; return }
    if (showShortcutsModal.value) { showShortcutsModal.value = false; return }
    if (showDrawer.value) { showDrawer.value = false; return }
    if (showPitchModal.value) { showPitchModal.value = false; return }
    if (showConvertModal.value) { showConvertModal.value = false; return }
    if (showQuickAdd.value) { showQuickAdd.value = false; return }
    if (showSecurityModal.value) { showSecurityModal.value = false; return }
  }

  if (e.key === '?') {
    e.preventDefault()
    showShortcutsModal.value = !showShortcutsModal.value
    return
  }

  if (e.key === '[') {
    e.preventDefault()
    toggleSplitView()
    return
  }

  if (e.key === '1') { switchMainTab('hunter'); return }
  if (e.key === '2') { switchMainTab('deals'); return }
  if (e.key === '3') { switchMainTab('leads'); return }
  if (e.key === '4') { switchMainTab('campaigns'); return }
  if (e.key === '5') { switchMainTab('studio'); return }

  if (e.key.toLowerCase() === 'j' || e.key === 'ArrowDown') {
    e.preventDefault()
    stepSelection(1)
    return
  }
  if (e.key.toLowerCase() === 'k' || e.key === 'ArrowUp') {
    e.preventDefault()
    stepSelection(-1)
    return
  }

  if (e.key.toLowerCase() === 'd') {
    e.preventDefault()
    handleHotkeyConvert()
    return
  }

  if (e.key.toLowerCase() === 'a') {
    e.preventDefault()
    handleHotkeyOutreach()
    return
  }

  if (e.key.toLowerCase() === 'p') {
    e.preventDefault()
    handleHotkeyPitch()
    return
  }

  if (e.key.toLowerCase() === 'x') {
    e.preventDefault()
    handleHotkeyDismiss()
    return
  }

  if (e.key.toLowerCase() === 'e') {
    e.preventDefault()
    handleHotkeyEnrich()
    return
  }

  if (e.key === ' ' || e.key === 'Spacebar') {
    e.preventDefault()
    handleHotkeyInspect()
    return
  }

  if (e.key.toLowerCase() === 'n') {
    e.preventDefault()
    showQuickAdd.value = true
    return
  }

  if (e.key.toLowerCase() === 's') {
    e.preventDefault()
    pollFeedsLive()
    return
  }
}

onMounted(() => {
  try {
    const saved = localStorage.getItem('crm_split_view')
    if (saved !== null) {
      isSplitView.value = saved === 'true'
    }
  } catch (e) {}

  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>

<template>
  <Head :title="`Executive International Sales Cockpit — ${app_meta?.app_name || 'CRM'}`" />

  <div class="crm-cockpit min-h-screen bg-slate-50 dark:bg-[#070b14] text-slate-900 dark:text-slate-100 flex flex-col font-sans selection:bg-purple-500 selection:text-white transition-colors duration-200">
    <!-- Top Executive Nav Header -->
    <header class="h-16 px-3 sm:px-6 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/85 dark:bg-slate-950/85 backdrop-blur-md sticky top-0 z-30 transition-colors duration-200">
      <div class="max-w-[1720px] mx-auto w-full h-full flex items-center justify-between gap-2 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-6 min-w-0">
          <Link href="/crm" class="flex items-center gap-2.5 group shrink-0">
            <img
              src="/images/db-logo.png"
              alt="DigitalBuilders Logo"
              class="h-9 w-9 sm:h-10 sm:w-10 object-contain flex-shrink-0 transition-transform duration-300 group-hover:scale-105 filter-none dark:drop-shadow-[0_0_12px_rgba(168,85,247,0.35)]"
            />
            <div class="hidden xs:block">
              <div class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                Digital<span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-indigo-600 dark:from-purple-400 dark:to-indigo-300">Builders</span>
                <span class="text-[9px] sm:text-[10px] px-1.5 py-0.2 rounded bg-purple-500/10 text-purple-700 dark:text-purple-400 font-bold border border-purple-500/20">USD CRM</span>
              </div>
              <div class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 hidden sm:block">International Deal Engine ($ USD)</div>
            </div>
          </Link>

          <!-- Primary Workspace Navigation Tabs -->
          <nav class="hidden md:flex items-center gap-1 bg-slate-100 dark:bg-slate-900/90 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
            <button
              type="button"
              @click="switchMainTab('hunter')"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
              :class="activeMainTab === 'hunter'
                ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-sm'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Globe class="w-3.5 h-3.5" />
              <span>RFP Hunter ({{ filteredMarketRequirements.length }})</span>
            </button>
            <button
              type="button"
              @click="switchMainTab('deals')"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
              :class="activeMainTab === 'deals'
                ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-sm'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Kanban class="w-3.5 h-3.5" />
              <span>Deals Pipeline (${{ Number(telemetry.total_pipeline_usd).toLocaleString() }})</span>
            </button>
            <button
              type="button"
              @click="switchMainTab('leads')"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
              :class="activeMainTab === 'leads'
                ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-sm'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Users class="w-3.5 h-3.5" />
              <span>Leads Directory ({{ all_leads?.length || 0 }})</span>
            </button>
            <button
              type="button"
              @click="switchMainTab('campaigns')"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
              :class="activeMainTab === 'campaigns'
                ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-sm'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Campaigns ({{ campaigns_stats?.active_sequences || 0 }})</span>
            </button>
            <button
              type="button"
              @click="switchMainTab('studio')"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
              :class="activeMainTab === 'studio'
                ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-sm'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Sparkles class="w-3.5 h-3.5" />
              <span>AI Proposal Studio</span>
            </button>
          </nav>
        </div>

        <!-- Action Header Controls -->
        <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
          <!-- Command Palette Trigger -->
          <button
            type="button"
            @click="showCommandPalette = true"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 dark:bg-slate-850 hover:bg-purple-500/10 hover:border-purple-500/40 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer shadow-sm"
            title="Open Command Center (Ctrl+K)"
          >
            <Command class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
            <span class="hidden sm:inline">Cockpit</span>
            <kbd class="hidden sm:inline px-1 py-0.2 rounded bg-slate-200 dark:bg-slate-800 text-[10px] font-mono text-slate-500">⌘K</kbd>
          </button>

          <!-- Split View Toggle -->
          <button
            type="button"
            @click="toggleSplitView"
            class="hidden md:flex items-center gap-1.5 px-2.5 py-1.5 sm:py-2 rounded-xl border text-xs font-bold transition cursor-pointer"
            :class="isSplitView
              ? 'bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30'
              : 'bg-slate-100 dark:bg-slate-850 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800'"
            :title="isSplitView ? 'Switch to Full-Width View ([)' : 'Switch to Split Cockpit View ([)'"
          >
            <Columns class="w-3.5 h-3.5" />
            <span class="hidden xl:inline">{{ isSplitView ? 'Split Mode' : 'Full Mode' }}</span>
          </button>

          <!-- Keyboard Shortcuts Sheet -->
          <button
            type="button"
            @click="showShortcutsModal = true"
            class="p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-purple-600 dark:text-slate-400 dark:hover:text-purple-400 hover:bg-slate-100 dark:hover:bg-slate-850 transition cursor-pointer"
            title="Keyboard Shortcuts Cheat Sheet (?)"
          >
            <Keyboard class="w-4 h-4" />
          </button>

          <button
            type="button"
            @click="pollFeedsLive"
            :disabled="isPolling"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer disabled:opacity-50"
            title="Scan HackerNews, Upwork RSS & live feeds"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isPolling }" />
            <span class="hidden sm:inline">{{ isPolling ? 'Scanning...' : 'Sync Feeds' }}</span>
          </button>

          <button
            type="button"
            @click="switchMainTab('studio')"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-500/20 flex items-center gap-1.5 transition cursor-pointer"
          >
            <Sparkles class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">AI Smart Ingest</span>
            <span class="sm:hidden">Ingest</span>
          </button>

          <button
            type="button"
            @click="showQuickAdd = true"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 dark:bg-slate-850 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5" />
            <span class="hidden xs:inline">Add Lead</span>
          </button>

          <ThemeToggle size="sm" />

          <!-- Founder Security & Password Settings -->
          <button
            type="button"
            @click="showSecurityModal = true"
            class="p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-purple-600 dark:text-slate-400 dark:hover:text-purple-400 hover:bg-slate-100 dark:hover:bg-slate-850 transition cursor-pointer"
            title="Founder Security & Password Settings"
          >
            <ShieldCheck class="w-4 h-4" />
          </button>

          <a
            href="/"
            target="_blank"
            class="p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-850 transition"
            title="Public Website"
          >
            <ExternalLink class="w-4 h-4" />
          </a>

          <button
            type="button"
            @click="logout"
            title="Logout"
            class="p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-rose-500/10 transition cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Mobile Sub-Navigation Bar -->
      <div class="md:hidden flex items-center justify-around border-t border-slate-200 dark:border-slate-800 py-1.5 bg-white dark:bg-slate-950">
        <button
          type="button"
          @click="switchMainTab('hunter')"
          class="text-[11px] font-bold px-2 py-1 rounded"
          :class="activeMainTab === 'hunter' ? 'text-purple-600 font-extrabold' : 'text-slate-500'"
        >
          🛰️ RFPs ({{ filteredMarketRequirements.length }})
        </button>
        <button
          type="button"
          @click="switchMainTab('deals')"
          class="text-[11px] font-bold px-2 py-1 rounded"
          :class="activeMainTab === 'deals' ? 'text-purple-600 font-extrabold' : 'text-slate-500'"
        >
          💼 Pipeline
        </button>
        <button
          type="button"
          @click="switchMainTab('leads')"
          class="text-[11px] font-bold px-2 py-1 rounded"
          :class="activeMainTab === 'leads' ? 'text-purple-600 font-extrabold' : 'text-slate-500'"
        >
          👥 Leads
        </button>
        <button
          type="button"
          @click="switchMainTab('campaigns')"
          class="text-[11px] font-bold px-2 py-1 rounded"
          :class="activeMainTab === 'campaigns' ? 'text-purple-600 font-extrabold' : 'text-slate-500'"
        >
          🚀 Drips ({{ campaigns_stats?.active_sequences || 0 }})
        </button>
        <button
          type="button"
          @click="switchMainTab('studio')"
          class="text-[11px] font-bold px-2 py-1 rounded"
          :class="activeMainTab === 'studio' ? 'text-purple-600 font-extrabold' : 'text-slate-500'"
        >
          ⚡ Studio
        </button>
      </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-1 max-w-[1720px] mx-auto w-full p-3.5 sm:p-5 md:p-6 space-y-5 sm:space-y-6 overflow-x-hidden">
      <!-- Executive Telemetry Strip (USD Focused) -->
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">
        <!-- Pipeline USD -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold mb-1">
            <span>Pipeline ($ USD)</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold">USD</span>
          </div>
          <div class="text-xl sm:text-2xl font-extrabold text-purple-600 dark:text-purple-300 font-mono tracking-tight">
            ${{ Number(telemetry.total_pipeline_usd).toLocaleString() }}
          </div>
          <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Total active contract value</div>
        </div>

        <!-- Won Revenue USD -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold mb-1">
            <span>Closed Won ($)</span>
            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
          </div>
          <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight">
            ${{ Number(telemetry.won_revenue_usd).toLocaleString() }}
          </div>
          <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Win Rate: {{ telemetry.win_rate }}%</div>
        </div>

        <!-- Active Opportunities -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold mb-1">
            <span>Active Deals</span>
            <Kanban class="w-3.5 h-3.5 text-sky-500" />
          </div>
          <div class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white font-mono tracking-tight">
            {{ dealCountLabel }}
          </div>
          <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Avg Deal: {{ telemetry.avg_deal_size ? '$' + Number(telemetry.avg_deal_size).toLocaleString() : '—' }}</div>
        </div>

        <!-- Qualified Leads -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold mb-1">
            <span>Prospect Leads</span>
            <Users class="w-3.5 h-3.5 text-indigo-500" />
          </div>
          <div class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white font-mono tracking-tight">
            {{ leadCountLabel }}
          </div>
          <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ leadsLocationLabel }}</div>
        </div>

        <!-- Overdue Follow-ups -->
        <div
          class="col-span-2 md:col-span-1 p-3.5 sm:p-4 rounded-2xl border shadow-sm flex flex-col justify-between"
          :class="telemetry.overdue_count > 0 ? 'bg-rose-50/80 dark:bg-rose-950/20 border-rose-300 dark:border-rose-500/30' : 'bg-white dark:bg-slate-900/80 border-slate-200/80 dark:border-slate-800/80'"
        >
          <div class="flex items-center justify-between text-xs font-semibold mb-1" :class="telemetry.overdue_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400'">
            <span>Follow-up Cadence</span>
            <AlertCircle class="w-3.5 h-3.5" />
          </div>
          <div class="text-xl sm:text-2xl font-extrabold tracking-tight font-mono" :class="telemetry.overdue_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white'">
            {{ telemetry.overdue_count > 0 ? `${telemetry.overdue_count} Overdue` : '0 Due Today' }}
          </div>
          <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ overdueSubtitle }}</div>
        </div>
      </div>

      <!-- Action Queue (Compact Top Strip) -->
      <div v-if="activeQueue.length > 0" class="p-3.5 rounded-2xl bg-gradient-to-r from-purple-500/5 via-slate-50 to-sky-500/5 dark:from-purple-950/20 dark:via-slate-900 dark:to-cyan-950/20 border border-purple-200/80 dark:border-purple-500/20 shadow-sm">
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
              Priority Founder Action Queue
            </h3>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold">
              {{ activeQueue.length }} Pending
            </span>
          </div>
          <span class="text-[11px] text-slate-400 hidden sm:inline">High intent outreach & proposal follow-ups</span>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-1 custom-scrollbar">
          <div
            v-for="item in activeQueue"
            :key="item.id"
            class="flex-shrink-0 w-72 p-3 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-purple-400 transition shadow-sm flex flex-col justify-between"
          >
            <div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-900 dark:text-white line-clamp-1">{{ item.name }}</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 font-mono">
                  {{ item.score }} pts
                </span>
              </div>
              <div class="text-[11px] text-slate-500 line-clamp-1">{{ item.company }}</div>
              <div class="text-[10px] text-purple-700 dark:text-purple-300 font-medium mt-1 line-clamp-1">
                👉 {{ item.next_action_note }}
              </div>
            </div>

            <div class="pt-2 mt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <span class="text-xs font-bold text-slate-900 dark:text-slate-200 font-mono">{{ item.deal_value }}</span>
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  @click="openLeadDetails(item.id)"
                  class="px-2.5 py-1 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-[11px] font-bold transition cursor-pointer"
                >
                  Engage
                </button>
                <button
                  type="button"
                  @click="dismissQueueItem(item.id)"
                  class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition"
                  title="Dismiss"
                >
                  <Check class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- TAB 1: 🛰️ INTERNATIONAL RFP HUNTER & LIVE FEEDS                          -->
      <!-- ========================================================================= -->
      <section v-if="activeMainTab === 'hunter'" class="space-y-4">
        <!-- Hunter Filter Bar (Source, Stack, Budget) -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
          <!-- Row 1: Platform Source Tabs + Sync Actions -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 custom-scrollbar">
              <button
                v-for="tab in sourceTabs"
                :key="tab.key"
                type="button"
                @click="selectedSourceFilter = tab.key"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer shrink-0"
                :class="selectedSourceFilter === tab.key
                  ? 'bg-purple-600 text-white shadow-sm'
                  : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
              >
                {{ tab.label }}
              </button>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <button
                type="button"
                @click="purgeJunkReqs"
                :disabled="purgingJunk"
                class="px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                title="Clean out dismissed or low-relevance items"
              >
                <Trash2 class="w-3.5 h-3.5" />
                <span>Clean Feed</span>
              </button>
              <button
                type="button"
                @click="pollFeedsLive"
                :disabled="isPolling"
                class="px-3 py-1.5 rounded-xl text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/30 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              >
                <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isPolling }" />
                <span>{{ isPolling ? 'Scanning...' : 'Sync Feeds' }}</span>
              </button>
            </div>
          </div>

          <!-- Row 2: Tech Stack Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">Stack:</span>
            <button
              v-for="stack in techStackTabs"
              :key="stack.key"
              type="button"
              @click="selectedStackFilter = stack.key"
              class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer shrink-0"
              :class="selectedStackFilter === stack.key
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
            >
              {{ stack.label }}
            </button>
            <span class="mx-2 h-4 w-px bg-slate-200 dark:bg-slate-700 shrink-0"></span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">Budget:</span>
            <button
              v-for="tier in budgetTierTabs"
              :key="tier.key"
              type="button"
              @click="selectedBudgetTier = tier.key"
              class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer shrink-0"
              :class="selectedBudgetTier === tier.key
                ? 'bg-emerald-600 text-white shadow-sm'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
            >
              {{ tier.label }}
            </button>
            <span v-if="selectedStackFilter !== 'all' || selectedBudgetTier !== 'all'" class="ml-auto shrink-0 text-[11px] text-purple-600 dark:text-purple-400 font-bold">
              {{ filteredMarketRequirements.length }} results
            </span>
          </div>
        </div>

        <!-- RFP Cards Grid -->
        <div v-if="filteredMarketRequirements.length === 0" class="p-12 text-center text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800">
          <Globe class="w-8 h-8 text-purple-500/40 mx-auto mb-2 animate-pulse" />
          <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">No RFPs in this channel right now</p>
          <p class="mt-1">Click "Sync Feeds" to poll Product Hunt launches, Jobicy, Arbeitnow, HackerNews, and Upwork, or use the "AI Proposal Studio" to paste any custom RFP link.</p>
        </div>

        <!-- Split Cockpit View vs Full Grid -->
        <template v-else>
          <!-- Split-Screen Cockpit View -->
          <div v-if="isSplitView" class="flex flex-col lg:flex-row gap-4 items-start">
            <!-- Left List: Dense & Keyboard Navigable (J/K) -->
            <div class="w-full lg:w-[45%] xl:w-[42%] space-y-2.5 max-h-[calc(100vh-220px)] overflow-y-auto pr-1.5 custom-scrollbar">
              <div
                v-for="(req, idx) in filteredMarketRequirements"
                :key="req.id"
                :id="`rfp-item-${req.id}`"
                @click="selectedRfpIndex = idx"
                class="p-3.5 rounded-2xl border transition-all cursor-pointer group select-none relative"
                :class="selectedRfpIndex === idx
                  ? 'bg-purple-50/80 dark:bg-purple-950/30 border-purple-500/80 dark:border-purple-400 ring-2 ring-purple-500/40 shadow-lg shadow-purple-500/10'
                  : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-slate-700'"
              >
                <!-- Active Indicator Pill -->
                <div
                  v-if="selectedRfpIndex === idx"
                  class="absolute -left-1.5 top-1/2 -translate-y-1/2 w-3 h-8 bg-purple-600 rounded-r-md shadow-sm"
                ></div>

                <!-- Top Row: Source, Budget, Score -->
                <div class="flex items-center justify-between gap-2 mb-1.5">
                  <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                      {{ req.source }}
                    </span>
                    <span class="text-[11px] font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
                      {{ req.budget }}
                    </span>
                  </div>
                  <span
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono"
                    :class="req.relevance_score >= 80 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'"
                  >
                    {{ req.relevance_score }}% Match
                  </span>
                </div>

                <!-- Title -->
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug mb-1">
                  {{ req.title }}
                </h4>

                <!-- Excerpt -->
                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-2">
                  {{ req.raw_text }}
                </p>

                <!-- Footer Info & 1-Key Hints -->
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1.5 border-t border-slate-100 dark:border-slate-800/80">
                  <span class="truncate max-w-[180px]">
                    {{ req.contact_company || req.contact_name || 'Verified Client' }}
                  </span>
                  <div class="flex items-center gap-1.5">
                    <button
                      type="button"
                      @click.stop="convertReqToDeal(req)"
                      class="px-2 py-0.5 rounded-md bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold transition flex items-center gap-0.5 cursor-pointer"
                      title="Convert to Deal (D)"
                    >
                      <span>Convert</span>
                      <kbd class="text-[9px] font-mono opacity-60">D</kbd>
                    </button>
                    <button
                      type="button"
                      @click.stop="openPitchPreview(req)"
                      class="px-2 py-0.5 rounded-md bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 text-[10px] font-bold transition flex items-center gap-0.5 cursor-pointer"
                      title="AI Pitch (P)"
                    >
                      <span>Pitch</span>
                      <kbd class="text-[9px] font-mono opacity-60">P</kbd>
                    </button>
                    <button
                      type="button"
                      @click.stop="dismissReq(req.id)"
                      class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 transition cursor-pointer"
                      title="Dismiss (X)"
                    >
                      <X class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Inspector Pane: Instant Live Details (Zero Latency) -->
            <div class="hidden lg:flex flex-1 sticky top-20 h-[calc(100vh-220px)] w-full">
              <RfpInspectorPane
                :requirement="focusedRfp"
                :app-meta="app_meta"
                @convert="convertReqToDeal"
                @dismiss="dismissReq"
                @open-pitch-modal="openPitchPreview"
              />
            </div>
          </div>

          <!-- Full-Width Card Grid View -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div
              v-for="req in filteredMarketRequirements"
              :key="req.id"
              class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-500/40 shadow-sm transition flex flex-col justify-between group"
            >
              <div>
                <!-- Top Row: Source, Budget & Score -->
                <div class="flex items-center justify-between gap-2 mb-2">
                  <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                      {{ req.source }}
                    </span>
                    <span class="text-[11px] font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
                      {{ req.budget }}
                    </span>
                  </div>
                  <span
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono"
                    :class="req.relevance_score >= 80 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'"
                  >
                    {{ req.relevance_score }}/100 Match
                  </span>
                </div>

                <!-- Title -->
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-2 mb-1.5 leading-snug">
                  {{ req.title }}
                </h4>

                <!-- Scope Description -->
                <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed mb-3">
                  {{ req.raw_text }}
                </p>

                <!-- Meta: Contact & Location -->
                <div class="text-[11px] text-slate-500 flex items-center gap-2 flex-wrap mb-3">
                  <span v-if="req.contact_company" class="font-semibold text-slate-700 dark:text-slate-300">
                    🏢 {{ req.contact_company }}
                  </span>
                  <span v-if="req.contact_name" class="text-slate-600 dark:text-slate-400">
                    👤 {{ req.contact_name }}
                  </span>
                  <span v-if="req.contact_email" class="text-sky-600 dark:text-sky-400 truncate">
                    ✉️ {{ req.contact_email }}
                  </span>
                  <span class="text-slate-400">🕒 {{ req.created_at }}</span>
                </div>
              </div>

              <!-- Card Action Footer -->
              <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-1.5">
                <div class="flex items-center gap-1.5">
                  <!-- Preview / Tailor Pitch -->
                  <button
                    type="button"
                    @click="openPitchPreview(req)"
                    class="px-3 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/20 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                  >
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>Pitch</span>
                  </button>

                  <!-- Quick Copy Upwork Pitch -->
                  <button
                    type="button"
                    @click="copyReqPitch(req)"
                    class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                    title="Quick Copy Upwork Proposal"
                  >
                    <Check v-if="copiedReqId === req.id" class="w-4 h-4 text-emerald-500" />
                    <Copy v-else class="w-4 h-4" />
                  </button>

                  <!-- Open Source Link -->
                  <a
                    v-if="req.url"
                    :href="req.url"
                    target="_blank"
                    class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition"
                    title="Open source link"
                  >
                    <ExternalLink class="w-4 h-4" />
                  </a>
                </div>

                <div class="flex items-center gap-1.5">
                  <!-- Convert to Deal -->
                  <button
                    type="button"
                    @click="convertReqToDeal(req)"
                    class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-sm"
                    title="Convert to active Pipeline Deal ($ USD)"
                  >
                    <ArrowRight class="w-3.5 h-3.5" />
                    <span>Convert ($)</span>
                  </button>

                  <!-- Dismiss -->
                  <button
                    type="button"
                    @click="dismissReq(req.id)"
                    class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 transition cursor-pointer"
                    title="Dismiss from feed"
                  >
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </section>

      <!-- ========================================================================= -->
      <!-- TAB 2: 💼 DEALS KANBAN & PIPELINE ($ USD)                                -->
      <!-- ========================================================================= -->
      <section v-if="activeMainTab === 'deals'" class="space-y-4">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="relative flex-1 max-w-md">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              v-model="searchQuery"
              @input="applyFilters"
              type="text"
              placeholder="Search deals, clients, tech..."
              class="w-full pl-9 pr-3 py-1.5 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500"
            />
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showQuickAdd = true"
              class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-500/20 flex items-center gap-1.5 transition cursor-pointer"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Add New Deal</span>
            </button>
          </div>
        </div>

        <!-- Horizontal Scrollable Kanban Columns -->
        <div class="flex gap-4 overflow-x-auto pb-4 custom-scrollbar">
          <KanbanColumn
            v-for="(stageData, stageKey) in stages"
            :key="stageKey"
            :stage-data="stageData"
            :stage-key="String(stageKey)"
            @select-deal="openLeadDetails"
            @proposal="openProposalModal"
            @move-stage="onMoveDealStage"
          />
        </div>

        <!-- Source ROI & Pipeline Velocity Analytics -->
        <div v-if="telemetry.source_analytics?.length" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <TrendingUp class="w-4 h-4 text-purple-500" />
              <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Source ROI & Revenue Attribution</h3>
            </div>
            <span class="text-[11px] text-slate-400 font-mono">
              Avg Close Velocity: <strong class="text-purple-600 dark:text-purple-300">{{ telemetry.avg_velocity_days }}d</strong>
            </span>
          </div>
          <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-xs">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 uppercase tracking-wider">
                  <th class="pb-2 text-left font-semibold">Source</th>
                  <th class="pb-2 text-center font-semibold">Leads</th>
                  <th class="pb-2 text-center font-semibold">Deals</th>
                  <th class="pb-2 text-center font-semibold">Won</th>
                  <th class="pb-2 text-center font-semibold">Win Rate</th>
                  <th class="pb-2 text-right font-semibold">Revenue Won ($)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="row in telemetry.source_analytics"
                  :key="row.source"
                  class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
                >
                  <td class="py-2 font-semibold text-slate-800 dark:text-slate-200 capitalize">{{ row.label }}</td>
                  <td class="py-2 text-center text-slate-600 dark:text-slate-400 font-mono">{{ row.total_leads }}</td>
                  <td class="py-2 text-center text-slate-600 dark:text-slate-400 font-mono">{{ row.deals_count }}</td>
                  <td class="py-2 text-center font-mono">
                    <span :class="row.won_count > 0 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400'">{{ row.won_count }}</span>
                  </td>
                  <td class="py-2 text-center">
                    <span
                      class="px-2 py-0.5 rounded-md font-mono font-bold text-[10px]"
                      :class="row.win_rate >= 50 ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : row.win_rate > 0 ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
                    >{{ row.win_rate }}%</span>
                  </td>
                  <td class="py-2 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                    {{ row.won_usd > 0 ? '$' + Number(row.won_usd).toLocaleString() : '—' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ========================================================================= -->
      <!-- TAB 3: 👥 LEADS DIRECTORY (FULL TABLE & BULK MANAGEMENT)                -->
      <!-- ========================================================================= -->
      <section v-if="activeMainTab === 'leads'" class="space-y-4">
        <!-- Search, Filter & Bulk Action Toolbar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center gap-2.5 flex-1 max-w-lg">
            <div class="relative flex-1">
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input
                v-model="leadSearch"
                type="text"
                placeholder="Search by prospect name, company, email..."
                class="w-full pl-9 pr-3 py-1.5 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500"
              />
            </div>
            <select
              v-model="leadSegmentFilter"
              class="px-3 py-1.5 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 font-semibold"
            >
              <option value="all">All Segments</option>
              <option value="saas_ai">🚀 SaaS & AI</option>
              <option value="ecommerce">🛍️ E-Commerce</option>
              <option value="edtech">🎓 EdTech</option>
              <option value="manufacturing">🏭 Manufacturing</option>
              <option value="general">🌐 General</option>
            </select>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button
              v-if="selectedLeadIds.length > 0"
              type="button"
              @click="bulkDeleteLeads"
              class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span>Delete Selected ({{ selectedLeadIds.length }})</span>
            </button>

            <button
              type="button"
              @click="toggleSplitView"
              class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
              :class="isSplitView
                ? 'bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30'
                : 'bg-slate-100 dark:bg-slate-850 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800'"
              :title="isSplitView ? 'Switch to Full Table View' : 'Switch to Split Cockpit View'"
            >
              <Columns class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">{{ isSplitView ? 'Split View' : 'Table View' }}</span>
            </button>

            <button
              type="button"
              @click="showQuickAdd = true"
              class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Create Prospect</span>
            </button>
          </div>
        </div>

        <!-- Split Cockpit View vs Full Table -->
        <template v-if="isSplitView">
          <div class="flex flex-col lg:flex-row gap-4 items-start">
            <!-- Left List: Dense & Keyboard Navigable (J/K) -->
            <div class="w-full lg:w-[48%] xl:w-[45%] space-y-2.5 max-h-[calc(100vh-220px)] overflow-y-auto pr-1.5 custom-scrollbar">
              <div v-if="filteredLeads.length === 0" class="p-8 text-center text-xs text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                No leads found matching your search.
              </div>

              <div
                v-for="(lead, idx) in filteredLeads"
                :key="lead.id"
                :id="`lead-item-${lead.id}`"
                @click="selectedLeadIndex = idx"
                class="p-3.5 rounded-2xl border transition-all cursor-pointer group select-none relative"
                :class="selectedLeadIndex === idx
                  ? 'bg-purple-50/80 dark:bg-purple-950/30 border-purple-500/80 dark:border-purple-400 ring-2 ring-purple-500/40 shadow-lg shadow-purple-500/10'
                  : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-slate-700'"
              >
                <!-- Active Indicator Pill -->
                <div
                  v-if="selectedLeadIndex === idx"
                  class="absolute -left-1.5 top-1/2 -translate-y-1/2 w-3 h-8 bg-purple-600 rounded-r-md shadow-sm"
                ></div>

                <div class="flex items-start justify-between gap-2 mb-1.5">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                      {{ (lead.name || 'P').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <div class="text-xs font-bold text-slate-900 dark:text-white truncate">
                        {{ lead.name }}
                      </div>
                      <div class="text-[11px] text-slate-500 font-medium truncate">
                        {{ lead.company || 'Private Client' }}
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-1.5 shrink-0">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                      {{ lead.segment }}
                    </span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-[11px]">
                      {{ lead.score }}%
                    </span>
                  </div>
                </div>

                <!-- Footer details & one-key hints -->
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1.5 border-t border-slate-100 dark:border-slate-800/80">
                  <span class="font-mono font-bold text-slate-800 dark:text-slate-200">
                    {{ lead.deal_amount || '$0' }}
                  </span>
                  <div class="flex items-center gap-1.5">
                    <button
                      type="button"
                      @click.stop="openLeadDetails(lead.id)"
                      class="px-2 py-0.5 rounded-md bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 text-[10px] font-bold transition flex items-center gap-0.5 cursor-pointer"
                      title="Inspect Profile & Cadence (Space / A)"
                    >
                      <span>Profile</span>
                      <kbd class="text-[9px] font-mono opacity-60">Space</kbd>
                    </button>
                    <button
                      v-if="lead.email"
                      type="button"
                      @click.stop="sendDirectMailto(lead)"
                      class="p-1 rounded-md text-slate-400 hover:text-sky-600 hover:bg-sky-500/10 transition cursor-pointer"
                      title="Direct Email"
                    >
                      <Mail class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click.stop="deleteLead(lead.id)"
                      class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 transition cursor-pointer"
                      title="Delete (X)"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Inspector Pane -->
            <div class="hidden lg:flex flex-1 sticky top-20 h-[calc(100vh-220px)] w-full">
              <LeadInspectorPane
                :lead="focusedLead"
                :app-meta="app_meta"
                @open-drawer="openLeadDetails"
                @convert="convertReqToDeal"
                @enrich="enrichLeadFromInspector"
                @delete="deleteLead"
              />
            </div>
          </div>
        </template>

        <!-- Full Table View -->
        <div v-else class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
          <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                  <th class="p-3.5 w-10">
                    <button type="button" @click="toggleSelectAllLeads" class="cursor-pointer">
                      <CheckSquare v-if="isAllLeadsSelected" class="w-4 h-4 text-purple-600" />
                      <Square v-else class="w-4 h-4 text-slate-400" />
                    </button>
                  </th>
                  <th class="p-3.5">Prospect & Company</th>
                  <th class="p-3.5">Contact Details</th>
                  <th class="p-3.5">Segment & Score</th>
                  <th class="p-3.5">Target Value</th>
                  <th class="p-3.5">Next Action</th>
                  <th class="p-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                <tr v-if="filteredLeads.length === 0">
                  <td colspan="7" class="p-8 text-center text-slate-400">
                    No leads found matching your search.
                  </td>
                </tr>

                <tr
                  v-for="lead in filteredLeads"
                  :key="lead.id"
                  class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition group"
                >
                  <td class="p-3.5">
                    <button type="button" @click="toggleLeadSelection(lead.id)" class="cursor-pointer">
                      <CheckSquare v-if="selectedLeadIds.includes(lead.id)" class="w-4 h-4 text-purple-600" />
                      <Square v-else class="w-4 h-4 text-slate-300 dark:text-slate-700" />
                    </button>
                  </td>

                  <td class="p-3.5">
                    <button
                      type="button"
                      @click="openLeadDetails(lead.id)"
                      class="font-bold text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition text-left cursor-pointer"
                    >
                      {{ lead.name }}
                    </button>
                    <div class="text-[11px] text-slate-500 font-medium">{{ lead.company }}</div>
                  </td>

                  <td class="p-3.5 font-mono text-[11px] text-slate-600 dark:text-slate-400">
                    <div v-if="lead.email" class="text-sky-600 dark:text-sky-400 truncate max-w-[180px]">
                      {{ lead.email }}
                    </div>
                    <div v-if="lead.phone" class="text-slate-500">{{ lead.phone }}</div>
                  </td>

                  <td class="p-3.5">
                    <div class="flex items-center gap-1.5">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                        {{ lead.segment }}
                      </span>
                      <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-[11px]">
                        {{ lead.score }}/100
                      </span>
                    </div>
                  </td>

                  <td class="p-3.5 font-mono font-extrabold text-slate-900 dark:text-slate-200">
                    {{ lead.deal_amount }}
                  </td>

                  <td class="p-3.5">
                    <div class="text-[11px] text-slate-700 dark:text-slate-300 font-medium truncate max-w-[200px]">
                      {{ lead.next_action_note || 'Initiate outreach' }}
                    </div>
                    <div v-if="lead.next_action_date" class="text-[10px] text-slate-400">
                      Due: {{ lead.next_action_date }}
                    </div>
                  </td>

                  <td class="p-3.5 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <button
                        v-if="lead.email"
                        type="button"
                        @click="sendDirectMailto(lead)"
                        class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-500/10 transition"
                        title="Send Email"
                      >
                        <Mail class="w-3.5 h-3.5" />
                      </button>
                      <button
                        type="button"
                        @click="openLeadDetails(lead.id)"
                        class="p-1.5 rounded-lg text-slate-500 hover:text-purple-600 hover:bg-purple-500/10 transition"
                        title="View Full Profile"
                      >
                        <ExternalLink class="w-3.5 h-3.5" />
                      </button>
                      <button
                        type="button"
                        @click="deleteLead(lead.id)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 transition"
                        title="Delete Lead"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ========================================================================= -->
      <!-- TAB 4: 🚀 OUTBOUND CAMPAIGNS & AUTOMATED DRIP SEQUENCES                  -->
      <!-- ========================================================================= -->
      <section v-if="activeMainTab === 'campaigns'" class="space-y-6">
        <!-- Campaign Performance KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4">
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
              <span>Active Sequences</span>
              <Send class="w-3.5 h-3.5 text-purple-500" />
            </div>
            <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 font-mono">
              {{ campaigns_stats?.active_sequences || 0 }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">In-flight 4-step cadences</div>
          </div>

          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
              <span>Emails Dispatched</span>
              <Mail class="w-3.5 h-3.5 text-sky-500" />
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono">
              {{ campaigns_stats?.total_emails_sent || 0 }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Total trackable touches sent</div>
          </div>

          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
              <span>Open Rate</span>
              <Eye class="w-3.5 h-3.5 text-emerald-500" />
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
              {{ campaigns_stats?.open_rate || 0 }}%
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Tracked 1x1 pixel opens</div>
          </div>

          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
              <span>Click Rate</span>
              <MousePointerClick class="w-3.5 h-3.5 text-indigo-500" />
            </div>
            <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 font-mono">
              {{ campaigns_stats?.click_rate || 0 }}%
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Book & portfolio link clicks</div>
          </div>

          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
              <span>Reply Conversion</span>
              <CheckCircle2 class="w-3.5 h-3.5 text-cyan-500" />
            </div>
            <div class="text-2xl font-extrabold text-cyan-600 dark:text-cyan-400 font-mono">
              {{ campaigns_stats?.reply_rate || 0 }}%
            </div>
            <div class="text-[11px] text-slate-400 mt-1">{{ campaigns_stats?.replied_sequences || 0 }} prospects replied</div>
          </div>
        </div>

        <!-- Upcoming Automated Touches Queue -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Clock class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Automated Outbound Queue (Next Scheduled Touches)
              </h3>
            </div>
            <div class="flex items-center gap-2 text-[11px] text-slate-500">
              <span class="inline-flex items-center gap-1.5 text-emerald-600 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Worker Active (10-min cron)
              </span>
            </div>
          </div>

          <div v-if="!campaigns_stats?.upcoming_queue?.length" class="p-10 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
            <Send class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
            <p class="font-semibold text-slate-600 dark:text-slate-400">No scheduled sequence touches pending.</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Open any lead from the Directory or Hunter tab to approve and launch a 4-step cadence.</p>
          </div>

          <div v-else class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-xs">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 uppercase tracking-wider text-left">
                  <th class="pb-2.5 font-semibold">Prospect & Company</th>
                  <th class="pb-2.5 font-semibold text-center">Step #</th>
                  <th class="pb-2.5 font-semibold">Step Title & Subject</th>
                  <th class="pb-2.5 font-semibold">Scheduled Execution</th>
                  <th class="pb-2.5 font-semibold text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="item in campaigns_stats.upcoming_queue"
                  :key="item.id"
                  class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition"
                >
                  <td class="py-3">
                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ item.lead_name }}</div>
                    <div class="text-[11px] text-slate-400">{{ item.company || item.email }}</div>
                  </td>
                  <td class="py-3 text-center">
                    <span class="w-5 h-5 inline-flex items-center justify-center rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 font-mono font-bold text-[10px]">
                      {{ item.step_number }}
                    </span>
                  </td>
                  <td class="py-3 max-w-md">
                    <div class="font-semibold text-slate-700 dark:text-slate-300 truncate">{{ item.title }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ item.subject }}</div>
                  </td>
                  <td class="py-3 text-slate-600 dark:text-slate-400 font-mono text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                      {{ item.scheduled_for_human }}
                    </span>
                  </td>
                  <td class="py-3 text-right">
                    <button
                      type="button"
                      @click="openLeadDetails(item.lead_id)"
                      class="px-2.5 py-1 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 text-xs font-bold transition cursor-pointer"
                    >
                      Manage Cadence
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ========================================================================= -->
      <!-- TAB 5: ⚡ SMART INGEST & AI PROPOSAL STUDIO                               -->
      <!-- ========================================================================= -->
      <section v-if="activeMainTab === 'studio'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Input Form Column -->
          <div class="lg:col-span-5 space-y-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                  <Sparkles class="w-4 h-4" />
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">AI Project Extractor & Proposal Studio</h3>
                  <p class="text-[11px] text-slate-500">Paste an Upwork link or raw client scope</p>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Project Link or Raw Scope Text
                </label>
                <textarea
                  v-model="studioInput"
                  rows="7"
                  placeholder="Paste URL (e.g. https://www.upwork.com/jobs/...) or paste full client RFP text here..."
                  class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 leading-relaxed font-mono"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Source</label>
                  <select
                    v-model="studioSource"
                    class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold"
                  >
                    <option value="upwork">Upwork</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="hackernews">Hacker News</option>
                    <option value="direct_rfp">Direct RFP</option>
                  </select>
                </div>

                <div>
                  <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Client Name (optional)</label>
                  <input
                    v-model="studioContactName"
                    type="text"
                    placeholder="e.g. David"
                    class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Company / Project Name (optional)</label>
                <input
                  v-model="studioCompanyName"
                  type="text"
                  placeholder="e.g. NovaTech Labs"
                  class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs"
                />
              </div>

              <button
                type="button"
                @click="runSmartIngest"
                :disabled="isAnalyzing || !studioInput.trim()"
                class="w-full py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-500/20 flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-50"
              >
                <Sparkles class="w-4 h-4" :class="{ 'animate-spin': isAnalyzing }" />
                <span>{{ isAnalyzing ? 'Analyzing with GPT-4o-mini...' : 'Analyze Scope & Generate Proposal' }}</span>
              </button>
            </div>
          </div>

          <!-- Studio Live Results Column -->
          <div class="lg:col-span-7">
            <div v-if="!studioResult" class="p-12 text-center text-xs text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 h-full flex flex-col items-center justify-center">
              <Sparkles class="w-10 h-10 text-purple-500/30 mb-2" />
              <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Ready for Analysis</h4>
              <p class="mt-1 max-w-sm">Paste any RFP or Upwork job on the left. The studio will extract the tech stack, client pain points, USD budget, and write 3 outreach variations.</p>
            </div>

            <div v-else class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
              <!-- Meta summary -->
              <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ studioResult.requirement?.title }}</h4>
                  <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                    <span class="font-bold text-emerald-600 font-mono">{{ studioResult.requirement?.budget }}</span>
                    <span>•</span>
                    <span class="text-purple-600 font-bold">{{ studioResult.requirement?.relevance_score }}/100 Match</span>
                    <span>•</span>
                    <span class="uppercase font-bold">{{ studioResult.requirement?.matched_segment }}</span>
                  </div>
                </div>
                <button
                  type="button"
                  @click="convertReqToDeal(studioResult.requirement?.id)"
                  class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 transition cursor-pointer shadow-sm"
                >
                  <ArrowRight class="w-3.5 h-3.5" />
                  <span>Send to Pipeline Deals</span>
                </button>
              </div>

              <!-- Tech stack & architecture -->
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="flex flex-wrap items-center gap-1.5">
                  <span class="text-[10px] uppercase font-bold text-slate-400 mr-1">Stack:</span>
                  <span
                    v-for="t in studioResult.pitch_data?.detected_tech_stack"
                    :key="t"
                    class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-300 text-[11px] font-bold"
                  >
                    {{ t }}
                  </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                  <strong class="text-slate-800 dark:text-slate-200">Recommended Arch:</strong>
                  {{ studioResult.pitch_data?.suggested_architecture }}
                </p>
              </div>

              <!-- Tabbed Pitch Outputs -->
              <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pt-1">
                <button
                  type="button"
                  @click="activeStudioTab = 'upwork'"
                  class="px-3 py-1.5 text-xs font-bold border-b-2 transition"
                  :class="activeStudioTab === 'upwork' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500'"
                >
                  Upwork Proposal
                </button>
                <button
                  type="button"
                  @click="activeStudioTab = 'email'"
                  class="px-3 py-1.5 text-xs font-bold border-b-2 transition"
                  :class="activeStudioTab === 'email' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500'"
                >
                  Cold Email
                </button>
                <button
                  type="button"
                  @click="activeStudioTab = 'linkedin'"
                  class="px-3 py-1.5 text-xs font-bold border-b-2 transition"
                  :class="activeStudioTab === 'linkedin' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500'"
                >
                  LinkedIn / X DM
                </button>
              </div>

              <!-- Pitch Display -->
              <div>
                <textarea
                  v-if="activeStudioTab === 'upwork'"
                  :value="studioResult.pitch_data?.upwork_proposal"
                  rows="9"
                  readonly
                  class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 leading-relaxed font-sans"
                ></textarea>

                <div v-else-if="activeStudioTab === 'email'" class="space-y-2">
                  <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Subject: {{ studioResult.pitch_data?.email_subject }}
                  </div>
                  <textarea
                    :value="studioResult.pitch_data?.email_pitch"
                    rows="8"
                    readonly
                    class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 leading-relaxed font-sans"
                  ></textarea>
                </div>

                <textarea
                  v-else-if="activeStudioTab === 'linkedin'"
                  :value="studioResult.pitch_data?.linkedin_dm"
                  rows="5"
                  readonly
                  class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 leading-relaxed font-sans"
                ></textarea>
              </div>

              <!-- Copy Pitch Button -->
              <div class="flex justify-end">
                <button
                  type="button"
                  @click="copyActiveStudioPitch"
                  class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold flex items-center gap-1.5 transition cursor-pointer shadow-sm"
                >
                  <Check v-if="copiedStudioPitch" class="w-3.5 h-3.5" />
                  <Copy v-else class="w-3.5 h-3.5" />
                  <span>{{ copiedStudioPitch ? 'Copied to Clipboard!' : 'Copy Proposal Draft' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Modals & Drawers -->
    <PitchPreviewModal
      :show="showPitchModal"
      :requirement="selectedReqForPitch"
      @close="showPitchModal = false"
      @convert="convertReqToDeal"
    />

    <LeadDrawer
      :show="showDrawer"
      :lead-id="selectedLeadForDrawer"
      @close="showDrawer = false"
      @updated="(msg) => { triggerToast(msg || 'Updated'); router.reload() }"
      @open-proposal="openProposalModal"
    />

    <QuickAddModal
      :show="showQuickAdd"
      @close="showQuickAdd = false"
      @success="(msg: string) => { triggerToast(msg); showQuickAdd = false; router.reload() }"
    />

    <CsvImportModal
      :show="showCsvImport"
      @close="showCsvImport = false"
      @success="(msg: string) => { triggerToast(msg); showCsvImport = false; router.reload() }"
    />

    <ProposalModal
      :show="showProposalModal"
      :deal-id="selectedDealForProposal"
      @close="showProposalModal = false"
      @saved="(msg: string) => { triggerToast(msg); router.reload() }"
    />

    <SecuritySettingsModal
      v-if="showSecurityModal"
      @close="showSecurityModal = false"
      @success="(msg: string) => triggerToast(msg)"
    />

    <!-- Convert RFP to Deal Modal (collects real contact data, no fake placeholders) -->
    <ConvertToDealModal
      :show="showConvertModal"
      :req="selectedReqForConvert"
      @close="showConvertModal = false"
      @converted="onConvertSuccess"
    />

    <!-- Global Floating Shortcut & Power-User Dock -->
    <FloatingShortcutDock
      :active-tab="activeMainTab"
      :current-index="activeMainTab === 'hunter' ? selectedRfpIndex : selectedLeadIndex"
      :total-count="activeMainTab === 'hunter' ? filteredMarketRequirements.length : filteredLeads.length"
      :is-split-view="isSplitView"
      @toggle-help="showShortcutsModal = true"
      @toggle-command="showCommandPalette = true"
      @toggle-split="toggleSplitView"
    />

    <!-- Global Spotlight Command Palette (Ctrl+K) -->
    <CommandPalette
      :show="showCommandPalette"
      :rfps="filteredMarketRequirements"
      :deals="all_deals"
      :leads="all_leads || []"
      :active-tab="activeMainTab"
      @close="showCommandPalette = false"
      @select-rfp="handleCommandSelectRfp"
      @select-deal="handleCommandSelectDeal"
      @select-lead="handleCommandSelectLead"
      @action="handleCommandAction"
      @switch-tab="(tabKey) => switchMainTab(tabKey as any)"
    />

    <!-- Keyboard Shortcuts Cheat Sheet Modal (?) -->
    <KeyboardShortcutsModal
      :show="showShortcutsModal"
      @close="showShortcutsModal = false"
    />

    <!-- Global Floating Toast Notification -->
    <div
      v-if="toast"
      class="fixed bottom-5 right-5 z-50 px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold shadow-2xl flex items-center gap-2 animate-in slide-in-from-bottom-3 duration-200 border border-slate-800 dark:border-slate-200"
    >
      <CheckCircle2 class="w-4 h-4 text-emerald-400 dark:text-emerald-600" />
      <span>{{ toast.message }}</span>
    </div>
  </div>
</template>
