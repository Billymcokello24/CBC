<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    GraduationCap,
    ChevronRight,
    Plus,
    Info,
    CheckCircle2,
    MoreVertical,
    ArrowLeft,
    Eye,
    Edit2,
    Trash2,
    X,
    LayoutGrid,
    List as ListIcon,
    Download,
    MoreHorizontal,
    Search,
    Filter,
    BookOpen,
    Target,
    Sparkles,
    Lightbulb,
    ClipboardList,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    strand: any;
    subject: any;
    grade: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Syllabus', href: '/curriculum/syllabus' },
    {
        title: props.subject.name,
        href: `/curriculum/syllabus/${props.subject.id}/${props.grade.id}`,
    },
    { title: props.strand.name, href: '#' },
];

// Modals State
const showSubStrandModal = ref(false);
const showOutcomeModal = ref(false);
const viewMode = ref<'grid' | 'list'>('list');

const editingSubStrand = ref<any>(null);
const editingOutcome = ref<any>(null);

// Forms
const subStrandForm = useForm({
    name: '',
    code: '',
    description: '',
    strand_id: props.strand.id,
    display_order: 1,
});

const outcomeForm = useForm({
    outcome: '',
    sub_strand_id: null as number | null,
    outcome_type: 'Standard',
    display_order: 1,
});

// Topic Logic (Sub-Strands)
const openSubStrandModal = (subStrand: any = null) => {
    editingSubStrand.value = subStrand;
    if (subStrand) {
        subStrandForm.name = subStrand.name;
        subStrandForm.code = subStrand.code || '';
        subStrandForm.description = subStrand.description || '';
        subStrandForm.display_order = subStrand.display_order || 1;
    } else {
        subStrandForm.reset();
        subStrandForm.strand_id = props.strand.id;
        subStrandForm.display_order =
            (props.strand.sub_strands?.length || 0) + 1;
    }
    showSubStrandModal.value = true;
};

const submitSubStrand = () => {
    if (editingSubStrand.value) {
        subStrandForm.put(
            route(
                'curriculum.syllabus.sub-topics.update',
                editingSubStrand.value.id,
            ),
            {
                onSuccess: () => {
                    showSubStrandModal.value = false;
                    editingSubStrand.value = null;
                },
            },
        );
    } else {
        subStrandForm.post(route('curriculum.syllabus.sub-topics.store'), {
            onSuccess: () => {
                showSubStrandModal.value = false;
            },
        });
    }
};

const deleteSubStrand = (id: number) => {
    if (
        confirm(
            'Are you sure you want to remove this sub-strand? This will also remove all its learning goals.',
        )
    ) {
        useForm({}).delete(route('curriculum.syllabus.sub-topics.destroy', id));
    }
};

// Outcome Logic
const openOutcomeModal = (subStrandId: number, outcome: any = null) => {
    editingOutcome.value = outcome;
    outcomeForm.sub_strand_id = subStrandId;
    if (outcome) {
        outcomeForm.outcome = outcome.outcome;
        outcomeForm.outcome_type = outcome.outcome_type || 'Standard';
        outcomeForm.display_order = outcome.display_order || 1;
    } else {
        outcomeForm.outcome = '';
        outcomeForm.outcome_type = 'Standard';
        const subStrand = props.strand.sub_strands.find(
            (ss: any) => ss.id === subStrandId,
        );
        outcomeForm.display_order =
            (subStrand?.learning_outcomes?.length || 0) + 1;
    }
    showOutcomeModal.value = true;
};

const submitOutcome = () => {
    if (editingOutcome.value) {
        outcomeForm.put(
            route(
                'curriculum.syllabus.outcomes.update',
                editingOutcome.value.id,
            ),
            {
                onSuccess: () => {
                    showOutcomeModal.value = false;
                    editingOutcome.value = null;
                },
            },
        );
    } else {
        outcomeForm.post(route('curriculum.syllabus.outcomes.store'), {
            onSuccess: () => {
                showOutcomeModal.value = false;
            },
        });
    }
};

