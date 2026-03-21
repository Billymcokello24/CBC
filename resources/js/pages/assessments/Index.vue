<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    ClipboardList, Plus, Search, Filter, Calendar, BookOpen, Users, 
    TrendingUp, MoreHorizontal, Eye, Edit, FileText, LayoutGrid, List,
    ChevronDown, ChevronUp, BookMarked, GraduationCap, Clock, CheckCircle2, 
    AlertCircle, Trash2, Download, History, Zap, Target, ArrowRight
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Card, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
];

const props = defineProps<{
    assessments: {
        data: Array<any>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: {
        total: number;
        thisWeek: number;
        pendingGrading: number;
        avgScore: number;
    };
    filters: {
        search?: string;
        status?: string;
        class_id?: string;
        subject_id?: string;
        per_page?: number;
        view?: 'grid' | 'list';
    };
    classes: Array<{ id: number; name: string }>;
    subjects: Array<{ id: number; name: string }>;
}>();

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedClassId = ref(props.filters.class_id ?? 'all');
const selectedSubjectId = ref(props.filters.subject_id ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const showFilters = ref(false);
const perPage = ref(props.assessments.per_page ?? 20);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (pageNumber = 1) => {
    router.get(
        '/assessments',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            class_id: selectedClassId.value !== 'all' ? selectedClassId.value : undefined,
            subject_id: selectedSubjectId.value !== 'all' ? selectedSubjectId.value : undefined,
            per_page: perPage.value,
            view: selectedView.value,
            page: pageNumber,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['assessments', 'stats', 'filters'],
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch([selectedStatus, selectedClassId, selectedSubjectId, perPage, selectedView], () => applyFilters());

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700 border-slate-200',
        published: 'bg-blue-600 text-white shadow-[0_0_8px_rgba(37,99,235,0.4)]',
        scheduled: 'bg-indigo-600 text-white',
        in_progress: 'bg-amber-500 text-white',
        grading: 'bg-purple-600 text-white font-black',
        completed: 'bg-emerald-600 text-white shadow-[0_0_8px_rgba(16,185,129,0.4)]',
    };
    return colors[status] || colors.draft;
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle2;
        case 'draft': return FileText;
        case 'in_progress': return Clock;
        case 'grading': return History;
        default: return AlertCircle;
    }
};

const pageLabel = computed(() => {
    const from = props.assessments.from ?? 0;
    const to = props.assessments.to ?? 0;
    return `Showing ${from} to ${to} of ${props.assessments.total} assessments`;
});

</script>

<template>
    <Head title="Assessments Hub" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1600px] mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 shadow-inner border border-indigo-100">
                        <ClipboardList class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Academic Assessments</h1>
                        <p class="text-muted-foreground font-medium">Coordinate testing cycles, grading rubrics and performance analytics</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                     <Button variant="outline" class="font-pulsar h-10 border-slate-200" as-child>
                        <Link href="/assessments/grading"><History class="mr-2 h-4 w-4" />Grading History</Link>
                     </Button>
                     <Button as-child class="bg-indigo-600 hover:bg-indigo-700 font-pulsar shadow-lg h-10 border-0">
                        <Link href="/assessments/create">
                            <Plus class="mr-2 h-4 w-4" />New Assessment
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-600 group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                         <div class="rounded-xl bg-indigo-500/10 p-2 text-indigo-600"><ClipboardList class="h-4 w-4" /></div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Global Catalog</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.total }} Tests</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-amber-500 group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                         <div class="rounded-xl bg-amber-500/10 p-2 text-amber-600"><Calendar class="h-4 w-4" /></div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Due This Week</p>
                        <p class="text-xl font-black text-amber-600">{{ stats.thisWeek }} Scheduled</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-purple-600 group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                         <div class="rounded-xl bg-purple-500/10 p-2 text-purple-600"><History class="h-4 w-4" /></div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Pending Grading</p>
                        <p class="text-xl font-black text-purple-600">{{ stats.pendingGrading }} Tasks</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                         <div class="rounded-xl bg-emerald-500/10 p-2 text-emerald-600"><TrendingUp class="h-4 w-4" /></div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Mean Score</p>
                        <p class="text-xl font-black text-emerald-600">{{ stats.avgScore }}% Efficiency</p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center bg-white p-4 rounded-xl border shadow-sm">
                <div class="relative flex-1 md:max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                    <Input v-model="searchQuery" placeholder="Search by title, subject or class..." class="pl-9 h-10 border-slate-200" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10 border-slate-200 font-pulsar px-4" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4" /> {{ showFilters ? 'Hide Advanced' : 'Show Filters' }}
                    </Button>
                    <div class="h-10 w-[1px] bg-slate-100 mx-2"></div>
                    <div class="flex items-center rounded-xl bg-slate-50 p-1 border shadow-inner">
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-8 w-8 rounded-lg" 
                            :class="{ 'bg-white shadow-sm text-indigo-600': selectedView === 'grid' }"
                            @click="selectedView = 'grid'"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-8 w-8 rounded-lg" 
                            :class="{ 'bg-white shadow-sm text-indigo-600': selectedView === 'list' }"
                            @click="selectedView = 'list'"
                        >
                            <List class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 p-5 rounded-2xl border bg-slate-50 shadow-inner md:grid-cols-3 animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Test Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-xl border bg-white px-3 text-sm font-medium shadow-sm focus:ring-2 focus:ring-indigo-500/20">
                         <option value="all">Every State</option>
                         <option value="published">Published</option>
                         <option value="grading">Grading</option>
                         <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Learning Group</label>
                    <select v-model="selectedClassId" class="h-10 w-full rounded-xl border bg-white px-3 text-sm font-medium shadow-sm">
                         <option value="all">All Classes</option>
                         <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Subject Area</label>
                    <select v-model="selectedSubjectId" class="h-10 w-full rounded-xl border bg-white px-3 text-sm font-medium shadow-sm">
                         <option value="all">All Subjects</option>
                         <option v-for="s in subjects" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Grid Content -->
            <div v-if="selectedView === 'grid'" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                 <div v-for="assessment in assessments.data" :key="assessment.id" class="rounded-3xl border bg-card overflow-hidden shadow-sm hover:shadow-xl transition-all group flex flex-col border-t-8" :style="`border-top-color: ${assessment.status === 'completed' ? '#10b981' : '#6366f1'}`">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <Badge :class="['rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest border-0 flex items-center gap-1.5', getStatusColor(assessment.status)]">
                                <component :is="getStatusIcon(assessment.status)" class="h-3 w-3" />
                                {{ assessment.status.replace('_', ' ') }}
                            </Badge>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full hover:bg-slate-100 transition-colors"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-48 font-pulsar rounded-xl overflow-hidden p-1 shadow-2xl border-slate-200">
                                    <DropdownMenuItem class="rounded-lg font-bold"><Eye class="mr-3 h-4 w-4 text-indigo-600" /> Assessment Log</DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-lg font-bold"><Edit class="mr-3 h-4 w-4 text-amber-500" /> Edit Metadata</DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="rounded-lg font-black text-rose-600"><Trash2 class="mr-3 h-4 w-4" /> Delete Task</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        
                        <h3 class="text-xl font-black text-slate-900 leading-tight group-hover:text-indigo-600 transition-colors capitalize mb-1">{{ assessment.title }}</h3>
                        <div class="flex items-center gap-2 mb-6">
                             <div class="rounded-lg bg-slate-50 border px-2 py-0.5 text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ assessment.assessment_type?.name }}</div>
                        </div>

                        <div class="space-y-4">
                             <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border shadow-inner group-hover:bg-indigo-50 transition-colors">
                                  <div class="flex items-center gap-2">
                                       <BookOpen class="h-4 w-4 text-slate-400" />
                                       <span class="text-xs font-bold text-slate-600">{{ assessment.subject?.name }}</span>
                                  </div>
                                  <span class="text-xs font-black text-slate-400">CLASS {{ assessment.class?.name }}</span>
                             </div>

                             <div class="grid grid-cols-2 gap-3">
                                  <div class="p-3 rounded-2xl border bg-white flex flex-col items-center justify-center text-center shadow-xs">
                                       <Calendar class="h-4 w-4 text-indigo-400 mb-1.5" />
                                       <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Date</p>
                                       <p class="text-[10px] font-bold text-slate-800">{{ assessment.assessment_date }}</p>
                                  </div>
                                  <div class="p-3 rounded-2xl border bg-white flex flex-col items-center justify-center text-center shadow-xs">
                                       <Target class="h-4 w-4 text-emerald-400 mb-1.5" />
                                       <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Max Score</p>
                                       <p class="text-sm font-black text-slate-900 tracking-tighter">{{ assessment.total_marks }} pts</p>
                                  </div>
                             </div>
                        </div>
                    </div>
                    <div class="mt-auto px-6 py-4 bg-slate-50 border-t flex items-center justify-between">
                         <div class="flex -space-x-2">
                              <div v-for="i in 3" :key="i" class="h-6 w-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[8px] font-bold text-slate-400">
                                   U{{ i }}
                              </div>
                         </div>
                         <Button as-child variant="ghost" class="text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50/50 font-black text-[10px] uppercase tracking-widest h-auto py-1 px-3 rounded-lg">
                              <Link :href="`/assessments/${assessment.id}/grading`" class="flex items-center gap-2"> Manage Grading <ArrowRight class="h-3 w-3" /></Link>
                         </Button>
                    </div>
                 </div>

                 <!-- Empty State -->
                 <div v-if="assessments.data.length === 0" class="col-span-full py-24 text-center">
                    <div class="mx-auto w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inner border">
                        <ClipboardList class="h-10 w-10 text-slate-200" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">No Assessments Found</h3>
                    <p class="text-slate-500 font-medium max-w-sm mx-auto">Try broadening your search or creating a new test for your current learning cluster.</p>
                 </div>
            </div>

            <!-- List View -->
            <div v-else class="rounded-3xl border bg-card shadow-sm overflow-hidden overflow-x-auto border-t-8 border-t-indigo-500">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 border-b font-pulsar">
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest">Assessment Detail</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Context</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Max Points</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">State</th>
                            <th class="px-6 py-5 text-right text-xs font-bold uppercase text-slate-500 tracking-widest">Logic</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="assessment in assessments.data" :key="assessment.id" class="group hover:bg-slate-50/80 transition-all duration-300">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all text-sm shadow-inner border border-slate-200 uppercase">
                                         {{ assessment.code || assessment.title.substring(0, 3) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors leading-tight capitalize">{{ assessment.title }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                             <Calendar class="h-3 w-3 text-slate-400" />
                                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ assessment.assessment_date }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                     <Badge variant="outline" class="bg-indigo-50 border-indigo-100 text-indigo-700 text-[10px] font-black px-2 py-0 rounded-lg">{{ assessment.subject?.name }}</Badge>
                                     <span class="text-[9px] font-black text-slate-400 uppercase mt-1 tracking-widest">GRADE {{ assessment.class?.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center font-black text-slate-900 tracking-tighter text-lg">
                                {{ assessment.total_marks }}
                            </td>
                            <td class="px-6 py-5 text-center">
                                <Badge :class="['rounded-full px-3 py-1 text-[8px] font-black uppercase tracking-widest border-0 flex items-center gap-1.5 mx-auto w-fit', getStatusColor(assessment.status)]">
                                    <component :is="getStatusIcon(assessment.status)" class="h-2.5 w-2.5" />
                                    {{ assessment.status.replace('_', ' ') }}
                                </Badge>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button as-child variant="ghost" size="icon" class="h-9 w-9 text-slate-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all"><Link :href="`/assessments/${assessment.id}/grading`"><Plus class="h-4 w-4" /></Link></Button>
                                    <Button as-child variant="ghost" size="icon" class="h-9 w-9 text-slate-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all"><Link :href="`/assessments/${assessment.id}`"><Eye class="h-4 w-4" /></Link></Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-9 w-9 rounded-full"><MoreHorizontal class="h-4 w-4 text-slate-400" /></Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-48 font-pulsar rounded-xl p-1 shadow-2xl border-slate-200">
                                            <DropdownMenuItem class="rounded-lg font-bold"><Edit class="mr-3 h-4 w-4 text-amber-500" /> Edit Metadata</DropdownMenuItem>
                                            <DropdownMenuItem class="rounded-lg font-black text-rose-600"><Trash2 class="mr-3 h-4 w-4" /> Purge Record</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between border-t border-slate-100 pt-8 mt-4">
                <div class="flex items-center gap-6">
                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest whitespace-nowrap">{{ pageLabel }}</p>
                    <div class="flex items-center gap-3">
                         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Per View:</span>
                         <select v-model="perPage" class="h-8 rounded-lg border-slate-200 bg-white px-2 text-[10px] font-black text-slate-600 focus:ring-indigo-500 uppercase">
                             <option v-for="n in [15, 20, 50, 100]" :key="n" :value="n">{{ n }}</option>
                         </select>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 overflow-x-auto pb-2 sm:pb-0">
                    <Button 
                        v-for="(link, i) in assessments.links" 
                        :key="i"
                        variant="ghost"
                        size="sm"
                        :disabled="!link.url || link.active"
                        :class="['h-9 min-w-[36px] rounded-xl font-black text-xs uppercase tracking-tighter', 
                                link.active ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md' : 'text-slate-400 hover:bg-slate-100 hover:text-indigo-600']"
                        as-child
                    >
                        <Link v-if="link.url" :href="link.url" v-html="link.label" />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-pulsar {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Animations matching other pages */
.fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
