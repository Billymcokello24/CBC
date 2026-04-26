<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft,
    Edit,
    Trash2,
    Plus,
    Filter,
    X,
    TrendingUp,
    Users,
    User,
    UserPlus,
    UserCheck,
    BookOpen,
    Award,
    GraduationCap,
    Calendar,
    Search,
    MoreHorizontal,
    Check,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const page = usePage();
const dept = page.props?.department ?? {};
const teachers = page.props?.teachers ?? [];
const subjects = page.props?.subjects ?? [];
const grades = page.props?.grades ?? [];
const analytics = page.props?.analytics ?? {};
const curriculumSubjects = page.props?.curriculum_subjects ?? [];

const selectedTermFilter = ref('all');
const selectedYearFilter = ref('all');
const selectedExamFilter = ref('all');
const showAddSubjectModal = ref(false);
const showAssignHeadModal = ref(false);
const showAddTeacherModal = ref(false);
const subjectSearch = ref('');
const teacherSearch = ref('');
const allTeachers = page.props?.all_teachers ?? [];

const filteredCurriculumSubjects = computed(() => {
    const search = subjectSearch.value.toLowerCase();
    return curriculumSubjects.filter(
        (s) =>
            (s.name.toLowerCase().includes(search) ||
                s.code.toLowerCase().includes(search)) &&
            !subjects.some((existing) => existing.id === s.id),
    );
});

const filteredTeachers = computed(() => {
    const search = teacherSearch.value.toLowerCase();
    return allTeachers.filter(
        (t) =>
            t.name.toLowerCase().includes(search) &&
            !teachers.some((existing) => existing.id === t.id),
    );
});

const filterGrades = computed(() => {
    let filtered = grades;
    if (selectedTermFilter.value !== 'all') {
        filtered = filtered.filter((g) => g.term === selectedTermFilter.value);
    }
    if (selectedYearFilter.value !== 'all') {
        filtered = filtered.filter((g) => g.year === selectedYearFilter.value);
    }
    // Exam type filtering if available in data
    return filtered;
});

const addSubject = (subjectId) => {
    router.post(
        `/departments/${dept.id}/subjects`,
        { subject_id: subjectId },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddSubjectModal.value = false;
                subjectSearch.value = '';
            },
        },
    );
};

const removeSubject = (subjectId) => {
    if (
        !confirm(
            'Are you sure you want to remove this subject from the department?',
        )
    )
        return;
    router.delete(`/departments/${dept.id}/subjects/${subjectId}`, {
        preserveScroll: true,
    });
};

const assignHead = (teacherId) => {
    router.patch(
        `/departments/${dept.id}/assign-head`,
        { head_of_department_id: teacherId },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAssignHeadModal.value = false;
            },
        },
    );
};

const addTeacher = (teacherId) => {
    router.post(
        `/departments/${dept.id}/teachers`,
        { teacher_id: teacherId },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddTeacherModal.value = false;
                teacherSearch.value = '';
            },
        },
    );
};

const removeTeacher = (teacherId) => {
    if (
        !confirm(
            'Are you sure you want to remove this staff member from the department?',
        )
    )
        return;
    router.delete(`/departments/${dept.id}/teachers/${teacherId}`, {
        preserveScroll: true,
    });
};

