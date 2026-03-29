<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    BookOpenCheck, GraduationCap, ChevronRight, 
    Plus, Info, CheckCircle2, MoreVertical, 
    ArrowLeft, Eye, Edit2, Trash2, X, LayoutGrid, List as ListIcon,
    Download, MoreHorizontal, Users, AlertTriangle, TrendingUp, BookOpen, Search
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@/components/ui/tabs";
import { Switch } from "@/components/ui/switch";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subject: any;
    grade: any;
    strands: any[];
    teachers: any[];
    classes: any[];
    allocations: any[];
    academicYear: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/syllabus' },
    { title: props.subject.name, href: '#' },
];

// Modals State
const showStrandModal = ref(false);
const showSubStrandModal = ref(false);
const showOutcomeModal = ref(false); // No longer needed here, but keeping for reference if needed
const viewMode = ref<'grid' | 'list'>('list');
const editingStrand = ref<any>(null);
const editingSubStrand = ref<any>(null);
const editingOutcome = ref<any>(null);

// Topic (Strand) Form
const strandForm = useForm({
    name: '',
    code: '',
    display_order: 1,
    subject_id: props.subject.id,
    grade_level_id: props.grade.id,
});

const openStrandModal = (strand: any = null) => {
    editingStrand.value = strand;
    if (strand) {
        strandForm.name = strand.name;
        strandForm.code = strand.code;
        strandForm.display_order = strand.display_order;
    } else {
        strandForm.reset();
        strandForm.display_order = props.strands.length + 1;
    }
    showStrandModal.value = true;
};

const submitStrand = () => {
    if (editingStrand.value) {
        strandForm.put(route('curriculum.syllabus.topics.update', editingStrand.value.id), {
            onSuccess: () => {
                showStrandModal.value = false;
                editingStrand.value = null;
            },
        });
    } else {
        strandForm.post(route('curriculum.syllabus.topics.store'), {
            onSuccess: () => {
                showStrandModal.value = false;
            },
        });
    }
};

const deleteStrand = (id: number) => {
    if (confirm('Are you sure you want to remove this strand and all its sub-strands?')) {
        useForm({}).delete(route('curriculum.syllabus.topics.destroy', id));
    }
};

// Sub-strand (SubStrand) Form
const subStrandForm = useForm({
    name: '',
    code: '',
    display_order: 1,
    strand_id: null as number | null,
});

const openSubStrandModal = (strandId: number, subStrand: any = null) => {
    editingSubStrand.value = subStrand;
    subStrandForm.strand_id = strandId;
    if (subStrand) {
        subStrandForm.name = subStrand.name;
        subStrandForm.code = subStrand.code;
        subStrandForm.display_order = subStrand.display_order;
    } else {
        subStrandForm.name = '';
        subStrandForm.code = '';
        const parentStrand = props.strands.find(s => s.id === strandId);
        subStrandForm.display_order = (parentStrand?.sub_strands?.length || 0) + 1;
    }
    showSubStrandModal.value = true;
};

const submitSubStrand = () => {
    if (editingSubStrand.value) {
        subStrandForm.put(route('curriculum.syllabus.sub-topics.update', editingSubStrand.value.id), {
            onSuccess: () => {
                showSubStrandModal.value = false;
                editingSubStrand.value = null;
            },
        });
    } else {
        subStrandForm.post(route('curriculum.syllabus.sub-topics.store'), {
            onSuccess: () => {
                showSubStrandModal.value = false;
            },
        });
    }
};

const deleteSubStrand = (id: number) => {
    if (confirm('Are you sure you want to remove this sub-strand?')) {
        useForm({}).delete(route('curriculum.syllabus.sub-topics.destroy', id));
    }
};

