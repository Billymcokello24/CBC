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
    AlertCircle,
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
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
        points: Number(l.points),
    })) ?? [
        {
            level_name: 'Exceeding Expectation',
            grade_code: 'EE',
            min_score: 80,
            max_score: 100,
            points: 4,
            description: 'Excellent performance exceeding requirements.',
        },
        {
            level_name: 'Meeting Expectation',
            grade_code: 'ME',
            min_score: 60,
            max_score: 79,
            points: 3,
            description: 'Good performance meeting all requirements.',
        },
        {
            level_name: 'Approaching Expectation',
            grade_code: 'AE',
            min_score: 40,
            max_score: 59,
            points: 2,
            description: 'Fair performance approaching requirements.',
        },
        {
            level_name: 'Below Expectation',
            grade_code: 'BE',
            min_score: 0,
            max_score: 39,
            points: 1,
            description: 'Performance below expected levels.',
        },
    ],
});

const addLevel = () => {
    form.levels.push({
        level_name: '',
        grade_code: '',
        min_score: 0,
        max_score: 0,
        points: 0,
        description: '',
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
        <div class="mx-auto flex h-full max-w-6xl flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10"
                    >
                        <BookMarked class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-foreground uppercase">
                           Configure {{ rubric ? 'Rubric' : 'Registry' }}
                        </h1>
                        <p class="text-xs font-bold uppercase tracking-widest text-muted-foreground opacity-60">
                            Automated Terminal Translation Core
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="ghost" as-child class="text-[10px] font-black uppercase tracking-widest"
                        ><Link href="/assessments/rubrics">Discard</Link></Button
                    >
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6">
                <!-- General Information -->
                <Card
                    class="overflow-hidden rounded-2xl border-border/50 shadow-sm"
                >
                    <CardHeader class="border-b bg-muted/5 p-6">
                        <CardTitle class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-foreground">
                            <LayoutGrid class="h-4 w-4 text-primary" />
                            General Registry Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-8 p-8">
                        <div class="grid gap-8 md:grid-cols-2">
                            <div class="space-y-3">
                                <Label
                                    for="name"
                                    class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60"
                                    >Rubric Identifier</Label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g., MATHEMATICS STANDARD"
                                    required
                                    class="h-12 rounded-xl border-border/60 uppercase text-[11px] font-bold tracking-widest focus:ring-primary"
                                />
                            </div>
                            <div class="space-y-3">
                                <Label
                                    for="total_points"
                                    class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60"
                                    >Max Possible Points</Label
                                >
                                <Input
                                    id="total_points"
                                    v-model.number="form.total_points"
                                    type="number"
                                    required
                                    class="h-12 rounded-xl border-border/60 text-lg font-black focus:ring-primary"
                                />
                            </div>
                        </div>

                        <div class="grid gap-8 md:grid-cols-2">
                            <div class="space-y-3">
                                <Label
                                    class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60"
                                    >Subject Association</Label
                                >
                                <SearchableSelect
                                    v-model="form.subject_id"
                                    :options="[
                                        {
                                            id: '',
                                            name: 'GENERAL / ALL SUBJECTS',
                                        },
                                        ...subjects,
                                    ]"
                                    placeholder="SELECT SUBJECT..."
                                    search-placeholder="SEARCH..."
                                    class="h-12"
                                />
                            </div>
                            <div class="space-y-3">
                                <Label
                                    class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60"
                                    >Context / Type</Label
                                >
                                <Select v-model="form.assessment_type_id">
                                    <SelectTrigger
                                        class="h-12 rounded-xl border-border/60 focus:ring-primary"
                                    >
                                        <SelectValue
                                            placeholder="SELECT TYPE"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in assessmentTypes"
                                            :key="type.id"
                                            :value="type.id.toString()"
                                            class="text-[10px] font-bold uppercase tracking-widest"
                                        >
                                            {{ type.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <Label
                                for="description"
                                class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60"
                                >Protocol Description</Label
                            >
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="BRIEF EXPLANATION..."
                                rows="3"
                                class="rounded-2xl border-border/60 focus:ring-primary text-[11px] font-medium"
                            />
                        </div>

                        <div
                            class="mt-4 flex items-center gap-3 border-t border-border/30 pt-6"
                        >
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="h-5 w-5 rounded-lg border-border/60 text-primary focus:ring-primary"
                            />
                            <Label class="text-[10px] font-black uppercase tracking-widest"
                                >Enable for Grading Terminal Use</Label
                            >
                        </div>
                    </CardContent>
                </Card>

                <!-- Grading Levels -->
                <Card
                    class="overflow-hidden rounded-2xl border-border/50 shadow-sm"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between border-b bg-muted/5 p-6"
                    >
                        <div>
                            <CardTitle class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-foreground">
                                <GraduationCap
                                    class="h-4 w-4 text-primary"
                                />
                                Translation Scales
                            </CardTitle>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addLevel"
                            class="h-9 px-6 rounded-xl border-border text-[9px] font-black uppercase tracking-widest hover:bg-muted"
                        >
                            <Plus class="mr-2 h-4 w-4" /> Add Scale
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr
                                        class="border-b bg-muted/10"
                                    >
                                        <th
                                            class="p-6 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 w-[240px]"
                                        >
                                            Scale Name
                                        </th>
                                        <th
                                            class="p-6 text-center text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 w-24"
                                        >
                                            Code
                                        </th>
                                        <th
                                            class="p-6 text-center text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 w-28"
                                        >
                                            Min %
                                        </th>
                                        <th
                                            class="p-6 text-center text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 w-28"
                                        >
                                            Max %
                                        </th>
                                        <th
                                            class="p-6 text-center text-[9px] font-black uppercase tracking-widest text-primary w-24"
                                        >
                                            Points
                                        </th>
                                        <th
                                            class="p-6 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60"
                                        >
                                            Feedback Template
                                        </th>
                                        <th class="w-12 p-6"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/20">
                                    <tr
                                        v-for="(level, index) in form.levels"
                                        :key="index"
                                        class="group hover:bg-muted/5 transition-colors"
                                    >
                                        <td class="p-4">
                                            <Input
                                                v-model="level.level_name"
                                                placeholder="EXCEEDING..."
                                                class="h-10 rounded-xl border-border/60 text-[10px] font-bold uppercase tracking-widest focus:ring-primary"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <Input
                                                v-model="level.grade_code"
                                                placeholder="EE"
                                                class="h-10 text-center rounded-xl border-border/60 text-[10px] font-black group-hover:border-primary/40 focus:ring-primary"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <Input
                                                v-model.number="level.min_score"
                                                type="number"
                                                class="h-10 text-center rounded-xl border-border/60 text-[11px] font-black focus:ring-primary"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <Input
                                                v-model.number="level.max_score"
                                                type="number"
                                                class="h-10 text-center rounded-xl border-border/60 text-[11px] font-black focus:ring-primary"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <Input
                                                v-model.number="level.points"
                                                type="number"
                                                class="h-10 text-center rounded-xl border-border/60 text-[11px] font-black text-primary focus:ring-primary"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <Input
                                                v-model="level.description"
                                                placeholder="OPTIONAL COMMENT..."
                                                class="h-10 rounded-xl border-border/60 text-[10px] font-medium focus:ring-primary"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                @click="removeLevel(index)"
                                                class="rounded-xl text-red-500 opacity-0 group-hover:opacity-100 hover:bg-red-50"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            v-if="form.levels.length === 0"
                            class="p-12 text-center"
                        >
                           <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-30">No translation scales defined</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Bar -->
                <div
                    class="flex items-center justify-end gap-3 border-t pt-8 pb-20"
                >
                    <Button
                        variant="outline"
                        type="button"
                        as-child
                        class="h-12 rounded-2xl px-8 text-[10px] font-black uppercase tracking-widest border-border/60"
                    >
                        <Link href="/assessments/rubrics">Discard Changes</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-12 rounded-2xl bg-primary px-12 text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary/20 hover:opacity-90"
                    >
                        <Save class="mr-2 h-4 w-4" />
                        Synchronize Rubric
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
