<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Filter,
    Download,
    FileText,
    MoreHorizontal,
    Edit2,
    Trash2,
    Calendar,
    BookOpen,
    Sparkles,
    LayoutGrid,
    ListFilter,
    ArrowLeft,
    Eye,
    Clock,
    CheckCircle2,
    GraduationCap,
    Layers,
    Save,
    ChevronRight,
    CheckSquare,
    Square,
    XCircle,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ref, computed, watch, useTemplateRef } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps({
    scheme: Object,
    strands: Array,
});

const page = usePage();

const breadcrumbs = [
    { title: 'Curriculum', href: '#' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: props.scheme.subject?.name || 'Subject', href: '#' },
    {
        title: props.scheme.title,
        href: `/curriculum/planner/schemes/${props.scheme.id}`,
    },
];

const showToast = ref(false);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

watch(flashSuccess, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
});

watch(flashError, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
});

const entryForm = useForm({
    week_number: '',
    lesson_number: '',
    duration_minutes: 35,
    lesson_date: '',
    strand_id: '',
    sub_strand_id: '',
    topic: '',
    key_vocabulary: '',
    learning_outcomes: '',
    learning_activities: '',
    teacher_activities: '',
    introduction: '',
    lesson_development: '',
    conclusion: '',
    resources: '',
    references: '',
    assessment: '',
    remarks: '',
    reflection: '',
    homework: '',
    core_competencies: [],
    pci: [],
    inquiry_questions: '',
});

const isGeneratingLessons = ref(false);
const generationForm = useForm({
    entry_ids: [],
    start_date: '',
    duration_minutes: 35,
    lessons_per_week: props.scheme.lessons_per_week || 5,
});

const currentGenerationStep = ref(1);
const selectedEntries = computed(() => {
    return props.scheme.entries.filter((e) =>
        generationForm.entry_ids.includes(e.id),
    );
});

const isAddingEntry = ref(false);
const editingEntryId = ref(null);

// Multi-select state
const selectedEntryIds = ref([]);
const isAllSelected = computed(() => {
    if (!props.scheme.entries?.length) return false;
    return props.scheme.entries.every((e) =>
        selectedEntryIds.value.includes(e.id),
    );
});
const hasSelection = computed(() => selectedEntryIds.value.length > 0);

const toggleSelectEntry = (id) => {
    const idx = selectedEntryIds.value.indexOf(id);
    if (idx > -1) {
        selectedEntryIds.value.splice(idx, 1);
    } else {
        selectedEntryIds.value.push(id);
    }
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedEntryIds.value = [];
    } else {
        selectedEntryIds.value = props.scheme.entries.map((e) => e.id);
    }
};

const bulkDeleteForm = useForm({ entry_ids: [] });
const bulkDelete = () => {
    if (
        !confirm(
            `Are you sure you want to delete ${selectedEntryIds.value.length} entries?`,
        )
    )
        return;
    bulkDeleteForm.entry_ids = [...selectedEntryIds.value];
    bulkDeleteForm.post(
        `/curriculum/planner/schemes/${props.scheme.id}/bulk-delete-entries`,
        {
            onSuccess: () => {
                selectedEntryIds.value = [];
            },
        },
    );
};

const submitEntry = () => {
    if (editingEntryId.value) {
        entryForm.put(
            `/curriculum/planner/schemes/${props.scheme.id}/entries/${editingEntryId.value}`,
            {
                onSuccess: () => {
                    isAddingEntry.value = false;
                    editingEntryId.value = null;
                    entryForm.reset();
                },
            },
        );
    } else {
        entryForm.post(
            `/curriculum/planner/schemes/${props.scheme.id}/entries`,
            {
                onSuccess: () => {
                    isAddingEntry.value = false;
                    entryForm.reset();
                },
            },
        );
    }
};

const openAddModal = (entry = null) => {
    if (entry) {
        editingEntryId.value = entry.id;
        entryForm.week_number = entry.week_number;
        entryForm.lesson_number = entry.lesson_number;
        entryForm.duration_minutes = entry.duration_minutes;
        entryForm.lesson_date = entry.lesson_date;
        entryForm.strand_id = entry.strand_id;
        entryForm.sub_strand_id = entry.sub_strand_id;
        entryForm.topic = entry.topic;
        entryForm.key_vocabulary = entry.key_vocabulary;
        entryForm.learning_outcomes = entry.learning_outcomes;
        entryForm.learning_activities = entry.learning_activities;
        entryForm.teacher_activities = entry.teacher_activities;
        entryForm.introduction = entry.introduction;
        entryForm.lesson_development = entry.lesson_development;
        entryForm.conclusion = entry.conclusion;
        entryForm.resources = entry.resources;
        entryForm.references = entry.references;
        entryForm.assessment = entry.assessment;
        entryForm.remarks = entry.remarks;
        entryForm.reflection = entry.reflection;
        entryForm.homework = entry.homework;
        entryForm.core_competencies = entry.core_competencies || [];
        entryForm.pci = entry.pci || [];
        entryForm.inquiry_questions = entry.inquiry_questions;
    } else {
        editingEntryId.value = null;
        entryForm.reset();
        entryForm.duration_minutes = 35;
    }
    isAddingEntry.value = true;
};

