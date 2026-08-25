<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

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
    score?: number;
    notesCount?: number;
}

interface LeadNote {
    id: number;
    lead_id: number;
    author_name: string;
    note: string;
    created_at: string;
}

const props = defineProps<{
    leads: LeadDTO[];
    filters?: { status?: string; search?: string };
}>();

const viewMode = ref<'kanban' | 'table'>('kanban');
const selectedScoreTier = ref<'all' | 'hot' | 'warm' | 'standard'>('all');
const isLoading = ref(false);
const search = ref(props.filters?.search ?? '');
let searchTimer: ReturnType<typeof setTimeout> | null = null;

// Selected Lead for Drawer
const selectedLead = ref<LeadDTO | null>(null);
const drawerOpen = ref(false);
const leadNotes = ref<LeadNote[]>([]);
const newNoteText = ref('');
const isSubmittingNote = ref(false);

const KANBAN_COLUMNS = [
    { key: 'new', label: 'New Inquiries', badge: 'bg-blue-500/20 text-blue-300 border-blue-500/30' },
    { key: 'contacted', label: 'Contacted', badge: 'bg-amber-500/20 text-amber-300 border-amber-500/30' },
    { key: 'proposal', label: 'Proposal Sent', badge: 'bg-purple-500/20 text-purple-300 border-purple-500/30' },
    { key: 'converted', label: 'Converted (Won)', badge: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' },
    { key: 'archived', label: 'Archived', badge: 'bg-slate-500/20 text-slate-300 border-slate-500/30' },
] as const;

const STATUS_STYLES: Record<string, string> = {
    new: 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
    contacted: 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
    proposal: 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
    converted: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
    archived: 'bg-slate-500/20 text-slate-300 border border-slate-500/30',
};

onMounted(() => {
    router.on('start', () => { isLoading.value = true; });
    router.on('finish', () => { isLoading.value = false; });
});

function onSearch() {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('library.leads.index'), {
            status: props.filters?.status ?? '',
            search: search.value,
        }, { preserveState: true, replace: true });
    }, 400);
}

function updateStatus(lead: LeadDTO, newStatus: string) {
    router.patch(route('library.leads.status', { id: lead.id }), { status: newStatus }, {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedLead.value && selectedLead.value.id === lead.id) {
                selectedLead.value.status = newStatus;
            }
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: `${lead.name} status updated to ${newStatus}`, type: 'success' },
            }));
        },
    });
}

function exportCsv() {
    window.location.href = route('library.leads.export');
}

function getScoreBadge(score?: number) {
    const s = score ?? 50;
    if (s >= 75) return { text: `🔥 ${s}`, class: 'bg-rose-500/20 text-rose-300 border-rose-500/40' };
    if (s >= 45) return { text: `⚡ ${s}`, class: 'bg-amber-500/20 text-amber-300 border-amber-500/40' };
    return { text: `📋 ${s}`, class: 'bg-slate-500/20 text-slate-300 border-slate-500/40' };
}

// Filter leads by search & score tier
const filteredLeads = computed(() => {
    return props.leads.filter((lead) => {
        const s = lead.score ?? 50;
        if (selectedScoreTier.value === 'hot') return s >= 75;
        if (selectedScoreTier.value === 'warm') return s >= 45 && s < 75;
        if (selectedScoreTier.value === 'standard') return s < 45;
        return true;
    });
});

// Group leads by status for Kanban view
const groupedLeads = computed(() => {
    const map: Record<string, LeadDTO[]> = {
        new: [],
        contacted: [],
        proposal: [],
        converted: [],
        archived: [],
    };
    filteredLeads.value.forEach((l) => {
        const st = l.status || 'new';
        if (!map[st]) map[st] = [];
        map[st].push(l);
    });
    return map;
});

// WhatsApp template URL helper
function getWhatsAppUrl(lead: LeadDTO): string {
    const phoneClean = lead.phone.replace(/[^0-9]/g, '');
    const phoneWithCountry = phoneClean.length === 10 ? `91${phoneClean}` : phoneClean;
    const text = encodeURIComponent(
        `Hi ${lead.name}, this is Ashish from DigitalBuilders. I reviewed your inquiry regarding ${lead.projectTypeLabel || 'your project'} and would love to discuss next steps. When is a good time for a quick 10-minute discovery call?`
    );
    return `https://wa.me/${phoneWithCountry}?text=${text}`;
}

