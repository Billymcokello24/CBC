<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import {
    ArrowLeft, User, BookOpen, ClipboardList, Clock, Calendar,
    GraduationCap, CheckCircle2, AlertCircle, ArrowRight, Users,
    Activity, BarChart3, TrendingUp, FileText, Link as LinkIcon
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
        case 'active': return 'bg-emerald-500 text-white';
        case 'suspended': return 'bg-rose-500 text-white';
        default: return 'bg-indigo-500 text-white';
    }
};

const getSubmissionStatusColor = (status: string) => {
    switch (status) {
        case 'graded': return 'bg-emerald-500 text-white';
        case 'submitted':
        case 'resubmitted': return 'bg-blue-500 text-white';
        case 'returned': return 'bg-amber-500 text-white';
        default: return 'bg-slate-400 text-white';
    }
};

const getAttendanceColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'present': return 'bg-emerald-500';
        case 'absent': return 'bg-rose-500';
        case 'late': return 'bg-amber-500';
        default: return 'bg-slate-300';
    }
};

const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
const timetableByDay = computed(() => {
    const grouped: Record<string, typeof props.timetable> = {};
    for (const slot of props.timetable) {
        const day = slot.day;
        if (!grouped[day]) grouped[day] = [];
        grouped[day].push(slot);
    }
    return grouped;
});

const pendingAssignments = computed(() => props.assignments.filter(a => !a.submission && !a.is_overdue));
const submittedAssignments = computed(() => props.assignments.filter(a => a.submission));
const overdueAssignments = computed(() => props.assignments.filter(a => a.is_overdue && !a.submission));
</script>

