<script setup lang="ts">
import { X, Command, Sparkles, Navigation, Zap, Layers, Keyboard } from 'lucide-vue-next'

defineProps<{
  show: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const shortcutGroups = [
  {
    title: 'Navigation & Layout',
    icon: Navigation,
    shortcuts: [
      { key: 'J / ↓', desc: 'Select next lead or RFP' },
      { key: 'K / ↑', desc: 'Select previous lead or RFP' },
      { key: '[', desc: 'Toggle Split-Screen Cockpit vs Full Width' },
      { key: '1 – 5', desc: 'Jump to tabs (Hunter, Deals, Leads, Campaigns, Studio)' },
      { key: 'Esc', desc: 'Close any active modal, palette, or drawer' },
    ]
  },
  {
    title: '1-Key Power Triage',
    icon: Zap,
    shortcuts: [
      { key: 'A', desc: 'Launch / inspect 4-step outbound cadence' },
      { key: 'D', desc: 'Convert RFP / Lead directly to active Deal ($ USD)' },
      { key: 'P', desc: 'Preview and copy tailored AI Pitch (Upwork/Email/LinkedIn)' },
      { key: 'X', desc: 'Dismiss / Pass / Archive item from feed' },
      { key: 'E', desc: 'Enrich lead contact intelligence & domain dossier' },
      { key: 'Space', desc: 'Open full lead profile & activity drawer' },
    ]
  },
  {
    title: 'Command Hub & Global Actions',
    icon: Command,
    shortcuts: [
      { key: '⌘K / Ctrl+K', desc: 'Open Spotlight Command Center & universal search' },
      { key: 'N', desc: 'Quick-add new lead / prospect' },
      { key: 'S', desc: 'Scan and sync 15+ live market feeds' },
      { key: '?', desc: 'Toggle this keyboard shortcut reference HUD' },
    ]
  }
]
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 dark:bg-black/80 backdrop-blur-md transition-all duration-200"
    @click.self="emit('close')"
  >
    <div
      class="relative w-full max-w-xl bg-white dark:bg-[#0c101c] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
            <Keyboard class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
              Power-User Keyboard Shortcuts
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
              Navigate and triage deals at lightning speed without touching your mouse
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="emit('close')"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Shortcuts Grid -->
      <div class="p-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
        <div v-for="group in shortcutGroups" :key="group.title" class="space-y-2.5">
          <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <component :is="group.icon" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
            <span>{{ group.title }}</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div
              v-for="sc in group.shortcuts"
              :key="sc.key"
              class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800/80 flex items-center justify-between gap-2"
            >
              <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">
                {{ sc.desc }}
              </span>
              <kbd class="px-2 py-0.5 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-purple-300 font-mono font-bold text-[11px] shadow-sm border border-slate-200 dark:border-slate-700 shrink-0">
                {{ sc.key }}
              </kbd>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 flex items-center justify-between text-[11px] text-slate-400">
        <span>Press <kbd class="px-1 py-0.2 rounded bg-slate-200 dark:bg-slate-800 text-[10px] font-mono font-bold">Esc</kbd> anytime to dismiss</span>
        <span class="font-mono text-purple-600 dark:text-purple-400 font-semibold">CRM Cockpit v2.5</span>
      </div>
    </div>
  </div>
</template>
