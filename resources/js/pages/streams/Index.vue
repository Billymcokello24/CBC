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
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
    { title: 'Academic', href: '#' },
    { title: 'Streams', href: '/streams' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>('list');
const selectedStreamIds = ref<number[]>([]);
const bulkForm = useForm<{
    stream_ids: number[];
    action: 'activate' | 'deactivate' | 'delete' | '';
}>({ stream_ids: [], action: '' });
const actionForm = useForm({});
const showFilters = ref(false);
const perPage = ref(20);
const currentPage = ref(1);

const filteredStreams = computed(() => {
    return props.streams.filter((stream) => {
        const matchesSearch =
            !searchQuery.value ||
            stream.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            stream.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus =
            selectedStatus.value === 'all' ||
            (selectedStatus.value === 'active'
                ? stream.is_active
                : !stream.is_active);
        return matchesSearch && matchesStatus;
    });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredStreams.value.length / perPage.value)),
);
const paginatedStreams = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredStreams.value.slice(start, start + perPage.value);
});
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/streams',
        {
            search: searchQuery.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
            view: selectedView.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
});
watch([selectedStatus, selectedView], () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const allSelectableStreamIds = computed(() =>
    paginatedStreams.value.map((stream) => stream.id),
);
const allSelected = computed(
    () =>
        allSelectableStreamIds.value.length > 0 &&
        allSelectableStreamIds.value.every((id) =>
            selectedStreamIds.value.includes(id),
        ),
);

const toggleAllStreams = () => {
    selectedStreamIds.value = allSelected.value
        ? []
        : [...allSelectableStreamIds.value];
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
    actionForm.patch(
        `/streams/${stream.id}/${stream.is_active ? 'deactivate' : 'activate'}`,
        { preserveScroll: true },
    );
};

const deleteStream = (stream: StreamRow) => {
    if (confirm('Are you sure you want to delete this stream?')) {
        actionForm.delete(`/streams/${stream.id}`, { preserveScroll: true });
    }
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
        <div
            v-if="showToast && flashSuccess"
            class="fixed top-8 right-8 z-50 animate-in duration-500 fade-in slide-in-from-top-4"
        >
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-6 py-4 shadow-lg dark:bg-slate-900 dark:border-emerald-500/20"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-lg shadow-emerald-100 dark:shadow-none"
                >
                    <CheckCircle2 class="h-4 w-4" />
                </div>
                <span class="text-xs font-bold text-slate-900 dark:text-white">{{
                    flashSuccess
                }}</span>
            </div>
        </div>

        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Streams</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Streams Registry
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage your school's streams and academic tracks.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        as-child
                    >
                        <Link href="/academic/allocations">
                            <Rows3 class="mr-2 h-4 w-4 opacity-40" />
                            Allocations
                        </Link>
                    </Button>
                    <Link
                        href="/streams/create"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-8 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Stream
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm"
                        >
                            <Rows3 class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Total</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Streams</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_streams }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Active</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Enabled</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.active_streams }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-orange-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <Grid2x2 class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Classes</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Nodes</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_classes }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Enrollment</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Students</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_students }}
                    </h3>
                </div>
            </div>

            <!-- Toolbar & Controls -->
            <div
                class="flex flex-col gap-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
            >
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex w-full flex-1 items-center gap-4 md:max-w-4xl">
                        <div class="relative flex-1">
                            <Search class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search streams by name or code..."
                                class="h-11 rounded-xl border-border bg-muted/20 pl-11 text-sm font-medium transition-all focus:bg-background"
                            />
                        </div>

                        <div class="flex items-center rounded-xl border border-border bg-muted/20 p-1">
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-9 w-9 rounded-lg p-0 transition-all',
                                    selectedView === 'grid'
                                        ? 'bg-background text-foreground shadow-sm border border-border/50'
                                        : 'text-muted-foreground/40 hover:text-foreground',
                                ]"
                                @click="selectedView = 'grid'"
                            >
                                <Grid2x2 class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-9 w-9 rounded-lg p-0 transition-all',
                                    selectedView === 'list'
                                        ? 'bg-background text-foreground shadow-sm border border-border/50'
                                        : 'text-muted-foreground/40 hover:text-foreground',
                                ]"
                                @click="selectedView = 'list'"
                            >
                                <List class="h-4 w-4" />
                            </Button>
                        </div>

                        <Button
                            variant="outline"
                            class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase"
                            @click="showFilters = !showFilters"
                        >
                            <Filter class="mr-2 h-4 w-4 opacity-40" />
                            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                        </Button>
                    </div>

                    <div class="ml-auto flex items-center gap-3">
                        <div v-if="selectedStreamIds.length > 0" class="animate-in slide-in-from-right-4 duration-300">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button class="h-11 rounded-xl bg-foreground px-6 text-xs font-bold tracking-tight text-background uppercase shadow-xl hover:bg-foreground/90">
                                        Bulk Actions ({{ selectedStreamIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 rounded-xl border-border p-2 shadow-lg">
                                    <DropdownMenuItem @click="runBulkAction('activate')" class="rounded-lg py-2.5 text-xs font-bold">
                                        <CheckCircle2 class="mr-3 h-4 w-4 text-emerald-500" />
                                        Activate
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="runBulkAction('deactivate')" class="rounded-lg py-2.5 text-xs font-bold">
                                        <ShieldOff class="mr-3 h-4 w-4 text-amber-500" />
                                        Deactivate
                                    </DropdownMenuItem>
                                    <div class="my-1 border-t border-border/50"></div>
                                    <DropdownMenuItem @click="runBulkAction('delete')" class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                        <Trash2 class="mr-3 h-4 w-4" />
                                        Delete Forever
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div v-if="showFilters" class="grid animate-in gap-6 border-t border-border/50 pt-6 md:grid-cols-4 duration-500 fade-in slide-in-from-top-2">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold tracking-tight text-muted-foreground uppercase">Status</label>
                        <select
                            v-model="selectedStatus"
                            class="h-11 w-full rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold tracking-tight transition-all outline-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div
                v-if="selectedView === 'grid'"
                class="animate-in space-y-6 duration-700 fade-in slide-in-from-bottom-4"
            >
                <div class="flex items-center gap-3 px-2">
                    <button
                        @click="toggleAllStreams"
                        class="flex h-5 w-5 items-center justify-center rounded-lg border-2 transition-all"
                        :class="allSelected ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : 'border-border bg-muted/20'"
                    >
                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[3px]" />
                        <Minus v-else-if="selectedStreamIds.length > 0" class="h-3.5 w-3.5 stroke-[3px] text-primary" />
                    </button>
                    <span class="text-xs font-bold tracking-tight text-muted-foreground uppercase">Select All Streams</span>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="stream in paginatedStreams"
                        :key="stream.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:scale-[1.01] hover:shadow-lg dark:border-white/5"
                    >
                        <div class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-primary/5 blur-3xl transition-all group-hover:bg-primary/10"></div>
                        
                        <div class="relative z-10 mb-6 flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <button
                                    @click="toggleStream(stream.id)"
                                    class="mt-1 flex h-5 w-5 items-center justify-center rounded-lg border-2 transition-all"
                                    :class="selectedStreamIds.includes(stream.id) ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : 'border-border bg-muted/20'"
                                >
                                    <Check v-if="selectedStreamIds.includes(stream.id)" class="h-3.5 w-3.5 stroke-[3px]" />
                                </button>
                                <div>
                                    <h2 class="max-w-[180px] truncate text-xl font-bold tracking-tight text-foreground group-hover:text-primary transition-colors">
                                        {{ stream.name }}
                                    </h2>
                                    <p class="text-[10px] font-bold tracking-tight text-muted-foreground/60 uppercase">
                                        Code: {{ stream.code }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="h-1 w-1 rounded-full bg-muted-foreground/40"></div>
                                        <p class="text-[10px] font-bold tracking-tight text-muted-foreground/50 uppercase">
                                            {{ stream.lead_name || 'No Lead Teacher' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground">
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 rounded-xl border-border p-2 shadow-lg">
                                    <DropdownMenuItem @click="router.get(`/streams/${stream.id}`)" class="rounded-lg py-2.5 text-xs font-bold">
                                        <Eye class="mr-3 h-4 w-4 text-primary" /> View Details
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="router.get(`/streams/${stream.id}/edit`)" class="rounded-lg py-2.5 text-xs font-bold">
                                        <Edit class="mr-3 h-4 w-4 text-amber-500" /> Edit Settings
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="toggleStreamStatus(stream)" class="rounded-lg py-2.5 text-xs font-bold">
                                        <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="mr-3 h-4 w-4" :class="stream.is_active ? 'text-amber-500' : 'text-emerald-500'" />
                                        {{ stream.is_active ? 'Deactivate' : 'Activate' }}
                                    </DropdownMenuItem>
                                    <div class="my-1 border-t border-border/50"></div>
                                    <DropdownMenuItem @click="deleteStream(stream)" class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                        <Trash2 class="mr-3 h-4 w-4 text-rose-500" /> Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="relative z-10 mt-auto space-y-5">
                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-xl border border-border bg-muted/20 p-3 text-center transition-all group-hover:bg-background">
                                    <p class="mb-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Classes</p>
                                    <p class="text-sm font-bold text-foreground">{{ stream.classes_count }}</p>
                                </div>
                                <div class="rounded-xl border border-border bg-muted/20 p-3 text-center transition-all group-hover:bg-background">
                                    <p class="mb-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Students</p>
                                    <p class="text-sm font-bold text-primary">{{ stream.students_count }}</p>
                                </div>
                                <div class="rounded-xl border border-border bg-muted/20 p-3 text-center transition-all group-hover:bg-background">
                                    <p class="mb-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Capacity</p>
                                    <p class="text-sm font-bold text-foreground opacity-30">{{ stream.capacity ?? '∞' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <Button
                                    variant="outline"
                                    class="h-10 flex-1 rounded-xl border-border bg-background text-[10px] font-bold tracking-tight uppercase hover:bg-primary hover:text-white hover:border-primary transition-all"
                                    as-child
                                >
                                    <Link :href="`/streams/${stream.id}`">View Details</Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-10 w-10 rounded-xl transition-all"
                                    :class="stream.is_active ? 'bg-amber-500/10 text-amber-600 hover:bg-amber-500 hover:text-white' : 'bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 hover:text-white'"
                                    @click="toggleStreamStatus(stream)"
                                >
                                    <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div
                v-else
                class="animate-in overflow-hidden rounded-2xl border border-border bg-card shadow-sm duration-700 fade-in slide-in-from-bottom-4 dark:border-white/5"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/20">
                                <th class="w-16 px-6 py-4">
                                    <button
                                        @click="toggleAllStreams"
                                        class="flex h-5 w-5 items-center justify-center rounded-lg border-2 transition-all"
                                        :class="allSelected ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : 'border-border bg-muted/20'"
                                    >
                                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[3px]" />
                                        <Minus v-else-if="selectedStreamIds.length > 0" class="h-3.5 w-3.5 stroke-[3px] text-primary" />
                                    </button>
                                </th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight text-muted-foreground uppercase">Stream Detail</th>
                                <th class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase">Classes</th>
                                <th class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase">Students</th>
                                <th class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold tracking-tight text-muted-foreground uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="stream in paginatedStreams"
                                :key="stream.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStream(stream.id)"
                                        class="flex h-5 w-5 items-center justify-center rounded-lg border-2 transition-all"
                                        :class="selectedStreamIds.includes(stream.id) ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : 'border-border bg-muted/20'"
                                    >
                                        <Check v-if="selectedStreamIds.includes(stream.id)" class="h-3.5 w-3.5 stroke-[3px]" />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">{{ stream.name }}</span>
                                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/60 uppercase">{{ stream.code }} • {{ stream.lead_name || 'No Lead' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-bold text-muted-foreground">{{ stream.classes_count }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-bold text-primary">{{ stream.students_count }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="border-none px-3 py-1 text-[10px] font-bold tracking-tight uppercase"
                                        :class="stream.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'"
                                    >
                                        {{ stream.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg text-muted-foreground hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/streams/${stream.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-muted-foreground hover:bg-muted">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-xl border-border p-2 shadow-lg">
                                                <DropdownMenuItem @click="router.get(`/streams/${stream.id}`)" class="rounded-lg py-2.5 text-xs font-bold">
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="toggleStreamStatus(stream)" class="rounded-lg py-2.5 text-xs font-bold">
                                                    {{ stream.is_active ? 'Deactivate' : 'Activate' }}
                                                </DropdownMenuItem>
                                                <div class="my-1 border-t border-border/50"></div>
                                                <DropdownMenuItem @click="deleteStream(stream)" class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedStreams.length === 0">
                                <td colspan="6" class="px-6 py-20 text-center text-sm font-medium text-muted-foreground/50 uppercase tracking-widest bg-muted/5">
                                    No streams found matching your criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-border/50 bg-muted/5 px-6 py-4">
                    <p class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase">
                        Showing {{ paginatedStreams.length }} of {{ filteredStreams.length }} Streams
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                            class="h-9 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase disabled:opacity-30"
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                            class="h-9 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase disabled:opacity-30"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
