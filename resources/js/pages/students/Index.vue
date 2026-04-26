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
    Square,
    UserPlus,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import BulkUploadModal from './partials/BulkUploadModal.vue';

interface LearnerRow {
    id: number;
    admission_number: string | null;
    name: string;
    gender: string;
    class: string | null;
    class_id: number | null;
    status: string;
    photo: string | null;
    photo_url: string | null;
    age: number | null;
    admission_date: string | null;
}

const { props: pageProps } = usePage<any>();
const permissions = computed(() => pageProps.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

interface PaginatedLearners {
    data: LearnerRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    learners: PaginatedLearners;
    stats: {
        total: number;
        active: number;
        withdrawn: number;
        new_this_month: number;
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
    { title: 'Learners', href: '/students' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedClassId = ref<string>(
    props.filters.class_id ? String(props.filters.class_id) : '',
);
const selectedGender = ref(props.filters.gender ?? 'all');
const selectedBoardingStatus = ref(props.filters.boarding_status ?? 'all');
const selectedCounty = ref(props.filters.county ?? '');
const showFilters = ref(props.filters.show_filters ?? true);
const perPage = ref(props.filters.per_page ?? 15);

const actionForm = useForm({});
const promotionForm = useForm<{ learner_ids: number[] }>({ learner_ids: [] });
const selectedLearnerIds = ref<number[]>([]);
const confirmOpen = ref(false);
const promoteOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'bulkDelete'>(
    'suspend',
);
const selectedLearner = ref<LearnerRow | null>(null);
const bulkUploadOpen = ref(false);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (page = 1) => {
    router.get(
        '/students',
        {
            search: searchQuery.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
            class_id: selectedClassId.value || undefined,
            gender:
                selectedGender.value !== 'all'
                    ? selectedGender.value
                    : undefined,
            boarding_status:
                selectedBoardingStatus.value !== 'all'
                    ? selectedBoardingStatus.value
                    : undefined,
            county: selectedCounty.value || undefined,
            show_filters: showFilters.value,
            per_page: perPage.value,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: [
                'learners',
                'stats',
                'filters',
                'classes',
                'counties',
                'statusOptions',
                'genderOptions',
                'boardingOptions',
            ],
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch(
    [
        selectedStatus,
        selectedClassId,
        selectedGender,
        selectedBoardingStatus,
        selectedCounty,
        perPage,
    ],
    () => applyFilters(),
);

const activeRate = computed(() => {
    if (!props.stats.total) return 0;
    return Math.round((props.stats.active / props.stats.total) * 100);
});

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedStatus.value !== 'all')
        params.set('status', selectedStatus.value);
    if (selectedClassId.value) params.set('class_id', selectedClassId.value);
    if (selectedGender.value !== 'all')
        params.set('gender', selectedGender.value);
    if (selectedBoardingStatus.value !== 'all')
        params.set('boarding_status', selectedBoardingStatus.value);
    if (selectedCounty.value) params.set('county', selectedCounty.value);
    return `/students?${params.toString()}`;
});

const pdfExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedStatus.value !== 'all')
        params.set('status', selectedStatus.value);
    if (selectedClassId.value) params.set('class_id', selectedClassId.value);
    if (selectedGender.value !== 'all')
        params.set('gender', selectedGender.value);
    if (selectedBoardingStatus.value !== 'all')
        params.set('boarding_status', selectedBoardingStatus.value);
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

const openActionModal = (
    mode: 'suspend' | 'activate' | 'delete' | 'bulkDelete',
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
    if (confirmMode.value === 'bulkDelete') {
        actionForm
            .transform(() => ({
                learner_ids: selectedLearnerIds.value,
            }))
            .post('/students/bulk-delete', {
                preserveScroll: true,
                onSuccess: () => {
                    selectedLearnerIds.value = [];
                    closeActionModal();
                },
            });
        return;
    }

    if (!selectedLearner.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedLearner.value.id}/suspend`, {
            preserveScroll: true,
            onSuccess: () => closeActionModal(),
        });
        return;
    }

    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedLearner.value.id}/activate`, {
            preserveScroll: true,
            onSuccess: () => closeActionModal(),
        });
        return;
    }

    actionForm.delete(`/students/${selectedLearner.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeActionModal(),
    });
};

const modalTitle = computed(() => {
    if (confirmMode.value === 'activate') return 'Activate learner';
    if (confirmMode.value === 'delete') return 'Delete learner';
    if (confirmMode.value === 'bulkDelete') return 'Delete selected learners';
    return 'Suspend learner';
});

const modalMessage = computed(() => {
    if (confirmMode.value === 'bulkDelete')
        return `Are you sure you want to delete ${selectedLearnerIds.value.length} selected learners? This action cannot be undone.`;
    if (!selectedLearner.value) return '';
    if (confirmMode.value === 'activate')
        return `Activate ${selectedLearner.value.name}?`;
    if (confirmMode.value === 'delete')
        return `Delete ${selectedLearner.value.name}? This action will remove the record from the active list.`;
    return `Suspend ${selectedLearner.value.name}?`;
});

const pageLabel = computed(() => {
    const from = props.learners.from ?? 0;
    const to = props.learners.to ?? 0;
    return `Showing ${from} to ${to} of ${props.learners.total} learners`;
});

watch(
    () => props.learners.data,
    () => {
        selectedLearnerIds.value = selectedLearnerIds.value.filter((id) =>
            props.learners.data.some((learner) => learner.id === id),
        );
    },
    { deep: true },
);

const allSelectableLearnerIds = computed(() =>
    props.learners.data
        .filter((learner) => learner.status === 'active')
        .map((learner) => learner.id),
);
const allSelected = computed(
    () =>
        allSelectableLearnerIds.value.length > 0 &&
        allSelectableLearnerIds.value.every((id) =>
            selectedLearnerIds.value.includes(id),
        ),
);
const selectedCount = computed(() => selectedLearnerIds.value.length);

const toggleAllLearners = () => {
    selectedLearnerIds.value = allSelected.value
        ? []
        : [...allSelectableLearnerIds.value];
};

const toggleLearner = (learnerId: number) => {
    selectedLearnerIds.value = selectedLearnerIds.value.includes(learnerId)
        ? selectedLearnerIds.value.filter((id) => id !== learnerId)
        : [...selectedLearnerIds.value, learnerId];
};

const openPromoteModal = () => {
    if (!selectedLearnerIds.value.length) return;
    promoteOpen.value = true;
};

const closePromoteModal = () => {
    promoteOpen.value = false;
};

const promoteSelectedLearners = () => {
    if (!selectedLearnerIds.value.length) return;
    promotionForm.learner_ids = [...selectedLearnerIds.value];
    promotionForm.post('/students/promote', {
        preserveScroll: true,
        onSuccess: () => {
            selectedLearnerIds.value = [];
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
    return active
        ? 'bg-emerald-500 text-white shadow-sm'
        : 'bg-slate-400 text-white';
};
</script>

<template>
    <Head title="Learners" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed top-4 right-4 z-50">
            <div
                class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg"
            >
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Learners
                    </h1>
                    <p
                        class="text-xs font-bold tracking-tight text-muted-foreground uppercase opacity-80 sm:text-xs"
                    >
                        Academic Enrollment & Demographic Registry
                    </p>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <Button
                        variant="outline"
                        class="hidden h-10 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-slate-900 hover:text-white sm:flex"
                        @click="exportToPDF"
                    >
                        <Download class="mr-2 h-4 w-4 opacity-70" />
                        Export
                    </Button>
                    <Button
                        variant="outline"
                        class="hidden h-10 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:border-blue-600 hover:bg-blue-600 hover:text-white sm:flex"
                        @click="openBulkUploadModal"
                    >
                        <Upload class="mr-2 h-4 w-4 opacity-70" />
                        Propagate
                    </Button>
                    <Link
                        v-if="hasPermission('students.create')"
                        href="/students/create"
                        class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-blue-600 px-6 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-blue-500/30 transition-all hover:scale-[1.02] hover:bg-blue-700 active:scale-95 sm:flex-none"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Enroll
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 px-1 sm:gap-6 lg:grid-cols-4">
                <div
                    class="group flex flex-col justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-blue-200 sm:flex-row sm:items-center sm:p-6"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Registry
                        </p>
                        <h3
                            class="text-xl font-bold text-slate-900 tabular-nums sm:text-2xl"
                        >
                            {{ stats.total.toLocaleString() }}
                        </h3>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-100/50 bg-blue-50 text-blue-600 transition-transform group-hover:scale-110 sm:h-12 sm:w-12"
                    >
                        <Users class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>

                <div
                    class="group flex flex-col justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-emerald-200 sm:flex-row sm:items-center sm:p-6"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Active Status
                        </p>
                        <h3
                            class="text-xl font-bold text-slate-900 tabular-nums sm:text-2xl"
                        >
                            {{ stats.active.toLocaleString() }}
                        </h3>
                    </div>
                    <div
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100/50 bg-emerald-50 text-emerald-600 transition-transform group-hover:scale-110 sm:h-12 sm:w-12"
                    >
                        <div
                            class="absolute top-2 right-2 h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                            v-if="stats.active > 0"
                        ></div>
                        <CheckCircle2 class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>

                <div
                    class="group flex flex-col justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-amber-200 sm:flex-row sm:items-center sm:p-6"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Withdrawn
                        </p>
                        <h3
                            class="text-xl font-bold text-slate-900 tabular-nums sm:text-2xl"
                        >
                            {{ stats.withdrawn.toLocaleString() }}
                        </h3>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-100/50 bg-amber-50 text-amber-600 transition-transform group-hover:scale-110 sm:h-12 sm:w-12"
                    >
                        <ShieldAlert class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>

                <div
                    class="group flex flex-col justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-sky-200 sm:flex-row sm:items-center sm:p-6"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Acquisition
                        </p>
                        <div class="flex items-baseline gap-2">
                            <h3
                                class="text-xl font-bold text-slate-900 tabular-nums sm:text-2xl"
                            >
                                {{ stats.new_this_month }}
                            </h3>
                            <span
                                class="rounded bg-emerald-50 px-1 text-xs font-bold text-emerald-600"
                                v-if="stats.growth > 0"
                                >+{{ stats.growth }}%</span
                            >
                        </div>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-sky-100/50 bg-sky-50 text-sky-600 transition-transform group-hover:scale-110 sm:h-12 sm:w-12"
                    >
                        <TrendingUp class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <!-- Toolbar -->
                <div class="space-y-4 border-b border-slate-50 p-4 sm:p-6">
                    <div
                        class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
                    >
                        <div class="group relative w-full lg:max-w-md">
                            <Search
                                class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="SEARCH BY NAME, ID OR UPI..."
                                class="h-11 rounded-xl border-slate-200 bg-slate-50/50 pl-10 text-xs font-bold tracking-tight uppercase shadow-none transition-all focus:bg-white"
                            />
                        </div>

                        <div class="flex w-full items-center gap-2 lg:w-auto">
                            <Button
                                variant="outline"
                                class="h-11 flex-1 rounded-xl border-slate-200 bg-white px-4 text-xs font-bold tracking-tight whitespace-nowrap uppercase hover:bg-slate-50 lg:flex-none"
                                @click="toggleFilters"
                            >
                                <Filter class="mr-2 h-3.5 w-3.5 opacity-70" />
                                Logic
                            </Button>

                            <DropdownMenu v-if="selectedCount > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        class="h-11 rounded-xl bg-slate-900 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-slate-900/10 hover:bg-slate-800"
                                    >
                                        BATCH ({{ selectedCount }})
                                        <ChevronDown
                                            class="ml-2 h-4 w-4 opacity-70"
                                        />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-56 rounded-xl border border-slate-100 p-2 text-xs font-bold tracking-tight uppercase shadow-xl"
                                >
                                    <DropdownMenuItem
                                        @click="openPromoteModal"
                                        class="rounded-lg"
                                    >
                                        <ArrowUpCircle
                                            class="mr-2 h-4 w-4 text-emerald-500"
                                        />
                                        Promote Selection
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1" />
                                    <DropdownMenuItem
                                        class="group rounded-lg text-rose-500"
                                        @click="openActionModal('bulkDelete')"
                                    >
                                        <Trash2
                                            class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100"
                                        />
                                        Purge Selection
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="h-11 w-11 rounded-xl border-slate-200 bg-white p-0"
                                    >
                                        <MoreHorizontal
                                            class="h-5 w-5 text-slate-400"
                                        />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-48 rounded-xl border border-slate-100 p-2 text-xs font-bold tracking-tight uppercase shadow-xl"
                                >
                                    <DropdownMenuItem
                                        @click="exportToPDF"
                                        class="rounded-lg"
                                    >
                                        <FileText
                                            class="mr-2 h-4 w-4 text-rose-500"
                                        />
                                        Export Index
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="openBulkUploadModal"
                                        class="rounded-lg"
                                    >
                                        <Download
                                            class="mr-2 h-4 w-4 text-blue-500"
                                        />
                                        Bulk Ingest
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Filters Drawer -->
                    <div
                        v-if="showFilters"
                        class="grid animate-in grid-cols-2 gap-3 pt-2 duration-300 slide-in-from-top-2 sm:gap-4 lg:grid-cols-4"
                    >
                        <div class="group relative">
                            <select
                                v-model="selectedStatus"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none hover:bg-slate-50/50 focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                            >
                                <option
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 transition-colors group-hover:text-slate-600"
                            />
                        </div>

                        <div class="group relative">
                            <select
                                v-model="selectedClassId"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none hover:bg-slate-50/50 focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                            >
                                <option value="">Every Class</option>
                                <option
                                    v-for="schoolClass in classes"
                                    :key="schoolClass.id"
                                    :value="String(schoolClass.id)"
                                >
                                    {{ schoolClass.name }}
                                </option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 transition-colors group-hover:text-slate-600"
                            />
                        </div>

                        <div class="group relative">
                            <select
                                v-model="selectedGender"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none hover:bg-slate-50/50 focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                            >
                                <option
                                    v-for="option in genderOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 transition-colors group-hover:text-slate-600"
                            />
                        </div>

                        <div class="group relative">
                            <select
                                v-model="selectedBoardingStatus"
                                class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none hover:bg-slate-50/50 focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                            >
                                <option
                                    v-for="option in boardingOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400 transition-colors group-hover:text-slate-600"
                            />
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/50 text-slate-500"
                            >
                                <th class="h-14 w-14 px-6">
                                    <button
                                        @click="toggleAllLearners"
                                        class="flex h-5 w-5 items-center justify-center rounded-md border-2 border-slate-200 bg-white transition-all hover:border-blue-400"
                                        :class="
                                            allSelected
                                                ? 'border-blue-600 bg-blue-600 text-white shadow-md'
                                                : ''
                                        "
                                    >
                                        <component
                                            :is="
                                                allSelected
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-3.5 w-3.5"
                                        />
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >
                                    Learner Profile
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >
                                    Placement Context
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >
                                    Live Status
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >
                                    Entry Date
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                >
                                    Operations
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr
                                v-for="learner in learners.data"
                                :key="learner.id"
                                class="group cursor-pointer transition-colors hover:bg-blue-50/30"
                                @click="router.visit(`/students/${learner.id}`)"
                            >
                                <td class="px-6 py-4 text-center" @click.stop>
                                    <button
                                        @click="toggleLearner(learner.id)"
                                        class="flex h-5 w-5 items-center justify-center rounded-md border-2 border-slate-200 bg-white transition-all hover:border-blue-400"
                                        :class="
                                            selectedLearnerIds.includes(
                                                learner.id,
                                            )
                                                ? 'border-blue-600 bg-blue-600 text-white shadow-md'
                                                : ''
                                        "
                                    >
                                        <component
                                            :is="
                                                selectedLearnerIds.includes(
                                                    learner.id,
                                                )
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-3.5 w-3.5"
                                        />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            v-if="learner.photo_url"
                                            class="h-11 w-11 flex-shrink-0 transform overflow-hidden rounded-xl border border-slate-100 shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:border-blue-400"
                                        >
                                            <img
                                                :src="learner.photo_url"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-11 w-11 flex-shrink-0 transform items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-xs font-bold text-blue-600 shadow-inner transition-all duration-300 group-hover:scale-105"
                                        >
                                            {{
                                                learner.name
                                                    .split(' ')
                                                    .map((n) => n[0])
                                                    .join('')
                                                    .substring(0, 2)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <div class="flex min-w-0 flex-col">
                                            <span
                                                class="truncate text-xs font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-blue-700"
                                                >{{ learner.name }}</span
                                            >
                                            <span
                                                class="mt-0.5 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                                >{{
                                                    learner.admission_number ||
                                                    'NO REG ID'
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold tracking-tight text-slate-700 uppercase"
                                            >{{
                                                learner.class || 'UNASSIGNED'
                                            }}</span
                                        >
                                        <span
                                            class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >{{ learner.gender }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge
                                        :variant="
                                            learner.status === 'active'
                                                ? 'outline'
                                                : 'secondary'
                                        "
                                        class="rounded-lg border-0 px-2.5 py-1 text-xs font-medium tracking-tight uppercase"
                                        :class="
                                            learner.status === 'active'
                                                ? 'bg-emerald-50 text-emerald-600'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        <div
                                            v-if="learner.status === 'active'"
                                            class="mr-1.5 h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500 shadow-sm"
                                        ></div>
                                        {{ learner.status }}
                                    </Badge>
                                </td>
                                <td
                                    class="px-6 py-4 text-xs font-medium tracking-tight text-slate-500 uppercase"
                                >
                                    {{ learner.admission_date || 'PENDING' }}
                                </td>
                                <td class="px-6 py-4 text-right" @click.stop>
                                    <div
                                        class="flex items-center justify-end gap-1 px-1"
                                    >
                                        <div
                                            class="flex translate-x-2 transform items-center gap-1 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100"
                                        >
                                            <Link
                                                :href="`/students/${learner.id}`"
                                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-transparent text-slate-400 transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600"
                                                ><Eye class="h-4 w-4"
                                            /></Link>
                                            <Link
                                                v-if="
                                                    hasPermission(
                                                        'students.update',
                                                    )
                                                "
                                                :href="`/students/${learner.id}/edit`"
                                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-transparent text-slate-400 transition-all hover:border-amber-100 hover:bg-amber-50 hover:text-amber-600"
                                                ><Edit class="h-4 w-4"
                                            /></Link>
                                        </div>

                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl hover:bg-slate-50"
                                                    ><MoreHorizontal
                                                        class="h-4.5 w-4.5 text-slate-400"
                                                /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border border-slate-100 p-2 text-xs font-bold tracking-tight uppercase shadow-xl"
                                            >
                                                <DropdownMenuItem
                                                    as-child
                                                    class="rounded-lg"
                                                    ><Link
                                                        :href="`/students/${learner.id}`"
                                                        ><Eye
                                                            class="mr-2 h-3.5 w-3.5"
                                                        />
                                                        View Registry</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    v-if="
                                                        hasPermission(
                                                            'students.update',
                                                        )
                                                    "
                                                    as-child
                                                    class="rounded-lg"
                                                >
                                                    <Link
                                                        :href="`/students/enrollments/create?student_id=${learner.id}`"
                                                    >
                                                        <UserPlus
                                                            class="mr-2 h-3.5 w-3.5 text-blue-500"
                                                        />
                                                        Allocate Context
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="
                                                        hasPermission(
                                                            'students.update',
                                                        )
                                                    "
                                                    as-child
                                                    class="rounded-lg"
                                                    ><Link
                                                        :href="`/students/${learner.id}/edit`"
                                                        ><Edit
                                                            class="mr-2 h-3.5 w-3.5"
                                                        />
                                                        Modify Profile</Link
                                                    ></DropdownMenuItem
                                                >
                                                <DropdownMenuSeparator
                                                    class="my-1"
                                                />
                                                <DropdownMenuItem
                                                    v-if="
                                                        learner.status !==
                                                            'suspended' &&
                                                        hasPermission(
                                                            'students.update',
                                                        )
                                                    "
                                                    @click="
                                                        openActionModal(
                                                            'suspend',
                                                            learner,
                                                        )
                                                    "
                                                    class="rounded-lg"
                                                    ><ShieldAlert
                                                        class="mr-2 h-3.5 w-3.5 text-amber-500"
                                                    />
                                                    Terminate
                                                    Access</DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    v-else-if="
                                                        hasPermission(
                                                            'students.update',
                                                        )
                                                    "
                                                    @click="
                                                        openActionModal(
                                                            'activate',
                                                            learner,
                                                        )
                                                    "
                                                    class="rounded-lg"
                                                    ><CheckCircle2
                                                        class="mr-2 h-3.5 w-3.5 text-emerald-500"
                                                    />
                                                    Restore
                                                    Access</DropdownMenuItem
                                                >
                                                <DropdownMenuItem
                                                    v-if="
                                                        hasPermission(
                                                            'students.delete',
                                                        )
                                                    "
                                                    class="group rounded-lg text-rose-600"
                                                    @click="
                                                        openActionModal(
                                                            'delete',
                                                            learner,
                                                        )
                                                    "
                                                    ><Trash2
                                                        class="mr-2 h-3.5 w-3.5 opacity-70 group-hover:opacity-100"
                                                    />
                                                    Purge
                                                    Entry</DropdownMenuItem
                                                >
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="learners.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-24 text-center text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >
                                    No records identified in registry
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    class="flex flex-col gap-6 border-t border-slate-50 bg-slate-50/30 px-4 py-6 sm:px-6 md:flex-row md:items-center md:justify-between"
                >
                    <p
                        class="order-2 text-xs font-bold tracking-tight text-slate-400 uppercase md:order-1"
                    >
                        {{ pageLabel }}
                    </p>
                    <div
                        class="order-1 flex items-center justify-between gap-6 md:order-2 md:justify-end"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="shrink-0 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >Limit</span
                            >
                            <select
                                v-model="perPage"
                                class="relative cursor-pointer appearance-none rounded-xl border border-slate-200 bg-white px-3 py-1.5 pr-8 text-xs font-bold tracking-tight uppercase outline-none focus:ring-2 focus:ring-blue-600/5"
                            >
                                <option :value="15">15 Units</option>
                                <option :value="50">50 Units</option>
                                <option :value="100">100 Units</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-slate-200 bg-white px-4 text-xs font-bold tracking-tight uppercase"
                                :disabled="learners.current_page <= 1"
                                @click="applyFilters(learners.current_page - 1)"
                                >Prev</Button
                            >
                            <div
                                class="flex h-9 min-w-[70px] items-center justify-center rounded-xl bg-slate-900 px-3 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-slate-900/20"
                            >
                                {{ learners.current_page }} /
                                {{ learners.last_page }}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-slate-200 bg-white px-4 text-xs font-bold tracking-tight uppercase"
                                :disabled="
                                    learners.current_page >= learners.last_page
                                "
                                @click="applyFilters(learners.current_page + 1)"
                                >Next</Button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Modals -->
        <Dialog :open="confirmOpen" @update:open="closeActionModal">
            <DialogContent
                class="animate-in overflow-hidden rounded-xl border-slate-100 p-0 shadow-lg duration-300 zoom-in-95 sm:max-w-[440px]"
            >
                <div class="space-y-6 p-8 text-center">
                    <div
                        class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-xl shadow-xl shadow-blue-500/10"
                        :class="
                            confirmMode === 'delete' ||
                            confirmMode === 'bulkDelete'
                                ? 'bg-red-50 text-red-600'
                                : 'bg-blue-50 text-blue-600'
                        "
                    >
                        <Trash2
                            v-if="
                                confirmMode === 'delete' ||
                                confirmMode === 'bulkDelete'
                            "
                            class="h-10 w-10"
                        />
                        <ShieldAlert v-else class="h-10 w-10" />
                    </div>

                    <div class="space-y-3">
                        <h2
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            {{ modalTitle }}
                        </h2>
                        <p
                            class="px-6 text-xs leading-relaxed font-bold tracking-tight text-slate-400 uppercase opacity-80"
                        >
                            {{ modalMessage }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 pt-6">
                        <Button
                            :variant="
                                confirmMode === 'delete' ||
                                confirmMode === 'bulkDelete'
                                    ? 'destructive'
                                    : 'default'
                            "
                            class="h-14 rounded-2xl border-0 text-sm font-bold tracking-tight uppercase shadow-xl transition-all"
                            :class="
                                confirmMode === 'delete' ||
                                confirmMode === 'bulkDelete'
                                    ? 'bg-red-600 shadow-red-500/20 hover:bg-red-700'
                                    : 'bg-slate-900 shadow-slate-900/10 hover:bg-slate-800'
                            "
                            :disabled="actionForm.processing"
                            @click="confirmAction"
                        >
                            {{
                                actionForm.processing
                                    ? 'Processing...'
                                    : 'Confirm'
                            }}
                        </Button>
                        <Button
                            variant="ghost"
                            class="h-12 rounded-2xl text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900"
                            @click="closeActionModal"
                            >Cancel</Button
                        >
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="promoteOpen" @update:open="closePromoteModal">
            <DialogContent
                class="animate-in overflow-hidden rounded-2xl border-emerald-100 p-0 shadow-lg duration-300 zoom-in-95 sm:max-w-[500px]"
            >
                <div
                    class="relative overflow-hidden bg-emerald-600 px-10 py-8 text-white"
                >
                    <div
                        class="absolute -top-8 -right-8 h-32 w-32 rounded-full bg-white/10 blur-3xl"
                    ></div>
                    <div class="relative z-10 flex items-center gap-5">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/20 shadow-xl backdrop-blur-md"
                        >
                            <ArrowUpCircle class="h-10 w-10 text-white" />
                        </div>
                        <div>
                            <h2
                                class="text-2xl leading-none font-bold tracking-tight"
                            >
                                Global Promotion
                            </h2>
                            <p
                                class="mt-2 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                            >
                                Registry Advancement Protocol
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 p-10">
                    <div
                        class="flex items-center justify-between rounded-xl border border-emerald-100 bg-emerald-50/50 p-6"
                    >
                        <div>
                            <p
                                class="text-xs font-bold tracking-tight text-emerald-600 text-muted-foreground uppercase"
                            >
                                Batched Units
                            </p>
                            <p
                                class="text-3xl font-bold tracking-tighter text-emerald-700"
                            >
                                {{ selectedCount }}
                                <span
                                    class="text-xs tracking-tight uppercase opacity-60"
                                    >Nodes</span
                                >
                            </p>
                        </div>
                        <CheckCircle2 class="h-10 w-10 text-emerald-500/30" />
                    </div>

                    <p
                        class="px-2 text-xs leading-relaxed font-bold tracking-tight text-muted-foreground text-slate-500 uppercase"
                    >
                        SELECTED LEARNERS WILL BE PROPAGATED TO THE NEXT
                        SEQUENTIAL GRADE UPON SYSTEM VALIDATION. THIS ACTION IS
                        IRREVERSIBLE ONCE COMMITTED TO THE CORE REGISTRY.
                    </p>

                    <div
                        class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/30 p-5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/20"
                        >
                            <ShieldAlert class="h-5 w-5" />
                        </div>
                        <p
                            class="text-xs leading-relaxed font-bold tracking-tight text-blue-800 uppercase"
                        >
                            Example Transformation:<br /><span
                                class="text-slate-500"
                                >GRADE 8 WEST</span
                            >
                            →
                            <span class="font-bold text-blue-600"
                                >GRADE 9 WEST</span
                            >
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 pt-4">
                        <Button
                            class="h-14 rounded-[1.5rem] border-0 bg-slate-900 text-sm font-bold tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800"
                            :disabled="promotionForm.processing"
                            @click="promoteSelectedLearners"
                        >
                            {{
                                promotionForm.processing
                                    ? 'Promoting...'
                                    : 'Promote Learners'
                            }}
                        </Button>
                        <Button
                            variant="ghost"
                            class="h-12 rounded-2xl text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900"
                            @click="closePromoteModal"
                            >Cancel</Button
                        >
                    </div>
                </div>
            </DialogContent>
        </Dialog>
        <BulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>

<style scoped>
/* Ensure clean styles */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
