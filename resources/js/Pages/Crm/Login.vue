<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useForm, Head, Link, usePage } from '@inertiajs/vue3'
import {
  ShieldCheck,
  Lock,
  Mail,
  ArrowRight,
  Eye,
  EyeOff,
  HelpCircle,
  KeyRound,
  CheckCircle2,
  AlertCircle,
  Sparkles,
  ArrowLeft,
  RotateCcw
} from 'lucide-vue-next'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const page = usePage()

const props = defineProps<{
  status?: string | null
  info?: string | null
}>()

const infoBanner = computed(() => props.info || (page.props.flash as Record<string, any>)?.info || '')

type AuthMode = 'login' | 'temp_activate' | 'recovery'

const currentMode = ref<AuthMode>('login')

// Password visibility toggles
const showLoginPassword = ref(false)
const showTempPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const showRecoveryPassword = ref(false)

// Standard Login Form - No hardcoded or exposed credentials
const loginForm = useForm({
  email: '',
  password: '',
  remember: true,
})

// First-Time / Temporary Password Activation Form
const activateForm = reactive({
  email: '',
  temp_password: '',
  password: '',
  password_confirmation: '',
  security_question: 'What is your primary software architecture focus?',
  custom_question: '',
  security_answer: '',
  processing: false,
  error: '',
  success: '',
})

// 2-Step Security Question Recovery State
const recoveryState = reactive({
  step: 1 as 1 | 2,
  email: '',
  question: '',
  security_answer: '',
  password: '',
  password_confirmation: '',
  loadingQuestion: false,
  processing: false,
  error: '',
  success: '',
})

const securityQuestionOptions = [
  'What is your primary software architecture focus?',
  'What was the name of your first software venture / product?',
  'What city was your company originally founded in?',
  'What is your private master recovery phrase?',
  'Custom Question...',
]

const effectiveSecurityQuestion = computed(() => {
  if (activateForm.security_question === 'Custom Question...') {
    return activateForm.custom_question.trim()
  }
  return activateForm.security_question
})

// Standard Login Handler
const handleLogin = () => {
  loginForm.post('/crm/login', {
    onFinish: () => {
      loginForm.password = ''
    },
    onSuccess: () => {
      // Check if flash indicated first-time temporary password
      const flash = page.props.flash as Record<string, unknown> | undefined
      if (flash?.first_time_required) {
        currentMode.value = 'temp_activate'
        activateForm.email = (flash.pending_email as string) || loginForm.email
      }
    },
  })
}

// First-Time Activation Handler
const handleFirstTimeActivation = async () => {
  if (!effectiveSecurityQuestion.value) {
    activateForm.error = 'Please select or enter a valid security recovery question.'
    return
  }
  if (!activateForm.security_answer.trim()) {
    activateForm.error = 'Please provide a secret answer for security recovery.'
    return
  }
  if (activateForm.password.length < 8) {
    activateForm.error = 'Permanent password must be at least 8 characters.'
    return
  }
  if (activateForm.password !== activateForm.password_confirmation) {
    activateForm.error = 'New password and confirmation do not match.'
    return
  }

  activateForm.processing = true
  activateForm.error = ''
  activateForm.success = ''

  try {
    const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
    const res = await fetch('/crm/password/first-time-update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        email: activateForm.email,
        temp_password: activateForm.temp_password,
        password: activateForm.password,
        password_confirmation: activateForm.password_confirmation,
        security_question: effectiveSecurityQuestion.value,
        security_answer: activateForm.security_answer.trim(),
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      if (data.errors) {
        const firstKey = Object.keys(data.errors)[0]
        activateForm.error = data.errors[firstKey][0]
      } else {
        activateForm.error = data.message || 'Failed to activate credentials.'
      }
      return
    }

    activateForm.success = data.message || 'Credentials updated successfully!'
    setTimeout(() => {
      window.location.href = data.redirect || '/crm'
    }, 600)
  } catch (err: unknown) {
    activateForm.error = 'Network error while updating credentials. Please try again.'
  } finally {
    activateForm.processing = false
  }
}

