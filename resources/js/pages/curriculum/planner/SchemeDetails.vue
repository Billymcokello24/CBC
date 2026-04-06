<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { 
    Plus, Search, Filter, Download, FileText, 
    MoreHorizontal, Edit2, Trash2, Calendar, 
    BookOpen, Sparkles, LayoutGrid, ListFilter,
    ArrowLeft, Eye, Clock, CheckCircle2,
    GraduationCap, Layers, Save, ChevronRight
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ref, computed, watch, useTemplateRef } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps({
    scheme: Object,
    strands: Array
});

const page = usePage();

const breadcrumbs = [
    { title: 'Curriculum', href: '#' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: props.scheme.subject?.name || 'Subject', href: '#' },
    { title: props.scheme.title, href: `/curriculum/planner/schemes/${props.scheme.id}` },
];

const showToast = ref(false);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

watch(flashSuccess, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => showToast.value = false, 3000);
    }
});

watch(flashError, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => showToast.value = false, 3000);
    }
});

const entryForm = useForm({
    week_number: '',
    lesson_number: '',
    duration_minutes: 35,
    lesson_date: '',
    strand_id: '',
    sub_strand_id: '',
    topic: '',
    key_vocabulary: '',
    learning_outcomes: '',
    learning_activities: '',
    teacher_activities: '',
    introduction: '',
    lesson_development: '',
    conclusion: '',
    resources: '',
    references: '',
    assessment: '',
    remarks: '',
    reflection: '',
    homework: '',
    core_competencies: [],
    pci: [],
    inquiry_questions: '',
});

const isGeneratingLessons = ref(false);
const generationForm = useForm({
    entry_ids: [],
    start_date: '',
    duration_minutes: 35,
    lessons_per_week: props.scheme.lessons_per_week || 5,
});

const currentGenerationStep = ref(1);
const selectedEntries = computed(() => {
    return props.scheme.entries.filter(e => generationForm.entry_ids.includes(e.id));
});

const isAddingEntry = ref(false);
const editingEntryId = ref(null);

const submitEntry = () => {
    if (editingEntryId.value) {
        entryForm.put(`/curriculum/planner/schemes/${props.scheme.id}/entries/${editingEntryId.value}`, {
            onSuccess: () => {
                isAddingEntry.value = false;
                editingEntryId.value = null;
                entryForm.reset();
            }
        });
    } else {
        entryForm.post(`/curriculum/planner/schemes/${props.scheme.id}/entries`, {
            onSuccess: () => {
                isAddingEntry.value = false;
                entryForm.reset();
            }
        });
    }
};

const openAddModal = (entry = null) => {
    if (entry) {
        editingEntryId.value = entry.id;
        entryForm.week_number = entry.week_number;
        entryForm.lesson_number = entry.lesson_number;
        entryForm.duration_minutes = entry.duration_minutes;
        entryForm.lesson_date = entry.lesson_date;
        entryForm.strand_id = entry.strand_id;
        entryForm.sub_strand_id = entry.sub_strand_id;
        entryForm.topic = entry.topic;
        entryForm.key_vocabulary = entry.key_vocabulary;
        entryForm.learning_outcomes = entry.learning_outcomes;
        entryForm.learning_activities = entry.learning_activities;
        entryForm.teacher_activities = entry.teacher_activities;
        entryForm.introduction = entry.introduction;
        entryForm.lesson_development = entry.lesson_development;
        entryForm.conclusion = entry.conclusion;
        entryForm.resources = entry.resources;
        entryForm.references = entry.references;
        entryForm.assessment = entry.assessment;
        entryForm.remarks = entry.remarks;
        entryForm.reflection = entry.reflection;
        entryForm.homework = entry.homework;
        entryForm.core_competencies = entry.core_competencies || [];
        entryForm.pci = entry.pci || [];
        entryForm.inquiry_questions = entry.inquiry_questions;
    } else {
        editingEntryId.value = null;
        entryForm.reset();
        entryForm.duration_minutes = 35;
    }
    isAddingEntry.value = true;
};

const fileInput = useTemplateRef('fileInput');
const importForm = useForm({
    file: null
});

const triggerImport = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    importForm.file = file;
    importForm.post(`/curriculum/planner/schemes/${props.scheme.id}/import`, {
        onSuccess: () => {
            importForm.reset();
        }
    });
};

