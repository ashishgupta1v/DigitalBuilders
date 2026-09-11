<script setup lang="ts">
import { ref } from 'vue'
import { Sparkles, Command, HelpCircle, ChevronUp, ChevronDown, Layers } from 'lucide-vue-next'

const props = defineProps<{
  activeTab: string
  currentIndex?: number
  totalCount?: number
  isSplitView?: boolean
}>()

const emit = defineEmits<{
  (e: 'toggleHelp'): void
  (e: 'toggleCommand'): void
  (e: 'toggleSplit'): void
}>()

const isMinimized = ref(false)
</script>

<template>
  <div class="fixed bottom-3.5 left-1/2 -translate-x-1/2 z-40 max-w-[95vw] select-none pointer-events-auto">
    <!-- Minimized pill -->
    <div
      v-if="isMinimized"
      class="px-3 py-1.5 rounded-full bg-slate-900/90 dark:bg-slate-950/90 text-slate-200 border border-slate-700/60 dark:border-slate-800 shadow-xl backdrop-blur-md text-xs font-bold flex items-center gap-2 cursor-pointer hover:border-purple-500/50 transition"
      @click="isMinimized = false"
    >
      <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
      <span>Cockpit Hotkeys</span>
      <ChevronUp class="w-3.5 h-3.5 text-slate-400" />
    </div>

    <!-- Full Floating Dock -->
    <div
      v-else
      class="px-3 sm:px-4 py-2 rounded-2xl bg-white/90 dark:bg-[#0c101c]/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xl shadow-purple-950/20 backdrop-blur-xl flex items-center gap-2 sm:gap-3 text-xs text-slate-700 dark:text-slate-300 transition-all duration-200"
    >
      <!-- Selection Counter badge -->
      <div v-if="totalCount !== undefined && totalCount > 0" class="hidden md:flex items-center gap-1.5 pr-2.5 border-r border-slate-200 dark:border-slate-800 text-[11px] font-mono text-slate-500">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="font-bold text-slate-800 dark:text-slate-200">#{{ (currentIndex ?? 0) + 1 }}</span>
        <span>of {{ totalCount }}</span>
      </div>

      <!-- Hotkey Chips -->
      <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- J/K Nav -->
        <div class="flex items-center gap-1" title="Navigate list">
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-purple-600 dark:text-purple-300 border border-slate-300/80 dark:border-slate-700">J</kbd>
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-purple-600 dark:text-purple-300 border border-slate-300/80 dark:border-slate-700">K</kbd>
          <span class="hidden lg:inline text-[11px] text-slate-400">Step</span>
        </div>

        <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>

        <!-- A for Outreach/Cadence -->
        <div class="flex items-center gap-1" title="Launch / inspect 4-step sequence">
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-sky-600 dark:text-sky-400 border border-slate-300/80 dark:border-slate-700">A</kbd>
          <span class="hidden sm:inline text-[11px] font-medium">Cadence</span>
        </div>

        <!-- D for Deal -->
        <div class="flex items-center gap-1" title="Convert to Deal ($ USD)">
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-emerald-600 dark:text-emerald-400 border border-slate-300/80 dark:border-slate-700">D</kbd>
          <span class="hidden sm:inline text-[11px] font-medium">Deal ($)</span>
        </div>

        <!-- P for Pitch -->
        <div v-if="activeTab === 'hunter'" class="flex items-center gap-1" title="AI Pitch draft">
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-amber-600 dark:text-amber-400 border border-slate-300/80 dark:border-slate-700">P</kbd>
          <span class="hidden sm:inline text-[11px] font-medium">Pitch</span>
        </div>

        <!-- E for Enrich -->
        <div v-if="activeTab === 'leads'" class="flex items-center gap-1" title="Enrich lead dossier">
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-indigo-600 dark:text-indigo-400 border border-slate-300/80 dark:border-slate-700">E</kbd>
          <span class="hidden sm:inline text-[11px] font-medium">Enrich</span>
        </div>

        <!-- X for Pass -->
        <div class="flex items-center gap-1" title="Dismiss / Pass">
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-rose-600 dark:text-rose-400 border border-slate-300/80 dark:border-slate-700">X</kbd>
          <span class="hidden md:inline text-[11px] font-medium text-slate-400">Pass</span>
        </div>

        <!-- Toggle Split [ -->
        <button
          type="button"
          @click="emit('toggleSplit')"
          class="hidden md:flex items-center gap-1 hover:text-purple-500 transition cursor-pointer"
          title="Toggle Split-Screen / Full Width"
        >
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[10px] text-slate-700 dark:text-slate-300 border border-slate-300/80 dark:border-slate-700">[</kbd>
          <span class="text-[11px] font-medium text-slate-400">{{ isSplitView ? 'Full' : 'Split' }}</span>
        </button>
      </div>

      <span class="text-slate-300 dark:text-slate-700">|</span>

      <!-- Action buttons -->
      <div class="flex items-center gap-1.5">
        <!-- Command palette button -->
        <button
          type="button"
          @click="emit('toggleCommand')"
          class="px-2 py-1 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer"
          title="Open Command Center (Ctrl+K)"
        >
          <Command class="w-3 h-3" />
          <span class="hidden sm:inline">⌘K</span>
        </button>

        <!-- Help shortcuts -->
        <button
          type="button"
          @click="emit('toggleHelp')"
          class="p-1 rounded-lg text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          title="Keyboard Cheat Sheet (?)"
        >
          <HelpCircle class="w-3.5 h-3.5" />
        </button>

        <!-- Minimize dock -->
        <button
          type="button"
          @click="isMinimized = true"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          title="Minimize shortcut dock"
        >
          <ChevronDown class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>
