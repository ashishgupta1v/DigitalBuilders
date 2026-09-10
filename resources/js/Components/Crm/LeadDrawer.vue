<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import {
  X, Building2, User, Phone, Mail, MessageSquare, Calendar, Sparkles,
  CreditCard, CheckCircle2, AlertCircle, ArrowRight, DollarSign, Clock,
  FileText, Send, Copy, Check, ExternalLink, ChevronRight, Edit3
} from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  leadId: number | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'updated', message?: string): void
  (e: 'openOutreach', lead: any, touch: number): void
  (e: 'openProposal', dealId: number): void
}>()

const leadData = ref<any>(null)
const loading = ref(false)
const activeTab = ref<'timeline' | 'commercials' | 'notes'>('timeline')

// Activity Logging
const newNote = ref('')
const newNoteType = ref('note')
const loggingActivity = ref(false)

// Wire Logging Modal
const showWireModal = ref(false)
const wireAmount = ref('')
const wireUtr = ref('')
const wireMethod = ref('rtgs')
const wireNotes = ref('')
const loggingWire = ref(false)

// Payment Link Generation
const generatingLink = ref(false)
const generatedLink = ref<string | null>(null)
const selectedPercentage = ref(40)
const linkCopied = ref(false)

const noteTemplates = [
  { label: '📞 Discovery Call Done', text: 'Held 20-min architecture discovery call. Mapped operational bottlenecks and agreed on fixed-price scope proposal.' },
  { label: '💬 Sent Price Book', text: 'Shared 2026 Price Book & Estimator link on WhatsApp. Client reviewing with partner.' },
  { label: '📄 Proposal Sent', text: 'Delivered fixed-scope engineering proposal. Awaiting sign-off and kickoff deposit.' },
  { label: '⏳ Follow-up Tuesday', text: 'Client requested follow-up next Tuesday afternoon to finalize contract.' },
]

const stages = [
  { key: 'new', label: 'New', num: 1 },
  { key: 'contacted', label: 'Contacted', num: 2 },
  { key: 'qualified', label: 'Qualified', num: 3 },
  { key: 'discovery_done', label: 'Discovery', num: 4 },
  { key: 'proposal_sent', label: 'Proposal', num: 5 },
  { key: 'negotiation', label: 'Negotiate', num: 6 },
  { key: 'closed_won', label: 'Won', num: 7 },
  { key: 'closed_lost', label: 'Lost', num: 8 },
]

const fetchLeadDetails = async () => {
  if (!props.leadId) return
  loading.value = true
  try {
    const res = await fetch(`/crm/leads/${props.leadId}`)
    const data = await res.json()
    leadData.value = data
  } catch (err) {
    console.error('Failed to fetch lead details', err)
  } finally {
    loading.value = false
  }
}

watch(
  () => [props.show, props.leadId],
  () => {
    if (props.show && props.leadId) {
      fetchLeadDetails()
    }
  },
  { immediate: true }
)

const activeDeal = computed(() => {
  return leadData.value?.deals?.[0] || null
})

const currentStageIndex = computed(() => {
  if (!activeDeal.value) return 0
  return stages.findIndex(s => s.key === activeDeal.value.stage)
})

const cleanPhone = computed(() => {
  if (!leadData.value?.lead?.phone) return ''
  const digits = leadData.value.lead.phone.replace(/[^0-9]/g, '')
  if (digits.length === 10) return `91${digits}`
  return digits
})

const changeDealStage = async (newStage: string) => {
  if (!activeDeal.value) return
  try {
    const res = await fetch(`/crm/deals/${activeDeal.value.id}/stage`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ stage: newStage }),
    })
    const data = await res.json()
    if (data.success) {
      fetchLeadDetails()
      emit('updated', `Stage updated to ${newStage.replace('_', ' ').toUpperCase()}`)
    }
  } catch (err) {
    console.error('Error advancing stage', err)
  }
}

