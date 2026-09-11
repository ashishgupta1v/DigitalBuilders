<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  User, Building2, Mail, Phone, Globe, ExternalLink, Calendar,
  Sparkles, CheckCircle2, ArrowRight, Send, Clock, Play, Pause,
  Shield, Edit3, Trash2, Layers, Check, Copy, Eye, MousePointerClick
} from 'lucide-vue-next'

const props = defineProps<{
  lead: any | null
  appMeta?: {
    founder_name?: string
    founder_email?: string
    booking_url?: string
    website_url?: string
  }
}>()

const emit = defineEmits<{
  (e: 'openDrawer', leadId: number): void
  (e: 'convert', lead: any): void
  (e: 'enrich', leadId: number): void
  (e: 'delete', leadId: number): void
  (e: 'close'): void
}>()

const sendDirectMail = () => {
  if (!props.lead?.email) return
  const founderName = props.appMeta?.founder_name || 'Ashish Gupta'
  const bookingUrl = props.appMeta?.booking_url || 'https://www.digitalbuilders.in/book'
  const websiteUrl = props.appMeta?.website_url || 'https://www.digitalbuilders.in'
  const company = props.lead.company || props.lead.name
  const subject = encodeURIComponent(`Architecture & Timeline Proposal for ${company}`)
  const body = encodeURIComponent(`Hi ${props.lead.name},\n\nI'm ${founderName}, founder & lead architect at ${props.appMeta?.founder_name || 'DigitalBuilders'} (${websiteUrl}).\n\nWanted to connect regarding your custom software scope.\n\nFeel free to pick a 15-min discovery slot on my calendar: ${bookingUrl}\n\nBest regards,\n${founderName}`)
  window.open(`mailto:${props.lead.email}?subject=${subject}&body=${body}`, '_blank')
}
</script>

