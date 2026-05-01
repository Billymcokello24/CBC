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
    gradeLevels: Array<any>;
    subjects: Array<any>;
    academicTerms: Array<any>;
    assessmentTypes: Array<any>;
    competencies: Array<any>;
    ratingScales: Array<any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Setup Wizard', href: '/assessments/setup' },
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
    id: number;
    indicator: string;
    [key: string]: any;
}

const currentStep = ref(1);
const loading = ref(false);
const strands = ref<Strand[]>([]);
const subStrands = ref<SubStrand[]>([]);
const indicators = ref<Indicator[]>([]);

const form = useForm({
    title: '',
    description: '',
    type_id: '',
    date: new Date().toISOString().split('T')[0],
    term_id: '',
    grade_level_id: '',
    class_id: '',
    subject_id: '',
    strand_id: '',
    sub_strand_id: '',
    indicators: [] as Indicator[],
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
    // In a real app, subjects might be filtered by grade level
    // For now returning from somewhere or assuming it's available
    return []; // Will need to pass subjects via props
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

const toggleIndicator = (indicator: any) => {
    const index = form.indicators.findIndex((i) => i.id === indicator.id);
    if (index === -1) {
        form.indicators.push({ ...indicator, max_score: 4 });
    } else {
        form.indicators.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('assessments.setup.store'), {
        onSuccess: () => {
            // Success handlng
        },
    });
};
</script>

<template>
    <Head title="Assessment Setup Wizard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="font-pulsar mx-auto mt-2 flex h-full max-w-[1200px] flex-1 flex-col gap-6 overflow-hidden p-6"
        >
            <!-- Progress Bar -->
            <div class="relative mb-8 flex items-center justify-between px-4">
                <div
                    class="absolute top-1/2 left-0 z-0 h-1 w-full -translate-y-1/2 bg-slate-100"
                ></div>
                <div
                    class="absolute top-1/2 left-0 z-0 h-1 -translate-y-1/2 bg-indigo-600 transition-all duration-500"
                    :style="{
                        width: `${((currentStep - 1) / (steps.length - 1)) * 100}%`,
                    }"
                ></div>

                <div
                    v-for="step in steps"
                    :key="step.id"
                    class="relative z-10 flex flex-col items-center"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full border-4 transition-all duration-300"
                        :class="[
                            currentStep === step.id
                                ? 'scale-110 border-indigo-200 bg-indigo-600 text-white'
                                : currentStep > step.id
                                  ? 'border-indigo-200 bg-indigo-600 text-white'
                                  : 'border-border bg-white text-slate-300',
                        ]"
                    >
                        <CheckCircle2
                            v-if="currentStep > step.id"
                            class="h-5 w-5"
                        />
                        <span v-else class="text-xs font-bold">{{
                            step.id
                        }}</span>
                    </div>
                    <span
                        class="absolute top-12 text-xs font-medium tracking-tight whitespace-nowrap text-muted-foreground/80 "
                        :class="{ 'text-indigo-600': currentStep === step.id }"
                    >
                        {{ step.name }}
                    </span>
                </div>
            </div>

            <div
                class="mt-8 flex h-full flex-col gap-8 overflow-hidden lg:flex-row"
            >
                <!-- Main Content Area -->
                <div class="flex flex-1 flex-col gap-6 overflow-hidden">
                    <Card
                        class="flex h-full flex-col overflow-hidden border-t-4 border-t-indigo-600 shadow-xl"
                    >
                        <CardHeader class="pb-4">
                            <div class="mb-1 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600 shadow-sm"
                                >
                                    <component
                                        :is="
                                            steps[currentStep - 1].id === 1
                                                ? Info
                                                : steps[currentStep - 1].id ===
                                                    4
                                                  ? ListChecks
                                                  : LayoutGrid
                                        "
                                        class="h-5 w-5"
                                    />
                                </div>
                                <div>
                                    <h2
                                        class="text-xl font-bold tracking-tight text-slate-800"
                                    >
                                        {{ steps[currentStep - 1].name }}
                                    </h2>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        {{ steps[currentStep - 1].description }}
                                    </p>
                                </div>
                            </div>
                        </CardHeader>
                        <Separator />

                        <CardContent class="flex-1 overflow-y-auto p-8">
                            <!-- Step 1: General Info -->
                            <div
                                v-if="currentStep === 1"
                                class="animate-in space-y-6 duration-500 fade-in slide-in-from-right-4"
                            >
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Assessment Title</label
                                        >
                                        <Input
                                            v-model="form.title"
                                            placeholder="e.g. End of Term Mathematics Evaluation"
                                            class="h-12 border-border/50 focus:ring-indigo-500/20"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Assessment Nature</label
                                        >
                                        <select
                                            v-model="form.type_id"
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm font-medium shadow-sm transition-all focus:ring-2 focus:ring-indigo-500/20"
                                        >
                                            <option value="">
                                                Select Category...
                                            </option>
                                            <option
                                                v-for="type in assessmentTypes"
                                                :key="type.id"
                                                :value="String(type.id)"
                                            >
                                                {{ type.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Academic Term</label
                                        >
                                        <select
                                            v-model="form.term_id"
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm font-medium shadow-sm transition-all focus:ring-2 focus:ring-indigo-500/20"
                                        >
                                            <option value="">
                                                Select Term...
                                            </option>
                                            <option
                                                v-for="term in academicTerms"
                                                :key="term.id"
                                                :value="String(term.id)"
                                            >
                                                {{ term.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Evaluation Date</label
                                        >
                                        <div class="relative">
                                            <Calendar
                                                class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/80"
                                            />
                                            <Input
                                                v-model="form.date"
                                                type="date"
                                                class="h-12 border-border/50 pl-10"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-6"
                                >
                                    <Info
                                        class="h-6 w-6 shrink-0 text-indigo-500"
                                    />
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-indigo-900"
                                        >
                                            Pro-Tip: Descriptive Titles
                                        </h4>
                                        <p
                                            class="mt-1 text-xs leading-relaxed text-indigo-700/80"
                                        >
                                            Include the term and scope in the
                                            title to make it easier to search
                                            later in the Report Card aggregator.
                                        </p>
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
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Grade Level</label
                                        >
                                        <select
                                            v-model="form.grade_level_id"
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm"
                                        >
                                            <option value="">
                                                Select Grade...
                                            </option>
                                            <option
                                                v-for="grade in gradeLevels"
                                                :key="grade.id"
                                                :value="String(grade.id)"
                                            >
                                                {{ grade.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Target Class</label
                                        >
                                        <select
                                            v-model="form.class_id"
                                            :disabled="!form.grade_level_id"
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm disabled:bg-muted/10"
                                        >
                                            <option value="">
                                                Select Class...
                                            </option>
                                            <option
                                                v-for="cls in availableClasses"
                                                :key="cls.id"
                                                :value="String(cls.id)"
                                            >
                                                {{ cls.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Subject Area</label
                                        >
                                        <select
                                            v-model="form.subject_id"
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm"
                                        >
                                            <option value="">
                                                Select Subject...
                                            </option>
                                            <!-- This should come from props -->
                                            <option
                                                v-for="sub in subjects"
                                                :key="sub.id"
                                                :value="String(sub.id)"
                                            >
                                                {{ sub.name }}
                                            </option>
                                        </select>
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
                                        class="h-12 w-12 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent"
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
                                            class="ml-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                            >Curriculum Strand</label
                                        >
                                        <select
                                            v-model="form.strand_id"
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm"
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
                                            class="h-12 w-full rounded-md border border-border/50 bg-white px-3 text-sm disabled:bg-muted/10"
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
                                    <Badge
                                        variant="outline"
                                        class="px-2 text-xs font-bold tracking-tight "
                                        >{{ indicators.length }} Total</Badge
                                    >
                                </div>

                                <div
                                    class="grid flex-1 gap-4 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="indicator in indicators"
                                        :key="indicator.id"
                                        @click="toggleIndicator(indicator)"
                                        class="group relative cursor-pointer overflow-hidden rounded-2xl border p-4 transition-all"
                                        :class="
                                            form.indicators.some(
                                                (i) => i.id === indicator.id,
                                            )
                                                ? 'border-indigo-200 bg-indigo-50 ring-2 ring-indigo-500/10'
                                                : 'border-border bg-white hover:border-indigo-200 hover:bg-muted/10'
                                        "
                                    >
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border transition-all"
                                                :class="
                                                    form.indicators.some(
                                                        (i) =>
                                                            i.id ===
                                                            indicator.id,
                                                    )
                                                        ? 'border-indigo-600 bg-indigo-600 text-white shadow-lg shadow-indigo-200'
                                                        : 'border-border/50 bg-white text-transparent'
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
                                                    class="text-sm leading-snug font-bold text-slate-700"
                                                >
                                                    {{ indicator.indicator }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="absolute top-0 right-0 h-full w-1 transition-all"
                                            :class="
                                                form.indicators.some(
                                                    (i) =>
                                                        i.id === indicator.id,
                                                )
                                                    ? 'bg-indigo-600'
                                                    : 'bg-transparent'
                                            "
                                        ></div>
                                    </div>

                                    <div
                                        v-if="
                                            indicators.length === 0 && !loading
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
                                        class="rounded-2xl border border-l-4 border-l-indigo-600 bg-white p-6 shadow-sm"
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
                                                class="rounded-lg border border-indigo-100 bg-indigo-50/50 px-3 py-1 font-bold whitespace-nowrap text-indigo-700"
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
                        <div
                            class="flex items-center justify-between border-t bg-muted/10/80 px-8 py-6"
                        >
                            <Button
                                variant="outline"
                                @click="prevStep"
                                :disabled="currentStep === 1"
                                class="h-11 rounded-xl border-border/50 px-6 text-xs font-bold tracking-tight  disabled:opacity-30"
                            >
                                <ChevronLeft class="mr-2 h-4 w-4" /> Go Back
                            </Button>

                            <div class="flex items-center gap-3">
                                <Button
                                    v-if="currentStep < 5"
                                    @click="nextStep"
                                    :disabled="
                                        currentStep === 4 &&
                                        form.indicators.length === 0
                                    "
                                    class="group h-11 rounded-xl bg-indigo-600 px-8 text-xs font-bold tracking-tight  shadow-lg shadow-indigo-100 hover:bg-indigo-700"
                                >
                                    Next Phase
                                    <ChevronRight
                                        class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                                    />
                                </Button>
                                <Button
                                    v-else
                                    @click="submit"
                                    :disabled="
                                        form.processing ||
                                        form.indicators.length === 0
                                    "
                                    class="h-11 rounded-xl bg-emerald-600 px-8 text-xs font-bold tracking-tight  shadow-lg shadow-emerald-100 hover:bg-emerald-700"
                                >
                                    <Save
                                        v-if="!form.processing"
                                        class="mr-2 h-4 w-4"
                                    />
                                    <div
                                        v-else
                                        class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                    ></div>
                                    Finalize Assessment
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Context Sidebar (Right) -->
                <div class="flex h-full w-full shrink-0 flex-col gap-6 lg:w-72">
                    <Card
                        class="relative overflow-hidden border-none bg-indigo-900 shadow-lg"
                    >
                        <div
                            class="absolute -top-8 -right-8 h-32 w-32 rounded-full bg-indigo-500/20 text-red-500 blur-3xl"
                        ></div>
                        <CardHeader>
                            <CardTitle
                                class="flex items-center gap-2 text-sm font-bold tracking-tight text-white "
                            >
                                <Target class="h-4 w-4 text-indigo-300" />
                                Assessment Scout
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6 text-indigo-100/80">
                            <div class="space-y-1">
                                <p
                                    class="text-xs leading-tight font-bold tracking-tight text-indigo-400 "
                                >
                                    Current Selection
                                </p>
                                <p
                                    class="text-xs leading-relaxed font-bold whitespace-pre-wrap"
                                >
                                    {{ form.title || 'Untitled Assessment' }}
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p
                                        class="text-xs leading-tight font-bold tracking-tight text-indigo-400 "
                                    >
                                        Focus Type
                                    </p>
                                    <Badge
                                        variant="outline"
                                        class="rounded-lg border-indigo-500/50 bg-indigo-500/10 py-0.5 text-xs font-bold tracking-tight whitespace-nowrap text-indigo-200 "
                                    >
                                        {{
                                            assessmentTypes.find(
                                                (t) =>
                                                    String(t.id) ===
                                                    String(form.type_id),
                                            )?.name || 'Not Selected'
                                        }}
                                    </Badge>
                                </div>
                                <div class="space-y-1">
                                    <p
                                        class="text-xs leading-tight font-bold tracking-tight text-indigo-400 "
                                    >
                                        Scale
                                    </p>
                                    <p
                                        class="text-xs font-bold whitespace-nowrap text-white"
                                    >
                                        Grade (1-4)
                                    </p>
                                </div>
                            </div>
                            <Separator class="bg-indigo-800" />
                            <div class="space-y-2">
                                <p
                                    class="text-xs leading-tight font-bold tracking-tight text-indigo-400 "
                                >
                                    Selected Criteria
                                </p>
                                <div class="flex flex-col gap-1.5">
                                    <div
                                        v-for="i in form.indicators"
                                        :key="i.id"
                                        class="group flex items-center gap-2 text-xs font-bold"
                                    >
                                        <div
                                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"
                                        ></div>
                                        <span class="truncate">{{
                                            i.indicator
                                        }}</span>
                                    </div>
                                    <p
                                        v-if="form.indicators.length === 0"
                                        class="text-xs font-bold tracking-tight text-indigo-600/50 "
                                    >
                                        No indicators yet
                                    </p>
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
.font-pulsar {
    font-family:
        'Inter',
        system-ui,
        -apple-system,
        sans-serif;
}

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
