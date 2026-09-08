<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { X, Upload, FileText, Download, CheckCircle2 } from 'lucide-vue-next'

const props = defineProps<{
  show: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'imported'): void
}>()

const form = useForm({
  file: null as File | null,
  segment: 'manufacturer',
})

const onFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.file = target.files[0]
  }
}

const submit = () => {
  if (!form.file) return
  form.post('/crm/leads/import', {
    onSuccess: () => {
      form.reset()
      emit('imported')
      emit('close')
    },
  })
}

const downloadSample = () => {
  const sample = "Lead Name,Company,Phone,Email,Estimated Scope\nRajesh Garg,Garg Packaging Mills,+91 98765 43210,rajesh@gargpackaging.com,379000\nHarpreet Singh,SS Knitwear Mills,+91 98141 87654,harpreet@ssknitwear.in,249000\nMarcus Vance,FitPulse AI Labs,+1 415 890 1234,marcus@fitpulse.ai,11000\n"
  const blob = new Blob([sample], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = 'digitalbuilders_crm_leads_template.csv'
  link.click()
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-md">
    <div class="crm-cockpit w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="p-2 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
            <Upload class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Import Prospect CSV</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Bulk upload prospect lists into sales pipeline</p>
          </div>
        </div>

        <button @click="emit('close')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Body -->
      <form @submit.prevent="submit" class="p-6 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
            Target Prospect Segment
          </label>
          <select
            v-model="form.segment"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/50"
          >
            <option value="manufacturer">Industrial Manufacturers (Ludhiana / NCR)</option>
            <option value="retail">Retail Chains & D2C</option>
            <option value="clinic">Clinics & Diagnostic Labs</option>
            <option value="coaching">Coaching Institutes & Academies</option>
            <option value="international">International Startups (USD)</option>
          </select>
        </div>

        <!-- File Upload Drag Zone -->
        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Select CSV File</label>
          <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-purple-500/50 dark:hover:border-purple-500/50 rounded-xl p-5 text-center bg-slate-50 dark:bg-slate-950/40 transition">
            <input
              type="file"
              accept=".csv,text/csv,text/plain"
              @change="onFileChange"
              required
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />
            <div class="flex flex-col items-center">
              <FileText class="w-8 h-8 text-slate-400 dark:text-slate-500 mb-2" />
              <div v-if="form.file" class="text-xs font-semibold text-purple-600 dark:text-purple-300 flex items-center gap-1">
                <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                {{ form.file.name }}
              </div>
              <div v-else>
                <p class="text-xs font-medium text-slate-700 dark:text-slate-300">Click or drag CSV here to upload</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-500 mt-1">Columns: Name, Company, Phone, Email, Amount</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sample Download Link -->
        <div class="flex items-center justify-between pt-1">
          <button
            type="button"
            @click="downloadSample"
            class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-500 dark:hover:text-purple-300 flex items-center gap-1 transition cursor-pointer"
          >
            <Download class="w-3.5 h-3.5" />
            <span>Download Sample CSV</span>
          </button>
        </div>

        <!-- Footer Actions -->
        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing || !form.file"
            class="px-5 py-2 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-400 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-purple-500/20 transition cursor-pointer disabled:opacity-50"
          >
            {{ form.processing ? 'Importing...' : 'Upload & Populate CRM' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
