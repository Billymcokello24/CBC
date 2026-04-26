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
    ShieldCheck,
    History,
    Zap,
    Cpu,
    HardDrive,
    Cloud,
    DollarSign,
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
        total_learners: number;
        total_teachers: number;
        system_performance: {
            cpu_load: number;
            memory_usage: number;
            disk_space: number;
            status: string;
        };
        recent_activities_count: number;
    };
    recent_schools: Array<{
        id: number;
        name: string;
        code: string;
        county: string;
        status: string;
        users_count: number;
        learners_count: number;
    }>;
    school_distribution: Array<{
        label: string;
        value: number;
    }>;
    impersonation_logs: Array<{
        id: number;
        admin_name: string;
        school_name: string;
        started_at: string;
        ended_at?: string;
        ip_address: string;
    }>;
    monitoring_urls: {
        telescope: string;
        horizon: string;
    };
    recent_activities: Array<{
        id: number;
        description: string;
        created_at: string;
        causer?: { name: string };
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Operations', href: '/super-admin/dashboard' },
];

const distributionChartData = {
    labels: props.school_distribution.map((d) => d.label || 'Unknown'),
    datasets: [
        {
            label: 'Schools',
            data: props.school_distribution.map((d) => d.value),
            color: 'rgb(139, 92, 246)',
        },
    ],
};
</script>

