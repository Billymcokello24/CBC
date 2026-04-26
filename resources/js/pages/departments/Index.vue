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
    Home,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

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
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 border-b border-sidebar-border pb-8 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Departments</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Department Management
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Organize academic units, manage staff assignments, and track departmental performance.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border px-4 font-medium hover:bg-muted"
                        @click="router.get(exportUrl)"
                    >
                        <Download class="mr-2 h-4 w-4 opacity-70" />
                        Export
                    </Button>
                    <Link
                        href="/departments/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Department
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            Total Departments
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            {{ page.props?.stats?.total ?? 0 }}
                        </h3>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"
                    >
                        <Building2 class="h-6 w-6" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            Active
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            {{ page.props?.stats?.active ?? 0 }}
                        </h3>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                    >
                        <div
                            class="mr-2 h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500"
                            v-if="page.props?.stats?.active > 0"
                        ></div>
                        <ShieldCheck class="h-6 w-6" v-else />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            Total Staff
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            {{ page.props?.stats?.teachers ?? 0 }}
                        </h3>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600"
                    >
                        <Users class="h-6 w-6" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            Academic Subjects
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            {{ page.props?.stats?.subjects ?? 0 }}
                        </h3>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
                    >
                        <BookOpen class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div class="space-y-4 border-b border-border/50 p-6">
                    <div
                        class="flex flex-col items-center justify-between gap-4 md:flex-row"
                    >
                        <div class="relative w-full md:max-w-md">
                            <Search
                                class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                placeholder="Search by name or code..."
                                class="h-11 rounded-xl border-border bg-muted/50 pl-10 transition-all focus:bg-background"
                            />
                        </div>

                        <div class="flex w-full items-center gap-2 md:w-auto">
                            <Button
                                variant="outline"
                                class="h-11 rounded-xl border-border bg-background px-4 font-semibold whitespace-nowrap"
                                @click="showFilters = !showFilters"
                            >
                                <Filter class="mr-2 h-4 w-4 opacity-70" />
                                {{
                                    showFilters
                                        ? 'Hide Filters'
                                        : 'More Filters'
                                }}
                            </Button>

                            <div v-if="selectedDepartments.length > 0">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            class="h-11 rounded-xl bg-foreground px-4 font-semibold text-background shadow-sm hover:bg-foreground/90"
                                        >
                                            Bulk Actions ({{
                                                selectedDepartments.length
                                            }})
                                            <ChevronDown
                                                class="ml-2 h-4 w-4 opacity-70"
                                            />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-56 rounded-xl border border-border p-2"
                                    >
                                        <DropdownMenuItem
                                            class="rounded-lg"
                                            @click="runBulkAction('activate')"
                                            ><CheckCircle2
                                                class="mr-2 h-4 w-4 text-emerald-500"
                                            />
                                            Activate Selected</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            class="rounded-lg"
                                            @click="runBulkAction('deactivate')"
                                            ><ShieldOff
                                                class="mr-2 h-4 w-4 text-amber-500"
                                            />
                                            Deactivate
                                            Selected</DropdownMenuItem
                                        >
                                        <DropdownMenuSeparator
                                            class="my-1 bg-border/50"
                                        />
                                        <DropdownMenuItem
                                            class="group rounded-lg text-rose-500"
                                            @click="runBulkAction('delete')"
                                            ><Trash2
                                                class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100"
                                            />
                                            Delete Selected</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <Button
                                variant="outline"
                                class="h-11 rounded-xl border-border px-4 font-semibold hover:bg-muted"
                                @click="router.get(exportUrl)"
                            >
                                <Download class="mr-2 h-4 w-4 opacity-70" />
                                Export CSV
                            </Button>
                        </div>
                    </div>

                    <!-- Filters Drawer -->
                    <div
                        v-if="showFilters"
                        class="grid animate-in gap-4 pt-4 duration-200 slide-in-from-top-2 md:grid-cols-2 lg:grid-cols-4"
                    >
                        <select
                            v-model="statusFilter"
                            @change="apply"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/30 px-4 text-sm font-medium transition-all outline-none hover:bg-muted/50 focus:ring-2 focus:ring-blue-600/20"
                        >
                            <option value="all">All Departments</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>
                        <select
                            v-model="perPage"
                            @change="apply"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/30 px-4 text-sm font-medium transition-all outline-none hover:bg-muted/50 focus:ring-2 focus:ring-blue-600/20"
                        >
                            <option
                                v-for="n in [10, 20, 50, 100]"
                                :key="n"
                                :value="n"
                            >
                                {{ n }} per page
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <!-- Main Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr
                                class="border-b border-border/50 bg-muted/30 text-muted-foreground"
                            >
                                <th class="h-12 w-12 px-6">
                                    <button
                                        @click="toggleAllDepartments"
                                        class="flex h-5 w-5 items-center justify-center rounded-md border-2 border-border bg-background transition-all"
                                        :class="
                                            allSelected
                                                ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                                : ''
                                        "
                                    >
                                        <component
                                            :is="allSelected ? Check : Square"
                                            class="h-3.5 w-3.5"
                                        />
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-semibold tracking-wider uppercase"
                                >
                                    Department Details
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-semibold tracking-wider uppercase"
                                >
                                    HOD / Leadership
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold tracking-wider uppercase"
                                >
                                    Teachers
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold tracking-wider uppercase"
                                >
                                    Subjects
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold tracking-wider uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold tracking-wider uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="dept in page.props?.departments?.data ??
                                []"
                                :key="dept.id"
                                class="group cursor-pointer transition-colors hover:bg-muted/30"
                                @click="router.visit(`/departments/${dept.id}`)"
                            >
                                <td class="px-6 py-4 text-center" @click.stop>
                                    <button
                                        @click="toggleDeptSelection(dept.id)"
                                        class="flex h-5 w-5 items-center justify-center rounded-md border-2 border-border bg-background transition-all"
                                        :class="
                                            isSelected(dept.id)
                                                ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                                : ''
                                        "
                                    >
                                        <component
                                            :is="
                                                isSelected(dept.id)
                                                    ? Check
                                                    : Square
                                            "
                                            class="h-3 w-3"
                                        />
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-xs font-bold text-blue-600"
                                        >
                                            {{ dept.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-bold text-foreground"
                                                >{{ dept.name }}</span
                                            >
                                            <span
                                                class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                                >{{
                                                    dept.code || 'No Code'
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-muted text-xs font-bold text-muted-foreground"
                                        >
                                            {{
                                                dept.head_of_department?.charAt(
                                                    0,
                                                ) || '?'
                                            }}
                                        </div>
                                        <span class="text-foreground">{{
                                            dept.head_of_department ||
                                            'Unassigned'
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        variant="outline"
                                        class="rounded-lg border-slate-100 bg-slate-50 px-2.5 py-0.5 text-xs font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        {{ dept.teachers_count ?? 0 }} Staff
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600"
                                    >
                                        {{ dept.subjects_count ?? 0 }} Subjects
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <div
                                            :class="[
                                                'h-2 w-2 rounded-full',
                                                dept.is_active
                                                    ? 'bg-emerald-500 shadow-sm'
                                                    : 'bg-slate-300',
                                            ]"
                                        ></div>
                                        <span
                                            class="text-sm font-bold tracking-tight uppercase"
                                            :class="
                                                dept.is_active
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-500'
                                            "
                                        >
                                            {{
                                                dept.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <Link
                                            :href="`/departments/${dept.id}`"
                                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-transparent text-muted-foreground shadow-none transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600"
                                            ><Eye class="h-4.5 w-4.5"
                                        /></Link>
                                        <Link
                                            :href="`/departments/${dept.id}/edit`"
                                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-transparent text-muted-foreground shadow-none transition-all hover:border-amber-100 hover:bg-amber-50 hover:text-amber-600"
                                            ><Edit class="h-4.5 w-4.5"
                                        /></Link>

                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl"
                                                    ><MoreHorizontal
                                                        class="h-4.5 w-4.5"
                                                /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border border-border p-2"
                                            >
                                                <DropdownMenuItem
                                                    as-child
                                                    class="rounded-lg"
                                                    ><Link
                                                        :href="`/departments/${dept.id}`"
                                                        ><Eye
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        View Details</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    as-child
                                                    class="rounded-lg"
                                                    ><Link
                                                        :href="`/departments/${dept.id}/edit`"
                                                        ><Edit
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        Edit Department</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuSeparator
                                                    class="my-1 bg-border/50"
                                                />
                                                <DropdownMenuItem
                                                    @click="toggleStatus(dept)"
                                                    class="rounded-lg"
                                                >
                                                    <component
                                                        :is="
                                                            dept.is_active
                                                                ? ShieldOff
                                                                : ShieldCheck
                                                        "
                                                        class="mr-2 h-4 w-4"
                                                        :class="
                                                            dept.is_active
                                                                ? 'text-amber-500'
                                                                : 'text-emerald-500'
                                                        "
                                                    />
                                                    {{
                                                        dept.is_active
                                                            ? 'Deactivate'
                                                            : 'Activate'
                                                    }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    class="group rounded-lg text-rose-600"
                                                    @click="
                                                        openDeleteModal(dept)
                                                    "
                                                    ><Trash2
                                                        class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100"
                                                    />
                                                    Delete
                                                    Department</DropdownMenuItem
                                                >
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!page.props?.departments?.data?.length">
                                <td
                                    colspan="7"
                                    class="px-6 py-24 text-center font-medium text-muted-foreground"
                                >
                                    No departments found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="page.props?.departments?.total > 0"
                    class="flex flex-col gap-4 border-t border-border/50 bg-muted/10 px-6 py-6 md:flex-row md:items-center md:justify-between"
                >
                    <p class="text-[13px] font-medium text-muted-foreground">
                        Showing {{ page.props?.departments?.from ?? 0 }} to
                        {{ page.props?.departments?.to ?? 0 }} of
                        {{ page.props?.departments?.total ?? 0 }} departments
                    </p>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-semibold tracking-tight text-muted-foreground uppercase"
                                >Show</span
                            >
                            <select
                                v-model="perPage"
                                @change="apply"
                                class="cursor-pointer rounded-xl border border-border bg-background px-3 py-1.5 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-600/10"
                            >
                                <option :value="10">10</option>
                                <option :value="20">20</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-border px-4 text-xs font-bold"
                                :disabled="
                                    page.props.departments.current_page <= 1
                                "
                                @click="
                                    page.props.departments.prev_page_url &&
                                    router.get(
                                        page.props.departments.prev_page_url,
                                    )
                                "
                                >Previous</Button
                            >
                            <div
                                class="flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white"
                            >
                                {{ page.props.departments.current_page }} /
                                {{ page.props.departments.last_page }}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-border px-4 text-xs font-bold"
                                :disabled="
                                    page.props.departments.current_page >=
                                    page.props.departments.last_page
                                "
                                @click="
                                    page.props.departments.next_page_url &&
                                    router.get(
                                        page.props.departments.next_page_url,
                                    )
                                "
                                >Next</Button
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 p-4 shadow-2xl backdrop-blur-sm sm:p-6"
                @click.self="showDeleteModal = false"
            >
                <div
                    class="w-full max-w-md animate-in rounded-xl border border-border bg-background p-6 shadow-xl duration-200 zoom-in-95"
                >
                    <h2 class="text-lg font-bold">Delete Department</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Are you sure you want to delete
                        <span class="font-semibold text-foreground">{{
                            deleteTarget?.name
                        }}</span
                        >? This action cannot be undone.
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <Button
                            variant="outline"
                            class="rounded-xl"
                            @click="showDeleteModal = false"
                            >Cancel</Button
                        >
                        <Button
                            variant="destructive"
                            class="rounded-xl px-6"
                            @click="confirmDelete"
                        >
                            Confirm Delete
                        </Button>
                    </div>
                </div>
            </div>
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
