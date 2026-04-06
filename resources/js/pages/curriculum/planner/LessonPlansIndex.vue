<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    Layers, BookOpen, ChevronRight, School,
    CheckCircle2, Users, Calendar, Clock, Edit2, FileText,
    Plus, BookCopy, Filter, Search, Info, Check, Trash,
    Wand2, Sparkles, Lightbulb, ListChecks, ArrowLeft,
    Download, Upload, X, AlertCircle, Loader2
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    grades: any[];
    stats: {
        total_plans: number;
        approved_plans: number;
        pending_plans: number;
        draft_plans: number;
    };
    allGrades: any[];
    subjects: any[];
    classes: any[];
    terms: any[];
    strands: any[];
    sub_strands: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
];

const getCategoryColor = (category: string) => {
    switch (category) {
        case 'Pre-Primary': return 'bg-pink-50 text-pink-600 border-pink-100';
        case 'Lower Primary': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Upper Primary': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'Junior Secondary': return 'bg-violet-50 text-violet-600 border-violet-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

// Modals State
const showModal = ref(false);
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
    number_of_learners: 40,
    learning_outcomes: '',
    key_vocabulary: '',
    core_competencies: [] as string[],
    values: [] as string[],
    life_skills: [] as string[],
    teaching_aids: '',
    references: '',
    introduction: '',
    learning_activities: [''] as string[], 
    conclusion: '',
    assessment_methods: '',
    reflection: '',
    homework: '',
    scheme_entry_id: '',
});

// Dynamic filtering for modal
const filteredStrands = computed(() => {
    if (!form.subject_id) return [];
    return props.strands.filter(s => s.subject_id == form.subject_id);
});

const filteredSubStrands = computed(() => {
    if (!form.strand_id) return [];
    return props.sub_strands.filter(s => s.strand_id == form.strand_id);
});

const filteredClasses = computed(() => {
    // Optionally filter classes by grade if we add a grade selector, 
    // but for now we list all.
    return props.classes;
});

const openModal = () => {
    form.reset();
    if (props.terms.length > 0) form.academic_term_id = props.terms[0].id;
    currentTab.value = 'administrative';
    showModal.value = true;
};

const submit = () => {
    form.post(route('curriculum.planner.lesson-plans.store'), {
        onSuccess: () => {
            showModal.value = false;
        },
    });
};

const showBulkModal = ref(false);
const bulkForm = useForm({
    file: null as File | null,
    class_id: '',
    subject_id: '',
});

const handleBulkUpload = () => {
    bulkForm.post(route('curriculum.planner.lesson-plans.bulk-upload'), {
        onSuccess: () => {
            showBulkModal.value = false;
            bulkForm.reset();
        }
    });
};

const addActivity = () => {
    form.learning_activities.push('');
};

const removeActivity = (index: number) => {
    form.learning_activities.splice(index, 1);
};

const route = (window as any).route;

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        bulkForm.file = input.files[0];
    }
};

const currentStepIndex = computed(() => tabs.indexOf(currentTab.value) + 1);

const dragOver = ref(false);
const handleDrop = (event: DragEvent) => {
    dragOver.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        bulkForm.file = event.dataTransfer.files[0];
    }
};

const removeFile = () => {
    bulkForm.file = null;
};


</script>