// Recovery Step 1: Look up Security Question
const fetchSecurityQuestion = async () => {
  if (!recoveryState.email || !recoveryState.email.includes('@')) {
    recoveryState.error = 'Please enter a valid administrator email.'
    return
  }

  recoveryState.loadingQuestion = true
  recoveryState.error = ''

  try {
    const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
    const res = await fetch('/crm/password/question', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ email: recoveryState.email }),
    })

    const data = await res.json()

    if (!res.ok) {
      recoveryState.error = data.message || 'No security recovery configured for this email.'
      return
    }

    recoveryState.question = data.question
    recoveryState.step = 2
  } catch (err) {
    recoveryState.error = 'Network error fetching security question.'
  } finally {
    recoveryState.loadingQuestion = false
  }
}

// Recovery Step 2: Verify Answer and Reset Password
const handleSecurityReset = async () => {
  if (!recoveryState.security_answer.trim()) {
    recoveryState.error = 'Please provide the answer to your security question.'
    return
  }
  if (recoveryState.password.length < 8) {
    recoveryState.error = 'New password must be at least 8 characters.'
    return
  }
  if (recoveryState.password !== recoveryState.password_confirmation) {
    recoveryState.error = 'New password and confirmation do not match.'
    return
  }

  recoveryState.processing = true
  recoveryState.error = ''
  recoveryState.success = ''

  try {
    const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
    const res = await fetch('/crm/password/reset-question', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        email: recoveryState.email,
        security_answer: recoveryState.security_answer.trim(),
        password: recoveryState.password,
        password_confirmation: recoveryState.password_confirmation,
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      if (data.errors) {
        const firstKey = Object.keys(data.errors)[0]
        recoveryState.error = data.errors[firstKey][0]
      } else {
        recoveryState.error = data.message || 'Invalid security answer.'
      }
      return
    }

    recoveryState.success = data.message || 'Password reset! Entering Sales Cockpit...'
    setTimeout(() => {
      window.location.href = data.redirect || '/crm'
    }, 600)
  } catch (err) {
    recoveryState.error = 'Network error while resetting password.'
  } finally {
    recoveryState.processing = false
  }
}

// Reset view states
const switchTo = (mode: AuthMode) => {
  currentMode.value = mode
  activateForm.error = ''
  activateForm.success = ''
  recoveryState.error = ''
  recoveryState.success = ''
  if (mode === 'recovery') {
    recoveryState.step = 1
    recoveryState.email = loginForm.email || activateForm.email
  }
  if (mode === 'temp_activate') {
    activateForm.email = loginForm.email || recoveryState.email
  }
}

onMounted(() => {
  const flash = page.props.flash as Record<string, unknown> | undefined
  if (flash?.first_time_required) {
    currentMode.value = 'temp_activate'
    activateForm.email = (flash.pending_email as string) || ''
  }
})
</script>

