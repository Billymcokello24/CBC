<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    School,
    Users,
    GraduationCap,
    UsersRound,
    Activity,
    ExternalLink,
    ChevronRight,
    MapPin,
    ShieldCheck
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { impersonate } from '@/routes/super-admin';
import StatCard from '@/components/dashboard/StatCard.vue';
import ChartCard from '@/components/dashboard/ChartCard.vue';
import type { BreadcrumbItem } from '@/types';

interface Props {
    stats: {
        total_schools: number;
        active_schools: number;
        total_users: number;
        total_students: number;
        total_teachers: number;
        system_performance: {
            cpu_load: number;
            memory_usage: number;
            disk_space: number;
            status: string;
        };
    };
    recent_schools: Array<{
        id: number;
        name: string;
        code: string;
        county: string;
        status: string;
        users_count: number;
        students_count: number;
    }>;
    school_distribution: Array<{
        label: string;
        value: number;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'SaaS Dashboard', href: '/super-admin/dashboard' },
];

const distributionChartData = {
    labels: props.school_distribution.map(d => d.label || 'Unknown'),
    datasets: [{
        label: 'Schools',
        data: props.school_distribution.map(d => d.value),
        color: 'rgb(139, 92, 246)'
    }]
};
</script>

<template>
    <Head title="SaaS Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">System Overview</h1>
                    <p class="text-muted-foreground">Manage your SaaS platform and schools.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/super-admin/schools/create"
                        class="inline-flex items-center justify-center rounded-md bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-violet-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        <School class="mr-2 h-4 w-4" />
                        Add New School
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Schools"
                    :value="stats.total_schools.toString()"
                    description="Registered institutions"
                    :icon="School"
                    variant="primary"
                />
                <StatCard
                    title="Total Users"
                    :value="stats.total_users.toLocaleString()"
                    description="Across all schools"
                    :icon="Users"
                    variant="info"
                />
                <StatCard
                    title="Total Learners"
                    :value="stats.total_students.toLocaleString()"
                    description="Enrolled students"
                    :icon="GraduationCap"
                    variant="success"
                />
                <StatCard
                    title="Active Teachers"
                    :value="stats.total_teachers.toLocaleString()"
                    description="Teaching staff"
                    :icon="UsersRound"
                    variant="warning"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Recent Schools -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl border bg-card text-card-foreground shadow">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="font-semibold leading-none tracking-tight">Recent Schools</h3>
                            <p class="text-sm text-muted-foreground">Latest schools onboarded to the platform.</p>
                        </div>
                        <div class="p-6 pt-0">
                            <div class="space-y-4">
                                <div v-for="school in recent_schools" :key="school.id" class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/30">
                                            <School class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm">{{ school.name }}</h4>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <span class="flex items-center gap-1"><MapPin class="h-3 w-3" /> {{ school.county || 'N/A' }}</span>
                                                <span>•</span>
                                                <span>{{ school.code }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="hidden text-right md:block">
                                            <p class="text-xs font-medium">{{ school.students_count }} Students</p>
                                            <p class="text-[10px] text-muted-foreground">{{ school.users_count }} Users</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Link
                                                :href="impersonate(school).url"
                                                method="post"
                                                as="button"
                                                class="inline-flex h-8 items-center rounded-md border border-input bg-background px-3 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground"
                                            >
                                                <ExternalLink class="mr-2 h-3.3 w-3" />
                                                Impersonate
                                            </Link>
                                            <Link
                                                :href="`/super-admin/schools/${school.id}/edit`"
                                                class="rounded-full p-2 hover:bg-muted"
                                            >
                                                <ChevronRight class="h-4 w-4" />
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <Link href="/super-admin/schools" class="text-sm font-medium text-violet-600 hover:underline">
                                    View all schools →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- System Health -->
                    <div class="rounded-xl border bg-card text-card-foreground shadow">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="font-semibold leading-none tracking-tight">System Health</h3>
                        </div>
                        <div class="p-6 pt-0 space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium">
                                    <span>CPU Load</span>
                                    <span>{{ stats.system_performance.cpu_load }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-secondary">
                                    <div class="h-full rounded-full bg-violet-600" :style="{ width: stats.system_performance.cpu_load + '%' }"></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium">
                                    <span pill>Memory Usage</span>
                                    <span>{{ stats.system_performance.memory_usage }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-secondary">
                                    <div class="h-full rounded-full bg-blue-500" :style="{ width: stats.system_performance.memory_usage + '%' }"></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium">
                                    <span>Disk Space</span>
                                    <span>{{ stats.system_performance.disk_space }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-secondary">
                                    <div class="h-full rounded-full bg-amber-500" :style="{ width: stats.system_performance.disk_space + '%' }"></div>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex items-center gap-2 rounded-lg bg-green-50 p-3 text-xs font-bold text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                <ShieldCheck class="h-4 w-4" />
                                All systems operational
                            </div>
                        </div>
                    </div>

                    <!-- School Distribution -->
                    <ChartCard
                        title="Distribution by Region"
                        chart-type="doughnut"
                        :data="distributionChartData"
                        :height="200"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
