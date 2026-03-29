<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    ClipboardList, Save, X, Calendar, BookOpen, Users, 
    FileText, CheckCircle2, Plus, TrendingUp, Info, 
    ChevronRight, ChevronLeft, Target, ShieldCheck, 
    Upload, Image as ImageIcon, Video, File, Trash2
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import SearchableSelect from '@/components/SearchableSelect.vue';
import axios from 'axios';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessmentTypes: Array<any>;
    classes: Array<any>;
    subjects: Array<any>;
    competencies: Array<any>;
    students: Array<any>; // Passed if we know the class, or fetched
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'CBC Wizard', href: '/assessments/create' },
];

const step = ref(1);
const loading = ref(false);

const strands = ref([]);
const subStrands = ref([]);
const indicators = ref([]);
const classStudents = ref(props.students || []);

const form = useForm({
    title: '',
    description: '',
    class_id: '',
    subject_id: '',
    assessment_type_id: '',
    assessment_date: new Date().toISOString().split('T')[0],
    
    // Step 2 & 3: Context
    strand_id: '',
    items: [
        {
            sub_strand_id: '',
            performance_indicator_id: '',
            competency_id: '',
        }
    ],

    // Step 4: Ratings
    ratings: [], // { student_id, level, remarks, evidence: [] }
});

// --- Dynamic Data Fetching ---

watch(() => form.subject_id, async (newVal) => {
    if (!newVal || !form.class_id) return;
    const klass = props.classes.find(c => c.id.toString() === form.class_id.toString());
    if (!klass) return;

    loading.value = true;
    try {
        const response = await axios.get('/api/curriculum/strands', {
            params: { subject_id: newVal, grade_level_id: klass.grade_id }
        });
        strands.value = response.data;
        form.strand_id = '';
        subStrands.value = [];
    } catch (e) {
        console.error("Failed to fetch strands", e);
    } finally {
        loading.value = false;
    }
});

watch(() => form.class_id, (newVal) => {
    // In a real app, we'd fetch students for this class via API if not provided
});

watch(() => form.strand_id, async (newVal) => {
    if (!newVal) return;
    loading.value = true;
    try {
        const response = await axios.get('/api/curriculum/sub-strands', {
            params: { strand_id: newVal }
        });
        subStrands.value = response.data;
    } catch (e) {
        console.error("Failed to fetch sub-strands", e);
    } finally {
        loading.value = false;
    }
});

const fetchIndicators = async (subStrandId, index) => {
    if (!subStrandId) return;
    try {
        const response = await axios.get('/api/curriculum/indicators', {
            params: { sub_strand_id: subStrandId }
        });
        indicators.value = response.data;
    } catch (e) {
        console.error("Failed to fetch indicators", e);
    }
};

// --- Wizard Navigation ---

const nextStep = () => {
    if (step.value === 1 && (!form.title || !form.class_id || !form.subject_id)) return;
    if (step.value === 2 && !form.strand_id) return;
    
    if (step.value === 3) {
        // Initialize ratings if empty
        if (form.ratings.length === 0) {
            form.ratings = classStudents.value.map(s => ({
                student_id: s.id,
                name: `${s.first_name} ${s.last_name}`,
                level: 'ME',
                remarks: '',
                evidence: []
            }));
        }
    }
    
    step.value++;
};

const prevStep = () => step.value--;

