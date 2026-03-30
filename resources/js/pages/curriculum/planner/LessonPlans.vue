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

// Feedback Modal State
const showFeedback = ref(false);
const feedbackType = ref<'success' | 'error' | 'info'>('success');
const feedbackMessage = ref('');

const openFeedback = (type: 'success' | 'error' | 'info', message: string) => {
    feedbackType.value = type;
    feedbackMessage.value = message;
    showFeedback.value = true;
};

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
const assessmentForm = useForm({
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
    assessmentForm.indicators = outcomes.split('\n').filter((l: string) => l.trim().length > 0).map((l: string) => l.replace(/^[-*•]\s+/, ''));
    
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
            openFeedback('success', 'Assessment derived and created successfully!');
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
    useForm({}).post(route('curriculum.planner.lesson-plans.submit', plan.id), {
        onSuccess: () => openFeedback('success', 'Lesson plan submitted for review.')
    });
};

const approvePlan = (plan: any) => {
    useForm({}).post(route('curriculum.planner.lesson-plans.approve', plan.id), {
        onSuccess: () => openFeedback('success', 'Lesson plan approved successfully.')
    });
};

const rejectPlan = (plan: any) => {
    const feedback = prompt('Provide feedback for revision (optional):');
    if (feedback !== null) {
        useForm({ feedback }).post(route('curriculum.planner.lesson-plans.reject', plan.id), {
            onSuccess: () => openFeedback('info', 'Lesson plan returned for revision.')
        });
    }
};

const deletePlan = (plan: any) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        useForm({}).delete(route('curriculum.planner.lesson-plans.destroy', plan.id), {
            onSuccess: () => openFeedback('success', 'Lesson plan removed successfully.')
        });
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
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-medium hover:bg-muted">
                        <Download class="mr-2 h-4 w-4 opacity-70" />
                        Export
                    </Button>
                    <Button
                        @click="openModal()"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Lesson
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
                <div class="p-6 border-b border-border/50 space-y-4">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative w-full md:max-w-md">
                            <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search by lesson title or subject..." class="pl-10 h-11 bg-muted/50 border-border rounded-xl focus:bg-background transition-all" />
                        </div>
                        
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <Button variant="outline" class="h-11 border-border font-semibold px-4 rounded-xl whitespace-nowrap bg-background" @click="showFilters = !showFilters">
                                <Filter class="mr-2 h-4 w-4 opacity-70" /> {{ showFilters ? 'Hide Filters' : 'More Filters' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Filters Drawer -->
                    <div v-if="showFilters" class="grid gap-4 pt-4 md:grid-cols-2 lg:grid-cols-4 animate-in slide-in-from-top-2 duration-200">
                        <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 outline-none appearance-none cursor-pointer hover:bg-muted/50 transition-all shadow-none border">
                            <option value="all">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                        <select v-model="selectedSubjectId" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 outline-none appearance-none cursor-pointer hover:bg-muted/50 transition-all shadow-none border">
                            <option value="all">All Subjects</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="String(subject.id)">{{ subject.name }}</option>
                        </select>
                        <select v-model="selectedClassId" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 outline-none appearance-none cursor-pointer hover:bg-muted/50 transition-all shadow-none border">
                            <option value="all">All Classes</option>
                            <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-muted/30 text-muted-foreground border-b border-border/50">
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Lesson Title</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Strand</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr 
                                v-for="plan in filteredPlans" 
                                :key="plan.id" 
                                class="hover:bg-muted/30 transition-colors group cursor-pointer"
                                @click="openModal(plan)"
                            >
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center font-bold text-blue-600 text-xs shadow-sm">
                                             {{ plan.subject?.name?.[0] }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-foreground">{{ plan.title }}</span>
                                            <span class="text-xs text-muted-foreground font-medium uppercase tracking-wider">{{ plan.subject?.name }} • {{ plan.class?.name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1 text-foreground">
                                            <Calendar class="h-3.5 w-3.5 opacity-50" />
                                            <span>{{ plan.lesson_date }}</span>
                                        </div>
                                        <span class="text-[10px] text-muted-foreground uppercase font-semibold">Period {{ plan.period_number }} • {{ plan.duration_minutes }} Mins</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground font-medium">
                                    <div class="flex flex-col gap-1 max-w-[200px]">
                                        <span class="truncate text-foreground font-semibold uppercase text-[10px]">{{ plan.strand?.name || 'Strand' }}</span>
                                        <span class="truncate text-[10px] italic">{{ plan.sub_strand?.name || 'Sub-Strand' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <Badge variant="outline" class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider" :class="getStatusColor(plan.status)">
                                         {{ plan.status }}
                                     </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl" @click.stop="openModal(plan)"><Eye class="h-4.5 w-4.5 text-muted-foreground" /></Button>
                                        <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl" @click.stop="openModal(plan)"><Edit2 class="h-4.5 w-4.5 text-muted-foreground" /></Button>
                                        
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl" @click.stop><MoreHorizontal class="h-4.5 w-4.5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border">
                                                <DropdownMenuItem class="rounded-lg" @click="openModal(plan)"><Eye class="mr-2 h-4 w-4" /> View Details</DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" @click="openModal(plan)"><Edit2 class="mr-2 h-4 w-4" /> Edit Lesson</DropdownMenuItem>
                                                
                                                <DropdownMenuItem @click="openAssessmentWizard(plan)" class="rounded-lg text-blue-600 font-bold bg-blue-50/50 hover:bg-blue-50">
                                                    <Wand2 class="mr-2 h-4 w-4" /> Derive Assessment
                                                </DropdownMenuItem>

                                                <template v-if="plan.status === 'draft'">
                                                    <DropdownMenuItem @click="submitForReview(plan)" class="rounded-lg text-amber-600">
                                                        <CheckCircle2 class="mr-2 h-4 w-4" /> Submit for Review
                                                    </DropdownMenuItem>
                                                </template>

                                                <template v-if="plan.status === 'pending'">
                                                    <DropdownMenuItem @click="approvePlan(plan)" class="rounded-lg text-emerald-600 font-bold">
                                                        <CheckCircle2 class="mr-2 h-4 w-4" /> Approve Lesson
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="rejectPlan(plan)" class="rounded-lg text-red-600 font-bold">
                                                        <AlertCircle class="mr-2 h-4 w-4" /> Reject Lesson
                                                    </DropdownMenuItem>
                                                </template>
                                                
                                                <DropdownMenuSeparator class="my-1 bg-border/50" />
                                                <DropdownMenuItem class="text-rose-600 rounded-lg group" @click="deletePlan(plan)"><Trash2 class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100" /> Delete Lesson</DropdownMenuItem>
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

        <!-- Lesson Plan Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-[900px] max-h-[90vh] overflow-hidden flex flex-col p-0 rounded-[2rem] border-slate-100 shadow-2xl">
                <DialogHeader class="p-8 pb-0">
                    <div class="flex items-center justify-between pr-8">
                        <div>
                            <DialogTitle class="text-2xl font-bold tracking-tight text-slate-900">{{ editingPlan ? 'Edit Lesson Plan' : 'New Lesson Plan' }}</DialogTitle>
                            <DialogDescription class="text-sm text-slate-500 italic mt-1">
                                {{ editingPlan ? 'Update lesson details and delivery strategy.' : 'Draft a new lesson based on curriculum strands.' }}
                            </DialogDescription>
                        </div>
                        <Badge v-if="editingPlan" variant="outline" :class="['rounded-full px-4 py-1 font-bold text-[10px] uppercase tracking-widest', getStatusColor(editingPlan.status)]">
                            {{ editingPlan.status }}
                        </Badge>
                    </div>
                    
                    <div class="mt-8">
                        <Tabs v-model="currentTab" class="w-full">
                            <TabsList class="bg-slate-50 p-1 rounded-2xl border border-slate-100 w-full justify-start overflow-x-auto no-scrollbar">
                                <TabsTrigger value="administrative" class="rounded-xl px-6 py-2.5 text-xs font-bold uppercase tracking-wider data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm transition-all whitespace-nowrap">1. Administrative</TabsTrigger>
                                <TabsTrigger value="curriculum" class="rounded-xl px-6 py-2.5 text-xs font-bold uppercase tracking-wider data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm transition-all whitespace-nowrap">2. Curriculum & Intent</TabsTrigger>
                                <TabsTrigger value="delivery" class="rounded-xl px-6 py-2.5 text-xs font-bold uppercase tracking-wider data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm transition-all whitespace-nowrap">3. Delivery Steps</TabsTrigger>
                                <TabsTrigger value="reflection" class="rounded-xl px-6 py-2.5 text-xs font-bold uppercase tracking-wider data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm transition-all whitespace-nowrap">4. Reflection & Tools</TabsTrigger>
                            </TabsList>
                        </Tabs>
                    </div>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-8 pt-6 no-scrollbar">
                    <form @submit.prevent="submit" id="lessonPlanForm">
                        <Tabs v-model="currentTab" class="w-full">
                            <TabsContent value="administrative" class="space-y-6 mt-0">
                                <div class="grid gap-3">
                                    <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Lesson Title</Label>
                                    <Input v-model="form.title" placeholder="e.g. Introduction to Fractions using local materials" class="h-14 rounded-2xl border-slate-100 bg-slate-50/30 text-lg font-bold tracking-tight focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all" required />
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Subject</Label>
                                        <select v-model="form.subject_id" class="w-full h-12 rounded-2xl border-slate-100 bg-slate-50/30 px-4 text-xs font-bold uppercase tracking-wider hover:bg-slate-50 transition-all outline-none border focus:ring-4 focus:ring-blue-500/10" required>
                                            <option value="" disabled>Select Subject</option>
                                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Class</Label>
                                        <select v-model="form.class_id" class="w-full h-12 rounded-2xl border-slate-100 bg-slate-50/30 px-4 text-xs font-bold uppercase tracking-wider hover:bg-slate-50 transition-all outline-none border focus:ring-4 focus:ring-blue-500/10" required>
                                            <option value="" disabled>Select Class</option>
                                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-4 gap-6">
                                    <div class="grid gap-3 md:col-span-2">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Lesson Date</Label>
                                        <Input type="date" v-model="form.lesson_date" class="h-12 rounded-2xl border-slate-100 bg-slate-50/30 text-xs font-bold" required />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Period</Label>
                                        <Input type="number" v-model="form.period_number" class="h-12 rounded-2xl border-slate-100 bg-slate-50/30 text-xs font-bold text-center" />
                                    </div>
                                    <div class="grid gap-3">
                                        <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Duration (Mins)</Label>
                                        <Input type="number" v-model="form.duration_minutes" class="h-12 rounded-2xl border-slate-100 bg-slate-50/30 text-xs font-bold text-center" />
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

                <DialogFooter class="p-8 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <Button v-if="currentTab !== 'administrative'" variant="ghost" @click="prevTab" class="rounded-xl px-6 h-12 font-bold text-xs uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Back</Button>
                    </div>
                    <div class="flex items-center gap-3">
                         <Button variant="outline" @click="showModal = false" class="h-12 px-8 rounded-xl border-slate-200 font-bold text-xs uppercase tracking-widest hover:bg-white transition-all shadow-sm">Cancel</Button>
                         
                         <Button v-if="currentTab !== 'reflection'" @click="nextTab" class="bg-slate-900 hover:bg-slate-800 text-white h-12 px-8 rounded-xl font-bold text-xs uppercase tracking-widest shadow-md transition-all">Next Section</Button>
                         
                         <Button v-else form="lessonPlanForm" type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20 transition-all">
                            {{ form.processing ? 'Saving...' : (editingPlan ? 'Update Lesson' : 'Save Lesson Plan') }}
                         </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Assessment Derivation Wizard -->
        <Dialog v-model:open="isCreatingAssessment">
            <DialogContent class="sm:max-w-[700px] p-0 rounded-[2.5rem] border-blue-50 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="bg-blue-600 p-10 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <Badge class="bg-white/20 text-white border-0 px-3 py-1 text-[10px] font-bold uppercase tracking-widest">Step {{ currentAssessmentStep }} of 4</Badge>
                        <Wand2 class="h-6 w-6 text-white/50 animate-pulse" />
                    </div>
                    <h2 class="text-3xl font-black tracking-tight leading-tight">Derive Assessment</h2>
                    <p class="text-blue-100 text-sm mt-2 font-medium opacity-80 italic">Auto-extracting competencies and indicators from your lesson plan.</p>
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

                <div class="p-10 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                    <Button v-if="currentAssessmentStep > 1" @click="prevAssessmentStep" variant="ghost" class="h-12 px-8 rounded-xl font-bold text-xs uppercase tracking-widest text-slate-400 hover:text-slate-600">Back</Button>
                    <div v-else></div>
                    
                    <div class="flex items-center gap-3">
                        <Button variant="outline" @click="isCreatingAssessment = false" class="h-12 px-8 rounded-xl border-slate-200 font-bold text-xs uppercase tracking-widest shadow-sm">Cancel</Button>
                        <Button v-if="currentAssessmentStep < 4" @click="nextAssessmentStep" class="h-12 px-8 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-widest shadow-md">Continue</Button>
                        <Button v-else @click="submitAssessment" :disabled="assessmentForm.processing" class="h-12 px-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20">
                            {{ assessmentForm.processing ? 'Syncing...' : 'Create Assessment' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Dynamic Feedback Modal -->
        <Dialog v-model:open="showFeedback">
            <DialogContent class="sm:max-w-[400px] p-0 overflow-hidden rounded-[2.5rem] border-0 shadow-2xl">
                <div :class="[
                    'p-8 text-center space-y-6',
                    feedbackType === 'success' ? 'bg-emerald-50' : feedbackType === 'error' ? 'bg-rose-50' : 'bg-blue-50'
                ]">
                    <div :class="[
                        'mx-auto h-20 w-20 rounded-full flex items-center justify-center animate-in zoom-in duration-500',
                        feedbackType === 'success' ? 'bg-emerald-100 text-emerald-600' : feedbackType === 'error' ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-600'
                    ]">
                        <CheckCircle2 v-if="feedbackType === 'success'" class="h-10 w-10" />
                        <AlertCircle v-else-if="feedbackType === 'error'" class="h-10 w-10" />
                        <Info v-else class="h-10 w-10" />
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="text-xl font-black tracking-tight text-slate-900">
                            {{ feedbackType === 'success' ? 'Success!' : feedbackType === 'error' ? 'Oops!' : 'Note' }}
                        </h3>
                        <p class="text-sm font-medium text-slate-500 leading-relaxed">
                            {{ feedbackMessage }}
                        </p>
                    </div>

                    <Button 
                        @click="showFeedback = false" 
                        :class="[
                            'w-full h-12 rounded-2xl font-bold text-xs uppercase tracking-widest shadow-lg transition-all active:scale-[0.98]',
                            feedbackType === 'success' ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/20' : 
                            feedbackType === 'error' ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-500/20' : 
                            'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/20'
                        ]"
                    >
                        Continue
                    </Button>
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
