<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Edit,
    Filter,
    Grid2x2,
    Layers3,
    List,
    MoreHorizontal,
    Plus,
    ShieldCheck,
    ShieldOff,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface ClassOption {
    id: number;
    name: string;
    grade_id: number;
    grade: string | null;
    stream: string | null;
    academic_year: string | null;
}
interface SubjectOption {
    id: number;
    name: string;
    code: string;
    learning_area: string | null;
    grade_level_ids: number[];
}
interface TeacherOption {
    id: number;
    name: string;
    staff_number: string | null;
}
interface AcademicYearOption {
    id: number;
    name: string;
}
interface AssignmentRow {
    id: number;
    teacher_id: number;
    subject_id: number;
    class_id: number;
    academic_year_id: number;
    teacher: string;
    staff_number: string | null;
    subject: string;
    subject_code: string;
    learning_area: string | null;
    class: string;
    academic_year: string;
    is_primary_teacher: boolean;
    is_active: boolean;
}

const props = defineProps<{
    classes: ClassOption[];
    subjects: SubjectOption[];
    teachers: TeacherOption[];
    academicYears: AcademicYearOption[];
    assignments: AssignmentRow[];
    stats: {
        total_assignments: number;
        active_assignments: number;
        primary_assignments: number;
        teachers_involved: number;
    };
}>();

const page = usePage<{ flash?: { success?: string } }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
    { title: 'Allocations', href: '/classes/allocations' },
];

const selectedView = ref<'grid' | 'list'>('grid');
const selectedClassId = ref<string>('');
const showFilters = ref(true);
const showCreate = ref(false);
const showConfirm = ref(false);
const editingAssignment = ref<AssignmentRow | null>(null);
const confirmTarget = ref<AssignmentRow | null>(null);
const confirmMode = ref<'delete' | 'activate' | 'deactivate'>('delete');
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;

const form = useForm({
    teacher_id: '',
    subject_id: '',
    class_id: '',
    academic_year_id: '',
    is_primary_teacher: true,
    is_active: true,
});

const actionForm = useForm({});

const classMap = computed(
    () => new Map(props.classes.map((item) => [item.id, item])),
);

const selectedClass = computed(() => {
    const id = Number(form.class_id || selectedClassId.value);
    return classMap.value.get(id) ?? null;
});

const filteredSubjects = computed(() => {
    const gradeId = selectedClass.value?.grade_id;

    if (!gradeId) {
        return editingAssignment.value
            ? props.subjects.filter(
                  (subject) =>
                      subject.id === editingAssignment.value?.subject_id,
              )
            : [];
    }

    const allowed = props.subjects.filter((subject) =>
        subject.grade_level_ids.includes(gradeId),
    );

    if (
        editingAssignment.value &&
        !allowed.some(
            (subject) => subject.id === editingAssignment.value?.subject_id,
        )
    ) {
        const current = props.subjects.find(
            (subject) => subject.id === editingAssignment.value?.subject_id,
        );
        return current ? [current, ...allowed] : allowed;
    }

    return allowed;
});

const filteredAssignments = computed(() => {
    if (!selectedClassId.value) return props.assignments;
    return props.assignments.filter(
        (item) => item.class_id === Number(selectedClassId.value),
    );
});

const openCreate = () => {
    editingAssignment.value = null;
    form.reset();
    form.is_primary_teacher = true;
    form.is_active = true;
    if (selectedClassId.value) form.class_id = selectedClassId.value;
    showCreate.value = true;
};

const openEdit = (assignment: AssignmentRow) => {
    editingAssignment.value = assignment;
    form.teacher_id = String(assignment.teacher_id);
    form.subject_id = String(assignment.subject_id);
    form.class_id = String(assignment.class_id);
    form.academic_year_id = String(assignment.academic_year_id);
    form.is_primary_teacher = assignment.is_primary_teacher;
    form.is_active = assignment.is_active;
    showCreate.value = true;
};

