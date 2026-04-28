<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Download,
    MoreHorizontal,
    Users,
    CheckCircle2,
    TrendingUp,
    BookOpen,
    Upload,
    FileText,
    X,
    AlertCircle,
    Plus,
    ChevronRight,
    Loader2,
    Sparkles,
    Search,
    Eye,
    Trash2,
    GraduationCap,
    CheckSquare,
    Square,
    ChevronDown,
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
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subjects: any;
    grades: any[];
    learningAreas: any[];
    teachers: any[];
    classes: any[];
    academicYear: any;
    filters?: {
        search?: string;
        per_page?: number | string;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/syllabus' },
];

const searchQuery = ref(props.filters?.search || '');
const showAddModal = ref(false);
const selectedArea = ref('all');
const perPage = ref(props.filters?.per_page || 20);
const selectedIds = ref<number[]>([]);

const applyFilters = (pageNumber?: number) => {
    router.get(
        route('curriculum.syllabus.index'),
        {
            search: searchQuery.value,
            per_page: perPage.value,
            page: pageNumber,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

let debounceTimer: any = null;
watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch(perPage, () => applyFilters());

const filteredSubjects = computed(() => {
    let list = props.subjects.data;
    if (selectedArea.value !== 'all') {
        list = list.filter(
            (s: any) => s.learning_area_id?.toString() === selectedArea.value,
        );
    }
    return list;
});

const form = useForm({
    name: '',
    learning_area_id: '',
    code: '',
    description: '',
});

const submit = () => {
    form.post(route('curriculum.syllabus.subjects.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

// Teacher Assignment Logic
const showAssignmentModal = ref(false);
const teacherSearch = ref('');
const assignmentForm = useForm({
    teacher_id: '',
    subject_id: '',
    class_id: '',
    academic_year_id: props.academicYear?.id || '',
    is_primary_teacher: true,
    is_active: true,
});

const selectedSubject = computed(() => {
    return props.subjects.data.find(
        (s: any) => s.id.toString() === assignmentForm.subject_id,
    );
});

const filteredClasses = computed(() => {
    if (!selectedSubject.value) return [];
    const gradeLevelIds = selectedSubject.value.grade_level_ids || [];
    return props.classes.filter((c) => gradeLevelIds.includes(c.grade_id));
});

const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    const search = teacherSearch.value.toLowerCase();
    return props.teachers.filter(
        (t) =>
            t.name.toLowerCase().includes(search) ||
            t.staff_number.toLowerCase().includes(search),
    );
});

const openAssignmentModal = (subjectId: string) => {
    assignmentForm.teacher_id = '';
    assignmentForm.class_id = '';
    assignmentForm.subject_id = subjectId;
    teacherSearch.value = '';
    assignmentForm.clearErrors();
    showAssignmentModal.value = true;
};

const submitAssignment = () => {
    assignmentForm.post(route('classes.allocations.store'), {
        onSuccess: () => {
            showAssignmentModal.value = false;
        },
    });
};

// Bulk Upload
const showBulkModal = ref(false);
const bulkForm = useForm({
    file: null as File | null,
});

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        bulkForm.file = input.files[0];
    }
};

const submitBulk = () => {
    bulkForm.post(route('curriculum.syllabus.subjects.bulk'), {
        onSuccess: () => {
            showBulkModal.value = false;
            bulkForm.reset();
        },
    });
};

const dragOver = ref(false);
const route = (window as any).route;

const handleDrop = (event: DragEvent) => {
    dragOver.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        bulkForm.file = event.dataTransfer.files[0];
    }
};

const removeFile = () => {
    bulkForm.file = null;
};

// Delete subject 
const confirmDeleteSubject = (id: number) => {
    if (window.confirm('Are you sure you want to delete this subject? This cannot be undone.')) {
        router.delete(`/curriculum/syllabus/subjects/${id}`);
    }
};

const toggleSelectAll = () => {
    if (selectedIds.value.length === props.subjects.data.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.subjects.data.map((s: any) => s.id);
    }
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) selectedIds.value.splice(index, 1);
    else selectedIds.value.push(id);
};

const bulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (window.confirm(`Are you sure you want to ${action} ${selectedIds.value.length} subjects?`)) {
        router.post('/curriculum/subjects/bulk-action', {
            ids: selectedIds.value,
            action: action,
        }, {
            onSuccess: () => selectedIds.value = [],
        });
    }
};
</script>

