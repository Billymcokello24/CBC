<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    GraduationCap,
    ChevronRight,
    Plus,
    Info,
    CheckCircle2,
    MoreVertical,
    ArrowLeft,
    Eye,
    Edit2,
    Trash2,
    X,
    LayoutGrid,
    List as ListIcon,
    Download,
    MoreHorizontal,
    Users,
    AlertTriangle,
    TrendingUp,
    BookOpen,
    Search,
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
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Switch } from '@/components/ui/switch';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subject: any;
    grade: any;
    strands: any[];
    teachers: any[];
    classes: any[];
    allocations: any[];
    academicYear: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/syllabus' },
    { title: props.subject.name, href: '#' },
];

// Modals State
const showStrandModal = ref(false);
const showSubStrandModal = ref(false);
const showOutcomeModal = ref(false); // No longer needed here, but keeping for reference if needed
const viewMode = ref<'grid' | 'list'>('list');
const editingStrand = ref<any>(null);
const editingSubStrand = ref<any>(null);
const editingOutcome = ref<any>(null);

// Topic (Strand) Form
const strandForm = useForm({
    name: '',
    code: '',
    display_order: 1,
    subject_id: props.subject.id,
    grade_level_id: props.grade.id,
});

const openStrandModal = (strand: any = null) => {
    editingStrand.value = strand;
    if (strand) {
        strandForm.name = strand.name;
        strandForm.code = strand.code;
        strandForm.display_order = strand.display_order;
    } else {
        strandForm.reset();
        strandForm.display_order = props.strands.length + 1;
    }
    showStrandModal.value = true;
};

const submitStrand = () => {
    if (editingStrand.value) {
        strandForm.put(
            route('curriculum.syllabus.topics.update', editingStrand.value.id),
            {
                onSuccess: () => {
                    showStrandModal.value = false;
                    editingStrand.value = null;
                },
            },
        );
    } else {
        strandForm.post(route('curriculum.syllabus.topics.store'), {
            onSuccess: () => {
                showStrandModal.value = false;
            },
        });
    }
};

const deleteStrand = (id: number) => {
    if (
        confirm(
            'Are you sure you want to remove this strand and all its sub-strands?',
        )
    ) {
        useForm({}).delete(route('curriculum.syllabus.topics.destroy', id));
    }
};

// Sub-strand (SubStrand) Form
const subStrandForm = useForm({
    name: '',
    code: '',
    display_order: 1,
    strand_id: null as number | null,
});

const openSubStrandModal = (strandId: number, subStrand: any = null) => {
    editingSubStrand.value = subStrand;
    subStrandForm.strand_id = strandId;
    if (subStrand) {
        subStrandForm.name = subStrand.name;
        subStrandForm.code = subStrand.code;
        subStrandForm.display_order = subStrand.display_order;
    } else {
        subStrandForm.name = '';
        subStrandForm.code = '';
        const parentStrand = props.strands.find((s) => s.id === strandId);
        subStrandForm.display_order =
            (parentStrand?.sub_strands?.length || 0) + 1;
    }
    showSubStrandModal.value = true;
};

const submitSubStrand = () => {
    if (editingSubStrand.value) {
        subStrandForm.put(
            route(
                'curriculum.syllabus.sub-topics.update',
                editingSubStrand.value.id,
            ),
            {
                onSuccess: () => {
                    showSubStrandModal.value = false;
                    editingSubStrand.value = null;
                },
            },
        );
    } else {
        subStrandForm.post(route('curriculum.syllabus.sub-topics.store'), {
            onSuccess: () => {
                showSubStrandModal.value = false;
            },
        });
    }
};

const deleteSubStrand = (id: number) => {
    if (confirm('Are you sure you want to remove this sub-strand?')) {
        useForm({}).delete(route('curriculum.syllabus.sub-topics.destroy', id));
    }
};

// Learning Outcome Form
const outcomeForm = useForm({
    outcome: '',
    sub_strand_id: null as number | null,
    outcome_type: 'Standard',
    display_order: 1,
});

