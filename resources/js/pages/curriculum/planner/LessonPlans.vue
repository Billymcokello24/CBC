<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    FileText, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    Calendar, User, BookOpen, CheckCircle2, 
    Clock, AlertCircle, ArrowLeft, ChevronRight,
    MapPin, Sparkles, MessageSquare, X
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
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
    plans: any[];
    subjects: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Teaching Plans', href: '/curriculum/planner/schemes' },
    { title: 'Daily Plans', href: '#' },
];

// Modals State
const showModal = ref(false);
const editingPlan = ref<any>(null);

const form = useForm({
    title: '',
    lesson_date: '',
    subject_id: '',
    class_id: 1, // Placeholder
    academic_term_id: 1, // Placeholder
    week_number: '',
    specific_objectives: '',
    learning_outcomes: '',
});

const openModal = (plan: any = null) => {
    editingPlan.value = plan;
    if (plan) {
        form.title = plan.title;
        form.lesson_date = plan.lesson_date;
        form.subject_id = plan.subject_id;
        form.class_id = plan.class_id || 1;
        form.academic_term_id = plan.academic_term_id || 1;
        form.week_number = plan.week_number;
        form.specific_objectives = plan.specific_objectives;
        form.learning_outcomes = plan.learning_outcomes;
    } else {
        form.reset();
    }
    showModal.value = true;
};

