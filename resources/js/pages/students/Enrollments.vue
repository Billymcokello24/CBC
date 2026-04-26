<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    ChevronDown,
    ChevronUp,
    Filter,
    Grid2x2,
    List,
    Search,
    Users,
    UserPlus,
    GraduationCap,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface EnrollmentGroup {
    class_id: number;
    class_name: string;
    grade_name: string | null;
    stream_name: string | null;
    academic_year: string | null;
    total_learners: number;
    active_learners: number;
    new_enrollments: number;
    promoted_learners: number;
}

interface Props {
    groups: EnrollmentGroup[];
    stats: {
        total: number;
        active: number;
        new: number;
        promoted: number;
    };
    filters: {
        search: string;
        status: string;
        enrollment_type: string;
        class_id: number | null;
        academic_year_id: number | null;
        view: 'grid' | 'list';
    };
    classes: Array<{ id: number; name: string }>;
    academicYears: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
    enrollmentTypeOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedEnrollmentType = ref(props.filters.enrollment_type ?? 'all');
const selectedClassId = ref(
    props.filters.class_id ? String(props.filters.class_id) : '',
);
const selectedAcademicYearId = ref(
    props.filters.academic_year_id
        ? String(props.filters.academic_year_id)
        : '',
);
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const showFilters = ref(true);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/students/enrollments',
        {
            search: searchQuery.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
            enrollment_type:
                selectedEnrollmentType.value !== 'all'
                    ? selectedEnrollmentType.value
                    : undefined,
            class_id: selectedClassId.value || undefined,
            academic_year_id: selectedAcademicYearId.value || undefined,
            view: selectedView.value,
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
watch(
    [
        selectedStatus,
        selectedEnrollmentType,
        selectedClassId,
        selectedAcademicYearId,
        selectedView,
    ],
    () => applyFilters(),
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedEnrollmentType.value = 'all';
    selectedClassId.value = '';
    selectedAcademicYearId.value = '';
    applyFilters();
};
</script>

<template>
    <Head title="Enrollment Groups" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4 sm:gap-5">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 sm:h-12 sm:w-12 sm:rounded-2xl"
                    >
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"
                        >
                            Enrollment Registry
                        </h1>
                        <p
                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase sm:text-xs"
                        >
                            Academic Assignments & History
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:h-11 sm:flex-none sm:px-6"
                    >
                        <Link href="/students">Directory</Link>
                    </Button>
                    <Button
                        as-child
                        class="h-10 flex-1 rounded-xl bg-slate-900 px-4 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 sm:h-11 sm:flex-none sm:px-6"
                    >
                        <Link href="/students/enrollments/create">
                            <UserPlus class="mr-2 h-4 w-4" /> Enroll
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 px-1 sm:gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-blue-100 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        Registry Load
                    </div>
                    <div
                        class="text-2xl font-bold text-slate-900 tabular-nums sm:text-3xl"
                    >
                        {{ stats.total.toLocaleString() }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-blue-100 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        Live Nodes
                    </div>
                    <div
                        class="text-2xl font-bold text-emerald-600 tabular-nums sm:text-3xl"
                    >
                        {{ stats.active.toLocaleString() }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-blue-100 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        New Entry
                    </div>
                    <div
                        class="text-2xl font-bold text-blue-600 tabular-nums sm:text-3xl"
                    >
                        {{ stats.new.toLocaleString() }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:border-blue-100 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                    >
                        Promoted
                    </div>
                    <div
                        class="text-2xl font-bold text-amber-600 tabular-nums sm:text-3xl"
                    >
                        {{ stats.promoted.toLocaleString() }}
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
                        placeholder="SCAN REGISTRY..."
                        class="h-11 rounded-xl border-slate-200 bg-slate-50/50 pl-11 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white sm:h-12"
                    />
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div
                        class="flex rounded-xl border border-slate-200/50 bg-slate-50 p-1"
                    >
                        <Button
                            variant="ghost"
                            :class="
                                selectedView === 'grid'
                                    ? 'border-slate-100 bg-white text-slate-900 shadow-sm'
                                    : 'border-transparent text-slate-400'
                            "
                            size="sm"
                            @click="selectedView = 'grid'"
                            class="h-9 rounded-lg border px-3 text-xs font-bold tracking-tight uppercase transition-all"
                        >
                            <Grid2x2 class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            variant="ghost"
                            :class="
                                selectedView === 'list'
                                    ? 'border-slate-100 bg-white text-slate-900 shadow-sm'
                                    : 'border-transparent text-slate-400'
                            "
                            size="sm"
                            @click="selectedView = 'list'"
                            class="ml-1 h-9 rounded-lg border px-3 text-xs font-bold tracking-tight uppercase transition-all"
                        >
                            <List class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showFilters = !showFilters"
                        class="h-11 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase hover:bg-slate-50 sm:flex-none"
                    >
                        <component
                            :is="showFilters ? ChevronUp : ChevronDown"
                            class="mr-2 h-4 w-4 text-slate-400"
                        />
                        Filters
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="clearFilters"
                        class="h-11 w-11 shrink-0 rounded-xl border-slate-200 p-0 transition-all hover:bg-slate-50"
                    >
                        <Filter class="h-4 w-4 text-slate-400" />
                    </Button>
                </div>
            </div>

            <div
                v-if="showFilters"
                class="grid animate-in gap-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm duration-300 slide-in-from-top-4 sm:p-8 md:grid-cols-2 lg:grid-cols-4"
            >
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Flow Status</label
                    >
                    <div class="relative">
                        <select
                            v-model="selectedStatus"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
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
                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                    </div>
                </div>
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Ingest Protocol</label
                    >
                    <div class="relative">
                        <select
                            v-model="selectedEnrollmentType"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                        >
                            <option
                                v-for="option in enrollmentTypeOptions"
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
                        >Class Context</label
                    >
                    <div class="relative">
                        <select
                            v-model="selectedClassId"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                        >
                            <option value="">All Contexts</option>
                            <option
                                v-for="schoolClass in classes"
                                :key="schoolClass.id"
                                :value="String(schoolClass.id)"
                            >
                                {{ schoolClass.name }}
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
                        >Academic Epoch</label
                    >
                    <div class="relative">
                        <select
                            v-model="selectedAcademicYearId"
                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                        >
                            <option value="">All Epochs</option>
                            <option
                                v-for="year in academicYears"
                                :key="year.id"
                                :value="String(year.id)"
                            >
                                {{ year.name }}
                            </option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                    </div>
                </div>
            </div>

            <div
                v-if="groups.length === 0"
                class="rounded-3xl border border-slate-100 bg-white p-16 text-center shadow-sm"
            >
                <Search class="mx-auto mb-4 h-12 w-12 text-slate-200" />
                <h3
                    class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                >
                    Null Set
                </h3>
                <p class="mt-1 text-xs tracking-tight text-slate-400 uppercase">
                    No registry groups found for current parameters.
                </p>
            </div>

            <div
                v-else-if="selectedView === 'grid'"
                class="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="group in groups"
                    :key="group.class_id"
                    :href="`/students/enrollments/groups/${group.class_id}`"
                    class="group flex flex-col rounded-xl border border-slate-100 bg-white p-6 transition-all hover:border-blue-400 hover:shadow-xl hover:shadow-blue-500/5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2
                                class="text-xl font-bold tracking-tight text-slate-900"
                            >
                                {{ group.class_name }}
                            </h2>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-blue-600 uppercase"
                            >
                                {{ group.grade_name || 'Level Unknown'
                                }}<span v-if="group.stream_name">
                                    • {{ group.stream_name }}</span
                                >
                            </p>
                            <div
                                class="mt-3 flex items-center gap-2 text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                            >
                                <Search class="h-3 w-3" />
                                {{ group.academic_year || 'No Epoch' }}
                            </div>
                        </div>
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg transition-transform duration-300 group-hover:scale-110"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div
                            class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition-all group-hover:border-slate-200 group-hover:bg-white"
                        >
                            <div
                                class="mb-1.5 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Learners
                            </div>
                            <div
                                class="text-lg font-bold text-slate-900 tabular-nums"
                            >
                                {{ group.total_learners }}
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition-all group-hover:border-slate-200 group-hover:bg-white"
                        >
                            <div
                                class="mb-1.5 text-xs leading-none font-bold tracking-tight text-emerald-500 uppercase"
                            >
                                Active
                            </div>
                            <div
                                class="text-lg font-bold text-emerald-600 tabular-nums"
                            >
                                {{ group.active_learners }}
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition-all group-hover:border-slate-200 group-hover:bg-white"
                        >
                            <div
                                class="mb-1.5 text-xs leading-none font-bold tracking-tight text-blue-500 uppercase"
                            >
                                New Entry
                            </div>
                            <div
                                class="text-lg font-bold text-blue-600 tabular-nums"
                            >
                                {{ group.new_enrollments }}
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition-all group-hover:border-slate-200 group-hover:bg-white"
                        >
                            <div
                                class="mb-1.5 text-xs leading-none font-bold tracking-tight text-amber-500 uppercase"
                            >
                                Promoted
                            </div>
                            <div
                                class="text-lg font-bold text-amber-600 tabular-nums"
                            >
                                {{ group.promoted_learners }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between border-t border-slate-50 pt-4 transition-colors group-hover:border-slate-100"
                    >
                        <span
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Access Manifest</span
                        >
                        <ChevronDown
                            class="h-4 w-4 -rotate-90 text-slate-300 transition-transform group-hover:translate-x-1"
                        />
                    </div>
                </Link>
            </div>

            <div
                v-else
                class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm"
            >
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/50"
                            >
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Context Group
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Epoch
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Nodes
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Active
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Promoted
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr
                                v-for="group in groups"
                                :key="group.class_id"
                                class="group transition-colors hover:bg-blue-50/30"
                            >
                                <td class="px-6 py-4">
                                    <div
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ group.class_name }}
                                    </div>
                                    <div
                                        class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        {{ group.grade_name || '??'
                                        }}<span v-if="group.stream_name">
                                            • {{ group.stream_name }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 text-xs font-bold text-slate-500"
                                >
                                    {{ group.academic_year || '—' }}
                                </td>
                                <td
                                    class="px-4 py-4 text-center text-sm font-bold"
                                >
                                    {{ group.total_learners }}
                                </td>
                                <td
                                    class="px-4 py-4 text-center text-sm font-bold text-emerald-600 tabular-nums"
                                >
                                    {{ group.active_learners }}
                                </td>
                                <td
                                    class="px-4 py-4 text-center text-sm font-bold text-amber-600 tabular-nums"
                                >
                                    {{ group.promoted_learners }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        as-child
                                        class="h-9 w-9 rounded-lg p-0 transition-all hover:bg-slate-900 hover:text-white"
                                    >
                                        <Link
                                            :href="`/students/enrollments/groups/${group.class_id}`"
                                        >
                                            <Search class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
