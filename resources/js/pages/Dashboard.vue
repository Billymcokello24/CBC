<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Users,
    GraduationCap,
    School,
    DollarSign,
    CalendarCheck,
    BookOpen,
    Bell
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import CalendarWidget from '@/components/dashboard/CalendarWidget.vue';
import ChartCard from '@/components/dashboard/ChartCard.vue';
import QuickActions from '@/components/dashboard/QuickActions.vue';
import RecentActivity from '@/components/dashboard/RecentActivity.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import type { BreadcrumbItem } from '@/types';

interface ChartDataset {
    label: string;
    data: number[];
    color?: string;
}

interface ChartData {
    labels: string[];
    datasets: ChartDataset[];
}

interface Stats {
    total_students: number;
    student_growth: number;
    total_teachers: number;
    teacher_growth: number;
    total_classes: number;
    attendance_rate: number;
    fee_collection: number;
    pending_fees: number;
    total_guardians: number;
    total_subjects: number;
}

interface Props {
    stats?: Stats;
    enrollmentTrends?: ChartData;
    classPerformance?: ChartData;
    genderDistribution?: ChartData;
    weeklyAttendance?: ChartData;
    recentActivities?: Array<{
        id: number | string;
        title: string;
        description: string;
        time: string;
        type: 'student' | 'teacher' | 'payment' | 'assessment' | 'attendance' | 'announcement' | 'system';
        user?: { name: string };
    }>;
    upcomingEvents?: Array<{
        id: number;
        title: string;
        date: string;
        time?: string;
        type: 'exam' | 'meeting' | 'event' | 'deadline' | 'holiday';
    }>;
    notificationsCount?: number;
    currentYear?: { name: string } | null;
    currentTerm?: { name: string } | null;
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_students: 1250,
        student_growth: 5.2,
        total_teachers: 85,
        teacher_growth: 2,
        total_classes: 42,
        attendance_rate: 94.5,
        fee_collection: 2850000,
        pending_fees: 450000,
        total_guardians: 890,
        total_subjects: 24,
    }),
    enrollmentTrends: () => ({
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            { label: 'New Students', data: [45, 52, 38, 65, 48, 73], color: 'rgb(59, 130, 246)' },
            { label: 'Withdrawals', data: [5, 8, 3, 7, 4, 6], color: 'rgb(239, 68, 68)' },
        ],
    }),
    classPerformance: () => ({
        labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
        datasets: [{ label: 'Average Score', data: [72, 78, 65, 81, 75, 70], color: 'rgb(16, 185, 129)' }],
    }),
    genderDistribution: () => ({
        labels: ['Boys', 'Girls'],
        datasets: [{ label: 'Students', data: [620, 630] }],
    }),
    weeklyAttendance: () => ({
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        datasets: [
            { label: 'Present %', data: [95, 92, 97, 94, 91], color: 'rgb(16, 185, 129)' },
            { label: 'Absent %', data: [5, 8, 3, 6, 9], color: 'rgb(239, 68, 68)' },
        ],
    }),
    recentActivities: () => [
        { id: 1, title: 'New Student Enrolled', description: 'John Kamau was enrolled in Grade 4A', time: '2 hours ago', type: 'student' as const, user: { name: 'Admin User' } },
        { id: 2, title: 'Fee Payment Received', description: 'KES 25,000 received from Mary Wanjiku', time: '3 hours ago', type: 'payment' as const, user: { name: 'Finance Officer' } },
        { id: 3, title: 'Assessment Published', description: 'Mathematics CAT 1 published for Grade 5', time: '5 hours ago', type: 'assessment' as const, user: { name: 'Mr. Ochieng' } },
        { id: 4, title: 'Attendance Marked', description: 'Morning attendance completed for all classes', time: '6 hours ago', type: 'attendance' as const, user: { name: 'Class Teachers' } },
        { id: 5, title: 'New Announcement', description: 'Parents meeting scheduled for next Friday', time: '1 day ago', type: 'announcement' as const, user: { name: 'Principal' } },
    ],
    upcomingEvents: () => [
        { id: 1, title: 'Term 1 Exams Begin', date: '2026-03-15', type: 'exam' as const },
        { id: 2, title: 'Staff Meeting', date: '2026-03-12', time: '2:00 PM', type: 'meeting' as const },
        { id: 3, title: 'Sports Day', date: '2026-03-20', type: 'event' as const },
        { id: 4, title: 'Report Cards Due', date: '2026-03-25', type: 'deadline' as const },
        { id: 5, title: 'Good Friday', date: '2026-04-03', type: 'holiday' as const },
    ],
    notificationsCount: 12,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Quick actions
