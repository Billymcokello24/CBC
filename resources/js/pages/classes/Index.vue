<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    BookOpen,
    GraduationCap,
    List,
    MoreHorizontal,
    Plus,
    School,
    Search,
    Users,
    Grid2x2,
    ShieldCheck,
    ShieldOff,
    Eye,
    Edit,
    Trash2,
    CheckCircle2,
    ChevronDown,
    Check,
    Square,
    Minus,
    Filter,
    Layers,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
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

interface ClassRow {
    id: number;
    name: string;
    code: string;
    grade: string | null;
    stream: string | null;
    stream_code: string | null;
    teacher: string | null;
    learners: number;
    capacity: number | null;
    academic_year: string | null;
    utilization: number;
    is_active: boolean;
}

const props = defineProps<{
    classes: {
        data: ClassRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: {
        total_classes: number;
        total_learners: number;
        average_class_size: number;
        grades_count: number;
    };
    filters: {
        search: string;
        grade_id: number | null;
        status: string | null;
        academic_year_id: number | null;
        view: 'grid' | 'list';
        per_page: number;
    };
    grades: Array<{ id: number; name: string }>;
    academicYears: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedGradeId = ref(
    props.filters.grade_id ? String(props.filters.grade_id) : '',
);
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedAcademicYearId = ref(
    props.filters.academic_year_id
        ? String(props.filters.academic_year_id)
        : '',
);
const perPage = ref(props.filters.per_page ?? 20);
const showFilters = ref(false);
const selectedClassIds = ref<number[]>([]);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/classes',
        {
            search: searchQuery.value || undefined,
            grade_id: selectedGradeId.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
            academic_year_id: selectedAcademicYearId.value || undefined,
            view: selectedView.value,
            per_page: perPage.value,
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
watch(selectedGradeId, () => applyFilters());
watch(selectedStatus, () => applyFilters());
watch(selectedAcademicYearId, () => applyFilters());
watch(selectedView, () => applyFilters());
watch(perPage, () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedGradeId.value = '';
    selectedStatus.value = 'all';
    selectedAcademicYearId.value = '';
    applyFilters();
};

watch(
    () => props.classes.data,
    () => {
        selectedClassIds.value = selectedClassIds.value.filter((id) =>
            props.classes.data.some((cls) => cls.id === id),
        );
    },
    { deep: true },
);

const bulkForm = useForm<{
    class_ids: number[];
    action: 'activate' | 'deactivate' | 'delete' | 'promote' | '';
}>({ class_ids: [], action: '' });
const allSelectableClassIds = computed(() =>
    props.classes.data.map((cls) => cls.id),
);
const allSelected = computed(
    () =>
        allSelectableClassIds.value.length > 0 &&
        allSelectableClassIds.value.every((id) =>
            selectedClassIds.value.includes(id),
        ),
);

const toggleAllClasses = () => {
    selectedClassIds.value = allSelected.value
        ? []
        : [...allSelectableClassIds.value];
};

const toggleClassSelection = (classId: number) => {
    selectedClassIds.value = selectedClassIds.value.includes(classId)
        ? selectedClassIds.value.filter((id) => id !== classId)
        : [...selectedClassIds.value, classId];
};

const runBulkAction = (
    action: 'activate' | 'deactivate' | 'delete' | 'promote',
) => {
    if (!selectedClassIds.value.length) return;

    if (
        action === 'promote' &&
        !confirm(
            'Are you sure you want to promote all learners in the selected classes? This will move them to the next grade.',
        )
    ) {
        return;
    }

    bulkForm.class_ids = [...selectedClassIds.value];
    bulkForm.action = action;
    bulkForm.post('/classes/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedClassIds.value = [];
        },
    });
};
const toggleClassStatus = (cls: any) => {
    router.patch(
        `/classes/${cls.id}/${cls.is_active ? 'deactivate' : 'activate'}`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const confirmDeleteClass = (cls: any) => {
    if (confirm(`Are you sure you want to delete ${cls.name}?`)) {
        router.delete(`/classes/${cls.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Matrix Units" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-foreground">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-12 p-6 pb-20 duration-1000 fade-in slide-in-from-bottom-4"
        >
            <!-- Simple Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight text-foreground underline decoration-blue-600 decoration-4 underline-offset-8"
                    >
                        Matrix Units
                    </h1>
                    <p
                        class="pt-2 text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Manage class structures, enrollment density, and
                        academic deployments.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border px-6 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="router.post('/classes/auto-create')"
                    >
                        <Plus class="mr-2 h-4 w-4 opacity-40" />
                        Sync Nodes
                    </Button>
                    <Link
                        href="/classes/create"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-8 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Deploy Unit
                    </Link>
                </div>
            </div>

            <!-- TailPanel Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-blue-600/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600/5 text-blue-600 transition-all duration-500 group-hover:bg-blue-600 group-hover:text-white"
                        >
                            <School
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Topology</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Total Units
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_classes.toLocaleString() }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-emerald-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/5 text-emerald-500 transition-all duration-500 group-hover:bg-emerald-500 group-hover:text-white"
                        >
                            <Users
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Population</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Active Learners
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_learners.toLocaleString() }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-amber-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/5 text-amber-500 transition-all duration-500 group-hover:bg-amber-500 group-hover:text-white"
                        >
                            <Layers
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Density</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Avg Unit Size
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.average_class_size }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-violet-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/5 text-violet-500 transition-all duration-500 group-hover:bg-violet-500 group-hover:text-white"
                        >
                            <GraduationCap
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Hierarchy</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Grade Strata
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.grades_count }} Nodes
                    </h3>
                </div>
            </div>

            <!-- Toolbar -->
            <div
                class="flex flex-col gap-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
            >
                <div
                    class="flex flex-col items-center justify-between gap-4 md:flex-row"
                >
                    <div
                        class="flex w-full flex-1 items-center gap-4 md:max-w-2xl"
                    >
                        <div class="relative flex-1">
                            <Search
                                class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/60"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by name, code or teacher..."
                                class="h-12 rounded-xl border-border bg-muted/20 pl-12 text-sm font-bold transition-all placeholder:text-xs placeholder:tracking-widest placeholder:uppercase focus:bg-background"
                            />
                        </div>

                        <div
                            class="flex h-12 items-center rounded-xl border border-border bg-muted p-1"
                        >
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-10 w-10 rounded-lg p-0 transition-all',
                                    selectedView === 'grid'
                                        ? 'bg-background text-blue-600 shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="selectedView = 'grid'"
                                ><Grid2x2 class="h-4 w-4"
                            /></Button>
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-10 w-10 rounded-lg p-0 transition-all',
                                    selectedView === 'list'
                                        ? 'bg-background text-blue-600 shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="selectedView = 'list'"
                                ><List class="h-4 w-4"
                            /></Button>
                        </div>

                        <Button
                            variant="outline"
                            class="h-12 rounded-xl border-border px-6 text-xs font-bold tracking-tight whitespace-nowrap uppercase"
                            @click="showFilters = !showFilters"
                        >
                            <Filter class="mr-2 h-4 w-4 opacity-40" />
                            {{ showFilters ? 'Collapse' : 'Refine' }}
                        </Button>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button
                            variant="outline"
                            as-child
                            class="h-12 rounded-xl border-border px-6 text-xs font-bold tracking-tight uppercase transition-all hover:bg-muted"
                        >
                            <Link href="/classes/allocations">Allocations</Link>
                        </Button>

                        <div
                            v-if="selectedClassIds.length > 0"
                            class="animate-in duration-300 slide-in-from-right-4"
                        >
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        class="h-12 rounded-xl bg-foreground px-6 text-xs font-bold tracking-tight text-background uppercase shadow-xl hover:bg-foreground/90"
                                    >
                                        Batch Actions ({{
                                            selectedClassIds.length
                                        }})
                                        <ChevronDown
                                            class="ml-2 h-4 w-4 opacity-40"
                                        />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-64 rounded-xl border border-border p-2 shadow-lg"
                                >
                                    <DropdownMenuItem
                                        @click="runBulkAction('activate')"
                                        ><CheckCircle2
                                            class="mr-3 h-4 w-4 text-emerald-500 opacity-60"
                                        />
                                        Activate Signal</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        @click="runBulkAction('deactivate')"
                                        ><ShieldOff
                                            class="mr-3 h-4 w-4 text-amber-500 opacity-60"
                                        />
                                        Deactivate Node</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        @click="runBulkAction('promote')"
                                        ><GraduationCap
                                            class="mr-3 h-4 w-4 text-blue-500 opacity-60"
                                        />
                                        Promote Population</DropdownMenuItem
                                    >
                                    <DropdownMenuSeparator
                                        class="my-1 bg-border/50"
                                    />
                                    <DropdownMenuItem
                                        class="group rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                        @click="runBulkAction('delete')"
                                        ><Trash2
                                            class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100"
                                        />
                                        Purge Matrix</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div
                    v-if="showFilters"
                    class="grid animate-in gap-6 border-t border-border/50 pt-6 duration-300 slide-in-from-top-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="space-y-2">
                        <label
                            class="pl-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >Grade Level</label
                        >
                        <select
                            v-model="selectedGradeId"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/20 px-4 text-sm font-medium tracking-tight uppercase transition-all outline-none hover:bg-muted/30 focus:ring-1 focus:ring-blue-600"
                        >
                            <option value="">All Grade Levels</option>
                            <option
                                v-for="grade in grades"
                                :key="grade.id"
                                :value="String(grade.id)"
                            >
                                {{ grade.name }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="pl-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >Condition</label
                        >
                        <select
                            v-model="selectedStatus"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/20 px-4 text-sm font-medium tracking-tight uppercase transition-all outline-none hover:bg-muted/30 focus:ring-1 focus:ring-blue-600"
                        >
                            <option value="all">All Statuses</option>
                            <option value="active">Active Units</option>
                            <option value="inactive">Inactive Units</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="pl-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >Academic Year</label
                        >
                        <select
                            v-model="selectedAcademicYearId"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/20 px-4 text-sm font-medium tracking-tight uppercase transition-all outline-none hover:bg-muted/30 focus:ring-1 focus:ring-blue-600"
                        >
                            <option value="">All Periods</option>
                            <option
                                v-for="year in $page.props.academicYears as any"
                                :key="year.id"
                                :value="String(year.id)"
                            >
                                {{ year.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <Button
                            variant="ghost"
                            class="h-11 w-full text-xs font-medium tracking-tight text-muted-foreground uppercase hover:text-foreground"
                            @click="clearFilters"
                            >Reset Parameters</Button
                        >
                    </div>
                </div>
            </div>

            <!-- Main Grid View -->
            <div v-if="selectedView === 'grid'" class="space-y-8">
                <div class="flex items-center gap-3 px-2">
                    <button
                        @click="toggleAllClasses"
                        class="flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-card shadow-sm transition-all"
                        :class="
                            allSelected
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : selectedClassIds.length > 0
                                  ? 'border-blue-600 bg-blue-50 text-blue-600'
                                  : 'border-border'
                        "
                    >
                        <Check
                            v-if="allSelected"
                            class="h-4 w-4 stroke-[3px]"
                        />
                        <Minus
                            v-else-if="selectedClassIds.length > 0"
                            class="h-4 w-4 stroke-[3px]"
                        />
                    </button>
                    <span
                        class="cursor-pointer text-xs font-bold tracking-tight text-muted-foreground/60 uppercase transition-colors select-none hover:text-foreground"
                        @click="toggleAllClasses"
                        >Matrix Segment Selection</span
                    >
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="cls in classes.data"
                        :key="cls.id"
                        class="group relative flex flex-col overflow-hidden rounded-xl border border-border bg-card p-8 transition-all duration-500 hover:border-blue-600/20 hover:shadow-lg"
                    >
                        <!-- Holographic Background Effect -->
                        <div
                            class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-blue-600/5 blur-3xl transition-all duration-700 group-hover:bg-blue-600/10"
                        ></div>

                        <div
                            class="relative z-10 mb-8 flex items-start justify-between"
                        >
                            <div class="flex items-center gap-5">
                                <button
                                    @click="toggleClassSelection(cls.id)"
                                    class="flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-card shadow-sm transition-all group-hover:border-blue-600/40"
                                    :class="
                                        selectedClassIds.includes(cls.id)
                                            ? 'border-blue-600 bg-blue-600 text-white'
                                            : 'border-border text-transparent'
                                    "
                                >
                                    <Check class="h-4 w-4 stroke-[3px]" />
                                </button>
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-foreground text-xl font-bold text-background shadow-xl transition-transform duration-500 group-hover:scale-110"
                                >
                                    {{ cls.stream_code || '?' }}
                                </div>
                                <div class="min-w-0">
                                    <h3
                                        class="max-w-[150px] truncate text-xl leading-tight font-bold tracking-tight text-foreground transition-colors group-hover:text-blue-600"
                                    >
                                        {{ cls.name }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        {{ cls.grade || 'Core Segment' }}
                                    </p>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 rounded-xl text-muted-foreground/40 transition-all hover:bg-muted hover:text-foreground"
                                    >
                                        <MoreHorizontal class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-64 rounded-xl border border-border p-2 shadow-lg"
                                >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link :href="`/classes/${cls.id}`"
                                            ><Eye
                                                class="mr-3 h-4 w-4 text-blue-500 opacity-60"
                                            />
                                            Unit Telemetry</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link :href="`/classes/${cls.id}/edit`"
                                            ><Edit
                                                class="mr-3 h-4 w-4 text-amber-500 opacity-60"
                                            />
                                            Adjust Parameters</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuSeparator
                                        class="my-1 bg-border/50"
                                    />
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link href="/students"
                                            ><Users
                                                class="mr-3 h-4 w-4 text-blue-600 opacity-60"
                                            />
                                            Population List</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link
                                            :href="`/students/enrollments/groups/${cls.id}`"
                                            ><Layers
                                                class="mr-3 h-4 w-4 text-emerald-500 opacity-60"
                                            />
                                            Allocation Matrix</Link
                                        ></DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="relative z-10 flex-1 space-y-6">
                            <div
                                class="flex items-center gap-4 rounded-2xl border border-border bg-muted/30 p-5 transition-all duration-500 group-hover:border-blue-600/20 group-hover:bg-blue-600/5"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-background text-foreground/40 shadow-sm transition-colors group-hover:text-blue-600"
                                >
                                    <Users class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        Unit Overseer
                                    </p>
                                    <p
                                        class="truncate text-xs font-bold text-foreground"
                                    >
                                        {{ cls.teacher || 'Unassigned Node' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <span
                                    class="rounded-lg border border-border bg-muted px-3 py-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >{{ cls.academic_year || '2026' }}</span
                                >
                                <span
                                    class="rounded-lg border border-blue-600/20 bg-blue-600/10 px-3 py-1 text-xs font-bold tracking-wider text-blue-600 uppercase"
                                    >{{ cls.stream || 'Segment' }}</span
                                >
                            </div>

                            <div class="pt-2">
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-bold tracking-tight text-muted-foreground/40"
                                        >Density Threshold</span
                                    >
                                    <span
                                        class="text-xs font-bold tracking-tight text-foreground uppercase"
                                    >
                                        <span
                                            :class="
                                                cls.utilization > 90
                                                    ? 'text-rose-500'
                                                    : 'text-foreground'
                                            "
                                            >{{ cls.learners }}</span
                                        >
                                        <span
                                            class="mx-1 text-muted-foreground/30"
                                            >/</span
                                        >
                                        {{ cls.capacity || '∞' }}
                                    </span>
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full border border-border/50 bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full shadow-lg transition-all duration-1000"
                                        :class="
                                            cls.utilization > 90
                                                ? 'bg-rose-500'
                                                : cls.utilization > 70
                                                  ? 'bg-amber-500'
                                                  : 'bg-blue-600'
                                        "
                                        :style="{
                                            width: `${cls.utilization}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-10 flex-1 rounded-xl border-border text-xs font-medium tracking-tight uppercase transition-all hover:bg-foreground hover:text-background"
                                    as-child
                                >
                                    <Link :href="`/classes/${cls.id}`"
                                        >Telemetry</Link
                                    >
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-10 flex-1 rounded-xl border-border text-xs font-medium tracking-tight uppercase transition-all hover:border-amber-500 hover:bg-amber-500 hover:text-white"
                                    as-child
                                >
                                    <Link :href="`/classes/${cls.id}/edit`"
                                        >Adjust</Link
                                    >
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-10 w-10 rounded-xl border-border p-0 transition-all hover:text-white"
                                    :class="
                                        cls.is_active
                                            ? 'text-amber-500 hover:border-amber-500 hover:bg-amber-500'
                                            : 'text-emerald-500 hover:border-emerald-500 hover:bg-emerald-500'
                                    "
                                    @click="toggleClassStatus(cls)"
                                >
                                    <component
                                        :is="
                                            cls.is_active
                                                ? ShieldOff
                                                : ShieldCheck
                                        "
                                        class="h-4 w-4"
                                    />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View Table -->
            <div
                v-else
                class="overflow-hidden rounded-xl border border-border bg-card shadow-lg dark:border-white/5"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-foreground/10 bg-foreground text-background"
                            >
                                <th class="w-16 px-8 py-6 text-center">
                                    <button
                                        @click="toggleAllClasses"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 border-background/20 transition-all hover:border-background/60"
                                        :class="
                                            allSelected
                                                ? 'border-background bg-background text-foreground'
                                                : selectedClassIds.length > 0
                                                  ? 'border-background/40 bg-background/20 text-background'
                                                  : ''
                                        "
                                    >
                                        <Check
                                            v-if="allSelected"
                                            class="h-4 w-4 stroke-[3px]"
                                        />
                                        <Minus
                                            v-else-if="
                                                selectedClassIds.length > 0
                                            "
                                            class="h-4 w-4 stroke-[3px]"
                                        />
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Unit Signal
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Strata
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Timeline
                                </th>
                                <th
                                    class="px-6 py-6 text-center text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Population
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Density Analysis
                                </th>
                                <th
                                    class="px-8 py-6 text-right text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Control
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="cls in classes.data"
                                :key="cls.id"
                                class="group transition-all duration-300 hover:bg-muted/30"
                            >
                                <td class="px-8 py-5 text-center">
                                    <button
                                        @click="toggleClassSelection(cls.id)"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 border-border bg-card shadow-sm transition-all group-hover:border-blue-600/40"
                                        :class="
                                            selectedClassIds.includes(cls.id)
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'text-transparent'
                                        "
                                    >
                                        <Check class="h-4 w-4 stroke-[3px]" />
                                    </button>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-5">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted font-bold text-foreground shadow-inner transition-all duration-500 group-hover:bg-foreground group-hover:text-background"
                                        >
                                            {{ cls.stream_code || '?' }}
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="font-bold tracking-tight text-foreground transition-colors group-hover:text-blue-600"
                                            >
                                                {{ cls.name }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                            >
                                                {{
                                                    cls.teacher ||
                                                    'UNASSIGNED NODE'
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span
                                        class="rounded-lg border border-border bg-muted/40 px-3 py-1 text-xs font-bold tracking-tight text-foreground uppercase"
                                        >{{ cls.grade || '—' }}</span
                                    >
                                </td>
                                <td class="px-6 py-5">
                                    <span
                                        class="text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                                        >{{ cls.academic_year || '—' }}</span
                                    >
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        :class="
                                            cls.utilization > 90
                                                ? 'bg-rose-500 text-white'
                                                : 'bg-muted text-foreground'
                                        "
                                        class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium tracking-tight uppercase shadow-sm"
                                        >{{ cls.learners }} /
                                        {{ cls.capacity || '∞' }}</span
                                    >
                                </td>
                                <td class="min-w-[180px] px-6 py-5">
                                    <div class="flex flex-col gap-2 pt-1">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                                >Usage:
                                                {{ cls.utilization }}%</span
                                            >
                                        </div>
                                        <div
                                            class="h-1.5 w-full overflow-hidden rounded-full border border-border/50 bg-muted"
                                        >
                                            <div
                                                class="h-full rounded-full shadow-md transition-all duration-1000"
                                                :class="
                                                    cls.utilization > 90
                                                        ? 'bg-rose-500'
                                                        : cls.utilization > 70
                                                          ? 'bg-amber-500'
                                                          : 'bg-blue-600'
                                                "
                                                :style="{
                                                    width: `${cls.utilization}%`,
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/classes/${cls.id}`"
                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-transparent bg-muted/20 text-muted-foreground opacity-0 transition-all group-hover:opacity-100 hover:border-blue-600/20 hover:bg-blue-600/10 hover:text-blue-600"
                                            ><Eye class="h-4.5 w-4.5"
                                        /></Link>
                                        <Link
                                            :href="`/classes/${cls.id}/edit`"
                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-transparent bg-muted/20 text-muted-foreground opacity-0 transition-all group-hover:opacity-100 hover:border-amber-500/20 hover:bg-amber-500/10 hover:text-amber-500"
                                            ><Edit class="h-4.5 w-4.5"
                                        /></Link>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-10 w-10 rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground"
                                                >
                                                    <MoreHorizontal
                                                        class="h-4.5 w-4.5"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-64 rounded-xl border border-border p-2 shadow-lg"
                                            >
                                                <DropdownMenuItem
                                                    class="rounded-lg py-2.5 text-xs font-bold"
                                                    as-child
                                                    ><Link
                                                        :href="`/classes/${cls.id}`"
                                                        ><Eye
                                                            class="mr-3 h-4 w-4 text-blue-500 opacity-60"
                                                        />
                                                        Unit Telemetry</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg py-2.5 text-xs font-bold"
                                                    as-child
                                                    ><Link
                                                        :href="`/classes/${cls.id}/edit`"
                                                        ><Edit
                                                            class="mr-3 h-4 w-4 text-amber-500 opacity-60"
                                                        />
                                                        Adjust Parameters</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuSeparator
                                                    class="my-1 bg-border/50"
                                                />
                                                <DropdownMenuItem
                                                    class="group rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                                    @click="
                                                        confirmDeleteClass(cls)
                                                    "
                                                    ><Trash2
                                                        class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100"
                                                    />
                                                    Purge Matrix
                                                    Node</DropdownMenuItem
                                                >
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Premium Pagination Footer -->
            <div
                class="group/footer relative flex flex-col items-center justify-between gap-8 overflow-hidden rounded-2xl border border-border bg-card px-10 py-8 shadow-lg md:flex-row"
            >
                <div
                    class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-blue-600/5 blur-3xl transition-all duration-1000 group-hover/footer:bg-blue-600/10"
                ></div>
                <div class="relative z-10 flex items-center gap-6">
                    <div
                        class="flex items-center gap-3 rounded-2xl border border-border bg-muted/50 px-5 py-2.5 shadow-inner transition-colors group-hover/footer:border-blue-600/20"
                    >
                        <label
                            class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >Density</label
                        >
                        <select
                            v-model="perPage"
                            class="cursor-pointer border-none bg-transparent py-0 text-sm font-bold tracking-tight text-foreground uppercase outline-none focus:ring-0"
                        >
                            <option :value="10">10 PER SEGMENT</option>
                            <option :value="20">20 PER SEGMENT</option>
                            <option :value="50">50 PER SEGMENT</option>
                            <option :value="100">100 PER SEGMENT</option>
                        </select>
                    </div>
                    <p
                        class="text-xs font-bold tracking-[0.15em] text-muted-foreground/30 uppercase"
                    >
                        Telemetry segment {{ classes.current_page }} /
                        {{ classes.last_page }}
                    </p>
                </div>

                <div class="relative z-10 flex items-center gap-2">
                    <template v-for="(link, i) in classes.links" :key="i">
                        <Button
                            v-if="
                                link.url ||
                                i === 0 ||
                                i === classes.links.length - 1
                            "
                            variant="outline"
                            size="sm"
                            :disabled="!link.url || link.active"
                            :class="[
                                'h-12 rounded-2xl border-border px-6 text-xs font-bold tracking-tight uppercase shadow-sm transition-all',
                                link.active
                                    ? 'border-blue-600 bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                    : 'text-foreground hover:bg-muted',
                            ]"
                            @click="
                                link.url && !link.active && router.get(link.url)
                            "
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Global Matrix Pulse -->
            <div
                class="group relative flex flex-col items-center justify-between gap-10 overflow-hidden rounded-2xl bg-slate-900 p-10 text-white shadow-[0_0_60px_rgba(0,0,0,0.3)] md:flex-row dark:bg-black"
            >
                <div
                    class="pointer-events-none absolute -right-24 -bottom-24 text-[12rem] font-bold text-white uppercase opacity-5 transition-all duration-[2000ms] select-none group-hover:scale-125"
                >
                    MATRX
                </div>
                <div class="relative z-10 flex items-center gap-8">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-3xl border border-white/10 bg-white/5 shadow-lg transition-all duration-700 group-hover:border-blue-600 group-hover:bg-blue-600"
                    >
                        <School
                            class="h-10 w-10 text-white/30 transition-all duration-500 group-hover:scale-110 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <h3
                            class="text-2xl font-bold tracking-tighter transition-colors duration-500 group-hover:text-blue-400"
                        >
                            Unit Deployment Active
                        </h3>
                        <p
                            class="mt-2 text-xs leading-relaxed font-bold tracking-[0.1em] text-muted-foreground text-white/40 uppercase"
                        >
                            Synchronizing Class Nodes for the
                            {{
                                $page.props.auth.school?.name || 'Campus'
                            }}
                            Academic Grid.
                        </p>
                    </div>
                </div>
                <div class="relative z-10 flex gap-8">
                    <div class="flex flex-col items-end">
                        <span
                            class="mb-2 text-xs font-bold tracking-tight text-white/30 uppercase opacity-60"
                            >Matrix Status</span
                        >
                        <span
                            class="animate-pulse rounded-full border border-blue-400/20 bg-blue-600/20 px-8 py-3 text-sm font-bold tracking-[0.25em] text-blue-400 uppercase shadow-[0_0_30px_rgba(59,130,246,0.3)]"
                            >Pulse Locked</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
