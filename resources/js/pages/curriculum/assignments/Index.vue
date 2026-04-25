<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Target, Plus, Search, Filter,
    MoreVertical, Eye, Edit2, Trash2,
    Calendar, BookOpen, Clock,
    CheckCircle2, AlertCircle, ChevronRight,
    FileText, TrendingUp, Users, MoreHorizontal
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignments: any[];
    subjects: any[];
    classes: any[];
    children: any[];
    userRole: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '#' },
];

const deleteAssignment = (id: number) => {
    if (confirm('Delete this assignment?')) {
        router.delete(`/curriculum/assignments/${id}`);
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'published': return 'bg-emerald-50 text-emerald-600';
        case 'draft': return 'bg-slate-50 text-slate-500';
        case 'closed': return 'bg-red-50 text-red-600';
        case 'graded': return 'bg-blue-50 text-blue-600';
        default: return 'bg-slate-50 text-slate-500';
    }
};

const isGuardian = computed(() => props.userRole === 'parent');

const publishedCount = computed(() => props.assignments.filter(a => a.status === 'published').length);
const draftCount = computed(() => props.assignments.filter(a => a.status === 'draft').length);
const totalSubmissions = computed(() => props.assignments.reduce((acc: number, a: any) => acc + (a.submissions_count || 0), 0));

// Guardian specific stats
const pendingSubmissions = computed(() => props.assignments.filter(a => !a.submissions?.length).length);
const gradedSubmissions = computed(() => props.assignments.filter(a => a.submissions?.some((s: any) => s.status === 'graded')).length);

</script>

