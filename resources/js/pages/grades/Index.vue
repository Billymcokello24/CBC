<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    GraduationCap,
    Grid2x2,
    List,
    Search,
    ShieldCheck,
    ShieldOff,
    Trash2,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    Check,
    Square,
    Minus,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface GradeRow {
    id: number;
    name: string;
    code: string;
    category: string;
    level_order: number;
    minimum_age: number | null;
    maximum_age: number | null;
    is_active: boolean;
    classes_count: number;
    learners_count: number;
    lead_name: string | null;
}

const props = defineProps<{
    grades: GradeRow[];
    stats: {
        total_grades: number;
        active_grades: number;
        total_classes: number;
        total_learners: number;
    };
    filters: {
        search: string;
        status: string;
        view: 'grid' | 'list';
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const page = usePage<{ flash?: { success?: string } }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>('list');
const selectedGradeIds = ref<number[]>([]);
const perPage = ref(20);
const currentPage = ref(1);
const bulkForm = useForm<{ grade_ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ grade_ids: [], action: '' });
const actionForm = useForm({});
const showFilters = ref(true);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/grades', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        view: selectedView.value,
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
watch([selectedStatus, selectedView], () => applyFilters());
watch(
    () => props.grades,
    () => {
        selectedGradeIds.value = selectedGradeIds.value.filter((id) => props.grades.some((grade) => grade.id === id));
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const filteredGrades = computed(() => {
    return props.grades.filter(grade => {
        const matchesSearch = !searchQuery.value || 
            grade.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            grade.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === 'all' || 
            (selectedStatus.value === 'active' ? grade.is_active : !grade.is_active);
        return matchesSearch && matchesStatus;
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredGrades.value.length / perPage.value)));
const paginatedGrades = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredGrades.value.slice(start, start + perPage.value);
});

const allSelectableGradeIds = computed(() => paginatedGrades.value.map((grade) => grade.id));
const allSelected = computed(() => allSelectableGradeIds.value.length > 0 && allSelectableGradeIds.value.every((id) => selectedGradeIds.value.includes(id)));

const toggleAllGrades = () => {
    selectedGradeIds.value = allSelected.value ? [] : [...allSelectableGradeIds.value];
};

const toggleGrade = (gradeId: number) => {
    selectedGradeIds.value = selectedGradeIds.value.includes(gradeId)
        ? selectedGradeIds.value.filter((id) => id !== gradeId)
        : [...selectedGradeIds.value, gradeId];
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedGradeIds.value.length) return;
    bulkForm.grade_ids = [...selectedGradeIds.value];
    bulkForm.action = action;
    bulkForm.post('/grades/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedGradeIds.value = [];
        },
    });
};

const toggleGradeStatus = (grade: GradeRow) => {
    actionForm.patch(`/grades/${grade.id}/${grade.is_active ? 'deactivate' : 'activate'}`, { preserveScroll: true });
};

