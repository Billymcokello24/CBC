<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    ClipboardList,
    Save,
    ArrowLeft,
    User,
    CheckCircle2,
    AlertCircle,
    Search,
    Keyboard,
    Zap,
    Info,
    MoreHorizontal,
    ChevronRight,
    ChevronDown,
    Check,
    X,
    Star,
    BarChart3,
    BookOpen,
    Layers,
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { Separator } from '@/components/ui/separator';
import axios from 'axios';

const props = defineProps<{
    assessment: any;
    students: Array<any>;
    existingRatings: Record<number, any[]>;
    ratingScales: Array<any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Grading Sheet', href: '#' },
];

const searchQuery = ref('');
const activeCell = ref({ studentIndex: 0, itemIndex: 0 });
const saving = ref(false);
const lastSavedAt = ref<Date | null>(null);

// Group items by competency (Strand)
const groupedItems = computed(() => {
    const groups: Record<string, any[]> = {};
    props.assessment.items.forEach((item: any) => {
        const strandName = item.indicator?.competency?.name || 'General';
        if (!groups[strandName]) groups[strandName] = [];
        groups[strandName].push(item);
    });
    return groups;
});

// Flatten items for linear index mapping
const flatItems = computed(() => {
    return Object.values(groupedItems.value).flat();
});

// Initialize results matrix
const results = ref<any[]>(
    props.students.map((student) => {
        const studentRatings = props.existingRatings[student.id] || [];
        return {
            id: student.id,
            name: `${student.first_name} ${student.last_name}`,
            admission_number: student.admission_number,
            photo: student.photo,
            ratings: flatItems.value.map((item: any) => {
                const existing = studentRatings.find(
                    (r: any) => r.assessment_item_id === item.id,
                );
                return {
                    assessment_item_id: item.id,
                    rating: existing ? existing.rating : null,
                    feedback: existing ? existing.feedback : '',
                };
            }),
        };
    }),
);

const filteredResults = computed(() => {
    if (!searchQuery.value) return results.value;
    const query = searchQuery.value.toLowerCase();
    return results.value.filter(
        (r: any) =>
            r.name.toLowerCase().includes(query) ||
            r.admission_number.toLowerCase().includes(query),
    );
});

const getRatingStyle = (rating: number | null) => {
    if (!rating) return {};
    const scale = props.ratingScales.find((s) => s.id === rating);
    return scale
        ? {
              backgroundColor: scale.color,
              borderColor: scale.color,
              color: 'white',
          }
        : {};
};

const updateRating = async (
    studentIndex: number,
    itemIndex: number,
    value: number | null,
) => {
    const student = filteredResults.value[studentIndex];
    const ratingEntry = student.ratings[itemIndex];

    if (ratingEntry.rating === value) {
        ratingEntry.rating = null;
    } else {
        ratingEntry.rating = value;
    }

    try {
        await axios.post(route('assessments.grading.quick-save'), {
            student_id: student.id,
            assessment_item_id: ratingEntry.assessment_item_id,
            rating: ratingEntry.rating,
        });
        lastSavedAt.value = new Date();
    } catch (e) {
        console.error('Failed to auto-save', e);
    }
};