const openOutcomeModal = (subStrandId: number, outcome: any = null) => {
    editingOutcome.value = outcome;
    outcomeForm.sub_strand_id = subStrandId;
    if (outcome) {
        outcomeForm.outcome = outcome.outcome;
        outcomeForm.outcome_type = outcome.outcome_type || 'Standard';
        outcomeForm.display_order = outcome.display_order || 1;
    } else {
        outcomeForm.outcome = '';
        outcomeForm.outcome_type = 'Standard';
        const subStrand = props.strands
            .flatMap((s) => s.sub_strands)
            .find((ss) => ss.id === subStrandId);
        outcomeForm.display_order =
            (subStrand?.learning_outcomes?.length || 0) + 1;
    }
    showOutcomeModal.value = true;
};

const submitOutcome = () => {
    if (editingOutcome.value) {
        outcomeForm.put(
            route(
                'curriculum.syllabus.outcomes.update',
                editingOutcome.value.id,
            ),
            {
                onSuccess: () => {
                    showOutcomeModal.value = false;
                    editingOutcome.value = null;
                },
            },
        );
    } else {
        outcomeForm.post(route('curriculum.syllabus.outcomes.store'), {
            onSuccess: () => {
                showOutcomeModal.value = false;
            },
        });
    }
};

const deleteOutcome = (id: number) => {
    if (confirm('Are you sure you want to remove this learning goal?')) {
        useForm({}).delete(route('curriculum.syllabus.outcomes.destroy', id));
    }
};

const printSyllabus = () => {
    window.print();
};

// Teacher Assignment Logic
const showAssignmentModal = ref(false);
const teacherSearch = ref('');
const assignmentForm = useForm({
    teacher_id: '',
    subject_id: props.subject.id,
    class_id: '',
    academic_year_id: props.academicYear?.id || '',
    is_primary_teacher: true,
    is_active: true,
});

const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    const search = teacherSearch.value.toLowerCase();
    return props.teachers.filter(
        (t) =>
            t.name.toLowerCase().includes(search) ||
            t.staff_number.toLowerCase().includes(search),
    );
});

const openAssignmentModal = (classId: string = '') => {
    assignmentForm.teacher_id = '';
    assignmentForm.class_id = classId;
    assignmentForm.is_primary_teacher = true;
    teacherSearch.value = '';
    assignmentForm.clearErrors();
    showAssignmentModal.value = true;
};

const submitAssignment = () => {
    assignmentForm.post(route('classes.allocations.store'), {
        onSuccess: () => {
            showAssignmentModal.value = false;
        },
        onError: (errors) => {
            console.error('Assignment failed:', errors);
        },
    });
};

const removeAssignment = (id: number) => {
    if (confirm('Are you sure you want to remove this teacher assignment?')) {
        useForm({}).delete(route('classes.allocations.destroy', id));
    }
};

const getTeacherForClass = (classId: number) => {
    return props.allocations.filter((a) => a.class_id === classId);
};
</script>

