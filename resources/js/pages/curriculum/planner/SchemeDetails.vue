<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ChevronLeft, Plus, Save, Trash2, 
    BookOpen, GraduationCap, Calendar, 
    Layers, Target, ListChecks, MessageSquare,
    Search, Download, FileText, Filter, ChevronRight, Sparkles,
    CheckCircle2, AlertCircle
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ref, computed, useTemplateRef } from 'vue';
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

const props = defineProps({
    scheme: Object,
    strands: Array
});

const isAddingEntry = ref(false);
const isViewingEntry = ref(false);
const isGeneratingLessons = ref(false);
const selectedEntry = ref(null);
const editingEntryId = ref(null);
const currentGenerationStep = ref(1);

// Feedback State
const showFeedback = ref(false);
const feedbackType = ref('success'); // 'success' | 'error'
const feedbackMessage = ref('');

const openFeedback = (type, message) => {
    feedbackType.value = type;
    feedbackMessage.value = message;
    showFeedback.value = true;
};

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

const submitEntry = () => {
    if (editingEntryId.value) {
        entryForm.put(`/curriculum/planner/schemes/${props.scheme.id}/entries/${editingEntryId.value}`, {
            onSuccess: () => {
                isAddingEntry.value = false;
                editingEntryId.value = null;
                entryForm.reset();
                openFeedback('success', 'Lesson entry updated successfully.');
            },
            onError: () => openFeedback('error', 'Failed to update entry. Please check your input.')
        });
    } else {
        entryForm.post(`/curriculum/planner/schemes/${props.scheme.id}/entries`, {
            onSuccess: () => {
                isAddingEntry.value = false;
                entryForm.reset();
                openFeedback('success', 'New lesson entry added to the matrix.');
            },
            onError: () => openFeedback('error', 'Failed to add entry. Please check your data.')
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

const viewDetails = (entry) => {
    selectedEntry.value = entry;
    isViewingEntry.value = true;
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
            openFeedback('success', 'Teaching matrix imported successfully from CSV.');
        },
        onError: (errors) => {
            openFeedback('error', Object.values(errors).join('\n') || 'Failed to import CSV. Please check the template format.');
        }
    });
};

const deleteEntry = (entryId) => {
    // We'll use a standard confirm for now, but style the feedback afterwards
    if (confirm('Are you sure you want to remove this entry?')) {
        useForm({}).delete(`/curriculum/planner/schemes/${props.scheme.id}/entries/${entryId}`, {
            onSuccess: () => openFeedback('success', 'Lesson entry removed successfully.'),
            onError: () => openFeedback('error', 'Failed to delete entry. Please try again.')
        });
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
            openFeedback('success', 'Daily lesson plans generated successfully and added to your planner.');
        },
        onError: () => openFeedback('error', 'Critical error during generation. Check your internet connection or server logs.')
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

const getStatusColor = (status) => {
    switch (status) {
        case 'approved': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <Head :title="'Scheme Details - ' + scheme.title" />

    <AppLayout>
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Breadcrumbs & Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-2">
                        <Link href="/curriculum/planner/schemes" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                            <ChevronLeft class="h-4 w-4" /> Academic Planner
                        </Link>
                        <span class="text-border">/</span>
                        <span class="font-medium text-foreground tracking-tight">{{ scheme.title }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ scheme.title }}</h1>
                        <Badge :class="getStatusColor(scheme.status)" variant="outline" class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                            {{ scheme.status }}
                        </Badge>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        accept=".csv" 
                        @change="handleFileUpload"
                    />
                    <a href="/curriculum/planner/schemes/template/download" class="flex items-center">
                        <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-medium hover:bg-muted bg-background transition-all">
                            <Download class="mr-2 h-4 w-4 opacity-70" /> 
                            Template
                        </Button>
                    </a>
                    <Button @click="triggerImport" variant="outline" class="h-10 px-4 rounded-xl border-border font-medium hover:bg-muted bg-background transition-all" :disabled="importForm.processing">
                        <FileText class="mr-2 h-4 w-4 opacity-70" /> 
                        Import CSV
                    </Button>
                    <Button @click="openAddModal()" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all">
                        <Plus class="mr-2 h-4 w-4" /> Add Lesson Entry
                    </Button>
                </div>
            </div>

            <!-- Administrative Details Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Learning Area</p>
                        <h3 class="text-lg font-bold text-foreground leading-tight">{{ scheme.subject?.name }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <BookOpen class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Grade Level</p>
                        <h3 class="text-lg font-bold text-foreground leading-tight">{{ scheme.grade_level?.name }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Term / Schedule</p>
                        <h3 class="text-lg font-bold text-foreground leading-tight">{{ scheme.academic_term?.name }} ({{ scheme.total_weeks }} Weeks)</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <Calendar class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Lessons / Week</p>
                        <h3 class="text-lg font-bold text-foreground leading-tight">{{ scheme.lessons_per_week }} Periods</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600">
                        <Layers class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Main Matrix Table -->
            <div class="rounded-2xl border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden">
                <div class="p-6 border-b border-border/50 flex items-center justify-between bg-muted/10">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                            <FileText class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-foreground">Teaching Matrix</h2>
                            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Instructional Timeline & Strands</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative group">
                            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground group-focus-within:text-blue-600 transition-colors" />
                            <Input placeholder="Search matrix..." class="pl-10 h-10 w-64 bg-background border-border rounded-xl focus:bg-background transition-all" />
                        </div>
                        <Button variant="outline" class="h-10 border-border font-semibold px-4 rounded-xl whitespace-nowrap bg-background">
                            <Filter class="mr-2 h-4 w-4 opacity-70" /> Filters
                        </Button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed min-w-[1200px]">
                        <thead>
                            <tr class="bg-muted/30 text-muted-foreground border-b border-border/50">
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider w-24">Timing</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider w-56">Strand / Theme</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider w-64">Sub-Strand / Topic</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider w-72">Outcomes & Activities</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider w-48">Resources</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-right w-40">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-for="entry in scheme.entries" :key="entry.id" class="hover:bg-muted/30 transition-colors group cursor-pointer" @click="viewDetails(entry)">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-foreground">Wk {{ entry.week_number }}</span>
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-tighter">Ls {{ entry.lesson_number }} • {{ entry.duration_minutes }}m</span>
                                        <span v-if="entry.lesson_date" class="text-[10px] font-semibold text-blue-600 mt-1">{{ entry.lesson_date }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1.5">
                                        <Badge variant="outline" class="w-fit text-[10px] bg-blue-50/50 text-blue-700 border-blue-100 rounded-lg py-0 px-2 font-bold uppercase tracking-wider">{{ entry.strand?.name }}</Badge>
                                        <span class="text-xs font-medium text-muted-foreground line-clamp-1 uppercase tracking-tighter">{{ entry.strand?.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-bold text-foreground line-clamp-1">{{ entry.sub_strand?.name || entry.topic }}</span>
                                        <span v-if="entry.sub_strand?.name" class="text-xs text-muted-foreground line-clamp-1">{{ entry.topic }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-2">
                                        <div class="flex gap-2 items-start">
                                            <div class="h-4 w-4 rounded-full bg-emerald-50 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-100">
                                                <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                            </div>
                                            <p class="text-xs text-foreground line-clamp-2 leading-relaxed font-medium">
                                                {{ entry.learning_outcomes }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2 items-start">
                                            <div class="h-4 w-4 rounded-full bg-blue-50 flex items-center justify-center shrink-0 mt-0.5 border border-blue-100">
                                                <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            </div>
                                            <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed italic">
                                                {{ entry.learning_activities }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 bg-slate-50 border border-slate-100 rounded-lg text-slate-400">
                                            <Layers class="h-3.5 w-3.5" />
                                        </div>
                                        <span class="text-xs text-muted-foreground line-clamp-1 font-medium italic">{{ entry.resources }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button @click.stop="openGenerationWizard(entry)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-blue-50 hover:text-blue-600 border border-transparent hover:border-blue-100 transition-all">
                                            <Sparkles class="h-4.5 w-4.5" />
                                        </Button>
                                        <Button @click.stop="viewDetails(entry)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 border border-transparent hover:border-indigo-100 transition-all">
                                            <Eye class="h-4.5 w-4.5" />
                                        </Button>
                                        <Button @click.stop="openAddModal(entry)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-amber-50 hover:text-amber-600 border border-transparent hover:border-amber-100 transition-all">
                                            <Edit2 class="h-4.5 w-4.5" />
                                        </Button>
                                        
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted transition-all" @click.stop><MoreHorizontal class="h-4.5 w-4.5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-border">
                                                <DropdownMenuItem class="rounded-lg" @click="viewDetails(entry)"><Eye class="mr-2 h-4 w-4" /> View Full Details</DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" @click="openAddModal(entry)"><Edit2 class="mr-2 h-4 w-4" /> Edit Entry</DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" @click="openGenerationWizard(entry)"><Sparkles class="mr-2 h-4 w-4" /> Generate Daily Plans</DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 bg-border/50" />
                                                <DropdownMenuItem class="text-rose-600 rounded-lg group" @click="deleteEntry(entry.id)"><Trash2 class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100" /> Delete Entry</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!scheme.entries?.length">
                                <td colspan="6" class="px-6 py-32 text-center text-muted-foreground bg-muted/5">
                                    <div class="flex flex-col items-center gap-4 max-w-sm mx-auto">
                                        <div class="h-20 w-20 rounded-[2.5rem] bg-muted flex items-center justify-center text-muted-foreground/50 border border-border shadow-sm">
                                            <FileText class="h-10 w-10" />
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-lg font-bold text-foreground">Empty Teaching Matrix</h3>
                                            <p class="text-sm font-medium leading-relaxed italic">Your scheme of work is currently empty. You can add entries manually or import them from a CSV.</p>
                                        </div>
                                        <Button @click="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl h-11 px-8 shadow-md mt-4 font-bold transition-all">
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
        <DialogContent class="sm:max-w-[850px] max-h-[95vh] overflow-y-auto rounded-[2rem] border-border bg-card shadow-2xl p-0">
            <DialogHeader class="p-8 pb-4 bg-muted/10 border-b border-border/50">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                        <BookOpen class="h-7 w-7" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold tracking-tight text-foreground">
                            {{ editingEntryId ? 'Edit Lesson Entry' : 'New Lesson Entry' }}
                        </DialogTitle>
                        <DialogDescription class="text-sm text-muted-foreground font-medium italic mt-0.5">
                            Provide comprehensive details for this instructional period.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="submitEntry" class="space-y-0">
                <Tabs defaultValue="admin" class="w-full">
                    <div class="px-8 pt-6">
                        <TabsList class="grid w-full grid-cols-4 rounded-xl bg-muted/50 p-1 border border-border/50">
                            <TabsTrigger value="admin" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-card data-[state=active]:shadow-sm">Administrative</TabsTrigger>
                            <TabsTrigger value="curriculum" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-card data-[state=active]:shadow-sm">Curriculum</TabsTrigger>
                            <TabsTrigger value="delivery" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-card data-[state=active]:shadow-sm">Delivery</TabsTrigger>
                            <TabsTrigger value="outcome" class="rounded-lg text-[10px] font-bold uppercase tracking-widest data-[state=active]:bg-card data-[state=active]:shadow-sm">Outcomes</TabsTrigger>
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

                <DialogFooter class="p-8 bg-muted/20 rounded-b-[2rem] border-t border-border/50 gap-3">
                    <Button type="button" variant="ghost" @click="isAddingEntry = false" class="rounded-xl h-11 px-6 font-bold text-muted-foreground hover:bg-muted transition-all">Cancel</Button>
                    <Button type="submit" :disabled="entryForm.processing" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-10 shadow-lg shadow-blue-600/20 font-bold h-11 transition-all">
                        <Save v-if="!entryForm.processing" class="mr-2 h-4 w-4" />
                        {{ entryForm.processing ? 'Saving Changes...' : (editingEntryId ? 'Update Matrix Entry' : 'Add to Matrix') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

            <!-- View Details Modal -->
            <Dialog v-model:open="isViewingEntry">
                <DialogContent class="sm:max-w-[850px] max-h-[92vh] overflow-y-auto rounded-[2.5rem] border-border bg-card shadow-2xl p-0">
                    <div class="p-8 space-y-8">
                        <DialogHeader>
                            <div class="flex items-start justify-between gap-6">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <Badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-100 text-[10px] font-black uppercase tracking-widest px-3 py-0.5 rounded-lg shadow-sm">
                                            Week {{ selectedEntry?.week_number }} • Lesson {{ selectedEntry?.lesson_number }}
                                        </Badge>
                                        <Badge variant="outline" class="bg-muted text-muted-foreground border-border text-[10px] font-bold uppercase tracking-widest px-3 py-0.5 rounded-lg">
                                            {{ selectedEntry?.duration_minutes }} Minutes
                                        </Badge>
                                    </div>
                                    <DialogTitle class="text-4xl font-extrabold tracking-tight text-foreground leading-[1.1]">{{ selectedEntry?.topic }}</DialogTitle>
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground font-medium italic">
                                        <div class="flex items-center gap-1.5">
                                            <Calendar class="h-4 w-4 text-blue-500" />
                                            {{ selectedEntry?.lesson_date || 'Schedule Pending' }}
                                        </div>
                                        <div class="h-1 w-1 rounded-full bg-border"></div>
                                        <div class="flex items-center gap-1.5">
                                            <GraduationCap class="h-4 w-4 text-indigo-500" />
                                            {{ selectedEntry?.strand?.name || 'General Strand' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Button @click="openAddModal(selectedEntry); isViewingEntry = false" variant="outline" class="rounded-xl font-bold text-xs uppercase tracking-wider h-12 px-6 border-border hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100 transition-all shadow-sm">
                                        <Edit2 class="mr-2 h-4 w-4" /> Edit Plan
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="isViewingEntry = false" class="h-12 w-12 rounded-xl text-muted-foreground hover:bg-muted font-bold transition-all">
                                        <Plus class="h-6 w-6 rotate-45" />
                                    </Button>
                                </div>
                            </div>
                        </DialogHeader>

                        <div class="grid grid-cols-5 gap-10">
                             <!-- Column 1: Core Curriculum (3 cols) -->
                             <div class="col-span-3 space-y-10">
                                 <section class="space-y-4">
                                     <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2.5">
                                         <div class="h-6 w-6 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                             <Target class="h-3.5 w-3.5" />
                                         </div>
                                         Learning Outcomes
                                     </h3>
                                     <div class="p-6 rounded-2xl bg-muted/30 border border-border/50 text-sm text-foreground leading-relaxed whitespace-pre-wrap font-medium shadow-inner">
                                         {{ selectedEntry?.learning_outcomes || 'No specific outcomes defined for this period.' }}
                                     </div>
                                 </section>

                                 <section class="space-y-4">
                                     <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2.5">
                                         <div class="h-6 w-6 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-600">
                                             <Layers class="h-3.5 w-3.5" />
                                         </div>
                                         Instructional Delivery
                                     </h3>
                                     <div class="space-y-4 pl-1">
                                         <div v-if="selectedEntry?.introduction" class="relative pl-6 border-l-2 border-blue-500/20 py-1">
                                             <div class="absolute -left-[5px] top-2 h-2 w-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                                             <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1.5 px-2 bg-blue-50 w-fit rounded shadow-sm">Phase 1: Introduction</p>
                                             <p class="text-sm text-muted-foreground leading-relaxed font-semibold italic">{{ selectedEntry?.introduction }}</p>
                                         </div>
                                         <div v-if="selectedEntry?.lesson_development" class="relative pl-6 border-l-2 border-indigo-500/20 py-1">
                                             <div class="absolute -left-[5px] top-2 h-2 w-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.5)]"></div>
                                             <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-1.5 px-2 bg-indigo-50 w-fit rounded shadow-sm">Phase 2: Development</p>
                                             <p class="text-sm text-muted-foreground leading-relaxed font-semibold italic">{{ selectedEntry?.lesson_development }}</p>
                                         </div>
                                         <div v-if="selectedEntry?.conclusion" class="relative pl-6 border-l-2 border-emerald-500/20 py-1">
                                             <div class="absolute -left-[5px] top-2 h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                             <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1.5 px-2 bg-emerald-50 w-fit rounded shadow-sm">Phase 3: Conclusion</p>
                                             <p class="text-sm text-muted-foreground leading-relaxed font-semibold italic">{{ selectedEntry?.conclusion }}</p>
                                         </div>
                                     </div>
                                 </section>
                             </div>

                             <!-- Column 2: Context & Resources (2 cols) -->
                             <div class="col-span-2 space-y-10">
                                 <section class="space-y-4">
                                     <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2.5">
                                         <div class="h-6 w-6 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600">
                                             <MessageSquare class="h-3.5 w-3.5" />
                                         </div>
                                         CBC Integration
                                     </h3>
                                     <div class="space-y-5 p-5 rounded-2xl border border-border bg-muted/10">
                                         <div v-if="selectedEntry?.core_competencies?.length">
                                             <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mb-2.5 ml-1">Competencies</p>
                                             <div class="flex flex-wrap gap-2">
                                                 <Badge v-for="c in selectedEntry.core_competencies" :key="c" variant="secondary" class="rounded-lg text-[10px] font-bold bg-blue-600/10 text-blue-700 border-none px-3 py-1">{{ c }}</Badge>
                                             </div>
                                         </div>
                                         <div v-if="selectedEntry?.pci?.length">
                                             <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mb-2.5 ml-1">Pertinent Issues</p>
                                             <div class="flex flex-wrap gap-2">
                                                 <Badge v-for="p in selectedEntry.pci" :key="p" variant="secondary" class="rounded-lg text-[10px] font-bold bg-amber-600/10 text-amber-700 border-none px-3 py-1">{{ p }}</Badge>
                                             </div>
                                         </div>
                                         <div v-if="selectedEntry?.inquiry_questions">
                                             <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mb-1.5 ml-1">Inquiry Question</p>
                                             <p class="text-xs text-foreground font-black italic bg-card px-3 py-2 rounded-xl border border-border/50 shadow-sm leading-relaxed">"{{ selectedEntry?.inquiry_questions }}"</p>
                                         </div>
                                     </div>
                                 </section>

                                 <section class="space-y-4">
                                     <h3 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2.5">
                                         <div class="h-6 w-6 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-600">
                                             <BookOpen class="h-3.5 w-3.5" />
                                         </div>
                                         Resources
                                     </h3>
                                     <div class="grid gap-3">
                                         <div class="p-4 rounded-xl bg-card border border-border/50 shadow-sm group hover:border-blue-200 transition-all">
                                             <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Instructional Materials</p>
                                             <p class="text-sm font-bold text-foreground">{{ selectedEntry?.resources || 'Standard classroom tools' }}</p>
                                         </div>
                                         <div v-if="selectedEntry?.assessment" class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 shadow-sm group hover:border-emerald-300 transition-all">
                                             <div class="flex items-center gap-2 mb-1">
                                                 <ListChecks class="h-3 w-3 text-emerald-600" />
                                                 <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Assessment</p>
                                             </div>
                                             <p class="text-sm font-bold text-emerald-800 italic leading-relaxed">{{ selectedEntry?.assessment }}</p>
                                         </div>
                                     </div>
                                 </section>
                             </div>
                        </div>
                    </div>

                    <div v-if="selectedEntry?.homework || selectedEntry?.reflection" class="m-8 mt-0 p-6 rounded-3xl bg-muted/20 border border-border/50 flex gap-10">
                         <div v-if="selectedEntry?.homework" class="flex-1 space-y-2">
                             <div class="flex items-center gap-2">
                                 <Save class="h-4 w-4 text-blue-500" />
                                 <p class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.1em]">Extension Task (Homework)</p>
                             </div>
                             <p class="text-sm text-foreground font-black leading-relaxed">{{ selectedEntry?.homework }}</p>
                         </div>
                         <div v-if="selectedEntry?.remarks" class="flex-1 space-y-2 border-l border-border/50 pl-10">
                             <div class="flex items-center gap-2">
                                 <MessageSquare class="h-4 w-4 text-indigo-500" />
                                 <p class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.1em]">Instructional Remarks</p>
                             </div>
                             <p class="text-sm text-muted-foreground italic leading-relaxed font-semibold">{{ selectedEntry?.remarks }}</p>
                         </div>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Generate Lessons Wizard -->
            <Dialog v-model:open="isGeneratingLessons">
                <DialogContent class="sm:max-w-[750px] rounded-[2.5rem] border-border bg-card shadow-2xl p-0 overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-700 p-10 text-white relative">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 pointer-events-none"></div>
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-6 border border-white/20 shadow-xl">
                                    <Sparkles class="h-8 w-8 text-blue-200 animate-pulse" />
                                </div>
                                <DialogTitle class="text-3xl font-black text-white tracking-tight">Smart Plan Generator</DialogTitle>
                                <DialogDescription class="text-blue-100/80 mt-2 font-medium">
                                    Phase {{ currentGenerationStep }} of 3: {{ 
                                        currentGenerationStep === 1 ? 'Target Matrix Scope' : 
                                        currentGenerationStep === 2 ? 'Temporal Config' : 'Validation & Commit'
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
                                <h3 class="text-sm font-black text-foreground uppercase tracking-widest ml-1">Target Matrix Weeks</h3>
                                <Badge variant="secondary" class="bg-blue-50 text-blue-600 rounded-lg">{{ generationForm.entry_ids.length }} Selected</Badge>
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
    <!-- Feedback Modal -->
    <Dialog v-model:open="showFeedback">
        <DialogContent class="sm:max-w-[400px] rounded-[2rem] border-none shadow-2xl p-0 overflow-hidden">
            <div class="p-8 text-center space-y-6">
                <div :class="[
                    'h-20 w-20 rounded-[2.5rem] mx-auto flex items-center justify-center shadow-lg transition-transform duration-500 hover:scale-110',
                    feedbackType === 'success' ? 'bg-emerald-500 text-white shadow-emerald-500/20' : 'bg-rose-500 text-white shadow-rose-500/20'
                ]">
                    <CheckCircle2 v-if="feedbackType === 'success'" class="h-10 w-10 animate-in zoom-in duration-300" />
                    <AlertCircle v-else class="h-10 w-10 animate-in zoom-in duration-300" />
                </div>
                
                <div class="space-y-2">
                    <h3 :class="[
                        'text-2xl font-bold tracking-tight',
                        feedbackType === 'success' ? 'text-emerald-600' : 'text-rose-600'
                    ]">
                        {{ feedbackType === 'success' ? 'Action Successful' : 'Action Failed' }}
                    </h3>
                    <p class="text-sm font-medium text-muted-foreground leading-relaxed px-4">
                        {{ feedbackMessage }}
                    </p>
                </div>

                <Button 
                    @click="showFeedback = false" 
                    :class="[
                        'w-full h-12 rounded-xl font-bold text-white shadow-md transition-all active:scale-95',
                        feedbackType === 'success' ? 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/20' : 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/20'
                    ]"
                >
                    Dismiss Notification
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
