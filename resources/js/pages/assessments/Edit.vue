<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    ClipboardList,
    CheckCircle2,
    ChevronRight,
    ChevronLeft,
    BookOpen,
    GraduationCap,
    Target,
    Save,
    Trash2,
    Search,
    ListChecks,
    Calendar,
    Info,
    LayoutGrid,
    Layers,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import axios from 'axios';

const props = defineProps<{
    assessment: any;
    gradeLevels: Array<any>;
    subjects: Array<any>;
    academicTerms: Array<any>;
    academicYears: Array<any>;
    assessmentTypes: Array<any>;
    competencies: Array<any>;
    ratingScales: Array<any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Edit Assessment', href: '#' },
];

interface Strand {
    id: number;
    name: string;
}
interface SubStrand {
    id: number;
    name: string;
}
interface Indicator {
    id: string | number;
    indicator: string;
    [key: string]: any;
}

const currentStep = ref(1);
const loading = ref(false);
const strands = ref<Strand[]>([]);
const subStrands = ref<SubStrand[]>([]);
const indicators = ref<Indicator[]>([]);

const standardMinistryIndicators: Indicator[] = [
    { id: 'knowledge', indicator: 'Knowledge & Understanding' },
    { id: 'skills', indicator: 'Skills Application' },
    { id: 'communication', indicator: 'Communication' },
    { id: 'values', indicator: 'Values & Attitudes' },
    { id: 'creativity', indicator: 'Creativity' },
    { id: 'thinking', indicator: 'Critical Thinking' },
];

