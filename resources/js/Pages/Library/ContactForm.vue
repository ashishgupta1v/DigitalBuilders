<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const flash = computed(() => (page.props as Record<string, any>).flash as Record<string, string> | undefined);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    project_type: '',
    description: '',
});

const projectTypes = [
    { value: 'web_app', label: 'Web Application' },
    { value: 'mobile_app', label: 'Mobile App' },
    { value: 'erp_crm', label: 'ERP / CRM' },
    { value: 'saas', label: 'SaaS Platform' },
    { value: 'ai_solutions', label: 'AI Solutions' },
    { value: 'other', label: 'Other' },
];

function submit() {
    form.post(route('library.leads.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Contact" />

    <div class="db-shell min-h-screen bg-transparent text-[#e7efff]">
        <div class="db-grid-overlay" />

        <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="db-reveal mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#b9cae6]">Discovery intake</p>
                    <h1 class="mt-2 text-4xl font-semibold text-[#e7efff]">Let's Connect</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-[#b4c3de]">
                        Your business requires software that works as hard as you do. We guarantee transparency, disciplined engineering processes, and premium architectural quality.
                    </p>
                </div>
                <span class="db-chip">24h response</span>
            </div>

            <div v-if="flash?.success" class="mt-6 rounded-xl border border-[#8de0ba66] bg-[#1f3a3a80] p-4 text-[#9feac5]">
                {{ flash.success }}
            </div>

            <form @submit.prevent="submit" class="db-mini db-panel db-reveal mt-8 space-y-6 rounded-[1.5rem] p-7" style="animation-delay: 0.08s">
                <div>
                    <label for="name" class="block text-sm font-medium text-[#cfdcf3]">Full Name</label>
                    <input id="name" v-model="form.name" type="text" placeholder="First and Last Name"
                        class="db-input mt-1 block w-full rounded-xl border border-[#b8c9e640] bg-[#223144cc] px-4 py-3 text-[#e7efff] placeholder:text-[#9bb0d1] shadow-sm focus:border-[#97a7ff] focus:ring-[#97a7ff] sm:text-sm" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-[#ff97b2]">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-[#cfdcf3]">Corporate Email</label>
                    <input id="email" v-model="form.email" type="email" placeholder="you@company.com"
                        class="db-input mt-1 block w-full rounded-xl border border-[#b8c9e640] bg-[#223144cc] px-4 py-3 text-[#e7efff] placeholder:text-[#9bb0d1] shadow-sm focus:border-[#97a7ff] focus:ring-[#97a7ff] sm:text-sm" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-[#ff97b2]">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-[#cfdcf3]">Phone Number</label>
                    <input id="phone" v-model="form.phone" type="text" placeholder="+91 XXXXX XXXXX"
                        class="db-input mt-1 block w-full rounded-xl border border-[#b8c9e640] bg-[#223144cc] px-4 py-3 text-[#e7efff] placeholder:text-[#9bb0d1] shadow-sm focus:border-[#97a7ff] focus:ring-[#97a7ff] sm:text-sm" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-[#ff97b2]">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <label for="project_type" class="block text-sm font-medium text-[#cfdcf3]">Project Type</label>
                    <select id="project_type" v-model="form.project_type"
                        class="db-input mt-1 block w-full rounded-xl border border-[#b8c9e640] bg-[#223144cc] px-4 py-3 text-[#e7efff] shadow-sm focus:border-[#97a7ff] focus:ring-[#97a7ff] sm:text-sm">
                        <option value="" disabled>Select a project type</option>
                        <option v-for="pt in projectTypes" :key="pt.value" :value="pt.value">{{ pt.label }}</option>
                    </select>
                    <p v-if="form.errors.project_type" class="mt-1 text-sm text-[#ff97b2]">{{ form.errors.project_type }}</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-[#cfdcf3]">Project Description (Optional)</label>
                    <textarea id="description" v-model="form.description" rows="4"
                        placeholder="Briefly describe your core operational challenge or project vision..."
                        class="db-input mt-1 block w-full rounded-xl border border-[#b8c9e640] bg-[#223144cc] px-4 py-3 text-[#e7efff] placeholder:text-[#9bb0d1] shadow-sm focus:border-[#97a7ff] focus:ring-[#97a7ff] sm:text-sm"></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-[#ff97b2]">{{ form.errors.description }}</p>
                </div>

                <div>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex justify-center rounded-full border border-[#b8c9e640] bg-[linear-gradient(95deg,#7ac4ff_0%,#9ba7ff_48%,#c593ff_100%)] px-6 py-3 text-sm font-semibold text-[#1a2231] shadow-sm hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-[#a6b0ff] focus:ring-offset-0 disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Request a Project Quote' }}
                    </button>
                </div>

                <p class="text-xs text-[#b4c3de]">
                    Your data is secure. We review all inquiries and respond within 24 business hours.
                </p>
            </form>
        </div>
    </div>
</template>
