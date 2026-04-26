<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    FileText,
    Plus,
    Search,
    Filter,
    MoreVertical,
    Eye,
    Edit2,
    Trash2,
    Calendar,
    User,
    BookOpen,
    CheckCircle2,
    Clock,
    AlertCircle,
    ChevronRight,
    ListChecks,
    BookCopy,
    Lightbulb,
    Users,
    Wand2,
    ClipboardCheck,
    Sparkles,
    Check,
    Info,
    Trash,
    MapPin,
    MoreHorizontal,
    CheckSquare,
    Square,
    ArrowLeft,
    Download,
    X,
    ArrowUpDown,
    ChevronUp,
    ChevronDown,
    ChevronLeft,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    currentClass: any;
    currentSubject: any;
    plans: any[];
    subjects: any[];
    grades: any[];
    classes: any[];
    terms: any[];
    strands: any[];
    sub_strands: any[];
    assessmentTypes: any[];
    rubrics: any[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
    {
        title: props.currentClass?.grade_level?.name || 'Grade',
        href: `/curriculum/planner/lesson-plans/grade/${props.currentClass?.grade_level_id}`,
    },
    {
        title: props.currentClass?.name || 'Class',
        href: `/curriculum/planner/lesson-plans/class/${props.currentClass?.id}/subjects`,
    },
    { title: props.currentSubject?.name || 'Subject', href: '#' },
]);

// Filtering State
const searchQuery = ref('');
const showFilters = ref(true);
const selectedSubjectId = ref('all');
const selectedStatus = ref('all');
const selectedTermId = ref('all');
const selectedWeek = ref('all');
const sortBy = ref('date_desc');
const showDuplicatesOnly = ref(false);

// Pagination State
const currentPage = ref(1);
const itemsPerPage = ref(10);

watch(
    [
        searchQuery,
        selectedStatus,
        selectedSubjectId,
        selectedTermId,
        selectedWeek,
        showDuplicatesOnly,
    ],
    () => {
        currentPage.value = 1;
    },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedSubjectId.value = 'all';
    selectedTermId.value = 'all';
    selectedWeek.value = 'all';
    sortBy.value = 'date_desc';
    showDuplicatesOnly.value = false;
    currentPage.value = 1;
};

const duplicateGroups = computed(() => {
    const groups: Record<string, any[]> = {};
    props.plans.forEach((plan) => {
        // Group strictly by Title (Case-Insensitive) as requested by user
        const key = plan.title?.toLowerCase().trim();
        if (!key) return;
        if (!groups[key]) groups[key] = [];
        groups[key].push(plan);
    });

    // Filter to only groups with >1 entry
    const duplicates: Record<string, any[]> = {};
    for (const key in groups) {
        if (groups[key].length > 1) {
            duplicates[key] = groups[key];
        }
    }
    return duplicates;
});

const getDuplicateStatus = (plan: any) => {
    const key = plan.title?.toLowerCase().trim();
    const group = duplicateGroups.value[key];
    if (!group) return null;

    const sortedGroup = [...group].sort((a, b) => a.id - b.id);
    return sortedGroup[0].id === plan.id ? 'original' : 'duplicate';
};

const duplicateCount = computed(() => {
    return Object.values(duplicateGroups.value).reduce(
        (acc, group) => acc + (group.length - 1),
        0,
    );
});

const selectRedundancies = () => {
    const idsToSelect: number[] = [];
    Object.values(duplicateGroups.value).forEach((group) => {
        // Sort by ID to keep the oldest (original) record, select the rest
        const sortedGroup = [...group].sort((a, b) => a.id - b.id);
        for (let i = 1; i < sortedGroup.length; i++) {
            idsToSelect.push(sortedGroup[i].id);
        }
    });
    selectedIds.value = [...new Set([...selectedIds.value, ...idsToSelect])];
    showDuplicatesOnly.value = true;
};

const mergeAll = () => {
    const idsToSelect: number[] = [];
    Object.values(duplicateGroups.value).forEach((group) => {
        const sortedGroup = [...group].sort((a, b) => a.id - b.id);
        for (let i = 1; i < sortedGroup.length; i++) {
            idsToSelect.push(sortedGroup[i].id);
        }
    });

    if (idsToSelect.length === 0) return;

    if (
        confirm(
            `Merge Action: This will keep the original (first) record for each lesson plan and permanently delete the remaining ${idsToSelect.length} duplicates. Proceed?`,
        )
    ) {
        router.post(
            `/curriculum/planner/lesson-plans/bulk-delete`,
            {
                ids: idsToSelect,
            },
            {
                onSuccess: () => {
                    showDuplicatesOnly.value = false;
                    selectedIds.value = [];
                },
            },
        );
    }
};

const mergeGroup = (plan: any) => {
    const key = plan.title?.toLowerCase().trim();
    if (!key) return;
    const group = duplicateGroups.value[key];
    if (!group) return;

    const sortedGroup = [...group].sort((a, b) => a.id - b.id);
    const idsToDelete = sortedGroup.slice(1).map((p) => p.id);

    if (idsToDelete.length === 0) return;

    if (
        confirm(
            `Merge Action: This will keep this original record and permanently delete the ${idsToDelete.length} other duplicates of "${plan.title}". Proceed?`,
        )
    ) {
        router.post(`/curriculum/planner/lesson-plans/bulk-delete`, {
            ids: idsToDelete,
        });
    }
};

const toggleSort = (field: string) => {
    if (field === 'title') {
        sortBy.value =
            sortBy.value === 'alphabetical'
                ? 'alphabetical_desc'
                : 'alphabetical';
    } else if (field === 'date') {
        sortBy.value = sortBy.value === 'date_desc' ? 'date_asc' : 'date_desc';
    } else if (field === 'week') {
        sortBy.value = sortBy.value === 'week_asc' ? 'week_desc' : 'week_asc';
    } else if (field === 'status') {
        sortBy.value =
            sortBy.value === 'status_asc' ? 'status_desc' : 'status_asc';
    }
};

const filteredPlans = computed(() => {
    let results = props.plans.filter((plan) => {
        const matchesSearch =
            plan.title
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            plan.subject?.name
                ?.toLowerCase()
                .includes(searchQuery.value.toLowerCase());
        const matchesSubject =
            selectedSubjectId.value === 'all' ||
            plan.subject_id == selectedSubjectId.value;
        const matchesStatus =
            selectedStatus.value === 'all' ||
            plan.status === selectedStatus.value;
        const matchesTerm =
            selectedTermId.value === 'all' ||
            plan.academic_term_id == selectedTermId.value;
        const matchesWeek =
            selectedWeek.value === 'all' ||
            plan.week_number == selectedWeek.value;

        let matchesDuplicates = true;
        if (showDuplicatesOnly.value) {
            const key = plan.title?.toLowerCase().trim();
            matchesDuplicates = key ? !!duplicateGroups.value[key] : false;
        }

        return (
            matchesSearch &&
            matchesSubject &&
            matchesStatus &&
            matchesDuplicates &&
            matchesTerm &&
            matchesWeek
        );
    });

    results.sort((a, b) => {
        if (showDuplicatesOnly.value) {
            return (a.title || '').localeCompare(b.title || '');
        }

        switch (sortBy.value) {
            case 'alphabetical':
                return (a.title || '').localeCompare(b.title || '');
            case 'alphabetical_desc':
                return (b.title || '').localeCompare(a.title || '');
            case 'week_asc':
                return (
                    (parseInt(a.week_number) || 0) -
                    (parseInt(b.week_number) || 0)
                );
            case 'week_desc':
                return (
                    (parseInt(b.week_number) || 0) -
                    (parseInt(a.week_number) || 0)
                );
            case 'status_asc':
                return (a.status || '').localeCompare(b.status || '');
            case 'status_desc':
                return (b.status || '').localeCompare(a.status || '');
            case 'date_desc':
                return (
                    new Date(b.lesson_date).getTime() -
                    new Date(a.lesson_date).getTime()
                );
            case 'date_asc':
                return (
                    new Date(a.lesson_date).getTime() -
                    new Date(b.lesson_date).getTime()
                );
            default:
                return 0;
        }
    });

    return results;
});

