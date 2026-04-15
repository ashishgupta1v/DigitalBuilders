<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface LeadDTO {
    id: number;
    name: string;
    email: string;
    phone: string;
    projectType: string;
    projectTypeLabel: string;
    description: string | null;
    status: string;
    createdAt: string | null;
}

const props = defineProps<{
    leads: LeadDTO[];
    filters?: { status?: string; search?: string };
}>();

const isLoading = ref(false);

const STATUS_TABS = [
    { value: '', label: 'All' },
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'converted', label: 'Converted' },
] as const;

const STATUS_STYLES: Record<string, string> = {
    new: 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
    contacted: 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
    converted: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
};

const search = ref(props.filters?.search ?? '');
let searchTimer: ReturnType<typeof setTimeout> | null = null;

onMounted(() => {
    router.on('start', () => {
        isLoading.value = true;
    });
    router.on('finish', () => {
        isLoading.value = false;
    });
});

function applyFilter(status?: string) {
    router.get(route('leads.index'), {
        status: status ?? props.filters?.status ?? '',
        search: search.value,
    }, { preserveState: true, replace: true });
}

function onSearch() {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('leads.index'), {
            status: props.filters?.status ?? '',
            search: search.value,
        }, { preserveState: true, replace: true });
    }, 400);
}

function updateStatus(lead: LeadDTO, status: string) {
    router.patch(route('leads.status', { id: lead.id }), { status }, {
        preserveScroll: true,
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: `${lead.name} marked as ${status}`, type: 'success' },
            }));
        },
        onError: () => {
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: 'Failed to update status', type: 'error' },
            }));
        },
    });
}

function exportCsv() {
    window.location.href = route('leads.export');
}
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-[#e7efff]">
                    Leads Vault
                </h2>
                <span class="db-chip">Pipeline intelligence</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="db-mini db-panel db-reveal overflow-hidden rounded-[1.5rem]">
                    <div class="p-6">

                        <!-- Toolbar -->
                        <div class="mb-5 flex flex-wrap items-center gap-3">
                            <!-- Status Tabs -->
                            <div class="flex rounded-xl border border-[#b8c9e633] p-1 gap-1">
                                <button
                                    v-for="tab in STATUS_TABS"
                                    :key="tab.value"
                                    @click="applyFilter(tab.value)"
                                    class="rounded-lg px-3 py-1 text-xs font-semibold uppercase tracking-wider transition"
                                    :class="(filters?.status ?? '') === tab.value
                                        ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff,#c593ff)] text-[#1a2231]'
                                        : 'text-[#bcd0ef] hover:text-white'"
                                >{{ tab.label }}</button>
                            </div>

                            <!-- Search -->
                            <div class="flex-1 min-w-[180px]">
                                <input
                                    v-model="search"
                                    @input="onSearch"
                                    type="search"
                                    placeholder="Search by name or email…"
                                    class="w-full rounded-xl border border-[#b8c9e633] bg-[#27374d60] px-4 py-2 text-sm text-[#e7efff] placeholder:text-[#6b82a0] focus:border-[#9ba7ff] focus:outline-none"
                                />
                            </div>

                            <div class="flex items-center gap-2 ml-auto">
                                <p class="text-[11px] uppercase tracking-[0.2em] text-[#bcd0ef]">{{ leads.length }} records</p>
                                <!-- CSV Export -->
                                <button
                                    @click="exportCsv"
                                    class="flex items-center gap-1.5 rounded-xl border border-[#b8c9e633] px-3 py-2 text-xs font-semibold text-[#bcd0ef] transition hover:border-[#9ba7ff] hover:text-white"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Export CSV
                                </button>
                            </div>
                        </div>

                        <!-- Table with skeleton loading -->
                        <div v-if="isLoading" class="space-y-2" aria-busy="true" aria-label="Loading leads...">
                            <div class="flex gap-4 border-b border-[#b8c9e626] px-5 py-4">
                                <div class="db-skeleton h-4 w-32 rounded" />
                                <div class="db-skeleton h-4 w-48 rounded" />
                                <div class="db-skeleton h-4 w-40 rounded" />
                                <div class="db-skeleton h-4 flex-1 rounded" />
                            </div>
                            <div class="flex gap-4 border-b border-[#b8c9e626] px-5 py-4">
                                <div class="db-skeleton h-4 w-32 rounded" />
                                <div class="db-skeleton h-4 w-48 rounded" />
                                <div class="db-skeleton h-4 w-40 rounded" />
                                <div class="db-skeleton h-4 flex-1 rounded" />
                            </div>
                            <div class="flex gap-4 border-b border-[#b8c9e626] px-5 py-4">
                                <div class="db-skeleton h-4 w-32 rounded" />
                                <div class="db-skeleton h-4 w-48 rounded" />
                                <div class="db-skeleton h-4 w-40 rounded" />
                                <div class="db-skeleton h-4 flex-1 rounded" />
                            </div>
                        </div>

                        <!-- Actual content -->
                        <div v-else-if="leads.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#b8c9e633]">
                                <thead class="bg-[#2f425ab5]">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Name</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Email</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Phone</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Project</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Status</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Submitted</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#bcd0ef]">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#b8c9e626] bg-transparent">
                                    <tr v-for="lead in leads" :key="lead.id" class="transition hover:bg-[#27374d40]">
                                        <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-[#e7efff]">{{ lead.name }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-[#bfd0eb]">{{ lead.email }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-[#bfd0eb]">{{ lead.phone }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-[#bfd0eb]">{{ lead.projectTypeLabel }}</td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span
                                                class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider"
                                                :class="STATUS_STYLES[lead.status] ?? STATUS_STYLES['new']"
                                            >{{ lead.status }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-[#bfd0eb]">{{ lead.createdAt }}</td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <select
                                                :value="lead.status"
                                                @change="updateStatus(lead, ($event.target as HTMLSelectElement).value)"
                                                class="rounded-lg border border-[#b8c9e633] bg-[#27374d90] px-2 py-1 text-xs text-[#e7efff] focus:border-[#9ba7ff] focus:outline-none"
                                            >
                                                <option value="new">New</option>
                                                <option value="contacted">Contacted</option>
                                                <option value="converted">Converted</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="mb-4 h-20 w-20 text-[#3d5575]" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="24" width="80" height="56" rx="8" stroke="currentColor" stroke-width="3" fill="none"/>
                                <path d="M8 36h80" stroke="currentColor" stroke-width="3"/>
                                <rect x="18" y="48" width="20" height="4" rx="2" fill="currentColor" opacity="0.4"/>
                                <rect x="18" y="58" width="36" height="4" rx="2" fill="currentColor" opacity="0.3"/>
                                <rect x="18" y="68" width="28" height="4" rx="2" fill="currentColor" opacity="0.2"/>
                                <circle cx="72" cy="20" r="12" stroke="currentColor" stroke-width="3" fill="none"/>
                                <path d="M68 20h8M72 16v8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                            <p class="text-base font-semibold text-[#dce7ff]">No leads yet</p>
                            <p class="mt-1 text-sm text-[#7a95b8]">
                                {{ filters?.status ? `No leads with status "${filters.status}"` : 'New inquiries from your contact form will appear here.' }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
