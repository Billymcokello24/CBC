<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Eye,
    Edit,
    Trash2,
    Grid3x3,
    List,
    Download,
    Plus,
    Filter,
    X,
    Search,
    Users,
    BookOpen,
    MoreHorizontal,
    Building2,
    ShieldCheck,
    ShieldOff,
    CheckCircle2,
    ChevronDown,
    Check,
    Square,
    Minus,
    Rows3,
    Grid2x2,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';

const page = usePage();
const filters = page.props?.filters ?? {};
const search = ref(filters.search ?? '');
const perPage = ref(filters.per_page ?? 20);
const viewMode = ref('list');
const statusFilter = ref(filters.status ?? 'all');
const showFilters = ref(false);
const selectedDepartments = ref([]);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let debounceTimer = null;

const apply = () => {
    router.get(
        '/departments',
        {
            search: search.value,
            per_page: perPage.value,
            view: viewMode.value,
            status: statusFilter.value,
        },
        {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        },
    );
};

watch(search, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(apply, 300);
});

const allSelectableDeptIds = computed(() =>
    (page.props.departments?.data ?? []).map((dept) => dept.id),
);
const allSelected = computed(
    () =>
        allSelectableDeptIds.value.length > 0 &&
        allSelectableDeptIds.value.every((id) =>
            selectedDepartments.value.includes(id),
        ),
);

const toggleAllDepartments = () => {
    selectedDepartments.value = allSelected.value
        ? []
        : [...allSelectableDeptIds.value];
};

const toggleDeptSelection = (id) => {
    const idx = selectedDepartments.value.indexOf(id);
    if (idx > -1) {
        selectedDepartments.value.splice(idx, 1);
    } else {
        selectedDepartments.value.push(id);
    }
};

const isSelected = (id) => selectedDepartments.value.includes(id);

