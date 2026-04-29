<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    BarChart3, FileText, Users, CalendarDays, 
    Zap, ShieldCheck, UserCircle, Briefcase, 
    DollarSign, UserPlus, ArrowRight, TrendingUp,
    CheckCircle2, AlertCircle, PieChart, Info
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard' },
    { title: 'Intelligence & Reports', href: '#' },
];

const reportCategories = [
    {
        title: 'Academic Intelligence',
        description: 'Competency-based evaluation and performance tracking',
        reports: [
            {
                title: 'Student Progress Report',
                subtitle: 'CBC Official Report Card',
                href: '/reports/report-cards',
                icon: FileText,
                status: 'CBC Ready',
                color: 'text-blue-600',
                bg: 'bg-blue-50',
                description: 'Detailed competency levels, learner traits, and teacher remarks.'
            },
            {
                title: 'Subject Performance Hub',
                subtitle: 'Subject-wise Analytics',
                href: '/reports/subject-performance',
                icon: TrendingUp,
                status: 'Analytics',
                color: 'text-emerald-600',
                bg: 'bg-emerald-50',
                description: 'Performance levels per learning area with class averages.'
            },
            {
                title: 'Class Performance Summary',
                subtitle: 'High-level Overview',
                href: '/reports/class-summary',
                icon: PieChart,
                status: 'Summary',
                color: 'text-indigo-600',
                bg: 'bg-indigo-50',
                description: 'Aggregate trends and distribution of performance levels.'
            },
            {
                title: 'Competency Mastery',
                subtitle: 'Skills & Traits Tracking',
                href: '/reports/competency-mastery',
                icon: CheckCircle2,
                status: 'Core CBC',
                color: 'text-amber-600',
                bg: 'bg-amber-50',
                description: 'Longitudinal tracking of creativity, critical thinking, etc.'
            }
        ]
    },
    {
        title: 'Operational Tracking',
        description: 'Daily activity and institutional management',
        reports: [
            {
                title: 'Attendance Analytics',
                subtitle: 'Presence & Punctuality',
                href: '/reports/attendance',
                icon: CalendarDays,
                status: 'Operational',
                color: 'text-rose-600',
                bg: 'bg-rose-50',
                description: 'Daily/monthly attendance percentages and chronic absenteeism alerts.'
            },
            {
                title: 'Continuous Assessment',
                subtitle: 'Task-level Progress',
                href: '/reports/assessments',
                icon: Zap,
                status: 'Live Data',
                color: 'text-cyan-600',
                bg: 'bg-cyan-50',
                description: 'Tracking assignments, projects, and activities in real-time.'
            },
            {
                title: 'Individual Learner Profile',
                subtitle: '360° Student View',
                href: '/reports/learner-profile',
                icon: UserCircle,
                status: 'Comprehensive',
                color: 'text-purple-600',
                bg: 'bg-purple-50',
                description: 'Consolidated view of academics, attendance, and observations.'
            },
            {
                title: 'Institutional Enrollment',
                subtitle: 'Admissions & Demographics',
                href: '/reports/enrollment',
                icon: UserPlus,
                status: 'Admin',
                color: 'text-slate-600',
                bg: 'bg-slate-50',
                description: 'Grade-wise student counts, admissions, and transfers.'
            }
        ]
    },
    {
        title: 'Resource & Fiscal Management',
        description: 'Staff performance and financial oversight',
        reports: [
            {
                title: 'Teacher Input Metrics',
                subtitle: 'Compliance & Trends',
                href: '/reports/teacher-metrics',
                icon: Briefcase,
                status: 'Insights',
                color: 'text-orange-600',
                bg: 'bg-orange-50',
                description: 'Monitoring lesson plan submissions and grading frequency.'
            },
            {
                title: 'Fee & Financial Audit',
                subtitle: 'Collection Intelligence',
                href: '/reports/finance',
                icon: DollarSign,
                status: 'Fiscal',
                color: 'text-green-600',
                bg: 'bg-green-50',
                description: 'In-depth fee payment trends, balances, and collection summaries.'
            }
        ]
    }
];

