<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
    MoreHorizontal,
    Users,
    TrendingUp,
    BookOpen,
    Search,
    Target,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Switch } from '@/components/ui/switch';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subject: any;
    grade: any;
    strands: any[];
    teachers: any[];
    classes: any[];
    allocations: any[];
    academicYear: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/syllabus' },
    { title: props.subject.name, href: '#' },
];

const route = (window as any).route;

// Strand (Topic) modals
const showStrandModal = ref(false);
const editingStrand = ref<any>(null);

const strandForm = useForm({
    name: '',
    code: '',
    display_order: 1,
    subject_id: props.subject.id,
    grade_level_id: props.grade.id,
});

const openStrandModal = (strand: any = null) => {
    editingStrand.value = strand;
    if (strand) {
        strandForm.name = strand.name;
        strandForm.code = strand.code;
        strandForm.display_order = strand.display_order;
    } else {
        strandForm.reset();
        strandForm.display_order = props.strands.length + 1;
        strandForm.subject_id = props.subject.id;
        strandForm.grade_level_id = props.grade.id;
    }
    showStrandModal.value = true;
};

const submitStrand = () => {
    if (editingStrand.value) {
        strandForm.put(route('curriculum.syllabus.topics.update', editingStrand.value.id), {
            onSuccess: () => {
                showStrandModal.value = false;
                editingStrand.value = null;
            },
        });
    } else {
        strandForm.post(route('curriculum.syllabus.topics.store'), {
            onSuccess: () => { showStrandModal.value = false; },
        });
    }
};

const deleteStrand = (id: number) => {
    if (window.confirm('Delete this topic and all its sub-topics? This cannot be undone.')) {
        useForm({}).delete(route('curriculum.syllabus.topics.destroy', id));
    }
};

// Teacher Assignment
const showAssignmentModal = ref(false);
const teacherSearch = ref('');
const assignmentForm = useForm({
    teacher_id: '',
    subject_id: props.subject.id,
    class_id: '',
    academic_year_id: props.academicYear?.id || '',
    is_primary_teacher: true,
    is_active: true,
});

const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    const s = teacherSearch.value.toLowerCase();
    return props.teachers.filter(
        (t) => t.name.toLowerCase().includes(s) || t.staff_number.toLowerCase().includes(s),
    );
});

const openAssignmentModal = (classId: string = '') => {
    assignmentForm.teacher_id = '';
    assignmentForm.class_id = classId;
    assignmentForm.is_primary_teacher = true;
    teacherSearch.value = '';
    assignmentForm.clearErrors();
    showAssignmentModal.value = true;
};

const submitAssignment = () => {
    assignmentForm.post(route('classes.allocations.store'), {
        onSuccess: () => { showAssignmentModal.value = false; },
    });
};

const removeAssignment = (id: number) => {
    if (window.confirm('Remove this teacher assignment?')) {
        useForm({}).delete(route('classes.allocations.destroy', id));
    }
};

const getTeacherForClass = (classId: number) => {
    return props.allocations.filter((a) => a.class_id === classId);
};

// Compute stats
const totalSubTopics = computed(() =>
    props.strands.reduce((acc, s) => acc + (s.sub_strands?.length || 0), 0),
);
const totalGoals = computed(() =>
    props.strands.reduce(
        (acc, s) =>
            acc + (s.sub_strands?.reduce((a2: number, ss: any) => a2 + (ss.learning_outcomes?.length || 0), 0) || 0),
        0,
    ),
);

const printSyllabus = () => { window.print(); };
</script>

