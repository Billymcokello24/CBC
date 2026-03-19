<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    BookCopy,
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    Grid2x2,
    List,
    MoreHorizontal,
    Plus,
    Search,
    ShieldCheck,
    ShieldOff,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface SubjectRow {
    id: number;
    learning_area_id: number;
    learning_area: string | null;
    department_id: number | null;
    department: {
        id: number;
        name: string;
        code: string;
    } | null;
    name: string;
    code: string;
    description: string | null;
    subject_type: string;
    is_examinable: boolean;
    display_order: number;
    is_active: boolean;
    strands_count: number;
}

const props = defineProps<{
    subjects: SubjectRow[];
    learningAreas: Array<{ id: number; name: string }>;
    stats: { total: number; active: number; strands: number };
    filters: { search: string; status: string; view: 'grid' | 'list' };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const page = usePage<{ flash?: { success?: string } }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/subjects' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const selectedIds = ref<number[]>([]);
const actionForm = useForm({});
const bulkForm = useForm<{ ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ ids: [], action: '' });
const showFilters = ref(true);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/curriculum/subjects', {
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
    () => props.subjects,
    () => {
        selectedIds.value = selectedIds.value.filter((id) => props.subjects.some((item) => item.id === id));
    },
    { deep: true },
);

const selectedCount = computed(() => selectedIds.value.length);
const allSelected = computed(() => props.subjects.length > 0 && props.subjects.every((item) => selectedIds.value.includes(item.id)));
const toggleAll = () => { selectedIds.value = allSelected.value ? [] : props.subjects.map((item) => item.id); };
const toggleOne = (id: number) => { selectedIds.value = selectedIds.value.includes(id) ? selectedIds.value.filter((value) => value !== id) : [...selectedIds.value, id]; };
const clearFilters = () => { searchQuery.value = ''; selectedStatus.value = 'all'; applyFilters(); };

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedIds.value.length) return;
    bulkForm.ids = [...selectedIds.value];
    bulkForm.action = action;
    bulkForm.post('/curriculum/subjects/bulk-action', {
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
};

const toggleStatus = (item: SubjectRow) => {
    actionForm.transform(() => ({
        learning_area_id: item.learning_area_id,
        name: item.name,
        code: item.code,
        description: item.description,
        subject_type: item.subject_type,
        is_examinable: item.is_examinable,
        display_order: item.display_order,
        is_active: !item.is_active,
    })).put(`/curriculum/subjects/${item.id}`, { preserveScroll: true });
};

const deleteItem = (item: SubjectRow) => {
    actionForm.delete(`/curriculum/subjects/${item.id}`, { preserveScroll: true });
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
    <Head title="Subjects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50 rounded-lg border bg-background px-4 py-3 shadow-lg">
            <div class="flex items-center gap-2 text-sm font-medium"><CheckCircle2 class="h-4 w-4 text-green-600" />{{ flashSuccess }}</div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10"><BookCopy class="h-6 w-6 text-blue-600" /></div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Subjects</h1>
                        <p class="text-muted-foreground">Curriculum subjects linked to learning areas and strands</p>
                    </div>
                </div>
                <Button as-child><Link href="/curriculum/subjects/create"><Plus class="mr-2 h-4 w-4" />Add Subject</Link></Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Subjects</div><div class="mt-1 text-3xl font-bold">{{ stats.total }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Strands</div><div class="mt-1 text-3xl font-bold text-purple-600">{{ stats.strands }}</div></div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm"><Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" /><Input v-model="searchQuery" placeholder="Search subjects..." class="pl-9" /></div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedView = 'grid'"><Grid2x2 class="mr-2 h-4 w-4" />Grid</Button>
                    <Button variant="outline" size="sm" @click="selectedView = 'list'"><List class="mr-2 h-4 w-4" />List</Button>
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters"><Filter class="mr-2 h-4 w-4" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}</Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2">
                <div class="space-y-2"><label class="text-sm font-medium">Status</label><select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option></select></div>
                <div class="flex items-end"><Button variant="outline" size="sm" @click="clearFilters">Reset Filters</Button></div>
            </div>

            <div v-if="selectedCount > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                <div><p class="text-sm font-medium">{{ selectedCount }} selected</p></div>
                <div class="flex gap-2"><Button variant="outline" size="sm" @click="runBulkAction('activate')">Activate</Button><Button variant="outline" size="sm" @click="runBulkAction('deactivate')">Deactivate</Button><Button variant="destructive" size="sm" @click="runBulkAction('delete')">Delete</Button></div>
            </div>

            <div v-if="selectedView === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="subject in subjects" :key="subject.id" class="rounded-xl border bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" :checked="selectedIds.includes(subject.id)" @change="toggleOne(subject.id)" />
                            <div>
                                <h2 class="text-lg font-semibold">{{ subject.name }}</h2>
                                <p class="text-sm text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || '—' }}</p>
                                <div v-if="subject.department" class="mt-1">
                                    <Badge variant="outline" class="text-[10px] h-4 bg-indigo-50 text-indigo-700 border-indigo-200">
                                        {{ subject.department.name }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/allocate`"><BookCopy class="mr-2 h-4 w-4" />Allocate</Link></DropdownMenuItem>
                                <DropdownMenuItem @click="toggleStatus(subject)"><component :is="subject.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />{{ subject.is_active ? 'Deactivate' : 'Activate' }}</DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive" @click="deleteItem(subject)"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Type</div><div class="mt-1 font-semibold">{{ subject.subject_type }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Strands</div><div class="mt-1 font-semibold">{{ subject.strands_count }}</div></div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"><input type="checkbox" :checked="allSelected" @change="toggleAll" /></th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Subject</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Learning Area</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Type</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Strands</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="subjects.length === 0"><td colspan="7" class="px-4 py-10 text-center text-sm text-muted-foreground">No subjects available yet.</td></tr>
                            <tr v-for="subject in subjects" :key="subject.id" class="border-b">
                                <td class="px-4 py-3"><input type="checkbox" :checked="selectedIds.includes(subject.id)" @change="toggleOne(subject.id)" /></td>
                                <td class="px-4 py-3"><div class="font-medium">{{ subject.name }}</div><div class="text-xs text-muted-foreground">{{ subject.code }}</div></td>
                                <td class="px-4 py-3 text-sm">
                                    <div>{{ subject.learning_area || '—' }}</div>
                                    <div v-if="subject.department" class="text-[10px] text-indigo-600 font-medium uppercase tracking-wider">
                                        {{ subject.department.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ subject.subject_type }}</td>
                                <td class="px-4 py-3 text-sm">{{ subject.strands_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ subject.is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/allocate`"><BookCopy class="mr-2 h-4 w-4" />Allocate</Link></DropdownMenuItem>
                                            <DropdownMenuItem @click="toggleStatus(subject)"><component :is="subject.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />{{ subject.is_active ? 'Deactivate' : 'Activate' }}</DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive" @click="deleteItem(subject)"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
