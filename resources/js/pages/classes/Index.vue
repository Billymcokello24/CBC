<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, List, MoreHorizontal, Plus, School, Search, Users, Grid2x2, ShieldCheck, ShieldOff, Eye, Edit, Trash2, CheckCircle2, ChevronDown, Check, Square, Minus, Filter, Layers } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface ClassRow {
    id: number;
    name: string;
    code: string;
    grade: string | null;
    stream: string | null;
    stream_code: string | null;
    teacher: string | null;
    learners: number;
    capacity: number | null;
    academic_year: string | null;
    utilization: number;
    is_active: boolean;
}

const props = defineProps<{
    classes: {
        data: ClassRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: {
        total_classes: number;
        total_learners: number;
        average_class_size: number;
        grades_count: number;
    };
    filters: {
        search: string;
        grade_id: number | null;
        view: 'grid' | 'list';
        per_page: number;
    };
    grades: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedGradeId = ref(props.filters.grade_id ? String(props.filters.grade_id) : '');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedAcademicYearId = ref(props.filters.academic_year_id ? String(props.filters.academic_year_id) : '');
const perPage = ref(props.filters.per_page ?? 20);
const showFilters = ref(false);
const selectedClassIds = ref<number[]>([]);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/classes', {
        search: searchQuery.value || undefined,
        grade_id: selectedGradeId.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        academic_year_id: selectedAcademicYearId.value || undefined,
        view: selectedView.value,
        per_page: perPage.value,
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
watch(selectedGradeId, () => applyFilters());
watch(selectedStatus, () => applyFilters());
watch(selectedAcademicYearId, () => applyFilters());
watch(selectedView, () => applyFilters());
watch(perPage, () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedGradeId.value = '';
    selectedStatus.value = 'all';
    selectedAcademicYearId.value = '';
    applyFilters();
};

watch(
    () => props.classes.data,
    () => {
        selectedClassIds.value = selectedClassIds.value.filter((id) => props.classes.data.some((cls) => cls.id === id));
    },
    { deep: true },
);

const bulkForm = useForm<{ class_ids: number[]; action: 'activate' | 'deactivate' | 'delete' | 'promote' | '' }>({ class_ids: [], action: '' });
const allSelectableClassIds = computed(() => props.classes.data.map((cls) => cls.id));
const allSelected = computed(() => allSelectableClassIds.value.length > 0 && allSelectableClassIds.value.every((id) => selectedClassIds.value.includes(id)));

const toggleAllClasses = () => {
    selectedClassIds.value = allSelected.value ? [] : [...allSelectableClassIds.value];
};

const toggleClassSelection = (classId: number) => {
    selectedClassIds.value = selectedClassIds.value.includes(classId)
        ? selectedClassIds.value.filter((id) => id !== classId)
        : [...selectedClassIds.value, classId];
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete' | 'promote') => {
    if (!selectedClassIds.value.length) return;
    
    if (action === 'promote' && !confirm('Are you sure you want to promote all learners in the selected classes? This will move them to the next grade.')) {
        return;
    }

    bulkForm.class_ids = [...selectedClassIds.value];
    bulkForm.action = action;
    bulkForm.post('/classes/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedClassIds.value = [];
        },
    });
};
const toggleClassStatus = (cls: any) => {
    router.patch(`/classes/${cls.id}/${cls.is_active ? 'deactivate' : 'activate'}`, {}, {
        preserveScroll: true,
    });
};

