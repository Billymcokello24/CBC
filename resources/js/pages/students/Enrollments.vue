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
const selectedClassId = ref(props.filters.class_id ? String(props.filters.class_id) : '');
const selectedAcademicYearId = ref(props.filters.academic_year_id ? String(props.filters.academic_year_id) : '');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const showFilters = ref(true);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/students/enrollments',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            enrollment_type: selectedEnrollmentType.value !== 'all' ? selectedEnrollmentType.value : undefined,
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
watch([selectedStatus, selectedEnrollmentType, selectedClassId, selectedAcademicYearId, selectedView], () => applyFilters());

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
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-xl sm:rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 shrink-0">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase italic">Enrollment Registry</h1>
                        <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-widest mt-1 italic opacity-70">Academic Assignments & History</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button variant="outline" as-child class="h-10 sm:h-11 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest px-4 sm:px-6 hover:bg-slate-50 transition-all flex-1 sm:flex-none">
                        <Link href="/students">Directory</Link>
                    </Button>
                    <Button as-child class="h-10 sm:h-11 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-[10px] uppercase tracking-widest px-4 sm:px-6 shadow-xl shadow-slate-900/10 transition-all flex-1 sm:flex-none">
                        <Link href="/students/enrollments/create">
                            <UserPlus class="mr-2 h-4 w-4" /> Enroll
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 px-1">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-blue-100">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Registry Load</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums italic">{{ stats.total.toLocaleString() }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-blue-100">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Live Nodes</div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 tabular-nums italic">{{ stats.active.toLocaleString() }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-blue-100">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">New Entry</div>
                    <div class="text-2xl sm:text-3xl font-black text-blue-600 tabular-nums italic">{{ stats.new.toLocaleString() }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-blue-100">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Promoted</div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-600 tabular-nums italic">{{ stats.promoted.toLocaleString() }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input v-model="searchQuery" placeholder="SCAN REGISTRY..." class="h-11 sm:h-12 pl-11 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="flex bg-slate-50 p-1 rounded-xl border border-slate-200/50">
                        <Button variant="ghost" :class="selectedView === 'grid' ? 'bg-white shadow-sm text-slate-900 border-slate-100' : 'text-slate-400 border-transparent'" size="sm" @click="selectedView = 'grid'" class="h-9 px-3 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all border">
                            <Grid2x2 class="h-3.5 w-3.5" />
                        </Button>
                        <Button variant="ghost" :class="selectedView === 'list' ? 'bg-white shadow-sm text-slate-900 border-slate-100' : 'text-slate-400 border-transparent'" size="sm" @click="selectedView = 'list'" class="h-9 px-3 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all border ml-1">
                            <List class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters" class="h-11 px-4 rounded-xl border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 flex-1 sm:flex-none">
                        <component :is="showFilters ? ChevronUp : ChevronDown" class="mr-2 h-4 w-4 text-slate-400" />
                        Filters
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters" class="h-11 w-11 p-0 rounded-xl border-slate-200 hover:bg-slate-50 transition-all shrink-0">
                        <Filter class="h-4 w-4 text-slate-400" />
                    </Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-3xl border border-slate-100 bg-white p-6 sm:p-8 md:grid-cols-2 lg:grid-cols-4 shadow-sm animate-in slide-in-from-top-4 duration-300">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Flow Status</label>
                    <div class="relative">
                        <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ingest Protocol</label>
                    <div class="relative">
                        <select v-model="selectedEnrollmentType" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                            <option v-for="option in enrollmentTypeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Class Context</label>
                    <div class="relative">
                        <select v-model="selectedClassId" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                            <option value="">All Contexts</option>
                            <option v-for="schoolClass in classes" :key="schoolClass.id" :value="String(schoolClass.id)">{{ schoolClass.name }}</option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Epoch</label>
                    <div class="relative">
                        <select v-model="selectedAcademicYearId" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                            <option value="">All Epochs</option>
                            <option v-for="year in academicYears" :key="year.id" :value="String(year.id)">{{ year.name }}</option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>
            </div>

            <div v-if="groups.length === 0" class="rounded-3xl border border-slate-100 bg-white p-16 text-center shadow-sm">
                <Search class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Null Set</h3>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">No registry groups found for current parameters.</p>
            </div>

            <div v-else-if="selectedView === 'grid'" class="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="group in groups"
                    :key="group.class_id"
                    :href="`/students/enrollments/groups/${group.class_id}`"
                    class="rounded-[2rem] border border-slate-100 bg-white p-6 transition-all hover:border-blue-400 hover:shadow-xl hover:shadow-blue-500/5 group flex flex-col"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 uppercase italic tracking-tight">{{ group.class_name }}</h2>
                            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mt-1">
                                {{ group.grade_name || 'Level Unknown' }}<span v-if="group.stream_name"> • {{ group.stream_name }}</span>
                            </p>
                            <div class="mt-3 flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic opacity-60">
                                <Search class="h-3 w-3" />
                                {{ group.academic_year || 'No Epoch' }}
                            </div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50/50 border border-slate-100 p-4 transition-all group-hover:bg-white group-hover:border-slate-200">
                            <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Learners</div>
                            <div class="text-lg font-black text-slate-900 tabular-nums italic">{{ group.total_learners }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/50 border border-slate-100 p-4 transition-all group-hover:bg-white group-hover:border-slate-200">
                            <div class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-1.5 leading-none">Active</div>
                            <div class="text-lg font-black text-emerald-600 tabular-nums italic">{{ group.active_learners }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/50 border border-slate-100 p-4 transition-all group-hover:bg-white group-hover:border-slate-200">
                            <div class="text-[8px] font-black text-blue-500 uppercase tracking-widest mb-1.5 leading-none">New Entry</div>
                            <div class="text-lg font-black text-blue-600 tabular-nums italic">{{ group.new_enrollments }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/50 border border-slate-100 p-4 transition-all group-hover:bg-white group-hover:border-slate-200">
                            <div class="text-[8px] font-black text-amber-500 uppercase tracking-widest mb-1.5 leading-none">Promoted</div>
                            <div class="text-lg font-black text-amber-600 tabular-nums italic">{{ group.promoted_learners }}</div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-50 flex items-center justify-between group-hover:border-slate-100 transition-colors">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Access Manifest</span>
                        <ChevronDown class="h-4 w-4 text-slate-300 -rotate-90 group-hover:translate-x-1 transition-transform" />
                    </div>
                </Link>
            </div>

            <div v-else class="rounded-[2rem] border border-slate-100 bg-white overflow-hidden shadow-sm">
                <div class="overflow-x-auto scrollbar-hide">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Context Group</th>
                                <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Epoch</th>
                                <th class="px-4 py-4 text-center text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Nodes</th>
                                <th class="px-4 py-4 text-center text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Active</th>
                                <th class="px-4 py-4 text-center text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Promoted</th>
                                <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="group in groups" :key="group.class_id" class="transition-colors hover:bg-blue-50/30 group">
                                <td class="px-6 py-4">
                                    <div class="font-black text-slate-900 text-[11px] uppercase italic">{{ group.class_name }}</div>
                                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ group.grade_name || '??' }}<span v-if="group.stream_name"> • {{ group.stream_name }}</span></div>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-black text-slate-500 italic">{{ group.academic_year || '—' }}</td>
                                <td class="px-4 py-4 text-center text-[11px] font-black italic">{{ group.total_learners }}</td>
                                <td class="px-4 py-4 text-center text-[11px] font-black text-emerald-600 italic tabular-nums">{{ group.active_learners }}</td>
                                <td class="px-4 py-4 text-center text-[11px] font-black text-amber-600 italic tabular-nums">{{ group.promoted_learners }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Button variant="ghost" size="sm" as-child class="h-9 w-9 p-0 rounded-lg hover:bg-slate-900 hover:text-white transition-all">
                                        <Link :href="`/students/enrollments/groups/${group.class_id}`">
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
