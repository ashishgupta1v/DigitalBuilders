<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import {
  Sparkles, Globe, DollarSign, Calendar, ExternalLink, ArrowRight,
  Copy, Check, Mail, Building2, User, X, Layers, CheckCircle2,
  Share2, Shield, Search, Terminal, Zap, Clock
} from 'lucide-vue-next'

const props = defineProps<{
  requirement: any | null
  appMeta?: {
    founder_name?: string
    founder_email?: string
    booking_url?: string
    website_url?: string
  }
}>()

const emit = defineEmits<{
  (e: 'convert', req: any): void
  (e: 'dismiss', reqId: number): void
  (e: 'openPitchModal', req: any): void
  (e: 'close'): void
}>()

const activePitchTab = ref<'upwork' | 'email' | 'linkedin' | 'arch'>('upwork')
const copiedPitch = ref(false)

const upworkPitch = computed(() => {
  if (!props.requirement) return ''
  return props.requirement.upwork_proposal || props.requirement.pitch_draft || ''
})

const emailPitch = computed(() => {
  if (!props.requirement) return ''
  return props.requirement.email_pitch || props.requirement.pitch_draft || ''
})

const emailSubject = computed(() => {
  if (!props.requirement) return ''
  return props.requirement.email_subject || `Technical Architecture Proposal for ${props.requirement.contact_company || 'Your Project'}`
})

const linkedinPitch = computed(() => {
  if (!props.requirement) return ''
  const founderName = props.appMeta?.founder_name || 'Ashish'
  const bookingUrl = props.appMeta?.booking_url || 'https://www.digitalbuilders.in/book'
  return props.requirement.linkedin_dm || `Hi ${props.requirement.contact_name || 'there'}, saw your project regarding ${props.requirement.title}. I'm ${founderName}, lead architect at DigitalBuilders. We specialize in rapid 4-6 week MVP delivery with clean architecture & full IP ownership. Let's sync: ${bookingUrl}`
})

const activeCopyText = computed(() => {
  if (activePitchTab.value === 'upwork') return upworkPitch.value
  if (activePitchTab.value === 'email') return `Subject: ${emailSubject.value}\n\n${emailPitch.value}`
  if (activePitchTab.value === 'linkedin') return linkedinPitch.value
  if (activePitchTab.value === 'arch') {
    const r = props.requirement
    return `Suggested Architecture: ${r?.suggested_architecture || 'Modular full-stack SaaS'}\nTech Stack: ${(r?.detected_tech_stack || []).join(', ')}\nKey Pain Points: ${(r?.client_pain_points || []).join(', ')}`
  }
  return upworkPitch.value
})

const copyPitch = async () => {
  if (!activeCopyText.value) return
  await navigator.clipboard.writeText(activeCopyText.value)
  copiedPitch.value = true
  setTimeout(() => {
    copiedPitch.value = false
  }, 2500)
}

const openDirectMail = () => {
  if (!props.requirement?.contact_email) return
  const subj = encodeURIComponent(emailSubject.value)
  const body = encodeURIComponent(emailPitch.value)
  window.open(`mailto:${props.requirement.contact_email}?subject=${subj}&body=${body}`, '_blank')
}
</script>