const applyTemplate = (tplText: string) => {
  newNote.value = tplText
}

const copiedPitch = ref(false)
const copyOutreachPitch = async () => {
  const name = leadData.value?.lead?.name || 'there'
  const company = leadData.value?.lead?.company || 'your team'
  const pitch = `Hi ${name},\n\nI'm Ashish, founder and lead architect at DigitalBuilders (https://www.digitalbuilders.in). Saw your project scope for ${company}.\n\nWe specialize in engineering production SaaS MVPs, custom web apps, and AI integrations in 4-6 weeks with 100% code ownership and fixed milestone pricing.\n\n• Book a 15-min discovery call: https://www.digitalbuilders.in/book\n• Or run your feature scope through our sprint estimator: https://www.digitalbuilders.in/estimator\n\nBest regards,\nAshish Gupta | DigitalBuilders`
  await navigator.clipboard.writeText(pitch)
  copiedPitch.value = true
  setTimeout(() => { copiedPitch.value = false }, 2500)
}

const copiedBooking = ref(false)
const copyBookingLink = async () => {
  await navigator.clipboard.writeText('https://www.digitalbuilders.in/book')
  copiedBooking.value = true
  setTimeout(() => { copiedBooking.value = false }, 2500)
}

const sendMailto = () => {
  if (!leadData.value?.lead?.email) return
  const name = leadData.value.lead.name || 'there'
  const company = leadData.value.lead.company || 'Your Project'
  const subject = encodeURIComponent(`Architecture & Timeline Proposal for ${company}`)
  const body = encodeURIComponent(`Hi ${name},\n\nI'm Ashish Gupta, founder & lead architect at DigitalBuilders (https://www.digitalbuilders.in).\n\nWanted to connect regarding your software development scope. We specialize in fixed-price 4-6 week sprints with complete source code ownership.\n\nFeel free to pick a 15-min slot on my calendar: https://www.digitalbuilders.in/book\n\nBest regards,\nAshish Gupta\nFounder & Lead Architect, DigitalBuilders`)
  window.open(`mailto:${leadData.value.lead.email}?subject=${subject}&body=${body}`, '_blank')
}

const addActivityNote = async () => {
  if (!newNote.value.trim() || !leadData.value?.lead?.id) return
  loggingActivity.value = true
  try {
    const res = await fetch('/crm/activities', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        lead_id: leadData.value.lead.id,
        deal_id: activeDeal.value?.id,
        type: newNoteType.value,
        subject: `${newNoteType.value === 'call' ? 'Call Logged' : 'Meeting / Call Note'} (${new Date().toLocaleDateString()})`,
        description: newNote.value.trim(),
      }),
    })
    const data = await res.json()
    if (data.success) {
      newNote.value = ''
      fetchLeadDetails()
      emit('updated', 'Note recorded in timeline')
    }
  } catch (err) {
    console.error('Error logging activity note', err)
  } finally {
    loggingActivity.value = false
  }
}

const generatePaymentLink = async (percentage: number = 40) => {
  if (!activeDeal.value) return
  selectedPercentage.value = percentage
  generatingLink.value = true
  generatedLink.value = null
  try {
    const targetAmount = Math.round((activeDeal.value.amount * percentage) / 100)
    const res = await fetch(`/crm/deals/${activeDeal.value.id}/payment-link`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        amount: targetAmount,
        percentage: percentage,
        description: `${activeDeal.value.title} — ${percentage}% Kickoff Advance`,
      }),
    })
    const data = await res.json()
    if (data.success) {
      generatedLink.value = data.payment_url
      fetchLeadDetails()
      emit('updated', `Payment link for ${percentage}% generated!`)
    }
  } catch (err) {
    console.error('Failed to create payment link', err)
  } finally {
    generatingLink.value = false
  }
}

