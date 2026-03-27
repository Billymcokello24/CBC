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
        <Head title="Enroll Learner" />

        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Formal Enrollment</h1>
                    <p class="text-slate-500 mt-1">Assign a learner to an academic year, grade, and stream.</p>
                </div>
                <Link href="/students/enrollments" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Registry
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- 1. Student Selection -->
                <Card class="border-slate-200 shadow-sm overflow-visible">
                    <CardHeader class="bg-slate-50/50 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <User class="w-5 h-5 text-indigo-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">Student Selection</CardTitle>
                                <CardDescription>Identify the learner to be enrolled</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div v-if="!selectedStudent" class="relative">
                            <Label for="student-search">Search Learner (Name or Admission #)</Label>
                            <div class="mt-1 relative">
                                <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                <Input 
                                    id="student-search"
                                    v-model="searchQuery"
                                    placeholder="Type to search..."
                                    class="pl-10 h-10 ring-offset-background focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <div v-if="isSearching" class="absolute right-3 top-3">
                                    <Loader2 class="h-4 w-4 animate-spin text-slate-400" />
                                </div>
                            </div>
                            
                            <div v-if="searchResults.length > 0" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg max-h-60 overflow-auto">
                                <div 
                                    v-for="student in searchResults" 
                                    :key="student.id"
                                    @click="selectStudent(student)"
                                    class="flex items-center px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors"
                                >
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center mr-3">
                                        <User class="h-4 w-4 text-slate-500" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-900">{{ student.first_name }} {{ student.last_name }}</p>
                                        <p class="text-xs text-slate-500">Adm: {{ student.admission_number || 'N/A' }} | Status: {{ student.status }}</p>
                                    </div>
                                    <Badge variant="outline" class="ml-2 capitalize">{{ student.status }}</Badge>
                                </div>
                            </div>
                            <InputError :message="form.errors.student_id" class="mt-2" />
                        </div>

                        <div v-else class="flex items-center justify-between p-4 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                            <div class="flex items-center">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                                    <User class="h-6 w-6 text-indigo-600" />
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-indigo-900">{{ selectedStudent.first_name }} {{ selectedStudent.last_name }}</p>
                                    <p class="text-sm text-indigo-600/80">Admission Number: {{ selectedStudent.admission_number || 'Not Assigned' }}</p>
                                </div>
                            </div>
                            <Button type="button" variant="ghost" size="icon" @click="clearStudent" class="text-indigo-600 hover:bg-indigo-100">
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- 2. Academic Assignment -->
                    <Card class="border-slate-200 shadow-sm">
                        <CardHeader class="bg-slate-50/50 border-b border-slate-100">
                            <div class="flex items-center space-x-2">
                                <div class="p-2 bg-indigo-100 rounded-lg">
                                    <GraduationCap class="w-5 h-5 text-indigo-600" />
                                </div>
                                <CardTitle class="text-lg">Academic Assignment</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>Academic Year</Label>
                                    <Select v-model="form.academic_year_id">
                                        <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10">
                                            <SelectValue placeholder="Select Year" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="year in academicYears" :key="year.id" :value="year.id.toString()">
                                                {{ year.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.academic_year_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Academic Term (Optional)</Label>
                                    <Select v-model="form.academic_term_id">
                                        <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10">
                                            <SelectValue placeholder="Select Term" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Full Year</SelectItem>
                                            <SelectItem v-for="term in academicTerms" :key="term.id" :value="term.id.toString()">
                                                {{ term.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.academic_term_id" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>Grade / Level</Label>
                                    <Select v-model="selectedGradeId">
                                        <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10">
                                            <SelectValue placeholder="Select Grade" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="grade in grades" :key="grade.id" :value="grade.id.toString()">
                                                {{ grade.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Stream (Optional)</Label>
                                    <Select v-model="selectedStreamId">
                                        <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10">
                                            <SelectValue placeholder="Select Stream" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Unassigned</SelectItem>
                                            <SelectItem v-for="stream in streams" :key="stream.id" :value="stream.id.toString()">
                                                {{ stream.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.stream_id" />
                                </div>
                            </div>
                            
                            <div v-if="form.class_id" class="px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center">
                                <CheckCircle2 class="w-4 h-4 text-emerald-600 mr-2" />
                                <span class="text-xs font-medium text-emerald-700">Matched Class Record Found</span>
                            </div>
                            <div v-else-if="selectedGradeId" class="px-4 py-2 bg-amber-50 border border-amber-100 rounded-lg flex items-center">
                                <AlertTriangle class="w-4 h-4 text-amber-600 mr-2" />
                                <span class="text-xs font-medium text-amber-700">No matching class record for this combo</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- 3. Lifecycle Details -->
                    <Card class="border-slate-200 shadow-sm">
                        <CardHeader class="bg-slate-50/50 border-b border-slate-100">
                            <div class="flex items-center space-x-2">
                                <div class="p-2 bg-indigo-100 rounded-lg">
                                    <Calendar class="w-5 h-5 text-indigo-600" />
                                </div>
                                <CardTitle class="text-lg">Lifecycle Details</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-6 space-y-4">
                            <div class="space-y-2">
                                <Label>Admission / Enrollment Number</Label>
                                <Input 
                                    v-model="form.admission_number" 
                                    placeholder="e.g. 2024-001"
                                    class="h-10 ring-offset-background focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError :message="form.errors.admission_number" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>Enrollment Date</Label>
                                    <Input 
                                        type="date"
                                        v-model="form.enrollment_date" 
                                        class="h-10 ring-offset-background focus:ring-2 focus:ring-indigo-600/10"
                                    />
                                    <InputError :message="form.errors.enrollment_date" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Initial Status</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="withdrawn">Withdrawn</SelectItem>
                                            <SelectItem value="transferred">Transferred</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- 4. Additional Information -->
                <Card class="border-slate-200 shadow-sm">
                    <CardHeader class="bg-slate-50/50 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <Info class="w-5 h-5 text-indigo-600" />
                            </div>
                            <CardTitle class="text-lg">Additional Information</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <Label>Entry Type</Label>
                                <Select v-model="form.entry_type">
                                    <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10 text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="new">New Student</SelectItem>
                                        <SelectItem value="transfer">Transfer In</SelectItem>
                                        <SelectItem value="continuing">Continuing</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.entry_type" />
                            </div>
                            <div class="space-y-2">
                                <Label>Boarding Status</Label>
                                <Select v-model="form.boarding_status">
                                    <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10 text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="day">Day Scholar</SelectItem>
                                        <SelectItem value="boarding">Boarder</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.boarding_status" />
                            </div>
                            <div class="space-y-2">
                                <Label>Sponsor / Fee Payer</Label>
                                <Select v-model="form.sponsor_type">
                                    <SelectTrigger class="focus:ring-2 focus:ring-indigo-600/10 text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Self">Self / Parent</SelectItem>
                                        <SelectItem value="Scholarship">Scholarship</SelectItem>
                                        <SelectItem value="Government">Government Sponsored</SelectItem>
                                        <SelectItem value="NGO">NGO / Donor</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.sponsor_type" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="space-y-2">
                                <Label>Previous School (if transfer)</Label>
                                <Input 
                                    v-model="form.previous_school" 
                                    placeholder="N/A"
                                    class="h-10 ring-offset-background focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError :message="form.errors.previous_school" />
                            </div>
                            <div class="space-y-2">
                                <Label>Internal Notes</Label>
                                <Input 
                                    v-model="form.notes" 
                                    placeholder="Any additional details..."
                                    class="h-10 ring-offset-background focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-slate-100">
                    <Button type="button" variant="outline" @click="form.reset()">
                        Reset Form
                    </Button>
                    <Button type="submit" :disabled="form.processing || !form.student_id" class="px-8 bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/20 shadow-lg">
                        <Save v-if="!form.processing" class="w-4 h-4 mr-2" />
                        <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
                        Finalize Enrollment
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.focus\:ring-indigo-600\/10:focus {
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}
</style>