<template>
    <Head title="Assignments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1600px] mx-auto bg-[#f9fafb]/30">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        {{ isGuardian ? 'Student Assignments' : 'Assignments' }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ isGuardian ? 'Keep track of your children\'s academic tasks and progress.' : 'Manage tasks and track student progress.' }}
                    </p>
                </div>

                <div class="flex items-center gap-3" v-if="!isGuardian">
                    <Link :href="route('curriculum.assignments.vault')" class="h-10 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 font-semibold text-xs text-white shadow-sm transition-all inline-flex items-center gap-2">
                        <Archive class="h-4 w-4 text-blue-400" /> Academic Vault
                    </Link>
                    <Link href="/curriculum/assignments/create" class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-xs text-white shadow-sm transition-all inline-flex items-center gap-2">
                        <Plus class="h-4 w-4" /> New Assignment
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ isGuardian ? 'Active Tasks' : 'Total' }}</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ assignments.length }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <FileText class="h-5 w-5" />
                    </div>
                </div>
                
                <template v-if="!isGuardian">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Published</p>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ publishedCount }}</h2>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Drafts</p>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ draftCount }}</h2>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                            <AlertCircle class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Submissions</p>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ totalSubmissions }}</h2>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <Users class="h-5 w-5" />
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pending</p>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ pendingSubmissions }}</h2>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                            <Clock class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Graded</p>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ gradedSubmissions }}</h2>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                            <TrendingUp class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Children</p>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ children.length }}</h2>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <Users class="h-5 w-5" />
                        </div>
                    </div>
                </template>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <!-- Toolbar -->
                <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative w-full md:max-w-md">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                        <Input placeholder="Search assignments..." class="pl-10 h-11 rounded-xl text-sm font-medium border-slate-100 bg-[#f9fafb]/50 focus:bg-white transition-all shadow-none" />
                    </div>
                    <div class="flex items-center gap-3">
                        <Button variant="outline" class="h-11 px-4 rounded-xl border-slate-100 bg-[#f9fafb]/50 font-bold text-xs text-slate-500 shadow-none hover:bg-slate-50 transition-all">
                             <Filter class="mr-2 h-4 w-4" /> Filters
                        </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto" v-if="assignments.length">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-50">
                                <th class="pl-6 py-4 w-10">
                                    <div class="h-4 w-4 rounded border border-slate-200"></div>
                                </th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Assignment</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Subject</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Class</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Submissions</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Due Date</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="assignment in assignments" :key="assignment.id" class="group hover:bg-[#f9fafb]/50 transition-all">
                                <td class="pl-6 py-4">
                                    <div class="h-4 w-4 rounded border border-slate-200 group-hover:border-blue-400 transition-colors"></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            <FileText class="h-4 w-4" />
                                        </div>
                                        <Link :href="route('curriculum.assignments.show', assignment.id)" class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 line-clamp-1 hover:text-blue-600 transition-colors">{{ assignment.title }}</span>
                                            <span class="text-[10px] font-medium text-slate-400 uppercase">{{ assignment.assignment_type || 'homework' }}</span>
                                        </Link>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs font-semibold text-slate-600">{{ assignment.subject?.name || '—' }}</td>
                                <td class="px-4 py-4 text-xs font-semibold text-slate-600">{{ assignment.classroom?.name || '—' }}</td>
                                <td class="px-4 py-4 text-center">
                                    <Link v-if="!isGuardian" :href="route('curriculum.assignments.submissions', assignment.id)">
                                        <Badge class="rounded bg-blue-50 text-blue-600 border-0 text-[10px] font-black px-3 py-1 hover:bg-blue-600 hover:text-white transition-all cursor-pointer shadow-sm">
                                            {{ assignment.submissions_count || 0 }} Submissions
                                        </Badge>
                                    </Link>
                                    <Badge v-else class="rounded bg-blue-50 text-blue-600 border-0 text-[9px] font-bold px-2 py-0.5">
                                        {{ assignment.submissions_count || 0 }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-4 text-xs font-semibold text-slate-600">{{ assignment.due_date ? new Date(assignment.due_date).toLocaleDateString() : '—' }}</td>
                                <td class="px-4 py-4">
                                    <Badge :class="['rounded border-0 text-[9px] font-bold px-2 py-0.5 uppercase', getStatusBadge(assignment.status)]">
                                        {{ assignment.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/curriculum/assignments/${assignment.id}/submissions`" v-if="!isGuardian" class="h-9 px-4 rounded-xl bg-blue-50 text-blue-600 font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all inline-flex items-center">
                                            Review <ChevronRight class="ml-1 h-3.5 w-3.5" />
                                        </Link>
                                        <Link :href="`/curriculum/assignments/${assignment.id}`" v-else class="h-9 px-4 rounded-xl bg-blue-50 text-blue-600 font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all inline-flex items-center">
                                            Open Task <ChevronRight class="ml-1 h-3.5 w-3.5" />
                                        </Link>
                                        
                                        <DropdownMenu v-if="!isGuardian">
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-slate-100 text-slate-400">
                                                    <MoreVertical class="h-3.5 w-3.5" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="rounded-xl p-2 min-w-[130px]">
                                                <Link :href="`/curriculum/assignments/${assignment.id}`">
                                                    <DropdownMenuItem class="rounded-lg font-bold text-xs cursor-pointer">
                                                        <Eye class="mr-2 h-3.5 w-3.5" /> View
                                                    </DropdownMenuItem>
                                                </Link>
                                                <Link :href="`/curriculum/assignments/${assignment.id}/edit`">
                                                    <DropdownMenuItem class="rounded-lg font-bold text-xs cursor-pointer">
                                                        <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit
                                                    </DropdownMenuItem>
                                                </Link>
                                                <DropdownMenuItem @click="deleteAssignment(assignment.id)" class="rounded-lg font-bold text-xs text-red-600">
                                                    <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="py-20 text-center bg-white flex flex-col items-center justify-center gap-4">
                     <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200">
                        <Target class="h-8 w-8" />
                     </div>
                     <div class="space-y-1">
                         <h3 class="text-xl font-bold text-slate-900 tracking-tight">No Assignments</h3>
                         <p class="text-xs text-slate-400 font-medium max-w-xs mx-auto">Create your first assignment to start tracking student work.</p>
                     </div>
                     <Link href="/curriculum/assignments/create" class="h-11 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 font-bold text-xs text-white shadow-sm transition-all uppercase tracking-widest inline-flex items-center gap-2">
                        <Plus class="h-4 w-4" /> New Assignment
                     </Link>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-white border-t border-slate-50 flex items-center justify-between" v-if="assignments.length">
                    <p class="text-xs font-bold text-slate-400">Found {{ assignments.length }} assignments</p>
                    <div class="flex items-center gap-2">
                        <Button disabled variant="outline" class="h-9 rounded-xl border-slate-100 text-slate-300 font-bold px-3 text-xs">Prev</Button>
                        <Button disabled variant="outline" class="h-9 rounded-xl border-slate-200 text-slate-900 font-bold px-3 text-xs">1</Button>
                        <Button disabled variant="outline" class="h-9 rounded-xl border-slate-100 text-slate-300 font-bold px-3 text-xs">Next</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
