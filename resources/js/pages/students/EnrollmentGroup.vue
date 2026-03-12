<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    MoreHorizontal,
    Search,
    ShieldAlert,
    Trash2,
    Users,
    AlertTriangle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface GroupInfo {
    class_id: number;
    class_name: string;
    grade_name: string | null;
    stream_name: string | null;
    academic_year: string | null;
    total_students: number;
    active_students: number;
}

interface GroupStudentRow {
    enrollment_id: number;
    student_id: number;
    student_name: string;
    admission_number: string | null;
    student_status: string | null;
    enrollment_status: string;
    enrollment_type: string;
    enrollment_date: string | null;
    end_date: string | null;
    roll_number: string | null;
    notes: string | null;
    enrolled_by: string | null;
    academic_year: string | null;
    academic_term: string | null;
    can_demote: boolean;
}

interface Props {
    group: GroupInfo;
    students: GroupStudentRow[];
    filters: {
        search: string;
        student_status: string;
        enrollment_status: string;
    };
    studentStatusOptions: Array<{ value: string; label: string }>;
    enrollmentStatusOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const page = usePage<{ flash?: { success?: string; error?: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
    { title: props.group.class_name, href: `/students/enrollments/groups/${props.group.class_id}` },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStudentStatus = ref(props.filters.student_status ?? 'all');
const selectedEnrollmentStatus = ref(props.filters.enrollment_status ?? 'all');
const showFilters = ref(true);
const actionForm = useForm({});
const confirmOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'demote'>('suspend');
const selectedStudent = ref<GroupStudentRow | null>(null);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        `/students/enrollments/groups/${props.group.class_id}`,
        {
            search: searchQuery.value || undefined,
            student_status: selectedStudentStatus.value !== 'all' ? selectedStudentStatus.value : undefined,
            enrollment_status: selectedEnrollmentStatus.value !== 'all' ? selectedEnrollmentStatus.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});
watch([selectedStudentStatus, selectedEnrollmentStatus], () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedStudentStatus.value = 'all';
    selectedEnrollmentStatus.value = 'all';
    applyFilters();
};

const selectedEnrollmentIds = ref<number[]>([]);
const isAllSelected = computed(() => props.students.length > 0 && selectedEnrollmentIds.value.length === props.students.length);

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedEnrollmentIds.value = [];
    } else {
        selectedEnrollmentIds.value = props.students.map((s) => s.enrollment_id);
    }
};

const toggleSelection = (id: number) => {
    const index = selectedEnrollmentIds.value.indexOf(id);
    if (index === -1) {
        selectedEnrollmentIds.value.push(id);
    } else {
        selectedEnrollmentIds.value.splice(index, 1);
    }
};

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

const openActionModal = (mode: 'suspend' | 'activate' | 'delete' | 'demote', student: GroupStudentRow) => {
    confirmMode.value = mode;
    selectedStudent.value = student;
    confirmOpen.value = true;
};

const bulkDeleteOpen = ref(false);
const deleteSelected = () => {
    if (selectedEnrollmentIds.value.length === 0) return;
    bulkDeleteOpen.value = true;
};

const confirmBulkDelete = () => {
    actionForm
        .transform(() => ({
            enrollment_ids: selectedEnrollmentIds.value,
        }))
        .post('/students/enrollments/bulk-delete', {
            preserveScroll: true,
            onSuccess: () => {
                bulkDeleteOpen.value = false;
                selectedEnrollmentIds.value = [];
            },
        });
};

const closeActionModal = () => {
    confirmOpen.value = false;
    selectedStudent.value = null;
};

const confirmAction = () => {
    if (!selectedStudent.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedStudent.value.student_id}/suspend`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedStudent.value.student_id}/activate`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    if (confirmMode.value === 'demote') {
        actionForm.patch(`/students/${selectedStudent.value.student_id}/demote`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    actionForm.delete(`/students/${selectedStudent.value.student_id}`, { preserveScroll: true, onSuccess: closeActionModal });
};

const modalTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate': return 'Activate student';
        case 'delete': return 'Delete student';
        case 'demote': return 'Demote student';
        default: return 'Suspend student';
    }
});

const modalMessage = computed(() => {
    if (!selectedStudent.value) return '';
    switch (confirmMode.value) {
        case 'activate': return `Activate ${selectedStudent.value.student_name}?`;
        case 'delete': return `Delete ${selectedStudent.value.student_name}? This removes the student from active records.`;
        case 'demote': return `Demote ${selectedStudent.value.student_name} to the previous grade while keeping the same stream?`;
        default: return `Suspend ${selectedStudent.value.student_name}?`;
    }
});
</script>

