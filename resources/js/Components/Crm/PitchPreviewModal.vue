<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { X, Copy, Check, ExternalLink, ArrowRight, Sparkles, Mail, MessageSquare, Layers, CheckCircle2 } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  requirement: any | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'convert', req: any): void
}>()

const activePitchTab = ref<'upwork' | 'email' | 'linkedin' | 'tech'>('upwork')

const upworkText = ref('')
const emailSubject = ref('')
const emailBody = ref('')
const linkedinText = ref('')
const copied = ref(false)

watch(
  () => props.requirement,
  (newReq) => {
    if (newReq) {
      upworkText.value = newReq.upwork_proposal || newReq.pitch_draft || ''
      emailSubject.value = newReq.email_subject || `Technical Architecture Proposal for ${newReq.contact_company || 'Your Project'}`
      emailBody.value = newReq.email_pitch || newReq.pitch_draft || ''
      linkedinText.value = newReq.linkedin_dm || `Hi ${newReq.contact_name || 'there'}, saw your project regarding ${newReq.title}. I'm Ashish, lead architect at DigitalBuilders. We build scalable SaaS MVPs in 4-6 weeks with 100% code ownership. Let's connect: https://www.digitalbuilders.in/book`
      copied.value = false
      activePitchTab.value = 'upwork'
    }
  },
  { immediate: true }
)

const activeContentToCopy = computed(() => {
  if (activePitchTab.value === 'upwork') return upworkText.value
  if (activePitchTab.value === 'email') return `Subject: ${emailSubject.value}\n\n${emailBody.value}`
  if (activePitchTab.value === 'linkedin') return linkedinText.value
  if (activePitchTab.value === 'tech') {
    const stack = (props.requirement?.detected_tech_stack || []).join(', ')
    const pain = (props.requirement?.client_pain_points || []).join('\n• ')
    const arch = props.requirement?.suggested_architecture || ''
    return `Detected Tech Stack: ${stack}\n\nKey Pain Points:\n• ${pain}\n\nSuggested Architecture:\n${arch}`
  }
  return upworkText.value
})

const copyToClipboard = async () => {
  if (!activeContentToCopy.value) return
  await navigator.clipboard.writeText(activeContentToCopy.value)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2500)
}

const onConvert = () => {
  if (props.requirement) {
    emit('convert', props.requirement)  // pass full req object for ConvertToDealModal
    emit('close')
  }
}
</script>

