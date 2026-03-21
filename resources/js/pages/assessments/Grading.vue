<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ClipboardList, Save, ArrowLeft, User, CheckCircle2, AlertCircle, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessment: any;
    students: Array<any>;
    existingResults: Record<number, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Grading', href: '#' },
];

const searchQuery = ref('');
const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(s => 
        `${s.first_name} ${s.last_name}`.toLowerCase().includes(query) || 
        s.admission_number.toLowerCase().includes(query)
    );
});

const form = useForm({
    results: props.students.map(student => ({
        student_id: student.id,
        marks_obtained: props.existingResults[student.id]?.marks_obtained ?? '',
        feedback: props.existingResults[student.id]?.feedback ?? '',
        is_absent: props.existingResults[student.id]?.is_absent ?? false,
    }))
});

const getGradeLevel = (marks: number | string) => {
    if (marks === '' || marks === null) return null;
    const score = Number(marks);
    const descriptors = props.assessment.grading_scale?.descriptors || [];
    return descriptors.find((d: any) => score >= d.min_score && score <= d.max_score);
};

const getResultByStudentId = (studentId: number) => {
    return form.results.find(r => r.student_id === studentId);
};

const submit = () => {
    form.post(`/assessments/${props.assessment.id}/grading`);
};

const autoSave = (index: number) => {
    // In a real app, you might want to implement debounced auto-save per student
};
</script>

<template>
    <Head :title="`Grading - ${assessment.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child class="rounded-full">
                        <Link href="/assessments"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">{{ assessment.title }}</h1>
                            <Badge variant="outline">{{ assessment.assessment_type?.name }}</Badge>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ assessment.class?.name }} • {{ assessment.subject?.name }} • Max Marks: {{ assessment.total_marks }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden md:block">
                        <p class="text-xs text-muted-foreground font-medium">Grading Progress</p>
                        <p class="text-sm font-bold text-indigo-600">
                            {{ form.results.filter(r => r.marks_obtained !== '' || r.is_absent).length }} / {{ students.length }} Students
                        </p>
                    </div>
                    <Button @click="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 h-10 px-6 font-bold">
                        <Save class="mr-2 h-4 w-4" />
                        Save All Grades
                    </Button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search students..." class="pl-9 bg-muted/20 border-indigo-50" />
                </div>
                <div class="flex items-center gap-4 text-xs font-medium text-muted-foreground bg-muted/30 px-4 py-2 rounded-lg">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> EE</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> ME</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> AE</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> BE</span>
                </div>
            </div>

            <Card class="border-indigo-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-6 py-4 text-left text-sm font-bold text-muted-foreground uppercase tracking-wider">Student</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-muted-foreground uppercase tracking-wider w-32">Marks / {{ assessment.total_marks }}</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-muted-foreground uppercase tracking-wider w-24">Level</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-muted-foreground uppercase tracking-wider">Feedback / Remarks</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-muted-foreground uppercase tracking-wider w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in filteredStudents" :key="student.id" 
                                class="border-b last:border-0 hover:bg-indigo-50/30 transition-colors"
                                :class="{'bg-red-50/30': getResultByStudentId(student.id)?.is_absent}"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <Avatar class="h-10 w-10 border-2 border-white shadow-sm">
                                            <AvatarImage :src="student.photo" />
                                            <AvatarFallback class="bg-indigo-100 text-indigo-700 font-bold">
                                                {{ student.first_name[0] }}{{ student.last_name[0] }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <div class="font-bold text-gray-900 capitalize">{{ student.first_name }} {{ student.last_name }}</div>
                                            <div class="text-xs text-muted-foreground font-medium">ADM: {{ student.admission_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center" v-if="getResultByStudentId(student.id)">
                                        <Input 
                                            v-model.number="getResultByStudentId(student.id)!.marks_obtained"
                                            type="number"
                                            :max="assessment.total_marks"
                                            min="0"
                                            class="w-20 text-center font-bold text-lg h-10 border-indigo-200"
                                            :disabled="getResultByStudentId(student.id)!.is_absent"
                                            @input="autoSave(index)"
                                        />
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <template v-if="getResultByStudentId(student.id) && !getResultByStudentId(student.id)!.is_absent">
                                        <Badge 
                                            v-if="getGradeLevel(getResultByStudentId(student.id)!.marks_obtained)"
                                            :style="{ backgroundColor: getGradeLevel(getResultByStudentId(student.id)!.marks_obtained).color + '20', color: getGradeLevel(getResultByStudentId(student.id)!.marks_obtained).color }"
                                            class="font-bold border-0"
                                        >
                                            {{ getGradeLevel(getResultByStudentId(student.id)!.marks_obtained).level_code }}
                                        </Badge>
                                        <span v-else class="text-xs text-muted-foreground font-medium">-</span>
                                    </template>
                                    <span v-else-if="getResultByStudentId(student.id)?.is_absent" class="text-xs font-bold text-red-500">N/A</span>
                                    <span v-else class="text-xs text-muted-foreground font-medium">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <Input 
                                        v-if="getResultByStudentId(student.id)"
                                        v-model="getResultByStudentId(student.id)!.feedback"
                                        placeholder="Add a remark..."
                                        class="bg-transparent border-transparent hover:border-muted-foreground/20 focus:border-indigo-400 focus:bg-white h-9 px-0 focus:px-3 transition-all"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center flex-col items-center gap-1" v-if="getResultByStudentId(student.id)">
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-8 px-2 text-[10px] font-bold uppercase tracking-wider"
                                            :class="getResultByStudentId(student.id)!.is_absent ? 'text-red-600 bg-red-50' : 'text-muted-foreground hover:text-red-600'"
                                            @click="() => {
                                                const res = getResultByStudentId(student.id);
                                                if (res) {
                                                    res.is_absent = !res.is_absent;
                                                    if (res.is_absent) res.marks_obtained = '';
                                                }
                                            }"
                                        >
                                            {{ getResultByStudentId(student.id)!.is_absent ? 'Absent' : 'Mark Absent' }}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <div class="flex items-center justify-between p-6 bg-indigo-900 rounded-2xl text-white shadow-xl shadow-indigo-200">
                <div class="flex items-center gap-6">
                    <div class="flex flex-col">
                        <span class="text-xs text-indigo-300 font-bold uppercase tracking-widest">Total Students</span>
                        <span class="text-2xl font-black">{{ students.length }}</span>
                    </div>
                    <div class="w-px h-10 bg-indigo-700"></div>
                    <div class="flex flex-col">
                        <span class="text-xs text-green-400 font-bold uppercase tracking-widest">Graded</span>
                        <span class="text-2xl font-black text-green-400">{{ form.results.filter(r => r.marks_obtained !== '' || r.is_absent).length }}</span>
                    </div>
                    <div class="w-px h-10 bg-indigo-700"></div>
                    <div class="flex flex-col">
                        <span class="text-xs text-amber-400 font-bold uppercase tracking-widest">Remaining</span>
                        <span class="text-2xl font-black text-amber-400">{{ students.length - form.results.filter(r => r.marks_obtained !== '' || r.is_absent).length }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <p class="text-sm font-medium text-indigo-200 italic mr-4">Don't forget to save your progress!</p>
                    <Button @click="submit" :disabled="form.processing" class="bg-green-500 hover:bg-green-600 font-black h-12 px-8 rounded-xl shadow-lg shadow-green-900/40">
                        <CheckCircle2 class="mr-2 h-5 w-5" />
                        SUBMIT ALL GRADES
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>