const deleteEntry = (entryId) => {
    if (confirm('Are you sure you want to remove this entry?')) {
        router.delete(`/curriculum/planner/schemes/${props.scheme.id}/entries/${entryId}`);
    }
};

const openGenerationWizard = (entry = null) => {
    if (entry) {
        generationForm.entry_ids = [entry.id];
        generationForm.start_date = entry.lesson_date || '';
    } else {
        generationForm.entry_ids = [];
    }
    currentGenerationStep.value = 1;
    isGeneratingLessons.value = true;
};

const toggleEntrySelection = (entryId) => {
    const index = generationForm.entry_ids.indexOf(entryId);
    if (index > -1) {
        generationForm.entry_ids.splice(index, 1);
    } else {
        generationForm.entry_ids.push(entryId);
    }
};

const selectAllInWeek = (weekNumber) => {
    const weekEntries = props.scheme.entries.filter(e => e.week_number === weekNumber).map(e => e.id);
    const allSelected = weekEntries.every(id => generationForm.entry_ids.includes(id));
    
    if (allSelected) {
        generationForm.entry_ids = generationForm.entry_ids.filter(id => !weekEntries.includes(id));
    } else {
        generationForm.entry_ids = [...new Set([...generationForm.entry_ids, ...weekEntries])];
    }
};

const submitGeneration = () => {
    generationForm.post(`/curriculum/planner/schemes/${props.scheme.id}/bulk-generate-lessons`, {
        onSuccess: () => {
            isGeneratingLessons.value = false;
            generationForm.reset();
        }
    });
};

const cbcCompetencies = [
    'Communication and Collaboration',
    'Self-efficacy',
    'Critical Thinking and Problem Solving',
    'Creativity and Imagination',
    'Citizenship',
    'Digital Literacy',
    'Learning to Learn'
];

const cbcPCIs = [
    'Environmental Education',
    'Health Education',
    'Life Skills Education',
    'Values Education',
    'Human Rights Education',
    'Gender Issues',
    'Financial Literacy'
];

const getStatusVariant = (status) => {
    switch (status) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'rejected': return 'bg-rose-50 text-rose-600 border-rose-100';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};
</script>

