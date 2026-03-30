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
const selectedEntry = ref(null);
const editingEntryId = ref(null);

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
    lessons_count: 5,
    start_date: '',
    duration_minutes: 35,
});

const activeEntryForGeneration = ref(null);

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
        },
        onError: (errors) => {
            alert(Object.values(errors).join('\n'));
        }
    });
};

const deleteEntry = (entryId) => {
    if (confirm('Are you sure you want to remove this entry?')) {
        useForm({}).delete(`/curriculum/planner/schemes/${props.scheme.id}/entries/${entryId}`);
    }
};

const openGenerationWizard = (entry) => {
    activeEntryForGeneration.value = entry;
    generationForm.start_date = entry.lesson_date || '';
    generationForm.duration_minutes = entry.duration_minutes || 35;
    isGeneratingLessons.value = true;
};

const submitGeneration = () => {
    generationForm.post(`/curriculum/planner/schemes/${props.scheme.id}/entries/${activeEntryForGeneration.value.id}/generate-lessons`, {
        onSuccess: () => {
            isGeneratingLessons.value = false;
            activeEntryForGeneration.value = null;
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
        <div class="max-w-[1600px] mx-auto p-6 space-y-8">
            <!-- Breadcrumbs & Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <Link href="/curriculum/planner/schemes" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                            <ChevronLeft class="h-4 w-4" /> Academic Planner
                        </Link>
                        <span class="text-slate-300">/</span>
                        <span class="font-medium text-slate-900">{{ scheme.title }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ scheme.title }}</h1>
                        <Badge :class="getStatusColor(scheme.status)" variant="outline" class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider">
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
                        <Button variant="outline" class="rounded-xl border-slate-200 hover:bg-slate-50 transition-all font-semibold">
                            <Download class="mr-2 h-4 w-4" /> Download Template
                        </Button>
                    </a>
                    <Button @click="triggerImport" variant="outline" class="rounded-xl border-slate-200 hover:bg-slate-50 transition-all font-semibold" :disabled="importForm.processing">
                        <FileText class="mr-2 h-4 w-4" /> Import CSV
                    </Button>
                    <Button @click="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md transition-all font-semibold">
                        <Plus class="mr-2 h-4 w-4" /> Add Lesson Entry
                    </Button>
                </div>
            </div>

            <!-- Administrative Details Card -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <BookOpen class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Learning Area</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.subject?.name }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-indigo-50 rounded-xl">
                        <GraduationCap class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Grade Level</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.grade_level?.name }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-amber-50 rounded-xl">
                        <Calendar class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Term / Schedule</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.academic_term?.name }} ({{ scheme.total_weeks }} Weeks)</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <Layers class="h-6 w-6 text-slate-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Lessons / Week</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.lessons_per_week }} Periods</p>
                    </div>
                </div>
            </div>

            <!-- Main Matrix Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden border-b-0">
                <div class="p-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-slate-400" />
                        <h2 class="font-bold text-slate-900">Termly Schemes of Work Matrix</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative group">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-hover:text-blue-500 transition-colors" />
                            <Input placeholder="Search matrix..." class="pl-10 rounded-xl border-slate-200 w-64 h-9 bg-white" />
                        </div>
                        <Button variant="outline" size="sm" class="rounded-lg h-9">
                            <Filter class="mr-2 h-3.5 w-3.5" /> Filters
                        </Button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed min-w-[1200px]">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-24">Timing</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-44">Strand/Sub-Strand</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-48">Topic</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-64">Learning Outcomes</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-64">Learning Activities</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-40">Resources</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-32">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="entry in scheme.entries" :key="entry.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900">Wk {{ entry.week_number }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Ls {{ entry.lesson_number }} • {{ entry.duration_minutes }}m</span>
                                        <span v-if="entry.lesson_date" class="text-[9px] font-medium text-blue-500">{{ entry.lesson_date }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1">
                                        <Badge variant="secondary" class="w-fit text-[10px] bg-slate-100 text-slate-600 rounded-md py-0 px-1.5">{{ entry.strand?.name }}</Badge>
                                        <span class="text-sm font-medium text-slate-800">{{ entry.sub_strand?.name }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 leading-relaxed">{{ entry.topic }}</td>
                                <td class="p-4">
                                    <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                        <li v-for="(line, i) in (entry.learning_outcomes?.split('\n') || [])" :key="i">{{ line }}</li>
                                    </ul>
                                </td>
                                <td class="p-4">
                                    <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                        <li v-for="(line, i) in (entry.learning_activities?.split('\n') || [])" :key="i">{{ line }}</li>
                                    </ul>
                                </td>
                                <td class="p-4 text-sm text-slate-500">{{ entry.resources }}</td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Button @click="openGenerationWizard(entry)" variant="ghost" size="icon" class="h-8 w-8 text-amber-500 hover:bg-amber-50 rounded-lg" title="Generate Daily Plans">
                                            <Sparkles class="h-4 w-4" />
                                        </Button>
                                        <Button @click="viewDetails(entry)" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="View Full Details">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button @click="openAddModal(entry)" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit Entry">
                                            <Edit2 class="h-4 w-4" />
                                        </Button>
                                        <Button @click="deleteEntry(entry.id)" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!scheme.entries?.length">
                                <td colspan="8" class="p-12 text-center text-slate-500">
                                    No matrix entries yet.
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
                <DialogContent class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto rounded-[2rem] border-slate-100 shadow-2xl p-0">
                    <DialogHeader class="p-8 pb-0">
                        <DialogTitle class="text-2xl font-bold tracking-tight">Lesson Entry Details</DialogTitle>
                        <DialogDescription class="text-sm text-slate-500 italic">
                            Provide comprehensive details for this instructional period.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitEntry" class="space-y-0">
                        <Tabs defaultValue="admin" class="w-full">
                            <div class="px-8 pt-6">
                                <TabsList class="grid w-full grid-cols-4 rounded-xl bg-slate-50 p-1">
                                    <TabsTrigger value="admin" class="rounded-lg text-[10px] font-bold uppercase tracking-widest">Administrative</TabsTrigger>
                                    <TabsTrigger value="curriculum" class="rounded-lg text-[10px] font-bold uppercase tracking-widest">Curriculum</TabsTrigger>
                                    <TabsTrigger value="delivery" class="rounded-lg text-[10px] font-bold uppercase tracking-widest">Delivery</TabsTrigger>
                                    <TabsTrigger value="outcome" class="rounded-lg text-[10px] font-bold uppercase tracking-widest">Outcomes</TabsTrigger>
                                </TabsList>
                            </div>

                            <div class="p-8 pt-6 space-y-6">
                                <TabsContent value="admin" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Week Number</Label>
                                            <Input v-model="entryForm.week_number" type="number" placeholder="e.g. 1" class="rounded-xl border-slate-100 italic" required />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lesson Number</Label>
                                            <Input v-model="entryForm.lesson_number" type="number" placeholder="e.g. 1" class="rounded-xl border-slate-100 italic" required />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lesson Date (Optional)</Label>
                                            <Input v-model="entryForm.lesson_date" type="date" class="rounded-xl border-slate-100 italic" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Duration (Minutes)</Label>
                                            <Input v-model="entryForm.duration_minutes" type="number" class="rounded-xl border-slate-100 italic" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Topic / Title</Label>
                                        <Input v-model="entryForm.topic" placeholder="e.g. Introduction to Place Value" class="rounded-xl border-slate-100 italic" required />
                                    </div>
                                </TabsContent>

                                <TabsContent value="curriculum" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Strand</Label>
                                            <select v-model="entryForm.strand_id" class="w-full h-10 px-3 rounded-xl border border-slate-100 bg-white text-sm font-medium outline-none focus:ring-2 focus:ring-blue-500 transition-all shrink-0">
                                                <option value="" disabled>Select Strand</option>
                                                <option v-for="s in strands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sub-Strand</Label>
                                            <Input v-model="entryForm.sub_strand_id" placeholder="ID or auto-populated" class="rounded-xl border-slate-100 italic" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Key Vocabulary</Label>
                                        <Textarea v-model="entryForm.key_vocabulary" placeholder="Key terms students should learn..." class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Learning Outcomes / Objectives</Label>
                                        <Textarea v-model="entryForm.learning_outcomes" placeholder="What should students be able to do?" class="rounded-xl border-slate-100 italic min-h-[100px]" />
                                    </div>
                                    <div class="space-y-4 pt-4 border-t border-slate-50">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Core Competencies</Label>
                                            <div class="flex flex-wrap gap-2">
                                                <Badge v-for="comp in cbcCompetencies" :key="comp" 
                                                    @click="entryForm.core_competencies.includes(comp) ? entryForm.core_competencies = entryForm.core_competencies.filter(c => c !== comp) : entryForm.core_competencies.push(comp)"
                                                    :variant="entryForm.core_competencies.includes(comp) ? 'default' : 'outline'"
                                                    class="cursor-pointer rounded-lg px-3 py-1 text-[10px] transition-all"
                                                    :class="entryForm.core_competencies.includes(comp) ? 'bg-blue-600' : 'hover:border-blue-300'">
                                                    {{ comp }}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PCIs (Pertinent Issues)</Label>
                                            <div class="flex flex-wrap gap-2">
                                                <Badge v-for="pci in cbcPCIs" :key="pci" 
                                                    @click="entryForm.pci.includes(pci) ? entryForm.pci = entryForm.pci.filter(p => p !== pci) : entryForm.pci.push(pci)"
                                                    :variant="entryForm.pci.includes(pci) ? 'default' : 'outline'"
                                                    class="cursor-pointer rounded-lg px-3 py-1 text-[10px] transition-all"
                                                    :class="entryForm.pci.includes(pci) ? 'bg-indigo-600' : 'hover:border-indigo-300'">
                                                    {{ pci }}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Key Inquiry Questions</Label>
                                            <Textarea v-model="entryForm.inquiry_questions" placeholder="How do we...?" class="rounded-xl border-slate-100 min-h-[60px]" />
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="delivery" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Introduction</Label>
                                            <Textarea v-model="entryForm.introduction" placeholder="How will you start the lesson?" class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lesson Development</Label>
                                            <Textarea v-model="entryForm.lesson_development" placeholder="Core instructional steps..." class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Teacher Activities</Label>
                                            <Textarea v-model="entryForm.teacher_activities" placeholder="What will the teacher do?" class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Learner Activities</Label>
                                            <Textarea v-model="entryForm.learning_activities" placeholder="What will students do?" class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Conclusion</Label>
                                        <Textarea v-model="entryForm.conclusion" placeholder="How will you wrap up?" class="rounded-xl border-slate-100 italic min-h-[60px]" />
                                    </div>
                                </TabsContent>

                                <TabsContent value="outcome" class="mt-0 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Resources / Aids</Label>
                                            <Textarea v-model="entryForm.resources" placeholder="Materials needed..." class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">References</Label>
                                            <Textarea v-model="entryForm.references" placeholder="Books or websites..." class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Assessment Methods</Label>
                                            <Textarea v-model="entryForm.assessment" placeholder="How will you measure success?" class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Homework / Reflection</Label>
                                            <Textarea v-model="entryForm.homework" placeholder="Follow-up tasks..." class="rounded-xl border-slate-100 italic min-h-[80px]" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Remarks / Reflection</Label>
                                        <Textarea v-model="entryForm.remarks" placeholder="General observations..." class="rounded-xl border-slate-100 italic min-h-[60px]" />
                                    </div>
                                </TabsContent>
                            </div>
                        </Tabs>

                        <DialogFooter class="p-8 bg-slate-50/50 rounded-b-[2rem] border-t border-slate-50">
                            <Button type="button" variant="outline" @click="isAddingEntry = false" class="rounded-xl border-slate-200">Cancel</Button>
                            <Button type="submit" :disabled="entryForm.processing" class="bg-blue-600 hover:bg-blue-700 rounded-xl px-8 shadow-md font-bold uppercase tracking-widest text-xs h-10">
                                {{ entryForm.processing ? 'Saving...' : (editingEntryId ? 'Update Entry' : 'Add Lesson Entry') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- View Details Modal -->
            <Dialog v-model:open="isViewingEntry">
                <DialogContent class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto rounded-[2rem] border-slate-100 shadow-2xl p-8 space-y-8">
                    <DialogHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <Badge variant="secondary" class="bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md mb-2">
                                    Week {{ selectedEntry?.week_number }} • Lesson {{ selectedEntry?.lesson_number }}
                                </Badge>
                                <DialogTitle class="text-3xl font-bold tracking-tight text-slate-900">{{ selectedEntry?.topic }}</DialogTitle>
                            </div>
                            <Button @click="openAddModal(selectedEntry); isViewingEntry = false" variant="outline" class="rounded-xl font-bold text-xs uppercase tracking-wider h-10 px-6 border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100 transition-all">
                                <Edit2 class="mr-2 h-4 w-4" /> Edit Plan
                            </Button>
                        </div>
                    </DialogHeader>

                    <div class="grid grid-cols-2 gap-8">
                         <!-- Column 1: Curriculum & Delivery -->
                         <div class="space-y-8">
                             <section class="space-y-3">
                                 <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b pb-2 flex items-center gap-2">
                                     <Target class="h-3.5 w-3.5 text-blue-500" /> Learning Outcomes
                                 </h3>
                                 <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap pl-1 font-medium">{{ selectedEntry?.learning_outcomes || 'Not specified' }}</div>
                             </section>

                             <section v-if="selectedEntry?.inquiry_questions" class="space-y-3">
                                 <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b pb-2 flex items-center gap-2">
                                     <MessageSquare class="h-3.5 w-3.5 text-amber-500" /> Inquiry Questions
                                 </h3>
                                 <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap pl-1 italic font-medium">"{{ selectedEntry?.inquiry_questions }}"</div>
                             </section>

                             <section class="space-y-3">
                                 <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b pb-2 flex items-center gap-2">
                                     <Layers class="h-3.5 w-3.5 text-indigo-500" /> Delivery Steps
                                 </h3>
                                 <div class="space-y-4 pl-1">
                                     <div v-if="selectedEntry?.introduction">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Introduction</p>
                                         <p class="text-sm text-slate-600 leading-relaxed font-medium">{{ selectedEntry?.introduction }}</p>
                                     </div>
                                     <div v-if="selectedEntry?.lesson_development">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Development</p>
                                         <p class="text-sm text-slate-600 leading-relaxed font-medium">{{ selectedEntry?.lesson_development }}</p>
                                     </div>
                                     <div v-if="selectedEntry?.conclusion">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Conclusion</p>
                                         <p class="text-sm text-slate-600 leading-relaxed font-medium">{{ selectedEntry?.conclusion }}</p>
                                     </div>
                                 </div>
                             </section>
                         </div>

                         <!-- Column 2: Activities & Resources -->
                         <div class="space-y-8">
                             <section class="space-y-3">
                                 <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b pb-2 flex items-center gap-2">
                                     <ListChecks class="h-3.5 w-3.5 text-emerald-500" /> Activities & Assessment
                                 </h3>
                                 <div class="space-y-4 pl-1">
                                     <div v-if="selectedEntry?.teacher_activities">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Teacher Activity</p>
                                         <p class="text-sm text-slate-600 italic">{{ selectedEntry?.teacher_activities }}</p>
                                     </div>
                                     <div v-if="selectedEntry?.learning_activities">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Learner Activity</p>
                                         <p class="text-sm text-slate-600 italic">{{ selectedEntry?.learning_activities }}</p>
                                     </div>
                                     <div v-if="selectedEntry?.assessment">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Assessment</p>
                                         <p class="text-sm text-emerald-600 font-bold bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100">{{ selectedEntry?.assessment }}</p>
                                     </div>
                                 </div>
                             </section>

                             <section v-if="selectedEntry?.core_competencies?.length || selectedEntry?.pci?.length" class="space-y-3">
                                 <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b pb-2 flex items-center gap-2">
                                     <GraduationCap class="h-3.5 w-3.5 text-blue-500" /> CBC Alignment
                                 </h3>
                                 <div class="space-y-3 pl-1">
                                     <div v-if="selectedEntry?.core_competencies?.length">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Competencies</p>
                                         <div class="flex flex-wrap gap-1.5">
                                             <Badge v-for="c in selectedEntry.core_competencies" :key="c" variant="secondary" class="rounded-md text-[10px] bg-blue-50 text-blue-700 border-blue-100">{{ c }}</Badge>
                                         </div>
                                     </div>
                                     <div v-if="selectedEntry?.pci?.length">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Pertinent Issues (PCIs)</p>
                                         <div class="flex flex-wrap gap-1.5">
                                             <Badge v-for="p in selectedEntry.pci" :key="p" variant="secondary" class="rounded-md text-[10px] bg-indigo-50 text-indigo-700 border-indigo-100">{{ p }}</Badge>
                                         </div>
                                     </div>
                                 </div>
                             </section>

                             <section class="space-y-3">
                                 <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b pb-2 flex items-center gap-2">
                                     <BookOpen class="h-3.5 w-3.5 text-amber-500" /> Resources & References
                                 </h3>
                                 <div class="space-y-4 pl-1">
                                     <div v-if="selectedEntry?.resources">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Materials</p>
                                         <p class="text-sm text-slate-600 font-medium">{{ selectedEntry?.resources }}</p>
                                     </div>
                                     <div v-if="selectedEntry?.references">
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Refs</p>
                                         <p class="text-sm text-slate-600 font-medium">{{ selectedEntry?.references }}</p>
                                     </div>
                                 </div>
                             </section>
                         </div>
                    </div>

                    <div v-if="selectedEntry?.homework || selectedEntry?.reflection" class="bg-slate-50 p-6 rounded-[1.5rem] border border-slate-100 flex gap-8">
                         <div v-if="selectedEntry?.homework" class="flex-1">
                             <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5"><Save class="h-3 w-3" /> Homework</p>
                             <p class="text-sm text-slate-700 font-semibold">{{ selectedEntry?.homework }}</p>
                         </div>
                         <div v-if="selectedEntry?.reflection" class="flex-1">
                             <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5"><MessageSquare class="h-3 w-3" /> Reflection</p>
                             <p class="text-sm text-slate-700 italic">{{ selectedEntry?.reflection }}</p>
                         </div>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Generate Lessons Wizard -->
            <Dialog v-model:open="isGeneratingLessons">
                <DialogContent class="sm:max-w-[500px] rounded-[2rem] border-slate-100 shadow-2xl p-0 overflow-hidden">
                    <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-8 text-white">
                        <Sparkles class="h-10 w-10 mb-4 opacity-80" />
                        <DialogTitle class="text-2xl font-bold text-white">Generate Daily Plans</DialogTitle>
                        <DialogDescription class="text-amber-100 mt-1">
                            We'll help you break down this weekly scheme into individual lesson sessions.
                        </DialogDescription>
                    </div>

                    <form @submit.prevent="submitGeneration" class="p-8 space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">How many lessons for this topic?</Label>
                                <Input type="number" v-model="generationForm.lessons_count" min="1" max="10" class="rounded-xl border-slate-200" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">Start Date</Label>
                                <Input type="date" v-model="generationForm.start_date" class="rounded-xl border-slate-200" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">Duration per lesson (mins)</Label>
                                <Input type="number" v-model="generationForm.duration_minutes" class="rounded-xl border-slate-200" />
                            </div>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                             <div class="flex gap-3">
                                 <CheckCircle2 class="h-5 w-5 text-blue-500 shrink-0" />
                                 <p class="text-xs text-blue-700 leading-relaxed">
                                     <strong>Strategic data sync:</strong> Learning outcomes, vocabulary, and references will be automatically synced to all generated lessons.
                                 </p>
                             </div>
                        </div>

                        <DialogFooter class="px-0 pt-4">
                            <Button type="button" variant="ghost" @click="isGeneratingLessons = false" class="rounded-xl">Cancel</Button>
                            <Button type="submit" :disabled="generationForm.processing" class="bg-amber-600 hover:bg-amber-700 rounded-xl px-8 shadow-md font-bold text-white h-11">
                                {{ generationForm.processing ? 'Generating...' : 'Build Lesson Plans' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
</template>