<template>
  <Head title="Executive Sales Cockpit Login — DigitalBuilders" />

  <div class="min-h-screen bg-slate-50 dark:bg-[#070b14] text-slate-800 dark:text-slate-100 flex flex-col justify-center items-center px-4 relative overflow-hidden selection:bg-cyan-500 selection:text-white transition-colors duration-300">
    <!-- Top-right theme toggle -->
    <div class="absolute top-4 right-4 z-20 flex items-center gap-2">
      <ThemeToggle />
    </div>

    <!-- Ambient Glowing Backdrop (aligned with DigitalBuilders Hero Section) -->
    <div class="absolute -top-32 -left-32 w-[30rem] h-[30rem] bg-cyan-500/15 dark:bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute -bottom-32 -right-32 w-[32rem] h-[32rem] bg-purple-600/15 dark:bg-purple-600/10 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[44rem] h-[44rem] bg-indigo-500/10 dark:bg-indigo-600/5 rounded-full blur-[140px] pointer-events-none" />

    <!-- Subtle Grid Lines -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:4rem_4rem] pointer-events-none" />

    <div class="w-full max-w-lg relative z-10 my-8">
      <!-- Brand Header -->
      <div class="text-center mb-7">
        <Link href="/" class="inline-block group mb-3" title="Return to DigitalBuilders Home">
          <ApplicationLogo size="lg" :is-link="false" text-class="text-2xl sm:text-3xl" />
        </Link>
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-700 dark:text-cyan-400 text-xs font-semibold uppercase tracking-wider mb-2 shadow-sm">
            <ShieldCheck class="w-3.5 h-3.5" />
            Founder Authentication System
          </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400" style="font-family: 'Outfit', sans-serif;">
          Executive Sales Cockpit & High-Ticket International Pipeline
        </p>
      </div>

      <!-- Main Glassmorphism Card -->
      <div class="bg-white/85 dark:bg-slate-900/85 backdrop-blur-2xl border border-slate-200/90 dark:border-slate-800/80 rounded-3xl p-6 sm:p-9 shadow-2xl dark:shadow-cyan-950/20 transition-all duration-300">
        
        <!-- Status / Info Banner -->
        <div v-if="infoBanner" class="mb-5 p-3.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800/50 text-cyan-800 dark:text-cyan-200 text-xs flex items-start gap-2.5">
          <AlertCircle class="w-4 h-4 text-cyan-600 dark:text-cyan-400 flex-shrink-0 mt-0.5" />
          <span>{{ infoBanner }}</span>
        </div>

        <!-- ======================================================== -->
        <!-- MODE 1: STANDARD SIGN IN -->
        <!-- ======================================================== -->
        <div v-if="currentMode === 'login'">
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <KeyRound class="w-4 h-4 text-cyan-500" />
              Sign In to Cockpit
            </h2>
            <button
              type="button"
              @click="switchTo('temp_activate')"
              class="text-xs font-medium text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 transition cursor-pointer"
            >
              First-Time Setup?
            </button>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-4">
            <!-- Email Input -->
            <div>
              <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                Founder / Architect Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                  <Mail class="w-4 h-4" />
                </div>
                <input
                  id="email"
                  v-model="loginForm.email"
                  type="email"
                  required
                  autocomplete="username email"
                  placeholder="name@example.com"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition"
                />
              </div>
              <p v-if="loginForm.errors.email" class="mt-1.5 text-xs text-rose-500 font-medium">
                {{ loginForm.errors.email }}
              </p>
            </div>

            <!-- Password Input with Show/Hide Toggle -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                  Password
                </label>
                <button
                  type="button"
                  @click="switchTo('recovery')"
                  class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition cursor-pointer"
                >
                  Forgot Password?
                </button>
              </div>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                  <Lock class="w-4 h-4" />
                </div>
                <input
                  id="password"
                  v-model="loginForm.password"
                  :type="showLoginPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="Enter your password"
                  class="w-full pl-10 pr-11 py-2.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition"
                />
                <button
                  type="button"
                  @click="showLoginPassword = !showLoginPassword"
                  class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition cursor-pointer"
                  tabindex="-1"
                >
                  <EyeOff v-if="showLoginPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
              <p v-if="loginForm.errors.password" class="mt-1.5 text-xs text-rose-500 font-medium">
                {{ loginForm.errors.password }}
              </p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="loginForm.remember"
                  class="w-4 h-4 rounded bg-white dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500/30 focus:ring-offset-0"
                />
                <span class="text-xs text-slate-600 dark:text-slate-400">Remember session (30 days)</span>
              </label>
            </div>

            <!-- Submit Button with Signature Gradient -->
            <button
              type="submit"
              :disabled="loginForm.processing"
              class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-600 to-purple-600 hover:from-cyan-400 hover:via-indigo-500 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/30 transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed group cursor-pointer"
            >
              <span v-if="loginForm.processing">Authenticating Founder...</span>
              <template v-else>
                <span>Enter Sales Cockpit</span>
                <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
              </template>
            </button>
          </form>

          <!-- Security Footer Hint -->
          <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span class="flex items-center gap-1.5">
              <Sparkles class="w-3.5 h-3.5 text-amber-500" />
              256-bit TLS Encrypted
            </span>
            <button
              type="button"
              @click="switchTo('temp_activate')"
              class="hover:text-cyan-600 dark:hover:text-cyan-400 transition cursor-pointer"
            >
              Have a temporary password?
            </button>
          </div>
        </div>

        <!-- ======================================================== -->
        <!-- MODE 2: FIRST-TIME TEMPORARY PASSWORD ACTIVATION -->
        <!-- ======================================================== -->
        <div v-else-if="currentMode === 'temp_activate'">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-cyan-500" />
              First-Time Activation
            </h2>
            <button
              type="button"
              @click="switchTo('login')"
              class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 flex items-center gap-1 cursor-pointer"
            >
              <ArrowLeft class="w-3.5 h-3.5" />
              Back to Sign In
            </button>
          </div>

          <p class="text-xs text-slate-600 dark:text-slate-400 mb-5 leading-relaxed">
            Exchange your initial temporary password for your personal permanent password, and establish a security hint question for self-service recovery.
          </p>

          <!-- Error / Success Alert -->
          <div v-if="activateForm.error" class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
            <AlertCircle class="w-4 h-4 flex-shrink-0" />
            <span>{{ activateForm.error }}</span>
          </div>
          <div v-if="activateForm.success" class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 flex-shrink-0" />
            <span>{{ activateForm.success }}</span>
          </div>

          <form @submit.prevent="handleFirstTimeActivation" class="space-y-3.5">
            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                Founder Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                  <Mail class="w-4 h-4" />
                </div>
                <input
                  v-model="activateForm.email"
                  type="email"
                  required
                  placeholder="your-email@example.com"
                  class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
                />
              </div>
            </div>

            <!-- Temporary Password -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                Current Temporary Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                  <Lock class="w-4 h-4" />
                </div>
                <input
                  v-model="activateForm.temp_password"
                  :type="showTempPassword ? 'text' : 'password'"
                  required
                  placeholder="Enter initial temp password"
                  class="w-full pl-9 pr-10 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
                />
                <button
                  type="button"
                  @click="showTempPassword = !showTempPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                  tabindex="-1"
                >
                  <EyeOff v-if="showTempPassword" class="w-3.5 h-3.5" />
                  <Eye v-else class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- New Password & Confirmation Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                  New Permanent Password
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <KeyRound class="w-4 h-4" />
                  </div>
                  <input
                    v-model="activateForm.password"
                    :type="showNewPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    placeholder="Min 8 characters"
                    class="w-full pl-9 pr-10 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                    tabindex="-1"
                  >
                    <EyeOff v-if="showNewPassword" class="w-3.5 h-3.5" />
                    <Eye v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                  Confirm Password
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <KeyRound class="w-4 h-4" />
                  </div>
                  <input
                    v-model="activateForm.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    placeholder="Confirm new password"
                    class="w-full pl-9 pr-10 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                    tabindex="-1"
                  >
                    <EyeOff v-if="showConfirmPassword" class="w-3.5 h-3.5" />
                    <Eye v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Security Hint Question Selection -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <HelpCircle class="w-3.5 h-3.5 text-cyan-500" />
                Security Recovery Question
              </label>
              <select
                v-model="activateForm.security_question"
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition mb-2"
              >
                <option v-for="q in securityQuestionOptions" :key="q" :value="q">
                  {{ q }}
                </option>
              </select>
              <input
                v-if="activateForm.security_question === 'Custom Question...'"
                v-model="activateForm.custom_question"
                type="text"
                required
                placeholder="Type your own security question..."
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
              />
            </div>

            <!-- Secret Answer -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                Secret Answer (Case-insensitive)
              </label>
              <input
                v-model="activateForm.security_answer"
                type="text"
                required
                placeholder="e.g. digital builders or secret answer"
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
              />
            </div>

            <button
              type="submit"
              :disabled="activateForm.processing"
              class="w-full mt-2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-600 to-purple-600 hover:from-cyan-400 hover:via-indigo-500 hover:to-purple-500 text-white font-semibold text-xs shadow-lg shadow-cyan-500/20 transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60"
            >
              <span v-if="activateForm.processing">Activating Account...</span>
              <span v-else>Save Credentials & Enter Cockpit</span>
            </button>
          </form>
        </div>

        <!-- ======================================================== -->
        <!-- MODE 3: 2-STEP SECURITY QUESTION HINT RECOVERY -->
        <!-- ======================================================== -->
        <div v-else-if="currentMode === 'recovery'">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <RotateCcw class="w-4 h-4 text-cyan-500" />
              Security Question Recovery
            </h2>
            <button
              type="button"
              @click="switchTo('login')"
              class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 flex items-center gap-1 cursor-pointer"
            >
              <ArrowLeft class="w-3.5 h-3.5" />
              Back to Sign In
            </button>
          </div>

          <!-- Alert banner -->
          <div v-if="recoveryState.error" class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
            <AlertCircle class="w-4 h-4 flex-shrink-0" />
            <span>{{ recoveryState.error }}</span>
          </div>
          <div v-if="recoveryState.success" class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 flex-shrink-0" />
            <span>{{ recoveryState.success }}</span>
          </div>

          <!-- STEP 1: Enter Email -->
          <form v-if="recoveryState.step === 1" @submit.prevent="fetchSecurityQuestion" class="space-y-4">
            <p class="text-xs text-slate-600 dark:text-slate-400">
              Enter your founder account email. The system will retrieve your configured security hint question.
            </p>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                Founder Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Mail class="w-4 h-4" />
                </div>
                <input
                  v-model="recoveryState.email"
                  type="email"
                  required
                  placeholder="name@example.com"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500/50 transition"
                />
              </div>
            </div>

            <button
              type="submit"
              :disabled="recoveryState.loadingQuestion"
              class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-600 to-purple-600 hover:from-cyan-400 hover:via-indigo-500 hover:to-purple-500 text-white font-semibold text-xs shadow-lg transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60"
            >
              <span v-if="recoveryState.loadingQuestion">Retrieving Question...</span>
              <span v-else>Look Up Security Question</span>
            </button>
          </form>

          <!-- STEP 2: Answer Question & Set New Password -->
          <form v-else-if="recoveryState.step === 2" @submit.prevent="handleSecurityReset" class="space-y-3.5">
            <div class="p-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-800 dark:text-cyan-300 text-xs">
              <div class="font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] mb-1">
                Your Security Question:
              </div>
              <div class="font-bold text-sm text-slate-900 dark:text-white">
                {{ recoveryState.question }}
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                Secret Answer
              </label>
              <input
                v-model="recoveryState.security_answer"
                type="text"
                required
                placeholder="Enter your security answer"
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
              />
            </div>

            <!-- New Password Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                  New Password
                </label>
                <div class="relative">
                  <input
                    v-model="recoveryState.password"
                    :type="showRecoveryPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    placeholder="Min 8 characters"
                    class="w-full px-3 pr-9 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
                  />
                  <button
                    type="button"
                    @click="showRecoveryPassword = !showRecoveryPassword"
                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                    tabindex="-1"
                  >
                    <EyeOff v-if="showRecoveryPassword" class="w-3.5 h-3.5" />
                    <Eye v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                  Confirm Password
                </label>
                <input
                  v-model="recoveryState.password_confirmation"
                  type="password"
                  required
                  minlength="8"
                  placeholder="Confirm password"
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-cyan-500/50 transition"
                />
              </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
              <button
                type="button"
                @click="recoveryState.step = 1"
                class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs transition cursor-pointer"
              >
                Change Email
              </button>
              <button
                type="submit"
                :disabled="recoveryState.processing"
                class="flex-1 py-2 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-600 to-purple-600 hover:from-cyan-400 hover:via-indigo-500 hover:to-purple-500 text-white font-semibold text-xs shadow-lg transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60"
              >
                <span v-if="recoveryState.processing">Verifying & Resetting...</span>
                <span v-else>Reset Password & Enter Cockpit</span>
              </button>
            </div>
          </form>
        </div>

      </div>

      <!-- Footer back link -->
      <div class="text-center mt-6">
        <Link href="/" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 transition inline-flex items-center gap-1.5">
          <ArrowLeft class="w-3.5 h-3.5" />
          Return to DigitalBuilders Public Site
        </Link>
      </div>
    </div>
  </div>
</template>
