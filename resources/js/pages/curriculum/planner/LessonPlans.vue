<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    FileText, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    Calendar, User, BookOpen, CheckCircle2, 
    Clock, AlertCircle, ChevronRight,
    ListChecks, BookCopy, Lightbulb, Users,
    Wand2, ClipboardCheck, Sparkles, Check, Info, Trash,
    MapPin, MoreHorizontal, CheckSquare, Square
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
    DropdownMenuSeparator,
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
    assessmentTypes: any[];
    rubrics: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Daily Planner', href: '#' },
];

// Filtering State
const searchQuery = ref('');
const showFilters = ref(true);
const selectedSubjectId = ref('all');
const selectedClassId = ref('all');
const selectedStatus = ref('all');

const filteredPlans = computed(() => {
    return props.plans.filter(plan => {
        const matchesSearch = plan.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            plan.subject?.name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = selectedSubjectId.value === 'all' || plan.subject_id == selectedSubjectId.value;
        const matchesClass = selectedClassId.value === 'all' || plan.class_id == selectedClassId.value;
        const matchesStatus = selectedStatus.value === 'all' || plan.status === selectedStatus.value;
        
        return matchesSearch && matchesSubject && matchesClass && matchesStatus;
    });
});

const stats = computed(() => ({
    total: props.plans.length,
    approved: props.plans.filter(p => p.status === 'approved').length,
    pending: props.plans.filter(p => p.status === 'pending').length,
    drafts: props.plans.filter(p => p.status === 'draft').length,
}));

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

const isCreatingAssessment = ref(false);
const currentAssessmentStep = ref(1);
const assessmentForm = useForm<{
    title: string;
    description: string;
    class_id: string | number;
    subject_id: string | number;
    rubric_id: string | number;
    assessment_type_id: string | number;
    assessment_date: string;
    total_marks: number;
    passing_marks: number;
    weight: number;
    status: string;
    lesson_plan_id: string | number;
    sub_strand_id: string | number;
    indicators: string[];
    competencies: string[];
}>({
    title: '',
    description: '',
    class_id: '',
    subject_id: '',
    rubric_id: '',
    assessment_type_id: '',
    assessment_date: '',
    total_marks: 10,
    passing_marks: 5,
    weight: 10,
    status: 'draft',
    lesson_plan_id: '',
    sub_strand_id: '',
    indicators: [],
    competencies: [],
});

const openAssessmentWizard = (plan: any) => {
    assessmentForm.reset();
    assessmentForm.title = `Assessment: ${plan.title}`;
    assessmentForm.class_id = plan.class_id;
    assessmentForm.subject_id = plan.subject_id;
    assessmentForm.lesson_plan_id = plan.id;
    assessmentForm.sub_strand_id = plan.sub_strand_id;
    assessmentForm.assessment_date = new Date().toISOString().split('T')[0];
    
    // Auto-extract indicators from outcomes (simple split by newline or bullet points)
    const outcomes = (plan.learning_outcomes as string) || '';
    assessmentForm.indicators = outcomes.split('\n')
        .map((l: string) => l.trim())
        .filter((l: string) => l.length > 0)
        .map((l: string) => l.replace(/^[-*•]\s+/, ''));
    
    // Mapping competencies from lesson
    assessmentForm.competencies = plan.core_competencies || [];
    
    assessmentForm.description = `Assessment based on lesson: ${plan.title}`;
    
    currentAssessmentStep.value = 1;
    isCreatingAssessment.value = true;
};

const nextAssessmentStep = () => {
    if (currentAssessmentStep.value < 4) currentAssessmentStep.value++;
};

const prevAssessmentStep = () => {
    if (currentAssessmentStep.value > 1) currentAssessmentStep.value--;
};

const removeIndicator = (index: number) => {
    assessmentForm.indicators.splice(index, 1);
};

const submitAssessment = () => {
    assessmentForm.post(route('assessments.store'), {
        onSuccess: () => {
            isCreatingAssessment.value = false;
            alert('Assessment derived and created successfully!');
        }
    });
};

