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
import { Dialog, DialogContent } from '@/components/ui/dialog';
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
    {
        title: props.group.class_name,
        href: `/students/enrollments/groups/${props.group.class_id}`,
    },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedLearnerStatus = ref(props.filters.learner_status ?? 'all');
const selectedEnrollmentStatus = ref(props.filters.enrollment_status ?? 'all');
const showFilters = ref(true);
const actionForm = useForm({});
const confirmOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'demote'>(
    'suspend',
);
const selectedLearner = ref<GroupLearnerRow | null>(null);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        `/students/enrollments/groups/${props.group.class_id}`,
        {
            search: searchQuery.value || undefined,
            learner_status:
                selectedLearnerStatus.value !== 'all'
                    ? selectedLearnerStatus.value
                    : undefined,
            enrollment_status:
                selectedEnrollmentStatus.value !== 'all'
                    ? selectedEnrollmentStatus.value
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
const isAllSelected = computed(
    () =>
        props.learners.length > 0 &&
        selectedEnrollmentIds.value.length === props.learners.length,
);

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedEnrollmentIds.value = [];
    } else {
        selectedEnrollmentIds.value = props.learners.map(
            (s) => s.enrollment_id,
        );
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

const openActionModal = (
    mode: 'suspend' | 'activate' | 'delete' | 'demote',
    learner: GroupLearnerRow,
) => {
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
        actionForm.patch(
            `/students/${selectedLearner.value.learner_id}/suspend`,
            { preserveScroll: true, onSuccess: closeActionModal },
        );
        return;
    }

    if (confirmMode.value === 'activate') {
        actionForm.patch(
            `/students/${selectedLearner.value.learner_id}/activate`,
            { preserveScroll: true, onSuccess: closeActionModal },
        );
        return;
    }

    if (confirmMode.value === 'demote') {
        actionForm.patch(
            `/students/${selectedLearner.value.learner_id}/demote`,
            { preserveScroll: true, onSuccess: closeActionModal },
        );
        return;
    }

    actionForm.delete(`/students/${selectedLearner.value.learner_id}`, {
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
        default:
            return 'Suspend';
    }
});

const modalMessage = computed(() => {
    if (!selectedLearner.value) return '';
    switch (confirmMode.value) {
        case 'activate':
            return `Activate [${selectedLearner.value.learner_name}]?`;
        case 'delete':
            return `Permanently delete [${selectedLearner.value.learner_name}]?`;
        case 'demote':
            return `Demote [${selectedLearner.value.learner_name}] to the previous grade?`;
        default:
            return `Suspend [${selectedLearner.value.learner_name}]?`;
    }
});
</script>

