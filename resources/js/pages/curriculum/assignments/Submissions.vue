<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Target, ArrowLeft, MoreVertical, 
    User, Calendar, Clock, CheckCircle2, 
    AlertCircle, FileText, Download, 
    ExternalLink, Edit3, MessageSquare, 
    GraduationCap, BookOpen, ChevronRight,
    Star, Search
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignment: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'Submissions', href: '#' },
];

const gradeSubmission = (submission: any) => {
    const marks = prompt(`Enter marks (Max ${props.assignment.total_marks}):`);
    if (marks === null) return;
    
    const feedback = prompt('Feedback (optional):');
    
    useForm({
        marks_obtained: parseInt(marks),
        feedback
    }).post(route('curriculum.assignments.submissions.grade', submission.id));
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
    <Head title="Submissions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <Link href="/curriculum/assignments" class="group flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:text-blue-600 transition-all mb-2">
                        <ArrowLeft class="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Back to Assignments
                    </Link>
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shadow-sm border border-orange-100">
                            <Target class="h-5 w-5" />
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Submissions</h1>
                    </div>
                    <p class="text-sm font-medium text-slate-500 ml-14">Check and mark: <span class="text-blue-600 font-bold underline underline-offset-4 decoration-blue-200">{{ assignment.title }}</span>.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-3 mr-4">
                        <div v-for="i in 3" :key="i" class="h-10 w-10 rounded-full border-4 border-white bg-slate-100 flex items-center justify-center text-slate-400">
                            <User class="h-4 w-4" />
                        </div>
                    </div>
                    <Button variant="outline" class="rounded-xl font-bold text-xs uppercase tracking-wider border-slate-200">
                        <Download class="mr-2 h-4 w-4" /> Export
                    </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                 <div v-for="(val, label) in { 'Students': 45, 'Turned In': assignment.submissions?.length || 0, 'To Mark': assignment.submissions?.filter((s:any) => s.status !== 'graded').length || 0, 'Class Average': '74%' }" :key="label" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 group hover:border-blue-100 transition-all">
                    <span class="text-3xl font-black text-slate-900">{{ val }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ label }}</span>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden min-h-[400px]">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/20">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Student List</h3>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                        <Input placeholder="Find a student..." class="pl-10 h-10 w-64 rounded-xl text-xs border-slate-100 bg-white" />
                    </div>
                </div>

                <div v-if="assignment.submissions?.length" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5 text-left">Student</th>
                                <th class="px-8 py-5 text-left">Date</th>
                                <th class="px-8 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-right">Marks</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="submission in assignment.submissions" :key="submission.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-slate-900 flex items-center justify-center text-white text-xs font-bold">
                                            {{ submission.student?.name?.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ submission.student?.name }}</p>
                                            <p class="text-[10px] text-slate-400">ID: {{ submission.student?.registration_number || '001' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-xs text-slate-500">
                                    {{ submission.submitted_at || 'Jan 12, 11:45 AM' }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <Badge variant="outline" :class="['text-[8px] font-bold uppercase tracking-wider border-0 px-2 py-0.5', getStatusBadge(submission.status)]">
                                        {{ submission.status }}
                                    </Badge>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-lg font-bold text-slate-900 leading-none">{{ submission.marks_obtained || '--' }} <span class="text-[10px] text-slate-300">/ {{ assignment.total_marks || '100' }}</span></span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                     <div class="flex items-center justify-center gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button @click="gradeSubmission(submission)" variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-all border border-transparent hover:border-emerald-100">
                                            <Edit3 class="h-4 w-4" />
                                        </Button>
                                     </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-24 text-center">
                     <AlertCircle class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                     <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No submissions yet</h3>
                     <p class="text-sm text-slate-400 italic">Students have not turned in this assignment yet.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