const fileInput = useTemplateRef('fileInput');
const importForm = useForm({
    file: null,
});

const triggerImport = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    importForm.file = file;
    importForm.post(`/curriculum/planner/schemes/${props.scheme.id}/import`, {
        onSuccess: () => {
            importForm.reset();
        },
    });
};

const deleteEntry = (entryId) => {
    if (confirm('Are you sure you want to remove this entry?')) {
        router.delete(
            `/curriculum/planner/schemes/${props.scheme.id}/entries/${entryId}`,
        );
    }
};

const openGenerationWizard = (entry = null) => {
    if (entry) {
        generationForm.entry_ids = [entry.id];
        generationForm.start_date = entry.lesson_date || '';
    } else {
        generationForm.entry_ids = [];
    }
    currentGenerationStep.value = 1;
    isGeneratingLessons.value = true;
};

const toggleEntrySelection = (entryId) => {
    const index = generationForm.entry_ids.indexOf(entryId);
    if (index > -1) {
        generationForm.entry_ids.splice(index, 1);
    } else {
        generationForm.entry_ids.push(entryId);
    }
};

const selectAllInWeek = (weekNumber) => {
    const weekEntries = props.scheme.entries
        .filter((e) => e.week_number === weekNumber)
        .map((e) => e.id);
    const allSelected = weekEntries.every((id) =>
        generationForm.entry_ids.includes(id),
    );

    if (allSelected) {
        generationForm.entry_ids = generationForm.entry_ids.filter(
            (id) => !weekEntries.includes(id),
        );
    } else {
        generationForm.entry_ids = [
            ...new Set([...generationForm.entry_ids, ...weekEntries]),
        ];
    }
};

const submitGeneration = () => {
    generationForm.post(
        `/curriculum/planner/schemes/${props.scheme.id}/bulk-generate-lessons`,
        {
            onSuccess: () => {
                isGeneratingLessons.value = false;
                generationForm.reset();
            },
        },
    );
};

const cbcCompetencies = [
    'Communication and Collaboration',
    'Self-efficacy',
    'Critical Thinking and Problem Solving',
    'Creativity and Imagination',
    'Citizenship',
    'Digital Literacy',
    'Learning to Learn',
];

const cbcPCIs = [
    'Environmental Education',
    'Health Education',
    'Life Skills Education',
    'Values Education',
    'Human Rights Education',
    'Gender Issues',
    'Financial Literacy',
];

const getStatusVariant = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'rejected':
            return 'bg-rose-50 text-rose-600 border-rose-100';
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};
</script>

