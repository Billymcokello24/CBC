<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Save, 
    Plus, 
    Trash2, 
    BookMarked,
    GraduationCap,
    LayoutGrid,
    CheckCircle2,
    AlertCircle
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import SearchableSelect from '@/components/SearchableSelect.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    rubric?: any;
    subjects: Array<any>;
    assessmentTypes: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Rubrics', href: '/assessments/rubrics' },
    { title: props.rubric ? 'Edit Rubric' : 'Create Rubric', href: '#' },
];

const form = useForm({
    name: props.rubric?.name ?? '',
    description: props.rubric?.description ?? '',
    subject_id: props.rubric?.subject_id?.toString() ?? '',
    assessment_type_id: props.rubric?.assessment_type_id?.toString() ?? '',
    total_points: props.rubric?.total_points ?? 100,
    is_active: props.rubric?.is_active ?? true,
    levels: props.rubric?.criteria?.[0]?.levels?.map((l: any) => ({
        ...l,
        min_score: Number(l.min_score),
        max_score: Number(l.max_score),
        points: Number(l.points)
    })) ?? [
        { level_name: 'Exceeding Expectation', grade_code: 'EE', min_score: 80, max_score: 100, points: 4, description: 'Excellent performance exceeding requirements.' },
        { level_name: 'Meeting Expectation', grade_code: 'ME', min_score: 60, max_score: 79, points: 3, description: 'Good performance meeting all requirements.' },
        { level_name: 'Approaching Expectation', grade_code: 'AE', min_score: 40, max_score: 59, points: 2, description: 'Fair performance approaching requirements.' },
        { level_name: 'Below Expectation', grade_code: 'BE', min_score: 0, max_score: 39, points: 1, description: 'Performance below expected levels.' },
    ],
});

const addLevel = () => {
    form.levels.push({
        level_name: '',
        grade_code: '',
        min_score: 0,
        max_score: 0,
        points: 0,
        description: ''
    });
};

const removeLevel = (index: any) => {
    form.levels.splice(index, 1);
};

const submit = () => {
    if (props.rubric) {
        form.put(`/assessments/rubrics/${props.rubric.id}`);
    } else {
        form.post('/assessments/rubrics');
    }
};
</script>

