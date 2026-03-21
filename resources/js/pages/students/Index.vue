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
    Users,
    TrendingUp,
    CheckSquare,
    Square
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

const { props: pageProps } = usePage<any>();
const permissions = computed(() => pageProps.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

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

const getStatusColor = (active: boolean) => {
    return active ? 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-slate-400 text-white';
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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <Users class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-none">Student Registry</h1>
                        <p class="text-muted-foreground font-medium uppercase text-[9px] tracking-widest mt-1.5 italic">Centralized database of all active and historical enrollments</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" class="font-pulsar h-9 border-slate-200 text-[10px] font-black uppercase tracking-widest px-4 rounded-xl" @click="exportToPDF">
                        <FileText class="mr-2 h-3.5 w-3.5 text-rose-500" />
                        Export PDF
                    </Button>
                    <Button v-if="hasPermission('students.create')" variant="outline" class="font-pulsar h-9 border-slate-200 text-[10px] font-black uppercase tracking-widest px-4 rounded-xl" @click="openBulkUploadModal">
                        <Download class="mr-2 h-3.5 w-3.5 text-emerald-500" />
                        Bulk Upload
                    </Button>
                    <Button v-if="hasPermission('students.create')" @click="router.visit('/students/create')" class="bg-blue-600 hover:bg-blue-700 font-pulsar shadow-lg h-9 border-0 text-[10px] font-black uppercase tracking-widest px-6 rounded-xl text-white">
                        <Plus class="mr-2 h-3.5 w-3.5" />New Admission
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                 <div class="rounded-2xl border bg-card p-4 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-3 group hover:shadow-md transition-all">
                    <div class="rounded-lg bg-blue-500/10 p-2.5 group-hover:bg-blue-500 group-hover:text-white transition-all"><Users class="h-4 w-4 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Enrollment</p>
                        <p class="text-lg font-black text-slate-900">{{ stats.total.toLocaleString() }} Scholars</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-4 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-3 group hover:shadow-md transition-all">
                    <div class="rounded-lg bg-emerald-500/10 p-2.5 group-hover:bg-emerald-500 group-hover:text-white transition-all"><CheckCircle2 class="h-4 w-4 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Active Presence</p>
                        <p class="text-lg font-black text-emerald-600">{{ stats.active.toLocaleString() }} Pulse</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-4 shadow-sm border-l-4 border-l-sky-500 flex items-center gap-3 group hover:shadow-md transition-all">
                    <div class="rounded-lg bg-sky-500/10 p-2.5 group-hover:bg-sky-500 group-hover:text-white transition-all"><TrendingUp class="h-4 w-4 text-sky-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Growth Matrix</p>
                        <p class="text-lg font-black text-slate-900">+{{ stats.growth }}% Cluster</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-4 shadow-sm border-l-4 border-l-slate-900 flex items-center gap-3 group hover:shadow-md transition-all">
                    <div class="rounded-lg bg-slate-900/10 p-2.5 group-hover:bg-slate-900 group-hover:text-white transition-all"><ShieldAlert class="h-4 w-4 text-slate-900 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">RBAC Status</p>
                        <p class="text-lg font-black text-slate-900 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
                    <div class="flex flex-1 items-center gap-2 max-w-2xl">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                            <Input v-model="searchQuery" placeholder="Search by name, admission ID or UPI..." class="pl-9 h-10 border-slate-200 text-xs rounded-xl" />
                        </div>
                        <Button variant="outline" class="h-10 border-slate-200 font-pulsar px-4 rounded-xl text-[10px] font-black uppercase tracking-widest" @click="toggleFilters">
                            <Filter class="mr-2 h-3.5 w-3.5 text-blue-500" /> {{ showFilters ? 'Hide Advanced' : 'Show Advanced' }}
                        </Button>
                        <Button variant="ghost" class="h-10 text-slate-400 hover:text-slate-600 font-black uppercase text-[10px] tracking-widest px-4" @click="clearFilters">Reset</Button>
                    </div>

                    <div class="flex items-center gap-3">
                         <div v-if="selectedCount > 0" class="animate-in slide-in-from-right-4 duration-300">
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button class="bg-slate-900 text-white hover:bg-black font-black text-[10px] uppercase tracking-widest h-10 px-4 shadow-lg border-0 rounded-xl">
                                        Batch Actions ({{ selectedCount }}) <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 font-pulsar">
                                    <DropdownMenuItem @click="openPromoteModal"><ArrowUpCircle class="mr-2 h-4 w-4 text-emerald-500" /> Promote Logic</DropdownMenuItem>
                                    <DropdownMenuItem class="text-rose-600 font-bold" @click="openActionModal('bulkDelete')"><Trash2 class="mr-2 h-4 w-4" /> Purge Records</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                         </div>
                    </div>
                </div>

                <div v-if="showFilters" class="grid gap-4 pt-4 border-t border-slate-50 md:grid-cols-2 lg:grid-cols-5 animate-in slide-in-from-top-2 duration-300">
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Operational Status</label>
                        <select v-model="selectedStatus" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Class Alignment</label>
                        <select v-model="selectedClassId" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500">
                            <option value="">All Academic Nodes</option>
                            <option v-for="schoolClass in classes" :key="schoolClass.id" :value="String(schoolClass.id)">{{ schoolClass.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Biological Identity</label>
                        <select v-model="selectedGender" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500">
                            <option v-for="option in genderOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Residency Status</label>
                        <select v-model="selectedBoardingStatus" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500">
                            <option v-for="option in boardingOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">County Origin</label>
                        <select v-model="selectedCounty" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500">
                            <option value="">All Geographical Areas</option>
                            <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table section -->
            <div class="rounded-[1.5rem] border bg-card shadow-sm overflow-hidden border-t-4 border-t-blue-600">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b font-pulsar">
                                <th class="w-12 px-6 py-4 text-center">
                                     <button @click="toggleAllStudents" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                          <component :is="allSelected ? CheckSquare : Square" class="h-3 w-3" />
                                     </button>
                                </th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase text-slate-400 tracking-widest">Scholar Identity</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase text-slate-400 tracking-widest">Admission ID</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase text-slate-400 tracking-widest">Sex</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase text-slate-400 tracking-widest">Academic Cluster</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase text-slate-400 tracking-widest">Age</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase text-slate-400 tracking-widest">Pulse St</th>
                                <th class="px-6 py-4 text-right text-[9px] font-black uppercase text-slate-400 tracking-widest">Ops</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-xs">
                            <tr v-for="student in students.data" :key="student.id" class="group hover:bg-slate-50/70 transition-all duration-300 cursor-default">
                                <td class="px-6 py-4 text-center">
                                     <button @click="toggleStudent(student.id)" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedStudentIds.includes(student.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-200'">
                                          <component :is="selectedStudentIds.includes(student.id) ? CheckSquare : Square" class="h-3 w-3" />
                                     </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600/10 font-black text-blue-700 shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-all text-sm uppercase">
                                                {{ student.name.charAt(0) }}
                                            </div>
                                            <div v-if="student.status === 'active'" class="absolute -bottom-1 -right-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 shadow-sm"></div>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-900 group-hover:text-blue-700 transition-colors text-sm leading-none mb-1">{{ student.name }}</p>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic leading-none">{{ student.admission_date ?? 'PENDING' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[9px] font-black text-slate-900 tracking-widest bg-slate-100 px-2 py-0.5 rounded-lg border border-slate-200 uppercase shadow-sm italic">{{ student.admission_number || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="student.gender === 'Male' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-rose-50 text-rose-700 border-rose-100'" class="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full border">
                                        {{ student.gender }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                     <div class="flex flex-col">
                                         <span class="font-black text-xs text-slate-700 group-hover:text-blue-600 transition-colors">{{ student.class || 'NULL_CLUSTER' }}</span>
                                         <span class="text-[8px] font-black text-slate-400 uppercase tracking-tight italic">{{ student.status === 'active' ? 'Operational Node' : 'Suspended' }}</span>
                                     </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-900 text-sm leading-none">{{ student.age ?? '--' }}</span>
                                    <span class="text-[8px] font-black text-slate-400 uppercase ml-0.5 opacity-50 italic">yrs</span>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :class="getStatusColor(student.status === 'active')" class="rounded-full px-2 py-0 h-4 text-[7px] font-black uppercase tracking-widest border-0">
                                        {{ student.status }} Node
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 outline-none">
                                        <Button v-if="hasPermission('students.view')" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" as-child>
                                            <Link :href="`/students/${student.id}`"><Eye class="h-3.5 w-3.5" /></Link>
                                        </Button>
                                        <Button v-if="hasPermission('students.update')" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" as-child>
                                            <Link :href="`/students/${student.id}/edit`"><Edit class="h-3.5 w-3.5" /></Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg"><MoreHorizontal class="h-3.5 w-3.5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 font-pulsar">
                                                <DropdownMenuItem v-if="hasPermission('students.view')" as-child><Link :href="`/students/${student.id}`"><Eye class="mr-2 h-3.5 w-3.5 text-blue-600" /> Deep Analyzer</Link></DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('students.update')" as-child><Link :href="`/students/${student.id}/edit`"><Edit class="mr-2 h-3.5 w-3.5 text-amber-500" /> Edit Metadata</Link></DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem v-if="student.status !== 'suspended' && hasPermission('students.update')" @click="openActionModal('suspend', student)"><ShieldAlert class="mr-2 h-3.5 w-3.5 text-amber-600" /> Suspend Matrix</DropdownMenuItem>
                                                <DropdownMenuItem v-else-if="hasPermission('students.update')" @click="openActionModal('activate', student)"><CheckCircle2 class="mr-2 h-3.5 w-3.5 text-emerald-600" /> Restore Matrix</DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('students.delete')" class="text-rose-600 font-bold" @click="openActionModal('delete', student)"><Trash2 class="mr-2 h-3.5 w-3.5" /> Purge Records</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="students.data.length === 0">
                                <td colspan="8" class="px-6 py-20 text-center text-muted-foreground italic font-medium bg-slate-50/30">
                                    No scholarship records found within the current matrix parameters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Section -->
                <div class="flex flex-col gap-4 border-t bg-slate-50/50 px-6 py-4 md:flex-row md:items-center md:justify-between">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">{{ pageLabel }}</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border shadow-sm">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-tight">Node Limit:</label>
                            <select v-model="perPage" class="h-6 border-0 bg-transparent px-1 text-[10px] font-black outline-none cursor-pointer">
                                <option :value="15">15 UNIT</option>
                                <option :value="50">50 UNIT</option>
                                <option :value="100">100 UNIT</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" class="h-8 px-4 rounded-xl font-black text-[9px] uppercase border-slate-200 tracking-widest" :disabled="students.current_page <= 1" @click="applyFilters(students.current_page - 1)">
                                Previous
                            </Button>
                            <div class="h-8 min-w-[3rem] items-center justify-center rounded-xl bg-blue-600 flex font-pulsar text-[9px] font-black text-white px-3 shadow-md shadow-blue-200 uppercase tracking-widest">
                                {{ students.current_page }} / {{ students.last_page }}
                            </div>
                            <Button variant="outline" size="sm" class="h-8 px-4 rounded-xl font-black text-[9px] uppercase border-slate-200 tracking-widest" :disabled="students.current_page >= students.last_page" @click="applyFilters(students.current_page + 1)">
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Matrix Pulse -->
            <div class="mt-2 p-6 rounded-[2rem] bg-slate-950 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl border-4 border-slate-900 transition-all hover:bg-slate-900 group relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 opacity-10 group-hover:scale-110 transition-all duration-700 text-blue-500">
                     <Users class="h-48 w-48 text-white" />
                </div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center border border-white/10 group-hover:bg-blue-600 transition-all duration-500">
                         <GraduationCap class="h-6 w-6 text-blue-400 group-hover:text-white" />
                    </div>
                    <div>
                        <h3 class="font-black text-base tracking-tight text-white uppercase italic">Registry Synchronization</h3>
                        <p class="text-slate-400 text-[9px] mt-0.5 tracking-widest font-black uppercase opacity-70 leading-relaxed italic">Real-time verification of scholarship nodes and bio-integrity across the vision core matrix.</p>
                    </div>
                </div>
                <div class="flex gap-4 relative z-10">
                    <div class="flex flex-col items-end">
                         <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Matrix Pulse</span>
                         <span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em]">Operational: Stable</span>
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

