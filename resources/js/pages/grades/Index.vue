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

const page = usePage<{ flash?: { success?: string } }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>('list');
const selectedGradeIds = ref<number[]>([]);
const perPage = ref(20);
const currentPage = ref(1);
const bulkForm = useForm<{
    grade_ids: number[];
    action: 'activate' | 'deactivate' | 'delete' | '';
}>({ grade_ids: [], action: '' });
const actionForm = useForm({});
const showFilters = ref(true);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/grades',
        {
            search: searchQuery.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
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
    () => props.grades,
    () => {
        selectedGradeIds.value = selectedGradeIds.value.filter((id) =>
            props.grades.some((grade) => grade.id === id),
        );
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const filteredGrades = computed(() => {
    return props.grades.filter((grade) => {
        const matchesSearch =
            !searchQuery.value ||
            grade.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            grade.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus =
            selectedStatus.value === 'all' ||
            (selectedStatus.value === 'active'
                ? grade.is_active
                : !grade.is_active);
        return matchesSearch && matchesStatus;
    });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredGrades.value.length / perPage.value)),
);
const paginatedGrades = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredGrades.value.slice(start, start + perPage.value);
});

const allSelectableGradeIds = computed(() =>
    paginatedGrades.value.map((grade) => grade.id),
);
const allSelected = computed(
    () =>
        allSelectableGradeIds.value.length > 0 &&
        allSelectableGradeIds.value.every((id) =>
            selectedGradeIds.value.includes(id),
        ),
);

const toggleAllGrades = () => {
    selectedGradeIds.value = allSelected.value
        ? []
        : [...allSelectableGradeIds.value];
};

const toggleGrade = (gradeId: number) => {
    selectedGradeIds.value = selectedGradeIds.value.includes(gradeId)
        ? selectedGradeIds.value.filter((id) => id !== gradeId)
        : [...selectedGradeIds.value, gradeId];
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
    actionForm.patch(
        `/grades/${grade.id}/${grade.is_active ? 'deactivate' : 'activate'}`,
        { preserveScroll: true },
    );
};

