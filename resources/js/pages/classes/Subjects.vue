<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    BookCopy,
    CheckCircle2,
    ClipboardList,
    Eye,
    Edit,
    GraduationCap,
    List,
    MoreHorizontal,
    ShieldCheck,
    ShieldOff,
    Trash2,
    UserCheck,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface TeacherOption {
    id: number;
    name: string;
}

interface SubjectRow {
    allocation_id: number;
    teacher_id: number;
    subject_id: number;
    academic_year_id: number;
    name: string;
    code: string;
    description: string | null;
    subject_type: string;
    learning_area: string | null;
    teacher: string;
    is_primary_teacher: boolean;
    is_active: boolean;
    subject_is_active: boolean;
    is_examinable: boolean;
    lessons_per_week: number | null;
    minutes_per_lesson: number | null;
    is_compulsory: boolean;
}

const props = defineProps<{
    classroom: {
        id: number;
        name: string;
        code: string;
        grade: string | null;
        grade_id: number;
        stream: string | null;
        academic_year: string | null;
        academic_year_id: number;
        students_count: number;
        teacher: string | null;
    };
    subjects: SubjectRow[];
    teachers: TeacherOption[];
    stats: {
        total: number;
        active: number;
        compulsory: number;
        examinable: number;
    };
}>();

const page = usePage<{ flash?: { success?: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
    { title: props.classroom.name, href: `/classes/${props.classroom.id}` },
    { title: 'Subjects', href: `/classes/${props.classroom.id}/subjects` },
];

const viewMode = ref<'grid' | 'list'>('grid');
const showToast = ref(false);
const assignModalOpen = ref(false);
const confirmModalOpen = ref(false);
const confirmMode = ref<'activate' | 'deactivate' | 'delete'>('deactivate');
const selectedSubject = ref<SubjectRow | null>(null);
let toastTimer: ReturnType<typeof setTimeout> | null = null;

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            showToast.value = false;
        }, 3000);
    },
    { immediate: true },
);

const assignmentForm = useForm({
    teacher_id: null as number | null,
    subject_id: null as number | null,
    class_id: props.classroom.id,
    academic_year_id: props.classroom.academic_year_id,
    is_primary_teacher: false,
    is_active: true,
});

const actionForm = useForm({
    teacher_id: null as number | null,
    subject_id: null as number | null,
    class_id: props.classroom.id,
    academic_year_id: props.classroom.academic_year_id,
    is_primary_teacher: false,
    is_active: true,
});

const openAssignModal = (subject: SubjectRow) => {
    selectedSubject.value = subject;
    assignmentForm.reset();
    assignmentForm.teacher_id = subject.teacher_id;
    assignmentForm.subject_id = subject.subject_id;
    assignmentForm.class_id = props.classroom.id;
    assignmentForm.academic_year_id = subject.academic_year_id || props.classroom.academic_year_id;
    assignmentForm.is_primary_teacher = subject.is_primary_teacher;
    assignmentForm.is_active = subject.is_active;
    assignModalOpen.value = true;
};

const closeAssignModal = () => {
    assignModalOpen.value = false;
    selectedSubject.value = null;
};

