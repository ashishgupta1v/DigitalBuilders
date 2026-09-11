<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import {
  X, Building2, User, Phone, Mail, MessageSquare, Calendar, Sparkles,
  CreditCard, CheckCircle2, AlertCircle, ArrowRight, DollarSign, Clock,
  FileText, Send, Copy, Check, ExternalLink, ChevronRight, ChevronDown, ChevronUp, Edit3,
  Eye, MousePointerClick, Bot, Receipt, Globe, Play, Pause, RefreshCw, Search
} from 'lucide-vue-next'

const props = withDefaults(defineProps<{
  show: boolean
  leadId: number | null
  initialTab?: 'timeline' | 'sequence' | 'commercials' | 'notes'
}>(), {
  initialTab: 'timeline',
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'updated', message?: string): void
  (e: 'openOutreach', lead: any, touch: number): void
  (e: 'openProposal', dealId: number): void
}>()

const leadData = ref<any>(null)
const loading = ref(false)
const activeTab = ref<'timeline' | 'sequence' | 'commercials' | 'notes'>('timeline')

// Outbound Sequence State
const sequenceData = ref<any>(null)
const loadingSequence = ref(false)
const startingSequence = ref(false)
const pausingSequence = ref(false)
const editingStepId = ref<number | null>(null)
const stepEdits = ref<Record<number, { subject: string; body_text: string; delay_days: number }>>({})
const contactEmailInput = ref('')
const savingEmail = ref(false)

// Activity Logging
const newNote = ref('')
const newNoteType = ref('note')
const loggingActivity = ref(false)

// Outbound Tracked Email
const showEmailModal = ref(false)
const emailSubject = ref('')
const emailBody = ref('')
const emailTouch = ref(1)
const sendingEmail = ref(false)

// Inbound Reply Triage
const showTriageModal = ref(false)
const triageInput = ref('')
const triaging = ref(false)
const triageResult = ref<any>(null)
const triageCopied = ref(false)

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

// Lead Domain & Intelligence Enrichment
const enrichingLead = ref(false)
const enrichSuccess = ref(false)

const enrichLead = async () => {
  if (!props.leadId || enrichingLead.value) return
  enrichingLead.value = true
  enrichSuccess.value = false
  try {
    const res = await fetch(`/crm/leads/${props.leadId}/enrich`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    if (data.success) {
      enrichSuccess.value = true
      if (leadData.value?.lead) {
        leadData.value.lead.ai_summary = data.ai_summary
      }
      if (data.dossier?.domain && leadData.value?.organization) {
        leadData.value.organization.domain = data.dossier.domain
      }
      fetchLeadDetails()
      emit('updated', 'Lead intelligence dossier enriched!')
      setTimeout(() => {
        enrichSuccess.value = false
      }, 3000)
    }
  } catch (err) {
    console.error('Failed to enrich lead', err)
  } finally {
    enrichingLead.value = false
  }
}

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

// Duplicate Detection & Merge State
const duplicateCheckResult = ref<{ count: number; duplicates: any[] }>({ count: 0, duplicates: [] })
const checkingDuplicates = ref(false)
const mergingDuplicateId = ref<number | null>(null)
const showMergeConfirm = ref<any | null>(null)

const fetchDuplicates = async () => {
  if (!props.leadId) return
  checkingDuplicates.value = true
  try {
    const res = await fetch(`/crm/leads/${props.leadId}/duplicates`)
    const data = await res.json()
    duplicateCheckResult.value = data
  } catch (e) {
    console.error('Failed to check duplicates', e)
  } finally {
    checkingDuplicates.value = false
  }
}

const mergeDuplicate = async (dupId: number) => {
  if (!props.leadId) return
  mergingDuplicateId.value = dupId
  try {
    const res = await fetch(`/crm/leads/${props.leadId}/merge`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ duplicate_lead_id: dupId }),
    })
    const data = await res.json()
    if (data.success) {
      emit('updated', data.message)
      showMergeConfirm.value = null
      fetchLeadDetails()
      fetchDuplicates()
    } else {
      alert(data.message || 'Merge failed')
    }
  } catch (e) {
    console.error('Error merging lead records', e)
  } finally {
    mergingDuplicateId.value = null
  }
}

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

const loadSequence = async () => {
  if (!props.leadId) return
  loadingSequence.value = true
  try {
    const res = await fetch(`/crm/leads/${props.leadId}/sequence`)
    const data = await res.json()
    sequenceData.value = data.sequence
    if (data.sequence?.steps) {
      data.sequence.steps.forEach((s: any) => {
        stepEdits.value[s.id] = { subject: s.subject, body_text: s.body_text, delay_days: s.delay_days }
      })
    }
  } catch (err) {
    console.error('Failed to load sequence', err)
  } finally {
    loadingSequence.value = false
  }
}