const filteredRubrics = computed(() => {
    if (!assessmentForm.subject_id) return props.rubrics;
    return props.rubrics.filter(r => r.subject_id === assessmentForm.subject_id || !r.subject_id);
});

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

const getStatusColor = (status: string) => {
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
    <Head title="Daily Lesson Planner" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Daily Planner</h1>
                    <p class="text-[15px] text-muted-foreground">
                        Draft lesson executions and derive assessments from your schemes.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-11 px-6 rounded-xl border-border font-bold text-[10px] uppercase tracking-widest hover:bg-muted transition-all bg-background shadow-sm">
                        <Download class="mr-2.5 h-4 w-4 opacity-60" /> Export
                    </Button>
                    <Button
                        @click="openModal()"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-xl shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95"
                    >
                        <Plus class="mr-2.5 h-4 w-4" /> New Lesson
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Lessons</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.total.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <FileText class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Taught / Approved</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.approved.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                         <CheckCircle2 class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Pending Review</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.pending.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <Clock class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Drafts</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.drafts.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600">
                        <Edit2 class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden">
                <!-- Toolbar -->
                <div class="p-8 border-b border-border/50 space-y-6 bg-muted/5">
                    <div class="flex flex-col md:flex-row gap-6 items-center justify-between">
                        <div class="relative w-full md:max-w-xl group">
                            <Search class="absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-blue-600" />
                            <Input v-model="searchQuery" placeholder="Search lessons by title, subject or context..." class="pl-11 h-12 bg-background border-border/60 rounded-2xl focus:ring-4 focus:ring-blue-600/5 transition-all text-sm font-medium shadow-sm" />
                        </div>
                        
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <Button variant="outline" class="h-12 px-6 rounded-2xl border-border font-bold text-[10px] uppercase tracking-widest bg-background hover:bg-muted/50 shadow-sm" @click="showFilters = !showFilters">
                                <Filter class="mr-2.5 h-4 w-4 opacity-70" /> {{ showFilters ? 'Hide Engine' : 'Filter Logic' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Filters Engine -->
                    <div v-if="showFilters" class="grid gap-4 pt-2 md:grid-cols-3 animate-in slide-in-from-top-4 duration-300">
                        <div class="space-y-2">
                             <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Sequence Status</Label>
                             <select v-model="selectedStatus" class="h-12 w-full rounded-2xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">Global Status</option>
                                <option value="draft">Draft Protocol</option>
                                <option value="pending">Awaiting Review</option>
                                <option value="approved">Operational</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Subject Discipline</Label>
                             <select v-model="selectedSubjectId" class="h-12 w-full rounded-2xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">All Disciplines</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="String(subject.id)">{{ subject.name }}</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Class Context</Label>
                             <select v-model="selectedClassId" class="h-12 w-full rounded-2xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">Global Reach</option>
                                <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                             </select>
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-muted/10 text-muted-foreground border-b border-border/40">
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Lesson Sequence</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Execution Date</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Instructional Focus</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr 
                                v-for="plan in filteredPlans" 
                                :key="plan.id" 
                                class="hover:bg-muted/20 transition-all group cursor-pointer relative"
                                @click="openModal(plan)"
                            >
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="h-12 w-12 flex-shrink-0 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center transition-all group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/30">
                                             <FileText class="h-6 w-6" />
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[15px] font-black text-foreground tracking-tight group-hover:text-blue-600 transition-colors">{{ plan.title }}</span>
                                            <div class="flex items-center gap-2">
                                                <Badge variant="outline" class="text-[8px] font-black bg-muted/50 rounded-md py-0 px-2 uppercase tracking-widest border-border/50">{{ plan.subject?.name }}</Badge>
                                                <span class="text-[10px] text-muted-foreground font-bold italic opacity-60">{{ plan.class?.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-[13px] font-bold text-foreground">
                                            <Calendar class="h-3.5 w-3.5 text-blue-500/70" />
                                            {{ plan.lesson_date }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase tracking-widest opacity-60 ml-0.5">
                                            <Clock class="h-3 w-3" />
                                            Period {{ plan.period_number }} • {{ plan.duration_minutes }} MINS
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1 max-w-[200px]">
                                        <span class="truncate text-foreground font-black uppercase text-[10px] tracking-tight">{{ plan.strand?.name || 'Protocol Strand' }}</span>
                                        <span class="truncate text-[10px] italic font-medium opacity-60">{{ plan.sub_strand?.name || 'Sub-Strand' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                     <Badge variant="outline" :class="[getStatusColor(plan.status), 'rounded-xl px-4 py-1.5 text-[9px] font-black uppercase tracking-[0.2em] border-2 shadow-sm flex items-center gap-2 w-fit transform transition-all group-hover:scale-105']">
                                         {{ plan.status }}
                                     </Badge>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                                        <Button variant="ghost" size="icon" class="h-10 w-10 rounded-2xl hover:bg-muted font-black border border-border/20 transition-all" @click.stop="openModal(plan)"><Eye class="h-5 w-5" /></Button>
                                        
                                        <DropdownMenu @click.stop>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-10 w-10 rounded-2xl hover:bg-muted font-black border border-border/20 transition-all"><MoreHorizontal class="h-5 w-5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-64 p-2 rounded-2xl border border-border shadow-2xl backdrop-blur-xl bg-card/95">
                                                 <div class="px-3 py-2.5 mb-1 border-b border-border/10">
                                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">Deployment Options</p>
                                                </div>
                                                <DropdownMenuItem class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider group" @click="openModal(plan)"><Eye class="mr-3 h-4 w-4 opacity-60 transition-colors group-hover:text-blue-600" /> Operational Details</DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider group" @click="openModal(plan)"><Edit2 class="mr-3 h-4 w-4 opacity-60 transition-colors group-hover:text-amber-600" /> Adjust Strategy</DropdownMenuItem>
                                                
                                                <DropdownMenuItem @click="openAssessmentWizard(plan)" class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider group text-blue-600 bg-blue-50/50 hover:bg-blue-600 hover:text-white transition-all">
                                                    <Wand2 class="mr-3 h-4 w-4" /> Derive Assessment
                                                </DropdownMenuItem>

                                                <template v-if="plan.status === 'draft'">
                                                    <DropdownMenuItem @click="submitForReview(plan)" class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider text-amber-600 hover:bg-amber-600 hover:text-white transition-all">
                                                        <CheckCircle2 class="mr-3 h-4 w-4" /> Finalize Deployment
                                                    </DropdownMenuItem>
                                                </template>

                                                <template v-if="plan.status === 'pending'">
                                                    <DropdownMenuItem @click="approvePlan(plan)" class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all">
                                                        <CheckCircle2 class="mr-3 h-4 w-4" /> Approve Execution
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="rejectPlan(plan)" class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                                        <AlertCircle class="mr-3 h-4 w-4" /> Reject Strategy
                                                    </DropdownMenuItem>
                                                </template>
                                                
                                                <DropdownMenuSeparator class="my-1.5 bg-border/5" />
                                                <DropdownMenuItem class="text-rose-600 rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider hover:bg-rose-600 hover:text-white transition-colors" @click="deletePlan(plan)"><Trash2 class="mr-3 h-4 w-4" /> Purge Plan</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredPlans.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center text-muted-foreground italic font-medium">
                                    No lesson plans found matching your criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-[900px] max-h-[92vh] overflow-hidden flex flex-col p-0 rounded-2xl border-border bg-card shadow-2xl">
                <DialogHeader class="p-10 pb-0">
                    <div class="flex items-center justify-between pr-8">
                        <div>
                            <DialogTitle class="text-3xl font-black tracking-tight text-foreground">{{ editingPlan ? 'Refine Lesson Execution' : 'Initialize Lesson Plan' }}</DialogTitle>
                            <DialogDescription class="text-[10px] text-muted-foreground font-black uppercase tracking-[0.2em] mt-1 opacity-70">
                                {{ editingPlan ? 'Strategic Adjustment of Operational Delivery' : 'Curriculum-Aligned Instructional Provisioning' }}
                            </DialogDescription>
                        </div>
                        <Badge v-if="editingPlan" variant="outline" :class="['rounded-full px-4 py-1 font-bold text-[10px] uppercase tracking-widest', getStatusColor(editingPlan.status)]">
                            {{ editingPlan.status }}
                        </Badge>
                    </div>
                    
                    <div class="mt-8">
                        <Tabs v-model="currentTab" class="w-full">
                            <TabsList class="bg-muted/20 p-1.5 rounded-2xl border border-border/40 w-full justify-start overflow-x-auto no-scrollbar backdrop-blur-sm">
                                <TabsTrigger value="administrative" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10 transition-all whitespace-nowrap">1. Administration</TabsTrigger>
                                <TabsTrigger value="curriculum" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10 transition-all whitespace-nowrap">2. Curriculum Focus</TabsTrigger>
                                <TabsTrigger value="delivery" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10 transition-all whitespace-nowrap">3. Instructional Steps</TabsTrigger>
                                <TabsTrigger value="reflection" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:text-blue-600 data-[state=active]:shadow-lg data-[state=active]:shadow-blue-600/10 transition-all whitespace-nowrap">4. Analysis & Tools</TabsTrigger>
                            </TabsList>
                        </Tabs>
                    </div>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-8 pt-6 no-scrollbar">
                    <form @submit.prevent="submit" id="lessonPlanForm">
                        <Tabs v-model="currentTab" class="w-full">
                             <TabsContent value="administrative" class="space-y-8 mt-0 animate-in fade-in slide-in-from-right-4 duration-500">
                                <div class="grid gap-3">
                                    <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Operational Title</Label>
                                    <Input v-model="form.title" placeholder="e.g. Introduction to Fractions using local materials" class="h-14 rounded-2xl border-border/60 bg-muted/20 text-lg font-black focus:bg-background focus:ring-4 focus:ring-blue-600/5 transition-all px-6 shadow-inner" required />
                                </div>

                                <div class="grid md:grid-cols-2 gap-8">
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Subject Discipline</Label>
                                        <select v-model="form.subject_id" class="w-full h-12 rounded-2xl border-border/60 bg-muted/20 px-5 text-xs font-bold uppercase tracking-wider hover:bg-background transition-all outline-none border focus:ring-4 focus:ring-blue-600/5 shadow-sm appearance-none cursor-pointer" required>
                                            <option value="" disabled>Select Discipline</option>
                                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Class Context</Label>
                                        <select v-model="form.class_id" class="w-full h-12 rounded-2xl border-border/60 bg-muted/20 px-5 text-xs font-bold uppercase tracking-wider hover:bg-background transition-all outline-none border focus:ring-4 focus:ring-blue-600/5 shadow-sm appearance-none cursor-pointer" required>
                                            <option value="" disabled>Select Context</option>
                                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-4 gap-8">
                                    <div class="grid gap-3 md:col-span-2">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Execution Date</Label>
                                        <Input type="date" v-model="form.lesson_date" class="h-12 rounded-2xl border-border/60 bg-muted/20 text-xs font-bold px-5" required />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Sequence Period</Label>
                                        <Input type="number" v-model="form.period_number" class="h-12 rounded-2xl border-border/60 bg-muted/20 text-xs font-bold text-center" />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Intensity (Mins)</Label>
                                        <Input type="number" v-model="form.duration_minutes" class="h-12 rounded-2xl border-border/60 bg-muted/20 text-xs font-bold text-center" />
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent value="curriculum" class="space-y-6 mt-0">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Strand</Label>
                                        <select v-model="form.strand_id" class="w-full h-12 rounded-2xl border-slate-100 bg-slate-50/30 px-4 text-xs font-bold uppercase tracking-wider hover:bg-slate-50 transition-all outline-none border focus:ring-4 focus:ring-blue-500/10" required>
                                            <option value="" disabled>Select Strand</option>
                                            <option v-for="s in filteredStrands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Sub-Strand</Label>
                                        <select v-model="form.sub_strand_id" class="w-full h-12 rounded-2xl border-slate-100 bg-slate-50/30 px-4 text-xs font-bold uppercase tracking-wider hover:bg-slate-50 transition-all outline-none border focus:ring-4 focus:ring-blue-500/10" required>
                                            <option value="" disabled>Select Sub-Strand</option>
                                            <option v-for="s in filteredSubStrands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-3">
                                    <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Learning Outcomes</Label>
                                    <Textarea v-model="form.learning_outcomes" placeholder="By the end of the lesson, the learner should be able to..." class="rounded-2xl border-slate-100 bg-slate-50/30 min-h-[120px] text-sm italic py-4" required />
                                </div>

                                <div class="grid gap-3">
                                    <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Key Vocabulary</Label>
                                    <Input v-model="form.key_vocabulary" placeholder="e.g. Numerator, Denominator, Vinculum" class="h-12 rounded-2xl border-slate-100 bg-slate-50/30 text-xs font-bold" />
                                </div>
                            </TabsContent>

                            <TabsContent value="delivery" class="space-y-6 mt-0">
                                <div class="grid gap-4">
                                     <div class="p-6 rounded-3xl bg-blue-50/50 border border-blue-100/50">
                                        <h4 class="text-xs font-black uppercase tracking-[0.2em] text-blue-600 mb-4 flex items-center gap-2">
                                            <Lightbulb class="h-4 w-4" /> Lesson Introduction
                                        </h4>
                                        <Textarea v-model="form.introduction" placeholder="Hook the learners. Revise previous knowledge..." class="rounded-2xl border-0 bg-white/80 min-h-[80px] text-sm italic shadow-sm" />
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div class="p-6 rounded-3xl bg-emerald-50/50 border border-emerald-100/50">
                                            <h4 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600 mb-4 flex items-center gap-2">
                                                <User class="h-4 w-4" /> Teacher Activities
                                            </h4>
                                            <Textarea v-model="form.teacher_activities" placeholder="Demonstrating, asking questions..." class="rounded-2xl border-0 bg-white/80 min-h-[100px] text-sm italic shadow-sm" />
                                        </div>
                                        <div class="p-6 rounded-3xl bg-amber-50/50 border border-amber-100/50">
                                            <h4 class="text-xs font-black uppercase tracking-[0.2em] text-amber-600 mb-4 flex items-center gap-2">
                                                <Users class="h-4 w-4" /> Learner Activities
                                            </h4>
                                            <Textarea v-model="form.learner_activities" placeholder="Pair work, discussion, practicals..." class="rounded-2xl border-0 bg-white/80 min-h-[100px] text-sm italic shadow-sm" />
                                        </div>
                                    </div>

                                    <div class="p-6 rounded-3xl bg-slate-50/50 border border-slate-100/50">
                                        <h4 class="text-xs font-black uppercase tracking-[0.2em] text-slate-600 mb-4 flex items-center gap-2">
                                            <CheckCircle2 class="h-4 w-4" /> Conclusion & Wrap-up
                                        </h4>
                                        <Textarea v-model="form.conclusion" placeholder="Summary, clean up, exit ticket..." class="rounded-2xl border-0 bg-white/80 min-h-[80px] text-sm italic shadow-sm" />
                                    </div>
                                </div>
                            </TabsContent>

                            <TabsContent value="reflection" class="space-y-6 mt-0">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Teaching Aids</Label>
                                        <Textarea v-model="form.teaching_aids" placeholder="Charts, digital content, realia..." class="rounded-2xl border-slate-100 bg-slate-50/30 min-h-[100px] text-sm italic" />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Assessment Methods</Label>
                                        <Textarea v-model="form.assessment_methods" placeholder="Observation, oral testing, written work..." class="rounded-2xl border-slate-100 bg-slate-50/30 min-h-[100px] text-sm italic" />
                                    </div>
                                </div>

                                <div class="p-6 rounded-3xl bg-rose-50/30 border border-rose-100/30">
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-rose-600 mb-4 flex items-center gap-2">
                                        <Sparkles class="h-4 w-4" /> Professional Reflection
                                    </h4>
                                    <Textarea v-model="form.reflection" placeholder="What went well? What would you change next time?" class="rounded-2xl border-slate-100 bg-white min-h-[100px] text-sm italic" />
                                </div>
                            </TabsContent>
                        </Tabs>
                    </form>
                </div>

                <DialogFooter class="p-10 bg-muted/5 border-t border-border/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <Button v-if="currentTab !== 'administrative'" variant="ghost" @click="prevTab" class="rounded-xl px-8 h-12 font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-foreground transition-all">Back</Button>
                    </div>
                    <div class="flex items-center gap-3">
                         <Button variant="ghost" @click="showModal = false" class="h-12 px-8 rounded-xl font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-foreground transition-all">Cancel Request</Button>
                         
                         <Button v-if="currentTab !== 'reflection'" @click="nextTab" class="bg-slate-900 border border-slate-800 hover:bg-black text-white h-12 px-8 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl transition-all active:scale-95">Proceed Next</Button>
                         
                         <Button v-else form="lessonPlanForm" type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-blue-600/20 transition-all active:scale-95 flex items-center gap-2">
                            <Sparkles class="h-3.5 w-3.5" />
                            {{ form.processing ? 'ENGINEERING...' : (editingPlan ? 'Commit Update' : 'Initialize Plan') }}
                         </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isCreatingAssessment">
            <DialogContent class="sm:max-w-[700px] p-0 rounded-2xl border-border shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
                <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-700 p-10 text-white relative">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <Badge class="bg-white/20 text-white border-none rounded-lg px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em]">Matrix Step {{ currentAssessmentStep }} / 4</Badge>
                            <Wand2 class="h-8 w-8 text-white/50 animate-pulse" />
                        </div>
                        <h2 class="text-3xl font-black tracking-tight leading-tight uppercase">Derive Assessment</h2>
                        <p class="text-blue-100 text-sm mt-3 font-semibold italic opacity-80 leading-relaxed max-w-[500px]">Strategic auto-extraction of competencies and indicators from operational lesson protocols.</p>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-10 no-scrollbar">
                    <div v-if="currentAssessmentStep === 1" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Assessment Title</Label>
                            <Input v-model="assessmentForm.title" class="h-14 rounded-2xl border-slate-100 text-lg font-bold tracking-tight focus:ring-4 focus:ring-blue-500/10" />
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="grid gap-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Assessment Type</Label>
                                <select v-model="assessmentForm.assessment_type_id" class="h-12 px-4 rounded-xl border border-slate-100 bg-slate-50/50 text-xs font-bold uppercase tracking-wider outline-none appearance-none cursor-pointer">
                                    <option v-for="type in assessmentTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                            <div class="grid gap-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Scheduled Date</Label>
                                <Input type="date" v-model="assessmentForm.assessment_date" class="h-12 rounded-xl border-slate-100 text-xs font-bold" />
                            </div>
                        </div>
                    </div>

                    <div v-if="currentAssessmentStep === 2" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Extracted Indicators</h3>
                                <p class="text-xs text-slate-400 font-medium italic">Learner outcomes mapped to assessment goals.</p>
                            </div>
                            <Button variant="ghost" size="sm" @click="assessmentForm.indicators.push('')" class="h-8 rounded-full bg-blue-50 text-blue-600 font-bold px-4 text-[10px] uppercase"><Plus class="h-3 w-3 mr-1" /> Add</Button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(indicator, idx) in assessmentForm.indicators" :key="idx" class="flex items-center gap-3 group">
                                <span class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-400 italic">{{ idx + 1 }}</span>
                                <Input v-model="assessmentForm.indicators[idx]" class="h-12 rounded-xl border-slate-100 text-xs font-medium italic group-hover:border-blue-200 transition-all shadow-none" />
                                <Button variant="ghost" size="icon" @click="removeIndicator(idx)" class="h-10 w-10 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"><Trash2 class="h-4 w-4" /></Button>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentAssessmentStep === 3" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                         <div class="space-y-1 mb-6">
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">Core Competencies</h3>
                            <p class="text-xs text-slate-400 font-medium italic">Strategic CBC areas addressed in this lesson.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="comp in ['Communication & Collaboration', 'Self-efficacy', 'Critical Thinking', 'Creativity', 'Digital Literacy', 'Learning to Learn', 'Citizenship']" 
                                 :key="comp" 
                                 @click="assessmentForm.competencies.includes(comp) ? assessmentForm.competencies = assessmentForm.competencies.filter(c => c !== comp) : assessmentForm.competencies.push(comp)"
                                 :class="['p-4 rounded-2xl border-2 transition-all cursor-pointer flex items-center gap-3 group', assessmentForm.competencies.includes(comp) ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-50 bg-slate-50/30 hover:border-slate-200']"
                            >
                                <div :class="['h-5 w-5 rounded-md flex items-center justify-center transition-all', assessmentForm.competencies.includes(comp) ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200 group-hover:border-slate-400']">
                                    <Check v-if="assessmentForm.competencies.includes(comp)" class="h-3 w-3" />
                                </div>
                                <span :class="['text-[11px] font-bold uppercase tracking-tight', assessmentForm.competencies.includes(comp) ? 'text-emerald-700' : 'text-slate-500']">{{ comp }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentAssessmentStep === 4" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="p-8 rounded-[2rem] bg-blue-50/50 border border-blue-100 flex flex-col items-center justify-center text-center space-y-4">
                            <div class="h-16 w-16 rounded-full bg-blue-600/10 flex items-center justify-center">
                                <ClipboardCheck class="h-8 w-8 text-blue-600" />
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-slate-900 tracking-tight">Grading Configuration</h4>
                                <p class="text-sm text-slate-500 italic mt-1 px-4">Success! Your assessment is configured. Select a rubric to finalize.</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Standardized Rubric</Label>
                            <select v-model="assessmentForm.rubric_id" class="h-12 w-full px-4 rounded-xl border border-slate-100 bg-slate-50/50 text-xs font-bold uppercase tracking-wider outline-none border focus:ring-4 focus:ring-blue-500/10">
                                <option value="" disabled>Select Rubric</option>
                                <option v-for="rubric in rubrics" :key="rubric.id" :value="rubric.id">{{ rubric.title }}</option>
                            </select>
                            <p class="text-[9px] text-slate-400 italic font-medium px-1 flex items-center gap-1"><Info class="h-3 w-3" /> This rubric will set the standard achievement levels for your learners.</p>
                        </div>
                    </div>
                </div>

                <div class="p-10 bg-muted/20 border-t border-border/30 flex items-center justify-between">
                    <Button v-if="currentAssessmentStep > 1" @click="prevAssessmentStep" variant="ghost" class="h-12 px-8 rounded-xl font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-foreground">Backtrack</Button>
                    <div v-else></div>

                    <div class="flex items-center gap-3">
                        <Button variant="ghost" @click="isCreatingAssessment = false" class="h-12 px-8 rounded-xl font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-foreground">Discard Wizard</Button>
                        
                        <Button v-if="currentAssessmentStep < 4" @click="nextAssessmentStep" class="bg-slate-900 border border-slate-800 hover:bg-black text-white h-12 px-10 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl transition-all active:scale-95">Advance Matrix</Button>
                        
                        <Button v-else @click="submitAssessment" :disabled="assessmentForm.processing" class="bg-blue-600 hover:bg-blue-700 text-white h-12 px-12 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-blue-600/20 transition-all active:scale-95 flex items-center gap-2">
                             <Sparkles class="h-4 w-4" /> 
                             {{ assessmentForm.processing ? 'GENESYS SYNCING...' : 'INITIALIZE ASSESSMENT' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