<template>
  <div
    v-if="requirement"
    class="h-full bg-white dark:bg-[#0c101c] border border-slate-200 dark:border-slate-800/90 rounded-2xl shadow-xl flex flex-col overflow-hidden transition-all duration-200"
  >
    <!-- Header Bar -->
    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex items-center justify-between gap-3 shrink-0">
      <div class="flex items-center gap-2 flex-wrap min-w-0">
        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
          {{ requirement.source }}
        </span>
        <span class="text-xs font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
          {{ requirement.budget || 'Custom Budget' }}
        </span>
        <span
          class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold"
          :class="requirement.relevance_score >= 80 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'"
        >
          {{ requirement.relevance_score }}/100 Match
        </span>
      </div>

      <div class="flex items-center gap-1.5 shrink-0">
        <!-- Convert to Deal -->
        <button
          type="button"
          @click="emit('convert', requirement)"
          class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-sm"
          title="Convert to active Pipeline Deal (Hot-key: D)"
        >
          <ArrowRight class="w-3.5 h-3.5" />
          <span>Convert ($)</span>
          <kbd class="hidden sm:inline px-1 py-0.2 rounded bg-black/20 text-[9px] font-mono">D</kbd>
        </button>

        <!-- Dismiss -->
        <button
          type="button"
          @click="emit('dismiss', requirement.id)"
          class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 transition cursor-pointer"
          title="Dismiss from feed (Hot-key: X)"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Scrollable Content Body -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-5 custom-scrollbar">
      <!-- Title & Time -->
      <div>
        <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-1">
          <Clock class="w-3.5 h-3.5" />
          <span>Detected: {{ requirement.created_at }}</span>
          <span v-if="requirement.url" class="mx-1">•</span>
          <a
            v-if="requirement.url"
            :href="requirement.url"
            target="_blank"
            class="text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-0.5"
          >
            <span>View Source Feed</span>
            <ExternalLink class="w-3 h-3" />
          </a>
        </div>
        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-snug">
          {{ requirement.title }}
        </h3>
      </div>

      <!-- Contact Discovery Box -->
      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800/80 space-y-2">
        <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
          <span>Client & Contact Dossier</span>
          <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
            <Shield class="w-3 h-3" />
            Verified Channel
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
          <div v-if="requirement.contact_company" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
            <Building2 class="w-3.5 h-3.5 text-slate-400 shrink-0" />
            <span class="font-bold truncate">{{ requirement.contact_company }}</span>
          </div>

          <div v-if="requirement.contact_name" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
            <User class="w-3.5 h-3.5 text-slate-400 shrink-0" />
            <span class="truncate">{{ requirement.contact_name }}</span>
          </div>

          <div v-if="requirement.contact_email" class="flex items-center gap-2 text-sky-600 dark:text-sky-400 col-span-full">
            <Mail class="w-3.5 h-3.5 shrink-0" />
            <span class="truncate font-mono">{{ requirement.contact_email }}</span>
            <button
              type="button"
              @click="openDirectMail"
              class="ml-auto px-2 py-0.5 rounded bg-sky-500/10 hover:bg-sky-500/20 text-sky-700 dark:text-sky-300 text-[10px] font-bold transition cursor-pointer"
            >
              Email Now
            </button>
          </div>
        </div>

        <!-- Quick 1-Click Search Links -->
        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center gap-2 text-[11px]">
          <span class="text-slate-400">Search:</span>
          <a
            :href="`https://www.google.com/search?q=${encodeURIComponent((requirement.contact_company || requirement.title) + ' founder OR CEO')}`"
            target="_blank"
            class="text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-0.5"
          >
            Google
            <ExternalLink class="w-2.5 h-2.5" />
          </a>
          <span class="text-slate-300 dark:text-slate-700">•</span>
          <a
            :href="`https://www.linkedin.com/search/results/all/?keywords=${encodeURIComponent(requirement.contact_company || requirement.contact_name || requirement.title)}`"
            target="_blank"
            class="text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-0.5"
          >
            LinkedIn
            <ExternalLink class="w-2.5 h-2.5" />
          </a>
        </div>
      </div>

      <!-- Tech Stack & Client Pain Points -->
      <div class="space-y-3">
        <div v-if="requirement.detected_tech_stack?.length" class="space-y-1.5">
          <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Detected Tech Stack</div>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="tech in requirement.detected_tech_stack"
              :key="tech"
              class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20"
            >
              {{ tech }}
            </span>
          </div>
        </div>

        <div v-if="requirement.client_pain_points?.length" class="space-y-1.5">
          <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Identified Client Pain Points</div>
          <ul class="space-y-1 text-xs text-slate-600 dark:text-slate-400">
            <li v-for="(point, pidx) in requirement.client_pain_points" :key="pidx" class="flex items-start gap-2">
              <span class="text-purple-500 font-bold shrink-0">•</span>
              <span>{{ point }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Full Scope Description -->
      <div class="space-y-1.5">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Full Requirement Scope</div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed max-h-56 overflow-y-auto custom-scrollbar">
          {{ requirement.raw_text }}
        </div>
      </div>

      <!-- Embedded AI Pitch Generator Studio -->
      <div class="p-4 rounded-xl bg-purple-500/5 dark:bg-purple-950/20 border border-purple-500/20 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Sparkles class="w-4 h-4 text-purple-600 dark:text-purple-400" />
            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
              Tailored AI Pitch & Cadence
            </h4>
          </div>

          <button
            type="button"
            @click="copyPitch"
            class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
            :class="copiedPitch
              ? 'bg-emerald-600 text-white'
              : 'bg-purple-600 text-white hover:bg-purple-500'"
          >
            <Check v-if="copiedPitch" class="w-3.5 h-3.5" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copiedPitch ? 'Copied to Clipboard!' : 'Copy Active Pitch' }}</span>
          </button>
        </div>

        <!-- Pitch Tabs -->
        <div class="flex items-center gap-1 border-b border-purple-500/20 pb-2">
          <button
            type="button"
            @click="activePitchTab = 'upwork'"
            class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
            :class="activePitchTab === 'upwork'
              ? 'bg-purple-600 text-white shadow-sm'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
          >
            Upwork Proposal
          </button>
          <button
            type="button"
            @click="activePitchTab = 'email'"
            class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
            :class="activePitchTab === 'email'
              ? 'bg-purple-600 text-white shadow-sm'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
          >
            Executive Email
          </button>
          <button
            type="button"
            @click="activePitchTab = 'linkedin'"
            class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
            :class="activePitchTab === 'linkedin'
              ? 'bg-purple-600 text-white shadow-sm'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
          >
            LinkedIn DM
          </button>
          <button
            type="button"
            @click="activePitchTab = 'arch'"
            class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
            :class="activePitchTab === 'arch'
              ? 'bg-purple-600 text-white shadow-sm'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
          >
            Architecture
          </button>
        </div>

        <!-- Pitch Content Box -->
        <div class="p-3.5 rounded-lg bg-white/90 dark:bg-slate-900/90 border border-purple-500/20 text-xs text-slate-800 dark:text-slate-200 leading-relaxed max-h-64 overflow-y-auto whitespace-pre-wrap font-mono custom-scrollbar">
          <template v-if="activePitchTab === 'email'">
            <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400 mb-2 border-b border-slate-200 dark:border-slate-800 pb-1">
              Subject: {{ emailSubject }}
            </div>
            {{ emailPitch || 'Generating email pitch...' }}
          </template>
          <template v-else-if="activePitchTab === 'linkedin'">
            {{ linkedinPitch }}
          </template>
          <template v-else-if="activePitchTab === 'arch'">
            <div class="space-y-2 font-sans">
              <div>
                <span class="font-bold text-slate-900 dark:text-white">Suggested Architecture: </span>
                <span>{{ requirement.suggested_architecture || 'Modular multi-tenant Laravel + Vue 3 architecture' }}</span>
              </div>
              <div>
                <span class="font-bold text-slate-900 dark:text-white">Estimated Timeline: </span>
                <span>4–6 weeks to production launch</span>
              </div>
            </div>
          </template>
          <template v-else>
            {{ upworkPitch || 'No pitch draft available. Click "Pitch" button in card to generate.' }}
          </template>
        </div>
      </div>
    </div>
  </div>

  <!-- Empty State when nothing selected -->
  <div
    v-else
    class="h-full min-h-[400px] p-8 text-center bg-white dark:bg-[#0c101c] border border-slate-200 dark:border-slate-800/90 rounded-2xl flex flex-col items-center justify-center text-slate-400 dark:text-slate-500"
  >
    <Globe class="w-10 h-10 text-purple-500/30 mb-3 animate-pulse" />
    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">No RFP Selected</h4>
    <p class="text-xs max-w-xs mt-1">
      Use <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-mono font-bold text-purple-600">J</kbd> and <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-mono font-bold text-purple-600">K</kbd> to step through the feed.
    </p>
  </div>
</template>