<template>
    <Head :title="`${subject.name} - ${grade.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 flex-col gap-6 bg-[#f9fafb]/30 p-6 font-sans"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <Link
                        href="/curriculum/syllabus"
                        class="group mb-1 inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase transition-all hover:text-blue-600"
                    >
                        <ArrowLeft
                            class="h-3 w-3 transition-transform group-hover:-translate-x-1"
                        />
                        Subjects
                    </Link>
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900"
                    >
                        {{ subject.name }} Topics
                    </h1>
                    <p class="text-sm text-slate-500">
                        Main topics for {{ grade.name }}.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        @click="printSyllabus"
                        variant="outline"
                        class="h-10 rounded-xl border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition-all hover:bg-slate-50"
                    >
                        <Download class="mr-2 h-3.5 w-3.5" /> Export
                    </Button>
                    <Button
                        @click="openStrandModal()"
                        class="h-10 rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" /> Add Topic
                    </Button>
                </div>
            </div>

            <!-- Stats Cards Section -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Strands -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Topics
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ strands.length }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <BookOpen class="h-5 w-5" />
                    </div>
                </div>

                <!-- Sub-strands -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Sub-topics
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{
                                strands.reduce(
                                    (acc: number, s: any) =>
                                        acc + (s.sub_strands?.length || 0),
                                    0,
                                )
                            }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>

                <!-- Learning Outcomes -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Goals
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{
                                strands.reduce(
                                    (acc: number, s: any) =>
                                        acc +
                                        (s.sub_strands?.reduce(
                                            (acc2: number, ss: any) =>
                                                acc2 +
                                                (ss.learning_outcomes?.length ||
                                                    0),
                                            0,
                                        ) || 0),
                                    0,
                                )
                            }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500"
                    >
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                </div>

                <!-- Status -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Status
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ strands.length > 0 ? 'LIVE' : 'EMPTY' }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-500"
                    >
                        <TrendingUp class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Tabs -->
            <Tabs defaultValue="curriculum" class="w-full">
                <TabsList
                    class="mx-auto mb-6 grid h-12 w-full max-w-md grid-cols-2 rounded-2xl bg-slate-100/50 p-1"
                >
                    <TabsTrigger
                        value="curriculum"
                        class="rounded-xl text-xs font-bold tracking-wider uppercase data-[state=active]:bg-white data-[state=active]:shadow-sm"
                    >
                        <BookOpenCheck class="mr-2 h-4 w-4" /> Curriculum
                        Strands
                    </TabsTrigger>
                    <TabsTrigger
                        value="teachers"
                        class="rounded-xl text-xs font-bold tracking-wider uppercase data-[state=active]:bg-white data-[state=active]:shadow-sm"
                    >
                        <Users class="mr-2 h-4 w-4" /> Teacher Assignments
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="curriculum">
                    <!-- Main Content Card (Original Curriculum Content) -->
                    <div
                        class="min-h-[500px] overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white shadow-sm"
                    >
                        <!-- Toolbar -->
                        <div
                            class="flex flex-col justify-between gap-4 border-b border-slate-50 p-6 md:flex-row md:items-center"
                        >
                            <div class="relative w-full md:max-w-md">
                                <Search
                                    class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-300"
                                />
                                <Input
                                    placeholder="Search strands..."
                                    class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 pl-10 text-sm font-medium shadow-none transition-all focus:bg-white"
                                />
                            </div>

                            <div class="flex items-center gap-3">
                                <Button
                                    variant="outline"
                                    class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 px-4 text-xs font-bold text-slate-500 shadow-none transition-all hover:bg-slate-50"
                                >
                                    <Filter class="mr-2 h-4 w-4" /> Filters
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-11 w-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 p-0 text-slate-400 shadow-none transition-all hover:bg-slate-50"
                                >
                                    <MoreHorizontal class="h-5 w-5" />
                                </Button>
                            </div>
                        </div>

                        <!-- Table Section -->
                        <div class="overflow-x-auto" v-if="strands.length">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr
                                        class="border-b border-slate-50 bg-white"
                                    >
                                        <th class="w-10 py-4 pl-6">
                                            <div
                                                class="h-4 w-4 rounded border border-slate-200"
                                            ></div>
                                        </th>
                                        <th
                                            class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Strand (Topic)
                                        </th>
                                        <th
                                            class="px-4 py-4 text-center text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Sub-topics
                                        </th>
                                        <th
                                            class="px-4 py-4 text-center text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Goals
                                        </th>
                                        <th
                                            class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-4 text-right text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr
                                        v-for="strand in strands"
                                        :key="strand.id"
                                        class="group transition-all hover:bg-[#f9fafb]/50"
                                    >
                                        <td class="py-4 pl-6">
                                            <div
                                                class="h-4 w-4 rounded border border-slate-200 transition-colors group-hover:border-blue-400"
                                            ></div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-xs font-bold text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white"
                                                >
                                                    {{ strand.display_order }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-sm font-bold text-slate-800"
                                                        >{{ strand.name }}</span
                                                    >
                                                    <span
                                                        class="text-xs font-medium text-slate-400 uppercase"
                                                        >{{ strand.code }}</span
                                                    >
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center text-xs font-bold text-slate-600"
                                        >
                                            {{
                                                strand.sub_strands?.length || 0
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center text-xs font-bold text-slate-600"
                                        >
                                            {{
                                                strand.sub_strands?.reduce(
                                                    (acc: number, ss: any) =>
                                                        acc +
                                                        (ss.learning_outcomes
                                                            ?.length || 0),
                                                    0,
                                                ) || 0
                                            }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <Badge
                                                class="rounded border-0 bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-600"
                                            >
                                                ACTIVE
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div
                                                class="flex items-center justify-end gap-2"
                                            >
                                                <Link
                                                    :href="`/curriculum/syllabus/topics/${strand.id}`"
                                                    class="inline-flex h-9 items-center rounded-xl bg-blue-50 px-4 text-xs font-bold text-blue-600 uppercase transition-all hover:bg-blue-600 hover:text-white"
                                                >
                                                    Manage
                                                    <ChevronRight
                                                        class="ml-1 h-3.5 w-3.5"
                                                    />
                                                </Link>
                                                <Button
                                                    @click="
                                                        openStrandModal(strand)
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-slate-400 hover:bg-slate-100"
                                                >
                                                    <Edit2
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                                <Button
                                                    @click="
                                                        deleteStrand(strand.id)
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-slate-300 hover:bg-red-50 hover:text-red-600"
                                                >
                                                    <Trash2
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center justify-center gap-4 bg-white py-20 text-center"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-200"
                            >
                                <GraduationCap class="h-8 w-8" />
                            </div>
                            <div class="space-y-1">
                                <h3
                                    class="text-xl font-bold tracking-tight text-slate-900"
                                >
                                    Add Strands
                                </h3>
                                <p
                                    class="mx-auto max-w-xs text-xs font-medium text-slate-400"
                                >
                                    No strands have been defined for
                                    {{ subject.name }} yet.
                                </p>
                            </div>
                            <Button
                                @click="openStrandModal()"
                                class="h-11 rounded-xl bg-blue-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-sm transition-all hover:bg-blue-700"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Add First Strand
                            </Button>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex items-center justify-between border-t border-slate-50 bg-white p-6"
                            v-if="strands.length"
                        >
                            <p class="text-xs font-bold text-slate-400">
                                Found {{ strands.length }} strands
                            </p>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="teachers">
                    <div
                        class="min-h-[500px] overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-50 bg-slate-50/30 p-6"
                        >
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">
                                    Teacher Assignments
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Assign teachers for
                                    {{ subject.name }} across
                                    {{ grade.name }} classes.
                                </p>
                            </div>
                            <Button
                                @click="openAssignmentModal()"
                                class="h-10 rounded-xl bg-blue-600 px-4 text-xs font-bold tracking-tight text-white uppercase shadow-sm transition-all hover:bg-blue-700"
                            >
                                <Plus class="mr-2 h-4 w-4" /> New Assignment
                            </Button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr
                                        class="border-b border-slate-50 bg-white"
                                    >
                                        <th
                                            class="py-4 pl-6 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Class
                                        </th>
                                        <th
                                            class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Assigned Teacher(s)
                                        </th>
                                        <th
                                            class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Role
                                        </th>
                                        <th
                                            class="px-6 py-4 text-right text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr
                                        v-for="cls in classes"
                                        :key="cls.id"
                                        class="group transition-all hover:bg-[#f9fafb]/50"
                                    >
                                        <td class="py-4 pl-6">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-sm font-bold text-slate-800"
                                                    >{{ cls.name }}</span
                                                >
                                                <span
                                                    class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                                    >{{
                                                        cls.stream ||
                                                        'No Stream'
                                                    }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                v-if="
                                                    getTeacherForClass(cls.id)
                                                        .length
                                                "
                                                class="space-y-2"
                                            >
                                                <div
                                                    v-for="alloc in getTeacherForClass(
                                                        cls.id,
                                                    )"
                                                    :key="alloc.id"
                                                    class="flex items-center gap-2"
                                                >
                                                    <div
                                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold text-blue-600"
                                                    >
                                                        {{
                                                            alloc.teacher_name
                                                                .substring(0, 2)
                                                                .toUpperCase()
                                                        }}
                                                    </div>
                                                    <span
                                                        class="text-xs font-semibold text-slate-700"
                                                        >{{
                                                            alloc.teacher_name
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                            <span
                                                v-else
                                                class="text-xs font-bold text-orange-400"
                                                >Not Assigned</span
                                            >
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                v-if="
                                                    getTeacherForClass(cls.id)
                                                        .length
                                                "
                                                class="flex items-center gap-2"
                                            >
                                                <Badge
                                                    v-for="alloc in getTeacherForClass(
                                                        cls.id,
                                                    )"
                                                    :key="alloc.id"
                                                    :variant="
                                                        alloc.is_primary
                                                            ? 'default'
                                                            : 'secondary'
                                                    "
                                                    class="rounded-md px-1.5 py-0.5 text-xs font-bold tracking-tighter uppercase"
                                                >
                                                    {{
                                                        alloc.is_primary
                                                            ? 'Primary'
                                                            : 'Assistant'
                                                    }}
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div
                                                class="flex items-center justify-end gap-2"
                                            >
                                                <Button
                                                    v-if="
                                                        getTeacherForClass(
                                                            cls.id,
                                                        ).length === 0
                                                    "
                                                    @click="
                                                        openAssignmentModal(
                                                            cls.id.toString(),
                                                        )
                                                    "
                                                    variant="ghost"
                                                    class="h-8 rounded-lg bg-blue-50 px-3 text-xs font-bold text-blue-600 uppercase transition-all hover:bg-blue-600 hover:text-white"
                                                >
                                                    Assign
                                                </Button>
                                                <DropdownMenu v-else>
                                                    <DropdownMenuTrigger
                                                        as-child
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            class="h-8 w-8 rounded-lg p-0"
                                                        >
                                                            <MoreVertical
                                                                class="h-4 w-4"
                                                            />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        class="rounded-xl border-slate-100 bg-white p-2 shadow-xl"
                                                    >
                                                        <DropdownMenuItem
                                                            @click="
                                                                openAssignmentModal(
                                                                    cls.id.toString(),
                                                                )
                                                            "
                                                            class="cursor-pointer rounded-lg px-3 py-2 text-xs font-bold uppercase hover:bg-slate-50"
                                                        >
                                                            Add Another Teacher
                                                        </DropdownMenuItem>
                                                        <div
                                                            class="my-1 border-t border-slate-50"
                                                        ></div>
                                                        <DropdownMenuItem
                                                            v-for="alloc in getTeacherForClass(
                                                                cls.id,
                                                            )"
                                                            :key="alloc.id"
                                                            @click="
                                                                removeAssignment(
                                                                    alloc.id,
                                                                )
                                                            "
                                                            class="cursor-pointer rounded-lg px-3 py-2 text-xs font-bold text-red-500 uppercase hover:bg-red-50"
                                                        >
                                                            Remove
                                                            {{
                                                                alloc.teacher_name
                                                            }}
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
                </TabsContent>
            </Tabs>

            <!-- MODALS -->

            <!-- Strand Modal (Original) -->
            <Dialog v-model:open="showStrandModal">
                <DialogContent
                    class="overflow-hidden rounded-2xl border-slate-100 p-0 shadow-xl sm:max-w-[425px]"
                >
                    <DialogHeader class="bg-slate-900 p-6 text-white">
                        <DialogTitle class="text-xl font-bold tracking-tight">{{
                            editingStrand ? 'Edit Strand' : 'Add Strand'
                        }}</DialogTitle>
                        <DialogDescription class="text-xs text-slate-400">
                            Define a main strand for the curriculum.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        @submit.prevent="submitStrand"
                        class="grid gap-4 bg-white px-6 py-6"
                    >
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label
                                    class="px-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Order</Label
                                >
                                <Input
                                    type="number"
                                    v-model="strandForm.display_order"
                                    class="h-10 rounded-xl border-slate-100 text-sm font-semibold"
                                    required
                                />
                            </div>
                            <div class="grid gap-1.5">
                                <Label
                                    class="px-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Code</Label
                                >
                                <Input
                                    v-model="strandForm.code"
                                    placeholder="e.g. MAT.T1"
                                    class="h-10 rounded-xl border-slate-100 text-sm font-semibold uppercase"
                                />
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label
                                class="px-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Strand Name</Label
                            >
                            <Input
                                v-model="strandForm.name"
                                placeholder="e.g. Numbers"
                                class="h-10 rounded-xl border-slate-100 text-sm font-semibold"
                                required
                            />
                        </div>
                        <DialogFooter class="px-0 pt-2">
                            <Button
                                type="submit"
                                :disabled="strandForm.processing"
                                class="h-11 w-full rounded-xl bg-blue-600 text-xs font-bold tracking-tight text-white uppercase shadow-sm transition-all hover:bg-blue-700"
                            >
                                {{
                                    strandForm.processing
                                        ? 'Saving...'
                                        : editingStrand
                                          ? 'Update'
                                          : 'Save'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Teacher Assignment Modal -->
            <Dialog v-model:open="showAssignmentModal">
                <DialogContent
                    class="overflow-hidden rounded-xl border-0 bg-white/95 p-0 shadow-lg backdrop-blur-xl sm:max-w-[450px]"
                >
                    <DialogHeader
                        class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white"
                    >
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <Users class="h-24 w-24 rotate-12" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle
                                class="mb-1 text-2xl font-bold tracking-tight"
                                >Assign Teacher</DialogTitle
                            >
                            <DialogDescription
                                class="text-xs font-medium text-blue-100 opacity-90"
                            >
                                Connect a specialized teacher to a specific
                                class for
                                <span class="font-bold text-white">{{
                                    subject.name
                                }}</span
                                >.
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    <form
                        @submit.prevent="submitAssignment"
                        class="space-y-6 p-8"
                    >
                        <!-- Class Selection -->
                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-bold tracking-[0.1em] text-slate-400 uppercase"
                                >Target Class</Label
                            >
                            <Select v-model="assignmentForm.class_id">
                                <SelectTrigger
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold shadow-none transition-all hover:bg-slate-50"
                                    :class="{
                                        'border-red-200 ring-red-50':
                                            assignmentForm.errors.class_id,
                                    }"
                                >
                                    <SelectValue
                                        placeholder="Which class is this for?"
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="max-h-[300px] rounded-2xl border-slate-100 bg-white/90 p-2 shadow-lg backdrop-blur-lg"
                                >
                                    <SelectItem
                                        v-for="cls in classes"
                                        :key="cls.id"
                                        :value="cls.id.toString()"
                                        class="mb-1 cursor-pointer rounded-xl py-3 font-semibold last:mb-0 focus:bg-blue-50 focus:text-blue-700"
                                    >
                                        <div
                                            class="flex w-full items-center justify-between"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="h-2 w-2 rounded-full bg-blue-400"
                                                ></span>
                                                {{ cls.name }}
                                                {{
                                                    cls.stream
                                                        ? '(' + cls.stream + ')'
                                                        : ''
                                                }}
                                            </div>
                                            <span
                                                class="rounded bg-slate-50 px-1.5 py-0.5 text-xs font-bold text-slate-400 uppercase"
                                                >{{ cls.academic_year }}</span
                                            >
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="assignmentForm.errors.class_id"
                                class="px-1 text-xs font-bold text-red-500"
                            >
                                {{ assignmentForm.errors.class_id }}
                            </p>
                        </div>

                        <!-- Teacher Selection with Search -->
                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-bold tracking-[0.1em] text-slate-400 uppercase"
                                >Select Teacher</Label
                            >
                            <Select v-model="assignmentForm.teacher_id">
                                <SelectTrigger
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold shadow-none transition-all hover:bg-slate-50"
                                    :class="{
                                        'border-red-200 ring-red-50':
                                            assignmentForm.errors.teacher_id,
                                    }"
                                >
                                    <SelectValue
                                        placeholder="Assign a teacher..."
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="max-h-[400px] overflow-hidden rounded-2xl border-slate-100 bg-white/90 p-0 shadow-lg backdrop-blur-lg"
                                >
                                    <div
                                        class="sticky top-0 z-10 border-b border-slate-50 bg-white/50 p-3 backdrop-blur-md"
                                    >
                                        <div class="relative">
                                            <Search
                                                class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                                            />
                                            <Input
                                                v-model="teacherSearch"
                                                placeholder="Search teacher by name or staff ID..."
                                                class="h-9 rounded-xl border-0 border-slate-100 bg-white pl-9 text-xs font-medium shadow-none ring-1 ring-slate-100 focus:ring-0"
                                            />
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <SelectItem
                                            v-for="teacher in filteredTeachers"
                                            :key="teacher.id"
                                            :value="teacher.id.toString()"
                                            class="mb-1 cursor-pointer rounded-xl py-3 font-semibold last:mb-0 focus:bg-blue-50 focus:text-blue-700"
                                        >
                                            <div class="flex flex-col">
                                                <span>{{ teacher.name }}</span>
                                                <span
                                                    class="text-xs font-bold tracking-tighter text-slate-400 uppercase"
                                                    >{{
                                                        teacher.staff_number
                                                    }}</span
                                                >
                                            </div>
                                        </SelectItem>
                                        <div
                                            v-if="filteredTeachers.length === 0"
                                            class="p-8 text-center"
                                        >
                                            <Search
                                                class="mx-auto mb-2 h-8 w-8 text-slate-200"
                                            />
                                            <p
                                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                            >
                                                No teachers matching your search
                                            </p>
                                        </div>
                                    </div>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="assignmentForm.errors.teacher_id"
                                class="px-1 text-xs font-bold text-red-500"
                            >
                                {{ assignmentForm.errors.teacher_id }}
                            </p>
                        </div>

                        <!-- Role Toggles -->
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                class="group flex cursor-pointer items-center justify-between rounded-[1.5rem] border border-blue-100/30 bg-blue-50/30 p-5 transition-all hover:bg-blue-50/50"
                                @click="
                                    assignmentForm.is_primary_teacher =
                                        !assignmentForm.is_primary_teacher
                                "
                            >
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <CheckCircle2
                                            class="h-4 w-4 text-blue-600"
                                            :class="{
                                                'opacity-20':
                                                    !assignmentForm.is_primary_teacher,
                                            }"
                                        />
                                        <Label
                                            class="cursor-pointer text-sm font-bold tracking-wider text-slate-700 uppercase"
                                            >Primary Teacher</Label
                                        >
                                    </div>
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        Lead instructor for this specific class.
                                    </p>
                                </div>
                                <Switch
                                    :checked="assignmentForm.is_primary_teacher"
                                />
                            </div>
                        </div>

                        <DialogFooter class="px-0 pt-4 pb-2">
                            <Button
                                type="submit"
                                :disabled="assignmentForm.processing"
                                class="h-14 w-full rounded-2xl bg-blue-600 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700"
                            >
                                {{
                                    assignmentForm.processing
                                        ? 'Recording...'
                                        : 'Verify & Confirm'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
