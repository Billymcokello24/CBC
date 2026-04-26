<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Download, MoreHorizontal, Users, CheckCircle2, AlertTriangle, TrendingUp, BookOpen,
    BookCopy, Upload, FileText, X, AlertCircle, Plus, Filter, ChevronRight, Loader2, Sparkles, Search, Info
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subjects: any[];
    grades: any[];
    learningAreas: any[];
    teachers: any[];
    classes: any[];
    academicYear: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/syllabus' },
];

const searchQuery = ref('');
const showAddModal = ref(false);
const viewMode = ref<'grid' | 'list'>('list');

const filteredSubjects = computed(() => {
    if (!searchQuery.value) return props.subjects;
    const lowerQuery = searchQuery.value.toLowerCase();
    return props.subjects.filter(s => 
        s.name.toLowerCase().includes(lowerQuery) || 
        (s.code && s.code.toLowerCase().includes(lowerQuery))
    );
});

const form = useForm({
    name: '',
    learning_area_id: '',
    code: '',
    description: '',
});

const submit = () => {
    form.post(route('curriculum.syllabus.subjects.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

// Teacher Assignment Logic
const showAssignmentModal = ref(false);
const teacherSearch = ref('');
const assignmentForm = useForm({
    teacher_id: '',
    subject_id: '',
    class_id: '',
    academic_year_id: props.academicYear?.id || '',
    is_primary_teacher: true,
    is_active: true,
});

const selectedSubject = computed(() => {
    return props.subjects.find(s => s.id.toString() === assignmentForm.subject_id);
});

const filteredClasses = computed(() => {
    if (!selectedSubject.value) return [];
    
    // Filter classes to only show those that belong to the subject's grade levels
    const gradeLevelIds = selectedSubject.value.grade_level_ids || [];
    return props.classes.filter(c => gradeLevelIds.includes(c.grade_id));
});

const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    const search = teacherSearch.value.toLowerCase();
    return props.teachers.filter(t => 
        t.name.toLowerCase().includes(search) || 
        t.staff_number.toLowerCase().includes(search)
    );
});

const openAssignmentModal = (subjectId: string) => {
    assignmentForm.teacher_id = '';
    assignmentForm.class_id = '';
    assignmentForm.subject_id = subjectId;
    teacherSearch.value = '';
    assignmentForm.clearErrors();
    showAssignmentModal.value = true;
};

const submitAssignment = () => {
    assignmentForm.post(route('classes.allocations.store'), {
        onSuccess: () => {
            showAssignmentModal.value = false;
        },
    });
};

const showBulkModal = ref(false);
const bulkForm = useForm({
    file: null as File | null,
});

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        bulkForm.file = input.files[0];
    }
};

const submitBulk = () => {
    bulkForm.post(route('curriculum.syllabus.subjects.bulk'), {
        onSuccess: () => {
            showBulkModal.value = false;
            bulkForm.reset();
        },
    });
};

