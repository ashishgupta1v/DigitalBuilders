<script setup lang="ts">
import { computed } from 'vue'
import { Building2, User, Phone, MessageSquare, AlertCircle, Calendar, ArrowRight, ChevronRight, Zap, FileText } from 'lucide-vue-next'

const props = defineProps<{
  deal: any
}>()

const emit = defineEmits<{
  (e: 'select', dealId: number): void
  (e: 'quickTouch', deal: any): void
  (e: 'advance', dealId: number): void
  (e: 'proposal', dealId: number): void
}>()

const segmentBadgeClass = computed(() => {
  const seg = props.deal.lead?.segment || 'general'
  switch (seg) {
    case 'manufacturer':
      return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
    case 'retail':
      return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
    case 'clinic':
      return 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20'
    case 'coaching':
      return 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20'
    case 'international':
      return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'
    default:
      return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20'
  }
})

const scoreColor = computed(() => {
  const score = props.deal.lead?.score || 50
  if (score >= 80) return 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
  if (score >= 65) return 'text-amber-600 dark:text-amber-400 bg-amber-500/10 border-amber-500/20'
  return 'text-slate-600 dark:text-slate-400 bg-slate-500/10 border-slate-500/20'
})

const onDragStart = (e: DragEvent) => {
  if (e.dataTransfer) {
    e.dataTransfer.setData('text/plain', String(props.deal.id))
    e.dataTransfer.effectAllowed = 'move'
  }
}
</script>

<template>
  <div
    draggable="true"
    @dragstart="onDragStart"
    @click="emit('select', deal.id)"
    class="group relative bg-white dark:bg-slate-900/90 hover:bg-slate-50 dark:hover:bg-slate-850 border border-slate-200 dark:border-slate-800/90 hover:border-cyan-500/40 rounded-2xl p-3.5 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-xl dark:hover:shadow-cyan-950/20 transition-all duration-200 cursor-grab active:cursor-grabbing select-none"
  >
    <!-- Top Row: Segment Badge, Probability & Qualification Score -->
    <div class="flex items-center justify-between gap-2 mb-2">
      <span class="text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 rounded-full border" :class="segmentBadgeClass">
        {{ deal.lead?.segment || 'General' }}
      </span>

      <div class="flex items-center gap-1.5">
        <span
          v-if="deal.probability > 0"
          title="Closing Probability"
          class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700/50 font-mono"
        >
          {{ deal.probability }}%
        </span>

        <span
          title="Lead Qualification Score (0-100)"
          class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
          :class="scoreColor"
        >
          {{ deal.lead?.score || 50 }} pts
        </span>
      </div>
    </div>

    <!-- Deal Title -->
    <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 group-hover:text-cyan-600 dark:group-hover:text-cyan-300 transition-colors line-clamp-1 mb-1">
      {{ deal.title }}
    </h4>

    <!-- Organization & Contact Profile -->
    <div class="space-y-1 mb-3 text-xs text-slate-500 dark:text-slate-400">
      <div v-if="deal.lead?.company || deal.organization?.name" class="flex items-center gap-1.5 line-clamp-1 text-slate-700 dark:text-slate-300">
        <Building2 class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" />
        <span class="font-medium">{{ deal.lead?.company || deal.organization?.name }}</span>
      </div>
      <div v-if="deal.lead?.name" class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
        <User class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" />
        <span>{{ deal.lead.name }}</span>
        <span v-if="deal.lead.touchpoint_count > 0" class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700/50 font-mono">
          T{{ deal.lead.touchpoint_count }}/5
        </span>
      </div>
    </div>

    <!-- Bottom Bar: Value, Next Action & 1-Tap Triggers -->
    <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
      <div class="font-extrabold text-slate-900 dark:text-white text-sm tracking-tight font-mono tabular-nums db-price">
        {{ deal.formatted_amount }}
      </div>

      <div class="flex items-center gap-1.5">
        <!-- Overdue Action Badge -->
        <div
          v-if="deal.lead?.is_overdue"
          title="Action overdue! Click to outreach"
          class="flex items-center gap-1 text-[11px] text-rose-600 dark:text-rose-400 font-medium bg-rose-500/10 px-1.5 py-0.5 rounded-lg border border-rose-500/20 animate-pulse"
        >
          <AlertCircle class="w-3 h-3" />
          <span>Due</span>
        </div>

        <!-- 1-Click Proposal Button -->
        <button
          v-if="deal.stage === 'discovery_done' || deal.stage === 'proposal_sent' || deal.stage === 'negotiation'"
          type="button"
          @click.stop="emit('proposal', deal.id)"
          title="Open Engineering Proposal"
          class="p-2 sm:p-1.5 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/20 hover:border-purple-500/40 transition cursor-pointer min-w-[36px] min-h-[36px] sm:min-w-0 sm:min-h-0 flex items-center justify-center"
        >
          <FileText class="w-3.5 h-3.5" />
        </button>

        <!-- 1-Click WhatsApp Touch Button -->
        <button
          type="button"
          @click.stop="emit('quickTouch', deal)"
          title="Open WhatsApp Outreach Script"
          class="p-2 sm:p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:border-emerald-500/40 transition cursor-pointer min-w-[36px] min-h-[36px] sm:min-w-0 sm:min-h-0 flex items-center justify-center"
        >
          <MessageSquare class="w-3.5 h-3.5" />
        </button>

        <!-- Quick Advance Button (Visible on Touch and on Desktop Hover) -->
        <button
          v-if="deal.stage !== 'closed_won' && deal.stage !== 'closed_lost'"
          type="button"
          @click.stop="emit('advance', deal.id)"
          title="Advance to next stage with 1 click"
          class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 hover:!opacity-100 p-2 sm:p-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 hover:border-cyan-500/40 transition cursor-pointer min-w-[36px] min-h-[36px] sm:min-w-0 sm:min-h-0 flex items-center justify-center"
        >
          <ChevronRight class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>
