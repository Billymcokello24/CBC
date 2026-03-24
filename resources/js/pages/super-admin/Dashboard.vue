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
    Cloud
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
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Operations', href: '/super-admin/dashboard' },
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
    <Head title="Super Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-slate-50/50 p-6 lg:p-10 space-y-8 animate-in fade-in duration-700">
            <!-- Glass Header -->
            <div class="relative overflow-hidden rounded-3xl bg-white p-8 shadow-sm border border-slate-200/60 backdrop-blur-xl">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-violet-100/50 blur-3xl"></div>
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-100/30 blur-3xl"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                             <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-black uppercase tracking-widest text-violet-700">Master Control</span>
                             <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                             <span class="text-xs font-bold text-slate-500">{{ stats.system_performance.status }}</span>
                        </div>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 lg:text-5xl">Global <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Observability</span></h1>
                        <p class="text-slate-500 font-medium text-lg">Operational intelligence for your multi-tenant ecosystem.</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <Link
                            href="/super-admin/schools/create"
                            class="group relative inline-flex items-center justify-center overflow-hidden rounded-2xl bg-slate-900 px-6 py-3 font-bold text-white transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-slate-200"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-600 to-indigo-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                            <School class="relative mr-2 h-5 w-5" />
                            <span class="relative">Provision New Tenant</span>
                        </Link>
                        
                        <button class="rounded-2xl border border-slate-200 bg-white p-3 text-slate-600 transition-colors hover:bg-slate-50 hover:border-slate-300 shadow-sm">
                             <Activity class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced KPI Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Enterprise Tenants"
                    :value="stats.total_schools.toString()"
                    :description="`${stats.active_schools} actively processing data`"
                    :icon="School"
                    variant="primary"
                    :trend="{ value: 12, direction: 'up' }"
                    class="hover:translate-y-[-4px] transition-transform"
                />
                <StatCard
                    title="Identities Managed"
                    :value="stats.total_users.toLocaleString()"
                    description="Across all infrastructure"
                    :icon="Users"
                    variant="info"
                    :trend="{ value: 5, direction: 'up' }"
                    class="hover:translate-y-[-4px] transition-transform"
                />
                <StatCard
                    title="Learner Success"
                    :value="stats.total_learners.toLocaleString()"
                    description="Verified enrollments"
                    :icon="GraduationCap"
                    variant="success"
                    class="hover:translate-y-[-4px] transition-transform"
                />
                <StatCard
                    title="Staff Allocation"
                    :value="stats.total_teachers.toLocaleString()"
                    description="Validated educators"
                    :icon="UsersRound"
                    variant="warning"
                    class="hover:translate-y-[-4px] transition-transform"
                />
            </div>

            <!-- Main Observability Grid -->
            <div class="grid gap-8 lg:grid-cols-12">
                <!-- System Infrastructure Pulse -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-8">
                             <div class="flex items-center gap-3">
                                 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-50 border border-slate-100 text-slate-900">
                                     <Zap class="h-5 w-5 fill-slate-900" />
                                 </div>
                                 <h3 class="text-xl font-black tracking-tight text-slate-900 uppercase tracking-wider">System Pulse</h3>
                             </div>
                             <div class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase text-emerald-600">
                                  <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-600"></div>
                                  Operational
                             </div>
                        </div>

                        <div class="space-y-6">
                            <!-- CPU -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                                        <Cpu class="h-3 w-3" />
                                        <span>Computational Load</span>
                                    </div>
                                    <span class="text-xs font-black text-slate-900">{{ stats.system_performance.cpu_load }}%</span>
                                </div>
                                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 transition-all duration-1000" :style="{ width: stats.system_performance.cpu_load + '%' }"></div>
                                </div>
                            </div>
                            
                            <!-- RAM -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                                        <History class="h-3 w-3" />
                                        <span>Memory Allocation</span>
                                    </div>
                                    <span class="text-xs font-black text-slate-900">{{ stats.system_performance.memory_usage }}%</span>
                                </div>
                                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 transition-all duration-1000" :style="{ width: stats.system_performance.memory_usage + '%' }"></div>
                                </div>
                            </div>

                            <!-- DISK -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                                        <HardDrive class="h-3 w-3" />
                                        <span>Cluster Storage</span>
                                    </div>
                                    <span class="text-xs font-black text-slate-900">{{ stats.system_performance.disk_space }}%</span>
                                </div>
                                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-1000" :style="{ width: stats.system_performance.disk_space + '%' }"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-10 pt-6 border-t border-slate-50 grid grid-cols-2 gap-4">
                             <div class="p-4 rounded-3xl bg-slate-50/50 border border-slate-100 text-center">
                                  <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Backup</div>
                                  <div class="text-xs font-black text-slate-900">12m ago</div>
                             </div>
                             <div class="p-4 rounded-3xl bg-slate-50/50 border border-slate-100 text-center">
                                  <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Queue</div>
                                  <div class="text-xs font-black text-slate-900">0 pending</div>
                             </div>
                        </div>
                    </div>

                    <ChartCard
                        title="Regional Tenant Map"
                        chart-type="doughnut"
                        :data="distributionChartData"
                        :height="260"
                        class="rounded-3xl border border-slate-200 shadow-sm"
                    />
                </div>

                <!-- Recent Tenant Activity & Audit Logs -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Recent Schools -->
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-black tracking-tight text-slate-900 uppercase tracking-wider">Tenant Provisioning</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Latest infrastructure shards</p>
                            </div>
                            <Link href="/super-admin/schools" class="flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-2 text-sm font-black text-slate-600 transition-colors hover:bg-slate-100">
                                Full Registry
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </div>
                        <div class="divide-y divide-slate-50">
                            <div v-for="school in recent_schools" :key="school.id" class="group flex items-center justify-between p-6 transition-all hover:bg-slate-50/80">
                                <div class="flex items-center gap-5">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-100 text-violet-600 transition-transform group-hover:scale-105">
                                        <School class="h-7 w-7" />
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-lg font-black text-slate-900 tracking-tight">{{ school.name }}</h4>
                                        <div class="flex items-center gap-3 text-xs font-bold text-slate-400">
                                            <span class="flex items-center gap-1.5"><MapPin class="h-3.5 w-3.5" /> {{ school.county || 'Global' }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            <span class="uppercase tracking-widest">{{ school.code }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            <span :class="['px-2 py-0.5 rounded-full text-[10px] uppercase font-black', school.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700']">
                                                {{ school.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="hidden text-right lg:block space-y-0.5">
                                        <div class="text-xs font-black text-slate-900 tracking-tight">{{ school.learners_count }} Identities</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ school.users_count }} Staff Accounts</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="impersonate(school).url"
                                            method="post"
                                            as="button"
                                            class="inline-flex h-11 items-center rounded-2xl bg-white border border-slate-200 px-5 text-sm font-black text-slate-600 shadow-sm transition-all hover:border-violet-300 hover:text-violet-600 hover:shadow-violet-100"
                                        >
                                            <ExternalLink class="mr-2 h-4 w-4" />
                                            Deep Link
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Impersonation Audit Logs -->
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 border border-rose-100 text-rose-600">
                                     <History class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-black tracking-tight text-slate-900 uppercase tracking-wider">Access Audit Trail</h3>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Impersonation & Switch session logs</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-[10px] font-black text-white uppercase tracking-widest tracking-widest">Authorized Only</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50 border-b border-slate-100">
                                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Operator</th>
                                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Target Tenant</th>
                                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Access Time</th>
                                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="log in impersonation_logs" :key="log.id" class="hover:bg-slate-50/30 transition-colors">
                                        <td class="p-6">
                                            <div class="flex items-center gap-3">
                                                 <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black text-slate-600">
                                                      {{ log.admin_name[0] }}
                                                 </div>
                                                 <span class="text-sm font-bold text-slate-900">{{ log.admin_name }}</span>
                                            </div>
                                        </td>
                                        <td class="p-6 text-sm font-medium text-slate-600">{{ log.school_name }}</td>
                                        <td class="p-6">
                                            <div class="text-sm font-black text-slate-900">{{ new Date(log.started_at).toLocaleTimeString() }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ new Date(log.started_at).toDateString() }}</div>
                                        </td>
                                        <td class="p-6">
                                            <div v-if="!log.ended_at" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase text-blue-600">
                                                 <span class="h-1 w-1 rounded-full bg-blue-600 animate-pulse"></span>
                                                 Active
                                            </div>
                                            <div v-else class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                 Closed
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!impersonation_logs?.length">
                                        <td colspan="4" class="p-12 text-center">
                                            <div class="flex flex-col items-center gap-3 opacity-20">
                                                <History class="h-12 w-12" />
                                                <span class="text-xs font-black uppercase tracking-widest">No access logs found</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
