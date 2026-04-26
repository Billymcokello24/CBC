<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Download,
    FileText,
    TrendingUp,
    Users,
    GraduationCap,
    DollarSign,
    CalendarCheck,
    PieChart,
    LineChart,
    Activity,
    Home,
    ChevronRight,
    ArrowUpRight,
    Search,
    Filter,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Intelligence', href: '/reports' },
];

const reportCategories = [
    {
        title: 'Academic Intelligence',
        icon: GraduationCap,
        color: 'blue',
        stats: '4 Matrices',
        reports: [
            {
                name: 'Performance Variance',
                description: 'Individual and class performance analysis',
            },
            {
                name: 'Area Breakdown',
                description: 'Subject-wise performance distribution',
            },
            {
                name: 'Digital Report Cards',
                description: 'Generate student performance journals',
            },
            {
                name: 'Competency Mapping',
                description: 'CBC achievement synchronization',
            },
        ],
    },
    {
        title: 'Attendance Flux',
        icon: CalendarCheck,
        color: 'green',
        stats: '12 Logs',
        reports: [
            {
                name: 'Daily Register',
                description: 'Daily attendance synchronization',
            },
            {
                name: 'Monthly Trends',
                description: 'Longitudinal attendance vectors',
            },
            {
                name: 'Anomaly Detection',
                description: 'High absenteeism detection',
            },
            {
                name: 'Staff Registry',
                description: 'Faculty attendance oversight',
            },
        ],
    },
    {
        title: 'Financial Liquidity',
        icon: DollarSign,
        color: 'amber',
        stats: '8 Ledgers',
        reports: [
            { name: 'Collection Pulse', description: 'Fee collection summary' },
            { name: 'Residual Balances', description: 'Outstanding fee ledgers' },
            {
                name: 'Transaction History',
                description: 'Audit-ready payment records',
            },
            {
                name: 'Outflow Analysis',
                description: 'School institutional expenses',
            },
        ],
    },
    {
        title: 'Administrative Density',
        icon: Users,
        color: 'purple',
        stats: '6 Nodes',
        reports: [
            {
                name: 'Enrollment Growth',
                description: 'Institutional expansion stats',
            },
            { name: 'Faculty Directory', description: 'Complete staff mapping' },
            { name: 'Node Statistics', description: 'Class-wise metric analysis' },
            {
                name: 'Demographic Scoping',
                description: 'Student population distribution',
            },
        ],
    },
];

const getColorClasses = (color: string) => {
    const colors: Record<string, { bg: string; text: string; iconBg: string; border: string }> =
        {
            blue: {
                bg: 'bg-blue-500/5 hover:bg-blue-500/10',
                text: 'text-blue-600',
                iconBg: 'bg-blue-500/10 text-blue-600',
                border: 'border-blue-500/20',
            },
            green: {
                bg: 'bg-green-500/5 hover:bg-green-500/10',
                text: 'text-green-600',
                iconBg: 'bg-green-500/10 text-green-600',
                border: 'border-green-500/20',
            },
            amber: {
                bg: 'bg-amber-500/5 hover:bg-amber-500/10',
                text: 'text-amber-600',
                iconBg: 'bg-amber-500/10 text-amber-600',
                border: 'border-amber-500/20',
            },
            purple: {
                bg: 'bg-purple-500/5 hover:bg-purple-500/10',
                text: 'text-purple-600',
                iconBg: 'bg-purple-500/10 text-purple-600',
                border: 'border-purple-500/20',
            },
        };
    return colors[color] || colors.blue;
};
</script>