<template>
  <div
    v-if="lead"
    class="h-full bg-white dark:bg-[#0c101c] border border-slate-200 dark:border-slate-800/90 rounded-2xl shadow-xl flex flex-col overflow-hidden transition-all duration-200"
  >
    <!-- Header Bar -->
    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex items-center justify-between gap-3 shrink-0">
      <div class="flex items-center gap-2 flex-wrap min-w-0">
        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
          {{ lead.segment }}
        </span>
        <span class="text-xs font-mono font-extrabold text-slate-900 dark:text-white">
          {{ lead.deal_amount }}
        </span>
        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
          {{ lead.score }}/100 Match
        </span>
      </div>

      <div class="flex items-center gap-1.5 shrink-0">
        <!-- Convert to Deal -->
        <button
          type="button"
          @click="emit('convert', lead)"
          class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-sm"
          title="Convert to active Pipeline Deal (Hot-key: D)"
        >
          <ArrowRight class="w-3.5 h-3.5" />
          <span>Convert ($)</span>
          <kbd class="hidden sm:inline px-1 py-0.2 rounded bg-black/20 text-[9px] font-mono">D</kbd>
        </button>

        <!-- Open Drawer -->
        <button
          type="button"
          @click="emit('openDrawer', lead.id)"
          class="px-2.5 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/20 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
          title="Open Full Profile & Cadence Drawer (Space)"
        >
          <ExternalLink class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Drawer</span>
        </button>
      </div>
    </div>

    <!-- Scrollable Content Body -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-5 custom-scrollbar">
      <!-- Profile Identification -->
      <div class="flex items-start gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white font-extrabold text-lg flex items-center justify-center shadow-md shadow-purple-500/20 shrink-0">
          {{ (lead.name || 'P').charAt(0).toUpperCase() }}
        </div>
        <div class="min-w-0">
          <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight truncate">
            {{ lead.name }}
          </h3>
          <div class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5 mt-0.5 truncate">
            <Building2 class="w-3.5 h-3.5 shrink-0" />
            <span class="truncate">{{ lead.company || 'Private Client' }}</span>
            <span v-if="lead.country" class="text-slate-300 dark:text-slate-700">•</span>
            <span v-if="lead.country" class="font-mono text-[11px]">{{ lead.country }}</span>
          </div>
        </div>
      </div>

      <!-- Contact Actions -->
      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800/80 space-y-2">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Contact Channels</div>
        <div class="space-y-1.5 text-xs">
          <div v-if="lead.email" class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-sky-600 dark:text-sky-400 font-mono truncate min-w-0">
              <Mail class="w-3.5 h-3.5 shrink-0" />
              <span class="truncate">{{ lead.email }}</span>
            </div>
            <button
              type="button"
              @click="sendDirectMail"
              class="px-2 py-0.5 rounded bg-sky-500/10 hover:bg-sky-500/20 text-sky-700 dark:text-sky-300 text-[10px] font-bold transition cursor-pointer shrink-0"
            >
              Email
            </button>
          </div>

          <div v-if="lead.phone" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 font-mono">
            <Phone class="w-3.5 h-3.5 shrink-0" />
            <span>{{ lead.phone }}</span>
          </div>

          <div v-if="lead.website" class="flex items-center gap-2 text-purple-600 dark:text-purple-400">
            <Globe class="w-3.5 h-3.5 shrink-0" />
            <a :href="lead.website" target="_blank" class="hover:underline truncate">{{ lead.website }}</a>
          </div>
        </div>

        <!-- Social Helpers -->
        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center gap-3 text-[11px]">
          <span class="text-slate-400">Enrich:</span>
          <a
            :href="`https://www.linkedin.com/search/results/all/?keywords=${encodeURIComponent(lead.name + ' ' + (lead.company || ''))}`"
            target="_blank"
            class="text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-0.5"
          >
            LinkedIn Search
            <ExternalLink class="w-2.5 h-2.5" />
          </a>
          <button
            type="button"
            @click="emit('enrich', lead.id)"
            class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold cursor-pointer"
          >
            ⚡ Auto-Enrich Dossier [E]
          </button>
        </div>
      </div>

      <!-- Detected Tech Stack Profile -->
      <div v-if="lead.detected_stack?.length" class="space-y-1.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Detected & Recommended Stack</div>
        <div class="flex items-center gap-1.5 flex-wrap">
          <span
            v-for="t in lead.detected_stack"
            :key="t"
            class="px-2 py-0.5 rounded-lg text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 shadow-xs"
          >
            ⚡ {{ t }}
          </span>
        </div>
      </div>

      <!-- Outbound Cadence Widget -->
      <div class="p-4 rounded-xl bg-purple-500/5 dark:bg-purple-950/20 border border-purple-500/20 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Send class="w-4 h-4 text-purple-600 dark:text-purple-400" />
            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
              4-Step Outbound Cadence
            </h4>
          </div>

          <button
            type="button"
            @click="emit('openDrawer', lead.id)"
            class="px-2.5 py-1 rounded-lg bg-purple-600 text-white hover:bg-purple-500 text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-sm"
          >
            <Sparkles class="w-3 h-3" />
            <span>Manage Cadence [A]</span>
          </button>
        </div>

        <!-- Sequence Step Indicators -->
        <div class="grid grid-cols-4 gap-1.5 text-center">
          <div class="p-2 rounded-lg bg-white/80 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
            <div class="text-[10px] font-extrabold text-purple-600">STEP 1</div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5">Value Pitch</div>
          </div>
          <div class="p-2 rounded-lg bg-white/80 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
            <div class="text-[10px] font-extrabold text-sky-600">STEP 2</div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5">Case Study</div>
          </div>
          <div class="p-2 rounded-lg bg-white/80 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
            <div class="text-[10px] font-extrabold text-amber-600">STEP 3</div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5">Urgency/ROI</div>
          </div>
          <div class="p-2 rounded-lg bg-white/80 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
            <div class="text-[10px] font-extrabold text-rose-600">STEP 4</div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5">Breakup</div>
          </div>
        </div>
      </div>

      <!-- Next Action & Notes -->
      <div class="space-y-2">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Next Scheduled Action</div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 text-xs">
          <div class="font-bold text-slate-900 dark:text-white">
            {{ lead.next_action_note || 'Initial discovery and outbound cadence activation' }}
          </div>
          <div v-if="lead.next_action_date" class="text-[11px] text-purple-600 dark:text-purple-400 font-mono mt-1">
            Scheduled Due Date: {{ lead.next_action_date }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Empty state -->
  <div
    v-else
    class="h-full min-h-[400px] p-8 text-center bg-white dark:bg-[#0c101c] border border-slate-200 dark:border-slate-800/90 rounded-2xl flex flex-col items-center justify-center text-slate-400 dark:text-slate-500"
  >
    <User class="w-10 h-10 text-purple-500/30 mb-3 animate-pulse" />
    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">No Lead Selected</h4>
    <p class="text-xs max-w-xs mt-1">
      Use <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-mono font-bold text-purple-600">J</kbd> and <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-mono font-bold text-purple-600">K</kbd> to step through contacts.
    </p>
  </div>
</template>
