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
    Upload,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import BulkUploadDialog from '@/components/assessments/BulkUploadDialog.vue';
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
const bulkUploadOpen = ref(false);

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
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Assessment Matrix</h1>
                    <p class="text-xs text-muted-foreground">Coordinating {{ assessments.total }} Academic Evaluation Nodes</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <Link href="/assessments/grading">
                            <Zap class="mr-2 h-4 w-4 text-primary" />Grading
                        </Link>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <Link href="/assessments/rubrics">
                            <BookMarked class="mr-2 h-4 w-4 text-primary" />Rubrics
                        </Link>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <Link href="/assessments/results">
                            <TrendingUp class="mr-2 h-4 w-4 text-primary" />Results
                        </Link>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <Link href="/assessments/report-cards">
                            <FileText class="mr-2 h-4 w-4 text-primary" />Report Cards
                        </Link>
                    </Button>
                     <div class="flex items-center gap-1 rounded-2xl border border-border bg-card p-1 shadow-sm">
                        <Button variant="ghost" size="icon" :class="selectedView === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'grid'" class="h-10 w-10 rounded-xl transition-all">
                            <LayoutGrid class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" :class="selectedView === 'list' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'list'" class="h-10 w-10 rounded-xl transition-all">
                            <Rows3 class="h-4 w-4" />
                        </Button>
                    </div>
                    <Button @click="bulkUploadOpen = true" variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted">
                        <Upload class="mr-2 h-4 w-4 text-primary" />Bulk Upload
                    </Button>
                    <Button as-child class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                        <Link href="/assessments/setup">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Assessment
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Assessment Usage Guide -->
            <div class="overflow-hidden rounded-xl border border-primary/20 bg-primary/5 shadow-sm transition-all hover:bg-primary/[0.07]">
                <div class="flex items-center gap-4 p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20">
                        <Info class="h-6 w-6" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <h2 class="text-sm font-bold text-primary tracking-tight">Assessment Utility Protocol</h2>
                        <p class="text-xs text-muted-foreground max-w-3xl">Distinguish between <span class="font-bold text-foreground underline decoration-primary/30">Internal (School-set)</span> and <span class="font-bold text-foreground underline decoration-primary/30">Ministry (KNEC)</span> evaluations. Initiate evaluations via the <span class="font-semibold text-primary">Setup Wizard</span>, map them to CBC learning outcomes, and execute data entry through the <span class="font-semibold text-primary">Grading Sheet</span> terminal. Use bulk upload for high-volume results migration.</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2">
                        <Button variant="outline" class="h-9 rounded-lg border-primary/20 bg-card text-[10px] font-bold tracking-widest text-primary uppercase transition-all hover:bg-primary hover:text-white">Usage Documentation</Button>
                    </div>
                </div>
            </div>

            <!-- Intelligence Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Global Catalog', val: stats.total, sub: 'Synchronized Tests', icon: ClipboardList, color: 'primary' },
                    { label: 'Temporal Due', val: stats.thisWeek, sub: 'Scheduled cycles', icon: Calendar, color: 'amber-500' },
                    { label: 'Pending Grading', val: stats.pendingGrading, sub: 'Active Mutation', icon: History, color: 'purple-500' },
                    { label: 'Mean Efficiency', val: `${stats.avgScore}%`, sub: 'Population Score', icon: TrendingUp, color: 'emerald-500' }
                ]" :key="idx" class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md">
                     <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight">{{ stat.val }}</h3>
                        <span class="text-[10px] font-semibold text-primary">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Internal Filter Protocol -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <SearchCode class="h-4 w-4 text-primary" />
                        <span class="text-xs font-semibold text-foreground tracking-tight">Assessment Search Center</span>
                    </div>
                </div>
                <div class="p-8">
                     <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Search Assessment</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input v-model="searchQuery" placeholder="Search parameters..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Learning Group</Label>
                             <div class="relative">
                                <select v-model="selectedClassId" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">Global Classes</option>
                                    <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                         <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Subject</Label>
                             <div class="relative">
                                <select v-model="selectedSubjectId" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">Global Subjects</option>
                                    <option v-for="s in subjects" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                         <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Status</Label>
                             <div class="relative">
                                <select v-model="selectedStatus" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">All States</option>
                                    <option value="published">Published</option>
                                    <option value="grading">Grading</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
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
                             <div class="flex items-center gap-2">
                                 <p class="text-[9px] font-bold tracking-[0.2em] text-primary uppercase opacity-60">{{ assessment.assessment_type?.name }}</p>
                                 <Badge v-if="assessment.source" 
                                       :class="assessment.source === 'ministry' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
                                       class="rounded-lg border px-2 py-0.5 text-[8px] font-black tracking-tight uppercase shadow-sm">
                                    {{ assessment.source === 'ministry' ? 'Ministry' : 'Internal' }}
                                 </Badge>
                             </div>
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
                            <Link :href="route('assessments.grading', { assessment: assessment.id })" class="flex items-center gap-2">Grading Matrix <ArrowRight class="h-3 w-3" /></Link>
                        </Button>
                    </div>
                    
                    <div class="absolute bottom-0 left-0 h-1 w-full" :class="assessment.status === 'completed' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-primary shadow-[0_0_10px_rgba(var(--primary),0.5)]'"></div>
                 </div>
            </div>

            <div v-else class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all">
                 <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="px-6 py-4">Assessment</th>
                                <th class="px-6 py-4">Context</th>
                                <th class="px-6 py-4">Max Marks</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
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
                                            <h4 class="text-base font-bold text-foreground capitalize group-hover:text-primary transition-colors leading-tight line-clamp-1">{{ assessment.title }}</h4>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge variant="outline" class="rounded-lg border-border bg-muted/20 px-2 py-0.5 text-[9px] font-bold tracking-tight text-muted-foreground uppercase transition-all hover:bg-primary/5 hover:text-primary">{{ assessment.subject?.name }}</Badge>
                            <Badge v-if="assessment.source" 
                                   :class="assessment.source === 'ministry' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
                                   class="rounded-lg border px-2 py-0.5 text-[9px] font-bold tracking-tight uppercase shadow-sm">
                                {{ assessment.source === 'ministry' ? 'Ministry/KNEC' : 'Internal' }}
                            </Badge>
                        </div>                </div>
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
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary transition-all"><Link :href="route('assessments.grading', { assessment: assessment.id })"><Zap class="h-4 w-4" /></Link></Button>
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
            <div class="flex h-16 items-center justify-between border-t border-border/50 px-6 bg-muted/5 rounded-xl border">
                <p class="text-xs text-muted-foreground font-medium">Showing {{ assessments.from }} - {{ assessments.to }} of {{ assessments.total }} assessments</p>

                <div class="flex items-center gap-1.5">
                    <template v-for="(link, i) in assessments.links" :key="i">
                        <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-8 w-8 rounded-lg text-xs font-medium transition-all', link.active ? 'border-primary bg-primary text-white shadow-sm' : 'border-border bg-card hover:bg-muted text-muted-foreground']" as-child>
                            <Link :href="link.url" v-html="link.label"></Link>
                        </Button>
                        <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-xs font-medium border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" as-child>
                             <Link v-if="link.url" :href="link.url">Prev</Link>
                             <span v-else>Prev</span>
                        </Button>
                        <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-xs font-medium border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" as-child>
                             <Link v-if="link.url" :href="link.url">Next</Link>
                             <span v-else>Next</span>
                        </Button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Dialog -->
        <BulkUploadDialog
            v-model:open="bulkUploadOpen"
            title="Bulk Import Assessments"
            description="Upload a CSV or Excel file to create multiple assessments at once."
            template-url="/assessments/import-template"
            upload-url="/assessments/import"
            @success="applyFilters()"
        />
    </AppLayout>
</template>
