<script setup lang="ts">
import { ref, computed } from 'vue'
import DealCard from './DealCard.vue'
import { Plus, ArrowDown } from 'lucide-vue-next'

const props = defineProps<{
  stageKey: string
  stageData: any
}>()

const emit = defineEmits<{
  (e: 'moveStage', dealId: number, targetStage: string): void
  (e: 'selectDeal', dealId: number): void
  (e: 'quickTouch', deal: any): void
  (e: 'addDeal', stageKey: string): void
  (e: 'proposal', dealId: number): void
}>()

const isOver = ref(false)

const stageOrder = ['new', 'contacted', 'qualified', 'discovery_done', 'proposal_sent', 'negotiation', 'closed_won']

const onAdvanceDeal = (dealId: number) => {
  const currentIdx = stageOrder.indexOf(props.stageKey)
  if (currentIdx >= 0 && currentIdx < stageOrder.length - 1) {
    emit('moveStage', dealId, stageOrder[currentIdx + 1])
  }
}

const stageColorClasses = computed(() => {
  switch (props.stageKey) {
    case 'new':
      return { border: 'border-t-blue-500', badge: 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-500/20' }
    case 'contacted':
      return { border: 'border-t-indigo-500', badge: 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-500/20' }
    case 'qualified':
      return { border: 'border-t-cyan-500', badge: 'bg-cyan-500/10 text-sky-700 dark:text-cyan-400 border border-cyan-500/20' }
    case 'discovery_done':
      return { border: 'border-t-amber-500', badge: 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20' }
    case 'proposal_sent':
      return { border: 'border-t-orange-500', badge: 'bg-orange-500/10 text-orange-700 dark:text-orange-400 border border-orange-500/20' }
    case 'negotiation':
      return { border: 'border-t-purple-500', badge: 'bg-purple-500/10 text-purple-700 dark:text-purple-400 border border-purple-500/20' }
    case 'closed_won':
      return { border: 'border-t-emerald-500', badge: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20' }
    case 'closed_lost':
      return { border: 'border-t-rose-500', badge: 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/20' }
    default:
      return { border: 'border-t-slate-500', badge: 'bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-500/20' }
  }
})

const formattedTotal = computed(() => {
  const parts = []
  if (props.stageData.total_inr > 0) {
    parts.push('₹' + Number(props.stageData.total_inr).toLocaleString('en-IN'))
  }
  if (props.stageData.total_usd > 0) {
    parts.push('$' + Number(props.stageData.total_usd).toLocaleString())
  }
  return parts.length > 0 ? parts.join(' · ') : '₹0'
})

const onDragOver = (e: DragEvent) => {
  e.preventDefault()
  isOver.value = true
}

const onDragLeave = () => {
  isOver.value = false
}

const onDrop = (e: DragEvent) => {
  e.preventDefault()
  isOver.value = false
  if (e.dataTransfer) {
    const dealId = Number(e.dataTransfer.getData('text/plain'))
    if (dealId) {
      emit('moveStage', dealId, props.stageKey)
    }
  }
}
</script>

<template>
  <div
    @dragover="onDragOver"
    @dragleave="onDragLeave"
    @drop="onDrop"
    class="flex flex-col flex-shrink-0 w-[84vw] max-w-[340px] sm:w-76 md:w-80 bg-slate-100/90 dark:bg-slate-950/70 rounded-2xl border border-slate-200 dark:border-slate-800/80 border-t-4 shadow-sm dark:shadow-md transition-all duration-200 max-h-[72vh] sm:max-h-[calc(100vh-210px)] snap-center sm:snap-align-none"
    :class="[
      stageColorClasses.border,
      isOver ? 'bg-sky-50/80 dark:bg-cyan-950/30 border-sky-400 dark:border-cyan-500/60 ring-2 ring-sky-400/20 dark:ring-cyan-500/20' : ''
    ]"
  >
    <!-- Column Header -->
    <div class="p-3 sm:p-3.5 border-b border-slate-200 dark:border-slate-800/70 flex items-center justify-between bg-slate-200/50 dark:bg-slate-900/40">
      <div class="flex items-center gap-2 min-w-0">
        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider truncate">
          {{ stageData.meta.name }}
        </h3>
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0" :class="stageColorClasses.badge">
          {{ stageData.count }}
        </span>
      </div>

      <div class="text-[11px] font-extrabold text-slate-800 dark:text-slate-300 shrink-0 ml-2 font-mono tabular-nums">
        {{ formattedTotal }}
      </div>
    </div>

    <!-- Cards Scroll Area -->
    <div class="flex-1 p-2.5 space-y-2.5 overflow-y-auto min-h-[140px] custom-scrollbar">
      <!-- Active Drop Target Indicator when dragging over -->
      <div
        v-if="isOver"
        class="h-14 border-2 border-dashed border-sky-400/60 dark:border-cyan-400/60 bg-sky-500/10 dark:bg-cyan-500/10 rounded-xl flex items-center justify-center text-xs text-sky-700 dark:text-cyan-300 font-bold gap-1.5 animate-pulse"
      >
        <ArrowDown class="w-4 h-4" />
        <span>Drop deal to advance here</span>
      </div>

      <!-- Empty State -->
      <div v-if="stageData.deals.length === 0 && !isOver" class="h-28 flex flex-col items-center justify-center text-center p-3 border-2 border-dashed border-slate-300 dark:border-slate-800/60 rounded-xl text-slate-500 dark:text-slate-600 text-xs select-none">
        <span>No deals in this stage</span>
        <span class="text-[10px] text-slate-400 dark:text-slate-700 mt-1">Drag deals here or use → on card</span>
      </div>

      <DealCard
        v-for="deal in stageData.deals"
        :key="deal.id"
        :deal="deal"
        @select="(id) => emit('selectDeal', id)"
        @quick-touch="(d) => emit('quickTouch', d)"
        @advance="onAdvanceDeal"
        @proposal="(id) => emit('proposal', id)"
      />
    </div>

    <!-- Column Footer Quick Add -->
    <div class="p-2 border-t border-slate-200/80 dark:border-slate-800/60 bg-slate-200/30 dark:bg-slate-900/20">
      <button
        type="button"
        @click="emit('addDeal', stageKey)"
        class="w-full py-1.5 px-3 rounded-xl bg-white dark:bg-slate-900/60 hover:bg-slate-50 dark:hover:bg-slate-850 hover:text-sky-600 dark:hover:text-cyan-300 text-slate-600 dark:text-slate-400 text-xs font-semibold border border-dashed border-slate-300 dark:border-slate-800/80 hover:border-slate-400 dark:hover:border-slate-700 flex items-center justify-center gap-1.5 transition cursor-pointer shadow-xs"
      >
        <Plus class="w-3.5 h-3.5" />
        <span>Add Lead</span>
      </button>
    </div>
  </div>
</template>
