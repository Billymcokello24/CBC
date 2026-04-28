<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    GraduationCap,
    Grid2x2,
    List,
    Plus,
    Search,
    ShieldCheck,
    ShieldOff,
    Trash2,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    Check,
    Minus,
    Home,
    Users,
    School,
    LayoutGrid,
    MoreHorizontal,
    BookOpen,
    FileText,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface GradeRow {
    id: number;
    name: string;
    code: string;
    category: string;
    level_order: number;
    minimum_age: number | null;
    maximum_age: number | null;
    is_active: boolean;
    classes_count: number;
    learners_count: number;
    lead_name: string | null;
}

const props = defineProps<{
    grades: GradeRow[];
    stats: {
        total_grades: number;
        active_grades: number;
        total_classes: number;
        total_learners: number;
    };
    filters: {
        search: string;
        status: string;
        view: 'grid' | 'list';
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const page = usePage<any>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'list');
const selectedGradeIds = ref<number[]>([]);
const perPage = ref(20);
const currentPage = ref(1);

const bulkForm = useForm<{
    grade_ids: number[];
    action: 'activate' | 'deactivate' | 'delete' | '';
}>({ grade_ids: [], action: '' });
const actionForm = useForm({});

const showFilters = ref(false);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const hasPermission = (permission: string) => {
    const permissions = page.props.auth?.user?.permissions;
    if (Array.isArray(permissions)) {
        return permissions.includes(permission);
    }
    return true; // Fallback to true if permissions not found/not array (simple RBAC)
};

const applyFilters = () => {
    router.get(
        '/grades',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            view: selectedView.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
});

watch([selectedStatus, selectedView], () => applyFilters());

const filteredGrades = computed(() => {
    return props.grades.filter((grade) => {
        const matchesSearch =
            !searchQuery.value ||
            grade.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            grade.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus =
            selectedStatus.value === 'all' ||
            (selectedStatus.value === 'active' ? grade.is_active : !grade.is_active);
        return matchesSearch && matchesStatus;
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredGrades.value.length / perPage.value)));
const paginatedGrades = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredGrades.value.slice(start, start + perPage.value);
});

const toggleSelectAll = () => {
    if (selectedGradeIds.value.length === paginatedGrades.value.length) {
        selectedGradeIds.value = [];
    } else {
        selectedGradeIds.value = paginatedGrades.value.map(g => g.id);
    }
};

const toggleSelectGrade = (id: number) => {
    const index = selectedGradeIds.value.indexOf(id);
    if (index === -1) {
        selectedGradeIds.value.push(id);
    } else {
        selectedGradeIds.value.splice(index, 1);
    }
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedGradeIds.value.length) return;
    bulkForm.grade_ids = [...selectedGradeIds.value];
    bulkForm.action = action;
    bulkForm.post('/grades/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedGradeIds.value = [];
        },
    });
};

const toggleGradeStatus = (grade: GradeRow) => {
    actionForm.patch(`/grades/${grade.id}/${grade.is_active ? 'deactivate' : 'activate'}`, {
        preserveScroll: true,
    });
};

const deleteGrade = (grade: GradeRow) => {
    if (confirm('Are you sure you want to delete this grade level? This action cannot be undone.')) {
        actionForm.delete(`/grades/${grade.id}`, {
            preserveScroll: true,
        });
    }
};

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(flashSuccess, (value) => {
    if (value) {
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => (showToast.value = false), 3000);
    }
}, { immediate: true });
const downloadPdf = () => {
    const params = new URLSearchParams({
        search: searchQuery.value,
        status: selectedStatus.value,
    });
    window.location.href = `/grades/export-pdf?${params.toString()}`;
};
</script>