<template>
    <Head title="Lesson Plans Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-border/40">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground flex items-center gap-3">
                        Lesson Plans
                        <span class="text-muted-foreground/30 font-thin text-xl">Overview</span>
                    </h1>
                    <p class="text-[14px] font-medium text-muted-foreground">
                        Strategic overview of instructional planning across all grade levels.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button
                        @click="openModal()"
                        class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 border-0"
                    >
                        <Plus class="mr-2.5 h-4 w-4" /> New Lesson
                    </Button>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm overflow-hidden relative group transition-all hover:shadow-xl hover:-translate-y-1 duration-500">
                <div class="absolute -right-10 -bottom-10 opacity-[0.03] group-hover:scale-110 transition-transform duration-1000 text-indigo-600">
                    <BookCopy class="h-64 w-64" />
                </div>
                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between relative z-10">
                    <div class="flex items-center gap-6">
                        <div class="h-20 w-20 rounded-[2rem] bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-100/50 group-hover:rotate-6 transition-transform">
                            <Upload class="h-10 w-10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Bulk Planning Hub</h2>
                                <Badge variant="secondary" class="text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border-emerald-100/50 px-2 py-0.5">Automated</Badge>
                            </div>
                            <p class="text-sm font-medium text-slate-500 mt-1 max-w-md">Import seasonal lesson plans in batch. Select your target class and subject, then upload your dataset.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button variant="outline" class="h-12 border-slate-200 font-bold px-8 rounded-2xl bg-white hover:bg-slate-50 transition-all text-[11px] uppercase tracking-widest" as-child>
                            <a :href="route('curriculum.planner.lesson-plans.template')" download>
                                <Download class="mr-2 h-4 w-4 text-emerald-600" />
                                Download Template
                            </a>
                        </Button>
                        <Button @click="showBulkModal = true" class="h-13 bg-slate-900 hover:bg-slate-800 text-white font-black px-10 rounded-2xl shadow-2xl shadow-slate-200 transition-all border-0 text-[11px] uppercase tracking-[0.2em]">
                            <BookCopy class="mr-2 h-4 w-4" />
                            Ingest Plans CSV
                        </Button>
                    </div>
                </div>
            </div>


            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between transition-all hover:border-primary/20">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Lessons</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.total_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                        <FileText class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between transition-all hover:border-emerald-200">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Approved</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.approved_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                         <CheckCircle2 class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between transition-all hover:border-amber-200">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Pending Review</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.pending_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100 shadow-sm">
                        <Clock class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between transition-all hover:border-slate-300">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">In Draft</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.draft_plans.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600 border border-slate-100 shadow-sm">
                        <Edit2 class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Grades Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link 
                    v-for="grade in grades" 
                    :key="grade.id"
                    :href="`/curriculum/planner/lesson-plans/grade/${grade.id}`"
                    class="group relative rounded-3xl border border-border bg-card p-6 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 overflow-hidden flex flex-col"
                >
                    <!-- Background Decoration -->
                    <div class="absolute -right-6 -top-6 rounded-full bg-primary/5 w-24 h-24 blur-2xl group-hover:bg-primary/10 transition-all duration-500"></div>
                    
                    <div class="flex items-start justify-between relative z-10">
                        <div class="flex flex-col gap-1.5">
                            <Badge variant="outline" :class="[getCategoryColor(grade.category), 'rounded-md py-0.5 px-2 text-[9px] font-black uppercase tracking-widest w-fit border shadow-sm']">
                                {{ grade.category || 'General' }}
                            </Badge>
                            <h3 class="text-2xl font-black tracking-tight text-foreground group-hover:text-primary transition-colors flex items-center gap-2">
                                {{ grade.name }}
                            </h3>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-muted/30 border border-border/50 flex items-center justify-center group-hover:scale-110 group-hover:bg-primary/10 group-hover:text-primary transition-all duration-300 text-muted-foreground">
                            <Layers class="h-6 w-6" />
                        </div>
                    </div>

                    <div class="mt-8 flex items-center gap-4 border-t border-border/40 pt-5 relative z-10">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">Classes</span>
                            <span class="text-base font-bold text-foreground flex items-center gap-1.5 mt-0.5">
                                <School class="h-4 w-4 text-slate-400" />
                                {{ grade.total_classes || 0 }} 
                            </span>
                        </div>
                        <div class="w-px h-8 bg-border/50"></div>
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">Total Plans</span>
                            <span class="text-base font-bold text-foreground flex items-center gap-1.5 mt-0.5">
                                <BookOpen class="h-4 w-4 text-slate-400" />
                                {{ grade.lesson_plans_count || 0 }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transform translate-x-4 group-hover:translate-x-0 transition-all duration-300">
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-lg shadow-primary/20 text-primary-foreground">
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </div>
                </Link>

                <div v-if="grades.length === 0" class="col-span-full py-20 text-center flex flex-col items-center justify-center bg-card rounded-3xl border border-dashed border-border/60">
                    <div class="h-16 w-16 rounded-full bg-muted/50 flex items-center justify-center mb-4 text-muted-foreground">
                        <Layers class="h-8 w-8 opacity-50" />
                    </div>
                    <h3 class="text-lg font-black tracking-tight text-foreground">No Grade Levels Assigned</h3>
                    <p class="text-sm text-muted-foreground max-w-[300px] mt-2 leading-relaxed">
                        You do not have any classes or grades assigned for lesson plan management.
                    </p>
                </div>
            </div>

            <!-- Global Creation Modal (Ported from LessonPlans.vue) -->
            <Dialog :open="showModal" @update:open="showModal = $event">
                <DialogContent class="max-w-5xl rounded-3xl p-0 overflow-hidden border-none shadow-2xl bg-card">
                    <DialogHeader class="px-8 py-6 bg-primary text-primary-foreground relative overflow-hidden">
                         <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.15),transparent)] pointer-events-none"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <DialogTitle class="text-2xl font-black tracking-tight uppercase">Instructional Blueprint</DialogTitle>
                                <DialogDescription class="text-primary-foreground/70 font-bold text-[11px] uppercase tracking-widest mt-1">
                                    {{ 'Strategic Lesson Configuration Engine' }}
                                </DialogDescription>
                            </div>
                            <div class="flex items-center gap-3">
                                <Badge variant="outline" class="bg-white/10 text-white border-white/20 rounded-full px-4 py-1 font-black text-[10px] uppercase tracking-widest">
                                    STEP: {{ currentStepIndex }} OF 4
                                </Badge>
                            </div>
                        </div>
                    </DialogHeader>

                    <div class="p-0">
                        <Tabs v-model="currentTab" class="w-full">
                            <TabsList class="w-full justify-start rounded-none h-auto p-0 bg-muted/30 border-b border-border/50">
                                <TabsTrigger 
                                    v-for="t in tabs" 
                                    :key="t" 
                                    :value="t" 
                                    class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-background transition-all"
                                >
                                    {{ t }}
                                </TabsTrigger>
                            </TabsList>

                            <div class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar bg-slate-50/30">
                                <TabsContent value="administrative" class="mt-0 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2 col-span-full">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Lesson Directive (Title)</Label>
                                            <Input v-model="form.title" placeholder="e.g., Exploration of Photosynthesis Dynamics" class="h-12 rounded-2xl border-border/60 font-bold focus:ring-4 focus:ring-primary/10 shadow-sm" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Execution Class</Label>
                                            <select v-model="form.class_id" class="flex h-12 w-full rounded-2xl border border-border/60 bg-background px-4 py-2 text-sm font-bold focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary/10 shadow-sm">
                                                <option value="">Select Class Context</option>
                                                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Subject Discipline</Label>
                                            <select v-model="form.subject_id" class="flex h-12 w-full rounded-2xl border border-border/60 bg-background px-4 py-2 text-sm font-bold focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary/10 shadow-sm">
                                                <option value="">Select Discipline</option>
                                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Deployment Date</Label>
                                            <Input type="date" v-model="form.lesson_date" class="h-12 rounded-2xl border-border/60 font-bold shadow-sm" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Academic Term</Label>
                                            <select v-model="form.academic_term_id" class="flex h-12 w-full rounded-2xl border border-border/60 bg-background px-4 py-2 text-sm font-bold shadow-sm">
                                                <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.name }}</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 col-span-full">
                                            <div class="space-y-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Temporal Scope (Duration)</Label>
                                                <div class="relative">
                                                     <Input type="number" v-model="form.duration_minutes" class="h-12 rounded-2xl border-border/60 font-bold pl-11 shadow-sm" />
                                                     <Clock class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                                     <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-muted-foreground uppercase tracking-widest">MINS</span>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Learner Population</Label>
                                                <div class="relative">
                                                     <Input type="number" v-model="form.number_of_learners" class="h-12 rounded-2xl border-border/60 font-bold pl-11 shadow-sm" />
                                                     <Users class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="curriculum" class="mt-0 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Syllabus Strand</Label>
                                            <select v-model="form.strand_id" class="flex h-12 w-full rounded-2xl border border-border/60 bg-background px-4 py-2 text-sm font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary/10">
                                                <option value="">Select Strand</option>
                                                <option v-for="strand in filteredStrands" :key="strand.id" :value="strand.id">{{ strand.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Sub-Strand Protocol</Label>
                                            <select v-model="form.sub_strand_id" class="flex h-12 w-full rounded-2xl border border-border/60 bg-background px-4 py-2 text-sm font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary/10">
                                                <option value="">Select Sub-Strand</option>
                                                <option v-for="ss in filteredSubStrands" :key="ss.id" :value="ss.id">{{ ss.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2 col-span-full">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Learning Outcomes (KPIs)</Label>
                                            <Textarea v-model="form.learning_outcomes" placeholder="Define measurable terminal outcomes..." class="min-h-[120px] rounded-2xl border-border/60 font-medium text-sm leading-relaxed shadow-sm p-4" />
                                        </div>
                                        <div class="space-y-4 col-span-full">
                                            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1 flex items-center gap-2">
                                                <Sparkles class="h-3.5 w-3.5 text-amber-500" /> CBC Strategic Pillars
                                            </Label>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="p-4 rounded-3xl bg-blue-50/50 border border-blue-100 flex flex-col gap-3 group transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-600/5">
                                                     <div class="flex items-center gap-2 text-blue-700">
                                                         <Users class="h-4 w-4" />
                                                         <span class="text-[10px] font-black uppercase tracking-[0.1em]">Core Competencies</span>
                                                     </div>
                                                     <div class="flex flex-wrap gap-2">
                                                        <Badge v-for="comp in ['Communication', 'Collaboration', 'Critical Thinking', 'Creativity', 'Self-efficacy', 'Digital Literacy', 'Citizenship']" 
                                                               :key="comp"
                                                               @click="form.core_competencies.includes(comp) ? form.core_competencies = form.core_competencies.filter(c => c !== comp) : form.core_competencies.push(comp)"
                                                               :variant="form.core_competencies.includes(comp) ? 'default' : 'outline'"
                                                               class="cursor-pointer text-[9px] uppercase tracking-tight py-1"
                                                        >
                                                            {{ comp }}
                                                        </Badge>
                                                     </div>
                                                </div>
                                                <div class="p-4 rounded-3xl bg-emerald-50/50 border border-emerald-100 flex flex-col gap-3 transition-all hover:bg-white hover:shadow-xl hover:shadow-emerald-600/5">
                                                     <div class="flex items-center gap-2 text-emerald-700">
                                                         <Lightbulb class="h-4 w-4" />
                                                         <span class="text-[10px] font-black uppercase tracking-[0.1em]">Pertinent Values</span>
                                                     </div>
                                                     <div class="flex flex-wrap gap-2">
                                                        <Badge v-for="val in ['Respect', 'Integrity', 'Responsibility', 'Teamwork', 'Love', 'Unity', 'Peace']" 
                                                               :key="val"
                                                               @click="form.values.includes(val) ? form.values = form.values.filter(v => v !== val) : form.values.push(val)"
                                                               :variant="form.values.includes(val) ? 'default' : 'outline'"
                                                               class="cursor-pointer text-[9px] uppercase tracking-tight py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200"
                                                        >
                                                            {{ val }}
                                                        </Badge>
                                                     </div>
                                                </div>
                                                <div class="p-4 rounded-3xl bg-purple-50/50 border border-purple-100 flex flex-col gap-3 transition-all hover:bg-white hover:shadow-xl hover:shadow-purple-600/5">
                                                     <div class="flex items-center gap-2 text-purple-700">
                                                         <Sparkles class="h-4 w-4" />
                                                         <span class="text-[10px] font-black uppercase tracking-[0.1em]">Life Skills</span>
                                                     </div>
                                                     <div class="flex flex-wrap gap-2">
                                                        <Badge v-for="skill in ['Self-awareness', 'Empathy', 'Decision Making', 'Effective Communication', 'Creative Thinking']" 
                                                               :key="skill"
                                                               @click="form.life_skills.includes(skill) ? form.life_skills = form.life_skills.filter(s => s !== skill) : form.life_skills.push(skill)"
                                                               :variant="form.life_skills.includes(skill) ? 'default' : 'outline'"
                                                               class="cursor-pointer text-[9px] uppercase tracking-tight py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 border-purple-200"
                                                        >
                                                            {{ skill }}
                                                        </Badge>
                                                     </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="delivery" class="mt-0 space-y-6">
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between px-1">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Instructional Workflow (Activities)</Label>
                                                <Button type="button" @click="addActivity" variant="ghost" size="sm" class="h-8 rounded-full px-4 text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-all">
                                                    <Plus class="mr-2 h-3 w-3" /> Append Module
                                                </Button>
                                            </div>
                                            
                                            <div class="space-y-4">
                                                <div v-for="(activity, index) in form.learning_activities" :key="index" class="relative group animate-in slide-in-from-left-4 duration-300" :style="{ animationDelay: `${index * 100}ms` }">
                                                    <div class="absolute left-[-15px] top-6 bottom-0 w-0.5 bg-border/40 group-last:bg-gradient-to-b group-last:from-border/40 group-last:to-transparent"></div>
                                                    <div class="absolute left-[-18px] top-6 h-2 w-2 rounded-full border-2 border-primary bg-background shadow-sm"></div>
                                                    
                                                    <div class="flex gap-4">
                                                        <div class="flex-1 p-5 rounded-3xl bg-card border border-border/60 shadow-sm transition-all group-focus-within:border-primary/40 group-focus-within:ring-4 group-focus-within:ring-primary/5">
                                                            <div class="flex items-center gap-3 mb-3">
                                                                <Badge variant="outline" class="bg-primary/5 text-primary border-none rounded-full px-2.5 h-6 font-black text-[9px]">STEP 0{{ index + 1 }}</Badge>
                                                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 italic font-mono">{{ index === 0 ? 'Foundation' : 'Advanced Stage' }}</span>
                                                            </div>
                                                            <Textarea v-model="form.learning_activities[index]" placeholder="Detail specific learner-teacher engagement protocols..." class="border-none bg-transparent p-0 focus-visible:ring-0 min-h-[80px] max-h-[220px] text-sm leading-relaxed" />
                                                        </div>
                                                        <Button v-if="form.learning_activities.length > 1" type="button" variant="ghost" size="icon" @click="removeActivity(index)" class="self-start mt-4 h-10 w-10 rounded-2xl text-rose-500 hover:bg-rose-50 hover:text-rose-600 border border-transparent hover:border-rose-100 transition-all">
                                                            <Trash class="h-4.5 w-4.5" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                            <div class="space-y-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Instructional Resources</Label>
                                                <Textarea v-model="form.teaching_aids" class="rounded-2xl border-border/60 shadow-sm" placeholder="List textbooks, digital media, lab equipment..." />
                                            </div>
                                            <div class="space-y-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Bibliographic References</Label>
                                                <Textarea v-model="form.references" class="rounded-2xl border-border/60 shadow-sm" placeholder="KICD Approved Textbooks, Journal articles..." />
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="reflection" class="mt-0 space-y-6">
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="p-8 rounded-[40px] bg-gradient-to-br from-indigo-50/50 to-blue-50/30 border border-blue-100/50 relative overflow-hidden shadow-inner">
                                            <div class="absolute top-0 right-0 p-8 text-blue-200 pointer-events-none transform rotate-12">
                                                 <Sparkles class="h-32 w-32 opacity-20" />
                                            </div>
                                            <div class="relative z-10 space-y-2">
                                                <div class="flex items-center gap-3 text-blue-700 mb-6">
                                                     <div class="h-12 w-12 rounded-3xl bg-blue-600 text-white flex items-center justify-center shadow-xl shadow-blue-600/20">
                                                          <ListChecks class="h-6 w-6" />
                                                     </div>
                                                     <h4 class="text-xl font-black uppercase tracking-tight">Lesson Conclusion</h4>
                                                </div>
                                                <Textarea v-model="form.conclusion" class="bg-white/80 border-blue-100/50 rounded-3xl min-h-[120px] p-6 text-sm font-medium leading-relaxed shadow-xl shadow-blue-900/5 focus:ring-4 focus:ring-blue-600/10" placeholder="Summarize the instructional engagement and consolidate learning..." />
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                            <div class="space-y-2">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Assessment Matrix</Label>
                                                <Textarea v-model="form.assessment_methods" placeholder="Oral questions, peer assessment, observation checklists..." class="rounded-2xl border-border/60 shadow-sm min-h-[100px]" />
                                            </div>
                                            <div class="space-y-2 text-primary">
                                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Extended Learning (Homework)</Label>
                                                <Textarea v-model="form.homework" placeholder="Assign task-based community engagement or individual research..." class="rounded-2xl border-primary/20 shadow-sm border-2 min-h-[100px] font-bold" />
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>
                            </div>
                        </Tabs>
                    </div>

                    <DialogFooter class="p-8 border-t border-border/50 bg-muted/20 flex flex-col sm:flex-row justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <Button v-if="currentTab !== tabs[0]" @click="prevTab" variant="outline" class="h-12 rounded-2xl px-6 border-border font-bold uppercase text-[10px] tracking-widest shadow-sm">
                                <ChevronRight class="mr-2 h-4 w-4 rotate-180" /> Back
                            </Button>
                        </div>
                        <div class="flex items-center gap-3">
                            <Button @click="showModal = false" variant="ghost" class="h-12 rounded-2xl px-6 font-bold uppercase text-[10px] tracking-widest">
                                Save Progress
                            </Button>
                            <Button v-if="currentTab !== tabs[tabs.length - 1]" @click="nextTab" class="h-12 rounded-2xl px-8 font-black uppercase text-[10px] tracking-[0.15em] shadow-xl transition-all active:scale-95">
                                Progression <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                            <Button v-else @click="submit" class="h-12 rounded-2xl px-10 bg-emerald-600 hover:bg-emerald-700 text-white font-black uppercase text-[10px] tracking-[0.2em] shadow-xl shadow-emerald-500/20 active:scale-95 transition-all">
                                <CheckCircle2 class="mr-2.5 h-4 w-4" /> Finalize Strategy
                            </Button>
                        </div>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Global Bulk Upload Modal -->
            <Dialog v-model:open="showBulkModal">
                <DialogContent class="sm:max-w-[550px] rounded-[2.5rem] border-0 shadow-2xl p-0 overflow-hidden bg-card">
                    <DialogHeader class="p-10 bg-slate-900 text-white relative">
                        <div class="absolute top-0 right-0 p-10 opacity-10">
                            <BookCopy class="h-24 w-24" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle class="text-3xl font-black tracking-tight mb-1 italic">Bulk Implementation</DialogTitle>
                            <DialogDescription class="text-slate-400 text-xs font-bold uppercase tracking-widest leading-relaxed">
                                High-Fidelity Lesson Plan Dataset Deployment
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    <div class="p-10 space-y-8">
                        <!-- Selectors -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Target Class Context</Label>
                                <select v-model="bulkForm.class_id" class="w-full h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold focus:ring-2 focus:ring-indigo-600/10 outline-none border transition-all appearance-none px-4 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                    <option value="">Select Class</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Learning Area Subject</Label>
                                <select v-model="bulkForm.subject_id" class="w-full h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold focus:ring-2 focus:ring-indigo-600/10 outline-none border transition-all appearance-none px-4 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                    <option value="">Select Subject</option>
                                    <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div
                            class="relative flex flex-col items-center justify-center rounded-[2rem] border-3 border-dashed p-12 transition-all duration-500 group"
                            :class="[dragOver ? 'border-indigo-600 bg-indigo-50/50 scale-[0.98]' : 'border-slate-100 bg-slate-50/30', bulkForm.file ? 'border-emerald-500/50 bg-emerald-50/20' : '']"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                        >
                            <input
                                type="file"
                                accept=".csv"
                                class="absolute inset-0 cursor-pointer opacity-0 z-20"
                                @change="handleFileChange"
                            />

                            <template v-if="!bulkForm.file">
                                <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white shadow-xl shadow-slate-200/50 group-hover:translate-y-[-4px] transition-transform duration-500">
                                    <Upload class="h-8 w-8 text-indigo-600" />
                                </div>
                                <p class="mt-6 text-sm font-black text-slate-900 tracking-tight">Drop Plan CSV here</p>
                                <p class="mt-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Standard Template Required</p>
                            </template>

                            <template v-else>
                                <div class="flex w-full items-center gap-6 min-w-0 animate-in zoom-in-95 duration-300">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.5rem] bg-emerald-500 text-white shadow-xl shadow-emerald-200">
                                        <CheckCircle2 class="h-8 w-8" />
                                    </div>
                                    <div class="flex-1 min-w-0 overflow-hidden">
                                        <p class="text-sm font-black break-all leading-tight text-slate-900" :title="bulkForm.file.name">
                                            {{ bulkForm.file.name }}
                                        </p>
                                        <p class="mt-1 text-[10px] font-bold text-slate-400 uppercase">{{ (bulkForm.file.size / 1024).toFixed(2) }} KB</p>
                                    </div>
                                    <Button variant="ghost" size="icon" class="h-10 w-10 shrink-0 text-slate-400 hover:text-rose-500 transition-colors z-30" @click.stop="removeFile">
                                        <X class="h-5 w-5" />
                                    </Button>
                                </div>
                            </template>
                        </div>

                        <div v-if="bulkForm.errors.file" class="flex items-start gap-2 bg-rose-50 p-4 rounded-xl border border-rose-100 animate-in slide-in-from-top-2">
                            <AlertCircle class="h-4 w-4 text-rose-500 shrink-0 mt-0.5" />
                            <span class="text-[11px] font-bold text-rose-600 leading-relaxed">{{ bulkForm.errors.file }}</span>
                        </div>

                        <Button 
                            @click="handleBulkUpload" 
                            :disabled="!bulkForm.file || bulkForm.processing || !bulkForm.class_id || !bulkForm.subject_id"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white rounded-2xl h-16 font-black text-xs shadow-2xl shadow-slate-200 transition-all uppercase tracking-[0.3em] overflow-hidden group"
                        >
                            <span v-if="bulkForm.processing" class="flex items-center gap-2 justify-center">
                                <Loader2 class="h-4 w-4 animate-spin" />
                                Processing Dataset...
                            </span>
                            <span v-else class="flex items-center gap-2 justify-center">
                                <Sparkles class="h-4 w-4 text-amber-400 group-hover:animate-pulse" />
                                Finalize Import Task
                            </span>
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>
