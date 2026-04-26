<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    ChevronDown,
    ChevronRight,
    Edit,
    FileText,
    Filter,
    GraduationCap,
    MoreHorizontal,
    Plus,
    Search,
    Trash2,
    CheckCircle2,
    Clock,
    AlertCircle,
    Sparkles,
    LayoutGrid,
    ListFilter,
    Users,
    TrendingUp,
    FileCheck,
    Eye,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface Scheme {
    id: number;
    title: string;
    status: 'draft' | 'submitted' | 'approved' | 'rejected';
    total_weeks: number;
    lessons_per_week: number;
    subject?: { name: string; id: number };
    grade_level?: { name: string; short_name: string; id: number };
    academic_term?: { name: string; id: number };
    description?: string;
    prepared_by_user?: { name: string };
    created_at: string;
}

interface Props {
    schemes: Scheme[];
    subjects: Array<{ id: number; name: string }>;
    grades: Array<{ id: number; name: string }>;
    terms: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const page = usePage<{ flash: { success?: string; error?: string } }>();

const breadcrumbs = [
    { title: 'Curriculum', href: '#' },
    { title: 'Academic Planner', href: '#' },
    { title: 'Schemes of Work', href: '/curriculum/planner/schemes' },
];

const showToast = ref(false);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

watch(flashSuccess, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
});

watch(flashError, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
});

// Filtering
const searchQuery = ref('');
const selectedSubject = ref('all');
const selectedGrade = ref('all');
const selectedTerm = ref('all');
const showFilters = ref(false);

const filteredSchemes = computed(() => {
    return props.schemes.filter((scheme) => {
        const matchesSearch =
            scheme.title
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            scheme.subject?.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase());
        const matchesSubject =
            selectedSubject.value === 'all' ||
            scheme.subject?.id === Number(selectedSubject.value);
        const matchesGrade =
            selectedGrade.value === 'all' ||
            scheme.grade_level?.id === Number(selectedGrade.value);
        const matchesTerm =
            selectedTerm.value === 'all' ||
            scheme.academic_term?.id === Number(selectedTerm.value);

        return matchesSearch && matchesSubject && matchesGrade && matchesTerm;
    });
});

// Modal Logic
const isAddingScheme = ref(false);
const isEditing = ref(false);
const selectedSchemeId = ref<number | null>(null);

