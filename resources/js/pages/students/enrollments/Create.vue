<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { 
    ArrowLeft, CheckCircle2, GraduationCap, Loader2, 
    Save, UserPlus, AlertTriangle, User, Calendar, 
    School, BookOpen, Layers, Info, Search, X
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import axios from 'axios';
import { format } from 'date-fns';

const props = defineProps<{
    academicYears: Array<{ id: number; name: string }>;
    academicTerms: Array<{ id: number; name: string }>;
    classes: Array<{ id: number; name: string; grade_id: number; stream_id: number }>;
    streams: Array<{ id: number; name: string }>;
    grades: Array<{ id: number; name: string }>;
    studentId?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
    { title: 'New Enrollment', href: '/students/enrollments/create' },
];

const form = useForm({
    student_id: props.studentId || '',
    academic_year_id: '',
    academic_term_id: '',
    class_id: '',
    stream_id: '',
    admission_number: '',
    enrollment_date: format(new Date(), 'yyyy-MM-dd'),
    status: 'active',
    entry_type: 'new',
    boarding_status: 'day',
    sponsor_type: 'Self',
    previous_school: '',
    notes: '',
});

// Student Search logic
const searchQuery = ref('');
const isSearching = ref(false);
const searchResults = ref<any[]>([]);
const selectedStudent = ref<any>(null);

const searchStudents = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    
    isSearching.value = true;
    try {
        const response = await axios.get(`/api/v1/student/students?filter[search]=${searchQuery.value}`);
        searchResults.value = response.data.data;
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        isSearching.value = false;
    }
};

let searchTimeout: any = null;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchStudents, 300);
});

const selectStudent = (student: any) => {
    selectedStudent.value = student;
    form.student_id = student.id;
    form.admission_number = student.admission_number || '';
    searchQuery.value = '';
    searchResults.value = [];
};

const clearStudent = () => {
    selectedStudent.value = null;
    form.student_id = '';
    form.admission_number = '';
};

// Auto-select class based on Grade + Stream
const selectedGradeId = ref<string>('');
const selectedStreamId = ref<string>('');

watch([selectedGradeId, selectedStreamId], ([gradeId, streamId]) => {
    if (gradeId) {
        const match = props.classes.find(c => 
            c.grade_id == parseInt(gradeId) && 
            (!streamId || c.stream_id == parseInt(streamId))
        );
        if (match) {
            form.class_id = match.id.toString();
            form.stream_id = streamId;
        } else {
            form.class_id = '';
        }
    }
});

onMounted(async () => {
    if (props.studentId) {
        try {
            const response = await axios.get(`/api/v1/student/students/${props.studentId}`);
            selectStudent(response.data.data);
        } catch (e) {
            console.error('Failed to load preselected student', e);
        }
    }
    
    // Set default academic year if one is marked current
    // (Assuming we might pass more data or just pick the first one)
    if (props.academicYears.length > 0) {
        form.academic_year_id = props.academicYears[0].id.toString();
    }
});

