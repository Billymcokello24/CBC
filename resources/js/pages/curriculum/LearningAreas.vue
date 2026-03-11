<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    BookOpenCheck,
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

interface LearningAreaRow {
    id: number;
    name: string;
    code: string;
    description: string | null;
    category: string | null;
    display_order: number;
    is_active: boolean;
    subjects_count: number;
}

const props = defineProps<{
    areas: LearningAreaRow[];
    stats: {
        total: number;
        active: number;
        subjects: number;
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
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const selectedIds = ref<number[]>([]);
const actionForm = useForm({});
const bulkForm = useForm<{ ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ ids: [], action: '' });
const showFilters = ref(true);
const showToast = ref(false);
const confirmOpen = ref(false);
const confirmMode = ref<'activate' | 'deactivate' | 'delete'>('delete');
const selectedItem = ref<LearningAreaRow | null>(null);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/curriculum/learning-areas',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
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
watch(
    () => props.areas,
    () => {
        selectedIds.value = selectedIds.value.filter((id) => props.areas.some((item) => item.id === id));
    },
    { deep: true },
);

const allSelected = computed(() => props.areas.length > 0 && props.areas.every((item) => selectedIds.value.includes(item.id)));
const selectedCount = computed(() => selectedIds.value.length);

const toggleAll = () => {
    selectedIds.value = allSelected.value ? [] : props.areas.map((item) => item.id);
};

const toggleOne = (id: number) => {
    selectedIds.value = selectedIds.value.includes(id)
        ? selectedIds.value.filter((value) => value !== id)
        : [...selectedIds.value, id];
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedIds.value.length) return;
    bulkForm.ids = [...selectedIds.value];
    bulkForm.action = action;
    bulkForm.post('/curriculum/learning-areas/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
        },
    });
};

const toggleStatus = (item: LearningAreaRow) => {
    selectedItem.value = item;
    confirmMode.value = item.is_active ? 'deactivate' : 'activate';
    confirmOpen.value = true;
};

const deleteItem = (item: LearningAreaRow) => {
    selectedItem.value = item;
    confirmMode.value = 'delete';
    confirmOpen.value = true;
};

const confirmAction = () => {
    if (!selectedItem.value) return;

    if (confirmMode.value === 'delete') {
        actionForm.delete(`/curriculum/learning-areas/${selectedItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                confirmOpen.value = false;
                selectedItem.value = null;
            },
        });
        return;
    }

    actionForm.transform(() => ({
        name: selectedItem.value?.name,
        code: selectedItem.value?.code,
        description: selectedItem.value?.description,
        category: selectedItem.value?.category,
        display_order: selectedItem.value?.display_order,
        is_active: confirmMode.value === 'activate',
    })).put(`/curriculum/learning-areas/${selectedItem.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            confirmOpen.value = false;
            selectedItem.value = null;
        },
    });
};

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            showToast.value = false;
        }, 3000);
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Learning Areas" />

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
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                        <BookOpenCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Learning Areas</h1>
                        <p class="text-muted-foreground">Manage CBC learning areas and organize subject groupings</p>
                    </div>
                </div>
                <Button as-child>
                    <Link href="/curriculum/learning-areas/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Learning Area
                    </Link>
                </Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Total</div>
                    <div class="mt-1 text-3xl font-bold">{{ stats.total }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Subjects</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.subjects }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search learning areas..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedView = 'grid'">
                        <Grid2x2 class="mr-2 h-4 w-4" />Grid
                    </Button>
                    <Button variant="outline" size="sm" @click="selectedView = 'list'">
                        <List class="mr-2 h-4 w-4" />List
                    </Button>
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <Button variant="outline" size="sm" @click="clearFilters">Reset Filters</Button>
                </div>
            </div>

            <div v-if="selectedCount > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                <div>
                    <p class="text-sm font-medium">{{ selectedCount }} selected</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="runBulkAction('activate')">Activate</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('deactivate')">Deactivate</Button>
                    <Button variant="destructive" size="sm" @click="runBulkAction('delete')">Delete</Button>
                </div>
            </div>

            <div v-if="selectedView === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="item in areas" :key="item.id" class="rounded-xl border bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="toggleOne(item.id)" />
                            <div>
                                <h2 class="text-lg font-semibold">{{ item.name }}</h2>
                                <p class="text-sm text-muted-foreground">{{ item.code }} • {{ item.category || 'General' }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child><Link :href="`/curriculum/learning-areas/${item.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/curriculum/learning-areas/${item.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                <DropdownMenuItem @click="toggleStatus(item)">
                                    <component :is="item.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />
                                    {{ item.is_active ? 'Deactivate' : 'Activate' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive" @click="deleteItem(item)">
                                    <Trash2 class="mr-2 h-4 w-4" />Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">Subjects</div>
                            <div class="mt-1 font-semibold">{{ item.subjects_count }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">Order</div>
                            <div class="mt-1 font-semibold">{{ item.display_order }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left"><input type="checkbox" :checked="allSelected" @change="toggleAll" /></th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Area</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Subjects</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in areas" :key="item.id" class="border-b">
                                <td class="px-4 py-3"><input type="checkbox" :checked="selectedIds.includes(item.id)" @change="toggleOne(item.id)" /></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ item.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ item.code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ item.category || 'General' }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.subjects_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/learning-areas/${item.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/learning-areas/${item.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                            <DropdownMenuItem @click="toggleStatus(item)">
                                                <component :is="item.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />
                                                {{ item.is_active ? 'Deactivate' : 'Activate' }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive" @click="deleteItem(item)">
                                                <Trash2 class="mr-2 h-4 w-4" />Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="confirmOpen = false">
            <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">
                    {{ confirmMode === 'delete' ? 'Delete learning area' : confirmMode === 'activate' ? 'Activate learning area' : 'Deactivate learning area' }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    <template v-if="selectedItem">
                        {{ confirmMode === 'delete'
                            ? `Delete ${selectedItem.name}? This action cannot be undone.`
                            : confirmMode === 'activate'
                                ? `Activate ${selectedItem.name}?`
                                : `Deactivate ${selectedItem.name}?` }}
                    </template>
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="confirmOpen = false">Cancel</Button>
                    <Button :variant="confirmMode === 'delete' ? 'destructive' : 'default'" :disabled="actionForm.processing" @click="confirmAction">Confirm</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