<template>
    <Head :title="`${child.name} — Profile`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in duration-500 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 rounded-xl hover:bg-blue-50">
                        <Link href="/guardian/children"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div class="flex items-center gap-5">
                        <div class="h-20 w-20 rounded-2xl overflow-hidden shadow-lg bg-muted ring-2 ring-white">
                            <img v-if="child.photo_url" :src="child.photo_url" class="h-full w-full object-cover" />
                            <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white text-3xl font-black">
                                {{ child.name?.[0] || 'S' }}
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ child.name }}</h1>
                                <Badge :class="getStatusColor(child.status)" class="rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider border-0">
                                    {{ child.status }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2 mt-1.5 text-sm text-muted-foreground">
                                <span class="text-blue-600 font-semibold">{{ child.class_name || 'Unassigned' }}</span>
                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                <span>{{ child.grade_name || '' }}</span>
                                <span v-if="child.admission_number" class="h-1 w-1 bg-border rounded-full"></span>
                                <span v-if="child.admission_number">ID: {{ child.admission_number }}</span>
                                <span v-if="child.age" class="h-1 w-1 bg-border rounded-full"></span>
                                <span v-if="child.age">{{ child.age }} years old</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout: Sidebar Tabs + Content -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Tab Nav Sidebar -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="flex flex-col gap-1.5 rounded-2xl border bg-card p-2 shadow-sm">
                        <button
                            v-for="tab in tabs" :key="tab.id"
                            @click="activeTab = tab.id"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 group"
                            :class="activeTab === tab.id
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                        >
                            <component :is="tab.icon" class="h-4.5 w-4.5" />
                            {{ tab.name }}
                            <div v-if="activeTab === tab.id" class="ml-auto">
                                <div class="h-1.5 w-1.5 bg-blue-200 rounded-full animate-pulse"></div>
                            </div>
                        </button>
                    </div>

                    <!-- Class Teacher Card -->
                    <div v-if="child.class_teacher" class="rounded-2xl border bg-card p-5 shadow-sm">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-3">Class Teacher</p>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                                <User class="h-5 w-5" />
                            </div>
                            <p class="text-sm font-bold text-foreground">{{ child.class_teacher.name }}</p>
                        </div>
                        <div v-if="child.assistant_teacher" class="mt-3 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                                <User class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Assistant</p>
                                <p class="text-sm font-bold text-foreground">{{ child.assistant_teacher.name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="rounded-2xl border bg-blue-600 p-6 text-white shadow-lg overflow-hidden relative">
                        <div class="absolute -right-6 -bottom-6 opacity-10"><TrendingUp class="h-32 w-32" /></div>
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 mb-4">Quick Stats</p>
                        <div class="space-y-3 relative z-10">
                            <div class="flex justify-between items-center">
                                <span class="text-xs opacity-80">Subjects</span>
                                <span class="text-sm font-bold">{{ subjects.length }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs opacity-80">Assignments</span>
                                <span class="text-sm font-bold">{{ assignments.length }}</span>
                            </div>
                            <div v-if="attendance_summary" class="flex justify-between items-center">
                                <span class="text-xs opacity-80">Attendance</span>
                                <span class="text-sm font-bold">{{ attendance_summary.percentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div class="lg:col-span-9 space-y-6">
                    <!-- OVERVIEW TAB -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <!-- Summary Cards -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border bg-card p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><BookOpen class="h-5 w-5" /></div>
                                    <p class="text-sm font-bold">Subjects</p>
                                </div>
                                <p class="text-3xl font-bold text-foreground">{{ subjects.length }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Enrolled subjects this term</p>
                            </div>
                            <div class="rounded-2xl border bg-card p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><AlertCircle class="h-5 w-5" /></div>
                                    <p class="text-sm font-bold">Pending</p>
                                </div>
                                <p class="text-3xl font-bold text-foreground">{{ pendingAssignments.length }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Assignments not yet submitted</p>
                            </div>
                            <div class="rounded-2xl border bg-card p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><CheckCircle2 class="h-5 w-5" /></div>
                                    <p class="text-sm font-bold">Submitted</p>
                                </div>
                                <p class="text-3xl font-bold text-foreground">{{ submittedAssignments.length }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Completed assignments</p>
                            </div>
                        </div>

                        <!-- Attendance Preview -->
                        <div v-if="attendance_summary" class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><Calendar class="h-5 w-5" /></div>
                                <h3 class="text-lg font-bold">Attendance Overview</h3>
                            </div>
                            <div class="grid grid-cols-4 gap-4">
                                <div class="rounded-xl bg-muted/30 border p-4 text-center">
                                    <p class="text-2xl font-bold text-foreground">{{ attendance_summary.total_days }}</p>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Total Days</p>
                                </div>
                                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-center">
                                    <p class="text-2xl font-bold text-emerald-600">{{ attendance_summary.present }}</p>
                                    <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest mt-1">Present</p>
                                </div>
                                <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 text-center">
                                    <p class="text-2xl font-bold text-rose-600">{{ attendance_summary.absent }}</p>
                                    <p class="text-[10px] font-bold text-rose-700 uppercase tracking-widest mt-1">Absent</p>
                                </div>
                                <div class="rounded-xl bg-amber-50 border border-amber-100 p-4 text-center">
                                    <p class="text-2xl font-bold text-amber-600">{{ attendance_summary.late }}</p>
                                    <p class="text-[10px] font-bold text-amber-700 uppercase tracking-widest mt-1">Late</p>
                                </div>
                            </div>
                            <div class="mt-4 bg-muted/30 rounded-xl p-3">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-xs font-semibold text-muted-foreground">Attendance Rate</span>
                                    <span class="text-xs font-bold text-foreground">{{ attendance_summary.percentage }}%</span>
                                </div>
                                <div class="w-full bg-muted h-2 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all" :style="{width: `${attendance_summary.percentage}%`}"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Assignments Warning -->
                        <div v-if="overdueAssignments.length > 0" class="rounded-2xl border-2 border-rose-200 bg-rose-50 p-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <AlertCircle class="h-6 w-6 text-rose-600" />
                                <h3 class="text-base font-bold text-rose-800">{{ overdueAssignments.length }} Overdue Assignment{{ overdueAssignments.length > 1 ? 's' : '' }}</h3>
                            </div>
                            <div class="space-y-2">
                                <Link
                                    v-for="a in overdueAssignments.slice(0, 3)" :key="a.id"
                                    :href="`/guardian/assignments/${a.id}?student_id=${child.id}`"
                                    class="flex items-center justify-between bg-white rounded-xl px-4 py-3 border border-rose-100 hover:border-rose-300 transition-colors"
                                >
                                    <div>
                                        <p class="text-sm font-bold text-rose-800">{{ a.title }}</p>
                                        <p class="text-xs text-rose-600">{{ a.subject }} · Due {{ a.due_date }}</p>
                                    </div>
                                    <ArrowRight class="h-4 w-4 text-rose-400" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SUBJECTS & TEACHERS TAB -->
                    <div v-else-if="activeTab === 'subjects'" class="space-y-6">
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><BookOpen class="h-5 w-5" /></div>
                                <div>
                                    <h3 class="text-lg font-bold">Subjects & Teachers</h3>
                                    <p class="text-xs text-muted-foreground">All subjects for {{ child.class_name || 'this class' }} with assigned teachers.</p>
                                </div>
                            </div>

                            <div v-if="subjects.length === 0" class="rounded-xl border border-dashed p-10 text-center">
                                <BookOpen class="h-10 w-10 mx-auto text-muted-foreground/20 mb-3" />
                                <p class="text-sm font-semibold text-muted-foreground">No subjects assigned to this class yet.</p>
                            </div>

                            <div v-else class="grid gap-4 md:grid-cols-2">
                                <div v-for="subject in subjects" :key="subject.id"
                                    class="rounded-2xl border p-5 hover:shadow-md transition-all group">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 text-white flex items-center justify-center font-bold text-sm shadow-md">
                                            {{ subject.code?.substring(0, 2) || 'SB' }}
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-foreground">{{ subject.name }}</h4>
                                            <Badge variant="outline" class="rounded-md text-[9px] font-bold px-1.5 mt-0.5">{{ subject.code }}</Badge>
                                        </div>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div v-for="teacher in subject.teachers" :key="teacher.id"
                                            class="flex items-center gap-3 bg-muted/30 rounded-xl px-3 py-2.5 border border-border/50">
                                            <div class="h-8 w-8 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                                                <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                                <div v-else class="h-full w-full flex items-center justify-center bg-blue-100 text-blue-600 text-xs font-black">
                                                    {{ teacher.name?.[0] || 'T' }}
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-foreground truncate">{{ teacher.name }}</p>
                                                <p v-if="teacher.phone" class="text-[10px] text-muted-foreground">{{ teacher.phone }}</p>
                                            </div>
                                            <Badge v-if="teacher.is_primary" class="bg-blue-100 text-blue-700 border-0 text-[8px] font-bold rounded-md">Primary</Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ASSIGNMENTS TAB -->
                    <div v-else-if="activeTab === 'assignments'" class="space-y-6">
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center"><ClipboardList class="h-5 w-5" /></div>
                                    <div>
                                        <h3 class="text-lg font-bold">Assignments</h3>
                                        <p class="text-xs text-muted-foreground">{{ assignments.length }} assignments found.</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="assignments.length === 0" class="rounded-xl border border-dashed p-10 text-center">
                                <ClipboardList class="h-10 w-10 mx-auto text-muted-foreground/20 mb-3" />
                                <p class="text-sm font-semibold text-muted-foreground">No assignments found for this class.</p>
                            </div>

                            <div v-else class="space-y-3">
                                <Link
                                    v-for="a in assignments" :key="a.id"
                                    :href="`/guardian/assignments/${a.id}?student_id=${child.id}`"
                                    class="flex items-center justify-between rounded-xl border p-4 hover:border-blue-200 hover:shadow-sm transition-all group cursor-pointer"
                                    :class="a.is_overdue && !a.submission ? 'border-rose-200 bg-rose-50/30' : ''"
                                >
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                            :class="a.submission
                                                ? (a.submission.status === 'graded' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600')
                                                : (a.is_overdue ? 'bg-rose-100 text-rose-600' : 'bg-muted text-muted-foreground')">
                                            <CheckCircle2 v-if="a.submission" class="h-5 w-5" />
                                            <AlertCircle v-else-if="a.is_overdue" class="h-5 w-5" />
                                            <ClipboardList v-else class="h-5 w-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-foreground truncate">{{ a.title }}</p>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                                                <span class="font-semibold text-blue-600">{{ a.subject }}</span>
                                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                                <span>{{ a.teacher }}</span>
                                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                                <span>Due {{ a.due_date }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <Badge v-if="a.submission"
                                            :class="getSubmissionStatusColor(a.submission.status)"
                                            class="rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0">
                                            {{ a.submission.status }}
                                        </Badge>
                                        <Badge v-else-if="a.is_overdue" class="bg-rose-500 text-white rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0">
                                            Overdue
                                        </Badge>
                                        <Badge v-else class="bg-muted text-muted-foreground rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0">
                                            Pending
                                        </Badge>
                                        <ArrowRight class="h-4 w-4 text-muted-foreground/30 group-hover:text-blue-500 transition-colors" />
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- ATTENDANCE TAB -->
                    <div v-else-if="activeTab === 'attendance'" class="space-y-6">
                        <div v-if="attendance_summary" class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center"><Calendar class="h-5 w-5" /></div>
                                <h3 class="text-lg font-bold">Attendance Summary</h3>
                            </div>
                            <div class="grid grid-cols-4 gap-4 mb-6">
                                <div class="rounded-xl bg-muted/30 border p-4 text-center">
                                    <p class="text-2xl font-bold">{{ attendance_summary.total_days }}</p>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Total Days</p>
                                </div>
                                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-center">
                                    <p class="text-2xl font-bold text-emerald-600">{{ attendance_summary.present }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-widest mt-1 text-emerald-700">Present</p>
                                </div>
                                <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 text-center">
                                    <p class="text-2xl font-bold text-rose-600">{{ attendance_summary.absent }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-widest mt-1 text-rose-700">Absent</p>
                                </div>
                                <div class="rounded-xl bg-amber-50 border border-amber-100 p-4 text-center">
                                    <p class="text-2xl font-bold text-amber-600">{{ attendance_summary.late }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-widest mt-1 text-amber-700">Late</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <h3 class="text-lg font-bold mb-4">Recent Attendance</h3>
                            <div v-if="recent_attendance.length === 0" class="text-center py-10 text-muted-foreground text-sm">No attendance records found.</div>
                            <div v-else class="space-y-2">
                                <div v-for="att in recent_attendance" :key="att.attendance_date" class="flex items-center justify-between rounded-xl border p-3.5 hover:bg-muted/20 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="h-3 w-3 rounded-full" :class="getAttendanceColor(att.status)"></div>
                                        <span class="text-sm font-semibold text-foreground">{{ att.attendance_date }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span v-if="att.notes" class="text-xs text-muted-foreground">{{ att.notes }}</span>
                                        <Badge class="rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0"
                                            :class="getAttendanceColor(att.status) + ' text-white'">
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
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center"><BarChart3 class="h-5 w-5" /></div>
                                <h3 class="text-lg font-bold">Results & Assessments</h3>
                            </div>
                            <div v-if="results.length === 0" class="text-center py-10">
                                <BarChart3 class="h-10 w-10 mx-auto text-muted-foreground/20 mb-3" />
                                <p class="text-sm font-semibold text-muted-foreground">No assessment results available yet.</p>
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="text-left py-3 px-2 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Assessment</th>
                                            <th class="text-left py-3 px-2 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Subject</th>
                                            <th class="text-left py-3 px-2 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Type</th>
                                            <th class="text-left py-3 px-2 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Date</th>
                                            <th class="text-left py-3 px-2 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="r in results" :key="r.id" class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                            <td class="py-3 px-2 font-bold">{{ r.assessment_title }}</td>
                                            <td class="py-3 px-2 text-blue-600 font-semibold">{{ r.subject_name || '—' }}</td>
                                            <td class="py-3 px-2">
                                                <Badge variant="outline" class="rounded-md text-[9px] font-bold px-1.5">{{ r.assessment_type }}</Badge>
                                            </td>
                                            <td class="py-3 px-2 text-muted-foreground">{{ r.assessment_date }}</td>
                                            <td class="py-3 px-2">
                                                <span class="font-bold">{{ r.marks_obtained ?? '—' }}</span>
                                                <span v-if="r.total_marks" class="text-muted-foreground"> / {{ r.total_marks }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RESOURCES TAB -->
                    <div v-else-if="activeTab === 'resources'" class="space-y-6">
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><BookOpen class="h-5 w-5" /></div>
                                <h3 class="text-lg font-bold">Reference Registry</h3>
                            </div>

                            <div v-if="resources.length === 0" class="text-center py-20 bg-slate-50/50 rounded-2xl border border-dashed">
                                <BookOpen class="h-12 w-12 mx-auto text-slate-200 mb-4" />
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">No resources found for this student's grade</p>
                            </div>

                            <div v-else class="space-y-10">
                                <section v-for="subject in resources" :key="subject.subject_name" class="space-y-4">
                                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] px-2 flex items-center gap-3">
                                        {{ subject.subject_name }}
                                        <div class="h-px bg-slate-100 flex-1"></div>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <a v-for="res in subject.resources" :key="res.id" 
                                            :href="res.download_url || res.url || '#'" target="_blank"
                                            class="flex items-center gap-4 rounded-xl border p-4 hover:border-blue-200 hover:bg-blue-50/50 transition-all group"
                                        >
                                            <div class="h-10 w-10 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-blue-600 group-hover:border-blue-100 shadow-sm transition-all">
                                                <FileText v-if="res.resource_type === 'pdf' || res.resource_type === 'document'" class="h-5 w-5" />
                                                <LinkIcon v-else-if="res.resource_type === 'link'" class="h-5 w-5" />
                                                <Activity v-else class="h-5 w-5" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-slate-900 truncate">{{ res.title }}</p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <Badge v-if="res.folder" class="bg-slate-100 text-slate-500 border-0 text-[8px] font-bold px-1.5 rounded uppercase">{{ res.folder }}</Badge>
                                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ res.resource_type }}</span>
                                                </div>
                                            </div>
                                            <ArrowRight class="h-4 w-4 text-slate-100 group-hover:text-blue-200 transition-colors" />
                                        </a>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    <!-- TIMETABLE TAB -->
                    <div v-else-if="activeTab === 'timetable'" class="space-y-6">
                        <div class="rounded-2xl border bg-card p-7 shadow-sm">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center"><Clock class="h-5 w-5" /></div>
                                <h3 class="text-lg font-bold">Class Timetable</h3>
                            </div>

                            <div v-if="Object.keys(timetableByDay).length === 0" class="text-center py-10">
                                <Clock class="h-10 w-10 mx-auto text-muted-foreground/20 mb-3" />
                                <p class="text-sm font-semibold text-muted-foreground">No timetable available for this class.</p>
                            </div>

                            <div v-else class="space-y-6">
                                <div v-for="(slots, day) in timetableByDay" :key="day" class="animate-in slide-in-from-left duration-300">
                                    <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-3 px-2">{{ day }}</h4>
                                    <div class="space-y-2">
                                        <div v-for="(slot, idx) in slots" :key="idx"
                                            class="flex items-center gap-4 rounded-xl border p-4 transition-all hover:border-blue-100 hover:bg-blue-50/20"
                                            :class="slot.type === 'break' ? 'bg-amber-50/50 border-amber-100' : 'bg-white shadow-sm border-slate-100'">
                                            <div class="text-xs font-black text-slate-400 font-mono w-28 flex-shrink-0">
                                                {{ slot.start_time }} – {{ slot.end_time }}
                                            </div>
                                            <div v-if="slot.type === 'break'" class="text-xs font-black text-amber-700 uppercase tracking-widest bg-amber-100/50 px-2.5 py-1 rounded-lg">Break Time</div>
                                            <div v-else class="flex-1 min-w-0">
                                                <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ slot.subject || 'Independent Study' }}</p>
                                                <p v-if="slot.teacher" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 flex items-center gap-1.5">
                                                    <User class="h-3 w-3" /> {{ slot.teacher }}
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