// Email template URL helper
function getMailtoUrl(lead: LeadDTO): string {
    const subject = encodeURIComponent(`DigitalBuilders Follow-up: ${lead.projectTypeLabel || 'Project Inquiry'}`);
    const body = encodeURIComponent(
        `Hi ${lead.name},\n\nThank you for reaching out to DigitalBuilders regarding ${lead.projectTypeLabel || 'your project'}.\n\nI have reviewed your inquiry and would be glad to schedule a 15-minute discovery session to walk through our technical approach, architecture recommendations, and sprint estimate.\n\nLooking forward to hearing from you.\n\nBest regards,\nAshish Gupta\nLead Digital Architect, DigitalBuilders\nhttps://www.digitalbuilders.in`
    );
    return `mailto:${lead.email}?subject=${subject}&body=${body}`;
}

// Open Drawer & Fetch Notes
async function openDrawer(lead: LeadDTO) {
    selectedLead.value = lead;
    drawerOpen.value = true;
    newNoteText.value = '';
    leadNotes.value = [];

    try {
        const res = await fetch(route('library.leads.notes', { id: lead.id }));
        if (res.ok) {
            leadNotes.value = await res.json();
        }
    } catch {
        // notes fetch fallback
    }
}

async function addNote() {
    if (!selectedLead.value || !newNoteText.value.trim()) return;
    isSubmittingNote.value = true;

    router.post(route('library.leads.notes.store', { id: selectedLead.value.id }), {
        note: newNoteText.value.trim(),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newNoteText.value = '';
            isSubmittingNote.value = false;
            // Refetch notes
            openDrawer(selectedLead.value!);
            window.dispatchEvent(new CustomEvent('db:toast', {
                detail: { message: 'Internal staff note added', type: 'success' },
            }));
        },
        onFinish: () => {
            isSubmittingNote.value = false;
        },
    });
}
</script>

