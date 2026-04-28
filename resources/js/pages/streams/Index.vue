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
    Home,
    SearchCode,
    Activity,
    Database,
    Zap,
    Building2,
    TrendingUp,
    LayoutGrid,
    FileText,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Academic Streams', href: '/streams' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');

const applyFilters = () => {
    router.get(
        '/streams',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            view: selectedView.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch([selectedStatus, selectedView], () => applyFilters());

const getStatusColor = (active: boolean) => {
    return active 
        ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
        : 'bg-slate-400 text-white shadow-lg shadow-slate-400/20';
};

const downloadPdf = () => {
    const params = new URLSearchParams({
        search: searchQuery.value,
        status: selectedStatus.value,
    });
    window.location.href = `/streams/export-pdf?${params.toString()}`;
};
</script>

<template>
    <Head title="Academic Infrastructure Matrix" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">Infrastructure matrix</h1>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Architecting {{ stats.total_streams }} Operational Channels</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                     <div class="flex items-center gap-1 rounded-2xl border border-border bg-card p-1 shadow-sm">
                        <Button variant="ghost" size="icon" :class="selectedView === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'grid'" class="h-10 w-10 rounded-xl transition-all">
                            <LayoutGrid class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" :class="selectedView === 'list' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'list'" class="h-10 w-10 rounded-xl transition-all">
                            <Rows3 class="h-4 w-4" />
                        </Button>
                    </div>
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="downloadPdf"
                    >
                        <FileText class="mr-2 h-4 w-4 text-rose-500" />
                        Download PDF
                    </Button>
                    <Button as-child class="h-10 rounded-xl bg-primary px-6 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <Link href="/streams/create">
                            <Plus class="mr-2.5 h-4 w-4" />
                            Add New Stream
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Global Capacity Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Global Channels', val: stats.total_streams, sub: 'Infrastructure Units', icon: Building2 },
                    { label: 'Active Pipeline', val: stats.active_streams, sub: 'Operational Nodes', icon: Activity },
                    { label: 'Population Density', val: stats.total_students, sub: 'Synchronized Assets', icon: Users },
                    { label: 'Node Load', val: stats.total_classes, sub: 'Class Assignments', icon: Database }
                ]" :key="idx" class="group relative overflow-hidden rounded-3xl border border-border bg-card p-6 transition-all hover:border-primary/20">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight text-foreground">{{ stat.val }}</h3>
                        <span class="text-[9px] font-bold text-primary uppercase opacity-60">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Intelligent Filters Hub -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm">
                <div class="flex h-14 items-center justify-between border-b border-border/50 bg-muted/10 px-8">
                    <div class="flex items-center gap-3">
                        <SearchCode class="h-4 w-4 text-primary" />
                        <span class="text-[10px] font-bold tracking-widest text-foreground uppercase">Infrastructure Filter Protocol</span>
                    </div>
                </div>
                <div class="p-8">
                     <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Channel Identification</Label>
                            <div class="relative">
                                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                <Input v-model="searchQuery" placeholder="Channel code or name..." class="h-12 rounded-xl border-border bg-muted/20 pl-11 pr-4 text-xs font-bold uppercase focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Operational Status</Label>
                             <div class="relative">
                                <select v-model="selectedStatus" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">Global Status</option>
                                    <option value="active">Active Nodes</option>
                                    <option value="inactive">Dormant Nodes</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="flex items-end">
                            <Button variant="ghost" class="h-12 w-full rounded-xl text-[10px] font-bold tracking-widest uppercase hover:bg-muted" @click="searchQuery = ''; selectedStatus = 'all'">
                                Reset Parameters
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matrix Display -->
            <div v-if="selectedView === 'grid'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                 <div v-for="stream in streams" :key="stream.id" class="group relative overflow-hidden rounded-[2rem] border border-border bg-card p-8 transition-all hover:border-primary/20 hover:scale-[1.02] hover:shadow-2xl hover:shadow-primary/5">
                    <div class="flex flex-col h-full gap-6">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <h3 class="text-lg font-black tracking-tight text-foreground uppercase group-hover:text-primary transition-colors">{{ stream.name }}</h3>
                                <p class="text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase opacity-50">{{ stream.code }}</p>
                            </div>
                            <Badge :class="getStatusColor(stream.is_active)" class="rounded-lg border-0 px-2.5 py-1 text-[8px] font-bold tracking-[0.2em] uppercase">
                                {{ stream.is_active ? 'ACTIVE' : 'DORMANT' }}
                            </Badge>
                        </div>

                        <div class="grid grid-cols-2 gap-4 rounded-2xl border border-border/50 bg-muted/10 p-4">
                            <div class="space-y-0.5">
                                <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Population</p>
                                <p class="text-xs font-black text-foreground uppercase">{{ stream.students_count }} Assets</p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Density</p>
                                <p class="text-xs font-black text-foreground uppercase">{{ Math.round((stream.students_count / (stream.capacity || 1)) * 100) }}% Load</p>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-4">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <Users class="h-4 w-4 text-primary" />
                                </div>
                                <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-60">{{ stream.lead_name || 'No Lead' }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm"><Link :href="`/streams/${stream.id}`"><Eye class="h-4 w-4" /></Link></Button>
                                <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm"><Link :href="`/streams/${stream.id}/edit`"><Edit class="h-4 w-4" /></Link></Button>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>

            <!-- List Matrix -->
            <div v-else class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                 <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/10 text-[10px] font-bold tracking-widest text-muted-foreground uppercase">
                                <th class="px-8 py-5">Academic Channel</th>
                                <th class="px-6 py-5">Population Node</th>
                                <th class="px-6 py-5">Capacity Load</th>
                                <th class="px-6 py-5">Strategic Lead</th>
                                <th class="px-8 py-5 text-right font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="stream in streams" :key="stream.id" class="group transition-all hover:bg-muted/30">
                                <td class="px-8 py-5">
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-black tracking-tight text-foreground group-hover:text-primary transition-colors uppercase">{{ stream.name }}</p>
                                        <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">{{ stream.code }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-xs font-black text-foreground uppercase tracking-tight">{{ stream.students_count }} Synchronized Assets</td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1 h-1.5 rounded-full bg-muted overflow-hidden">
                                            <div :style="{ width: `${Math.min((stream.students_count / (stream.capacity || 1)) * 100, 100)}%` }" class="h-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.4)]"></div>
                                        </div>
                                        <span class="text-[9px] font-black text-muted-foreground uppercase opacity-60">{{ stream.capacity || 'Inf' }} Cap</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-xs font-black text-foreground uppercase opacity-70 tracking-tight">{{ stream.lead_name || '--' }}</td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm"><Link :href="`/streams/${stream.id}`"><Eye class="h-4 w-4" /></Link></Button>
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm"><Link :href="`/streams/${stream.id}/edit`"><Edit class="h-4 w-4" /></Link></Button>
                                         <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-2xl border-border bg-card p-2 shadow-2xl">
                                                <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-muted-foreground uppercase focus:bg-muted">
                                                    <Activity class="mr-3 h-4 w-4" />
                                                    Node Health
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 border-border/50" />
                                                <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-rose-600 uppercase focus:bg-rose-50" @click="() => router.delete(`/streams/${stream.id}`)">
                                                    <Trash2 class="mr-3 h-4 w-4" />
                                                    Purge Channel
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
