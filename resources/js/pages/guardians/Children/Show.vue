<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import {
    ArrowLeft,
    User,
    BookOpen,
    ClipboardList,
    Clock,
    Calendar,
    GraduationCap,
    CheckCircle2,
    AlertCircle,
    ArrowRight,
    Users,
    Activity,
    BarChart3,
    TrendingUp,
    FileText,
    Link as LinkIcon,
} from 'lucide-vue-next';

const props = defineProps<{
    child: {
        id: number;
        name: string;
        first_name: string;
        last_name: string;
        admission_number: string | null;
        gender: string | null;
        date_of_birth: string | null;
        age: number | null;
        status: string;
        photo_url: string | null;
        class_name: string | null;
        grade_name: string | null;
        stream_name: string | null;
        class_teacher: { name: string } | null;
        assistant_teacher: { name: string } | null;
    };
    subjects: Array<{
        id: number;
        name: string;
        code: string;
        teachers: Array<{
            id: number;
            name: string;
            photo_url: string | null;
            phone: string | null;
            email: string | null;
            is_primary: boolean;
        }>;
    }>;
    assignments: Array<{
        id: number;
        title: string;
        description: string | null;
        assignment_type: string;
        subject: string | null;
        teacher: string | null;
        assigned_date: string | null;
        due_date: string | null;
        total_marks: number | null;
        attachments_count: number;
        is_overdue: boolean;
        submission: {
            id: number;
            status: string;
            submitted_at: string | null;
            marks_obtained: number | null;
            final_marks: number | null;
            feedback: string | null;
            grade: string | null;
        } | null;
    }>;
    attendance_summary: {
        total_days: number;
        present: number;
        absent: number;
        late: number;
        percentage: number;
    } | null;
    recent_attendance: Array<{
        attendance_date: string;
        status: string;
        notes: string | null;
    }>;
    results: any[];
    timetable: Array<{
        day: string;
        start_time: string;
        end_time: string;
        type: string;
        subject: string | null;
        teacher: string | null;
    }>;
    resources: Array<{
        subject_name: string;
        resources: Array<{
            id: number;
            title: string;
            description: string | null;
            resource_type: string;
            file_path: string | null;
            url: string | null;
            folder: string | null;
            download_url: string | null;
        }>;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'My Children', href: '/guardian/children' },
    { title: props.child.name, href: `/guardian/children/${props.child.id}` },
];

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', name: 'Overview', icon: Activity },
    { id: 'subjects', name: 'Subjects & Teachers', icon: BookOpen },
    { id: 'assignments', name: 'Assignments', icon: ClipboardList },
    { id: 'attendance', name: 'Attendance', icon: Calendar },
    { id: 'results', name: 'Results', icon: BarChart3 },
    { id: 'resources', name: 'Resources', icon: BookOpen },
    { id: 'timetable', name: 'Timetable', icon: Clock },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white';
        case 'suspended':
            return 'bg-rose-500 text-white';
        default:
            return 'bg-indigo-500 text-white';
    }
};

const getSubmissionStatusColor = (status: string) => {
    switch (status) {
        case 'graded':
            return 'bg-emerald-500 text-white';
        case 'submitted':
        case 'resubmitted':
            return 'bg-blue-500 text-white';
        case 'returned':
            return 'bg-amber-500 text-white';
        default:
            return 'bg-slate-400 text-white';
    }
};

const getAttendanceColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'present':
            return 'bg-emerald-500';
        case 'absent':
            return 'bg-rose-500';
        case 'late':
            return 'bg-amber-500';
        default:
            return 'bg-slate-300';
    }
};

const dayNames = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
];
const timetableByDay = computed(() => {
    const grouped: Record<string, typeof props.timetable> = {};
    for (const slot of props.timetable) {
        const day = slot.day;
        if (!grouped[day]) grouped[day] = [];
        grouped[day].push(slot);
    }
    return grouped;
});

