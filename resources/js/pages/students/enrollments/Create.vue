<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    GraduationCap,
    Loader2,
    Save,
    UserPlus,
    AlertTriangle,
    User,
    Calendar,
    School,
    BookOpen,
    Layers,
    Info,
    Search,
    X,
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import axios from 'axios';
import { format } from 'date-fns';

const props = defineProps<{
    academicYears: Array<{ id: number; name: string }>;
    academicTerms: Array<{ id: number; name: string }>;
    classes: Array<{
        id: number;
        name: string;
        grade_id: number;
        stream_id: number;
    }>;
    streams: Array<{ id: number; name: string }>;
    grades: Array<{ id: number; name: string }>;
    studentId?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
    { title: 'New Enrollment', href: '/students/enrollments/create' },
];

const form = useForm({
    student_id: props.studentId || '',
    academic_year_id: '',
    academic_term_id: '',
    class_id: '',
    stream_id: '',
    admission_number: '',
    enrollment_date: format(new Date(), 'yyyy-MM-dd'),
    status: 'active',
    entry_type: 'new',
    boarding_status: 'day',
    sponsor_type: 'Self',
    previous_school: '',
    notes: '',
});

// Student Search logic
const searchQuery = ref('');
const isSearching = ref(false);
const searchResults = ref<any[]>([]);
const selectedStudent = ref<any>(null);

const searchStudents = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get(
            `/api/v1/student/students?filter[search]=${searchQuery.value}`,
        );
        searchResults.value = response.data.data;
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        isSearching.value = false;
    }
};

let searchTimeout: any = null;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchStudents, 300);
});

const selectStudent = (student: any) => {
    selectedStudent.value = student;
    form.student_id = student.id;
    form.admission_number = student.admission_number || '';
    searchQuery.value = '';
    searchResults.value = [];
};

const clearStudent = () => {
    selectedStudent.value = null;
    form.student_id = '';
    form.admission_number = '';
};

// Auto-select class based on Grade + Stream
const selectedGradeId = ref<string>('');
const selectedStreamId = ref<string>('');

watch([selectedGradeId, selectedStreamId], ([gradeId, streamId]) => {
    if (gradeId) {
        const match = props.classes.find(
            (c) =>
                c.grade_id == parseInt(gradeId) &&
                (!streamId || c.stream_id == parseInt(streamId)),
        );
        if (match) {
            form.class_id = match.id.toString();
            form.stream_id = streamId;
        } else {
            form.class_id = '';
        }
    }
});

onMounted(async () => {
    if (props.studentId) {
        try {
            const response = await axios.get(
                `/api/v1/student/students/${props.studentId}`,
            );
            selectStudent(response.data.data);
        } catch (e) {
            console.error('Failed to load preselected student', e);
        }
    }

    // Set default academic year if one is marked current
    // (Assuming we might pass more data or just pick the first one)
    if (props.academicYears.length > 0) {
        form.academic_year_id = props.academicYears[0].id.toString();
    }
});

