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
    total_learners: number;
    active_learners: number;
}

interface GroupLearnerRow {
    enrollment_id: number;
    learner_id: number;
    learner_name: string;
    admission_number: string | null;
    learner_status: string | null;
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
    learners: GroupLearnerRow[];
    filters: {
        search: string;
        learner_status: string;
        enrollment_status: string;
    };
    learnerStatusOptions: Array<{ value: string; label: string }>;
    enrollmentStatusOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const page = usePage<{ flash?: { success?: string; error?: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learners', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
    { title: props.group.class_name, href: `/students/enrollments/groups/${props.group.class_id}` },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedLearnerStatus = ref(props.filters.learner_status ?? 'all');
const selectedEnrollmentStatus = ref(props.filters.enrollment_status ?? 'all');
const showFilters = ref(true);
const actionForm = useForm({});
const confirmOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'demote'>('suspend');
const selectedLearner = ref<GroupLearnerRow | null>(null);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        `/students/enrollments/groups/${props.group.class_id}`,
        {
            search: searchQuery.value || undefined,
            learner_status: selectedLearnerStatus.value !== 'all' ? selectedLearnerStatus.value : undefined,
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
watch([selectedLearnerStatus, selectedEnrollmentStatus], () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedLearnerStatus.value = 'all';
    selectedEnrollmentStatus.value = 'all';
    applyFilters();
};

const selectedEnrollmentIds = ref<number[]>([]);
const isAllSelected = computed(() => props.learners.length > 0 && selectedEnrollmentIds.value.length === props.learners.length);

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedEnrollmentIds.value = [];
    } else {
        selectedEnrollmentIds.value = props.learners.map((s) => s.enrollment_id);
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

const openActionModal = (mode: 'suspend' | 'activate' | 'delete' | 'demote', learner: GroupLearnerRow) => {
    confirmMode.value = mode;
    selectedLearner.value = learner;
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
    selectedLearner.value = null;
};

const confirmAction = () => {
    if (!selectedLearner.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedLearner.value.learner_id}/suspend`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedLearner.value.learner_id}/activate`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    if (confirmMode.value === 'demote') {
        actionForm.patch(`/students/${selectedLearner.value.learner_id}/demote`, { preserveScroll: true, onSuccess: closeActionModal });
        return;
    }

    actionForm.delete(`/students/${selectedLearner.value.learner_id}`, { preserveScroll: true, onSuccess: closeActionModal });
};

const modalTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate': return 'Activate';
        case 'delete': return 'Delete';
        case 'demote': return 'Demote';
        default: return 'Suspend';
    }
});

const modalMessage = computed(() => {
    if (!selectedLearner.value) return '';
    switch (confirmMode.value) {
        case 'activate': return `Activate [${selectedLearner.value.learner_name}]?`;
        case 'delete': return `Permanently delete [${selectedLearner.value.learner_name}]?`;
        case 'demote': return `Demote [${selectedLearner.value.learner_name}] to the previous grade?`;
        default: return `Suspend [${selectedLearner.value.learner_name}]?`;
    }
});
</script>

<template>
    <Head :title="group.class_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-[100] animate-in slide-in-from-top-4 duration-500">
            <div class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-white px-5 py-4 shadow-2xl">
                <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center">
                    <CheckCircle2 class="h-4 w-4 text-blue-600" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Registry Sync</p>
                    <p class="text-xs font-black text-slate-900 mt-0.5">{{ flashSuccess }}</p>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-xl sm:rounded-2xl bg-slate-900 text-white shadow-lg shadow-slate-900/10 shrink-0">
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase italic leading-none">{{ group.class_name }}</h1>
                        <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-widest mt-2 italic opacity-70">
                            {{ group.grade_name || 'Level Unknown' }}<span v-if="group.stream_name" class="mx-1">•</span>{{ group.stream_name }}<span v-if="group.academic_year" class="mx-1 sm:inline hidden">•</span><span class="sm:inline hidden uppercase">{{ group.academic_year }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button v-if="selectedEnrollmentIds.length > 0" variant="destructive" size="sm" @click="deleteSelected" class="h-10 sm:h-11 px-4 sm:px-6 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-red-500/10 flex-1 sm:flex-none">
                        <Trash2 class="mr-2 h-4 w-4" />
                        PURGE ({{ selectedEnrollmentIds.length }})
                    </Button>
                    <Button variant="outline" as-child class="h-10 sm:h-11 px-4 sm:px-6 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex-1 sm:flex-none">
                        <Link href="/students/enrollments">Groups</Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 px-1">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-blue-100">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Pop. Index</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums italic">{{ group.total_learners }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-blue-100 border-l-4 border-l-emerald-500">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Live Nodes</div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 tabular-nums italic">{{ group.active_learners }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input v-model="searchQuery" placeholder="LOCATE NODE..." class="h-11 sm:h-12 pl-11 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters" class="h-11 px-4 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex-1 sm:flex-none">
                        <Filter class="mr-2 h-4 w-4 text-slate-400" />
                        {{ showFilters ? 'HIDE' : 'CONFIG' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters" class="h-11 px-4 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex-1 sm:flex-none">RESET</Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-3xl border border-slate-100 bg-white p-6 sm:p-8 md:grid-cols-2 animate-in slide-in-from-top-4 duration-300 shadow-lg">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Registry Status</label>
                    <div class="relative">
                        <select v-model="selectedLearnerStatus" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                            <option v-for="option in learnerStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assignment State</label>
                    <div class="relative">
                        <select v-model="selectedEnrollmentStatus" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                            <option v-for="option in enrollmentStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white overflow-hidden shadow-sm">
                <div class="overflow-x-auto scrollbar-hide">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="w-[60px] px-6 py-4">
                                    <div class="flex items-center justify-center">
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900 transition-all cursor-pointer"
                                            :checked="isAllSelected"
                                            @change="toggleSelectAll"
                                        />
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Manifest Handle</th>
                                <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Entity ID</th>
                                <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Context</th>
                                <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Heartbeat</th>
                                <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Operation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="learners.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <Users class="h-10 w-10 text-slate-100 mx-auto mb-4" />
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Registry Void</p>
                                </td>
                            </tr>
                            <tr v-for="learner in learners" :key="learner.enrollment_id" class="transition-colors hover:bg-slate-50/50 group" :class="{ 'bg-blue-50/40': selectedEnrollmentIds.includes(learner.enrollment_id) }">
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center">
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 rounded-lg border-slate-200 text-slate-900 focus:ring-slate-900 transition-all cursor-pointer"
                                            :checked="selectedEnrollmentIds.includes(learner.enrollment_id)"
                                            @change="toggleSelection(learner.enrollment_id)"
                                        />
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-black text-slate-900 text-[11px] uppercase italic leading-none">{{ learner.learner_name }}</div>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <Badge variant="outline" class="h-4 rounded-md px-1.5 text-[8px] font-black uppercase tracking-widest bg-slate-50 text-slate-500 border-slate-100">{{ learner.enrollment_type.replace('_', ' ') }}</Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-[10px] font-black text-blue-600 uppercase italic">{{ learner.admission_number || '—' }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <Badge :class="[
                                        'rounded-md px-2 py-0.5 text-[8px] font-black uppercase tracking-widest italic',
                                        learner.enrollment_status === 'active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200'
                                    ]" variant="outline">{{ learner.enrollment_status }}</Badge>
                                </td>
                                <td class="px-4 py-4">
                                     <Badge :class="[
                                        'rounded-md px-2 py-0.5 text-[8px] font-black uppercase tracking-widest italic',
                                        learner.learner_status === 'active' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100'
                                    ]" variant="outline">{{ learner.learner_status }}</Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-slate-900 hover:text-white transition-all">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                         <DropdownMenuContent align="end" class="w-48 rounded-2xl p-2 border-slate-100 shadow-2xl">
                                            <DropdownMenuItem as-child class="rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest cursor-pointer">
                                                <Link :href="`/students/${learner.learner_id}`">
                                                    <Eye class="mr-3 h-4 w-4 text-slate-400" /> View entity
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child class="rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest cursor-pointer">
                                                <Link :href="`/students/${learner.learner_id}/edit`">
                                                    <Edit class="mr-3 h-4 w-4 text-slate-400" /> Modify node
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="learner.learner_status !== 'suspended'"
                                                @click="openActionModal('suspend', learner)"
                                                class="rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest cursor-pointer"
                                            >
                                                <ShieldAlert class="mr-3 h-4 w-4 text-amber-500" /> Suspend
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-else
                                                @click="openActionModal('activate', learner)"
                                                class="rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest cursor-pointer"
                                            >
                                                <CheckCircle2 class="mr-3 h-4 w-4 text-emerald-500" /> Activate
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                :disabled="!learner.can_demote"
                                                @click="openActionModal('demote', learner)"
                                                class="rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest cursor-pointer"
                                            >
                                                <ArrowDownCircle class="mr-3 h-4 w-4 text-blue-500" /> Demote level
                                            </DropdownMenuItem>
                                            <div class="h-px bg-slate-50 my-1"></div>
                                            <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-red-600 focus:bg-red-50 focus:text-red-700 cursor-pointer" @click="openActionModal('delete', learner)">
                                                <Trash2 class="mr-3 h-4 w-4" /> Purge entity
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

        <div v-if="confirmOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in duration-300" @click.self="closeActionModal">
            <div class="w-full max-w-md rounded-[2.5rem] bg-white p-8 shadow-2xl border border-slate-100 scale-in-center animate-in zoom-in-95 duration-200">
                <div class="h-16 w-16 rounded-3xl bg-red-50 flex items-center justify-center mb-6">
                    <AlertTriangle class="h-8 w-8 text-red-500" />
                </div>
                <h2 class="text-2xl font-black text-slate-900 uppercase italic tracking-tight mb-2">{{ modalTitle }} Context</h2>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic opacity-70 mb-6">Core Registry Modification Inquiry</p>
                
                <p class="text-sm font-black text-slate-600 uppercase tracking-widest leading-relaxed">{{ modalMessage }}</p>
                
                <div class="mt-10 flex flex-col sm:flex-row gap-3">
                    <Button variant="outline" @click="closeActionModal" class="h-12 flex-1 rounded-2xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all">Abort Op</Button>
                    <Button :variant="confirmMode === 'delete' ? 'destructive' : 'default'" :disabled="actionForm.processing" @click="confirmAction" class="h-12 flex-1 rounded-2xl bg-slate-900 text-white hover:bg-slate-800 font-black text-[10px] uppercase tracking-widest shadow-xl shadow-slate-900/10 transition-all">
                        <Loader2 v-if="actionForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Execute Override
                    </Button>
                </div>
            </div>
        </div>

        <!-- Bulk Delete Modal -->
        <div v-if="bulkDeleteOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in duration-300" @click.self="bulkDeleteOpen = false">
            <div class="w-full max-w-md rounded-[2.5rem] bg-white p-8 shadow-2xl border border-slate-100 scale-in-center animate-in zoom-in-95 duration-200">
                <div class="h-16 w-16 rounded-3xl bg-red-900 flex items-center justify-center mb-6 shadow-xl shadow-red-900/20">
                    <Trash2 class="h-8 w-8 text-white" />
                </div>
                <h2 class="text-2xl font-black text-slate-900 uppercase italic tracking-tight mb-2">Bulk Purge Op</h2>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic opacity-70 mb-6">High Impact Batch Operation</p>
                
                <p class="text-sm font-black text-slate-600 uppercase tracking-widest leading-relaxed">
                    INITIATING PURGE FOR <span class="text-red-600">{{ selectedEnrollmentIds.length }}</span> REGISTRY NODES. THIS ACTION IS DESTRUCTIVE AND TERMINATES ALL ASSIGNMENT LINKS IN THIS CONTEXT.
                </p>
                
                <div class="mt-10 flex flex-col sm:flex-row gap-3">
                    <Button variant="outline" @click="bulkDeleteOpen = false" class="h-12 flex-1 rounded-2xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all">Cancel Purge</Button>
                    <Button variant="destructive" :disabled="actionForm.processing" @click="confirmBulkDelete" class="h-12 flex-1 rounded-2xl bg-red-600 text-white hover:bg-red-700 font-black text-[10px] uppercase tracking-widest shadow-xl shadow-red-600/10 transition-all">
                        <Loader2 v-if="actionForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Authorize Purge
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
