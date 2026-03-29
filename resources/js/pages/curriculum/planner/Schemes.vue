<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    Calendar, User, BookOpen, CheckCircle2, 
    Clock, AlertCircle, FileText, Download, ChevronRight, X, GraduationCap
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    schemes: any[];
    subjects: any[];
    grades: any[];
    terms: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Teaching Plans', href: '/curriculum/planner/schemes' },
];

// Modals State
const showModal = ref(false);
const editingScheme = ref<any>(null);

const form = useForm({
    title: '',
    description: '',
    subject_id: '',
    grade_level_id: '',
    academic_term_id: '',
    total_weeks: 13,
    lessons_per_week: 5,
});

const openModal = (scheme: any = null) => {
    editingScheme.value = scheme;
    if (scheme) {
        form.title = scheme.title;
        form.description = scheme.description;
        form.subject_id = scheme.subject_id;
        form.grade_level_id = scheme.grade_level_id;
        form.academic_term_id = scheme.academic_term_id;
        form.total_weeks = scheme.total_weeks;
        form.lessons_per_week = scheme.lessons_per_week;
    } else {
        form.reset();
        if (props.terms.length > 0) form.academic_term_id = props.terms[0].id;
    }
    showModal.value = true;
};

const submit = () => {
    if (editingScheme.value) {
        form.put(route('curriculum.planner.schemes.update', editingScheme.value.id), {
            onSuccess: () => {
                showModal.value = false;
                editingScheme.value = null;
            },
        });
    } else {
        form.post(route('curriculum.planner.schemes.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const submitForReview = (scheme: any) => {
    if (confirm('Send this plan for approval?')) {
        useForm({}).post(route('curriculum.planner.schemes.submit', scheme.id));
    }
};

const approveScheme = (scheme: any) => {
    if (confirm('Approve this teaching plan?')) {
        useForm({}).post(route('curriculum.planner.schemes.approve', scheme.id));
    }
};

const rejectScheme = (scheme: any) => {
    const feedback = prompt('Provide feedback for revision (optional):');
    if (feedback !== null) {
        useForm({ feedback }).post(route('curriculum.planner.schemes.reject', scheme.id));
    }
};

const deleteScheme = (scheme: any) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        useForm({}).delete(route('curriculum.planner.schemes.destroy', scheme.id));
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'pending': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'draft': return 'bg-slate-50 text-slate-600 border-slate-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

</script>

<template>
    <Head title="Teaching Plans" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Teaching Plans</h1>
                    <p class="text-sm font-medium text-slate-500">Manage your teaching schedule for the term.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-12 rounded-xl font-bold text-[10px] uppercase tracking-wider border-slate-200 shadow-sm bg-white">
                        <Download class="mr-2 h-4 w-4" /> Export All
                    </Button>
                    <Button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 h-12 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md">
                        <Plus class="mr-2 h-4 w-4" /> Create Plan
                    </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div v-for="(count, label) in { 'Total': schemes.length, 'Approved': schemes.filter(s => s.status === 'approved').length, 'Waiting': schemes.filter(s => s.status === 'pending').length, 'Drafts': schemes.filter(s => s.status === 'draft').length }" :key="label" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 group hover:border-blue-100 transition-all hover:shadow-md cursor-default">
                    <span class="text-3xl font-black text-slate-900 group-hover:text-blue-600 transition-colors">{{ count }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ label }}</span>
                </div>
            </div>

            <!-- Schemes List -->
            <div class="grid lg:grid-cols-2 gap-8">
                <div v-for="scheme in schemes" :key="scheme.id" class="group relative p-8 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-start justify-between mb-6">
                        <div class="space-y-3">
                             <div class="flex items-center gap-2">
                                <Badge variant="outline" :class="['text-[9px] font-bold uppercase tracking-wider border-0 px-3 py-1', getStatusBadge(scheme.status)]">
                                    {{ scheme.status }}
                                </Badge>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ scheme.academic_term?.name || 'Current Term' }}</span>
                             </div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight leading-tight transition-colors group-hover:text-blue-600">{{ scheme.title }}</h3>
                            <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
                                <span class="flex items-center gap-1.5 font-bold uppercase tracking-tighter text-[10px]"><BookOpen class="h-3.5 w-3.5 text-blue-500" /> {{ scheme.subject?.name }}</span>
                                <span class="flex items-center gap-1.5 font-bold uppercase tracking-tighter text-[10px] text-slate-400"><GraduationCap class="h-3.5 w-3.5 text-blue-500" /> {{ scheme.grade_level?.name }}</span>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" class="h-10 w-10 rounded-full border border-slate-50 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <MoreVertical class="h-4 w-4 text-slate-400" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="rounded-2xl border-slate-100 shadow-xl p-2 min-w-[160px]">
                                <DropdownMenuItem class="rounded-xl text-xs font-bold py-3">
                                    <Eye class="mr-2 h-3.5 w-3.5" /> View Matrix
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="openModal(scheme)" class="rounded-xl text-xs font-bold py-3">
                                    <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Basic Info
                                </DropdownMenuItem>
                                
                                <template v-if="scheme.status === 'draft'">
                                    <DropdownMenuItem @click="submitForReview(scheme)" class="rounded-xl text-xs font-bold py-3 text-blue-600">
                                        <CheckCircle2 class="mr-2 h-3.5 w-3.5" /> Submit to HOD
                                    </DropdownMenuItem>
                                </template>

                                <template v-if="scheme.status === 'pending'">
                                    <DropdownMenuItem @click="approveScheme(scheme)" class="rounded-xl text-xs font-bold py-3 text-emerald-600">
                                        <CheckCircle2 class="mr-2 h-3.5 w-3.5" /> Approve
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="rejectScheme(scheme)" class="rounded-xl text-xs font-bold py-3 text-red-600">
                                        <AlertCircle class="mr-2 h-3.5 w-3.5" /> Reject
                                    </DropdownMenuItem>
                                </template>

                                <DropdownMenuItem @click="deleteScheme(scheme)" class="rounded-xl text-xs font-bold py-3 text-red-600">
                                    <Trash2 class="mr-2 h-3.5 w-3.5" /> Remove Plan
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col gap-1 hover:bg-white hover:border-blue-100 transition-colors">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Instructional Time</span>
                            <span class="text-xs font-bold text-slate-700">{{ scheme.total_weeks }} Weeks • {{ scheme.lessons_per_week }} Lessons/Wk</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col gap-1 hover:bg-white hover:border-blue-100 transition-colors">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Facilitator</span>
                            <span class="text-xs font-bold text-slate-700 truncate">{{ scheme.prepared_by?.name || 'Assigned Teacher' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <div class="flex -space-x-2 overflow-hidden">
                             <div v-for="i in 3" :key="i" class="inline-block h-8 w-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center">
                                <User class="h-4 w-4 text-slate-400" />
                             </div>
                        </div>
                        <Link :href="`/curriculum/planner/schemes/${scheme.id}`">
                            <Button variant="ghost" class="h-10 px-5 rounded-xl text-[10px] font-bold uppercase tracking-widest text-blue-600 hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100">
                                Open Planner <ChevronRight class="ml-1 h-3.5 w-3.5" />
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Create Placeholder -->
                <div @click="openModal()" class="p-12 rounded-[2rem] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-slate-50 hover:border-blue-200 transition-all cursor-pointer group min-h-[300px]">
                     <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all shadow-sm">
                        <Plus class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-slate-400 group-hover:text-blue-600 transition-all uppercase tracking-tight">Create Plan</h3>
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic font-medium">Add a new teaching schedule</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!schemes.length" class="py-24 text-center rounded-[3rem] bg-slate-50 border-2 border-dashed border-slate-100">
                 <LayoutDashboard class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                 <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No Plans Found</h3>
                 <p class="text-sm text-slate-400 italic font-medium">Start by creating a teaching plan for this term.</p>
                 <Button @click="openModal()" class="mt-8 bg-blue-600 hover:bg-blue-700 px-8 rounded-xl font-bold text-xs uppercase tracking-wider h-12 shadow-md text-white">
                    <Plus class="mr-2 h-4 w-4" /> Start Planning
                 </Button>
            </div>

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[500px] rounded-[2rem] border-slate-100 shadow-2xl">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingScheme ? 'Edit Scheme' : 'New Scheme of Work' }}</DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 italic">
                            {{ editingScheme ? 'Update teaching plan details.' : 'Define the framework for your termly teaching.' }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="grid gap-6 py-4">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Plan Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Mathematics - Term 1 Plan" class="rounded-xl border-slate-100 italic text-sm" required />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Subject</Label>
                                <select v-model="form.subject_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                    <option value="" disabled>Select Subject</option>
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Grade</Label>
                                <select v-model="form.grade_level_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                    <option value="" disabled>Select Grade</option>
                                    <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Academic Term</Label>
                            <select v-model="form.academic_term_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                <option value="" disabled>Select Term</option>
                                <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }} ({{ t.academic_year?.name }})</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Weeks</Label>
                                <Input type="number" v-model="form.total_weeks" class="rounded-xl border-slate-100 text-sm font-bold" required />
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lessons / Week</Label>
                                <Input type="number" v-model="form.lessons_per_week" class="rounded-xl border-slate-100 text-sm font-bold" required />
                            </div>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Notes (Optional)</Label>
                            <Textarea v-model="form.description" placeholder="Any additional context..." class="rounded-xl border-slate-100 italic text-sm min-h-[80px]" />
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-sm text-white h-12 shadow-lg">
                                {{ form.processing ? 'Syncing...' : (editingScheme ? 'Update Plan' : 'Create Plan') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
