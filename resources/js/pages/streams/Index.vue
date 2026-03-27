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
    Minus,
    Users,
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

        <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-20 p-6">
            <!-- Simple Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic underline decoration-blue-600 decoration-4 underline-offset-8">Parallel Streams</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest pt-2">
                        Configure academic tracks and parallel learning pathways.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="rounded-xl h-11 px-6 border-border font-black uppercase text-[10px] tracking-widest hover:bg-muted">
                        <Rows3 class="mr-2 h-4 w-4 opacity-40" />
                        Allocation Map
                    </Button>
                    <Link
                        v-if="true"
                        href="/streams/create"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Stream
                    </Link>
                </div>
            </div>

            <!-- TailPanel Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-blue-600/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-blue-600/5 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-inner">
                            <Rows3 class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Tracks</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Total Streams</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic">{{ stats.total_streams }} Lines</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-emerald-500/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/5 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500 shadow-inner">
                            <CheckCircle2 class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Active</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Live Channels</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic">{{ stats.active_streams }} Paths</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-orange-500/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-orange-500/5 flex items-center justify-center text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-all duration-500 shadow-inner">
                            <Grid2x2 class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Cells</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Total Classes</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic">{{ stats.total_classes }} Nodes</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-indigo-600/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-indigo-600/5 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-inner">
                            <Users class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Census</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Enrolled Learners</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic">{{ stats.total_students }} Souls</h3>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div class="flex flex-col gap-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex flex-1 items-center gap-4 w-full md:max-w-4xl">
                        <div class="relative flex-1">
                            <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/60" />
                            <Input v-model="searchQuery" placeholder="Filter pathways by name or code..." class="pl-12 h-12 bg-muted/20 border-border rounded-xl text-xs font-black focus:bg-background transition-all placeholder:text-[9px] placeholder:uppercase placeholder:tracking-widest italic" />
                        </div>
                        
                        <div class="flex items-center p-1 bg-muted/20 rounded-xl border border-border">
                            <Button variant="ghost" :class="['h-10 w-10 p-0 rounded-lg transition-all', selectedView === 'grid' ? 'bg-background text-foreground shadow-sm border border-border/50' : 'text-muted-foreground/40 hover:text-foreground']" @click="selectedView = 'grid'"><Grid2x2 class="h-4 w-4" /></Button>
                            <Button variant="ghost" :class="['h-10 w-10 p-0 rounded-lg transition-all', selectedView === 'list' ? 'bg-background text-foreground shadow-sm border border-border/50' : 'text-muted-foreground/40 hover:text-foreground']" @click="selectedView = 'list'"><List class="h-4 w-4" /></Button>
                        </div>

                        <Button variant="outline" class="h-12 border-border font-black px-6 rounded-xl text-[10px] uppercase tracking-widest whitespace-nowrap italic" @click="showFilters = !showFilters">
                            <Filter class="mr-2 h-4 w-4 opacity-40" /> {{ showFilters ? 'DENSE' : 'REFINE' }}
                        </Button>
                        <Button variant="ghost" class="h-12 text-muted-foreground/40 hover:text-foreground font-black uppercase text-[10px] tracking-widest px-4 italic" @click="clearFilters">Reset</Button>
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                        <div v-if="selectedStreamIds.length > 0" class="animate-in slide-in-from-right-4 duration-300">
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button class="bg-foreground text-background hover:bg-foreground/90 font-black text-[10px] uppercase tracking-[0.2em] h-12 px-8 shadow-xl border-0 rounded-xl italic transition-all active:scale-95 shadow-foreground/20">
                                        Batch Control ({{ selectedStreamIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" @click="runBulkAction('activate')"><CheckCircle2 class="mr-3 h-4 w-4 text-emerald-500 opacity-60" /> Activate Tracks</DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" @click="runBulkAction('deactivate')"><ShieldOff class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Suspend Tracks</DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 bg-border/50" />
                                    <DropdownMenuItem class="text-rose-500 rounded-lg py-2.5 font-bold text-xs group" @click="runBulkAction('delete')"><Trash2 class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100" /> Purge Pathways</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div v-if="showFilters" class="grid gap-6 md:grid-cols-4 pt-4 border-t border-border/50 animate-in fade-in slide-in-from-top-2 duration-500">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] italic">Flow Status</label>
                        <select v-model="selectedStatus" class="h-12 w-full rounded-xl border border-border bg-muted/20 px-4 text-xs font-black focus:bg-background transition-all outline-none italic uppercase tracking-widest">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                </div>
            </div>




            <!-- Grid View -->
            <div v-if="selectedView === 'grid'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="flex items-center gap-3 px-2 py-1">
                    <button @click="toggleAllStreams" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-card dark:border-white/10" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : selectedStreamIds.length > 0 ? 'border-blue-600 bg-blue-500/10 text-blue-600' : 'border-border'">
                        <Check v-if="allSelected" class="h-4 w-4 stroke-[3px]" />
                        <Minus v-else-if="selectedStreamIds.length > 0" class="h-4 w-4 stroke-[3px]" />
                    </button>
                    <span class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] italic cursor-pointer select-none" @click="toggleAllStreams">Global Flow Sync</span>
                </div>
                
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="stream in paginatedStreams" :key="stream.id" class="group relative rounded-[2rem] border border-border bg-card p-8 transition-all duration-500 hover:shadow-2xl hover:scale-[1.02] dark:border-white/5 flex flex-col overflow-hidden">
                        <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-blue-600/5 blur-3xl group-hover:bg-blue-600/15 transition-all duration-700"></div>
                        
                        <div class="mb-8 flex items-start justify-between relative z-10">
                            <div class="flex items-start gap-5">
                                <button @click="toggleStream(stream.id)" class="mt-1 h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-background dark:border-white/10 shadow-inner" :class="selectedStreamIds.includes(stream.id) ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : 'border-border'">
                                    <Check v-if="selectedStreamIds.includes(stream.id)" class="h-4 w-4 stroke-[3px]" />
                                </button>
                                <div>
                                    <h2 class="text-2xl font-black text-foreground group-hover:text-blue-600 transition-colors leading-tight uppercase italic tracking-tighter truncate max-w-[200px]">{{ stream.name }}</h2>
                                    <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mt-2 italic">{{ stream.code }} FLOWPATH</p>
                                    <p class="text-[9px] font-bold text-muted-foreground/30 uppercase tracking-widest mt-1 italic">{{ stream.lead_name || 'NO FLOW HEAD' }}</p>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground"><MoreHorizontal class="h-5 w-5" /></Button></DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/streams/${stream.id}`"><Eye class="mr-3 h-4 w-4 text-blue-500 opacity-60" /> Flow Specs</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/streams/${stream.id}/edit`"><Edit class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Reconfigure Path</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" @click="toggleStreamStatus(stream)">
                                        <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="mr-3 h-4 w-4" :class="stream.is_active ? 'text-amber-500' : 'text-emerald-500'" />
                                        {{ stream.is_active ? 'Divert Stream' : 'Enable Stream' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 bg-border/50" />
                                    <DropdownMenuItem class="text-rose-500 font-bold rounded-lg py-2.5 text-xs group" @click="deleteStream(stream)"><Trash2 class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100" /> Purge Flow</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        
                        <div class="mt-auto space-y-6 relative z-10">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner dark:border-white/5 transition-all group-hover:bg-background">
                                    <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mb-2 italic">Cells</p>
                                    <p class="text-lg font-black text-foreground">{{ stream.classes_count }}</p>
                                </div>
                                <div class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner dark:border-white/5 transition-all group-hover:bg-background">
                                    <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mb-2 italic">Souls</p>
                                    <p class="text-lg font-black text-blue-600">{{ stream.students_count }}</p>
                                </div>
                                <div class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner dark:border-white/5 transition-all group-hover:bg-background">
                                    <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mb-2 italic">Cap</p>
                                    <p class="text-lg font-black text-foreground opacity-30">{{ stream.capacity ?? '∞' }}</p>
                                </div>
                            </div>

                            <div class="flex gap-3 h-12">
                                <Button variant="outline" class="flex-1 rounded-xl border-border font-black text-[10px] uppercase tracking-widest h-full hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all italic shadow-sm" as-child>
                                    <Link :href="`/streams/${stream.id}`">Sync</Link>
                                </Button>
                                <Button variant="outline" class="flex-1 rounded-xl border-border font-black text-[10px] uppercase tracking-widest h-full hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all italic shadow-sm" as-child>
                                    <Link :href="`/streams/${stream.id}/edit`">Mod</Link>
                                </Button>
                                <Button variant="outline" class="w-12 rounded-xl border-border transition-all flex items-center justify-center p-0 hover:bg-muted"
                                    :class="stream.is_active ? 'text-amber-600 hover:bg-amber-50 shadow-inner' : 'text-emerald-600 hover:bg-emerald-50 shadow-inner'"
                                    @click="toggleStreamStatus(stream)">
                                    <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="rounded-[2.5rem] border border-border bg-card shadow-2xl overflow-hidden dark:border-white/5 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-foreground border-b border-foreground/10 text-background">
                                <th class="w-20 px-8 py-6 text-center border-r border-background/10">
                                    <button @click="toggleAllStreams" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-background mx-auto shadow-inner" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : selectedStreamIds.length > 0 ? 'border-blue-600 bg-blue-500/10 text-blue-600' : 'border-background/20'">
                                        <Check v-if="allSelected" class="h-4 w-4 stroke-[3px]" />
                                        <Minus v-else-if="selectedStreamIds.length > 0" class="h-4 w-4 stroke-[3px]" />
                                    </button>
                                </th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Stream Intel</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Flow Head</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Cells</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Souls</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Status</th>
                                <th class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-widest opacity-80 italic">Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-if="paginatedStreams.length === 0">
                                <td colspan="7" class="px-8 py-32 text-center text-muted-foreground/20 italic font-black uppercase text-xl tracking-[0.2em] bg-muted/10">
                                    No Parallel Pathways Detected
                                </td>
                            </tr>
                            <tr v-for="stream in paginatedStreams" :key="stream.id" class="group transition-all duration-300 hover:bg-muted/30">
                                <td class="px-8 py-5 text-center">
                                    <button @click="toggleStream(stream.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-background mx-auto shadow-inner dark:border-white/10" :class="selectedStreamIds.includes(stream.id) ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : 'border-border'">
                                        <Check v-if="selectedStreamIds.includes(stream.id)" class="h-4 w-4 stroke-[3px]" />
                                    </button>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-5">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-foreground font-black group-hover:bg-foreground group-hover:text-background transition-all duration-500 shadow-inner text-sm uppercase italic border border-border/50 font-black">
                                            {{ stream.name.charAt(0) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-foreground group-hover:text-blue-600 transition-colors text-sm uppercase italic tracking-tight truncate max-w-[180px] leading-tight mb-1">{{ stream.name }}</p>
                                            <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest italic leading-none">{{ stream.code }} FLOWPATH</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-foreground uppercase italic group-hover:text-blue-600 transition-colors truncate max-w-[150px]">{{ stream.lead_name || 'NULL NODE' }}</span>
                                        <span class="text-[9px] font-black text-muted-foreground/30 uppercase italic tracking-widest mt-1">Lead Intel</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <Badge variant="outline" class="rounded-lg h-7 border-border bg-muted/30 text-foreground font-black text-[10px] px-4 uppercase italic tracking-widest group-hover:bg-foreground group-hover:text-background transition-all">{{ stream.classes_count }} Cells</Badge>
                                </td>
                                <td class="px-8 py-5 text-center font-black text-blue-600 text-sm italic tabular-nums">
                                    {{ stream.students_count }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-2 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.4)]" :class="stream.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-muted-foreground/20'"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em] italic" :class="stream.is_active ? 'text-emerald-500' : 'text-muted-foreground/30'">{{ stream.is_active ? 'INITIALIZED' : 'DIVERCHED' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 outline-none">
                                        <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl text-muted-foreground hover:bg-blue-600/10 hover:text-blue-600 transition-all opacity-0 group-hover:opacity-100 border border-transparent hover:border-blue-600/20 shadow-sm" as-child title="Core Sync">
                                            <Link :href="`/streams/${stream.id}`"><Eye class="h-4.5 w-4.5" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl text-muted-foreground hover:bg-amber-500/10 hover:text-amber-500 transition-all opacity-0 group-hover:opacity-100 border border-transparent hover:border-amber-500/20 shadow-sm" as-child title="Mod Flow">
                                            <Link :href="`/streams/${stream.id}/edit`"><Edit class="h-4.5 w-4.5" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl transition-all border border-transparent opacity-0 group-hover:opacity-100 shadow-sm" :class="stream.is_active ? 'text-amber-500 hover:bg-amber-50 border-amber-500/10' : 'text-emerald-500 hover:bg-emerald-50 border-emerald-500/10'" @click="toggleStreamStatus(stream)" :title="stream.is_active ? 'Suspend Flow' : 'Enable Flow'">
                                            <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="h-4.5 w-4.5" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all opacity-0 group-hover:opacity-100 border border-transparent hover:border-rose-500/20 shadow-sm" @click="deleteStream(stream)" title="Purge Pathway">
                                            <Trash2 class="h-4.5 w-4.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Footer -->
            <div class="flex flex-col gap-6 border-t border-border/30 bg-muted/10 px-10 py-10 md:flex-row md:items-center md:justify-between group/footer rounded-[2rem] shadow-sm relative overflow-hidden dark:border-white/5">
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-600/5 blur-3xl group-hover/footer:bg-blue-600/10 transition-all duration-1000"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-[0.2em] italic">Segments</span>
                        <select v-model="perPage" @change="currentPage = 1" class="h-10 rounded-xl border border-border bg-background px-4 text-[11px] font-black text-foreground focus:ring-2 focus:ring-blue-600 transition-all outline-none italic">
                            <option v-for="n in [10, 20, 50, 100, 500]" :key="n" :value="n">{{ n }} FLOWS</option>
                        </select>
                    </div>
                    <div class="h-10 w-px bg-border/50"></div>
                    <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.15em] italic">{{ filteredStreams.length }} Pathways active in grid</p>
                </div>

                <div v-if="totalPages > 1" class="flex items-center gap-2 relative z-10">
                    <Button variant="outline" size="sm" class="h-12 w-12 p-0 rounded-2xl border-border text-muted-foreground hover:bg-muted disabled:opacity-20 transition-all shadow-sm" :disabled="currentPage === 1" @click="currentPage = Math.max(1, currentPage - 1)">
                        <ChevronLeft class="h-5 w-5" />
                    </Button>
                    
                    <div class="flex items-center gap-2 mx-2">
                        <template v-for="p in totalPages" :key="p">
                            <Button v-if="Math.abs(p - currentPage) <= 2 || p === 1 || p === totalPages" variant="outline"
                                class="h-12 min-w-[3rem] px-4 rounded-2xl border-border font-black text-[11px] transition-all duration-300 uppercase italic tracking-tighter shadow-sm"
                                :class="currentPage === p ? 'bg-blue-600 text-white border-blue-600 shadow-blue-600/20 scale-105 shadow-inner px-6' : 'text-foreground hover:bg-muted'"
                                @click="currentPage = p; selectedStreamIds = []"
                            >
                                {{ p }}
                            </Button>
                            <span v-else-if="Math.abs(p - currentPage) === 3" class="text-muted-foreground/30 font-black px-1 opacity-50">...</span>
                        </template>
                    </div>

                    <Button variant="outline" size="sm" class="h-12 w-12 p-0 rounded-2xl border-border text-muted-foreground hover:bg-muted disabled:opacity-20 transition-all shadow-sm" :disabled="currentPage === totalPages" @click="currentPage = Math.min(totalPages, currentPage + 1)">
                        <ChevronRight class="h-5 w-5" />
                    </Button>
                </div>
            </div>

            <!-- Global Matrix Pulse -->
            <div class="p-10 rounded-[3rem] bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-10 group relative overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.3)] dark:bg-black">
                <div class="absolute -right-24 -bottom-24 opacity-5 group-hover:scale-125 transition-all duration-[2000ms] text-white font-black italic uppercase text-[12rem] select-none pointer-events-none tracking-widest">
                     PULSE
                </div>
                <div class="flex items-center gap-8 relative z-10">
                    <div class="h-20 w-20 rounded-3xl bg-white/5 flex items-center justify-center border border-white/10 group-hover:bg-blue-600 group-hover:border-blue-600 transition-all duration-700 shadow-2xl">
                         <ShieldCheck class="h-10 w-10 text-white/30 group-hover:text-white group-hover:scale-110 transition-all duration-500" />
                    </div>
                    <div>
                        <h3 class="font-black text-2xl tracking-tighter uppercase italic group-hover:text-blue-400 transition-colors duration-500">Flow Pathways Intelligence</h3>
                        <p class="text-white/40 text-[10px] mt-2 tracking-[0.2em] font-black uppercase leading-relaxed italic opacity-80 decoration-blue-500/30 underline underline-offset-4">Maintaining equilibrium across {{ $page.props.auth.school?.name || 'Campus' }} academic streams.</p>
                    </div>
                </div>
                <div class="flex gap-8 relative z-10">
                    <div class="flex flex-col items-end">
                         <span class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mb-2 opacity-60">Flow Stability</span>
                         <span class="bg-blue-600/20 text-blue-400 border border-blue-400/20 px-8 py-3 rounded-full text-[11px] font-black uppercase tracking-[0.25em] shadow-[0_0_30px_rgba(59,130,246,0.3)] animate-pulse italic">Locked & Balanced</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
