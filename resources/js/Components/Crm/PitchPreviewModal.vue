<script setup lang="ts">
import { ref, watch } from 'vue'
import { X, Copy, Check, ExternalLink, ArrowRight, Sparkles, Building2, Globe } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  requirement: any | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'convert', id: number): void
}>()

const editablePitch = ref('')
const copied = ref(false)

watch(
  () => props.requirement,
  (newReq) => {
    if (newReq) {
      editablePitch.value = newReq.pitch_draft || ''
      copied.value = false
    }
  },
  { immediate: true }
)

const copyToClipboard = async () => {
  if (!editablePitch.value) return
  await navigator.clipboard.writeText(editablePitch.value)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2500)
}

const onConvert = () => {
  if (props.requirement) {
    emit('convert', props.requirement.id)
    emit('close')
  }
}
</script>

<template>
  <div v-if="show && requirement" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200">
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-1">
              {{ requirement.title }}
            </h3>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <span class="uppercase font-bold text-purple-600 dark:text-purple-400">{{ requirement.source }}</span>
              <span>•</span>
              <span class="font-mono font-bold">{{ requirement.budget }}</span>
              <span>•</span>
              <span>{{ requirement.relevance_score }}/100 Match</span>
            </div>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-5 overflow-y-auto space-y-4">
        <!-- Raw Requirement Summary -->
        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800">
          <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Raw Requirement / Scope</span>
          <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3">
            {{ requirement.raw_text }}
          </p>
        </div>

        <!-- Editable Proposal Pitch -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
              Customized Proposal Draft (Editable)
            </label>
            <span class="text-[11px] text-slate-400">
              Feel free to tweak before copying
            </span>
          </div>
          <textarea
            v-model="editablePitch"
            rows="10"
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-purple-500 leading-relaxed custom-scrollbar"
          ></textarea>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
          <a
            v-if="requirement.url"
            :href="requirement.url"
            target="_blank"
            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition"
          >
            <ExternalLink class="w-3.5 h-3.5" />
            <span>Open Post</span>
          </a>
          <button
            type="button"
            @click="onConvert"
            class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer border border-indigo-500/20"
          >
            <ArrowRight class="w-3.5 h-3.5" />
            <span>Convert to Deal</span>
          </button>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="emit('close')"
            class="px-3.5 py-1.5 rounded-xl text-slate-600 dark:text-slate-400 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            Close
          </button>
          <button
            type="button"
            @click="copyToClipboard"
            class="px-4 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
            :class="copied
              ? 'bg-emerald-600 text-white'
              : 'bg-purple-600 hover:bg-purple-700 text-white'"
          >
            <Check v-if="copied" class="w-3.5 h-3.5" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copied ? 'Copied to Clipboard!' : 'Copy Proposal' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
