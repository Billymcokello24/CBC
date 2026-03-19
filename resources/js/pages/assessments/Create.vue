<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ClipboardList, Save, X, Calendar, BookOpen, Users, FileText, CheckCircle2, Plus, TrendingUp, Info } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import BulkUploadDialog from '@/components/assessments/BulkUploadDialog.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { ChevronsUpDown } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessmentTypes: Array<any>;
    gradingScales: Array<any>;
    classes: Array<any>;
    subjects: Array<any>;
    rubrics: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Create', href: '/assessments/create' },
];

const form = useForm({
    title: '',
    description: '',
    class_id: '',
    subject_id: '',
    rubric_id: '',
    assessment_type_id: '',
    grading_scale_id: '',
    assessment_date: new Date().toISOString().split('T')[0],
    total_marks: 100,
    passing_marks: 50,
    weight: 100,
    status: 'draft',
});

watch(() => form.rubric_id, (newRubricId) => {
    if (!newRubricId) return;
    const rubric = props.rubrics.find(r => r.id.toString() === newRubricId.toString());
    if (rubric) {
        form.total_marks = Number(rubric.total_points);
        form.passing_marks = Math.round(Number(rubric.total_points) * 0.5);
        form.assessment_type_id = rubric.assessment_type_id?.toString() || '';
    }
});

const showUploadDialog = ref(false);

const submit = () => {
    form.post('/assessments');
};
</script>

<template>
    <Head title="Create Assessment" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                        <Plus class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Create Assessment</h1>
                        <p class="text-muted-foreground">Define a new assessment for your students</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="border-indigo-200 text-indigo-700 font-bold" @click="showUploadDialog = true">
                        <Upload class="mr-2 h-4 w-4" />
                        Bulk Upload
                    </Button>
                    <Button variant="ghost" as-child><Link href="/assessments">Cancel</Link></Button>
                    <Button @click="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700">
                        <Save class="mr-2 h-4 w-4" />
                        Save Assessment
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6">
                <!-- Basic Information -->
                <Card class="border-indigo-100 shadow-sm overflow-hidden">
                    <CardHeader class="bg-muted/30 border-b">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <FileText class="h-5 w-5 text-indigo-600" />
                            Basic Information
                        </CardTitle>
                        <CardDescription>Main details about the assessment</CardDescription>
                    </CardHeader>
                    <CardContent class="p-6 grid gap-6">
                        <div class="space-y-2">
                            <Label for="title">Assessment Title</Label>
                            <Input id="title" v-model="form.title" placeholder="e.g., Mathematics End of Term CAT" required class="font-medium" />
                            <div v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Briefly describe the purpose of this assessment..." rows="3" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2">
                                    Assessment Rubric
                                    <Badge variant="outline" class="h-5 px-1.5 text-[10px] font-black border-orange-200 text-orange-700 bg-orange-50">REQUIRED</Badge>
                                </Label>
                                <SearchableSelect 
                                    v-model="form.rubric_id"
                                    :options="rubrics"
                                    placeholder="Select a rubric..."
                                    search-placeholder="Search rubrics..."
                                />
                                <p class="text-[10px] text-muted-foreground font-medium italic">Rubrics define the grading levels and performance criteria.</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Assessment Type (Auto-filled)</Label>
                                <Select v-model="form.assessment_type_id" disabled>
                                    <SelectTrigger class="bg-muted/30 border-dashed border-indigo-100">
                                        <SelectValue placeholder="Derived from Rubric" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in assessmentTypes" :key="type.id" :value="type.id.toString()">
                                            {{ type.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Allocation & Timing -->
                <div class="grid gap-6 sm:grid-cols-2">
                    <Card class="border-blue-100 shadow-sm">
                        <CardHeader class="bg-muted/30 border-b">
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Users class="h-5 w-5 text-blue-600" />
                                Allocation
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-6 space-y-6">
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2">
                                    Target Class
                                    <Badge variant="outline" class="h-5 px-1.5 text-[10px] font-black border-blue-200 text-blue-700 bg-blue-50">REQUIRED</Badge>
                                </Label>
                                <SearchableSelect 
                                    v-model="form.class_id"
                                    :options="classes"
                                    placeholder="Select a class..."
                                    search-placeholder="Search classes (e.g. Grade 1)..."
                                />
                                <p class="text-[10px] text-muted-foreground font-medium italic">Choosing a class will filter students for results entry later.</p>
                            </div>
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2">
                                    Subject
                                    <Badge variant="outline" class="h-5 px-1.5 text-[10px] font-black border-indigo-200 text-indigo-700 bg-indigo-50">REQUIRED</Badge>
                                </Label>
                                <SearchableSelect 
                                    v-model="form.subject_id"
                                    :options="subjects"
                                    placeholder="Select a subject..."
                                    search-placeholder="Search subjects (e.g. Mathematics)..."
                                />
                                <p class="text-[10px] text-muted-foreground font-medium italic">Only subjects offered by your school are listed here.</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-purple-100 shadow-sm">
                        <CardHeader class="bg-muted/30 border-b">
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Calendar class="h-5 w-5 text-purple-600" />
                                Timing
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-6 space-y-4">
                            <div class="space-y-2">
                                <Label for="assessment_date">Assessment Date</Label>
                                <Input id="assessment_date" v-model="form.assessment_date" type="date" />
                            </div>
                            <div class="space-y-2">
                                <Label>Initial Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="scheduled">Scheduled</SelectItem>
                                        <SelectItem value="published">Published</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Marks & Weighting -->
                <Card class="border-green-100 shadow-sm">
                    <CardHeader class="bg-muted/30 border-b text-center">
                        <CardTitle class="text-lg flex items-center justify-center gap-2">
                            <TrendingUp class="h-5 w-5 text-green-600" />
                            Grading & Weighting
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div class="grid gap-6 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="total_marks">Total Marks</Label>
                                <div class="relative">
                                    <Input id="total_marks" v-model.number="form.total_marks" type="number" class="pl-10 text-xl font-bold" />
                                    <CheckCircle2 class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-green-500" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label for="passing_marks">Passing Marks</Label>
                                <Input id="passing_marks" v-model.number="form.passing_marks" type="number" class="text-xl font-bold text-amber-600" />
                            </div>
                            <div class="space-y-2">
                                <Label for="weight">Weight (%)</Label>
                                <div class="relative">
                                    <Input id="weight" v-model.number="form.weight" type="number" class="text-xl font-bold text-indigo-600" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 font-bold text-muted-foreground">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex items-center justify-center gap-2 p-4 bg-green-50 rounded-lg text-sm text-green-700 font-medium">
                            <Info class="h-4 w-4" />
                            This assessment will contribute {{ form.weight }}% to the overall grade for this term.
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Bar -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <Button variant="outline" type="button" as-child><Link href="/assessments">Discard</Link></Button>
                    <Button type="submit" :disabled="form.processing" class="px-8 bg-indigo-600 hover:bg-indigo-700 h-12 text-lg font-bold shadow-lg shadow-indigo-100">
                        Create Assessment
                    </Button>
                </div>
            </form>
        </div>

        <!-- Bulk Upload Dialog -->
        <BulkUploadDialog 
            v-model:open="showUploadDialog"
            title="Import Assessments"
            description="Upload a CSV or Excel file to create multiple assessments at once."
            template-url="/assessments/import-template"
            upload-url="/assessments/import"
        />
    </AppLayout>
</template>