const regenerateSequence = async () => {
  if (!props.leadId) return
  loadingSequence.value = true
  try {
    const res = await fetch(`/crm/leads/${props.leadId}/sequence/regenerate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    sequenceData.value = data.sequence
    if (data.sequence?.steps) {
      data.sequence.steps.forEach((s: any) => {
        stepEdits.value[s.id] = { subject: s.subject, body_text: s.body_text, delay_days: s.delay_days }
      })
    }
    emit('updated', 'AI 4-Step Cadence regenerated!')
  } catch (err) {
    console.error('Failed to regenerate sequence', err)
  } finally {
    loadingSequence.value = false
  }
}

const startSequence = async () => {
  if (!sequenceData.value?.id) return
  startingSequence.value = true
  try {
    const stepsPayload = Object.entries(stepEdits.value).map(([id, val]) => ({ id: Number(id), ...val }))
    const res = await fetch(`/crm/sequences/${sequenceData.value.id}/start`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ steps: stepsPayload }),
    })
    const data = await res.json()
    if (data.success) {
      sequenceData.value = data.sequence
      fetchLeadDetails()
      emit('updated', 'Campaign launched! Touch #1 dispatched.')
    } else {
      alert(data.error || 'Failed to start campaign.')
    }
  } catch (err) {
    console.error('Failed to start sequence', err)
  } finally {
    startingSequence.value = false
  }
}

const pauseSequence = async () => {
  if (!sequenceData.value?.id) return
  pausingSequence.value = true
  try {
    const res = await fetch(`/crm/sequences/${sequenceData.value.id}/pause`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    if (data.success) {
      sequenceData.value = data.sequence
      emit('updated', 'Sequence paused.')
    }
  } catch (err) {
    console.error('Failed to pause sequence', err)
  } finally {
    pausingSequence.value = false
  }
}

const resumeSequence = async () => {
  if (!sequenceData.value?.id) return
  pausingSequence.value = true
  try {
    const res = await fetch(`/crm/sequences/${sequenceData.value.id}/resume`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    if (data.success) {
      sequenceData.value = data.sequence
      emit('updated', 'Sequence resumed.')
    }
  } catch (err) {
    console.error('Failed to resume sequence', err)
  } finally {
    pausingSequence.value = false
  }
}

const markAsReplied = async () => {
  if (!props.leadId) return
  try {
    const res = await fetch(`/crm/leads/${props.leadId}/mark-replied`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ note: 'Prospect replied to email outreach' }),
    })
    const data = await res.json()
    if (data.success) {
      loadSequence()
      fetchLeadDetails()
      emit('updated', 'Prospect marked as replied. Outbound sequence halted.')
    }
  } catch (err) {
    console.error('Failed to mark replied', err)
  }
}

const saveStep = async (stepId: number) => {
  if (!sequenceData.value?.id || !stepEdits.value[stepId]) return
  try {
    const res = await fetch(`/crm/sequences/${sequenceData.value.id}/steps/${stepId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify(stepEdits.value[stepId]),
    })
    const data = await res.json()
    if (data.success) {
      editingStepId.value = null
      emit('updated', 'Step saved.')
    }
  } catch (err) {
    console.error('Failed to save step', err)
  }
}

const saveContactEmail = async () => {
  if (!props.leadId || !contactEmailInput.value.trim()) return
  savingEmail.value = true
  try {
    const res = await fetch(`/crm/leads/${props.leadId}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ email: contactEmailInput.value.trim() }),
    })
    const data = await res.json()
    if (data.success) {
      if (leadData.value?.lead) {
        leadData.value.lead.email = contactEmailInput.value.trim()
      }
      contactEmailInput.value = ''
      loadSequence()
      emit('updated', 'Email updated! Sequence unlocked.')
    }
  } catch (err) {
    console.error('Failed to save email', err)
  } finally {
    savingEmail.value = false
  }
}

watch(
  () => [props.show, props.leadId, props.initialTab],
  () => {
    if (props.show && props.leadId) {
      if (props.initialTab) {
        activeTab.value = props.initialTab
      }
      fetchLeadDetails()
      fetchDuplicates()
      loadSequence()
    }
  },
  { immediate: true }
)

const enrichment = computed(() => leadData.value?.lead?.enrichment_data || null)
const step1Draft = computed(() => sequenceData.value?.steps?.find((s: any) => s.step_number === 1) || null)
const isStep1AwaitingApproval = computed(() => sequenceData.value?.status === 'draft' && !!step1Draft.value)
const isDossierCollapsed = ref(false)

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

const openEmailComposer = (targetTouch?: number) => {
  if (!leadData.value?.lead?.email) return
  const name = leadData.value.lead.name || 'there'
  const company = leadData.value.lead.company || 'your team'
  const touch = targetTouch ? Number(targetTouch) : Math.min(5, (leadData.value.lead.touchpoint_count || 0) + 1)
  emailTouch.value = touch

  if (touch === 1) {
    emailSubject.value = `Architecture & Technical Execution for ${company}`
    emailBody.value = `Hi ${name},\n\nI'm Ashish Gupta, Principal Software Architect and Founder at DigitalBuilders (https://www.digitalbuilders.in).\n\nI noticed your active software engineering requirements. At DigitalBuilders, we specialize in high-throughput web applications, clean microservices, and custom ERP systems built with zero bloated plugins, 100% automated test coverage, and weekly live staging deliveries.\n\nWe provide complete IP ownership and a 30-day post-launch warranty on every project.\n\nWould you be open to a quick 15-minute technical sync this week? You can pick a convenient slot directly on my calendar here:\n👉 https://www.digitalbuilders.in/book\n\nLooking forward to speaking.\n\nBest regards,\nAshish Gupta`
  } else if (touch === 2) {
    emailSubject.value = `DigitalBuilders Scope Estimator & Pricing Book for ${company}`
    emailBody.value = `Hi ${name},\n\nFollowing up on my previous note. Wanted to share our transparent 2026 Scope Estimator in case you want to ballpark engineering costs and milestones for ${company}:\n👉 https://www.digitalbuilders.in/estimator\n\nIf you have a quick 10 minutes, let's connect to review your technical bottlenecks:\n👉 https://www.digitalbuilders.in/book\n\nBest,\nAshish Gupta`
  } else if (touch === 3) {
    emailSubject.value = `Architecture Case Study relevant to ${company}`
    emailBody.value = `Hi ${name},\n\nThought you might find this relevant—we recently architected an industrial ERP & ordering system for Garg Enterprises (10k+ SKUs) that eliminated dispatch errors completely: https://www.digitalbuilders.in/portfolio/garg-enterprises.\n\nWe've also built high-concurrency platforms like Habuilt handling 65k+ users: https://www.digitalbuilders.in/portfolio/habuilt.\n\nCould we jump on a 15-minute screen share to review how we would structure ${company}'s build?\n👉 https://www.digitalbuilders.in/book\n\nBest regards,\nAshish Gupta`
  } else {
    emailSubject.value = `Checking in on ${company}'s technical roadmap`
    emailBody.value = `Hi ${name},\n\nAshish here from DigitalBuilders. Dropping a quick note to see if solving software bottlenecks for ${company} is still a priority for this quarter, or if you'd like to revisit down the road?\n\nIf now is not a good time, no worries at all. If you want to review your build scope, you can reach me anytime at https://www.digitalbuilders.in/book.\n\nBest,\nAshish Gupta`
  }
  showEmailModal.value = true
}

const sendTrackedEmail = async () => {
  if (!leadData.value?.lead?.id) return
  sendingEmail.value = true
  try {
    const res = await fetch(`/crm/leads/${leadData.value.lead.id}/send-email`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        subject: emailSubject.value,
        body_text: emailBody.value,
        touchpoint_number: emailTouch.value,
      }),
    })
    const data = await res.json()
    if (data.success) {
      showEmailModal.value = false
      fetchLeadDetails()
      emit('updated', 'Tracked email dispatched! Stage updated to Contacted.')
    } else {
      alert(data.error || 'Failed to send email.')
    }
  } catch (err: any) {
    console.error('Error sending tracked email', err)
    alert('Failed to dispatch email. Please check server logs.')
  } finally {
    sendingEmail.value = false
  }
}

