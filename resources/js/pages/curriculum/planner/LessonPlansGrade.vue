<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Users, BookOpen, ChevronRight, School,
    User, ArrowLeft
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grade: any;
    classes: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
    { title: props.grade.name, href: '#' },
];
</script>

<template>
    <Head :title="`${grade.name} - Classes`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Navigation & Header -->
            <div class="flex flex-col md:flex-row md:items-center gap-6 pb-6 border-b border-border/40">
                <Link href="/curriculum/planner/lesson-plans">
                    <Button variant="outline" size="icon" class="h-10 w-10 rounded-full border-border bg-background hover:bg-muted shadow-sm transition-all">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div class="space-y-1">
                    <h1 class="flex items-center gap-3 text-3xl font-black tracking-tight text-foreground">
                        {{ grade.name }}
                        <Badge variant="outline" class="bg-primary/5 text-primary border-primary/20 text-xs py-1">{{ grade.code }}</Badge>
                    </h1>
                    <p class="text-[15px] text-muted-foreground">
                        Select a specific class context to manage its daily lesson plans.
                    </p>
                </div>
            </div>

            <!-- Classes Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link 
                    v-for="c in classes" 
                    :key="c.id"
                    :href="`/curriculum/planner/lesson-plans/class/${c.id}/subjects`"
                    class="group relative rounded-3xl border border-border bg-card p-6 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 overflow-hidden flex flex-col justify-between min-h-[220px]"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-12 w-12 flex-shrink-0 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary group-hover:scale-110 group-hover:bg-primary group-hover:text-primary-foreground group-hover:shadow-lg group-hover:shadow-primary/30 transition-all duration-300">
                                <School class="h-6 w-6" />
                            </div>
                            <Badge variant="outline" class="bg-card text-foreground px-3 py-1 font-bold text-[10px] uppercase tracking-widest border border-border/50 shadow-sm">Class Context</Badge>
                        </div>
                        <h3 class="text-xl font-black text-foreground tracking-tight group-hover:text-primary transition-colors">
                            {{ c.name }}
                        </h3>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground/80 mt-2 flex items-center gap-1.5 opacity-80">
                            <User class="h-3.5 w-3.5" /> 
                            Teacher: {{ c.class_teacher?.first_name ? `${c.class_teacher.first_name} ${c.class_teacher.last_name}` : 'Not Assigned' }}
                        </p>
                    </div>

                    <div class="mt-6 pt-5 border-t border-border/40 flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-blue-50/50 flex items-center justify-center">
                                <BookOpen class="h-4 w-4 text-blue-500" />
                            </div>
                            <span class="text-sm font-bold text-foreground">
                                {{ c.lesson_plans_count || 0 }} <span class="text-[10px] uppercase tracking-widest text-muted-foreground ml-1 font-medium">Plans</span>
                            </span>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-lg shadow-primary/20 text-primary-foreground transform group-hover:translate-x-1 transition-transform duration-300">
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </div>
                </Link>

                <div v-if="classes.length === 0" class="col-span-full py-20 text-center flex flex-col items-center justify-center bg-card rounded-3xl border border-dashed border-border/60">
                    <div class="h-16 w-16 rounded-full bg-muted/50 flex items-center justify-center mb-4 text-muted-foreground">
                        <School class="h-8 w-8 opacity-50" />
                    </div>
                    <h3 class="text-lg font-black tracking-tight text-foreground">No Classes Assigned</h3>
                    <p class="text-sm text-muted-foreground max-w-[300px] mt-2 leading-relaxed">
                        There are no classes found under {{ grade.name }} for your account context.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