const submit = () => {
    form.post('/students/enrollments', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Formal Enrollment" />

        <div
            class="mx-auto h-full max-w-[1200px] flex-1 animate-in flex-col gap-8 p-6 duration-700 fade-in"
        >
            <!-- Header section -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4 sm:gap-5">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 sm:h-12 sm:w-12 sm:rounded-2xl"
                    >
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"
                        >
                            Formal Ingest
                        </h1>
                        <p
                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase sm:text-xs"
                        >
                            Academic Assignment Protocol
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:h-11 sm:flex-none sm:px-6"
                    >
                        <Link href="/students/enrollments">
                            <ArrowLeft class="mr-2 h-4 w-4" /> Registry
                        </Link>
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 pb-12 sm:gap-8">
                <!-- 1. Student Selection -->
                <div
                    class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100"
                >
                    <div
                        class="border-b border-slate-50 bg-slate-50/30 px-6 py-5 sm:px-8"
                    >
                        <h2
                            class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900"
                        >
                            <User class="h-5 w-5 text-blue-600" />
                            Entity Identification
                        </h2>
                        <p
                            class="mt-1 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                        >
                            Source Data Hook
                        </p>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div v-if="!selectedStudent" class="relative">
                            <Label
                                for="student-search"
                                class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >Scan Registry (Name / ADM)</Label
                            >
                            <div class="group relative mt-2">
                                <Search
                                    class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-500"
                                />
                                <Input
                                    id="student-search"
                                    v-model="searchQuery"
                                    placeholder="ENTRY..."
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 pl-12 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                />
                                <div
                                    v-if="isSearching"
                                    class="absolute top-1/2 right-4 -translate-y-1/2"
                                >
                                    <Loader2
                                        class="h-4 w-4 animate-spin text-blue-500"
                                    />
                                </div>
                            </div>

                            <div
                                v-if="searchResults.length > 0"
                                class="absolute z-50 mt-2 max-h-72 w-full animate-in overflow-hidden overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-lg duration-200 fade-in slide-in-from-top-2"
                            >
                                <div
                                    v-for="student in searchResults"
                                    :key="student.id"
                                    @click="selectStudent(student)"
                                    class="group flex cursor-pointer items-center border-b border-slate-50 px-4 py-4 transition-colors last:border-0 hover:bg-slate-50"
                                >
                                    <div
                                        class="mr-4 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        <User class="h-5 w-5" />
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                        >
                                            {{ student.first_name }}
                                            {{ student.last_name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Handle:
                                            {{
                                                student.admission_number ||
                                                'VOID'
                                            }}
                                            | Context: {{ student.status }}
                                        </p>
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="ml-2 rounded-md border-slate-100 bg-slate-50 px-2 py-0.5 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >{{ student.status }}</Badge
                                    >
                                </div>
                            </div>
                            <InputError
                                :message="form.errors.student_id"
                                class="mt-2"
                            />
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-blue-100/50 bg-blue-50/30 p-6 sm:flex-row"
                        >
                            <div
                                class="flex flex-col items-center text-center sm:flex-row sm:text-left"
                            >
                                <div
                                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 sm:mr-6 sm:mb-0"
                                >
                                    <User class="h-8 w-8" />
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-bold tracking-tight text-slate-900 sm:text-base"
                                    >
                                        {{ selectedStudent.first_name }}
                                        {{ selectedStudent.last_name }}
                                    </p>
                                    <p
                                        class="mt-1.5 text-xs font-bold tracking-tight text-blue-600 text-muted-foreground uppercase sm:text-xs"
                                    >
                                        System Handle:
                                        {{
                                            selectedStudent.admission_number ||
                                            'NOT_ASSIGNED'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                @click="clearStudent"
                                class="h-10 w-10 shrink-0 rounded-xl text-slate-400 transition-all hover:bg-red-50 hover:text-red-500"
                            >
                                <X class="h-5 w-5" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:gap-8 lg:grid-cols-2">
                    <!-- 2. Academic Assignment -->
                    <div
                        class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100"
                    >
                        <div
                            class="border-b border-slate-50 bg-slate-50/30 px-6 py-5 sm:px-8"
                        >
                            <h2
                                class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900"
                            >
                                <Layers class="h-5 w-5 text-blue-600" />
                                Academic Linkage
                            </h2>
                            <p
                                class="mt-1 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                            >
                                Spatio-Temporal Mapping
                            </p>
                        </div>
                        <div class="space-y-6 p-6 sm:p-8">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Academic Epoch</Label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.academic_year_id"
                                            class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option
                                                v-for="year in academicYears"
                                                :key="year.id"
                                                :value="year.id.toString()"
                                            >
                                                {{ year.name }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.academic_year_id"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Temporal Node (Term)</Label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.academic_term_id"
                                            class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option value="">Full Cycle</option>
                                            <option
                                                v-for="term in academicTerms"
                                                :key="term.id"
                                                :value="term.id.toString()"
                                            >
                                                {{ term.name }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.academic_term_id"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Level / Grade</Label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="selectedGradeId"
                                            class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option value="">
                                                Select level
                                            </option>
                                            <option
                                                v-for="grade in grades"
                                                :key="grade.id"
                                                :value="grade.id.toString()"
                                            >
                                                {{ grade.name }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.class_id"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Context / Stream</Label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="selectedStreamId"
                                            class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option value="">Unmapped</option>
                                            <option
                                                v-for="stream in streams"
                                                :key="stream.id"
                                                :value="stream.id.toString()"
                                            >
                                                {{ stream.name }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.stream_id"
                                    />
                                </div>
                            </div>

                            <div
                                v-if="form.class_id"
                                class="flex animate-in items-center rounded-2xl border border-emerald-100 bg-emerald-50/50 px-6 py-4 duration-300 zoom-in-95 fade-in"
                            >
                                <CheckCircle2
                                    class="mr-3 h-5 w-5 shrink-0 text-emerald-600"
                                />
                                <span
                                    class="text-xs leading-tight font-bold tracking-tight text-emerald-700 uppercase"
                                    >Registry Match Found: Logical Link
                                    Validated</span
                                >
                            </div>
                            <div
                                v-else-if="selectedGradeId"
                                class="flex animate-in items-center rounded-2xl border border-amber-100 bg-amber-50/50 px-6 py-4 duration-300 zoom-in-95 fade-in"
                            >
                                <AlertTriangle
                                    class="mr-3 h-5 w-5 shrink-0 text-amber-600"
                                />
                                <span
                                    class="text-xs leading-tight font-bold tracking-tight text-amber-700 uppercase"
                                    >Registry Exception: No Logical Context for
                                    this Path</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- 3. Lifecycle Details -->
                    <div
                        class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100"
                    >
                        <div
                            class="border-b border-slate-50 bg-slate-50/30 px-6 py-5 sm:px-8"
                        >
                            <h2
                                class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900"
                            >
                                <Calendar class="h-5 w-5 text-blue-600" />
                                Registry Pulse
                            </h2>
                            <p
                                class="mt-1 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                            >
                                Logistics & State Ingest
                            </p>
                        </div>
                        <div class="space-y-6 p-6 sm:p-8">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >Registry Handle (ADM)</Label
                                >
                                <Input
                                    v-model="form.admission_number"
                                    placeholder="SYNTAX: 2024-XXXX"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight text-blue-600 uppercase transition-all focus:bg-white"
                                />
                                <InputError
                                    :message="form.errors.admission_number"
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Ingest Epoch</Label
                                    >
                                    <Input
                                        type="date"
                                        v-model="form.enrollment_date"
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                    />
                                    <InputError
                                        :message="form.errors.enrollment_date"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Neural State</Label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.status"
                                            class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option value="active">
                                                Active
                                            </option>
                                            <option value="withdrawn">
                                                Withdrawn
                                            </option>
                                            <option value="transferred">
                                                Transferred
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Metadata Payload -->
                <div
                    class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100"
                >
                    <div
                        class="border-b border-slate-50 bg-slate-50/30 px-6 py-5 sm:px-8"
                    >
                        <h2
                            class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900"
                        >
                            <Info class="h-5 w-5 text-blue-600" />
                            Metadata Payload
                        </h2>
                        <p
                            class="mt-1 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                        >
                            Supplemental Logic
                        </p>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >Ingest Protocol</Label
                                >
                                <div class="relative">
                                    <select
                                        v-model="form.entry_type"
                                        class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                    >
                                        <option value="new">New Student</option>
                                        <option value="transfer">
                                            Transfer In
                                        </option>
                                        <option value="continuing">
                                            Continuing
                                        </option>
                                    </select>
                                    <ChevronDown
                                        class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                </div>
                                <InputError :message="form.errors.entry_type" />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >Residency Hub</Label
                                >
                                <div class="relative">
                                    <select
                                        v-model="form.boarding_status"
                                        class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                    >
                                        <option value="day">Day Scholar</option>
                                        <option value="boarding">
                                            Boarder
                                        </option>
                                    </select>
                                    <ChevronDown
                                        class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                </div>
                                <InputError
                                    :message="form.errors.boarding_status"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >Fee Propagation</Label
                                >
                                <div class="relative">
                                    <select
                                        v-model="form.sponsor_type"
                                        class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                    >
                                        <option value="Self">
                                            Self / Parent
                                        </option>
                                        <option value="Scholarship">
                                            Scholarship
                                        </option>
                                        <option value="Government">
                                            Government
                                        </option>
                                        <option value="NGO">NGO / Donor</option>
                                    </select>
                                    <ChevronDown
                                        class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                </div>
                                <InputError
                                    :message="form.errors.sponsor_type"
                                />
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >Previous Context (Legacy)</Label
                                >
                                <Input
                                    v-model="form.previous_school"
                                    placeholder="VOID"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                />
                                <InputError
                                    :message="form.errors.previous_school"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >Internal Annotation</Label
                                >
                                <Input
                                    v-model="form.notes"
                                    placeholder="LOG..."
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div
                    class="flex flex-col items-center justify-between gap-6 border-t border-slate-100 pt-10 sm:flex-row"
                >
                    <div
                        class="hidden items-center gap-3 rounded-2xl border border-blue-100/50 bg-blue-50 px-5 py-2 text-blue-700 shadow-sm sm:flex"
                    >
                        <div
                            class="h-2 w-2 animate-pulse rounded-full bg-blue-500"
                        ></div>
                        <span
                            class="text-xs font-medium tracking-tight uppercase"
                            >Registry Sync Armed</span
                        >
                    </div>
                    <div
                        class="flex w-full flex-col gap-4 sm:w-auto sm:flex-row"
                    >
                        <Button
                            type="button"
                            variant="ghost"
                            @click="form.reset()"
                            class="h-12 w-full rounded-xl px-8 text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900 sm:w-auto"
                        >
                            Reset Form
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing || !form.student_id"
                            class="h-12 w-full rounded-xl border-0 bg-slate-900 px-10 text-sm font-bold tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 sm:w-auto"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Finalize Ingest
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Scoped styles kept for safety if needed, though Tailwind classes preferred */
</style>
