<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { 
    ClipboardList, ArrowRight, CheckCircle2, 
    AlertCircle, Clock, User, ChevronRight,
    FileText, Calendar
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

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
            is_overdue: boolean;
            total_marks: number | null;
            status: string;
            submitted: boolean;
        }>;
    }>;
    total_assignments: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'Assignments', href: '#' },
];

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'graded': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'returned': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-slate-50 text-slate-500 border-slate-100';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'graded': return 'Marked & Graded';
        case 'submitted': return 'Pending Review';
        case 'returned': return 'Needs Revision';
        case 'pending': return 'Not Started';
        default: return status.toUpperCase();
    }
};
</script>

<template>
    <Head title="Academic Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-10 p-6 md:p-8 max-w-[1400px] mx-auto animate-in fade-in duration-500 pb-20">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-100 pb-8">
                <div class="flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-xl shadow-blue-200">
                        <ClipboardList class="h-7 w-7" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase">Academic Assignments</h1>
                        <p class="text-sm font-medium text-slate-500">Monitor and assist with your children's learning tasks across all subjects.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="px-5 py-3 rounded-2xl bg-white border border-slate-100 shadow-sm flex flex-col items-end">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Pipeline</span>
                        <span class="text-xl font-black text-blue-600 tracking-tighter leading-none">{{ total_assignments }} Tasks</span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="total_assignments === 0" class="rounded-[2.5rem] bg-white border border-slate-100 p-20 text-center shadow-xl shadow-slate-200/20">
                <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200">
                    <ClipboardList class="h-10 w-10" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight uppercase mb-2">No Active Assignments</h3>
                <p class="text-sm text-slate-400 font-medium max-w-sm mx-auto italic">Your children currently have no published tasks in their respective classrooms.</p>
            </div>

            <!-- Organized Lists by Child -->
            <div v-else class="space-y-16">
                <div v-for="child in children" :key="child.id" class="space-y-6">
                    <!-- Child Section Header -->
                    <div class="flex items-center justify-between px-2">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-slate-900 flex items-center justify-center text-white text-base font-black shadow-lg">
                                {{ child.name.charAt(0) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">{{ child.name }}</h2>
                                <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em]">{{ child.class || 'No Class Assigned' }}</p>
                            </div>
                        </div>
                        <Link :href="`/guardian/children/${child.id}`" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-blue-600 transition-colors flex items-center gap-2">
                            View Child Profile <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <!-- Individual Assignments for this Child -->
                    <div v-if="child.assignments.length === 0" class="rounded-2xl border border-dashed border-slate-200 p-10 text-center bg-slate-50/20">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No individual assignments for this student</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                        <Link
                            v-for="a in child.assignments" :key="a.id"
                            :href="route('guardian.assignments.show', { assignment: a.id, student_id: child.id })"
                            class="group relative bg-white rounded-2xl border border-slate-100 p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6 transition-all hover:border-blue-200 hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-1 active:translate-y-0"
                            :class="a.is_overdue && !a.submitted ? 'border-l-4 border-l-rose-500' : ''"
                        >
                            <div class="flex-1 flex items-start sm:items-center gap-5 min-w-0">
                                <!-- Status Icon -->
                                <div class="h-12 w-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-110"
                                    :class="a.submitted
                                        ? 'bg-emerald-50 text-emerald-600'
                                        : a.is_overdue ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400'">
                                    <CheckCircle2 v-if="a.submitted" class="h-6 w-6" />
                                    <AlertCircle v-else-if="a.is_overdue" class="h-6 w-6" />
                                    <FileText v-else class="h-6 w-6" />
                                </div>
                                
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                        <Badge variant="outline" class="rounded-lg text-[9px] font-black uppercase tracking-widest px-2 py-0.5 border-slate-100 bg-slate-50 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600 group-hover:border-blue-100 transition-colors">
                                            {{ a.assignment_type }}
                                        </Badge>
                                        <Badge class="rounded bg-blue-50 text-blue-600 border-0 text-[10px] font-black px-2 py-0.5">
                                            {{ a.subject }}
                                        </Badge>
                                    </div>
                                    <h3 class="text-base font-black text-slate-900 tracking-tight leading-tight group-hover:text-blue-600 transition-colors">
                                        {{ a.title }}
                                    </h3>
                                    <div class="flex items-center gap-3 mt-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        <span class="flex items-center gap-1.5"><User class="h-3 w-3" /> {{ a.teacher }}</span>
                                        <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                                        <span class="flex items-center gap-1.5"><Calendar class="h-3 w-3" /> Due {{ a.due_date }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 justify-between lg:justify-end border-t lg:border-t-0 pt-4 lg:pt-0 border-slate-50">
                                <div class="text-right flex flex-col items-end">
                                    <Badge :class="['rounded-xl border-0 text-[10px] font-black px-4 py-1.5 uppercase shadow-sm', getStatusBadge(a.status)]">
                                        {{ getStatusLabel(a.status) }}
                                    </Badge>
                                    <p v-if="a.total_marks" class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-2 px-1">Score: {{ a.total_marks }} Pts</p>
                                </div>
                                <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <ArrowRight class="h-5 w-5" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