<template>
    <Head :title="group.class_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
                        <Users class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ group.class_name }}</h1>
                        <p class="text-muted-foreground">
                            {{ group.grade_name || 'Unknown Grade' }}<span v-if="group.stream_name"> • {{ group.stream_name }}</span><span v-if="group.academic_year"> • {{ group.academic_year }}</span>
                        </p>
                    </div>
            </div>
            <div class="flex items-center gap-2">
                <Button v-if="selectedEnrollmentIds.length > 0" variant="destructive" size="sm" @click="deleteSelected">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete Selected ({{ selectedEnrollmentIds.length }})
                </Button>
                <Button variant="outline" as-child>
                    <Link href="/students/enrollments">Back to Groups</Link>
                </Button>
            </div>
        </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Total Students</div>
                    <div class="mt-1 text-3xl font-bold">{{ group.total_students }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active Students</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ group.active_students }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search student or admission number..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">Reset</Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Student Status</label>
                    <select v-model="selectedStudentStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in studentStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Enrollment Status</label>
                    <select v-model="selectedEnrollmentStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in enrollmentStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="w-[50px] px-4 py-3">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        :checked="isAllSelected"
                                        @change="toggleSelectAll"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Adm. No</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Type</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Enrollment</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Date</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.length === 0">
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-muted-foreground">No students found in this group.</td>
                            </tr>
                            <tr v-for="student in students" :key="student.enrollment_id" class="border-b transition-colors hover:bg-muted/50" :class="{ 'bg-primary/5': selectedEnrollmentIds.includes(student.enrollment_id) }">
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        :checked="selectedEnrollmentIds.includes(student.enrollment_id)"
                                        @change="toggleSelection(student.enrollment_id)"
                                    />
                                </td>
                                <td class="px-4 py-3 font-medium">{{ student.student_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ student.admission_number || '—' }}</td>
                                <td class="px-4 py-3 text-sm capitalize">{{ student.enrollment_type.replace('_', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <Badge :variant="student.enrollment_status === 'active' ? 'default' : 'secondary'">{{ student.enrollment_status }}</Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="student.student_status === 'active' ? 'default' : 'secondary'">{{ student.student_status }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ student.enrollment_date || '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/students/${student.student_id}`">
                                                    <Eye class="mr-2 h-4 w-4" /> View
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/students/${student.student_id}/edit`">
                                                    <Edit class="mr-2 h-4 w-4" /> Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="student.student_status !== 'suspended'"
                                                @click="openActionModal('suspend', student)"
                                            >
                                                <ShieldAlert class="mr-2 h-4 w-4" /> Suspend
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-else
                                                @click="openActionModal('activate', student)"
                                            >
                                                <CheckCircle2 class="mr-2 h-4 w-4" /> Activate
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                :disabled="!student.can_demote"
                                                @click="openActionModal('demote', student)"
                                            >
                                                <ArrowDownCircle class="mr-2 h-4 w-4" /> Demote
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive" @click="openActionModal('delete', student)">
                                                <Trash2 class="mr-2 h-4 w-4" /> Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeActionModal">
            <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                <div class="flex items-center gap-3 text-destructive mb-2">
                    <AlertTriangle class="h-5 w-5" />
                    <h2 class="text-lg font-semibold">{{ modalTitle }}</h2>
                </div>
                <p class="mt-2 text-sm text-muted-foreground">{{ modalMessage }}</p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="closeActionModal">Cancel</Button>
                    <Button :variant="confirmMode === 'delete' ? 'destructive' : 'default'" :disabled="actionForm.processing" @click="confirmAction">
                        Confirm
                    </Button>
                </div>
            </div>
        </div>

        <!-- Bulk Delete Modal -->
        <div v-if="bulkDeleteOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="bulkDeleteOpen = false">
            <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                <div class="flex items-center gap-3 text-destructive mb-2">
                    <AlertTriangle class="h-6 w-6" />
                    <h2 class="text-xl font-bold">Confirm Bulk Removal</h2>
                </div>
                <p class="mt-3 text-sm text-muted-foreground leading-relaxed">
                    Are you sure you want to remove <span class="font-bold text-foreground">{{ selectedEnrollmentIds.length }}</span> selected students from this enrollment group? 
                    This action will delete their enrollment records for this class.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <Button variant="outline" @click="bulkDeleteOpen = false">Cancel</Button>
                    <Button variant="destructive" :disabled="actionForm.processing" @click="confirmBulkDelete">
                        <Trash2 v-if="!actionForm.processing" class="mr-2 h-4 w-4" />
                        <span v-else class="mr-2 h-4 w-4 animate-spin">⌛</span>
                        Yes, Remove Selected
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
