<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Layers, BookOpen, ChevronRight, School,
    CheckCircle2, Users, Calendar, Clock, Edit2, FileText
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    grades: any[];
    stats: {
        total_plans: number;
        approved_plans: number;
        pending_plans: number;
        draft_plans: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
];

const getCategoryColor = (category: string) => {
    switch (category) {
        case 'Pre-Primary': return 'bg-pink-50 text-pink-600 border-pink-100';
        case 'Lower Primary': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Upper Primary': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'Junior Secondary': return 'bg-violet-50 text-violet-600 border-violet-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

</script>

<template>
    <Head title="Lesson Plans Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Lesson Plans Overview</h1>
                    <p class="text-[15px] text-muted-foreground">
                        Strategic overview of instructional planning across all grade levels.
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Lessons</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.total_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <FileText class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Approved</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.approved_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                         <CheckCircle2 class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Pending Review</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.pending_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <Clock class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">In Draft</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.draft_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600">
                        <Edit2 class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Grades Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link 
                    v-for="grade in grades" 
                    :key="grade.id"
                    :href="`/curriculum/planner/lesson-plans/grade/${grade.id}`"
                    class="group relative rounded-3xl border border-border bg-card p-6 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 overflow-hidden flex flex-col"
                >
                    <!-- Background Decoration -->
                    <div class="absolute -right-6 -top-6 rounded-full bg-primary/5 w-24 h-24 blur-2xl group-hover:bg-primary/10 transition-all duration-500"></div>
                    
                    <div class="flex items-start justify-between relative z-10">
                        <div class="flex flex-col gap-1.5">
                            <Badge variant="outline" :class="[getCategoryColor(grade.category), 'rounded-md py-0.5 px-2 text-[9px] font-black uppercase tracking-widest w-fit border shadow-sm']">
                                {{ grade.category || 'General' }}
                            </Badge>
                            <h3 class="text-2xl font-black tracking-tight text-foreground group-hover:text-primary transition-colors flex items-center gap-2">
                                {{ grade.name }}
                            </h3>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-muted/30 border border-border/50 flex items-center justify-center group-hover:scale-110 group-hover:bg-primary/10 group-hover:text-primary transition-all duration-300 text-muted-foreground">
                            <Layers class="h-6 w-6" />
                        </div>
                    </div>

                    <div class="mt-8 flex items-center gap-4 border-t border-border/40 pt-5 relative z-10">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">Classes</span>
                            <span class="text-base font-bold text-foreground flex items-center gap-1.5 mt-0.5">
                                <School class="h-4 w-4 text-slate-400" />
                                {{ grade.total_classes || 0 }} 
                            </span>
                        </div>
                        <div class="w-px h-8 bg-border/50"></div>
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">Total Plans</span>
                            <span class="text-base font-bold text-foreground flex items-center gap-1.5 mt-0.5">
                                <BookOpen class="h-4 w-4 text-slate-400" />
                                {{ grade.lesson_plans_count || 0 }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transform translate-x-4 group-hover:translate-x-0 transition-all duration-300">
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-lg shadow-primary/20 text-primary-foreground">
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </div>
                </Link>

                <div v-if="grades.length === 0" class="col-span-full py-20 text-center flex flex-col items-center justify-center bg-card rounded-3xl border border-dashed border-border/60">
                    <div class="h-16 w-16 rounded-full bg-muted/50 flex items-center justify-center mb-4 text-muted-foreground">
                        <Layers class="h-8 w-8 opacity-50" />
                    </div>
                    <h3 class="text-lg font-black tracking-tight text-foreground">No Grade Levels Assigned</h3>
                    <p class="text-sm text-muted-foreground max-w-[300px] mt-2 leading-relaxed">
                        You do not have any classes or grades assigned for lesson plan management.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