const handleKeyDown = (e: KeyboardEvent) => {
    const { studentIndex, itemIndex } = activeCell.value;

    if (e.key === 'ArrowUp' && studentIndex > 0) {
        activeCell.value.studentIndex--;
        e.preventDefault();
    } else if (
        e.key === 'ArrowDown' &&
        studentIndex < filteredResults.value.length - 1
    ) {
        activeCell.value.studentIndex++;
        e.preventDefault();
    } else if (e.key === 'ArrowLeft' && itemIndex > 0) {
        activeCell.value.itemIndex--;
        e.preventDefault();
    } else if (
        e.key === 'ArrowRight' &&
        itemIndex < flatItems.value.length - 1
    ) {
        activeCell.value.itemIndex++;
        e.preventDefault();
    } else if (['1', '2', '3', '4'].includes(e.key)) {
        updateRating(studentIndex, itemIndex, parseInt(e.key));
    } else if (e.key === 'Backspace' || e.key === 'Delete') {
        updateRating(studentIndex, itemIndex, null);
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const submitAll = () => {
    saving.value = true;
    const flatRatings = filteredResults.value.flatMap((student) =>
        student.ratings.map((r: any) => ({
            student_id: student.id,
            assessment_item_id: r.assessment_item_id,
            rating: r.rating,
            feedback: r.feedback,
        })),
    );

    router.post(
        route('assessments.grading.store', { assessment: props.assessment.id }),
        {
            ratings: flatRatings,
        },
        {
            onFinish: () => {
                saving.value = false;
            },
        },
    );
};
</script>

<template>
    <Head :title="`Grading Sheet - ${assessment.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto mt-2 flex h-full max-w-[1600px] flex-1 flex-col gap-6 overflow-hidden bg-background p-6 font-sans"
        >
            <!-- Professional Header -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-border bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-center gap-6">
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                        class="h-12 w-12 rounded-2xl border border-border bg-white shadow-sm hover:bg-muted/10"
                    >
                        <Link href="/assessments"
                            ><ArrowLeft class="h-6 w-6 text-muted-foreground"
                        /></Link>
                    </Button>
                    <div class="hidden h-12 w-px bg-slate-100 md:block"></div>
                    <div>
                        <div class="mb-1 flex items-center gap-3">
                            <h1
                                class="text-3xl font-bold tracking-tight text-foreground"
                            >
                                {{ assessment.title }}
                            </h1>
                            <Badge
                                class="rounded-lg bg-primary px-3 py-1 text-xs font-bold tracking-tight text-white "
                                >OFFICIAL EVALUATION</Badge
                            >
                        </div>
                        <div
                            class="flex items-center gap-4 text-xs font-bold text-muted-foreground/80"
                        >
                            <span
                                class="flex items-center gap-2 rounded-md border border-indigo-100 bg-indigo-50 px-2 py-0.5 text-indigo-600"
                            >
                                <BookOpen class="h-3 w-3" />
                                {{ assessment.subject?.name }}
                            </span>
                            <span
                                class="flex items-center gap-2 rounded-md border border-purple-100 bg-purple-50 px-2 py-0.5 text-purple-600"
                            >
                                <Layers class="h-3 w-3" />
                                {{ assessment.class?.name }}
                            </span>
                            <span class="flex items-center gap-2">
                                <Calendar class="h-3 w-3" />
                                {{ assessment.academic_term?.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div
                        class="mr-2 hidden border-r border-border pr-6 text-right sm:block"
                    >
                        <p
                            class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                        >
                            Assessment Matrix State
                        </p>
                        <p
                            class="flex items-center justify-end gap-2 text-sm font-bold text-emerald-600"
                        >
                            <Zap class="h-4 w-4 fill-emerald-600" />
                            {{
                                lastSavedAt
                                    ? `LAST SAVED: ${lastSavedAt.toLocaleTimeString()}`
                                    : 'LIVE SYNC ACTIVE'
                            }}
                        </p>
                    </div>
                    <Button
                        @click="submitAll"
                        :disabled="saving"
                        class="h-14 rounded-2xl bg-primary px-10 text-xs font-bold tracking-tight text-white  shadow-xl shadow-slate-200 transition-all hover:scale-[1.02] hover:bg-slate-800 active:scale-95"
                    >
                        <Save v-if="!saving" class="mr-3 h-5 w-5" />
                        <div
                            v-else
                            class="mr-3 h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></div>
                        Finalize Assessment
                    </Button>
                </div>
            </div>

            <!-- Scoring Guide & Filters -->
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="group relative xl:col-span-3">
                    <Search
                        class="absolute top-1/2 left-5 h-5 w-5 -translate-y-1/2 text-muted-foreground/80 transition-colors group-focus-within:text-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search learner list..."
                        class="h-14 rounded-2xl border-white bg-white pl-14 font-bold shadow-sm placeholder:text-slate-300 focus:ring-slate-900/10"
                    />
                </div>

                <div
                    class="scrollbar-none flex items-center gap-4 overflow-x-auto scroll-smooth pb-4 xl:col-span-9"
                >
                    <div
                        v-for="scale in ratingScales"
                        :key="scale.id"
                        class="flex shrink-0 items-center gap-4 rounded-2xl border border-b-4 border-border bg-white px-5 py-3 shadow-sm"
                        :style="{ borderBottomColor: scale.color }"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-white shadow-lg"
                            :style="{ backgroundColor: scale.color }"
                        >
                            {{ scale.id }}
                        </div>
                        <div>
                            <p
                                class="mb-1 text-xs leading-none font-bold tracking-tight text-muted-foreground/80 "
                            >
                                {{ scale.code }}
                            </p>
                            <p
                                class="text-xs font-bold whitespace-nowrap text-slate-800"
                            >
                                {{ scale.name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master Grading Matrix -->
            <Card
                class="flex flex-1 flex-col overflow-hidden rounded-xl border-none bg-white shadow-lg"
            >
                <div class="relative flex-1 overflow-auto">
                    <table class="w-full border-collapse">
                        <thead class="sticky top-0 z-30 bg-white">
                            <!-- Strand Grouping Row -->
                            <tr
                                class="bg-primary text-xs font-bold tracking-tight text-white "
                            >
                                <th
                                    class="sticky left-0 z-40 w-[320px] min-w-[320px] bg-primary p-6 text-left"
                                >
                                    LEARNER IDENTIFICATION
                                </th>
                                <th
                                    v-for="(items, strand) in groupedItems"
                                    :key="strand"
                                    :colspan="items.length"
                                    class="border-l border-white/10 p-4 text-center"
                                >
                                    <span
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <Layers class="h-4 w-4" />
                                        STRAND: {{ strand }}
                                    </span>
                                </th>
                            </tr>
                            <!-- Indicators Row -->
                            <tr class="bg-white shadow-sm shadow-slate-100">
                                <th
                                    class="sticky left-0 z-40 border-r border-b border-border bg-white p-6 text-left"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-bold text-slate-800"
                                            >Full Name</span
                                        >
                                        <Badge
                                            variant="outline"
                                            class="text-xs font-bold"
                                            >ADMISSION</Badge
                                        >
                                    </div>
                                </th>
                                <th
                                    v-for="(item, idx) in flatItems"
                                    :key="item.id"
                                    class="min-w-[220px] border-b border-l border-border p-4 text-left transition-colors"
                                    :class="{
                                        'bg-muted/10 ring-2 ring-slate-900/5 ring-inset':
                                            activeCell.itemIndex === idx,
                                    }"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-primary text-xs font-bold text-white"
                                        >
                                            {{ Number(idx) + 1 }}
                                        </div>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger
                                                    class="text-left"
                                                >
                                                    <p
                                                        class="line-clamp-2 text-sm leading-tight font-extrabold text-slate-700"
                                                    >
                                                        {{
                                                            item.indicator
                                                                ?.indicator ||
                                                            'N/A'
                                                        }}
                                                    </p>
                                                </TooltipTrigger>
                                                <TooltipContent
                                                    class="max-w-[320px] rounded-[1.5rem] border-none bg-primary p-5 text-white shadow-lg"
                                                >
                                                    <p
                                                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground "
                                                    >
                                                        Detailed Indicator:
                                                    </p>
                                                    <p
                                                        class="text-xs leading-relaxed font-bold"
                                                    >
                                                        "{{
                                                            item.indicator
                                                                ?.indicator
                                                        }}"
                                                    </p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(student, sIdx) in filteredResults"
                                :key="student.id"
                                class="group transition-colors even:bg-muted/10/20 hover:bg-background"
                            >
                                <!-- Student Name Column -->
                                <td
                                    class="sticky left-0 z-20 border-r border-b border-border bg-white p-6 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)] group-hover:bg-muted/10"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <Avatar
                                                class="h-12 w-12 border-2 border-white shadow-lg ring-4 ring-slate-100/50 transition-all duration-300 group-hover:scale-110"
                                            >
                                                <AvatarImage
                                                    :src="student.photo"
                                                />
                                                <AvatarFallback
                                                    class="bg-indigo-600 text-sm font-bold text-white "
                                                >
                                                    {{
                                                        String(student.name)
                                                            .split(' ')
                                                            .map(
                                                                (n: string) =>
                                                                    n[0],
                                                            )
                                                            .join('')
                                                    }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div
                                                v-if="
                                                    student.ratings.every(
                                                        (r: any) =>
                                                            r.rating !== null,
                                                    )
                                                "
                                                class="absolute -right-2 -bottom-2 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-emerald-500 shadow-lg"
                                            >
                                                <Check
                                                    class="h-3.5 w-3.5 stroke-[4] text-white"
                                                />
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p
                                                class="mb-1 truncate text-sm leading-none font-bold tracking-tight text-foreground "
                                            >
                                                {{ student.name }}
                                            </p>
                                            <p
                                                class="text-xs leading-none font-bold tracking-tight text-muted-foreground/80"
                                            >
                                                {{ student.admission_number }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Ratings Matrix Cells -->
                                <td
                                    v-for="(rating, iIdx) in student.ratings"
                                    :key="iIdx"
                                    class="relative border-b border-l border-border p-4 text-center transition-all"
                                    :class="{
                                        'z-10 scale-[1.05] rounded-xl bg-white shadow-lg ring-4 ring-slate-900':
                                            activeCell.studentIndex ===
                                                Number(sIdx) &&
                                            activeCell.itemIndex ===
                                                Number(iIdx),
                                    }"
                                    @click="
                                        activeCell = {
                                            studentIndex: Number(sIdx),
                                            itemIndex: Number(iIdx),
                                        }
                                    "
                                >
                                    <div
                                        class="group/cell flex h-12 w-full items-center justify-center gap-2 transition-all"
                                    >
                                        <!-- Rating Buttons -->
                                        <template
                                            v-if="
                                                activeCell.studentIndex ===
                                                    sIdx &&
                                                activeCell.itemIndex === iIdx
                                            "
                                        >
                                            <button
                                                v-for="val in [1, 2, 3, 4]"
                                                :key="val"
                                                @click.stop="
                                                    updateRating(
                                                        sIdx,
                                                        iIdx,
                                                        val,
                                                    )
                                                "
                                                class="flex h-10 w-10 items-center justify-center rounded-xl border-2 text-sm font-bold shadow-md transition-all hover:scale-110 active:scale-90"
                                                :style="
                                                    getRatingStyle(
                                                        val === rating.rating
                                                            ? val
                                                            : null,
                                                    )
                                                "
                                                :class="[
                                                    val === rating.rating
                                                        ? 'border-transparent'
                                                        : 'border-slate-50 bg-white text-slate-200 hover:border-border/50 hover:text-muted-foreground/80',
                                                ]"
                                            >
                                                {{ val }}
                                            </button>
                                        </template>
                                        <template v-else>
                                            <div
                                                v-if="rating.rating"
                                                class="flex h-11 w-11 items-center justify-center rounded-2xl text-base font-bold shadow-xl transition-all duration-300 group-hover/cell:scale-110"
                                                :style="
                                                    getRatingStyle(
                                                        rating.rating,
                                                    )
                                                "
                                            >
                                                {{ rating.rating }}
                                            </div>
                                            <div
                                                v-else
                                                class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-dashed border-border transition-all group-hover/cell:border-slate-300"
                                            >
                                                <span
                                                    class="text-sm font-bold text-slate-100 opacity-20 group-hover/cell:text-slate-300"
                                                    >PENDING</span
                                                >
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Keyboard Helper Overlay -->
                                    <div
                                        v-if="
                                            activeCell.studentIndex === sIdx &&
                                            activeCell.itemIndex === iIdx
                                        "
                                        class="absolute -top-5 left-1/2 z-50 -translate-x-1/2 animate-bounce rounded-full bg-primary px-3 py-1 text-xs font-bold tracking-tight text-white  shadow-xl"
                                    >
                                        Type 1-4
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Matrix Legend / Global Stats -->
                <div
                    class="flex flex-col items-center justify-between gap-8 border-t border-slate-800 bg-primary p-8 text-white md:flex-row"
                >
                    <div class="flex items-center gap-6">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white shadow-inner backdrop-blur-md"
                        >
                            <Zap class="h-7 w-7 animate-pulse fill-white" />
                        </div>
                        <div class="space-y-1">
                            <h4
                                class="text-sm font-bold tracking-tight "
                            >
                                Evaluation Integrity Matrix
                            </h4>
                            <p
                                class="text-xs font-bold tracking-wider text-muted-foreground/80"
                            >
                                Keyboard Bindings: ARROW KEYS to navigate • 1-4
                                to Rate • BACKSPACE to Clear
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-10">
                        <div class="group text-center">
                            <span
                                class="mb-1 block text-xs font-bold tracking-tight text-muted-foreground "
                                >Total Learners</span
                            >
                            <span class="text-2xl font-bold">{{
                                results.length
                            }}</span>
                        </div>
                        <div class="h-10 w-px bg-white/10"></div>
                        <div class="group text-center">
                            <span
                                class="mb-1 block text-xs font-bold tracking-tight text-muted-foreground "
                                >Evaluation Points</span
                            >
                            <span class="text-2xl font-bold">{{
                                flatItems.length
                            }}</span>
                        </div>
                        <div class="h-10 w-px bg-white/10"></div>
                        <div class="min-w-[200px] space-y-2">
                            <div class="flex items-end justify-between">
                                <span
                                    class="text-xs font-bold tracking-tight text-muted-foreground "
                                    >Completion Progress</span
                                >
                                <span
                                    class="text-sm font-bold text-emerald-400"
                                >
                                    {{
                                        Math.round(
                                            (results
                                                .flatMap((s: any) => s.ratings)
                                                .filter(
                                                    (r: any) =>
                                                        r.rating !== null,
                                                ).length /
                                                (results.length *
                                                    flatItems.length)) *
                                                100,
                                        )
                                    }}%
                                </span>
                            </div>
                            <div
                                class="h-2 w-full overflow-hidden rounded-full border border-white/10 bg-white/5 p-0.5"
                            >
                                <div
                                    class="h-full rounded-full bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)] transition-all duration-1000"
                                    :style="{
                                        width: `${Math.round((results.flatMap((s: any) => s.ratings).filter((r: any) => r.rating !== null).length / (results.length * flatItems.length)) * 100)}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,400;1,700;1,800&display=swap');

.font-sans {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

table {
    border-spacing: 0;
}

th.sticky,
td.sticky {
    box-shadow: 4px 0 8px -4px rgba(0, 0, 0, 0.05);
}

.animate-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Transition for cell scaling */
td {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