<template>
    <Head :title="rubric ? 'Edit Rubric' : 'Create Rubric'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10">
                        <BookMarked class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ rubric ? 'Edit Rubric' : 'Create Rubric' }}</h1>
                        <p class="text-muted-foreground">Define how student scores translate to grades and comments</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="ghost" as-child><Link href="/assessments/rubrics">Cancel</Link></Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6">
                <!-- General Information -->
                <Card class="border-orange-100 shadow-sm overflow-hidden rounded-3xl">
                    <CardHeader class="bg-orange-50/30 border-b p-6">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <LayoutGrid class="h-5 w-5 text-orange-600" />
                            General Information
                        </CardTitle>
                        <CardDescription>Basic rubric details and scope</CardDescription>
                    </CardHeader>
                    <CardContent class="p-8 grid gap-8">
                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <Label for="name" class="font-black text-xs uppercase tracking-widest text-muted-foreground">Rubric Name</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g., Standard CBC Grading" required class="h-12 border-orange-100 focus:ring-orange-500 rounded-xl" />
                            </div>
                            <div class="space-y-3">
                                <Label for="total_points" class="font-black text-xs uppercase tracking-widest text-muted-foreground">Total Points</Label>
                                <Input id="total_points" v-model.number="form.total_points" type="number" required class="h-12 border-orange-100 focus:ring-orange-500 rounded-xl font-bold text-lg" />
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <Label class="font-black text-xs uppercase tracking-widest text-muted-foreground">Subject (Optional)</Label>
                                <SearchableSelect 
                                    v-model="form.subject_id"
                                    :options="[{id: '', name: 'General / All Subjects'}, ...subjects]"
                                    placeholder="Select a subject..."
                                    search-placeholder="Search subjects..."
                                    class="h-12"
                                />
                            </div>
                            <div class="space-y-3">
                                <Label class="font-black text-xs uppercase tracking-widest text-muted-foreground">Assessment Type</Label>
                                <Select v-model="form.assessment_type_id">
                                    <SelectTrigger class="h-12 border-orange-100 rounded-xl focus:ring-orange-500">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in assessmentTypes" :key="type.id" :value="type.id.toString()">
                                            {{ type.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <Label for="description" class="font-black text-xs uppercase tracking-widest text-muted-foreground">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Briefly explain the purpose of this rubric..." rows="3" class="border-orange-100 focus:ring-orange-500 rounded-2xl" />
                        </div>

                        <div class="flex items-center gap-2 pt-4 border-t border-orange-100/50 mt-4">
                            <input type="checkbox" v-model="form.is_active" class="h-5 w-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500" />
                            <Label class="font-bold text-sm">This rubric is active and available for use</Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grading Levels -->
                <Card class="border-indigo-100 shadow-sm overflow-hidden rounded-3xl">
                    <CardHeader class="bg-indigo-50/30 border-b p-6 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <GraduationCap class="h-5 w-5 text-indigo-600" />
                                Grading Levels & Descriptors
                            </CardTitle>
                            <CardDescription>Map score ranges to specific grades and performance comments</CardDescription>
                        </div>
                        <Button type="button" variant="outline" size="sm" @click="addLevel" class="border-indigo-200 text-indigo-700 font-bold hover:bg-indigo-50">
                            <Plus class="mr-2 h-4 w-4" /> Add Level
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-indigo-50/50 border-b border-indigo-100">
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Level Name</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Grade Code</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground w-24">Min %</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground w-24">Max %</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground w-20">Points</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Feedback Template / Comment</th>
                                        <th class="p-4 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(level, index) in form.levels" :key="index" class="border-b last:border-0 hover:bg-indigo-50/20 transition-colors group">
                                        <td class="p-3">
                                            <Input v-model="level.level_name" placeholder="e.g. Exceeding Expectation" class="h-10 border-indigo-100 focus:ring-indigo-500 rounded-lg text-sm" />
                                        </td>
                                        <td class="p-3">
                                            <Input v-model="level.grade_code" placeholder="e.g. EE" class="h-10 border-indigo-100 focus:ring-indigo-500 rounded-lg text-sm w-20 font-black uppercase text-center" />
                                        </td>
                                        <td class="p-3">
                                            <Input v-model.number="level.min_score" type="number" class="h-10 border-indigo-100 focus:ring-indigo-500 rounded-lg text-sm text-center font-bold" />
                                        </td>
                                        <td class="p-3">
                                            <Input v-model.number="level.max_score" type="number" class="h-10 border-indigo-100 focus:ring-indigo-500 rounded-lg text-sm text-center font-bold" />
                                        </td>
                                        <td class="p-3">
                                            <Input v-model.number="level.points" type="number" class="h-10 border-indigo-100 focus:ring-indigo-500 rounded-lg text-sm text-center font-bold text-indigo-600" />
                                        </td>
                                        <td class="p-3">
                                            <Input v-model="level.description" placeholder="Optional performance comment..." class="h-10 border-indigo-100 focus:ring-indigo-500 rounded-lg text-sm italic" />
                                        </td>
                                        <td class="p-3">
                                            <Button type="button" variant="ghost" size="icon" @click="removeLevel(index)" class="text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="form.levels.length === 0" class="p-12 text-center text-muted-foreground italic bg-gray-50/50">
                            No grading levels defined yet. Click "Add Level" to start defining your criteria.
                        </div>
                        
                    </CardContent>
                </Card>


                <!-- Action Bar -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t pb-10">
                    <Button variant="outline" type="button" as-child class="h-12 px-6 font-bold rounded-xl border-gray-200">
                        <Link href="/assessments/rubrics">Discard Changes</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="h-12 px-10 bg-orange-600 hover:bg-orange-700 text-lg font-black rounded-xl shadow-xl shadow-orange-100">
                        <Save class="mr-2 h-5 w-5" />
                        Save Rubric
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
