<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ClipboardList,
    Plus,
    Search,
    Filter,
    Calendar,
    BookOpen,
    Users,
    TrendingUp,
    MoreHorizontal,
    Eye,
    Edit,
    FileText,
    LayoutGrid,
    List,
    ChevronDown,
    ChevronUp,
    BookMarked,
    GraduationCap,
    Clock,
    CheckCircle2,
    AlertCircle,
    Trash2,
    Download,
    History,
    Zap,
    Target,
    ArrowRight,
    SearchCode,
    Activity,
    Database,
    Rows3,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Assessment Registry', href: '/assessments' },
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
const perPage = ref(props.assessments.per_page ?? 20);

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
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch([selectedStatus, selectedClassId, selectedSubjectId, perPage, selectedView], () => applyFilters());

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-slate-400 text-white shadow-lg shadow-slate-400/20',
        published: 'bg-primary text-white shadow-lg shadow-primary/20',
        scheduled: 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20',
        in_progress: 'bg-amber-500 text-white shadow-lg shadow-amber-500/20',
        grading: 'bg-purple-500 text-white shadow-lg shadow-purple-500/20',
        completed: 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20',
    };
    return colors[status] || 'bg-slate-400 text-white';
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle2;
        case 'draft': return FileText;
        case 'in_progress': return Activity;
        case 'grading': return History;
        default: return AlertCircle;
    }
};
</script>

