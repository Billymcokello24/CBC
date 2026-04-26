<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import {
    Users,
    BookOpen,
    Calendar,
    ClipboardList,
    Clock,
    GraduationCap,
    TrendingUp,
    CheckCircle,
    AlertCircle,
    ArrowRight,
    LayoutDashboard,
    UserCircle,
    BarChart3,
    BookMarked,
    ShieldCheck,
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Props {
    teacher: any;
    isClassTeacher: boolean;
    isHod: boolean;
    myClasses: any[];
    mySubjects: any[];
    totalStudents: number;
    todaysTimetable: any[];
    recentAssessments: any[];
    attendanceStats: any[];
    syllabusProgress: any[];
    pendingTasks: any[];
    classManagement?: {
        class: any;
        students: any[];
        total_students: number;
        boys_count: number;
        girls_count: number;
        attendance_rate: number;
        recent_assessments: any[];
    };
    departmentData?: {
        department: any;
        teachers: any[];
        subjects: any[];
        total_students: number;
        recent_assessments: any[];
    };
    academicYear: string;
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    {
        title: 'My Classes',
        value: props.myClasses.length.toString(),
        icon: Users,
        color: 'blue',
    },
    {
        title: 'My Subjects',
        value: props.mySubjects.length.toString(),
        icon: BookOpen,
        color: 'emerald',
    },
    {
        title: "Today's Lessons",
        value: props.todaysTimetable.length.toString(),
        icon: Clock,
        color: 'amber',
    },
    {
        title: 'Active Students',
        value: (props.totalStudents || 0).toString(),
        icon: GraduationCap,
        color: 'indigo',
    },
];
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Welcome Back, {{ teacher?.first_name }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        Academic Year: {{ academicYear }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/assessments/analytics"
                        class="flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-200 transition-colors hover:bg-blue-700"
                    >
                        <TrendingUp class="h-4 w-4" />
                        Detailed Analytics
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 p-2 sm:p-4 md:p-6 lg:p-0">
            <!-- Stats -->
            <div
                class="grid grid-cols-2 gap-3 sm:gap-6 md:grid-cols-2 lg:grid-cols-4"
            >
                <StatCard
                    v-for="stat in stats"
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                />
            </div>

            <!-- Role-Specific Sections -->
            <div
                class="grid grid-cols-1 gap-6 lg:grid-cols-2"
                v-if="isClassTeacher || isHod"
            >
                <!-- Class Teacher Quick View -->
                <div
                    v-if="isClassTeacher && classManagement"
                    class="rounded-3xl bg-gradient-to-br from-emerald-600 to-emerald-800 p-5 text-white shadow-xl shadow-emerald-200 sm:p-6"
                >
                    <div
                        class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-xl bg-white/20 p-2 backdrop-blur-md"
                            >
                                <UserCircle class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg leading-tight font-bold">
                                    Class Management
                                </h3>
                                <p class="text-xs text-emerald-100">
                                    {{ classManagement.class?.name }} ({{
                                        classManagement.total_students
                                    }}
                                    Students)
                                </p>
                            </div>
                        </div>
                        <Link
                            href="/classes"
                            class="flex items-center justify-center gap-2 rounded-xl bg-white/10 px-3 py-1.5 text-xs font-bold backdrop-blur-md transition-colors hover:bg-white/20"
                        >
                            Manage Class <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-4">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-sm sm:p-4"
                        >
                            <p
                                class="mb-1 text-xs font-bold text-emerald-200 uppercase sm:text-xs"
                            >
                                Attendance
                            </p>
                            <p
                                class="text-lg font-bold tabular-nums sm:text-xl"
                            >
                                {{ classManagement.attendance_rate }}%
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-sm sm:p-4"
                        >
                            <p
                                class="mb-1 text-xs font-bold text-emerald-200 uppercase sm:text-xs"
                            >
                                Boys
                            </p>
                            <p
                                class="text-lg font-bold tabular-nums sm:text-xl"
                            >
                                {{ classManagement.boys_count }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-sm sm:p-4"
                        >
                            <p
                                class="mb-1 text-xs font-bold text-emerald-200 uppercase sm:text-xs"
                            >
                                Girls
                            </p>
                            <p
                                class="text-lg font-bold tabular-nums sm:text-xl"
                            >
                                {{ classManagement.girls_count }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- HOD Quick View -->
                <div
                    v-if="isHod && departmentData"
                    class="rounded-3xl bg-gradient-to-br from-blue-600 to-blue-800 p-5 text-white shadow-xl shadow-blue-200 sm:p-6"
                >
                    <div
                        class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-xl bg-white/20 p-2 backdrop-blur-md"
                            >
                                <LayoutDashboard class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg leading-tight font-bold">
                                    Department Overview
                                </h3>
                                <p class="text-xs text-blue-100">
                                    {{ departmentData.department?.name }}
                                </p>
                            </div>
                        </div>
                        <Link
                            href="/curriculum"
                            class="flex items-center justify-center gap-2 rounded-xl bg-white/10 px-3 py-1.5 text-xs font-bold backdrop-blur-md transition-colors hover:bg-white/20"
                        >
                            Full Analytics <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-4">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-sm sm:p-4"
                        >
                            <p
                                class="mb-1 text-xs font-bold text-blue-200 uppercase sm:text-xs"
                            >
                                Teachers
                            </p>
                            <p
                                class="text-lg font-bold tabular-nums sm:text-xl"
                            >
                                {{ departmentData.teachers?.length || 0 }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-sm sm:p-4"
                        >
                            <p
                                class="mb-1 text-xs font-bold text-blue-200 uppercase sm:text-xs"
                            >
                                Subjects
                            </p>
                            <p
                                class="text-lg font-bold tabular-nums sm:text-xl"
                            >
                                {{ departmentData.subjects?.length || 0 }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-sm sm:p-4"
                        >
                            <p
                                class="mb-1 text-xs font-bold text-blue-200 uppercase sm:text-xs"
                            >
                                Learners
                            </p>
                            <p
                                class="text-lg font-bold tabular-nums sm:text-xl"
                            >
                                {{ departmentData.total_students }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Timetable -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <Clock class="h-5 w-5 text-blue-500" />
                            Today's Timetable
                        </h3>
                    </div>

                    <div
                        v-if="todaysTimetable.length > 0"
                        class="space-y-3 sm:space-y-4"
                    >
                        <div
                            v-for="slot in todaysTimetable"
                            :key="slot.id"
                            class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3 transition-colors hover:bg-slate-100/50 sm:gap-4 sm:p-4"
                        >
                            <div class="w-16 flex-shrink-0 text-center sm:w-20">
                                <span
                                    class="block text-xs leading-tight font-bold text-blue-600 sm:text-sm"
                                    >{{ slot.start_time }}</span
                                >
                                <span
                                    class="text-xs tracking-tight text-slate-500 sm:text-xs"
                                    >{{ slot.end_time }}</span
                                >
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4
                                    class="truncate text-sm font-bold text-slate-800 sm:text-base"
                                >
                                    {{ slot.subject }}
                                </h4>
                                <p
                                    class="truncate text-xs text-slate-500 sm:text-sm"
                                >
                                    {{ slot.class }} •
                                    {{ slot.room || 'No Room' }}
                                </p>
                            </div>
                            <div class="hidden sm:block">
                                <span
                                    class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold tracking-wider text-blue-600 uppercase"
                                >
                                    {{ slot.status || 'Active' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-10 text-center sm:py-12">
                        <div
                            class="mx-auto mb-4 flex w-12 items-center justify-center rounded-full bg-slate-50 sm:h-16 sm:w-16"
                        >
                            <Clock
                                class="h-6 w-6 text-slate-300 sm:h-8 sm:w-8"
                            />
                        </div>
                        <p class="text-sm text-slate-500">
                            No lessons scheduled for today.
                        </p>
                    </div>
                </div>

                <!-- Syllabus Coverage -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <CheckCircle class="h-5 w-5 text-emerald-500" />
                        Syllabus Coverage
                    </h3>
                    <div
                        v-if="syllabusProgress.length > 0"
                        class="space-y-5 sm:space-y-6"
                    >
                        <div
                            v-for="item in syllabusProgress"
                            :key="item.subject + item.class"
                        >
                            <div class="mb-2 flex items-end justify-between">
                                <div>
                                    <h4
                                        class="text-sm font-bold text-slate-800"
                                    >
                                        {{ item.subject }}
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        {{ item.class }}
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600"
                                    >{{ item.progress }}%</span
                                >
                            </div>
                            <div
                                class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                            >
                                <div
                                    class="h-full bg-emerald-500 transition-all duration-1000"
                                    :style="{ width: item.progress + '%' }"
                                ></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ item.completed }} of {{ item.total }} lessons
                                completed
                            </p>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-sm text-slate-500">
                            No planning data available.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks & Dashboard Analytics -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Pending Tasks -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <AlertCircle class="h-5 w-5 text-amber-500" />
                        Pending Action Items
                    </h3>
                    <div class="space-y-3 sm:space-y-4">
                        <div
                            v-for="task in pendingTasks"
                            :key="task.title"
                            class="group flex items-center justify-between rounded-2xl border border-amber-100 bg-amber-50 p-4 transition-colors hover:bg-amber-100/50"
                        >
                            <div>
                                <h4
                                    class="text-xs font-bold tracking-tight text-amber-900 uppercase sm:text-sm"
                                >
                                    {{ task.title }}
                                </h4>
                                <p
                                    class="text-xl font-bold text-amber-600 tabular-nums sm:text-2xl"
                                >
                                    {{ task.count }}
                                </p>
                            </div>
                            <Link
                                :href="task.link"
                                class="transform rounded-xl bg-white p-2.5 text-amber-600 shadow-sm transition-all group-hover:scale-110 hover:bg-amber-600 hover:text-white"
                            >
                                <ArrowRight class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-1">
                    <!-- My Classes -->
                    <div
                        class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h3
                            class="mb-4 flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <Users class="h-5 w-5 text-emerald-500" />
                            My Classes
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="cls in myClasses"
                                :key="cls.id"
                                class="group flex items-center justify-between rounded-2xl border border-transparent p-2.5 transition-colors hover:border-slate-100 hover:bg-slate-50"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 font-bold text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white"
                                    >
                                        {{ cls.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-800"
                                        >
                                            {{ cls.name }}
                                        </h4>
                                        <p
                                            class="text-xs font-medium text-slate-500 sm:text-xs"
                                        >
                                            {{ cls.learner_count }} Students
                                        </p>
                                    </div>
                                </div>
                                <span
                                    v-if="cls.is_class_teacher"
                                    class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold tracking-tighter text-emerald-600 uppercase"
                                >
                                    CLASS TEACHER
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- My Subjects -->
                    <div
                        class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h3
                            class="mb-4 flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <BookOpen class="h-5 w-5 text-indigo-500" />
                            My Subjects
                        </h3>
                        <div
                            class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-1"
                        >
                            <div
                                v-for="subject in mySubjects"
                                :key="subject.id + '-' + subject.class_name"
                                class="group rounded-2xl border border-indigo-100 bg-indigo-50/50 p-3 transition-colors hover:bg-indigo-50"
                            >
                                <h4
                                    class="text-xs font-bold text-indigo-900 group-hover:text-indigo-700 sm:text-sm"
                                >
                                    {{ subject.name }}
                                </h4>
                                <p
                                    class="mt-1 text-xs leading-none font-bold tracking-tight text-indigo-600/80 uppercase"
                                >
                                    {{ subject.class_name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Trend -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <TrendingUp class="h-5 w-5 text-blue-500" />
                        Attendance Trend
                    </h3>

                    <div
                        v-if="attendanceStats.length > 0"
                        class="flex h-48 items-end justify-between gap-1 px-1 sm:h-64 sm:gap-2"
                    >
                        <div
                            v-for="day in attendanceStats"
                            :key="day.date"
                            class="group flex flex-1 flex-col items-center gap-2"
                        >
                            <div
                                class="relative flex h-32 w-full flex-col justify-end overflow-hidden rounded-lg border border-slate-100/50 bg-slate-50 sm:h-44"
                            >
                                <div
                                    class="absolute w-full rounded-t-sm bg-blue-600 transition-all duration-700 group-hover:bg-blue-400"
                                    :style="{ height: day.rate + '%' }"
                                ></div>
                                <div
                                    class="absolute inset-x-0 bottom-2 flex items-center justify-center text-xs font-bold tracking-tighter"
                                    :class="
                                        day.rate > 40
                                            ? 'text-white'
                                            : 'text-slate-600'
                                    "
                                >
                                    {{ day.rate }}%
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >{{ day.date.substring(0, 3) }}</span
                            >
                        </div>
                    </div>
                    <div v-else class="py-10 text-center sm:py-12">
                        <p class="text-sm text-slate-500">
                            No attendance data available.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
