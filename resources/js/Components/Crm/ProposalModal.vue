<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  X, FileText, Edit3, Eye, Copy, Check, Printer, Share2, Send,
  Sparkles, ExternalLink, ShieldCheck, CheckCircle2, Clock, DollarSign
} from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  dealId: number | null
  appMeta?: {
    founder_name?: string
    founder_title?: string
    founder_email?: string
    founder_phone?: string
    company_name?: string
    app_name?: string
    website_url?: string
    booking_url?: string
    estimator_url?: string
    default_currency?: string
    active_sources_count?: number
  }
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'proposal-sent', data: any): void
}>()

const activeTab = ref<'preview' | 'edit'>('preview')
const loading = ref(false)
const saving = ref(false)
const sending = ref(false)
const copied = ref(false)
const copiedLink = ref(false)

const proposalData = ref<{
  deal_id: number
  title: string
  amount: number
  formatted_amount: string
  currency: string
  stage: string
  proposal_content: string
  proposal_html: string
  proposal_token: string
  public_url: string
  proposal_sent_at: string | null
  proposal_viewed_at: string | null
  proposal_accepted_at: string | null
  client_name: string
  client_phone: string
  company_name: string
} | null>(null)

const editableContent = ref('')

const fetchProposal = async () => {
  if (!props.dealId) return
  loading.value = true
  try {
    const res = await fetch(`/crm/deals/${props.dealId}/proposal`)
    const data = await res.json()
    proposalData.value = data
    editableContent.value = data.proposal_content
  } catch (err) {
    console.error('Failed to load proposal', err)
  } finally {
    loading.value = false
  }
}

watch(
  () => [props.show, props.dealId],
  () => {
    if (props.show && props.dealId) {
      activeTab.value = 'preview'
      fetchProposal()
    }
  },
  { immediate: true }
)

const saveProposal = async () => {
  if (!props.dealId) return
  saving.value = true
  try {
    const res = await fetch(`/crm/deals/${props.dealId}/proposal`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ proposal_content: editableContent.value }),
    })
    const data = await res.json()
    if (data.success && proposalData.value) {
      proposalData.value.proposal_content = editableContent.value
      proposalData.value.proposal_html = data.proposal_html
      activeTab.value = 'preview'
    }
  } catch (err) {
    console.error('Failed to save proposal', err)
  } finally {
    saving.value = false
  }
}

const markAsSent = async () => {
  if (!props.dealId) return
  sending.value = true
  try {
    const res = await fetch(`/crm/deals/${props.dealId}/proposal/send`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
    })
    const data = await res.json()
    if (data.success && proposalData.value) {
      proposalData.value.proposal_sent_at = 'Just now'
      proposalData.value.stage = 'proposal_sent'
      emit('proposal-sent', data)
    }
  } catch (err) {
    console.error('Failed to mark proposal sent', err)
  } finally {
    sending.value = false
  }
}