const confirmDeleteClass = (cls: any) => {
    if (confirm(`Are you sure you want to delete ${cls.name}?`)) {
        router.delete(`/classes/${cls.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Classes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 mt-2 w-full animate-in fade-in duration-500">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md transition-transform hover:scale-105 duration-300">
                        <School class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Classes List</h1>
                        <p class="text-sm text-muted-foreground mt-1">Manage all class units and learner distributions.</p>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-slate-50 p-3"><School class="h-5 w-5 text-slate-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground font-medium">Total Classes</p>
                            <p class="text-xl font-bold text-slate-900">{{ stats.total_classes }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-blue-50 p-3"><Users class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground font-medium">Total Learners</p>
                            <p class="text-xl font-bold text-blue-600">{{ stats.total_learners }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-green-50 p-3"><GraduationCap class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground font-medium">Avg Class Size</p>
                            <p class="text-xl font-bold text-slate-900">{{ stats.average_class_size }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-amber-50 p-3"><BookOpen class="h-5 w-5 text-amber-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground font-medium">Grade Levels</p>
                            <p class="text-xl font-bold text-slate-900">{{ stats.grades_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
                    <div class="flex flex-1 items-center gap-2">
                        <div class="relative flex-1 max-w-md">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search by name, code or teacher..." class="pl-9 h-11 border-slate-200 text-sm rounded-xl" />
                        </div>
                        
                        <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200">
                            <Button variant="ghost" :class="['h-9 w-9 p-0 rounded-lg transition-all', selectedView === 'grid' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600']" @click="selectedView = 'grid'"><Grid2x2 class="h-4 w-4" /></Button>
                            <Button variant="ghost" :class="['h-9 w-9 p-0 rounded-lg transition-all', selectedView === 'list' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600']" @click="selectedView = 'list'"><List class="h-4 w-4" /></Button>
                        </div>

                        <Button variant="outline" class="h-11 border-slate-200 font-medium px-4 rounded-xl text-xs" @click="showFilters = !showFilters">
                            <Filter class="mr-2 h-3.5 w-3.5 text-blue-500" /> {{ showFilters ? 'Hide Advanced' : 'Show Advanced' }}
                        </Button>
                        
                        <Button variant="ghost" class="h-11 text-slate-400 hover:text-slate-600 font-bold uppercase text-[10px] tracking-widest px-4" @click="clearFilters">Reset</Button>
                    </div>

                    <div class="flex items-center gap-2 ml-auto">
                        <Button variant="outline" as-child class="h-11 border-slate-200 text-xs font-bold px-4 rounded-xl hover:bg-slate-50 transition-all uppercase tracking-widest">
                            <Link href="/classes/allocations">Allocations</Link>
                        </Button>
                        <Button variant="outline" @click="router.post('/classes/auto-create')" class="h-11 border-blue-200 text-[10px] font-black px-4 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all uppercase tracking-widest">
                            <Plus class="mr-1 h-3.5 w-3.5" />Sync
                        </Button>
                        <Button as-child class="bg-blue-600 hover:bg-blue-700 shadow-lg border-0 h-11 px-6 rounded-xl text-white font-bold text-[10px] uppercase tracking-widest transition-all">
                            <Link href="/classes/create"><Plus class="mr-1 h-3.5 w-3.5" />Add Class</Link>
                        </Button>

                        <div v-if="selectedClassIds.length > 0" class="animate-in slide-in-from-right-4 duration-300">
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button class="bg-blue-600 text-white hover:bg-blue-700 font-bold text-[10px] uppercase tracking-widest h-11 px-6 shadow-lg border-0 rounded-xl">
                                        Batch Actions ({{ selectedClassIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 font-pulsar">
                                    <DropdownMenuItem @click="runBulkAction('activate')"><CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" /> Activate Selected</DropdownMenuItem>
                                    <DropdownMenuItem @click="runBulkAction('deactivate')"><ShieldOff class="mr-2 h-4 w-4 text-amber-500" /> Deactivate Selected</DropdownMenuItem>
                                    <DropdownMenuItem @click="runBulkAction('promote')"><GraduationCap class="mr-2 h-4 w-4 text-blue-500" /> Promote Selected</DropdownMenuItem>
                                    <DropdownMenuItem class="text-rose-600 font-bold" @click="runBulkAction('delete')"><Trash2 class="mr-2 h-4 w-4" /> Delete Selected</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div v-if="showFilters" class="grid gap-4 pt-4 border-t border-slate-50 md:grid-cols-2 lg:grid-cols-3 animate-in slide-in-from-top-2 duration-300">
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Grade Level</label>
                        <select v-model="selectedGradeId" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-600">
                            <option value="">All Grade Levels</option>
                            <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Operational Status</label>
                        <select v-model="selectedStatus" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-600">
                            <option value="all">All Statuses</option>
                            <option value="active">Active Classes</option>
                            <option value="inactive">Inactive Classes</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Academic Year</label>
                        <select v-model="selectedAcademicYearId" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-600">
                            <option value="">All Academic Years</option>
                            <option v-for="year in $page.props.academicYears" :key="year.id" :value="String(year.id)">{{ year.name }}</option>
                        </select>
                    </div>
                </div>
            </div>


            <!-- Main Grid View -->
            <div v-if="selectedView === 'grid'" class="space-y-4">
                <div class="flex items-center gap-3 px-2 py-1">
                    <button @click="toggleAllClasses" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedClassIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                        <Minus v-else-if="selectedClassIds.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                    </button>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer select-none" @click="toggleAllClasses">Select All on Page</span>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="cls in classes.data" :key="cls.id" class="group relative rounded-2xl border bg-white p-7 transition-all hover:shadow-lg border-slate-100 hover:border-blue-200 flex flex-col">
                    <div class="mb-6 flex items-start justify-between relative z-10">
                        <div class="flex items-center gap-4">
                            <button @click="toggleClassSelection(cls.id)" class="mt-1 h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="selectedClassIds.includes(cls.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                <Check v-if="selectedClassIds.includes(cls.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                            </button>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-blue-700 text-white font-bold shadow-lg text-lg group-hover:rotate-3 transition-transform">
                                {{ cls.stream_code || '?' }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-tight">{{ cls.name }}</h3>
                                <p class="text-xs text-slate-400 font-bold mt-1 uppercase tracking-tight">{{ cls.grade || 'Unknown Grade' }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/classes/${cls.id}`"><Eye class="mr-2 h-4 w-4 text-blue-500" /> View Details</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/classes/${cls.id}/edit`"><Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit Class</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" as-child><Link href="/students"><Users class="mr-2 h-4 w-4 text-blue-600" /> Learner List</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/students/enrollments/groups/${cls.id}`"><Layers class="mr-2 h-4 w-4 text-emerald-500" /> Enrollment Matrix</Link></DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    
                    <div class="space-y-5 relative z-10">
                        <div class="flex items-center gap-3 p-4 bg-slate-50/50 rounded-xl border border-slate-100 group-hover:bg-blue-50/30 transition-colors">
                            <div class="h-8 w-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center shadow-sm text-slate-400">
                                <Users class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mb-0.5">Class Teacher</p>
                                <p class="text-xs font-bold text-slate-700 truncate max-w-[180px]">{{ cls.teacher || 'Unassigned' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Badge variant="secondary" class="rounded-lg px-2.5 text-[10px] font-bold bg-slate-100 text-slate-600 border-none">{{ cls.academic_year || 'No Year' }}</Badge>
                            <Badge variant="secondary" class="rounded-lg px-2.5 text-[10px] font-bold bg-blue-50 text-blue-600 border-none uppercase">{{ cls.stream || 'General' }}</Badge>
                        </div>
                        
                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-400 tracking-tight uppercase">Capacity Usage</span>
                                <span class="text-xs font-bold text-slate-900">{{ cls.learners }} / {{ cls.capacity || '∞' }} <span class="text-[10px] text-slate-400 ml-1">LEARNERS</span></span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-slate-100">
                                <div class="h-full rounded-full transition-all duration-1000 shadow-sm" :class="cls.utilization > 90 ? 'bg-rose-500' : cls.utilization > 70 ? 'bg-amber-500' : 'bg-blue-600'" :style="{ width: `${cls.utilization}%` }"></div>
                            </div>
                        </div>

                        <div class="flex gap-1 pt-3 flex-wrap">
                            <Button variant="outline" size="sm" class="flex-1 rounded-xl h-8 border-slate-200 text-xs font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all" as-child>
                                <Link :href="`/classes/${cls.id}`"><Eye class="mr-1 h-3.5 w-3.5" />View</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="flex-1 rounded-xl h-8 border-slate-200 text-xs font-bold hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all" as-child>
                                <Link :href="`/classes/${cls.id}/edit`"><Edit class="mr-1 h-3.5 w-3.5" />Edit</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="rounded-xl h-8 border-slate-200 transition-all px-2 hover:text-white" :class="cls.is_active ? 'text-amber-500 hover:bg-amber-500 hover:border-amber-500' : 'text-emerald-500 hover:bg-emerald-500 hover:border-emerald-500'" @click="toggleClassStatus(cls)" :title="cls.is_active ? 'Deactivate' : 'Activate'">
                                <component :is="cls.is_active ? ShieldOff : ShieldCheck" class="h-3.5 w-3.5" />
                            </Button>
                            <Button variant="outline" size="sm" class="rounded-xl h-8 border-slate-200 text-xs font-bold hover:bg-rose-500 hover:text-white hover:border-rose-500 text-rose-600 transition-all px-2" @click="confirmDeleteClass(cls)" title="Delete Class">
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List View Table -->
        <div v-else class="rounded-2xl border bg-white overflow-hidden shadow-sm border-slate-100">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-slate-50/50">
                                <th class="w-14 px-6 py-5 text-center">
                                    <button @click="toggleAllClasses" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedClassIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                                        <Minus v-else-if="selectedClassIds.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                                    </button>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Class Name</th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Grade Level</th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Academic Year</th>
                                <th class="px-6 py-5 text-center text-xs font-bold uppercase tracking-tight text-slate-400">Learners</th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-tight text-slate-400">Usage</th>
                                <th class="px-6 py-5 text-right text-xs font-bold uppercase tracking-tight text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="cls in classes.data" :key="cls.id" class="group transition-all hover:bg-blue-50/20">
                                <td class="px-6 py-5 text-center">
                                    <button @click="toggleClassSelection(cls.id)" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedClassIds.includes(cls.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                        <Check v-if="selectedClassIds.includes(cls.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                                    </button>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-900 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">{{ cls.stream_code || '?' }}</div>
                                        <div>
                                            <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ cls.name }}</div>
                                            <div class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ cls.teacher || 'Unassigned' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-xs font-bold text-slate-600 uppercase tracking-tight">{{ cls.grade || '—' }}</td>
                                <td class="px-6 py-5 text-xs font-bold text-slate-600 uppercase tracking-tight">{{ cls.academic_year || '—' }}</td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ cls.learners }} / {{ cls.capacity || '∞' }}</span>
                                </td>
                                <td class="px-6 py-5 min-w-[140px]">
                                    <div class="flex flex-col gap-1.5 pt-1">
                                        <div class="flex items-center justify-between">
                                             <span class="text-[10px] font-bold text-slate-400">{{ cls.utilization }}%</span>
                                        </div>
                                        <div class="h-1 w-full rounded-full bg-slate-100">
                                            <div class="h-full rounded-full transition-all duration-1000" :class="cls.utilization > 90 ? 'bg-rose-500' : 'bg-blue-600'" :style="{ width: `${cls.utilization}%` }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end items-center gap-1">
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" as-child title="View Details">
                                            <Link :href="`/classes/${cls.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition-all" as-child title="Edit Class">
                                            <Link :href="`/classes/${cls.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="cls.is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50'" @click="toggleClassStatus(cls)" :title="cls.is_active ? 'Deactivate' : 'Activate'">
                                            <component :is="cls.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-rose-400 hover:bg-rose-50 transition-all" @click="confirmDeleteClass(cls)" title="Delete Class">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Footer -->
            <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center justify-between bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-tight">Display Configuration</span>
                    <select v-model="perPage" class="h-9 rounded-xl border-slate-200 bg-slate-50 px-4 text-xs font-bold focus:ring-blue-600 transition-all min-w-[110px] outline-none">
                        <option :value="10">10 per page</option>
                        <option :value="20">20 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                        <option :value="500">500 per page</option>
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <template v-for="(link, i) in classes.links" :key="i">
                        <Button 
                            v-if="link.url || i === 0 || i === classes.links.length - 1"
                            variant="outline" 
                            size="sm" 
                            :disabled="!link.url"
                            :class="[
                                'h-9 px-4 rounded-xl border-slate-100 text-xs font-bold transition-all',
                                link.active ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'hover:bg-blue-50 text-slate-600'
                            ]"
                            @click="link.url && router.get(link.url)"
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