<template>
    <Head :title="'Scheme Details - ' + scheme.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Notifications -->
        <div
            v-if="showToast && (flashSuccess || flashError)"
            class="fixed top-8 right-8 z-[100] animate-in duration-300 slide-in-from-right-4"
        >
            <div
                :class="[
                    'flex items-center gap-3 rounded-2xl border px-6 py-4 shadow-lg backdrop-blur-xl',
                    flashSuccess
                        ? 'border-emerald-200 bg-emerald-50/90 text-emerald-800'
                        : 'border-rose-200 bg-rose-50/90 text-rose-800',
                ]"
            >
                <div
                    :class="[
                        'flex h-8 w-8 items-center justify-center rounded-xl',
                        flashSuccess
                            ? 'bg-emerald-500 text-white'
                            : 'bg-rose-500 text-white',
                    ]"
                >
                    <CheckCircle2 v-if="flashSuccess" class="h-5 w-5" />
                    <Sparkles v-else class="h-5 w-5" />
                </div>
                <div class="flex flex-col">
                    <p class="text-[13px] font-bold tracking-tight">
                        {{ flashSuccess || flashError }}
                    </p>
                    <p class="text-xs font-medium whitespace-nowrap opacity-70">
                        Instructional matrix synced successfully
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mx-auto max-w-[1600px] animate-in space-y-8 p-6 pb-32 duration-1000 fade-in slide-in-from-bottom-4 md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <Link
                            href="/curriculum/planner/schemes"
                            class="text-xs font-bold text-blue-600 hover:underline"
                            >Schemes of Work</Link
                        >
                        <ChevronRight
                            class="h-3 w-3 text-muted-foreground/50"
                        />
                        <span class="text-xs font-bold text-muted-foreground">{{
                            scheme.subject?.name
                        }}</span>
                    </div>
                    <div class="mt-1 flex items-center gap-3">
                        <h1
                            class="text-3xl font-bold tracking-tight text-foreground"
                        >
                            {{ scheme.title }}
                        </h1>
                        <Badge
                            :class="[
                                getStatusVariant(scheme.status),
                                'rounded-lg border px-3 py-1 text-xs font-bold tracking-tight uppercase shadow-sm',
                            ]"
                        >
                            {{ scheme.status }}
                        </Badge>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input
                        type="file"
                        ref="fileInput"
                        class="hidden"
                        accept=".csv"
                        @change="handleFileUpload"
                    />

                    <div
                        class="flex items-center rounded-xl border border-border/40 bg-muted/20 p-1 shadow-sm"
                    >
                        <a href="/curriculum/planner/schemes/template/download">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-9 rounded-lg px-4 text-xs font-bold tracking-[0.15em] text-indigo-600 uppercase hover:bg-background"
                            >
                                <Download class="mr-2 h-3.5 w-3.5" /> Get
                                Template
                            </Button>
                        </a>
                        <a
                            :href="`/curriculum/planner/schemes/${scheme.id}/download`"
                            target="_blank"
                        >
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-9 rounded-lg px-4 text-xs font-bold tracking-[0.15em] text-blue-600 uppercase hover:bg-background"
                            >
                                <FileText class="mr-2 h-3.5 w-3.5" /> Download
                                PDF
                            </Button>
                        </a>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-9 rounded-lg px-4 text-xs font-bold tracking-[0.15em] text-emerald-600 uppercase hover:bg-background"
                            @click="triggerImport"
                        >
                            <ListFilter class="mr-2 h-3.5 w-3.5" /> Bulk Upload
                        </Button>
                    </div>

                    <Button
                        @click="openAddModal()"
                        class="flex h-10 items-center gap-2 rounded-xl bg-blue-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                    >
                        <Plus class="h-4 w-4" /> New Plan
                    </Button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div
                    v-for="(stat, idx) in [
                        {
                            label: 'Scheme Entries',
                            value: scheme.entries?.length || 0,
                            icon: BookOpen,
                            color: 'text-blue-600',
                            bg: 'bg-blue-50',
                        },
                        {
                            label: 'Academic Period',
                            value: (scheme.total_weeks || 0) + ' Weeks',
                            icon: Calendar,
                            color: 'text-indigo-600',
                            bg: 'bg-indigo-50',
                        },
                        {
                            label: 'Weekly Periods',
                            value: (scheme.lessons_per_week || 0) + ' L/Wk',
                            icon: Sparkles,
                            color: 'text-amber-600',
                            bg: 'bg-amber-50',
                        },
                        {
                            label: 'Grade Level',
                            value:
                                scheme.grade_level?.short_name ||
                                scheme.grade_level?.name ||
                                'N/A',
                            icon: GraduationCap,
                            color: 'text-rose-600',
                            bg: 'bg-rose-50',
                        },
                    ]"
                    :key="idx"
                    class="group flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-md"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase opacity-70"
                        >
                            {{ stat.label }}
                        </p>
                        <h3
                            class="text-2xl font-bold tracking-tight text-foreground"
                        >
                            {{ stat.value }}
                        </h3>
                    </div>
                    <div
                        :class="[
                            'flex h-12 w-12 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:scale-110',
                            stat.bg,
                            stat.color,
                        ]"
                    >
                        <component :is="stat.icon" class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Instructional Matrix Table -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all duration-500"
            >
                <div
                    class="flex items-center justify-between border-b border-border/50 bg-muted/5 p-6"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
                        >
                            <Layers class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-foreground">
                                Instructional Matrix
                            </h2>
                            <p
                                class="text-sm font-medium text-muted-foreground opacity-60"
                            >
                                Sequence and Term Roadmap
                            </p>
                        </div>
                    </div>
                    <Button
                        @click="openGenerationWizard()"
                        variant="outline"
                        class="flex h-10 items-center gap-2 rounded-xl border-border bg-background px-5 text-xs font-bold tracking-tight uppercase shadow-sm hover:bg-muted/50"
                    >
                        <Sparkles class="h-4 w-4 text-blue-600" /> Execute
                        Automation
                    </Button>
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[1200px] table-fixed border-collapse text-left"
                    >
                        <thead>
                            <tr
                                class="border-b border-border/50 bg-muted/30 text-muted-foreground"
                            >
                                <th class="w-12 px-4 py-4 text-center">
                                    <button
                                        @click="toggleSelectAll"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded border transition-all"
                                        :class="
                                            isAllSelected
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-border hover:border-blue-400'
                                        "
                                    >
                                        <CheckSquare
                                            v-if="isAllSelected"
                                            class="h-3.5 w-3.5"
                                        />
                                        <Square
                                            v-else
                                            class="h-3.5 w-3.5 opacity-40"
                                        />
                                    </button>
                                </th>
                                <th
                                    class="w-24 px-6 py-4 text-center text-xs font-bold tracking-tight uppercase"
                                >
                                    Timing
                                </th>
                                <th
                                    class="w-64 px-6 py-4 text-xs font-bold tracking-tight uppercase"
                                >
                                    Strand & Sub-Strand
                                </th>
                                <th
                                    class="w-64 px-6 py-4 text-xs font-bold tracking-tight uppercase"
                                >
                                    Instructional Topic
                                </th>
                                <th
                                    class="w-80 px-6 py-4 text-xs font-bold tracking-tight uppercase"
                                >
                                    Outcomes & Methodology
                                </th>
                                <th
                                    class="w-48 px-6 py-4 text-center text-xs font-bold tracking-tight uppercase"
                                >
                                    Resources
                                </th>
                                <th
                                    class="w-40 px-6 py-4 text-right text-xs font-bold tracking-tight uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="entry in scheme.entries"
                                :key="entry.id"
                                class="group cursor-pointer transition-all hover:bg-muted/20"
                                :class="{
                                    'bg-blue-50/50': selectedEntryIds.includes(
                                        entry.id,
                                    ),
                                }"
                                @click="viewDetails(entry)"
                            >
                                <td class="px-4 py-5 text-center" @click.stop>
                                    <button
                                        @click="toggleSelectEntry(entry.id)"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded border transition-all"
                                        :class="
                                            selectedEntryIds.includes(entry.id)
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-border hover:border-blue-400'
                                        "
                                    >
                                        <CheckSquare
                                            v-if="
                                                selectedEntryIds.includes(
                                                    entry.id,
                                                )
                                            "
                                            class="h-3.5 w-3.5"
                                        />
                                        <Square
                                            v-else
                                            class="h-3.5 w-3.5 opacity-40"
                                        />
                                    </button>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div
                                        class="inline-flex flex-col items-center justify-center rounded-xl border border-blue-100/50 bg-blue-50 px-3 py-1.5"
                                    >
                                        <span
                                            class="text-sm leading-none font-bold tracking-tight text-blue-600 uppercase"
                                            >Wk {{ entry.week_number }}</span
                                        >
                                        <span
                                            class="mt-1 text-xs font-bold tracking-tight text-blue-400 uppercase"
                                            >Ls {{ entry.lesson_number }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2">
                                            <Badge
                                                variant="outline"
                                                class="rounded-md border-border/50 bg-muted/50 px-2 py-0 text-xs font-bold tracking-tight uppercase"
                                                >{{ entry.strand?.name }}</Badge
                                            >
                                        </div>
                                        <span
                                            class="ml-0.5 truncate text-sm leading-none font-bold text-foreground/80"
                                            >{{
                                                entry.sub_strand?.name ||
                                                'General Sub-strand'
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span
                                        class="line-clamp-2 text-sm leading-tight font-bold tracking-tight text-foreground uppercase transition-colors group-hover:text-blue-600"
                                        >{{ entry.topic }}</span
                                    >
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-2">
                                        <div
                                            class="flex items-start gap-2 rounded-xl border border-emerald-500/10 bg-emerald-50/30 p-2.5"
                                        >
                                            <div
                                                class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"
                                            ></div>
                                            <p
                                                class="line-clamp-2 text-sm leading-relaxed font-bold tracking-tight text-emerald-800"
                                            >
                                                {{ entry.learning_outcomes }}
                                            </p>
                                        </div>
                                        <div
                                            class="flex items-start gap-2 px-2.5 opacity-60"
                                        >
                                            <div
                                                class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-muted-foreground"
                                            ></div>
                                            <p
                                                class="line-clamp-1 truncate text-xs leading-relaxed font-medium text-muted-foreground"
                                            >
                                                {{ entry.learning_activities }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div
                                        class="mx-auto flex max-w-[140px] flex-col items-center justify-center rounded-xl border border-border/40 bg-muted/30 p-2 transition-all group-hover:bg-background"
                                    >
                                        <Layers
                                            class="mb-1 h-3.5 w-3.5 text-slate-400"
                                        />
                                        <span
                                            class="w-full truncate text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                            >{{
                                                entry.resources || 'Resources'
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-100 transition-all duration-300 group-hover:opacity-100 md:opacity-0"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                            class="h-9 w-9 rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600"
                                        >
                                            <Link
                                                :href="`/curriculum/planner/schemes/${scheme.id}/entries/${entry.id}`"
                                                ><Eye class="h-4.5 w-4.5"
                                            /></Link>
                                        </Button>
                                        <DropdownMenu @click.stop>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl font-bold transition-all hover:bg-muted"
                                                    ><MoreHorizontal
                                                        class="h-4.5 w-4.5"
                                                /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border border-border p-2 shadow-lg"
                                            >
                                                <DropdownMenuItem
                                                    class="group rounded-lg px-3 py-2 text-xs font-bold tracking-wider uppercase"
                                                    @click="openAddModal(entry)"
                                                >
                                                    <Edit2
                                                        class="mr-3 h-4 w-4 opacity-60 group-hover:text-amber-600"
                                                    />
                                                    Edit Content
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    class="rounded-lg bg-blue-50/50 px-3 py-2 text-xs font-bold tracking-wider text-blue-600 uppercase transition-colors hover:bg-blue-600 hover:text-white"
                                                    @click="
                                                        openGenerationWizard(
                                                            entry,
                                                        )
                                                    "
                                                >
                                                    <Sparkles
                                                        class="mr-3 h-4 w-4"
                                                    />
                                                    Automate Plans
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator
                                                    class="my-1 bg-border/5"
                                                />
                                                <DropdownMenuItem
                                                    class="rounded-lg px-3 py-2 text-xs font-bold tracking-wider text-rose-600 uppercase transition-colors hover:bg-rose-50"
                                                    @click="
                                                        deleteEntry(entry.id)
                                                    "
                                                >
                                                    <Trash2
                                                        class="mr-3 h-4 w-4"
                                                    />
                                                    Remove Entry
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!scheme.entries?.length">
                                <td
                                    colspan="7"
                                    class="bg-muted/5 px-6 py-32 text-center text-muted-foreground"
                                >
                                    <div
                                        class="mx-auto flex max-w-sm flex-col items-center gap-4"
                                    >
                                        <div
                                            class="flex h-20 w-20 items-center justify-center rounded-3xl border border-border bg-muted/50 text-muted-foreground/30 shadow-inner"
                                        >
                                            <FileText class="h-10 w-10" />
                                        </div>
                                        <div class="space-y-1">
                                            <h3
                                                class="text-lg font-bold tracking-tight text-foreground"
                                            >
                                                Empty Teaching Matrix
                                            </h3>
                                            <p
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Your scheme of work is currently
                                                empty. Initialize the matrix to
                                                start planning.
                                            </p>
                                        </div>
                                        <Button
                                            @click="openAddModal()"
                                            class="mt-4 h-11 rounded-xl bg-blue-600 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                                        >
                                            <Plus class="mr-2 h-4 w-4" /> Start
                                            Planning
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Floating Bulk Action Bar -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-y-10 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-10 opacity-0"
            >
                <div
                    v-if="hasSelection"
                    class="fixed bottom-8 left-1/2 z-50 -translate-x-1/2"
                >
                    <div
                        class="flex items-center gap-4 rounded-2xl border border-white/10 bg-slate-900 px-6 py-3.5 text-white shadow-lg backdrop-blur-xl"
                    >
                        <div class="flex items-center gap-2">
                            <CheckSquare class="h-4 w-4 text-blue-400" />
                            <span class="text-sm font-bold"
                                >{{ selectedEntryIds.length }} selected</span
                            >
                        </div>
                        <div class="h-5 w-px bg-white/20"></div>
                        <Button
                            size="sm"
                            class="h-9 rounded-xl bg-rose-600 px-5 text-xs font-bold tracking-wider text-white uppercase hover:bg-rose-700"
                            @click="bulkDelete"
                            :disabled="bulkDeleteForm.processing"
                        >
                            <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete Selected
                        </Button>
                        <Button
                            size="sm"
                            variant="ghost"
                            class="h-9 rounded-xl px-3 text-white/70 hover:bg-white/10 hover:text-white"
                            @click="selectedEntryIds = []"
                        >
                            <XCircle class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </Transition>
        </div>
    </AppLayout>

    <!-- Add/Edit Entry Modal -->
    <Dialog v-model:open="isAddingEntry">
        <DialogContent
            class="max-h-[92vh] overflow-y-auto rounded-xl border-border bg-card p-0 shadow-lg sm:max-w-[850px]"
        >
            <DialogHeader
                class="relative overflow-hidden border-b border-border/10 bg-muted/5 p-8 pb-6"
            >
                <div class="relative z-10 flex items-center gap-6">
                    <div
                        class="flex h-14 w-14 animate-in items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20 duration-500 zoom-in"
                    >
                        <BookOpen class="h-7 w-7" />
                    </div>
                    <div>
                        <DialogTitle
                            class="text-2xl font-bold tracking-tight text-foreground"
                        >
                            {{
                                editingEntryId
                                    ? 'Refine Lesson Entry'
                                    : 'Create Lesson Entry'
                            }}
                        </DialogTitle>
                        <p
                            class="mt-0.5 text-sm font-medium text-muted-foreground opacity-70"
                        >
                            Configure instructional methodology and sequence
                            parameters.
                        </p>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="submitEntry" class="space-y-0">
                <Tabs defaultValue="admin" class="w-full">
                    <div class="px-8 pt-6">
                        <TabsList
                            class="grid w-full grid-cols-4 rounded-xl border border-border/50 bg-muted/50 p-1"
                        >
                            <TabsTrigger
                                value="admin"
                                class="rounded-lg text-xs font-bold tracking-tight uppercase data-[state=active]:bg-background data-[state=active]:shadow-sm"
                                >Administrative</TabsTrigger
                            >
                            <TabsTrigger
                                value="curriculum"
                                class="rounded-lg text-xs font-bold tracking-tight uppercase data-[state=active]:bg-background data-[state=active]:shadow-sm"
                                >Curriculum</TabsTrigger
                            >
                            <TabsTrigger
                                value="delivery"
                                class="rounded-lg text-xs font-bold tracking-tight uppercase data-[state=active]:bg-background data-[state=active]:shadow-sm"
                                >Delivery</TabsTrigger
                            >
                            <TabsTrigger
                                value="outcome"
                                class="rounded-lg text-xs font-bold tracking-tight uppercase data-[state=active]:bg-background data-[state=active]:shadow-sm"
                                >Outcomes</TabsTrigger
                            >
                        </TabsList>
                    </div>

                    <div class="space-y-6 p-8 pt-6">
                        <TabsContent
                            value="admin"
                            class="mt-0 animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                        >
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Week Number</Label
                                    >
                                    <Input
                                        v-model="entryForm.week_number"
                                        type="number"
                                        placeholder="e.g. 1"
                                        class="h-11 rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                        required
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Lesson Number</Label
                                    >
                                    <Input
                                        v-model="entryForm.lesson_number"
                                        type="number"
                                        placeholder="e.g. 1"
                                        class="h-11 rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Lesson Date (Optional)</Label
                                    >
                                    <Input
                                        v-model="entryForm.lesson_date"
                                        type="date"
                                        class="h-11 rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Duration (Minutes)</Label
                                    >
                                    <Input
                                        v-model="entryForm.duration_minutes"
                                        type="number"
                                        class="h-11 rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >Topic / Title</Label
                                >
                                <Input
                                    v-model="entryForm.topic"
                                    placeholder="e.g. Introduction to Place Value"
                                    class="h-11 rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    required
                                />
                            </div>
                        </TabsContent>

                        <TabsContent
                            value="curriculum"
                            class="mt-0 animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                        >
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Strand</Label
                                    >
                                    <select
                                        v-model="entryForm.strand_id"
                                        class="h-11 w-full rounded-xl border border-border bg-muted/10 px-4 text-sm font-semibold transition-all outline-none focus:ring-2 focus:ring-blue-500/20"
                                    >
                                        <option value="" disabled>
                                            Select Strand
                                        </option>
                                        <option
                                            v-for="s in strands"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Sub-Strand</Label
                                    >
                                    <Input
                                        v-model="entryForm.sub_strand_id"
                                        placeholder="ID or auto-populated"
                                        class="h-11 rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >Learning Outcomes / Objectives</Label
                                >
                                <Textarea
                                    v-model="entryForm.learning_outcomes"
                                    placeholder="What should students be able to do?"
                                    class="min-h-[120px] rounded-xl border-border bg-muted/10 leading-relaxed font-medium transition-all focus:bg-background"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Key Vocabulary</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.key_vocabulary"
                                        placeholder="Key terms students should learn..."
                                        class="min-h-[80px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Key Inquiry Questions</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.inquiry_questions"
                                        placeholder="How do we...?"
                                        class="min-h-[80px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent
                            value="delivery"
                            class="mt-0 animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                        >
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Introduction</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.introduction"
                                        placeholder="How will you start the lesson?"
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Lesson Development</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.lesson_development"
                                        placeholder="Core instructional steps..."
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Teacher Activities</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.teacher_activities"
                                        placeholder="What will the teacher do?"
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Learner Activities</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.learning_activities"
                                        placeholder="What will students do?"
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent
                            value="outcome"
                            class="mt-0 animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                        >
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Resources / Aids</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.resources"
                                        placeholder="Materials needed..."
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Assessment Methods</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.assessment"
                                        placeholder="How will you measure success?"
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Homework / Reflection</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.homework"
                                        placeholder="Follow-up tasks..."
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >General Remarks</Label
                                    >
                                    <Textarea
                                        v-model="entryForm.remarks"
                                        placeholder="Observations..."
                                        class="min-h-[100px] rounded-xl border-border bg-muted/10 font-medium transition-all focus:bg-background"
                                    />
                                </div>
                            </div>
                        </TabsContent>
                    </div>
                </Tabs>

                <DialogFooter
                    class="flex items-center justify-between border-t border-border/10 bg-muted/5 p-8 sm:justify-between"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isAddingEntry = false"
                        class="h-11 rounded-xl px-8 text-xs font-bold tracking-tight text-muted-foreground uppercase transition-all hover:text-foreground"
                        >Discard Changes</Button
                    >
                    <Button
                        type="submit"
                        :disabled="entryForm.processing"
                        class="flex h-11 items-center gap-2 rounded-xl bg-blue-600 px-10 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                    >
                        <Save v-if="!entryForm.processing" class="h-4 w-4" />
                        {{
                            entryForm.processing
                                ? 'Syncing...'
                                : editingEntryId
                                  ? 'Update Entry'
                                  : 'Append Entry'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Generate Lessons Wizard -->
    <Dialog v-model:open="isGeneratingLessons">
        <DialogContent
            class="overflow-hidden rounded-2xl border-border bg-card p-0 shadow-lg sm:max-w-[750px]"
        >
            <div
                class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-700 p-10 text-white"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"
                ></div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-xl backdrop-blur-md"
                        >
                            <Sparkles
                                class="h-8 w-8 animate-pulse text-blue-200"
                            />
                        </div>
                        <DialogTitle
                            class="text-2xl font-bold tracking-tight text-white"
                            >Smart Plan Generator</DialogTitle
                        >
                        <DialogDescription
                            class="mt-1 font-medium text-blue-100/80"
                        >
                            Step {{ currentGenerationStep }} of 3:
                            {{
                                currentGenerationStep === 1
                                    ? 'Target Scope'
                                    : currentGenerationStep === 2
                                      ? 'Temporal Config'
                                      : 'Validation'
                            }}
                        </DialogDescription>
                    </div>
                    <div
                        class="mt-2 flex gap-1.5 rounded-full border border-white/5 bg-black/10 p-2 backdrop-blur-sm"
                    >
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="h-2 w-8 rounded-full transition-all duration-500"
                            :class="
                                i <= currentGenerationStep
                                    ? 'bg-white shadow-[0_0_12px_rgba(255,255,255,0.5)]'
                                    : 'bg-white/10'
                            "
                        ></div>
                    </div>
                </div>
            </div>

            <div
                v-if="currentGenerationStep === 1"
                class="animate-in space-y-8 p-10 duration-500 fade-in slide-in-from-right-4"
            >
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3
                            class="ml-1 text-sm font-bold tracking-tight text-foreground uppercase"
                        >
                            Target Matrix Weeks
                        </h3>
                        <Badge
                            variant="secondary"
                            class="rounded-lg border-none bg-blue-600 text-white"
                            >{{
                                generationForm.entry_ids.length
                            }}
                            Selected</Badge
                        >
                    </div>
                    <div
                        class="custom-scrollbar grid max-h-[300px] grid-cols-4 gap-4 overflow-y-auto pr-3"
                    >
                        <template
                            v-for="week in [
                                ...new Set(
                                    scheme.entries.map((e) => e.week_number),
                                ),
                            ]"
                            :key="week"
                        >
                            <div
                                @click="selectAllInWeek(week)"
                                class="group flex cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 p-5 transition-all active:scale-95"
                                :class="
                                    scheme.entries
                                        .filter((e) => e.week_number === week)
                                        .every((e) =>
                                            generationForm.entry_ids.includes(
                                                e.id,
                                            ),
                                        )
                                        ? 'border-blue-600 bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                        : 'border-border/50 bg-muted/30 hover:border-blue-400 hover:bg-card'
                                "
                            >
                                <p
                                    class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    :class="
                                        scheme.entries
                                            .filter(
                                                (e) => e.week_number === week,
                                            )
                                            .every((e) =>
                                                generationForm.entry_ids.includes(
                                                    e.id,
                                                ),
                                            )
                                            ? 'text-blue-100'
                                            : 'text-muted-foreground/60 group-hover:text-blue-500'
                                    "
                                >
                                    Week
                                </p>
                                <p class="text-3xl font-bold">{{ week }}</p>
                            </div>
                        </template>
                    </div>

                    <div
                        class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm"
                        >
                            <BookOpen class="h-5 w-5" />
                        </div>
                        <p
                            class="text-xs leading-relaxed font-semibold text-blue-800"
                        >
                            We will transform
                            <span class="text-sm font-bold text-blue-600">{{
                                generationForm.entry_ids.length
                            }}</span>
                            curriculum matrix entries into fully operational
                            lesson plans.
                        </p>
                    </div>
                </div>

                <DialogFooter
                    class="flex w-full justify-between border-t border-border/50 px-0 pt-6 sm:justify-between"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isGeneratingLessons = false"
                        class="h-12 rounded-xl px-8 font-bold text-muted-foreground hover:bg-muted"
                        >Discard</Button
                    >
                    <Button
                        @click="currentGenerationStep = 2"
                        :disabled="!generationForm.entry_ids.length"
                        class="h-12 rounded-xl bg-blue-600 px-10 font-bold text-white shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                    >
                        Configure Rotation <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>
                </DialogFooter>
            </div>

            <div
                v-if="currentGenerationStep === 2"
                class="animate-in space-y-10 p-10 duration-500 fade-in slide-in-from-right-4"
            >
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <Label
                            class="ml-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                            >Cycle Commencement</Label
                        >
                        <Input
                            type="date"
                            v-model="generationForm.start_date"
                            class="h-14 rounded-xl border-border bg-muted/10 text-base font-bold transition-all focus:ring-4 focus:ring-blue-500/10"
                        />
                        <p class="ml-1 text-xs text-muted-foreground">
                            The date lesson #1 starts
                        </p>
                    </div>
                    <div class="space-y-3">
                        <Label
                            class="ml-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                            >Default Duration</Label
                        >
                        <div class="relative">
                            <Input
                                type="number"
                                v-model="generationForm.duration_minutes"
                                class="h-14 rounded-xl border-border bg-muted/10 pl-12 text-base font-bold transition-all focus:ring-4 focus:ring-blue-500/10"
                            />
                            <Calendar
                                class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-muted-foreground"
                            />
                            <span
                                class="absolute top-1/2 right-4 -translate-y-1/2 text-xs font-bold text-muted-foreground"
                                >MINS</span
                            >
                        </div>
                        <p class="ml-1 text-xs text-muted-foreground">
                            Applied if unspecified in matrix
                        </p>
                    </div>
                </div>

                <div class="space-y-6 pt-4">
                    <Label
                        class="block text-center text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >Instructional Density (Lessons / Week)</Label
                    >
                    <div
                        class="flex items-center justify-center gap-10 rounded-3xl border border-border/50 bg-muted/10 py-4 shadow-inner"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            class="h-14 w-14 rounded-2xl border-2 bg-card text-2xl font-bold shadow-sm hover:bg-muted active:scale-90"
                            @click="
                                generationForm.lessons_per_week > 1 &&
                                generationForm.lessons_per_week--
                            "
                        >
                            -
                        </Button>
                        <div class="min-w-[100px] text-center">
                            <span
                                class="text-6xl font-bold tracking-tighter text-foreground"
                                >{{ generationForm.lessons_per_week }}</span
                            >
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-blue-600 uppercase"
                            >
                                PERIODS / WK
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-14 w-14 rounded-2xl border-2 bg-card text-2xl font-bold shadow-sm hover:bg-muted active:scale-90"
                            @click="
                                generationForm.lessons_per_week < 10 &&
                                generationForm.lessons_per_week++
                            "
                        >
                            +
                        </Button>
                    </div>
                </div>

                <div
                    class="flex items-center gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm"
                    >
                        <Calendar class="h-5 w-5" />
                    </div>
                    <p
                        class="text-xs leading-relaxed font-semibold text-indigo-700"
                    >
                        Smart Scheduler will automatically handle weekends and
                        distribution based on your frequency setting.
                    </p>
                </div>

                <DialogFooter
                    class="flex w-full justify-between border-t border-border/50 px-0 pt-6 sm:justify-between"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="currentGenerationStep = 1"
                        class="h-12 rounded-xl px-8 font-bold text-muted-foreground hover:bg-muted"
                        >Back to Scope</Button
                    >
                    <Button
                        @click="currentGenerationStep = 3"
                        class="h-12 rounded-xl border border-slate-800 bg-slate-900 px-10 font-bold text-white shadow-lg transition-all hover:bg-black active:scale-95"
                    >
                        Run Diagnostics <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>
                </DialogFooter>
            </div>

            <div
                v-if="currentGenerationStep === 3"
                class="animate-in space-y-8 p-10 duration-500 fade-in slide-in-from-right-4"
            >
                <div class="space-y-6">
                    <h3
                        class="ml-1 text-sm font-bold tracking-tight text-foreground uppercase"
                    >
                        Generation Manifest
                    </h3>
                    <div
                        class="grid gap-3 rounded-xl border border-border/50 bg-muted/10 p-8 shadow-inner"
                    >
                        <div
                            class="flex items-center justify-between border-b border-border/30 py-3"
                        >
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >Target Dataset</span
                            >
                            <span class="text-base font-bold text-foreground"
                                >{{
                                    generationForm.entry_ids.length
                                }}
                                Instructions</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-border/30 py-3"
                        >
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >Timeline Start</span
                            >
                            <span class="text-base font-bold text-blue-600">{{
                                generationForm.start_date || 'TBD'
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >Protocol Sync</span
                            >
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2 w-2 animate-ping rounded-full bg-emerald-500"
                                ></div>
                                <Badge
                                    variant="secondary"
                                    class="rounded-lg border-none bg-emerald-500 py-1 text-xs font-bold text-white"
                                    >CBC ACTIVE</Badge
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm"
                        >
                            <AlertCircle class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-xs font-bold tracking-tight text-amber-800 uppercase"
                            >
                                Final Authorization Required
                            </p>
                            <p
                                class="text-sm leading-relaxed font-semibold text-amber-700/80"
                            >
                                Proceeding will automate the creation of draft
                                daily lesson plans. This operation is
                                irreversible but entries can be edited
                                individually.
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="flex w-full justify-between border-t border-border/50 px-0 pt-6 sm:justify-between"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="currentGenerationStep = 2"
                        class="h-12 rounded-xl px-8 font-bold text-muted-foreground hover:bg-muted"
                        >Recalibrate</Button
                    >
                    <Button
                        @click="submitGeneration"
                        :disabled="generationForm.processing"
                        class="group h-12 rounded-xl bg-blue-600 px-12 font-bold text-white shadow-xl shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95"
                    >
                        <span class="flex items-center gap-2">
                            {{
                                generationForm.processing
                                    ? 'ENGINEERING PLANS...'
                                    : 'BUILD ALL PLANS'
                            }}
                            <Sparkles
                                v-if="!generationForm.processing"
                                class="h-4 w-4 transition-transform group-hover:rotate-12"
                            />
                        </span>
                    </Button>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>