const toggleStatus = () => {
    router.patch(
        `/departments/${dept.id}/activate`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Departments', href: '/departments' },
    { title: dept.name, href: '#' },
];
</script>

<template>
    <Head :title="dept.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="w-full animate-in space-y-8 px-4 py-8 duration-500 fade-in sm:space-y-12 sm:px-6 sm:py-12 lg:px-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                        class="h-10 w-10 rounded-xl text-slate-400 transition-all hover:bg-slate-50 hover:text-blue-600"
                    >
                        <Link href="/departments">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-xl font-bold tracking-tighter text-white uppercase shadow-lg shadow-blue-100"
                    >
                        {{
                            dept.code?.substring(0, 2) ||
                            dept.name?.substring(0, 2).toUpperCase()
                        }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1
                                class="max-w-[400px] truncate text-2xl font-bold tracking-tight text-slate-900 uppercase"
                            >
                                {{ dept.name }}
                            </h1>
                            <div
                                class="flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold tracking-tight uppercase transition-all"
                                :class="
                                    dept.is_active
                                        ? 'border-emerald-100 bg-emerald-50 text-emerald-600 shadow-sm'
                                        : 'border-slate-100 bg-slate-50 text-slate-400'
                                "
                            >
                                <div
                                    class="h-1 w-1 rounded-full"
                                    :class="
                                        dept.is_active
                                            ? 'animate-pulse bg-emerald-500'
                                            : 'bg-slate-300'
                                    "
                                ></div>
                                {{ dept.is_active ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                        <p
                            class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            {{ dept.code }} •
                            <span class="font-medium normal-case">{{
                                dept.description ||
                                'Academic Excellence Department'
                            }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-slate-200 px-6 text-xs font-bold tracking-wider uppercase shadow-sm transition-all hover:bg-slate-50"
                        @click="toggleStatus"
                    >
                        {{ dept.is_active ? 'Deactivate' : 'Activate' }} Status
                    </Button>
                    <Button
                        class="h-11 rounded-xl border border-slate-200 bg-white px-6 text-xs font-bold tracking-wider text-slate-700 uppercase shadow-sm transition-all hover:bg-slate-50"
                        as-child
                    >
                        <Link :href="`/departments/${dept.id}/edit`">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Details
                        </Link>
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                class="h-11 w-11 rounded-xl border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:bg-blue-50 hover:text-blue-600"
                                variant="ghost"
                                size="icon"
                                ><MoreHorizontal class="h-4 w-4"
                            /></Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            align="end"
                            class="w-56 rounded-xl border-slate-100 p-2 shadow-xl"
                        >
                            <DropdownMenuItem
                                class="rounded-lg font-bold text-rose-600"
                            >
                                <Trash2 class="mr-2 h-4 w-4" /> Delete
                                Department
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <!-- Quick Stats & Analytics -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600">
                            <Users class="h-5 w-5" />
                        </div>
                        <span
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >Total Teachers</span
                        >
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">
                        {{ teachers.length }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-50 p-2 text-amber-600">
                            <BookOpen class="h-5 w-5" />
                        </div>
                        <span
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >Department Subjects</span
                        >
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">
                        {{ subjects.length }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-lg bg-emerald-50 p-2 text-emerald-600"
                        >
                            <Award class="h-5 w-5" />
                        </div>
                        <span
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >Mean Grade Average</span
                        >
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">
                        {{ analytics.mean_grade ?? '—' }}%
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-lg bg-blue-600 p-2 text-white shadow-md shadow-blue-100"
                        >
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <span
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >Student Pass Rate</span
                        >
                    </div>
                    <p class="mt-4 text-3xl font-bold text-blue-600">
                        {{ analytics.pass_rate ?? 0 }}%
                    </p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Content (Left) -->
                <div class="space-y-8 sm:space-y-12 lg:col-span-2">
                    <!-- Performance Analytics -->
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <h3
                            class="mb-8 flex items-center gap-3 text-lg font-bold text-slate-900"
                        >
                            <TrendingUp class="h-5 w-5 text-blue-600" />
                            Academic Performance Analysis
                        </h3>

                        <div class="grid gap-12 md:grid-cols-2">
                            <!-- Grade Distribution -->
                            <div class="space-y-6">
                                <h4
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Grade Distribution
                                </h4>
                                <div class="space-y-6">
                                    <div
                                        v-for="(count, grade) in {
                                            'Exceeding (EE)':
                                                analytics.ee_count,
                                            'Meeting (ME)': analytics.me_count,
                                            'Approaching (AE)':
                                                analytics.ae_count,
                                            'Below (BE)': analytics.be_count,
                                        }"
                                        :key="grade"
                                    >
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <span
                                                class="text-xs font-bold tracking-wide text-slate-700 uppercase"
                                                >{{ grade }}</span
                                            >
                                            <span
                                                class="text-xs font-bold text-slate-900"
                                                >{{ count ?? 0 }}
                                                {{
                                                    count === 1
                                                        ? 'Learner'
                                                        : 'Learners'
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                                        >
                                            <div
                                                class="h-full rounded-full shadow-sm transition-all duration-1000"
                                                :class="{
                                                    'bg-emerald-500':
                                                        grade.includes('EE'),
                                                    'bg-blue-600':
                                                        grade.includes('ME'),
                                                    'bg-amber-400':
                                                        grade.includes('AE'),
                                                    'bg-rose-500':
                                                        grade.includes('BE'),
                                                }"
                                                :style="{
                                                    width:
                                                        analytics.total_grades >
                                                        0
                                                            ? ((count ?? 0) /
                                                                  analytics.total_grades) *
                                                                  100 +
                                                              '%'
                                                            : '0%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pass/Fail breakdown -->
                            <div
                                class="flex flex-col justify-center gap-8 border-slate-100 md:border-l md:pl-12"
                            >
                                <div>
                                    <h4
                                        class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Performance Summary
                                    </h4>
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        Based on
                                        {{ analytics.total_grades }} assessment
                                        records
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div
                                        class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm"
                                    >
                                        <p
                                            class="mb-1 text-xs font-bold tracking-tight text-emerald-700 uppercase"
                                        >
                                            Meeting Expectations
                                        </p>
                                        <p
                                            class="text-3xl font-bold text-emerald-800"
                                        >
                                            {{ analytics.pass_rate }}%
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-rose-100 bg-rose-50 p-5 shadow-sm"
                                    >
                                        <p
                                            class="mb-1 text-xs font-bold tracking-tight text-rose-700 uppercase"
                                        >
                                            Below Expectations
                                        </p>
                                        <p
                                            class="text-3xl font-bold text-rose-800"
                                        >
                                            {{ analytics.fail_rate }}%
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-100 bg-slate-50 p-5"
                                >
                                    <p
                                        class="mb-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Department Status
                                    </p>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                                        ></div>
                                        <span
                                            class="text-xs font-bold tracking-tight text-slate-900 uppercase"
                                            >Active Academic Growth</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects List -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-50 bg-slate-50/30 p-8"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600"
                                >
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">
                                    Academic Subjects
                                </h3>
                            </div>
                            <Button
                                class="h-10 rounded-xl bg-blue-600 px-6 text-xs font-bold tracking-wider text-white uppercase shadow-md shadow-blue-100 hover:bg-blue-700"
                                @click="showAddSubjectModal = true"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Manage Subjects
                            </Button>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-px bg-slate-50 md:grid-cols-2"
                        >
                            <div
                                v-if="subjects.length === 0"
                                class="col-span-full bg-white py-16 text-center font-medium text-slate-400"
                            >
                                No subjects assigned to this department yet.
                            </div>
                            <div
                                v-for="subject in subjects"
                                :key="subject.id"
                                class="group flex items-start justify-between bg-white p-6 transition-all hover:bg-blue-50/30"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-400 shadow-sm transition-all group-hover:border-blue-600 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        {{ subject.code }}
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm leading-tight font-bold text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                        >
                                            {{ subject.name }}
                                        </h4>
                                        <p
                                            class="mt-1 line-clamp-1 text-xs font-medium text-slate-400"
                                        >
                                            {{
                                                subject.description ||
                                                'Academic Subject'
                                            }}
                                        </p>
                                        <div
                                            class="mt-3 flex items-center gap-2"
                                        >
                                            <span
                                                class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-2 py-1 text-xs font-bold tracking-tight text-emerald-700 uppercase shadow-sm"
                                            >
                                                {{
                                                    subject.is_offered
                                                        ? 'Active'
                                                        : 'Paused'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="removeSubject(subject.id)"
                                    class="h-9 w-9 rounded-xl text-slate-300 opacity-100 transition-all group-hover:opacity-100 hover:bg-rose-50 hover:text-rose-600 md:opacity-0"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Grades Table -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-4 border-b border-slate-50 bg-slate-50/30 p-8 md:flex-row md:items-center md:justify-between"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600"
                                >
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">
                                    Learner Performance Records
                                </h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <select
                                    v-model="selectedTermFilter"
                                    class="h-9 rounded-xl border border-slate-200 bg-white px-3 py-1 text-xs font-bold tracking-wider uppercase shadow-sm transition-all outline-none focus:ring-2 focus:ring-blue-600"
                                >
                                    <option value="all">All Terms</option>
                                    <option
                                        v-for="t in [
                                            'Term 1',
                                            'Term 2',
                                            'Term 3',
                                        ]"
                                        :key="t"
                                        :value="t"
                                    >
                                        {{ t }}
                                    </option>
                                </select>
                                <select
                                    v-model="selectedYearFilter"
                                    class="h-9 rounded-xl border border-slate-200 bg-white px-3 py-1 text-xs font-bold tracking-wider uppercase shadow-sm transition-all outline-none focus:ring-2 focus:ring-blue-600"
                                >
                                    <option value="all">All Years</option>
                                    <option
                                        v-for="y in ['2024', '2025', '2026']"
                                        :key="y"
                                        :value="y"
                                    >
                                        {{ y }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr
                                        class="border-b border-slate-100 bg-slate-50"
                                    >
                                        <th
                                            class="px-8 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Learner Name
                                        </th>
                                        <th
                                            class="px-8 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Subject
                                        </th>
                                        <th
                                            class="px-8 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Score %
                                        </th>
                                        <th
                                            class="px-8 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Grade
                                        </th>
                                        <th
                                            class="px-8 py-4 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Academic Period
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr
                                        v-if="filterGrades.length === 0"
                                        class="h-32"
                                    >
                                        <td
                                            colspan="5"
                                            class="text-center font-medium text-slate-400"
                                        >
                                            No performance records found
                                            matching your filters.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="grade in filterGrades.slice(
                                            0,
                                            10,
                                        )"
                                        :key="grade.id"
                                        class="group transition-all hover:bg-blue-50/30"
                                    >
                                        <td class="px-8 py-5">
                                            <div
                                                class="text-xs font-bold text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                            >
                                                {{ grade.student_name }}
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="h-1.5 w-1.5 rounded-full bg-blue-500 shadow-sm shadow-blue-200"
                                                ></div>
                                                <span
                                                    class="text-xs font-bold tracking-tight text-slate-500 uppercase"
                                                    >{{
                                                        grade.subject_name
                                                    }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <div
                                                class="inline-flex h-8 min-w-[40px] items-center justify-center rounded-lg border border-slate-100 bg-slate-50 px-2 text-sm font-bold text-slate-900"
                                            >
                                                {{ grade.percentage }}%
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex justify-center">
                                                <div
                                                    class="flex items-center justify-center rounded-lg border px-3 py-1 text-xs font-bold tracking-tight shadow-sm transition-all"
                                                    :class="{
                                                        'border-emerald-100 bg-emerald-50 text-emerald-700':
                                                            grade.grade ===
                                                            'EE',
                                                        'border-blue-100 bg-blue-50 text-blue-700':
                                                            grade.grade ===
                                                            'ME',
                                                        'border-amber-100 bg-amber-50 text-amber-700':
                                                            grade.grade ===
                                                            'AE',
                                                        'border-rose-100 bg-rose-50 text-rose-700':
                                                            grade.grade ===
                                                            'BE',
                                                    }"
                                                >
                                                    {{ grade.grade }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <div
                                                class="flex flex-col items-end"
                                            >
                                                <span
                                                    class="text-xs leading-none font-bold tracking-tight text-slate-900 uppercase"
                                                    >{{ grade.term }}</span
                                                >
                                                <span
                                                    class="mt-1.5 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                                                    >{{ grade.year }}</span
                                                >
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            v-if="filterGrades.length > 10"
                            class="border-t border-slate-50 bg-slate-50/50 p-4 text-center"
                        >
                            <p
                                class="text-xs font-bold tracking-[0.1em] text-slate-400 uppercase"
                            >
                                Showing latest 10 of
                                {{ filterGrades.length }} performance records
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="space-y-8 sm:space-y-12">
                    <!-- Department Head -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <div
                            class="absolute top-0 right-0 -z-0 h-32 w-32 rounded-bl-[100px] bg-blue-50/50 transition-all group-hover:bg-blue-100/50"
                        ></div>

                        <div class="relative z-10">
                            <h3
                                class="mb-8 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Department Head
                            </h3>
                            <div class="flex flex-col items-center text-center">
                                <div
                                    class="mb-6 flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border-2 border-white bg-slate-50 shadow-xl"
                                >
                                    <div
                                        v-if="dept.head_of_department"
                                        class="flex h-full w-full items-center justify-center bg-blue-600 text-3xl font-bold tracking-tighter text-white uppercase"
                                    >
                                        {{
                                            dept.head_of_department.name.charAt(
                                                0,
                                            )
                                        }}
                                    </div>
                                    <User
                                        v-else
                                        class="h-10 w-10 text-slate-200"
                                    />
                                </div>
                                <h4
                                    class="mb-1 text-xl font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    {{
                                        dept.head_of_department
                                            ? dept.head_of_department.name
                                            : 'Not Assigned'
                                    }}
                                </h4>
                                <p
                                    class="mb-8 text-xs font-bold tracking-tight text-blue-600 uppercase"
                                >
                                    Lead Instructor
                                </p>

                                <div class="w-full space-y-3">
                                    <Button
                                        variant="outline"
                                        class="h-11 w-full rounded-xl border-slate-200 text-xs font-bold tracking-wider uppercase shadow-sm transition-all hover:bg-slate-50"
                                        @click="showAssignHeadModal = true"
                                    >
                                        <UserPlus class="mr-2 h-4 w-4" />
                                        Change Head
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Members -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-50 bg-slate-50/30 p-8"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600"
                                >
                                    <Users class="h-5 w-5" />
                                </div>
                                <h3
                                    class="text-lg leading-none font-bold text-slate-900"
                                >
                                    Staff Members
                                </h3>
                            </div>
                            <span
                                class="rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-600"
                                >{{ teachers.length }}</span
                            >
                        </div>

                        <div
                            class="custom-scrollbar max-h-[600px] divide-y divide-slate-50 overflow-y-auto"
                        >
                            <div
                                v-if="teachers.length === 0"
                                class="p-12 text-center font-medium text-slate-400"
                            >
                                No staff members currently assigned.
                            </div>
                            <div
                                v-for="teacher in teachers"
                                :key="teacher.id"
                                class="group flex items-center justify-between p-6 transition-all hover:bg-blue-50/30"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-400 shadow-sm transition-all group-hover:border-blue-600 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        {{ teacher.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h5
                                            class="text-xs font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                        >
                                            {{ teacher.name }}
                                        </h5>
                                        <div
                                            class="mt-1 flex items-center gap-2"
                                        >
                                            <span
                                                class="text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                                                >{{
                                                    teacher.staff_code || 'T-ID'
                                                }}</span
                                            >
                                            <div
                                                class="h-1 w-1 rounded-full bg-slate-200"
                                            ></div>
                                            <span
                                                class="text-xs leading-none font-bold tracking-tight text-blue-500 uppercase"
                                                >{{
                                                    teacher.subjects_count || 0
                                                }}
                                                Subjects</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg text-slate-300 hover:bg-blue-50 hover:text-blue-600"
                                            ><MoreHorizontal class="h-4 w-4"
                                        /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="rounded-xl border-slate-100 p-2 shadow-xl"
                                    >
                                        <DropdownMenuItem
                                            class="rounded-lg text-xs font-bold tracking-wider uppercase"
                                            @click="removeTeacher(teacher.id)"
                                        >
                                            <X
                                                class="mr-2 h-4 w-4 text-rose-500"
                                            />
                                            Remove from Staff
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <div
                            class="border-t border-slate-50 bg-slate-50/50 p-6"
                        >
                            <Button
                                class="h-11 w-full rounded-xl border-0 bg-slate-900 text-xs font-bold tracking-wider text-white uppercase shadow-lg shadow-slate-200 transition-all hover:bg-slate-800"
                                @click="showAddTeacherModal = true"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Add Staff Member
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Subject Modal -->
            <div
                v-if="showAddSubjectModal"
                class="fixed inset-0 z-50 flex animate-in items-center justify-center bg-slate-900/60 backdrop-blur-sm duration-300 fade-in"
                @click.self="showAddSubjectModal = false"
            >
                <div
                    class="mx-4 w-full max-w-md animate-in rounded-3xl border border-slate-100 bg-white p-8 shadow-lg duration-200 zoom-in-95"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-50 p-2 text-blue-600"
                            >
                                <BookOpen class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-xl font-bold tracking-tight text-slate-900"
                            >
                                Assign Subjects
                            </h3>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="showAddSubjectModal = false"
                            class="h-10 w-10 rounded-xl text-slate-400 transition-all hover:bg-slate-50 hover:text-rose-600"
                        >
                            <X class="h-5 w-5" />
                        </Button>
                    </div>

                    <div class="space-y-6">
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="subjectSearch"
                                placeholder="Search curriculum subjects..."
                                class="h-12 rounded-xl border-slate-200 pl-10 text-sm transition-all focus:ring-blue-600"
                            />
                        </div>

                        <div
                            class="custom-scrollbar max-h-[350px] space-y-2 overflow-y-auto pr-1"
                        >
                            <div
                                v-if="filteredCurriculumSubjects.length === 0"
                                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 py-16 text-center font-medium text-slate-400"
                            >
                                No unassigned subjects found.
                            </div>
                            <div
                                v-for="sub in filteredCurriculumSubjects"
                                :key="sub.id"
                                @click="addSubject(sub.id)"
                                class="group flex cursor-pointer items-center justify-between rounded-2xl border border-transparent bg-white p-4 shadow-sm transition-all hover:border-blue-100 hover:bg-blue-50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-500 shadow-sm transition-all group-hover:border-blue-600 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        {{ sub.code }}
                                    </div>
                                    <div>
                                        <span
                                            class="block text-xs leading-none font-bold text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                            >{{ sub.name }}</span
                                        >
                                        <span
                                            class="mt-1.5 block text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                                            >Core Subject</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/50 text-blue-600 opacity-0 transition-all group-hover:opacity-100"
                                >
                                    <Plus class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign HOD Modal -->
            <div
                v-if="showAssignHeadModal"
                class="fixed inset-0 z-50 flex animate-in items-center justify-center bg-slate-900/60 backdrop-blur-sm duration-300 fade-in"
                @click.self="showAssignHeadModal = false"
            >
                <div
                    class="mx-4 w-full max-w-md animate-in rounded-3xl border border-slate-100 bg-white p-8 shadow-lg duration-200 zoom-in-95"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-50 p-2 text-blue-600"
                            >
                                <UserCheck class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-xl font-bold tracking-tight text-slate-900"
                            >
                                Assign Dept. Head
                            </h3>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="showAssignHeadModal = false"
                            class="h-10 w-10 rounded-xl text-slate-400 transition-all hover:bg-slate-50 hover:text-rose-600"
                        >
                            <X class="h-5 w-5" />
                        </Button>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="custom-scrollbar max-h-[350px] space-y-2 overflow-y-auto pr-1"
                        >
                            <div
                                v-for="t in allTeachers"
                                :key="t.id"
                                @click="assignHead(t.id)"
                                class="group flex cursor-pointer items-center justify-between rounded-2xl border border-transparent bg-white p-4 shadow-sm transition-all hover:border-blue-100 hover:bg-blue-50"
                                :class="{
                                    'border-blue-200 bg-blue-50/50':
                                        dept.head_of_department?.id === t.id,
                                }"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-400 shadow-sm transition-all group-hover:border-blue-600 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        {{ t.name.charAt(0) }}
                                    </div>
                                    <span
                                        class="block text-xs leading-none font-bold text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                        >{{ t.name }}</span
                                    >
                                </div>
                                <div
                                    v-if="dept.head_of_department?.id === t.id"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white"
                                >
                                    <Check class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Teacher Modal -->
            <div
                v-if="showAddTeacherModal"
                class="fixed inset-0 z-50 flex animate-in items-center justify-center bg-slate-900/60 backdrop-blur-sm duration-300 fade-in"
                @click.self="showAddTeacherModal = false"
            >
                <div
                    class="mx-4 w-full max-w-md animate-in rounded-3xl border border-slate-100 bg-white p-8 shadow-lg duration-200 zoom-in-95"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-50 p-2 text-blue-600"
                            >
                                <UserPlus class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-xl font-bold tracking-tight text-slate-900"
                            >
                                Add Staff Member
                            </h3>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="showAddTeacherModal = false"
                            class="h-10 w-10 rounded-xl text-slate-400 transition-all hover:bg-slate-50 hover:text-rose-600"
                        >
                            <X class="h-5 w-5" />
                        </Button>
                    </div>

                    <div class="space-y-6">
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="teacherSearch"
                                placeholder="Search staff members..."
                                class="h-12 rounded-xl border-slate-200 pl-10 text-sm transition-all focus:ring-blue-600"
                            />
                        </div>

                        <div
                            class="custom-scrollbar max-h-[350px] space-y-2 overflow-y-auto pr-1"
                        >
                            <div
                                v-if="filteredTeachers.length === 0"
                                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 py-16 text-center font-medium text-slate-400"
                            >
                                No unassigned staff found.
                            </div>
                            <div
                                v-for="t in filteredTeachers"
                                :key="t.id"
                                @click="addTeacher(t.id)"
                                class="group flex cursor-pointer items-center justify-between rounded-2xl border border-transparent bg-white p-4 shadow-sm transition-all hover:border-blue-100 hover:bg-blue-50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-400 shadow-sm transition-all group-hover:border-blue-600 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        {{ t.name.charAt(0) }}
                                    </div>
                                    <span
                                        class="block text-xs leading-none font-bold text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                        >{{ t.name }}</span
                                    >
                                </div>
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/50 text-blue-600 opacity-0 transition-all group-hover:opacity-100"
                                >
                                    <Plus class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
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
