<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3'
import { ShieldCheck, Lock, Mail, ArrowRight, Zap } from 'lucide-vue-next'
import ThemeToggle from '@/Components/ThemeToggle.vue'

const form = useForm({
  email: 'ashish@digitalbuilders.in',
  password: '',
  remember: true,
})

const submit = () => {
  form.post('/crm/login', {
    onFinish: () => {
      form.password = ''
    },
  })
}

const fillDemo = (email: string) => {
  form.email = email
  form.password = 'password'
}
</script>

<template>
  <Head title="Executive CRM Login — DigitalBuilders" />

  <div class="crm-cockpit min-h-screen bg-slate-50 dark:bg-[#070b14] text-slate-800 dark:text-slate-100 flex flex-col justify-center items-center px-4 relative overflow-hidden selection:bg-cyan-500 selection:text-white transition-colors duration-300">
    <!-- Top-right theme toggle -->
    <div class="absolute top-4 right-4 z-20">
      <ThemeToggle />
    </div>

    <!-- Ambient glowing backdrop -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[120px] pointer-events-none" />

    <div class="w-full max-w-md relative z-10 my-8">
      <!-- Brand Header -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-block group mb-3" title="DigitalBuilders Home">
          <img
            src="/images/db-logo.png"
            alt="DigitalBuilders Logo"
            class="h-16 w-16 mx-auto object-contain drop-shadow-[0_0_16px_rgba(56,189,248,0.4)] transition-transform duration-300 group-hover:scale-105"
          />
        </Link>
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-600 dark:text-cyan-400 text-xs font-semibold uppercase tracking-wider mb-3 shadow-sm">
            <ShieldCheck class="w-3.5 h-3.5" />
            Founder Portal
          </div>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
          Digital<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 via-indigo-500 to-purple-600 dark:from-cyan-400 dark:via-indigo-300 dark:to-purple-400">Builders</span>
        </h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
          Executive Sales Cockpit & High-Ticket Pipeline
        </p>
      </div>

      <!-- Auth Card -->
      <div class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-xl dark:shadow-2xl dark:shadow-cyan-950/20">
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-xs font-medium text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
              Founder / Architect Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Mail class="w-4 h-4" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                placeholder="ashish@digitalbuilders.in"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-500 font-medium">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label for="password" class="block text-xs font-medium text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Access Password
              </label>
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Lock class="w-4 h-4" />
              </div>
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••••••"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition"
              />
            </div>
            <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-500 font-medium">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Remember Me & Session Duration -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="form.remember"
                class="w-4 h-4 rounded bg-white dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500/30 focus:ring-offset-0"
              />
              <span class="text-xs text-slate-600 dark:text-slate-400 select-none">Remember 30 days (Mobile & Desktop)</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-600 to-purple-600 hover:from-cyan-400 hover:via-indigo-500 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/30 transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed group cursor-pointer"
          >
            <span v-if="form.processing">Authenticating...</span>
            <template v-else>
              <span>Enter Sales Cockpit</span>
              <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
            </template>
          </button>
        </form>

        <!-- Quick Access Hint for Founder -->
        <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800/80">
          <p class="text-xs text-slate-500 dark:text-slate-400 text-center mb-2.5 font-medium flex items-center justify-center gap-1.5">
            <Zap class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400" />
            Quick 1-Click Credentials:
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              type="button"
              @click="fillDemo('ashish@digitalbuilders.in')"
              class="p-2 rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700/50 text-left transition"
            >
              <div class="font-semibold text-cyan-600 dark:text-cyan-300 truncate">ashish@digital...</div>
              <div class="text-[11px] text-slate-500">password: password</div>
            </button>
            <button
              type="button"
              @click="fillDemo('admin@digitalbuilders.in')"
              class="p-2 rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700/50 text-left transition"
            >
              <div class="font-semibold text-purple-600 dark:text-purple-300 truncate">admin@digital...</div>
              <div class="text-[11px] text-slate-500">password: password</div>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer back link -->
      <div class="text-center mt-6">
        <Link href="/" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 transition">
          ← Return to DigitalBuilders Public Site
        </Link>
      </div>
    </div>
  </div>
</template>