<template>
    <Head title="Assessment Intelligence Matrix" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">Assessment matrix</h1>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Coordinating {{ assessments.total }} Academic Evaluation Nodes</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                     <div class="flex items-center gap-1 rounded-2xl border border-border bg-card p-1 shadow-sm">
                        <Button variant="ghost" size="icon" :class="selectedView === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'grid'" class="h-10 w-10 rounded-xl transition-all">
                            <LayoutGrid class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" :class="selectedView === 'list' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'list'" class="h-10 w-10 rounded-xl transition-all">
                            <Rows3 class="h-4 w-4" />
                        </Button>
                    </div>
                    <Button as-child class="h-12 rounded-2xl bg-primary px-8 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <Link href="/assessments/create">
                            <Plus class="mr-2.5 h-4 w-4" />
                            Initiate test node
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Intelligence Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Global Catalog', val: stats.total, sub: 'Synchronized Tests', icon: ClipboardList, color: 'primary' },
                    { label: 'Temporal Due', val: stats.thisWeek, sub: 'Scheduled cycles', icon: Calendar, color: 'amber-500' },
                    { label: 'Pending Grading', val: stats.pendingGrading, sub: 'Active Mutation', icon: History, color: 'purple-500' },
                    { label: 'Mean Efficiency', val: `${stats.avgScore}%`, sub: 'Population Score', icon: TrendingUp, color: 'emerald-500' }
                ]" :key="idx" class="group relative overflow-hidden rounded-3xl border border-border bg-card p-6 transition-all hover:border-primary/20">
                     <div class="absolute -right-4 -top-4 opacity-[0.03] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight text-foreground uppercase">{{ stat.val }}</h3>
                        <span class="text-[9px] font-bold text-primary uppercase opacity-60">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Internal Filter Protocol -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm transition-all hover:border-primary/10">
                <div class="flex h-14 items-center justify-between border-b border-border/50 bg-muted/10 px-8">
                    <div class="flex items-center gap-3">
                        <SearchCode class="h-4 w-4 text-primary" />
                        <span class="text-[10px] font-bold tracking-widest text-foreground uppercase">Assessment Search Protocol</span>
                    </div>
                </div>
                <div class="p-8">
                     <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Identification</Label>
                            <div class="relative">
                                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                <Input v-model="searchQuery" placeholder="Search parameters..." class="h-12 rounded-xl border-border bg-muted/20 pl-11 pr-4 text-xs font-bold uppercase focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Learning Group</Label>
                             <div class="relative">
                                <select v-model="selectedClassId" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">Global Classes</option>
                                    <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                         <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Subject Node</Label>
                             <div class="relative">
                                <select v-model="selectedSubjectId" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">Global Subjects</option>
                                    <option v-for="s in subjects" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                         <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Pulse State</Label>
                             <div class="relative">
                                <select v-model="selectedStatus" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">All States</option>
                                    <option value="published">Published</option>
                                    <option value="grading">Grading</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluation Matrix (Content) -->
            <div v-if="selectedView === 'grid'" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                 <div v-for="assessment in assessments.data" :key="assessment.id" 
                      class="group relative flex flex-col overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20 hover:shadow-2xl hover:shadow-primary/5 hover:scale-[1.02]">
                    
                    <div class="p-8 space-y-6">
                         <div class="flex items-start justify-between">
                            <Badge :class="getStatusColor(assessment.status)" class="rounded-xl border-0 px-3 py-1 text-[8px] font-black tracking-widest uppercase shadow-sm">
                                <component :is="getStatusIcon(assessment.status)" class="mr-1.5 h-3 w-3" />
                                {{ assessment.status }}
                            </Badge>
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-10 w-10 rounded-2xl hover:bg-muted text-muted-foreground transition-all">
                                        <MoreHorizontal class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 rounded-2xl border-border bg-card p-2 shadow-2xl">
                                    <DropdownMenuItem class="rounded-xl px-4 py-3 text-[10px] font-black tracking-widest text-muted-foreground uppercase focus:bg-muted" as-child>
                                        <Link :href="`/assessments/${assessment.id}`"><Eye class="mr-3 h-4 w-4 text-primary" /> View Intelligence</Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem class="rounded-xl px-4 py-3 text-[10px] font-black tracking-widest text-muted-foreground uppercase focus:bg-muted" as-child>
                                        <Link :href="`/assessments/${assessment.id}/edit`"><Edit class="mr-3 h-4 w-4 text-amber-500" /> Mutate Metadata</Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 border-border/50" />
                                     <DropdownMenuItem class="rounded-xl px-4 py-3 text-[10px] font-black tracking-widest text-rose-600 uppercase focus:bg-rose-50">
                                        <Trash2 class="mr-3 h-4 w-4" /> Purge Node
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                             </DropdownMenu>
                         </div>

                         <div class="space-y-2">
                             <h3 class="text-xl font-black tracking-tight text-foreground uppercase group-hover:text-primary transition-colors leading-tight">{{ assessment.title }}</h3>
                             <p class="text-[9px] font-bold tracking-[0.2em] text-primary uppercase opacity-60">{{ assessment.assessment_type?.name }}</p>
                         </div>

                         <div class="space-y-4 rounded-3xl border border-border/50 bg-muted/20 p-5 group-hover:bg-card transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <BookOpen class="h-4 w-4 text-muted-foreground opacity-40" />
                                    <span class="text-[10px] font-black text-foreground uppercase tracking-tight">{{ assessment.subject?.name }}</span>
                                </div>
                                <span class="text-[9px] font-bold text-muted-foreground uppercase opacity-40 tracking-widest">GRP_{{ assessment.class?.name }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Temporal Key</p>
                                    <p class="text-[10px] font-black text-foreground uppercase">{{ assessment.assessment_date }}</p>
                                </div>
                                <div class="space-y-1 text-right">
                                    <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Max marks</p>
                                    <p class="text-[10px] font-black text-primary uppercase">{{ assessment.total_marks }} PTS</p>
                                </div>
                            </div>
                         </div>
                    </div>

                    <div class="mt-auto border-t border-border/50 bg-muted/10 px-8 py-5 flex items-center justify-between">
                        <div class="flex -space-x-3">
                            <div v-for="i in 3" :key="i" class="h-8 w-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[8px] font-black text-slate-400">UID</div>
                        </div>
                        <Button variant="ghost" class="h-10 rounded-xl px-4 text-[9px] font-black tracking-widest text-primary uppercase hover:bg-primary/10" as-child>
                            <Link :href="`/assessments/${assessment.id}/grading`" class="flex items-center gap-2">Grading Matrix <ArrowRight class="h-3 w-3" /></Link>
                        </Button>
                    </div>
                    
                    <div class="absolute bottom-0 left-0 h-1 w-full" :class="assessment.status === 'completed' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-primary shadow-[0_0_10px_rgba(var(--primary),0.5)]'"></div>
                 </div>
            </div>

            <div v-else class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                 <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/10 text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                                <th class="px-10 py-6">Evaluation Node</th>
                                <th class="px-6 py-6">Academic Context</th>
                                <th class="px-6 py-6">Performance Key</th>
                                <th class="px-6 py-6">Pulse State</th>
                                <th class="px-10 py-6 text-right font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                             <tr v-for="assessment in assessments.data" :key="assessment.id" class="group transition-all hover:bg-muted/30">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-border bg-white text-[10px] font-black text-primary uppercase shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                                            {{ assessment.code || assessment.title.substring(0, 3) }}
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-xs font-black tracking-tight text-foreground uppercase group-hover:text-primary transition-colors">{{ assessment.title }}</p>
                                            <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50 tracking-widest">{{ assessment.assessment_date }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="space-y-1">
                                        <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2 py-0.5 text-[8px] font-black tracking-widest text-primary uppercase">{{ assessment.subject?.name }}</Badge>
                                        <p class="text-[9px] font-black text-muted-foreground uppercase opacity-40 ml-1">GRADE_{{ assessment.class?.name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-sm font-black text-foreground uppercase tracking-tighter">{{ assessment.total_marks }} PTS</td>
                                <td class="px-6 py-6">
                                     <Badge :class="getStatusColor(assessment.status)" class="rounded-xl border-0 px-3 py-1 text-[8px] font-black tracking-widest uppercase">
                                        {{ assessment.status }}
                                    </Badge>
                                </td>
                                <td class="px-10 py-6 text-right">
                                     <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary transition-all"><Link :href="`/assessments/${assessment.id}/grading`"><Zap class="h-4 w-4" /></Link></Button>
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary transition-all"><Link :href="`/assessments/${assessment.id}`"><Eye class="h-4 w-4" /></Link></Button>
                                         <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted transition-all"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-2xl border-border bg-card p-2 shadow-2xl">
                                                <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-black tracking-widest text-muted-foreground uppercase focus:bg-muted" as-child>
                                                    <Link :href="`/assessments/${assessment.id}/edit`"><Edit class="mr-3 h-4 w-4" /> Mutate</Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 border-border/50" />
                                                <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-black tracking-widest text-rose-600 uppercase focus:bg-rose-50">
                                                    <Trash2 class="mr-3 h-4 w-4" /> Purge
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                             </tr>
                        </tbody>
                    </table>
                 </div>
            </div>

            <!-- Analytics & Pagination Container -->
            <div class="flex flex-col gap-10 border-t border-border/50 pt-10 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-10">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Matrix Telemetry</p>
                        <p class="text-[10px] font-black text-foreground uppercase tracking-widest">Showing {{ assessments.from }} - {{ assessments.to }} of {{ assessments.total }} Nodes</p>
                    </div>
                     <div class="flex items-center gap-3 bg-muted/20 px-4 py-2 rounded-2xl border border-border/50">
                        <span class="text-[9px] font-black text-muted-foreground uppercase opacity-60">Density:</span>
                        <select v-model="perPage" class="bg-transparent text-[10px] font-black uppercase outline-none text-primary">
                            <option v-for="n in [15, 20, 50, 100]" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Button v-for="(link, i) in assessments.links" :key="i"
                        variant="ghost" :disabled="!link.url || link.active"
                        :class="[
                            'h-10 min-w-[40px] rounded-xl text-[10px] font-black tracking-widest uppercase transition-all',
                            link.active ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary' : 'text-muted-foreground hover:bg-muted'
                        ]"
                        as-child
                    >
                        <Link v-if="link.url" :href="link.url" v-html="link.label"></Link>
                        <span v-else v-html="link.label"></span>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