<template>
  <div v-if="show && requirement" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm animate-in fade-in duration-200">
    <div class="relative w-full max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-1">
              {{ requirement.title }}
            </h3>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              <span class="uppercase font-bold text-purple-600 dark:text-purple-400">{{ requirement.source }}</span>
              <span>•</span>
              <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ requirement.budget }}</span>
              <span>•</span>
              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-500/10 text-sky-600 dark:text-cyan-400">
                {{ requirement.relevance_score }}/100 Match
              </span>
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

      <!-- Outreach Channel Tabs -->
      <div class="flex items-center gap-1 px-5 pt-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/40">
        <button
          type="button"
          @click="activePitchTab = 'upwork'"
          class="px-3 py-2 text-xs font-bold border-b-2 transition flex items-center gap-1.5 cursor-pointer"
          :class="activePitchTab === 'upwork'
            ? 'border-purple-600 text-purple-600 dark:text-purple-400'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Sparkles class="w-3.5 h-3.5" />
          <span>Upwork Proposal</span>
        </button>
        <button
          type="button"
          @click="activePitchTab = 'email'"
          class="px-3 py-2 text-xs font-bold border-b-2 transition flex items-center gap-1.5 cursor-pointer"
          :class="activePitchTab === 'email'
            ? 'border-sky-600 text-sky-600 dark:text-sky-400'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Mail class="w-3.5 h-3.5" />
          <span>Cold Email</span>
        </button>
        <button
          type="button"
          @click="activePitchTab = 'linkedin'"
          class="px-3 py-2 text-xs font-bold border-b-2 transition flex items-center gap-1.5 cursor-pointer"
          :class="activePitchTab === 'linkedin'
            ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <MessageSquare class="w-3.5 h-3.5" />
          <span>LinkedIn / X Note</span>
        </button>
        <button
          type="button"
          @click="activePitchTab = 'tech'"
          class="px-3 py-2 text-xs font-bold border-b-2 transition flex items-center gap-1.5 cursor-pointer"
          :class="activePitchTab === 'tech'
            ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Layers class="w-3.5 h-3.5" />
          <span>Architecture & Scope</span>
        </button>
      </div>

      <!-- Body -->
      <div class="p-5 overflow-y-auto space-y-4">
        <!-- Upwork Proposal Tab -->
        <div v-if="activePitchTab === 'upwork'" class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
              Tailored Upwork Proposal Draft (Ready to Paste)
            </label>
            <span class="text-[11px] text-slate-400">Under 160 words • Dual CTA included</span>
          </div>
          <textarea
            v-model="upworkText"
            rows="10"
            class="w-full p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-sans text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-purple-500 leading-relaxed"
          ></textarea>
        </div>

        <!-- Cold Email Tab -->
        <div v-if="activePitchTab === 'email'" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Email Subject Line
            </label>
            <input
              v-model="emailSubject"
              type="text"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-sky-500"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Email Body
            </label>
            <textarea
              v-model="emailBody"
              rows="9"
              class="w-full p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-sans text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-sky-500 leading-relaxed"
            ></textarea>
          </div>
        </div>

        <!-- LinkedIn Note Tab -->
        <div v-if="activePitchTab === 'linkedin'" class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
              Personalized Direct Note (LinkedIn / X DM)
            </label>
            <span class="text-[11px] text-slate-400 font-mono">{{ linkedinText.length }}/300 chars</span>
          </div>
          <textarea
            v-model="linkedinText"
            rows="5"
            class="w-full p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-sans text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 leading-relaxed"
          ></textarea>
        </div>

        <!-- Architecture & Scope Tab -->
        <div v-if="activePitchTab === 'tech'" class="space-y-3.5">
          <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Detected Tech Stack</span>
            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="t in (requirement.detected_tech_stack?.length ? requirement.detected_tech_stack : ['Vue 3 / React', 'Laravel / Node', 'PostgreSQL', 'Tailwind'])"
                :key="t"
                class="px-2 py-0.5 rounded-lg bg-sky-500/10 text-sky-600 dark:text-cyan-400 border border-sky-500/20 text-xs font-semibold"
              >
                {{ t }}
              </span>
            </div>
          </div>

          <div v-if="requirement.client_pain_points?.length" class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Client Pain Points</span>
            <ul class="space-y-1 text-xs text-slate-700 dark:text-slate-300">
              <li v-for="(p, i) in requirement.client_pain_points" :key="i" class="flex items-start gap-1.5">
                <span class="text-rose-500 font-bold">•</span>
                <span>{{ p }}</span>
              </li>
            </ul>
          </div>

          <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Recommended Solution Architecture</span>
            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
              {{ requirement.suggested_architecture || 'Modular full-stack architecture with Vue 3 / Next.js reactive frontend, robust Laravel / Node REST API, and PostgreSQL.' }}
            </p>
          </div>

          <!-- Raw Scope Snippet -->
          <div class="p-3 rounded-xl bg-slate-100/70 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Original Requirement Text</span>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-4">
              {{ requirement.raw_text }}
            </p>
          </div>
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
            <span>Open Source Link</span>
          </a>
          <button
            type="button"
            @click="onConvert"
            class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer border border-indigo-500/20"
          >
            <ArrowRight class="w-3.5 h-3.5" />
            <span>Convert to USD Pipeline Deal</span>
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
              : 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white'"
          >
            <Check v-if="copied" class="w-3.5 h-3.5" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copied ? 'Copied to Clipboard!' : 'Copy Active Tab' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