const totalPages = computed(() =>
    Math.ceil(filteredPlans.value.length / itemsPerPage.value),
);
const paginatedPlans = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredPlans.value.slice(start, start + itemsPerPage.value);
});

const stats = computed(() => ({
    total: props.plans.length,
    approved: props.plans.filter((p) => p.status === 'approved').length,
    pending: props.plans.filter((p) => p.status === 'submitted').length,
    drafts: props.plans.filter((p) => p.status === 'draft').length,
}));

// Modals State
const showModal = ref(false);
const editingPlan = ref<any>(null);
const currentTab = ref('administrative');
const tabs = ['administrative', 'curriculum', 'delivery', 'reflection'];

const nextTab = () => {
    const currentIndex = tabs.indexOf(currentTab.value);
    if (currentIndex < tabs.length - 1) {
        currentTab.value = tabs[currentIndex + 1];
    }
};

const prevTab = () => {
    const currentIndex = tabs.indexOf(currentTab.value);
    if (currentIndex > 0) {
        currentTab.value = tabs[currentIndex - 1];
    }
};

const form = useForm({
    title: '',
    lesson_date: '',
    subject_id: '',
    class_id: '',
    academic_term_id: '',
    strand_id: '',
    sub_strand_id: '',
    week_number: '',
    period_number: '',
    duration_minutes: 35,
    number_of_learners: 40,
    learning_outcomes: '',
    key_vocabulary: '',
    core_competencies: [] as string[],
    values: [] as string[],
    life_skills: [] as string[],
    pci: [] as string[],
    inquiry_questions: '',
    teaching_aids: '',
    references: '',
    introduction: '',
    learning_activities: [''] as string[], // Multi-activity list
    teacher_activities: '',
    learner_activities: '',
    conclusion: '',
    assessment_methods: '',
    reflection: '',
    homework: '',
    scheme_entry_id: '',
});

// Selection State
const selectedIds = ref<number[]>([]);
const selectAll = computed({
    get: () =>
        filteredPlans.value.length > 0 &&
        selectedIds.value.length === filteredPlans.value.length,
    set: (value) => {
        if (value) {
            selectedIds.value = filteredPlans.value.map((p) => p.id);
        } else {
            selectedIds.value = [];
        }
    },
});

const toggleSelection = (id: number) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter((i) => i !== id);
    } else {
        selectedIds.value.push(id);
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;

    if (
        confirm(
            `Are you sure you want to delete ${selectedIds.value.length} selected lesson plans?`,
        )
    ) {
        router.post(
            `/curriculum/planner/lesson-plans/bulk-delete`,
            {
                ids: selectedIds.value,
            },
            {
                onSuccess: () => {
                    selectedIds.value = [];
                },
            },
        );
    }
};

const filteredStrands = computed(() => {
    if (!form.subject_id) return [];
    return props.strands.filter((s) => s.subject_id === form.subject_id);
});

const filteredSubStrands = computed(() => {
    if (!form.strand_id) return [];
    return props.sub_strands.filter((s) => s.strand_id === form.strand_id);
});

const openModal = (plan: any = null) => {
    editingPlan.value = plan;
    if (plan) {
        form.title = plan.title;
        form.lesson_date = plan.lesson_date
            ? plan.lesson_date.split('T')[0]
            : '';
        form.subject_id = plan.subject_id;
        form.class_id = plan.class_id;
        form.academic_term_id = plan.academic_term_id;
        form.strand_id = plan.strand_id;
        form.sub_strand_id = plan.sub_strand_id;
        form.week_number = plan.week_number;
        form.period_number = plan.period_number;
        form.duration_minutes = plan.duration_minutes;
        form.number_of_learners = plan.number_of_learners;
        form.learning_outcomes = plan.learning_outcomes;
        form.key_vocabulary = plan.key_vocabulary;
        form.core_competencies = plan.core_competencies || [];
        form.values = plan.values || [];
        form.life_skills = plan.life_skills || [];
        form.pci = plan.pci || [];
        form.inquiry_questions = plan.inquiry_questions;
        form.teaching_aids = plan.teaching_aids;
        form.references = plan.references;
        form.introduction = plan.introduction;
        form.learning_activities =
            plan.learning_activities && plan.learning_activities.length
                ? plan.learning_activities
                : [''];
        form.teacher_activities = plan.teacher_activities;
        form.learner_activities = plan.learner_activities;
        form.conclusion = plan.conclusion;
        form.assessment_methods = plan.assessment_methods;
        form.reflection = plan.reflection;
        form.homework = plan.homework;
        form.scheme_entry_id = plan.scheme_entry_id;
    } else {
        form.reset();
        form.class_id = props.currentClass?.id;
        form.subject_id = props.currentSubject?.id;
        if (props.terms.length > 0) form.academic_term_id = props.terms[0].id;
    }
    showModal.value = true;
};

const isCreatingAssessment = ref(false);
const currentAssessmentStep = ref(1);
const assessmentForm = useForm<{
    title: string;
    description: string;
    class_id: string | number;
    subject_id: string | number;
    rubric_id: string | number;
    assessment_type_id: string | number;
    assessment_date: string;
    total_marks: number;
    passing_marks: number;
    weight: number;
    status: string;
    lesson_plan_id: string | number;
    sub_strand_id: string | number;
    indicators: string[];
    competencies: string[];
}>({
    title: '',
    description: '',
    class_id: '',
    subject_id: '',
    rubric_id: '',
    assessment_type_id: '',
    assessment_date: '',
    total_marks: 10,
    passing_marks: 5,
    weight: 10,
    status: 'draft',
    lesson_plan_id: '',
    sub_strand_id: '',
    indicators: [],
    competencies: [],
});

const openAssessmentWizard = (plan: any) => {
    assessmentForm.reset();
    assessmentForm.title = `Assessment: ${plan.title}`;
    assessmentForm.class_id = plan.class_id;
    assessmentForm.subject_id = plan.subject_id;
    assessmentForm.lesson_plan_id = plan.id;
    assessmentForm.sub_strand_id = plan.sub_strand_id;
    assessmentForm.assessment_date = new Date().toISOString().split('T')[0];

    // Auto-extract indicators from outcomes (simple split by newline or bullet points)
    const outcomes = (plan.learning_outcomes as string) || '';
    assessmentForm.indicators = outcomes
        .split('\n')
        .map((l: string) => l.trim())
        .filter((l: string) => l.length > 0)
        .map((l: string) => l.replace(/^[-*•]\s+/, ''));

    // Mapping competencies from lesson
    assessmentForm.competencies = plan.core_competencies || [];

    assessmentForm.description = `Assessment based on lesson: ${plan.title}`;

    currentAssessmentStep.value = 1;
    isCreatingAssessment.value = true;
};

const nextAssessmentStep = () => {
    if (currentAssessmentStep.value < 4) currentAssessmentStep.value++;
};

const prevAssessmentStep = () => {
    if (currentAssessmentStep.value > 1) currentAssessmentStep.value--;
};

const removeIndicator = (index: number) => {
    assessmentForm.indicators.splice(index, 1);
};

const submitAssessment = () => {
    assessmentForm.post(route('assessments.store'), {
        onSuccess: () => {
            isCreatingAssessment.value = false;
            alert('Assessment derived and created successfully!');
        },
    });
};

