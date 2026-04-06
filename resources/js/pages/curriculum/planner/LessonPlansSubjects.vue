<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    BookOpen, ChevronRight, School,
    ArrowLeft, LayoutGrid, Plus, BookCopy
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    currentClass: any;
    subjects: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
    { title: props.currentClass.grade_level.name, href: `/curriculum/planner/lesson-plans/grade/${props.currentClass.grade_level_id}` },
    { title: props.currentClass.name, href: '#' },
];

const getSubjectStyles = (type: string) => {
    switch (type) {
        case 'core': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'elective': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};
</script>

<template>
    <Head :title="`${currentClass.name} - Subjects`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Navigation & Header -->
            <div class="flex flex-col md:flex-row md:items-center gap-6 pb-6 border-b border-border/40">
                <Link :href="`/curriculum/planner/lesson-plans/grade/${currentClass.grade_level_id}`">
                    <Button variant="outline" size="icon" class="h-10 w-10 rounded-full border-border bg-background hover:bg-muted shadow-sm transition-all">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div class="space-y-1">
                    <h1 class="flex items-center gap-3 text-3xl font-black tracking-tight text-foreground">
                        {{ currentClass.name }}
                        <span class="text-muted-foreground/30 font-thin mx-1">/</span>
                        <span class="text-primary/70">Subjects</span>
                    </h1>
                    <p class="text-[15px] text-muted-foreground">
                        Select a subject discipline to access its daily planning matrix.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        @click="router.get(`/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subjects[0]?.id}?showBulk=true`)"
                        class="inline-flex items-center justify-center rounded-2xl px-6 h-11 text-[10px] font-black uppercase tracking-widest border-border shadow-sm hover:bg-muted transition-all"
                        v-if="subjects.length > 0"
                    >
                        <BookCopy class="mr-2.5 h-4 w-4" /> Bulk Upload
                    </Button>
                    <Button
                        @click="router.get(`/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subjects[0]?.id}?addNew=true`)"
                        class="inline-flex items-center justify-center rounded-2xl bg-primary px-8 h-11 text-[10px] font-black uppercase tracking-widest text-primary-foreground shadow-xl shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
                        v-if="subjects.length > 0"
                    >
                        <Plus class="mr-2.5 h-4 w-4" /> New Lesson
                    </Button>
                </div>
            </div>

            <!-- Subjects Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link 
                    v-for="subject in subjects" 
                    :key="subject.id"
                    :href="`/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subject.id}`"
                    class="group relative rounded-3xl border border-border bg-card p-6 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 overflow-hidden flex flex-col justify-between min-h-[200px]"
                >
                    <div class="absolute inset-x-0 top-0 h-1.5 bg-primary/10 group-hover:bg-primary transition-colors duration-300"></div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-12 w-12 flex-shrink-0 rounded-2xl bg-muted/40 border border-border/50 flex items-center justify-center text-muted-foreground group-hover:scale-110 group-hover:bg-primary/10 group-hover:text-primary transition-all duration-300">
                                <LayoutGrid class="h-6 w-6" />
                            </div>
                            <Badge variant="outline" :class="[getSubjectStyles(subject.subject_type), 'rounded-md py-0.5 px-2 text-[9px] font-black uppercase tracking-widest border shadow-sm']">
                                {{ subject.subject_type || 'General' }}
                            </Badge>
                        </div>
                        <h3 class="text-xl font-black text-foreground tracking-tight group-hover:text-primary transition-colors line-clamp-2">
                            {{ subject.name }}
                        </h3>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground/60 mt-2">
                            CODE: {{ subject.code }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-border/40 flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="flex flex-col">
                                <span class="text-[9px] uppercase font-black tracking-widest text-muted-foreground/60">Operational Plans</span>
                                <span class="text-sm font-bold text-foreground">
                                    {{ subject.lesson_plans_count || 0 }} 
                                </span>
                            </div>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-lg shadow-primary/20 text-primary-foreground transform group-hover:rotate-[-45deg] transition-transform duration-500">
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </div>
                </Link>

                <div v-if="subjects.length === 0" class="col-span-full py-24 text-center flex flex-col items-center justify-center bg-card rounded-3xl border border-dashed border-border/60">
                    <div class="h-20 w-20 rounded-full bg-muted/50 flex items-center justify-center mb-6 text-muted-foreground">
                        <LayoutGrid class="h-10 w-10 opacity-30" />
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-foreground">No Disciplines Configured</h3>
                    <p class="text-muted-foreground max-w-[360px] mt-2 leading-relaxed font-medium">
                        There are no active subjects found for this class context. Please configure subjects in the Curriculum department first.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
