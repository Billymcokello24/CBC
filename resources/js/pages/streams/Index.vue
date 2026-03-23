<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    Grid2x2,
    List,
    MoreHorizontal,
    Plus,
    Rows3,
    Search,
    ShieldCheck,
    ShieldOff,
    Trash2,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    Check,
    Square,
    Minus,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface StreamRow {
    id: number;
    name: string;
    code: string;
    capacity: number | null;
    is_active: boolean;
    classes_count: number;
    students_count: number;
    lead_name: string | null;
}

const props = defineProps<{
    streams: StreamRow[];
    stats: {
        total_streams: number;
        active_streams: number;
        total_classes: number;
        total_students: number;
    };
    filters: {
        search: string;
        status: string;
        view: 'grid' | 'list';
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const page = usePage<{ flash?: { success?: string } }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Streams', href: '/streams' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>('list');
const selectedStreamIds = ref<number[]>([]);
const bulkForm = useForm<{ stream_ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ stream_ids: [], action: '' });
const actionForm = useForm({});
const showFilters = ref(true);
const perPage = ref(20);
const currentPage = ref(1);

const filteredStreams = computed(() => {
    return props.streams.filter(stream => {
        const matchesSearch = !searchQuery.value || 
            stream.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            stream.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === 'all' || 
            (selectedStatus.value === 'active' ? stream.is_active : !stream.is_active);
        return matchesSearch && matchesStatus;
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredStreams.value.length / perPage.value)));
const paginatedStreams = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredStreams.value.slice(start, start + perPage.value);
});
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/streams', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        view: selectedView.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
});
watch([selectedStatus, selectedView], () => applyFilters());
watch(
    () => props.streams,
    () => {
        selectedStreamIds.value = selectedStreamIds.value.filter((id) => props.streams.some((stream) => stream.id === id));
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const allSelectableStreamIds = computed(() => paginatedStreams.value.map((stream) => stream.id));
const allSelected = computed(() => allSelectableStreamIds.value.length > 0 && allSelectableStreamIds.value.every((id) => selectedStreamIds.value.includes(id)));

const toggleAllStreams = () => {
    selectedStreamIds.value = allSelected.value ? [] : [...allSelectableStreamIds.value];
};

const toggleStream = (streamId: number) => {
    selectedStreamIds.value = selectedStreamIds.value.includes(streamId)
        ? selectedStreamIds.value.filter((id) => id !== streamId)
        : [...selectedStreamIds.value, streamId];
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedStreamIds.value.length) return;
    bulkForm.stream_ids = [...selectedStreamIds.value];
    bulkForm.action = action;
    bulkForm.post('/streams/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedStreamIds.value = [];
        },
    });
};

const toggleStreamStatus = (stream: StreamRow) => {
    actionForm.patch(`/streams/${stream.id}/${stream.is_active ? 'deactivate' : 'activate'}`, { preserveScroll: true });
};