const submit = () => {
    if (editingPlan.value) {
        form.put(route('curriculum.planner.lesson-plans.update', editingPlan.value.id), {
            onSuccess: () => {
                showModal.value = false;
                editingPlan.value = null;
            },
        });
    } else {
        form.post(route('curriculum.planner.lesson-plans.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const submitForReview = (plan: any) => {
    if (confirm('Send this plan for approval?')) {
        useForm({}).post(route('curriculum.planner.lesson-plans.submit', plan.id));
    }
};

const approvePlan = (plan: any) => {
    if (confirm('Approve this plan?')) {
        useForm({}).post(route('curriculum.planner.lesson-plans.approve', plan.id));
    }
};

const rejectPlan = (plan: any) => {
    const feedback = prompt('Provide feedback for revision (optional):');
    if (feedback !== null) {
        useForm({ feedback }).post(route('curriculum.planner.lesson-plans.reject', plan.id));
    }
};

const deletePlan = (plan: any) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        useForm({}).delete(route('curriculum.planner.lesson-plans.destroy', plan.id));
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
    <Head title="Daily Plans" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Daily Plans</h1>
                    <p class="text-sm font-medium text-slate-500 font-medium italic">Create and manage your daily teaching plans.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-12 rounded-xl font-bold text-[10px] uppercase tracking-wider border-slate-200 shadow-sm bg-white">
                        <Filter class="mr-2 h-4 w-4" /> Filter Plans
                    </Button>
                    <Button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 h-12 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md">
                        <Plus class="mr-2 h-4 w-4" /> Add Lesson
                    </Button>
                </div>
            </div>

            <!-- Lesson Grid -->
            <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-8">
                <div v-for="plan in plans" :key="plan.id" class="group relative flex flex-col p-8 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 min-h-[380px]">
                    <!-- Card Header -->
                    <div class="relative z-10 flex items-start justify-between mb-6">
                         <div class="space-y-2">
                             <Badge variant="outline" :class="['text-[9px] font-bold uppercase tracking-wider border-0 px-3 py-1', getStatusBadge(plan.status)]">
                                {{ plan.status }}
                             </Badge>
                             <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <Calendar class="h-3 w-3" /> {{ plan.lesson_date || 'Date TBD' }}
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
                                    <Eye class="mr-2 h-3.5 w-3.5" /> Full Plan
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="openModal(plan)" class="rounded-xl text-xs font-bold py-3">
                                    <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Basic Info
                                </DropdownMenuItem>

                                <template v-if="plan.status === 'draft'">
                                    <DropdownMenuItem @click="submitForReview(plan)" class="rounded-xl text-xs font-bold py-3 text-blue-600">
                                        <CheckCircle2 class="mr-2 h-3.5 w-3.5" /> Submit to HOD
                                    </DropdownMenuItem>
                                </template>

                                <template v-if="plan.status === 'pending'">
                                    <DropdownMenuItem @click="approvePlan(plan)" class="rounded-xl text-xs font-bold py-3 text-emerald-600">
                                        <CheckCircle2 class="mr-2 h-3.5 w-3.5" /> Approve
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="rejectPlan(plan)" class="rounded-xl text-xs font-bold py-3 text-red-600">
                                        <AlertCircle class="mr-2 h-3.5 w-3.5" /> Reject
                                    </DropdownMenuItem>
                                </template>

                                <DropdownMenuItem @click="deletePlan(plan)" class="rounded-xl text-xs font-bold py-3 text-red-600">
                                    <Trash2 class="mr-2 h-3.5 w-3.5" /> Remove Plan
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Card Body -->
                    <div class="relative z-10 flex-1 space-y-4">
                        <h3 class="text-xl font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">{{ plan.title }}</h3>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center gap-1.5 border border-slate-100">
                                <MapPin class="h-3 w-3 text-blue-500" /> {{ plan.classroom?.name || 'Assigned Class' }}
                            </span>
                            <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center gap-1.5 border border-slate-100">
                                <BookOpen class="h-3 w-3 text-blue-500" /> {{ plan.subject?.name || 'Assigned Subject' }}
                            </span>
                        </div>
                        <p class="text-[13px] text-slate-500 line-clamp-3 leading-relaxed pt-4 border-t border-slate-50 italic font-medium">
                            {{ plan.specific_objectives || 'No goals specified for this lesson.' }}
                        </p>
                    </div>

                    <!-- Card Footer -->
                    <div class="relative z-10 mt-8 pt-6 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                             <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all border border-slate-100">
                                <User class="h-5 w-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Instructor</span>
                                <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]">{{ plan.teacher?.name || 'Not assigned' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all">
                                <MessageSquare class="h-4 w-4" />
                            </Button>
                            <Button class="h-12 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 font-bold text-[10px] uppercase tracking-widest text-white shadow-md transition-all hover:gap-3">
                                Plan Details <ChevronRight class="ml-1 h-3.5 w-3.5 whitespace-nowrap" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Create Placeholder -->
                <div @click="openModal()" class="p-16 rounded-[2rem] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-slate-50 hover:border-blue-200 transition-all duration-300 cursor-pointer group min-h-[380px]">
                     <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all shadow-sm">
                        <Plus class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-slate-400 group-hover:text-blue-600 transition-all uppercase tracking-tight">New Plan</h3>
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic font-medium">Draft your next teaching strategy</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!plans.length" class="py-24 text-center rounded-[3rem] bg-slate-50 border-2 border-dashed border-slate-100">
                 <FileText class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                 <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No Plans Drafted</h3>
                 <p class="text-sm text-slate-400 italic font-medium">Start by drafting your first plan for a lesson.</p>
                 <Button @click="openModal()" class="mt-8 bg-blue-600 hover:bg-blue-700 px-8 rounded-xl font-bold text-xs uppercase tracking-wider h-12 shadow-md text-white">
                    <Plus class="mr-2 h-4 w-4" /> Start Planning
                 </Button>
            </div>

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[550px] rounded-[2rem] border-slate-100 shadow-2xl">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingPlan ? 'Edit Lesson Plan' : 'New Lesson Plan' }}</DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 italic font-medium">
                            {{ editingPlan ? 'Update the details for this teaching session.' : 'Detail the objectives and content for your upcoming lesson.' }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="grid gap-6 py-4">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-sans">Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Intro to Organic Chemistry" class="rounded-xl border-slate-100 italic text-sm" required />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</Label>
                                <Input type="date" v-model="form.lesson_date" class="rounded-xl border-slate-100 text-sm font-bold" required />
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Subject</Label>
                                <select v-model="form.subject_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                    <option value="" disabled>Select Subject</option>
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Specific Objectives</Label>
                            <Textarea v-model="form.specific_objectives" placeholder="What should students know by the end?" class="rounded-xl border-slate-100 italic text-[13px] min-h-[100px] focus:ring-blue-500" />
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Learning Content / Steps</Label>
                            <Textarea v-model="form.learning_outcomes" placeholder="Outline the lesson flow..." class="rounded-xl border-slate-100 italic text-[13px] min-h-[100px] focus:ring-blue-500" />
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-sm text-white h-12 shadow-lg">
                                {{ form.processing ? 'Syncing...' : (editingPlan ? 'Update Plan' : 'Add Plan') }}
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