<template>
    <Head :title="`${subject.name} — ${grade.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">

            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <Link
                        href="/curriculum/syllabus"
                        class="group mb-1 inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground transition-all hover:text-primary"
                    >
                        <ArrowLeft class="h-3 w-3 transition-transform group-hover:-translate-x-1" />
                        Back to Subjects
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        {{ subject.name }} — {{ grade.name }}
                    </h1>
                    <p class="text-xs text-muted-foreground">
                        Topics and sub-topics for {{ subject.name }} in {{ grade.name }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        @click="printSyllabus"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <Download class="mr-2 h-4 w-4 text-muted-foreground" />
                        Export
                    </Button>
                    <Button
                        @click="openStrandModal()"
                        class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add Topic
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, idx) in [
                        { label: 'Total Topics', val: strands.length, sub: 'Main topics', icon: BookOpen },
                        { label: 'Sub-topics', val: totalSubTopics, sub: 'Under all topics', icon: ChevronRight },
                        { label: 'Learning Goals', val: totalGoals, sub: 'Expected outcomes', icon: Target },
                        { label: 'Status', val: strands.length > 0 ? 'Active' : 'Empty', sub: grade.name, icon: TrendingUp },
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

            <!-- Tabs -->
            <Tabs defaultValue="topics" class="w-full">
                <TabsList class="mb-6 h-11 rounded-xl border border-border bg-muted/20 p-1">
                    <TabsTrigger value="topics" class="rounded-lg text-xs font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm">
                        <BookOpenCheck class="mr-2 h-4 w-4" /> Topics
                    </TabsTrigger>
                    <TabsTrigger value="teachers" class="rounded-lg text-xs font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm">
                        <Users class="mr-2 h-4 w-4" /> Teacher Assignments
                    </TabsTrigger>
                </TabsList>

                <!-- Topics Tab -->
                <TabsContent value="topics">
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <th class="px-6 py-4">Topic</th>
                                        <th class="px-6 py-4 text-center">Sub-topics</th>
                                        <th class="px-6 py-4 text-center">Goals</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    <!-- Empty -->
                                    <tr v-if="strands.length === 0">
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <GraduationCap class="mx-auto mb-4 h-12 w-12 text-muted-foreground/20" />
                                            <p class="text-sm font-semibold text-muted-foreground">No topics added yet</p>
                                            <p class="text-xs text-muted-foreground/60 mt-1">Add the first topic for {{ subject.name }} — {{ grade.name }}</p>
                                            <Button
                                                @click="openStrandModal()"
                                                class="mt-4 h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white"
                                            >
                                                <Plus class="mr-2 h-4 w-4" /> Add First Topic
                                            </Button>
                                        </td>
                                    </tr>
                                    <!-- Data rows -->
                                    <tr
                                        v-for="strand in strands"
                                        :key="strand.id"
                                        class="group transition-all hover:bg-muted/20"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary border border-border">
                                                    {{ strand.display_order }}
                                                </div>
                                                <div class="space-y-0.5">
                                                    <p class="text-sm font-semibold tracking-tight text-foreground group-hover:text-primary transition-colors">
                                                        {{ strand.name }}
                                                    </p>
                                                    <p v-if="strand.code" class="text-[10px] font-medium text-muted-foreground uppercase">
                                                        {{ strand.code }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-bold text-foreground">{{ strand.sub_strands?.length || 0 }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-bold text-foreground">
                                                {{ strand.sub_strands?.reduce((acc: number, ss: any) => acc + (ss.learning_outcomes?.length || 0), 0) || 0 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Badge class="rounded-md border-0 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 uppercase dark:bg-emerald-950/30 dark:text-emerald-400">
                                                Active
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary" title="View Sub-topics">
                                                    <Link :href="`/curriculum/syllabus/topics/${strand.id}`">
                                                        <ChevronRight class="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-muted" title="Edit Topic" @click="openStrandModal(strand)">
                                                    <Edit2 class="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-rose-50 hover:text-rose-600" title="Delete Topic" @click="deleteStrand(strand.id)">
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Footer -->
                        <div v-if="strands.length > 0" class="flex h-14 items-center justify-between border-t border-border/50 bg-muted/5 px-6">
                            <p class="text-xs font-medium text-muted-foreground">{{ strands.length }} topics in {{ grade.name }}</p>
                        </div>
                    </div>
                </TabsContent>

                <!-- Teachers Tab -->
                <TabsContent value="teachers">
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <div class="flex items-center justify-between border-b border-border/50 bg-muted/5 px-6 py-4">
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-bold text-foreground">Teacher Assignments</h3>
                                <p class="text-xs text-muted-foreground">Assign teachers for {{ subject.name }} across {{ grade.name }} classes</p>
                            </div>
                            <Button @click="openAssignmentModal()" class="h-10 rounded-lg bg-primary px-4 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                                <Plus class="mr-2 h-4 w-4" /> Assign Teacher
                            </Button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <th class="px-6 py-4">Class</th>
                                        <th class="px-6 py-4">Assigned Teacher(s)</th>
                                        <th class="px-6 py-4">Role</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    <tr v-for="cls in classes" :key="cls.id" class="group transition-all hover:bg-muted/20">
                                        <td class="px-6 py-4">
                                            <div class="space-y-0.5">
                                                <p class="text-sm font-semibold text-foreground">{{ cls.name }}</p>
                                                <p v-if="cls.stream" class="text-[10px] font-medium text-muted-foreground uppercase">{{ cls.stream }}</p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="getTeacherForClass(cls.id).length" class="space-y-1">
                                                <div v-for="alloc in getTeacherForClass(cls.id)" :key="alloc.id" class="flex items-center gap-2">
                                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary/10 text-[10px] font-bold text-primary">
                                                        {{ alloc.teacher_name?.substring(0, 2)?.toUpperCase() || '??' }}
                                                    </div>
                                                    <span class="text-xs font-semibold text-foreground">{{ alloc.teacher_name }}</span>
                                                </div>
                                            </div>
                                            <span v-else class="text-xs font-medium text-amber-500">Not assigned yet</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <Badge
                                                    v-for="alloc in getTeacherForClass(cls.id)"
                                                    :key="alloc.id"
                                                    class="rounded-md border-0 px-2 py-0.5 text-[10px] font-semibold"
                                                    :class="alloc.is_primary ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'"
                                                >
                                                    {{ alloc.is_primary ? 'Main Teacher' : 'Assistant' }}
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <Button
                                                    v-if="getTeacherForClass(cls.id).length === 0"
                                                    @click="openAssignmentModal(cls.id.toString())"
                                                    variant="outline"
                                                    class="h-8 rounded-lg border-border px-3 text-xs font-semibold hover:bg-primary hover:text-white hover:border-primary transition-all"
                                                >
                                                    Assign
                                                </Button>
                                                <DropdownMenu v-else>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-muted">
                                                            <MoreVertical class="h-4 w-4 text-muted-foreground" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" class="w-48 rounded-xl border-border bg-card p-2 shadow-lg">
                                                        <DropdownMenuItem @click="openAssignmentModal(cls.id.toString())" class="rounded-lg px-3 py-2 text-xs font-medium cursor-pointer">
                                                            Add Another Teacher
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            v-for="alloc in getTeacherForClass(cls.id)"
                                                            :key="alloc.id"
                                                            @click="removeAssignment(alloc.id)"
                                                            class="rounded-lg px-3 py-2 text-xs font-medium text-rose-600 focus:bg-rose-50 cursor-pointer"
                                                        >
                                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                            Remove {{ alloc.teacher_name }}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>

            <!-- ============================================================ -->
            <!-- Add/Edit Topic Modal -->
            <!-- ============================================================ -->
            <Dialog v-model:open="showStrandModal">
                <DialogContent class="sm:max-w-[440px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-border/50 bg-muted/5 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground">
                                {{ editingStrand ? 'Edit Topic' : 'Add New Topic' }}
                            </h3>
                            <p class="text-xs text-muted-foreground font-medium">
                                Topic for {{ subject.name }} — {{ grade.name }}
                            </p>
                        </div>
                    </div>
                    <form @submit.prevent="submitStrand" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Order Number</Label>
                                <Input
                                    type="number"
                                    v-model="strandForm.display_order"
                                    class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Topic Code</Label>
                                <Input
                                    v-model="strandForm.code"
                                    placeholder="e.g. MAT.T1"
                                    class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background uppercase"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Topic Name *</Label>
                            <Input
                                v-model="strandForm.name"
                                placeholder="e.g. Numbers and Operations"
                                class="h-10 rounded-lg border-border bg-muted/10 text-sm focus:bg-background"
                                required
                            />
                            <p v-if="strandForm.errors.name" class="text-xs text-rose-500">{{ strandForm.errors.name }}</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showStrandModal = false" class="h-10 rounded-lg px-4 text-xs font-semibold">
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="strandForm.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all"
                            >
                                {{ strandForm.processing ? 'Saving...' : editingStrand ? 'Save Changes' : 'Add Topic' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- ============================================================ -->
            <!-- Assign Teacher Modal -->
            <!-- ============================================================ -->
            <Dialog v-model:open="showAssignmentModal">
                <DialogContent class="sm:max-w-[480px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-border/50 bg-muted/5 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Users class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground">Assign Teacher</h3>
                            <p class="text-xs text-muted-foreground font-medium">
                                Assign a teacher to {{ subject.name }} — {{ grade.name }}
                            </p>
                        </div>
                    </div>
                    <form @submit.prevent="submitAssignment" class="p-6 space-y-5">
                        <!-- Class Selection -->
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Which Class?</Label>
                            <div class="relative">
                                <select
                                    v-model="assignmentForm.class_id"
                                    class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                                    :class="{ 'border-rose-300': assignmentForm.errors.class_id }"
                                >
                                    <option value="">Select a class</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id.toString()">
                                        {{ cls.name }} {{ cls.stream ? '(' + cls.stream + ')' : '' }}
                                    </option>
                                </select>
                                <ChevronRight class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/40" />
                            </div>
                            <p v-if="assignmentForm.errors.class_id" class="text-xs text-rose-500">{{ assignmentForm.errors.class_id }}</p>
                        </div>

                        <!-- Teacher Search -->
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Which Teacher?</Label>
                            <div class="relative mb-2">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input
                                    v-model="teacherSearch"
                                    placeholder="Search by name or staff number..."
                                    class="h-10 rounded-lg border-border bg-muted/10 pl-10 text-sm focus:bg-background"
                                />
                            </div>
                            <div class="relative">
                                <select
                                    v-model="assignmentForm.teacher_id"
                                    class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                                    :class="{ 'border-rose-300': assignmentForm.errors.teacher_id }"
                                >
                                    <option value="">Select a teacher</option>
                                    <option v-for="teacher in filteredTeachers" :key="teacher.id" :value="teacher.id.toString()">
                                        {{ teacher.name }} ({{ teacher.staff_number }})
                                    </option>
                                </select>
                                <ChevronRight class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/40" />
                            </div>
                            <p v-if="assignmentForm.errors.teacher_id" class="text-xs text-rose-500">{{ assignmentForm.errors.teacher_id }}</p>
                        </div>

                        <!-- Primary Teacher Toggle -->
                        <div class="flex items-center justify-between rounded-xl border border-border bg-muted/10 p-4">
                            <div class="space-y-0.5">
                                <p class="text-sm font-semibold text-foreground">Set as Main Teacher</p>
                                <p class="text-xs text-muted-foreground">This teacher leads the class for this subject</p>
                            </div>
                            <Switch
                                :checked="assignmentForm.is_primary_teacher"
                                @update:checked="(v: boolean) => (assignmentForm.is_primary_teacher = v)"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showAssignmentModal = false" class="h-10 rounded-lg px-4 text-xs font-semibold">
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="assignmentForm.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all"
                            >
                                {{ assignmentForm.processing ? 'Saving...' : 'Assign Teacher' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