<template>
    <Head title="Super Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="animate-in space-y-10 duration-1000 fade-in slide-in-from-bottom-4"
        >
            <!-- Simple Welcome Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight text-foreground"
                    >
                        Dashboard Overview
                    </h1>
                    <p
                        class="text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Welcome back! Here's what's happening with the platform
                        today.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/super-admin/schools/create"
                        class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <School class="mr-2 h-4 w-4" />
                        New Tenant
                    </Link>
                    <button
                        class="rounded-xl border border-border bg-card p-3 text-muted-foreground shadow-sm transition-colors hover:bg-muted/50 dark:border-white/5"
                    >
                        <Activity class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Revenue"
                    value="$128,430"
                    description="Gross platform revenue"
                    :icon="DollarSign"
                    variant="success"
                    :trend="{ value: 12.5, direction: 'up' }"
                />
                <StatCard
                    title="Active Tenants"
                    :value="stats.active_schools.toString()"
                    description="Schools processing data"
                    :icon="School"
                    variant="primary"
                    :trend="{ value: 4.2, direction: 'up' }"
                />
                <StatCard
                    title="Total Managed"
                    :value="stats.total_users.toLocaleString()"
                    description="Active user identities"
                    :icon="Users"
                    variant="info"
                    :trend="{ value: 8.1, direction: 'up' }"
                />
                <StatCard
                    title="Success Rate"
                    value="99.9%"
                    description="System uptime average"
                    :icon="Zap"
                    variant="warning"
                />
            </div>

            <!-- Main Content Area -->
            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Charts Area -->
                <div class="space-y-8 lg:col-span-8">
                    <ChartCard
                        title="Revenue Overview"
                        chart-type="area"
                        :data="{
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            datasets: [
                                {
                                    label: 'Revenue',
                                    data: [4000, 5500, 4800, 7200, 8100, 9500],
                                },
                            ],
                        }"
                    />

                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- Recent Activity -->
                        <div
                            class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
                        >
                            <div
                                class="flex items-center justify-between border-b border-border/50 p-6"
                            >
                                <h3
                                    class="text-xs font-medium tracking-tight text-foreground uppercase"
                                >
                                    Recent Events
                                </h3>
                                <Link
                                    href="/super-admin/activity-logs"
                                    class="text-xs font-medium tracking-tight text-primary uppercase hover:underline"
                                    >View All</Link
                                >
                            </div>
                            <div class="divide-y divide-border/30">
                                <div
                                    v-for="log in recent_activities.slice(0, 5)"
                                    :key="log.id"
                                    class="group flex items-center gap-4 p-4"
                                >
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted/50"
                                    >
                                        <Activity
                                            class="h-4 w-4 text-muted-foreground/40"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm font-bold text-foreground"
                                        >
                                            {{ log.description }}
                                        </p>
                                        <p
                                            class="text-xs font-bold tracking-tighter text-muted-foreground/40 uppercase"
                                        >
                                            {{
                                                new Date(
                                                    log.created_at,
                                                ).toLocaleTimeString()
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="!recent_activities?.length"
                                    class="p-8 text-center text-xs font-bold tracking-tight text-muted-foreground/20 uppercase"
                                >
                                    Waiting for signals...
                                </div>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div
                            class="space-y-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                        >
                            <h3
                                class="text-xs font-medium tracking-tight text-foreground uppercase"
                            >
                                System Telemetry
                            </h3>

                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                    >
                                        <span>CPU Load</span>
                                        <span class="text-foreground"
                                            >{{
                                                stats.system_performance
                                                    .cpu_load
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                                    >
                                        <div
                                            class="h-full bg-primary transition-all duration-1000"
                                            :style="{
                                                width:
                                                    stats.system_performance
                                                        .cpu_load + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                    >
                                        <span>Memory usage</span>
                                        <span class="text-foreground"
                                            >{{
                                                stats.system_performance
                                                    .memory_usage
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                                    >
                                        <div
                                            class="h-full bg-indigo-500 transition-all duration-1000"
                                            :style="{
                                                width:
                                                    stats.system_performance
                                                        .memory_usage + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-2 gap-4 border-t border-border/50 pt-4"
                            >
                                <a
                                    :href="monitoring_urls.telescope"
                                    target="_blank"
                                    class="group rounded-xl border border-border/50 bg-muted/30 p-3 text-center transition-all hover:border-primary/20 hover:bg-primary/5"
                                >
                                    <Activity
                                        class="mx-auto mb-2 h-4 w-4 text-muted-foreground/40 transition-colors group-hover:text-primary"
                                    />
                                    <span
                                        class="text-xs font-medium tracking-tight text-muted-foreground uppercase group-hover:text-primary"
                                        >Telescope</span
                                    >
                                </a>
                                <a
                                    :href="monitoring_urls.horizon"
                                    target="_blank"
                                    class="group rounded-xl border border-border/50 bg-muted/30 p-3 text-center transition-all hover:border-primary/20 hover:bg-primary/5"
                                >
                                    <Zap
                                        class="mx-auto mb-2 h-4 w-4 text-muted-foreground/40 transition-colors group-hover:text-amber-500"
                                    />
                                    <span
                                        class="text-xs font-medium tracking-tight text-muted-foreground uppercase group-hover:text-amber-500"
                                        >Horizon</span
                                    >
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Area -->
                <div class="space-y-8 lg:col-span-4">
                    <ChartCard
                        title="Distribution"
                        chart-type="doughnut"
                        :data="distributionChartData"
                        :height="240"
                    />

                    <!-- Recent Tenants List -->
                    <div
                        class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
                    >
                        <div class="border-b border-border/50 p-6">
                            <h3
                                class="text-xs font-medium tracking-tight text-foreground uppercase"
                            >
                                Recent Tenants
                            </h3>
                        </div>
                        <div class="divide-y divide-border/30">
                            <div
                                v-for="school in recent_schools.slice(0, 4)"
                                :key="school.id"
                                class="group flex items-center justify-between p-4 transition-colors hover:bg-muted/20"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"
                                    >
                                        <School class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-bold tracking-tight text-foreground uppercase"
                                        >
                                            {{ school.name }}
                                        </p>
                                        <p
                                            class="text-xs font-bold text-muted-foreground/60 uppercase"
                                        >
                                            {{ school.county || 'Global' }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    :class="[
                                        'h-2 w-2 rounded-full',
                                        school.status === 'active'
                                            ? 'bg-emerald-500 shadow-sm'
                                            : 'bg-amber-500',
                                    ]"
                                ></div>
                            </div>
                        </div>
                        <div class="bg-muted/20 p-4">
                            <Link
                                href="/super-admin/schools"
                                class="block w-full rounded-xl border border-border bg-card py-2 text-center text-xs font-medium tracking-tight text-muted-foreground uppercase transition-all hover:border-primary/20 hover:text-primary"
                            >
                                Platform Registry
                            </Link>
                        </div>
                    </div>

                    <!-- Security Audit Pulse -->
                    <div
                        class="group relative overflow-hidden rounded-2xl bg-slate-900 p-6 text-white"
                    >
                        <div
                            class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-primary/20 blur-2xl transition-all duration-700 group-hover:bg-primary/30"
                        ></div>
                        <div class="relative space-y-4">
                            <div class="flex items-center gap-3">
                                <ShieldCheck class="h-5 w-5 text-primary" />
                                <h3
                                    class="text-xs font-medium tracking-tight text-white/50 uppercase"
                                >
                                    Security Guard
                                </h3>
                            </div>
                            <p
                                class="text-xs leading-relaxed font-bold text-white/80"
                            >
                                Cluster security is optimal. No unauthorized
                                access attempts detected in the last cycle.
                            </p>
                            <div
                                class="h-1.5 w-full overflow-hidden rounded-full bg-white/10"
                            >
                                <div
                                    class="h-full w-[98%] bg-primary shadow-[0_0_10px_rgba(59,130,246,0.5)]"
                                ></div>
                            </div>
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
