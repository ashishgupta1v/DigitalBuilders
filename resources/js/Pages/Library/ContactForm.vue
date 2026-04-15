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

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Let's Connect</h1>
            <p class="mt-4 text-gray-600">
                Your business requires software that works as hard as you do. We guarantee transparency, disciplined engineering processes, and premium architectural quality.
            </p>

            <div v-if="flash?.success" class="mt-6 rounded-md bg-green-50 p-4 text-green-800">
                {{ flash.success }}
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="name" v-model="form.name" type="text" placeholder="First and Last Name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Corporate Email</label>
                    <input id="email" v-model="form.email" type="email" placeholder="you@company.com"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input id="phone" v-model="form.phone" type="text" placeholder="+91 XXXXX XXXXX"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <label for="project_type" class="block text-sm font-medium text-gray-700">Project Type</label>
                    <select id="project_type" v-model="form.project_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="" disabled>Select a project type</option>
                        <option v-for="pt in projectTypes" :key="pt.value" :value="pt.value">{{ pt.label }}</option>
                    </select>
                    <p v-if="form.errors.project_type" class="mt-1 text-sm text-red-600">{{ form.errors.project_type }}</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Project Description (Optional)</label>
                    <textarea id="description" v-model="form.description" rows="4"
                        placeholder="Briefly describe your core operational challenge or project vision..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <div>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Request a Project Quote' }}
                    </button>
                </div>

                <p class="text-xs text-gray-500">
                    Your data is secure. We review all inquiries and respond within 24 business hours.
                </p>
            </form>
        </div>
    </div>
</template>
