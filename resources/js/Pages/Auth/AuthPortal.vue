<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

function submitLogin() {
    loginForm.post(route('login'), {
        preserveScroll: true,
        onFinish: () => {
            loginForm.reset('password');
        },
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Staff Sign In — DigitalBuilders" />

        <div class="mb-6">
            <span class="db-chip">Secure Staff Portal</span>
            <h1 class="mt-4 text-2xl font-bold text-white">Sign in to control deck</h1>
            <p class="mt-2 text-sm text-[#b4c3de]">Access internal CRM pipeline, leads vault, and architecture telemetry.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-[#9feac5]">
            {{ status }}
        </div>

        <a
            :href="route('auth.google.redirect')"
            class="db-action mb-6 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#b8c9e64d] bg-[#27374dd9] px-4 py-3 text-sm font-semibold text-[#e7efff] transition hover:border-[#d0ddff80] hover:text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
            Continue with Google
        </a>

        <div class="mb-6 flex items-center gap-3 text-xs uppercase tracking-[0.2em] text-[#9fb1cd]">
            <span class="h-px flex-1 bg-[#b8c9e633]" />
            Or sign in with email
            <span class="h-px flex-1 bg-[#b8c9e633]" />
        </div>

        <form @submit.prevent="submitLogin">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="loginForm.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="loginForm.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="loginForm.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="loginForm.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="loginForm.remember" />
                    <span class="ms-2 text-sm text-[#b4c3de]">Remember me</span>
                </label>
            </div>

            <div class="mt-6 flex flex-col-reverse items-start gap-3 sm:flex-row sm:items-center sm:justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-[#bcd0ef] underline decoration-[#8ea4ff80] underline-offset-4 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#a6b0ff] focus:ring-offset-0"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="w-full justify-center sm:ms-4 sm:w-auto"
                    :class="{ 'opacity-25': loginForm.processing }"
                    :disabled="loginForm.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
