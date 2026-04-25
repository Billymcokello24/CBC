<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Target, ArrowLeft, User, Calendar, 
    Download, Eye, Search, AlertCircle,
    ChevronRight, TrendingUp
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignment: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'Submission Scoreboard', href: '#' },
];

const openReview = (submission: any) => {
    router.get(route('curriculum.assignments.submissions.review', { 
        assignment: props.assignment.id, 
        submission: submission.id 
    }));
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'graded': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'pending': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

</script>

<template>
    <Head title="Submission Scoreboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1400px] mx-auto animate-in fade-in duration-500">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Link :href="route('curriculum.assignments.index')" class="group flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-blue-600 transition-all mb-2">
                        <ArrowLeft class="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Back to Assignments
                    </Link>
                    <div class="flex items-center gap-4">
                        <div class="h-11 w-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm">
                            <Target class="h-5 w-5" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Submission Scoreboard</h1>
                            <p class="text-xs font-medium text-slate-500 italic">{{ assignment.title }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-100 bg-[#f9fafb]/50 font-bold text-xs text-slate-500 shadow-none hover:bg-slate-50 transition-all">
                        <Download class="mr-2 h-4 w-4" /> Export Results
                    </Button>
                </div>
            </div>

            <!-- Dashboard Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                 <div v-for="(val, label) in { 'Learners': 45, 'Submissions': assignment.submissions?.length || 0, 'To Assess': assignment.submissions?.filter((s:any) => s.status !== 'graded').length || 0, 'Class Index': '74%' }" :key="label" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-200 transition-all">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ label }}</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ val }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all">
                        <TrendingUp v-if="label === 'Class Index'" class="h-5 w-5" />
                        <Users v-else-if="label === 'Learners'" class="h-5 w-5" />
                        <FileText v-else class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Learner Directory -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Learner Scoreboard</h3>
                        <Badge class="bg-emerald-50 text-emerald-600 border-0 font-bold text-[9px] px-2 py-0.5 rounded uppercase">Live Intake</Badge>
                    </div>
                    <div class="relative w-full md:max-w-md">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300" />
                        <Input placeholder="Filter learners..." class="pl-10 h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 shadow-none font-bold text-xs focus:bg-white transition-all" />
                    </div>
                </div>

                <div v-if="assignment.submissions?.length" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-50">
                                <th class="pl-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-1/3">Learner Profile</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date/Time</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Performance</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="submission in assignment.submissions" :key="submission.id" 
                                class="group transition-all hover:bg-[#f9fafb]/50"
                            >
                                <td class="pl-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            {{ submission.student?.first_name?.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 line-clamp-1 hover:text-blue-600 transition-colors cursor-pointer" @click="openReview(submission)">
                                                {{ submission.student?.first_name }} {{ submission.student?.last_name }}
                                            </span>
                                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">ID: #{{ submission.student?.admission_number || '001' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700">{{ new Date(submission.submitted_at).toLocaleDateString(undefined, { dateStyle: 'medium' }) }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ new Date(submission.submitted_at).toLocaleTimeString(undefined, { timeStyle: 'short' }) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <Badge :class="['rounded border-0 text-[10px] font-bold px-3 py-1 uppercase', getStatusBadge(submission.status)]">
                                        {{ submission.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-lg font-bold text-slate-900 tracking-tight">{{ submission.marks_obtained !== null ? submission.marks_obtained : '--' }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">/ {{ assignment.total_marks }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                     <Link :href="route('curriculum.assignments.submissions.review', { assignment: assignment.id, submission: submission.id })" 
                                        class="h-9 px-4 rounded-xl bg-blue-50 text-blue-600 font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all inline-flex items-center shadow-sm"
                                     >
                                         Review <ChevronRight class="ml-1 h-3.5 w-3.5" />
                                     </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-32 flex flex-col items-center justify-center text-center">
                     <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center mb-6">
                         <AlertCircle class="h-10 w-10 text-slate-200" />
                     </div>
                     <h3 class="text-2xl font-black text-slate-300 uppercase tracking-[0.2em] mb-2">No Inbound Data</h3>
                     <p class="text-sm text-slate-400 italic">The class list for this assignment is currently empty.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* High-end list styling */
</style>