const stats = [
    { label: 'Total Archives', value: '1,248', icon: ShieldCheck, change: '+12% this term' },
    { label: 'Active Reports', value: '10+', icon: BarChart3, change: 'Fully Calibrated' },
    { label: 'System Health', value: 'Optimal', icon: Info, change: 'All nodes active' }
];

</script>

<template>
    <Head title="Intelligence & Reports Hub" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1400px] space-y-8 p-4 sm:p-6 lg:p-8 fade-in">
            
            <!-- HERO HEADER -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-slate-900 to-indigo-950 p-8 text-white shadow-2xl">
                <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-primary/20 blur-[100px]"></div>
                <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-500/10 blur-[100px]"></div>
                
                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Badge variant="outline" class="border-primary/50 text-primary-foreground text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-white/10 backdrop-blur-md">
                                Institutional Oversight
                            </Badge>
                            <span class="h-1 w-1 rounded-full bg-primary animate-pulse"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-primary-foreground/60">Live Analytics</span>
                        </div>
                        <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-none">Intelligence Hub</h1>
                        <p class="text-sm sm:text-lg text-primary-foreground/70 font-medium max-w-xl">
                            Consolidated data-driven reporting system optimized for the Competency Based Curriculum (CBC) framework.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 w-full md:w-auto">
                        <div v-for="stat in stats" :key="stat.label" class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-4 transition-all hover:bg-white/10">
                            <div class="flex items-center gap-3 mb-1">
                                <stat.icon class="h-4 w-4 text-primary" />
                                <span class="text-2xl font-black tabular-nums">{{ stat.value }}</span>
                            </div>
                            <p class="text-[10px] uppercase font-black tracking-widest text-primary-foreground/40">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REPORTS GRID -->
            <div class="space-y-12">
                <section v-for="category in reportCategories" :key="category.title" class="space-y-6">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-2xl font-black text-foreground">{{ category.title }}</h2>
                        <p class="text-sm font-medium text-muted-foreground">{{ category.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <Link v-for="report in category.reports" :key="report.title" :href="report.href" class="group h-full">
                            <Card class="h-full rounded-3xl border-border/40 bg-card/60 backdrop-blur-sm transition-all duration-300 hover:shadow-2xl hover:shadow-primary/5 hover:-translate-y-1 group-hover:border-primary/30 relative overflow-hidden flex flex-col">
                                <div class="absolute top-0 right-0 p-4 opacity-10 transition-transform group-hover:scale-110 group-hover:opacity-20">
                                    <report.icon class="h-24 w-24" />
                                </div>
                                <CardHeader class="pb-2">
                                    <div :class="['h-12 w-12 rounded-2xl flex items-center justify-center mb-4 transition-all group-hover:scale-110', report.bg]">
                                        <report.icon :class="['h-6 w-6', report.color]" />
                                    </div>
                                    <div class="flex justify-between items-start">
                                        <div class="space-y-0.5">
                                            <CardTitle class="text-[15px] font-black leading-tight">{{ report.title }}</CardTitle>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">{{ report.subtitle }}</p>
                                        </div>
                                        <Badge variant="outline" class="text-[8px] font-black uppercase tracking-widest border-border/40">{{ report.status }}</Badge>
                                    </div>
                                </CardHeader>
                                <CardContent class="mt-auto pt-4 border-t border-border/10 flex flex-col justify-between flex-1">
                                    <p class="text-[11px] leading-relaxed text-muted-foreground font-medium mb-4">{{ report.description }}</p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            Launch Report <ArrowRight class="h-3 w-3" />
                                        </span>
                                        <div class="h-8 w-8 rounded-full bg-primary/5 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                            <ArrowRight class="h-4 w-4" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                </section>Section -->
            </div>

            <!-- FOOTER INFO -->
            <div class="rounded-3xl bg-muted/30 border border-dashed border-border/60 p-6 flex items-center gap-6">
                <div class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center shadow-sm shrink-0">
                    <Info class="h-6 w-6 text-indigo-500" />
                </div>
                <div>
                    <h4 class="text-sm font-black text-foreground">Institutional Compliance Notice</h4>
                    <p class="text-xs text-muted-foreground font-medium max-w-2xl">
                        These reports are automatically generated based on live teacher submissions and classroom activities. All templates comply with the Ministry of Education standards for CBC implementation.
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}
</style>
