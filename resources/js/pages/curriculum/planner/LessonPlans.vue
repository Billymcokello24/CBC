<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    FileText, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    Calendar, User, BookOpen, CheckCircle2, 
    Clock, AlertCircle, ChevronRight,
    MapPin, Sparkles, MessageSquare, X, GraduationCap,
    ListChecks, BookCopy, Lightbulb, Users
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
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@/components/ui/tabs";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    plans: any[];
    subjects: any[];
    grades: any[];
    classes: any[];
    terms: any[];
    strands: any[];
    sub_strands: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Daily Planner', href: '#' },
];

// Modals State
const showModal = ref(false);
const editingPlan = ref<any>(null);
const currentTab = ref('administrative');
const tabs = ['administrative', 'curriculum', 'delivery', 'reflection'];

const nextTab = () => {
    const currentIndex = tabs.indexOf(currentTab.value);
    if (currentIndex < tabs.length - 1) {
        currentTab.value = tabs[currentIndex + 1];
    }
};

const prevTab = () => {
    const currentIndex = tabs.indexOf(currentTab.value);
    if (currentIndex > 0) {
        currentTab.value = tabs[currentIndex - 1];
    }
};

const form = useForm({
    title: '',
    lesson_date: '',
    subject_id: '',
    class_id: '',
    academic_term_id: '',
    strand_id: '',
    sub_strand_id: '',
    week_number: '',
    period_number: '',
    duration_minutes: 35,
    specific_objectives: '',
    learning_outcomes: '',
    key_vocabulary: '',
    teaching_aids: '',
    references: '',
    introduction: '',
    lesson_development: '',
    teacher_activities: '',
    learner_activities: '',
    conclusion: '',
    assessment_methods: '',
    reflection: '',
    homework: '',
    scheme_entry_id: '',
});

const filteredStrands = computed(() => {
    if (!form.subject_id) return [];
    return props.strands.filter(s => s.subject_id === form.subject_id);
});

const filteredSubStrands = computed(() => {
    if (!form.strand_id) return [];
    return props.sub_strands.filter(s => s.strand_id === form.strand_id);
});

const openModal = (plan: any = null) => {
    editingPlan.value = plan;
    if (plan) {
        form.title = plan.title;
        form.lesson_date = plan.lesson_date ? plan.lesson_date.split('T')[0] : '';
        form.subject_id = plan.subject_id;
        form.class_id = plan.class_id;
        form.academic_term_id = plan.academic_term_id;
        form.strand_id = plan.strand_id;
        form.sub_strand_id = plan.sub_strand_id;
        form.week_number = plan.week_number;
        form.period_number = plan.period_number;
        form.duration_minutes = plan.duration_minutes;
        form.specific_objectives = plan.specific_objectives;
        form.learning_outcomes = plan.learning_outcomes;
        form.key_vocabulary = plan.key_vocabulary;
        form.teaching_aids = plan.teaching_aids;
        form.references = plan.references;
        form.introduction = plan.introduction;
        form.lesson_development = plan.lesson_development;
        form.teacher_activities = plan.teacher_activities;
        form.learner_activities = plan.learner_activities;
        form.conclusion = plan.conclusion;
        form.assessment_methods = plan.assessment_methods;
        form.reflection = plan.reflection;
        form.homework = plan.homework;
        form.scheme_entry_id = plan.scheme_entry_id;
    } else {
        form.reset();
        if (props.terms.length > 0) form.academic_term_id = props.terms[0].id;
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

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const entryId = urlParams.get('scheme_entry_id');

    if (id) {
        const plan = props.plans.find(p => p.id == id);
        if (plan) openModal(plan);
    } else if (entryId) {
        // Find scheme entry in props if possible, or just pre-fill if we have basic info
        // Since we don't have all entries in props, we might need more data.
        // But for now, we'll try to find if any existing plan has this entry_id
        const existingPlan = props.plans.find(p => p.scheme_entry_id == entryId);
        if (existingPlan) {
            openModal(existingPlan);
        } else {
            // Find the scheme entry from the schemes if we had them, 
            // but here we just have partial data.
            // Let's at least set the scheme_entry_id in the form
            form.reset();
            form.scheme_entry_id = entryId;
            
            // Try to find context from the entry if it's passed (maybe we should pass it via state/props)
            // For now, just open the modal with the ID linked.
            showModal.value = true;
        }
    }
});

</script>

