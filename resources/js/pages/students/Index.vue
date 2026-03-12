<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Download,
    Edit,
    Eye,
    Filter,
    GraduationCap,
    MoreHorizontal,
    Plus,
    Search,
    ShieldAlert,
    Trash2,
    ChevronDown,
    ChevronUp,
    ArrowUpCircle,
    FileText,
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
import BulkUploadModal from './partials/BulkUploadModal.vue';

interface StudentRow {
    id: number;
    admission_number: string | null;
    name: string;
    gender: string;
    class: string | null;
    class_id: number | null;
    status: string;
    photo: string | null;
    age: number | null;
    admission_date: string | null;
}

interface PaginatedStudents {
    data: StudentRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    students: PaginatedStudents;
    stats: {
        total: number;
        active: number;
        boys: number;
        girls: number;
        growth: number;
    };
    filters: {
        search: string;
        status: string;
        class_id: number | null;
        gender: string;
        boarding_status: string;
        county: string;
        per_page: number;
        show_filters: boolean;
    };
    classes: Array<{ id: number; name: string }>;
    counties: string[];
    statusOptions: Array<{ value: string; label: string }>;
    genderOptions: Array<{ value: string; label: string }>;
    boardingOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const page = usePage<{ flash?: { success?: string; error?: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedClassId = ref<string>(props.filters.class_id ? String(props.filters.class_id) : '');
const selectedGender = ref(props.filters.gender ?? 'all');
const selectedBoardingStatus = ref(props.filters.boarding_status ?? 'all');
const selectedCounty = ref(props.filters.county ?? '');
const showFilters = ref(props.filters.show_filters ?? true);
const perPage = ref(props.filters.per_page ?? 15);

const actionForm = useForm({});
const promotionForm = useForm<{ student_ids: number[] }>({ student_ids: [] });
const selectedStudentIds = ref<number[]>([]);
const confirmOpen = ref(false);
const promoteOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'bulkDelete'>('suspend');
const selectedStudent = ref<StudentRow | null>(null);
const bulkUploadOpen = ref(false);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (page = 1) => {
    router.get(
        '/students',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            class_id: selectedClassId.value || undefined,
            gender: selectedGender.value !== 'all' ? selectedGender.value : undefined,
            boarding_status: selectedBoardingStatus.value !== 'all' ? selectedBoardingStatus.value : undefined,
            county: selectedCounty.value || undefined,
            show_filters: showFilters.value,
            per_page: perPage.value,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['students', 'stats', 'filters', 'classes', 'counties', 'statusOptions', 'genderOptions', 'boardingOptions'],
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch([selectedStatus, selectedClassId, selectedGender, selectedBoardingStatus, selectedCounty, perPage], () => applyFilters());

const activeRate = computed(() => {
    if (!props.stats.total) return 0;
    return Math.round((props.stats.active / props.stats.total) * 100);
});

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedStatus.value !== 'all') params.set('status', selectedStatus.value);
    if (selectedClassId.value) params.set('class_id', selectedClassId.value);
    if (selectedGender.value !== 'all') params.set('gender', selectedGender.value);
    if (selectedBoardingStatus.value !== 'all') params.set('boarding_status', selectedBoardingStatus.value);
    if (selectedCounty.value) params.set('county', selectedCounty.value);
    return `/students?${params.toString()}`;
});

const pdfExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedStatus.value !== 'all') params.set('status', selectedStatus.value);
    if (selectedClassId.value) params.set('class_id', selectedClassId.value);
    if (selectedGender.value !== 'all') params.set('gender', selectedGender.value);
    if (selectedBoardingStatus.value !== 'all') params.set('boarding_status', selectedBoardingStatus.value);
    if (selectedCounty.value) params.set('county', selectedCounty.value);
    return `/students/export-pdf?${params.toString()}`;
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedClassId.value = '';
    selectedGender.value = 'all';
    selectedBoardingStatus.value = 'all';
    selectedCounty.value = '';
    applyFilters();
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
    applyFilters();
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

const openActionModal = (mode: 'suspend' | 'activate' | 'delete' | 'bulkDelete', student: StudentRow | null = null) => {
    confirmMode.value = mode;
    selectedStudent.value = student;
    confirmOpen.value = true;
};

const closeActionModal = () => {
    confirmOpen.value = false;
    selectedStudent.value = null;
};

const confirmAction = () => {
    if (confirmMode.value === 'bulkDelete') {
        actionForm.transform(() => ({
            student_ids: selectedStudentIds.value
        })).post('/students/bulk-delete', {
            preserveScroll: true,
            onSuccess: () => {
                selectedStudentIds.value = [];
                closeActionModal();
            },
        });
        return;
    }

    if (!selectedStudent.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedStudent.value.id}/suspend`, {
            preserveScroll: true,
            onSuccess: () => closeActionModal(),
        });
        return;
    }

    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedStudent.value.id}/activate`, {
            preserveScroll: true,
            onSuccess: () => closeActionModal(),
        });
        return;
    }

    actionForm.delete(`/students/${selectedStudent.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeActionModal(),
    });
};

const modalTitle = computed(() => {
    if (confirmMode.value === 'activate') return 'Activate student';
    if (confirmMode.value === 'delete') return 'Delete student';
    if (confirmMode.value === 'bulkDelete') return 'Delete selected students';
    return 'Suspend student';
});

const modalMessage = computed(() => {
    if (confirmMode.value === 'bulkDelete') return `Are you sure you want to delete ${selectedStudentIds.value.length} selected students? This action cannot be undone.`;
    if (!selectedStudent.value) return '';
    if (confirmMode.value === 'activate') return `Activate ${selectedStudent.value.name}?`;
    if (confirmMode.value === 'delete') return `Delete ${selectedStudent.value.name}? This action will remove the record from the active list.`;
    return `Suspend ${selectedStudent.value.name}?`;
});

const pageLabel = computed(() => {
    const from = props.students.from ?? 0;
    const to = props.students.to ?? 0;
    return `Showing ${from} to ${to} of ${props.students.total} students`;
});

watch(
    () => props.students.data,
    () => {
        selectedStudentIds.value = selectedStudentIds.value.filter((id) => props.students.data.some((student) => student.id === id));
    },
    { deep: true },
);

const allSelectableStudentIds = computed(() => props.students.data.filter((student) => student.status === 'active').map((student) => student.id));
const allSelected = computed(() => allSelectableStudentIds.value.length > 0 && allSelectableStudentIds.value.every((id) => selectedStudentIds.value.includes(id)));
const selectedCount = computed(() => selectedStudentIds.value.length);

const toggleAllStudents = () => {
    selectedStudentIds.value = allSelected.value ? [] : [...allSelectableStudentIds.value];
};

const toggleStudent = (studentId: number) => {
    selectedStudentIds.value = selectedStudentIds.value.includes(studentId)
        ? selectedStudentIds.value.filter((id) => id !== studentId)
        : [...selectedStudentIds.value, studentId];
};

const openPromoteModal = () => {
    if (!selectedStudentIds.value.length) return;
    promoteOpen.value = true;
};

const closePromoteModal = () => {
    promoteOpen.value = false;
};

const promoteSelectedStudents = () => {
    if (!selectedStudentIds.value.length) return;
    promotionForm.student_ids = [...selectedStudentIds.value];
    promotionForm.post('/students/promote', {
        preserveScroll: true,
        onSuccess: () => {
            selectedStudentIds.value = [];
            closePromoteModal();
        },
    });
};

const openBulkUploadModal = () => {
  bulkUploadOpen.value = true;
};

const exportToPDF = () => {
    window.location.href = pdfExportUrl.value;
};
</script>

<template>
    <Head title="Students" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <GraduationCap class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Students</h1>
                        <p class="text-muted-foreground">Manage student records and enrollments from the live database</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm" @click="exportToPDF">
                        <FileText class="mr-2 h-4 w-4" />
                        Export to PDF
                    </Button>
                    <Button variant="outline" size="sm" @click="openBulkUploadModal">
                        <Upload class="mr-2 h-4 w-4" />
                        Bulk Upload
                    </Button>
                    <Button v-if="selectedCount > 0" variant="destructive" size="sm" @click="openActionModal('bulkDelete')">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Selected ({{ selectedCount }})
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <a :href="exportUrl">
                            <Download class="mr-2 h-4 w-4" />
                            Export CSV
                        </a>
                    </Button>
                    <Button size="sm" as-child>
                        <Link href="/students/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Student
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-linear-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="text-sm text-muted-foreground">Total Students</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.total.toLocaleString() }}</div>
                    <div class="mt-1 text-xs text-green-600">{{ stats.growth }}% recent admission growth</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active.toLocaleString() }}</div>
                    <div class="mt-1 text-xs text-muted-foreground">{{ activeRate }}% of total</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-indigo-50 to-indigo-100/50 p-4 dark:from-indigo-950/50 dark:to-indigo-900/30">
                    <div class="text-sm text-muted-foreground">Boys</div>
                    <div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.boys.toLocaleString() }}</div>
                    <div class="mt-1 text-xs text-muted-foreground">Live from MySQL</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-pink-50 to-pink-100/50 p-4 dark:from-pink-950/50 dark:to-pink-900/30">
                    <div class="text-sm text-muted-foreground">Girls</div>
                    <div class="mt-1 text-3xl font-bold text-pink-600">{{ stats.girls.toLocaleString() }}</div>
                    <div class="mt-1 text-xs text-muted-foreground">Live from MySQL</div>
                </div>
            </div>
            <!-- Filters -->
            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search by name, admission no, or UPI..." class="pl-9" />
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="toggleFilters">
                        <component :is="showFilters ? ChevronUp : ChevronDown" class="mr-2 h-4 w-4" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">
                        <Filter class="mr-2 h-4 w-4" />
                        Reset
                    </Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Class</label>
                    <select v-model="selectedClassId" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option value="">All Classes</option>
                        <option v-for="schoolClass in classes" :key="schoolClass.id" :value="String(schoolClass.id)">{{ schoolClass.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Gender</label>
                    <select v-model="selectedGender" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in genderOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Boarding</label>
                    <select v-model="selectedBoardingStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in boardingOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">County</label>
                    <select v-model="selectedCounty" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option value="">All Counties</option>
                        <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                    </select>
                </div>
            </div>
            <!-- Table -->
            <div v-if="selectedCount > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                <div>
                    <p class="text-sm font-medium">{{ selectedCount }} student{{ selectedCount === 1 ? '' : 's' }} selected</p>
                    <p class="text-xs text-muted-foreground">Bulk promotion keeps the same stream and moves students to the next grade where available.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedStudentIds = []">Clear</Button>
                    <Button size="sm" @click="openPromoteModal">
                        <ArrowUpCircle class="mr-2 h-4 w-4" />
                        Promote Selected
                    </Button>
                </div>
            </div>
            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        :disabled="allSelectableStudentIds.length === 0"
                                        @change="toggleAllStudents"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Adm. No</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Gender</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Age</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.data.length === 0">
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-muted-foreground">
                                    No students found for the current filters.
                                </td>
                            </tr>
                            <tr v-for="student in students.data" :key="student.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 align-top">
                                    <input
                                        type="checkbox"
                                        :checked="selectedStudentIds.includes(student.id)"
                                        :disabled="student.status !== 'active'"
                                        @change="toggleStudent(student.id)"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-linear-to-br from-primary/20 to-primary/10 text-sm font-semibold text-primary">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ student.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ student.admission_date ?? 'No admission date' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ student.admission_number || '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="student.gender === 'Male' ? 'text-blue-600' : 'text-pink-600'">
                                        {{ student.gender }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ student.class || 'Unassigned' }}</td>
                                <td class="px-4 py-3 text-sm">{{ student.age ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <Badge :variant="student.status === 'active' ? 'default' : 'secondary'">
                                        {{ student.status }}
                                    </Badge>
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
                                                <Link :href="`/students/${student.id}`">
                                                    <Eye class="mr-2 h-4 w-4" /> View
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/students/${student.id}/edit`">
                                                    <Edit class="mr-2 h-4 w-4" /> Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="student.status !== 'suspended'"
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
                <div class="flex flex-col gap-3 border-t px-4 py-3 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-muted-foreground">{{ pageLabel }}</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-medium text-muted-foreground">Per page:</label>
                            <select v-model="perPage" class="h-8 rounded-md border bg-background px-2 text-xs">
                                <option :value="15">15</option>
                                <option :value="20">20</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                                <option :value="200">200</option>
                                <option :value="500">500</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="students.current_page <= 1"
                                @click="applyFilters(students.current_page - 1)"
                            >
                                Previous
                            </Button>
                            <span class="text-sm text-muted-foreground">
                                Page {{ students.current_page }} of {{ students.last_page }}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="students.current_page >= students.last_page"
                                @click="applyFilters(students.current_page + 1)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="confirmOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeActionModal"
        >
            <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">{{ modalTitle }}</h2>
                <p class="mt-2 text-sm text-muted-foreground">{{ modalMessage }}</p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="closeActionModal">Cancel</Button>
                    <Button
                        :variant="confirmMode === 'delete' ? 'destructive' : 'default'"
                        :disabled="actionForm.processing"
                        @click="confirmAction"
                    >
                        Confirm
                    </Button>
                </div>
            </div>
        </div>

        <div
            v-if="promoteOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closePromoteModal"
        >
            <div class="w-full max-w-lg rounded-xl border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Promote Selected Students</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ selectedCount }} selected active student{{ selectedCount === 1 ? '' : 's' }} will be moved to the next grade while keeping the same stream where a matching class exists.
                </p>
                <div class="mt-4 rounded-lg border bg-muted/30 p-4 text-sm text-muted-foreground">
                    Example: <span class="font-medium text-foreground">Grade 8 West</span> → <span class="font-medium text-foreground">Grade 9 West</span>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="closePromoteModal">Cancel</Button>
                    <Button :disabled="promotionForm.processing" @click="promoteSelectedStudents">
                        <ArrowUpCircle class="mr-2 h-4 w-4" />
                        Confirm Promotion
                    </Button>
                </div>
            </div>
        </div>
        <BulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>

<style>
/* Fix class name issues */
.bg-gradient-to-br {
  @apply bg-linear-to-br;
}
</style>

