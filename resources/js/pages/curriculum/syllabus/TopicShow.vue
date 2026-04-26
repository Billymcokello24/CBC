<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    GraduationCap,
    ChevronRight,
    Plus,
    CheckCircle2,
    MoreVertical,
    ArrowLeft,
    Edit2,
    Trash2,
    Download,
    Search,
    BookOpen,
    Target,
    Sparkles,
    Lightbulb,
    ClipboardList,
    LayoutGrid,
    List as ListIcon,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
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
    { title: 'Subjects', href: '/curriculum/syllabus' },
    {
        title: props.subject.name,
        href: `/curriculum/syllabus/${props.subject.id}/${props.grade.id}`,
    },
    { title: props.strand.name, href: '#' },
];

const route = (window as any).route;
const viewMode = ref<'list' | 'grid'>('list');

// Sub-strand modal
const showSubStrandModal = ref(false);
const editingSubStrand = ref<any>(null);

const subStrandForm = useForm({
    name: '',
    code: '',
    description: '',
    strand_id: props.strand.id,
    display_order: 1,
});

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
        subStrandForm.display_order = (props.strand.sub_strands?.length || 0) + 1;
    }
    showSubStrandModal.value = true;
};

const submitSubStrand = () => {
    if (editingSubStrand.value) {
        subStrandForm.put(route('curriculum.syllabus.sub-topics.update', editingSubStrand.value.id), {
            onSuccess: () => {
                showSubStrandModal.value = false;
                editingSubStrand.value = null;
            },
        });
    } else {
        subStrandForm.post(route('curriculum.syllabus.sub-topics.store'), {
            onSuccess: () => { showSubStrandModal.value = false; },
        });
    }
};

const deleteSubStrand = (id: number) => {
    if (window.confirm('Delete this sub-topic and all its learning goals? This cannot be undone.')) {
        useForm({}).delete(route('curriculum.syllabus.sub-topics.destroy', id));
    }
};

// Outcome modal
const showOutcomeModal = ref(false);
const editingOutcome = ref<any>(null);

const outcomeForm = useForm({
    outcome: '',
    sub_strand_id: null as number | null,
    outcome_type: 'Standard',
    display_order: 1,
});

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
        const ss = props.strand.sub_strands.find((s: any) => s.id === subStrandId);
        outcomeForm.display_order = (ss?.learning_outcomes?.length || 0) + 1;
    }
    showOutcomeModal.value = true;
};

const submitOutcome = () => {
    if (editingOutcome.value) {
        outcomeForm.put(route('curriculum.syllabus.outcomes.update', editingOutcome.value.id), {
            onSuccess: () => {
                showOutcomeModal.value = false;
                editingOutcome.value = null;
            },
        });
    } else {
        outcomeForm.post(route('curriculum.syllabus.outcomes.store'), {
            onSuccess: () => { showOutcomeModal.value = false; },
        });
    }
};

const deleteOutcome = (id: number) => {
    if (window.confirm('Remove this learning goal?')) {
        useForm({}).delete(route('curriculum.syllabus.outcomes.destroy', id));
    }
};

const totalGoals = computed(() =>
    props.strand.sub_strands?.reduce(
        (acc: number, ss: any) => acc + (ss.learning_outcomes?.length || 0), 0,
    ) || 0,
);

const totalCompetencies = computed(() =>
    props.strand.sub_strands?.reduce(
        (acc: number, ss: any) =>
            acc + (ss.learning_outcomes?.filter((o: any) => o.outcome_type === 'Competency').length || 0),
        0,
    ) || 0,
);

const printSyllabus = () => { window.print(); };
</script>

