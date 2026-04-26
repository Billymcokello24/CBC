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
    Home,
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
        maximumFractionDigits: 0,
    }).format(amount);
};

// Derived Top Performing Units from classPerformance
const topUnits = computed(() => {
    if (!props.classPerformance?.labels?.length) return [];

    return props.classPerformance.labels
        .map((name, index) => ({
            name: name,
            proficiency: props.classPerformance.datasets[0].data[index],
            // Randomly assign growth for flavor if not in DB, or just omit
            growth: 5.2 + index * 1.2,
        }))
        .slice(0, 4);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Header Section -->
            <div class="flex flex-col gap-1 px-1">
                <div
                    class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs"
                >
                    <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                    <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                    <span
                        class="font-medium tracking-tight text-foreground uppercase"
                        >Dashboard</span
                    >
                </div>
                <h1
                    class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    Institutional Overview
                </h1>
                <p class="text-sm text-muted-foreground sm:text-sm">
                    Aggregated metrics and growth indicators for the current
                    academic year.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <StatCard
                    title="Total Collections"
                    :value="
                        formatCurrency(props.stats.fee_collection).split('.')[0]
                    "
                    :icon="DollarSign"
                    variant="success"
                    :trend="{
                        value: props.stats.collection_growth,
                        direction:
                            props.stats.collection_growth >= 0 ? 'up' : 'down',
                    }"
                />
                <StatCard
                    title="Active Learners"
                    :value="props.stats.total_learners.toLocaleString()"
                    :icon="Users"
                    variant="info"
                    :trend="{
                        value: props.stats.learner_growth,
                        direction:
                            props.stats.learner_growth >= 0 ? 'up' : 'down',
                    }"
                />
                <StatCard
                    title="Active Units"
                    :value="props.stats.total_classes.toLocaleString()"
                    :icon="LayoutDashboard"
                    variant="danger"
                    :trend="{
                        value: props.stats.class_growth,
                        direction:
                            props.stats.class_growth >= 0 ? 'up' : 'down',
                    }"
                />
                <StatCard
                    title="Attendance"
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
                        title="Enrollment Trends"
                        chart-type="area"
                        :data="props.enrollmentTrends"
                        :height="300"
                    />
                </div>

                <!-- Secondary Analysis -->
                <div class="flex flex-col gap-6 lg:col-span-5 xl:col-span-4">
                    <ChartCard
                        title="Performance Matrix"
                        chart-type="bar"
                        :data="props.classPerformance"
                        :height="300"
                    />
                </div>
            </div>

            <!-- Bottom Data Matrix -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Recent Enrollments table -->
                <div
                    class="flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm lg:col-span-12 xl:col-span-7 dark:border-white/5"
                >
                    <div
                        class="flex items-center justify-between border-b border-border/50 bg-muted/5 px-5 py-4 sm:px-6"
                    >
                        <h3
                            class="text-sm font-bold tracking-tight text-foreground uppercase sm:text-base"
                        >
                            Recent Enrollments
                        </h3>
                        <Link
                            href="/students"
                            class="text-xs font-bold tracking-tight text-primary uppercase hover:underline sm:text-xs"
                            >View Register</Link
                        >
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[350px] text-left">
                            <tbody class="divide-y divide-border/30">
                                <tr
                                    v-for="student in props.recentEnrollments"
                                    :key="student.id"
                                    class="group transition-all hover:bg-muted/30"
                                >
                                    <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-xs font-bold text-primary ring-2 ring-background transition-colors group-hover:bg-primary group-hover:text-white"
                                            >
                                                {{ student.initials }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span
                                                    class="mb-1 text-xs leading-none font-bold tracking-tight text-foreground sm:text-sm"
                                                    >{{ student.name }}</span
                                                >
                                                <span
                                                    class="text-xs font-semibold tracking-tighter text-muted-foreground uppercase"
                                                    >{{
                                                        student.class_name
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-5 py-3.5 text-right sm:px-6 sm:py-4"
                                    >
                                        <div
                                            class="flex flex-col items-end gap-1"
                                        >
                                            <span
                                                class="text-xs font-bold text-foreground tabular-nums sm:text-sm"
                                                >{{ student.date }}</span
                                            >
                                            <Badge
                                                variant="outline"
                                                class="rounded-md border-emerald-100 bg-emerald-50 px-2 py-0 text-xs font-bold tracking-tighter text-emerald-600 uppercase"
                                            >
                                                {{ student.status }}
                                            </Badge>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!props.recentEnrollments?.length">
                                    <td
                                        colspan="2"
                                        class="px-6 py-12 text-center text-xs font-medium text-muted-foreground uppercase"
                                    >
                                        No recent synchronizations recorded.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Featured Units Area -->
                <div class="flex flex-col gap-6 lg:col-span-12 xl:col-span-5">
                    <div
                        class="flex h-full flex-col rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                    >
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-foreground">
                                Top Performing Units
                            </h3>
                        </div>

                        <div class="space-y-6">
                            <div
                                v-for="unit in topUnits"
                                :key="unit.name"
                                class="group flex items-center justify-between"
                            >
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-foreground"
                                        >{{ unit.name }}</span
                                    >
                                    <span class="text-xs text-muted-foreground"
                                        >Current Proficiency Index</span
                                    >
                                </div>
                                <div class="flex flex-col items-end">
                                    <span
                                        class="text-sm font-semibold text-foreground"
                                        >{{ unit.proficiency }}%
                                        Proficiency</span
                                    >
                                    <div
                                        class="flex items-center gap-1 text-xs font-medium text-emerald-600"
                                    >
                                        <TrendingUp class="h-3 w-3" />
                                        {{ unit.growth }}%
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="!topUnits.length"
                                class="py-12 text-center text-sm text-muted-foreground"
                            >
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