const pendingAssignments = computed(() =>
    props.assignments.filter((a) => !a.submission && !a.is_overdue),
);
const submittedAssignments = computed(() =>
    props.assignments.filter((a) => a.submission),
);
const overdueAssignments = computed(() =>
    props.assignments.filter((a) => a.is_overdue && !a.submission),
);
</script>

<template>
    <Head :title="`${child.name} — Profile`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-8 p-6 pb-20 duration-500 fade-in md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-5">
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                        class="h-10 w-10 rounded-xl hover:bg-blue-50"
                    >
                        <Link href="/guardian/children"
                            ><ArrowLeft class="h-5 w-5"
                        /></Link>
                    </Button>
                    <div class="flex items-center gap-5">
                        <div
                            class="h-20 w-20 overflow-hidden rounded-2xl bg-muted shadow-lg ring-2 ring-white"
                        >
                            <img
                                v-if="child.photo_url"
                                :src="child.photo_url"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-3xl font-bold text-white"
                            >
                                {{ child.name?.[0] || 'S' }}
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1
                                    class="text-2xl font-bold tracking-tight text-foreground"
                                >
                                    {{ child.name }}
                                </h1>
                                <Badge
                                    :class="getStatusColor(child.status)"
                                    class="rounded-lg border-0 px-2.5 py-0.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    {{ child.status }}
                                </Badge>
                            </div>
                            <div
                                class="mt-1.5 flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <span class="font-semibold text-blue-600">{{
                                    child.class_name || 'Unassigned'
                                }}</span>
                                <span
                                    class="h-1 w-1 rounded-full bg-border"
                                ></span>
                                <span>{{ child.grade_name || '' }}</span>
                                <span
                                    v-if="child.admission_number"
                                    class="h-1 w-1 rounded-full bg-border"
                                ></span>
                                <span v-if="child.admission_number"
                                    >ID: {{ child.admission_number }}</span
                                >
                                <span
                                    v-if="child.age"
                                    class="h-1 w-1 rounded-full bg-border"
                                ></span>
                                <span v-if="child.age"
                                    >{{ child.age }} years old</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout: Sidebar Tabs + Content -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Tab Nav Sidebar -->
                <div class="space-y-4 lg:col-span-3">
                    <div
                        class="flex flex-col gap-1.5 rounded-2xl border bg-card p-2 shadow-sm"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200"
                            :class="
                                activeTab === tab.id
                                    ? 'bg-blue-600 text-white shadow-md'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                            "
                        >
                            <component :is="tab.icon" class="h-4.5 w-4.5" />
                            {{ tab.name }}
                            <div v-if="activeTab === tab.id" class="ml-auto">
                                <div
                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-200"
                                ></div>
                            </div>
                        </button>
                    </div>

                    <!-- Class Teacher Card -->
                    <div
                        v-if="child.class_teacher"
                        class="rounded-2xl border bg-card p-5 shadow-sm"
                    >
                        <p
                            class="mb-3 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                        >
                            Class Teacher
                        </p>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600"
                            >
                                <User class="h-5 w-5" />
                            </div>
                            <p class="text-sm font-bold text-foreground">
                                {{ child.class_teacher.name }}
                            </p>
                        </div>
                        <div
                            v-if="child.assistant_teacher"
                            class="mt-3 flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600"
                            >
                                <User class="h-5 w-5" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Assistant
                                </p>
                                <p class="text-sm font-bold text-foreground">
                                    {{ child.assistant_teacher.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div
                        class="relative overflow-hidden rounded-2xl border bg-blue-600 p-6 text-white shadow-lg"
                    >
                        <div class="absolute -right-6 -bottom-6 opacity-10">
                            <TrendingUp class="h-32 w-32" />
                        </div>
                        <p
                            class="mb-4 text-xs font-bold tracking-tight uppercase opacity-80"
                        >
                            Quick Stats
                        </p>
                        <div class="relative z-10 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs opacity-80">Subjects</span>
                                <span class="text-sm font-bold">{{
                                    subjects.length
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs opacity-80"
                                    >Assignments</span
                                >
                                <span class="text-sm font-bold">{{
                                    assignments.length
                                }}</span>
                            </div>
                            <div
                                v-if="attendance_summary"
                                class="flex items-center justify-between"
                            >
                                <span class="text-xs opacity-80"
                                    >Attendance</span
                                >
                                <span class="text-sm font-bold"
                                    >{{ attendance_summary.percentage }}%</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div class="space-y-6 lg:col-span-9">
                    <!-- OVERVIEW TAB -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <!-- Summary Cards -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <div
                                class="rounded-2xl border bg-card p-6 shadow-sm"
                            >
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                    >
                                        <BookOpen class="h-5 w-5" />
                                    </div>
                                    <p class="text-sm font-bold">Subjects</p>
                                </div>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ subjects.length }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Enrolled subjects this term
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border bg-card p-6 shadow-sm"
                            >
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                                    >
                                        <AlertCircle class="h-5 w-5" />
                                    </div>
                                    <p class="text-sm font-bold">Pending</p>
                                </div>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ pendingAssignments.length }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Assignments not yet submitted
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border bg-card p-6 shadow-sm"
                            >
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                    >
                                        <CheckCircle2 class="h-5 w-5" />
                                    </div>
                                    <p class="text-sm font-bold">Submitted</p>
                                </div>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ submittedAssignments.length }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Completed assignments
                                </p>
                            </div>
                        </div>

                        <!-- Attendance Preview -->
                        <div
                            v-if="attendance_summary"
                            class="rounded-2xl border bg-card p-7 shadow-sm"
                        >
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                                >
                                    <Calendar class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold">
                                    Attendance Overview
                                </h3>
                            </div>
                            <div class="grid grid-cols-4 gap-4">
                                <div
                                    class="rounded-xl border bg-muted/30 p-4 text-center"
                                >
                                    <p
                                        class="text-2xl font-bold text-foreground"
                                    >
                                        {{ attendance_summary.total_days }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >
                                        Total Days
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center"
                                >
                                    <p
                                        class="text-2xl font-bold text-emerald-600"
                                    >
                                        {{ attendance_summary.present }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-emerald-700 uppercase"
                                    >
                                        Present
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-rose-100 bg-rose-50 p-4 text-center"
                                >
                                    <p class="text-2xl font-bold text-rose-600">
                                        {{ attendance_summary.absent }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-rose-700 uppercase"
                                    >
                                        Absent
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-amber-100 bg-amber-50 p-4 text-center"
                                >
                                    <p
                                        class="text-2xl font-bold text-amber-600"
                                    >
                                        {{ attendance_summary.late }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-amber-700 uppercase"
                                    >
                                        Late
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 rounded-xl bg-muted/30 p-3">
                                <div
                                    class="mb-1.5 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-semibold text-muted-foreground"
                                        >Attendance Rate</span
                                    >
                                    <span
                                        class="text-xs font-bold text-foreground"
                                        >{{
                                            attendance_summary.percentage
                                        }}%</span
                                    >
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full bg-emerald-500 transition-all"
                                        :style="{
                                            width: `${attendance_summary.percentage}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Assignments Warning -->
                        <div
                            v-if="overdueAssignments.length > 0"
                            class="rounded-2xl border-2 border-rose-200 bg-rose-50 p-6 shadow-sm"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <AlertCircle class="h-6 w-6 text-rose-600" />
                                <h3 class="text-base font-bold text-rose-800">
                                    {{ overdueAssignments.length }} Overdue
                                    Assignment{{
                                        overdueAssignments.length > 1 ? 's' : ''
                                    }}
                                </h3>
                            </div>
                            <div class="space-y-2">
                                <Link
                                    v-for="a in overdueAssignments.slice(0, 3)"
                                    :key="a.id"
                                    :href="`/guardian/assignments/${a.id}?student_id=${child.id}`"
                                    class="flex items-center justify-between rounded-xl border border-rose-100 bg-white px-4 py-3 transition-colors hover:border-rose-300"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-bold text-rose-800"
                                        >
                                            {{ a.title }}
                                        </p>
                                        <p class="text-xs text-rose-600">
                                            {{ a.subject }} · Due
                                            {{ a.due_date }}
                                        </p>
                                    </div>
                                    <ArrowRight class="h-4 w-4 text-rose-400" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SUBJECTS & TEACHERS TAB -->
                    <div v-else-if="activeTab === 'subjects'" class="space-y-6">
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                                >
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">
                                        Subjects & Teachers
                                    </h3>
                                    <p class="text-xs text-muted-foreground">
                                        All subjects for
                                        {{
                                            child.class_name || 'this class'
                                        }}
                                        with assigned teachers.
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="subjects.length === 0"
                                class="rounded-xl border border-dashed p-10 text-center"
                            >
                                <BookOpen
                                    class="mx-auto mb-3 h-10 w-10 text-muted-foreground/20"
                                />
                                <p
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    No subjects assigned to this class yet.
                                </p>
                            </div>

                            <div v-else class="grid gap-4 md:grid-cols-2">
                                <div
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    class="group rounded-2xl border p-5 transition-all hover:shadow-md"
                                >
                                    <div class="mb-4 flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 text-sm font-bold text-white shadow-md"
                                        >
                                            {{
                                                subject.code?.substring(0, 2) ||
                                                'SB'
                                            }}
                                        </div>
                                        <div>
                                            <h4
                                                class="text-sm font-bold text-foreground"
                                            >
                                                {{ subject.name }}
                                            </h4>
                                            <Badge
                                                variant="outline"
                                                class="mt-0.5 rounded-md px-1.5 text-xs font-bold"
                                                >{{ subject.code }}</Badge
                                            >
                                        </div>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div
                                            v-for="teacher in subject.teachers"
                                            :key="teacher.id"
                                            class="flex items-center gap-3 rounded-xl border border-border/50 bg-muted/30 px-3 py-2.5"
                                        >
                                            <div
                                                class="h-8 w-8 flex-shrink-0 overflow-hidden rounded-lg bg-muted"
                                            >
                                                <img
                                                    v-if="teacher.photo_url"
                                                    :src="teacher.photo_url"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-full w-full items-center justify-center bg-blue-100 text-xs font-bold text-blue-600"
                                                >
                                                    {{
                                                        teacher.name?.[0] || 'T'
                                                    }}
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-xs font-bold text-foreground"
                                                >
                                                    {{ teacher.name }}
                                                </p>
                                                <p
                                                    v-if="teacher.phone"
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {{ teacher.phone }}
                                                </p>
                                            </div>
                                            <Badge
                                                v-if="teacher.is_primary"
                                                class="rounded-md border-0 bg-blue-100 text-xs font-bold text-blue-700"
                                                >Primary</Badge
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ASSIGNMENTS TAB -->
                    <div
                        v-else-if="activeTab === 'assignments'"
                        class="space-y-6"
                    >
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="mb-6 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600"
                                    >
                                        <ClipboardList class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">
                                            Assignments
                                        </h3>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ assignments.length }} assignments
                                            found.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="assignments.length === 0"
                                class="rounded-xl border border-dashed p-10 text-center"
                            >
                                <ClipboardList
                                    class="mx-auto mb-3 h-10 w-10 text-muted-foreground/20"
                                />
                                <p
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    No assignments found for this class.
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <Link
                                    v-for="a in assignments"
                                    :key="a.id"
                                    :href="`/guardian/assignments/${a.id}?student_id=${child.id}`"
                                    class="group flex cursor-pointer items-center justify-between rounded-xl border p-4 transition-all hover:border-blue-200 hover:shadow-sm"
                                    :class="
                                        a.is_overdue && !a.submission
                                            ? 'border-rose-200 bg-rose-50/30'
                                            : ''
                                    "
                                >
                                    <div
                                        class="flex min-w-0 items-center gap-4"
                                    >
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                                            :class="
                                                a.submission
                                                    ? a.submission.status ===
                                                      'graded'
                                                        ? 'bg-emerald-100 text-emerald-600'
                                                        : 'bg-blue-100 text-blue-600'
                                                    : a.is_overdue
                                                      ? 'bg-rose-100 text-rose-600'
                                                      : 'bg-muted text-muted-foreground'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="a.submission"
                                                class="h-5 w-5"
                                            />
                                            <AlertCircle
                                                v-else-if="a.is_overdue"
                                                class="h-5 w-5"
                                            />
                                            <ClipboardList
                                                v-else
                                                class="h-5 w-5"
                                            />
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-bold text-foreground"
                                            >
                                                {{ a.title }}
                                            </p>
                                            <div
                                                class="mt-1 flex items-center gap-2 text-xs text-muted-foreground"
                                            >
                                                <span
                                                    class="font-semibold text-blue-600"
                                                    >{{ a.subject }}</span
                                                >
                                                <span
                                                    class="h-1 w-1 rounded-full bg-border"
                                                ></span>
                                                <span>{{ a.teacher }}</span>
                                                <span
                                                    class="h-1 w-1 rounded-full bg-border"
                                                ></span>
                                                <span
                                                    >Due {{ a.due_date }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-shrink-0 items-center gap-3"
                                    >
                                        <Badge
                                            v-if="a.submission"
                                            :class="
                                                getSubmissionStatusColor(
                                                    a.submission.status,
                                                )
                                            "
                                            class="rounded-lg border-0 px-2.5 py-0.5 text-xs font-bold uppercase"
                                        >
                                            {{ a.submission.status }}
                                        </Badge>
                                        <Badge
                                            v-else-if="a.is_overdue"
                                            class="rounded-lg border-0 bg-rose-500 px-2.5 py-0.5 text-xs font-bold text-white uppercase"
                                        >
                                            Overdue
                                        </Badge>
                                        <Badge
                                            v-else
                                            class="rounded-lg border-0 bg-muted px-2.5 py-0.5 text-xs font-bold text-muted-foreground uppercase"
                                        >
                                            Pending
                                        </Badge>
                                        <ArrowRight
                                            class="h-4 w-4 text-muted-foreground/30 transition-colors group-hover:text-blue-500"
                                        />
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- ATTENDANCE TAB -->
                    <div
                        v-else-if="activeTab === 'attendance'"
                        class="space-y-6"
                    >
                        <div
                            v-if="attendance_summary"
                            class="rounded-2xl border bg-card p-7 shadow-sm"
                        >
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600"
                                >
                                    <Calendar class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold">
                                    Attendance Summary
                                </h3>
                            </div>
                            <div class="mb-6 grid grid-cols-4 gap-4">
                                <div
                                    class="rounded-xl border bg-muted/30 p-4 text-center"
                                >
                                    <p class="text-2xl font-bold">
                                        {{ attendance_summary.total_days }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >
                                        Total Days
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center"
                                >
                                    <p
                                        class="text-2xl font-bold text-emerald-600"
                                    >
                                        {{ attendance_summary.present }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-emerald-700 uppercase"
                                    >
                                        Present
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-rose-100 bg-rose-50 p-4 text-center"
                                >
                                    <p class="text-2xl font-bold text-rose-600">
                                        {{ attendance_summary.absent }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-rose-700 uppercase"
                                    >
                                        Absent
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-amber-100 bg-amber-50 p-4 text-center"
                                >
                                    <p
                                        class="text-2xl font-bold text-amber-600"
                                    >
                                        {{ attendance_summary.late }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs font-bold tracking-tight text-amber-700 uppercase"
                                    >
                                        Late
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <h3 class="mb-4 text-lg font-bold">
                                Recent Attendance
                            </h3>
                            <div
                                v-if="recent_attendance.length === 0"
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                No attendance records found.
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="att in recent_attendance"
                                    :key="att.attendance_date"
                                    class="flex items-center justify-between rounded-xl border p-3.5 transition-colors hover:bg-muted/20"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-3 w-3 rounded-full"
                                            :class="
                                                getAttendanceColor(att.status)
                                            "
                                        ></div>
                                        <span
                                            class="text-sm font-semibold text-foreground"
                                            >{{ att.attendance_date }}</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span
                                            v-if="att.notes"
                                            class="text-xs text-muted-foreground"
                                            >{{ att.notes }}</span
                                        >
                                        <Badge
                                            class="rounded-lg border-0 px-2.5 py-0.5 text-xs font-bold uppercase"
                                            :class="
                                                getAttendanceColor(att.status) +
                                                ' text-white'
                                            "
                                        >
                                            {{ att.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESULTS TAB -->
                    <div v-else-if="activeTab === 'results'" class="space-y-6">
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                                >
                                    <BarChart3 class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold">
                                    Results & Assessments
                                </h3>
                            </div>
                            <div
                                v-if="results.length === 0"
                                class="py-10 text-center"
                            >
                                <BarChart3
                                    class="mx-auto mb-3 h-10 w-10 text-muted-foreground/20"
                                />
                                <p
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    No assessment results available yet.
                                </p>
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th
                                                class="px-2 py-3 text-left text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                            >
                                                Assessment
                                            </th>
                                            <th
                                                class="px-2 py-3 text-left text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                            >
                                                Subject
                                            </th>
                                            <th
                                                class="px-2 py-3 text-left text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                            >
                                                Type
                                            </th>
                                            <th
                                                class="px-2 py-3 text-left text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                            >
                                                Date
                                            </th>
                                            <th
                                                class="px-2 py-3 text-left text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                            >
                                                Score
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="r in results"
                                            :key="r.id"
                                            class="border-b transition-colors last:border-0 hover:bg-muted/20"
                                        >
                                            <td class="px-2 py-3 font-bold">
                                                {{ r.assessment_title }}
                                            </td>
                                            <td
                                                class="px-2 py-3 font-semibold text-blue-600"
                                            >
                                                {{ r.subject_name || '—' }}
                                            </td>
                                            <td class="px-2 py-3">
                                                <Badge
                                                    variant="outline"
                                                    class="rounded-md px-1.5 text-xs font-bold"
                                                    >{{
                                                        r.assessment_type
                                                    }}</Badge
                                                >
                                            </td>
                                            <td
                                                class="px-2 py-3 text-muted-foreground"
                                            >
                                                {{ r.assessment_date }}
                                            </td>
                                            <td class="px-2 py-3">
                                                <span class="font-bold">{{
                                                    r.marks_obtained ?? '—'
                                                }}</span>
                                                <span
                                                    v-if="r.total_marks"
                                                    class="text-muted-foreground"
                                                >
                                                    / {{ r.total_marks }}</span
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RESOURCES TAB -->
                    <div
                        v-else-if="activeTab === 'resources'"
                        class="space-y-6"
                    >
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                >
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold">
                                    Reference Registry
                                </h3>
                            </div>

                            <div
                                v-if="resources.length === 0"
                                class="rounded-2xl border border-dashed bg-slate-50/50 py-20 text-center"
                            >
                                <BookOpen
                                    class="mx-auto mb-4 h-12 w-12 text-slate-200"
                                />
                                <p
                                    class="text-sm font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    No resources found for this student's grade
                                </p>
                            </div>

                            <div v-else class="space-y-10">
                                <section
                                    v-for="subject in resources"
                                    :key="subject.subject_name"
                                    class="space-y-4"
                                >
                                    <h4
                                        class="flex items-center gap-3 px-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        {{ subject.subject_name }}
                                        <div
                                            class="h-px flex-1 bg-slate-100"
                                        ></div>
                                    </h4>
                                    <div
                                        class="grid grid-cols-1 gap-4 md:grid-cols-2"
                                    >
                                        <a
                                            v-for="res in subject.resources"
                                            :key="res.id"
                                            :href="
                                                res.download_url ||
                                                res.url ||
                                                '#'
                                            "
                                            target="_blank"
                                            class="group flex items-center gap-4 rounded-xl border p-4 transition-all hover:border-blue-200 hover:bg-blue-50/50"
                                        >
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-100 bg-white text-slate-400 shadow-sm transition-all group-hover:border-blue-100 group-hover:text-blue-600"
                                            >
                                                <FileText
                                                    v-if="
                                                        res.resource_type ===
                                                            'pdf' ||
                                                        res.resource_type ===
                                                            'document'
                                                    "
                                                    class="h-5 w-5"
                                                />
                                                <LinkIcon
                                                    v-else-if="
                                                        res.resource_type ===
                                                        'link'
                                                    "
                                                    class="h-5 w-5"
                                                />
                                                <Activity
                                                    v-else
                                                    class="h-5 w-5"
                                                />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-sm font-bold text-slate-900"
                                                >
                                                    {{ res.title }}
                                                </p>
                                                <div
                                                    class="mt-0.5 flex items-center gap-2"
                                                >
                                                    <Badge
                                                        v-if="res.folder"
                                                        class="rounded border-0 bg-slate-100 px-1.5 text-xs font-bold text-slate-500 uppercase"
                                                        >{{ res.folder }}</Badge
                                                    >
                                                    <span
                                                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                                        >{{
                                                            res.resource_type
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                            <ArrowRight
                                                class="h-4 w-4 text-slate-100 transition-colors group-hover:text-blue-200"
                                            />
                                        </a>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    <!-- TIMETABLE TAB -->
                    <div
                        v-else-if="activeTab === 'timetable'"
                        class="space-y-6"
                    >
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600"
                                >
                                    <Clock class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-bold">
                                    Class Timetable
                                </h3>
                            </div>

                            <div
                                v-if="Object.keys(timetableByDay).length === 0"
                                class="py-10 text-center"
                            >
                                <Clock
                                    class="mx-auto mb-3 h-10 w-10 text-muted-foreground/20"
                                />
                                <p
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    No timetable available for this class.
                                </p>
                            </div>

                            <div v-else class="space-y-6">
                                <div
                                    v-for="(slots, day) in timetableByDay"
                                    :key="day"
                                    class="animate-in duration-300 slide-in-from-left"
                                >
                                    <h4
                                        class="mb-3 px-2 text-sm font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        {{ day }}
                                    </h4>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(slot, idx) in slots"
                                            :key="idx"
                                            class="flex items-center gap-4 rounded-xl border p-4 transition-all hover:border-blue-100 hover:bg-blue-50/20"
                                            :class="
                                                slot.type === 'break'
                                                    ? 'border-amber-100 bg-amber-50/50'
                                                    : 'border-slate-100 bg-white shadow-sm'
                                            "
                                        >
                                            <div
                                                class="w-28 flex-shrink-0 font-mono text-xs font-bold text-slate-400"
                                            >
                                                {{ slot.start_time }} –
                                                {{ slot.end_time }}
                                            </div>
                                            <div
                                                v-if="slot.type === 'break'"
                                                class="rounded-lg bg-amber-100/50 px-2.5 py-1 text-xs font-bold tracking-tight text-amber-700 uppercase"
                                            >
                                                Break Time
                                            </div>
                                            <div v-else class="min-w-0 flex-1">
                                                <p
                                                    class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                                                >
                                                    {{
                                                        slot.subject ||
                                                        'Independent Study'
                                                    }}
                                                </p>
                                                <p
                                                    v-if="slot.teacher"
                                                    class="mt-0.5 flex items-center gap-1.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                                >
                                                    <User class="h-3 w-3" />
                                                    {{ slot.teacher }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