const copyMarkdown = async () => {
  if (!editableContent.value) return
  try {
    await navigator.clipboard.writeText(editableContent.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (e) {
    console.error('Failed to copy', e)
  }
}

const copyClientLink = async () => {
  if (!proposalData.value?.public_url) return
  try {
    await navigator.clipboard.writeText(proposalData.value.public_url)
    copiedLink.value = true
    setTimeout(() => {
      copiedLink.value = false
    }, 2000)
  } catch (e) {
    console.error('Failed to copy link', e)
  }
}

const cleanPhone = computed(() => {
  if (!proposalData.value?.client_phone) return ''
  const raw = String(proposalData.value.client_phone).trim()
  if (raw.startsWith('+')) {
    return raw.replace(/[^0-9]/g, '')
  }
  const digits = raw.replace(/[^0-9]/g, '')
  if (digits.length === 10) return `91${digits}`
  return digits
})

const whatsAppShareUrl = computed(() => {
  if (!proposalData.value) return '#'
  const founder = props.appMeta?.founder_name || 'Founder'
  const founderTitle = props.appMeta?.founder_title || 'Lead Architect'
  const lines = [
    `Hi ${proposalData.value.client_name},`,
    '',
    `Our ${founderTitle} ${founder} has finalized the engineering architecture and commercial scope for *${proposalData.value.title}*.`,
    '',
    `Target Investment: *${proposalData.value.formatted_amount}*`,
    `You can review and accept the official scope document online here:`,
    proposalData.value.public_url,
    '',
    `Let me know once reviewed and we will schedule the Phase 1 kickoff sprint.`,
  ]
  return `https://wa.me/${cleanPhone.value}?text=${encodeURIComponent(lines.join('\n'))}`
})

const printProposal = () => {
  window.print()
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 dark:bg-black/80 backdrop-blur-md print:p-0 print:bg-white print:static">
    <div class="crm-cockpit w-full max-w-4xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[94vh] print:max-h-none print:border-none print:shadow-none print:rounded-none print:bg-white">
      <!-- Header (Hidden on print) -->
      <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50/80 dark:bg-slate-950/80 print:hidden">
        <div class="flex items-center justify-between sm:justify-start gap-3 min-w-0">
          <div class="flex items-center gap-3 min-w-0">
            <div class="p-2 sm:p-2.5 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 shrink-0">
              <FileText class="w-5 h-5" />
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate">Client Engineering Proposal</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-purple-700 dark:text-purple-300 font-mono font-bold border border-slate-200 dark:border-slate-700 shrink-0">
                  {{ proposalData?.formatted_amount }}
                </span>
              </div>
              <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate">
                {{ proposalData?.company_name }} • {{ proposalData?.client_name }}
              </p>
            </div>
          </div>

          <button @click="emit('close')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer sm:hidden shrink-0">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2">
          <!-- View/Edit Tabs -->
          <div class="flex items-center bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-0.5 sm:p-1">
            <button
              @click="activeTab = 'preview'"
              class="px-2.5 sm:px-3 py-1 text-xs font-semibold rounded-lg transition flex items-center gap-1.5 cursor-pointer"
              :class="activeTab === 'preview' ? 'bg-purple-600 text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Eye class="w-3.5 h-3.5" />
              <span>Preview</span>
            </button>
            <button
              @click="activeTab = 'edit'"
              class="px-2.5 sm:px-3 py-1 text-xs font-semibold rounded-lg transition flex items-center gap-1.5 cursor-pointer"
              :class="activeTab === 'edit' ? 'bg-purple-600 text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Edit3 class="w-3.5 h-3.5" />
              <span>Edit Markdown</span>
            </button>
          </div>

          <button @click="emit('close')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer hidden sm:inline-flex">
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Status Subheader Bar (Hidden on print) -->
      <div v-if="proposalData" class="px-6 py-2.5 bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800/80 flex flex-wrap items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2 print:hidden">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-1.5">
            <Clock class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
            <span>Sent: <strong class="text-slate-800 dark:text-slate-200">{{ proposalData.proposal_sent_at || 'Draft (Not sent)' }}</strong></span>
          </div>
          <div class="flex items-center gap-1.5">
            <Eye class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
            <span>Viewed: <strong :class="proposalData.proposal_viewed_at ? 'text-cyan-600 dark:text-cyan-400 font-semibold' : 'text-slate-500 dark:text-slate-400'">{{ proposalData.proposal_viewed_at || 'Pending' }}</strong></span>
          </div>
          <div class="flex items-center gap-1.5">
            <CheckCircle2 class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
            <span>Accepted: <strong :class="proposalData.proposal_accepted_at ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-500 dark:text-slate-400'">{{ proposalData.proposal_accepted_at || 'Pending' }}</strong></span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="proposalData.public_url"
            target="_blank"
            class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-500 dark:hover:text-purple-300 inline-flex items-center gap-1 hover:underline"
          >
            <span>Client Link</span>
            <ExternalLink class="w-3 h-3" />
          </a>
        </div>
      </div>

      <!-- Main Body -->
      <div class="p-6 overflow-y-auto flex-1 custom-scrollbar print:p-0 print:overflow-visible">
        <div v-if="loading" class="py-20 text-center">
          <div class="inline-block w-8 h-8 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Synthesizing executive proposal scope...</p>
        </div>

        <template v-else-if="proposalData">
          <!-- Live Preview Mode -->
          <div v-if="activeTab === 'preview'" class="space-y-6">
            <div
              class="proposal-prose prose max-w-none text-slate-800 dark:text-slate-300 dark:prose-invert text-sm leading-relaxed print:text-black print:prose-neutral"
              v-html="proposalData.proposal_html"
            ></div>
          </div>

          <!-- Edit Markdown Mode -->
          <div v-else-if="activeTab === 'edit'" class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Markdown Source Editor
              </span>
              <button
                type="button"
                @click="saveProposal"
                :disabled="saving"
                class="px-4 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white font-bold text-xs transition flex items-center gap-1.5 cursor-pointer"
              >
                <Sparkles class="w-3.5 h-3.5" />
                <span>{{ saving ? 'Saving...' : 'Save & Render Preview' }}</span>
              </button>
            </div>
            <textarea
              v-model="editableContent"
              rows="18"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950/90 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-purple-500/50 leading-relaxed custom-scrollbar resize-y"
            ></textarea>
          </div>
        </template>
      </div>

      <!-- Footer Actions Toolbar (Hidden on print) -->
      <div v-if="proposalData" class="px-4 sm:px-6 py-3 sm:py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-950/90 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 sm:gap-3 print:hidden">
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 sm:pb-0 custom-scrollbar shrink">
          <!-- Copy Markdown -->
          <button
            type="button"
            @click="copyMarkdown"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer border border-slate-200 dark:border-slate-700 shrink-0"
          >
            <Check v-if="copied" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copied ? 'Copied!' : 'Copy MD' }}</span>
          </button>

          <!-- Copy Client Link -->
          <button
            type="button"
            @click="copyClientLink"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer border border-slate-200 dark:border-slate-700 shrink-0"
          >
            <Check v-if="copiedLink" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
            <ExternalLink v-else class="w-3.5 h-3.5" />
            <span>{{ copiedLink ? 'Copied Link!' : 'Client Link' }}</span>
          </button>

          <!-- Print / PDF -->
          <button
            type="button"
            @click="printProposal"
            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer border border-slate-200 dark:border-slate-700 shrink-0"
          >
            <Printer class="w-3.5 h-3.5" />
            <span>Print / PDF</span>
          </button>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <!-- Share on WhatsApp -->
          <a
            :href="whatsAppShareUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="flex-1 sm:flex-initial px-3.5 sm:px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md shadow-emerald-500/10 cursor-pointer"
          >
            <Share2 class="w-3.5 h-3.5" />
            <span>WhatsApp</span>
          </a>

          <!-- Mark as Sent & Advance Stage -->
          <button
            type="button"
            @click="markAsSent"
            :disabled="sending || proposalData.stage === 'proposal_sent'"
            class="flex-1 sm:flex-initial px-3.5 sm:px-4 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 disabled:opacity-50 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md shadow-purple-500/20 cursor-pointer"
          >
            <Send class="w-3.5 h-3.5" />
            <span>{{ sending ? 'Sending...' : (proposalData.stage === 'proposal_sent' ? '✓ Sent' : 'Mark Sent (75%)') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* Custom Proposal Styling */
.proposal-prose h1 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.75rem;
  margin-bottom: 1.25rem;
}
.dark .proposal-prose h1 {
  color: #f8fafc;
  border-bottom: 1px solid #334155;
}
.proposal-prose h2 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1e293b;
  margin-top: 1.75rem;
  margin-bottom: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.35rem;
}
.dark .proposal-prose h2 {
  color: #e2e8f0;
  border-bottom: 1px solid #1e293b;
}
.proposal-prose p {
  margin-bottom: 0.85rem;
  line-height: 1.6;
}
.proposal-prose ul {
  list-style-type: disc;
  padding-left: 1.25rem;
  margin-bottom: 1rem;
}
.proposal-prose li {
  margin-bottom: 0.4rem;
}
.proposal-prose table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.25rem 0;
  font-size: 0.825rem;
}
.proposal-prose th {
  background-color: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 0.6rem 0.8rem;
  text-align: left;
  font-weight: 700;
  color: #0f172a;
}
.dark .proposal-prose th {
  background-color: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  color: #f1f5f9;
}
.proposal-prose td {
  border: 1px solid #cbd5e1;
  padding: 0.6rem 0.8rem;
}
.dark .proposal-prose td {
  border: 1px solid #334155;
}
.proposal-prose tr:nth-child(even) td {
  background-color: #f8fafc;
}
.dark .proposal-prose tr:nth-child(even) td {
  background-color: rgba(15, 23, 42, 0.4);
}
.proposal-prose blockquote {
  border-left: 3px solid #9333ea;
  background: #faf5ff;
  padding: 0.75rem 1rem;
  border-radius: 0 0.75rem 0.75rem 0;
  margin: 1rem 0;
  color: #475569;
  font-style: italic;
}
.dark .proposal-prose blockquote {
  border-left: 3px solid #a855f7;
  background: rgba(168, 85, 247, 0.08);
  color: #cbd5e1;
}

@media print {
  body * {
    visibility: hidden;
  }
  .proposal-prose, .proposal-prose * {
    visibility: visible;
    color: #0f172a !important;
  }
  .proposal-prose {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 20mm;
  }
  .proposal-prose h1 {
    color: #0f172a !important;
    border-bottom: 2px solid #0f172a !important;
  }
  .proposal-prose h2 {
    color: #1e293b !important;
    border-bottom: 1px solid #cbd5e1 !important;
  }
  .proposal-prose th {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
    border: 1px solid #cbd5e1 !important;
  }
  .proposal-prose td {
    border: 1px solid #cbd5e1 !important;
  }
  .proposal-prose blockquote {
    border-left: 3px solid #7c3aed !important;
    background: #faf5ff !important;
    color: #334155 !important;
  }
}
</style>
