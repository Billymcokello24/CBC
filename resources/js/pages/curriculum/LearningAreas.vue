<script setup lang="ts">
/* global route */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    GraduationCap,
    Search,
    Plus,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Layers,
    BookOpen,
    ChevronDown,
    CheckSquare,
    Square,
    LayoutGrid,
    List,
    CheckCircle2,
    XCircle,
    ShieldCheck,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import LearningAreaModal from '@/components/curriculum/LearningAreaModal.vue';
import ConfirmDialog from '@/components/curriculum/ConfirmDialog.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    areas: any[];
    stats: {
        total: number;
        active: number;
        subjects: number;
    };
    filters: {
        search?: string;
        status?: string;
        view?: 'grid' | 'list';
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const { props: pageProps } = usePage<any>();
const permissions = computed(() => pageProps.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
];

// State
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const currentView = ref(props.filters.view || 'list');
const selectedIds = ref<number[]>([]);

// Modals
const showModal = ref(false);
const modalTitle = ref('New Learning Area');
const editingArea = ref<any>(null);

const showConfirm = ref(false);
const confirmConfig = ref({
    title: '',
    message: '',
    confirmText: '',
    variant: 'danger' as 'danger' | 'warning' | 'info',
    action: null as (() => void) | null,
    loading: false,
});

// Logic
let debounceTimeout: any = null;
const updateFilters = () => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(
            '/curriculum/learning-areas',
            {
                search: searchQuery.value,
                status: selectedStatus.value,
                view: currentView.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300);
};

watch([searchQuery, selectedStatus, currentView], () => updateFilters());

// Actions
const openCreate = () => {
    editingArea.value = null;
    modalTitle.value = 'New Learning Area';
    showModal.value = true;
};

const openEdit = (area: any) => {
    editingArea.value = area;
    modalTitle.value = `Edit ${area.name}`;
    showModal.value = true;
};

const confirmDelete = (area: any) => {
    confirmConfig.value = {
        title: 'Delete Learning Area?',
        message: `Are you sure you want to delete "${area.name}"? This action cannot be undone and may affect related subjects.`,
        confirmText: 'Delete Area',
        variant: 'danger',
        loading: false,
        action: () => {
            confirmConfig.value.loading = true;
            router.delete(`/curriculum/learning-areas/${area.id}`, {
                onFinish: () => {
                    showConfirm.value = false;
                    confirmConfig.value.loading = false;
                },
            });
        },
    };
    showConfirm.value = true;
};

// Selection
const toggleSelectAll = () => {
    if (selectedIds.value.length === props.areas.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.areas.map((a) => a.id);
    }
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) selectedIds.value.splice(index, 1);
    else selectedIds.value.push(id);
};

const bulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    const labels = {
        activate: {
            title: 'Activate Areas?',
            msg: 'Enable student enrollment for the selected learning areas.',
            text: 'Activate',
            variant: 'info',
        },
        deactivate: {
            title: 'Deactivate Areas?',
            msg: 'Disable student enrollment for the selected learning areas.',
            text: 'Deactivate',
            variant: 'warning',
        },
        delete: {
            title: 'Delete Areas?',
            msg: 'Permanently remove the selected learning areas and their data.',
            text: 'Delete',
            variant: 'danger',
        },
    };

    const config = labels[action];

    confirmConfig.value = {
        title: config.title,
        message: `${config.msg} (${selectedIds.value.length} items)`,
        confirmText: config.text,
        variant: config.variant as any,
        loading: false,
        action: () => {
            confirmConfig.value.loading = true;
            router.post(
                '/curriculum/learning-areas/bulk-action',
                {
                    ids: selectedIds.value,
                    action: action,
                },
                {
                    onFinish: () => {
                        showConfirm.value = false;
                        confirmConfig.value.loading = false;
                        selectedIds.value = [];
                    },
                },
            );
        },
    };
    showConfirm.value = true;
};

const getStatusColor = (active: boolean) => {
    return active
        ? 'bg-emerald-500 text-white shadow-sm'
        : 'bg-slate-400 text-white';
};
</script>

