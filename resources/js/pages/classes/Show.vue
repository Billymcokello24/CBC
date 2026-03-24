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
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface LearnerRow {
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
        learners_count: number;
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
    learners: LearnerRow[];
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
const selectedLearnerIds = ref<number[]>([]);
const transferTargetId = ref('');
const showFilters = ref(true);
const actionForm = useForm({});
const bulkPromoteForm = useForm<{ learner_ids: number[] }>({ learner_ids: [] });
const bulkTransferForm = useForm<{ learner_ids: number[]; target_class_id: number | null }>({ learner_ids: [], target_class_id: null });
const confirmOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'demote' | 'transfer' | 'promote'>('suspend');
const selectedLearner = ref<LearnerRow | null>(null);
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
    () => props.learners,
    () => {
        selectedLearnerIds.value = selectedLearnerIds.value.filter((id) => props.learners.some((learner) => learner.id === id));
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedGender.value = 'all';
    applyFilters();
};

const allSelected = computed(() => props.learners.length > 0 && props.learners.every((learner) => selectedLearnerIds.value.includes(learner.id)));
const selectedCount = computed(() => selectedLearnerIds.value.length);

const toggleAllLearners = () => {
    selectedLearnerIds.value = allSelected.value ? [] : props.learners.map((learner) => learner.id);
};

const toggleLearner = (learnerId: number) => {
    selectedLearnerIds.value = selectedLearnerIds.value.includes(learnerId)
        ? selectedLearnerIds.value.filter((id) => id !== learnerId)
        : [...selectedLearnerIds.value, learnerId];
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

const openActionModal = (mode: 'suspend' | 'activate' | 'delete' | 'demote' | 'transfer' | 'promote', learner: LearnerRow | null = null) => {
    confirmMode.value = mode;
    selectedLearner.value = learner;
    confirmOpen.value = true;
};

const closeActionModal = () => {
    confirmOpen.value = false;
    selectedLearner.value = null;
};

const confirmAction = () => {
    if (confirmMode.value === 'promote') {
        bulkPromoteForm.learner_ids = [...selectedLearnerIds.value];
        bulkPromoteForm.post('/students/promote', { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    if (confirmMode.value === 'transfer') {
        if (!selectedLearnerIds.value.length || !transferTargetId.value) return;
        bulkTransferForm.learner_ids = [...selectedLearnerIds.value];
        bulkTransferForm.target_class_id = Number(transferTargetId.value);
        selectedLearnerIds.value.forEach((learnerId) => {
            actionForm.transform(() => ({
                target_class_id: Number(transferTargetId.value),
            })).patch(`/students/${learnerId}/transfer`, {
                preserveScroll: true,
            });
        });
        closeActionModal();
        selectedLearnerIds.value = [];
        return;
    }

    if (!selectedLearner.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedLearner.value.id}/suspend`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }
    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedLearner.value.id}/activate`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }
    if (confirmMode.value === 'demote') {
        actionForm.patch(`/students/${selectedLearner.value.id}/demote`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }
    actionForm.delete(`/students/${selectedLearner.value.id}`, { preserveScroll: true, onSuccess: closeActionModal });
};

const modalTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate': return 'Activate';
        case 'delete': return 'Delete';
        case 'demote': return 'Demote';
        case 'transfer': return 'Transfer Learners';
        case 'promote': return 'Promote Learners';
        default: return 'Suspend';
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
        <div v-if="showToast && flashSuccess" class="fixed right-8 top-8 z-50 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-white px-6 py-4 shadow-2xl shadow-blue-900/10">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-lg shadow-emerald-100">
                    <CheckCircle2 class="h-4 w-4" />
                </div>
                <span class="text-[11px] font-black uppercase tracking-tight text-slate-900 italic font-pulsar">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-8 p-8 mt-2 w-full animate-in fade-in duration-500">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-8">
                <div class="flex items-center gap-5">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-md transition-transform hover:scale-105 duration-300">
                        <School class="h-7 w-7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900 leading-none">{{ classroom.name }}</h1>
                            <Badge variant="outline" class="rounded-xl px-3 py-1 h-6 text-[10px] font-bold uppercase tracking-tight border-blue-200 text-blue-600 bg-blue-50/50">{{ classroom.code }}</Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-2 flex items-center gap-2">
                            <Layers class="h-4 w-4 text-blue-500" />
                            {{ classroom.grade || 'No Grade' }} • <span class="bg-blue-50 px-2 py-0.5 rounded-lg text-blue-600 border border-blue-100/50">{{ classroom.stream || 'General' }}</span> • Year: {{ classroom.academic_year || 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-11 border-slate-200 text-xs font-medium px-6 rounded-xl hover:bg-slate-50 transition-all">
                        <Link href="/classes">Back to Classes</Link>
                    </Button>
                    <Button as-child class="bg-slate-900 hover:bg-black shadow-sm h-11 px-8 rounded-xl text-white transition-all transform hover:-translate-y-0.5">
                        <Link :href="`/classes/${classroom.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Class</Link>
                    </Button>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Identification</p>
                    <p class="text-xl font-bold text-slate-900 leading-none">{{ classroom.code }}</p>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Capacity</p>
                    <p class="text-xl font-bold text-blue-600 leading-none">{{ classroom.capacity ?? '—' }} <span class="text-[10px] text-slate-400 ml-1">LEARNERS</span></p>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Active Learners</p>
                    <p class="text-xl font-bold text-indigo-600 leading-none">{{ classroom.learners_count }} <span class="text-[10px] text-slate-400 ml-1">ACT</span></p>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Class Teacher</p>
                    <div class="flex flex-col gap-1 truncate">
                         <p class="text-xs font-bold text-slate-900 leading-none truncate">{{ classroom.teacher || 'Unassigned' }}</p>
                         <p class="text-[9px] font-medium text-slate-400 truncate">{{ classroom.teacher_email || 'No Email' }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Status</p>
                    <div class="flex items-center gap-2 pt-1">
                        <div class="h-2 w-2 rounded-full" :class="classroom.is_active ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                        <span class="text-xs font-bold" :class="classroom.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ classroom.is_active ? 'Active' : 'Draft' }}</span>
                    </div>
                </div>
            </div>

            <!-- Learner List (Students) -->
            <div class="rounded-2xl border bg-white p-8 shadow-sm overflow-hidden relative group">
                <div class="relative z-10 mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md">
                                <Users class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">Learners</h2>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1.5">Manage class learners.</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Capacity Usage</span>
                            <span class="text-sm font-bold text-slate-900">{{ classroom.utilization }}%</span>
                        </div>
                        <div class="w-64 h-2 rounded-full bg-slate-100 overflow-hidden shadow-inner">
                            <div class="h-full rounded-full transition-all duration-1000 shadow-sm" :class="classroom.utilization > 90 ? 'bg-rose-500' : 'bg-blue-600'" :style="{ width: `${classroom.utilization}%` }"></div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="flex flex-col gap-4 mb-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="relative flex-1 md:max-w-md">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search by name or number..." class="pl-9 h-11 border-slate-200 text-sm rounded-xl" />
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" class="h-11 rounded-xl border-slate-200 text-xs font-medium px-6 hover:bg-slate-50 transition-all" @click="showFilters = !showFilters">
                                <Filter class="mr-2 h-4 w-4" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                            </Button>
                            <Button variant="ghost" class="h-11 text-slate-500 hover:text-blue-600 font-medium text-xs px-4" @click="clearFilters">Reset Filters</Button>
                        </div>
                    </div>

                    <div v-if="showFilters" class="grid gap-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-6 animate-in slide-in-from-top-4 duration-300 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Status</label>
                            <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-xs font-bold focus:ring-blue-600 transition-all outline-none shadow-sm">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Gender</label>
                            <select v-model="selectedGender" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-xs font-bold focus:ring-blue-600 transition-all outline-none shadow-sm">
                                <option v-for="option in genderOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedCount > 0" class="mb-6 flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50/50 p-5 shadow-sm animate-in zoom-in-95 duration-200">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-white shadow-md">
                            <Users class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ selectedCount }} Learner{{ selectedCount === 1 ? '' : 's' }} Selected</p>
                            <p class="text-xs text-blue-600 font-medium">Bulk Actions</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" class="h-10 text-slate-500 hover:text-slate-700 font-medium text-sm px-6" @click="selectedLearnerIds = []">Cancel</Button>
                        <Button variant="outline" class="h-10 border-blue-200 text-blue-600 font-bold text-xs px-6 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" @click="openActionModal('transfer')">
                            <ArrowRightLeft class="mr-2 h-4 w-4" />Transfer
                        </Button>
                        <Button class="bg-slate-900 hover:bg-black text-white font-medium text-xs h-10 px-8 rounded-xl shadow-sm border-0" @click="openActionModal('promote')">
                            <ArrowUpCircle class="mr-2 h-4 w-4" />Promote
                        </Button>
                    </div>
                </div>

                <!-- Learners Table -->
                <div class="rounded-2xl border bg-white overflow-hidden shadow-sm border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="w-16 px-6 py-5 text-center"><Checkbox :checked="allSelected" @update:checked="toggleAllLearners" class="rounded-md border-slate-300" /></th>
                                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Full Name</th>
                                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Admission Number</th>
                                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Gender</th>
                                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Status</th>
                                    <th class="px-6 py-5 text-right text-xs font-bold uppercase tracking-tight text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-if="learners.length === 0">
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-4">
                                            <div class="rounded-full bg-slate-50 p-6 shadow-inner"><Users class="h-10 w-10 text-slate-200" /></div>
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">No learners found in this class.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="learner in learners" :key="learner.id" class="group transition-all hover:bg-blue-50/20">
                                    <td class="px-6 py-5 text-center"><Checkbox :checked="selectedLearnerIds.includes(learner.id)" @update:checked="toggleLearner(learner.id)" class="rounded-md border-slate-300 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" /></td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-900 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">{{ learner.name.charAt(0) }}</div>
                                            <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-none truncate max-w-[200px]">{{ learner.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-xs font-bold text-slate-600 uppercase tracking-tight">{{ learner.admission_number || '—' }}</td>
                                    <td class="px-6 py-5">
                                        <span class="text-xs font-bold text-slate-600 uppercase">{{ learner.gender }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <Badge variant="secondary" class="rounded-xl px-2.5 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 border-none uppercase">{{ learner.status }}</Badge>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
                                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/students/${learner.id}`"><Eye class="mr-2 h-4 w-4 text-blue-500" /> View</Link></DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/students/${learner.id}/edit`"><Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit</Link></DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" v-if="learner.status !== 'suspended'" @click="openActionModal('suspend', learner)"><ShieldAlert class="mr-2 h-4 w-4 text-rose-500" /> Suspend</DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" v-else @click="openActionModal('activate', learner)"><UserCheck class="mr-2 h-4 w-4 text-emerald-500" /> Activate</DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" @click="openActionModal('demote', learner)"><ArrowDownCircle class="mr-2 h-4 w-4 text-amber-600" /> Demote</DropdownMenuItem>
                                                <DropdownMenuItem class="text-rose-600 font-bold rounded-lg" @click="openActionModal('delete', learner)"><Trash2 class="mr-2 h-4 w-4" /> Delete</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subjects & Teachers -->
            <div class="rounded-2xl border bg-white p-8 shadow-sm overflow-hidden relative group">
                <div class="relative z-10 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md">
                                <BookCopy class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">Subjects & Teachers</h2>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1.5">Manage subject allocations and teachers for this class.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none">Subjects Count</p>
                            <p class="text-sm font-bold text-slate-900 mt-1">{{ subjectAllocations.length }} Total</p>
                        </div>
                        <Button variant="outline" as-child class="h-11 rounded-xl border-slate-200 text-xs font-medium px-8 hover:bg-slate-50 transition-all">
                            <Link :href="`/classes/${classroom.id}/subjects`">Manage Subjects</Link>
                        </Button>
                    </div>
                </div>

                <div v-if="subjectAllocations.length === 0" class="rounded-2xl border-2 border-dashed border-slate-100 p-16 text-center">
                    <p class="text-sm font-medium text-slate-400">No subjects assigned to this class yet.</p>
                </div>
                <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="allocation in subjectAllocations" :key="allocation.id" class="group/sub relative overflow-hidden rounded-2xl border bg-white p-6 transition-all hover:shadow-lg border-slate-100 hover:border-emerald-200">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 group-hover/sub:text-emerald-600 transition-colors uppercase truncate leading-tight">{{ allocation.subject }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 opacity-80">{{ allocation.code }} • {{ allocation.learning_area || 'Core' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-2.5 text-slate-400 shadow-sm border border-slate-100 group-hover/sub:bg-emerald-600 group-hover/sub:text-white transition-all font-bold text-xs">L</div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-slate-50/50 rounded-xl border border-slate-100 mb-5 group-hover/sub:bg-blue-50/30 transition-colors">
                            <div class="h-8 w-8 rounded-lg bg-white flex items-center justify-center border border-slate-100 shadow-sm">
                                <UserCheck class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1">Subject Teacher</p>
                                <p class="text-[10px] font-bold text-slate-900 uppercase truncate leading-none pt-0.5">{{ allocation.teacher }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-2">
                            <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Subject Type</p>
                                <p class="text-[10px] font-bold text-slate-900 uppercase">{{ allocation.type }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status</p>
                                <div class="flex items-center gap-1.5 pt-0.5">
                                    <div class="h-1.5 w-1.5 rounded-full" :class="allocation.is_active ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                    <p class="text-[10px] font-bold uppercase text-slate-900" :class="allocation.is_active ? '' : 'text-slate-400'">{{ allocation.is_active ? 'Active' : 'Halted' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute right-4 top-4 z-10 opacity-0 group-hover/sub:opacity-100 transition-opacity">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 bg-white/80 backdrop-blur rounded-lg shadow-sm border border-slate-100">
                                        <MoreHorizontal class="h-4 w-4 text-emerald-600" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
                                    <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/classes/${classroom.id}/subjects`"><Eye class="mr-2 h-4 w-4 text-blue-500" /> Manage Allocation</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/curriculum/subjects/${allocation.subject_id}`"><BookCopy class="mr-2 h-4 w-4 text-indigo-500" /> Subject Details</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/classes/allocations?class_id=${classroom.id}&subject_id=${allocation.subject_id}`"><UserCheck class="mr-2 h-4 w-4 text-emerald-600" /> Assign Teacher</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg" @click="openSubjectActionModal(allocation.is_active ? 'deactivate' : 'activate', allocation)"><component :is="allocation.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" :class="allocation.is_active ? 'text-amber-500' : 'text-emerald-500'" />{{ allocation.is_active ? 'Deactivate' : 'Activate' }}</DropdownMenuItem>
                                    <DropdownMenuItem class="text-rose-600 font-bold rounded-lg" @click="openSubjectActionModal('delete', allocation)"><Trash2 class="mr-2 h-4 w-4" /> Remove Assignment</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Modals -->
            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="confirmOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-6 backdrop-blur-[2px]" @click.self="closeActionModal">
                    <div class="w-full max-w-[440px] rounded-3xl border-0 bg-white p-8 shadow-2xl animate-in zoom-in-95 duration-300 relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg mb-6">
                                <component :is="confirmMode === 'delete' ? Trash2 : confirmMode === 'promote' ? ArrowUpCircle : confirmMode === 'transfer' ? ArrowRightLeft : ShieldAlert" class="h-6 w-6" />
                            </div>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2 leading-none uppercase">{{ modalTitle }}</h2>

                            <div v-if="confirmMode === 'transfer'" class="mt-6 space-y-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Target Class</label>
                                    <select v-model="transferTargetId" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-xs font-bold focus:ring-blue-600 transition-all outline-none shadow-sm">
                                        <option value="">Select a class</option>
                                        <option v-for="target in transferTargets" :key="target.id" :value="String(target.id)">{{ target.name }}</option>
                                    </select>
                                </div>
                                <div class="rounded-xl bg-blue-50 p-4 border border-blue-100">
                                     <p class="text-xs font-medium text-blue-700 leading-relaxed uppercase">
                                         Transferring learners will move them to the selected class for the current academic session.
                                     </p>
                                </div>
                            </div>
                             <div v-else class="mt-4">
                                <p class="text-sm font-medium text-slate-500 leading-relaxed mb-4">
                                    <template v-if="confirmMode === 'promote'">Are you sure you want to promote the selected {{ selectedCount }} learners to the next grade level?</template>
                                    <template v-else-if="selectedLearner">{{ confirmMode === 'activate' ? `Are you sure you want to activate learner [${selectedLearner.name}]?` : confirmMode === 'delete' ? `Are you sure you want to permanently delete learner [${selectedLearner.name}]?` : confirmMode === 'demote' ? `Are you sure you want to demote learner [${selectedLearner.name}]?` : `Are you sure you want to suspend learner [${selectedLearner.name}]?` }}</template>
                                </p>
                                <div v-if="confirmMode === 'delete'" class="rounded-xl bg-rose-50 border border-rose-100 p-4">
                                    <p class="text-xs font-bold text-rose-600 uppercase leading-relaxed text-center">Warning: This action cannot be undone.</p>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end gap-3">
                                <Button variant="ghost" class="h-12 rounded-xl text-slate-400 hover:text-slate-600 font-bold uppercase text-[10px] tracking-widest px-8" @click="closeActionModal">Cancel</Button>
                                <Button :variant="confirmMode === 'delete' ? 'destructive' : 'default'" class="h-12 rounded-xl min-w-[140px] font-bold uppercase text-[10px] tracking-widest shadow-lg transition-all border-0" :class="confirmMode === 'delete' ? 'bg-rose-600' : 'bg-slate-900'" :disabled="actionForm.processing || bulkPromoteForm.processing" @click="confirmAction">
                                    {{ confirmMode === 'delete' ? 'Delete' : 'Confirm' }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="subjectConfirmOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-6 backdrop-blur-[2px]" @click.self="closeSubjectActionModal">
                    <div class="w-full max-w-[440px] rounded-3xl border-0 bg-white p-8 shadow-2xl animate-in zoom-in-95 duration-300 relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg mb-6">
                                <CheckCircle2 class="h-6 w-6" />
                            </div>
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2 leading-none uppercase">
                                {{ subjectConfirmMode === 'delete' ? 'Remove Allocation' : subjectConfirmMode === 'activate' ? 'Activate Subject' : 'Deactivate Subject' }}
                            </h2>
                            <p class="text-sm font-medium text-slate-500 leading-relaxed mb-6">
                                <template v-if="selectedSubjectAllocation">
                                    {{ subjectConfirmMode === 'delete'
                                        ? `Are you sure you want to remove [${selectedSubjectAllocation.subject}] from this class?`
                                        : subjectConfirmMode === 'activate'
                                            ? `Are you sure you want to activate [${selectedSubjectAllocation.subject}] for this class?`
                                            : `Are you sure you want to deactivate [${selectedSubjectAllocation.subject}] for this class?` }}
                                </template>
                            </p>
                            <div class="mt-8 flex justify-end gap-3">
                                <Button variant="ghost" class="h-12 rounded-xl text-slate-400 hover:text-slate-600 font-bold uppercase text-[10px] tracking-widest px-8" @click="closeSubjectActionModal">Cancel</Button>
                                <Button :variant="subjectConfirmMode === 'delete' ? 'destructive' : 'default'" class="h-12 rounded-xl min-w-[140px] font-bold uppercase text-[10px] tracking-widest shadow-lg transition-all border-0" :class="subjectConfirmMode === 'delete' ? 'bg-rose-600' : 'bg-emerald-600'" :disabled="subjectActionForm.processing" @click="confirmSubjectAction">
                                    {{ subjectConfirmMode === 'delete' ? 'Remove' : 'Confirm' }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>