<template>
    <Head title="Grade Structure" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Toast Notification -->
        <div
            v-if="showToast && flashSuccess"
            class="fixed top-8 right-8 z-50 animate-in duration-500 fade-in slide-in-from-top-4"
        >
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-6 py-4 shadow-xl dark:bg-slate-900 dark:border-emerald-500/20">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white">
                    <CheckCircle2 class="h-4 w-4" />
                </div>
                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Home class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Grade Structure</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">Institutional Grades</h1>
                    <p class="text-sm text-muted-foreground">Configure and manage academic levels and institutional milestones.</p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="downloadPdf"
                    >
                        <FileText class="mr-2 h-4 w-4 text-rose-500" />
                        Download PDF
                    </Button>
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="selectedView = selectedView === 'grid' ? 'list' : 'grid'"
                    >
                        <component :is="selectedView === 'grid' ? List : LayoutGrid" class="mr-2 h-4 w-4 opacity-70" />
                        {{ selectedView === 'grid' ? 'Table View' : 'Grid View' }}
                    </Button>
                    <Link
                        href="/grades/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add New Grade
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all">
                            <School class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Institutional</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_grades }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Total Grade Levels</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-emerald-500/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Active</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active_grades }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Operational Nodes</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-blue-500/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600">
                            <Grid2x2 class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Subdivisions</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_classes }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Active Classes</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-indigo-500/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Learners</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_learners }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Enrolled Students</p>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="group relative flex-1">
                    <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search grades by name or code..."
                        class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold tracking-tight uppercase transition-all focus:bg-background"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <div v-if="selectedGradeIds.length > 0" class="flex items-center gap-2 pr-2 border-r border-border mr-2">
                         <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button class="h-10 rounded-xl bg-foreground text-background px-6 text-[10px] font-bold tracking-tight uppercase">
                                    Bulk actions ({{ selectedGradeIds.length }}) <ChevronDown class="ml-2 h-3 w-3" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 rounded-xl border-border p-2 shadow-xl">
                                <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold" @click="runBulkAction('activate')">
                                    <ShieldCheck class="mr-3 h-4 w-4 text-emerald-500" /> Activate Selected
                                </DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold" @click="runBulkAction('deactivate')">
                                    <ShieldOff class="mr-3 h-4 w-4 text-amber-500" /> Suspend Selected
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="my-1" />
                                <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold text-rose-500" @click="runBulkAction('delete')">
                                    <Trash2 class="mr-3 h-4 w-4" /> Delete Selected
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="showFilters = !showFilters"
                    >
                        <Filter class="mr-2 h-3.5 w-3.5 opacity-70" />
                        {{ showFilters ? 'Hide Filters' : 'Refine view' }}
                    </Button>
                </div>
            </div>

            <!-- Extended Filters -->
            <div v-if="showFilters" class="grid grid-cols-1 gap-6 rounded-2xl border border-border bg-muted/10 p-6 animate-in slide-in-from-top-2 md:grid-cols-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase">Operational Status</label>
                    <select
                        v-model="selectedStatus"
                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-xs font-bold uppercase transition-all focus:ring-2 focus:ring-primary/20"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>

            <!-- Page Content -->
            <div v-if="paginatedGrades.length > 0">
                <!-- Grid View -->
                <div v-if="selectedView === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 px-1">
                    <div
                        v-for="grade in paginatedGrades"
                        :key="grade.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:scale-[1.02] hover:border-primary/20 hover:shadow-xl"
                    >
                        <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-primary/5 blur-2xl transition-all group-hover:bg-primary/10"></div>
                        
                        <div class="relative z-10 flex items-start justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div 
                                    @click="toggleSelectGrade(grade.id)"
                                    class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-2 transition-all"
                                    :class="selectedGradeIds.includes(grade.id) ? 'bg-primary border-primary text-white' : 'border-border bg-background'"
                                >
                                    <Check v-if="selectedGradeIds.includes(grade.id)" class="h-3 w-3 stroke-[3px]" />
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/5 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                                    <span class="text-xl font-black">{{ grade.code[0] }}</span>
                                </div>
                            </div>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl opacity-40 hover:opacity-100">
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-52 rounded-xl border-border p-2 shadow-xl">
                                    <DropdownMenuItem as-child class="rounded-lg py-2.5 text-xs font-bold">
                                        <Link :href="`/grades/${grade.id}`">
                                            <Eye class="mr-3 h-4 w-4 opacity-60" /> View Statistics
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child class="rounded-lg py-2.5 text-xs font-bold text-primary">
                                        <Link :href="`/grades/${grade.id}/edit`">
                                            <Edit class="mr-3 h-4 w-4 opacity-60" /> Edit level
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1" />
                                    <DropdownMenuItem @click="toggleGradeStatus(grade)" class="rounded-lg py-2.5 text-xs font-bold">
                                        <component :is="grade.is_active ? ShieldOff : ShieldCheck" class="mr-3 h-4 w-4 opacity-60" />
                                        {{ grade.is_active ? 'Suspend node' : 'Activate node' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="deleteGrade(grade)" class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                        <Trash2 class="mr-3 h-4 w-4 opacity-60" /> Delete grade
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="relative z-10 space-y-4">
                            <div>
                                <h3 class="text-lg font-bold text-foreground group-hover:text-primary transition-colors leading-tight">
                                    {{ grade.name }}
                                </h3>
                                <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">
                                    {{ grade.code }} • {{ grade.category.toUpperCase() }} UNIT
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 py-4 border-y border-border/50">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-muted-foreground/30 uppercase">Classes</span>
                                    <span class="text-base font-bold text-foreground tabular-nums">{{ grade.classes_count }}</span>
                                </div>
                                <div class="flex flex-col border-l border-border/50 pl-4">
                                    <span class="text-[9px] font-bold text-muted-foreground/30 uppercase">Learners</span>
                                    <span class="text-base font-bold text-primary tabular-nums">{{ grade.learners_count }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <Badge
                                    variant="secondary"
                                    class="rounded-lg border-none bg-emerald-500/10 text-[10px] font-bold text-emerald-600 uppercase tracking-tighter"
                                    v-if="grade.is_active"
                                >
                                    Operational
                                </Badge>
                                <Badge
                                    variant="secondary"
                                    class="rounded-lg border-none bg-rose-500/10 text-[10px] font-bold text-rose-600 uppercase tracking-tighter"
                                    v-else
                                >
                                    Suspended
                                </Badge>
                                
                                <p class="text-[10px] font-bold text-muted-foreground opacity-40 uppercase truncate max-w-[100px]">
                                    {{ grade.lead_name || 'No Lead' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm mx-1">
                    <div class="scrollbar-hide overflow-x-auto">
                        <table class="w-full min-w-[1000px] text-left">
                            <thead class="bg-muted/30">
                                <tr class="text-muted-foreground">
                                    <th class="px-6 py-5 text-center w-16">
                                        <div 
                                            @click="toggleSelectAll"
                                            class="mx-auto flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-2 transition-all"
                                            :class="selectedGradeIds.length === paginatedGrades.length && paginatedGrades.length > 0 ? 'bg-primary border-primary text-white' : 'border-border bg-background'"
                                        >
                                            <Check v-if="selectedGradeIds.length === paginatedGrades.length && paginatedGrades.length > 0" class="h-3 w-3 stroke-[3px]" />
                                            <Minus v-else-if="selectedGradeIds.length > 0" class="h-3 w-3 stroke-[3px]" />
                                        </div>
                                    </th>
                                    <th class="px-6 py-5 text-[10px] font-bold tracking-tight uppercase">Grade identity</th>
                                    <th class="px-6 py-5 text-[10px] font-bold tracking-tight uppercase">Level Lead</th>
                                    <th class="px-6 py-5 text-center text-[10px] font-bold tracking-tight uppercase">Composition</th>
                                    <th class="px-6 py-5 text-center text-[10px] font-bold tracking-tight uppercase">Status</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-bold tracking-tight uppercase">Control</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/30">
                                <tr v-for="grade in paginatedGrades" :key="grade.id" class="group transition-all hover:bg-muted/30">
                                    <td class="px-6 py-4 text-center">
                                        <div 
                                            @click="toggleSelectGrade(grade.id)"
                                            class="mx-auto flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-2 transition-all"
                                            :class="selectedGradeIds.includes(grade.id) ? 'bg-primary border-primary text-white' : 'border-border bg-background'"
                                        >
                                            <Check v-if="selectedGradeIds.includes(grade.id)" class="h-3 w-3 stroke-[3px]" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/5 text-primary font-black group-hover:scale-110 transition-all">
                                                {{ grade.code[0] }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-foreground">{{ grade.name }}</span>
                                                <span class="text-[10px] font-bold text-muted-foreground/40 uppercase">{{ grade.code }} • {{ grade.category }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-foreground opacity-80">{{ grade.lead_name || 'Unassigned' }}</span>
                                            <span class="text-[9px] font-bold text-muted-foreground/40 uppercase">Assigned Lead</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-8">
                                            <div class="flex flex-col items-center">
                                                <span class="text-[9px] font-bold text-muted-foreground/30 uppercase">Classes</span>
                                                <span class="text-xs font-bold tabular-nums">{{ grade.classes_count }}</span>
                                            </div>
                                            <div class="flex flex-col items-center">
                                                <span class="text-[9px] font-bold text-muted-foreground/30 uppercase">Learners</span>
                                                <span class="text-xs font-bold text-primary tabular-nums">{{ grade.learners_count }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Badge
                                            variant="secondary"
                                            class="px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-tight"
                                            :class="grade.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600'"
                                        >
                                            {{ grade.is_active ? 'Operational' : 'Suspended' }}
                                        </Badge>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                            <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-muted-foreground hover:bg-primary/10 hover:text-primary" as-child>
                                                <Link :href="`/grades/${grade.id}`"><Eye class="h-4 w-4" /></Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-muted-foreground hover:bg-primary/10 hover:text-primary" as-child>
                                                <Link :href="`/grades/${grade.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                            </Button>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" class="w-56 rounded-xl border-border p-2 shadow-xl">
                                                    <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold" @click="toggleGradeStatus(grade)">
                                                        <component :is="grade.is_active ? ShieldOff : ShieldCheck" class="mr-3 h-4 w-4 opacity-70" />
                                                        Update Status
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator class="my-1" />
                                                    <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold text-rose-500" @click="deleteGrade(grade)">
                                                        <Trash2 class="mr-3 h-4 w-4" /> Purge node
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

                <!-- Overdue/Pagination Progress -->
                <div v-if="totalPages > 1" class="flex flex-col items-center justify-between gap-4 border-t border-border/50 bg-muted/5 px-6 py-6 md:flex-row shadow-sm rounded-2xl mx-1 mt-6">
                    <p class="text-[10px] font-bold text-muted-foreground/40 uppercase tracking-widest">
                        Node {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, filteredGrades.length) }} of {{ filteredGrades.length }} Institutional Levels
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            class="h-10 rounded-xl border-border bg-background px-5 text-xs font-bold tracking-tight uppercase"
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                        >
                            Back
                        </Button>
                        <div class="flex h-10 min-w-[40px] items-center justify-center rounded-xl bg-primary text-xs font-black text-white shadow-lg shadow-primary/20 p-2">
                            {{ currentPage }} / {{ totalPages }}
                        </div>
                        <Button
                            variant="outline"
                            class="h-10 rounded-xl border-border bg-background px-5 text-xs font-bold tracking-tight uppercase"
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center space-y-6 rounded-3xl border border-dashed border-border/80 bg-muted/5 py-40 mx-1">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-muted/20 text-muted-foreground/20">
                    <School class="h-10 w-10" />
                </div>
                <div class="space-y-2 text-center">
                    <h3 class="text-xl font-bold text-foreground">No Grade Structures Found</h3>
                    <p class="max-w-xs text-sm font-medium text-muted-foreground/60 mx-auto">We couldn't find any levels matching your criteria. Reset filters or add a new grade level.</p>
                </div>
                <Button variant="outline" class="h-11 rounded-xl px-8 text-xs font-bold uppercase tracking-tight" @click="searchQuery = ''; selectedStatus = 'all'">Reset Grid</Button>
            </div>

            <!-- Footer Message -->
            <div class="relative overflow-hidden rounded-3xl bg-slate-900 dark:bg-black p-8 text-white shadow-2xl">
                 <div class="absolute -right-16 -bottom-16 h-64 w-64 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                 <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 border border-white/5 shadow-2xl transition-all hover:bg-primary hover:scale-110">
                            <ShieldCheck class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <h4 class="text-lg font-bold tracking-tight">Institutional Architecture Pulse</h4>
                            <p class="text-xs text-white/50 font-medium max-w-md mt-1">Maintaining synchronization across the academic hierarchy. All changes here reflect across the institution dynamically.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="border-white/20 text-white/40 text-[9px] font-black uppercase tracking-widest px-3 py-1">Structure Locked</Badge>
                    </div>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>
