<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { X, Plus, Sparkles, Building2, User, Phone, Mail, DollarSign, Layers } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
  defaultStage?: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'created'): void
}>()

const form = useForm({
  name: '',
  company: '',
  role_title: '',
  phone: '',
  email: '',
  segment: 'manufacturer',
  stage: 'new',
  project_type: '',
  deal_amount: null as number | null,
  currency: 'INR',
  pricing_tier: 'growth',
  description: '',
  score: 60,
})

watch(
  () => props.defaultStage,
  (newStage) => {
    if (newStage) {
      form.stage = newStage
    }
  },
  { immediate: true }
)

const onPhoneBlur = () => {
  let p = form.phone.trim().replace(/[^0-9+]/g, '')
  if (p.length === 10 && !p.startsWith('+')) {
    form.phone = `+91 ${p.substring(0, 5)} ${p.substring(5)}`
  }
}

const onSegmentChange = () => {
  if (form.segment === 'international') {
    form.currency = 'USD'
    if (!form.project_type) form.project_type = 'High-Throughput Web App'
  } else {
    form.currency = 'INR'
    if (!form.project_type) {
      if (form.segment === 'manufacturer') form.project_type = 'Custom ERP & Mobile Ordering App'
      else if (form.segment === 'retail') form.project_type = 'E-Commerce Store & Catalog'
      else if (form.segment === 'clinic') form.project_type = 'Clinic Booking & Reminder Portal'
      else if (form.segment === 'coaching') form.project_type = 'Attendance & Fee Management App'
      else if (form.segment === 'startup') form.project_type = 'Full-Stack MVP Platform'
    }
  }
}

const submit = () => {
  form.post('/crm/leads', {
    onSuccess: () => {
      form.reset()
      emit('created')
      emit('close')
    },
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-md">
    <div class="crm-cockpit w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
      <!-- Header -->
      <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/70">
        <div class="flex items-center gap-3">
          <div class="p-2 sm:p-2.5 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shrink-0">
            <Plus class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Quick Ingest Lead & Deal</h3>
              <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-cyan-700 dark:text-cyan-300 font-mono font-bold border border-slate-200 dark:border-slate-700">
                Stage: {{ (form.stage || 'new').replace('_', ' ') }}
              </span>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Add prospect directly into high-ticket pipeline</p>
          </div>
        </div>

        <button @click="emit('close')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="submit" class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
        <!-- Target Segment Selection -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
            Target Industry / Niche
          </label>
          <select
            v-model="form.segment"
            @change="onSegmentChange"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
          >
            <option value="manufacturer">Industrial Manufacturer / Factory (₹2.5L–₹6L)</option>
            <option value="retail">Retail Store / D2C Brand (₹1L–₹2.8L)</option>
            <option value="clinic">Clinic / Diagnostic Lab / Telehealth (₹1L–₹2.8L)</option>
            <option value="coaching">Coaching Institute / Academy (₹1.2L–₹3.3L)</option>
            <option value="startup">Tech Startup / MVP Build (₹1.5L–₹3L)</option>
            <option value="international">International Founder / US SME ($3.5k–$26k USD)</option>
            <option value="general">General Web / Software Build</option>
          </select>
        </div>

        <!-- Name & Company -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Prospect Name *</label>
            <input
              v-model="form.name"
              required
              placeholder="e.g. Rajesh Garg"
              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Company / Unit</label>
            <input
              v-model="form.company"
              placeholder="e.g. Garg Packaging Mills"
              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
            />
          </div>
        </div>

        <!-- Phone / WhatsApp & Email -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Phone / WhatsApp *</label>
            <input
              v-model="form.phone"
              @blur="onPhoneBlur"
              required
              placeholder="+91 98765 43210"
              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email (Optional)</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="founder@company.com"
              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
            />
          </div>
        </div>

        <!-- Target Scope & Deal Value -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Project Scope Title</label>
            <input
              v-model="form.project_type"
              placeholder="e.g. B2B Order App & ERP"
              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
              Target ({{ form.currency }})
            </label>
            <input
              v-model="form.deal_amount"
              type="number"
              min="0"
              placeholder="e.g. 150000"
              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
            />
          </div>
        </div>

        <!-- Notes / Pain Points -->
        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bottlenecks & Context Notes</label>
          <textarea
            v-model="form.description"
            rows="2"
            placeholder="e.g. Orders taken on unstructured WhatsApp notes; high dispatch mistakes..."
            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/50 placeholder-slate-400"
          ></textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer text-center"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-xs font-bold shadow-lg shadow-cyan-500/20 transition cursor-pointer disabled:opacity-50 text-center"
          >
            {{ form.processing ? 'Ingesting...' : 'Add to Pipeline & Queue Touch 1' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