const deleteOutcome = (id: number) => {
    if (confirm('Are you sure you want to remove this learning goal?')) {
        useForm({}).delete(route('curriculum.syllabus.outcomes.destroy', id));
    }
};

const printSyllabus = () => {
    window.print();
};
</script>

<template>
    <Head :title="`${strand.name} - ${subject.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 flex-col gap-6 bg-[#f9fafb]/30 p-6 font-sans"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <Link
                        :href="`/curriculum/syllabus/${subject.id}/${grade.id}`"
                        class="group mb-1 inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase transition-all hover:text-blue-600"
                    >
                        <ArrowLeft
                            class="h-3 w-3 transition-transform group-hover:-translate-x-1"
                        />
                        Strands
                    </Link>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white"
                        >
                            <BookOpenCheck class="h-5 w-5" />
                        </div>
                        <div class="space-y-0.5">
                            <h1
                                class="text-2xl leading-tight font-bold tracking-tight text-slate-900"
                            >
                                {{ strand.name }}
                            </h1>
                            <div class="flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-0 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600 uppercase shadow-none"
                                    >{{ subject.name }}</Badge
                                >
                                <span class="font-bold text-slate-300">•</span>
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >{{ grade.name }}</span
                                >
                                <span class="font-bold text-slate-300">•</span>
                                <span
                                    class="rounded border border-slate-200 px-1.5 py-0.5 text-xs font-bold text-slate-900"
                                    >{{ strand.code }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        @click="printSyllabus"
                        variant="outline"
                        class="h-10 rounded-xl border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition-all hover:bg-slate-50"
                    >
                        <Download class="mr-2 h-3.5 w-3.5" /> Export
                    </Button>
                    <Button
                        @click="openSubStrandModal()"
                        class="h-10 rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" /> Add Sub-strand
                    </Button>
                </div>
            </div>

            <!-- Stats Analytics -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Sub-strands
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ strand.sub_strands?.length || 0 }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <Target class="h-5 w-5" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Goals
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{
                                strand.sub_strands?.reduce(
                                    (acc: number, ss: any) =>
                                        acc +
                                        (ss.learning_outcomes?.length || 0),
                                    0,
                                ) || 0
                            }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"
                    >
                        <Sparkles class="h-5 w-5" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Competencies
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{
                                strand.sub_strands?.reduce(
                                    (acc: number, ss: any) =>
                                        acc +
                                        (ss.learning_outcomes?.filter(
                                            (o: any) =>
                                                o.outcome_type === 'Competency',
                                        ).length || 0),
                                    0,
                                ) || 0
                            }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500"
                    >
                        <Lightbulb class="h-5 w-5" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Status
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            LIVE
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                    >
                        <ClipboardList class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Sub-strands Display -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <!-- Toolbar -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-50 bg-white p-6 md:flex-row md:items-center"
                >
                    <div class="relative w-full md:max-w-md">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <Input
                            placeholder="Search sub-strands..."
                            class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 pl-10 text-sm font-medium shadow-none transition-all focus:bg-white"
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <Button
                            variant="outline"
                            class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 px-4 text-xs font-bold text-slate-500 shadow-none transition-all hover:bg-slate-50"
                        >
                            <Filter class="mr-2 h-4 w-4" /> Filters
                        </Button>
                        <div
                            class="flex items-center rounded-xl border border-slate-200/50 bg-slate-100/50 p-1 print:hidden"
                        >
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    'flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold tracking-wider uppercase transition-all',
                                    viewMode === 'list'
                                        ? 'bg-white text-blue-600 shadow-sm'
                                        : 'text-slate-400 hover:text-slate-600',
                                ]"
                            >
                                <ListIcon class="h-3 w-3" /> List
                            </button>
                            <button
                                @click="viewMode = 'grid'"
                                :class="[
                                    'flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold tracking-wider uppercase transition-all',
                                    viewMode === 'grid'
                                        ? 'bg-white text-blue-600 shadow-sm'
                                        : 'text-slate-400 hover:text-slate-600',
                                ]"
                            >
                                <LayoutGrid class="h-3 w-3" /> Grid
                            </button>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div
                    v-if="viewMode === 'list' && strand.sub_strands?.length"
                    class="divide-y divide-slate-50"
                >
                    <div
                        v-for="ss in strand.sub_strands"
                        :key="ss.id"
                        class="group/ss transition-all hover:bg-[#f9fafb]/50"
                    >
                        <!-- Header Row -->
                        <div
                            class="flex flex-col justify-between gap-4 p-6 md:flex-row md:items-center"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-100 bg-[#f9fafb] text-sm font-bold text-slate-900 shadow-sm transition-all group-hover/ss:bg-blue-600 group-hover/ss:text-white"
                                >
                                    {{ ss.display_order }}
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <h3
                                            class="text-base font-bold text-slate-800"
                                        >
                                            {{ ss.name }}
                                        </h3>
                                        <Badge
                                            variant="outline"
                                            class="border-0 bg-blue-50/50 px-1.5 py-0 text-xs font-bold text-blue-400 uppercase"
                                            >{{ ss.code }}</Badge
                                        >
                                    </div>
                                    <p
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        {{
                                            ss.learning_outcomes?.length || 0
                                        }}
                                        Learning Goals
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Button
                                    @click="openOutcomeModal(ss.id)"
                                    variant="outline"
                                    class="h-9 rounded-lg border-blue-100 bg-blue-50/30 px-3 text-xs font-bold text-blue-600 uppercase transition-all hover:bg-blue-600 hover:text-white"
                                >
                                    <Plus class="mr-1.5 h-3.5 w-3.5" /> Add Goal
                                </Button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-lg border border-slate-100 text-slate-300 hover:text-slate-600"
                                        >
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="min-w-[140px] rounded-xl p-2"
                                    >
                                        <DropdownMenuItem
                                            @click="openSubStrandModal(ss)"
                                            class="rounded-lg text-xs font-bold"
                                            >Edit</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            @click="deleteSubStrand(ss.id)"
                                            class="rounded-lg text-xs font-bold text-red-600"
                                            >Delete</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <!-- Outcomes Section -->
                        <div class="flex flex-col gap-2 px-6 pb-6 pl-16">
                            <div
                                v-for="outcome in ss.learning_outcomes"
                                :key="outcome.id"
                                class="group/outcome relative flex items-center justify-between rounded-xl border border-slate-50 bg-white p-4 transition-all hover:border-blue-100"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600 transition-all group-hover/outcome:bg-blue-600 group-hover/outcome:text-white"
                                    >
                                        {{ outcome.display_order }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p
                                            class="text-sm font-semibold text-slate-600 transition-colors group-hover/outcome:text-slate-900"
                                        >
                                            {{ outcome.outcome }}
                                        </p>
                                        <Badge
                                            variant="outline"
                                            class="border-slate-50 bg-slate-50 text-xs font-bold text-slate-400 uppercase"
                                            >{{
                                                outcome.outcome_type ||
                                                'Standard'
                                            }}</Badge
                                        >
                                    </div>
                                </div>

                                <div
                                    class="flex items-center gap-1 opacity-0 transition-all group-hover/outcome:opacity-100"
                                >
                                    <Button
                                        @click="
                                            openOutcomeModal(ss.id, outcome)
                                        "
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 rounded-lg text-slate-400 hover:bg-slate-100"
                                    >
                                        <Edit2 class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        @click="deleteOutcome(outcome.id)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <div
                                v-if="!ss.learning_outcomes?.length"
                                class="cursor-pointer rounded-xl border border-dashed border-slate-100 bg-[#f9fafb]/30 py-4 text-center transition-all hover:border-blue-200"
                                @click="openOutcomeModal(ss.id)"
                            >
                                <p
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    No outcomes defined. Click to add.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div
                    v-if="viewMode === 'grid' && strand.sub_strands?.length"
                    class="grid grid-cols-1 gap-6 bg-[#f9fafb]/30 p-6 lg:grid-cols-2"
                >
                    <div
                        v-for="ss in strand.sub_strands"
                        :key="ss.id"
                        class="flex flex-col rounded-2xl border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="relative overflow-hidden border-b border-slate-50 p-6"
                        >
                            <div
                                class="relative z-10 mb-2 flex items-start justify-between gap-4"
                            >
                                <div class="space-y-1">
                                    <Badge
                                        class="rounded border-0 bg-slate-900 px-2 py-0.5 text-xs font-bold text-white uppercase"
                                        >{{ ss.code }}</Badge
                                    >
                                    <h3
                                        class="text-lg leading-tight font-bold text-slate-900"
                                    >
                                        {{ ss.name }}
                                    </h3>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg text-slate-400"
                                        >
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="min-w-[120px] rounded-xl p-2"
                                    >
                                        <DropdownMenuItem
                                            @click="openSubStrandModal(ss)"
                                            class="text-xs font-bold"
                                            >Edit</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            @click="deleteSubStrand(ss.id)"
                                            class="text-xs font-bold text-red-600"
                                            >Delete</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <p
                                class="line-clamp-1 text-xs font-medium text-slate-400"
                            >
                                {{
                                    ss.description || 'No description provided.'
                                }}
                            </p>
                        </div>

                        <div
                            class="scrollbar-hide max-h-[300px] flex-1 space-y-3 overflow-y-auto p-6"
                        >
                            <div
                                v-for="outcome in ss.learning_outcomes"
                                :key="outcome.id"
                                class="flex gap-3 rounded-xl border border-slate-100/50 bg-slate-50/50 p-4"
                            >
                                <div
                                    class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-500"
                                ></div>
                                <p
                                    class="text-xs leading-snug font-semibold text-slate-600"
                                >
                                    {{ outcome.outcome }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-b-2xl border-t border-slate-50 bg-[#f9fafb] p-4"
                        >
                            <span
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >{{
                                    ss.learning_outcomes?.length || 0
                                }}
                                OUTCOMES</span
                            >
                            <Button
                                @click="openOutcomeModal(ss.id)"
                                variant="ghost"
                                class="rounded-lg p-1.5 text-xs font-bold tracking-wider text-blue-600 uppercase hover:bg-blue-50"
                                >Add Goal</Button
                            >
                        </div>
                    </div>
                </div>

                <div
                    v-if="!strand.sub_strands?.length"
                    class="flex flex-col items-center justify-center gap-6 bg-white py-24 text-center"
                >
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-200"
                    >
                        <GraduationCap class="h-10 w-10" />
                    </div>
                    <div class="space-y-1">
                        <h3
                            class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Expansion Required
                        </h3>
                        <p
                            class="mx-auto max-w-sm text-xs leading-relaxed font-medium text-slate-400"
                        >
                            Add sub-components to start defining the learning
                            pathway.
                        </p>
                    </div>
                    <Button
                        @click="openSubStrandModal()"
                        class="h-11 rounded-xl bg-slate-900 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-sm transition-all hover:bg-black"
                    >
                        <Plus class="mr-2 h-4 w-4" /> Add Sub-strand
                    </Button>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-slate-50 bg-white p-6"
                    v-if="strand.sub_strands?.length"
                >
                    <p
                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                    >
                        Found {{ strand.sub_strands?.length }} Sub-strands
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-100 px-4 text-xs font-bold text-slate-300"
                            >Prev</Button
                        >
                        <Button
                            disabled
                            class="h-9 w-9 rounded-xl bg-slate-900 text-xs font-bold text-white"
                            >1</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-100 px-4 text-xs font-bold text-slate-300"
                            >Next</Button
                        >
                    </div>
                </div>
            </div>

            <!-- Modals -->

            <!-- Sub-Strand Modal -->
            <Dialog v-model:open="showSubStrandModal">
                <DialogContent
                    class="overflow-hidden rounded-2xl border-slate-100 p-0 shadow-xl sm:max-w-[425px]"
                >
                    <DialogHeader class="bg-slate-900 p-6 text-white">
                        <DialogTitle class="text-xl font-bold tracking-tight"
                            >{{
                                editingSubStrand ? 'Edit' : 'Add'
                            }}
                            Sub-strand</DialogTitle
                        >
                        <DialogDescription class="text-xs text-slate-400">
                            Update pathway components for
                            <span class="font-bold text-blue-400"
                                >"{{ strand.name }}"</span
                            >.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        @submit.prevent="submitSubStrand"
                        class="grid gap-4 bg-white px-6 py-6"
                    >
                        <div class="grid gap-1.5">
                            <Label
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Name</Label
                            >
                            <Input
                                v-model="subStrandForm.name"
                                placeholder="Registry name..."
                                class="h-10 rounded-xl border-slate-100 text-sm font-semibold"
                                required
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label
                                    class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Code</Label
                                >
                                <Input
                                    v-model="subStrandForm.code"
                                    placeholder="SS.01"
                                    class="h-10 rounded-xl border-slate-100 text-sm font-bold uppercase"
                                />
                            </div>
                            <div class="grid gap-1.5">
                                <Label
                                    class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Order</Label
                                >
                                <Input
                                    type="number"
                                    v-model="subStrandForm.display_order"
                                    class="h-10 rounded-xl border-slate-100 text-sm font-semibold"
                                    required
                                />
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Description</Label
                            >
                            <Textarea
                                v-model="subStrandForm.description"
                                placeholder="Technical breakdown..."
                                class="min-h-[100px] rounded-xl border-slate-100 p-4 text-xs leading-relaxed font-medium text-slate-600"
                            />
                        </div>
                        <DialogFooter class="px-0 pt-2">
                            <Button
                                type="submit"
                                :disabled="subStrandForm.processing"
                                class="h-11 w-full rounded-xl bg-blue-600 text-xs font-bold text-white uppercase shadow-sm transition-all hover:bg-blue-700"
                            >
                                {{
                                    subStrandForm.processing
                                        ? 'Saving...'
                                        : editingSubStrand
                                          ? 'Update'
                                          : 'Save'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Outcome Modal -->
            <Dialog v-model:open="showOutcomeModal">
                <DialogContent
                    class="overflow-hidden rounded-2xl border-slate-100 p-0 shadow-xl sm:max-w-[425px]"
                >
                    <DialogHeader class="bg-blue-600 p-6 text-white">
                        <DialogTitle class="text-xl font-bold tracking-tight"
                            >{{
                                editingOutcome ? 'Edit' : 'Add'
                            }}
                            Goal</DialogTitle
                        >
                        <DialogDescription class="text-xs text-white/80">
                            Define mastery level for this component.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        @submit.prevent="submitOutcome"
                        class="grid gap-4 bg-white px-6 py-6"
                    >
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label
                                    class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Goal Type</Label
                                >
                                <select
                                    v-model="outcomeForm.outcome_type"
                                    class="h-10 w-full rounded-xl border border-slate-100 bg-[#f9fafb]/50 px-3 text-xs font-bold tracking-wider text-slate-700 uppercase outline-none"
                                >
                                    <option>Standard</option>
                                    <option>Competency</option>
                                    <option>Behavioral</option>
                                    <option>Skill</option>
                                </select>
                            </div>
                            <div class="grid gap-1.5">
                                <Label
                                    class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Sequence</Label
                                >
                                <Input
                                    type="number"
                                    v-model="outcomeForm.display_order"
                                    class="h-10 rounded-xl border-slate-100 text-sm font-semibold"
                                    required
                                />
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Outcome Text</Label
                            >
                            <Textarea
                                v-model="outcomeForm.outcome"
                                placeholder="Describe the expected mastery..."
                                class="min-h-[120px] rounded-xl border-slate-100 p-4 text-sm leading-relaxed font-medium text-slate-600"
                                required
                            />
                        </div>
                        <DialogFooter class="px-0 pt-2">
                            <Button
                                type="submit"
                                :disabled="outcomeForm.processing"
                                class="h-11 w-full rounded-xl bg-slate-900 text-xs font-bold text-white uppercase shadow-sm transition-all hover:bg-black"
                            >
                                {{
                                    outcomeForm.processing
                                        ? 'Saving...'
                                        : editingOutcome
                                          ? 'Update'
                                          : 'Save'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none !important;
    }
}
</style>
