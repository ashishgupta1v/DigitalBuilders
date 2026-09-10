<script setup lang="ts">
import { reactive, ref } from 'vue'
import {
  X,
  Lock,
  KeyRound,
  ShieldCheck,
  Eye,
  EyeOff,
  HelpCircle,
  CheckCircle2,
  AlertCircle
} from 'lucide-vue-next'

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success', message: string): void
}>()

const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
  security_question: 'What is your primary software architecture focus?',
  custom_question: '',
  security_answer: '',
  processing: false,
  error: '',
  success: '',
})

const questionOptions = [
  'What is your primary software architecture focus?',
  'What was the name of your first software venture / product?',
  'What city was your company originally founded in?',
  'What is your private master recovery phrase?',
  'Custom Question...',
]

const effectiveQuestion = () => {
  if (form.security_question === 'Custom Question...') {
    return form.custom_question.trim()
  }
  return form.security_question
}

const handleSubmit = async () => {
  if (form.password.length < 8) {
    form.error = 'New password must be at least 8 characters.'
    return
  }
  if (form.password !== form.password_confirmation) {
    form.error = 'New password and confirmation do not match.'
    return
  }

  form.processing = true
  form.error = ''
  form.success = ''

  try {
    const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
    const res = await fetch('/crm/profile/password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        current_password: form.current_password,
        password: form.password,
        password_confirmation: form.password_confirmation,
        security_question: effectiveQuestion(),
        security_answer: form.security_answer.trim() || undefined,
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      if (data.errors) {
        const firstKey = Object.keys(data.errors)[0]
        form.error = data.errors[firstKey][0]
      } else {
        form.error = data.message || 'Failed to update password.'
      }
      return
    }

    form.success = data.message || 'Password updated successfully!'
    emit('success', form.success)
    setTimeout(() => {
      emit('close')
    }, 1000)
  } catch (err) {
    form.error = 'Network error while updating credentials.'
  } finally {
    form.processing = false
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm animate-in fade-in duration-200">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-6 relative overflow-hidden">
      <!-- Glow accent -->
      <div class="absolute -top-20 -right-20 w-40 h-40 bg-purple-500/10 rounded-full blur-2xl pointer-events-none" />

      <!-- Header -->
      <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 mb-5">
        <div class="flex items-center gap-2.5">
          <div class="p-2 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-600 dark:text-purple-400">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Security & Password Settings</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update founder credentials & recovery hint</p>
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

      <!-- Error / Success Banners -->
      <div v-if="form.error" class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
        <AlertCircle class="w-4 h-4 flex-shrink-0" />
        <span>{{ form.error }}</span>
      </div>
      <div v-if="form.success" class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 flex-shrink-0" />
        <span>{{ form.success }}</span>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-3.5">
        <!-- Current Password -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
            Current Password
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
              <Lock class="w-4 h-4" />
            </div>
            <input
              v-model="form.current_password"
              :type="showCurrent ? 'text' : 'password'"
              required
              placeholder="Enter current password"
              class="w-full pl-9 pr-10 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 transition"
            />
            <button
              type="button"
              @click="showCurrent = !showCurrent"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
              tabindex="-1"
            >
              <EyeOff v-if="showCurrent" class="w-3.5 h-3.5" />
              <Eye v-else class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- New Password & Confirm -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              New Password
            </label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showNew ? 'text' : 'password'"
                required
                minlength="8"
                placeholder="Min 8 chars"
                class="w-full px-3 pr-9 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 transition"
              />
              <button
                type="button"
                @click="showNew = !showNew"
                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                tabindex="-1"
              >
                <EyeOff v-if="showNew" class="w-3.5 h-3.5" />
                <Eye v-else class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Confirm
            </label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirm ? 'text' : 'password'"
                required
                minlength="8"
                placeholder="Confirm new"
                class="w-full px-3 pr-9 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 transition"
              />
              <button
                type="button"
                @click="showConfirm = !showConfirm"
                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                tabindex="-1"
              >
                <EyeOff v-if="showConfirm" class="w-3.5 h-3.5" />
                <Eye v-else class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Security Question Update (Optional) -->
        <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1 flex items-center gap-1.5">
            <HelpCircle class="w-3.5 h-3.5 text-purple-500" />
            Update Recovery Hint Question (Optional)
          </label>
          <select
            v-model="form.security_question"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 transition mb-2"
          >
            <option v-for="q in questionOptions" :key="q" :value="q">
              {{ q }}
            </option>
          </select>
          <input
            v-if="form.security_question === 'Custom Question...'"
            v-model="form.custom_question"
            type="text"
            placeholder="Type your own security question..."
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 transition mb-2"
          />
          <input
            v-model="form.security_answer"
            type="text"
            placeholder="New secret answer (leave blank to keep existing)"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 transition"
          />
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-2 pt-3">
          <button
            type="button"
            @click="emit('close')"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-500/20 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60"
          >
            <span v-if="form.processing">Updating...</span>
            <span v-else>Save Credentials</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