const quickActions = [
    { title: 'Add Student', description: 'Register new student', href: '/students/create', icon: '👨‍🎓', color: 'blue' as const },
    { title: 'Mark Attendance', description: 'Daily attendance', href: '/attendance', icon: '✅', color: 'green' as const },
    { title: 'New Assessment', description: 'Create assessment', href: '/assessments/create', icon: '📝', color: 'purple' as const },
    { title: 'Record Payment', description: 'Fee payment', href: '/finance/payments/create', icon: '💰', color: 'amber' as const },
    { title: 'Send Notice', description: 'Communication', href: '/communication/announcements', icon: '📢', color: 'pink' as const },
    { title: 'View Reports', description: 'Analytics', href: '/reports', icon: '📊', color: 'red' as const },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// Computed values for display
const studentGrowthDirection = computed(() => props.stats.student_growth >= 0 ? 'up' : 'down');
const teacherGrowthDirection = computed(() => props.stats.teacher_growth >= 0 ? 'up' : 'down');
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Welcome Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Welcome back! 👋</h1>
                    <p class="text-muted-foreground">Here's what's happening at your school today.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        System Online
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Students"
                    :value="props.stats.total_students.toLocaleString()"
                    description="from last term"
                    :icon="GraduationCap"
                    :trend="{ value: Math.abs(props.stats.student_growth), direction: studentGrowthDirection }"
                    variant="primary"
                />
                <StatCard
                    title="Total Teachers"
                    :value="props.stats.total_teachers.toString()"
                    description="Active staff"
                    :icon="Users"
                    :trend="{ value: Math.abs(props.stats.teacher_growth), direction: teacherGrowthDirection }"
                    variant="info"
                />
                <StatCard
                    title="Attendance Rate"
                    :value="`${props.stats.attendance_rate}%`"
                    description="This week"
                    :icon="CalendarCheck"
                    :trend="{ value: 1.5, direction: 'up' }"
                    variant="success"
                />
                <StatCard
                    title="Fee Collection"
                    :value="formatCurrency(props.stats.fee_collection)"
                    description="This term"
                    :icon="DollarSign"
                    :trend="{ value: 12, direction: 'up' }"
                    variant="warning"
                />
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <ChartCard
                    title="Enrollment Trends"
                    chart-type="area"
                    :data="props.enrollmentTrends"
                    :height="280"
                />
                <ChartCard
                    title="Class Performance"
                    chart-type="bar"
                    :data="props.classPerformance"
                    :height="280"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column - 2 cols -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Attendance Chart -->
                    <ChartCard
                        title="Weekly Attendance"
                        chart-type="bar"
                        :data="props.weeklyAttendance"
                        :height="200"
                    />

                    <!-- Recent Activity -->
                    <RecentActivity :activities="props.recentActivities" />
                </div>

                <!-- Right Column - 1 col -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <QuickActions :actions="quickActions" />

                    <!-- Gender Distribution -->
                    <ChartCard
                        title="Gender Distribution"
                        chart-type="doughnut"
                        :data="props.genderDistribution"
                        :height="200"
                    />

                    <!-- Calendar -->
                    <CalendarWidget :events="props.upcomingEvents" />
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <School class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Classes</p>
                            <p class="text-xl font-semibold">{{ props.stats.total_classes }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                            <DollarSign class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Pending Fees</p>
                            <p class="text-xl font-semibold">{{ formatCurrency(props.stats.pending_fees) }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <BookOpen class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Subjects</p>
                            <p class="text-xl font-semibold">{{ props.stats.total_subjects }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <Bell class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Notifications</p>
                            <p class="text-xl font-semibold">{{ props.notificationsCount }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
