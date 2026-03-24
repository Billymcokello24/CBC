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
    Bell,
    ChevronRight,
    Zap,
    LayoutDashboard,
    Calendar,
    Target
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
    { title: 'Overview', href: '/dashboard' },
];

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

const studentGrowthDirection = computed(() => props.stats.student_growth >= 0 ? 'up' : 'down');
const teacherGrowthDirection = computed(() => props.stats.teacher_growth >= 0 ? 'up' : 'down');
</script>

<template>
    <Head title="School Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-slate-50/50 p-6 lg:p-10 space-y-8 animate-in fade-in duration-700">
            <!-- Glass Header -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-white p-8 lg:p-10 shadow-sm border border-slate-200/60 backdrop-blur-3xl">
                <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-blue-50/50 blur-[100px]"></div>
                <div class="absolute -left-20 -bottom-20 h-80 w-80 rounded-full bg-violet-50/30 blur-[100px]"></div>
                
                <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                             <div class="flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-blue-600 border border-blue-100/50">
                                  <Calendar class="h-3 w-3" />
                                  {{ currentYear?.name || 'Academic Year 2026' }}
                             </div>
                             <div class="flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-violet-600 border border-violet-100/50">
                                  <Target class="h-3 w-3" />
                                  {{ currentTerm?.name || 'Term 1' }}
                             </div>
                        </div>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 lg:text-5xl">Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600">{{ $page.props.auth.school?.name || 'School' }}</span> 👋</h1>
                        <p class="text-slate-500 font-medium text-lg lg:text-xl">Your school's operational intelligence at a glance.</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col items-end">
                             <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600 border border-emerald-100">
                                  <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                  LIVE SHARD
                             </div>
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Tenant ID: {{ $page.props.auth.user.school_id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Learners"
                    :value="props.stats.total_students.toLocaleString()"
                    description="Verified student identities"
                    :icon="GraduationCap"
                    :trend="{ value: Math.abs(props.stats.student_growth), direction: studentGrowthDirection }"
                    variant="primary"
                    class="hover:translate-y-[-4px] transition-transform"
                />
                <StatCard
                    title="Staff Force"
                    :value="props.stats.total_teachers.toString()"
                    description="Active educators"
                    :icon="Users"
                    :trend="{ value: Math.abs(props.stats.teacher_growth), direction: teacherGrowthDirection }"
                    variant="info"
                    class="hover:translate-y-[-4px] transition-transform"
                />
                <StatCard
                    title="Attendance Pulse"
                    :value="`${props.stats.attendance_rate}%`"
                    description="Current week average"
                    :icon="CalendarCheck"
                    :trend="{ value: 1.5, direction: 'up' }"
                    variant="success"
                    class="hover:translate-y-[-4px] transition-transform"
                />
                <StatCard
                    title="Financial Liquidity"
                    :value="formatCurrency(props.stats.fee_collection)"
                    description="Total term revenue"
                    :icon="DollarSign"
                    :trend="{ value: 12, direction: 'up' }"
                    variant="warning"
                    class="hover:translate-y-[-4px] transition-transform"
                />
            </div>

            <!-- Main Content Container -->
            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Data Visualization Column -->
                <div class="lg:col-span-8 space-y-8">
                    <div class="grid gap-8 md:grid-cols-2">
                        <ChartCard
                            title="Enrollment Trajectory"
                            chart-type="area"
                            :data="props.enrollmentTrends"
                            :height="300"
                            class="rounded-[2rem] border-slate-200/60 shadow-xl shadow-slate-200/20"
                        />
                        <ChartCard
                            title="Academic Excellence"
                            chart-type="bar"
                            :data="props.classPerformance"
                            :height="300"
                            class="rounded-[2rem] border-slate-200/60 shadow-xl shadow-slate-200/20"
                        />
                    </div>

                    <div class="rounded-[2rem] border border-slate-200/60 bg-white shadow-xl shadow-slate-200/20 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                             <div class="flex items-center gap-3">
                                  <div class="h-10 w-10 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                       <Zap class="h-5 w-5 fill-blue-600" />
                                  </div>
                                  <h3 class="text-xl font-black text-slate-900 uppercase tracking-widest text-sm">Real-time Activity Feed</h3>
                             </div>
                             <Link href="#" class="text-xs font-black text-blue-600 uppercase tracking-widest hover:underline">View All</Link>
                        </div>
                        <RecentActivity :activities="props.recentActivities" class="border-none shadow-none" />
                    </div>
                </div>

                <!-- Operations Column -->
                <div class="lg:col-span-4 space-y-8">
                    <QuickActions :actions="quickActions" class="rounded-[2rem] border-slate-200/60 shadow-xl shadow-slate-200/20" />
                    
                    <ChartCard
                        title="Demographic Split"
                        chart-type="doughnut"
                        :data="props.genderDistribution"
                        :height="240"
                        class="rounded-[2rem] border-slate-200/60 shadow-xl shadow-slate-200/20"
                    />

                    <CalendarWidget :events="props.upcomingEvents" class="rounded-[2rem] border-slate-200/60 shadow-xl shadow-slate-200/20" />
                </div>
            </div>

            <!-- Operational Shards Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-900 border border-slate-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <School class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Classes</p>
                            <p class="text-2xl font-black text-slate-900">{{ props.stats.total_classes }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-900 border border-slate-100 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <DollarSign class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">At Risk Revenue</p>
                            <p class="text-2xl font-black text-slate-900">{{ formatCurrency(props.stats.pending_fees) }}</p>
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-900 border border-slate-100 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Curriculum Nodes</p>
                            <p class="text-2xl font-black text-slate-900">{{ props.stats.total_subjects }}</p>
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-900 border border-slate-100 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                            <Bell class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Alerts</p>
                            <p class="text-2xl font-black text-slate-900">{{ props.notificationsCount }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