const deleteGrade = (grade: GradeRow) => {
    actionForm.delete(`/grades/${grade.id}`, { preserveScroll: true });
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
</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                        <GraduationCap class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Grades</h1>
                        <p class="text-muted-foreground">Manage grade levels, linked classes, and progression structure</p>
                    </div>
                </div>
                </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Grades</div><div class="mt-1 text-3xl font-bold">{{ stats.total_grades }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active Grades</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active_grades }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Classes</div><div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.total_classes }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Learners</div><div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.total_learners }}</div></div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
                    <div class="flex flex-1 items-center gap-2 max-w-2xl">
                    <div class="flex flex-1 items-center gap-2">
                        <div class="relative flex-1 max-w-md">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search grades by name or code..." class="pl-9 h-11 border-slate-200 text-sm rounded-xl" />
                        </div>
                        
                        <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200">
                            <Button variant="ghost" :class="['h-9 w-9 p-0 rounded-lg transition-all', selectedView === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600']" @click="selectedView = 'grid'"><Grid2x2 class="h-4 w-4" /></Button>
                            <Button variant="ghost" :class="['h-9 w-9 p-0 rounded-lg transition-all', selectedView === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600']" @click="selectedView = 'list'"><List class="h-4 w-4" /></Button>
                        </div>

                        <Button variant="outline" class="h-11 border-slate-200 font-medium px-4 rounded-xl text-xs" @click="showFilters = !showFilters">
                            <Filter class="mr-2 h-3.5 w-3.5 text-blue-500" /> {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                        </Button>
                        
                        <Button variant="ghost" class="h-11 text-slate-400 hover:text-slate-600 font-bold uppercase text-[10px] tracking-widest px-4" @click="clearFilters">Reset</Button>
                    </div>
                    </div>

                    <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3 ml-auto">
                        <Button as-child class="bg-blue-600 hover:bg-blue-700 shadow-lg border-0 h-11 px-6 rounded-xl">
                            <Link href="/grades/create"><Plus class="mr-2 h-4 w-4" />Add Grade</Link>
                        </Button>

                        <div v-if="selectedGradeIds.length > 0" class="animate-in slide-in-from-right-4 duration-300">
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button class="bg-blue-600 text-white hover:bg-blue-700 font-bold text-[10px] uppercase tracking-widest h-11 px-6 shadow-lg border-0 rounded-xl">
                                        Batch Actions ({{ selectedGradeIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 font-pulsar">
                                    <DropdownMenuItem @click="runBulkAction('activate')"><CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" /> Activate Selected</DropdownMenuItem>
                                    <DropdownMenuItem @click="runBulkAction('deactivate')"><ShieldOff class="mr-2 h-4 w-4 text-amber-500" /> Deactivate Selected</DropdownMenuItem>
                                    <DropdownMenuItem class="text-rose-600 font-bold" @click="runBulkAction('delete')"><Trash2 class="mr-2 h-4 w-4" /> Delete Selected</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-400 uppercase tracking-wider">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>


            <div v-if="selectedView === 'grid'" class="space-y-4">
                <div class="flex items-center gap-3 px-2 py-1">
                    <button @click="toggleAllGrades" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedGradeIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                        <Minus v-else-if="selectedGradeIds.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                    </button>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer select-none" @click="toggleAllGrades">Select All on Page</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="grade in paginatedGrades" :key="grade.id" class="group relative rounded-2xl border bg-white p-6 transition-all hover:shadow-lg border-slate-100 flex flex-col">
                    <div class="mb-6 flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <button @click="toggleGrade(grade.id)" class="mt-1 h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="selectedGradeIds.includes(grade.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                <Check v-if="selectedGradeIds.includes(grade.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                            </button>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-tight uppercase truncate max-w-[180px]">{{ grade.name }}</h2>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ grade.code }} • {{ grade.category }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ grade.lead_name || 'Not assigned' }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/grades/${grade.id}`"><Eye class="mr-2 h-4 w-4 text-blue-500" /> View Details</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" as-child><Link :href="`/grades/${grade.id}/edit`"><Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit Grade</Link></DropdownMenuItem>
                                <DropdownMenuItem class="rounded-lg" @click="toggleGradeStatus(grade)">
                                    <component :is="grade.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" :class="grade.is_active ? 'text-amber-500' : 'text-emerald-500'" />
                                    {{ grade.is_active ? 'Deactivate' : 'Activate' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-rose-600 font-bold rounded-lg" @click="deleteGrade(grade)"><Trash2 class="mr-2 h-4 w-4" /> Delete Grade</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    
                    <div class="mt-auto space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Classes</p>
                                <p class="text-base font-bold text-slate-900">{{ grade.classes_count }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Learners</p>
                                <p class="text-base font-bold text-blue-600">{{ grade.learners_count }}</p>
                            </div>
                        </div>

                        <div class="flex gap-1.5 flex-wrap">
                            <Button variant="outline" size="sm" class="flex-1 h-8 rounded-xl border-slate-200 text-[10px] font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all" as-child>
                                <Link :href="`/grades/${grade.id}`"><Eye class="mr-1 h-3 w-3" />View</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="flex-1 h-8 rounded-xl border-slate-200 text-[10px] font-bold hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all" as-child>
                                <Link :href="`/grades/${grade.id}/edit`"><Edit class="mr-1 h-3 w-3" />Edit</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="h-8 rounded-xl border-slate-200 text-[10px] font-bold transition-all px-2"
                                :class="grade.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50'"
                                @click="toggleGradeStatus(grade)">
                                <component :is="grade.is_active ? ShieldOff : ShieldCheck" class="h-3 w-3" />
                            </Button>
                            <Button variant="outline" size="sm" class="h-8 rounded-xl border-slate-200 text-rose-600 hover:bg-rose-50 transition-all px-2" @click="deleteGrade(grade)">
                                <Trash2 class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl border bg-white overflow-hidden shadow-sm border-slate-100 animate-in fade-in duration-500">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-slate-50/50">
                                <th class="w-16 px-6 py-5 text-center">
                                    <button @click="toggleAllGrades" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedGradeIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                                        <Minus v-else-if="selectedGradeIds.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                                    </button>
                                </th>
                                <th class="px-6 py-5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Grade Level</th>
                                <th class="px-6 py-5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Grade Lead</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">Classes</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">Learners</th>
                                <th class="px-6 py-5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-6 py-5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="paginatedGrades.length === 0">
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="rounded-full bg-slate-50 p-6 shadow-inner"><GraduationCap class="h-10 w-10 text-slate-200" /></div>
                                        <p class="text-sm font-medium text-slate-400">No grades found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="grade in paginatedGrades" :key="grade.id" class="group transition-all hover:bg-slate-50/50">
                                <td class="px-6 py-5 text-center">
                                    <button @click="toggleGrade(grade.id)" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedGradeIds.includes(grade.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                        <Check v-if="selectedGradeIds.includes(grade.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                                    </button>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-900 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all">{{ grade.name.charAt(0) }}</div>
                                        <div>
                                            <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors mb-0.5 uppercase">{{ grade.name }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ grade.code }} • {{ grade.category }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-slate-600 uppercase">{{ grade.lead_name || 'Not assigned' }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <Badge variant="outline" class="rounded-lg h-6 border-slate-200 text-slate-900 font-bold text-[10px] px-3 uppercase bg-white">{{ grade.classes_count }} Classes</Badge>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-xs font-bold text-blue-600">{{ grade.learners_count }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 rounded-full" :class="grade.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider" :class="grade.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ grade.is_active ? 'Active' : 'Inactive' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end items-center gap-1">
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" as-child title="View Details">
                                            <Link :href="`/grades/${grade.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" as-child title="Edit Grade">
                                            <Link :href="`/grades/${grade.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="grade.is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50'" @click="toggleGradeStatus(grade)" :title="grade.is_active ? 'Deactivate' : 'Activate'">
                                            <component :is="grade.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-rose-400 hover:bg-rose-50 transition-all" @click="deleteGrade(grade)" title="Delete Grade">
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
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-semibold">Show</span>
                    <select v-model="perPage" @change="currentPage = 1" class="h-9 rounded-xl border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option v-for="n in [10, 20, 50, 100, 500]" :key="n" :value="n">{{ n }}</option>
                    </select>
                    <span class="text-xs text-slate-400 font-semibold">/ page · {{ filteredGrades.length }} total</span>
                </div>
                <div v-if="totalPages > 1" class="flex items-center gap-1">
                    <Button variant="outline" size="sm" class="h-8 w-8 p-0 rounded-xl border-slate-200 text-slate-500 hover:bg-blue-50 disabled:opacity-40" :disabled="currentPage === 1" @click="currentPage = Math.max(1, currentPage - 1)">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <template v-for="p in totalPages" :key="p">
                        <Button v-if="Math.abs(p - currentPage) <= 2 || p === 1 || p === totalPages" variant="outline" size="sm"
                            class="h-8 w-8 p-0 rounded-xl border-slate-200 text-xs font-bold transition-all"
                            :class="currentPage === p ? 'bg-blue-600 text-white border-blue-600' : 'text-slate-600 hover:bg-blue-50'"
                            @click="currentPage = p; selectedGradeIds = []"
                        >{{ p }}</Button>
                    </template>
                    <Button variant="outline" size="sm" class="h-8 w-8 p-0 rounded-xl border-slate-200 text-slate-500 hover:bg-blue-50 disabled:opacity-40" :disabled="currentPage === totalPages" @click="currentPage = Math.min(totalPages, currentPage + 1)">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
