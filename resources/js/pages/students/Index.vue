<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Download,
    Edit,
    Eye,
    Filter,
    Plus,
    Search,
    ShieldAlert,
    Trash2,
    ChevronDown,
    ArrowUpCircle,
    FileText,
    Users,
    TrendingUp,
    CheckSquare,
    Square,
    UserPlus,
    Upload
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
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
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
const selectedClassId = ref<string>(props.filters.class_id ? String(props.filters.class_id) : '');
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
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'bulkDelete'>('suspend');
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
            only: ['learners', 'stats', 'filters', 'classes', 'counties', 'statusOptions', 'genderOptions', 'boardingOptions'],
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

const openActionModal = (mode: 'suspend' | 'activate' | 'delete' | 'bulkDelete', learner: LearnerRow | null = null) => {
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
        actionForm.transform(() => ({
            learner_ids: selectedLearnerIds.value
        })).post('/students/bulk-delete', {
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
    if (confirmMode.value === 'bulkDelete') return `Are you sure you want to delete ${selectedLearnerIds.value.length} selected learners? This action cannot be undone.`;
    if (!selectedLearner.value) return '';
    if (confirmMode.value === 'activate') return `Activate ${selectedLearner.value.name}?`;
    if (confirmMode.value === 'delete') return `Delete ${selectedLearner.value.name}? This action will remove the record from the active list.`;
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
        selectedLearnerIds.value = selectedLearnerIds.value.filter((id) => props.learners.data.some((learner) => learner.id === id));
    },
    { deep: true },
);

const allSelectableLearnerIds = computed(() => props.learners.data.filter((learner) => learner.status === 'active').map((learner) => learner.id));
const allSelected = computed(() => allSelectableLearnerIds.value.length > 0 && allSelectableLearnerIds.value.every((id) => selectedLearnerIds.value.includes(id)));
const selectedCount = computed(() => selectedLearnerIds.value.length);

const toggleAllLearners = () => {
    selectedLearnerIds.value = allSelected.value ? [] : [...allSelectableLearnerIds.value];
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
    return active ? 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-slate-400 text-white';
};
</script>

<template>
    <Head title="Learners" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="space-y-6 sm:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-4 sm:p-6 md:p-8">
            <PageHeader 
                title="Learners Registry" 
                description="Comprehensive student enrollment and demographic management console."
                :breadcrumbs="breadcrumbs"
            >
                <template #actions>
                    <Button variant="outline" class="hidden sm:flex h-10 px-4 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all shadow-sm" @click="exportToPDF">
                        <Download class="mr-2 h-4 w-4 opacity-70" />
                        Export
                    </Button>
                    <Button variant="outline" class="hidden sm:flex h-10 px-4 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm" @click="openBulkUploadModal">
                        <Upload class="mr-2 h-4 w-4 opacity-70" />
                        Propagate
                    </Button>
                    <Link
                        v-if="hasPermission('students.create')"
                        href="/students/create"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 h-10 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:scale-[1.02] active:scale-95 transition-all"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Enroll
                    </Link>
                </template>
            </PageHeader>

            <!-- Status Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="card-premium p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 group hover:border-blue-200">
                    <div>
                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mb-1">Total Registry</p>
                        <h3 class="text-xl sm:text-2xl font-black text-foreground tabular-nums leading-none">{{ props.stats.total.toLocaleString() }}</h3>
                    </div>
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100/50 group-hover:scale-110 transition-transform">
                        <Users class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>

                <div class="card-premium p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 group hover:border-emerald-200">
                    <div>
                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mb-1">Active Learners</p>
                        <h3 class="text-xl sm:text-2xl font-black text-foreground tabular-nums leading-none">{{ props.stats.active.toLocaleString() }}</h3>
                    </div>
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100/50 group-hover:scale-110 transition-transform relative">
                         <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse absolute top-2 right-2" v-if="props.stats.active > 0"></div>
                         <CheckCircle2 class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>

                <div class="card-premium p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 group hover:border-amber-200">
                    <div>
                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mb-1">Withdrawn</p>
                        <h3 class="text-xl sm:text-2xl font-black text-foreground tabular-nums leading-none">{{ props.stats.withdrawn.toLocaleString() }}</h3>
                    </div>
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100/50 group-hover:scale-110 transition-transform">
                        <ShieldAlert class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>

                <div class="card-premium p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 group hover:border-sky-200">
                    <div>
                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mb-1">New Acquisition</p>
                        <div class="flex items-baseline gap-2 leading-none">
                             <h3 class="text-xl sm:text-2xl font-black text-foreground tabular-nums">{{ props.stats.new_this_month }}</h3>
                             <span class="text-[8px] font-black text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded" v-if="props.stats.growth > 0">+{{ props.stats.growth }}%</span>
                        </div>
                    </div>
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600 border border-sky-100/50 group-hover:scale-110 transition-transform">
                        <TrendingUp class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card-premium overflow-hidden">
                <!-- Toolbar -->
                <div class="p-4 sm:p-6 border-b border-slate-50 space-y-4">
                    <div class="flex flex-col lg:flex-row gap-4 lg:items-center justify-between">
                        <div class="relative w-full lg:max-w-md group">
                            <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                            <Input v-model="searchQuery" placeholder="SEARCH BY NAME, ID OR UPI..." class="pl-10 h-11 bg-slate-50/50 border-slate-200 rounded-xl focus:bg-white transition-all shadow-none font-black text-[10px] uppercase tracking-widest" />
                        </div>
                        
                        <div class="flex items-center gap-2 w-full lg:w-auto">
                            <Button variant="outline" class="h-11 flex-1 lg:flex-none border-slate-200 font-black text-[10px] uppercase tracking-widest px-4 rounded-xl whitespace-nowrap bg-white hover:bg-slate-50" @click="toggleFilters">
                                <Filter class="mr-2 h-3.5 w-3.5 opacity-70" /> Logic
                            </Button>
                            
                            <DropdownMenu v-if="selectedCount > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button class="bg-slate-900 text-white hover:bg-slate-800 font-black text-[10px] uppercase tracking-widest h-11 px-6 rounded-xl shadow-lg shadow-slate-900/10">
                                        BATCH ({{ selectedCount }}) <ChevronDown class="ml-2 h-4 w-4 opacity-70" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-slate-100 shadow-xl font-black text-[9px] uppercase tracking-widest">
                                    <DropdownMenuItem @click="openPromoteModal" class="rounded-lg">
                                        <ArrowUpCircle class="mr-2 h-4 w-4 text-emerald-500" /> Promote Selection
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1" />
                                    <DropdownMenuItem class="text-rose-500 rounded-lg group" @click="openActionModal('bulkDelete')">
                                        <Trash2 class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100" /> Purge Selection
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-11 w-11 p-0 rounded-xl border-slate-200 bg-white">
                                        <MoreHorizontal class="h-5 w-5 text-slate-400" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-48 p-2 rounded-xl border border-slate-100 shadow-xl font-black text-[9px] uppercase tracking-widest">
                                     <DropdownMenuItem @click="exportToPDF" class="rounded-lg">
                                        <FileText class="mr-2 h-4 w-4 text-rose-500" /> Export Index
                                     </DropdownMenuItem>
                                     <DropdownMenuItem @click="openBulkUploadModal" class="rounded-lg">
                                        <Download class="mr-2 h-4 w-4 text-blue-500" /> Bulk Ingest
                                     </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Filters Drawer -->
                    <div v-if="showFilters" class="grid gap-3 sm:gap-4 pt-2 grid-cols-2 lg:grid-cols-4 animate-in slide-in-from-top-2 duration-300">
                        <div class="relative group">
                            <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[9px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer hover:bg-slate-50/50 transition-all">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none group-hover:text-slate-600 transition-colors" />
                        </div>

                        <div class="relative group">
                            <select v-model="selectedClassId" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[9px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer hover:bg-slate-50/50 transition-all">
                                <option value="">Every Class</option>
                                <option v-for="schoolClass in classes" :key="schoolClass.id" :value="String(schoolClass.id)">{{ schoolClass.name }}</option>
                            </select>
                            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none group-hover:text-slate-600 transition-colors" />
                        </div>

                        <div class="relative group">
                            <select v-model="selectedGender" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[9px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer hover:bg-slate-50/50 transition-all">
                                <option v-for="option in genderOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none group-hover:text-slate-600 transition-colors" />
                        </div>

                        <div class="relative group">
                            <select v-model="selectedBoardingStatus" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[9px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer hover:bg-slate-50/50 transition-all">
                                <option v-for="option in boardingOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none group-hover:text-slate-600 transition-colors" />
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="overflow-x-auto scrollbar-hide">
                    <table class="w-full text-left min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100">
                                <th class="w-14 h-14 px-6">
                                     <button @click="toggleAllLearners" class="h-5 w-5 rounded-md border-2 border-slate-200 flex items-center justify-center transition-all bg-white hover:border-blue-400" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white shadow-md' : ''">
                                          <component :is="allSelected ? CheckSquare : Square" class="h-3.5 w-3.5" />
                                     </button>
                                </th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em]">Learner Profile</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em]">Placement Context</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em]">Live Status</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em]">Entry Date</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr 
                                v-for="learner in learners.data" 
                                :key="learner.id" 
                                class="hover:bg-blue-50/30 transition-colors group cursor-pointer"
                                @click="router.visit(`/students/${learner.id}`)"
                            >
                                <td class="px-6 py-4 text-center" @click.stop>
                                     <button @click="toggleLearner(learner.id)" class="h-5 w-5 rounded-md border-2 border-slate-200 flex items-center justify-center transition-all bg-white hover:border-blue-400" :class="selectedLearnerIds.includes(learner.id) ? 'bg-blue-600 border-blue-600 text-white shadow-md' : ''">
                                          <component :is="selectedLearnerIds.includes(learner.id) ? CheckSquare : Square" class="h-3.5 w-3.5" />
                                     </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div v-if="learner.photo_url" class="h-11 w-11 flex-shrink-0 rounded-xl overflow-hidden shadow-sm border border-slate-100 group-hover:border-blue-400 transition-all transform group-hover:scale-105 duration-300">
                                            <img :src="learner.photo_url" class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="h-11 w-11 flex-shrink-0 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center font-black text-blue-600 text-[10px] transform group-hover:scale-105 transition-all duration-300 shadow-inner">
                                             {{ learner.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-black text-slate-900 group-hover:text-blue-700 transition-colors uppercase tracking-tight text-xs truncate">{{ learner.name }}</span>
                                            <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-0.5">{{ learner.admission_number || 'NO REG ID' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-700 uppercase tracking-tight">{{ learner.class || 'UNASSIGNED' }}</span>
                                        <span class="text-[9px] text-slate-400 uppercase font-black tracking-widest mt-0.5">{{ learner.gender }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <Badge :variant="learner.status === 'active' ? 'outline' : 'secondary'" class="rounded-lg px-2.5 py-1 text-[8px] font-black uppercase tracking-widest border-0" :class="learner.status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-600'">
                                         <div v-if="learner.status === 'active'" class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5 shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></div>
                                         {{ learner.status }}
                                     </Badge>
                                </td>
                                <td class="px-6 py-4 text-[10px] text-slate-500 font-black uppercase tracking-widest">
                                    {{ learner.admission_date || 'PENDING' }}
                                </td>
                                <td class="px-6 py-4 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1 px-1">
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                            <Link :href="`/students/${learner.id}`" class="h-9 w-9 flex items-center justify-center rounded-xl hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100"><Eye class="h-4 w-4" /></Link>
                                            <Link v-if="hasPermission('students.update')" :href="`/students/${learner.id}/edit`" class="h-9 w-9 flex items-center justify-center rounded-xl hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-all border border-transparent hover:border-amber-100"><Edit class="h-4 w-4" /></Link>
                                        </div>
                                        
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-slate-50"><MoreHorizontal class="h-4.5 w-4.5 text-slate-400" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-slate-100 shadow-xl font-black text-[9px] uppercase tracking-widest">
                                                <DropdownMenuItem as-child class="rounded-lg"><Link :href="`/students/${learner.id}`"><Eye class="mr-2 h-3.5 w-3.5" /> View Registry</Link></DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('students.update')" as-child class="rounded-lg">
                                                    <Link :href="`/students/enrollments/create?student_id=${learner.id}`">
                                                        <UserPlus class="mr-2 h-3.5 w-3.5 text-blue-500" /> Allocate Context
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('students.update')" as-child class="rounded-lg"><Link :href="`/students/${learner.id}/edit`"><Edit class="mr-2 h-3.5 w-3.5" /> Modify Profile</Link></DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1" />
                                                <DropdownMenuItem v-if="learner.status !== 'suspended' && hasPermission('students.update')" @click="openActionModal('suspend', learner)" class="rounded-lg"><ShieldAlert class="mr-2 h-3.5 w-3.5 text-amber-500" /> Terminate Access</DropdownMenuItem>
                                                <DropdownMenuItem v-else-if="hasPermission('students.update')" @click="openActionModal('activate', learner)" class="rounded-lg"><CheckCircle2 class="mr-2 h-3.5 w-3.5 text-emerald-500" /> Restore Access</DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('students.delete')" class="text-rose-600 rounded-lg group" @click="openActionModal('delete', learner)"><Trash2 class="mr-2 h-3.5 w-3.5 opacity-70 group-hover:opacity-100" /> Purge Entry</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="learners.data.length === 0">
                                <td colspan="6" class="px-6 py-24 text-center text-slate-400 italic font-black uppercase tracking-widest text-[10px]">
                                    No records identified in registry
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 sm:px-6 py-6 border-t border-slate-50 bg-slate-50/30 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest order-2 md:order-1">{{ pageLabel }}</p>
                    <div class="flex items-center justify-between md:justify-end gap-6 order-1 md:order-2">
                        <div class="flex items-center gap-3">
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest shrink-0">Limit</span>
                             <select v-model="perPage" class="bg-white text-[10px] font-black border border-slate-200 px-3 py-1.5 rounded-xl outline-none focus:ring-2 focus:ring-blue-600/5 cursor-pointer uppercase tracking-widest appearance-none pr-8 relative">
                                 <option :value="15">15 Units</option>
                                 <option :value="50">50 Units</option>
                                 <option :value="100">100 Units</option>
                             </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" class="h-9 px-4 rounded-xl border-slate-200 font-black text-[9px] uppercase tracking-widest bg-white" :disabled="learners.current_page <= 1" @click="applyFilters(learners.current_page - 1)">Prev</Button>
                            <div class="flex items-center justify-center min-w-[70px] h-9 px-3 rounded-xl bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest shadow-lg shadow-slate-900/20">
                                {{ learners.current_page }} / {{ learners.last_page }}
                            </div>
                            <Button variant="outline" size="sm" class="h-9 px-4 rounded-xl border-slate-200 font-black text-[9px] uppercase tracking-widest bg-white" :disabled="learners.current_page >= learners.last_page" @click="applyFilters(learners.current_page + 1)">Next</Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Modals -->
        <Dialog :open="confirmOpen" @update:open="closeActionModal">
            <DialogContent class="sm:max-w-[440px] rounded-xl border-slate-100 p-0 overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="p-8 space-y-6 text-center">
                    <div class="mx-auto h-20 w-20 rounded-2xl flex items-center justify-center shadow-xl mb-6 shadow-blue-500/10" :class="confirmMode === 'delete' || confirmMode === 'bulkDelete' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'">
                        <Trash2 v-if="confirmMode === 'delete' || confirmMode === 'bulkDelete'" class="h-10 w-10" />
                        <ShieldAlert v-else class="h-10 w-10" />
                    </div>
                    
                    <div class="space-y-3">
                        <h2 class="text-xl font-black text-slate-900 uppercase italic tracking-tight">{{ modalTitle }}</h2>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-relaxed opacity-80 px-6">
                            {{ modalMessage }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 pt-6">
                        <Button
                            :variant="confirmMode === 'delete' || confirmMode === 'bulkDelete' ? 'destructive' : 'default'"
                            class="h-14 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl transition-all border-0 italic"
                            :class="confirmMode === 'delete' || confirmMode === 'bulkDelete' ? 'bg-red-600 hover:bg-red-700 shadow-red-500/20' : 'bg-slate-900 hover:bg-slate-800 shadow-slate-900/10'"
                            :disabled="actionForm.processing"
                            @click="confirmAction"
                        >
                            {{ actionForm.processing ? 'EXECUTING_PROTOCOL...' : 'CONFIRM_AUTHORIZATION' }}
                        </Button>
                        <Button variant="ghost" class="h-12 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all" @click="closeActionModal">ABORT_MISSION</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="promoteOpen" @update:open="closePromoteModal">
            <DialogContent class="sm:max-w-[500px] rounded-xl border-emerald-100 p-0 overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="bg-emerald-600 px-10 py-8 text-white relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 h-32 w-32 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="h-16 w-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-xl">
                            <ArrowUpCircle class="h-10 w-10 text-white" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-black uppercase italic tracking-tight leading-none">Global Promotion</h2>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] mt-2 italic opacity-80">Registry Advancement Protocol</p>
                        </div>
                    </div>
                </div>

                <div class="p-10 space-y-8">
                    <div class="rounded-[2rem] bg-emerald-50/50 p-6 border border-emerald-100 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest italic opacity-70">Batched Units</p>
                            <p class="text-3xl font-black text-emerald-700 italic tracking-tighter">{{ selectedCount }} <span class="text-xs uppercase tracking-widest opacity-60">Nodes</span></p>
                        </div>
                        <CheckCircle2 class="h-10 w-10 text-emerald-500/30" />
                    </div>

                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-relaxed italic opacity-80 px-2">
                        SELECTED LEARNERS WILL BE PROPAGATED TO THE NEXT SEQUENTIAL GRADE UPON SYSTEM VALIDATION. THIS ACTION IS IRREVERSIBLE ONCE COMMITTED TO THE CORE REGISTRY.
                    </p>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50/30 p-5 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                            <ShieldAlert class="h-5 w-5" />
                        </div>
                        <p class="text-[9px] font-black text-blue-800 uppercase tracking-widest leading-relaxed italic">Example Transformation:<br/><span class="text-slate-500">GRADE 8 WEST</span> → <span class="text-blue-600 font-bold">GRADE 9 WEST</span></p>
                    </div>

                    <div class="flex flex-col gap-3 pt-4">
                        <Button 
                            class="h-14 rounded-xl bg-slate-900 text-white hover:bg-slate-800 font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-slate-900/10 transition-all border-0 italic" 
                            :disabled="promotionForm.processing" 
                            @click="promoteSelectedLearners"
                        >
                            {{ promotionForm.processing ? 'ADVANCING_REGISTRY...' : 'INITIATE_PROMOTION_CYCLE' }}
                        </Button>
                        <Button variant="ghost" class="h-12 rounded-xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all" @click="closePromoteModal">CANCEL_SEQUENCE</Button>
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