const schemeForm = useForm({
    subject_id: '',
    grade_level_id: '',
    academic_term_id: '',
    title: '',
    description: '',
    total_weeks: 13,
    lessons_per_week: 5,
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedSchemeId.value = null;
    schemeForm.reset();
    isAddingScheme.value = true;
};

const editScheme = (scheme: Scheme) => {
    isEditing.value = true;
    selectedSchemeId.value = scheme.id;
    schemeForm.subject_id = scheme.subject?.id ? String(scheme.subject.id) : '';
    schemeForm.grade_level_id = scheme.grade_level?.id
        ? String(scheme.grade_level.id)
        : '';
    schemeForm.academic_term_id = scheme.academic_term?.id
        ? String(scheme.academic_term.id)
        : '';
    schemeForm.title = scheme.title;
    schemeForm.description = scheme.description || '';
    schemeForm.total_weeks = scheme.total_weeks;
    schemeForm.lessons_per_week = scheme.lessons_per_week;
    isAddingScheme.value = true;
};

const submitScheme = () => {
    if (isEditing.value && selectedSchemeId.value) {
        schemeForm.put(
            `/curriculum/planner/schemes/${selectedSchemeId.value}`,
            {
                onSuccess: () => {
                    isAddingScheme.value = false;
                    schemeForm.reset();
                },
            },
        );
    } else {
        schemeForm.post('/curriculum/planner/schemes', {
            onSuccess: () => {
                isAddingScheme.value = false;
                schemeForm.reset();
            },
        });
    }
};

const deleteScheme = (id: number) => {
    if (confirm('Are you sure you want to delete this scheme?')) {
        router.delete(`/curriculum/planner/schemes/${id}`);
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'rejected':
            return 'bg-rose-50 text-rose-600 border-rose-100';
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

const updateStatus = (id: number, action: string) => {
    router.post(`/curriculum/planner/schemes/${id}/${action}`);
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved':
            return CheckCircle2;
        case 'submitted':
            return Clock;
        case 'rejected':
            return AlertCircle;
        default:
            return FileText;
    }
};
</script>

<template>
    <Head title="Schemes of Work" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Notifications -->
        <div
            v-if="showToast && (flashSuccess || flashError)"
            class="fixed top-8 right-8 z-[100] animate-in duration-300 slide-in-from-right-4"
        >
            <div
                :class="[
                    'flex items-center gap-3 rounded-2xl border px-6 py-4 shadow-lg backdrop-blur-xl',
                    flashSuccess
                        ? 'border-emerald-200 bg-emerald-50/90 text-emerald-800'
                        : 'border-rose-200 bg-rose-50/90 text-rose-800',
                ]"
            >
                <div
                    :class="[
                        'flex h-8 w-8 items-center justify-center rounded-xl',
                        flashSuccess
                            ? 'bg-emerald-500 text-white'
                            : 'bg-rose-500 text-white',
                    ]"
                >
                    <CheckCircle2 v-if="flashSuccess" class="h-5 w-5" />
                    <Sparkles v-else class="h-5 w-5" />
                </div>
                <div class="flex flex-col">
                    <p class="text-[13px] font-bold tracking-tight">
                        {{ flashSuccess || flashError }}
                    </p>
                    <p class="text-xs font-medium opacity-70">
                        Operation completed successfully
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col justify-between gap-4 px-1 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <h1
                        class="flex items-center gap-3 text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        <Calendar class="h-8 w-8 text-blue-600" />
                        Teaching Plans
                    </h1>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">
                        Create and manage your weekly work plans for each subject.
                    </p>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-muted"
                        @click="showFilters = !showFilters"
                    >
                        <Filter class="mr-2 h-4 w-4 opacity-70" />
                        Filters
                    </Button>
                    <Button
                        @click="openCreateModal"
                        class="h-10 rounded-xl bg-blue-600 px-5 text-xs font-bold tracking-tight text-white uppercase shadow-lg transition-all hover:bg-blue-700 active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Scheme
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4">
                <div
                    v-for="stat in [
                        {
                            label: 'Total',
                            value: filteredSchemes.length,
                            icon: FileText,
                            color: 'text-blue-600',
                            bg: 'bg-blue-50',
                        },
                        {
                            label: 'Pending',
                            value: filteredSchemes.filter(
                                (s) => s.status === 'submitted',
                            ).length,
                            icon: Clock,
                            color: 'text-amber-600',
                            bg: 'bg-amber-50',
                        },
                        {
                            label: 'Approved',
                            value: filteredSchemes.filter(
                                (s) => s.status === 'approved',
                            ).length,
                            icon: CheckCircle2,
                            color: 'text-emerald-600',
                            bg: 'bg-emerald-50',
                        },
                        {
                            label: 'Avg Sched',
                            value: '5 L/Wk',
                            icon: TrendingUp,
                            color: 'text-indigo-600',
                            bg: 'bg-indigo-50',
                        },
                    ]"
                    :key="stat.label"
                    class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-300 hover:border-blue-500/50 sm:p-6 dark:border-white/5"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground uppercase sm:text-xs"
                        >
                            {{ stat.label }}
                        </p>
                        <div
                            :class="[
                                'flex h-7 w-7 items-center justify-center rounded-lg transition-colors sm:h-9 sm:w-9',
                                stat.bg,
                                stat.color,
                            ]"
                        >
                            <component
                                :is="stat.icon"
                                class="h-3.5 w-3.5 sm:h-5 sm:w-5"
                            />
                        </div>
                    </div>
                    <h3
                        class="text-xl font-bold tracking-tight text-foreground tabular-nums sm:text-2xl"
                    >
                        {{ stat.value }}
                    </h3>
                </div>
            </div>

            <!-- Schemes Table -->
            <!-- Schemes Table Container -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-500 sm:rounded-xl dark:border-white/5"
            >
                <!-- Toolbar -->
                <div
                    class="space-y-4 border-b border-border/50 bg-muted/5 p-4 sm:p-6"
                >
                    <div
                        class="flex flex-col items-center justify-between gap-4 lg:flex-row"
                    >
                        <div class="group relative w-full lg:max-w-md">
                            <Search
                                class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-blue-600"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search schemes..."
                                class="h-11 rounded-xl border-border/60 bg-background pl-10 text-sm shadow-sm transition-all focus:ring-4 focus:ring-blue-600/5"
                            />
                        </div>

                        <div class="flex w-full items-center gap-2 lg:w-auto">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="h-11 flex-1 rounded-xl border-border bg-background px-5 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted/50 lg:flex-none"
                                    >
                                        <ListFilter
                                            class="mr-2 h-4 w-4 opacity-70"
                                        />
                                        Actions
                                        <ChevronDown
                                            class="ml-2 h-4 w-4 opacity-50"
                                        />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-56 rounded-xl border border-border p-2 shadow-lg"
                                >
                                    <DropdownMenuItem
                                        class="rounded-lg px-3 py-2 text-xs font-bold tracking-wider uppercase"
                                        ><FileText
                                            class="mr-3 h-4 w-4 text-blue-500"
                                        />
                                        Export Selected</DropdownMenuItem
                                    >
                                    <DropdownMenuSeparator
                                        class="my-1 bg-border/20"
                                    />
                                    <DropdownMenuItem
                                        class="rounded-lg px-3 py-2 text-xs font-bold tracking-wider text-rose-500 uppercase"
                                        ><Trash2 class="mr-3 h-4 w-4" /> Bulk
                                        Delete</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div
                        v-if="showFilters"
                        class="grid animate-in grid-cols-1 gap-3 pt-2 duration-300 fade-in slide-in-from-top-2 sm:grid-cols-3"
                    >
                        <div class="space-y-1.5">
                            <Label
                                class="ml-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >Subject</Label
                            >
                            <select
                                v-model="selectedSubject"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border/60 bg-background px-4 text-sm font-bold tracking-wider uppercase shadow-sm transition-all outline-none hover:border-blue-500/30 focus:ring-4 focus:ring-blue-600/5"
                            >
                                <option value="all">All Subjects</option>
                                <option
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    :value="subject.id"
                                >
                                    {{ subject.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <Label
                                class="ml-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >Grade</Label
                            >
                            <select
                                v-model="selectedGrade"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border/60 bg-background px-4 text-sm font-bold tracking-wider uppercase shadow-sm transition-all outline-none hover:border-blue-500/30 focus:ring-4 focus:ring-blue-600/5"
                            >
                                <option value="all">All Grades</option>
                                <option
                                    v-for="grade in grades"
                                    :key="grade.id"
                                    :value="grade.id"
                                >
                                    {{ grade.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <Label
                                class="ml-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >Term</Label
                            >
                            <select
                                v-model="selectedTerm"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border/60 bg-background px-4 text-sm font-bold tracking-wider uppercase shadow-sm transition-all outline-none hover:border-blue-500/30 focus:ring-4 focus:ring-blue-600/5"
                            >
                                <option value="all">All Terms</option>
                                <option
                                    v-for="term in terms"
                                    :key="term.id"
                                    :value="term.id"
                                >
                                    {{ term.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="scrollbar-none overflow-x-auto">
                    <table
                        class="w-full min-w-[700px] border-collapse text-left sm:min-w-0"
                    >
                        <thead>
                            <tr
                                class="border-b border-border/40 bg-muted/5 text-muted-foreground"
                            >
                                <th
                                    class="px-4 py-4 text-xs font-medium tracking-tight uppercase sm:px-6"
                                >
                                    Scheme Details
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-medium tracking-tight uppercase sm:px-6"
                                >
                                    Placement
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-medium tracking-tight uppercase sm:px-6"
                                >
                                    Schedule
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-medium tracking-tight uppercase sm:px-6"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-4 text-right text-xs font-medium tracking-tight uppercase sm:px-6"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/20 uppercase">
                            <tr
                                v-for="scheme in filteredSchemes"
                                :key="scheme.id"
                                class="group cursor-pointer transition-all hover:bg-muted/10"
                                @click="
                                    router.visit(
                                        `/curriculum/planner/schemes/${scheme.id}`,
                                    )
                                "
                            >
                                <td class="px-4 py-5 sm:px-6">
                                    <div
                                        class="flex items-center gap-3 sm:gap-4"
                                    >
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 transition-all group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/30 sm:h-11 sm:w-11"
                                        >
                                            <FileText
                                                class="h-5 w-5 sm:h-5.5 sm:w-5.5"
                                            />
                                        </div>
                                        <div
                                            class="flex min-w-0 flex-col gap-0.5"
                                        >
                                            <span
                                                class="truncate text-[13px] leading-none font-bold tracking-tight text-foreground uppercase transition-colors group-hover:text-blue-600 sm:text-sm"
                                                >{{ scheme.title }}</span
                                            >
                                            <div
                                                class="mt-1.5 flex items-center gap-2 overflow-hidden"
                                            >
                                                <Badge
                                                    variant="outline"
                                                    class="shrink-0 rounded-md border-border/30 bg-muted/30 px-2 py-0 text-xs font-bold tracking-tight uppercase"
                                                    >{{
                                                        scheme.subject?.name
                                                    }}</Badge
                                                >
                                                <span
                                                    class="truncate text-xs font-bold text-muted-foreground normal-case opacity-80"
                                                    >By
                                                    {{
                                                        scheme.prepared_by_user
                                                            ?.name ||
                                                        'Academic Dept'
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5 sm:px-6">
                                    <div class="flex flex-col gap-1">
                                        <div
                                            class="flex items-center gap-2 text-sm leading-none font-bold text-foreground sm:text-[12px]"
                                        >
                                            <GraduationCap
                                                class="h-3.5 w-3.5 text-blue-500/70"
                                            />
                                            {{
                                                scheme.grade_level
                                                    ?.short_name ||
                                                scheme.grade_level?.name
                                            }}
                                        </div>
                                        <div
                                            class="mt-1.5 ml-0.5 flex items-center gap-2 text-xs leading-none font-bold tracking-tight text-muted-foreground uppercase opacity-60"
                                        >
                                            <Calendar class="h-3 w-3" />
                                            {{
                                                scheme.academic_term?.name ||
                                                'Yearly'
                                            }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5 text-center sm:px-6">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-xs leading-none font-bold text-foreground tabular-nums sm:text-[13px]"
                                            >{{ scheme.total_weeks }} Wks</span
                                        >
                                        <span
                                            class="mt-1.5 text-xs leading-none font-bold tracking-tight text-muted-foreground uppercase opacity-60"
                                            >{{
                                                scheme.lessons_per_week
                                            }}
                                            L/Wk</span
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-5 sm:px-6">
                                    <div class="flex justify-center">
                                        <Badge
                                            variant="outline"
                                            :class="[
                                                getStatusVariant(scheme.status),
                                                'flex items-center gap-1.5 rounded-lg border px-2 py-1 text-xs font-medium tracking-tight uppercase transition-all group-hover:scale-105 sm:px-3 sm:text-xs',
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getStatusIcon(scheme.status)
                                                "
                                                class="h-3 w-3 sm:h-3.5 sm:w-3.5"
                                            />
                                            <span class="hidden sm:inline">{{
                                                scheme.status
                                            }}</span>
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-5 text-right sm:px-6">
                                    <div
                                        class="flex items-center justify-end gap-1 sm:gap-2"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click.stop="
                                                router.visit(
                                                    `/curriculum/planner/schemes/${scheme.id}`,
                                                )
                                            "
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-blue-50 hover:text-blue-600"
                                        >
                                            <Eye
                                                class="h-4 w-4 sm:h-4.5 sm:w-4.5"
                                            />
                                        </Button>

                                        <DropdownMenu>
                                            <DropdownMenuTrigger
                                                as-child
                                                @click.stop
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl font-bold transition-all hover:bg-muted"
                                                    ><MoreHorizontal
                                                        class="h-4 w-4 sm:h-4.5 sm:w-4.5"
                                                /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border border-border p-2 shadow-lg"
                                            >
                                                <DropdownMenuItem
                                                    @click="
                                                        router.visit(
                                                            `/curriculum/planner/schemes/${scheme.id}`,
                                                        )
                                                    "
                                                    class="group rounded-lg px-3 py-2 text-xs font-bold tracking-wider uppercase"
                                                >
                                                    <FileCheck
                                                        class="mr-3 h-4 w-4 opacity-60 group-hover:text-blue-600"
                                                    />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    @click.stop="
                                                        editScheme(scheme)
                                                    "
                                                    class="group rounded-lg px-3 py-2 text-xs font-bold tracking-wider uppercase"
                                                >
                                                    <Edit
                                                        class="mr-3 h-4 w-4 opacity-60 group-hover:text-amber-600"
                                                    />
                                                    Edit Parameters
                                                </DropdownMenuItem>

                                                <template
                                                    v-if="
                                                        scheme.status ===
                                                        'draft'
                                                    "
                                                >
                                                    <DropdownMenuSeparator
                                                        class="my-1 bg-border/5"
                                                    />
                                                    <DropdownMenuItem
                                                        @click.stop="
                                                            updateStatus(
                                                                scheme.id,
                                                                'submit',
                                                            )
                                                        "
                                                        class="rounded-lg bg-blue-50/50 px-3 py-2 text-xs font-bold tracking-wider text-blue-600 uppercase"
                                                    >
                                                        <Sparkles
                                                            class="mr-3 h-4 w-4"
                                                        />
                                                        Submit for Review
                                                    </DropdownMenuItem>
                                                </template>

                                                <template
                                                    v-if="
                                                        scheme.status ===
                                                        'submitted'
                                                    "
                                                >
                                                    <DropdownMenuSeparator
                                                        class="my-1 bg-border/5"
                                                    />
                                                    <DropdownMenuItem
                                                        @click.stop="
                                                            updateStatus(
                                                                scheme.id,
                                                                'approve',
                                                            )
                                                        "
                                                        class="rounded-lg bg-emerald-50/50 px-3 py-2 text-xs font-bold tracking-wider text-emerald-600 uppercase"
                                                    >
                                                        <CheckCircle2
                                                            class="mr-3 h-4 w-4"
                                                        />
                                                        Approve Scheme
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        @click.stop="
                                                            updateStatus(
                                                                scheme.id,
                                                                'reject',
                                                            )
                                                        "
                                                        class="rounded-lg bg-rose-50/50 px-3 py-2 text-xs font-bold tracking-wider text-rose-600 uppercase"
                                                    >
                                                        <Clock
                                                            class="mr-3 h-4 w-4"
                                                        />
                                                        Return to Draft
                                                    </DropdownMenuItem>
                                                </template>

                                                <DropdownMenuSeparator
                                                    class="my-1 bg-border/5"
                                                />
                                                <DropdownMenuItem
                                                    class="rounded-lg px-3 py-2 text-xs font-bold tracking-wider text-rose-600 uppercase transition-colors hover:bg-rose-50"
                                                    @click.stop="
                                                        deleteScheme(scheme.id)
                                                    "
                                                >
                                                    <Trash2
                                                        class="mr-3 h-4 w-4"
                                                    />
                                                    Delete Scheme
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredSchemes.length === 0">
                                <td
                                    colspan="5"
                                    class="bg-muted/5 px-8 py-32 text-center text-muted-foreground"
                                >
                                    <div
                                        class="mx-auto flex max-w-sm flex-col items-center gap-4"
                                    >
                                        <div
                                            class="flex h-20 w-20 items-center justify-center rounded-3xl border border-border/50 bg-muted/50 text-muted-foreground/30 shadow-inner"
                                        >
                                            <FileText class="h-10 w-10" />
                                        </div>
                                        <div class="space-y-1">
                                            <h3
                                                class="text-lg font-bold text-foreground"
                                            >
                                                No Schemes Found
                                            </h3>
                                            <p class="text-xs font-medium">
                                                We couldn't find any schemes of
                                                work matching your criteria.
                                            </p>
                                        </div>
                                        <Button
                                            @click="isAddingScheme = true"
                                            class="mt-4 h-10 rounded-xl bg-blue-600 px-6 font-bold tracking-tight text-white uppercase shadow-sm transition-all hover:bg-blue-700"
                                        >
                                            <Plus class="mr-2 h-4 w-4" /> Create
                                            Scheme
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- New Scheme Modal -->
        <Dialog v-model:open="isAddingScheme">
            <DialogContent
                class="overflow-hidden rounded-xl border-border bg-card p-0 shadow-lg sm:max-w-[600px]"
            >
                <DialogHeader
                    class="border-b border-border/10 bg-muted/5 p-8 pb-6"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
                        >
                            <Plus class="h-6 w-6" />
                        </div>
                        <div>
                            <DialogTitle
                                class="text-xl font-bold tracking-tight text-foreground"
                                >{{
                                    isEditing
                                        ? 'Edit Scheme of Work'
                                        : 'New Scheme of Work'
                                }}</DialogTitle
                            >
                            <p
                                class="mt-0.5 text-sm font-medium text-muted-foreground"
                            >
                                Initialize a new instructional sequence for your
                                classes.
                            </p>
                        </div>
                    </div>
                </DialogHeader>

                <form @submit.prevent="submitScheme" class="space-y-6 p-8">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Subject</Label
                                >
                                <select
                                    v-model="schemeForm.subject_id"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border/60 bg-muted/20 px-4 text-xs font-bold tracking-wider uppercase transition-all outline-none focus:bg-background focus:ring-4 focus:ring-blue-600/5"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Subject
                                    </option>
                                    <option
                                        v-for="s in subjects"
                                        :key="s.id"
                                        :value="s.id"
                                    >
                                        {{ s.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Grade Level</Label
                                >
                                <select
                                    v-model="schemeForm.grade_level_id"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border/60 bg-muted/20 px-4 text-xs font-bold tracking-wider uppercase transition-all outline-none focus:bg-background focus:ring-4 focus:ring-blue-600/5"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Grade
                                    </option>
                                    <option
                                        v-for="g in grades"
                                        :key="g.id"
                                        :value="g.id"
                                    >
                                        {{ g.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Academic Term</Label
                                >
                                <select
                                    v-model="schemeForm.academic_term_id"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border/60 bg-muted/20 px-4 text-xs font-bold tracking-wider uppercase transition-all outline-none focus:bg-background focus:ring-4 focus:ring-blue-600/5"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Term
                                    </option>
                                    <option
                                        v-for="t in terms"
                                        :key="t.id"
                                        :value="t.id"
                                    >
                                        {{ t.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Scheme Title</Label
                                >
                                <Input
                                    v-model="schemeForm.title"
                                    placeholder="e.g. Mathematics - Term 1"
                                    class="h-11 rounded-xl border-border/60 bg-muted/20 px-4 text-sm font-bold shadow-sm transition-all focus:bg-background"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Total Weeks</Label
                                >
                                <Input
                                    v-model="schemeForm.total_weeks"
                                    type="number"
                                    class="h-11 rounded-xl border-border/60 bg-muted/20 px-4 text-sm font-bold transition-all focus:bg-background"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Lessons per Week</Label
                                >
                                <Input
                                    v-model="schemeForm.lessons_per_week"
                                    type="number"
                                    class="h-11 rounded-xl border-border/60 bg-muted/20 px-4 text-sm font-bold transition-all focus:bg-background"
                                    required
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >Description (Optional)</Label
                            >
                            <Textarea
                                v-model="schemeForm.description"
                                placeholder="Outcome objectives and overview..."
                                class="min-h-[100px] rounded-xl border-border/60 bg-muted/20 p-4 text-sm leading-relaxed font-medium transition-all focus:bg-background"
                            />
                        </div>
                    </div>

                    <DialogFooter
                        class="-m-8 mt-4 flex items-center justify-between border-t border-border/10 bg-muted/5 p-8 pt-6 sm:justify-between"
                    >
                        <Button
                            type="button"
                            variant="ghost"
                            @click="isAddingScheme = false"
                            class="h-11 rounded-xl px-6 text-sm font-bold tracking-tight text-muted-foreground uppercase transition-all hover:text-foreground"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            :disabled="schemeForm.processing"
                            class="flex h-11 items-center gap-2 rounded-xl bg-blue-600 px-8 text-sm font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                        >
                            <Sparkles class="h-3.5 w-3.5" />
                            {{
                                schemeForm.processing
                                    ? isEditing
                                        ? 'Updating...'
                                        : 'Creating...'
                                    : isEditing
                                      ? 'Update Scheme'
                                      : 'Create Scheme'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
