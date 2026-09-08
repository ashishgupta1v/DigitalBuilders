<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { MessageSquare, X, Send, Sparkles, Copy, Check, Mail, ExternalLink, ShieldAlert, ArrowRight } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  lead: any
  initialTouch?: number
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'touchLogged', result: any): void
}>()

const channel = ref<'whatsapp' | 'email'>('whatsapp')
const mode = ref<'touchpoint' | 'objection'>('touchpoint')
const selectedTouch = ref(props.initialTouch || 1)
const selectedObjection = ref('price')
const scriptText = ref('')
const subjectText = ref('')
const loading = ref(false)
const copied = ref(false)
const useAi = ref(false)
const sending = ref(false)

const touchpoints = [
  { num: 1, label: 'Touch 1: Problem Hook + Proof', day: 'Day 0' },
  { num: 2, label: 'Touch 2: 2026 Price Book & Estimator', day: 'Day 2' },
  { num: 3, label: 'Touch 3: Architecture Case Study', day: 'Day 5' },
  { num: 4, label: 'Touch 4: Priority & Bottleneck Check', day: 'Day 9' },
  { num: 5, label: 'Touch 5: Polite Breakup Note', day: 'Day 14' },
]

const objections = [
  { key: 'price', label: '💰 Price Too High ("₹25k other quote")' },
  { key: 'inhouse', label: '🛠️ Internal Dev ("We have our own guy")' },
  { key: 'proposal', label: '📄 Send Proposal First ("Email quote")' },
  { key: 'think', label: '🤔 Need to Think About It' },
  { key: 'details', label: '💬 Send All Details on WhatsApp' },
]

// Real-time sanitized phone number
const cleanPhone = computed(() => {
  if (!props.lead?.phone) return ''
  const digits = props.lead.phone.replace(/[^0-9]/g, '')
  // If Indian 10 digits without country code, prefix 91
  if (digits.length === 10) return `91${digits}`
  return digits
})

// Dynamic WhatsApp Web deep-link that reactively binds to the current textarea script text
const currentWhatsAppUrl = computed(() => {
  if (!cleanPhone.value) return ''
  return `https://wa.me/${cleanPhone.value}?text=${encodeURIComponent(scriptText.value)}`
})

// Mailto URL for email outreach
const mailtoUrl = computed(() => {
  if (!props.lead?.email) return ''
  return `mailto:${props.lead.email}?subject=${encodeURIComponent(subjectText.value)}&body=${encodeURIComponent(scriptText.value)}`
})

const fetchScript = async () => {
  if (!props.lead?.id) return
  loading.value = true
  try {
    const res = await fetch('/crm/ai/script', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        lead_id: props.lead.id,
        type: mode.value,
        touch: selectedTouch.value,
        objection: selectedObjection.value,
        use_ai: useAi.value,
      }),
    })
    const data = await res.json()
    if (data.success) {
      scriptText.value = data.script
      subjectText.value = data.subject || ''
    }
  } catch (err) {
    console.error('Failed to generate script', err)
  } finally {
    loading.value = false
  }
}

watch(
  () => [props.show, props.lead, mode.value, selectedTouch.value, selectedObjection.value, useAi.value],
  () => {
    if (props.show && props.lead?.id) {
      fetchScript()
    }
  },
  { immediate: true }
)

const copyScript = () => {
  const contentToCopy = channel.value === 'email' && subjectText.value
    ? `Subject: ${subjectText.value}\n\n${scriptText.value}`
    : scriptText.value
  navigator.clipboard.writeText(contentToCopy)
  copied.value = true
  setTimeout(() => (copied.value = false), 2200)
}

