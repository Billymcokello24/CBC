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
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Curriculum</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Curriculum Management
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage academic standards, schemes of work, and teaching resources.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                    >
                        <Activity class="mr-2 h-4 w-4 opacity-70" />
                        Academic Audit
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <div
                    v-for="item in summary"
                    :key="item.label"
                    class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">{{ item.label }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold text-foreground tabular-nums">
                            {{ item.value }}
                        </h3>
                        <span class="text-[10px] font-bold text-emerald-600" v-if="item.trend">{{ item.trend }}</span>
                    </div>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Total Count</p>
                </div>
            </div>

            <!-- Management Modules -->
            <div class="grid gap-6 md:grid-cols-2">
                <Link
                    v-for="mod in curriculumModules"
                    :key="mod.title"
                    :href="mod.href"
                    class="group flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/30 hover:shadow-md dark:border-white/5"
                >
                    <div class="flex items-center gap-5 sm:gap-6">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:scale-110 group-hover:bg-primary group-hover:text-white sm:h-14 sm:w-14"
                        >
                            <component :is="mod.icon" class="h-6 w-6 sm:h-7 sm:w-7" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="truncate text-lg font-bold text-foreground transition-colors group-hover:text-primary sm:text-xl">
                                {{ mod.title }}
                            </h4>
                            <p class="text-xs text-muted-foreground sm:text-sm line-clamp-1">
                                {{ mod.description }}
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-10 w-10 rounded-xl group-hover:bg-primary/5 group-hover:text-primary transition-all"
                    >
                        <ArrowRight class="h-5 w-5" />
                    </Button>
                </Link>
            </div>

            <!-- School Insights Section -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 sm:p-8">
                <div class="mb-8 flex flex-col gap-1">
                    <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">
                        Teaching Insights
                    </h3>
                    <p class="text-xs font-medium text-muted-foreground/60">
                        Overview of academic progress and standards across the school.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Progress Card -->
                    <div class="flex flex-col items-center gap-4 rounded-2xl border border-border bg-muted/20 p-6 text-center transition-all hover:bg-card">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-primary/20 bg-card text-lg font-bold text-primary shadow-sm">
                            {{ insights.syllabus_coverage }}%
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-foreground">Syllabus Completion</h4>
                            <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Overall Progress</p>
                        </div>
                    </div>

                    <!-- Review Card -->
                    <div class="flex flex-col items-center gap-4 rounded-2xl border border-border bg-muted/20 p-6 text-center transition-all hover:bg-card">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <ShieldCheck class="h-8 w-8" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-foreground">{{ insights.pending_approvals }} Pending</h4>
                            <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Plans for Review</p>
                        </div>
                    </div>

                    <!-- Punctuality Card -->
                    <div class="flex flex-col items-center gap-4 rounded-2xl border border-border bg-muted/20 p-6 text-center transition-all hover:bg-card">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600">
                            <Activity class="h-8 w-8" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-foreground">{{ insights.avg_submission_rate }}% Activity</h4>
                            <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Learner Engagement</p>
                        </div>
                    </div>

                    <!-- Alerts Card -->
                    <div class="flex flex-col items-center gap-4 rounded-2xl border border-border bg-muted/20 p-6 text-center transition-all hover:bg-card">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600">
                            <Zap class="h-8 w-8" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-foreground">{{ insights.unassigned_subjects }} Empty</h4>
                            <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Unassigned Areas</p>
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