const toggleStatus = (dept) => {
    router.patch(
        `/departments/${dept.id}/${dept.is_active ? 'deactivate' : 'activate'}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => apply(),
        },
    );
};

const openDeleteModal = (dept) => {
    deleteTarget.value = dept;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/departments/${deleteTarget.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
            apply();
        },
    });
};

const runBulkAction = (action) => {
    if (selectedDepartments.value.length === 0) return;

    if (
        action === 'delete' &&
        !confirm(
            `Are you sure you want to delete ${selectedDepartments.value.length} departments?`,
        )
    ) {
        return;
    }

    router.post(
        '/departments/bulk-action',
        {
            department_ids: selectedDepartments.value,
            action: action,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedDepartments.value = [];
            },
        },
    );
};

const exportUrl = computed(() => {
    const params = new URLSearchParams({
        search: search.value,
        status: statusFilter.value,
    });
    return `/departments/export?${params.toString()}`;
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Departments', href: '/departments' },
];
</script>

<template>
    <Head title="Departments" />
    <AppLayout :breadcrumbs="breadcrumbs">
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
                        <span class="font-medium tracking-tight text-foreground uppercase">Organization</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Departments</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Department Registry
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage academic departments, staff allocations, and administrative units.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="router.get(exportUrl)"
                    >
                        <Download class="mr-2 h-4 w-4 opacity-70" />
                        Export
                    </Button>
                    <Link
                        href="/departments/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] hover:bg-primary/90 active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Department
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 px-1 sm:gap-6 lg:grid-cols-4">
                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Building2 class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Departments</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ page.props?.stats?.total ?? 0 }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Total Units</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-emerald-200 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                            <ShieldCheck class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Active Now</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ page.props?.stats?.active ?? 0 }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Running Units</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Staff</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ page.props?.stats?.teachers ?? 0 }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Attached Personnel</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <BookOpen class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Subjects</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ page.props?.stats?.subjects ?? 0 }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Assigned Learning</p>
                </div>
            </div>

            <!-- Toolbar & Filter -->
            <div class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row">
                <div class="flex w-full flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="group relative w-full sm:max-w-xs">
                        <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary" />
                        <Input
                            v-model="search"
                            placeholder="Find by name or code..."
                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold tracking-tight uppercase transition-all focus:bg-background"
                        />
                    </div>

                    <div class="flex gap-2 w-full sm:w-auto">
                        <Button
                            variant="outline"
                            class="h-11 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                            @click="showFilters = !showFilters"
                        >
                            <Filter class="mr-2 h-3.5 w-3.5 opacity-70" />
                            Filters
                        </Button>
                    </div>

                    <div v-if="selectedDepartments.length > 0">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    class="h-11 rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 hover:bg-primary/90"
                                >
                                    Bulk Actions ({{ selectedDepartments.length }})
                                    <ChevronDown class="ml-2 h-4 w-4 opacity-70" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-56 rounded-xl border-border p-2 shadow-xl"
                            >
                                <DropdownMenuItem
                                    class="rounded-lg py-2.5 text-xs font-bold"
                                    @click="runBulkAction('activate')"
                                ><CheckCircle2
                                    class="mr-3 h-4 w-4 text-emerald-500 opacity-60"
                                />
                                    Activate Selected</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    class="rounded-lg py-2.5 text-xs font-bold"
                                    @click="runBulkAction('deactivate')"
                                ><ShieldOff
                                    class="mr-3 h-4 w-4 text-amber-500 opacity-60"
                                />
                                    Deactivate Selected</DropdownMenuItem
                                >
                                <DropdownMenuSeparator class="my-1" />
                                <DropdownMenuItem
                                    class="rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                    @click="runBulkAction('delete')"
                                ><Trash2
                                    class="mr-3 h-4 w-4 opacity-60"
                                />
                                    Delete Selected</DropdownMenuItem
                                >
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="router.get(exportUrl)"
                    >
                        <Download class="mr-2 h-3.5 w-3.5 opacity-70" />
                        CSV Export
                    </Button>
                </div>
            </div>

            <!-- Filters Drawer -->
            <div
                v-if="showFilters"
                class="grid animate-in grid-cols-2 gap-3 pt-2 duration-300 slide-in-from-top-2 sm:gap-4 lg:grid-cols-4"
            >
                <div class="group relative">
                    <select
                        v-model="statusFilter"
                        @change="apply"
                        class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border bg-background px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none hover:bg-muted/50 focus:border-primary/50 focus:ring-2 focus:ring-primary/5"
                    >
                        <option value="all">Every Status</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/40 transition-colors group-hover:text-muted-foreground" />
                </div>
                <div class="group relative">
                    <select
                        v-model="perPage"
                        @change="apply"
                        class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border bg-background px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none hover:bg-muted/50 focus:border-primary/50 focus:ring-2 focus:ring-primary/5"
                    >
                        <option :value="10">10 per page</option>
                        <option :value="20">20 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/40 transition-colors group-hover:text-muted-foreground" />
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-muted-foreground">
                                <th class="h-14 w-14 px-6 md:px-8">
                                    <button
                                        @click="toggleAllDepartments"
                                        class="flex h-5 w-5 items-center justify-center rounded-lg border-2 border-border bg-background transition-all hover:border-primary/40"
                                        :class="allSelected ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : ''"
                                    >
                                        <Check v-if="allSelected" class="h-3.5 w-3.5" />
                                    </button>
                                </th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase">Department</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase">Head of Dept</th>
                                <th class="px-6 py-4 text-center text-xs font-bold tracking-tight uppercase">Staff</th>
                                <th class="px-6 py-4 text-center text-xs font-bold tracking-tight uppercase">Subjects</th>
                                <th class="px-6 py-4 text-center text-xs font-bold tracking-tight uppercase">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold tracking-tight uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="dept in page.props?.departments?.data ?? []"
                                :key="dept.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-6 py-4 md:px-8">
                                    <button
                                        @click.stop="toggleDeptSelection(dept.id)"
                                        class="flex h-5 w-5 items-center justify-center rounded-lg border-2 border-border bg-background transition-all hover:border-primary/40"
                                        :class="isSelected(dept.id) ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : ''"
                                    >
                                        <Check v-if="isSelected(dept.id)" class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl border border-primary/10 bg-primary/10 text-xs font-bold text-primary transition-all group-hover:scale-110">
                                            {{ dept.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <Link :href="`/departments/${dept.id}`" class="text-sm font-bold text-foreground transition-colors hover:text-primary">
                                                {{ dept.name }}
                                            </Link>
                                            <span class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">
                                                {{ dept.code || 'No Code' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-muted text-[10px] font-bold text-muted-foreground uppercase">
                                            {{ dept.head_of_department?.charAt(0) || '?' }}
                                        </div>
                                        <span class="text-xs font-bold text-foreground">
                                            {{ dept.head_of_department || 'Unassigned' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge variant="outline" class="h-6 rounded-lg border-none bg-primary/5 px-2 text-[10px] font-bold tracking-tight text-primary uppercase">
                                        {{ dept.teachers_count ?? 0 }} personnel
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="rounded-lg border border-border bg-muted/50 px-3 py-1 text-[10px] font-bold tracking-tight text-foreground uppercase">
                                        {{ dept.subjects_count ?? 0 }} Learning Areas
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="border-none px-3 py-1 text-[10px] font-bold tracking-tight uppercase"
                                        :class="dept.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-500'"
                                    >
                                        {{ dept.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right px-6 md:px-8">
                                    <div class="flex items-center justify-end gap-1 px-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/departments/${dept.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/departments/${dept.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-muted-foreground hover:bg-muted"
                                                >
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border-border p-2 shadow-xl"
                                            >
                                                <DropdownMenuItem
                                                    class="rounded-lg py-2.5 text-xs font-bold"
                                                    @click="toggleStatus(dept)"
                                                >
                                                    <component
                                                        :is="dept.is_active ? ShieldOff : ShieldCheck"
                                                        class="mr-3 h-4 w-4 opacity-60"
                                                        :class="dept.is_active ? 'text-amber-500' : 'text-emerald-500'"
                                                    />
                                                    {{ dept.is_active ? 'Deactivate' : 'Activate' }} Record
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1" />
                                                <DropdownMenuItem
                                                    class="rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                                    @click="openDeleteModal(dept)"
                                                >
                                                    <Trash2 class="mr-3 h-4 w-4 opacity-60" />
                                                    Remove Unit
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div
                    v-if="page.props?.departments?.total > 0"
                    class="flex flex-col items-center justify-between gap-4 border-t border-border/50 bg-muted/5 px-6 py-4 md:flex-row"
                >
                    <p class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase">
                        Showing {{ page.props?.departments?.from ?? 0 }} - {{ page.props?.departments?.to ?? 0 }} of {{ page.props?.departments?.total ?? 0 }} departments
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            class="h-9 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted"
                            :disabled="page.props.departments.current_page <= 1"
                            @click="page.props.departments.prev_page_url && router.get(page.props.departments.prev_page_url)"
                        >
                            Previous
                        </Button>
                        <div class="flex h-9 items-center justify-center rounded-xl bg-primary px-4 text-xs font-bold text-white shadow-lg shadow-primary/20">
                            {{ page.props.departments.current_page }} / {{ page.props.departments.last_page }}
                        </div>
                        <Button
                            variant="outline"
                            class="h-9 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted"
                            :disabled="page.props.departments.current_page >= page.props.departments.last_page"
                            @click="page.props.departments.next_page_url && router.get(page.props.departments.next_page_url)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Delete Dialog -->
            <Dialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
                <DialogContent class="max-w-md rounded-2xl border-border p-8 shadow-2xl">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                            <Trash2 class="h-8 w-8" />
                        </div>
                        <h3 class="mb-2 text-xl font-bold tracking-tight text-slate-900 uppercase">
                            Delete Department
                        </h3>
                        <p class="mb-8 text-sm font-medium text-slate-500">
                            Are you sure you want to delete <span class="font-bold text-foreground">{{ deleteTarget?.name }}</span>? This action is permanent and cannot be undone.
                        </p>
                        <div class="grid w-full grid-cols-2 gap-4">
                            <Button
                                variant="outline"
                                class="h-12 rounded-xl border-border text-xs font-bold tracking-tight uppercase hover:bg-muted"
                                @click="showDeleteModal = false"
                            >
                                Cancel action
                            </Button>
                            <Button
                                class="h-12 rounded-xl bg-rose-600 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-rose-100 hover:bg-rose-700 transition-all"
                                @click="confirmDelete"
                            >
                                Confirm Delete
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Standard styles */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