// Learning Outcome Form
const outcomeForm = useForm({
    outcome: '',
    sub_strand_id: null as number | null,
    outcome_type: 'Standard',
    display_order: 1,
});

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
        const subStrand = props.strands.flatMap(s => s.sub_strands).find(ss => ss.id === subStrandId);
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
            },
        });
    } else {
        outcomeForm.post(route('curriculum.syllabus.outcomes.store'), {
            onSuccess: () => {
                showOutcomeModal.value = false;
            },
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

// Teacher Assignment Logic
const showAssignmentModal = ref(false);
const teacherSearch = ref('');
const assignmentForm = useForm({
    teacher_id: '',
    subject_id: props.subject.id,
    class_id: '',
    academic_year_id: props.academicYear?.id || '',
    is_primary_teacher: true,
    is_active: true,
});

const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    const search = teacherSearch.value.toLowerCase();
    return props.teachers.filter(t => 
        t.name.toLowerCase().includes(search) || 
        t.staff_number.toLowerCase().includes(search)
    );
});

const openAssignmentModal = (classId: string = '') => {
    assignmentForm.teacher_id = '';
    assignmentForm.class_id = classId;
    assignmentForm.is_primary_teacher = true;
    teacherSearch.value = '';
    assignmentForm.clearErrors();
    showAssignmentModal.value = true;
};

const submitAssignment = () => {
    assignmentForm.post(route('classes.allocations.store'), {
        onSuccess: () => {
            showAssignmentModal.value = false;
        },
        onError: (errors) => {
            console.error('Assignment failed:', errors);
        }
    });
};

const removeAssignment = (id: number) => {
    if (confirm('Are you sure you want to remove this teacher assignment?')) {
        useForm({}).delete(route('classes.allocations.destroy', id));
    }
};

const getTeacherForClass = (classId: number) => {
    return props.allocations.filter(a => a.class_id === classId);
};

</script>

<template>
    <Head :title="`${subject.name} - ${grade.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1600px] mx-auto bg-[#f9fafb]/30">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Link href="/curriculum/syllabus" class="group inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:text-blue-600 transition-all mb-1">
                        <ArrowLeft class="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Subjects
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ subject.name }} Strands</h1>
                    <p class="text-sm text-slate-500">Curriculum strands for {{ grade.name }}.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button @click="printSyllabus" variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-semibold text-xs text-slate-600 shadow-sm transition-all hover:bg-slate-50">
                        <Download class="mr-2 h-3.5 w-3.5" /> Export
                    </Button>
                    <Button @click="openStrandModal()" class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-xs text-white shadow-sm transition-all">
                        <Plus class="mr-2 h-4 w-4" /> Add Strand
                    </Button>
                </div>
            </div>

            <!-- Stats Cards Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Strands -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Strands</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ strands.length }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <BookOpen class="h-5 w-5" />
                    </div>
                </div>

                <!-- Sub-strands -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sub-strands</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ strands.reduce((acc: number, s: any) => acc + (s.sub_strands?.length || 0), 0) }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>

                <!-- Learning Outcomes -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Goals</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ strands.reduce((acc: number, s: any) => acc + (s.sub_strands?.reduce((acc2: number, ss: any) => acc2 + (ss.learning_outcomes?.length || 0), 0) || 0), 0) }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ strands.length > 0 ? 'LIVE' : 'EMPTY' }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-sky-50 flex items-center justify-center text-sky-500">
                        <TrendingUp class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Tabs -->
            <Tabs defaultValue="curriculum" class="w-full">
                <TabsList class="grid w-full grid-cols-2 max-w-md mx-auto mb-6 bg-slate-100/50 p-1 rounded-2xl h-12">
                    <TabsTrigger value="curriculum" class="rounded-xl font-bold text-xs uppercase tracking-wider data-[state=active]:bg-white data-[state=active]:shadow-sm">
                        <BookOpenCheck class="mr-2 h-4 w-4" /> Curriculum Strands
                    </TabsTrigger>
                    <TabsTrigger value="teachers" class="rounded-xl font-bold text-xs uppercase tracking-wider data-[state=active]:bg-white data-[state=active]:shadow-sm">
                        <Users class="mr-2 h-4 w-4" /> Teacher Assignments
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="curriculum">
                    <!-- Main Content Card (Original Curriculum Content) -->
                    <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                        <!-- Toolbar -->
                        <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="relative w-full md:max-w-md">
                                <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                                <Input placeholder="Search strands..." class="pl-10 h-11 rounded-xl text-sm font-medium border-slate-100 bg-[#f9fafb]/50 focus:bg-white transition-all shadow-none" />
                            </div>

                            <div class="flex items-center gap-3">
                                <Button variant="outline" class="h-11 px-4 rounded-xl border-slate-100 bg-[#f9fafb]/50 font-bold text-xs text-slate-500 shadow-none hover:bg-slate-50 transition-all">
                                     <Filter class="mr-2 h-4 w-4" /> Filters
                                </Button>
                                <Button variant="outline" class="h-11 w-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 p-0 shadow-none hover:bg-slate-50 transition-all text-slate-400">
                                    <MoreHorizontal class="h-5 w-5" />
                                </Button>
                            </div>
                        </div>

                        <!-- Table Section -->
                        <div class="overflow-x-auto" v-if="strands.length">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white border-b border-slate-50">
                                        <th class="pl-6 py-4 w-10">
                                            <div class="h-4 w-4 rounded border border-slate-200"></div>
                                        </th>
                                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Strand</th>
                                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Sub-strands</th>
                                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Goals</th>
                                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="strand in strands" :key="strand.id" class="group hover:bg-[#f9fafb]/50 transition-all">
                                        <td class="pl-6 py-4">
                                            <div class="h-4 w-4 rounded border border-slate-200 group-hover:border-blue-400 transition-colors"></div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs group-hover:bg-blue-600 group-hover:text-white transition-all">
                                                    {{ strand.display_order }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-slate-800">{{ strand.name }}</span>
                                                    <span class="text-[10px] font-medium text-slate-400 uppercase">{{ strand.code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center text-xs font-bold text-slate-600">
                                            {{ strand.sub_strands?.length || 0 }}
                                        </td>
                                        <td class="px-4 py-4 text-center text-xs font-bold text-slate-600">
                                            {{ strand.sub_strands?.reduce((acc: number, ss: any) => acc + (ss.learning_outcomes?.length || 0), 0) || 0 }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <Badge class="rounded bg-emerald-50 text-emerald-600 border-0 text-[9px] font-bold px-2 py-0.5">
                                                ACTIVE
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <Link :href="`/curriculum/syllabus/topics/${strand.id}`" class="h-9 px-4 rounded-xl bg-blue-50 text-blue-600 font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all inline-flex items-center">
                                                    Manage <ChevronRight class="ml-1 h-3.5 w-3.5" />
                                                </Link>
                                                <Button @click="openStrandModal(strand)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-slate-100 text-slate-400">
                                                    <Edit2 class="h-3.5 w-3.5" />
                                                </Button>
                                                <Button @click="deleteStrand(strand.id)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-red-50 hover:text-red-600 text-slate-300">
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="py-20 text-center bg-white flex flex-col items-center justify-center gap-4">
                             <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200">
                                <GraduationCap class="h-8 w-8" />
                             </div>
                             <div class="space-y-1">
                                 <h3 class="text-xl font-bold text-slate-900 tracking-tight">Add Strands</h3>
                                 <p class="text-xs text-slate-400 font-medium max-w-xs mx-auto">No strands have been defined for {{ subject.name }} yet.</p>
                             </div>
                             <Button @click="openStrandModal()" class="h-11 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 font-bold text-xs text-white shadow-sm transition-all uppercase tracking-widest">
                                <Plus class="mr-2 h-4 w-4" /> Add First Strand
                             </Button>
                        </div>

                        <!-- Footer -->
                        <div class="p-6 bg-white border-t border-slate-50 flex items-center justify-between" v-if="strands.length">
                            <p class="text-xs font-bold text-slate-400">Found {{ strands.length }} strands</p>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="teachers">
                    <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Teacher Assignments</h3>
                                <p class="text-xs text-slate-500">Assign teachers for {{ subject.name }} across {{ grade.name }} classes.</p>
                            </div>
                            <Button @click="openAssignmentModal()" class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-bold text-xs text-white shadow-sm transition-all uppercase tracking-widest">
                                <Plus class="mr-2 h-4 w-4" /> New Assignment
                            </Button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white border-b border-slate-50">
                                        <th class="pl-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Class</th>
                                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Assigned Teacher(s)</th>
                                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Role</th>
                                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="cls in classes" :key="cls.id" class="group hover:bg-[#f9fafb]/50 transition-all">
                                        <td class="pl-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-800">{{ cls.name }}</span>
                                                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ cls.stream || 'No Stream' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div v-if="getTeacherForClass(cls.id).length" class="space-y-2">
                                                <div v-for="alloc in getTeacherForClass(cls.id)" :key="alloc.id" class="flex items-center gap-2">
                                                    <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-[10px]">
                                                        {{ alloc.teacher_name.substring(0, 2).toUpperCase() }}
                                                    </div>
                                                    <span class="text-xs font-semibold text-slate-700">{{ alloc.teacher_name }}</span>
                                                </div>
                                            </div>
                                            <span v-else class="text-[10px] font-bold text-orange-400 uppercase italic">Not Assigned</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div v-if="getTeacherForClass(cls.id).length" class="flex items-center gap-2">
                                                <Badge v-for="alloc in getTeacherForClass(cls.id)" :key="alloc.id" 
                                                       :variant="alloc.is_primary ? 'default' : 'secondary'"
                                                       class="text-[8px] font-bold px-1.5 py-0.5 rounded-md uppercase tracking-tighter">
                                                    {{ alloc.is_primary ? 'Primary' : 'Assistant' }}
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <Button v-if="getTeacherForClass(cls.id).length === 0" @click="openAssignmentModal(cls.id.toString())" variant="ghost" class="h-8 px-3 rounded-lg bg-blue-50 text-blue-600 font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all">
                                                    Assign
                                                </Button>
                                                <DropdownMenu v-else>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button variant="ghost" class="h-8 w-8 p-0 rounded-lg">
                                                            <MoreVertical class="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" class="rounded-xl border-slate-100 shadow-xl bg-white p-2">
                                                        <DropdownMenuItem @click="openAssignmentModal(cls.id.toString())" class="text-[10px] font-bold uppercase rounded-lg px-3 py-2 cursor-pointer hover:bg-slate-50">
                                                            Add Another Teacher
                                                        </DropdownMenuItem>
                                                        <div class="border-t border-slate-50 my-1"></div>
                                                        <DropdownMenuItem v-for="alloc in getTeacherForClass(cls.id)" :key="alloc.id" @click="removeAssignment(alloc.id)" class="text-[10px] font-bold uppercase text-red-500 rounded-lg px-3 py-2 cursor-pointer hover:bg-red-50">
                                                            Remove {{ alloc.teacher_name }}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>

            <!-- MODALS -->
            
            <!-- Strand Modal (Original) -->
            <Dialog v-model:open="showStrandModal">
                <DialogContent class="sm:max-w-[425px] rounded-2xl border-slate-100 shadow-xl p-0 overflow-hidden">
                    <DialogHeader class="p-6 bg-slate-900 text-white">
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingStrand ? 'Edit Strand' : 'Add Strand' }}</DialogTitle>
                        <DialogDescription class="text-slate-400 text-xs">
                            Define a main strand for the curriculum.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitStrand" class="grid gap-4 py-6 px-6 bg-white">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Order</Label>
                                <Input type="number" v-model="strandForm.display_order" class="h-10 rounded-xl border-slate-100 text-sm font-semibold" required />
                            </div>
                            <div class="grid gap-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Code</Label>
                                <Input v-model="strandForm.code" placeholder="e.g. MAT.T1" class="h-10 rounded-xl border-slate-100 text-sm uppercase font-semibold" />
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Strand Name</Label>
                            <Input v-model="strandForm.name" placeholder="e.g. Numbers" class="h-10 rounded-xl border-slate-100 text-sm font-semibold" required />
                        </div>
                        <DialogFooter class="px-0 pt-2">
                            <Button type="submit" :disabled="strandForm.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-xs text-white h-11 shadow-sm transition-all uppercase tracking-widest">
                                {{ strandForm.processing ? 'Saving...' : (editingStrand ? 'Update' : 'Save') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Teacher Assignment Modal -->
            <Dialog v-model:open="showAssignmentModal">
                <DialogContent class="sm:max-w-[450px] rounded-[2rem] border-0 shadow-2xl p-0 overflow-hidden bg-white/95 backdrop-blur-xl">
                    <DialogHeader class="p-8 bg-gradient-to-br from-blue-600 to-indigo-700 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <Users class="h-24 w-24 rotate-12" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle class="text-2xl font-black tracking-tight mb-1">Assign Teacher</DialogTitle>
                            <DialogDescription class="text-blue-100 text-xs font-medium opacity-90">
                                Connect a specialized teacher to a specific class for <span class="text-white font-bold">{{ subject.name }}</span>.
                            </DialogDescription>
                        </div>
                    </DialogHeader>
                    
                    <form @submit.prevent="submitAssignment" class="p-8 space-y-6">
                        <!-- Class Selection -->
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 px-1">Target Class</Label>
                            <Select v-model="assignmentForm.class_id">
                                <SelectTrigger class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold shadow-none hover:bg-slate-50 transition-all" :class="{'border-red-200 ring-red-50': assignmentForm.errors.class_id}">
                                    <SelectValue placeholder="Which class is this for?" />
                                </SelectTrigger>
                                <SelectContent class="bg-white/90 backdrop-blur-lg rounded-2xl border-slate-100 shadow-2xl p-2 max-h-[300px]">
                                    <SelectItem v-for="cls in classes" :key="cls.id" :value="cls.id.toString()" class="rounded-xl py-3 focus:bg-blue-50 focus:text-blue-700 font-semibold cursor-pointer mb-1 last:mb-0">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                                {{ cls.name }} {{ cls.stream ? '('+cls.stream+')' : '' }}
                                            </div>
                                            <span class="text-[9px] font-bold text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded uppercase">{{ cls.academic_year }}</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="assignmentForm.errors.class_id" class="text-[10px] font-bold text-red-500 px-1">{{ assignmentForm.errors.class_id }}</p>
                        </div>

                        <!-- Teacher Selection with Search -->
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 px-1">Select Teacher</Label>
                            <Select v-model="assignmentForm.teacher_id">
                                <SelectTrigger class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold shadow-none hover:bg-slate-50 transition-all" :class="{'border-red-200 ring-red-50': assignmentForm.errors.teacher_id}">
                                    <SelectValue placeholder="Assign a teacher..." />
                                </SelectTrigger>
                                <SelectContent class="bg-white/90 backdrop-blur-lg rounded-2xl border-slate-100 shadow-2xl p-0 overflow-hidden max-h-[400px]">
                                    <div class="p-3 border-b border-slate-50 sticky top-0 bg-white/50 backdrop-blur-md z-10">
                                        <div class="relative">
                                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" />
                                            <Input v-model="teacherSearch" placeholder="Search teacher by name or staff ID..." class="h-9 pl-9 rounded-xl border-slate-100 text-xs font-medium bg-white focus:ring-0 shadow-none border-0 ring-1 ring-slate-100" />
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <SelectItem v-for="teacher in filteredTeachers" :key="teacher.id" :value="teacher.id.toString()" class="rounded-xl py-3 focus:bg-blue-50 focus:text-blue-700 font-semibold cursor-pointer mb-1 last:mb-0">
                                            <div class="flex flex-col">
                                                <span>{{ teacher.name }}</span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ teacher.staff_number }}</span>
                                            </div>
                                        </SelectItem>
                                        <div v-if="filteredTeachers.length === 0" class="p-8 text-center">
                                            <Search class="h-8 w-8 mx-auto text-slate-200 mb-2" />
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No teachers matching your search</p>
                                        </div>
                                    </div>
                                </SelectContent>
                            </Select>
                            <p v-if="assignmentForm.errors.teacher_id" class="text-[10px] font-bold text-red-500 px-1">{{ assignmentForm.errors.teacher_id }}</p>
                        </div>

                        <!-- Role Toggles -->
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex items-center justify-between p-5 bg-blue-50/30 rounded-[1.5rem] border border-blue-100/30 group hover:bg-blue-50/50 transition-all cursor-pointer" @click="assignmentForm.is_primary_teacher = !assignmentForm.is_primary_teacher">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <CheckCircle2 class="h-4 w-4 text-blue-600" :class="{'opacity-20': !assignmentForm.is_primary_teacher}" />
                                        <Label class="text-[11px] font-black uppercase tracking-wider text-slate-700 cursor-pointer">Primary Teacher</Label>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-medium">Lead instructor for this specific class.</p>
                                </div>
                                <Switch :checked="assignmentForm.is_primary_teacher" />
                            </div>
                        </div>

                        <DialogFooter class="px-0 pt-4 pb-2">
                            <Button type="submit" :disabled="assignmentForm.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-2xl font-black text-xs text-white h-14 shadow-xl shadow-blue-500/20 transition-all uppercase tracking-[0.2em]">
                                {{ assignmentForm.processing ? 'Recording...' : 'Verify & Confirm' }}
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