watch(
    () => form.class_id,
    () => {
        if (
            !filteredSubjects.value.some(
                (subject) => String(subject.id) === form.subject_id,
            )
        ) {
            form.subject_id = '';
        }
    },
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        teacher_id: Number(data.teacher_id),
        subject_id: Number(data.subject_id),
        class_id: Number(data.class_id),
        academic_year_id: Number(data.academic_year_id),
        is_primary_teacher: Boolean(data.is_primary_teacher),
        is_active: Boolean(data.is_active),
    }));

    if (editingAssignment.value) {
        form.put(`/classes/allocations/${editingAssignment.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showCreate.value = false;
                editingAssignment.value = null;
                form.reset();
            },
        });
        return;
    }

    form.post('/classes/allocations', {
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            form.reset();
        },
    });
};

const requestAction = (
    assignment: AssignmentRow,
    mode: 'delete' | 'activate' | 'deactivate',
) => {
    confirmTarget.value = assignment;
    confirmMode.value = mode;
    showConfirm.value = true;
};

const confirmAction = () => {
    if (!confirmTarget.value) return;

    if (confirmMode.value === 'delete') {
        actionForm.delete(`/classes/allocations/${confirmTarget.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showConfirm.value = false;
                confirmTarget.value = null;
            },
        });
        return;
    }

    actionForm
        .transform(() => ({
            teacher_id: confirmTarget.value?.teacher_id,
            subject_id: confirmTarget.value?.subject_id,
            class_id: confirmTarget.value?.class_id,
            academic_year_id: confirmTarget.value?.academic_year_id,
            is_primary_teacher: confirmTarget.value?.is_primary_teacher,
            is_active: confirmMode.value === 'activate',
        }))
        .put(`/classes/allocations/${confirmTarget.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showConfirm.value = false;
                confirmTarget.value = null;
            },
        });
};

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => (showToast.value = false), 3000);
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Class Allocations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            v-if="showToast && flashSuccess"
            class="fixed top-4 right-4 z-50 rounded-lg border bg-background px-4 py-3 shadow-lg"
        >
            <div class="flex items-center gap-2 text-sm font-medium">
                <CheckCircle2 class="h-4 w-4 text-green-600" />{{
                    flashSuccess
                }}
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10"
                    >
                        <Layers3 class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Class Subject Allocations
                        </h1>
                        <p class="text-muted-foreground">
                            Assign only grade-appropriate subjects to teachers
                            within specific classes
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child
                        ><Link href="/classes">Back to Classes</Link></Button
                    >
                    <Button @click="openCreate"
                        ><Plus class="mr-2 h-4 w-4" />Add Allocation</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">
                        Total Assignments
                    </div>
                    <div class="mt-1 text-3xl font-bold">
                        {{ stats.total_assignments }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">
                        {{ stats.active_assignments }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">
                        Primary Teachers
                    </div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">
                        {{ stats.primary_assignments }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">
                        Teachers Involved
                    </div>
                    <div class="mt-1 text-3xl font-bold text-purple-600">
                        {{ stats.teachers_involved }}
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Class Filter</label>
                        <select
                            v-model="selectedClassId"
                            class="h-10 min-w-60 rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="">All classes</option>
                            <option
                                v-for="classroom in classes"
                                :key="classroom.id"
                                :value="String(classroom.id)"
                            >
                                {{ classroom.name
                                }}{{
                                    classroom.grade
                                        ? ` • ${classroom.grade}`
                                        : ''
                                }}
                            </option>
                        </select>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        class="mt-7"
                        @click="showFilters = !showFilters"
                        ><Filter class="mr-2 h-4 w-4" />{{
                            showFilters ? 'Hide Filters' : 'Show Filters'
                        }}</Button
                    >
                </div>
                <div class="mt-7 flex items-center gap-2 md:mt-0">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="selectedView = 'grid'"
                        ><Grid2x2 class="mr-2 h-4 w-4" />Grid</Button
                    >
                    <Button
                        variant="outline"
                        size="sm"
                        @click="selectedView = 'list'"
                        ><List class="mr-2 h-4 w-4" />List</Button
                    >
                </div>
            </div>

            <div
                v-if="selectedView === 'grid'"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <div
                    v-for="assignment in filteredAssignments"
                    :key="assignment.id"
                    class="rounded-xl border bg-card p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ assignment.subject }}
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                {{ assignment.subject_code }} •
                                {{ assignment.class }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Teacher: {{ assignment.teacher }}
                            </p>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child
                                ><Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8"
                                    ><MoreHorizontal class="h-4 w-4" /></Button
                            ></DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="openEdit(assignment)"
                                    ><Edit
                                        class="mr-2 h-4 w-4"
                                    />Edit</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    @click="
                                        requestAction(
                                            assignment,
                                            assignment.is_active
                                                ? 'deactivate'
                                                : 'activate',
                                        )
                                    "
                                    ><component
                                        :is="
                                            assignment.is_active
                                                ? ShieldOff
                                                : ShieldCheck
                                        "
                                        class="mr-2 h-4 w-4"
                                    />{{
                                        assignment.is_active
                                            ? 'Deactivate'
                                            : 'Activate'
                                    }}</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    class="text-destructive"
                                    @click="requestAction(assignment, 'delete')"
                                    ><Trash2
                                        class="mr-2 h-4 w-4"
                                    />Delete</DropdownMenuItem
                                >
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">
                                Learning Area
                            </div>
                            <div class="mt-1 font-semibold">
                                {{ assignment.learning_area || '—' }}
                            </div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">Year</div>
                            <div class="mt-1 font-semibold">
                                {{ assignment.academic_year }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Subject
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Class
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Teacher
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Year
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="assignment in filteredAssignments"
                                :key="assignment.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        {{ assignment.subject }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ assignment.subject_code }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ assignment.class }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ assignment.teacher }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ assignment.academic_year }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{
                                        assignment.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child
                                            ><Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8"
                                                ><MoreHorizontal
                                                    class="h-4 w-4" /></Button
                                        ></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                @click="openEdit(assignment)"
                                                ><Edit
                                                    class="mr-2 h-4 w-4"
                                                />Edit</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                @click="
                                                    requestAction(
                                                        assignment,
                                                        assignment.is_active
                                                            ? 'deactivate'
                                                            : 'activate',
                                                    )
                                                "
                                                ><component
                                                    :is="
                                                        assignment.is_active
                                                            ? ShieldOff
                                                            : ShieldCheck
                                                    "
                                                    class="mr-2 h-4 w-4"
                                                />{{
                                                    assignment.is_active
                                                        ? 'Deactivate'
                                                        : 'Activate'
                                                }}</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                class="text-destructive"
                                                @click="
                                                    requestAction(
                                                        assignment,
                                                        'delete',
                                                    )
                                                "
                                                ><Trash2
                                                    class="mr-2 h-4 w-4"
                                                />Delete</DropdownMenuItem
                                            >
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            v-if="showCreate"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showCreate = false"
        >
            <div
                class="w-full max-w-3xl rounded-xl border bg-background p-6 shadow-xl"
            >
                <h2 class="text-lg font-semibold">
                    {{
                        editingAssignment ? 'Edit Allocation' : 'Add Allocation'
                    }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Pick a class, then assign only grade-appropriate subjects to
                    a teacher.
                </p>
                <form
                    @submit.prevent="submit"
                    class="mt-6 grid gap-4 md:grid-cols-2"
                >
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Class</label
                        ><select
                            v-model="form.class_id"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="">Select class</option>
                            <option
                                v-for="classroom in classes"
                                :key="classroom.id"
                                :value="String(classroom.id)"
                            >
                                {{ classroom.name
                                }}{{
                                    classroom.grade
                                        ? ` • ${classroom.grade}`
                                        : ''
                                }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Subject</label
                        ><select
                            v-model="form.subject_id"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="">Select subject</option>
                            <option
                                v-for="subject in filteredSubjects"
                                :key="subject.id"
                                :value="String(subject.id)"
                            >
                                {{ subject.name }} • {{ subject.code }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Teacher</label
                        ><select
                            v-model="form.teacher_id"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="">Select teacher</option>
                            <option
                                v-for="teacher in teachers"
                                :key="teacher.id"
                                :value="String(teacher.id)"
                            >
                                {{ teacher.name
                                }}{{
                                    teacher.staff_number
                                        ? ` • ${teacher.staff_number}`
                                        : ''
                                }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Academic Year</label
                        ><select
                            v-model="form.academic_year_id"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="">Select year</option>
                            <option
                                v-for="year in academicYears"
                                :key="year.id"
                                :value="String(year.id)"
                            >
                                {{ year.name }}
                            </option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm"
                        ><input
                            v-model="form.is_primary_teacher"
                            type="checkbox"
                        />
                        Primary teacher for this subject</label
                    >
                    <label class="flex items-center gap-2 text-sm"
                        ><input v-model="form.is_active" type="checkbox" />
                        Active allocation</label
                    >
                    <div class="mt-4 flex justify-end gap-2 md:col-span-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="showCreate = false"
                            >Cancel</Button
                        ><Button type="submit" :disabled="form.processing">{{
                            editingAssignment
                                ? 'Save Changes'
                                : 'Save Allocation'
                        }}</Button>
                    </div>
                </form>
            </div>
        </div>

        <div
            v-if="showConfirm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showConfirm = false"
        >
            <div
                class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl"
            >
                <h2 class="text-lg font-semibold">
                    {{
                        confirmMode === 'delete'
                            ? 'Delete allocation'
                            : confirmMode === 'activate'
                              ? 'Activate allocation'
                              : 'Deactivate allocation'
                    }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Are you sure you want to continue with this allocation
                    action?
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="showConfirm = false"
                        >Cancel</Button
                    ><Button
                        :variant="
                            confirmMode === 'delete' ? 'destructive' : 'default'
                        "
                        :disabled="actionForm.processing"
                        @click="confirmAction"
                        >Confirm</Button
                    >
                </div>
            </div>
        </div>
    </AppLayout>
</template>
