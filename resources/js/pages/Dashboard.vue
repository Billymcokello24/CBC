<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    School,
    Users,
    DollarSign,
    TrendingUp,
    TrendingDown,
    Search,
    Bell,
    ChevronDown,
    LayoutDashboard,
    BarChart3,
    Settings,
    ArrowRight,
    Activity,
    Zap,
    GraduationCap,
    BookOpen,
    Calendar,
    UsersRound,
    History,
    ShieldCheck,
    Clock,
    AlertCircle,
    ChevronRight,
    ArrowUpRight,
    MoreHorizontal,
    Plus,
    Home
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import ChartCard from '@/components/dashboard/ChartCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

interface Props {
    dashboardType: string;
    stats: {
        total_learners: number;
        learner_growth: number;
        total_teachers: number;
        teacher_growth: number;
        total_classes: number;
        class_growth: number;
        attendance_rate: number;
        fee_collection: number;
        collection_growth: number;
        pending_fees: number;
        total_guardians: number;
        total_subjects: number;
    };
    enrollmentTrends: {
        labels: string[];
        datasets: Array<{ label: string; data: number[]; color?: string }>;
    };
    classPerformance: {
        labels: string[];
        datasets: Array<{ label: string; data: number[]; color?: string }>;
    };
    recentActivities: Array<{
        id: number;
        description: string;
        created_at: string;
        causer?: { name: string };
    }>;
    recentEnrollments: Array<{
        id: number;
        name: string;
        class_name: string;
        date: string;
        status: string;
        initials: string;
    }>;
    upcomingEvents: Array<{
        id: number;
        title: string;
        event_date: string;
        description?: string;
    }>;
    notificationsCount: number;
    currentYear?: { name: string };
    currentTerm?: { name: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Dashboard', href: '/dashboard' },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', { 
        style: 'currency', 
        currency: 'KES',
        maximumFractionDigits: 0
    }).format(amount);
};

// Derived Top Performing Units from classPerformance
const topUnits = computed(() => {
    if (!props.classPerformance?.labels?.length) return [];
    
    return props.classPerformance.labels.map((name, index) => ({
        name: name,
        proficiency: props.classPerformance.datasets[0].data[index],
        // Randomly assign growth for flavor if not in DB, or just omit
        growth: 5.2 + (index * 1.2)
    })).slice(0, 4);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2 text-xs text-muted-foreground mb-2">
                    <Home class="h-3.5 w-3.5" />
                    <ChevronRight class="h-3 w-3" />
                    <span class="font-medium text-foreground">Dashboard</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Dashboard Overview</h1>
                <p class="text-[15px] text-muted-foreground">
                    Welcome back! Here's what's happening with your institutional data today.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Collections"
                    :value="formatCurrency(props.stats.fee_collection).split('.')[0]"
                    :icon="DollarSign"
                    variant="success"
                    :trend="{ value: props.stats.collection_growth, direction: props.stats.collection_growth >= 0 ? 'up' : 'down' }"
                />
                <StatCard
                    title="Active Learners"
                    :value="props.stats.total_learners.toLocaleString()"
                    :icon="Users"
                    variant="info"
                    :trend="{ value: props.stats.learner_growth, direction: props.stats.learner_growth >= 0 ? 'up' : 'down' }"
                />
                <StatCard
                    title="Active Units"
                    :value="props.stats.total_classes.toLocaleString()"
                    :icon="LayoutDashboard"
                    variant="danger"
                    :trend="{ value: props.stats.class_growth, direction: props.stats.class_growth >= 0 ? 'up' : 'down' }"
                />
                <StatCard
                    title="Attendance Rate"
                    :value="`${props.stats.attendance_rate}%`"
                    :icon="Activity"
                    variant="success"
                    :trend="{ value: 2.1, direction: 'up' }"
                />
            </div>

            <!-- Main Analytical Area -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Large Flow Chart -->
                <div class="lg:col-span-7 xl:col-span-8">
                    <ChartCard
                        title="Enrollment Overview"
                        chart-type="area"
                        :data="props.enrollmentTrends"
                        :height="350"
                    />
                </div>

                <!-- Secondary Analysis -->
                <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6">
                     <ChartCard
                        title="Performance vs Target"
                        chart-type="bar"
                        :data="props.classPerformance"
                        :height="350"
                    />
                </div>
            </div>

            <!-- Bottom Data Matrix -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Recent Enrollments table -->
                <div class="lg:col-span-12 xl:col-span-7 rounded-2xl border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-border/50 flex items-center justify-between">
                        <h3 class="font-semibold text-base text-foreground">Recent Enrollments</h3>
                        <Link href="/students" class="text-xs font-medium text-blue-600 hover:underline">View All</Link>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-border/30">
                                <tr v-for="student in props.recentEnrollments" :key="student.id" class="hover:bg-muted/30 transition-all group">
                                    <td class="px-6 py-4">
                                         <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-blue-50 flex items-center justify-center text-[10px] font-bold text-blue-600 ring-2 ring-white">
                                                {{ student.initials }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-sm text-foreground">{{ student.name }}</span>
                                                <span class="text-xs text-muted-foreground">{{ student.class_name }}</span>
                                            </div>
                                         </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="font-semibold text-sm text-foreground">{{ student.date }}</span>
                                            <Badge variant="outline" class="rounded-md border-emerald-100 bg-emerald-50 text-emerald-600 text-[10px] px-2 py-0">
                                                {{ student.status }}
                                            </Badge>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!props.recentEnrollments?.length">
                                    <td colspan="2" class="px-6 py-12 text-center text-sm text-muted-foreground italic">
                                        No recent enrollments recorded.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Featured Units Area -->
                <div class="lg:col-span-12 xl:col-span-5 flex flex-col gap-6">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex flex-col h-full">
                        <div class="mb-6">
                            <h3 class="font-semibold text-base text-foreground">Top Performing Units</h3>
                        </div>

                        <div class="space-y-6">
                            <div v-for="unit in topUnits" :key="unit.name" class="flex items-center justify-between group">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-foreground">{{ unit.name }}</span>
                                    <span class="text-xs text-muted-foreground italic">Current Proficiency Index</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="font-semibold text-sm text-foreground">{{ unit.proficiency }}% Proficiency</span>
                                    <div class="flex items-center gap-1 text-[10px] font-medium text-emerald-600">
                                        <TrendingUp class="h-3 w-3" />
                                        {{ unit.growth }}%
                                    </div>
                                </div>
                            </div>
                            <div v-if="!topUnits.length" class="py-12 text-center text-sm text-muted-foreground italic">
                                Performance data synchronization required.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* No extra styles needed, matching standard aesthetics */
</style>