const runReplyTriage = async () => {
  if (!leadData.value?.lead?.id || !triageInput.value.trim()) return
  triaging.value = true
  triageResult.value = null
  try {
    const res = await fetch(`/crm/leads/${leadData.value.lead.id}/triage-reply`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        message: triageInput.value.trim(),
        apply_stage_action: true,
      }),
    })
    const data = await res.json()
    if (data.success) {
      triageResult.value = data
      fetchLeadDetails()
      emit('updated', `Reply triaged: ${data.intent.toUpperCase()}`)
    }
  } catch (err) {
    console.error('Error running reply triage', err)
  } finally {
    triaging.value = false
  }
}

const copyTriageResponse = () => {
  const resp = triageResult.value?.battlecard_response || triageResult.value?.suggested_response
  if (!resp) return
  navigator.clipboard.writeText(resp)
  triageCopied.value = true
  setTimeout(() => { triageCopied.value = false }, 2500)
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
            <!-- Send Tracked Email -->
            <button
              v-if="leadData?.lead?.email"
              type="button"
              @click="openEmailComposer(leadData?.lead?.touchpoint_count ? leadData.lead.touchpoint_count + 1 : 1)"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/20 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
              title="Compose 1-click tracked email with open/click telemetry"
            >
              <Mail class="w-3.5 h-3.5" />
              <span>Email</span>
            </button>

            <!-- AI Reply Triage -->
            <button
              type="button"
              @click="showTriageModal = true"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-violet-500/10 hover:bg-violet-500/20 text-violet-600 dark:text-violet-400 border border-violet-500/20 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
              title="Triage inbound prospect reply with AI battlecards"
            >
              <Bot class="w-3.5 h-3.5" />
              <span>Triage</span>
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

            <!-- Lead Intelligence Enrichment Button -->
            <button
              type="button"
              @click="enrichLead"
              :disabled="enrichingLead"
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border"
              :class="enrichSuccess ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border-indigo-500/20'"
              title="Enrich lead with company domain, architecture recommendations, and executive search queries"
            >
              <Globe class="w-3.5 h-3.5" :class="{ 'animate-spin': enrichingLead }" />
              <span>{{ enrichingLead ? 'Enriching...' : (enrichSuccess ? 'Enriched!' : 'Enrich') }}</span>
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

      <!-- Duplicate Contact Alert Banner & Quick Merge Action -->
      <div
        v-if="duplicateCheckResult.count > 0"
        class="mx-4 sm:mx-6 mt-3 p-3.5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-orange-500/10 border border-amber-500/30 text-xs"
      >
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="flex items-center gap-2">
            <AlertCircle class="w-4 h-4 text-amber-500 shrink-0" />
            <span class="font-bold text-amber-900 dark:text-amber-200">
              Potential Duplicate Records Detected ({{ duplicateCheckResult.count }})
            </span>
          </div>
          <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-300 font-mono font-bold">
            Matched Phone / Domain / Company
          </span>
        </div>

        <div class="space-y-2">
          <div
            v-for="dup in duplicateCheckResult.duplicates"
            :key="dup.id"
            class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-between gap-2 shadow-xs"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-slate-900 dark:text-white truncate">{{ dup.name }}</span>
                <span class="text-[11px] text-slate-500 truncate">{{ dup.company }}</span>
                <span
                  v-for="r in dup.reasons"
                  :key="r"
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300"
                >
                  {{ r }}
                </span>
              </div>
              <div class="text-[10px] text-slate-400 mt-0.5 truncate">
                {{ dup.email || 'No email' }} • {{ dup.phone || 'No phone' }} • Stage: {{ dup.stage }} ({{ dup.deals_count }} deals) • Created {{ dup.created_at }}
              </div>
            </div>

            <button
              type="button"
              @click="showMergeConfirm = dup"
              :disabled="mergingDuplicateId === dup.id"
              class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-[11px] font-bold transition flex items-center gap-1 cursor-pointer shrink-0 shadow-xs disabled:opacity-50"
            >
              <span>{{ mergingDuplicateId === dup.id ? 'Merging...' : 'Merge Into Lead' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Smart Outbound Sequence Approval Banner (1-Click Founder Dispatch) -->
      <div
        v-if="isStep1AwaitingApproval"
        class="mx-4 sm:mx-6 mt-3 p-3.5 rounded-2xl bg-gradient-to-r from-purple-500/15 via-indigo-500/10 to-sky-500/15 border border-purple-400/50 dark:border-purple-500/40 shadow-sm space-y-2.5"
      >
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
            <span class="text-xs font-extrabold uppercase tracking-wider text-purple-900 dark:text-purple-200 flex items-center gap-1.5">
              ⚡ Touch #1 Outreach Ready for Founder Review
            </span>
          </div>
          <button
            type="button"
            @click="activeTab = 'sequence'"
            class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1 cursor-pointer"
          >
            <span>View Full 4-Step Cadence</span>
            <ArrowRight class="w-3 h-3" />
          </button>
        </div>

        <div class="p-3 rounded-xl bg-white dark:bg-slate-950 border border-purple-200/80 dark:border-purple-900/60 text-xs">
          <div class="font-bold text-slate-900 dark:text-slate-100 truncate">
            Subject: <span class="font-normal text-slate-600 dark:text-slate-300">{{ step1Draft?.subject }}</span>
          </div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-3 leading-relaxed font-sans whitespace-pre-line">
            {{ step1Draft?.body_text }}
          </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 pt-0.5">
          <span class="text-[10px] text-slate-500 dark:text-slate-400">
            Dispatches Touch 1 now. Touches #2 (+3d), #3 (+7d), and #4 (+11d) schedule automatically.
          </span>
          <button
            type="button"
            @click="startSequence"
            :disabled="startingSequence || !leadData?.lead?.email"
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 text-white text-xs font-extrabold shadow-md shadow-purple-500/20 transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 shrink-0"
          >
            <Send class="w-3.5 h-3.5" :class="{ 'animate-pulse': startingSequence }" />
            <span>{{ startingSequence ? 'Dispatching...' : '🚀 1-Click Approve & Dispatch Touch #1' }}</span>
          </button>
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
          @click="activeTab = 'sequence'"
          class="pb-3 transition border-b-2 cursor-pointer flex items-center gap-2 shrink-0"
          :class="activeTab === 'sequence' ? 'border-purple-500 dark:border-purple-400 text-purple-600 dark:text-purple-300 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
        >
          <Send class="w-3.5 h-3.5" />
          <span>Outbound Cadence ({{ sequenceData?.status ? sequenceData.status.toUpperCase() : '4 Steps' }})</span>
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
        <!-- Executive Intelligence Dossier (Domain, Tech Stack, Pain Points & Research Links) -->
        <div
          v-if="enrichment"
          class="p-4 rounded-2xl bg-gradient-to-br from-slate-50 via-indigo-50/20 to-purple-50/20 dark:from-slate-950 dark:via-indigo-950/25 dark:to-purple-950/25 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <Building2 class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
              <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">
                Executive Intelligence Dossier
              </h4>
              <span
                v-if="enrichment.is_domain_verified"
                class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
              >
                Live Domain Verified
              </span>
            </div>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="enrichLead"
                :disabled="enrichingLead"
                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer"
                title="Re-scan domain and refresh OpenAI intelligence dossier"
              >
                <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': enrichingLead }" />
                <span>{{ enrichingLead ? 'Scanning...' : 'Re-scan Domain' }}</span>
              </button>

              <button
                type="button"
                @click="isDossierCollapsed = !isDossierCollapsed"
                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition cursor-pointer"
                :title="isDossierCollapsed ? 'Expand dossier' : 'Collapse dossier'"
              >
                <ChevronDown v-if="isDossierCollapsed" class="w-4 h-4" />
                <ChevronUp v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Expanded Dossier Details -->
          <div v-if="!isDossierCollapsed" class="space-y-3">
            <!-- Business Overview & Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400">Industry & Core Offering</div>
                <div class="font-bold text-slate-900 dark:text-slate-100">{{ enrichment.industry || 'Software / Technology' }}</div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed font-sans">
                  {{ enrichment.company_summary }}
                </div>
              </div>

              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                <div>
                  <div class="text-[10px] uppercase font-bold text-slate-400">Team Size & Scale</div>
                  <div class="font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ enrichment.estimated_team_size || '1-20 Founders' }}</div>
                </div>
                <div class="pt-2 mt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] uppercase font-bold text-slate-400">Target Domain</span>
                  <span class="font-mono text-[11px] font-bold text-indigo-600 dark:text-indigo-400">{{ enrichment.domain || 'Direct Contact' }}</span>
                </div>
              </div>
            </div>

            <!-- Detected Tech Stack Badges -->
            <div v-if="enrichment.detected_tech_stack?.length" class="space-y-1.5">
              <div class="text-[10px] uppercase font-bold text-slate-400">Detected & Recommended Tech Stack</div>
              <div class="flex items-center gap-1.5 flex-wrap">
                <span
                  v-for="tag in enrichment.detected_tech_stack"
                  :key="tag"
                  class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 shadow-xs"
                >
                  ⚡ {{ tag }}
                </span>
              </div>
            </div>

            <!-- Client Pain Points & Recommended Hook -->
            <div v-if="enrichment.client_pain_points?.length || enrichment.recommended_hook" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-xs space-y-1.5">
              <div v-if="enrichment.client_pain_points?.length" class="flex items-start gap-2">
                <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-amber-400 shrink-0">Pain Points:</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                  {{ enrichment.client_pain_points.join(' • ') }}
                </span>
              </div>
              <div v-if="enrichment.recommended_hook" class="flex items-start gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                <span class="text-[10px] font-bold uppercase text-purple-600 dark:text-purple-400 shrink-0">Outreach Hook:</span>
                <span class="text-[11px] text-purple-700 dark:text-purple-300 font-medium font-sans">
                  "{{ enrichment.recommended_hook }}"
                </span>
              </div>
            </div>

            <!-- 1-Tap Research Links -->
            <div class="flex items-center gap-1.5 flex-wrap pt-1">
              <a
                v-if="enrichment.domain"
                :href="'https://' + enrichment.domain"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1 transition shadow-xs"
              >
                <Globe class="w-3 h-3 text-cyan-500" />
                <span>Website</span>
                <ExternalLink class="w-2.5 h-2.5 text-slate-400" />
              </a>
              <a
                v-if="enrichment.linkedin_company_url"
                :href="enrichment.linkedin_company_url"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 text-[11px] font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1 transition"
              >
                <Building2 class="w-3 h-3" />
                <span>LinkedIn Co.</span>
                <ExternalLink class="w-2.5 h-2.5 text-blue-400" />
              </a>
              <a
                v-if="enrichment.linkedin_exec_url"
                :href="enrichment.linkedin_exec_url"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/20 text-[11px] font-bold text-sky-600 dark:text-sky-400 flex items-center gap-1 transition"
              >
                <User class="w-3 h-3" />
                <span>Founder Profile</span>
                <ExternalLink class="w-2.5 h-2.5 text-sky-400" />
              </a>
              <a
                v-if="enrichment.crunchbase_url"
                :href="enrichment.crunchbase_url"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 transition"
              >
                <DollarSign class="w-3 h-3" />
                <span>Crunchbase</span>
                <ExternalLink class="w-2.5 h-2.5 text-emerald-400" />
              </a>
              <a
                v-if="enrichment.builtwith_url"
                :href="enrichment.builtwith_url"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 border border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1 transition"
              >
                <Bot class="w-3 h-3 text-purple-500" />
                <span>BuiltWith</span>
                <ExternalLink class="w-2.5 h-2.5 text-slate-400" />
              </a>
            </div>
          </div>

          <!-- Collapsed Strip View -->
          <div v-else class="flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center gap-2 truncate">
              <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ enrichment.company_name }}</span>
              <span>•</span>
              <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ enrichment.domain || 'Direct Contact' }}</span>
              <span>•</span>
              <span class="truncate">{{ enrichment.industry }}</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <a
                v-if="enrichment.linkedin_company_url"
                :href="enrichment.linkedin_company_url"
                target="_blank"
                class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-blue-600"
                title="LinkedIn"
              >
                <Building2 class="w-3.5 h-3.5" />
              </a>
              <a
                v-if="enrichment.domain"
                :href="'https://' + enrichment.domain"
                target="_blank"
                class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-cyan-600"
                title="Website"
              >
                <Globe class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </div>
        <!-- Tab 0: Outbound Cadence Engine -->
        <div v-if="activeTab === 'sequence'" class="space-y-4">
          <!-- Missing Email Contact Warning & 1-Click Search Helper -->
          <div v-if="!leadData?.lead?.email" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-3">
            <div class="flex items-center gap-2">
              <AlertCircle class="w-4 h-4 text-amber-500 shrink-0" />
              <span class="font-bold text-amber-900 dark:text-amber-200 text-xs">
                No Verified Contact Email for this Prospect
              </span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-400">
              To launch automated B2B outbound cadences, add an email address. Use quick-search to find their founder/CEO profile on LinkedIn or Google:
            </p>
            <div class="flex flex-wrap items-center gap-2 pt-1">
              <a
                :href="`https://www.linkedin.com/search/results/all/?keywords=${encodeURIComponent((leadData?.lead?.name || '') + ' ' + (leadData?.lead?.company || ''))}`"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-blue-600/10 hover:bg-blue-600/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-[11px] font-bold flex items-center gap-1.5 transition"
              >
                <ExternalLink class="w-3 h-3" />
                <span>Search on LinkedIn</span>
              </a>
              <a
                :href="`https://www.google.com/search?q=${encodeURIComponent((leadData?.lead?.name || '') + ' ' + (leadData?.lead?.company || '') + ' email')}`"
                target="_blank"
                class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 text-[11px] font-bold flex items-center gap-1.5 transition"
              >
                <Search class="w-3 h-3" />
                <span>Search on Google</span>
              </a>
            </div>
            <div class="flex items-center gap-2 pt-1">
              <input
                v-model="contactEmailInput"
                type="email"
                placeholder="Paste founder/decision-maker email..."
                class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-950 border border-amber-300 dark:border-amber-800 rounded-xl text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500"
              />
              <button
                type="button"
                @click="saveContactEmail"
                :disabled="savingEmail || !contactEmailInput.trim()"
                class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition disabled:opacity-50 cursor-pointer shadow-xs"
              >
                {{ savingEmail ? 'Saving...' : 'Save & Unlock' }}
              </button>
            </div>
          </div>

          <!-- Cadence Header Status Card -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <Send class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">
                  4-Step Outbound Cadence Engine
                </h4>
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase border"
                  :class="{
                    'bg-purple-500/10 text-purple-600 border-purple-500/20': sequenceData?.status === 'draft',
                    'bg-emerald-500/10 text-emerald-600 border-emerald-500/20': sequenceData?.status === 'active',
                    'bg-amber-500/10 text-amber-600 border-amber-500/20': sequenceData?.status === 'paused',
                    'bg-sky-500/10 text-sky-600 border-sky-500/20': sequenceData?.status === 'replied',
                    'bg-rose-500/10 text-rose-600 border-rose-500/20': sequenceData?.status === 'opted_out',
                    'bg-slate-500/10 text-slate-500 border-slate-500/20': sequenceData?.status === 'completed',
                  }"
                >
                  {{ sequenceData?.status || 'Draft' }}
                </span>
              </div>

              <div class="flex items-center gap-2 flex-wrap">
                <button
                  type="button"
                  @click="regenerateSequence"
                  :disabled="loadingSequence"
                  class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[11px] font-semibold flex items-center gap-1 transition cursor-pointer"
                  title="Re-generate personalized sequence copy with AI"
                >
                  <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': loadingSequence }" />
                  <span>Regenerate AI Copy</span>
                </button>

                <button
                  v-if="sequenceData?.status === 'active'"
                  type="button"
                  @click="pauseSequence"
                  :disabled="pausingSequence"
                  class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/20 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer"
                >
                  <Pause class="w-3 h-3" />
                  <span>Pause</span>
                </button>

                <button
                  v-if="sequenceData?.status === 'paused'"
                  type="button"
                  @click="resumeSequence"
                  :disabled="pausingSequence"
                  class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer"
                >
                  <Play class="w-3 h-3" />
                  <span>Resume</span>
                </button>

                <button
                  v-if="sequenceData?.status === 'active' || sequenceData?.status === 'paused'"
                  type="button"
                  @click="markAsReplied"
                  class="px-2.5 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-700 dark:text-sky-400 border border-sky-500/20 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer"
                  title="Prospect replied on email or LinkedIn — stop remaining touches"
                >
                  <CheckCircle2 class="w-3 h-3" />
                  <span>Mark Replied</span>
                </button>
              </div>
            </div>

            <!-- 1-Click Launch Button -->
            <div v-if="sequenceData?.status === 'draft'" class="pt-2">
              <button
                type="button"
                @click="startSequence"
                :disabled="startingSequence || !leadData?.lead?.email"
                class="w-full py-2.5 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 text-white text-xs font-extrabold shadow-md shadow-purple-500/20 flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-50"
              >
                <Send class="w-4 h-4" :class="{ 'animate-pulse': startingSequence }" />
                <span>{{ startingSequence ? 'Launching Campaign...' : '🚀 1-Click Approve & Launch Cadence (Send Touch #1 Now)' }}</span>
              </button>
              <div class="text-[10px] text-slate-400 text-center mt-1.5">
                Dispatches Touch #1 immediately via SMTP. Touchpoints #2 (+3d), #3 (+7d), and #4 (+11d) will automatically execute on schedule.
              </div>
            </div>
          </div>

          <!-- The 4 Steps Timeline -->
          <div v-if="loadingSequence" class="py-10 text-center text-xs text-slate-400">
            <RefreshCw class="w-6 h-6 animate-spin mx-auto text-purple-500 mb-2" />
            <span>Loading AI Sequence Steps...</span>
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="step in sequenceData?.steps || []"
              :key="step.id"
              class="p-4 rounded-2xl bg-white dark:bg-slate-900 border transition shadow-xs"
              :class="{
                'border-emerald-500/50 dark:border-emerald-500/40 bg-emerald-500/[0.02]': step.status === 'sent',
                'border-purple-500/40 dark:border-purple-500/30': step.status === 'scheduled',
                'border-slate-200 dark:border-slate-800': step.status === 'pending' || step.status === 'skipped',
              }"
            >
              <div class="flex items-center justify-between gap-2 mb-2.5">
                <div class="flex items-center gap-2">
                  <span
                    class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-extrabold font-mono"
                    :class="{
                      'bg-emerald-500 text-white': step.status === 'sent',
                      'bg-purple-600 text-white': step.status === 'scheduled',
                      'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400': step.status === 'pending',
                      'bg-slate-100 text-slate-400 line-through': step.status === 'skipped',
                    }"
                  >
                    {{ step.step_number }}
                  </span>
                  <div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                      <span>{{ step.title }}</span>
                      <span class="text-[10px] text-slate-400 font-normal">
                        ({{ step.delay_days === 0 ? 'Day 0 • Immediate' : `+${step.delay_days} days` }})
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <span
                    class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase"
                    :class="{
                      'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400': step.status === 'sent',
                      'bg-purple-500/15 text-purple-700 dark:text-purple-400': step.status === 'scheduled',
                      'bg-slate-100 dark:bg-slate-800 text-slate-500': step.status === 'pending',
                      'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through': step.status === 'skipped',
                    }"
                  >
                    {{ step.status === 'scheduled' && step.scheduled_at ? `Scheduled (${new Date(step.scheduled_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })})` : step.status }}
                  </span>

                  <button
                    v-if="step.status !== 'sent'"
                    type="button"
                    @click="editingStepId = (editingStepId === step.id ? null : step.id)"
                    class="p-1 rounded-lg text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 transition cursor-pointer"
                    title="Edit step content"
                  >
                    <Edit3 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <!-- Content Viewer / Inline Editor -->
              <div v-if="editingStepId === step.id" class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <div>
                  <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Subject Line</label>
                  <input
                    v-if="stepEdits[step.id]"
                    v-model="stepEdits[step.id].subject"
                    type="text"
                    class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-purple-500/40 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Email Body</label>
                  <textarea
                    v-if="stepEdits[step.id]"
                    v-model="stepEdits[step.id].body_text"
                    rows="6"
                    class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-purple-500/40 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none font-sans leading-relaxed"
                  ></textarea>
                </div>
                <div class="flex justify-end gap-2">
                  <button
                    type="button"
                    @click="editingStepId = null"
                    class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold cursor-pointer"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="saveStep(step.id)"
                    class="px-3 py-1 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold cursor-pointer"
                  >
                    Save Changes
                  </button>
                </div>
              </div>

              <div v-else class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                <div class="font-bold text-slate-800 dark:text-slate-200">
                  Subject: <span class="font-normal">{{ stepEdits[step.id]?.subject || step.subject }}</span>
                </div>
                <p class="whitespace-pre-line text-[11px] leading-relaxed line-clamp-3 text-slate-500 dark:text-slate-400">
                  {{ stepEdits[step.id]?.body_text || step.body_text }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 1: Timeline & Notes -->
        <div v-if="activeTab === 'timeline'" class="space-y-4">
          <!-- Tracked Email Telemetry Stream -->
          <div v-if="leadData?.outreach_emails?.length > 0" class="p-4 rounded-2xl bg-sky-50/50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/60 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-sky-900 dark:text-sky-200 flex items-center gap-1.5">
                <Mail class="w-3.5 h-3.5 text-sky-500" />
                Tracked Email Telemetry ({{ leadData.outreach_emails.length }})
              </span>
              <span class="text-[10px] text-sky-600 dark:text-sky-400 font-mono">1x1 Pixel & Link Redirects</span>
            </div>

            <div class="space-y-2">
              <div
                v-for="em in leadData.outreach_emails"
                :key="em.id"
                class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs flex items-center justify-between gap-3"
              >
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300">
                      Touch #{{ em.touchpoint_number }}
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ em.subject }}</span>
                  </div>
                  <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                    <span>Sent {{ em.sent_at }}</span>
                    <span v-if="em.opened_at">• Opened {{ em.opened_at }}</span>
                    <span v-if="em.clicked_at">• Clicked {{ em.clicked_at }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 text-[11px]">
                  <!-- Open Telemetry -->
                  <div
                    class="flex items-center gap-1 px-2 py-0.5 rounded-lg font-mono font-semibold"
                    :class="em.open_count > 0 ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
                    :title="em.opened_at ? `Opened ${em.opened_at}` : 'Unopened'"
                  >
                    <Eye class="w-3 h-3" />
                    <span>{{ em.open_count > 0 ? `${em.open_count} open${em.open_count > 1 ? 's' : ''}` : 'Unopened' }}</span>
                  </div>

                  <!-- Click Telemetry -->
                  <div
                    class="flex items-center gap-1 px-2 py-0.5 rounded-lg font-mono font-semibold"
                    :class="em.click_count > 0 ? 'bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
                    :title="em.clicked_at ? `Clicked ${em.clicked_at}` : 'No clicks'"
                  >
                    <MousePointerClick class="w-3 h-3" />
                    <span>{{ em.click_count > 0 ? `${em.click_count} click${em.click_count > 1 ? 's' : ''}` : '0 clicks' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

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

            <div class="flex items-center gap-2 w-full sm:w-auto">
              <a
                :href="`/crm/deals/${activeDeal.id}/invoice`"
                target="_blank"
                class="flex-1 sm:flex-initial px-3 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center justify-center gap-1.5 shrink-0"
                title="View printable proforma & tax invoice"
              >
                <Receipt class="w-3.5 h-3.5 text-purple-500" />
                <span>GST Invoice</span>
              </a>

              <button
                type="button"
                @click="emit('openProposal', activeDeal.id)"
                class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-purple-500/20 cursor-pointer shrink-0"
              >
                <FileText class="w-3.5 h-3.5" />
                <span>{{ activeDeal.stage === 'proposal_sent' ? 'View Proposal' : 'Proposal' }}</span>
              </button>
            </div>
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

    <!-- Inline Modal: Tracked Email Composer -->
    <div
      v-if="showEmailModal"
      class="fixed inset-0 z-60 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <div class="p-2 rounded-xl bg-sky-500/10 text-sky-500 border border-sky-500/20">
              <Mail class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tracked Outreach Email</h3>
              <p class="text-[11px] text-slate-500">Includes 1x1 tracking pixel + booking redirect telemetry</p>
            </div>
          </div>
          <button
            type="button"
            @click="showEmailModal = false"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">To</label>
            <input
              type="text"
              :value="`${leadData?.lead?.name || ''} <${leadData?.lead?.email || ''}>`"
              disabled
              class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-mono"
            />
          </div>

          <div class="flex items-center gap-3">
            <div class="flex-1">
              <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Touchpoint Sequence</label>
              <select
                v-model="emailTouch"
                @change="openEmailComposer(emailTouch)"
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100"
              >
                <option :value="1">Touch #1: Initial Architecture Intro</option>
                <option :value="2">Touch #2: 48h Sprint Follow-Up</option>
                <option :value="3">Touch #3: Technical Case Study Brief</option>
                <option :value="4">Touch #4: Quarterly Check-in</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Subject</label>
            <input
              v-model="emailSubject"
              type="text"
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-semibold"
            />
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Email Body (Plain text / links auto-tracked)</label>
            <textarea
              v-model="emailBody"
              rows="8"
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-sans leading-relaxed focus:ring-1 focus:ring-sky-500 focus:outline-none"
            ></textarea>
          </div>

          <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <Eye class="w-3.5 h-3.5 text-sky-500 shrink-0" />
            <span>Opens and link clicks will send real-time alerts to your configured executive Telegram bot.</span>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            @click="showEmailModal = false"
            class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="sendTrackedEmail"
            :disabled="sendingEmail || !emailSubject || !emailBody"
            class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <Send class="w-3.5 h-3.5" />
            <span>{{ sendingEmail ? 'Dispatching...' : 'Dispatch Tracked Email' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Inline Modal: AI Reply Triage & Battlecards -->
    <div
      v-if="showTriageModal"
      class="fixed inset-0 z-60 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <div class="p-2 rounded-xl bg-violet-500/10 text-violet-500 border border-violet-500/20">
              <Bot class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">AI Reply Triage & Battlecards</h3>
              <p class="text-[11px] text-slate-500">Paste lead response to classify intent & generate optimal rebuttal</p>
            </div>
          </div>
          <button
            type="button"
            @click="showTriageModal = false"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">
              Prospect's Inbound Message (Email, WhatsApp, or LinkedIn)
            </label>
            <textarea
              v-model="triageInput"
              rows="4"
              placeholder="e.g. Thanks for reaching out, but your pricing is too high for our current seed stage..."
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-sans leading-relaxed focus:ring-1 focus:ring-violet-500 focus:outline-none"
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button
              type="button"
              @click="runReplyTriage"
              :disabled="triaging || !triageInput.trim()"
              class="px-4 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Sparkles class="w-3.5 h-3.5" />
              <span>{{ triaging ? 'Analyzing Intent...' : 'Analyze Reply' }}</span>
            </button>
          </div>

          <!-- Triage Classification Results -->
          <div v-if="triageResult" class="p-3.5 rounded-2xl bg-violet-50/50 dark:bg-violet-950/20 border border-violet-200 dark:border-violet-800/60 space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-violet-900 dark:text-violet-200">Detected Intent:</span>
                <span class="px-2 py-0.5 rounded-md text-xs font-bold uppercase bg-violet-200 dark:bg-violet-900/60 text-violet-800 dark:text-violet-300">
                  {{ triageResult.intent?.replace('_', ' ') }}
                </span>
              </div>
              <span v-if="triageResult.confidence" class="text-[10px] text-slate-500 font-mono">
                Confidence: {{ Math.round(triageResult.confidence * 100) }}%
              </span>
            </div>

            <div>
              <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Recommended Battlecard Response:</div>
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 whitespace-pre-wrap font-sans leading-relaxed">
                {{ triageResult.battlecard_response }}
              </div>
            </div>

            <div class="flex justify-end gap-2">
              <button
                type="button"
                @click="copyTriageResponse"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border"
                :class="triageCopied ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
              >
                <Check v-if="triageCopied" class="w-3.5 h-3.5" />
                <Copy v-else class="w-3.5 h-3.5" />
                <span>{{ triageCopied ? 'Copied to Clipboard' : 'Copy Response' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Merge Confirmation Dialog -->
  <Teleport to="body">
    <div
      v-if="showMergeConfirm"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="showMergeConfirm = null"
    >
      <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-amber-400/40 p-6 space-y-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
            <AlertCircle class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Confirm Lead Merge</h3>
            <p class="text-xs text-slate-500 mt-0.5">This action is permanent and cannot be undone.</p>
          </div>
        </div>

        <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-xs space-y-1">
          <div class="font-bold text-amber-900 dark:text-amber-200">Merging duplicate into this lead:</div>
          <div class="text-slate-700 dark:text-slate-300">
            <strong>{{ showMergeConfirm.name }}</strong> ({{ showMergeConfirm.company }})
          </div>
          <div class="text-slate-500">{{ showMergeConfirm.email }} • {{ showMergeConfirm.phone }}</div>
          <div class="text-[11px] text-amber-700 dark:text-amber-300 mt-2">
            All deals ({{ showMergeConfirm.deals_count }}), activities, notes, and outreach emails will be reassigned to this lead. The duplicate will be archived.
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-1">
          <button
            type="button"
            @click="showMergeConfirm = null"
            class="px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="mergeDuplicate(showMergeConfirm.id)"
            :disabled="mergingDuplicateId !== null"
            class="px-4 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60"
          >
            <span>{{ mergingDuplicateId !== null ? 'Merging...' : 'Confirm & Merge' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
