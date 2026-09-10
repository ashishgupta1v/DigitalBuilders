<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { X, UserPlus, DollarSign, Building2, Mail, Phone, Tag, AlertCircle, ChevronRight, Loader2 } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  req: any | null // MarketRequirement row
}>()

const emit = defineEmits<{
  close: []
  converted: [dealId: number]
}>()

// Form state
const contactName    = ref('')
const contactCompany = ref('')
const contactEmail   = ref('')
const contactPhone   = ref('')
const amount         = ref<string>('')
const stage          = ref('new')
const isSubmitting   = ref(false)
const errorMsg       = ref('')

// Pre-fill from scraped data when the req prop changes
watch(() => props.req, (newReq) => {
  if (!newReq) return
  contactName.value    = newReq.contact_name    || ''
  contactCompany.value = newReq.contact_company || ''
  contactEmail.value   = newReq.contact_email   || ''
  contactPhone.value   = newReq.contact_phone   || ''
  // Pre-fill budget from AI estimate if available (formatted_amount without symbol)
  if (newReq.budget && newReq.budget !== '—') {
    amount.value = String(newReq.budget).replace(/[^0-9.]/g, '')
  } else {
    amount.value = ''
  }
  stage.value    = 'new'
  errorMsg.value = ''
}, { immediate: true })

const stageOptions = [
  { key: 'new',           label: 'New Inbound RFP' },
  { key: 'contacted',     label: 'Outreach Sent' },
  { key: 'qualified',     label: 'Qualified ($ USD)' },
  { key: 'proposal_sent', label: 'Proposal Sent' },
]

const isNameRequired = computed(() => !contactName.value.trim() && !contactCompany.value.trim())

const getCsrfToken = () =>
  (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''

const handleConvert = async () => {
  errorMsg.value = ''

  // At minimum we need either a name or company
  if (!contactName.value.trim() && !contactCompany.value.trim()) {
    errorMsg.value = 'Please provide at least a contact name or company name.'
    return
  }

  isSubmitting.value = true
  try {
    const payload: Record<string, any> = {
      stage:           stage.value,
      contact_name:    contactName.value.trim()    || null,
      contact_company: contactCompany.value.trim() || null,
      contact_email:   contactEmail.value.trim()   || null,
      contact_phone:   contactPhone.value.trim()   || null,
      amount:          amount.value ? parseFloat(amount.value) : null,
    }

    const res = await fetch(`/crm/market/requirements/${props.req!.id}/convert`, {
      method: 'POST',
      headers: {
        'Content-Type':  'application/json',
        'Accept':        'application/json',
        'X-CSRF-TOKEN':  getCsrfToken(),
      },
      body: JSON.stringify(payload),
    })

    const data = await res.json()
    if (data.success) {
      emit('converted', data.deal_id)
    } else {
      errorMsg.value = data.message || 'Conversion failed. Please try again.'
    }
  } catch (e) {
    console.error(e)
    errorMsg.value = 'Network error — please check your connection.'
  } finally {
    isSubmitting.value = false
  }
}

const close = () => {
  if (!isSubmitting.value) emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @click.self="close"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 overflow-hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-gradient-to-r from-purple-600/5 to-indigo-600/5">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-purple-500/10">
                <UserPlus class="w-5 h-5 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">Convert RFP → CRM Deal</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Fill in contact details to create a real lead record</p>
              </div>
            </div>
            <button
              type="button"
              @click="close"
              :disabled="isSubmitting"
              class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition cursor-pointer disabled:opacity-40"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- RFP Summary Banner -->
          <div v-if="req" class="mx-6 mt-4 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200/60 dark:border-indigo-700/40">
            <p class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider mb-1">RFP Source: {{ req.source }}</p>
            <p class="text-xs text-slate-700 dark:text-slate-300 line-clamp-2 leading-relaxed">{{ req.title }}</p>
            <div class="flex items-center gap-2 mt-1.5">
              <span v-if="req.budget && req.budget !== '—'" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                Budget hint: {{ req.budget }}
              </span>
              <span v-else class="text-[11px] text-slate-400 dark:text-slate-500 italic">No budget mentioned in RFP</span>
              <span class="text-[11px] text-slate-400">· {{ req.created_at }}</span>
            </div>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleConvert" class="px-6 py-4 space-y-4">

            <!-- Alert: required fields notice -->
            <div v-if="isNameRequired" class="flex items-start gap-2 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-700/40">
              <AlertCircle class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
              <p class="text-[11px] text-amber-700 dark:text-amber-300">At minimum, provide a contact name or company name to avoid blank records.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <!-- Contact Name -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                  Contact Name
                </label>
                <div class="relative">
                  <UserPlus class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                  <input
                    v-model="contactName"
                    type="text"
                    placeholder="e.g. Sarah Johnson"
                    class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition"
                  />
                </div>
              </div>

              <!-- Company Name -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                  Company / Startup
                </label>
                <div class="relative">
                  <Building2 class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                  <input
                    v-model="contactCompany"
                    type="text"
                    placeholder="e.g. Acme Inc."
                    class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition"
                  />
                </div>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                Email Address <span class="text-slate-400 font-normal normal-case">(optional — no fake email injected if blank)</span>
              </label>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                <input
                  v-model="contactEmail"
                  type="email"
                  placeholder="contact@company.com"
                  class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <!-- Phone -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                  Phone <span class="text-slate-400 font-normal normal-case">(optional)</span>
                </label>
                <div class="relative">
                  <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                  <input
                    v-model="contactPhone"
                    type="text"
                    placeholder="+1 555 000 0000"
                    class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition"
                  />
                </div>
              </div>

              <!-- Deal Amount -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                  Deal Value (USD) <span class="text-slate-400 font-normal normal-case">(optional)</span>
                </label>
                <div class="relative">
                  <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                  <input
                    v-model="amount"
                    type="number"
                    min="0"
                    step="100"
                    placeholder="Leave blank if unknown"
                    class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition"
                  />
                </div>
              </div>
            </div>

            <!-- Pipeline Stage -->
            <div>
              <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                <Tag class="inline w-3 h-3 mr-1" /> Initial Pipeline Stage
              </label>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button
                  v-for="opt in stageOptions"
                  :key="opt.key"
                  type="button"
                  @click="stage = opt.key"
                  class="px-2 py-1.5 rounded-xl border text-[11px] font-bold transition cursor-pointer"
                  :class="stage === opt.key
                    ? 'bg-purple-600 text-white border-purple-600 shadow-md'
                    : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-purple-400 hover:text-purple-600 dark:hover:text-purple-400 bg-white dark:bg-slate-800'"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>

            <!-- Error -->
            <div v-if="errorMsg" class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200/60 dark:border-rose-700/40">
              <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
              <p class="text-xs text-rose-700 dark:text-rose-300">{{ errorMsg }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
              <button
                type="button"
                @click="close"
                :disabled="isSubmitting"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer disabled:opacity-40"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-sm font-extrabold shadow-md shadow-purple-500/25 flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-60"
              >
                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                <ChevronRight v-else class="w-4 h-4" />
                <span>{{ isSubmitting ? 'Creating Deal…' : 'Create Deal' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.97) translateY(8px);
}
</style>
