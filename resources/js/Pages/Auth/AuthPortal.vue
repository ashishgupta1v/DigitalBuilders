<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type AuthMode = 'login' | 'register';

const props = defineProps<{
    initialMode: AuthMode;
    canResetPassword?: boolean;
    status?: string;
}>();

const mode = ref<AuthMode>(props.initialMode);

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const headTitle = computed(() => (mode.value === 'login' ? 'Log in' : 'Register'));

function switchMode(nextMode: AuthMode) {
    if (mode.value === nextMode) {
        return;
    }

    mode.value = nextMode;
    loginForm.clearErrors();
    registerForm.clearErrors();

    const targetUrl = nextMode === 'login' ? route('login') : route('register');
    window.history.replaceState(window.history.state, '', targetUrl);
}

function submitLogin() {
    loginForm.post(route('login'), {
        preserveScroll: true,
        onFinish: () => {
            loginForm.reset('password');
        },
    });
}

function submitRegister() {
    registerForm.post(route('register'), {
        preserveScroll: true,
        onFinish: () => {
            registerForm.reset('password', 'password_confirmation');
        },
    });
}
</script>

<template>
    <GuestLayout>
        <Head :title="headTitle" />

        <div class="mb-6 flex items-center rounded-xl border border-[#b8c9e640] bg-[#1f2d3fcc] p-1">
            <button
                type="button"
                @click="switchMode('login')"
                class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition"
                :class="mode === 'login' ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] text-[#1a2231]' : 'text-[#bfd0eb] hover:text-white'"
            >
                Log in
            </button>
            <button
                type="button"
                @click="switchMode('register')"
                class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition"
                :class="mode === 'register' ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] text-[#1a2231]' : 'text-[#bfd0eb] hover:text-white'"
            >
                Register
            </button>
        </div>

        <div class="mb-6" v-if="mode === 'login'">
            <span class="db-chip">Secure access</span>
            <h1 class="mt-4 text-2xl font-semibold text-white">Sign in to your control deck</h1>
            <p class="mt-2 text-sm text-[#b4c3de]">Access premium dashboards, leads, and profile controls.</p>
        </div>

        <div class="mb-6" v-else>
            <span class="db-chip">Premium onboarding</span>
            <h1 class="mt-4 text-2xl font-semibold text-white">Create your DigitalBuilders account</h1>
            <p class="mt-2 text-sm text-[#b4c3de]">Get access to architecture workflows and the full command experience.</p>
        </div>

        <div v-if="props.status && mode === 'login'" class="mb-4 text-sm font-medium text-[#9feac5]">
            {{ props.status }}
        </div>

        <a
            :href="route('auth.google.redirect')"
            class="db-action mb-6 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#b8c9e64d] bg-[#27374dd9] px-4 py-3 text-sm font-semibold text-[#e7efff] transition hover:border-[#d0ddff80] hover:text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z")/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z")/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z")/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z")/>
                <path fill="none" d="M0 0h48v48H0z")/>
            </svg>
            {{ mode === 'login' ? 'Continue with Google' : 'Sign up with Google' }}
        </a>

        <div class="mb-6 flex items-center gap-3 text-xs uppercase tracking-[0.2em] text-[#9fb1cd]">
            <span class="h-px flex-1 bg-[#b8c9e633]" />
            {{ mode === 'login' ? 'Or sign in with email' : 'Or sign up with email' }}
            <span class="h-px flex-1 bg-[#b8c9e633]" />
        </div>

        <form v-if="mode === 'login'" @submit.prevent="submitLogin">
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
                    v-if="props.canResetPassword"
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

        <form v-else @submit.prevent="submitRegister">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="registerForm.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="registerForm.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="register-email" value="Email" />

                <TextInput
                    id="register-email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="registerForm.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="registerForm.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="register-password" value="Password" />

                <TextInput
                    id="register-password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="registerForm.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="registerForm.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="registerForm.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="registerForm.errors.password_confirmation" />
            </div>

            <div class="mt-6 flex items-center justify-end">
                <PrimaryButton
                    class="w-full justify-center sm:w-auto"
                    :class="{ 'opacity-25': registerForm.processing }"
                    :disabled="registerForm.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
