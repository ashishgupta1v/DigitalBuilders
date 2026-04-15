<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

interface LeadDTO {
    id: number;
    name: string;
    email: string;
    phone: string;
    projectType: string;
    projectTypeLabel: string;
    description: string | null;
    createdAt: string | null;
}

defineProps<{
    leads: LeadDTO[];
}>();
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Leads
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200" v-if="leads.length">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Project Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Submitted</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="lead in leads" :key="lead.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ lead.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ lead.email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ lead.phone }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ lead.projectTypeLabel }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ lead.createdAt }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-gray-500">No leads yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
