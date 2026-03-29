<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    BookOpenCheck, GraduationCap, ChevronRight, 
    Filter, Plus, Info, Search, X, LayoutGrid, List as ListIcon,
    Download, MoreHorizontal, Users, CheckCircle2, AlertTriangle, TrendingUp, BookOpen
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

</script>

<template>
    <Head title="Syllabus Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1600px] mx-auto bg-[#f9fafb]/30">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Syllabus Management</h1>
                    <p class="text-sm text-slate-500">Manage your subjects and learning areas here.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-semibold text-xs text-slate-600 shadow-sm transition-all hover:bg-slate-50">
                        <Download class="mr-2 h-3.5 w-3.5" /> Export
                    </Button>
                    <Dialog v-model:open="showAddModal">
                        <DialogTrigger as-child>
                            <Button class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-xs text-white shadow-sm transition-all">
                                <Plus class="mr-2 h-4 w-4" /> Add Subject
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[425px] rounded-2xl border-slate-100 shadow-xl p-0 overflow-hidden">
                            <DialogHeader class="p-6 bg-slate-900 text-white">
                                <DialogTitle class="text-xl font-bold tracking-tight">New Subject</DialogTitle>
                                <DialogDescription class="text-slate-400 text-xs">
                                    Add a new subject to the curriculum.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submit" class="grid gap-4 py-6 px-6">
                                <div class="grid gap-1.5">
                                    <Label for="name" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Subject Name</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g. Mathematics" class="rounded-xl border-slate-100 h-10 text-sm font-semibold" required />
                                </div>
                                <div class="grid gap-1.5">
                                    <Label for="learning_area" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Learning Area</Label>
                                    <Select v-model="form.learning_area_id">
                                        <SelectTrigger id="learning_area" class="rounded-xl border-slate-100 h-10 text-sm font-semibold">
                                            <SelectValue placeholder="Select area" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-xl border-slate-100">
                                            <SelectItem v-for="area in learningAreas" :key="area.id" :value="area.id.toString()">
                                                {{ area.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-1.5">
                                    <Label for="code" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Code</Label>
                                    <Input id="code" v-model="form.code" placeholder="e.g. MAT" class="rounded-xl border-slate-100 h-10 text-sm uppercase font-semibold" />
                                </div>
                                <div class="grid gap-1.5">
                                    <Label for="description" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Description</Label>
                                    <Textarea id="description" v-model="form.description" placeholder="Brief info..." class="rounded-xl border-slate-100 text-sm min-h-[60px]" />
                                </div>
                                <DialogFooter class="px-0 pt-2">
                                    <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-xs text-white h-11 shadow-sm uppercase tracking-widest">
                                        {{ form.processing ? 'Saving...' : 'Save Subject' }}
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
                </div>
            </div>

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
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