const dispatchOutreach = async () => {
  sending.value = true

  // 1. Trigger client application
  if (channel.value === 'whatsapp' && currentWhatsAppUrl.value) {
    window.open(currentWhatsAppUrl.value, '_blank')
  } else if (channel.value === 'email' && mailtoUrl.value) {
    window.location.href = mailtoUrl.value
  }

  // 2. Automatically log the touchpoint and advance cadence in CRM
  try {
    const res = await fetch('/crm/activities/touchpoint', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({
        lead_id: props.lead.id,
        channel: channel.value,
        touch_number: mode.value === 'touchpoint' ? selectedTouch.value : (props.lead.touchpoint_count || 1),
        message: scriptText.value,
        subject: subjectText.value,
      }),
    })
    const data = await res.json()
    if (data.success) {
      emit('touchLogged', {
        ...data,
        channel: channel.value,
        leadName: props.lead.name,
      })
      emit('close')
    }
  } catch (e) {
    console.error('Error auto-logging touchpoint', e)
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 dark:bg-black/80 backdrop-blur-md">
    <div class="crm-cockpit w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
      <!-- Modal Header -->
      <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/70">
        <div class="flex items-center gap-3 min-w-0">
          <div
            class="p-2 sm:p-2.5 rounded-xl border flex items-center justify-center transition-colors shrink-0"
            :class="channel === 'whatsapp' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'"
          >
            <component :is="channel === 'whatsapp' ? MessageSquare : Mail" class="w-5 h-5" />
          </div>
          <div class="min-w-0">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
              <span>Outreach & 5-Touch Cadence Engine</span>
              <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-semibold border border-cyan-500/20 truncate max-w-[140px]">
                {{ lead?.name }}
              </span>
            </h3>
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate">
              {{ lead?.company || 'Direct Prospect' }} • {{ lead?.phone || lead?.email || 'No Direct Contact' }}
            </p>
          </div>
        </div>

        <button @click="emit('close')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer shrink-0">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Channel & Mode Switcher Bar -->
      <div class="px-4 sm:px-6 pt-3.5 bg-slate-50/50 dark:bg-slate-950/40 border-b border-slate-200 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
        <!-- Sequence vs Battlecard Tabs -->
        <div class="flex gap-2 text-xs font-semibold uppercase tracking-wider">
          <button
            type="button"
            @click="mode = 'touchpoint'"
            class="pb-3 px-3 transition border-b-2 cursor-pointer"
            :class="mode === 'touchpoint' ? 'border-cyan-500 dark:border-cyan-400 text-cyan-600 dark:text-cyan-300 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
          >
            5-Touch Cadence
          </button>
          <button
            type="button"
            @click="mode = 'objection'"
            class="pb-3 px-3 transition border-b-2 cursor-pointer"
            :class="mode === 'objection' ? 'border-purple-500 dark:border-purple-400 text-purple-600 dark:text-purple-300 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
          >
            Objection Battlecards
          </button>
        </div>

        <!-- Channel Selector (WhatsApp vs Email) -->
        <div class="flex rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-0.5 text-xs font-bold mb-2">
          <button
            type="button"
            @click="channel = 'whatsapp'"
            class="px-3 py-1 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
            :class="channel === 'whatsapp' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
          >
            <MessageSquare class="w-3.5 h-3.5" />
            <span>WhatsApp</span>
          </button>
          <button
            type="button"
            @click="channel = 'email'"
            class="px-3 py-1 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
            :class="channel === 'email' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
          >
            <Mail class="w-3.5 h-3.5" />
            <span>Email</span>
          </button>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
        <!-- Touchpoint Pills -->
        <div v-if="mode === 'touchpoint'">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
            Select Cadence Milestone:
          </label>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <button
              v-for="t in touchpoints"
              :key="t.num"
              type="button"
              @click="selectedTouch = t.num"
              class="p-2.5 rounded-xl border text-left transition cursor-pointer text-xs"
              :class="selectedTouch === t.num ? 'bg-cyan-50 dark:bg-cyan-500/10 border-cyan-400 dark:border-cyan-500/40 text-cyan-800 dark:text-cyan-200 font-semibold shadow-sm' : 'bg-slate-50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'"
            >
              <div class="flex items-center justify-between text-[10px] text-slate-500 mb-0.5">
                <span class="font-bold">{{ t.day }}</span>
                <span v-if="lead?.touchpoint_count >= t.num" class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-0.5">
                  <Check class="w-3 h-3" /> Sent
                </span>
              </div>
              <div class="line-clamp-1 text-slate-800 dark:text-slate-200">{{ t.label }}</div>
            </button>
          </div>
        </div>

        <!-- Objection Battlecard Pills -->
        <div v-else>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
            Select Client Objection to Counter:
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button
              v-for="obj in objections"
              :key="obj.key"
              type="button"
              @click="selectedObjection = obj.key"
              class="p-2.5 rounded-xl border text-left transition cursor-pointer text-xs"
              :class="selectedObjection === obj.key ? 'bg-purple-50 dark:bg-purple-500/10 border-purple-400 dark:border-purple-500/40 text-purple-800 dark:text-purple-200 font-semibold shadow-sm' : 'bg-slate-50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'"
            >
              <div class="font-semibold text-slate-800 dark:text-slate-200">{{ obj.label }}</div>
            </button>
          </div>
        </div>

        <!-- Subject Line Field (Visible in Email mode) -->
        <div v-if="channel === 'email'">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
            Subject Line:
          </label>
          <input
            v-model="subjectText"
            type="text"
            placeholder="Subject line..."
            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/90 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium"
          />
        </div>

        <!-- Script Preview & Live Editor -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
              <span>Personalized Pitch Copy</span>
              <span class="text-[10px] text-slate-400 dark:text-slate-500 normal-case font-normal hidden sm:inline">(Editable — live syncs to outreach URL)</span>
            </label>

            <div class="flex items-center gap-2 sm:gap-3">
              <label class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                <input type="checkbox" v-model="useAi" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-purple-600" />
                <span class="flex items-center gap-1 text-[11px] text-purple-600 dark:text-purple-300 font-medium">
                  <Sparkles class="w-3 h-3 text-purple-600 dark:text-purple-400" /> AI Polish
                </span>
              </label>

              <button
                type="button"
                @click="copyScript"
                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs flex items-center gap-1.5 transition cursor-pointer"
              >
                <component :is="copied ? Check : Copy" class="w-3.5 h-3.5" :class="copied ? 'text-emerald-600 dark:text-emerald-400' : ''" />
                <span :class="copied ? 'text-emerald-600 dark:text-emerald-400 font-bold' : ''">{{ copied ? 'Copied!' : 'Copy' }}</span>
              </button>
            </div>
          </div>

          <div class="relative">
            <textarea
              v-model="scriptText"
              rows="6"
              placeholder="Generating contextual pitch..."
              class="w-full p-3.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 text-xs font-mono leading-relaxed focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition resize-y custom-scrollbar"
            ></textarea>
            <div v-if="loading" class="absolute inset-0 bg-white/80 dark:bg-slate-950/70 backdrop-blur-xs rounded-xl flex items-center justify-center text-xs text-cyan-700 dark:text-cyan-300 font-medium">
              Generating tailored pitch with playbook...
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer Action -->
      <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 truncate">
          <span class="font-medium shrink-0">Target:</span>
          <span class="font-mono text-slate-800 dark:text-slate-200 font-semibold truncate">
            {{ channel === 'whatsapp' ? (lead?.phone || 'No phone') : (lead?.email || 'No email') }}
          </span>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
          <button
            type="button"
            @click="emit('close')"
            class="flex-1 sm:flex-initial px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer text-center"
          >
            Cancel
          </button>

          <!-- WhatsApp Dispatch Button -->
          <button
            v-if="channel === 'whatsapp'"
            type="button"
            @click="dispatchOutreach"
            :disabled="loading || !cleanPhone || sending"
            class="flex-1 sm:flex-initial px-4 sm:px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-50"
          >
            <Send class="w-3.5 h-3.5" />
            <span>{{ sending ? 'Opening WhatsApp...' : '1-Click Send on WhatsApp' }}</span>
          </button>

          <!-- Email Dispatch Button -->
          <button
            v-else
            type="button"
            @click="dispatchOutreach"
            :disabled="loading || !lead?.email || sending"
            class="flex-1 sm:flex-initial px-4 sm:px-5 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-50"
          >
            <Mail class="w-3.5 h-3.5" />
            <span>{{ sending ? 'Opening Email...' : '1-Click Send via Email' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