const form = useForm({
    title: props.assessment?.title || '',
    description: props.assessment?.description || '',
    type_id: props.assessment?.assessment_type_id || '',
    date: props.assessment?.assessment_date ? new Date(props.assessment.assessment_date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    academic_year_id: props.assessment?.academic_year_id || '',
    term_id: props.assessment?.academic_term_id || '',
    grade_level_id: props.assessment?.grade_level_id || props.gradeLevels.find(g => g.classes?.some((c:any) => c.id === props.assessment?.class_id))?.id || '',
    class_id: props.assessment?.class_id || '',
    subject_id: props.assessment?.subject_id || '',
    strand_id: '',
    sub_strand_id: '',
    total_marks: props.assessment?.total_marks || 100,
    passing_marks: props.assessment?.passing_marks || 50,
    weight: props.assessment?.weight || 10,
    status: props.assessment?.status || 'draft',
    indicators: props.assessment?.indicators || [] as Indicator[],
    source: props.assessment?.source || 'internal',
});

const steps = [
    {
        id: 1,
        name: 'General Information',
        description: 'Basic assessment details',
    },
    {
        id: 2,
        name: 'Academic Context',
        description: 'Grade, Class and Subject',
    },
    {
        id: 3,
        name: 'Curriculum Mapping',
        description: 'Select Strand & Sub-strand',
    },
    {
        id: 4,
        name: 'Indicators Selection',
        description: 'Choose what to evaluate',
    },
    { id: 5, name: 'Review & Confirm', description: 'Final verification' },
];

// Computed
const selectedGrade = computed(() =>
    props.gradeLevels.find((g) => String(g.id) === String(form.grade_level_id)),
);
const availableClasses = computed(() => selectedGrade.value?.classes || []);
const availableSubjects = computed(() => {
    return props.subjects || [];
});

const availableIndicators = computed(() => {
    if (form.source === 'ministry') return standardMinistryIndicators;
    return indicators.value.length > 0 ? indicators.value : standardMinistryIndicators;
});

// Watchers for dynamic data fetching
watch(
    () => form.subject_id,
    async (val) => {
        if (!val) return;
        loading.value = true;
        try {
            const res = await axios.get(
                route('assessments.strands', { subject_id: val }),
            );
            strands.value = res.data as Strand[];
            form.strand_id = '';
            form.sub_strand_id = '';
        } finally {
            loading.value = false;
        }
    },
);

watch(
    () => form.strand_id,
    async (val) => {
        if (!val) return;
        loading.value = true;
        try {
            const res = await axios.get(
                route('assessments.sub-strands', { strand_id: val }),
            );
            subStrands.value = res.data as SubStrand[];
            form.sub_strand_id = '';
        } finally {
            loading.value = false;
        }
    },
);

watch(
    () => form.sub_strand_id,
    async (val) => {
        if (!val || !form.grade_level_id) return;
        loading.value = true;
        try {
            const res = await axios.get(
                route('assessments.indicators', {
                    grade_level_id: form.grade_level_id,
                    sub_strand_id: val,
                }),
            );
            indicators.value = res.data as { id: number; indicator: string }[];
        } finally {
            loading.value = false;
        }
    },
);

const nextStep = () => {
    if (currentStep.value < 5) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const toggleIndicator = (indicator: Indicator) => {
    const index = form.indicators.findIndex((i: Indicator) => i.id === indicator.id);
    if (index === -1) {
        form.indicators.push({ ...indicator, max_score: 4 });
    } else {
        form.indicators.splice(index, 1);
    }
};

const selectAllIndicators = () => {
    form.indicators = availableIndicators.value.map(i => ({ ...i, max_score: 4 }));
};

const clearAllIndicators = () => {
    form.indicators = [];
};

const submit = () => {
    form.put(route('assessments.update', props.assessment.id), {
        onSuccess: () => {
            // Success handlng
        },
    });
};
</script>

<template>
    <Head title="Edit Assessment" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 overflow-hidden p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            <!-- Progress Bar -->
            <div class="relative flex items-center justify-between px-4">
                <div class="absolute top-1/2 left-0 z-0 h-0.5 w-full -translate-y-1/2 bg-border"></div>
                <div class="absolute top-1/2 left-0 z-0 h-0.5 -translate-y-1/2 bg-primary transition-all duration-500" :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"></div>
                <div v-for="step in steps" :key="step.id" class="relative z-10 flex flex-col items-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 transition-all duration-300" :class="[currentStep === step.id ? 'scale-110 border-primary bg-primary text-white shadow-sm' : currentStep > step.id ? 'border-primary bg-primary text-white' : 'border-border bg-card text-muted-foreground']">
                        <CheckCircle2 v-if="currentStep > step.id" class="h-4 w-4" />
                        <span v-else class="text-xs font-semibold">{{ step.id }}</span>
                    </div>
                    <span class="absolute top-11 text-[10px] font-medium whitespace-nowrap text-muted-foreground" :class="{ '!text-primary font-semibold': currentStep === step.id }">{{ step.name }}</span>
                </div>
            </div>

            <div class="mt-4 flex h-full flex-col gap-8 overflow-hidden lg:flex-row">
                <!-- Main Content Area -->
                <div class="flex flex-1 flex-col gap-6 overflow-hidden">
                    <Card class="flex h-full flex-col overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <CardHeader class="border-b border-border/50 bg-muted/5 pb-4 pt-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <component :is="steps[currentStep - 1].id === 1 ? Info : steps[currentStep - 1].id === 4 ? ListChecks : LayoutGrid" class="h-4 w-4" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold tracking-tight text-foreground">{{ steps[currentStep - 1].name }}</h2>
                                    <p class="text-xs font-medium text-muted-foreground">{{ steps[currentStep - 1].description }}</p>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="flex-1 overflow-y-auto p-6">
                            <!-- Step 1: General Info -->
                            <div
                                v-if="currentStep === 1"
                                class="animate-in space-y-6 duration-500 fade-in slide-in-from-right-4"
                            >
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Input
                                            v-model="form.title"
                                            placeholder="e.g. End of Term Mathematics Evaluation"
                                            class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Assessment Source</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button 
                                                type="button"
                                                @click="form.source = 'internal'"
                                                class="flex h-10 items-center justify-center rounded-lg border px-4 text-xs font-semibold transition-all"
                                                :class="form.source === 'internal' ? 'border-primary bg-primary/10 text-primary shadow-sm ring-1 ring-primary/20' : 'border-border bg-muted/5 text-muted-foreground hover:bg-muted/10'"
                                            >
                                                Internal (School)
                                            </button>
                                            <button 
                                                type="button"
                                                @click="form.source = 'ministry'"
                                                class="flex h-10 items-center justify-center rounded-lg border px-4 text-xs font-semibold transition-all"
                                                :class="form.source === 'ministry' ? 'border-primary bg-primary/10 text-primary shadow-sm ring-1 ring-primary/20' : 'border-border bg-muted/5 text-muted-foreground hover:bg-muted/10'"
                                            >
                                                Ministry (KNEC)
                                            </button>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Academic Year</label>
                                        <SearchableSelect 
                                            v-model="form.academic_year_id"
                                            :options="academicYears.map(y => ({ id: y.id, name: y.name }))"
                                            placeholder="Select Year..."
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Assessment Nature</label>
                                        <SearchableSelect 
                                            v-model="form.type_id"
                                            :options="assessmentTypes.map(t => ({ id: t.id, name: t.name }))"
                                            placeholder="Select Category..."
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Academic Term</label>
                                        <SearchableSelect 
                                            v-model="form.term_id"
                                            :options="academicTerms.map(t => ({ id: t.id, name: t.name }))"
                                            placeholder="Select Term..."
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Evaluation Date</label>
                                        <div class="relative">
                                            <Calendar class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/80" />
                                            <Input v-model="form.date" type="date" class="h-10 rounded-lg border-border bg-muted/10 pl-10 text-sm focus:bg-background" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Total Marks</label>
                                        <Input v-model="form.total_marks" type="number" class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Passing Marks</label>
                                        <Input v-model="form.passing_marks" type="number" class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Weight (%)</label>
                                        <Input v-model="form.weight" type="number" class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-muted-foreground">Status</label>
                                        <select v-model="form.status" class="h-10 w-full rounded-lg border border-border bg-muted/10 px-3 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex gap-4 rounded-xl border border-border bg-muted/5 p-4">
                                    <Info class="h-5 w-5 shrink-0 text-primary" />
                                    <div>
                                        <h4 class="text-xs font-semibold text-foreground">Pro-Tip: Descriptive Titles</h4>
                                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">Include the term and scope in the title to make it easier to search later in the Report Card aggregator.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Context -->
                            <div
                                v-if="currentStep === 2"
                                class="animate-in space-y-6 duration-500 fade-in slide-in-from-right-4"
                            >
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <label
                                            class="text-xs font-medium text-muted-foreground"
                                            >Grade Level</label
                                        >
                                        <SearchableSelect 
                                            v-model="form.grade_level_id"
                                            :options="gradeLevels.map(g => ({ id: g.id, name: g.name }))"
                                            placeholder="Select Grade..."
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-xs font-medium text-muted-foreground"
                                            >Target Class</label
                                        >
                                        <SearchableSelect 
                                            v-model="form.class_id"
                                            :options="availableClasses.map((c: any) => ({ id: c.id, name: c.name }))"
                                            placeholder="Select Class..."
                                            :disabled="!form.grade_level_id"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-xs font-medium text-muted-foreground"
                                            >Subject Area</label
                                        >
                                        <SearchableSelect 
                                            v-model="form.subject_id"
                                            :options="subjects.map(s => ({ id: s.id, name: s.name }))"
                                            placeholder="Select Subject..."
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Mapping -->
                            <div
                                v-if="currentStep === 3"
                                class="animate-in space-y-6 duration-500 fade-in slide-in-from-right-4"
                            >
                                <div
                                    v-if="loading"
                                    class="flex flex-col items-center justify-center py-12"
                                >
                                    <div
                                        class="h-12 w-12 animate-spin rounded-full border-4 border-primary border-t-transparent"
                                    ></div>
                                    <p
                                        class="mt-4 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                    >
                                        Accessing Curriculum Repository...
                                    </p>
                                </div>
                                <div v-else class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <label
                                            class="text-xs font-medium text-muted-foreground"
                                            >Curriculum Strand</label
                                        >
                                        <select
                                            v-model="form.strand_id"
                                            class="h-10 w-full rounded-lg border border-border bg-muted/10 px-3 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                                        >
                                            <option value="">
                                                Select Strand...
                                            </option>
                                            <option
                                                v-for="strand in strands"
                                                :key="strand.id"
                                                :value="String(strand.id)"
                                            >
                                                {{ strand.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Sub-Strand</label
                                        >
                                        <select
                                            v-model="form.sub_strand_id"
                                            :disabled="!form.strand_id"
                                            class="h-10 w-full rounded-lg border border-border bg-muted/10 px-3 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10 disabled:opacity-50"
                                        >
                                            <option value="">
                                                Select Sub-Strand...
                                            </option>
                                            <option
                                                v-for="sub in subStrands"
                                                :key="sub.id"
                                                :value="String(sub.id)"
                                            >
                                                {{ sub.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Indicators -->
                            <div
                                v-if="currentStep === 4"
                                class="flex h-full animate-in flex-col space-y-6 overflow-hidden duration-500 fade-in slide-in-from-right-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <h3
                                        class="text-sm font-bold tracking-tight text-muted-foreground/80 "
                                    >
                                        Available Learning Indicators
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            v-if="availableIndicators.length > 0"
                                            variant="ghost" 
                                            size="sm" 
                                            @click="selectAllIndicators"
                                            class="h-7 px-2 text-[10px] font-black uppercase tracking-widest text-primary hover:bg-primary/10"
                                        >
                                            Select All
                                        </Button>
                                        <Badge
                                            variant="outline"
                                            class="px-2 text-xs font-bold tracking-tight "
                                            >{{ availableIndicators.length }} Total</Badge
                                        >
                                    </div>
                                </div>

                                <div v-if="form.indicators.length > 0" class="flex flex-wrap gap-2 mb-4 p-3 rounded-xl bg-primary/5 border border-primary/20">
                                    <p class="w-full text-[9px] font-black uppercase tracking-widest text-primary mb-1 ml-1">Current Selection ({{ form.indicators.length }})</p>
                                    <Badge
                                        v-for="i in form.indicators"
                                        :key="i.id"
                                        variant="secondary"
                                        class="rounded-lg border border-primary/20 bg-background px-3 py-1 font-bold text-primary shadow-sm flex items-center gap-2"
                                    >
                                        {{ i.indicator.substring(0, 30) }}{{ i.indicator.length > 30 ? '...' : '' }}
                                        <X class="h-3 w-3 cursor-pointer hover:text-red-500" @click.stop="toggleIndicator(i)" />
                                    </Badge>
                                    <button @click="clearAllIndicators" class="text-[9px] font-black uppercase tracking-widest text-red-500 hover:text-red-600 transition-all ml-auto pr-2">Clear All</button>
                                </div>

                                <div
                                    class="grid flex-1 gap-4 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="indicator in availableIndicators"
                                        :key="indicator.id"
                                        @click="toggleIndicator(indicator)"
                                        class="group relative cursor-pointer overflow-hidden rounded-2xl border p-4 transition-all"
                                        :class="
                                            form.indicators.some(
                                                (i: Indicator) => i.id === indicator.id,
                                            )
                                                ? 'border-primary/30 bg-primary/10 ring-2 ring-primary/20'
                                                : 'border-border bg-card hover:border-primary/30 hover:bg-muted/10'
                                        "
                                    >
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border transition-all"
                                                :class="
                                                    form.indicators.some(
                                                        (i: Indicator) =>
                                                            i.id ===
                                                            indicator.id,
                                                    )
                                                        ? 'border-primary bg-primary text-white shadow-lg shadow-primary/30'
                                                        : 'border-border/50 bg-background text-transparent'
                                                "
                                            >
                                                <CheckCircle2 class="h-4 w-4" />
                                            </div>
                                            <div class="flex-1">
                                                <p
                                                    class="mb-1 text-xs leading-none font-bold tracking-tight text-muted-foreground/80 "
                                                >
                                                    Indicator Content
                                                </p>
                                                <p
                                                    class="text-sm leading-snug font-bold text-foreground"
                                                >
                                                    {{ indicator.indicator }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="absolute top-0 right-0 h-full w-1 transition-all"
                                            :class="
                                                form.indicators.some(
                                                    (i: Indicator) =>
                                                        i.id === indicator.id,
                                                )
                                                    ? 'bg-primary'
                                                    : 'bg-transparent'
                                            "
                                        ></div>
                                    </div>

                                    <div
                                        v-if="
                                            availableIndicators.length === 0 && !loading
                                        "
                                        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border py-12"
                                    >
                                        <Layers
                                            class="mb-4 h-10 w-10 text-slate-200"
                                        />
                                        <p
                                            class="text-sm font-bold tracking-tight text-slate-300 "
                                        >
                                            No indicators mapped for this
                                            sub-strand
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5: Review -->
                            <div
                                v-if="currentStep === 5"
                                class="animate-in space-y-8 rounded-2xl border bg-background p-8 shadow-inner"
                            >
                                <div class="mt-4 bg-red-50 text-red-600 p-4 rounded-xl text-sm font-semibold border-2 border-red-100" v-if="Object.keys(form.errors).length > 0">
                                    <div class="flex items-center gap-2 mb-2 text-red-700">
                                        <Info class="h-4 w-4" />
                                        Please correct the following fields to update your assessment:
                                    </div>
                                    <ul class="list-disc pl-5 mt-2 font-normal text-xs space-y-1">
                                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                    </ul>
                                </div>

                                <div class="grid gap-8 md:grid-cols-2">
                                    <div class="space-y-4">
                                        <div>
                                            <p
                                                class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >
                                                Assessment Name
                                            </p>
                                            <p
                                                class="text-lg leading-tight font-bold text-foreground"
                                            >
                                                {{ form.title }}
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p
                                                    class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                                >
                                                    Grade
                                                </p>
                                                <p
                                                    class="text-sm font-bold text-slate-700 capitalize transition-colors group-hover:text-indigo-600"
                                                >
                                                    {{
                                                        selectedGrade?.name ||
                                                        'N/A'
                                                    }}
                                                </p>
                                            </div>
                                            <div>
                                                <p
                                                    class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                                >
                                                    Date
                                                </p>
                                                <p
                                                    class="text-sm font-bold text-slate-700"
                                                >
                                                    {{ form.date }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-l-4 border-l-primary bg-card p-6 shadow-sm"
                                    >
                                        <p
                                            class="mb-2 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                        >
                                            Mapped Indicators
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <Badge
                                                v-for="i in form.indicators"
                                                :key="i.id"
                                                variant="secondary"
                                                class="rounded-lg border border-primary/20 bg-primary/10 px-3 py-1 font-bold whitespace-nowrap text-primary"
                                            >
                                                {{
                                                    i.indicator.substring(
                                                        0,
                                                        30,
                                                    )
                                                }}...
                                            </Badge>
                                            <p
                                                v-if="
                                                    form.indicators.length === 0
                                                "
                                                class="text-xs font-bold tracking-tight text-rose-500 "
                                            >
                                                No Indicators Selected!
                                            </p>
                                        </div>
                                        <p
                                            class="mt-4 text-right text-xs font-bold tracking-tight text-muted-foreground/80 "
                                        >
                                            {{
                                                form.indicators.length
                                            }}
                                            Evaluation Items
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>

                        <!-- Footer -->
                        <div class="flex items-center justify-between border-t border-border/50 bg-muted/5 px-6 py-4">
                            <Button variant="outline" @click="prevStep" :disabled="currentStep === 1" class="h-10 rounded-lg border-border bg-card px-6 text-xs font-semibold disabled:opacity-30">
                                <ChevronLeft class="mr-2 h-4 w-4" /> Go Back
                            </Button>
                            <div class="flex items-center gap-3">
                                <Button v-if="currentStep < 5" @click="nextStep" :disabled="currentStep === 4 && form.indicators.length === 0" class="group h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                                    Next Phase
                                    <ChevronRight class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </Button>
                                <Button v-else @click="submit" :disabled="form.processing || form.indicators.length === 0" class="h-10 rounded-lg bg-emerald-600 px-8 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all">
                                    <Save v-if="!form.processing" class="mr-2 h-4 w-4" />
                                    <div v-else class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                    Update Assessment
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Context Sidebar (Right) -->
                <div class="flex h-full w-full shrink-0 flex-col gap-6 lg:w-72">
                    <Card class="relative overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <CardHeader class="border-b border-border/50 bg-muted/5">
                            <CardTitle class="flex items-center gap-2 text-xs font-semibold text-foreground">
                                <Target class="h-4 w-4 text-primary" />
                                Assessment Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-5 p-4">
                            <div class="space-y-1">
                                <p class="text-[10px] font-medium text-muted-foreground">Current Selection</p>
                                <p class="text-xs font-semibold text-foreground">{{ form.title || 'Untitled Assessment' }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-medium text-muted-foreground">Focus Type</p>
                                    <Badge variant="outline" class="rounded-lg border-border bg-muted/10 py-0.5 text-[10px] font-semibold text-foreground">
                                        {{ assessmentTypes.find(t => String(t.id) === String(form.type_id))?.name || 'Not Selected' }}
                                    </Badge>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-medium text-muted-foreground">Scale</p>
                                    <p class="text-xs font-semibold text-foreground">Grade (1-4)</p>
                                </div>
                            </div>
                            <Separator />
                            <div class="space-y-2">
                                <p class="text-[10px] font-medium text-muted-foreground">Selected Criteria</p>
                                <div class="flex flex-col gap-1.5">
                                    <div v-for="i in form.indicators" :key="i.id" class="flex items-center gap-2 text-xs">
                                        <div class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></div>
                                        <span class="truncate text-foreground">{{ i.indicator }}</span>
                                    </div>
                                    <p v-if="form.indicators.length === 0" class="text-[10px] text-muted-foreground">No indicators yet</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div
                        class="flex flex-1 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border/50 bg-muted/10 p-6 text-center opacity-70"
                    >
                        <BookOpen class="mb-3 h-8 w-8 text-slate-300" />
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground/80 "
                        >
                            Contextual Guidance will appear here during setup
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
}

.animate-in {
    animation-fill-mode: forwards;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.98);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.slide-in-from-right-4 {
    animation: slideInFromRight 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideInFromRight {
    from {
        transform: translateX(20px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
