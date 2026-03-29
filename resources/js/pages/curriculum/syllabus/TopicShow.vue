<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    BookOpenCheck, GraduationCap, ChevronRight, 
    Plus, Info, CheckCircle2, MoreVertical, 
    ArrowLeft, Eye, Edit2, Trash2, X, LayoutGrid, List as ListIcon,
    Download, MoreHorizontal, Search, Filter, BookOpen, Target,
    Sparkles, Lightbulb, ClipboardList
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    strand: any;
    subject: any;
    grade: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Syllabus', href: '/curriculum/syllabus' },
    { title: props.subject.name, href: `/curriculum/syllabus/${props.subject.id}/${props.grade.id}` },
    { title: props.strand.name, href: '#' },
];

// Modals State
const showSubStrandModal = ref(false);
const showOutcomeModal = ref(false);
const viewMode = ref<'grid' | 'list'>('list');

const editingSubStrand = ref<any>(null);
const editingOutcome = ref<any>(null);

// Forms
const subStrandForm = useForm({
    name: '',
    code: '',
    description: '',
    strand_id: props.strand.id,
    display_order: 1,
});

const outcomeForm = useForm({
    outcome: '',
    sub_strand_id: null as number | null,
    outcome_type: 'Standard',
    display_order: 1,
});

// Topic Logic (Sub-Strands)
const openSubStrandModal = (subStrand: any = null) => {
    editingSubStrand.value = subStrand;
    if (subStrand) {
        subStrandForm.name = subStrand.name;
        subStrandForm.code = subStrand.code || '';
        subStrandForm.description = subStrand.description || '';
        subStrandForm.display_order = subStrand.display_order || 1;
    } else {
        subStrandForm.reset();
        subStrandForm.strand_id = props.strand.id;
        subStrandForm.display_order = (props.strand.sub_strands?.length || 0) + 1;
    }
    showSubStrandModal.value = true;
};

const submitSubStrand = () => {
    if (editingSubStrand.value) {
        subStrandForm.put(route('curriculum.syllabus.sub-topics.update', editingSubStrand.value.id), {
            onSuccess: () => {
                showSubStrandModal.value = false;
                editingSubStrand.value = null;
            }
        });
    } else {
        subStrandForm.post(route('curriculum.syllabus.sub-topics.store'), {
            onSuccess: () => {
                showSubStrandModal.value = false;
            }
        });
    }
};

const deleteSubStrand = (id: number) => {
    if (confirm('Are you sure you want to remove this sub-strand? This will also remove all its learning goals.')) {
        useForm({}).delete(route('curriculum.syllabus.sub-topics.destroy', id));
    }
};

// Outcome Logic
const openOutcomeModal = (subStrandId: number, outcome: any = null) => {
    editingOutcome.value = outcome;
    outcomeForm.sub_strand_id = subStrandId;
    if (outcome) {
        outcomeForm.outcome = outcome.outcome;
        outcomeForm.outcome_type = outcome.outcome_type || 'Standard';
        outcomeForm.display_order = outcome.display_order || 1;
    } else {
        outcomeForm.outcome = '';
        outcomeForm.outcome_type = 'Standard';
        const subStrand = props.strand.sub_strands.find((ss: any) => ss.id === subStrandId);
        outcomeForm.display_order = (subStrand?.learning_outcomes?.length || 0) + 1;
    }
    showOutcomeModal.value = true;
};

const submitOutcome = () => {
    if (editingOutcome.value) {
        outcomeForm.put(route('curriculum.syllabus.outcomes.update', editingOutcome.value.id), {
            onSuccess: () => {
                showOutcomeModal.value = false;
                editingOutcome.value = null;
            }
        });
    } else {
        outcomeForm.post(route('curriculum.syllabus.outcomes.store'), {
            onSuccess: () => {
                showOutcomeModal.value = false;
            }
        });
    }
};

const deleteOutcome = (id: number) => {
    if (confirm('Are you sure you want to remove this learning goal?')) {
        useForm({}).delete(route('curriculum.syllabus.outcomes.destroy', id));
    }
};

const printSyllabus = () => {
    window.print();
};

</script>