<template>
    <Head title="Subjects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Subjects</h1>
                    <p class="text-sm text-muted-foreground">All subjects taught at this school, organised by learning area</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button 
                        v-if="selectedIds.length > 0"
                        variant="outline" 
                        class="h-10 rounded-lg border-slate-200 bg-slate-900 text-white hover:bg-black transition-all"
                    >
                        <DropdownMenu>
                            <DropdownMenuTrigger class="flex items-center gap-2 px-3 py-1 text-xs font-bold uppercase tracking-wider">
                                Actions ({{ selectedIds.length }})
                                <ChevronDown class="h-3 w-3" />
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48 rounded-xl border-border bg-card p-2 shadow-xl">
                                <DropdownMenuItem @click="bulkAction('activate')" class="rounded-lg px-3 py-2 text-xs font-medium text-emerald-600 focus:bg-emerald-50">
                                    <CheckCircle2 class="mr-2 h-4 w-4" /> Activate
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="bulkAction('deactivate')" class="rounded-lg px-3 py-2 text-xs font-medium text-amber-600 focus:bg-amber-50">
                                    <XCircle class="mr-2 h-4 w-4" /> Deactivate
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="my-1" />
                                <DropdownMenuItem @click="bulkAction('delete')" class="rounded-lg px-3 py-2 text-xs font-medium text-rose-600 focus:bg-rose-50">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </Button>

                    <Button
                        variant="outline"
                        @click="showBulkModal = true"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <Upload class="mr-2 h-4 w-4 text-primary" />
                        Add Many Subjects
                    </Button>
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <a :href="`/curriculum/subjects/export-pdf?search=${searchQuery}&learning_area_id=${selectedArea}`" target="_blank">
                            <Download class="mr-2 h-4 w-4 text-rose-500" />
                            Download PDF
                        </a>
                    </Button>
                    <Button
                        @click="showAddModal = true"
                        class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all font-bold uppercase tracking-widest"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add Subject
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, idx) in [
                        { label: 'Total Subjects', val: subjects.total, sub: 'Across all areas', icon: BookOpen },
                        { label: 'Learning Areas', val: learningAreas.length, sub: 'Subject groups', icon: GraduationCap },
                        { label: 'Active Subjects', val: subjects.total, sub: 'Currently taught', icon: CheckCircle2 },
                        { label: 'Grade Levels', val: grades.length, sub: 'Classes covered', icon: TrendingUp },
                    ]"
                    :key="idx"
                    class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md"
                >
                    <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest opacity-60">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight text-primary">{{ stat.val }}</h3>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase opacity-40">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-primary" />
                        <span class="text-[10px] font-bold text-foreground uppercase tracking-wider">Search & Filter</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 pr-4 border-r border-border/50">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase opacity-40">Records</Label>
                            <select v-model="perPage" class="h-8 rounded-lg border border-border bg-transparent px-2 text-[11px] font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                <option :value="20">20 / page</option>
                                <option :value="50">50 / page</option>
                                <option :value="100">100 / page</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Search Subject</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search by name or code..."
                                    class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold focus:bg-background transition-all"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Learning Area</Label>
                            <div class="relative">
                                <select
                                    v-model="selectedArea"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold outline-none focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all uppercase"
                                >
                                    <option value="all">All Areas</option>
                                    <option
                                        v-for="area in learningAreas"
                                        :key="area.id"
                                        :value="area.id.toString()"
                                    >{{ area.name }}</option>
                                </select>
                                <ChevronRight class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/30" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="w-16 px-6 py-4 text-center">
                                    <button @click="toggleSelectAll" class="flex h-5 w-5 items-center justify-center rounded border-2 border-border bg-background transition-all hover:border-primary mx-auto">
                                        <CheckSquare v-if="selectedIds.length === subjects.data.length && subjects.data.length > 0" class="h-4 w-4 text-primary" />
                                        <div v-else-if="selectedIds.length > 0" class="h-2 w-2 rounded-sm bg-primary"></div>
                                    </button>
                                </th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Code</th>
                                <th class="px-6 py-4">Learning Area</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <!-- Empty state -->
                            <tr v-if="filteredSubjects.length === 0">
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <BookOpen class="mx-auto mb-4 h-12 w-12 text-muted-foreground/20" />
                                    <p class="text-sm font-semibold text-muted-foreground">No subjects found</p>
                                    <p class="text-xs text-muted-foreground/60 mt-1">Try a different search or add a new subject</p>
                                </td>
                            </tr>
                            <!-- Data rows -->
                            <tr
                                v-for="subject in filteredSubjects"
                                :key="subject.id"
                                class="group transition-all hover:bg-muted/20"
                                :class="{ 'bg-primary/5': selectedIds.includes(subject.id) }"
                            >
                                <td class="px-6 py-4">
                                    <button @click="toggleSelect(subject.id)" class="flex h-5 w-5 items-center justify-center rounded border-2 border-border bg-background transition-all hover:border-primary mx-auto" :class="{ 'border-primary bg-primary/10': selectedIds.includes(subject.id) }">
                                        <CheckSquare v-if="selectedIds.includes(subject.id)" class="h-4 w-4 text-primary" />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 flex items-center justify-center rounded-lg bg-primary/10 text-[10px] font-black text-primary border border-border shadow-sm">
                                            {{ subject.name.substring(0, 3).toUpperCase() }}
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-bold tracking-tight text-foreground group-hover:text-primary transition-colors leading-none uppercase">
                                                {{ subject.name }}
                                            </p>
                                            <p class="text-[10px] font-bold text-muted-foreground opacity-40 uppercase">
                                                {{ subject.description || 'No description' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="outline" class="rounded-lg border-primary/10 bg-primary/5 px-2.5 py-0.5 text-[9px] font-bold text-primary uppercase tracking-wider">
                                        {{ subject.code || 'N/A' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-bold text-foreground uppercase tracking-tighter opacity-60">
                                    {{ subject.learning_area || 'General' }}
                                </td>
                                <td class="px-6 py-4">
                                    <Badge class="rounded-lg border-0 bg-emerald-50 px-2.5 py-1 text-[9px] font-bold text-emerald-600 uppercase tracking-widest dark:bg-emerald-950/30 dark:text-emerald-400">
                                        Active
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 translate-x-1 group-hover:translate-x-0 transition-transform">
                                        <!-- Assign Teacher -->
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary"
                                            title="Assign Teacher"
                                            @click="openAssignmentModal(subject.id.toString())"
                                        >
                                            <Users class="h-4 w-4" />
                                        </Button>
                                        <!-- Delete -->
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 rounded-lg hover:bg-muted"
                                                >
                                                    <MoreHorizontal class="h-4 w-4 text-muted-foreground/40" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-52 rounded-xl border-border bg-card p-2 shadow-lg">
                                                <div class="mb-1 px-2 pb-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-40 border-b border-border/50">
                                                    View Topics By Grade
                                                </div>
                                                <div class="grid grid-cols-2 gap-1 pt-1">
                                                    <DropdownMenuItem
                                                        v-for="grade in grades"
                                                        :key="grade.id"
                                                        as-child
                                                    >
                                                        <Link
                                                            :href="`/curriculum/syllabus/${subject.id}/${grade.id}`"
                                                            class="flex cursor-pointer items-center justify-center rounded-lg bg-muted/50 px-2 py-1.5 text-[9px] font-bold uppercase transition-all hover:bg-primary hover:text-white"
                                                        >
                                                            {{ grade.name }}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                </div>
                                                <DropdownMenuSeparator class="my-2" />
                                                <DropdownMenuItem
                                                    class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 focus:bg-rose-50 focus:text-rose-700 cursor-pointer"
                                                    @click="confirmDeleteSubject(subject.id)"
                                                >
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete Subject
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer (Pagination) -->
                <div class="flex h-16 items-center justify-between border-t border-border/50 px-6 bg-muted/5">
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">
                        Page {{ subjects.current_page }} of {{ subjects.last_page }}
                    </p>
                    <div class="flex items-center gap-2">
                        <template v-for="(link, i) in subjects.links" :key="i">
                            <Button 
                                v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" 
                                variant="outline" 
                                size="sm" 
                                :class="['h-8 w-8 rounded-lg text-[10px] font-bold transition-all', link.active ? 'border-primary bg-primary text-white shadow-sm' : 'border-border bg-card hover:bg-muted text-muted-foreground']" 
                                @click="router.get(link.url!)"
                            >
                                {{ link.label }}
                            </Button>
                            <Button 
                                v-else-if="link.label.includes('Previous')" 
                                variant="outline" 
                                size="sm" 
                                class="h-8 rounded-lg px-3 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" 
                                :disabled="!link.url" 
                                @click="router.get(link.url!)"
                            >
                                Prev
                            </Button>
                            <Button 
                                v-else-if="link.label.includes('Next')" 
                                variant="outline" 
                                size="sm" 
                                class="h-8 rounded-lg px-3 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" 
                                :disabled="!link.url" 
                                @click="router.get(link.url!)"
                            >
                                Next
                            </Button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- Add Subject Modal -->
            <!-- ============================================================ -->
            <Dialog v-model:open="showAddModal">
                <DialogContent class="sm:max-w-[480px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-border/50 bg-muted/5 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">Add New Subject</h3>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">Fill in the details for the new subject</p>
                        </div>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-5">
                        <div class="space-y-2">
                            <Label for="name" class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Subject Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g. Mathematics"
                                class="h-10 rounded-lg border-border bg-muted/10 text-sm font-bold focus:bg-background"
                                required
                            />
                            <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Learning Area</Label>
                            <div class="relative">
                                <select
                                    v-model="form.learning_area_id"
                                    class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-[10px] font-bold outline-none focus:bg-background focus:ring-2 focus:ring-primary/10 uppercase tracking-widest"
                                >
                                    <option value="">Select a learning area</option>
                                    <option
                                        v-for="area in learningAreas"
                                        :key="area.id"
                                        :value="area.id.toString()"
                                    >{{ area.name }}</option>
                                </select>
                                <ChevronRight class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/30" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="code" class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Subject Code (optional)</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g. MAT"
                                class="h-10 rounded-lg border-border bg-muted/10 text-sm font-bold focus:bg-background uppercase tracking-widest"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="description" class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Description (optional)</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Brief description of the subject..."
                                rows="3"
                                class="w-full rounded-lg border border-border bg-muted/10 px-3 py-2 text-sm font-medium outline-none resize-none focus:bg-background focus:ring-2 focus:ring-primary/10"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showAddModal = false" class="h-10 rounded-lg px-4 text-[10px] font-bold uppercase tracking-widest">
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-[10px] font-bold text-white shadow-sm hover:opacity-90 transition-all uppercase tracking-widest"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Subject' }}
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
                            <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">Assign Teacher</h3>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">
                                Link a teacher for
                                <span class="text-foreground font-black">{{ selectedSubject?.name }}</span>
                            </p>
                        </div>
                    </div>
                    <form @submit.prevent="submitAssignment" class="p-6 space-y-5">
                        <!-- Class Selection -->
                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Which Class?</Label>
                            <div class="relative">
                                <select
                                    v-model="assignmentForm.class_id"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/10 px-4 text-[10px] font-bold outline-none focus:bg-background focus:ring-2 focus:ring-primary/10 uppercase tracking-widest"
                                    :class="{ 'border-rose-300': assignmentForm.errors.class_id }"
                                >
                                    <option value="">Select a class</option>
                                    <option
                                        v-for="cls in filteredClasses"
                                        :key="cls.id"
                                        :value="cls.id.toString()"
                                    >
                                        {{ cls.grade_name }} — {{ cls.name }} {{ cls.stream ? '(' + cls.stream + ')' : '' }}
                                    </option>
                                </select>
                                <ChevronRight class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/30" />
                            </div>
                            <p v-if="filteredClasses.length === 0" class="text-xs text-muted-foreground italic font-medium">
                                No classes available for this subject's grade levels.
                            </p>
                            <p v-if="assignmentForm.errors.class_id" class="text-xs text-rose-500">{{ assignmentForm.errors.class_id }}</p>
                        </div>

                        <!-- Teacher Selection -->
                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase opacity-40 ml-1">Which Teacher?</Label>
                            <div class="relative mb-2">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                <Input
                                    v-model="teacherSearch"
                                    placeholder="Search by name or staff number..."
                                    class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold focus:bg-background transition-all"
                                />
                            </div>
                            <div class="relative">
                                <select
                                    v-model="assignmentForm.teacher_id"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/10 px-4 text-[10px] font-bold outline-none focus:bg-background focus:ring-2 focus:ring-primary/10 uppercase tracking-widest"
                                    :class="{ 'border-rose-300': assignmentForm.errors.teacher_id }"
                                >
                                    <option value="">Select a teacher</option>
                                    <option
                                        v-for="teacher in filteredTeachers"
                                        :key="teacher.id"
                                        :value="teacher.id.toString()"
                                    >
                                        {{ teacher.name }} ({{ teacher.staff_number }})
                                    </option>
                                </select>
                                <ChevronRight class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 rotate-90 text-muted-foreground/30" />
                            </div>
                            <p v-if="assignmentForm.errors.teacher_id" class="text-xs text-rose-500">{{ assignmentForm.errors.teacher_id }}</p>
                        </div>

                        <!-- Primary Teacher Toggle -->
                        <div class="flex items-center justify-between rounded-xl border border-border bg-muted/10 p-4">
                            <div class="space-y-0.5">
                                <p class="text-[11px] font-black text-foreground uppercase tracking-wider">Set as Main Teacher</p>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-40">This teacher leads the class</p>
                            </div>
                            <Switch
                                :checked="assignmentForm.is_primary_teacher"
                                @update:checked="(v: boolean) => (assignmentForm.is_primary_teacher = v)"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showAssignmentModal = false" class="h-10 rounded-lg px-4 text-[10px] font-bold uppercase tracking-widest">
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="assignmentForm.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-[10px] font-bold text-white shadow-sm hover:opacity-90 transition-all uppercase tracking-widest"
                            >
                                {{ assignmentForm.processing ? 'Saving...' : 'Assign Teacher' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- ============================================================ -->
            <!-- Bulk Upload Modal -->
            <!-- ============================================================ -->
            <Dialog v-model:open="showBulkModal">
                <DialogContent class="sm:max-w-[500px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-border/50 bg-muted/5 p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Upload class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">Upload Many Subjects</h3>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">Use a CSV file to add multiple subjects</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Download Template -->
                        <div class="flex items-center justify-between rounded-xl border border-border bg-muted/10 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-foreground uppercase tracking-wider">CSV Template</p>
                                    <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-40">Download this to fill in</p>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" class="h-9 rounded-lg border-border px-4 text-[10px] font-bold uppercase tracking-widest" as-child>
                                <a :href="route('curriculum.syllabus.subjects.template')" download>
                                    <Download class="mr-2 h-3.5 w-3.5 text-primary" />
                                    Download
                                </a>
                            </Button>
                        </div>

                        <!-- Drop Zone -->
                        <div
                            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-10 transition-all duration-300"
                            :class="[
                                dragOver ? 'scale-[0.99] border-primary bg-primary/5' : 'border-border bg-muted/10',
                                bulkForm.file ? 'border-emerald-400 bg-emerald-50/30' : '',
                            ]"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                        >
                            <input
                                type="file"
                                accept=".csv"
                                class="absolute inset-0 z-20 cursor-pointer opacity-0"
                                @change="handleFileChange"
                            />

                            <template v-if="!bulkForm.file">
                                <Upload class="h-10 w-10 text-muted-foreground/30 mb-3" />
                                <p class="text-[11px] font-black text-foreground uppercase tracking-wider">Drop CSV file here</p>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-40 mt-1">or click to choose</p>
                            </template>

                            <template v-else>
                                <div class="flex w-full items-center gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white">
                                        <CheckCircle2 class="h-6 w-6" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-black text-foreground truncate uppercase tracking-widest">{{ bulkForm.file.name }}</p>
                                        <p class="text-[9px] font-bold text-muted-foreground uppercase">
                                            {{ (bulkForm.file.size / 1024).toFixed(2) }} KB
                                        </p>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="z-30 h-8 w-8 shrink-0 text-muted-foreground hover:text-rose-500"
                                        @click.stop="removeFile"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/50">
                            <Button type="button" variant="ghost" @click="showBulkModal = false" class="h-10 rounded-lg px-4 text-[10px] font-bold uppercase tracking-widest">
                                Cancel
                            </Button>
                            <Button
                                @click="submitBulk"
                                :disabled="!bulkForm.file || bulkForm.processing"
                                class="h-10 rounded-lg bg-primary px-6 text-[10px] font-bold text-white shadow-sm hover:opacity-90 transition-all uppercase tracking-widest"
                            >
                                <span v-if="bulkForm.processing" class="flex items-center gap-2">
                                    <Loader2 class="h-4 w-4 animate-spin" />
                                    Uploading...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <Sparkles class="h-4 w-4" />
                                    Upload Subjects
                                </span>
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
