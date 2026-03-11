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
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
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
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const selectedStreamIds = ref<number[]>([]);
const bulkForm = useForm<{ stream_ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ stream_ids: [], action: '' });
const actionForm = useForm({});
const showFilters = ref(true);
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

const selectedCount = computed(() => selectedStreamIds.value.length);
const allSelected = computed(() => props.streams.length > 0 && props.streams.every((stream) => selectedStreamIds.value.includes(stream.id)));
const toggleAllStreams = () => {
    selectedStreamIds.value = allSelected.value ? [] : props.streams.map((stream) => stream.id);
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
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10">
                        <Rows3 class="h-6 w-6 text-cyan-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Streams</h1>
                        <p class="text-muted-foreground">Manage school streams and drill into the classes under each stream</p>
                    </div>
                </div>
                <Button as-child><Link href="/streams/create"><Plus class="mr-2 h-4 w-4" />Add Stream</Link></Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Streams</div><div class="mt-1 text-3xl font-bold">{{ stats.total_streams }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active Streams</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active_streams }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Classes</div><div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.total_classes }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Students</div><div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.total_students }}</div></div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search streams..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedView = 'grid'"><Grid2x2 class="mr-2 h-4 w-4" />Grid</Button>
                    <Button variant="outline" size="sm" @click="selectedView = 'list'"><List class="mr-2 h-4 w-4" />List</Button>
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters"><Filter class="mr-2 h-4 w-4" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}</Button>
                    <Button variant="outline" size="sm" @click="clearFilters">Reset</Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>

            <div v-if="selectedCount > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                <div>
                    <p class="text-sm font-medium">{{ selectedCount }} stream{{ selectedCount === 1 ? '' : 's' }} selected</p>
                    <p class="text-xs text-muted-foreground">Apply bulk actions across multiple streams at once.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedStreamIds = []">Clear</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('activate')">Activate</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('deactivate')">Deactivate</Button>
                    <Button variant="destructive" size="sm" @click="runBulkAction('delete')">Delete</Button>
                </div>
            </div>

            <div v-if="selectedView === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="stream in streams" :key="stream.id" class="rounded-xl border bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" :checked="selectedStreamIds.includes(stream.id)" @change="toggleStream(stream.id)" />
                            <div>
                                <h2 class="text-lg font-semibold">{{ stream.name }}</h2>
                                <p class="text-sm text-muted-foreground">{{ stream.code }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">Stream Lead: {{ stream.lead_name || 'Not assigned' }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child><Link :href="`/streams/${stream.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/streams/${stream.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                <DropdownMenuItem @click="toggleStreamStatus(stream)">
                                    <component :is="stream.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />
                                    {{ stream.is_active ? 'Deactivate' : 'Activate' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive" @click="deleteStream(stream)"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Classes</div><div class="mt-1 font-semibold">{{ stream.classes_count }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Students</div><div class="mt-1 font-semibold">{{ stream.students_count }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Capacity</div><div class="mt-1 font-semibold">{{ stream.capacity ?? '—' }}</div></div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <Button variant="outline" size="sm" class="flex-1" as-child><Link :href="`/streams/${stream.id}`">Open Stream</Link></Button>
                        <Button variant="outline" size="sm" class="flex-1" as-child><Link href="/classes">Classes</Link></Button>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"><input type="checkbox" :checked="allSelected" @change="toggleAllStreams" /></th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Stream</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Lead</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Classes</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Students</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stream in streams" :key="stream.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3"><input type="checkbox" :checked="selectedStreamIds.includes(stream.id)" @change="toggleStream(stream.id)" /></td>
                                <td class="px-4 py-3"><div class="font-medium">{{ stream.name }}</div><div class="text-xs text-muted-foreground">{{ stream.code }}</div></td>
                                <td class="px-4 py-3 text-sm">{{ stream.lead_name || 'Not assigned' }}</td>
                                <td class="px-4 py-3 text-sm">{{ stream.classes_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ stream.students_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ stream.is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" as-child><Link :href="`/streams/${stream.id}`">Open</Link></Button>
                                        <Button variant="outline" size="sm" as-child><Link :href="`/streams/${stream.id}/edit`">Edit</Link></Button>
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