<template>
    <Head title="Intelligence & Analytics" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Home class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Analysis</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Intelligence Hub</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Intelligence & Analytics
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Synthesize institutional data into actionable performance insights.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative hidden sm:block">
                        <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/50" />
                        <Input placeholder="Search matrices..." class="h-10 w-64 rounded-xl border-border bg-muted/20 pl-9 text-xs font-bold transition-all focus:ring-2 focus:ring-primary/10" />
                    </div>
                    <Button variant="outline" class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase shadow-sm">
                        <Filter class="mr-2 h-4 w-4 opacity-40" /> Refine
                    </Button>
                </div>
            </div>

            <!-- Dashboard Pulse Nodes -->
            <div class="grid grid-cols-2 gap-4 px-1 sm:gap-6 lg:grid-cols-4">
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-blue-500/5 blur-3xl group-hover:bg-blue-500/10 transition-all"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white">
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Academic Yield</p>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">72.5%</h3>
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-emerald-500/5 blur-3xl group-hover:bg-emerald-500/10 transition-all"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white">
                            <CalendarCheck class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Attendance Ratio</p>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">94.5%</h3>
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-amber-500/20">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-amber-500/5 blur-3xl group-hover:bg-amber-500/10 transition-all"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 shadow-sm transition-all group-hover:bg-amber-600 group-hover:text-white">
                            <DollarSign class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Collection Index</p>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">86%</h3>
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-purple-500/20">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-purple-500/5 blur-3xl group-hover:bg-purple-500/10 transition-all"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 shadow-sm transition-all group-hover:bg-purple-600 group-hover:text-white">
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Population Density</p>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">1,250</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intelligence Map -->
            <div class="grid gap-6 px-1 lg:grid-cols-2">
                <div
                    v-for="category in reportCategories"
                    :key="category.title"
                    class="flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-sm transition-all hover:shadow-lg"
                >
                    <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-xl shadow-inner transition-all',
                                    getColorClasses(category.color).iconBg,
                                ]"
                            >
                                <component :is="category.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">
                                    {{ category.title }}
                                </h2>
                                <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">{{ category.stats }} Synchronized</p>
                            </div>
                        </div>
                        <Badge variant="outline" class="h-6 rounded-lg text-[9px] font-bold tracking-tighter opacity-40">SYSTEM READY</Badge>
                    </div>

                    <div class="grid gap-4 p-8 sm:grid-cols-2">
                        <div
                            v-for="report in category.reports"
                            :key="report.name"
                            class="group/report relative flex cursor-pointer flex-col justify-between overflow-hidden rounded-2xl border border-border/50 bg-muted/10 p-5 transition-all hover:border-primary/20 hover:bg-background hover:shadow-md"
                        >
                            <div class="relative z-10 flex flex-col gap-2">
                                <h3 class="text-xs font-bold text-foreground transition-colors group-hover/report:text-primary uppercase">
                                    {{ report.name }}
                                </h3>
                                <p class="text-[10px] font-bold leading-relaxed text-muted-foreground/50 uppercase">
                                    {{ report.description }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center justify-between opacity-0 transition-opacity group-hover/report:opacity-100">
                                <span class="text-[9px] font-bold tracking-widest text-primary uppercase">Execute Matrix</span>
                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg bg-primary/10 text-primary">
                                    <ArrowUpRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Sync Log -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm px-1">
                <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <FileText class="h-5 w-5" />
                        </div>
                        <h3 class="text-sm font-bold tracking-tight text-foreground uppercase">Recent Intelligence Bursts</h3>
                    </div>
                    <Button variant="ghost" size="sm" class="h-9 rounded-xl text-[10px] font-bold tracking-tight uppercase hover:bg-muted">
                        Archive Feed
                    </Button>
                </div>

                <div class="divide-y divide-border/30">
                    <div class="group flex items-center justify-between p-6 transition-all hover:bg-muted/10">
                        <div class="flex items-center gap-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/10 bg-blue-500/5 text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white">
                                <BarChart3 class="h-6 w-6" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-foreground group-hover:text-blue-600 transition-colors uppercase">
                                    Term 1 Performance Distribution <span class="mx-2 opacity-10">/</span> <span class="text-[10px] text-muted-foreground/40">Grade 5 Map</span>
                                </h4>
                                <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-0.5">
                                    Generated 12 Hours Ago <span class="mx-1 opacity-30">•</span> System UID: R-88219
                                </p>
                            </div>
                        </div>
                        <Button variant="outline" class="h-9 rounded-xl border-border bg-background px-4 text-[10px] font-bold tracking-tight uppercase shadow-sm hover:bg-primary hover:text-white transition-all">
                            <Download class="mr-2 h-3.5 w-3.5" /> Synchronize PDF
                        </Button>
                    </div>

                    <div class="group flex items-center justify-between p-6 transition-all hover:bg-muted/10">
                        <div class="flex items-center gap-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border-emerald-500/10 bg-emerald-500/5 text-emerald-600 transition-all group-hover:bg-emerald-600 group-hover:text-white">
                                <Activity class="h-6 w-6" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-foreground group-hover:text-emerald-600 transition-colors uppercase">
                                    Institutional Attendance Flux <span class="mx-2 opacity-10">/</span> <span class="text-[10px] text-muted-foreground/40">Monthly Drift</span>
                                </h4>
                                <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-0.5">
                                    Generated 2 Days Ago <span class="mx-1 opacity-30">•</span> System UID: R-88102
                                </p>
                            </div>
                        </div>
                        <Button variant="outline" class="h-9 rounded-xl border-border bg-background px-4 text-[10px] font-bold tracking-tight uppercase shadow-sm hover:bg-primary hover:text-white transition-all">
                            <Download class="mr-2 h-3.5 w-3.5" /> Synchronize PDF
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
