<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Layers,
    ArrowRight,
    Activity,
    Zap,
    ShieldCheck,
    Plus,
    Settings,
    Network,
    Calendar,
    BookOpenCheck,
    Target,
    Sparkles,
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
        color: 'blue',
    },
    {
        title: 'Academic Planner',
        description: 'Schemes of work and lesson plans.',
        href: '/curriculum/planner/schemes',
        icon: Calendar,
        color: 'indigo',
    },
    {
        title: 'Assignment Center',
        description: 'Give tasks and grade student work.',
        href: '/curriculum/assignments',
        icon: Target,
        color: 'emerald',
    },
    {
        title: 'Resource Vault',
        description: 'Teaching notes, videos, and PDFs.',
        href: '/curriculum/resources',
        icon: Sparkles,
        color: 'amber',
    },
];

const colors = {
    blue: 'bg-blue-50 text-blue-600',
    indigo: 'bg-indigo-50 text-indigo-600',
    emerald: 'bg-emerald-50 text-emerald-600',
    amber: 'bg-amber-50 text-amber-600',
};

const summary = [
    {
        label: 'Learning Areas',
        value: props.stats.learning_areas,
        icon: Layers,
        trend: '+2',
    },
    {
        label: 'Subjects',
        value: props.stats.subjects,
        icon: BookOpen,
        trend: '+5',
    },
    {
        label: 'Topics',
        value: props.stats.strands,
        icon: Network,
        trend: '+12',
    },
    {
        label: 'Competencies',
        value: props.stats.competencies,
        icon: Zap,
        trend: '+8',
    },
];

const curriculumModules = [
    {
        title: 'Syllabus Mapping',
        description: 'Subjects, topics, and learning goals.',
        href: '/curriculum/syllabus',
        icon: BookOpenCheck,
        color: 'blue',
    },
    {
        title: 'Academic Planner',
        description: 'Schemes of work and lesson plans.',
        href: '/curriculum/planner/schemes',
        icon: Calendar,
        color: 'indigo',
    },
    {
        title: 'Assignment Center',
        description: 'Give tasks and grade student work.',
        href: '/curriculum/assignments',
        icon: Target,
        color: 'emerald',
    },
    {
        title: 'Resource Vault',
        description: 'Teaching notes, videos, and PDFs.',
        href: '/curriculum/resources',
        icon: Sparkles,
        color: 'amber',
    },
];
</script>

<template>
    <Head title="Curriculum" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col justify-between gap-4 px-1 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Curriculum Engine
                    </h1>
                    <p
                        class="text-sm font-medium text-muted-foreground sm:text-sm"
                    >
                        Design, monitor, and scale academic standards across the
                        institution.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-muted"
                    >
                        <Activity class="mr-2 h-4 w-4 opacity-70" />
                        Live Audit
                    </Button>
                </div>
            </div>

            <!-- Curriculum Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4">
                <div
                    v-for="item in summary"
                    :key="item.label"
                    class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-300 hover:border-blue-500/50 sm:p-6 dark:border-white/5"
                >
                    <div
                        class="mb-3 flex flex-col justify-between gap-2 sm:flex-row sm:items-center"
                    >
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground uppercase sm:text-xs"
                        >
                            {{ item.label }}
                        </p>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white sm:h-8 sm:w-8"
                        >
                            <component
                                :is="item.icon"
                                class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                            />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <h3
                            class="text-xl font-bold text-foreground tabular-nums sm:text-2xl"
                        >
                            {{ item.value }}
                        </h3>
                        <span
                            class="text-xs font-bold text-emerald-600 sm:text-xs"
                            v-if="item.trend"
                            >{{ item.trend }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Management Grid -->
            <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                <Link
                    v-for="mod in curriculumModules"
                    :key="mod.title"
                    :href="mod.href"
                    class="group flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:border-blue-200 sm:rounded-3xl sm:p-8"
                >
                    <div class="flex items-center gap-4 sm:gap-6">
                        <div
                            :class="[
                                'flex h-12 w-12 items-center justify-center rounded-xl transition-colors sm:h-16 sm:w-16 sm:rounded-2xl',
                                colors[mod.color as keyof typeof colors],
                            ]"
                        >
                            <component
                                :is="mod.icon"
                                class="h-6 w-6 sm:h-8 sm:w-8"
                            />
                        </div>
                        <div class="min-w-0 space-y-0.5 sm:space-y-1">
                            <h4
                                class="truncate text-base font-bold text-slate-900 transition-colors group-hover:text-blue-600 sm:text-xl"
                            >
                                {{ mod.title }}
                            </h4>
                            <p
                                class="line-clamp-1 text-xs text-slate-500 sm:text-sm"
                            >
                                {{ mod.description }}
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="ml-2 h-9 w-9 rounded-xl group-hover:bg-blue-50 group-hover:text-blue-600 sm:h-10 sm:w-10"
                    >
                        <ArrowRight class="h-5 w-5" />
                    </Button>
                </Link>
            </div>

            <!-- Overview Section -->
            <div
                class="rounded-xl border border-slate-100 bg-white p-10 shadow-sm"
            >
                <div class="mb-10 flex items-center justify-between">
                    <div class="space-y-1">
                        <h3
                            class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            School Overview
                        </h3>
                        <p class="text-xs font-medium text-slate-400">
                            Teaching and learning activities across the school.
                        </p>
                    </div>
                </div>

                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Coverage -->
                    <div
                        class="flex flex-col items-center space-y-4 rounded-3xl border border-slate-100 bg-slate-50 p-8 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full border-8 border-blue-50 bg-white text-xl font-bold text-blue-600"
                        >
                            {{ insights.syllabus_coverage }}%
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">
                                Topics Covered
                            </h4>
                            <p class="text-xs font-medium text-slate-400">
                                Completion of syllabus
                            </p>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div
                        class="flex flex-col items-center space-y-4 rounded-3xl border border-slate-100 bg-slate-50 p-8 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50 text-indigo-600"
                        >
                            <ShieldCheck class="h-10 w-10" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">
                                {{ insights.pending_approvals }} Waiting
                            </h4>
                            <p class="text-xs font-medium text-slate-400">
                                Plans needing review
                            </p>
                        </div>
                    </div>

                    <!-- Activity -->
                    <div
                        class="flex flex-col items-center space-y-4 rounded-3xl border border-slate-100 bg-slate-50 p-8 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                        >
                            <Activity class="h-10 w-10" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">
                                {{ insights.avg_submission_rate }}% Activity
                            </h4>
                            <p class="text-xs font-medium text-slate-400">
                                Student task completion
                            </p>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div
                        class="flex flex-col items-center space-y-4 rounded-3xl border border-slate-100 bg-slate-50 p-8 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-amber-500"
                        >
                            <Zap class="h-10 w-10" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">
                                {{ insights.unassigned_subjects }} Empty
                            </h4>
                            <p class="text-xs font-medium text-slate-400">
                                Unassigned subjects
                            </p>
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