const addItem = () => {
    form.items.push({
        sub_strand_id: '',
        performance_indicator_id: '',
        competency_id: '',
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) form.items.splice(index, 1);
};

const handleFileUpload = (studentId, event) => {
    const files = Array.from(event.target.files);
    const ratingIndex = form.ratings.findIndex(r => r.student_id === studentId);
    if (ratingIndex !== -1) {
        form.ratings[ratingIndex].evidence.push(...files);
    }
};

const removeFile = (studentId, fileIndex) => {
    const ratingIndex = form.ratings.findIndex(r => r.student_id === studentId);
    if (ratingIndex !== -1) {
        form.ratings[ratingIndex].evidence.splice(fileIndex, 1);
    }
};

const submit = () => {
    form.post('/assessments/cbc', {
        forceFormData: true,
        onSuccess: () => {
            // Success handled by controller redirect
        }
    });
};

const levels = [
    { code: 'EE', label: 'Exceeding Expectation', color: 'bg-emerald-500' },
    { code: 'ME', label: 'Meeting Expectation', color: 'bg-blue-500' },
    { code: 'AE', label: 'Approaching Expectation', color: 'bg-amber-500' },
    { code: 'BE', label: 'Below Expectation', color: 'bg-rose-500' },
];
</script>

<template>
    <Head title="CBC Assessment Wizard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
            <!-- Header & Steps Indicator -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                            <Target class="h-6 w-6 text-indigo-600" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">CBC Assessment Wizard</h1>
                            <p class="text-muted-foreground">Evidence-based competency tracking</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" as-child><Link href="/assessments">Cancel</Link></Button>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="relative mt-4">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-muted"></div>
                    </div>
                    <div class="relative flex justify-between">
                        <div v-for="s in 4" :key="s" 
                             class="flex h-8 w-8 items-center justify-center rounded-full border-2 transition-all duration-300"
                             :class="[
                                 step >= s ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-background border-muted text-muted-foreground',
                                 step === s ? 'ring-4 ring-indigo-100 shadow-md' : ''
                             ]">
                            <span class="text-xs font-bold">{{ s }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wizard Steps -->
            <div class="mt-4">
                <!-- Step 1: Definition -->
                <div v-if="step === 1" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <Card class="border-indigo-100 shadow-lg shadow-indigo-500/5">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5 text-indigo-600" />
                                Step 1: Assessment Basics
                            </CardTitle>
                            <CardDescription>Define the core details of this assessment activity.</CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-6">
                            <div class="space-y-2">
                                <Label for="title">Assessment Title</Label>
                                <Input id="title" v-model="form.title" placeholder="e.g. Fractions Group Project" class="h-12 text-lg font-medium" />
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label>Class</Label>
                                    <SearchableSelect v-model="form.class_id" :options="classes" placeholder="Select class..." />
                                </div>
                                <div class="space-y-2">
                                    <Label>Learning Area (Subject)</Label>
                                    <SearchableSelect v-model="form.subject_id" :options="subjects" placeholder="Select subject..." />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label>Assessment Type</Label>
                                    <Select v-model="form.assessment_type_id">
                                        <SelectTrigger><SelectValue placeholder="Select type..." /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="t in assessmentTypes" :key="t.id" :value="t.id.toString()">{{ t.name }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>Date</Label>
                                    <Input v-model="form.assessment_date" type="date" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Step 2: Context -->
                <div v-if="step === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                    <Card class="border-indigo-100 shadow-lg shadow-indigo-500/5">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5 text-indigo-600" />
                                Step 2: Curricular Context
                            </CardTitle>
                            <CardDescription>Link this activity to the curriculum architecture.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-2">
                                <Label>Main Strand (Topic)</Label>
                                <Select v-model="form.strand_id">
                                    <SelectTrigger class="h-12"><SelectValue placeholder="Select strand..." /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="s in strands" :key="s.id" :value="s.id.toString()">{{ s.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div v-if="form.strand_id" class="space-y-4">
                                <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Sub-Strands & Indicators</h3>
                                <div v-for="(item, index) in form.items" :key="index" class="p-4 border rounded-xl bg-muted/20 relative group">
                                    <button @click="removeItem(index)" class="absolute -top-2 -right-2 p-1 bg-white border shadow-sm rounded-full text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <X class="h-4 w-4" />
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label>Sub-Strand</Label>
                                            <Select v-model="item.sub_strand_id" @update:modelValue="(val) => fetchIndicators(val, index)">
                                                <SelectTrigger><SelectValue placeholder="Select sub-strand..." /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="ss in subStrands" :key="ss.id" :value="ss.id.toString()">{{ ss.name }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Performance Indicator</Label>
                                            <Select v-model="item.performance_indicator_id">
                                                <SelectTrigger><SelectValue placeholder="Select indicator..." /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="ind in indicators" :key="ind.id" :value="ind.id.toString()">{{ ind.indicator }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        <Label class="flex items-center gap-2">
                                            <ShieldCheck class="h-4 w-4 text-emerald-600" />
                                            Target Competency
                                        </Label>
                                        <Select v-model="item.competency_id">
                                            <SelectTrigger><SelectValue placeholder="Select core competency..." /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="c in competencies" :key="c.id" :value="c.id.toString()">{{ c.name }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                                <Button variant="outline" @click="addItem" class="w-full border-dashed border-indigo-200 text-indigo-600 hover:bg-indigo-50">
                                    <Plus class="mr-2 h-4 w-4" /> Add another context item
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Step 3: Global Indicators Summary (Skipping for now or merged into Step 2) -->
                
                <!-- Step 4: Ratings & Evidence -->
                <div v-if="step === 3 || step === 4" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <Users class="h-5 w-5 text-indigo-600" />
                            Learner Assessment & Evidence
                        </h2>
                        <div class="text-sm font-medium text-muted-foreground bg-muted/50 px-3 py-1 rounded-full border">
                            {{ form.ratings.length }} Students
                        </div>
                    </div>

                    <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="(rating, index) in form.ratings" :key="rating.student_id" 
                             class="bg-white border rounded-2xl shadow-sm overflow-hidden hover:border-indigo-200 transition-colors">
                            <div class="p-4 bg-muted/5 border-b flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center font-black text-indigo-700">
                                        {{ rating.name.split(' ').map(n => n[0]).join('') }}
                                    </div>
                                    <div class="font-bold text-lg text-slate-800">{{ rating.name }}</div>
                                </div>
                                <div class="flex gap-1 bg-white p-1 rounded-lg border shadow-sm">
                                    <button v-for="level in levels" :key="level.code"
                                            @click="rating.level = level.code"
                                            class="px-3 py-1.5 rounded-md text-xs font-black transition-all"
                                            :class="[
                                                rating.level === level.code ? `${level.color} text-white shadow-md` : 'text-slate-400 hover:bg-slate-50'
                                            ]"
                                            type="button">
                                        {{ level.code }}
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 grid md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-xs uppercase tracking-wider font-bold text-slate-500">Teacher Remarks</Label>
                                    <Textarea v-model="rating.remarks" placeholder="Add specific observation..." rows="2" class="resize-none" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs uppercase tracking-wider font-bold text-slate-500 flex items-center justify-between">
                                        Evidence (Photos/Video)
                                        <span class="text-[10px] text-indigo-600 font-black cursor-pointer hover:underline" @click="$refs[`file_${rating.student_id}`][0].click()">
                                            + ADD FILES
                                        </span>
                                    </Label>
                                    <input type="file" :ref="`file_${rating.student_id}`" class="hidden" multiple @change="(e) => handleFileUpload(rating.student_id, e)" />
                                    
                                    <div v-if="rating.evidence.length > 0" class="flex flex-wrap gap-2">
                                        <div v-for="(file, fIdx) in rating.evidence" :key="fIdx" 
                                             class="relative h-16 w-16 bg-slate-50 border rounded-lg flex items-center justify-center overflow-hidden group">
                                            <div v-if="file.type.startsWith('image/')" class="h-full w-full">
                                                <img :src="URL.createObjectURL(file)" class="h-full w-full object-cover" />
                                            </div>
                                            <div v-else class="flex flex-col items-center">
                                                <FileText class="h-6 w-6 text-slate-400" />
                                                <span class="text-[8px] font-bold uppercase truncate max-w-full px-1">{{ file.name.split('.').pop() }}</span>
                                            </div>
                                            <button @click="removeFile(rating.student_id, fIdx)" class="absolute top-0 right-0 p-0.5 bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                                <X class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else class="h-16 border-2 border-dashed rounded-lg flex flex-col items-center justify-center text-slate-300 text-xs font-bold gap-1 bg-slate-50/50">
                                        <Upload class="h-4 w-4" />
                                        NO EVIDENCE
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Action Bar -->
            <div class="mt-auto pt-6 flex items-center justify-between border-t sticky bottom-0 bg-background/80 backdrop-blur-md">
                <Button v-if="step > 1" variant="outline" @click="prevStep" class="h-12 px-6">
                    <ChevronLeft class="mr-2 h-4 w-4" /> Previous
                </Button>
                <div v-else></div>

                <div class="flex gap-3">
                    <Button v-if="step < 4" @click="nextStep" class="h-12 px-10 bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-100 font-bold">
                        Continue <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>
                    <Button v-else @click="submit" :disabled="form.processing" class="h-12 px-12 bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-100 font-black">
                        <Save class="mr-2 h-4 w-4" /> FINALIZE ASSESSMENT
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
