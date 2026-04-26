<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Download,
    MoreHorizontal,
    Users,
    CheckCircle2,
    AlertTriangle,
    TrendingUp,
    BookOpen,
    BookCopy,
    Upload,
    FileText,
    X,
    AlertCircle,
    Plus,
    Filter,
    ChevronRight,
    Loader2,
    Sparkles,
    Search,
    Info,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subjects: any[];
    grades: any[];
    learningAreas: any[];
    teachers: any[];
    classes: any[];
    academicYear: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/syllabus' },
];

const searchQuery = ref('');
const showAddModal = ref(false);
const viewMode = ref<'grid' | 'list'>('list');

const filteredSubjects = computed(() => {
    if (!searchQuery.value) return props.subjects;
    const lowerQuery = searchQuery.value.toLowerCase();
    return props.subjects.filter(
        (s) =>
            s.name.toLowerCase().includes(lowerQuery) ||
            (s.code && s.code.toLowerCase().includes(lowerQuery)),
    );
});

const form = useForm({
    name: '',
    learning_area_id: '',
    code: '',
    description: '',
});

const submit = () => {
    form.post(route('curriculum.syllabus.subjects.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

// Teacher Assignment Logic
const showAssignmentModal = ref(false);
const teacherSearch = ref('');
const assignmentForm = useForm({
    teacher_id: '',
    subject_id: '',
    class_id: '',
    academic_year_id: props.academicYear?.id || '',
    is_primary_teacher: true,
    is_active: true,
});

const selectedSubject = computed(() => {
    return props.subjects.find(
        (s) => s.id.toString() === assignmentForm.subject_id,
    );
});

const filteredClasses = computed(() => {
    if (!selectedSubject.value) return [];

    // Filter classes to only show those that belong to the subject's grade levels
    const gradeLevelIds = selectedSubject.value.grade_level_ids || [];
    return props.classes.filter((c) => gradeLevelIds.includes(c.grade_id));
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

const openAssignmentModal = (subjectId: string) => {
    assignmentForm.teacher_id = '';
    assignmentForm.class_id = '';
    assignmentForm.subject_id = subjectId;
    teacherSearch.value = '';
    assignmentForm.clearErrors();
    showAssignmentModal.value = true;
};

const submitAssignment = () => {
    assignmentForm.post(route('classes.allocations.store'), {
        onSuccess: () => {
            showAssignmentModal.value = false;
        },
    });
};

const showBulkModal = ref(false);
const bulkForm = useForm({
    file: null as File | null,
});

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        bulkForm.file = input.files[0];
    }
};

const submitBulk = () => {
    bulkForm.post(route('curriculum.syllabus.subjects.bulk'), {
        onSuccess: () => {
            showBulkModal.value = false;
            bulkForm.reset();
        },
    });
};

const dragOver = ref(false);
const route = (window as any).route;

const handleDrop = (event: DragEvent) => {
    dragOver.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        bulkForm.file = event.dataTransfer.files[0];
    }
};

const removeFile = () => {
    bulkForm.file = null;
};
</script>

<template>
    <Head title="Syllabus Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col justify-between gap-4 px-1 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <h1
                        class="flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        <BookOpenCheck class="h-8 w-8 text-blue-600" />
                        Subjects
                    </h1>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">
                        Manage institutional learning areas and academic
                        standards.
                    </p>
                </div>

                <div
                    class="flex items-center gap-2 rounded-2xl border border-slate-200/50 bg-slate-100/50 p-1.5 sm:gap-3"
                >
                    <Dialog v-model:open="showAddModal">
                        <DialogTrigger as-child>
                            <Button
                                class="h-10 rounded-xl border-0 bg-blue-600 px-5 text-xs font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Add Subject
                            </Button>
                        </DialogTrigger>
                        <DialogContent
                            class="overflow-hidden rounded-3xl border-0 bg-white p-0 shadow-lg sm:max-w-[425px]"
                        >
                            <DialogHeader
                                class="relative bg-slate-900 p-8 text-white"
                            >
                                <div
                                    class="absolute top-0 right-0 p-8 opacity-10"
                                >
                                    <BookOpen class="h-20 w-20 rotate-12" />
                                </div>
                                <div class="relative z-10">
                                    <DialogTitle
                                        class="mb-1 text-2xl font-bold tracking-tight"
                                        >New Subject</DialogTitle
                                    >
                                    <DialogDescription
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        Add a new subject to the curriculum.
                                    </DialogDescription>
                                </div>
                            </DialogHeader>
                            <form
                                @submit.prevent="submit"
                                class="space-y-6 p-8"
                            >
                                <div class="space-y-2">
                                    <Label
                                        for="name"
                                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Subject Name</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g. Mathematics"
                                        class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold transition-all focus:ring-2 focus:ring-blue-600/10"
                                        required
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="learning_area"
                                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Learning Area</Label
                                    >
                                    <Select v-model="form.learning_area_id">
                                        <SelectTrigger
                                            id="learning_area"
                                            class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold transition-all focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <SelectValue
                                                placeholder="Select Area"
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            class="rounded-2xl border-slate-100 bg-white/95 shadow-lg backdrop-blur-xl"
                                        >
                                            <SelectItem
                                                v-for="area in learningAreas"
                                                :key="area.id"
                                                :value="area.id.toString()"
                                                class="cursor-pointer rounded-xl py-3 font-semibold focus:bg-blue-50 focus:text-blue-700"
                                            >
                                                {{ area.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="space-y-2">
                                        <Label
                                            for="code"
                                            class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                            >Subject Code</Label
                                        >
                                        <Input
                                            id="code"
                                            v-model="form.code"
                                            placeholder="e.g. MAT"
                                            class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold tracking-tight uppercase transition-all focus:ring-2 focus:ring-blue-600/10"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="description"
                                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Brief Description</Label
                                    >
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Provide extra context..."
                                        class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50/50 text-sm transition-all focus:ring-2 focus:ring-blue-600/10"
                                    />
                                </div>
                                <DialogFooter class="pt-4">
                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="h-14 w-full rounded-2xl bg-blue-600 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700"
                                    >
                                        {{
                                            form.processing
                                                ? 'Saving...'
                                                : 'Save Subject'
                                        }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div
                class="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-8 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-xl"
            >
                <div
                    class="absolute -right-10 -bottom-10 text-blue-600 opacity-[0.03] transition-transform duration-1000 group-hover:scale-110"
                >
                    <BookCopy class="h-64 w-64" />
                </div>
                <div
                    class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-center gap-6">
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border border-blue-100/50 bg-blue-50 text-blue-600 shadow-sm transition-transform group-hover:rotate-6"
                        >
                            <Upload class="h-10 w-10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h2
                                    class="text-2xl font-bold tracking-tight text-slate-900"
                                >
                                    Bulk Syllabus Management
                                </h2>
                                <Badge
                                    variant="secondary"
                                    class="border-emerald-100/50 bg-emerald-50 px-2 py-0.5 text-xs font-medium tracking-tight text-emerald-600 uppercase"
                                    >Efficient</Badge
                                >
                            </div>
                            <p
                                class="mt-1 max-w-md text-sm font-medium text-slate-500"
                            >
                                Import multiple subjects at once using our
                                standardized curriculum template.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button
                            variant="outline"
                            class="h-12 rounded-2xl border-slate-200 bg-white px-8 text-sm font-bold tracking-tight uppercase transition-all hover:bg-slate-50"
                            as-child
                        >
                            <a
                                :href="
                                    route(
                                        'curriculum.syllabus.subjects.template',
                                    )
                                "
                                download
                            >
                                <Download
                                    class="mr-2 h-4 w-4 text-emerald-600"
                                />
                                Download Template
                            </a>
                        </Button>
                        <Button
                            @click="showBulkModal = true"
                            class="h-13 rounded-2xl border-0 bg-slate-900 px-10 text-sm font-bold tracking-tight text-white uppercase shadow-lg shadow-slate-200 transition-all hover:bg-slate-800"
                        >
                            <BookCopy class="mr-2 h-4 w-4" />
                            Upload CSV Dataset
                        </Button>
                    </div>
                </div>
            </div>

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
                                    selectedSubject?.name
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
                            <div class="flex items-center justify-between px-1">
                                <Label
                                    class="text-xs font-bold tracking-[0.1em] text-slate-400 uppercase"
                                    >Target Class</Label
                                >
                                <Badge
                                    v-if="filteredClasses.length"
                                    variant="outline"
                                    class="h-4 rounded-full border-slate-100 py-0 text-xs font-bold text-slate-400"
                                >
                                    {{ filteredClasses.length }} Available
                                </Badge>
                            </div>
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
                                        v-for="cls in filteredClasses"
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
                                                {{ cls.grade_name }} —
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
                                    <div
                                        v-if="filteredClasses.length === 0"
                                        class="p-8 text-center"
                                    >
                                        <Info
                                            class="mx-auto mb-2 h-8 w-8 text-slate-200"
                                        />
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            No classes found for this subject's
                                            grades
                                        </p>
                                    </div>
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

            <!-- Stats Cards Section -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Subjects -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Subjects
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ subjects.length }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <BookOpen class="h-5 w-5" />
                    </div>
                </div>

                <!-- Active -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Active
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ subjects.length }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>

                <!-- Learning Areas -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Areas
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ learningAreas.length }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500"
                    >
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                </div>

                <!-- Updates -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Growth
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            NEW
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-500"
                    >
                        <TrendingUp class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div
                class="min-h-[500px] overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white shadow-sm"
            >
                <!-- Filters Bar -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-50 p-6 md:flex-row md:items-center"
                >
                    <div class="relative w-full md:max-w-md">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by name or code..."
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

                <!-- Quick Filters -->
                <div
                    class="flex flex-wrap gap-3 border-b border-slate-50 bg-white px-6 py-4"
                >
                    <div
                        v-for="filter in [
                            'Learning Areas',
                            'Grades',
                            'Statuses',
                        ]"
                        :key="filter"
                        class="flex h-10 min-w-[150px] cursor-pointer items-center justify-between rounded-xl border border-slate-100 bg-[#fbfcfd] px-4 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50"
                    >
                        {{ filter }}
                        <ChevronRight
                            class="h-3 w-3 rotate-90 text-slate-300"
                        />
                    </div>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-50 bg-white">
                                <th class="w-10 py-4 pl-6">
                                    <div
                                        class="h-4 w-4 rounded border border-slate-200"
                                    ></div>
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Subject
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Area
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Date
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
                                v-for="subject in filteredSubjects"
                                :key="subject.id"
                                class="group transition-all hover:bg-[#f9fafb]/50"
                            >
                                <td class="py-4 pl-6">
                                    <div
                                        class="h-4 w-4 rounded border border-slate-200 transition-colors group-hover:border-blue-400"
                                    ></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-xs font-bold text-blue-600"
                                        >
                                            {{
                                                subject.name
                                                    .substring(0, 2)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-slate-800"
                                                >{{ subject.name }}</span
                                            >
                                            <span
                                                class="text-xs font-medium text-slate-400 uppercase"
                                                >{{
                                                    subject.code ||
                                                    'SUB-' + subject.id
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="text-xs font-semibold text-slate-600"
                                        >{{
                                            subject.learning_area?.name ||
                                            'General'
                                        }}</span
                                    >
                                </td>
                                <td class="px-4 py-4">
                                    <Badge
                                        class="rounded border-0 bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-600"
                                    >
                                        ACTIVE
                                    </Badge>
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-slate-400"
                                >
                                    2026-03-26
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="h-9 rounded-xl px-4 text-xs font-bold text-blue-600 uppercase hover:bg-blue-50"
                                            >
                                                Manage
                                                <ChevronRight
                                                    class="ml-1 h-3.5 w-3.5"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="min-w-[220px] rounded-2xl border-slate-100 bg-white p-3 shadow-xl"
                                        >
                                            <div
                                                class="mb-2 border-b border-slate-50 pb-2"
                                            >
                                                <h4
                                                    class="px-1 text-xs font-bold text-slate-400 uppercase"
                                                >
                                                    Actions
                                                </h4>
                                            </div>

                                            <DropdownMenuItem
                                                @click="
                                                    openAssignmentModal(
                                                        subject.id.toString(),
                                                    )
                                                "
                                                class="flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold uppercase transition-all hover:bg-blue-50 hover:text-blue-600"
                                            >
                                                <Users class="h-3.5 w-3.5" />
                                                Assign Teacher
                                            </DropdownMenuItem>

                                            <div
                                                class="my-2 border-b border-slate-50"
                                            ></div>
                                            <div class="mb-1 px-1">
                                                <h4
                                                    class="text-xs font-bold text-slate-400 uppercase"
                                                >
                                                    View Syllabus
                                                </h4>
                                            </div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <DropdownMenuItem
                                                    v-for="grade in grades"
                                                    :key="grade.id"
                                                    as-child
                                                >
                                                    <Link
                                                        :href="`/curriculum/syllabus/${subject.id}/${grade.id}`"
                                                        class="flex cursor-pointer items-center justify-center rounded-lg border border-transparent bg-slate-50 px-2 py-1.5 text-xs font-bold uppercase transition-all hover:bg-blue-600 hover:text-white"
                                                    >
                                                        {{ grade.name }}
                                                    </Link>
                                                </DropdownMenuItem>
                                            </div>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-slate-50 bg-white p-6"
                >
                    <p class="text-xs font-bold text-slate-400">
                        Found {{ filteredSubjects.length }} items
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-100 px-3 text-xs font-bold text-slate-300"
                            >Prev</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-200 px-3 text-xs font-bold text-slate-900"
                            >1</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-100 px-3 text-xs font-bold text-slate-300"
                            >Next</Button
                        >
                    </div>
                </div>
            </div>

            <!-- Bulk Upload Modal -->
            <Dialog v-model:open="showBulkModal">
                <DialogContent
                    class="overflow-hidden rounded-xl border-0 bg-card p-0 shadow-lg sm:max-w-[500px]"
                >
                    <DialogHeader class="relative bg-slate-900 p-10 text-white">
                        <div class="absolute top-0 right-0 p-10 opacity-10">
                            <BookCopy class="h-24 w-24" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle
                                class="mb-1 text-3xl font-bold tracking-tight"
                                >Bulk Ingestion</DialogTitle
                            >
                            <DialogDescription
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Syllabus Dataset Integration
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    <div class="space-y-8 p-10">
                        <div
                            class="group flex items-center justify-between rounded-[1.5rem] border border-slate-100 bg-slate-50/50 p-6 transition-all hover:bg-blue-50/30"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200 transition-transform group-hover:scale-110"
                                >
                                    <FileText class="h-6 w-6" />
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium tracking-tight text-slate-900 uppercase"
                                    >
                                        Standard Template
                                    </p>
                                    <p class="text-xs font-bold text-slate-400">
                                        Required CSV Schema
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase shadow-sm hover:bg-white"
                                as-child
                            >
                                <a
                                    :href="
                                        route(
                                            'curriculum.syllabus.subjects.template',
                                        )
                                    "
                                    download
                                >
                                    <Download
                                        class="mr-2 h-3.5 w-3.5 text-emerald-600"
                                    />
                                    Download
                                </a>
                            </Button>
                        </div>

                        <div
                            class="group relative flex flex-col items-center justify-center rounded-xl border-3 border-dashed p-12 transition-all duration-500"
                            :class="[
                                dragOver
                                    ? 'scale-[0.98] border-blue-600 bg-blue-50/50'
                                    : 'border-slate-100 bg-slate-50/30',
                                bulkForm.file
                                    ? 'border-emerald-500/50 bg-emerald-50/20'
                                    : '',
                            ]"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                        >
                            <input
                                type="file"
                                accept=".csv"
                                class="absolute inset-0 z-20 cursor-pointer opacity-0"
                                @change="handleFileChange"
                            />

                            <template v-if="!bulkForm.file">
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white shadow-xl shadow-slate-200/50 transition-transform duration-500 group-hover:translate-y-[-4px]"
                                >
                                    <Upload class="h-8 w-8 text-blue-600" />
                                </div>
                                <p
                                    class="mt-6 text-sm font-bold tracking-tight text-slate-900"
                                >
                                    Drop CSV file here
                                </p>
                                <p
                                    class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    or click to browse local storage
                                </p>
                            </template>

                            <template v-else>
                                <div
                                    class="flex w-full min-w-0 animate-in items-center gap-6 duration-300 zoom-in-95"
                                >
                                    <div
                                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.5rem] bg-emerald-500 text-white shadow-xl shadow-emerald-200"
                                    >
                                        <CheckCircle2 class="h-8 w-8" />
                                    </div>
                                    <div class="min-w-0 flex-1 overflow-hidden">
                                        <p
                                            class="text-sm leading-tight font-bold break-all text-slate-900"
                                            :title="bulkForm.file.name"
                                        >
                                            {{ bulkForm.file.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-bold text-slate-400 uppercase"
                                        >
                                            {{
                                                (
                                                    bulkForm.file.size / 1024
                                                ).toFixed(2)
                                            }}
                                            KB
                                        </p>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="z-30 h-10 w-10 shrink-0 text-slate-400 transition-colors hover:text-rose-500"
                                        @click.stop="removeFile"
                                    >
                                        <X class="h-5 w-5" />
                                    </Button>
                                </div>
                            </template>
                        </div>

                        <div
                            v-if="bulkForm.errors.file"
                            class="flex animate-in items-start gap-2 rounded-xl border border-rose-100 bg-rose-50 p-4 slide-in-from-top-2"
                        >
                            <AlertCircle
                                class="mt-0.5 h-4 w-4 shrink-0 text-rose-500"
                            />
                            <span
                                class="text-sm leading-relaxed font-bold text-rose-600"
                                >{{ bulkForm.errors.file }}</span
                            >
                        </div>

                        <Button
                            @click="submitBulk"
                            :disabled="!bulkForm.file || bulkForm.processing"
                            class="group h-16 w-full overflow-hidden rounded-2xl bg-slate-900 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-slate-200 transition-all hover:bg-slate-800"
                        >
                            <span
                                v-if="bulkForm.processing"
                                class="flex items-center gap-2"
                            >
                                <Loader2 class="h-4 w-4 animate-spin" />
                                Processing Dataset...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Sparkles
                                    class="h-4 w-4 text-amber-400 group-hover:animate-pulse"
                                />
                                Execute Import Task
                            </span>
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
