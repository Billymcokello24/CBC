<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    Search,
    Filter,
    Download,
    Plus,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    ArrowUpDown,
    CheckCircle2,
    XCircle,
    Clock,
    Users
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
}

interface Props {
    data: any[];
    columns: Column[];
    title?: string;
    description?: string;
    searchable?: boolean;
    searchPlaceholder?: string;
    filterable?: boolean;
    filters?: { key: string; label: string; options: { value: string; label: string }[] }[];
    selectable?: boolean;
    createRoute?: string;
    createLabel?: string;
    exportable?: boolean;
    pagination?: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
        from: number;
        to: number;
    };
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    searchable: true,
    searchPlaceholder: 'Search...',
    filterable: false,
    selectable: false,
    exportable: false,
    loading: false,
});

const emit = defineEmits<{
    (e: 'search', value: string): void;
    (e: 'filter', key: string, value: string): void;
    (e: 'sort', key: string, direction: 'asc' | 'desc'): void;
    (e: 'page', page: number): void;
    (e: 'perPage', perPage: number): void;
    (e: 'select', ids: number[]): void;
    (e: 'delete', id: number): void;
    (e: 'bulkDelete', ids: number[]): void;
    (e: 'export', format: string): void;
}>();

const searchQuery = ref('');
const selectedIds = ref<number[]>([]);
const sortKey = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');
const activeFilters = ref<Record<string, string>>({});
const deleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const allSelected = computed(() => {
    return props.data.length > 0 && selectedIds.value.length === props.data.length;
});

const someSelected = computed(() => {
    return selectedIds.value.length > 0 && selectedIds.value.length < props.data.length;
});

const toggleAll = () => {
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.data.map((item: any) => item.id);
    }
    emit('select', selectedIds.value);
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(id);
    }
    emit('select', selectedIds.value);
};

const handleSort = (key: string) => {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDirection.value = 'asc';
    }
    emit('sort', key, sortDirection.value);
};

const handleSearch = () => {
    emit('search', searchQuery.value);
};

const handleFilter = (key: string, value: string) => {
    activeFilters.value[key] = value;
    emit('filter', key, value);
};

const confirmDelete = (id: number) => {
    itemToDelete.value = id;
    deleteDialogOpen.value = true;
};

const handleDelete = () => {
    if (itemToDelete.value) {
        emit('delete', itemToDelete.value);
    }
    deleteDialogOpen.value = false;
    itemToDelete.value = null;
};

const handleBulkDelete = () => {
    emit('bulkDelete', selectedIds.value);
    selectedIds.value = [];
};