const copyPaymentLink = () => {
  if (!generatedLink.value) return
  navigator.clipboard.writeText(generatedLink.value)
  linkCopied.value = true
  setTimeout(() => (linkCopied.value = false), 2200)
}

const sharePaymentLinkOnWhatsApp = () => {
  if (!generatedLink.value || !cleanPhone.value) return
  const msg = `Hi ${leadData.value?.lead?.name}, here is the secure checkout link for your ${selectedPercentage.value}% kickoff deposit for ${activeDeal.value?.title}:\n\n${generatedLink.value}\n\nOnce received, your sprint timeline kicks off immediately.`
  const url = `https://wa.me/${cleanPhone.value}?text=${encodeURIComponent(msg)}`
  window.open(url, '_blank')
}

const submitWirePayment = async () => {
  if (!activeDeal.value || !wireAmount.value || !wireUtr.value) return
  loggingWire.value = true
  try {
    const res = await fetch(`/crm/deals/${activeDeal.value.id}/wire`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        amount: parseFloat(wireAmount.value),
        transaction_utr: wireUtr.value.trim(),
        payment_method: wireMethod.value,
        notes: wireNotes.value.trim(),
      }),
    })
    const data = await res.json()
    if (data.success) {
      showWireModal.value = false
      wireAmount.value = ''
      wireUtr.value = ''
      wireNotes.value = ''
      fetchLeadDetails()
      emit('updated', 'Bank wire confirmed! Deal moved to Closed Won.')
    }
  } catch (err) {
    console.error('Error recording wire', err)
  } finally {
    loggingWire.value = false
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-hidden bg-slate-900/60 dark:bg-black/70 backdrop-blur-xs flex justify-end">
    <div class="crm-cockpit w-full max-w-2xl bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl h-full flex flex-col overflow-hidden text-slate-800 dark:text-slate-200">
      <!-- Top Drawer Header -->
      <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-950/90 flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-br from-cyan-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white font-extrabold text-sm sm:text-base shadow-md shadow-cyan-500/20 shrink-0">
            {{ leadData?.lead?.name?.substring(0, 2)?.toUpperCase() || 'DB' }}
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate">{{ leadData?.lead?.name || 'Loading...' }}</h2>
              <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shrink-0">
                {{ leadData?.lead?.segment }}
              </span>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate">
              {{ leadData?.lead?.company || 'Direct Contact' }} • {{ leadData?.lead?.role_title || 'Decision Maker' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
          <!-- Score Meter -->
          <div class="text-right">
            <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">Qualification</div>
            <div class="text-xs sm:text-sm font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 justify-end font-mono">
              <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
              <span>{{ leadData?.lead?.score || 50 }}/100</span>
            </div>
          </div>

          <button @click="emit('close')" class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Interactive Stage Stepper Bar -->
      <div class="px-3 sm:px-6 py-3 bg-slate-100/70 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800/80 overflow-x-auto custom-scrollbar">
        <div class="flex items-center gap-2 min-w-max">
          <button
            v-for="(st, idx) in stages"
            :key="st.key"
            type="button"
            @click="changeDealStage(st.key)"
            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 group border"
            :class="activeDeal?.stage === st.key
              ? 'bg-cyan-50 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border-cyan-400 dark:border-cyan-500/60 font-bold shadow-sm dark:shadow-cyan-500/10'
              : (idx < currentStageIndex
                ? 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700/60'
                : 'bg-slate-50 dark:bg-slate-950/40 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-800/80 hover:text-slate-700 dark:hover:text-slate-300')"
          >
            <span
              class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]"
              :class="activeDeal?.stage === st.key
                ? 'bg-cyan-600 dark:bg-cyan-400 text-white dark:text-slate-950 font-bold'
                : (idx < currentStageIndex ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500')"
            >
              {{ idx < currentStageIndex ? '✓' : (idx + 1) }}
            </span>
            <span>{{ st.label }}</span>
          </button>
        </div>
      </div>

      <!-- Quick Action Hub (WhatsApp Trigger + AI Recommendation) -->
      <div class="p-3.5 sm:p-4 bg-gradient-to-r from-cyan-500/5 via-slate-50 to-purple-500/5 dark:from-cyan-950/30 dark:via-slate-900 dark:to-purple-950/30 border-b border-slate-200 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          <div class="flex items-start gap-2.5">
            <div class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 mt-0.5 shrink-0">
              <Sparkles class="w-4 h-4" />
            </div>
            <div>
              <div class="text-xs font-bold text-slate-900 dark:text-slate-200">
                AI Next Best Action:
                <span class="text-amber-700 dark:text-amber-300 font-medium">
                  {{ leadData?.lead?.next_action_note || 'Dispatch Touch 1 on WhatsApp' }}
                </span>
              </div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-1 flex-wrap">
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ leadData?.lead?.phone }}</span>
                <span>•</span>
                <span class="text-slate-700 dark:text-slate-300">Touchpoint {{ leadData?.lead?.touchpoint_count || 0 }}/5</span>
                <span v-if="leadData?.lead?.last_contact_date" class="text-slate-400 dark:text-slate-500">
                  ({{ leadData.lead.last_contact_date }})
                </span>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-1.5 self-end sm:self-auto shrink-0 w-full sm:w-auto flex-wrap sm:flex-nowrap">
            <!-- Send Email -->
            <button
              v-if="leadData?.lead?.email"
              type="button"
              @click="sendMailto"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/20 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
              title="Open email draft"
            >
              <Mail class="w-3.5 h-3.5" />
              <span>Email</span>
            </button>

            <!-- Copy Pitch -->
            <button
              type="button"
              @click="copyOutreachPitch"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border"
              :class="copiedPitch ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
              title="Copy customized proposal"
            >
              <Check v-if="copiedPitch" class="w-3.5 h-3.5" />
              <Copy v-else class="w-3.5 h-3.5" />
              <span>{{ copiedPitch ? 'Copied' : 'Pitch' }}</span>
            </button>

            <!-- Copy 15-Min Booking Link -->
            <button
              type="button"
              @click="copyBookingLink"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border"
              :class="copiedBooking ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-300 border-purple-500/20'"
              title="Copy Calendly booking link (https://www.digitalbuilders.in/book)"
            >
              <Calendar class="w-3.5 h-3.5" />
              <span>{{ copiedBooking ? 'Copied Link' : 'Booking' }}</span>
            </button>

            <!-- Proposal Button -->
            <button
              v-if="activeDeal"
              type="button"
              @click="emit('openProposal', activeDeal.id)"
              class="px-3 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-500/20 flex items-center justify-center gap-1.5 transition cursor-pointer"
            >
              <FileText class="w-3.5 h-3.5" />
              <span>{{ activeDeal?.stage === 'proposal_sent' ? 'View Proposal' : 'Proposal' }}</span>
            </button>

            <!-- WhatsApp (Secondary Fallback) -->
            <button
              v-if="cleanPhone"
              type="button"
              @click="emit('openOutreach', leadData.lead, (leadData?.lead?.touchpoint_count || 0) + 1)"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-bold flex items-center justify-center gap-1.5 transition cursor-pointer"
              title="WhatsApp outreach"
            >
              <MessageSquare class="w-3.5 h-3.5" />
              <span>WA</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Content Tabs -->
      <div class="px-4 sm:px-6 pt-3 flex gap-4 sm:gap-6 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-950/30 overflow-x-auto custom-scrollbar">
        <button
          @click="activeTab = 'timeline'"
          class="pb-3 transition border-b-2 cursor-pointer flex items-center gap-2 shrink-0"
          :class="activeTab === 'timeline' ? 'border-cyan-500 dark:border-cyan-400 text-cyan-600 dark:text-cyan-300 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
        >
          <Clock class="w-3.5 h-3.5" />
          <span>Timeline & Notes ({{ leadData?.activities?.length || 0 }})</span>
        </button>
        <button
          @click="activeTab = 'commercials'"
          class="pb-3 transition border-b-2 cursor-pointer flex items-center gap-2 shrink-0"
          :class="activeTab === 'commercials' ? 'border-cyan-500 dark:border-cyan-400 text-cyan-600 dark:text-cyan-300 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
        >
          <CreditCard class="w-3.5 h-3.5" />
          <span>Commercials & Closing ({{ activeDeal?.formatted_amount }})</span>
        </button>
      </div>

      <!-- Tab Content Area -->
      <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 sm:space-y-5 custom-scrollbar">
        <!-- Tab 1: Timeline & Notes -->
        <div v-if="activeTab === 'timeline'" class="space-y-4">
          <!-- Add Note Box -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Log Interaction / Call Notes</span>
              <select
                v-model="newNoteType"
                class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300 font-medium"
              >
                <option value="note">Internal Note</option>
                <option value="call">Phone Call Log</option>
                <option value="meeting">Discovery Screen Share</option>
              </select>
            </div>

            <!-- Quick Template Chips -->
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Quick:</span>
              <button
                v-for="tpl in noteTemplates"
                :key="tpl.label"
                type="button"
                @click="applyTemplate(tpl.text)"
                class="text-[10px] px-2 py-0.5 rounded-lg bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-300 border border-slate-200 dark:border-slate-800 transition cursor-pointer"
              >
                {{ tpl.label }}
              </button>
            </div>

            <textarea
              v-model="newNote"
              rows="2"
              placeholder="e.g. Talked with owner; agreed to start with ₹19,000 Discovery Sprint..."
              class="w-full p-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 rounded-xl text-xs text-slate-900 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-500 placeholder-slate-400"
            ></textarea>

            <div class="flex justify-end">
              <button
                type="button"
                @click="addActivityNote"
                :disabled="loggingActivity || !newNote.trim()"
                class="px-4 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
              >
                <span>{{ loggingActivity ? 'Logging...' : 'Save Activity Note' }}</span>
              </button>
            </div>
          </div>

          <!-- Chronological Stream -->
          <div class="space-y-3">
            <div v-if="leadData?.activities?.length === 0" class="text-center py-8 text-xs text-slate-400 dark:text-slate-500">
              No interactions logged yet. Send Touch 1 to kick off the cadence!
            </div>

            <div
              v-for="act in leadData?.activities"
              :key="act.id"
              class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800/80 text-xs space-y-1.5"
            >
              <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-200">
                  <span
                    class="w-2.5 h-2.5 rounded-full"
                    :class="act.type === 'whatsapp' ? 'bg-emerald-500' : (act.type === 'payment' ? 'bg-cyan-500' : 'bg-indigo-500')"
                  />
                  <span>{{ act.subject }}</span>
                </div>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ act.created_at }}</span>
              </div>
              <p v-if="act.description" class="text-slate-700 dark:text-slate-300 text-xs whitespace-pre-wrap pl-4 border-l-2 border-slate-200 dark:border-slate-800 font-sans leading-relaxed">
                {{ act.description }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tab 2: Commercials & Payment Closing -->
        <div v-else-if="activeTab === 'commercials'" class="space-y-5">
          <!-- Financial KPI Cards -->
          <div class="grid grid-cols-1 xs:grid-cols-3 gap-2.5 sm:gap-3">
            <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800">
              <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-slate-500 font-bold">Deal Total</div>
              <div class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white mt-1 truncate font-mono">
                {{ activeDeal?.formatted_amount || '₹0' }}
              </div>
            </div>

            <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800">
              <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-slate-500 font-bold">Collected</div>
              <div class="text-base sm:text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 truncate font-mono">
                {{ activeDeal?.formatted_paid || '₹0' }}
              </div>
            </div>

            <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800">
              <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-slate-500 font-bold">Pending</div>
              <div class="text-base sm:text-lg font-extrabold text-amber-600 dark:text-amber-400 mt-1 truncate font-mono">
                {{ activeDeal?.currency === 'USD' ? '$' + activeDeal?.pending_balance : '₹' + Number(activeDeal?.pending_balance || 0).toLocaleString('en-IN') }}
              </div>
            </div>
          </div>

          <!-- Proposal Action Card -->
          <div v-if="activeDeal" class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-950/40 dark:to-slate-950 border border-purple-200 dark:border-purple-800/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="p-2.5 rounded-xl bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/30 shrink-0">
                <FileText class="w-5 h-5" />
              </div>
              <div>
                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Client Engineering Proposal</h4>
                <p class="text-[11px] text-slate-600 dark:text-slate-400">
                  {{ activeDeal.stage === 'proposal_sent' ? 'Formal scope document dispatched (75% win probability)' : 'Generate executive deliverables & milestone scope' }}
                </p>
              </div>
            </div>

            <button
              type="button"
              @click="emit('openProposal', activeDeal.id)"
              class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-purple-500/20 cursor-pointer shrink-0"
            >
              <FileText class="w-3.5 h-3.5" />
              <span>{{ activeDeal.stage === 'proposal_sent' ? 'View Proposal' : 'Generate Proposal' }}</span>
            </button>
          </div>

          <!-- Payment Action Hub -->
          <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                Generate Instant Deposit Link
              </h4>
              <span class="text-[11px] text-slate-500 dark:text-slate-400">Razorpay (UPI/Cards) or Stripe</span>
            </div>

            <!-- Percentage Deposit Presets -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <button
                type="button"
                @click="generatePaymentLink(20)"
                :disabled="generatingLink"
                class="p-2.5 rounded-xl border text-center transition cursor-pointer text-xs"
                :class="selectedPercentage === 20 ? 'bg-cyan-50 dark:bg-cyan-500/10 border-cyan-400 dark:border-cyan-500/40 text-cyan-700 dark:text-cyan-300 font-bold' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'"
              >
                <div class="font-bold">20% Discovery</div>
                <div class="text-[10px] text-slate-500 mt-0.5 font-mono">
                  {{ activeDeal?.currency === 'USD' ? '$' + Math.round(activeDeal?.amount * 0.2) : '₹' + Number(Math.round(activeDeal?.amount * 0.2)).toLocaleString('en-IN') }}
                </div>
              </button>

              <button
                type="button"
                @click="generatePaymentLink(40)"
                :disabled="generatingLink"
                class="p-2.5 rounded-xl border text-center transition cursor-pointer text-xs"
                :class="selectedPercentage === 40 ? 'bg-cyan-50 dark:bg-cyan-500/10 border-cyan-400 dark:border-cyan-500/40 text-cyan-700 dark:text-cyan-300 font-bold' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'"
              >
                <div class="font-bold">40% Kickoff</div>
                <div class="text-[10px] text-slate-500 mt-0.5 font-mono">
                  {{ activeDeal?.currency === 'USD' ? '$' + Math.round(activeDeal?.amount * 0.4) : '₹' + Number(Math.round(activeDeal?.amount * 0.4)).toLocaleString('en-IN') }}
                </div>
              </button>

              <button
                type="button"
                @click="generatePaymentLink(50)"
                :disabled="generatingLink"
                class="p-2.5 rounded-xl border text-center transition cursor-pointer text-xs"
                :class="selectedPercentage === 50 ? 'bg-cyan-50 dark:bg-cyan-500/10 border-cyan-400 dark:border-cyan-500/40 text-cyan-700 dark:text-cyan-300 font-bold' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'"
              >
                <div class="font-bold">50% Milestone</div>
                <div class="text-[10px] text-slate-500 mt-0.5 font-mono">
                  {{ activeDeal?.currency === 'USD' ? '$' + Math.round(activeDeal?.amount * 0.5) : '₹' + Number(Math.round(activeDeal?.amount * 0.5)).toLocaleString('en-IN') }}
                </div>
              </button>

              <button
                type="button"
                @click="generatePaymentLink(100)"
                :disabled="generatingLink"
                class="p-2.5 rounded-xl border text-center transition cursor-pointer text-xs"
                :class="selectedPercentage === 100 ? 'bg-cyan-50 dark:bg-cyan-500/10 border-cyan-400 dark:border-cyan-500/40 text-cyan-700 dark:text-cyan-300 font-bold' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'"
              >
                <div class="font-bold">100% Full Fee</div>
                <div class="text-[10px] text-slate-500 mt-0.5 font-mono">
                  {{ activeDeal?.formatted_amount }}
                </div>
              </button>
            </div>

            <!-- Display generated link if ready -->
            <div v-if="generatedLink" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-300 dark:border-emerald-500/30 text-xs space-y-2.5">
              <div class="font-bold text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                  <span>{{ selectedPercentage }}% Payment Link Ready:</span>
                </div>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Auto-closes deal on payment</span>
              </div>

              <div class="flex items-center gap-2">
                <input
                  readonly
                  :value="generatedLink"
                  class="flex-1 p-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-[11px] font-mono text-slate-800 dark:text-slate-200 select-all"
                />
                <!-- Copy Link Button -->
                <button
                  type="button"
                  @click="copyPaymentLink"
                  class="px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
                >
                  <component :is="linkCopied ? Check : Copy" class="w-3.5 h-3.5" :class="linkCopied ? 'text-emerald-500' : ''" />
                  <span>{{ linkCopied ? 'Copied!' : 'Copy' }}</span>
                </button>

                <!-- Share on WhatsApp Button -->
                <button
                  type="button"
                  @click="sharePaymentLinkOnWhatsApp"
                  class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                >
                  <Send class="w-3.5 h-3.5" />
                  <span>WhatsApp Link</span>
                </button>
              </div>
            </div>

            <!-- Record Bank Wire Option -->
            <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
              <div class="text-xs text-slate-500 dark:text-slate-400">Direct RTGS / NEFT / Bank Transfer?</div>
              <button
                type="button"
                @click="showWireModal = true"
                class="px-3.5 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-300 border border-purple-500/30 text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
              >
                <span>Record Bank Wire UTR</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Inline Modal: Record Direct Bank Wire -->
    <div v-if="showWireModal" class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-slate-900/70 dark:bg-black/80 backdrop-blur-md">
      <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-2xl space-y-3.5">
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-bold text-slate-900 dark:text-white">Record Bank Wire Transfer</h4>
          <button @click="showWireModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div>
          <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
          <select
            v-model="wireMethod"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100"
          >
            <option value="rtgs">RTGS (High-Value Transfer)</option>
            <option value="neft">NEFT / National Transfer</option>
            <option value="imps">IMPS / Instant Bank Transfer</option>
            <option value="upi_direct">UPI Direct to Bank</option>
            <option value="wire">International Bank Wire (Swift)</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Amount Received ({{ activeDeal?.currency }})</label>
          <input
            v-model="wireAmount"
            type="number"
            :placeholder="String(activeDeal?.amount)"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono"
          />
        </div>

        <div>
          <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Bank Transaction UTR / Ref Number *</label>
          <input
            v-model="wireUtr"
            required
            placeholder="e.g. HDFC000123456789"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono"
          />
        </div>

        <div>
          <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</label>
          <input
            v-model="wireNotes"
            placeholder="e.g. 100% advance project fee verified"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100"
          />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            @click="showWireModal = false"
            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submitWirePayment"
            :disabled="loggingWire || !wireAmount || !wireUtr"
            class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition disabled:opacity-50"
          >
            {{ loggingWire ? 'Verifying...' : 'Confirm & Close Deal' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