<template>
    <Head title="Leads Vault & CRM Pipeline — DigitalBuilders" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black leading-tight text-[#e7efff]">
                        CRM Pipeline & Leads Vault
                    </h2>
                    <p class="text-xs text-slate-400">Manage client acquisition, pipeline stages, lead scores & staff notes.</p>
                </div>
                <span class="db-chip">Real-Time CRM Deck</span>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="db-mini db-panel overflow-hidden rounded-[1.5rem] p-4 sm:p-6">
                    
                    <!-- Toolbar & Controls -->
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <!-- View Toggle (Kanban vs Table) -->
                        <div class="flex items-center rounded-xl border border-[#b8c9e633] bg-[#1a2534] p-1">
                            <button
                                @click="viewMode = 'kanban'"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition"
                                :class="viewMode === 'kanban' ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] text-[#1a2231]' : 'text-slate-400 hover:text-white'"
                            >
                                📊 Kanban Pipeline
                            </button>
                            <button
                                @click="viewMode = 'table'"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition"
                                :class="viewMode === 'table' ? 'bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] text-[#1a2231]' : 'text-slate-400 hover:text-white'"
                            >
                                📋 Tabular Vault
                            </button>
                        </div>

                        <!-- Score Tier Tabs -->
                        <div class="flex items-center gap-1.5 rounded-xl border border-[#b8c9e625] bg-[#172230] p-1 text-xs">
                            <button
                                @click="selectedScoreTier = 'all'"
                                class="rounded-lg px-2.5 py-1 font-semibold transition"
                                :class="selectedScoreTier === 'all' ? 'bg-sky-500/20 text-sky-300 font-bold' : 'text-slate-400 hover:text-white'"
                            >
                                All ({{ props.leads.length }})
                            </button>
                            <button
                                @click="selectedScoreTier = 'hot'"
                                class="rounded-lg px-2.5 py-1 font-semibold transition"
                                :class="selectedScoreTier === 'hot' ? 'bg-rose-500/20 text-rose-300 font-bold' : 'text-slate-400 hover:text-white'"
                            >
                                🔥 Hot ({{ props.leads.filter(l => (l.score ?? 50) >= 75).length }})
                            </button>
                            <button
                                @click="selectedScoreTier = 'warm'"
                                class="rounded-lg px-2.5 py-1 font-semibold transition"
                                :class="selectedScoreTier === 'warm' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'text-slate-400 hover:text-white'"
                            >
                                ⚡ Warm ({{ props.leads.filter(l => (l.score ?? 50) >= 45 && (l.score ?? 50) < 75).length }})
                            </button>
                            <button
                                @click="selectedScoreTier = 'standard'"
                                class="rounded-lg px-2.5 py-1 font-semibold transition"
                                :class="selectedScoreTier === 'standard' ? 'bg-slate-500/20 text-slate-300 font-bold' : 'text-slate-400 hover:text-white'"
                            >
                                📋 Standard ({{ props.leads.filter(l => (l.score ?? 50) < 45).length }})
                            </button>
                        </div>

                        <!-- Search Input -->
                        <div class="flex-1 min-w-[200px] max-w-xs">
                            <input
                                v-model="search"
                                @input="onSearch"
                                type="search"
                                placeholder="Search by client name, email or project..."
                                class="w-full rounded-xl border border-[#b8c9e633] bg-[#27374d60] px-4 py-2 text-xs text-[#e7efff] placeholder:text-[#6b82a0] focus:border-[#9ba7ff] focus:outline-none"
                            />
                        </div>

                        <!-- Export CSV -->
                        <div class="flex items-center gap-3">
                            <button
                                @click="exportCsv"
                                class="flex items-center gap-1.5 rounded-xl border border-[#b8c9e633] px-3.5 py-2 text-xs font-bold text-[#bcd0ef] transition hover:border-[#9ba7ff] hover:text-white"
                            >
                                📥 Export CSV
                            </button>
                        </div>
                    </div>

                    <!-- 1. KANBAN PIPELINE VIEW -->
                    <div v-if="viewMode === 'kanban'" class="overflow-x-auto pb-4">
                        <div class="grid min-w-[1100px] grid-cols-5 gap-4">
                            <div
                                v-for="col in KANBAN_COLUMNS"
                                :key="col.key"
                                class="flex flex-col rounded-2xl border border-[#b8c9e622] bg-[#1a253480] p-3 min-h-[500px]"
                            >
                                <!-- Column Header -->
                                <div class="mb-3 flex items-center justify-between border-b border-[#b8c9e622] pb-2.5">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-white">
                                        <svg v-if="col.key === 'new'" class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <svg v-else-if="col.key === 'contacted'" class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <svg v-else-if="col.key === 'proposal'" class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <svg v-else-if="col.key === 'converted'" class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg v-else class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 012-2h10a2 2 0 012 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        <span>{{ col.label }}</span>
                                    </div>
                                    <span class="rounded-full bg-[#27374d] px-2 py-0.5 text-[10px] font-bold text-[#9ba7ff]">
                                        {{ groupedLeads[col.key]?.length ?? 0 }}
                                    </span>
                                </div>

                                <!-- Cards List -->
                                <div class="flex-1 space-y-3">
                                    <div
                                        v-for="lead in groupedLeads[col.key]"
                                        :key="lead.id"
                                        @click="openDrawer(lead)"
                                        class="group cursor-pointer rounded-xl border border-[#b8c9e633] bg-[#27374dcb] p-3.5 shadow-md transition hover:-translate-y-0.5 hover:border-[#9ba7ff]"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-xs font-bold text-white group-hover:text-[#b7d3ff] truncate">{{ lead.name }}</h4>
                                            <!-- Score Badge -->
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-[10px] font-extrabold"
                                                :class="getScoreBadge(lead.score).class"
                                            >
                                                {{ getScoreBadge(lead.score).text }}
                                            </span>
                                        </div>

                                        <p class="mt-1 truncate text-[11px] text-slate-300">{{ lead.email }}</p>
                                        
                                        <div class="mt-2.5 flex items-center justify-between text-[10px] text-slate-400">
                                            <span class="rounded bg-[#1a2534] px-2 py-0.5 text-[#9ba7ff] font-semibold truncate max-w-[110px]">{{ lead.projectTypeLabel }}</span>
                                            <span>💬 {{ lead.notesCount ?? 0 }}</span>
                                        </div>

                                        <!-- Quick Move Dropdown & 1-Click Action Icons -->
                                        <div class="mt-3 flex items-center gap-2 border-t border-[#b8c9e618] pt-2" @click.stop>
                                            <select
                                                :value="lead.status"
                                                @change="updateStatus(lead, ($event.target as HTMLSelectElement).value)"
                                                class="flex-1 rounded-lg border border-[#b8c9e626] bg-[#1a2534] px-2 py-1 text-[10px] text-slate-200 focus:outline-none"
                                            >
                                                <option value="new">Move to New</option>
                                                <option value="contacted">Move to Contacted</option>
                                                <option value="proposal">Move to Proposal</option>
                                                <option value="converted">Move to Converted</option>
                                                <option value="archived">Move to Archived</option>
                                            </select>
                                            <a
                                                :href="getWhatsAppUrl(lead)"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="1-Click WhatsApp Direct Chat"
                                                class="rounded-lg bg-[#25d366]/20 border border-[#25d366]/40 p-1 text-[#25d366] hover:bg-[#25d366] hover:text-white transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. TABULAR VAULT VIEW -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[900px] divide-y divide-[#b8c9e626]">
                            <thead class="bg-[#1a2534]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Score</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Client Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Project Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status Stage</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Notes</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">1-Click Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#b8c9e618]">
                                <tr
                                    v-for="lead in filteredLeads"
                                    :key="lead.id"
                                    @click="openDrawer(lead)"
                                    class="cursor-pointer transition hover:bg-[#27374d60]"
                                >
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-bold" :class="getScoreBadge(lead.score).class">
                                            {{ getScoreBadge(lead.score).text }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs font-bold text-white">{{ lead.name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-300">{{ lead.email }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-[#b7d3ff]">{{ lead.projectTypeLabel }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase" :class="STATUS_STYLES[lead.status] ?? STATUS_STYLES['new']">
                                            {{ lead.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-400">💬 {{ lead.notesCount ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 flex items-center gap-2" @click.stop>
                                        <a
                                            :href="getWhatsAppUrl(lead)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="1-Click WhatsApp"
                                            class="rounded-lg bg-[#25d366]/20 border border-[#25d366]/40 p-1.5 text-[#25d366] hover:bg-[#25d366] hover:text-white transition"
                                        >
                                            <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                                        </a>
                                        <a
                                            :href="getMailtoUrl(lead)"
                                            title="1-Click Email Template"
                                            class="rounded-lg bg-sky-500/20 border border-sky-500/40 p-1.5 text-sky-400 hover:bg-sky-500 hover:text-white transition"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </a>
                                        <select
                                            :value="lead.status"
                                            @change="updateStatus(lead, ($event.target as HTMLSelectElement).value)"
                                            class="rounded-lg border border-[#b8c9e626] bg-[#1a2534] px-2 py-1 text-xs text-white focus:outline-none"
                                        >
                                            <option value="new">New</option>
                                            <option value="contacted">Contacted</option>
                                            <option value="proposal">Proposal</option>
                                            <option value="converted">Converted</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. SLIDE-OVER LEAD DETAIL & NOTES DRAWER -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-x-full"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="drawerOpen && selectedLead"
                class="fixed inset-y-0 right-0 z-[9000] flex w-full max-w-md flex-col border-l border-[#b8c9e640] bg-[#1a2534] p-6 shadow-2xl backdrop-blur-xl"
            >
                <!-- Drawer Header -->
                <div class="flex items-center justify-between border-b border-[#b8c9e622] pb-4">
                    <div>
                        <span class="db-chip">Lead Intelligence</span>
                        <h3 class="mt-1 text-lg font-bold text-white">{{ selectedLead.name }}</h3>
                    </div>
                    <button @click="drawerOpen = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <!-- Drawer Content -->
                <div class="flex-1 overflow-y-auto py-4 space-y-6 text-xs">
                    <!-- 1-Click Outreach Action Bar -->
                    <div class="grid grid-cols-2 gap-3">
                        <a
                            :href="getWhatsAppUrl(selectedLead)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center justify-center gap-2 rounded-xl bg-[#25d366] px-3 py-2.5 text-xs font-bold text-white shadow hover:bg-[#20ba5a] transition"
                        >
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.815 11.815 0 018.413 3.479 11.821 11.821 0 013.48 8.413c-.003 6.558-5.339 11.893-11.893 11.893h-.005a11.882 11.882 0 01-5.683-1.448L0 24h.057z"/></svg>
                            <span>WhatsApp Pitch</span>
                        </a>
                        <a
                            :href="getMailtoUrl(selectedLead)"
                            class="flex items-center justify-center gap-2 rounded-xl bg-[linear-gradient(95deg,#0284c7,#4f46e5)] px-3 py-2.5 text-xs font-bold text-white shadow hover:scale-[1.02] transition"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Email Pitch</span>
                        </a>
                    </div>

                    <!-- Score & Contact Cards -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-[#b8c9e622] bg-[#27374d60] p-3">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400">Quality Score</span>
                            <p class="mt-1 text-lg font-black" :class="getScoreBadge(selectedLead.score).class">
                                {{ getScoreBadge(selectedLead.score).text }} / 100
                            </p>
                        </div>
                        <div class="rounded-xl border border-[#b8c9e622] bg-[#27374d60] p-3">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400">Current Stage</span>
                            <p class="mt-1 text-xs font-bold uppercase text-[#9ba7ff]">{{ selectedLead.status }}</p>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="rounded-xl border border-[#b8c9e622] bg-[#27374d60] p-4 space-y-2 text-slate-200">
                        <p><strong>Email:</strong> <a :href="`mailto:${selectedLead.email}`" class="text-[#9ba7ff] hover:underline">{{ selectedLead.email }}</a></p>
                        <p><strong>Phone:</strong> <a :href="`tel:${selectedLead.phone}`" class="text-[#9ba7ff] hover:underline">{{ selectedLead.phone }}</a></p>
                        <p><strong>Project Category:</strong> {{ selectedLead.projectTypeLabel }}</p>
                        <p><strong>Submitted:</strong> {{ selectedLead.createdAt }}</p>
                    </div>

                    <!-- Description -->
                    <div v-if="selectedLead.description">
                        <h4 class="font-bold text-white uppercase text-[10px] tracking-wider text-slate-400">Project Requirements & Scope</h4>
                        <div class="mt-2 rounded-xl border border-[#b8c9e622] bg-[#17212d] p-3.5 leading-relaxed text-slate-200 whitespace-pre-line">
                            {{ selectedLead.description }}
                        </div>
                    </div>

                    <!-- Internal Staff Notes Thread -->
                    <div>
                        <h4 class="font-bold text-white uppercase text-[10px] tracking-wider text-slate-400 mb-2">Internal Staff Notes</h4>
                        
                        <!-- Notes List -->
                        <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                            <div
                                v-for="note in leadNotes"
                                :key="note.id"
                                class="rounded-xl border border-[#b8c9e618] bg-[#243449] p-3"
                            >
                                <div class="flex items-center justify-between text-[10px] text-[#9ba7ff]">
                                    <span class="font-bold">{{ note.author_name }}</span>
                                    <span>{{ note.created_at }}</span>
                                </div>
                                <p class="mt-1 text-slate-200 leading-normal">{{ note.note }}</p>
                            </div>
                            <p v-if="!leadNotes.length" class="text-slate-500 italic text-[11px]">No internal notes added yet.</p>
                        </div>

                        <!-- Add Note Form -->
                        <form @submit.prevent="addNote" class="space-y-2">
                            <textarea
                                v-model="newNoteText"
                                rows="2"
                                placeholder="Add internal note for sales team..."
                                class="w-full rounded-xl border border-[#b8c9e633] bg-[#17212d] p-3 text-xs text-white focus:border-[#9ba7ff] focus:outline-none"
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="isSubmittingNote || !newNoteText.trim()"
                                class="w-full rounded-full bg-[linear-gradient(95deg,#7ac4ff,#9ba7ff)] py-2 text-xs font-bold text-[#1a2231] hover:brightness-110 disabled:opacity-50"
                            >
                                {{ isSubmittingNote ? 'Saving Note...' : 'Post Internal Note' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