<template>
    <Head :title="`${strand.name} — ${subject.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">

            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <Link
                        :href="`/curriculum/syllabus/${subject.id}/${grade.id}`"
                        class="group mb-1 inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground transition-all hover:text-primary"
                    >
                        <ArrowLeft class="h-3 w-3 transition-transform group-hover:-translate-x-1" />
                        Back to Topics
                    </Link>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary border border-border">
                            <BookOpenCheck class="h-5 w-5" />
                        </div>
                        <div class="space-y-0.5">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                {{ strand.name }}
                            </h1>
                            <div class="flex items-center gap-2">
                                <Badge variant="outline" class="border-primary/10 bg-primary/5 px-2 py-0.5 text-[10px] font-semibold text-primary uppercase">
                                    {{ subject.name }}
                                </Badge>
                                <span class="text-muted-foreground/30">•</span>
                                <span class="text-xs font-semibold text-muted-foreground">{{ grade.name }}</span>
                                <span v-if="strand.code" class="text-muted-foreground/30">•</span>
                                <Badge v-if="strand.code" variant="outline" class="border-border px-1.5 py-0.5 text-[10px] font-bold uppercase">
                                    {{ strand.code }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        @click="printSyllabus"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted print:hidden"
                    >
                        <Download class="mr-2 h-4 w-4 text-muted-foreground" />
                        Export
                    </Button>
                    <Button
                        @click="openSubStrandModal()"
                        class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add Sub-topic
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, idx) in [
                        { label: 'Sub-topics', val: strand.sub_strands?.length || 0, sub: 'Under this topic', icon: Target },
                        { label: 'Learning Goals', val: totalGoals, sub: 'Total outcomes', icon: Sparkles },
                        { label: 'Skills', val: totalCompetencies, sub: 'Competencies', icon: Lightbulb },
                        { label: 'Status', val: strand.sub_strands?.length > 0 ? 'Active' : 'Empty', sub: strand.name, icon: ClipboardList },
                    ]"
                    :key="idx"
                    class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md"
                >
                    <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight">{{ stat.val }}</h3>
                        <span class="text-[10px] font-semibold text-primary">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Sub-topics Content -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <!-- Toolbar -->
                <div class="flex flex-col justify-between gap-4 border-b border-border/50 bg-muted/5 p-4 md:flex-row md:items-center">
                    <div class="relative w-full md:max-w-sm">
                        <Search class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                        <Input
                            placeholder="Search sub-topics..."
                            class="h-10 rounded-lg border-border bg-background pl-10 text-sm focus:bg-background"
                        />
                    </div>
                    <div class="flex items-center gap-2 print:hidden">
                        <div class="flex items-center rounded-lg border border-border bg-muted/20 p-0.5">
                            <button
                                @click="viewMode = 'list'"
                                :class="['flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all', viewMode === 'list' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground']"
                            >
                                <ListIcon class="h-3.5 w-3.5" /> List
                            </button>
                            <button
                                @click="viewMode = 'grid'"
                                :class="['flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all', viewMode === 'grid' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground']"
                            >
                                <LayoutGrid class="h-3.5 w-3.5" /> Grid
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="!strand.sub_strands?.length"
                    class="flex flex-col items-center justify-center gap-4 py-24 text-center"
                >
                    <GraduationCap class="h-12 w-12 text-muted-foreground/20" />
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-foreground">No sub-topics yet</h3>
                        <p class="text-xs text-muted-foreground">Add sub-topics to define the curriculum pathway for {{ strand.name }}</p>
                    </div>
                    <Button
                        @click="openSubStrandModal()"
                        class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white"
                    >
                        <Plus class="mr-2 h-4 w-4" /> Add First Sub-topic
                    </Button>
                </div>

                <!-- List View -->
                <div v-if="viewMode === 'list' && strand.sub_strands?.length" class="divide-y divide-border/50">
                    <div
                        v-for="ss in strand.sub_strands"
                        :key="ss.id"
                        class="group/ss"
                    >
                        <!-- Sub-topic Header -->
                        <div class="flex flex-col justify-between gap-4 p-5 transition-all hover:bg-muted/10 md:flex-row md:items-center">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-sm font-bold text-foreground transition-all group-hover/ss:border-primary group-hover/ss:bg-primary group-hover/ss:text-white">
                                    {{ ss.display_order }}
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold text-foreground">{{ ss.name }}</h3>
                                        <Badge v-if="ss.code" variant="outline" class="border-border px-1.5 py-0 text-[10px] font-semibold uppercase">{{ ss.code }}</Badge>
                                    </div>
                                    <p class="text-xs font-medium text-muted-foreground">
                                        {{ ss.learning_outcomes?.length || 0 }} learning goal{{ ss.learning_outcomes?.length !== 1 ? 's' : '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    @click="openOutcomeModal(ss.id)"
                                    variant="outline"
                                    class="h-8 rounded-lg border-border px-3 text-xs font-semibold hover:bg-primary hover:text-white hover:border-primary transition-all"
                                >
                                    <Plus class="mr-1 h-3 w-3" /> Add Goal
                                </Button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-muted">
                                            <MoreVertical class="h-4 w-4 text-muted-foreground" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40 rounded-xl border-border bg-card p-2 shadow-lg">
                                        <DropdownMenuItem @click="openSubStrandModal(ss)" class="rounded-lg px-3 py-2 text-xs font-medium cursor-pointer">
                                            <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="deleteSubStrand(ss.id)" class="rounded-lg px-3 py-2 text-xs font-medium text-rose-600 focus:bg-rose-50 cursor-pointer">
                                            <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <!-- Description if available -->
                        <p v-if="ss.description" class="px-20 pb-2 text-xs text-muted-foreground">{{ ss.description }}</p>

                        <!-- Learning Goals -->
                        <div class="flex flex-col gap-1.5 px-5 pb-5 pl-[72px]">
                            <div
                                v-for="outcome in ss.learning_outcomes"
                                :key="outcome.id"
                                class="group/outcome flex items-center justify-between rounded-lg border border-border/50 bg-muted/10 p-3 transition-all hover:border-border hover:bg-card"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary">
                                        {{ outcome.display_order }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-semibold leading-snug text-foreground">{{ outcome.outcome }}</p>
                                        <Badge class="border-0 bg-muted px-1.5 py-0 text-[10px] font-semibold text-muted-foreground">
                                            {{ outcome.outcome_type || 'Standard' }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 transition-all group-hover/outcome:opacity-100">
                                    <Button @click="openOutcomeModal(ss.id, outcome)" variant="ghost" size="icon" class="h-7 w-7 rounded-lg hover:bg-muted">
                                        <Edit2 class="h-3 w-3" />
                                    </Button>
                                    <Button @click="deleteOutcome(outcome.id)" variant="ghost" size="icon" class="h-7 w-7 rounded-lg hover:bg-rose-50 hover:text-rose-600">
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                            <!-- Empty goals prompt -->
                            <div
                                v-if="!ss.learning_outcomes?.length"
                                class="cursor-pointer rounded-lg border border-dashed border-border bg-muted/5 py-3 text-center transition-all hover:border-primary/30"
                                @click="openOutcomeModal(ss.id)"
                            >
                                <p class="text-[10px] font-semibold text-muted-foreground uppercase">No goals yet — click to add</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div v-if="viewMode === 'grid' && strand.sub_strands?.length" class="grid grid-cols-1 gap-6 bg-muted/5 p-6 lg:grid-cols-2">
                    <div
                        v-for="ss in strand.sub_strands"
                        :key="ss.id"
                        class="flex flex-col rounded-xl border border-border bg-card shadow-sm overflow-hidden"
                    >
                        <div class="flex items-start justify-between gap-4 border-b border-border/50 p-5">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <Badge v-if="ss.code" class="border-0 bg-foreground/5 px-1.5 py-0 text-[10px] font-bold uppercase">{{ ss.code }}</Badge>
                                </div>
                                <h3 class="text-sm font-bold text-foreground">{{ ss.name }}</h3>
                                <p v-if="ss.description" class="text-xs text-muted-foreground line-clamp-1">{{ ss.description }}</p>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-7 w-7 rounded-lg hover:bg-muted shrink-0">
                                        <MoreVertical class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40 rounded-xl border-border bg-card p-2 shadow-lg">
                                    <DropdownMenuItem @click="openSubStrandModal(ss)" class="rounded-lg px-3 py-2 text-xs font-medium cursor-pointer">
                                        <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="deleteSubStrand(ss.id)" class="rounded-lg px-3 py-2 text-xs font-medium text-rose-600 focus:bg-rose-50 cursor-pointer">
                                        <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <!-- Goals list -->
                        <div class="max-h-[280px] flex-1 space-y-2 overflow-y-auto p-4">
                            <div
                                v-for="outcome in ss.learning_outcomes"
                                :key="outcome.id"
                                class="flex items-start gap-2 rounded-lg border border-border/50 bg-muted/10 p-3"
                            >
                                <div class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></div>
                                <p class="text-xs font-semibold leading-snug text-foreground">{{ outcome.outcome }}</p>
                            </div>
                            <div
                                v-if="!ss.learning_outcomes?.length"
                                class="cursor-pointer rounded-lg border border-dashed border-border py-4 text-center hover:border-primary/30"
                                @click="openOutcomeModal(ss.id)"
                            >
                                <p class="text-[10px] font-semibold text-muted-foreground uppercase">No goals yet</p>
                            </div>
                        </div>
                        <!-- Card footer -->
                        <div class="flex items-center justify-between border-t border-border/50 bg-muted/5 px-4 py-3">
                            <span class="text-[10px] font-semibold text-muted-foreground uppercase">{{ ss.learning_outcomes?.length || 0 }} Goals</span>
                            <Button @click="openOutcomeModal(ss.id)" variant="ghost" class="h-7 rounded-lg px-2 text-[10px] font-bold text-primary hover:bg-primary/10 uppercase">
                                Add Goal
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="strand.sub_strands?.length" class="flex h-14 items-center justify-between border-t border-border/50 bg-muted/5 px-6">
                    <p class="text-xs font-medium text-muted-foreground">
                        {{ strand.sub_strands.length }} sub-topic{{ strand.sub_strands.length !== 1 ? 's' : '' }} in {{ strand.name }}
                    </p>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- Add/Edit Sub-topic Modal -->
            <!-- ============================================================ -->
            <Dialog v-model:open="showSubStrandModal">
                <DialogContent class="sm:max-w-[440px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-border/50 bg-muted/5 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground">
                                {{ editingSubStrand ? 'Edit Sub-topic' : 'Add Sub-topic' }}
                            </h3>
                            <p class="text-xs text-muted-foreground font-medium">Under "{{ strand.name }}"</p>
                        </div>
                    </div>
                    <form @submit.prevent="submitSubStrand" class="p-6 space-y-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Sub-topic Name *</Label>
                            <Input
                                v-model="subStrandForm.name"
                                placeholder="e.g. Counting and Place Value"
                                class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background"
                                required
                            />
                            <p v-if="subStrandForm.errors.name" class="text-xs text-rose-500">{{ subStrandForm.errors.name }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Code</Label>
                                <Input
                                    v-model="subStrandForm.code"
                                    placeholder="SS.01"
                                    class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background uppercase"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Order</Label>
                                <Input
                                    type="number"
                                    v-model="subStrandForm.display_order"
                                    class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background"
                                    required
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Description (optional)</Label>
                            <textarea
                                v-model="subStrandForm.description"
                                placeholder="Brief description..."
                                rows="3"
                                class="w-full rounded-lg border border-border bg-muted/10 px-3 py-2 text-sm outline-none resize-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                            ></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showSubStrandModal = false" class="h-10 rounded-lg px-4 text-xs font-semibold">
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="subStrandForm.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all"
                            >
                                {{ subStrandForm.processing ? 'Saving...' : editingSubStrand ? 'Save Changes' : 'Add Sub-topic' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- ============================================================ -->
            <!-- Add/Edit Learning Goal Modal -->
            <!-- ============================================================ -->
            <Dialog v-model:open="showOutcomeModal">
                <DialogContent class="sm:max-w-[440px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-border/50 bg-muted/5 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Sparkles class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground">
                                {{ editingOutcome ? 'Edit Learning Goal' : 'Add Learning Goal' }}
                            </h3>
                            <p class="text-xs text-muted-foreground font-medium">What should students learn or achieve?</p>
                        </div>
                    </div>
                    <form @submit.prevent="submitOutcome" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Goal Type</Label>
                                <div class="relative">
                                    <select
                                        v-model="outcomeForm.outcome_type"
                                        class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-3 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                                    >
                                        <option>Standard</option>
                                        <option>Competency</option>
                                        <option>Behavioral</option>
                                        <option>Skill</option>
                                    </select>
                                    <ChevronRight class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/40" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Order</Label>
                                <Input
                                    type="number"
                                    v-model="outcomeForm.display_order"
                                    class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background"
                                    required
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Learning Goal Description *</Label>
                            <textarea
                                v-model="outcomeForm.outcome"
                                placeholder="What will students know or be able to do?"
                                rows="4"
                                class="w-full rounded-lg border border-border bg-muted/10 px-3 py-2 text-sm outline-none resize-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                                required
                            ></textarea>
                            <p v-if="outcomeForm.errors.outcome" class="text-xs text-rose-500">{{ outcomeForm.errors.outcome }}</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showOutcomeModal = false" class="h-10 rounded-lg px-4 text-xs font-semibold">
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="outcomeForm.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all"
                            >
                                {{ outcomeForm.processing ? 'Saving...' : editingOutcome ? 'Save Changes' : 'Add Goal' }}
                            </Button>
                        </div>
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