const filteredRubrics = computed(() => {
    if (!assessmentForm.subject_id) return props.rubrics;
    return props.rubrics.filter(
        (r) => r.subject_id === assessmentForm.subject_id || !r.subject_id,
    );
});

const submit = () => {
    if (editingPlan.value) {
        form.put(
            route(
                'curriculum.planner.lesson-plans.update',
                editingPlan.value.id,
            ),
            {
                onSuccess: () => {
                    showModal.value = false;
                    editingPlan.value = null;
                },
            },
        );
    } else {
        form.post(route('curriculum.planner.lesson-plans.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const submitForReview = (plan: any) => {
    if (confirm('Send this plan for approval?')) {
        router.post(route('curriculum.planner.lesson-plans.submit', plan.id));
    }
};

const approvePlan = (plan: any) => {
    if (confirm('Approve this plan?')) {
        router.post(route('curriculum.planner.lesson-plans.approve', plan.id));
    }
};

const rejectPlan = (plan: any) => {
    const feedback = prompt('Provide feedback for revision (optional):');
    if (feedback !== null) {
        router.post(route('curriculum.planner.lesson-plans.reject', plan.id), {
            feedback,
        });
    }
};

const deletePlan = (plan: any) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        router.delete(
            route('curriculum.planner.lesson-plans.destroy', plan.id),
        );
    }
};

const downloadPdf = (plan: any) => {
    window.location.href = `/curriculum/planner/lesson-plans/${plan.id}/download`;
};

const downloadAllPdf = () => {
    window.location.href = `/curriculum/planner/lesson-plans/class/${props.currentClass.id}/subject/${props.currentSubject.id}/download-all`;
};

const showBulkModal = ref(false);
const bulkForm = useForm({
    file: null as File | null,
    class_id: props.currentClass?.id,
    subject_id: props.currentSubject?.id,
});

const handleBulkUpload = () => {
    bulkForm.post('/curriculum/planner/lesson-plans/bulk-upload', {
        onSuccess: () => {
            showBulkModal.value = false;
            bulkForm.reset();
        },
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'draft':
            return 'bg-slate-50 text-slate-600 border-slate-100';
        default:
            return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

const viewPlan = (plan: any) => {
    router.visit(`/curriculum/planner/lesson-plans/${plan.id}`);
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const entryId = urlParams.get('scheme_entry_id');

    if (id) {
        const plan = props.plans.find((p) => p.id == id);
        if (plan) openModal(plan);
    } else if (entryId) {
        // Find scheme entry in props if possible, or just pre-fill if we have basic info
        // Since we don't have all entries in props, we might need more data.
        // But for now, we'll try to find if any existing plan has this entry_id
        const existingPlan = props.plans.find(
            (p) => p.scheme_entry_id == entryId,
        );
        if (existingPlan) {
            openModal(existingPlan);
        } else {
            // Find the scheme entry from the schemes if we had them,
            // but here we just have partial data.
            // Let's at least set the scheme_entry_id in the form
            form.reset();
            form.scheme_entry_id = entryId;

            // Try to find context from the entry if it's passed (maybe we should pass it via state/props)
            // For now, just open the modal with the ID linked.
            showModal.value = true;
        }
    }
});
</script>

<template>
    <Head :title="`${currentClass?.name} - Lesson Plans`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full w-full max-w-[1600px] flex-1 animate-in flex-col gap-6 p-6 duration-500 fade-in md:p-8"
        >
            <!-- Page Header (Standard Academic Arrangement) -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-primary/5 bg-primary/10 shadow-sm"
                    >
                        <BookOpen class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ currentSubject?.name }} &nbsp;Plans
                        </h1>
                        <p class="text-sm font-medium text-muted-foreground">
                            {{ currentClass?.name }} •
                            {{ currentClass?.grade_level?.name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 rounded-xl px-4 font-semibold"
                    >
                        <Link
                            :href="`/curriculum/planner/lesson-plans/class/${currentClass?.id}/subjects`"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Back to Subjects
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        @click="downloadAllPdf"
                        class="h-10 rounded-xl px-4 font-semibold"
                    >
                        <Download class="mr-2 h-4 w-4" /> Download PDF
                    </Button>
                    <Button
                        variant="outline"
                        @click="showBulkModal = true"
                        class="h-10 rounded-xl px-4 font-semibold"
                    >
                        <BookCopy class="mr-2 h-4 w-4" /> Bulk Upload
                    </Button>
                    <Button
                        @click="openModal()"
                        class="h-10 rounded-xl bg-primary px-6 font-bold shadow-lg shadow-primary/20 hover:opacity-90"
                    >
                        <Plus class="mr-2 h-4 w-4" /> New Plan
                    </Button>
                </div>
            </div>

            <!-- Stats Grid (Aligned with Students Page) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                    >
                        Total Lessons
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ stats.total }}
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                        >
                            <FileText class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                    >
                        Awaiting Review
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ stats.pending }}
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                        >
                            <Clock class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                    >
                        Operational Plans
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ stats.approved }}
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                    >
                        Draft Records
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ stats.drafts }}
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400"
                        >
                            <Edit2 class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duplication Alert (Standard Blue/Slate Palette) -->
            <div
                v-if="duplicateCount > 0"
                class="mb-2 flex animate-in flex-col items-start justify-between gap-4 rounded-3xl border border-primary/10 bg-primary/[0.03] p-5 duration-500 zoom-in-95 fade-in md:flex-row md:items-center"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-inner"
                    >
                        <Sparkles class="h-6 w-6" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-wider text-slate-900 uppercase"
                        >
                            Duplication Management
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-semibold text-muted-foreground"
                        >
                            Identified {{ duplicateCount }} redundant titles.
                            Use individual or bulk merge to consolidate.
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showDuplicatesOnly = !showDuplicatesOnly"
                        class="h-10 rounded-2xl border-slate-200 px-5 text-xs font-bold tracking-tight text-slate-700 uppercase shadow-sm hover:bg-muted"
                    >
                        {{
                            showDuplicatesOnly
                                ? 'View All Matrix'
                                : 'Filter Duplicates'
                        }}
                    </Button>
                    <Button
                        @click="mergeAll"
                        class="h-10 rounded-2xl bg-primary px-5 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:opacity-90"
                    >
                        Execute Bulk Merge
                    </Button>
                </div>
            </div>

            <!-- Toolbar (Aligned with Students Page) -->
            <div
                class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="group relative flex-1 md:max-w-md">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search lesson titles..."
                        class="h-11 rounded-xl border-slate-200 bg-slate-50/30 pl-10 shadow-none transition-all focus:bg-white"
                    />
                </div>
                <div
                    class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showFilters = !showFilters"
                        class="h-10 gap-2 rounded-xl text-xs font-bold tracking-tight uppercase"
                    >
                        <Filter class="h-3.5 w-3.5" />
                        {{ showFilters ? 'Hide Matrix' : 'Advanced Filters' }}
                    </Button>

                    <Button
                        variant="ghost"
                        size="sm"
                        @click="clearFilters"
                        class="h-10 rounded-xl text-xs font-semibold text-muted-foreground transition-colors hover:text-primary"
                    >
                        Reset
                    </Button>

                    <div
                        v-if="selectedIds.length > 0"
                        class="mx-2 hidden h-8 w-px bg-border md:block"
                    ></div>

                    <Button
                        v-if="selectedIds.length > 0"
                        variant="destructive"
                        size="sm"
                        @click="bulkDelete"
                        class="h-10 gap-2 rounded-xl text-xs font-bold tracking-tight uppercase shadow-lg shadow-destructive/20"
                    >
                        <Trash2 class="h-3.5 w-3.5" /> Delete ({{
                            selectedIds.length
                        }})
                    </Button>
                </div>
            </div>

            <!-- Extended Filter Engine -->
            <div
                v-if="showFilters"
                class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-4"
            >
                <div class="space-y-2">
                    <label class="text-sm font-medium">Order By</label>
                    <select
                        v-model="sortBy"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                    >
                        <option value="date_desc">Newest First</option>
                        <option value="date_asc">Oldest First</option>
                        <option value="alphabetical">Title (A-Z)</option>
                        <option value="week_asc">
                            Institutional Focus (Week)
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Plan Status</label>
                    <select
                        v-model="selectedStatus"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                    >
                        <option value="all">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Awaiting Review</option>
                        <option value="approved">Operational</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Academic Context</label>
                    <select
                        v-model="selectedTermId"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                    >
                        <option value="all">All Academic Terms</option>
                        <option
                            v-for="term in terms"
                            :key="term.id"
                            :value="String(term.id)"
                        >
                            {{ term.name }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Time Context</label>
                    <select
                        v-model="selectedWeek"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                    >
                        <option value="all">All Focus Weeks</option>
                        <option v-for="w in 15" :key="w" :value="String(w)">
                            Week {{ w }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Main Table (Standard Aesthetic) -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left">
                                <input
                                    type="checkbox"
                                    v-model="selectAll"
                                    class="h-4 w-4 rounded border-border"
                                />
                            </th>
                            <th
                                class="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                                @click="toggleSort('title')"
                            >
                                Lesson Title
                                <ArrowUpDown
                                    class="ml-2 inline h-3 w-3 opacity-50"
                                />
                            </th>
                            <th
                                class="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                                @click="toggleSort('date')"
                            >
                                Execution Date
                                <ArrowUpDown
                                    class="ml-2 inline h-3 w-3 opacity-50"
                                />
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                            >
                                Instructional Context
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="plan in paginatedPlans"
                            :key="plan.id"
                            class="group border-b transition-colors hover:bg-muted/50"
                            :class="{
                                'bg-primary/5': selectedIds.includes(plan.id),
                            }"
                        >
                            <td class="px-4 py-3" @click.stop>
                                <input
                                    type="checkbox"
                                    :value="plan.id"
                                    v-model="selectedIds"
                                    class="h-4 w-4 rounded border-border"
                                />
                            </td>
                            <td class="px-4 py-3" @click="viewPlan(plan)">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-medium text-slate-900"
                                            >{{ plan.title }}</span
                                        >
                                        <div
                                            v-if="getDuplicateStatus(plan)"
                                            class="flex items-center gap-1"
                                        >
                                            <template
                                                v-if="
                                                    getDuplicateStatus(plan) ===
                                                    'original'
                                                "
                                            >
                                                <Badge
                                                    variant="default"
                                                    class="h-4 bg-green-600 text-xs"
                                                    >Original</Badge
                                                >
                                                <Button
                                                    v-if="showDuplicatesOnly"
                                                    variant="ghost"
                                                    size="sm"
                                                    @click.stop="
                                                        mergeGroup(plan)
                                                    "
                                                    class="h-4 border border-amber-100 bg-amber-50 px-1.5 text-xs font-bold text-amber-600 uppercase hover:bg-amber-600 hover:text-white"
                                                >
                                                    Merge
                                                </Button>
                                            </template>
                                            <Badge
                                                v-else
                                                variant="secondary"
                                                class="h-4 text-xs"
                                                >Duplicate</Badge
                                            >
                                        </div>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >{{ plan.subject?.name }}</span
                                        >
                                        <span
                                            class="text-xs text-muted-foreground/60"
                                            >• Week {{ plan.week_number }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ plan.lesson_date }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col space-y-0.5 text-xs">
                                    <span class="font-medium text-slate-700">{{
                                        plan.strand?.name || '---'
                                    }}</span>
                                    <span
                                        class="max-w-[150px] truncate text-muted-foreground"
                                        >{{
                                            plan.sub_strand?.name || '---'
                                        }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        plan.status === 'approved'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    >{{ plan.status }}</Badge
                                >
                            </td>
                            <td class="px-4 py-3 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-56"
                                    >
                                        <DropdownMenuItem
                                            @click="viewPlan(plan)"
                                        >
                                            <Eye class="mr-2 h-4 w-4" /> View
                                            Details
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="openModal(plan)"
                                        >
                                            <Edit2 class="mr-2 h-4 w-4" /> Edit
                                            Lesson
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="downloadPdf(plan)"
                                        >
                                            <Download class="mr-2 h-4 w-4" />
                                            Download PDF
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="openAssessmentWizard(plan)"
                                        >
                                            <Wand2 class="mr-2 h-4 w-4" />
                                            Create Assessment
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <template
                                            v-if="plan.status !== 'approved'"
                                        >
                                            <DropdownMenuItem
                                                @click="approvePlan(plan)"
                                                class="text-green-600"
                                            >
                                                <CheckCircle2
                                                    class="mr-2 h-4 w-4"
                                                />
                                                Approve Plan
                                            </DropdownMenuItem>
                                        </template>
                                        <DropdownMenuItem
                                            class="text-destructive"
                                            @click="deletePlan(plan)"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredPlans.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-24 text-center font-medium text-muted-foreground"
                            >
                                No lesson plans found matching your criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                class="flex flex-col items-center justify-between gap-6 border-t border-border/40 bg-muted/5 p-6 md:flex-row"
            >
                <div
                    class="rounded-xl border border-border/40 bg-white/50 px-4 py-2 text-sm font-bold tracking-tight text-muted-foreground uppercase shadow-sm"
                >
                    Showing
                    <span class="text-foreground">{{
                        (currentPage - 1) * itemsPerPage + 1
                    }}</span>
                    to
                    <span class="text-foreground">{{
                        Math.min(
                            currentPage * itemsPerPage,
                            filteredPlans.length,
                        )
                    }}</span>
                    of
                    <span class="text-foreground">{{
                        filteredPlans.length
                    }}</span>
                    Lessons
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-10 w-10 rounded-xl border-border/60 shadow-sm transition-all hover:bg-white disabled:opacity-30"
                        :disabled="currentPage === 1"
                        @click="currentPage = 1"
                    >
                        <ChevronsLeft class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-10 w-10 rounded-xl border-border/60 shadow-sm transition-all hover:bg-white disabled:opacity-30"
                        :disabled="currentPage === 1"
                        @click="currentPage--"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>

                    <div class="flex items-center gap-1.5 px-3">
                        <template v-for="page in totalPages" :key="page">
                            <Button
                                v-if="
                                    Math.abs(page - currentPage) < 3 ||
                                    page === 1 ||
                                    page === totalPages
                                "
                                variant="outline"
                                class="h-10 min-w-[40px] rounded-xl border-border/60 text-xs font-bold shadow-sm transition-all"
                                :class="
                                    currentPage === page
                                        ? 'border-blue-600 bg-blue-600 text-white shadow-blue-500/20'
                                        : 'text-foreground hover:bg-white'
                                "
                                @click="currentPage = page"
                            >
                                {{ page }}
                            </Button>
                            <span
                                v-else-if="Math.abs(page - currentPage) === 3"
                                class="px-1 font-bold text-muted-foreground opacity-40"
                                >...</span
                            >
                        </template>
                    </div>

                    <Button
                        variant="outline"
                        size="icon"
                        class="h-10 w-10 rounded-xl border-border/60 shadow-sm transition-all hover:bg-white disabled:opacity-30"
                        :disabled="currentPage === totalPages"
                        @click="currentPage++"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-10 w-10 rounded-xl border-border/60 shadow-sm transition-all hover:bg-white disabled:opacity-30"
                        :disabled="currentPage === totalPages"
                        @click="currentPage = totalPages"
                    >
                        <ChevronsRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="showModal">
            <DialogContent
                class="flex max-h-[92vh] flex-col overflow-hidden rounded-2xl border-border bg-card p-0 shadow-lg sm:max-w-[900px]"
            >
                <DialogHeader class="p-10 pb-0">
                    <div class="flex items-center justify-between pr-8">
                        <div>
                            <DialogTitle
                                class="text-3xl font-bold tracking-tight text-foreground"
                                >{{
                                    editingPlan
                                        ? 'Refine Lesson Execution'
                                        : 'Initialize Lesson Plan'
                                }}</DialogTitle
                            >
                            <DialogDescription
                                class="mt-1 text-xs font-medium tracking-tight text-muted-foreground uppercase opacity-70"
                            >
                                {{
                                    editingPlan
                                        ? 'Strategic Adjustment of Operational Delivery'
                                        : 'Curriculum-Aligned Instructional Provisioning'
                                }}
                            </DialogDescription>
                        </div>
                        <Badge
                            v-if="editingPlan"
                            variant="outline"
                            :class="[
                                'rounded-full px-4 py-1 text-xs font-bold tracking-tight uppercase',
                                getStatusColor(editingPlan.status),
                            ]"
                        >
                            {{ editingPlan.status }}
                        </Badge>
                    </div>

                    <div class="mt-8">
                        <Tabs v-model="currentTab" class="w-full">
                            <TabsList
                                class="no-scrollbar w-full justify-start overflow-x-auto rounded-2xl border border-border/40 bg-muted/20 p-1.5 backdrop-blur-sm"
                            >
                                <TabsTrigger
                                    value="administrative"
                                    class="rounded-xl px-6 py-2.5 text-xs font-medium tracking-tight whitespace-nowrap uppercase transition-all data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10"
                                    >1. Administration</TabsTrigger
                                >
                                <TabsTrigger
                                    value="curriculum"
                                    class="rounded-xl px-6 py-2.5 text-xs font-medium tracking-tight whitespace-nowrap uppercase transition-all data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10"
                                    >2. Curriculum Focus</TabsTrigger
                                >
                                <TabsTrigger
                                    value="delivery"
                                    class="rounded-xl px-6 py-2.5 text-xs font-medium tracking-tight whitespace-nowrap uppercase transition-all data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10"
                                    >3. Instructional Steps</TabsTrigger
                                >
                                <TabsTrigger
                                    value="reflection"
                                    class="rounded-xl px-6 py-2.5 text-xs font-medium tracking-tight whitespace-nowrap uppercase transition-all data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10"
                                    >4. Analysis & Tools</TabsTrigger
                                >
                            </TabsList>
                        </Tabs>
                    </div>
                </DialogHeader>

                <div class="no-scrollbar flex-1 overflow-y-auto p-8 pt-6">
                    <form @submit.prevent="submit" id="lessonPlanForm">
                        <Tabs v-model="currentTab" class="w-full">
                            <TabsContent
                                value="administrative"
                                class="mt-0 animate-in space-y-8 duration-500 fade-in slide-in-from-right-4"
                            >
                                <div class="grid gap-3">
                                    <Label
                                        class="ml-3 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                        >Operational Title</Label
                                    >
                                    <Input
                                        v-model="form.title"
                                        placeholder="e.g. Introduction to Fractions using local materials"
                                        class="h-14 rounded-2xl border-border/60 bg-muted/20 px-6 text-lg font-bold shadow-inner transition-all focus:bg-background focus:ring-4 focus:ring-blue-600/5"
                                        required
                                    />
                                </div>

                                <div class="grid gap-8 md:grid-cols-2">
                                    <div class="grid gap-3">
                                        <Label
                                            class="ml-3 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                            >Subject Discipline</Label
                                        >
                                        <Input
                                            :value="currentSubject?.name"
                                            disabled
                                            class="h-12 w-full rounded-2xl border-border/60 bg-muted/50 px-5 text-xs font-bold tracking-wider text-muted-foreground uppercase shadow-sm"
                                        />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="ml-3 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                            >Class Context</Label
                                        >
                                        <Input
                                            :value="currentClass?.name"
                                            disabled
                                            class="h-12 w-full rounded-2xl border-border/60 bg-muted/50 px-5 text-xs font-bold tracking-wider text-muted-foreground uppercase shadow-sm"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-8 md:grid-cols-4">
                                    <div class="grid gap-3 md:col-span-2">
                                        <Label
                                            class="ml-3 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                            >Execution Date</Label
                                        >
                                        <Input
                                            type="date"
                                            v-model="form.lesson_date"
                                            class="h-12 rounded-2xl border-border/60 bg-muted/20 px-5 text-xs font-bold"
                                            required
                                        />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="ml-3 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                            >Sequence Period</Label
                                        >
                                        <Input
                                            type="number"
                                            v-model="form.period_number"
                                            class="h-12 rounded-2xl border-border/60 bg-muted/20 text-center text-xs font-bold"
                                        />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="ml-3 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                            >Total Learners</Label
                                        >
                                        <Input
                                            type="number"
                                            v-model="form.number_of_learners"
                                            class="h-12 rounded-2xl border-border/60 bg-muted/20 text-center text-xs font-bold"
                                        />
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent
                                value="curriculum"
                                class="mt-0 space-y-6"
                            >
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Strand</Label
                                        >
                                        <select
                                            v-model="form.strand_id"
                                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/30 px-4 text-xs font-bold tracking-wider uppercase transition-all outline-none hover:bg-slate-50 focus:ring-4 focus:ring-blue-500/10"
                                            required
                                        >
                                            <option value="" disabled>
                                                Select Strand
                                            </option>
                                            <option
                                                v-for="s in filteredStrands"
                                                :key="s.id"
                                                :value="s.id"
                                            >
                                                {{ s.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Sub-Strand</Label
                                        >
                                        <select
                                            v-model="form.sub_strand_id"
                                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/30 px-4 text-xs font-bold tracking-wider uppercase transition-all outline-none hover:bg-slate-50 focus:ring-4 focus:ring-blue-500/10"
                                            required
                                        >
                                            <option value="" disabled>
                                                Select Sub-Strand
                                            </option>
                                            <option
                                                v-for="s in filteredSubStrands"
                                                :key="s.id"
                                                :value="s.id"
                                            >
                                                {{ s.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-3">
                                    <Label
                                        class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                        >Learning Outcomes</Label
                                    >
                                    <Textarea
                                        v-model="form.learning_outcomes"
                                        placeholder="By the end of the lesson, the learner should be able to identify parts of a plant..."
                                        class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50/30 py-4 text-sm"
                                        required
                                    />
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Core Competencies</Label
                                        >
                                        <div
                                            class="flex flex-wrap gap-2 rounded-2xl border border-slate-100 bg-slate-50/30 p-3"
                                        >
                                            <Badge
                                                v-for="comp in [
                                                    'Critical Thinking',
                                                    'Collaboration',
                                                    'Digital Literacy',
                                                    'Communication',
                                                    'Creativity',
                                                ]"
                                                :key="comp"
                                                @click="
                                                    form.core_competencies.includes(
                                                        comp,
                                                    )
                                                        ? (form.core_competencies =
                                                              form.core_competencies.filter(
                                                                  (c) =>
                                                                      c !==
                                                                      comp,
                                                              ))
                                                        : form.core_competencies.push(
                                                              comp,
                                                          )
                                                "
                                                :variant="
                                                    form.core_competencies.includes(
                                                        comp,
                                                    )
                                                        ? 'default'
                                                        : 'outline'
                                                "
                                                class="cursor-pointer py-1 text-xs tracking-tight uppercase"
                                            >
                                                {{ comp }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Values</Label
                                        >
                                        <div
                                            class="flex flex-wrap gap-2 rounded-2xl border border-slate-100 bg-slate-50/30 p-3"
                                        >
                                            <Badge
                                                v-for="val in [
                                                    'Respect',
                                                    'Integrity',
                                                    'Responsibility',
                                                    'Teamwork',
                                                    'Love',
                                                ]"
                                                :key="val"
                                                @click="
                                                    form.values.includes(val)
                                                        ? (form.values =
                                                              form.values.filter(
                                                                  (v) =>
                                                                      v !== val,
                                                              ))
                                                        : form.values.push(val)
                                                "
                                                :variant="
                                                    form.values.includes(val)
                                                        ? 'default'
                                                        : 'outline'
                                                "
                                                class="cursor-pointer border-emerald-200 bg-emerald-50 py-1 text-xs tracking-tight text-emerald-700 uppercase hover:bg-emerald-100"
                                            >
                                                {{ val }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Life Skills</Label
                                        >
                                        <div
                                            class="flex flex-wrap gap-2 rounded-2xl border border-slate-100 bg-slate-50/30 p-3"
                                        >
                                            <Badge
                                                v-for="skill in [
                                                    'Self-awareness',
                                                    'Empathy',
                                                    'Decision Making',
                                                    'Effective Communication',
                                                ]"
                                                :key="skill"
                                                @click="
                                                    form.life_skills.includes(
                                                        skill,
                                                    )
                                                        ? (form.life_skills =
                                                              form.life_skills.filter(
                                                                  (s) =>
                                                                      s !==
                                                                      skill,
                                                              ))
                                                        : form.life_skills.push(
                                                              skill,
                                                          )
                                                "
                                                :variant="
                                                    form.life_skills.includes(
                                                        skill,
                                                    )
                                                        ? 'default'
                                                        : 'outline'
                                                "
                                                class="cursor-pointer border-amber-200 bg-amber-50 py-1 text-xs tracking-tight text-amber-700 uppercase hover:bg-amber-100"
                                            >
                                                {{ skill }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-3">
                                    <Label
                                        class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                        >Key Vocabulary</Label
                                    >
                                    <Input
                                        v-model="form.key_vocabulary"
                                        placeholder="e.g. Numerator, Denominator, Vinculum"
                                        class="h-12 rounded-2xl border-slate-100 bg-slate-50/30 text-xs font-bold"
                                    />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >PCI (Pertinent Issues)</Label
                                        >
                                        <div
                                            class="flex flex-wrap gap-2 rounded-2xl border border-slate-100 bg-slate-50/30 p-3"
                                        >
                                            <Badge
                                                v-for="issue in [
                                                    'Environmental awareness',
                                                    'Citizenship',
                                                    'Health education',
                                                    'Life skills',
                                                    'Financial literacy',
                                                ]"
                                                :key="issue"
                                                @click="
                                                    form.pci.includes(issue)
                                                        ? (form.pci =
                                                              form.pci.filter(
                                                                  (i) =>
                                                                      i !==
                                                                      issue,
                                                              ))
                                                        : form.pci.push(issue)
                                                "
                                                :variant="
                                                    form.pci.includes(issue)
                                                        ? 'default'
                                                        : 'outline'
                                                "
                                                class="cursor-pointer border-blue-200 bg-blue-50 py-1 text-xs tracking-tight text-blue-700 uppercase hover:bg-blue-100"
                                            >
                                                {{ issue }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Inquiry Questions</Label
                                        >
                                        <Textarea
                                            v-model="form.inquiry_questions"
                                            placeholder="What strategies can you use to win an argument in a debate?"
                                            class="min-h-[80px] rounded-2xl border-slate-100 bg-slate-50/30 text-sm"
                                        />
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent
                                value="delivery"
                                class="mt-0 space-y-6"
                            >
                                <div class="grid gap-4">
                                    <div
                                        class="rounded-3xl border border-blue-100/50 bg-blue-50/50 p-6"
                                    >
                                        <h4
                                            class="mb-4 flex items-center gap-2 text-xs font-medium tracking-tight text-blue-600 text-muted-foreground uppercase"
                                        >
                                            <Lightbulb class="h-4 w-4" /> Lesson
                                            Introduction
                                        </h4>
                                        <Textarea
                                            v-model="form.introduction"
                                            placeholder="Hook the learners. Revise previous knowledge..."
                                            class="min-h-[80px] rounded-2xl border-0 bg-white/80 text-sm shadow-sm"
                                        />
                                    </div>

                                    <div
                                        class="rounded-3xl border border-emerald-100/50 bg-emerald-50/50 p-6"
                                    >
                                        <div
                                            class="mb-4 flex items-center justify-between"
                                        >
                                            <h4
                                                class="flex items-center gap-2 text-xs font-medium tracking-tight text-emerald-600 text-muted-foreground uppercase"
                                            >
                                                <Users class="h-4 w-4" /> Lesson
                                                Development Activities
                                            </h4>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="
                                                    form.learning_activities.push(
                                                        '',
                                                    )
                                                "
                                                class="h-8 rounded-full bg-emerald-100 px-4 text-xs font-bold text-emerald-700 uppercase"
                                                ><Plus class="mr-1 h-3 w-3" />
                                                Add Activity</Button
                                            >
                                        </div>
                                        <div class="space-y-4">
                                            <div
                                                v-for="(
                                                    activity, idx
                                                ) in form.learning_activities"
                                                :key="idx"
                                                class="group relative flex gap-3"
                                            >
                                                <div
                                                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-emerald-200/50 bg-emerald-100/50 text-xs font-bold text-emerald-600"
                                                >
                                                    {{ idx + 1 }}
                                                </div>
                                                <div class="grid flex-1 gap-2">
                                                    <Textarea
                                                        v-model="
                                                            form
                                                                .learning_activities[
                                                                idx
                                                            ]
                                                        "
                                                        :placeholder="`Shared Activity ${idx + 1}: Learners in groups observe real plant...`"
                                                        class="min-h-[80px] rounded-2xl border-0 bg-white/80 text-sm shadow-sm"
                                                    />
                                                </div>
                                                <Button
                                                    v-if="
                                                        form.learning_activities
                                                            .length > 1
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="
                                                        form.learning_activities.splice(
                                                            idx,
                                                            1,
                                                        )
                                                    "
                                                    class="absolute -top-2 -right-2 h-8 w-8 text-rose-300 opacity-0 transition-all group-hover:opacity-100 hover:text-rose-600"
                                                    ><Trash class="h-3.5 w-3.5"
                                                /></Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div
                                            class="rounded-3xl border border-indigo-100/50 bg-indigo-50/50 p-6"
                                        >
                                            <h4
                                                class="mb-4 flex items-center gap-2 text-xs font-medium tracking-tight text-indigo-600 text-muted-foreground uppercase"
                                            >
                                                <User class="h-4 w-4" /> Teacher
                                                Activities
                                            </h4>
                                            <Textarea
                                                v-model="
                                                    form.teacher_activities
                                                "
                                                placeholder="What the teacher will be doing..."
                                                class="min-h-[80px] rounded-2xl border-0 bg-white/80 text-sm shadow-sm"
                                            />
                                        </div>
                                        <div
                                            class="rounded-3xl border border-amber-100/50 bg-amber-50/50 p-6"
                                        >
                                            <h4
                                                class="mb-4 flex items-center gap-2 text-xs font-medium tracking-tight text-amber-600 text-muted-foreground uppercase"
                                            >
                                                <Users class="h-4 w-4" />
                                                Learner Activities
                                            </h4>
                                            <Textarea
                                                v-model="
                                                    form.learner_activities
                                                "
                                                placeholder="What the learners will be doing..."
                                                class="min-h-[80px] rounded-2xl border-0 bg-white/80 text-sm shadow-sm"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-3xl border border-slate-100/50 bg-slate-50/50 p-6"
                                    >
                                        <h4
                                            class="mb-4 flex items-center gap-2 text-xs font-medium tracking-tight text-muted-foreground text-slate-600 uppercase"
                                        >
                                            <CheckCircle2 class="h-4 w-4" />
                                            Conclusion & Wrap-up
                                        </h4>
                                        <Textarea
                                            v-model="form.conclusion"
                                            placeholder="Summary, clean up, exit ticket..."
                                            class="min-h-[80px] rounded-2xl border-0 bg-white/80 text-sm shadow-sm"
                                        />
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent
                                value="reflection"
                                class="mt-0 space-y-6"
                            >
                                <div class="grid gap-6 md:grid-cols-3">
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Teaching Aids</Label
                                        >
                                        <Textarea
                                            v-model="form.teaching_aids"
                                            placeholder="Charts, digital content, realia..."
                                            class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50/30 text-sm"
                                        />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >Assessment Methods</Label
                                        >
                                        <Textarea
                                            v-model="form.assessment_methods"
                                            placeholder="Observation, oral testing, written work..."
                                            class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50/30 text-sm"
                                        />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                            >References</Label
                                        >
                                        <Textarea
                                            v-model="form.references"
                                            placeholder="Textbooks, page numbers, digital URLs..."
                                            class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50/30 text-sm"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div
                                        class="rounded-3xl border border-rose-100/30 bg-rose-50/30 p-6"
                                    >
                                        <h4
                                            class="mb-4 flex items-center gap-2 text-xs font-medium tracking-tight text-muted-foreground text-rose-600 uppercase"
                                        >
                                            <Sparkles class="h-4 w-4" />
                                            Professional Reflection
                                        </h4>
                                        <Textarea
                                            v-model="form.reflection"
                                            placeholder="What went well? What would you change next time?"
                                            class="min-h-[100px] rounded-2xl border-slate-100 bg-white text-sm"
                                        />
                                    </div>
                                    <div
                                        class="rounded-3xl border border-indigo-100/30 bg-indigo-50/30 p-6"
                                    >
                                        <h4
                                            class="mb-4 flex items-center gap-2 text-xs font-medium tracking-tight text-indigo-600 text-muted-foreground uppercase"
                                        >
                                            <Home class="h-4 w-4" /> Homework /
                                            Extended Learning
                                        </h4>
                                        <Textarea
                                            v-model="form.homework"
                                            placeholder="Tasks for students to complete at home..."
                                            class="min-h-[100px] rounded-2xl border-slate-100 bg-white text-sm"
                                        />
                                    </div>
                                </div>
                            </TabsContent>
                        </Tabs>
                    </form>
                </div>

                <DialogFooter
                    class="flex items-center justify-between border-t border-border/30 bg-muted/5 p-10"
                >
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="currentTab !== 'administrative'"
                            variant="ghost"
                            @click="prevTab"
                            class="h-12 rounded-xl px-8 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase transition-all hover:text-foreground"
                            >Back</Button
                        >
                    </div>
                    <div class="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            @click="showModal = false"
                            class="h-12 rounded-xl px-8 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase transition-all hover:text-foreground"
                            >Cancel Request</Button
                        >

                        <Button
                            v-if="currentTab !== 'reflection'"
                            @click="nextTab"
                            class="h-12 rounded-xl border border-slate-800 bg-slate-900 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-xl transition-all hover:bg-black active:scale-95"
                            >Proceed Next</Button
                        >

                        <Button
                            v-else
                            form="lessonPlanForm"
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-12 items-center gap-2 rounded-xl bg-blue-600 px-10 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                        >
                            <Sparkles class="h-3.5 w-3.5" />
                            {{
                                form.processing
                                    ? 'ENGINEERING...'
                                    : editingPlan
                                      ? 'Commit Update'
                                      : 'Initialize Plan'
                            }}
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showBulkModal">
            <DialogContent
                class="overflow-hidden rounded-3xl border-border p-0 shadow-lg sm:max-w-[500px]"
            >
                <div class="space-y-8 bg-card p-10">
                    <div
                        class="flex flex-col items-center space-y-4 text-center"
                    >
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-3xl bg-primary/10 text-primary"
                        >
                            <BookCopy class="h-8 w-8" />
                        </div>
                        <div class="space-y-1">
                            <h2
                                class="text-2xl font-bold tracking-tight text-foreground uppercase"
                            >
                                Bulk Import Lessons
                            </h2>
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Upload a CSV file to initialize multiple lesson
                                protocols.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="space-y-4 rounded-2xl border border-border bg-muted/5 p-6"
                        >
                            <div class="flex items-center justify-between">
                                <Label
                                    class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >Master Template</Label
                                >
                                <a
                                    href="/curriculum/planner/lesson-plans/template"
                                    class="flex items-center gap-1.5 text-xs font-medium tracking-tight text-muted-foreground text-primary uppercase underline-offset-4 hover:underline"
                                >
                                    <ListChecks class="h-3 w-3" /> Download
                                    Sample
                                </a>
                            </div>
                            <p
                                class="text-sm leading-relaxed text-muted-foreground"
                            >
                                Ensure your CSV follows the official matrix
                                structure for perfect synchronization.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <Label
                                class="ml-3 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Select CSV Data File</Label
                            >
                            <Input
                                type="file"
                                @change="bulkForm.file = $event.target.files[0]"
                                accept=".csv"
                                class="h-14 rounded-2xl border-2 border-dashed border-border/60 bg-muted/20 px-6 py-3.5 text-xs font-bold transition-all hover:bg-muted/30"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <Button
                            @click="handleBulkUpload"
                            :disabled="bulkForm.processing || !bulkForm.file"
                            class="h-12 w-full rounded-2xl bg-primary text-xs font-bold tracking-tight text-primary-foreground uppercase shadow-xl shadow-primary/20 transition-all hover:opacity-90 disabled:opacity-50"
                        >
                            {{
                                bulkForm.processing
                                    ? 'SYNCHRONIZING...'
                                    : 'INITIALIZE DATA UPLOAD'
                            }}
                        </Button>
                        <Button
                            variant="ghost"
                            @click="showBulkModal = false"
                            class="h-12 w-full rounded-2xl text-xs font-bold tracking-tight text-muted-foreground/60 uppercase hover:text-foreground"
                            >Abort Protocol</Button
                        >
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isCreatingAssessment">
            <DialogContent
                class="flex max-h-[92vh] flex-col overflow-hidden rounded-2xl border-border p-0 shadow-lg sm:max-w-[700px]"
            >
                <div
                    class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-700 p-10 text-white"
                >
                    <div
                        class="pointer-events-none absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"
                    ></div>
                    <div class="relative z-10">
                        <div class="mb-6 flex items-center justify-between">
                            <Badge
                                class="rounded-lg border-none bg-white/20 px-3 py-1 text-xs font-medium tracking-tight text-muted-foreground text-white uppercase"
                                >Matrix Step {{ currentAssessmentStep }} /
                                4</Badge
                            >
                            <Wand2
                                class="h-8 w-8 animate-pulse text-white/50"
                            />
                        </div>
                        <h2
                            class="text-3xl leading-tight font-bold tracking-tight uppercase"
                        >
                            Derive Assessment
                        </h2>
                        <p
                            class="mt-3 max-w-[500px] text-sm leading-relaxed font-semibold text-blue-100 text-muted-foreground"
                        >
                            Strategic auto-extraction of competencies and
                            indicators from operational lesson protocols.
                        </p>
                    </div>
                </div>

                <div class="no-scrollbar flex-1 overflow-y-auto p-10">
                    <div
                        v-if="currentAssessmentStep === 1"
                        class="animate-in space-y-8 duration-500 fade-in slide-in-from-right-4"
                    >
                        <div class="space-y-3">
                            <Label
                                class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Assessment Title</Label
                            >
                            <Input
                                v-model="assessmentForm.title"
                                class="h-14 rounded-2xl border-slate-100 text-lg font-bold tracking-tight focus:ring-4 focus:ring-blue-500/10"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="grid gap-3">
                                <Label
                                    class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Assessment Type</Label
                                >
                                <select
                                    v-model="assessmentForm.assessment_type_id"
                                    class="h-12 cursor-pointer appearance-none rounded-xl border border-slate-100 bg-slate-50/50 px-4 text-xs font-bold tracking-wider uppercase outline-none"
                                >
                                    <option
                                        v-for="type in assessmentTypes"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="grid gap-3">
                                <Label
                                    class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Scheduled Date</Label
                                >
                                <Input
                                    type="date"
                                    v-model="assessmentForm.assessment_date"
                                    class="h-12 rounded-xl border-slate-100 text-xs font-bold"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="currentAssessmentStep === 2"
                        class="animate-in space-y-8 duration-500 fade-in slide-in-from-right-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <h3
                                    class="text-xl font-bold tracking-tight text-slate-900"
                                >
                                    Extracted Indicators
                                </h3>
                                <p class="text-xs font-medium text-slate-400">
                                    Learner outcomes mapped to assessment goals.
                                </p>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="assessmentForm.indicators.push('')"
                                class="h-8 rounded-full bg-blue-50 px-4 text-xs font-bold text-blue-600 uppercase"
                                ><Plus class="mr-1 h-3 w-3" /> Add</Button
                            >
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(
                                    indicator, idx
                                ) in assessmentForm.indicators"
                                :key="idx"
                                class="group flex items-center gap-3"
                            >
                                <span
                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-400"
                                    >{{ idx + 1 }}</span
                                >
                                <Input
                                    v-model="assessmentForm.indicators[idx]"
                                    class="h-12 rounded-xl border-slate-100 text-xs font-medium shadow-none transition-all group-hover:border-blue-200"
                                />
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="removeIndicator(idx)"
                                    class="h-10 w-10 text-slate-300 opacity-0 transition-opacity group-hover:opacity-100 hover:text-red-500"
                                    ><Trash2 class="h-4 w-4"
                                /></Button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="currentAssessmentStep === 3"
                        class="animate-in space-y-8 duration-500 fade-in slide-in-from-right-4"
                    >
                        <div class="mb-6 space-y-1">
                            <h3
                                class="text-xl font-bold tracking-tight text-slate-900"
                            >
                                Core Competencies
                            </h3>
                            <p class="text-xs font-medium text-slate-400">
                                Strategic CBC areas addressed in this lesson.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                v-for="comp in [
                                    'Communication & Collaboration',
                                    'Self-efficacy',
                                    'Critical Thinking',
                                    'Creativity',
                                    'Digital Literacy',
                                    'Learning to Learn',
                                    'Citizenship',
                                ]"
                                :key="comp"
                                @click="
                                    assessmentForm.competencies.includes(comp)
                                        ? (assessmentForm.competencies =
                                              assessmentForm.competencies.filter(
                                                  (c) => c !== comp,
                                              ))
                                        : assessmentForm.competencies.push(comp)
                                "
                                :class="[
                                    'group flex cursor-pointer items-center gap-3 rounded-2xl border-2 p-4 transition-all',
                                    assessmentForm.competencies.includes(comp)
                                        ? 'border-emerald-500 bg-emerald-50/50'
                                        : 'border-slate-50 bg-slate-50/30 hover:border-slate-200',
                                ]"
                            >
                                <div
                                    :class="[
                                        'flex h-5 w-5 items-center justify-center rounded-md transition-all',
                                        assessmentForm.competencies.includes(
                                            comp,
                                        )
                                            ? 'bg-emerald-600 text-white'
                                            : 'border border-slate-200 bg-white group-hover:border-slate-400',
                                    ]"
                                >
                                    <Check
                                        v-if="
                                            assessmentForm.competencies.includes(
                                                comp,
                                            )
                                        "
                                        class="h-3 w-3"
                                    />
                                </div>
                                <span
                                    :class="[
                                        'text-sm font-bold tracking-tight uppercase',
                                        assessmentForm.competencies.includes(
                                            comp,
                                        )
                                            ? 'text-emerald-700'
                                            : 'text-slate-500',
                                    ]"
                                    >{{ comp }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="currentAssessmentStep === 4"
                        class="animate-in space-y-8 duration-500 fade-in slide-in-from-right-4"
                    >
                        <div
                            class="flex flex-col items-center justify-center space-y-4 rounded-xl border border-blue-100 bg-blue-50/50 p-8 text-center"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-600/10"
                            >
                                <ClipboardCheck class="h-8 w-8 text-blue-600" />
                            </div>
                            <div>
                                <h4
                                    class="text-xl font-bold tracking-tight text-slate-900"
                                >
                                    Grading Configuration
                                </h4>
                                <p class="mt-1 px-4 text-sm text-slate-500">
                                    Success! Your assessment is configured.
                                    Select a rubric to finalize.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <Label
                                class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Standardized Rubric</Label
                            >
                            <select
                                v-model="assessmentForm.rubric_id"
                                class="h-12 w-full rounded-xl border border-slate-100 bg-slate-50/50 px-4 text-xs font-bold tracking-wider uppercase outline-none focus:ring-4 focus:ring-blue-500/10"
                            >
                                <option value="" disabled>Select Rubric</option>
                                <option
                                    v-for="rubric in rubrics"
                                    :key="rubric.id"
                                    :value="rubric.id"
                                >
                                    {{ rubric.title }}
                                </option>
                            </select>
                            <p
                                class="flex items-center gap-1 px-1 text-xs font-medium text-slate-400"
                            >
                                <Info class="h-3 w-3" /> This rubric will set
                                the standard achievement levels for your
                                learners.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between border-t border-border/30 bg-muted/20 p-10"
                >
                    <Button
                        v-if="currentAssessmentStep > 1"
                        @click="prevAssessmentStep"
                        variant="ghost"
                        class="h-12 rounded-xl px-8 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase hover:text-foreground"
                        >Backtrack</Button
                    >
                    <div v-else></div>

                    <div class="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            @click="isCreatingAssessment = false"
                            class="h-12 rounded-xl px-8 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase hover:text-foreground"
                            >Discard Wizard</Button
                        >

                        <Button
                            v-if="currentAssessmentStep < 4"
                            @click="nextAssessmentStep"
                            class="h-12 rounded-xl border border-slate-800 bg-slate-900 px-10 text-xs font-bold tracking-tight text-white uppercase shadow-xl transition-all hover:bg-black active:scale-95"
                            >Advance Matrix</Button
                        >

                        <Button
                            v-else
                            @click="submitAssessment"
                            :disabled="assessmentForm.processing"
                            class="flex h-12 items-center gap-2 rounded-xl bg-blue-600 px-12 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                        >
                            <Sparkles class="h-4 w-4" />
                            {{
                                assessmentForm.processing
                                    ? 'GENESYS SYNCING...'
                                    : 'INITIALIZE ASSESSMENT'
                            }}
                        </Button>
                    </div>
                </div>
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

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
