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
</script>

<template>
    <Head title="Curriculum" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Curriculum</h1>
                    <p class="text-sm font-medium text-slate-500">Manage subjects, teaching plans, and learning materials.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="rounded-xl font-bold text-xs uppercase tracking-wider border-slate-200 shadow-sm">
                        <Settings class="mr-2 h-4 w-4" /> Setup
                    </Button>
                    <Button class="bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md">
                        <Plus class="mr-2 h-4 w-4" /> Add Subject
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="(stat, index) in [
                    { label: 'Learning Areas', value: stats.learning_areas, icon: Layers, color: 'text-blue-600', bg: 'bg-blue-50' },
                    { label: 'Subjects', value: stats.subjects, icon: BookOpen, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                    { label: 'Topics', value: stats.strands, icon: Network, color: 'text-violet-600', bg: 'bg-violet-50' },
                    { label: 'Competencies', value: stats.competencies, icon: Zap, color: 'text-amber-600', bg: 'bg-amber-50' }
                ]" :key="index" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-200 transition-all">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ stat.label }}</p>
                        <p class="text-3xl font-black text-slate-900">{{ stat.value }}</p>
                    </div>
                    <div :class="['h-14 w-14 rounded-full flex items-center justify-center shadow-sm', stat.bg]">
                        <component :is="stat.icon" :class="['h-7 w-7', stat.color]" />
                    </div>
                </div>
            </div>

            <!-- Main Modules -->
            <div class="grid md:grid-cols-2 gap-6">
                <Link v-for="mod in mainModules" :key="mod.title" :href="mod.href" class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-200 transition-all">
                    <div class="flex items-center gap-6">
                        <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center transition-colors', colors[mod.color as keyof typeof colors]]">
                            <component :is="mod.icon" class="h-8 w-8" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xl font-bold text-slate-900">{{ mod.title }}</h4>
                            <p class="text-sm text-slate-500">{{ mod.description }}</p>
                        </div>
                    </div>
                    <Button variant="ghost" size="icon" class="rounded-xl group-hover:bg-blue-50 group-hover:text-blue-600">
                        <ArrowRight class="h-6 w-6" />
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