<template>
    <Head :title="'Scheme Details - ' + scheme.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Notifications -->
        <div v-if="showToast && (flashSuccess || flashError)" class="fixed top-8 right-8 z-[100] animate-in slide-in-from-right-4 duration-300">
            <div :class="['flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border backdrop-blur-xl', flashSuccess ? 'bg-emerald-50/90 border-emerald-200 text-emerald-800' : 'bg-rose-50/90 border-rose-200 text-rose-800']">
                <div :class="['h-8 w-8 rounded-xl flex items-center justify-center', flashSuccess ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white']">
                    <CheckCircle2 v-if="flashSuccess" class="h-5 w-5" />
                    <Sparkles v-else class="h-5 w-5" />
                </div>
                <div class="flex flex-col">
                    <p class="text-[13px] font-bold tracking-tight">{{ flashSuccess || flashError }}</p>
                    <p class="text-[10px] opacity-70 font-medium whitespace-nowrap">Instructional matrix synced successfully</p>
                </div>
            </div>
        </div>

        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-32 p-6 md:p-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <Link href="/curriculum/planner/schemes" class="text-xs font-bold text-blue-600 hover:underline">Schemes of Work</Link>
                        <ChevronRight class="h-3 w-3 text-muted-foreground/50" />
                        <span class="text-xs font-bold text-muted-foreground">{{ scheme.subject?.name }}</span>
                    </div>
                    <div class="flex items-center gap-3 mt-1">
                        <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ scheme.title }}</h1>
                        <Badge :class="[getStatusVariant(scheme.status), 'px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border shadow-sm']">
                            {{ scheme.status }}
                        </Badge>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleFileUpload" />
                    
                    <div class="flex items-center bg-muted/20 p-1 rounded-xl border border-border/40 shadow-sm">
                        <a href="/curriculum/planner/schemes/template/download">
                            <Button variant="ghost" size="sm" class="h-9 px-4 rounded-lg font-bold text-[10px] uppercase tracking-[0.15em] hover:bg-background text-indigo-600">
                                <Download class="mr-2 h-3.5 w-3.5" /> Get Template
                            </Button>
                        </a>
                        <div class="w-px h-3 bg-border/40 mx-1"></div>
                        <Button @click="triggerImport" variant="ghost" size="sm" class="h-9 px-4 rounded-lg font-bold text-[10px] uppercase tracking-[0.15em] hover:bg-background text-emerald-600">
                            <FileText class="mr-2 h-3.5 w-3.5" /> Bulk Upload
                        </Button>
                    </div>
                    
                    <Button @click="openAddModal()" class="h-10 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 font-bold text-[10px] uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                        <Plus class="h-4 w-4" /> New Plan
                    </Button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div v-for="(stat, idx) in [
                    { label: 'Scheme Entries', value: scheme.entries?.length || 0, icon: BookOpen, color: 'text-blue-600', bg: 'bg-blue-50' },
                    { label: 'Academic Period', value: (scheme.total_weeks || 0) + ' Weeks', icon: Calendar, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                    { label: 'Weekly Periods', value: (scheme.lessons_per_week || 0) + ' L/Wk', icon: Sparkles, color: 'text-amber-600', bg: 'bg-amber-50' },
                    { label: 'Grade Level', value: scheme.grade_level?.short_name || scheme.grade_level?.name || 'N/A', icon: GraduationCap, color: 'text-rose-600', bg: 'bg-rose-50' }
                ]" :key="idx" class="rounded-2xl border border-border bg-card p-6 shadow-sm flex items-center justify-between group hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1 opacity-70">{{ stat.label }}</p>
                        <h3 class="text-2xl font-bold text-foreground tracking-tight">{{ stat.value }}</h3>
                    </div>
                    <div :class="['h-12 w-12 rounded-2xl flex items-center justify-center transition-transform duration-500 group-hover:scale-110', stat.bg, stat.color]">
                        <component :is="stat.icon" class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Instructional Matrix Table -->
            <div class="rounded-[2rem] border border-border bg-card shadow-sm overflow-hidden transition-all duration-500">
                <div class="p-6 border-b border-border/50 bg-muted/5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                            <Layers class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-foreground">Instructional Matrix</h2>
                            <p class="text-[11px] text-muted-foreground font-medium opacity-60">Sequence and Term Roadmap</p>
                        </div>
                    </div>
                    <Button @click="openGenerationWizard()" variant="outline" class="h-10 px-5 rounded-xl border-border font-bold text-[10px] uppercase tracking-widest bg-background hover:bg-muted/50 shadow-sm flex items-center gap-2">
                        <Sparkles class="h-4 w-4 text-blue-600" /> Execute Automation
                    </Button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed min-w-[1200px]">
                        <thead>
                            <tr class="bg-muted/30 text-muted-foreground border-b border-border/50">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest w-24 text-center">Timing</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest w-64">Strand & Sub-Strand</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest w-64">Instructional Topic</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest w-80">Outcomes & Methodology</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest w-48 text-center">Resources</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-right w-40">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-for="entry in scheme.entries" :key="entry.id" class="hover:bg-muted/20 transition-all group cursor-pointer" @click="viewDetails(entry)">
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex flex-col items-center justify-center px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100/50">
                                        <span class="text-[11px] font-bold text-blue-600 tracking-tight leading-none uppercase">Wk {{ entry.week_number }}</span>
                                        <span class="text-[9px] font-bold text-blue-400 uppercase tracking-widest mt-1">Ls {{ entry.lesson_number }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2">
                                            <Badge variant="outline" class="text-[8px] font-bold bg-muted/50 rounded-md py-0 px-2 uppercase tracking-widest border-border/50">{{ entry.strand?.name }}</Badge>
                                        </div>
                                        <span class="text-[11px] font-bold text-foreground/80 leading-none truncate ml-0.5">{{ entry.sub_strand?.name || 'General Sub-strand' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-sm font-bold text-foreground tracking-tight group-hover:text-blue-600 transition-colors uppercase leading-tight line-clamp-2">{{ entry.topic }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-2">
                                        <div class="flex gap-2 items-start bg-emerald-50/30 p-2.5 rounded-xl border border-emerald-500/10">
                                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                                            <p class="text-[11px] text-emerald-800 line-clamp-2 leading-relaxed font-bold tracking-tight italic">
                                                {{ entry.learning_outcomes }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2 items-start px-2.5 opacity-60">
                                            <div class="h-1 w-1 rounded-full bg-muted-foreground mt-1.5 shrink-0"></div>
                                            <p class="text-[10px] text-muted-foreground line-clamp-1 leading-relaxed font-medium truncate">
                                                {{ entry.learning_activities }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col items-center justify-center p-2 rounded-xl bg-muted/30 border border-border/40 max-w-[140px] mx-auto group-hover:bg-background transition-all">
                                        <Layers class="h-3.5 w-3.5 text-slate-400 mb-1" />
                                        <span class="text-[9px] text-muted-foreground font-bold tracking-tight uppercase truncate w-full text-center">{{ entry.resources || 'Resources' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all">
                                            <Link :href="`/curriculum/planner/schemes/${scheme.id}/entries/${entry.id}`"><Eye class="h-4.5 w-4.5" /></Link>
                                        </Button>
                                        <DropdownMenu @click.stop>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted transition-all font-black"><MoreHorizontal class="h-4.5 w-4.5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-border shadow-2xl">
                                                <DropdownMenuItem class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider group" @click="openAddModal(entry)">
                                                    <Edit2 class="mr-3 h-4 w-4 opacity-60 group-hover:text-amber-600" /> Edit Content
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50/50 hover:bg-blue-600 hover:text-white transition-colors" @click="openGenerationWizard(entry)">
                                                    <Sparkles class="mr-3 h-4 w-4" /> Automate Plans
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 bg-border/5" />
                                                <DropdownMenuItem class="text-rose-600 rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider hover:bg-rose-50 transition-colors" @click="deleteEntry(entry.id)">
                                                    <Trash2 class="mr-3 h-4 w-4" /> Remove Entry
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!scheme.entries?.length">
                                <td colspan="6" class="px-6 py-32 text-center text-muted-foreground bg-muted/5">
                                    <div class="flex flex-col items-center gap-4 max-w-sm mx-auto">
                                        <div class="h-20 w-20 rounded-3xl bg-muted/50 flex items-center justify-center text-muted-foreground/30 border border-border shadow-inner">
                                            <FileText class="h-10 w-10" />
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-lg font-bold text-foreground tracking-tight">Empty Teaching Matrix</h3>
                                            <p class="text-xs font-medium italic opacity-70">Your scheme of work is currently empty. Initialize the matrix to start planning.</p>
                                        </div>
                                        <Button @click="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl h-11 px-8 shadow-lg shadow-blue-600/20 mt-4 font-bold transition-all active:scale-95 uppercase text-[10px] tracking-widest">
                                            <Plus class="mr-2 h-4 w-4" /> Start Planning
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Add/Edit Entry Modal -->
    <Dialog v-model:open="isAddingEntry">
        <DialogContent class="sm:max-w-[850px] max-h-[92vh] overflow-y-auto rounded-[2rem] border-border bg-card shadow-2xl p-0">
            <DialogHeader class="p-8 pb-6 bg-muted/5 border-b border-border/10 relative overflow-hidden">
                <div class="flex items-center gap-6 relative z-10">
                    <div class="h-14 w-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20 animate-in zoom-in duration-500">
                        <BookOpen class="h-7 w-7" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold tracking-tight text-foreground">
                            {{ editingEntryId ? 'Refine Lesson Entry' : 'Create Lesson Entry' }}
                        </DialogTitle>
                        <p class="text-[11px] text-muted-foreground font-medium mt-0.5 opacity-70">
                            Configure instructional methodology and sequence parameters.
                        </p>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="submitEntry" class="space-y-0">
                <Tabs defaultValue="admin" class="w-full">
                    <div class="px-8 pt-6">
                        <TabsList class="grid w-full grid-cols-4 rounded-xl bg-muted/50 p-1 border border-border/50">
                            <TabsTrigger value="admin" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:shadow-sm">Administrative</TabsTrigger>
                            <TabsTrigger value="curriculum" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:shadow-sm">Curriculum</TabsTrigger>
                            <TabsTrigger value="delivery" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:shadow-sm">Delivery</TabsTrigger>
                            <TabsTrigger value="outcome" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-background data-[state=active]:shadow-sm">Outcomes</TabsTrigger>
                        </TabsList>
                    </div>

                    <div class="p-8 pt-6 space-y-6">
                        <TabsContent value="admin" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Week Number</Label>
                                    <Input v-model="entryForm.week_number" type="number" placeholder="e.g. 1" class="rounded-xl border-border h-11 bg-muted/10 focus:bg-background transition-all font-medium" required />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Lesson Number</Label>
                                    <Input v-model="entryForm.lesson_number" type="number" placeholder="e.g. 1" class="rounded-xl border-border h-11 bg-muted/10 focus:bg-background transition-all font-medium" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Lesson Date (Optional)</Label>
                                    <Input v-model="entryForm.lesson_date" type="date" class="rounded-xl border-border h-11 bg-muted/10 focus:bg-background transition-all font-medium" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Duration (Minutes)</Label>
                                    <Input v-model="entryForm.duration_minutes" type="number" class="rounded-xl border-border h-11 bg-muted/10 focus:bg-background transition-all font-medium" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Topic / Title</Label>
                                <Input v-model="entryForm.topic" placeholder="e.g. Introduction to Place Value" class="rounded-xl border-border h-11 bg-muted/10 focus:bg-background transition-all font-medium italic" required />
                            </div>
                        </TabsContent>

                        <TabsContent value="curriculum" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Strand</Label>
                                    <select v-model="entryForm.strand_id" class="w-full h-11 px-4 rounded-xl border border-border bg-muted/10 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                                        <option value="" disabled>Select Strand</option>
                                        <option v-for="s in strands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Sub-Strand</Label>
                                    <Input v-model="entryForm.sub_strand_id" placeholder="ID or auto-populated" class="rounded-xl border-border h-11 bg-muted/10 focus:bg-background transition-all font-medium" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Learning Outcomes / Objectives</Label>
                                <Textarea v-model="entryForm.learning_outcomes" placeholder="What should students be able to do?" class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[120px] leading-relaxed" />
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Key Vocabulary</Label>
                                    <Textarea v-model="entryForm.key_vocabulary" placeholder="Key terms students should learn..." class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[80px]" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Key Inquiry Questions</Label>
                                    <Textarea v-model="entryForm.inquiry_questions" placeholder="How do we...?" class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[80px]" />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="delivery" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Introduction</Label>
                                    <Textarea v-model="entryForm.introduction" placeholder="How will you start the lesson?" class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Lesson Development</Label>
                                    <Textarea v-model="entryForm.lesson_development" placeholder="Core instructional steps..." class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Teacher Activities</Label>
                                    <Textarea v-model="entryForm.teacher_activities" placeholder="What will the teacher do?" class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Learner Activities</Label>
                                    <Textarea v-model="entryForm.learning_activities" placeholder="What will students do?" class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="outcome" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Resources / Aids</Label>
                                    <Textarea v-model="entryForm.resources" placeholder="Materials needed..." class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Assessment Methods</Label>
                                    <Textarea v-model="entryForm.assessment" placeholder="How will you measure success?" class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Homework / Reflection</Label>
                                    <Textarea v-model="entryForm.homework" placeholder="Follow-up tasks..." class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">General Remarks</Label>
                                    <Textarea v-model="entryForm.remarks" placeholder="Observations..." class="rounded-xl border-border bg-muted/10 focus:bg-background transition-all font-medium min-h-[100px]" />
                                </div>
                            </div>
                        </TabsContent>
                    </div>
                </Tabs>

                <DialogFooter class="p-8 bg-muted/5 border-t border-border/10 flex justify-between sm:justify-between items-center bg-muted/5">
                    <Button type="button" variant="ghost" @click="isAddingEntry = false" class="h-11 px-8 rounded-xl font-bold text-[10px] uppercase tracking-widest text-muted-foreground hover:text-foreground transition-all">Discard Changes</Button>
                    <Button type="submit" :disabled="entryForm.processing" class="h-11 px-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 font-bold text-[10px] uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                        <Save v-if="!entryForm.processing" class="h-4 w-4" /> 
                        {{ entryForm.processing ? 'Syncing...' : (editingEntryId ? 'Update Entry' : 'Append Entry') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

            <!-- Generate Lessons Wizard -->
            <Dialog v-model:open="isGeneratingLessons">
                <DialogContent class="sm:max-w-[750px] rounded-2xl border-border bg-card shadow-2xl p-0 overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-700 p-10 text-white relative">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 pointer-events-none"></div>
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-6 border border-white/20 shadow-xl">
                                    <Sparkles class="h-8 w-8 text-blue-200 animate-pulse" />
                                </div>
                                <DialogTitle class="text-2xl font-bold text-white tracking-tight">Smart Plan Generator</DialogTitle>
                                <DialogDescription class="text-blue-100/80 mt-1 font-medium italic">
                                    Step {{ currentGenerationStep }} of 3: {{ 
                                        currentGenerationStep === 1 ? 'Target Scope' : 
                                        currentGenerationStep === 2 ? 'Temporal Config' : 'Validation'
                                    }}
                                </DialogDescription>
                            </div>
                            <div class="flex gap-1.5 mt-2 bg-black/10 backdrop-blur-sm p-2 rounded-full border border-white/5">
                                <div v-for="i in 3" :key="i" class="h-2 w-8 rounded-full transition-all duration-500" :class="i <= currentGenerationStep ? 'bg-white shadow-[0_0_12px_rgba(255,255,255,0.5)]' : 'bg-white/10'"></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentGenerationStep === 1" class="p-10 space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[11px] font-bold text-foreground uppercase tracking-widest ml-1">Target Matrix Weeks</h3>
                                <Badge variant="secondary" class="bg-blue-600 text-white rounded-lg border-none">{{ generationForm.entry_ids.length }} Selected</Badge>
                            </div>
                            <div class="grid grid-cols-4 gap-4 max-h-[300px] overflow-y-auto pr-3 custom-scrollbar">
                                <template v-for="week in [...new Set(scheme.entries.map(e => e.week_number))]" :key="week">
                                    <div 
                                        @click="selectAllInWeek(week)"
                                        class="p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col items-center justify-center gap-1 group active:scale-95"
                                        :class="scheme.entries.filter(e => e.week_number === week).every(e => generationForm.entry_ids.includes(e.id)) ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-muted/30 border-border/50 hover:border-blue-400 hover:bg-card'">
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em]" :class="scheme.entries.filter(e => e.week_number === week).every(e => generationForm.entry_ids.includes(e.id)) ? 'text-blue-100' : 'text-muted-foreground/60 group-hover:text-blue-500'">Week</p>
                                        <p class="text-3xl font-black">{{ week }}</p>
                                    </div>
                                </template>
                            </div>
                            
                            <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm shrink-0">
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <p class="text-xs font-semibold text-blue-800 leading-relaxed">
                                    We will transform <span class="font-black text-blue-600 text-sm">{{ generationForm.entry_ids.length }}</span> curriculum matrix entries into fully operational lesson plans.
                                </p>
                            </div>
                        </div>

                        <DialogFooter class="px-0 pt-6 flex justify-between sm:justify-between w-full border-t border-border/50">
                            <Button type="button" variant="ghost" @click="isGeneratingLessons = false" class="rounded-xl h-12 px-8 font-bold text-muted-foreground hover:bg-muted">Discard</Button>
                            <Button @click="currentGenerationStep = 2" :disabled="!generationForm.entry_ids.length" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-10 shadow-lg shadow-blue-600/20 font-bold h-12 active:scale-95 transition-all">
                                Configure Rotation <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </DialogFooter>
                    </div>

                    <div v-if="currentGenerationStep === 2" class="p-10 space-y-10 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-1">Cycle Commencement</Label>
                                <Input type="date" v-model="generationForm.start_date" class="rounded-xl border-border h-14 bg-muted/10 text-base font-bold transition-all focus:ring-4 focus:ring-blue-500/10" />
                                <p class="text-[10px] text-muted-foreground italic ml-1">The date lesson #1 starts</p>
                            </div>
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-1">Default Duration</Label>
                                <div class="relative">
                                    <Input type="number" v-model="generationForm.duration_minutes" class="rounded-xl border-border h-14 bg-muted/10 text-base font-bold pl-12 transition-all focus:ring-4 focus:ring-blue-500/10" />
                                    <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-muted-foreground">MINS</span>
                                </div>
                                <p class="text-[10px] text-muted-foreground italic ml-1">Applied if unspecified in matrix</p>
                            </div>
                        </div>

                        <div class="space-y-6 pt-4">
                            <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground text-center block">Instructional Density (Lessons / Week)</Label>
                            <div class="flex items-center justify-center gap-10 py-4 bg-muted/10 rounded-3xl border border-border/50 shadow-inner">
                                <Button type="button" variant="outline" class="h-14 w-14 rounded-2xl border-2 text-2xl font-black bg-card shadow-sm hover:bg-muted active:scale-90" @click="generationForm.lessons_per_week > 1 && generationForm.lessons_per_week--">
                                    -
                                </Button>
                                <div class="text-center min-w-[100px]">
                                    <span class="text-6xl font-black text-foreground tracking-tighter">{{ generationForm.lessons_per_week }}</span>
                                    <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.3em] mt-1 italic">PERIODS / WK</p>
                                </div>
                                <Button type="button" variant="outline" class="h-14 w-14 rounded-2xl border-2 text-2xl font-black bg-card shadow-sm hover:bg-muted active:scale-90" @click="generationForm.lessons_per_week < 10 && generationForm.lessons_per_week++">
                                    +
                                </Button>
                            </div>
                        </div>

                        <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                <Calendar class="h-5 w-5" />
                            </div>
                            <p class="text-xs text-indigo-700 leading-relaxed font-semibold italic">
                                Smart Scheduler will automatically handle weekends and distribution based on your frequency setting.
                            </p>
                        </div>

                        <DialogFooter class="px-0 pt-6 flex justify-between sm:justify-between w-full border-t border-border/50">
                            <Button type="button" variant="ghost" @click="currentGenerationStep = 1" class="rounded-xl h-12 px-8 font-bold text-muted-foreground hover:bg-muted">Back to Scope</Button>
                            <Button @click="currentGenerationStep = 3" class="bg-slate-900 border border-slate-800 hover:bg-black text-white rounded-xl px-10 shadow-lg font-bold h-12 active:scale-95 transition-all">
                                Run Diagnostics <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </DialogFooter>
                    </div>

                    <div v-if="currentGenerationStep === 3" class="p-10 space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="space-y-6">
                            <h3 class="text-sm font-black text-foreground uppercase tracking-widest ml-1">Generation Manifest</h3>
                            <div class="grid gap-3 bg-muted/10 p-8 rounded-[2rem] border border-border/50 shadow-inner">
                                <div class="flex justify-between items-center py-3 border-b border-border/30">
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Target Dataset</span>
                                    <span class="text-base font-black text-foreground">{{ generationForm.entry_ids.length }} Instructions</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-border/30">
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Timeline Start</span>
                                    <span class="text-base font-black text-blue-600">{{ generationForm.start_date || 'TBD' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3">
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Protocol Sync</span>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></div>
                                        <Badge variant="secondary" class="bg-emerald-500 text-white border-none rounded-lg font-black text-[10px] py-1">CBC ACTIVE</Badge>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 p-5 bg-amber-50 rounded-2xl border border-amber-200">
                                <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                                    <AlertCircle class="h-6 w-6" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs text-amber-800 font-black uppercase tracking-tight">Final Authorization Required</p>
                                    <p class="text-[11px] text-amber-700/80 leading-relaxed font-semibold italic">
                                        Proceeding will automate the creation of draft daily lesson plans. This operation is irreversible but entries can be edited individually.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <DialogFooter class="px-0 pt-6 flex justify-between sm:justify-between w-full border-t border-border/50">
                            <Button type="button" variant="ghost" @click="currentGenerationStep = 2" class="rounded-xl h-12 px-8 font-bold text-muted-foreground hover:bg-muted">Recalibrate</Button>
                            <Button @click="submitGeneration" :disabled="generationForm.processing" class="group bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-12 shadow-xl shadow-blue-600/20 font-black h-12 active:scale-95 transition-all">
                                <span class="flex items-center gap-2">
                                    {{ generationForm.processing ? 'ENGINEERING PLANS...' : 'BUILD ALL PLANS' }}
                                    <Sparkles v-if="!generationForm.processing" class="h-4 w-4 group-hover:rotate-12 transition-transform" />
                                </span>
                            </Button>
                        </DialogFooter>
                    </div>
                </DialogContent>
            </Dialog>

</template>