const getStatusBadge = (status: string) => {
    const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; icon: any }> = {
        active: { variant: 'default', icon: CheckCircle2 },
        inactive: { variant: 'secondary', icon: XCircle },
        pending: { variant: 'outline', icon: Clock },
        suspended: { variant: 'destructive', icon: XCircle },
    };
    return variants[status] || variants.inactive;
};

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        emit('search', value);
    }, 300);
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 v-if="title" class="text-2xl font-bold tracking-tight">{{ title }}</h2>
                <p v-if="description" class="text-muted-foreground">{{ description }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button v-if="selectedIds.length > 0" variant="destructive" size="sm" @click="handleBulkDelete">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete ({{ selectedIds.length }})
                </Button>
                <DropdownMenu v-if="exportable">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Download class="mr-2 h-4 w-4" />
                            Export
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem @click="emit('export', 'csv')">Export as CSV</DropdownMenuItem>
                        <DropdownMenuItem @click="emit('export', 'excel')">Export as Excel</DropdownMenuItem>
                        <DropdownMenuItem @click="emit('export', 'pdf')">Export as PDF</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button v-if="createRoute" as-child>
                    <Link :href="createRoute">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ createLabel || 'Add New' }}
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <div v-if="searchable" class="relative flex-1 md:max-w-sm">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="pl-9"
                />
            </div>
            <div v-if="filterable && filters" class="flex flex-wrap items-center gap-2">
                <template v-for="filter in filters" :key="filter.key">
                    <Select
                        :model-value="activeFilters[filter.key]"
                        @update:model-value="(v) => handleFilter(filter.key, v as string)"
                    >
                        <SelectTrigger class="w-[150px]">
                            <SelectValue :placeholder="filter.label" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All</SelectItem>
                            <SelectItem v-for="opt in filter.options" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th v-if="selectable" class="w-12 px-4 py-3">
                                <Checkbox
                                    :checked="allSelected"
                                    :indeterminate="someSelected"
                                    @update:checked="toggleAll"
                                />
                            </th>
                            <th
                                v-for="column in columns"
                                :key="column.key"
                                :class="cn(
                                    'px-4 py-3 text-left text-sm font-medium text-muted-foreground',
                                    column.sortable && 'cursor-pointer hover:text-foreground',
                                    column.class
                                )"
                                @click="column.sortable && handleSort(column.key)"
                            >
                                <div class="flex items-center gap-2">
                                    {{ column.label }}
                                    <ArrowUpDown v-if="column.sortable" class="h-4 w-4" />
                                </div>
                            </th>
                            <th class="w-20 px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loading state -->
                        <template v-if="loading">
                            <tr v-for="i in 5" :key="i">
                                <td v-if="selectable" class="px-4 py-3">
                                    <div class="h-4 w-4 animate-pulse rounded bg-muted"></div>
                                </td>
                                <td v-for="column in columns" :key="column.key" class="px-4 py-3">
                                    <div class="h-4 w-3/4 animate-pulse rounded bg-muted"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="h-4 w-8 animate-pulse rounded bg-muted"></div>
                                </td>
                            </tr>
                        </template>

                        <!-- Data rows -->
                        <template v-else-if="data.length > 0">
                            <tr
                                v-for="item in data"
                                :key="item.id"
                                class="border-b transition-colors hover:bg-muted/50"
                            >
                                <td v-if="selectable" class="px-4 py-3">
                                    <Checkbox
                                        :checked="selectedIds.includes(item.id)"
                                        @update:checked="toggleSelect(item.id)"
                                    />
                                </td>
                                <td
                                    v-for="column in columns"
                                    :key="column.key"
                                    :class="cn('px-4 py-3 text-sm', column.class)"
                                >
                                    <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]">
                                        <template v-if="column.key === 'status'">
                                            <Badge :variant="getStatusBadge(item[column.key]).variant">
                                                <component
                                                    :is="getStatusBadge(item[column.key]).icon"
                                                    class="mr-1 h-3 w-3"
                                                />
                                                {{ item[column.key] }}
                                            </Badge>
                                        </template>
                                        <template v-else-if="column.key === 'photo'">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 overflow-hidden rounded-full bg-muted">
                                                    <img
                                                        v-if="item.photo"
                                                        :src="item.photo"
                                                        :alt="item.name || item.full_name"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div v-else class="flex h-full w-full items-center justify-center text-muted-foreground">
                                                        <Users class="h-5 w-5" />
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            {{ item[column.key] }}
                                        </template>
                                    </slot>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem as-child>
                                                <Link :href="`${item.id}`">
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`${item.id}/edit`">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="text-destructive focus:text-destructive"
                                                @click="confirmDelete(item.id)"
                                            >
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty state -->
                        <template v-else>
                            <tr>
                                <td :colspan="columns.length + (selectable ? 2 : 1)" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Users class="h-12 w-12 text-muted-foreground/50" />
                                        <p class="text-lg font-medium">No data found</p>
                                        <p class="text-sm text-muted-foreground">Try adjusting your search or filters</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination" class="flex flex-col items-center justify-between gap-4 border-t px-4 py-3 sm:flex-row">
                <div class="text-sm text-muted-foreground">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </div>
                <div class="flex items-center gap-2">
                    <Select
                        :model-value="String(pagination.perPage)"
                        @update:model-value="(v) => emit('perPage', Number(v))"
                    >
                        <SelectTrigger class="w-[80px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="25">25</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                            <SelectItem value="100">100</SelectItem>
                        </SelectContent>
                    </Select>

                    <div class="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            :disabled="pagination.currentPage === 1"
                            @click="emit('page', 1)"
                        >
                            <ChevronsLeft class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            :disabled="pagination.currentPage === 1"
                            @click="emit('page', pagination.currentPage - 1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="px-3 text-sm">
                            Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
                        </span>
                        <Button
                            variant="outline"
                            size="icon"
                            :disabled="pagination.currentPage === pagination.lastPage"
                            @click="emit('page', pagination.currentPage + 1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            :disabled="pagination.currentPage === pagination.lastPage"
                            @click="emit('page', pagination.lastPage)"
                        >
                            <ChevronsRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirm Delete</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete this item? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
                <Button variant="destructive" @click="handleDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