<template>
    <Head :title="group.class_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            v-if="showToast && flashSuccess"
            class="fixed top-4 right-4 z-[100] animate-in duration-500 slide-in-from-top-4"
        >
            <div
                class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-white px-5 py-4 shadow-lg"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50"
                >
                    <CheckCircle2 class="h-4 w-4 text-blue-600" />
                </div>
                <div>
                    <p
                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        Registry Sync
                    </p>
                    <p class="mt-0.5 text-xs font-bold text-slate-900">
                        {{ flashSuccess }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4 sm:gap-5">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg shadow-slate-900/10 sm:h-12 sm:w-12 sm:rounded-2xl"
                    >
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <h1
                            class="text-xl leading-none font-bold tracking-tight text-slate-900 sm:text-2xl"
                        >
                            {{ group.class_name }}
                        </h1>
                        <p
                            class="mt-2 text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase sm:text-xs"
                        >
                            {{ group.grade_name || 'Level Unknown'
                            }}<span v-if="group.stream_name" class="mx-1"
                                >•</span
                            >{{ group.stream_name
                            }}<span
                                v-if="group.academic_year"
                                class="mx-1 hidden sm:inline"
                                >•</span
                            ><span class="hidden uppercase sm:inline">{{
                                group.academic_year
                            }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button
                        v-if="selectedEnrollmentIds.length > 0"
                        variant="destructive"
                        size="sm"
                        @click="deleteSelected"
                        class="h-10 flex-1 rounded-xl px-4 text-xs font-bold tracking-tight uppercase shadow-xl shadow-red-500/10 sm:h-11 sm:flex-none sm:px-6"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        PURGE ({{ selectedEnrollmentIds.length }})
                    </Button>
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:h-11 sm:flex-none sm:px-6"
                    >
                        <Link href="/students/enrollments">Groups</Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 px-1 sm:gap-4">
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-blue-100 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        Pop. Index
                    </div>
                    <div
                        class="text-2xl font-bold text-slate-900 tabular-nums sm:text-3xl"
                    >
                        {{ group.total_learners }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-l-4 border-slate-100 border-l-emerald-500 bg-white p-4 shadow-sm transition-all hover:border-blue-100 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        Live Nodes
                    </div>
                    <div
                        class="text-2xl font-bold text-emerald-600 tabular-nums sm:text-3xl"
                    >
                        {{ group.active_learners }}
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5 md:flex-row md:items-center"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="LOCATE NODE..."
                        class="h-11 rounded-xl border-slate-200 bg-slate-50/50 pl-11 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white sm:h-12"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showFilters = !showFilters"
                        class="h-11 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:flex-none"
                    >
                        <Filter class="mr-2 h-4 w-4 text-slate-400" />
                        {{ showFilters ? 'HIDE' : 'CONFIG' }}
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="clearFilters"
                        class="h-11 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:flex-none"
                        >RESET</Button
                    >
                </div>
            </div>

            <div
                v-if="showFilters"
                class="grid animate-in gap-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-lg duration-300 slide-in-from-top-4 sm:p-8 md:grid-cols-2"
            >
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Registry Status</label
                    >
                    <div class="relative">
                        <select
                            v-model="selectedLearnerStatus"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                        >
                            <option
                                v-for="option in learnerStatusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                    </div>
                </div>
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Assignment State</label
                    >
                    <div class="relative">
                        <select
                            v-model="selectedEnrollmentStatus"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                        >
                            <option
                                v-for="option in enrollmentStatusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm"
            >
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/50"
                            >
                                <th class="w-[60px] px-6 py-4">
                                    <div
                                        class="flex items-center justify-center"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 cursor-pointer rounded-lg border-slate-300 text-slate-900 transition-all focus:ring-slate-900"
                                            :checked="isAllSelected"
                                            @change="toggleSelectAll"
                                        />
                                    </div>
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Manifest Handle
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Entity ID
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Context
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Heartbeat
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Operation
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="learners.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <Users
                                        class="mx-auto mb-4 h-10 w-10 text-slate-100"
                                    />
                                    <p
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Registry Void
                                    </p>
                                </td>
                            </tr>
                            <tr
                                v-for="learner in learners"
                                :key="learner.enrollment_id"
                                class="group transition-colors hover:bg-slate-50/50"
                                :class="{
                                    'bg-blue-50/40':
                                        selectedEnrollmentIds.includes(
                                            learner.enrollment_id,
                                        ),
                                }"
                            >
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-center"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 cursor-pointer rounded-lg border-slate-200 text-slate-900 transition-all focus:ring-slate-900"
                                            :checked="
                                                selectedEnrollmentIds.includes(
                                                    learner.enrollment_id,
                                                )
                                            "
                                            @change="
                                                toggleSelection(
                                                    learner.enrollment_id,
                                                )
                                            "
                                        />
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div
                                        class="text-sm leading-none font-bold text-slate-900"
                                    >
                                        {{ learner.learner_name }}
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <Badge
                                            variant="outline"
                                            class="h-4 rounded-md border-slate-100 bg-slate-50 px-1.5 text-xs font-medium tracking-tight text-slate-500 uppercase"
                                            >{{
                                                learner.enrollment_type.replace(
                                                    '_',
                                                    ' ',
                                                )
                                            }}</Badge
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="text-xs font-bold text-blue-600"
                                        >{{
                                            learner.admission_number || '—'
                                        }}</span
                                    >
                                </td>
                                <td class="px-4 py-4">
                                    <Badge
                                        :class="[
                                            'rounded-md px-2 py-0.5 text-xs font-medium tracking-tight uppercase',
                                            learner.enrollment_status ===
                                            'active'
                                                ? 'border-emerald-100 bg-emerald-50 text-emerald-600'
                                                : 'border-slate-200 bg-slate-100 text-slate-500',
                                        ]"
                                        variant="outline"
                                        >{{ learner.enrollment_status }}</Badge
                                    >
                                </td>
                                <td class="px-4 py-4">
                                    <Badge
                                        :class="[
                                            'rounded-md px-2 py-0.5 text-xs font-medium tracking-tight uppercase',
                                            learner.learner_status === 'active'
                                                ? 'border-blue-100 bg-blue-50 text-blue-600'
                                                : 'border-amber-100 bg-amber-50 text-amber-600',
                                        ]"
                                        variant="outline"
                                        >{{ learner.learner_status }}</Badge
                                    >
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-9 w-9 rounded-xl transition-all hover:bg-slate-900 hover:text-white"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="w-48 rounded-2xl border-slate-100 p-2 shadow-lg"
                                        >
                                            <DropdownMenuItem
                                                as-child
                                                class="cursor-pointer rounded-xl px-4 py-2.5 text-xs font-medium tracking-tight uppercase"
                                            >
                                                <Link
                                                    :href="`/students/${learner.learner_id}`"
                                                >
                                                    <Eye
                                                        class="mr-3 h-4 w-4 text-slate-400"
                                                    />
                                                    View entity
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                as-child
                                                class="cursor-pointer rounded-xl px-4 py-2.5 text-xs font-medium tracking-tight uppercase"
                                            >
                                                <Link
                                                    :href="`/students/${learner.learner_id}/edit`"
                                                >
                                                    <Edit
                                                        class="mr-3 h-4 w-4 text-slate-400"
                                                    />
                                                    Modify node
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="
                                                    learner.learner_status !==
                                                    'suspended'
                                                "
                                                @click="
                                                    openActionModal(
                                                        'suspend',
                                                        learner,
                                                    )
                                                "
                                                class="cursor-pointer rounded-xl px-4 py-2.5 text-xs font-medium tracking-tight uppercase"
                                            >
                                                <ShieldAlert
                                                    class="mr-3 h-4 w-4 text-amber-500"
                                                />
                                                Suspend
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-else
                                                @click="
                                                    openActionModal(
                                                        'activate',
                                                        learner,
                                                    )
                                                "
                                                class="cursor-pointer rounded-xl px-4 py-2.5 text-xs font-medium tracking-tight uppercase"
                                            >
                                                <CheckCircle2
                                                    class="mr-3 h-4 w-4 text-emerald-500"
                                                />
                                                Activate
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                :disabled="!learner.can_demote"
                                                @click="
                                                    openActionModal(
                                                        'demote',
                                                        learner,
                                                    )
                                                "
                                                class="cursor-pointer rounded-xl px-4 py-2.5 text-xs font-medium tracking-tight uppercase"
                                            >
                                                <ArrowDownCircle
                                                    class="mr-3 h-4 w-4 text-blue-500"
                                                />
                                                Demote level
                                            </DropdownMenuItem>
                                            <div
                                                class="my-1 h-px bg-slate-50"
                                            ></div>
                                            <DropdownMenuItem
                                                class="cursor-pointer rounded-xl px-4 py-2.5 text-xs font-medium tracking-tight text-red-600 uppercase focus:bg-red-50 focus:text-red-700"
                                                @click="
                                                    openActionModal(
                                                        'delete',
                                                        learner,
                                                    )
                                                "
                                            >
                                                <Trash2 class="mr-3 h-4 w-4" />
                                                Purge entity
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

        <Dialog :open="confirmOpen" @update:open="closeActionModal">
            <DialogContent
                class="animate-in overflow-hidden rounded-xl border-slate-100 p-0 shadow-lg duration-300 zoom-in-95 sm:max-w-[440px]"
            >
                <div class="space-y-6 p-8 text-center">
                    <div
                        class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-xl shadow-xl shadow-red-500/10"
                        :class="
                            confirmMode === 'activate'
                                ? 'bg-emerald-50 text-emerald-600 shadow-emerald-500/10'
                                : 'bg-red-50 text-red-600'
                        "
                    >
                        <CheckCircle2
                            v-if="confirmMode === 'activate'"
                            class="h-10 w-10"
                        />
                        <AlertTriangle v-else class="h-10 w-10" />
                    </div>

                    <div class="space-y-3">
                        <h2
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            {{ modalTitle }} Context
                        </h2>
                        <p
                            class="px-6 text-xs leading-relaxed font-bold tracking-tight text-slate-400 uppercase opacity-80"
                        >
                            Core Registry Modification Inquiry
                        </p>
                    </div>

                    <p
                        class="px-4 text-xs leading-relaxed font-bold tracking-tight text-slate-600 uppercase"
                    >
                        {{ modalMessage }}
                    </p>

                    <div class="flex flex-col gap-3 pt-6">
                        <Button
                            :variant="
                                confirmMode === 'delete'
                                    ? 'destructive'
                                    : 'default'
                            "
                            class="h-14 rounded-2xl border-0 text-sm font-bold tracking-tight uppercase shadow-xl transition-all"
                            :class="
                                confirmMode === 'delete'
                                    ? 'bg-red-600 shadow-red-500/20 hover:bg-red-700'
                                    : 'bg-slate-900 shadow-slate-900/10 hover:bg-slate-800'
                            "
                            :disabled="actionForm.processing"
                            @click="confirmAction"
                        >
                            {{
                                actionForm.processing
                                    ? 'Processing...'
                                    : 'EXECUTE_OVERRIDE'
                            }}
                        </Button>
                        <Button
                            variant="ghost"
                            class="h-12 rounded-2xl text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900"
                            @click="closeActionModal"
                            >ABORT_OP</Button
                        >
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Bulk Delete Modal -->
        <Dialog :open="bulkDeleteOpen" @update:open="bulkDeleteOpen = false">
            <DialogContent
                class="animate-in overflow-hidden rounded-xl border-red-100 p-0 shadow-lg duration-300 zoom-in-95 sm:max-w-[440px]"
            >
                <div class="space-y-6 p-8 text-center">
                    <div
                        class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-xl bg-red-900 text-white shadow-lg shadow-red-900/20"
                    >
                        <Trash2 class="h-10 w-10" />
                    </div>

                    <div class="space-y-3">
                        <h2
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            Bulk Purge Op
                        </h2>
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase"
                        >
                            High Impact Batch Operation
                        </p>
                    </div>

                    <p
                        class="px-4 text-xs leading-relaxed font-bold tracking-tight text-slate-600 uppercase"
                    >
                        INITIATING PURGE FOR
                        <span class="font-bold text-red-600">{{
                            selectedEnrollmentIds.length
                        }}</span>
                        REGISTRY NODES. THIS ACTION IS DESTRUCTIVE AND
                        TERMINATES ALL ASSIGNMENT LINKS.
                    </p>

                    <div class="flex flex-col gap-3 pt-6">
                        <Button
                            variant="destructive"
                            class="h-14 rounded-2xl border-0 bg-red-600 text-sm font-bold tracking-tight text-white uppercase shadow-xl shadow-red-500/20 transition-all hover:bg-red-700"
                            :disabled="actionForm.processing"
                            @click="confirmBulkDelete"
                        >
                            {{
                                actionForm.processing
                                    ? 'PURGING_REGISTRY...'
                                    : 'AUTHORIZE_PURGE'
                            }}
                        </Button>
                        <Button
                            variant="ghost"
                            class="h-12 rounded-2xl text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900"
                            @click="bulkDeleteOpen = false"
                            >CANCEL_PURGE</Button
                        >
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