<template>
    <Head title="Daily Planner" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Daily Planner</h1>
                    <p class="text-sm font-medium text-slate-500 font-medium italic">Create and manage your CBC-compliant lesson plans.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-12 rounded-xl font-bold text-[10px] uppercase tracking-wider border-slate-200 shadow-sm bg-white">
                        <Filter class="mr-2 h-4 w-4" /> Filter Plans
                    </Button>
                    <Button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 h-12 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md transition-all hover:scale-[1.02]">
                        <Plus class="mr-2 h-4 w-4" /> Add Lesson
                    </Button>
                </div>
            </div>

            <!-- Lesson Grid -->
            <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-8">
                <div v-for="plan in plans" :key="plan.id" class="group relative flex flex-col p-8 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 min-h-[400px]">
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
                                    <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Details
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
                        <div class="space-y-1">
                             <div class="text-[9px] font-black text-blue-500 uppercase tracking-[0.2em]">{{ plan.subject?.name || 'Subject' }}</div>
                             <h3 class="text-xl font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">{{ plan.title }}</h3>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center gap-1.5 border border-slate-100">
                                <MapPin class="h-3 w-3 text-blue-500" /> {{ plan.classroom?.name || 'Class' }}
                            </span>
                            <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center gap-1.5 border border-slate-100">
                                <Clock class="h-3 w-3 text-blue-500" /> {{ plan.duration_minutes }} Min
                            </span>
                        </div>
                        
                        <div v-if="plan.strand" class="p-3 rounded-2xl bg-blue-50/50 border border-blue-100/50">
                            <div class="text-[8px] font-bold text-blue-400 uppercase tracking-widest mb-1">Strand</div>
                            <div class="text-[11px] font-bold text-slate-700 leading-tight">{{ plan.strand?.name }}</div>
                        </div>

                        <p class="text-[13px] text-slate-500 line-clamp-3 leading-relaxed pt-2 italic font-medium">
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
                        
                        <Button variant="ghost" class="h-12 px-6 rounded-2xl text-[10px] font-bold uppercase tracking-widest text-blue-600 hover:bg-blue-50 group-hover:gap-3 transition-all border border-transparent hover:border-blue-100/50 shadow-sm bg-white border-slate-100">
                            View Plan <ChevronRight class="ml-1 h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                        </Button>
                    </div>
                </div>

                <!-- Create Placeholder -->
                <div @click="openModal()" class="p-16 rounded-[2rem] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-white hover:border-blue-200 hover:shadow-xl transition-all duration-300 cursor-pointer group min-h-[400px] bg-slate-50/50">
                     <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center text-slate-300 group-hover:scale-110 group-hover:text-blue-600 transition-all shadow-sm border border-slate-100">
                        <Plus class="h-10 w-10" />
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-slate-400 group-hover:text-blue-600 transition-all uppercase tracking-tight">New Lesson Plan</h3>
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic font-medium">Draft your next CBC teaching strategy</p>
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

            <!-- Enhanced Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[900px] h-[90vh] p-0 rounded-[2rem] overflow-hidden flex flex-col border-0 shadow-2xl bg-white">
                    <div class="p-8 border-b border-slate-50 bg-slate-50/50">
                        <DialogTitle class="text-2xl font-bold tracking-tight text-slate-900">{{ editingPlan ? 'Refine Lesson Plan' : 'Draft New Lesson Plan' }}</DialogTitle>
                        <DialogDescription class="text-sm text-slate-500 font-medium italic">
                            Fill in the details to create a standard CBC lesson plan.
                        </DialogDescription>
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col flex-1 overflow-hidden">
                        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                            <Tabs v-model="currentTab" class="w-full">
                                <TabsList class="grid w-full grid-cols-4 h-14 bg-slate-100/50 rounded-2xl p-1 mb-8 border border-slate-200/50">
                                    <TabsTrigger value="administrative" class="rounded-xl data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm font-bold text-[10px] uppercase tracking-wider">
                                        <Users class="w-3.5 h-3.5 mr-2" /> Admin
                                    </TabsTrigger>
                                    <TabsTrigger value="curriculum" class="rounded-xl data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm font-bold text-[10px] uppercase tracking-wider">
                                        <BookOpen class="w-3.5 h-3.5 mr-2" /> Curriculum
                                    </TabsTrigger>
                                    <TabsTrigger value="delivery" class="rounded-xl data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm font-bold text-[10px] uppercase tracking-wider">
                                        <Sparkles class="w-3.5 h-3.5 mr-2" /> Delivery
                                    </TabsTrigger>
                                    <TabsTrigger value="reflection" class="rounded-xl data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm font-bold text-[10px] uppercase tracking-wider">
                                        <Lightbulb class="w-3.5 h-3.5 mr-2" /> Outcome
                                    </TabsTrigger>
                                </TabsList>

                                <TabsContent value="administrative" class="space-y-6 animate-in slide-in-from-left duration-300">
                                    <div class="grid gap-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Lesson Title</Label>
                                        <Input v-model="form.title" placeholder="e.g. Exploring Different Types of Soils" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white" required />
                                    </div>

                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Date</Label>
                                            <Input type="date" v-model="form.lesson_date" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white" required />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Classroom</Label>
                                            <select v-model="form.class_id" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white px-4 hover:border-blue-400 transition-colors border" required>
                                                <option value="" disabled>Select Class</option>
                                                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                            </select>
                                        </div>
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Subject / Area</Label>
                                            <select v-model="form.subject_id" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white px-4 hover:border-blue-400 transition-colors border" required>
                                                <option value="" disabled>Select Subject</option>
                                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-3 gap-6">
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Term</Label>
                                            <select v-model="form.academic_term_id" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white px-4 border" required>
                                                <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
                                            </select>
                                        </div>
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Week No.</Label>
                                            <Input v-model="form.week_number" placeholder="Week 1" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white" />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Duration (Min)</Label>
                                            <Input type="number" v-model="form.duration_minutes" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white" />
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="curriculum" class="space-y-6 animate-in slide-in-from-left duration-300">
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Strand</Label>
                                            <select v-model="form.strand_id" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white px-4 border" :disabled="!form.subject_id">
                                                <option value="">Select Strand</option>
                                                <option v-for="s in filteredStrands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Sub-Strand</Label>
                                            <select v-model="form.sub_strand_id" class="h-12 rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white px-4 border" :disabled="!form.strand_id">
                                                <option value="">Select Sub-Strand</option>
                                                <option v-for="s in filteredSubStrands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Specific Learning Outcomes</Label>
                                        <Textarea v-model="form.specific_objectives" placeholder="By the end of the lesson, the learner should be able to..." class="rounded-2xl border-slate-200 p-4 text-[13px] font-medium min-h-[120px] focus:ring-blue-500 bg-white shadow-inner" />
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Key Vocabulary</Label>
                                            <Textarea v-model="form.key_vocabulary" placeholder="Key words for the lesson..." class="rounded-xl border-slate-200 min-h-[80px]" />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Learning Resources</Label>
                                            <Textarea v-model="form.teaching_aids" placeholder="Charts, Models, ICT tools..." class="rounded-xl border-slate-200 min-h-[80px]" />
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="delivery" class="space-y-6 animate-in slide-in-from-left duration-300">
                                    <div class="p-6 rounded-3xl bg-blue-50/30 border border-blue-100 flex flex-col gap-6">
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-blue-500">Introduction (5-10 Min)</Label>
                                            <Textarea v-model="form.introduction" placeholder="How will you start the lesson? hook the learners..." class="rounded-2xl border-blue-100/50 min-h-[100px] text-[13px] italic font-medium" />
                                        </div>
                                        
                                        <div class="grid gap-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-blue-500">Lesson Development / Core Activities</Label>
                                            <Textarea v-model="form.lesson_development" placeholder="Detailed steps of the teaching..." class="rounded-2xl border-blue-100/50 min-h-[150px] text-[13px] italic font-medium" />
                                        </div>

                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div class="grid gap-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Teacher Activities</Label>
                                                <Textarea v-model="form.teacher_activities" placeholder="What you will do..." class="rounded-xl border-emerald-100 min-h-[100px] text-xs" />
                                            </div>
                                            <div class="grid gap-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Learner Activities</Label>
                                                <Textarea v-model="form.learner_activities" placeholder="What students will do..." class="rounded-xl border-emerald-100 min-h-[100px] text-xs" />
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="reflection" class="space-y-6 animate-in slide-in-from-left duration-300">
                                     <div class="grid gap-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Conclusion & Summary</Label>
                                        <Textarea v-model="form.conclusion" placeholder="Recap and closing..." class="rounded-xl border-slate-200 min-h-[100px]" />
                                    </div>
                                    
                                    <div class="grid gap-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Assessment Methods</Label>
                                        <Textarea v-model="form.assessment_methods" placeholder="Observation, Oral questions, Portfolio..." class="rounded-xl border-slate-200 min-h-[80px]" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-blue-500">Self-Reflection / Evaluation</Label>
                                        <Textarea v-model="form.reflection" placeholder="How did the lesson go? areas of improvement..." class="rounded-xl border-blue-100 min-h-[100px] shadow-sm bg-blue-50/20" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Extended Activity / Homework</Label>
                                        <Textarea v-model="form.homework" placeholder="Home link activity..." class="rounded-xl border-slate-200 min-h-[80px]" />
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </div>

                        <div class="p-8 border-t border-slate-50 bg-slate-50/50 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <Button type="button" variant="ghost" @click="prevTab" :disabled="currentTab === 'administrative'" class="h-10 px-4 rounded-xl text-[10px] font-bold uppercase tracking-widest">Back</Button>
                                <div class="flex gap-1.5">
                                    <div v-for="t in tabs" :key="t" :class="['w-2 h-2 rounded-full transition-all', currentTab === t ? 'bg-blue-600 w-4' : 'bg-slate-300']"></div>
                                </div>
                                <Button type="button" variant="ghost" @click="nextTab" :disabled="currentTab === 'reflection'" class="h-10 px-4 rounded-xl text-[10px] font-bold uppercase tracking-widest">Next</Button>
                            </div>
                            
                            <Button type="submit" :disabled="form.processing" class="h-14 px-12 bg-blue-600 hover:bg-blue-700 rounded-2xl font-bold text-xs uppercase tracking-[0.1em] text-white shadow-xl transition-all hover:scale-[1.03] active:scale-[0.98]">
                                {{ form.processing ? 'Syncing...' : (editingPlan ? 'Update Final Plan' : 'Save & Publish Plan') }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