<template>
    <Head :title="`${strand.name} - ${subject.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1600px] mx-auto bg-[#f9fafb]/30">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Link :href="`/curriculum/syllabus/${subject.id}/${grade.id}`" class="group inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:text-blue-600 transition-all mb-1">
                        <ArrowLeft class="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Strands
                    </Link>
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                            <BookOpenCheck class="h-5 w-5" />
                        </div>
                        <div class="space-y-0.5">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 leading-tight">
                                {{ strand.name }}
                            </h1>
                            <div class="flex items-center gap-2">
                                <Badge variant="outline" class="bg-blue-50 border-0 text-[9px] font-bold uppercase px-2 py-0.5 text-blue-600 shadow-none">{{ subject.name }}</Badge>
                                <span class="text-slate-300 font-bold">•</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ grade.name }}</span>
                                <span class="text-slate-300 font-bold">•</span>
                                <span class="text-[10px] font-bold text-slate-900 border border-slate-200 px-1.5 py-0.5 rounded">{{ strand.code }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button @click="printSyllabus" variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-semibold text-xs text-slate-600 shadow-sm transition-all hover:bg-slate-50">
                        <Download class="mr-2 h-3.5 w-3.5" /> Export
                    </Button>
                    <Button @click="openSubStrandModal()" class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-xs text-white shadow-sm transition-all">
                        <Plus class="mr-2 h-4 w-4" /> Add Sub-strand
                    </Button>
                </div>
            </div>

            <!-- Stats Analytics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sub-strands</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ strand.sub_strands?.length || 0 }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <Target class="h-5 w-5" />
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Goals</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                            {{ strand.sub_strands?.reduce((acc: number, ss: any) => acc + (ss.learning_outcomes?.length || 0), 0) || 0 }}
                        </h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                        <Sparkles class="h-5 w-5" />
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Competencies</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                            {{ strand.sub_strands?.reduce((acc: number, ss: any) => acc + (ss.learning_outcomes?.filter((o: any) => o.outcome_type === 'Competency').length || 0), 0) || 0 }}
                        </h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                        <Lightbulb class="h-5 w-5" />
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">LIVE</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <ClipboardList class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Sub-strands Display -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <!-- Toolbar -->
                <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white">
                    <div class="relative w-full md:max-w-md">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                        <Input placeholder="Search sub-strands..." class="pl-10 h-11 rounded-xl text-sm font-medium border-slate-100 bg-[#f9fafb]/50 focus:bg-white transition-all shadow-none" />
                    </div>

                    <div class="flex items-center gap-3">
                        <Button variant="outline" class="h-11 px-4 rounded-xl border-slate-100 bg-[#f9fafb]/50 font-bold text-xs text-slate-500 shadow-none hover:bg-slate-50 transition-all">
                             <Filter class="mr-2 h-4 w-4" /> Filters
                        </Button>
                        <div class="flex items-center bg-slate-100/50 p-1 rounded-xl border border-slate-200/50 print:hidden">
                            <button 
                                @click="viewMode = 'list'"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-all flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider',
                                    viewMode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'
                                ]"
                            >
                                <ListIcon class="h-3 w-3" /> List
                            </button>
                            <button 
                                @click="viewMode = 'grid'"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-all flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider',
                                    viewMode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'
                                ]"
                            >
                                <LayoutGrid class="h-3 w-3" /> Grid
                            </button>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-if="viewMode === 'list' && strand.sub_strands?.length" class="divide-y divide-slate-50">
                    <div v-for="ss in strand.sub_strands" :key="ss.id" class="group/ss hover:bg-[#f9fafb]/50 transition-all">
                        <!-- Header Row -->
                        <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-11 w-11 rounded-xl bg-[#f9fafb] border border-slate-100 flex items-center justify-center text-slate-900 font-bold text-sm group-hover/ss:bg-blue-600 group-hover/ss:text-white transition-all shadow-sm">
                                    {{ ss.display_order }}
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-800">{{ ss.name }}</h3>
                                        <Badge variant="outline" class="bg-blue-50/50 border-0 text-[8px] font-bold uppercase px-1.5 py-0 text-blue-400">{{ ss.code }}</Badge>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ ss.learning_outcomes?.length || 0 }} Learning Goals
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Button @click="openOutcomeModal(ss.id)" variant="outline" class="h-9 px-3 rounded-lg border-blue-100 bg-blue-50/30 text-blue-600 font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all">
                                    <Plus class="mr-1.5 h-3.5 w-3.5" /> Add Goal
                                </Button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon" class="h-9 w-9 rounded-lg border border-slate-100 text-slate-300 hover:text-slate-600">
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="rounded-xl p-2 min-w-[140px]">
                                        <DropdownMenuItem @click="openSubStrandModal(ss)" class="rounded-lg font-bold text-xs">Edit</DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteSubStrand(ss.id)" class="rounded-lg font-bold text-xs text-red-600">Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <!-- Outcomes Section -->
                        <div class="px-6 pb-6 flex flex-col gap-2 pl-16">
                            <div v-for="outcome in ss.learning_outcomes" :key="outcome.id" class="relative group/outcome flex items-center justify-between p-4 rounded-xl bg-white border border-slate-50 hover:border-blue-100 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="h-6 w-6 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-[9px] group-hover/outcome:bg-blue-600 group-hover/outcome:text-white transition-all">
                                        {{ outcome.display_order }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-sm font-semibold text-slate-600 group-hover/outcome:text-slate-900 transition-colors">{{ outcome.outcome }}</p>
                                        <Badge variant="outline" class="text-[8px] font-bold uppercase border-slate-50 text-slate-400 bg-slate-50">{{ outcome.outcome_type || 'Standard' }}</Badge>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 opacity-0 group-hover/outcome:opacity-100 transition-all">
                                    <Button @click="openOutcomeModal(ss.id, outcome)" variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-slate-100 text-slate-400">
                                        <Edit2 class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button @click="deleteOutcome(outcome.id)" variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-red-50 hover:text-red-500 text-slate-300">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <div v-if="!ss.learning_outcomes?.length" class="py-4 text-center bg-[#f9fafb]/30 rounded-xl border border-dashed border-slate-100 hover:border-blue-200 transition-all cursor-pointer" @click="openOutcomeModal(ss.id)">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">No outcomes defined. Click to add.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div v-if="viewMode === 'grid' && strand.sub_strands?.length" class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6 bg-[#f9fafb]/30">
                    <div v-for="ss in strand.sub_strands" :key="ss.id" class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
                        <div class="p-6 border-b border-slate-50 relative overflow-hidden">
                            <div class="relative z-10 flex items-start justify-between gap-4 mb-2">
                                <div class="space-y-1">
                                    <Badge class="rounded bg-slate-900 text-white font-bold text-[8px] uppercase px-2 py-0.5 border-0">{{ ss.code }}</Badge>
                                    <h3 class="text-lg font-bold text-slate-900 leading-tight">{{ ss.name }}</h3>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-slate-400">
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="rounded-xl p-2 min-w-[120px]">
                                        <DropdownMenuItem @click="openSubStrandModal(ss)" class="font-bold text-xs">Edit</DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteSubStrand(ss.id)" class="font-bold text-xs text-red-600">Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <p class="text-xs font-medium text-slate-400 line-clamp-1">{{ ss.description || 'No description provided.' }}</p>
                        </div>

                        <div class="p-6 flex-1 space-y-3 max-h-[300px] overflow-y-auto scrollbar-hide">
                            <div v-for="outcome in ss.learning_outcomes" :key="outcome.id" class="p-4 rounded-xl bg-slate-50/50 border border-slate-100/50 flex gap-3">
                                 <div class="mt-1 h-1.5 w-1.5 rounded-full bg-blue-500 shrink-0"></div>
                                 <p class="text-xs font-semibold text-slate-600 leading-snug">{{ outcome.outcome }}</p>
                            </div>
                        </div>

                        <div class="p-4 bg-[#f9fafb] border-t border-slate-50 flex items-center justify-between rounded-b-2xl">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ ss.learning_outcomes?.length || 0 }} OUTCOMES</span>
                            <Button @click="openOutcomeModal(ss.id)" variant="ghost" class="text-blue-600 font-bold text-[9px] uppercase tracking-wider hover:bg-blue-50 p-1.5 rounded-lg">Add Goal</Button>
                        </div>
                    </div>
                </div>

                <div v-if="!strand.sub_strands?.length" class="py-24 text-center bg-white flex flex-col items-center justify-center gap-6">
                     <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200">
                        <GraduationCap class="h-10 w-10" />
                     </div>
                     <div class="space-y-1">
                         <h3 class="text-xl font-bold text-slate-900 tracking-tight uppercase">Expansion Required</h3>
                         <p class="text-xs text-slate-400 font-medium max-w-sm mx-auto leading-relaxed">Add sub-components to start defining the learning pathway.</p>
                     </div>
                     <Button @click="openSubStrandModal()" class="h-11 px-8 rounded-xl bg-slate-900 hover:bg-black font-bold text-xs text-white shadow-sm transition-all uppercase tracking-widest">
                        <Plus class="mr-2 h-4 w-4" /> Add Sub-strand
                     </Button>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-white border-t border-slate-50 flex items-center justify-between" v-if="strand.sub_strands?.length">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Found {{ strand.sub_strands?.length }} Sub-strands</p>
                    <div class="flex items-center gap-2">
                        <Button disabled variant="outline" class="h-9 px-4 rounded-xl border-slate-100 text-slate-300 font-bold text-[10px]">Prev</Button>
                        <Button disabled class="h-9 w-9 rounded-xl bg-slate-900 text-white font-bold text-xs">1</Button>
                        <Button disabled variant="outline" class="h-9 px-4 rounded-xl border-slate-100 text-slate-300 font-bold text-[10px]">Next</Button>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            
            <!-- Sub-Strand Modal -->
            <Dialog v-model:open="showSubStrandModal">
                <DialogContent class="sm:max-w-[425px] rounded-2xl border-slate-100 shadow-xl p-0 overflow-hidden">
                    <DialogHeader class="p-6 bg-slate-900 text-white">
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingSubStrand ? 'Edit' : 'Add' }} Sub-strand</DialogTitle>
                        <DialogDescription class="text-slate-400 text-xs">
                             Update pathway components for <span class="text-blue-400 font-bold">"{{ strand.name }}"</span>.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitSubStrand" class="grid gap-4 py-6 px-6 bg-white">
                        <div class="grid gap-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Name</Label>
                            <Input v-model="subStrandForm.name" placeholder="Registry name..." class="h-10 rounded-xl border-slate-100 text-sm font-semibold" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Code</Label>
                                <Input v-model="subStrandForm.code" placeholder="SS.01" class="h-10 rounded-xl border-slate-100 text-sm font-bold uppercase" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Order</Label>
                                <Input type="number" v-model="subStrandForm.display_order" class="h-10 rounded-xl border-slate-100 text-sm font-semibold" required />
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Description</Label>
                            <Textarea v-model="subStrandForm.description" placeholder="Technical breakdown..." class="rounded-xl border-slate-100 text-xs font-medium text-slate-600 min-h-[100px] p-4 leading-relaxed" />
                        </div>
                        <DialogFooter class="px-0 pt-2">
                            <Button type="submit" :disabled="subStrandForm.processing" class="w-full h-11 bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-xs uppercase text-white shadow-sm transition-all">
                                {{ subStrandForm.processing ? 'Saving...' : (editingSubStrand ? 'Update' : 'Save') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Outcome Modal -->
            <Dialog v-model:open="showOutcomeModal">
                <DialogContent class="sm:max-w-[425px] rounded-2xl border-slate-100 shadow-xl p-0 overflow-hidden">
                    <DialogHeader class="p-6 bg-blue-600 text-white">
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingOutcome ? 'Edit' : 'Add' }} Goal</DialogTitle>
                        <DialogDescription class="text-white/80 text-xs">
                            Define mastery level for this component.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitOutcome" class="grid gap-4 py-6 px-6 bg-white">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Goal Type</Label>
                                <select v-model="outcomeForm.outcome_type" class="w-full h-10 rounded-xl border border-slate-100 bg-[#f9fafb]/50 px-3 text-[10px] font-bold uppercase tracking-wider outline-none text-slate-700">
                                    <option>Standard</option>
                                    <option>Competency</option>
                                    <option>Behavioral</option>
                                    <option>Skill</option>
                                </select>
                            </div>
                            <div class="grid gap-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sequence</Label>
                                <Input type="number" v-model="outcomeForm.display_order" class="h-10 rounded-xl border-slate-100 text-sm font-semibold" required />
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Outcome Text</Label>
                            <Textarea v-model="outcomeForm.outcome" placeholder="Describe the expected mastery..." class="rounded-xl border-slate-100 text-sm font-medium text-slate-600 min-h-[120px] p-4 p-4 leading-relaxed" required />
                        </div>
                        <DialogFooter class="px-0 pt-2">
                            <Button type="submit" :disabled="outcomeForm.processing" class="w-full h-11 bg-slate-900 hover:bg-black rounded-xl font-bold text-xs uppercase text-white shadow-sm transition-all">
                                {{ outcomeForm.processing ? 'Saving...' : (editingOutcome ? 'Update' : 'Save') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none !important;
    }
}
</style>
