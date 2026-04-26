<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import { 
    BookOpen, Layers, ArrowRight, Activity, Zap, 
    ShieldCheck, Plus, Settings, Network, Calendar,
    BookOpenCheck, Target, Sparkles
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        learning_areas: number;
        subjects: number;
        strands: number;
        competencies: number;
        schemes: number;
        lesson_plans: number;
        assignments: number;
        resources: number;
    };
    insights: {
        syllabus_coverage: number;
        pending_approvals: number;
        avg_submission_rate: number;
        unassigned_subjects: number;
    };
    recent_areas: any[];
    recent_competencies: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
];

const mainModules = [
    {
        title: 'Syllabus Mapping',
        description: 'Subjects, topics, and learning goals.',
        href: '/curriculum/syllabus',
        icon: BookOpenCheck,
        color: 'blue'
    },
    {
        title: 'Academic Planner',
        description: 'Schemes of work and lesson plans.',
        href: '/curriculum/planner/schemes',
        icon: Calendar,
        color: 'indigo'
    },
    {
        title: 'Assignment Center',
        description: 'Give tasks and grade student work.',
        href: '/curriculum/assignments',
        icon: Target,
        color: 'emerald'
    },
    {
        title: 'Resource Vault',
        description: 'Teaching notes, videos, and PDFs.',
        href: '/curriculum/resources',
        icon: Sparkles,
        color: 'amber'
    }
];

const colors = {
    blue: 'bg-blue-50 text-blue-600',
    indigo: 'bg-indigo-50 text-indigo-600',
    emerald: 'bg-emerald-50 text-emerald-600',
    amber: 'bg-amber-50 text-amber-600'
};

const summary = [
    { label: 'Learning Areas', value: props.stats.learning_areas, icon: Layers, trend: '+2' },
    { label: 'Subjects', value: props.stats.subjects, icon: BookOpen, trend: '+5' },
    { label: 'Topics', value: props.stats.strands, icon: Network, trend: '+12' },
    { label: 'Competencies', value: props.stats.competencies, icon: Zap, trend: '+8' }
];

const curriculumModules = [
    { title: 'Syllabus Mapping', description: 'Subjects, topics, and learning goals.', href: '/curriculum/syllabus', icon: BookOpenCheck, color: 'blue' },
    { title: 'Academic Planner', description: 'Schemes of work and lesson plans.', href: '/curriculum/planner/schemes', icon: Calendar, color: 'indigo' },
    { title: 'Assignment Center', description: 'Give tasks and grade student work.', href: '/curriculum/assignments', icon: Target, color: 'emerald' },
    { title: 'Resource Vault', description: 'Teaching notes, videos, and PDFs.', href: '/curriculum/resources', icon: Sparkles, color: 'amber' }
];
</script>

<template>
    <Head title="Curriculum" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 sm:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-10 sm:pb-20 p-4 sm:p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
                <div class="space-y-1">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground">Curriculum Engine</h1>
                    <p class="text-sm sm:text-[15px] text-muted-foreground font-medium">Design, monitor, and scale academic standards across the institution.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-bold text-xs uppercase tracking-widest hover:bg-muted transition-all">
                        <Activity class="mr-2 h-4 w-4 opacity-70" />
                        Live Audit
                    </Button>
                </div>
            </div>

            <!-- Curriculum Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                <div v-for="item in summary" :key="item.label" 
                     class="rounded-2xl border border-border bg-card p-4 sm:p-6 shadow-sm dark:border-white/5 group hover:border-blue-500/50 transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <p class="text-[10px] sm:text-xs font-black text-muted-foreground uppercase tracking-widest">{{ item.label }}</p>
                        <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <component :is="item.icon" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <h3 class="text-xl sm:text-2xl font-black text-foreground tabular-nums">{{ item.value }}</h3>
                        <span class="text-[9px] sm:text-xs font-bold text-emerald-600" v-if="item.trend">{{ item.trend }}</span>
                    </div>
                </div>
            </div>

            <!-- Management Grid -->
            <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                <Link v-for="mod in curriculumModules" :key="mod.title" :href="mod.href" class="bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-200 transition-all">
                    <div class="flex items-center gap-4 sm:gap-6">
                        <div :class="['h-12 w-12 sm:h-16 sm:w-16 rounded-xl sm:rounded-2xl flex items-center justify-center transition-colors', colors[mod.color as keyof typeof colors]]">
                            <component :is="mod.icon" class="h-6 w-6 sm:h-8 sm:w-8" />
                        </div>
                        <div class="space-y-0.5 sm:space-y-1 min-w-0">
                            <h4 class="text-base sm:text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors truncate">{{ mod.title }}</h4>
                            <p class="text-xs sm:text-sm text-slate-500 line-clamp-1">{{ mod.description }}</p>
                        </div>
                    </div>
                    <Button variant="ghost" size="icon" class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl group-hover:bg-blue-50 group-hover:text-blue-600 ml-2">
                        <ArrowRight class="h-5 w-5" />
                    </Button>
                </Link>
            </div>

            <!-- Overview Section -->
            <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-10">
                    <div class="space-y-1">
                        <h3 class="text-xl font-bold tracking-tight text-slate-900 uppercase">School Overview</h3>
                        <p class="text-xs font-medium text-slate-400">Teaching and learning activities across the school.</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-4 gap-8">
                    <!-- Coverage -->
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center space-y-4">
                        <div class="h-20 w-20 rounded-full border-8 border-blue-50 flex items-center justify-center font-bold text-xl text-blue-600 bg-white">
                            {{ insights.syllabus_coverage }}%
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Topics Covered</h4>
                            <p class="text-[10px] text-slate-400 font-medium">Completion of syllabus</p>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center space-y-4">
                        <div class="h-20 w-20 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <ShieldCheck class="h-10 w-10" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ insights.pending_approvals }} Waiting</h4>
                            <p class="text-[10px] text-slate-400 font-medium">Plans needing review</p>
                        </div>
                    </div>

                    <!-- Activity -->
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center space-y-4">
                         <div class="h-20 w-20 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <Activity class="h-10 w-10" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ insights.avg_submission_rate }}% Activity</h4>
                            <p class="text-[10px] text-slate-400 font-medium">Student task completion</p>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center space-y-4">
                         <div class="h-20 w-20 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                            <Zap class="h-10 w-10" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ insights.unassigned_subjects }} Empty</h4>
                            <p class="text-[10px] text-slate-400 font-medium">Unassigned subjects</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Clean and minimal */
</style>