const deleteGrade = (grade: GradeRow) => {
    actionForm.delete(`/grades/${grade.id}`, { preserveScroll: true });
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
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed top-4 right-4 z-50">
            <div
                class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg"
            >
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div
            class="mx-auto max-w-[1600px] animate-in space-y-12 p-6 pb-20 duration-1000 fade-in slide-in-from-bottom-4"
        >
            <!-- Simple Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight text-foreground underline decoration-green-600 decoration-4 underline-offset-8"
                    >
                        Academic Levels
                    </h1>
                    <p
                        class="pt-2 text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Manage grade structures, linked classes, and learner
                        progression.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border px-6 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                    >
                        <List class="mr-2 h-4 w-4 opacity-40" />
                        Hierarchy View
                    </Button>
                    <Link
                        v-if="true"
                        href="/grades/create"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-green-600 px-8 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-green-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Define Level
                    </Link>
                </div>
            </div>

            <!-- TailPanel Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-green-600/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-600/5 text-green-600 transition-all duration-500 group-hover:bg-green-600 group-hover:text-white"
                        >
                            <GraduationCap
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Levels</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Total Grades
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_grades }} Units
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-emerald-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/5 text-emerald-500 transition-all duration-500 group-hover:bg-emerald-500 group-hover:text-white"
                        >
                            <CheckCircle2
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Active</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Live Structure
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.active_grades }} Nodes
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-blue-600/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600/5 text-blue-600 transition-all duration-500 group-hover:bg-blue-600 group-hover:text-white"
                        >
                            <Grid2x2
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Clusters</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Live Classes
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_classes }} Cells
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-500 hover:border-indigo-600/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600/5 text-indigo-600 transition-all duration-500 group-hover:bg-indigo-600 group-hover:text-white"
                        >
                            <Users
                                class="h-5 w-5 opacity-40 group-hover:opacity-100"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Census</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Active Learners
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ stats.total_learners }} Souls
                    </h3>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div
                class="flex flex-col gap-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
            >
                <div
                    class="flex flex-col items-center justify-between gap-4 md:flex-row"
                >
                    <div
                        class="flex w-full flex-1 items-center gap-4 md:max-w-4xl"
                    >
                        <div class="relative flex-1">
                            <Search
                                class="-translate-y-1-2 absolute top-1/2 left-4 h-4 w-4 text-muted-foreground/60"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Filter by name or code..."
                                class="h-12 rounded-xl border-border bg-muted/20 pl-12 text-xs font-bold transition-all placeholder:text-xs placeholder:tracking-widest placeholder:uppercase focus:bg-background"
                            />
                        </div>

                        <div
                            class="flex items-center rounded-xl border border-border bg-muted/20 p-1"
                        >
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-10 w-10 rounded-lg p-0 transition-all',
                                    selectedView === 'grid'
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground/40 hover:text-foreground',
                                ]"
                                @click="selectedView = 'grid'"
                                ><Grid2x2 class="h-4 w-4"
                            /></Button>
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-10 w-10 rounded-lg p-0 transition-all',
                                    selectedView === 'list'
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground/40 hover:text-foreground',
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
                            {{ showFilters ? 'DENSE' : 'REFINE' }}
                        </Button>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            v-if="selectedGradeIds.length > 0"
                            class="animate-in duration-300 slide-in-from-right-4"
                        >
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        class="h-12 rounded-xl border-0 bg-foreground px-8 text-xs font-bold tracking-tight text-background uppercase shadow-xl transition-all hover:bg-foreground/90 active:scale-95"
                                    >
                                        Batch Control ({{
                                            selectedGradeIds.length
                                        }}) <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-64 rounded-xl border border-border p-2 shadow-lg"
                                >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        @click="runBulkAction('activate')"
                                        ><CheckCircle2
                                            class="mr-3 h-4 w-4 text-emerald-500 opacity-60"
                                        />
                                        Activate Units</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        @click="runBulkAction('deactivate')"
                                        ><ShieldOff
                                            class="mr-3 h-4 w-4 text-amber-500 opacity-60"
                                        />
                                        Suspend Units</DropdownMenuItem
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
                                        Purge Records</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div
                    v-if="showFilters"
                    class="grid animate-in gap-6 border-t border-border/50 pt-4 duration-500 fade-in slide-in-from-top-2 md:grid-cols-4"
                >
                    <div class="space-y-3">
                        <label
                            class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >Metric Filter</label
                        >
                        <select
                            v-model="selectedStatus"
                            class="h-12 w-full rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold tracking-tight uppercase transition-all outline-none focus:bg-background"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grid View -->
            <div
                v-if="selectedView === 'grid'"
                class="animate-in space-y-8 duration-700 fade-in slide-in-from-bottom-4"
            >
                <div class="flex items-center gap-3">
                    <button
                        @click="toggleAllGrades"
                        class="flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-card transition-all dark:border-white/10"
                        :class="
                            allSelected
                                ? 'border-green-600 bg-green-600 text-white'
                                : selectedGradeIds.length > 0
                                  ? 'border-green-600 bg-green-500/10 text-green-600'
                                  : 'border-border'
                        "
                    >
                        <Check
                            v-if="allSelected"
                            class="h-4 w-4 stroke-[3px]"
                        />
                        <Minus
                            v-else-if="selectedGradeIds.length > 0"
                            class="h-4 w-4 stroke-[3px]"
                        />
                    </button>
                    <span
                        class="cursor-pointer text-xs font-bold tracking-tight text-muted-foreground/50 uppercase select-none"
                        @click="toggleAllGrades"
                        >Global Grid Sync</span
                    >
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="grade in paginatedGrades"
                        :key="grade.id"
                        class="group relative flex flex-col overflow-hidden rounded-xl border border-border bg-card p-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-lg dark:border-white/5"
                    >
                        <div
                            class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-green-600/5 blur-3xl transition-all duration-700 group-hover:bg-green-600/15"
                        ></div>

                        <div
                            class="relative z-10 mb-8 flex items-start justify-between"
                        >
                            <div class="flex items-start gap-5">
                                <button
                                    @click="toggleGrade(grade.id)"
                                    class="mt-1 flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-background shadow-inner transition-all dark:border-white/10"
                                    :class="
                                        selectedGradeIds.includes(grade.id)
                                            ? 'border-green-600 bg-green-600 text-white shadow-green-600/30'
                                            : 'border-border'
                                    "
                                >
                                    <Check
                                        v-if="
                                            selectedGradeIds.includes(grade.id)
                                        "
                                        class="h-4 w-4 stroke-[3px]"
                                    />
                                </button>
                                <div>
                                    <h2
                                        class="max-w-[200px] truncate text-2xl leading-tight font-bold tracking-tighter text-foreground transition-colors group-hover:text-green-600"
                                    >
                                        {{ grade.name }}
                                    </h2>
                                    <p
                                        class="mt-2 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        {{ grade.code }} •
                                        {{ grade.category.toUpperCase() }} UNIT
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                                    >
                                        {{
                                            grade.lead_name ||
                                            'NO LEAD ASSIGNED'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child
                                    ><Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground"
                                        ><MoreHorizontal
                                            class="h-5 w-5" /></Button
                                ></DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-64 rounded-xl border border-border p-2 shadow-lg"
                                >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link :href="`/grades/${grade.id}`"
                                            ><Eye
                                                class="mr-3 h-4 w-4 text-green-500 opacity-60"
                                            />
                                            Core Intel</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        as-child
                                        ><Link
                                            :href="`/grades/${grade.id}/edit`"
                                            ><Edit
                                                class="mr-3 h-4 w-4 text-amber-500 opacity-60"
                                            />
                                            Modify Structure</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg py-2.5 text-xs font-bold"
                                        @click="toggleGradeStatus(grade)"
                                    >
                                        <component
                                            :is="
                                                grade.is_active
                                                    ? ShieldOff
                                                    : ShieldCheck
                                            "
                                            class="mr-3 h-4 w-4"
                                            :class="
                                                grade.is_active
                                                    ? 'text-amber-500'
                                                    : 'text-emerald-500'
                                            "
                                        />
                                        {{
                                            grade.is_active
                                                ? 'Suspend Node'
                                                : 'Initialize Node'
                                        }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator
                                        class="my-1 bg-border/50"
                                    />
                                    <DropdownMenuItem
                                        class="group rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                        @click="deleteGrade(grade)"
                                        ><Trash2
                                            class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100"
                                        />
                                        Purge Unit</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="relative z-10 mt-auto space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner transition-all group-hover:bg-background dark:border-white/5"
                                >
                                    <p
                                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        Class Cells
                                    </p>
                                    <p
                                        class="text-xl font-bold text-foreground"
                                    >
                                        {{ grade.classes_count }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner transition-all group-hover:bg-background dark:border-white/5"
                                >
                                    <p
                                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        Learner Souls
                                    </p>
                                    <p class="text-xl font-bold text-green-600">
                                        {{ grade.learners_count }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex h-12 gap-3">
                                <Button
                                    variant="outline"
                                    class="h-full flex-1 rounded-xl border-border text-xs font-bold tracking-tight uppercase transition-all hover:border-green-600 hover:bg-green-600 hover:text-white"
                                    as-child
                                >
                                    <Link :href="`/grades/${grade.id}`"
                                        >Sync</Link
                                    >
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-full flex-1 rounded-xl border-border text-xs font-bold tracking-tight uppercase transition-all hover:border-amber-500 hover:bg-amber-500 hover:text-white"
                                    as-child
                                >
                                    <Link :href="`/grades/${grade.id}/edit`"
                                        >Mod</Link
                                    >
                                </Button>
                                <Button
                                    variant="outline"
                                    class="flex w-12 items-center justify-center rounded-xl border-border p-0 transition-all hover:bg-muted"
                                    :class="
                                        grade.is_active
                                            ? 'text-amber-600 hover:bg-amber-50'
                                            : 'text-emerald-600 hover:bg-emerald-50'
                                    "
                                    @click="toggleGradeStatus(grade)"
                                >
                                    <component
                                        :is="
                                            grade.is_active
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

            <!-- List View -->
            <div
                v-else-1
                class="animate-in overflow-hidden rounded-xl border border-border bg-card shadow-lg duration-700 fade-in slide-in-from-bottom-4 dark:border-white/5"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-foreground/10 bg-foreground text-background"
                            >
                                <th
                                    class="w-20 border-r border-background/10 px-8 py-6 text-center"
                                >
                                    <button
                                        @click="toggleAllGrades"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-background shadow-inner transition-all"
                                        :class="
                                            allSelected
                                                ? 'border-green-600 bg-green-600 text-white shadow-green-600/30'
                                                : selectedGradeIds.length > 0
                                                  ? 'border-green-600 bg-green-500/10 text-green-600'
                                                  : 'border-background/20'
                                        "
                                    >
                                        <Check
                                            v-if="allSelected"
                                            class="h-4 w-4 stroke-[3px]"
                                        />
                                        <Minus
                                            v-else-if="
                                                selectedGradeIds.length > 0
                                            "
                                            class="h-4 w-4 stroke-[3px]"
                                        />
                                    </button>
                                </th>
                                <th
                                    class="border-r border-background/10 px-8 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Level Intel
                                </th>
                                <th
                                    class="border-r border-background/10 px-8 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Guardian Intel
                                </th>
                                <th
                                    class="border-r border-background/10 px-8 py-6 text-center text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Cells
                                </th>
                                <th
                                    class="border-r border-background/10 px-8 py-6 text-center text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Souls
                                </th>
                                <th
                                    class="border-r border-background/10 px-8 py-6 text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Node Status
                                </th>
                                <th
                                    class="px-8 py-6 text-right text-xs font-medium tracking-tight uppercase opacity-80"
                                >
                                    Control
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-if="paginatedGrades.length === 0">
                                <td
                                    colspan="7"
                                    class="bg-muted/10 px-8 py-32 text-center text-xl font-bold tracking-tight text-muted-foreground/20 uppercase"
                                >
                                    No Academic Levels Detected in Matrix
                                </td>
                            </tr>
                            <tr
                                v-for="grade in paginatedGrades"
                                :key="grade.id"
                                class="group transition-all duration-300 hover:bg-muted/30"
                            >
                                <td class="px-8 py-5 text-center">
                                    <button
                                        @click="toggleGrade(grade.id)"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-background shadow-inner transition-all dark:border-white/10"
                                        :class="
                                            selectedGradeIds.includes(grade.id)
                                                ? 'border-green-600 bg-green-600 text-white shadow-green-600/30'
                                                : 'border-border'
                                        "
                                    >
                                        <Check
                                            v-if="
                                                selectedGradeIds.includes(
                                                    grade.id,
                                                )
                                            "
                                            class="h-4 w-4 stroke-[3px]"
                                        />
                                    </button>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-5">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl border border-border/50 bg-muted text-sm font-bold text-foreground shadow-inner transition-all duration-500 group-hover:bg-foreground group-hover:text-background"
                                        >
                                            {{ grade.name.charAt(0) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="mb-1 max-w-[180px] truncate text-sm leading-tight font-bold tracking-tight text-foreground transition-colors group-hover:text-green-600"
                                            >
                                                {{ grade.name }}
                                            </p>
                                            <p
                                                class="text-xs leading-none font-bold tracking-tight text-muted-foreground/40 uppercase"
                                            >
                                                {{ grade.code }} •
                                                {{
                                                    grade.category.toUpperCase()
                                                }}
                                                UNIT
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span
                                            class="max-w-[150px] truncate text-xs font-bold text-foreground transition-colors group-hover:text-green-600"
                                            >{{
                                                grade.lead_name || 'NULL NODE'
                                            }}</span
                                        >
                                        <span
                                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/30"
                                            >Level Lead</span
                                        >
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <Badge
                                        variant="outline"
                                        class="h-7 rounded-lg border-border bg-muted/30 px-4 text-xs font-bold tracking-tight text-foreground transition-all group-hover:bg-foreground group-hover:text-background"
                                        >{{ grade.classes_count }} Cells</Badge
                                    >
                                </td>
                                <td
                                    class="px-8 py-5 text-center text-sm font-bold text-green-600 tabular-nums"
                                >
                                    {{ grade.learners_count }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-2 w-2 rounded-full shadow-sm"
                                            :class="
                                                grade.is_active
                                                    ? 'animate-pulse bg-emerald-500'
                                                    : 'bg-muted-foreground/20'
                                            "
                                        ></div>
                                        <span
                                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                            :class="
                                                grade.is_active
                                                    ? 'text-emerald-500'
                                                    : 'text-muted-foreground/30'
                                            "
                                            >{{
                                                grade.is_active
                                                    ? 'INITIALIZED'
                                                    : 'OFFLINE'
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 outline-none"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-10 w-10 rounded-xl border border-transparent p-0 text-muted-foreground opacity-0 transition-all group-hover:opacity-100 hover:border-green-600/20 hover:bg-green-600/10 hover:text-green-600"
                                            as-child
                                            title="Sync Level"
                                        >
                                            <Link :href="`/grades/${grade.id}`"
                                                ><Eye class="h-4.5 w-4.5"
                                            /></Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-10 w-10 rounded-xl border border-transparent p-0 text-muted-foreground opacity-0 transition-all group-hover:opacity-100 hover:border-amber-500/20 hover:bg-amber-500/10 hover:text-amber-500"
                                            as-child
                                            title="Mod Structure"
                                        >
                                            <Link
                                                :href="`/grades/${grade.id}/edit`"
                                                ><Edit class="h-4.5 w-4.5"
                                            /></Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-10 w-10 rounded-xl border border-transparent p-0 opacity-0 transition-all group-hover:opacity-100"
                                            :class="
                                                grade.is_active
                                                    ? 'border-amber-500/10 text-amber-500 hover:bg-amber-50'
                                                    : 'border-emerald-500/10 text-emerald-500 hover:bg-emerald-50'
                                            "
                                            @click="toggleGradeStatus(grade)"
                                            :title="
                                                grade.is_active
                                                    ? 'Suspend Node'
                                                    : 'Initialize Node'
                                            "
                                        >
                                            <component
                                                :is="
                                                    grade.is_active
                                                        ? ShieldOff
                                                        : ShieldCheck
                                                "
                                                class="h-4.5 w-4.5"
                                            />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-10 w-10 rounded-xl border border-transparent p-0 text-rose-500 opacity-0 transition-all group-hover:opacity-100 hover:border-rose-500/20 hover:bg-rose-500/10"
                                            @click="deleteGrade(grade)"
                                            title="Purge Level"
                                        >
                                            <Trash2 class="h-4.5 w-4.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Footer -->
            <div
                class="group/footer relative flex flex-col gap-6 overflow-hidden rounded-xl border-t border-border/30 bg-muted/10 px-10 py-10 shadow-sm md:flex-row md:items-center md:justify-between dark:border-white/5"
            >
                <div
                    class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-green-600/5 blur-3xl transition-all duration-1000 group-hover/footer:bg-green-600/10"
                ></div>

                <div class="relative z-10 flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Segments</span
                        >
                        <select
                            v-model="perPage"
                            @change="currentPage = 1"
                            class="h-10 rounded-xl border border-border bg-background px-4 text-sm font-bold text-foreground transition-all outline-none focus:ring-2 focus:ring-green-600"
                        >
                            <option
                                v-for="n in [10, 20, 50, 100, 500]"
                                :key="n"
                                :value="n"
                            >
                                {{ n }} CELLS
                            </option>
                        </select>
                    </div>
                    <div class="h-10 w-px bg-border/50"></div>
                    <p
                        class="text-xs font-bold tracking-[0.15em] text-muted-foreground/40 uppercase"
                    >
                        {{ filteredGrades.length }} Units active in matrix
                    </p>
                </div>

                <div
                    v-if="totalPages > 1"
                    class="relative z-10 flex items-center gap-2"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-12 w-12 rounded-2xl border-border p-0 text-muted-foreground transition-all hover:bg-muted disabled:opacity-20"
                        :disabled="currentPage === 1"
                        @click="currentPage = Math.max(1, currentPage - 1)"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </Button>

                    <div class="mx-2 flex items-center gap-2">
                        <template v-for="p in totalPages" :key="p">
                            <Button
                                v-if="
                                    Math.abs(p - currentPage) <= 2 ||
                                    p === 1 ||
                                    p === totalPages
                                "
                                variant="outline"
                                class="h-12 min-w-[3rem] rounded-2xl border-border px-4 text-sm font-bold tracking-tighter shadow-sm transition-all duration-300"
                                :class="
                                    currentPage === p
                                        ? 'border-green-600 bg-green-600 text-white shadow-green-600/20'
                                        : 'text-foreground hover:bg-muted'
                                "
                                @click="
                                    currentPage = p;
                                    selectedGradeIds = [];
                                "
                            >
                                {{ p }}
                            </Button>
                            <span
                                v-else-if="Math.abs(p - currentPage) === 3"
                                class="px-1 font-bold text-muted-foreground/30 opacity-50"
                                >...</span
                            >
                        </template>
                    </div>

                    <Button
                        variant="outline"
                        size="sm"
                        class="h-12 w-12 rounded-2xl border-border p-0 text-muted-foreground transition-all hover:bg-muted disabled:opacity-20"
                        :disabled="currentPage === totalPages"
                        @click="
                            currentPage = Math.min(totalPages, currentPage + 1)
                        "
                    >
                        <ChevronRight class="h-5 w-5" />
                    </Button>
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
                        class="flex h-20 w-20 items-center justify-center rounded-3xl border border-white/10 bg-white/5 shadow-lg transition-all duration-700 group-hover:border-green-600 group-hover:bg-green-600"
                    >
                        <ShieldCheck
                            class="h-10 w-10 text-white/30 transition-all duration-500 group-hover:scale-110 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <h3
                            class="text-2xl font-bold tracking-tighter transition-colors duration-500 group-hover:text-green-400"
                        >
                            Structural Synchronization Pulse
                        </h3>
                        <p
                            class="mt-2 text-xs leading-relaxed font-bold tracking-[0.1em] text-muted-foreground text-white/40 uppercase underline decoration-green-500/30 underline-offset-4"
                        >
                            Maintaining active academic clusters for the
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
                            class="animate-pulse rounded-full border border-green-400/20 bg-green-600/20 px-8 py-3 text-sm font-bold tracking-[0.25em] text-green-400 uppercase shadow-[0_0_30px_rgba(34,197,94,0.3)]"
                            >Structure Locked</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