const saveAssignment = () => {
    if (!selectedSubject.value) return;

    assignmentForm.put(`/classes/allocations/${selectedSubject.value.allocation_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeAssignModal();
        },
    });
};

const openConfirmModal = (mode: 'activate' | 'deactivate' | 'delete', subject: SubjectRow) => {
    confirmMode.value = mode;
    selectedSubject.value = subject;
    confirmModalOpen.value = true;
};

const closeConfirmModal = () => {
    confirmModalOpen.value = false;
    selectedSubject.value = null;
};

const confirmAction = () => {
    if (!selectedSubject.value) return;

    if (confirmMode.value === 'delete') {
        actionForm.delete(`/classes/allocations/${selectedSubject.value.allocation_id}`, {
            preserveScroll: true,
            onSuccess: closeConfirmModal,
        });

        return;
    }

    actionForm
        .transform(() => ({
            teacher_id: selectedSubject.value?.teacher_id ?? null,
            subject_id: selectedSubject.value?.subject_id ?? null,
            class_id: props.classroom.id,
            academic_year_id: selectedSubject.value?.academic_year_id ?? props.classroom.academic_year_id,
            is_primary_teacher: selectedSubject.value?.is_primary_teacher ?? false,
            is_active: confirmMode.value === 'activate',
        }))
        .put(`/classes/allocations/${selectedSubject.value.allocation_id}`, {
            preserveScroll: true,
            onSuccess: closeConfirmModal,
        });
};

const confirmTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate':
            return 'Activate subject allocation';
        case 'delete':
            return 'Delete subject allocation';
        default:
            return 'Deactivate subject allocation';
    }
});
</script>

<template>
    <Head :title="`${classroom.name} Subjects`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <BookCopy class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ classroom.name }} Subjects</h1>
                        <p class="text-muted-foreground">
                            {{ classroom.grade || 'Unknown grade' }}
                            <span v-if="classroom.stream"> • {{ classroom.stream }}</span>
                            <span v-if="classroom.academic_year"> • {{ classroom.academic_year }}</span>
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Manage all subjects allocated to this class, including teacher assignment and allocation status.
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/classes/${classroom.id}`">Back to Class</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/classes/allocations?class_id=${classroom.id}`">Manage Allocations</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/curriculum/subjects">All Subjects</Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Total Subjects</div>
                    <div class="mt-2 text-2xl font-bold">{{ stats.total }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active Allocations</div>
                    <div class="mt-2 text-2xl font-bold text-emerald-600">{{ stats.active }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Compulsory Subjects</div>
                    <div class="mt-2 text-2xl font-bold text-blue-600">{{ stats.compulsory }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Examinable Subjects</div>
                    <div class="mt-2 text-2xl font-bold text-violet-600">{{ stats.examinable }}</div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Allocated Subjects</h2>
                        <p class="text-sm text-muted-foreground">Open a subject, reassign its teacher, manage allocation state, or jump to grade allocation.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button :variant="viewMode === 'grid' ? 'default' : 'outline'" size="sm" @click="viewMode = 'grid'">
                            <GraduationCap class="mr-2 h-4 w-4" />Grid View
                        </Button>
                        <Button :variant="viewMode === 'list' ? 'default' : 'outline'" size="sm" @click="viewMode = 'list'">
                            <List class="mr-2 h-4 w-4" />List View
                        </Button>
                    </div>
                </div>

                <div v-if="subjects.length === 0" class="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">
                    No subjects are currently allocated to this class.
                </div>

                <div v-else-if="viewMode === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="subject in subjects" :key="subject.allocation_id" class="group relative overflow-hidden rounded-xl border bg-card transition hover:border-primary/40 hover:shadow-md">
                        <Link :href="`/curriculum/subjects/${subject.subject_id}`" class="block h-full p-5 pr-16">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold transition group-hover:text-primary">{{ subject.name }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || 'General' }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <Badge variant="outline">{{ subject.subject_type }}</Badge>
                                <Badge :variant="subject.is_active ? 'default' : 'outline'">{{ subject.is_active ? 'Active' : 'Inactive' }}</Badge>
                                <Badge v-if="subject.is_compulsory" variant="secondary">Compulsory</Badge>
                            </div>

                            <div class="mt-4 space-y-2 text-sm text-muted-foreground">
                                <p><span class="font-medium text-foreground">Teacher:</span> {{ subject.teacher }}</p>
                                <p><span class="font-medium text-foreground">Lessons / week:</span> {{ subject.lessons_per_week ?? '—' }}</p>
                                <p><span class="font-medium text-foreground">Minutes / lesson:</span> {{ subject.minutes_per_lesson ?? '—' }}</p>
                            </div>
                        </Link>

                        <div class="absolute right-3 top-3 z-10">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 bg-background/80 backdrop-blur">
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/curriculum/subjects/${subject.subject_id}`">
                                            <Eye class="mr-2 h-4 w-4" />View Subject
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/curriculum/subjects/${subject.subject_id}/edit`">
                                            <Edit class="mr-2 h-4 w-4" />Edit Subject
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="openAssignModal(subject)">
                                        <UserCheck class="mr-2 h-4 w-4" />Assign Teacher
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/curriculum/subjects/${subject.subject_id}/allocate`">
                                            <ClipboardList class="mr-2 h-4 w-4" />Allocate to Grades
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="openConfirmModal(subject.is_active ? 'deactivate' : 'activate', subject)">
                                        <component :is="subject.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />
                                        {{ subject.is_active ? 'Deactivate' : 'Activate' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive" @click="openConfirmModal('delete', subject)">
                                        <Trash2 class="mr-2 h-4 w-4" />Delete Allocation
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div v-else class="overflow-x-auto rounded-xl border">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50 text-left text-sm text-muted-foreground">
                                <th class="px-4 py-3 font-medium">Subject</th>
                                <th class="px-4 py-3 font-medium">Teacher</th>
                                <th class="px-4 py-3 font-medium">Type</th>
                                <th class="px-4 py-3 font-medium">Lessons</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="subject in subjects" :key="subject.allocation_id" class="border-b transition-colors hover:bg-muted/40">
                                <td class="px-4 py-3">
                                    <Link :href="`/curriculum/subjects/${subject.subject_id}`" class="font-medium hover:text-primary">
                                        {{ subject.name }}
                                    </Link>
                                    <div class="text-sm text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || 'General' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ subject.teacher }}</td>
                                <td class="px-4 py-3 text-sm">{{ subject.subject_type }}</td>
                                <td class="px-4 py-3 text-sm">{{ subject.lessons_per_week ?? '—' }}/week</td>
                                <td class="px-4 py-3">
                                    <Badge :variant="subject.is_active ? 'default' : 'outline'">{{ subject.is_active ? 'Active' : 'Inactive' }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/curriculum/subjects/${subject.subject_id}`">
                                                    <Eye class="mr-2 h-4 w-4" />View Subject
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/curriculum/subjects/${subject.subject_id}/edit`">
                                                    <Edit class="mr-2 h-4 w-4" />Edit Subject
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="openAssignModal(subject)">
                                                <UserCheck class="mr-2 h-4 w-4" />Assign Teacher
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/curriculum/subjects/${subject.subject_id}/allocate`">
                                                    <ClipboardList class="mr-2 h-4 w-4" />Allocate to Grades
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="openConfirmModal(subject.is_active ? 'deactivate' : 'activate', subject)">
                                                <component :is="subject.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />
                                                {{ subject.is_active ? 'Deactivate' : 'Activate' }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive" @click="openConfirmModal('delete', subject)">
                                                <Trash2 class="mr-2 h-4 w-4" />Delete Allocation
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeAssignModal">
                <div class="w-full max-w-lg rounded-xl border bg-background p-6 shadow-xl">
                    <h2 class="text-lg font-semibold">Assign Teacher</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        <template v-if="selectedSubject">Update the teacher assigned to {{ selectedSubject.name }} for {{ classroom.name }}.</template>
                    </p>

                    <div class="mt-6 space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Teacher</label>
                            <select v-model="assignmentForm.teacher_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                <option :value="null">Select teacher</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}</option>
                            </select>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border p-3 text-sm">
                                <input v-model="assignmentForm.is_primary_teacher" type="checkbox" />
                                Primary teacher
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border p-3 text-sm">
                                <input v-model="assignmentForm.is_active" type="checkbox" />
                                Allocation active
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <Button variant="outline" @click="closeAssignModal">Cancel</Button>
                        <Button :disabled="assignmentForm.processing || !assignmentForm.teacher_id" @click="saveAssignment">Save Assignment</Button>
                    </div>
                </div>
            </div>

            <div v-if="confirmModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeConfirmModal">
                <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                    <h2 class="text-lg font-semibold">{{ confirmTitle }}</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        <template v-if="selectedSubject">
                            {{ confirmMode === 'delete'
                                ? `Delete ${selectedSubject.name} from ${classroom.name}?`
                                : confirmMode === 'activate'
                                    ? `Activate ${selectedSubject.name} for ${classroom.name}?`
                                    : `Deactivate ${selectedSubject.name} for ${classroom.name}?` }}
                        </template>
                    </p>
                    <div class="mt-6 flex justify-end gap-2">
                        <Button variant="outline" @click="closeConfirmModal">Cancel</Button>
                        <Button :variant="confirmMode === 'delete' ? 'destructive' : 'default'" :disabled="actionForm.processing" @click="confirmAction">Confirm</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
