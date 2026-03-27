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
        status: string | null;
        academic_year_id: number | null;
        view: 'grid' | 'list';
        per_page: number;
    };
    grades: Array<{ id: number; name: string }>;
    academicYears: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
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
    <Head title="Matrix Units" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-foreground">
        <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-20 p-6">
            <!-- Simple Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic underline decoration-blue-600 decoration-4 underline-offset-8">Matrix Units</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest pt-2">
                        Manage class structures, enrollment density, and academic deployments.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="rounded-xl h-11 px-6 border-border font-black uppercase text-[10px] tracking-widest hover:bg-muted" @click="router.post('/classes/auto-create')">
                        <Plus class="mr-2 h-4 w-4 opacity-40" />
                        Sync Nodes
                    </Button>
                    <Link
                        href="/classes/create"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Deploy Unit
                    </Link>
                </div>
            </div>

            <!-- TailPanel Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-blue-600/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-blue-600/5 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                            <School class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest">Topology</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">Total Units</p>
                    <h3 class="text-2xl font-black text-foreground">{{ stats.total_classes.toLocaleString() }}</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-emerald-500/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/5 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500">
                            <Users class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest">Population</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">Active Learners</p>
                    <h3 class="text-2xl font-black text-foreground">{{ stats.total_learners.toLocaleString() }}</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-amber-500/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-amber-500/5 flex items-center justify-center text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500">
                            <Layers class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest">Density</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">Avg Unit Size</p>
                    <h3 class="text-2xl font-black text-foreground">{{ stats.average_class_size }}</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-violet-500/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-violet-500/5 flex items-center justify-center text-violet-500 group-hover:bg-violet-500 group-hover:text-white transition-all duration-500">
                            <GraduationCap class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest">Hierarchy</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">Grade Strata</p>
                    <h3 class="text-2xl font-black text-foreground">{{ stats.grades_count }} Nodes</h3>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col gap-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex flex-1 items-center gap-4 w-full md:max-w-2xl">
                        <div class="relative flex-1">
                            <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/60" />
                            <Input v-model="searchQuery" placeholder="Search by name, code or teacher..." class="pl-12 h-12 bg-muted/20 border-border rounded-xl text-sm font-bold focus:bg-background transition-all placeholder:text-[10px] placeholder:uppercase placeholder:tracking-widest" />
                        </div>
                        
                        <div class="flex items-center p-1 bg-muted rounded-xl border border-border h-12">
                            <Button variant="ghost" :class="['h-10 w-10 p-0 rounded-lg transition-all', selectedView === 'grid' ? 'bg-background shadow-sm text-blue-600' : 'text-muted-foreground hover:text-foreground']" @click="selectedView = 'grid'"><Grid2x2 class="h-4 w-4" /></Button>
                            <Button variant="ghost" :class="['h-10 w-10 p-0 rounded-lg transition-all', selectedView === 'list' ? 'bg-background shadow-sm text-blue-600' : 'text-muted-foreground hover:text-foreground']" @click="selectedView = 'list'"><List class="h-4 w-4" /></Button>
                        </div>

                        <Button variant="outline" class="h-12 border-border font-black px-6 rounded-xl text-[10px] uppercase tracking-widest whitespace-nowrap" @click="showFilters = !showFilters">
                            <Filter class="mr-2 h-4 w-4 opacity-40" /> {{ showFilters ? 'Collapse' : 'Refine' }}
                        </Button>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button variant="outline" as-child class="h-12 border-border text-[10px] font-black px-6 rounded-xl hover:bg-muted transition-all uppercase tracking-widest">
                            <Link href="/classes/allocations">Allocations</Link>
                        </Button>
                        
                        <div v-if="selectedClassIds.length > 0" class="animate-in slide-in-from-right-4 duration-300">
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button class="bg-foreground text-background hover:bg-foreground/90 font-black text-[10px] uppercase tracking-widest h-12 px-6 rounded-xl shadow-xl">
                                        Batch Actions ({{ selectedClassIds.length }}) <ChevronDown class="ml-2 h-4 w-4 opacity-40" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                                    <DropdownMenuItem @click="runBulkAction('activate')"><CheckCircle2 class="mr-3 h-4 w-4 text-emerald-500 opacity-60" /> Activate Signal</DropdownMenuItem>
                                    <DropdownMenuItem @click="runBulkAction('deactivate')"><ShieldOff class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Deactivate Node</DropdownMenuItem>
                                    <DropdownMenuItem @click="runBulkAction('promote')"><GraduationCap class="mr-3 h-4 w-4 text-blue-500 opacity-60" /> Promote Population</DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 bg-border/50" />
                                    <DropdownMenuItem class="text-rose-500 rounded-lg py-2.5 font-bold text-xs group" @click="runBulkAction('delete')"><Trash2 class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100" /> Purge Matrix</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div v-if="showFilters" class="grid gap-6 pt-6 border-t border-border/50 md:grid-cols-2 lg:grid-cols-4 animate-in slide-in-from-top-4 duration-300">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-muted-foreground/40 tracking-[0.2em] pl-1">Grade Level</label>
                        <select v-model="selectedGradeId" class="h-11 w-full rounded-xl border-border bg-muted/20 px-4 text-[11px] font-black uppercase tracking-widest hover:bg-muted/30 transition-all outline-none focus:ring-1 focus:ring-blue-600 appearance-none cursor-pointer">
                            <option value="">All Grade Levels</option>
                            <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-muted-foreground/40 tracking-[0.2em] pl-1">Condition</label>
                        <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-border bg-muted/20 px-4 text-[11px] font-black uppercase tracking-widest hover:bg-muted/30 transition-all outline-none focus:ring-1 focus:ring-blue-600 appearance-none cursor-pointer">
                            <option value="all">All Statuses</option>
                            <option value="active">Active Units</option>
                            <option value="inactive">Inactive Units</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-muted-foreground/40 tracking-[0.2em] pl-1">Academic Year</label>
                        <select v-model="selectedAcademicYearId" class="h-11 w-full rounded-xl border-border bg-muted/20 px-4 text-[11px] font-black uppercase tracking-widest hover:bg-muted/30 transition-all outline-none focus:ring-1 focus:ring-blue-600 appearance-none cursor-pointer">
                            <option value="">All Periods</option>
                            <option v-for="year in ($page.props.academicYears as any)" :key="year.id" :value="String(year.id)">{{ year.name }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                         <Button variant="ghost" class="h-11 w-full text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-foreground" @click="clearFilters">Reset Parameters</Button>
                    </div>
                </div>
            </div>


            <!-- Main Grid View -->
            <div v-if="selectedView === 'grid'" class="space-y-8">
                <div class="flex items-center gap-3 px-2">
                    <button @click="toggleAllClasses" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-card shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedClassIds.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-border'">
                        <Check v-if="allSelected" class="h-4 w-4 stroke-[3px]" />
                        <Minus v-else-if="selectedClassIds.length > 0" class="h-4 w-4 stroke-[3px]" />
                    </button>
                    <span class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest cursor-pointer select-none hover:text-foreground transition-colors" @click="toggleAllClasses">Matrix Segment Selection</span>
                </div>
                
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="cls in classes.data" :key="cls.id" class="group relative rounded-[2rem] border border-border bg-card p-8 transition-all duration-500 hover:shadow-2xl hover:border-blue-600/20 flex flex-col overflow-hidden">
                        <!-- Holographic Background Effect -->
                        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-600/5 group-hover:bg-blue-600/10 transition-all duration-700 blur-3xl"></div>
                        
                        <div class="mb-8 flex items-start justify-between relative z-10">
                            <div class="flex items-center gap-5">
                                <button @click="toggleClassSelection(cls.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-card shadow-sm group-hover:border-blue-600/40" :class="selectedClassIds.includes(cls.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-border text-transparent'">
                                    <Check class="h-4 w-4 stroke-[3px]" />
                                </button>
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-foreground text-background font-black shadow-xl text-xl group-hover:scale-110 transition-transform duration-500 italic">
                                    {{ cls.stream_code || '?' }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xl font-black text-foreground group-hover:text-blue-600 transition-colors leading-tight uppercase italic tracking-tight truncate max-w-[150px]">{{ cls.name }}</h3>
                                    <p class="text-[10px] font-black text-muted-foreground/40 mt-1 uppercase tracking-widest">{{ cls.grade || 'Core Segment' }}</p>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl text-muted-foreground/40 hover:text-foreground hover:bg-muted transition-all">
                                        <MoreHorizontal class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/classes/${cls.id}`"><Eye class="mr-3 h-4 w-4 text-blue-500 opacity-60" /> Unit Telemetry</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/classes/${cls.id}/edit`"><Edit class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Adjust Parameters</Link></DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 bg-border/50" />
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link href="/students"><Users class="mr-3 h-4 w-4 text-blue-600 opacity-60" /> Population List</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/students/enrollments/groups/${cls.id}`"><Layers class="mr-3 h-4 w-4 text-emerald-500 opacity-60" /> Allocation Matrix</Link></DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        
                        <div class="space-y-6 relative z-10 flex-1">
                            <div class="flex items-center gap-4 p-5 bg-muted/30 rounded-2xl border border-border group-hover:bg-blue-600/5 group-hover:border-blue-600/20 transition-all duration-500">
                                <div class="h-10 w-10 rounded-xl bg-background border border-border flex items-center justify-center shadow-sm text-foreground/40 group-hover:text-blue-600 transition-colors">
                                    <Users class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest mb-1">Unit Overseer</p>
                                    <p class="text-xs font-black text-foreground truncate uppercase italic">{{ cls.teacher || 'Unassigned Node' }}</p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <span class="rounded-lg px-3 py-1 text-[9px] font-black bg-muted text-muted-foreground uppercase tracking-wider border border-border">{{ cls.academic_year || '2026' }}</span>
                                <span class="rounded-lg px-3 py-1 text-[9px] font-black bg-blue-600/10 text-blue-600 uppercase tracking-wider border border-blue-600/20 italic">{{ cls.stream || 'Segment' }}</span>
                            </div>
                            
                            <div class="pt-2">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-black text-muted-foreground/40 tracking-widest uppercase italic">Density Threshold</span>
                                    <span class="text-[10px] font-black text-foreground uppercase tracking-tight">
                                        <span :class="cls.utilization > 90 ? 'text-rose-500' : 'text-foreground'">{{ cls.learners }}</span> 
                                        <span class="text-muted-foreground/30 mx-1">/</span> 
                                        {{ cls.capacity || '∞' }}
                                    </span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-muted overflow-hidden border border-border/50">
                                    <div class="h-full rounded-full transition-all duration-1000 shadow-lg" :class="cls.utilization > 90 ? 'bg-rose-500' : cls.utilization > 70 ? 'bg-amber-500' : 'bg-blue-600'" :style="{ width: `${cls.utilization}%` }"></div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <Button variant="outline" size="sm" class="flex-1 rounded-xl h-10 border-border text-[9px] font-black uppercase tracking-widest hover:bg-foreground hover:text-background transition-all italic" as-child>
                                    <Link :href="`/classes/${cls.id}`">Telemetry</Link>
                                </Button>
                                <Button variant="outline" size="sm" class="flex-1 rounded-xl h-10 border-border text-[9px] font-black uppercase tracking-widest hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all italic" as-child>
                                    <Link :href="`/classes/${cls.id}/edit`">Adjust</Link>
                                </Button>
                                <Button variant="outline" size="sm" class="rounded-xl h-10 w-10 p-0 border-border transition-all hover:text-white" :class="cls.is_active ? 'text-amber-500 hover:bg-amber-500 hover:border-amber-500' : 'text-emerald-500 hover:bg-emerald-500 hover:border-emerald-500'" @click="toggleClassStatus(cls)">
                                    <component :is="cls.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View Table -->
            <div v-else class="rounded-[2.5rem] border border-border bg-card shadow-2xl overflow-hidden dark:border-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-foreground border-b border-foreground/10 text-background">
                                <th class="w-16 px-8 py-6 text-center">
                                    <button @click="toggleAllClasses" class="h-6 w-6 rounded-lg border-2 border-background/20 flex items-center justify-center transition-all hover:border-background/60 mx-auto" :class="allSelected ? 'bg-background border-background text-foreground' : selectedClassIds.length > 0 ? 'bg-background/20 text-background border-background/40' : ''">
                                        <Check v-if="allSelected" class="h-4 w-4 stroke-[3px]" />
                                        <Minus v-else-if="selectedClassIds.length > 0" class="h-4 w-4 stroke-[3px]" />
                                    </button>
                                </th>
                                <th class="px-6 py-6 text-[10px] font-black uppercase tracking-widest opacity-80">Unit Signal</th>
                                <th class="px-6 py-6 text-[10px] font-black uppercase tracking-widest opacity-80">Strata</th>
                                <th class="px-6 py-6 text-[10px] font-black uppercase tracking-widest opacity-80">Timeline</th>
                                <th class="px-6 py-6 text-center text-[10px] font-black uppercase tracking-widest opacity-80">Population</th>
                                <th class="px-6 py-6 text-[10px] font-black uppercase tracking-widest opacity-80">Density Analysis</th>
                                <th class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-widest opacity-80">Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-for="cls in classes.data" :key="cls.id" class="group transition-all duration-300 hover:bg-muted/30">
                                <td class="px-8 py-5 text-center">
                                    <button @click="toggleClassSelection(cls.id)" class="h-6 w-6 rounded-lg border-2 border-border flex items-center justify-center transition-all bg-card shadow-sm group-hover:border-blue-600/40 mx-auto" :class="selectedClassIds.includes(cls.id) ? 'bg-blue-600 border-blue-600 text-white' : 'text-transparent'">
                                        <Check class="h-4 w-4 stroke-[3px]" />
                                    </button>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-5">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-foreground font-black group-hover:bg-foreground group-hover:text-background transition-all duration-500 shadow-inner italic">{{ cls.stream_code || '?' }}</div>
                                        <div class="min-w-0">
                                            <div class="font-black text-foreground uppercase italic tracking-tight group-hover:text-blue-600 transition-colors">{{ cls.name }}</div>
                                            <div class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest mt-1">{{ cls.teacher || 'UNASSIGNED NODE' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-[10px] font-black text-foreground uppercase tracking-widest bg-muted/40 px-3 py-1 rounded-lg border border-border">{{ cls.grade || '—' }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest italic">{{ cls.academic_year || '—' }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span :class="cls.utilization > 90 ? 'bg-rose-500 text-white' : 'bg-muted text-foreground'" class="inline-flex items-center rounded-lg px-3 py-1 text-[10px] font-black uppercase tracking-widest shadow-sm">{{ cls.learners }} / {{ cls.capacity || '∞' }}</span>
                                </td>
                                <td class="px-6 py-5 min-w-[180px]">
                                    <div class="flex flex-col gap-2 pt-1">
                                        <div class="flex items-center justify-between">
                                             <span class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest italic">Usage: {{ cls.utilization }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-muted border border-border/50 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-1000 shadow-md" :class="cls.utilization > 90 ? 'bg-rose-500' : cls.utilization > 70 ? 'bg-amber-500' : 'bg-blue-600'" :style="{ width: `${cls.utilization}%` }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <Link :href="`/classes/${cls.id}`" class="h-10 w-10 flex items-center justify-center rounded-xl bg-muted/20 text-muted-foreground hover:bg-blue-600/10 hover:text-blue-600 border border-transparent hover:border-blue-600/20 transition-all opacity-0 group-hover:opacity-100"><Eye class="h-4.5 w-4.5" /></Link>
                                        <Link :href="`/classes/${cls.id}/edit`" class="h-10 w-10 flex items-center justify-center rounded-xl bg-muted/20 text-muted-foreground hover:bg-amber-500/10 hover:text-amber-500 border border-transparent hover:border-amber-500/20 transition-all opacity-0 group-hover:opacity-100"><Edit class="h-4.5 w-4.5" /></Link>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground">
                                                    <MoreHorizontal class="h-4.5 w-4.5" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                                                <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/classes/${cls.id}`"><Eye class="mr-3 h-4 w-4 text-blue-500 opacity-60" /> Unit Telemetry</Link></DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/classes/${cls.id}/edit`"><Edit class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Adjust Parameters</Link></DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 bg-border/50" />
                                                <DropdownMenuItem class="text-rose-500 rounded-lg py-2.5 font-bold text-xs group" @click="confirmDeleteClass(cls)"><Trash2 class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100" /> Purge Matrix Node</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Premium Pagination Footer -->
            <div class="flex flex-col md:flex-row items-center justify-between px-10 py-8 bg-card rounded-[3rem] border border-border shadow-2xl gap-8 group/footer relative overflow-hidden">
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-600/5 blur-3xl group-hover/footer:bg-blue-600/10 transition-all duration-1000"></div>
                <div class="flex items-center gap-6 relative z-10">
                    <div class="flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-muted/50 border border-border shadow-inner group-hover/footer:border-blue-600/20 transition-colors">
                        <label class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em]">Density</label>
                        <select v-model="perPage" class="bg-transparent text-[11px] font-black text-foreground outline-none border-none py-0 focus:ring-0 cursor-pointer uppercase tracking-widest">
                            <option :value="10">10 PER SEGMENT</option>
                            <option :value="20">20 PER SEGMENT</option>
                            <option :value="50">50 PER SEGMENT</option>
                            <option :value="100">100 PER SEGMENT</option>
                        </select>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-[0.15em] italic">Telemetry segment {{ classes.current_page }} / {{ classes.last_page }}</p>
                </div>
                
                <div class="flex items-center gap-2 relative z-10">
                    <template v-for="(link, i) in classes.links" :key="i">
                        <Button 
                            v-if="link.url || i === 0 || i === classes.links.length - 1"
                            variant="outline" 
                            size="sm" 
                            :disabled="!link.url || link.active"
                            :class="[
                                'h-12 px-6 rounded-2xl border-border font-black text-[10px] uppercase tracking-widest transition-all shadow-sm',
                                link.active ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-600/20' : 'hover:bg-muted text-foreground'
                            ]"
                            @click="link.url && !link.active && router.get(link.url)"
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Global Matrix Pulse -->
            <div class="p-10 rounded-[3rem] bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-10 group relative overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.3)] dark:bg-black">
                <div class="absolute -right-24 -bottom-24 opacity-5 group-hover:scale-125 transition-all duration-[2000ms] text-white font-black italic uppercase text-[12rem] select-none pointer-events-none">
                     MATRX
                </div>
                <div class="flex items-center gap-8 relative z-10">
                    <div class="h-20 w-20 rounded-3xl bg-white/5 flex items-center justify-center border border-white/10 group-hover:bg-blue-600 group-hover:border-blue-600 transition-all duration-700 shadow-2xl">
                         <School class="h-10 w-10 text-white/30 group-hover:text-white group-hover:scale-110 transition-all duration-500" />
                    </div>
                    <div>
                        <h3 class="font-black text-2xl tracking-tighter uppercase italic group-hover:text-blue-400 transition-colors duration-500">Unit Deployment Active</h3>
                        <p class="text-white/40 text-xs mt-2 tracking-[0.1em] font-black uppercase leading-relaxed italic opacity-80">Synchronizing Class Nodes for the {{ $page.props.auth.school?.name || 'Campus' }} Academic Grid.</p>
                    </div>
                </div>
                <div class="flex gap-8 relative z-10">
                    <div class="flex flex-col items-end">
                         <span class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mb-2 opacity-60">Matrix Status</span>
                         <span class="bg-blue-600/20 text-blue-400 border border-blue-400/20 px-8 py-3 rounded-full text-[11px] font-black uppercase tracking-[0.25em] shadow-[0_0_30px_rgba(59,130,246,0.3)] animate-pulse">Pulse Locked</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
