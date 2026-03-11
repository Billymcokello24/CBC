<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowRightLeft,
    BookCopy,
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    MoreHorizontal,
    School,
    Search,
    ShieldAlert,
    ShieldCheck,
    ShieldOff,
    Trash2,
    UserCheck,
    ArrowUpCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface StudentRow {
    id: number;
    name: string;
    admission_number: string | null;
    gender: string;
    status: string;
}

const props = defineProps<{
    classroom: {
        id: number;
        name: string;
        code: string;
        grade: string | null;
        stream: string | null;
        academic_year: string | null;
        capacity: number | null;
        students_count: number;
        utilization: number;
        is_active: boolean;
        teacher: string | null;
        teacher_email: string | null;
    };
    subjectAllocations: Array<{
        id: number;
        teacher_id: number;
        subject_id: number;
        academic_year_id: number;
        subject: string;
        code: string;
        type: string;
        learning_area: string | null;
        teacher: string;
        is_primary_teacher: boolean;
        is_active: boolean;
    }>;
    students: StudentRow[];
    filters: {
        search: string;
        status: string;
        gender: string;
    };
    transferTargets: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
    genderOptions: Array<{ value: string; label: string }>;
}>();

const page = usePage<{ flash?: { success?: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
    { title: props.classroom.name, href: `/classes/${props.classroom.id}` },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedGender = ref(props.filters.gender ?? 'all');
const selectedStudentIds = ref<number[]>([]);
const transferTargetId = ref('');
const showFilters = ref(true);
const actionForm = useForm({});
const bulkPromoteForm = useForm<{ student_ids: number[] }>({ student_ids: [] });
const bulkTransferForm = useForm<{ student_ids: number[]; target_class_id: number | null }>({ student_ids: [], target_class_id: null });
const confirmOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'demote' | 'transfer' | 'promote'>('suspend');
const selectedStudent = ref<StudentRow | null>(null);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const subjectActionForm = useForm({});
const subjectConfirmOpen = ref(false);
const subjectConfirmMode = ref<'activate' | 'deactivate' | 'delete'>('deactivate');
const selectedSubjectAllocation = ref<typeof props.subjectAllocations[number] | null>(null);

const applyFilters = () => {
    router.get(`/classes/${props.classroom.id}`, {
        search: searchQuery.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        gender: selectedGender.value !== 'all' ? selectedGender.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
});
watch([selectedStatus, selectedGender], () => applyFilters());

watch(
    () => props.students,
    () => {
        selectedStudentIds.value = selectedStudentIds.value.filter((id) => props.students.some((student) => student.id === id));
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedGender.value = 'all';
    applyFilters();
};

const allSelected = computed(() => props.students.length > 0 && props.students.every((student) => selectedStudentIds.value.includes(student.id)));
const selectedCount = computed(() => selectedStudentIds.value.length);

const toggleAllStudents = () => {
    selectedStudentIds.value = allSelected.value ? [] : props.students.map((student) => student.id);
};

const toggleStudent = (studentId: number) => {
    selectedStudentIds.value = selectedStudentIds.value.includes(studentId)
        ? selectedStudentIds.value.filter((id) => id !== studentId)
        : [...selectedStudentIds.value, studentId];
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

const openActionModal = (mode: 'suspend' | 'activate' | 'delete' | 'demote' | 'transfer' | 'promote', student: StudentRow | null = null) => {
    confirmMode.value = mode;
    selectedStudent.value = student;
    confirmOpen.value = true;
};

const closeActionModal = () => {
    confirmOpen.value = false;
    selectedStudent.value = null;
};

const confirmAction = () => {
    if (confirmMode.value === 'promote') {
        bulkPromoteForm.student_ids = [...selectedStudentIds.value];
        bulkPromoteForm.post('/students/promote', { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    if (confirmMode.value === 'transfer') {
        if (!selectedStudentIds.value.length || !transferTargetId.value) return;
        bulkTransferForm.student_ids = [...selectedStudentIds.value];
        bulkTransferForm.target_class_id = Number(transferTargetId.value);
        selectedStudentIds.value.forEach((studentId) => {
            actionForm.transform(() => ({
                target_class_id: Number(transferTargetId.value),
            })).patch(`/students/${studentId}/transfer`, {
                preserveScroll: true,
            });
        });
        closeActionModal();
        selectedStudentIds.value = [];
        return;
    }

    if (!selectedStudent.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedStudent.value.id}/suspend`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }
    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedStudent.value.id}/activate`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }
    if (confirmMode.value === 'demote') {
        actionForm.patch(`/students/${selectedStudent.value.id}/demote`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }
    actionForm.delete(`/students/${selectedStudent.value.id}`, { preserveScroll: true, onSuccess: closeActionModal });
};

const modalTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate': return 'Activate student';
        case 'delete': return 'Delete student';
        case 'demote': return 'Demote student';
        case 'transfer': return 'Transfer selected students';
        case 'promote': return 'Promote selected students';
        default: return 'Suspend student';
    }
});

const openSubjectActionModal = (mode: 'activate' | 'deactivate' | 'delete', allocation: typeof props.subjectAllocations[number]) => {
    subjectConfirmMode.value = mode;
    selectedSubjectAllocation.value = allocation;
    subjectConfirmOpen.value = true;
};

const closeSubjectActionModal = () => {
    subjectConfirmOpen.value = false;
    selectedSubjectAllocation.value = null;
};

const confirmSubjectAction = () => {
    if (!selectedSubjectAllocation.value) return;

    if (subjectConfirmMode.value === 'delete') {
        subjectActionForm.delete(`/classes/allocations/${selectedSubjectAllocation.value.id}`, {
            preserveScroll: true,
            onSuccess: closeSubjectActionModal,
        });
        return;
    }

    subjectActionForm.transform(() => ({
        teacher_id: selectedSubjectAllocation.value?.teacher_id,
        subject_id: selectedSubjectAllocation.value?.subject_id,
        class_id: props.classroom.id,
        academic_year_id: selectedSubjectAllocation.value?.academic_year_id,
        is_primary_teacher: selectedSubjectAllocation.value?.is_primary_teacher,
        is_active: subjectConfirmMode.value === 'activate',
    })).put(`/classes/allocations/${selectedSubjectAllocation.value.id}`, {
        preserveScroll: true,
        onSuccess: closeSubjectActionModal,
    });
};
</script>

<template>
    <Head :title="classroom.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <School class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ classroom.name }}</h1>
                        <p class="text-muted-foreground">{{ classroom.grade || 'Unknown grade' }}<span v-if="classroom.stream"> • {{ classroom.stream }}</span><span v-if="classroom.academic_year"> • {{ classroom.academic_year }}</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child><Link :href="`/classes/${classroom.id}/edit`">Edit Class</Link></Button>
                    <Button variant="outline" as-child><Link href="/classes">Back to Classes</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Code</div><div class="mt-1 text-2xl font-bold">{{ classroom.code }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Capacity</div><div class="mt-1 text-2xl font-bold">{{ classroom.capacity ?? '—' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Students</div><div class="mt-1 text-2xl font-bold text-blue-600">{{ classroom.students_count }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Teacher</div><div class="mt-1 font-semibold">{{ classroom.teacher || 'Not assigned' }}</div><div class="text-xs text-muted-foreground">{{ classroom.teacher_email || 'No email' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Status</div><div class="mt-1"><Badge>{{ classroom.is_active ? 'Active' : 'Inactive' }}</Badge></div></div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Class Operations</h2>
                        <p class="text-sm text-muted-foreground">Manage all students assigned to this class</p>
                    </div>
                    <div class="text-sm text-muted-foreground">Utilization: {{ classroom.utilization }}%</div>
                </div>
                <div class="mb-6 h-2 rounded-full bg-muted"><div class="h-full rounded-full bg-indigo-500" :style="{ width: `${classroom.utilization}%` }"></div></div>

                <div class="flex flex-col gap-4 md:flex-row md:items-center">
                    <div class="relative flex-1 md:max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchQuery" placeholder="Search students..." class="pl-9" />
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="showFilters = !showFilters"><Filter class="mr-2 h-4 w-4" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}</Button>
                        <Button variant="outline" size="sm" @click="clearFilters">Reset</Button>
                    </div>
                </div>

                <div v-if="showFilters" class="mt-4 grid gap-4 rounded-xl border p-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Student Status</label>
                        <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Gender</label>
                        <select v-model="selectedGender" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option v-for="option in genderOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                </div>

                <div v-if="selectedCount > 0" class="mt-4 flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                    <div>
                        <p class="text-sm font-medium">{{ selectedCount }} student{{ selectedCount === 1 ? '' : 's' }} selected</p>
                        <p class="text-xs text-muted-foreground">Promote, transfer, or clear selection for this class.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="selectedStudentIds = []">Clear</Button>
                        <Button variant="outline" size="sm" @click="openActionModal('transfer')"><ArrowRightLeft class="mr-2 h-4 w-4" />Transfer</Button>
                        <Button size="sm" @click="openActionModal('promote')"><ArrowUpCircle class="mr-2 h-4 w-4" />Promote</Button>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"><input type="checkbox" :checked="allSelected" @change="toggleAllStudents" /></th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Adm. No</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Gender</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.length === 0"><td colspan="6" class="px-4 py-10 text-center text-sm text-muted-foreground">No students match the current class filters.</td></tr>
                            <tr v-for="student in students" :key="student.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3"><input type="checkbox" :checked="selectedStudentIds.includes(student.id)" @change="toggleStudent(student.id)" /></td>
                                <td class="px-4 py-3 font-medium">{{ student.name }}</td>
                                <td class="px-4 py-3 text-sm">{{ student.admission_number || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ student.gender }}</td>
                                <td class="px-4 py-3"><Badge variant="outline">{{ student.status }}</Badge></td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child><Link :href="`/students/${student.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/students/${student.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                            <DropdownMenuItem v-if="student.status !== 'suspended'" @click="openActionModal('suspend', student)"><ShieldAlert class="mr-2 h-4 w-4" />Suspend</DropdownMenuItem>
                                            <DropdownMenuItem v-else @click="openActionModal('activate', student)"><UserCheck class="mr-2 h-4 w-4" />Activate</DropdownMenuItem>
                                            <DropdownMenuItem @click="openActionModal('demote', student)"><ArrowDownCircle class="mr-2 h-4 w-4" />Demote</DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive" @click="openActionModal('delete', student)"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Subjects Assigned to {{ classroom.name }}</h2>
                        <p class="text-sm text-muted-foreground">Open the class subjects page by clicking a card. From there you can manage teacher assignment, status, and subject actions.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-muted-foreground">{{ subjectAllocations.length }} subject allocation(s)</div>
                        <Button variant="outline" as-child><Link :href="`/classes/${classroom.id}/subjects`">Open Subjects Page</Link></Button>
                    </div>
                </div>
                <div v-if="subjectAllocations.length === 0" class="rounded-xl border border-dashed p-8 text-sm text-muted-foreground">
                    No subjects have been assigned to this class yet.
                </div>
                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="allocation in subjectAllocations" :key="allocation.id" class="group relative overflow-hidden rounded-xl border bg-card transition hover:border-primary/40 hover:shadow-md">
                        <Link :href="`/classes/${classroom.id}/subjects`" class="block h-full p-5 pr-16">
                            <h3 class="text-lg font-semibold transition group-hover:text-primary">{{ allocation.subject }}</h3>
                            <p class="text-sm text-muted-foreground">{{ allocation.code }} • {{ allocation.learning_area || 'General' }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Teacher: {{ allocation.teacher }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-lg border p-3">
                                    <div class="text-muted-foreground">Type</div>
                                    <div class="mt-1 font-semibold">{{ allocation.type }}</div>
                                </div>
                                <div class="rounded-lg border p-3">
                                    <div class="text-muted-foreground">Status</div>
                                    <div class="mt-1 font-semibold">{{ allocation.is_active ? 'Active' : 'Inactive' }}</div>
                                </div>
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
                                    <DropdownMenuItem as-child><Link :href="`/classes/${classroom.id}/subjects`"><Eye class="mr-2 h-4 w-4" />Open Class Subjects</Link></DropdownMenuItem>
                                    <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${allocation.subject_id}`"><BookCopy class="mr-2 h-4 w-4" />Open Subject</Link></DropdownMenuItem>
                                    <DropdownMenuItem as-child><Link :href="`/classes/allocations?class_id=${classroom.id}&subject_id=${allocation.subject_id}`"><UserCheck class="mr-2 h-4 w-4" />Assign Teacher</Link></DropdownMenuItem>
                                    <DropdownMenuItem @click="openSubjectActionModal(allocation.is_active ? 'deactivate' : 'activate', allocation)"><component :is="allocation.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />{{ allocation.is_active ? 'Deactivate' : 'Activate' }}</DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive" @click="openSubjectActionModal('delete', allocation)"><Trash2 class="mr-2 h-4 w-4" />Delete Allocation</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeActionModal">
                <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                    <h2 class="text-lg font-semibold">{{ modalTitle }}</h2>
                    <div v-if="confirmMode === 'transfer'" class="mt-4 space-y-2">
                        <label class="text-sm font-medium">Target Class</label>
                        <select v-model="transferTargetId" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="">Select target class</option>
                            <option v-for="target in transferTargets" :key="target.id" :value="String(target.id)">{{ target.name }}</option>
                        </select>
                    </div>
                    <p v-else class="mt-2 text-sm text-muted-foreground">
                        <template v-if="confirmMode === 'promote'">Promote {{ selectedCount }} selected student{{ selectedCount === 1 ? '' : 's' }} to the next grade in the same stream.</template>
                        <template v-else-if="selectedStudent">{{ confirmMode === 'activate' ? `Activate ${selectedStudent.name}?` : confirmMode === 'delete' ? `Delete ${selectedStudent.name}?` : confirmMode === 'demote' ? `Demote ${selectedStudent.name} to the previous grade in the same stream?` : `Suspend ${selectedStudent.name}?` }}</template>
                    </p>
                    <div class="mt-6 flex justify-end gap-2">
                        <Button variant="outline" @click="closeActionModal">Cancel</Button>
                        <Button :disabled="actionForm.processing || bulkPromoteForm.processing" @click="confirmAction">Confirm</Button>
                    </div>
                </div>
            </div>

            <div v-if="subjectConfirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeSubjectActionModal">
                <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                    <h2 class="text-lg font-semibold">{{ subjectConfirmMode === 'delete' ? 'Delete subject allocation' : subjectConfirmMode === 'activate' ? 'Activate subject allocation' : 'Deactivate subject allocation' }}</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        <template v-if="selectedSubjectAllocation">
                            {{ subjectConfirmMode === 'delete'
                                ? `Delete ${selectedSubjectAllocation.subject} from ${classroom.name}?`
                                : subjectConfirmMode === 'activate'
                                    ? `Activate ${selectedSubjectAllocation.subject} for ${classroom.name}?`
                                    : `Deactivate ${selectedSubjectAllocation.subject} for ${classroom.name}?` }}
                        </template>
                    </p>
                    <div class="mt-6 flex justify-end gap-2">
                        <Button variant="outline" @click="closeSubjectActionModal">Cancel</Button>
                        <Button :variant="subjectConfirmMode === 'delete' ? 'destructive' : 'default'" :disabled="subjectActionForm.processing" @click="confirmSubjectAction">Confirm</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
