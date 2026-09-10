<script setup lang="ts">
import { ref } from 'vue'
import { X, Sparkles, Send, Globe, DollarSign, Building2, User, Check, Copy } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'ingested'): void
}>()

const title = ref('')
const source = ref('upwork')
const contactName = ref('')
const contactCompany = ref('')
const budgetRaw = ref('$5,000 – $10,000')
const currency = ref<'USD' | 'INR'>('USD')
const rawText = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')
const generatedResult = ref<{
  pitch: string
  score: number
  segment: string
} | null>(null)
const copied = ref(false)

const submit = async () => {
  if (!title.value.trim() || !rawText.value.trim()) {
    errorMessage.value = 'Please provide both a Title and Requirement description.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const res = await fetch('/crm/market/ingest-custom', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        title: title.value.trim(),
        source: source.value,
        contact_name: contactName.value.trim() || null,
        contact_company: contactCompany.value.trim() || null,
        budget_raw: budgetRaw.value.trim() || null,
        currency: currency.value,
        raw_text: rawText.value.trim(),
      }),
    })

    const data = await res.json()
    if (!res.ok || !data.success) {
      throw new Error(data.message || 'Failed to ingest RFP.')
    }

    generatedResult.value = {
      pitch: data.pitch || '',
      score: data.score || 85,
      segment: data.segment || 'saas_ai',
    }
    emit('ingested')
  } catch (err: any) {
    errorMessage.value = err.message || 'Network error occurred while ingesting.'
  } finally {
    isSubmitting.value = false
  }
}

const copyPitch = async () => {
  if (!generatedResult.value?.pitch) return
  await navigator.clipboard.writeText(generatedResult.value.pitch)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2500)
}

const resetAndClose = () => {
  title.value = ''
  rawText.value = ''
  contactName.value = ''
  contactCompany.value = ''
  generatedResult.value = null
  errorMessage.value = ''
  emit('close')
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200">
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
              {{ generatedResult ? 'AI Pitch Generated & Alert Dispatched' : 'Ingest Upwork / Custom RFP' }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ generatedResult ? 'Proposal ready to copy + sent to your Telegram bot.' : 'Paste any job posting or DM to generate an instant high-converting pitch.' }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="resetAndClose"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Result View -->
      <div v-if="generatedResult" class="p-5 overflow-y-auto space-y-4">
        <div class="flex items-center justify-between p-3 rounded-xl bg-purple-500/10 border border-purple-500/20">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-purple-700 dark:text-purple-300">Match Score: {{ generatedResult.score }}/100</span>
            <span class="text-xs text-slate-400">•</span>
            <span class="text-xs font-semibold uppercase text-slate-600 dark:text-slate-300">{{ generatedResult.segment }}</span>
          </div>
          <button
            type="button"
            @click="copyPitch"
            class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
          >
            <Check v-if="copied" class="w-3.5 h-3.5" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copied ? 'Copied to Clipboard!' : 'Copy Proposal' }}</span>
          </button>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Generated Pitch Draft</label>
          <textarea
            readonly
            rows="9"
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-800 dark:text-slate-200 focus:outline-none leading-relaxed"
            :value="generatedResult.pitch"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            @click="resetAndClose"
            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>

      <!-- Ingest Form -->
      <form v-else @submit.prevent="submit" class="p-5 overflow-y-auto space-y-4">
        <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs">
          {{ errorMessage }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Job / Opportunity Title *</label>
            <input
              v-model="title"
              type="text"
              required
              placeholder="e.g. Build MVP SaaS Platform (Laravel + Vue 3)"
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Source Channel</label>
            <select
              v-model="source"
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500"
            >
              <option value="upwork">🟢 Upwork</option>
              <option value="linkedin">💼 LinkedIn</option>
              <option value="reddit">🔴 Reddit</option>
              <option value="direct">⚡ Direct / Referral</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Client / Poster Name</label>
            <input
              v-model="contactName"
              type="text"
              placeholder="e.g. David (optional)"
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Company / Domain</label>
            <input
              v-model="contactCompany"
              type="text"
              placeholder="e.g. FinTech Startup (optional)"
              class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Budget Band & Currency</label>
            <div class="flex items-center gap-1.5">
              <input
                v-model="budgetRaw"
                type="text"
                placeholder="$5,000 or $65/hr"
                class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500"
              />
              <select
                v-model="currency"
                class="w-20 px-2 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500"
              >
                <option value="USD">$ USD</option>
                <option value="INR">₹ INR</option>
              </select>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Raw Job Description / Requirements *</label>
          <textarea
            v-model="rawText"
            rows="5"
            required
            placeholder="Paste the full job post description, client requirements, or Figma details here..."
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-purple-500 leading-relaxed"
          ></textarea>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
          <span class="text-[11px] text-slate-400">
            Will score relevance, create custom pitch, and notify Telegram bot.
          </span>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="resetAndClose"
              class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-500/20 flex items-center gap-1.5 transition cursor-pointer disabled:opacity-50"
            >
              <Sparkles v-if="!isSubmitting" class="w-3.5 h-3.5" />
              <span v-if="isSubmitting">Generating Pitch...</span>
              <span v-else>Generate Pitch & Ingest</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