const deleteStream = (stream: StreamRow) => {
    actionForm.delete(`/streams/${stream.id}`, { preserveScroll: true });
};

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => (showToast.value = false), 3000);
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Streams" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-8 top-8 z-50 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-6 py-4 shadow-2xl">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-lg shadow-emerald-100">
                    <CheckCircle2 class="h-4 w-4" />
                </div>
                <span class="text-xs font-bold text-slate-900">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="w-full py-12 px-4 sm:px-6 lg:px-8 space-y-12 animate-in fade-in duration-500">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 shadow-lg text-white">
                        <Rows3 class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Streams</h1>
                            <Badge variant="secondary" class="rounded-full px-3 py-0.5 h-5 text-[10px] font-bold bg-blue-50 text-blue-600 border-none uppercase">School Structure</Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Manage academic streams and parallel learning paths.</p>
                    </div>
                </div>
                </div>

            <!-- Stats Bar -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Streams</p>
                        <Rows3 class="h-4 w-4 text-slate-400" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.total_streams }}</p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active Streams</p>
                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.active_streams }}</p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Classes</p>
                        <School class="h-4 w-4 text-blue-500" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.total_classes }}</p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Learners</p>
                        <Users class="h-4 w-4 text-indigo-500" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.total_students }}</p>
                </div>
            </div>

            <!-- Operational Controls -->
            <div class="flex flex-col gap-4 rounded-2xl border bg-white p-4 shadow-sm md:flex-row md:items-center">
                <div class="flex flex-1 items-center gap-2">
                    <div class="relative flex-1 max-w-md">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchQuery" placeholder="Search streams by name or code..." class="pl-9 h-11 border-slate-200 text-sm rounded-xl" />
                    </div>
                    
                    <div class="flex items-center p-1 bg-slate-100 rounded-xl">
                        <Button variant="ghost" class="h-9 w-9 p-0 rounded-lg transition-all" :class="selectedView === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'" @click="selectedView = 'grid'"><Grid2x2 class="h-4 w-4" /></Button>
                        <Button variant="ghost" class="h-9 w-9 p-0 rounded-lg transition-all" :class="selectedView === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'" @click="selectedView = 'list'"><List class="h-4 w-4" /></Button>
                    </div>

                    <Button variant="outline" class="h-11 rounded-xl border-slate-200 text-xs font-medium px-4 hover:bg-slate-50 transition-all" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4 text-blue-500" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button variant="ghost" class="h-11 text-slate-400 hover:text-slate-600 font-bold uppercase text-[10px] tracking-widest px-4" @click="clearFilters">Reset</Button>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <Button as-child class="bg-blue-600 hover:bg-blue-700 shadow-lg border-0 h-11 px-6 rounded-xl text-white font-bold text-[10px] uppercase tracking-widest transition-all">
                        <Link href="/streams/create"><Plus class="mr-1 h-3.5 w-3.5" />Add Stream</Link>
                    </Button>

                    <div v-if="selectedStreamIds.length > 0" class="animate-in slide-in-from-right-4 duration-300">
                         <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button class="bg-blue-600 text-white hover:bg-blue-700 font-bold text-[10px] uppercase tracking-widest h-11 px-6 shadow-lg border-0 rounded-xl">
                                    Batch Actions ({{ selectedStreamIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 font-pulsar">
                                <DropdownMenuItem @click="runBulkAction('activate')"><CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" /> Activate Selected</DropdownMenuItem>
                                <DropdownMenuItem @click="runBulkAction('deactivate')"><ShieldOff class="mr-2 h-4 w-4 text-amber-500" /> Deactivate Selected</DropdownMenuItem>
                                <DropdownMenuItem class="text-rose-600 font-bold" @click="runBulkAction('delete')"><Trash2 class="mr-2 h-4 w-4" /> Delete Selected</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-6 animate-in slide-in-from-top-4 duration-300 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Stream Status</label>
                    <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-xs font-bold focus:ring-blue-600 transition-all outline-none shadow-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>




            <!-- Main Workspace -->
            <div v-if="selectedView === 'grid'" class="space-y-4">
                <div class="flex items-center gap-3 px-2 py-1">
                    <button @click="toggleAllStreams" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedStreamIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                        <Minus v-else-if="selectedStreamIds.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                    </button>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer select-none" @click="toggleAllStreams">Select All on Page</span>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="stream in paginatedStreams" :key="stream.id" class="group relative overflow-hidden rounded-2xl border bg-white p-6 transition-all hover:shadow-lg border-slate-100 animate-in zoom-in-95 duration-300">
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div class="flex items-start gap-4">
                            <div class="pt-1">
                                <button @click="toggleStream(stream.id)" class="mt-1 h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="selectedStreamIds.includes(stream.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                    <Check v-if="selectedStreamIds.includes(stream.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                                </button>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-tight uppercase truncate max-w-[180px]">{{ stream.name }}</h2>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ stream.code }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/streams/${stream.id}`"><Eye class="mr-2 h-4 w-4 text-blue-500" /> View Details</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/streams/${stream.id}/edit`"><Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit Stream</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" @click="toggleStreamStatus(stream)">
                                    <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" :class="stream.is_active ? 'text-amber-500' : 'text-emerald-500'" />
                                    {{ stream.is_active ? 'Deactivate' : 'Activate' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-rose-600 font-bold rounded-lg" @click="deleteStream(stream)"><Trash2 class="mr-2 h-4 w-4" /> Delete Stream</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-slate-50/50 rounded-xl border border-slate-100 mb-6 group-hover:bg-blue-50/30 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-[10px] font-bold text-blue-600 shadow-sm">{{ stream.lead_name?.charAt(0) || '?' }}</div>
                        <div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1">Stream Head</p>
                            <p class="text-[10px] font-bold text-slate-900 uppercase truncate leading-none pt-0.5">{{ stream.lead_name || 'Not Assigned' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Classes</p>
                            <p class="text-xs font-bold text-slate-900">{{ stream.classes_count }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Learners</p>
                            <p class="text-xs font-bold text-blue-600">{{ stream.students_count }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Capacity</p>
                            <p class="text-xs font-bold text-slate-600">{{ stream.capacity ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="flex gap-1.5 flex-wrap">
                        <Button variant="outline" size="sm" class="flex-1 h-8 rounded-xl border-slate-200 text-[10px] font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all" as-child>
                            <Link :href="`/streams/${stream.id}`"><Eye class="mr-1 h-3 w-3" />View</Link>
                        </Button>
                        <Button variant="outline" size="sm" class="flex-1 h-8 rounded-xl border-slate-200 text-[10px] font-bold hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all" as-child>
                            <Link :href="`/streams/${stream.id}/edit`"><Edit class="mr-1 h-3 w-3" />Edit</Link>
                        </Button>
                        <Button variant="outline" size="sm" class="h-8 rounded-xl border-slate-200 text-[10px] font-bold transition-all px-2"
                            :class="stream.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50'"
                            @click="toggleStreamStatus(stream)">
                            <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="h-3 w-3" />
                        </Button>
                        <Button variant="outline" size="sm" class="h-8 rounded-xl border-slate-200 text-rose-600 hover:bg-rose-50 transition-all px-2" @click="deleteStream(stream)">
                            <Trash2 class="h-3 w-3" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
            
        <div v-else class="rounded-2xl border bg-white overflow-hidden shadow-sm border-slate-100 animate-in fade-in duration-500">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-slate-50/50">
                                <th class="w-16 px-6 py-5 text-center">
                                    <button @click="toggleAllStreams" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedStreamIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                                        <Minus v-else-if="selectedStreamIds.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                                    </button>
                                </th>
                                <th class="px-6 py-5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Stream Name</th>
                                <th class="px-6 py-5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Stream Head</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">Classes</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">Learners</th>
                                <th class="px-6 py-5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-6 py-5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="paginatedStreams.length === 0">
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="rounded-full bg-slate-50 p-6 shadow-inner"><Rows3 class="h-10 w-10 text-slate-200" /></div>
                                        <p class="text-sm font-medium text-slate-400">No streams found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="stream in paginatedStreams" :key="stream.id" class="group transition-all hover:bg-slate-50/50">
                                <td class="px-6 py-5 text-center">
                                    <button @click="toggleStream(stream.id)" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedStreamIds.includes(stream.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-200'">
                                        <Check v-if="selectedStreamIds.includes(stream.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                                    </button>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-900 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all">{{ stream.name.charAt(0) }}</div>
                                        <div>
                                            <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors mb-0.5 uppercase">{{ stream.name }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ stream.code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-slate-600 uppercase">{{ stream.lead_name || 'Not Assigned' }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <Badge variant="outline" class="rounded-lg h-6 border-slate-200 text-slate-900 font-bold text-[10px] px-3 uppercase bg-white">{{ stream.classes_count }} Classes</Badge>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-xs font-bold text-blue-600">{{ stream.students_count }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 rounded-full" :class="stream.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider" :class="stream.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ stream.is_active ? 'Active' : 'Inactive' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end items-center gap-1">
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" as-child title="View Details">
                                            <Link :href="`/streams/${stream.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" as-child title="Edit Stream">
                                            <Link :href="`/streams/${stream.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="stream.is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50'" @click="toggleStreamStatus(stream)" :title="stream.is_active ? 'Deactivate' : 'Activate'">
                                            <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-rose-400 hover:bg-rose-50 transition-all" @click="deleteStream(stream)" title="Delete Stream">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Footer -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-semibold">Show</span>
                    <select v-model="perPage" @change="currentPage = 1" class="h-9 rounded-xl border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option v-for="n in [10, 20, 50, 100, 500]" :key="n" :value="n">{{ n }}</option>
                    </select>
                    <span class="text-xs text-slate-400 font-semibold">/ page · {{ filteredStreams.length }} total</span>
                </div>
                <div v-if="totalPages > 1" class="flex items-center gap-1">
                    <Button variant="outline" size="sm" class="h-8 w-8 p-0 rounded-xl border-slate-200 text-slate-500 hover:bg-blue-50 disabled:opacity-40" :disabled="currentPage === 1" @click="currentPage = Math.max(1, currentPage - 1)">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <template v-for="p in totalPages" :key="p">
                        <Button v-if="Math.abs(p - currentPage) <= 2 || p === 1 || p === totalPages" variant="outline" size="sm"
                            class="h-8 w-8 p-0 rounded-xl border-slate-200 text-xs font-bold transition-all"
                            :class="currentPage === p ? 'bg-blue-600 text-white border-blue-600' : 'text-slate-600 hover:bg-blue-50'"
                            @click="currentPage = p; selectedStreamIds = []"
                        >{{ p }}</Button>
                    </template>
                    <Button variant="outline" size="sm" class="h-8 w-8 p-0 rounded-xl border-slate-200 text-slate-500 hover:bg-blue-50 disabled:opacity-40" :disabled="currentPage === totalPages" @click="currentPage = Math.min(totalPages, currentPage + 1)">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