<template>
    <Head title="Learning Areas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar mt-2 flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <GraduationCap class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Learning Areas
                        </h1>
                        <p class="font-medium text-muted-foreground">
                            Manage top-level curriculum domains and subject
                            clusters
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="font-pulsar h-10 border-slate-200"
                        v-if="selectedIds.length > 0"
                        @click="selectedIds = []"
                    >
                        Clear Selected ({{ selectedIds.length }})
                    </Button>
                    <Button
                        v-if="hasPermission('curriculum.create')"
                        @click="openCreate"
                        class="font-pulsar h-10 bg-violet-600 shadow-lg hover:bg-violet-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />New Learning Area
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-violet-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-violet-500/10 p-3 transition-all group-hover:bg-violet-500 group-hover:text-white"
                    >
                        <Layers
                            class="h-5 w-5 text-violet-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Areas
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ stats.total }} Domains
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-emerald-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-emerald-500/10 p-3 transition-all group-hover:bg-emerald-500 group-hover:text-white"
                    >
                        <CheckCircle2
                            class="h-5 w-5 text-emerald-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Active Areas
                        </p>
                        <p class="text-xl font-bold text-emerald-600">
                            {{ stats.active }} Pulse
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-blue-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-blue-500/10 p-3 transition-all group-hover:bg-blue-500 group-hover:text-white"
                    >
                        <BookOpen
                            class="h-5 w-5 text-blue-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Subjects
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ stats.subjects }} Active
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-indigo-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-indigo-500/10 p-3 transition-all group-hover:bg-indigo-500 group-hover:text-white"
                    >
                        <ShieldCheck
                            class="h-5 w-5 text-indigo-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            RBAC Status
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-indigo-600 uppercase"
                        >
                            SECURED
                        </p>
                    </div>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div
                class="flex flex-col justify-between gap-4 rounded-xl border bg-card p-4 shadow-sm sm:flex-row sm:items-center"
            >
                <div class="flex max-w-xl flex-1 items-center gap-4">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 font-medium text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by name or code..."
                            class="h-10 border-slate-200 pl-9"
                        />
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                class="font-pulsar h-10 border-slate-200 px-4"
                            >
                                <Filter class="mr-2 h-4 w-4 text-violet-500" />
                                {{
                                    selectedStatus === 'all'
                                        ? 'All Statuses'
                                        : selectedStatus
                                              .charAt(0)
                                              .toUpperCase() +
                                          selectedStatus.slice(1)
                                }}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="font-pulsar w-48">
                            <DropdownMenuItem @click="selectedStatus = 'all'"
                                >All Statuses</DropdownMenuItem
                            >
                            <DropdownMenuItem @click="selectedStatus = 'active'"
                                >Active Only</DropdownMenuItem
                            >
                            <DropdownMenuItem
                                @click="selectedStatus = 'inactive'"
                                >Inactive Only</DropdownMenuItem
                            >
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center rounded-lg border bg-slate-100 p-1"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 rounded-md"
                            :class="{
                                'bg-white text-violet-600 shadow-sm':
                                    currentView === 'grid',
                            }"
                            @click="currentView = 'grid'"
                            ><LayoutGrid class="h-4 w-4"
                        /></Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 rounded-md"
                            :class="{
                                'bg-white text-violet-600 shadow-sm':
                                    currentView === 'list',
                            }"
                            @click="currentView = 'list'"
                            ><List class="h-4 w-4"
                        /></Button>
                    </div>

                    <div class="mx-2 h-8 w-[1px] bg-slate-200"></div>

                    <div v-if="selectedIds.length > 0">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    class="font-pulsar h-10 border-0 bg-slate-900 px-4 text-white shadow-lg hover:bg-black"
                                >
                                    Bulk Actions ({{ selectedIds.length }})
                                    <ChevronDown class="ml-2 h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="font-pulsar w-48"
                            >
                                <DropdownMenuItem
                                    @click="bulkAction('activate')"
                                    ><CheckCircle2
                                        class="mr-2 h-4 w-4 text-emerald-500"
                                    />
                                    Activate</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    @click="bulkAction('deactivate')"
                                    ><XCircle
                                        class="mr-2 h-4 w-4 text-amber-500"
                                    />
                                    Deactivate</DropdownMenuItem
                                >
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    @click="bulkAction('delete')"
                                    class="font-bold text-rose-600"
                                    ><Trash2 class="mr-2 h-4 w-4" />
                                    Delete</DropdownMenuItem
                                >
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <transition name="fade" mode="out-in">
                <!-- Grid View -->
                <div
                    v-if="currentView === 'grid'"
                    :key="'grid'"
                    class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="area in areas"
                        :key="area.id"
                        class="group relative flex flex-col overflow-hidden rounded-3xl border border-t-4 bg-card shadow-sm transition-all hover:shadow-md"
                        :style="`border-top-color: ${area.is_active ? '#8b5cf6' : '#94a3b8'}`"
                    >
                        <!-- Selector -->
                        <div class="absolute top-4 left-4 z-10">
                            <button
                                @click="toggleSelect(area.id)"
                                class="flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-white/50 backdrop-blur-sm transition-all"
                                :class="
                                    selectedIds.includes(area.id)
                                        ? 'border-violet-600 bg-violet-600 text-white'
                                        : 'border-slate-300'
                                "
                            >
                                <component
                                    :is="
                                        selectedIds.includes(area.id)
                                            ? CheckSquare
                                            : Square
                                    "
                                    class="h-4 w-4"
                                />
                            </button>
                        </div>

                        <div class="p-6 pb-4">
                            <div class="mb-4 flex items-start justify-between">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl border border-violet-100 bg-violet-600/10 text-sm font-bold text-violet-700 uppercase shadow-inner transition-all group-hover:bg-violet-600 group-hover:text-white"
                                >
                                    {{ area.code.substring(0, 2) }}
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            ><MoreHorizontal class="h-4 w-4"
                                        /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="font-pulsar w-48"
                                    >
                                        <DropdownMenuItem
                                            v-if="
                                                hasPermission(
                                                    'curriculum.update',
                                                )
                                            "
                                            @click="openEdit(area)"
                                            ><Edit class="mr-2 h-4 w-4" /> Edit
                                            Details</DropdownMenuItem
                                        >
                                        <DropdownMenuItem as-child
                                            ><Link
                                                :href="`/curriculum/learning-areas/${area.id}`"
                                                ><Eye class="mr-2 h-4 w-4" />
                                                View Logic</Link
                                            ></DropdownMenuItem
                                        >
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            v-if="
                                                hasPermission(
                                                    'curriculum.delete',
                                                )
                                            "
                                            @click="confirmDelete(area)"
                                            class="font-bold text-rose-600"
                                            ><Trash2 class="mr-2 h-4 w-4" />
                                            Delete Area</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="space-y-2">
                                <h3
                                    class="text-xl font-bold text-slate-900 transition-colors group-hover:text-violet-700"
                                >
                                    {{ area.name }}
                                </h3>
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >{{ area.code }}</span
                                >
                                <p
                                    class="mt-2 line-clamp-2 text-xs font-medium text-muted-foreground"
                                >
                                    {{
                                        area.description ||
                                        'No description provided for this academic domain.'
                                    }}
                                </p>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-xl border bg-slate-50 p-3 text-center shadow-inner"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Subjects
                                    </p>
                                    <p class="text-lg font-bold text-slate-900">
                                        {{ area.subjects_count }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border bg-slate-50 p-3 text-center shadow-inner"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Status
                                    </p>
                                    <div
                                        class="flex items-center justify-center gap-1.5"
                                    >
                                        <div
                                            class="h-2 w-2 rounded-full"
                                            :class="
                                                area.is_active
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-300'
                                            "
                                        ></div>
                                        <p
                                            class="text-xs font-bold text-slate-900"
                                        >
                                            {{
                                                area.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-auto flex items-center justify-between border-t bg-slate-50/50 px-6 py-4"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-300 uppercase"
                                    >ORDER #{{ area.display_order }}</span
                                >
                            </div>
                            <Link
                                :href="`/curriculum/learning-areas/${area.id}`"
                                class="text-xs font-bold text-violet-600 uppercase hover:underline"
                            >
                                Expand Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div
                    v-else
                    :key="'list'"
                    class="overflow-hidden overflow-x-auto rounded-3xl border border-t-4 border-t-violet-500 bg-card shadow-sm"
                >
                    <table class="w-full text-left">
                        <thead>
                            <tr class="font-pulsar border-b bg-slate-50">
                                <th class="w-12 px-6 py-4 text-center">
                                    <button
                                        @click="toggleSelectAll"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-white shadow-sm transition-all"
                                        :class="
                                            selectedIds.length === areas.length
                                                ? 'border-violet-600 bg-violet-600 text-white'
                                                : 'border-slate-300'
                                        "
                                    >
                                        <component
                                            :is="
                                                selectedIds.length ===
                                                areas.length
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Domain / Cluster
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Code
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Subjects
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <tr
                                v-for="area in areas"
                                :key="area.id"
                                class="group transition-colors hover:bg-slate-50/70"
                            >
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="toggleSelect(area.id)"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-white shadow-sm transition-all"
                                        :class="
                                            selectedIds.includes(area.id)
                                                ? 'border-violet-600 bg-violet-600 text-white'
                                                : 'border-slate-200'
                                        "
                                    >
                                        <component
                                            :is="
                                                selectedIds.includes(area.id)
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-violet-100 bg-violet-600/10 text-sm font-bold text-violet-700 uppercase shadow-inner transition-all group-hover:bg-violet-600 group-hover:text-white"
                                        >
                                            {{ area.code.substring(0, 2) }}
                                        </div>
                                        <div>
                                            <div
                                                class="mb-1 text-base leading-none font-bold tracking-tight text-slate-900 transition-colors group-hover:text-violet-700"
                                            >
                                                {{ area.name }}
                                            </div>
                                            <div
                                                class="mt-0.5 text-xs font-bold tracking-tight text-slate-400"
                                            >
                                                {{
                                                    area.category ||
                                                    'Curriculum Domain'
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="text-xs font-bold tracking-tight text-slate-900"
                                    >
                                        {{ area.code }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-lg leading-none font-bold text-slate-900"
                                            >{{ area.subjects_count }}</span
                                        >
                                        <span
                                            class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Active Nodes</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        :class="getStatusColor(area.is_active)"
                                        class="rounded-full border-0 px-2 py-0.5 text-xs font-medium tracking-tight uppercase"
                                    >
                                        {{
                                            area.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Button
                                            v-if="
                                                hasPermission(
                                                    'curriculum.update',
                                                )
                                            "
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-slate-400 hover:text-amber-600"
                                            @click="openEdit(area)"
                                            ><Edit class="h-4 w-4"
                                        /></Button>
                                        <Button
                                            v-if="
                                                hasPermission(
                                                    'curriculum.delete',
                                                )
                                            "
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-slate-400 hover:text-rose-600"
                                            @click="confirmDelete(area)"
                                            ><Trash2 class="h-4 w-4"
                                        /></Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8"
                                                    ><MoreHorizontal
                                                        class="h-4 w-4"
                                                /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="font-pulsar w-48"
                                            >
                                                <DropdownMenuItem as-child
                                                    ><Link
                                                        :href="`/curriculum/learning-areas/${area.id}`"
                                                        ><Eye
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        Details</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    v-if="
                                                        hasPermission(
                                                            'curriculum.update',
                                                        )
                                                    "
                                                    @click="openEdit(area)"
                                                    ><Edit
                                                        class="mr-2 h-4 w-4"
                                                    />
                                                    Edit</DropdownMenuItem
                                                >
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    v-if="
                                                        hasPermission(
                                                            'curriculum.delete',
                                                        )
                                                    "
                                                    @click="confirmDelete(area)"
                                                    class="font-bold text-rose-600"
                                                    ><Trash2
                                                        class="mr-2 h-4 w-4"
                                                    />
                                                    Delete</DropdownMenuItem
                                                >
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="areas.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center font-medium text-muted-foreground"
                                >
                                    No administrative domains found in the
                                    current cluster search.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </transition>

            <!-- Footer Sync -->
            <div
                class="group mt-2 flex flex-col items-center justify-between gap-6 rounded-2xl border border-slate-700 bg-slate-900 p-5 text-white shadow-xl transition-all hover:bg-slate-950 md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 transition-all group-hover:bg-violet-600 group-hover:text-white"
                    >
                        <ShieldCheck
                            class="h-5 w-5 text-violet-400 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-white/90 uppercase"
                        >
                            Domain Synchronization Active
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-semibold tracking-wider text-slate-400"
                        >
                            Ensuring curriculum consistency across all academic
                            hierarchies mapping to the 2026 Academic Year.
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 cursor-default rounded-xl border-white/10 bg-white/5 px-4 text-xs font-bold text-white uppercase hover:bg-white/20"
                        >Registry: Stable</Button
                    >
                </div>
            </div>

            <!-- Modals -->
            <LearningAreaModal
                :show="showModal"
                :area="editingArea"
                :title="modalTitle"
                @close="showModal = false"
                @success="selectedIds = []"
            />

            <ConfirmDialog
                :show="showConfirm"
                v-bind="confirmConfig"
                @close="showConfirm = false"
                @confirm="confirmConfig.action?.()"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.font-bold {
    font-weight: 950;
}
</style>