const dragOver = ref(false);
const route = (window as any).route;

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
    <Head title="Syllabus Management" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 sm:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-10 sm:pb-20 p-4 sm:p-6 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
                <div class="space-y-1">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                        <BookOpenCheck class="h-8 w-8 text-blue-600" />
                        Subjects
                    </h1>
                    <p class="text-xs sm:text-[15px] font-medium text-slate-500">Manage institutional learning areas and academic standards.</p>
                </div>
                
                <div class="flex items-center gap-2 sm:gap-3 bg-slate-100/50 p-1.5 rounded-2xl border border-slate-200/50">
                    <Dialog v-model:open="showAddModal">
                        <DialogTrigger as-child>
                            <Button class="h-10 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 font-bold text-xs text-white shadow-lg shadow-blue-200 transition-all border-0">
                                <Plus class="mr-2 h-4 w-4" /> Add Subject
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[425px] rounded-3xl border-0 shadow-2xl p-0 overflow-hidden bg-white">
                            <DialogHeader class="p-8 bg-slate-900 text-white relative">
                                <div class="absolute top-0 right-0 p-8 opacity-10">
                                    <BookOpen class="h-20 w-20 rotate-12" />
                                </div>
                                <div class="relative z-10">
                                    <DialogTitle class="text-2xl font-black tracking-tight mb-1">New Subject</DialogTitle>
                                    <DialogDescription class="text-slate-400 text-xs font-medium">
                                        Add a new subject to the curriculum.
                                    </DialogDescription>
                                </div>
                            </DialogHeader>
                            <form @submit.prevent="submit" class="p-8 space-y-6">
                                <div class="space-y-2">
                                    <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Subject Name</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g. Mathematics" class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold focus:ring-2 focus:ring-blue-600/10 transition-all" required />
                                </div>
                                <div class="space-y-2">
                                    <Label for="learning_area" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Learning Area</Label>
                                    <Select v-model="form.learning_area_id">
                                        <SelectTrigger id="learning_area" class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold focus:ring-2 focus:ring-blue-600/10 transition-all">
                                            <SelectValue placeholder="Select Area" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-2xl border-slate-100 shadow-2xl bg-white/95 backdrop-blur-xl">
                                            <SelectItem v-for="area in learningAreas" :key="area.id" :value="area.id.toString()" class="rounded-xl py-3 focus:bg-blue-50 focus:text-blue-700 font-semibold cursor-pointer">
                                                {{ area.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="space-y-2">
                                        <Label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Subject Code</Label>
                                        <Input id="code" v-model="form.code" placeholder="e.g. MAT" class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm uppercase font-black tracking-widest focus:ring-2 focus:ring-blue-600/10 transition-all" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="description" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Brief Description</Label>
                                    <Textarea id="description" v-model="form.description" placeholder="Provide extra context..." class="rounded-2xl border-slate-100 bg-slate-50/50 text-sm min-h-[100px] focus:ring-2 focus:ring-blue-600/10 transition-all" />
                                </div>
                                <DialogFooter class="pt-4">
                                    <Button type="submit" :disabled="form.processing" class="w-full h-14 bg-blue-600 hover:bg-blue-700 rounded-2xl font-black text-xs text-white shadow-xl shadow-blue-500/20 transition-all uppercase tracking-[0.2em]">
                                        {{ form.processing ? 'Saving...' : 'Save Subject' }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm overflow-hidden relative group transition-all hover:shadow-xl hover:-translate-y-1 duration-500">
                <div class="absolute -right-10 -bottom-10 opacity-[0.03] group-hover:scale-110 transition-transform duration-1000 text-blue-600">
                    <BookCopy class="h-64 w-64" />
                </div>
                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between relative z-10">
                    <div class="flex items-center gap-6">
                        <div class="h-20 w-20 rounded-[2rem] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 shadow-sm border border-blue-100/50 group-hover:rotate-6 transition-transform">
                            <Upload class="h-10 w-10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Bulk Syllabus Management</h2>
                                <Badge variant="secondary" class="text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border-emerald-100/50 px-2 py-0.5">Efficient</Badge>
                            </div>
                            <p class="text-sm font-medium text-slate-500 mt-1 max-w-md">Import multiple subjects at once using our standardized curriculum template.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button variant="outline" class="h-12 border-slate-200 font-bold px-8 rounded-2xl bg-white hover:bg-slate-50 transition-all text-[11px] uppercase tracking-widest" as-child>
                            <a :href="route('curriculum.syllabus.subjects.template')" download>
                                <Download class="mr-2 h-4 w-4 text-emerald-600" />
                                Download Template
                            </a>
                        </Button>
                        <Button @click="showBulkModal = true" class="h-13 bg-slate-900 hover:bg-slate-800 text-white font-black px-10 rounded-2xl shadow-2xl shadow-slate-200 transition-all border-0 text-[11px] uppercase tracking-[0.2em]">
                            <BookCopy class="mr-2 h-4 w-4" />
                            Upload CSV Dataset
                        </Button>
                    </div>
                </div>
            </div>

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
                                Connect a specialized teacher to a specific class for <span class="text-white font-bold">{{ selectedSubject?.name }}</span>.
                            </DialogDescription>
                        </div>
                    </DialogHeader>
                    
                    <form @submit.prevent="submitAssignment" class="p-8 space-y-6">
                        <!-- Class Selection -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-1">
                                <Label class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400">Target Class</Label>
                                <Badge v-if="filteredClasses.length" variant="outline" class="text-[9px] font-bold py-0 h-4 border-slate-100 text-slate-400 rounded-full">
                                    {{ filteredClasses.length }} Available
                                </Badge>
                            </div>
                            <Select v-model="assignmentForm.class_id">
                                <SelectTrigger class="h-12 rounded-2xl border-slate-100 bg-slate-50/50 text-sm font-bold shadow-none hover:bg-slate-50 transition-all" :class="{'border-red-200 ring-red-50': assignmentForm.errors.class_id}">
                                    <SelectValue placeholder="Which class is this for?" />
                                </SelectTrigger>
                                <SelectContent class="bg-white/90 backdrop-blur-lg rounded-2xl border-slate-100 shadow-2xl p-2 max-h-[300px]">
                                    <SelectItem v-for="cls in filteredClasses" :key="cls.id" :value="cls.id.toString()" class="rounded-xl py-3 focus:bg-blue-50 focus:text-blue-700 font-semibold cursor-pointer mb-1 last:mb-0">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                                {{ cls.grade_name }} — {{ cls.name }} {{ cls.stream ? '('+cls.stream+')' : '' }}
                                            </div>
                                            <span class="text-[9px] font-bold text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded uppercase">{{ cls.academic_year }}</span>
                                        </div>
                                    </SelectItem>
                                    <div v-if="filteredClasses.length === 0" class="p-8 text-center">
                                        <Info class="h-8 w-8 mx-auto text-slate-200 mb-2" />
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">No classes found for this subject's grades</p>
                                    </div>
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

            <!-- Stats Cards Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Subjects -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Subjects</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ subjects.length }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <BookOpen class="h-5 w-5" />
                    </div>
                </div>

                <!-- Active -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ subjects.length }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>

                <!-- Learning Areas -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Areas</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ learningAreas.length }}</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                </div>

                <!-- Updates -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Growth</p>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">NEW</h2>
                    </div>
                    <div class="h-11 w-11 rounded-xl bg-sky-50 flex items-center justify-center text-sky-500">
                        <TrendingUp class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                <!-- Filters Bar -->
                <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative w-full md:max-w-md">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                        <Input v-model="searchQuery" placeholder="Search by name or code..." class="pl-10 h-11 rounded-xl text-sm font-medium border-slate-100 bg-[#f9fafb]/50 focus:bg-white transition-all shadow-none" />
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

                <!-- Quick Filters -->
                <div class="px-6 py-4 border-b border-slate-50 flex flex-wrap gap-3 bg-white">
                    <div v-for="filter in ['Learning Areas', 'Grades', 'Statuses']" :key="filter" 
                         class="h-10 px-4 rounded-xl border border-slate-100 bg-[#fbfcfd] flex items-center justify-between min-w-[150px] text-xs font-bold text-slate-600 cursor-pointer hover:bg-slate-50 transition-colors">
                        {{ filter }}
                        <ChevronRight class="h-3 w-3 text-slate-300 rotate-90" />
                    </div>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-50">
                                <th class="pl-6 py-4 w-10">
                                    <div class="h-4 w-4 rounded border border-slate-200"></div>
                                </th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Subject</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Area</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="subject in filteredSubjects" :key="subject.id" class="group hover:bg-[#f9fafb]/50 transition-all">
                                <td class="pl-6 py-4">
                                    <div class="h-4 w-4 rounded border border-slate-200 group-hover:border-blue-400 transition-colors"></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs">
                                            {{ subject.name.substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800">{{ subject.name }}</span>
                                            <span class="text-[10px] font-medium text-slate-400 uppercase">{{ subject.code || 'SUB-' + subject.id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-xs font-semibold text-slate-600">{{ subject.learning_area?.name || 'General' }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <Badge class="rounded bg-emerald-50 text-emerald-600 border-0 text-[9px] font-bold px-2 py-0.5">
                                        ACTIVE
                                    </Badge>
                                </td>
                                <td class="px-4 py-4 text-xs font-semibold text-slate-400">
                                    2026-03-26
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-9 px-4 rounded-xl font-bold text-[10px] uppercase text-blue-600 hover:bg-blue-50">
                                                Manage <ChevronRight class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="rounded-2xl border-slate-100 shadow-xl p-3 min-w-[220px] bg-white">
                                            <div class="mb-2 border-b border-slate-50 pb-2">
                                                <h4 class="text-[9px] font-bold text-slate-400 uppercase px-1">Actions</h4>
                                            </div>
                                            
                                            <DropdownMenuItem @click="openAssignmentModal(subject.id.toString())" class="rounded-xl text-[10px] font-bold py-2.5 px-3 flex items-center gap-2 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer uppercase">
                                                <Users class="h-3.5 w-3.5" /> Assign Teacher
                                            </DropdownMenuItem>

                                            <div class="my-2 border-b border-slate-50"></div>
                                            <div class="mb-1 px-1">
                                                <h4 class="text-[9px] font-bold text-slate-400 uppercase">View Syllabus</h4>
                                            </div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <DropdownMenuItem v-for="grade in grades" :key="grade.id" as-child>
                                                    <Link :href="`/curriculum/syllabus/${subject.id}/${grade.id}`" class="rounded-lg text-[8px] font-bold py-1.5 px-2 flex items-center justify-center bg-slate-50 hover:bg-blue-600 hover:text-white transition-all cursor-pointer uppercase border border-transparent">
                                                        {{ grade.name }}
                                                    </Link>
                                                </DropdownMenuItem>
                                            </div>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-white border-t border-slate-50 flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400">Found {{ filteredSubjects.length }} items</p>
                    <div class="flex items-center gap-2">
                        <Button disabled variant="outline" class="h-9 rounded-xl border-slate-100 text-slate-300 font-bold px-3 text-xs">Prev</Button>
                        <Button disabled variant="outline" class="h-9 rounded-xl border-slate-200 text-slate-900 font-bold px-3 text-xs">1</Button>
                        <Button disabled variant="outline" class="h-9 rounded-xl border-slate-100 text-slate-300 font-bold px-3 text-xs">Next</Button>
                    </div>
                </div>
            </div>

            <!-- Bulk Upload Modal -->
            <Dialog v-model:open="showBulkModal">
                <DialogContent class="sm:max-w-[500px] rounded-[2.5rem] border-0 shadow-2xl p-0 overflow-hidden bg-card">
                    <DialogHeader class="p-10 bg-slate-900 text-white relative">
                        <div class="absolute top-0 right-0 p-10 opacity-10">
                            <BookCopy class="h-24 w-24" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle class="text-3xl font-black tracking-tight mb-1 italic">Bulk Ingestion</DialogTitle>
                            <DialogDescription class="text-slate-400 text-xs font-bold uppercase tracking-widest">
                                Syllabus Dataset Integration
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    <div class="p-10 space-y-8">
                        <div class="flex items-center justify-between rounded-[1.5rem] border border-slate-100 bg-slate-50/50 p-6 group hover:bg-blue-50/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform">
                                    <FileText class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">Standard Template</p>
                                    <p class="text-[10px] font-bold text-slate-400">Required CSV Schema</p>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" class="rounded-xl border-slate-200 font-black text-[9px] uppercase tracking-widest h-9 px-4 shadow-sm hover:bg-white" as-child>
                                <a :href="route('curriculum.syllabus.subjects.template')" download>
                                    <Download class="mr-2 h-3.5 w-3.5 text-emerald-600" />
                                    Download
                                </a>
                            </Button>
                        </div>

                        <div
                            class="relative flex flex-col items-center justify-center rounded-[2rem] border-3 border-dashed p-12 transition-all duration-500 group"
                            :class="[dragOver ? 'border-blue-600 bg-blue-50/50 scale-[0.98]' : 'border-slate-100 bg-slate-50/30', bulkForm.file ? 'border-emerald-500/50 bg-emerald-50/20' : '']"
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
                                    <Upload class="h-8 w-8 text-blue-600" />
                                </div>
                                <p class="mt-6 text-sm font-black text-slate-900 tracking-tight">Drop CSV file here</p>
                                <p class="mt-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">or click to browse local storage</p>
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
                            @click="submitBulk" 
                            :disabled="!bulkForm.file || bulkForm.processing"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white rounded-2xl h-16 font-black text-xs shadow-2xl shadow-slate-200 transition-all uppercase tracking-[0.3em] overflow-hidden group"
                        >
                            <span v-if="bulkForm.processing" class="flex items-center gap-2">
                                <Loader2 class="h-4 w-4 animate-spin" />
                                Processing Dataset...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Sparkles class="h-4 w-4 text-amber-400 group-hover:animate-pulse" />
                                Execute Import Task
                            </span>
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