const submit = () => {
    form.post('/students/enrollments', {
        preserveScroll: true,
    });
};

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Formal Enrollment" />

        <div class="h-full flex-1 flex-col gap-8 p-6 max-w-[1200px] mx-auto animate-in fade-in duration-700">
            <!-- Header section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-xl sm:rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 shrink-0">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase italic">Formal Ingest</h1>
                        <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-widest mt-1 italic opacity-70">Academic Assignment Protocol</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-10 sm:h-11 px-4 sm:px-6 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex-1 sm:flex-none">
                        <Link href="/students/enrollments">
                            <ArrowLeft class="w-4 h-4 mr-2" /> Registry
                        </Link>
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 sm:gap-8 pb-12">
                <!-- 1. Student Selection -->
                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                    <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                            <User class="h-5 w-5 text-blue-600" />
                            Entity Identification
                        </h2>
                        <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Source Data Hook</p>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div v-if="!selectedStudent" class="relative">
                            <Label for="student-search" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Scan Registry (Name / ADM)</Label>
                            <div class="mt-2 relative group">
                                <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                                <Input 
                                    id="student-search"
                                    v-model="searchQuery"
                                    placeholder="ENTRY..."
                                    class="h-12 pl-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest"
                                />
                                <div v-if="isSearching" class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <Loader2 class="h-4 w-4 animate-spin text-blue-500" />
                                </div>
                            </div>
                            
                            <div v-if="searchResults.length > 0" class="absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-2xl max-h-72 overflow-hidden overflow-y-auto animate-in fade-in slide-in-from-top-2 duration-200">
                                <div 
                                    v-for="student in searchResults" 
                                    :key="student.id"
                                    @click="selectStudent(student)"
                                    class="flex items-center px-4 py-4 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors group"
                                >
                                    <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center mr-4 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                                        <User class="h-5 w-5" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[11px] font-black text-slate-900 uppercase tracking-widest italic group-hover:text-blue-600 transition-colors">{{ student.first_name }} {{ student.last_name }}</p>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 italic">Handle: {{ student.admission_number || 'VOID' }} | Context: {{ student.status }}</p>
                                    </div>
                                    <Badge variant="outline" class="ml-2 rounded-md px-2 py-0.5 text-[8px] font-black uppercase tracking-widest bg-slate-50 text-slate-400 border-slate-100">{{ student.status }}</Badge>
                                </div>
                            </div>
                            <InputError :message="form.errors.student_id" class="mt-2" />
                        </div>

                        <div v-else class="flex flex-col sm:flex-row items-center justify-between p-6 bg-blue-50/30 border border-blue-100/50 rounded-2xl gap-4">
                            <div class="flex items-center text-center sm:text-left flex-col sm:flex-row">
                                <div class="h-16 w-16 rounded-2xl bg-blue-600 text-white flex items-center justify-center sm:mr-6 mb-4 sm:mb-0 shadow-lg shadow-blue-500/20">
                                    <User class="h-8 w-8" />
                                </div>
                                <div>
                                    <p class="text-sm sm:text-base font-black text-slate-900 uppercase italic tracking-tight">{{ selectedStudent.first_name }} {{ selectedStudent.last_name }}</p>
                                    <p class="text-[9px] sm:text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mt-1.5 italic opacity-80">System Handle: {{ selectedStudent.admission_number || 'NOT_ASSIGNED' }}</p>
                                </div>
                            </div>
                            <Button type="button" variant="ghost" size="icon" @click="clearStudent" class="h-10 w-10 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all shrink-0">
                                <X class="h-5 w-5" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                    <!-- 2. Academic Assignment -->
                    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                        <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5">
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                                <Layers class="h-5 w-5 text-blue-600" />
                                Academic Linkage
                            </h2>
                            <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Spatio-Temporal Mapping</p>
                        </div>
                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Epoch</Label>
                                    <div class="relative">
                                        <select v-model="form.academic_year_id" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option v-for="year in academicYears" :key="year.id" :value="year.id.toString()">{{ year.name }}</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.academic_year_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Temporal Node (Term)</Label>
                                    <div class="relative">
                                        <select v-model="form.academic_term_id" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="">Full Cycle</option>
                                            <option v-for="term in academicTerms" :key="term.id" :value="term.id.toString()">{{ term.name }}</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.academic_term_id" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Level / Grade</Label>
                                    <div class="relative">
                                        <select v-model="selectedGradeId" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="">Select level</option>
                                            <option v-for="grade in grades" :key="grade.id" :value="grade.id.toString()">{{ grade.name }}</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Context / Stream</Label>
                                    <div class="relative">
                                        <select v-model="selectedStreamId" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="">Unmapped</option>
                                            <option v-for="stream in streams" :key="stream.id" :value="stream.id.toString()">{{ stream.name }}</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.stream_id" />
                                </div>
                            </div>
                            
                            <div v-if="form.class_id" class="px-6 py-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl flex items-center animate-in fade-in zoom-in-95 duration-300">
                                <CheckCircle2 class="h-5 w-5 text-emerald-600 mr-3 shrink-0" />
                                <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest italic leading-tight">Registry Match Found: Logical Link Validated</span>
                            </div>
                            <div v-else-if="selectedGradeId" class="px-6 py-4 bg-amber-50/50 border border-amber-100 rounded-2xl flex items-center animate-in fade-in zoom-in-95 duration-300">
                                <AlertTriangle class="h-5 w-5 text-amber-600 mr-3 shrink-0" />
                                <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest italic leading-tight">Registry Exception: No Logical Context for this Path</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Lifecycle Details -->
                    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                        <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5">
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                                <Calendar class="h-5 w-5 text-blue-600" />
                                Registry Pulse
                            </h2>
                            <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Logistics & State Ingest</p>
                        </div>
                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Registry Handle (ADM)</Label>
                                <Input 
                                    v-model="form.admission_number" 
                                    placeholder="SYNTAX: 2024-XXXX"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest text-blue-600 italic px-4"
                                />
                                <InputError :message="form.errors.admission_number" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ingest Epoch</Label>
                                    <Input 
                                        type="date"
                                        v-model="form.enrollment_date" 
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest px-4"
                                    />
                                    <InputError :message="form.errors.enrollment_date" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Neural State</Label>
                                    <div class="relative">
                                        <select v-model="form.status" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="active">Active</option>
                                            <option value="withdrawn">Withdrawn</option>
                                            <option value="transferred">Transferred</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Metadata Payload -->
                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                    <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                            <Info class="h-5 w-5 text-blue-600" />
                            Metadata Payload
                        </h2>
                        <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Supplemental Logic</p>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ingest Protocol</Label>
                                <div class="relative">
                                    <select v-model="form.entry_type" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="new">New Student</option>
                                        <option value="transfer">Transfer In</option>
                                        <option value="continuing">Continuing</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.entry_type" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Residency Hub</Label>
                                <div class="relative">
                                    <select v-model="form.boarding_status" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="day">Day Scholar</option>
                                        <option value="boarding">Boarder</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.boarding_status" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fee Propagation</Label>
                                <div class="relative">
                                    <select v-model="form.sponsor_type" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="Self">Self / Parent</option>
                                        <option value="Scholarship">Scholarship</option>
                                        <option value="Government">Government</option>
                                        <option value="NGO">NGO / Donor</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.sponsor_type" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Previous Context (Legacy)</Label>
                                <Input 
                                    v-model="form.previous_school" 
                                    placeholder="VOID"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest px-4"
                                />
                                <InputError :message="form.errors.previous_school" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Internal Annotation</Label>
                                <Input 
                                    v-model="form.notes" 
                                    placeholder="LOG..."
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest px-4"
                                />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-10 border-t border-slate-100 italic">
                    <div class="hidden sm:flex items-center gap-3 px-5 py-2 rounded-2xl bg-blue-50 text-blue-700 border border-blue-100/50 shadow-sm">
                        <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest">Registry Sync Armed</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <Button type="button" variant="ghost" @click="form.reset()" class="w-full sm:w-auto text-slate-400 hover:text-slate-900 font-black text-[10px] uppercase tracking-widest h-12 px-8 rounded-xl transition-all">
                            Reset Form
                        </Button>
                        <Button type="submit" :disabled="form.processing || !form.student_id" class="w-full sm:w-auto h-12 px-10 rounded-xl bg-slate-900 text-white hover:bg-slate-800 font-black text-[11px] uppercase tracking-widest shadow-xl shadow-slate-900/10 transition-all border-0">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Finalize Ingest
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Scoped styles kept for safety if needed, though Tailwind classes preferred */
</style>
