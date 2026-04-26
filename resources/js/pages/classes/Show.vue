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
    Home,
    ChevronRight,
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
const bulkTransferForm = useForm<{
    learner_ids: number[];
    target_class_id: number | null;
}>({ learner_ids: [], target_class_id: null });
const confirmOpen = ref(false);
const confirmMode = ref<
    'suspend' | 'activate' | 'delete' | 'demote' | 'transfer' | 'promote'
>('suspend');
const selectedLearner = ref<LearnerRow | null>(null);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const subjectActionForm = useForm({});
const subjectConfirmOpen = ref(false);
const subjectConfirmMode = ref<'activate' | 'deactivate' | 'delete'>(
    'deactivate',
);
const selectedSubjectAllocation = ref<
    (typeof props.subjectAllocations)[number] | null
>(null);

const applyFilters = () => {
    router.get(
        `/classes/${props.classroom.id}`,
        {
            search: searchQuery.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
            gender:
                selectedGender.value !== 'all'
                    ? selectedGender.value
                    : undefined,
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
    debounceTimer = setTimeout(() => applyFilters(), 300);
});
watch([selectedStatus, selectedGender], () => applyFilters());

watch(
    () => props.learners,
    () => {
        selectedLearnerIds.value = selectedLearnerIds.value.filter((id) =>
            props.learners.some((learner) => learner.id === id),
        );
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedGender.value = 'all';
    applyFilters();
};

const allSelected = computed(
    () =>
        props.learners.length > 0 &&
        props.learners.every((learner) =>
            selectedLearnerIds.value.includes(learner.id),
        ),
);
const selectedCount = computed(() => selectedLearnerIds.value.length);

const toggleAllLearners = () => {
    selectedLearnerIds.value = allSelected.value
        ? []
        : props.learners.map((learner) => learner.id);
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

const openActionModal = (
    mode: 'suspend' | 'activate' | 'delete' | 'demote' | 'transfer' | 'promote',
    learner: LearnerRow | null = null,
) => {
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
        bulkPromoteForm.post('/students/promote', {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }

    if (confirmMode.value === 'transfer') {
        if (!selectedLearnerIds.value.length || !transferTargetId.value) return;
        bulkTransferForm.learner_ids = [...selectedLearnerIds.value];
        bulkTransferForm.target_class_id = Number(transferTargetId.value);
        selectedLearnerIds.value.forEach((learnerId) => {
            actionForm
                .transform(() => ({
                    target_class_id: Number(transferTargetId.value),
                }))
                .patch(`/students/${learnerId}/transfer`, {
                    preserveScroll: true,
                });
        });
        closeActionModal();
        selectedLearnerIds.value = [];
        return;
    }

    if (!selectedLearner.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedLearner.value.id}/suspend`, {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }
    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedLearner.value.id}/activate`, {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }
    if (confirmMode.value === 'demote') {
        actionForm.patch(`/students/${selectedLearner.value.id}/demote`, {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }
    actionForm.delete(`/students/${selectedLearner.value.id}`, {
        preserveScroll: true,
        onSuccess: closeActionModal,
    });
};

const modalTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate':
            return 'Activate';
        case 'delete':
            return 'Delete';
        case 'demote':
            return 'Demote';
        case 'transfer':
            return 'Transfer Learners';
        case 'promote':
            return 'Promote Learners';
        default:
            return 'Suspend';
    }
});

const openSubjectActionModal = (
    mode: 'activate' | 'deactivate' | 'delete',
    allocation: (typeof props.subjectAllocations)[number],
) => {
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
        subjectActionForm.delete(
            `/classes/allocations/${selectedSubjectAllocation.value.id}`,
            {
                preserveScroll: true,
                onSuccess: closeSubjectActionModal,
            },
        );
        return;
    }

    subjectActionForm
        .transform(() => ({
            teacher_id: selectedSubjectAllocation.value?.teacher_id,
            subject_id: selectedSubjectAllocation.value?.subject_id,
            class_id: props.classroom.id,
            academic_year_id: selectedSubjectAllocation.value?.academic_year_id,
            is_primary_teacher:
                selectedSubjectAllocation.value?.is_primary_teacher,
            is_active: subjectConfirmMode.value === 'activate',
        }))
        .put(`/classes/allocations/${selectedSubjectAllocation.value.id}`, {
            preserveScroll: true,
            onSuccess: closeSubjectActionModal,
        });
};
</script>

<template>
    <Head :title="classroom.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            v-if="showToast && flashSuccess"
            class="fixed top-8 right-8 z-50 animate-in duration-500 fade-in slide-in-from-top-4"
        >
            <div
                class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-white px-6 py-4 shadow-lg shadow-blue-900/10"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-lg shadow-emerald-100"
                >
                    <CheckCircle2 class="h-4 w-4" />
                </div>
                <span
                    class="font-pulsar text-sm font-bold tracking-tight text-slate-900 uppercase"
                    >{{ flashSuccess }}</span
                >
            </div>
        </div>

        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 border-b border-sidebar-border pb-8 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex items-start gap-4">
                    <Button variant="outline" size="icon" class="mt-1 hidden md:flex h-8 w-8 shrink-0 rounded-xl md:h-10 md:w-10" as-child>
                        <Link href="/classes"><ArrowLeft class="h-4 w-4 md:h-5 md:w-5" /></Link>
                    </Button>
                    <div class="flex flex-col gap-1">
                        <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                            <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                            <span class="font-medium tracking-tight text-foreground uppercase">Classes</span>
                            <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                            <span class="font-medium tracking-tight text-foreground uppercase">{{ classroom.name }}</span>
                        </div>
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                            <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                                {{ classroom.name }}
                            </h1>
                            <Badge variant="outline" class="h-6 w-fit rounded-xl border-blue-200 bg-blue-50/50 px-3 py-1 text-xs font-bold tracking-tight text-blue-600 uppercase">
                                {{ classroom.code }}
                            </Badge>
                        </div>
                        <p class="mt-2 flex items-center gap-2 text-sm text-muted-foreground">
                            <School class="h-4 w-4 text-blue-500" />
                            {{ classroom.grade || 'No Grade' }} •
                            <span class="rounded-lg border border-blue-100/50 bg-blue-50 px-2 py-0.5 text-blue-600">
                                {{ classroom.stream || 'General' }}
                            </span>
                            • Year: {{ classroom.academic_year || 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        as-child
                        class="h-11 rounded-xl border-slate-200 px-6 text-xs font-medium transition-all hover:bg-slate-50"
                    >
                        <Link href="/classes">Back to Classes</Link>
                    </Button>
                    <Button
                        as-child
                        class="h-11 transform rounded-xl bg-slate-900 px-8 text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-black"
                    >
                        <Link :href="`/classes/${classroom.id}/edit`"
                            ><Edit class="mr-2 h-4 w-4" />Edit Class</Link
                        >
                    </Button>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div
                    class="rounded-xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-3 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Identification
                    </p>
                    <p class="text-xl leading-none font-bold text-slate-900">
                        {{ classroom.code }}
                    </p>
                </div>
                <div
                    class="rounded-xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-3 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Capacity
                    </p>
                    <p class="text-xl leading-none font-bold text-blue-600">
                        {{ classroom.capacity ?? '—' }}
                        <span class="ml-1 text-xs text-slate-400"
                            >LEARNERS</span
                        >
                    </p>
                </div>
                <div
                    class="rounded-xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-3 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Active Learners
                    </p>
                    <p class="text-xl leading-none font-bold text-indigo-600">
                        {{ classroom.learners_count }}
                        <span class="ml-1 text-xs text-slate-400">ACT</span>
                    </p>
                </div>
                <div
                    class="rounded-xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-3 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Class Teacher
                    </p>
                    <div class="flex flex-col gap-1 truncate">
                        <p
                            class="truncate text-xs leading-none font-bold text-slate-900"
                        >
                            {{ classroom.teacher || 'Unassigned' }}
                        </p>
                        <p class="truncate text-xs font-medium text-slate-400">
                            {{ classroom.teacher_email || 'No Email' }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-3 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Status
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <div
                            class="h-2 w-2 rounded-full"
                            :class="
                                classroom.is_active
                                    ? 'bg-emerald-500'
                                    : 'bg-slate-300'
                            "
                        ></div>
                        <span
                            class="text-xs font-bold"
                            :class="
                                classroom.is_active
                                    ? 'text-emerald-600'
                                    : 'text-slate-400'
                            "
                            >{{
                                classroom.is_active ? 'Active' : 'Draft'
                            }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Learner List (Students) -->
            <div
                class="group relative overflow-hidden rounded-2xl border bg-white p-8 shadow-sm"
            >
                <div
                    class="relative z-10 mb-8 flex flex-col justify-between gap-6 lg:flex-row lg:items-center"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md"
                            >
                                <Users class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">
                                Learners
                            </h2>
                        </div>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Manage class learners.
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="mb-2 flex items-center gap-3">
                            <span
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Capacity Usage</span
                            >
                            <span class="text-sm font-bold text-slate-900"
                                >{{ classroom.utilization }}%</span
                            >
                        </div>
                        <div
                            class="h-2 w-64 overflow-hidden rounded-full bg-slate-100 shadow-inner"
                        >
                            <div
                                class="h-full rounded-full shadow-sm transition-all duration-1000"
                                :class="
                                    classroom.utilization > 90
                                        ? 'bg-rose-500'
                                        : 'bg-blue-600'
                                "
                                :style="{ width: `${classroom.utilization}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="mb-6 flex flex-col gap-4">
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center"
                    >
                        <div class="relative flex-1 md:max-w-md">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by name or number..."
                                class="h-11 rounded-xl border-slate-200 pl-9 text-sm"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                class="h-11 rounded-xl border-slate-200 px-6 text-xs font-medium transition-all hover:bg-slate-50"
                                @click="showFilters = !showFilters"
                            >
                                <Filter class="mr-2 h-4 w-4" />{{
                                    showFilters
                                        ? 'Hide Filters'
                                        : 'Show Filters'
                                }}
                            </Button>
                            <Button
                                variant="ghost"
                                class="h-11 px-4 text-xs font-medium text-slate-500 hover:text-blue-600"
                                @click="clearFilters"
                                >Reset Filters</Button
                            >
                        </div>
                    </div>

                    <div
                        v-if="showFilters"
                        class="grid animate-in gap-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-6 duration-300 slide-in-from-top-4 md:grid-cols-2"
                    >
                        <div class="space-y-2">
                            <label
                                class="ml-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Status</label
                            >
                            <select
                                v-model="selectedStatus"
                                class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-xs font-bold shadow-sm transition-all outline-none focus:ring-blue-600"
                            >
                                <option
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="ml-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Gender</label
                            >
                            <select
                                v-model="selectedGender"
                                class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-xs font-bold shadow-sm transition-all outline-none focus:ring-blue-600"
                            >
                                <option
                                    v-for="option in genderOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div
                    v-if="selectedCount > 0"
                    class="mb-6 flex animate-in items-center justify-between rounded-xl border border-blue-100 bg-blue-50/50 p-5 shadow-sm duration-200 zoom-in-95"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-white shadow-md"
                        >
                            <Users class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">
                                {{ selectedCount }} Learner{{
                                    selectedCount === 1 ? '' : 's'
                                }}
                                Selected
                            </p>
                            <p class="text-xs font-medium text-blue-600">
                                Bulk Actions
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="ghost"
                            class="h-10 px-6 text-sm font-medium text-slate-500 hover:text-slate-700"
                            @click="selectedLearnerIds = []"
                            >Cancel</Button
                        >
                        <Button
                            variant="outline"
                            class="h-10 rounded-xl border-blue-200 px-6 text-xs font-bold text-blue-600 shadow-sm transition-all hover:bg-blue-600 hover:text-white"
                            @click="openActionModal('transfer')"
                        >
                            <ArrowRightLeft class="mr-2 h-4 w-4" />Transfer
                        </Button>
                        <Button
                            class="h-10 rounded-xl border-0 bg-slate-900 px-8 text-xs font-medium text-white shadow-sm hover:bg-black"
                            @click="openActionModal('promote')"
                        >
                            <ArrowUpCircle class="mr-2 h-4 w-4" />Promote
                        </Button>
                    </div>
                </div>

                <!-- Learners Table -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="w-16 px-6 py-5 text-center">
                                        <Checkbox
                                            :checked="allSelected"
                                            @update:checked="toggleAllLearners"
                                            class="rounded-md border-slate-300"
                                        />
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Full Name
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Admission Number
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Gender
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-5 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-if="learners.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-20 text-center"
                                    >
                                        <div
                                            class="flex flex-col items-center gap-4"
                                        >
                                            <div
                                                class="rounded-full bg-slate-50 p-6 shadow-inner"
                                            >
                                                <Users
                                                    class="h-10 w-10 text-slate-200"
                                                />
                                            </div>
                                            <p
                                                class="text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                No learners found in this class.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="learner in learners"
                                    :key="learner.id"
                                    class="group transition-all hover:bg-blue-50/20"
                                >
                                    <td class="px-6 py-5 text-center">
                                        <Checkbox
                                            :checked="
                                                selectedLearnerIds.includes(
                                                    learner.id,
                                                )
                                            "
                                            @update:checked="
                                                toggleLearner(learner.id)
                                            "
                                            class="rounded-md border-slate-300 data-[state=checked]:border-blue-600 data-[state=checked]:bg-blue-600"
                                        />
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 font-bold text-slate-900 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white"
                                            >
                                                {{ learner.name.charAt(0) }}
                                            </div>
                                            <div
                                                class="max-w-[200px] truncate leading-none font-bold text-slate-900 transition-colors group-hover:text-blue-600"
                                            >
                                                {{ learner.name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-5 text-xs font-bold tracking-tight text-slate-600 uppercase"
                                    >
                                        {{ learner.admission_number || '—' }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <span
                                            class="text-xs font-bold text-slate-600 uppercase"
                                            >{{ learner.gender }}</span
                                        >
                                    </td>
                                    <td class="px-6 py-5">
                                        <Badge
                                            variant="secondary"
                                            class="rounded-xl border-none bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600 uppercase"
                                            >{{ learner.status }}</Badge
                                        >
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child
                                                ><Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-slate-400 transition-all hover:bg-blue-50 hover:text-blue-600"
                                                    ><MoreHorizontal
                                                        class="h-4 w-4" /></Button
                                            ></DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border-slate-100 p-2 shadow-xl"
                                            >
                                                <DropdownMenuItem
                                                    class="rounded-lg"
                                                    as-child
                                                    ><Link
                                                        :href="`/students/${learner.id}`"
                                                        ><Eye
                                                            class="mr-2 h-4 w-4 text-blue-500"
                                                        />
                                                        View</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg"
                                                    as-child
                                                    ><Link
                                                        :href="`/students/${learner.id}/edit`"
                                                        ><Edit
                                                            class="mr-2 h-4 w-4 text-amber-500"
                                                        />
                                                        Edit</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg"
                                                    v-if="
                                                        learner.status !==
                                                        'suspended'
                                                    "
                                                    @click="
                                                        openActionModal(
                                                            'suspend',
                                                            learner,
                                                        )
                                                    "
                                                    ><ShieldAlert
                                                        class="mr-2 h-4 w-4 text-rose-500"
                                                    />
                                                    Suspend</DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg"
                                                    v-else
                                                    @click="
                                                        openActionModal(
                                                            'activate',
                                                            learner,
                                                        )
                                                    "
                                                    ><UserCheck
                                                        class="mr-2 h-4 w-4 text-emerald-500"
                                                    />
                                                    Activate</DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg"
                                                    @click="
                                                        openActionModal(
                                                            'demote',
                                                            learner,
                                                        )
                                                    "
                                                    ><ArrowDownCircle
                                                        class="mr-2 h-4 w-4 text-amber-600"
                                                    />
                                                    Demote</DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    class="rounded-lg font-bold text-rose-600"
                                                    @click="
                                                        openActionModal(
                                                            'delete',
                                                            learner,
                                                        )
                                                    "
                                                    ><Trash2
                                                        class="mr-2 h-4 w-4"
                                                    />
                                                    Delete</DropdownMenuItem
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

            <!-- Subjects & Teachers -->
            <div
                class="group relative overflow-hidden rounded-2xl border bg-white p-8 shadow-sm"
            >
                <div
                    class="relative z-10 mb-8 flex flex-col justify-between gap-6 md:flex-row md:items-center"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md"
                            >
                                <BookCopy class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">
                                Subjects & Teachers
                            </h2>
                        </div>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Manage subject allocations and teachers for this
                            class.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p
                                class="text-xs leading-none font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Subjects Count
                            </p>
                            <p class="mt-1 text-sm font-bold text-slate-900">
                                {{ subjectAllocations.length }} Total
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            as-child
                            class="h-11 rounded-xl border-slate-200 px-8 text-xs font-medium transition-all hover:bg-slate-50"
                        >
                            <Link :href="`/classes/${classroom.id}/subjects`"
                                >Manage Subjects</Link
                            >
                        </Button>
                    </div>
                </div>

                <div
                    v-if="subjectAllocations.length === 0"
                    class="rounded-2xl border-2 border-dashed border-slate-100 p-16 text-center"
                >
                    <p class="text-sm font-medium text-slate-400">
                        No subjects assigned to this class yet.
                    </p>
                </div>
                <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="allocation in subjectAllocations"
                        :key="allocation.id"
                        class="group/sub relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 transition-all hover:border-emerald-200 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <h3
                                    class="truncate text-lg leading-tight font-bold text-slate-900 uppercase transition-colors group-hover/sub:text-emerald-600"
                                >
                                    {{ allocation.subject }}
                                </h3>
                                <p
                                    class="mt-1 text-xs font-bold tracking-wider text-slate-400 uppercase opacity-80"
                                >
                                    {{ allocation.code }} •
                                    {{ allocation.learning_area || 'Core' }}
                                </p>
                            </div>
                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-xs font-bold text-slate-400 shadow-sm transition-all group-hover/sub:bg-emerald-600 group-hover/sub:text-white"
                            >
                                L
                            </div>
                        </div>
                        <div
                            class="mb-5 flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition-colors group-hover/sub:bg-blue-50/30"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-100 bg-white shadow-sm"
                            >
                                <UserCheck class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <p
                                    class="mb-1 text-xs leading-none font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Subject Teacher
                                </p>
                                <p
                                    class="truncate pt-0.5 text-xs leading-none font-bold text-slate-900 uppercase"
                                >
                                    {{ allocation.teacher }}
                                </p>
                            </div>
                        </div>
                        <div class="mb-2 grid grid-cols-2 gap-3">
                            <div
                                class="rounded-xl border border-slate-100 bg-white p-3 shadow-sm"
                            >
                                <p
                                    class="mb-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Subject Type
                                </p>
                                <p
                                    class="text-xs font-bold text-slate-900 uppercase"
                                >
                                    {{ allocation.type }}
                                </p>
                            </div>
                            <div
                                class="rounded-xl border border-slate-100 bg-white p-3 shadow-sm"
                            >
                                <p
                                    class="mb-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Status
                                </p>
                                <div class="flex items-center gap-1.5 pt-0.5">
                                    <div
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="
                                            allocation.is_active
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-300'
                                        "
                                    ></div>
                                    <p
                                        class="text-xs font-bold text-slate-900 uppercase"
                                        :class="
                                            allocation.is_active
                                                ? ''
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{
                                            allocation.is_active
                                                ? 'Active'
                                                : 'Halted'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute top-4 right-4 z-10 opacity-0 transition-opacity group-hover/sub:opacity-100"
                        >
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 rounded-lg border border-slate-100 bg-white/80 shadow-sm backdrop-blur"
                                    >
                                        <MoreHorizontal
                                            class="h-4 w-4 text-emerald-600"
                                        />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-56 rounded-xl border-slate-100 p-2 shadow-xl"
                                >
                                    <DropdownMenuItem
                                        class="rounded-lg"
                                        as-child
                                        ><Link
                                            :href="`/classes/${classroom.id}/subjects`"
                                            ><Eye
                                                class="mr-2 h-4 w-4 text-blue-500"
                                            />
                                            Manage Allocation</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg"
                                        as-child
                                        ><Link
                                            :href="`/curriculum/subjects/${allocation.subject_id}`"
                                            ><BookCopy
                                                class="mr-2 h-4 w-4 text-indigo-500"
                                            />
                                            Subject Details</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg"
                                        as-child
                                        ><Link
                                            :href="`/classes/allocations?class_id=${classroom.id}&subject_id=${allocation.subject_id}`"
                                            ><UserCheck
                                                class="mr-2 h-4 w-4 text-emerald-600"
                                            />
                                            Assign Teacher</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg"
                                        @click="
                                            openSubjectActionModal(
                                                allocation.is_active
                                                    ? 'deactivate'
                                                    : 'activate',
                                                allocation,
                                            )
                                        "
                                        ><component
                                            :is="
                                                allocation.is_active
                                                    ? ShieldOff
                                                    : ShieldCheck
                                            "
                                            class="mr-2 h-4 w-4"
                                            :class="
                                                allocation.is_active
                                                    ? 'text-amber-500'
                                                    : 'text-emerald-500'
                                            "
                                        />{{
                                            allocation.is_active
                                                ? 'Deactivate'
                                                : 'Activate'
                                        }}</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg font-bold text-rose-600"
                                        @click="
                                            openSubjectActionModal(
                                                'delete',
                                                allocation,
                                            )
                                        "
                                        ><Trash2 class="mr-2 h-4 w-4" /> Remove
                                        Assignment</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Modals -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="confirmOpen"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-6 backdrop-blur-[2px]"
                    @click.self="closeActionModal"
                >
                    <div
                        class="relative w-full max-w-[440px] animate-in overflow-hidden rounded-xl border-0 bg-white p-8 shadow-lg duration-300 zoom-in-95"
                    >
                        <div class="relative z-10">
                            <div
                                class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg"
                            >
                                <component
                                    :is="
                                        confirmMode === 'delete'
                                            ? Trash2
                                            : confirmMode === 'promote'
                                              ? ArrowUpCircle
                                              : confirmMode === 'transfer'
                                                ? ArrowRightLeft
                                                : ShieldAlert
                                    "
                                    class="h-6 w-6"
                                />
                            </div>
                            <h2
                                class="mb-2 text-2xl leading-none font-bold tracking-tight text-slate-900 uppercase"
                            >
                                {{ modalTitle }}
                            </h2>

                            <div
                                v-if="confirmMode === 'transfer'"
                                class="mt-6 space-y-4"
                            >
                                <div class="space-y-1.5">
                                    <label
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Target Class</label
                                    >
                                    <select
                                        v-model="transferTargetId"
                                        class="h-12 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-xs font-bold shadow-sm transition-all outline-none focus:ring-blue-600"
                                    >
                                        <option value="">Select a class</option>
                                        <option
                                            v-for="target in transferTargets"
                                            :key="target.id"
                                            :value="String(target.id)"
                                        >
                                            {{ target.name }}
                                        </option>
                                    </select>
                                </div>
                                <div
                                    class="rounded-xl border border-blue-100 bg-blue-50 p-4"
                                >
                                    <p
                                        class="text-xs leading-relaxed font-medium text-blue-700 uppercase"
                                    >
                                        Transferring learners will move them to
                                        the selected class for the current
                                        academic session.
                                    </p>
                                </div>
                            </div>
                            <div v-else class="mt-4">
                                <p
                                    class="mb-4 text-sm leading-relaxed font-medium text-slate-500"
                                >
                                    <template v-if="confirmMode === 'promote'"
                                        >Are you sure you want to promote the
                                        selected {{ selectedCount }} learners to
                                        the next grade level?</template
                                    >
                                    <template v-else-if="selectedLearner">{{
                                        confirmMode === 'activate'
                                            ? `Are you sure you want to activate learner [${selectedLearner.name}]?`
                                            : confirmMode === 'delete'
                                              ? `Are you sure you want to permanently delete learner [${selectedLearner.name}]?`
                                              : confirmMode === 'demote'
                                                ? `Are you sure you want to demote learner [${selectedLearner.name}]?`
                                                : `Are you sure you want to suspend learner [${selectedLearner.name}]?`
                                    }}</template>
                                </p>
                                <div
                                    v-if="confirmMode === 'delete'"
                                    class="rounded-xl border border-rose-100 bg-rose-50 p-4"
                                >
                                    <p
                                        class="text-center text-xs leading-relaxed font-bold text-rose-600 uppercase"
                                    >
                                        Warning: This action cannot be undone.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end gap-3">
                                <Button
                                    variant="ghost"
                                    class="h-12 rounded-xl px-8 text-xs font-bold tracking-tight text-slate-400 uppercase hover:text-slate-600"
                                    @click="closeActionModal"
                                    >Cancel</Button
                                >
                                <Button
                                    :variant="
                                        confirmMode === 'delete'
                                            ? 'destructive'
                                            : 'default'
                                    "
                                    class="h-12 min-w-[140px] rounded-xl border-0 text-xs font-bold tracking-tight uppercase shadow-lg transition-all"
                                    :class="
                                        confirmMode === 'delete'
                                            ? 'bg-rose-600'
                                            : 'bg-slate-900'
                                    "
                                    :disabled="
                                        actionForm.processing ||
                                        bulkPromoteForm.processing
                                    "
                                    @click="confirmAction"
                                >
                                    {{
                                        confirmMode === 'delete'
                                            ? 'Delete'
                                            : 'Confirm'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="subjectConfirmOpen"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-6 backdrop-blur-[2px]"
                    @click.self="closeSubjectActionModal"
                >
                    <div
                        class="relative w-full max-w-[440px] animate-in overflow-hidden rounded-xl border-0 bg-white p-8 shadow-lg duration-300 zoom-in-95"
                    >
                        <div class="relative z-10">
                            <div
                                class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg"
                            >
                                <CheckCircle2 class="h-6 w-6" />
                            </div>
                            <h2
                                class="mb-2 text-2xl leading-none font-bold tracking-tight text-slate-900 uppercase"
                            >
                                {{
                                    subjectConfirmMode === 'delete'
                                        ? 'Remove Allocation'
                                        : subjectConfirmMode === 'activate'
                                          ? 'Activate Subject'
                                          : 'Deactivate Subject'
                                }}
                            </h2>
                            <p
                                class="mb-6 text-sm leading-relaxed font-medium text-slate-500"
                            >
                                <template v-if="selectedSubjectAllocation">
                                    {{
                                        subjectConfirmMode === 'delete'
                                            ? `Are you sure you want to remove [${selectedSubjectAllocation.subject}] from this class?`
                                            : subjectConfirmMode === 'activate'
                                              ? `Are you sure you want to activate [${selectedSubjectAllocation.subject}] for this class?`
                                              : `Are you sure you want to deactivate [${selectedSubjectAllocation.subject}] for this class?`
                                    }}
                                </template>
                            </p>
                            <div class="mt-8 flex justify-end gap-3">
                                <Button
                                    variant="ghost"
                                    class="h-12 rounded-xl px-8 text-xs font-bold tracking-tight text-slate-400 uppercase hover:text-slate-600"
                                    @click="closeSubjectActionModal"
                                    >Cancel</Button
                                >
                                <Button
                                    :variant="
                                        subjectConfirmMode === 'delete'
                                            ? 'destructive'
                                            : 'default'
                                    "
                                    class="h-12 min-w-[140px] rounded-xl border-0 text-xs font-bold tracking-tight uppercase shadow-lg transition-all"
                                    :class="
                                        subjectConfirmMode === 'delete'
                                            ? 'bg-rose-600'
                                            : 'bg-emerald-600'
                                    "
                                    :disabled="subjectActionForm.processing"
                                    @click="confirmSubjectAction"
                                >
                                    {{
                                        subjectConfirmMode === 'delete'
                                            ? 'Remove'
                                            : 'Confirm'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>
