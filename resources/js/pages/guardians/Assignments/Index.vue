<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import {
    ClipboardList, ArrowRight, CheckCircle2, AlertCircle, Clock
} from 'lucide-vue-next';

const props = defineProps<{
    children: Array<{
        id: number;
        name: string;
        class: string | null;
        assignments: Array<{
            id: number;
            title: string;
            subject: string | null;
            class: string | null;
            teacher: string | null;
            assignment_type: string;
            due_date: string | null;
            total_marks: number | null;
            is_overdue: boolean;
            status: string;
            marks_obtained: number | null;
            final_marks: number | null;
        }>;
    }>;
    total_assignments_count: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'Assignments', href: '#' },
];

const getStatusColor = (status: string, isOverdue: boolean) => {
    if (status === 'graded') return 'bg-emerald-500 text-white';
    if (status === 'submitted') return 'bg-blue-500 text-white';
    if (isOverdue) return 'bg-rose-500 text-white';
    return 'bg-amber-500 text-white';
};

const getStatusLabel = (status: string, isOverdue: boolean) => {
    if (status === 'graded') return 'Graded';
    if (status === 'submitted') return 'Submitted';
    if (isOverdue) return 'Overdue';
    return 'Pending';
};
</script>

<template>
    <Head title="Children Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 md:p-8 max-w-[1600px] mx-auto animate-in fade-in duration-500 pb-20">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-200">
                        <ClipboardList class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">Scoreboard</h1>
                        <p class="text-sm text-muted-foreground">Assignments and tasks for all your children.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-xs font-bold text-slate-500">{{ total_assignments_count }} Assignments Total</span>
                </div>
            </div>

            <!-- Children Grouped Sections -->
            <div v-if="children.length === 0" class="rounded-2xl border border-dashed p-16 text-center">
                <Users class="h-12 w-12 mx-auto mb-4 text-muted-foreground/20" />
                <h3 class="text-lg font-bold text-foreground">No Children Linked</h3>
                <p class="text-sm text-muted-foreground mt-1">Please contact the school office to link your children to your account.</p>
            </div>

            <div v-else class="space-y-12">
                <div v-for="child in children" :key="child.id" class="space-y-6">
                    <!-- Child Header -->
                    <div class="flex items-center justify-between border-b pb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs">
                                {{ child.name.charAt(0) }}
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ child.name }}</h2>
                                <p class="text-xs text-slate-500">{{ child.class || 'No Class' }}</p>
                            </div>
                        </div>
                        <Link :href="`/guardian/children/${child.id}`" class="text-xs font-bold text-blue-600 hover:underline">View Profile</Link>
                    </div>

                    <!-- Assignments Grid/List for this Child -->
                    <div v-if="child.assignments.length === 0" class="py-10 text-center rounded-2xl bg-slate-50/50 border border-dashed border-slate-200">
                        <p class="text-sm text-slate-400 italic">No assignments found for this child.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <Link 
                            v-for="a in child.assignments" :key="a.id"
                            :href="`/guardian/assignments/${a.id}?student_id=${child.id}`"
                            class="group relative flex flex-col rounded-2xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-xl hover:border-blue-400 transition-all duration-300 overflow-hidden"
                        >
                            <!-- Decorative status corner -->
                            <div class="absolute top-0 right-0 w-24 h-24 -mr-12 -mt-12 group-hover:scale-110 transition-transform origin-center">
                                <div :class="['w-full h-full rotate-45 opacity-5', getStatusColor(a.status, a.is_overdue)]"></div>
                            </div>

                            <div class="flex-1 space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1 min-w-0">
                                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ a.subject }}</p>
                                        <h3 class="text-base font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                            {{ a.title }}
                                        </h3>
                                    </div>
                                    <Badge :class="['rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0 flex-shrink-0', getStatusColor(a.status, a.is_overdue)]">
                                        {{ getStatusLabel(a.status, a.is_overdue) }}
                                    </Badge>
                                </div>

                                <div class="flex flex-wrap gap-3 pt-2">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                        <Clock :class="['h-3.5 w-3.5', a.is_overdue && a.status === 'pending' ? 'text-rose-500 font-bold' : '']" />
                                        <span :class="a.is_overdue && a.status === 'pending' ? 'text-rose-500 font-black' : ''">{{ a.due_date }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                                        <User class="h-3 w-3" />
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">{{ a.teacher }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-blue-600 font-black text-[10px] uppercase tracking-widest opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all">
                                    Open Task <ArrowRight class="h-3 w-3" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
