<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
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
    ChevronRight,
    Home,
    Check,
    Square,
    Minus,
    Filter,
    Layers,
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

const page = usePage<any>();

const hasPermission = (permission: string) => {
    return page.props.auth.user.permissions?.includes(permission);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: Home },
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

const downloadPdf = () => {
    const params = new URLSearchParams({
        search: searchQuery.value,
        grade_id: selectedGradeId.value,
        status: selectedStatus.value,
        academic_year_id: selectedAcademicYearId.value,
    });
    window.location.href = `/classes/export-pdf?${params.toString()}`;
};
</script>

<template>
    <Head title="Classes" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-foreground">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Classes
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        View and manage classes, students, and teachers.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-lg border-border px-6 text-xs font-bold uppercase transition-all hover:bg-muted"
                        @click="downloadPdf"
                    >
                        <FileText class="mr-2 h-4 w-4 text-rose-500" />
                        Download PDF
                    </Button>
                    <Button
                        variant="outline"
                        class="h-10 rounded-lg border-border px-6 text-xs font-bold uppercase transition-all hover:bg-muted"
                        @click="router.post('/classes/auto-create')"
                    >
                        <Plus class="mr-2 h-4 w-4 opacity-40" />
                        Generate Classes
                    </Button>
                    <Link
                        v-if="hasPermission('classes.create')"
                        href="/classes/create"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-8 text-xs font-bold text-white uppercase shadow-sm transition-all hover:opacity-90"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Create Class
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary transition-all"
                        >
                            <School
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Classes</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold text-muted-foreground uppercase opacity-60"
                    >
                        Total Classes
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_classes.toLocaleString() }}
                    </h3>
                </div>

                <div
                    class="group rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 transition-all"
                        >
                            <Users
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Students</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold text-muted-foreground uppercase opacity-60"
                    >
                        Total Students
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_learners.toLocaleString() }}
                    </h3>
                </div>

                <div
                    class="group rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500 transition-all"
                        >
                            <Layers
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Size</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold text-muted-foreground uppercase opacity-60"
                    >
                        Average Class Size
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.average_class_size }}
                    </h3>
                </div>

                <div
                    class="group rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10 text-violet-500 transition-all"
                        >
                            <GraduationCap
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Grades</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold text-muted-foreground uppercase opacity-60"
                    >
                        Total Grade Levels
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.grades_count }} Grades
                    </h3>
                </div>
            </div>

            <div
                class="flex flex-col gap-6 rounded-xl border border-border bg-card p-6 shadow-sm"
            >
                <div
                    class="flex flex-col items-center justify-between gap-4 md:flex-row"
                >
                    <div
                        class="flex w-full flex-1 items-center gap-4 md:max-w-2xl"
                    >
                        <div class="relative flex-1">
                            <Search
                                class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search classes..."
                                class="h-10 rounded-lg border-border bg-muted/20 pl-12 text-sm font-semibold transition-all focus:bg-background"
                            />
                        </div>

                        <div
                            class="flex h-10 items-center rounded-lg border border-border bg-muted p-1"
                        >
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-8 w-8 rounded-md p-0 transition-all',
                                    selectedView === 'grid'
                                        ? 'bg-card text-primary shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="selectedView = 'grid'"
                                ><Grid2x2 class="h-4 w-4"
                            /></Button>
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-8 w-8 rounded-md p-0 transition-all',
                                    selectedView === 'list'
                                        ? 'bg-card text-primary shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="selectedView = 'list'"
                                ><List class="h-4 w-4"
                            /></Button>
                        </div>

                        <Button
                            variant="outline"
                            class="h-10 rounded-lg border-border px-6 text-xs font-bold uppercase transition-all"
                            @click="showFilters = !showFilters"
                        >
                            <Filter class="mr-2 h-4 w-4 opacity-40" />
                            Filters
                        </Button>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button
                            variant="outline"
                            as-child
                            class="h-10 rounded-lg border-border px-6 text-xs font-bold uppercase transition-all"
                        >
                            <Link href="/classes/allocations">Allocations</Link>
                        </Button>

                        <div
                            v-if="selectedClassIds.length > 0"
                        >
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        class="h-10 rounded-lg bg-primary px-6 text-xs font-bold text-white uppercase shadow-sm"
                                    >
                                        Bulk Actions ({{
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
                                            class="mr-3 h-4 w-4 text-emerald-500"
                                        />
                                        Activate</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        @click="runBulkAction('deactivate')"
                                        ><ShieldOff
                                            class="mr-3 h-4 w-4 text-amber-500"
                                        />
                                        Deactivate</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        @click="runBulkAction('promote')"
                                        ><GraduationCap
                                            class="mr-3 h-4 w-4 text-primary"
                                        />
                                        Promote Students</DropdownMenuItem
                                    >
                                    <DropdownMenuSeparator
                                        class="my-1 bg-border/50"
                                    />
                                    <DropdownMenuItem
                                        class="group rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                        @click="runBulkAction('delete')"
                                        ><Trash2
                                            class="mr-3 h-4 w-4"
                                        />
                                        Delete Selected</DropdownMenuItem
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
                            class="pl-1 text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Grade Level</label
                        >
                        <select
                            v-model="selectedGradeId"
                            class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold transition-all outline-none focus:bg-background"
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
                            class="pl-1 text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Status</label
                        >
                        <select
                            v-model="selectedStatus"
                            class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold transition-all outline-none focus:bg-background"
                        >
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="pl-1 text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Academic Year</label
                        >
                        <select
                            v-model="selectedAcademicYearId"
                            class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold transition-all outline-none focus:bg-background"
                        >
                            <option value="">All Years</option>
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
                            >Clear Filters</Button
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
                                ? 'border-primary bg-primary text-white'
                                : selectedClassIds.length > 0
                                  ? 'border-primary bg-primary/10 text-primary'
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
                        >Class Selection</span
                    >
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="cls in classes.data"
                        :key="cls.id"
                        class="group relative flex flex-col overflow-hidden rounded-xl border border-border bg-card p-8 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-lg"
                    >
                        <!-- Holographic Background Effect -->
                        <div
                            class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-primary/5 blur-3xl transition-all duration-700 group-hover:bg-primary/10"
                        ></div>

                        <div
                            class="relative z-10 mb-8 flex items-start justify-between"
                        >
                            <div class="flex items-center gap-5">
                                <button
                                    @click="toggleClassSelection(cls.id)"
                                    class="flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-card shadow-sm transition-all group-hover:border-primary/40"
                                    :class="
                                        selectedClassIds.includes(cls.id)
                                            ? 'border-primary bg-primary text-white'
                                            : 'border-border text-transparent'
                                    "
                                >
                                    <Check class="h-4 w-4 stroke-[3px]" />
                                </button>
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-900 text-xl font-bold text-white shadow-md transition-transform duration-300 group-hover:scale-105"
                                >
                                    {{ cls.stream_code || '?' }}
                                </div>
                                <div class="min-w-0">
                                    <h3
                                        class="max-w-[150px] truncate text-xl leading-tight font-bold tracking-tight text-foreground transition-colors group-hover:text-primary"
                                    >
                                        {{ cls.name }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        {{ cls.grade || 'General' }}
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
                                                class="mr-3 h-4 w-4 text-primary"
                                            />
                                            View Details</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link :href="`/classes/${cls.id}/edit`"
                                            ><Edit
                                                class="mr-3 h-4 w-4 text-primary"
                                            />
                                            Edit Class</Link
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
                                                class="mr-3 h-4 w-4 text-primary"
                                            />
                                            Students</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link
                                            :href="`/students/enrollments/groups/${cls.id}`"
                                            ><Layers
                                                class="mr-3 h-4 w-4 text-primary"
                                            />
                                            Enrolments</Link
                                        ></DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="relative z-10 flex-1 space-y-6">
                            <div
                                class="flex items-center gap-4 rounded-2xl border border-border bg-muted/30 p-5 transition-all duration-500 group-hover:border-primary/20 group-hover:bg-primary/5"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-background text-primary/40 shadow-sm transition-colors group-hover:text-primary"
                                >
                                    <Users class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        Class Teacher
                                    </p>
                                    <p
                                        class="truncate text-xs font-bold text-foreground"
                                    >
                                        {{ cls.teacher || 'Unassigned' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <span
                                    class="rounded-lg border border-border bg-muted px-3 py-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >{{ cls.academic_year || '2026' }}</span
                                >
                                <span
                                    class="rounded-lg border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-bold tracking-wider text-primary uppercase"
                                    >{{ cls.stream || 'General' }}</span
                                >
                            </div>

                            <div class="pt-2">
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                                        >Class Enrollment</span
                                    >
                                    <span
                                        class="text-xs font-bold text-foreground uppercase"
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
                                            >of</span
                                        >
                                        {{ cls.capacity || '∞' }}
                                    </span>
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full border border-border/50 bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full shadow-sm transition-all duration-1000"
                                        :class="
                                            cls.utilization > 90
                                                ? 'bg-rose-500'
                                                : cls.utilization > 70
                                                  ? 'bg-amber-500'
                                                  : 'bg-primary'
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
                                    class="h-10 flex-1 rounded-lg border-border text-xs font-bold uppercase transition-all hover:bg-slate-900 hover:text-white"
                                    as-child
                                >
                                    <Link :href="`/classes/${cls.id}`"
                                        >Details</Link
                                    >
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-10 flex-1 rounded-xl border-border text-xs font-medium tracking-tight uppercase transition-all hover:border-amber-500 hover:bg-amber-500 hover:text-white"
                                    as-child
                                >
                                    <Link :href="`/classes/${cls.id}/edit`"
                                        >Edit</Link
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
                                    class="px-6 py-6 text-xs font-semibold uppercase opacity-80"
                                >
                                    Class
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-semibold uppercase opacity-80"
                                >
                                    Grade
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-semibold uppercase opacity-80"
                                >
                                    Year
                                </th>
                                <th
                                    class="px-6 py-6 text-center text-xs font-semibold uppercase opacity-80"
                                >
                                    Students
                                </th>
                                <th
                                    class="px-6 py-6 text-xs font-semibold uppercase opacity-80"
                                >
                                    Capacity
                                </th>
                                <th
                                    class="px-8 py-6 text-right text-xs font-semibold uppercase opacity-80"
                                >
                                    Actions
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
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 font-bold text-white shadow-sm"
                                        >
                                            {{ cls.stream_code || '?' }}
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="font-bold text-foreground transition-colors group-hover:text-primary"
                                            >
                                                {{ cls.name }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs font-bold text-muted-foreground uppercase opacity-40"
                                            >
                                                {{
                                                    cls.teacher ||
                                                    'Unassigned'
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
                                                class="text-[10px] font-bold text-muted-foreground uppercase opacity-40"
                                                >{{ cls.utilization }}% Full</span
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
                                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-transparent bg-muted/20 text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            ><Eye class="h-4 w-4"
                                        /></Link>
                                        <Link
                                            :href="`/classes/${cls.id}/edit`"
                                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-transparent bg-muted/20 text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            ><Edit class="h-4 w-4"
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
                                                            class="mr-3 h-4 w-4 text-primary"
                                                        />
                                                        View Details</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg py-2.5 text-xs font-bold"
                                                    as-child
                                                    ><Link
                                                        :href="`/classes/${cls.id}/edit`"
                                                        ><Edit
                                                            class="mr-3 h-4 w-4 text-primary"
                                                        />
                                                        Edit Class</Link
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
                                                        class="mr-3 h-4 w-4"
                                                    />
                                                    Delete Class</DropdownMenuItem
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

            <!-- Pagination Footer -->
            <div
                class="relative flex flex-col items-center justify-between gap-8 rounded-xl border border-border bg-card px-10 py-8 shadow-sm md:flex-row"
            >
                <div class="relative z-10 flex items-center gap-6">
                    <div
                        class="flex items-center gap-3 rounded-lg border border-border bg-muted/50 px-5 py-2.5 shadow-sm"
                    >
                        <label
                            class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                            >Rows</label
                        >
                        <select
                            v-model="perPage"
                            class="cursor-pointer border-none bg-transparent py-0 text-sm font-bold text-foreground outline-none focus:ring-0"
                        >
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                    <p
                        class="text-xs font-bold text-muted-foreground uppercase opacity-40"
                    >
                        Page {{ classes.current_page }} of
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
                                'h-10 rounded-lg border-border px-6 text-xs font-bold uppercase transition-all',
                                link.active
                                    ? 'bg-primary text-white'
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

            <!-- Status Banner -->
            <div
                class="group relative flex flex-col items-center justify-between gap-10 overflow-hidden rounded-xl bg-slate-900 p-10 text-white shadow-lg md:flex-row"
            >
                <div class="relative z-10 flex items-center gap-8">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-xl bg-white/5 shadow-lg border border-white/10"
                    >
                        <School
                            class="h-8 w-8 text-white/30"
                        />
                    </div>
                    <div>
                        <h3
                            class="text-2xl font-bold uppercase"
                        >
                            System Status
                        </h3>
                        <p
                            class="mt-2 text-xs font-medium text-white/40 uppercase"
                        >
                            All class records are synchronized and up to date for
                            {{
                                $page.props.auth.school?.name || 'Institutional'
                            }}
                            academic records.
                        </p>
                    </div>
                </div>
                <div class="relative z-10 flex gap-8">
                    <div class="flex flex-col items-end">
                        <span
                            class="mb-2 text-xs font-bold text-white/20 uppercase"
                            >Database Status</span
                        >
                        <span
                            class="rounded-full border border-emerald-500/20 bg-emerald-500/20 px-8 py-2 text-sm font-bold text-emerald-400 uppercase"
                            >Connected</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
