<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    ShieldCheck,
    Plus,
    Search,
    Edit2,
    Trash2,
    Layers,
    ChevronRight,
    GraduationCap,
    CheckCircle2,
    BookOpen,
    Sparkles,
    Target,
    Activity,
    Book,
    FileText,
    MoreHorizontal,
    Filter,
    X,
    Upload,
    Download,
    Info,
    LayoutGrid,
    Rows3,
    Zap,
    History,
    TrendingUp,
    ClipboardList,
    Calendar,
    SearchCode,
    Database,
    ChevronDown,
    ArrowRight,
    Eye,
    Edit
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import BulkUploadDialog from '@/components/assessments/BulkUploadDialog.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    competencies: Record<string, any[]>;
    grades: any[];
    subjects: any[];
    indicators: any[];
}>();

const breadcrumbs = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Curriculum Schema', href: '#' },
    { title: 'Competencies', href: '#' },
];

const selectedSubjectId = ref('all');
const selectedGradeId = ref('all');
const searchQuery = ref('');
const selectedView = ref<'grid' | 'list'>('grid');
const bulkUploadOpen = ref(false);

const showCompetencyModal = ref(false);
const showIndicatorModal = ref(false);
const editingCompetency = ref<any>(null);

const competencyForm = useForm({
    name: '',
    code: '',
    description: '',
    category: 'custom',
    display_order: 1,
    is_active: true,
});

const indicatorForm = useForm({
    competency_id: '',
    grade_level_id: '',
    subject_id: '',
    strand_id: '',
    sub_strand_id: '',
    indicator: '',
    code: '',
    description: '',
    display_order: 1,
    is_active: true,
});

// For modal logic
const availableStrands = computed(() => {
    if (!indicatorForm.subject_id) return [];
    return props.subjects.find((s: any) => s.id === Number(indicatorForm.subject_id))?.strands || [];
});

const availableSubStrands = computed(() => {
    if (!indicatorForm.strand_id) return [];
    return availableStrands.value.find((s: any) => s.id === Number(indicatorForm.strand_id))?.sub_strands || [];
});

const filteredIndicators = computed(() => {
    return props.indicators.filter(ind => {
        const matchesSearch = !searchQuery.value || 
            ind.indicator.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            ind.code?.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesGrade = selectedGradeId.value === 'all' || ind.grade_level_id === Number(selectedGradeId.value);
        
        const matchesSubject = selectedSubjectId.value === 'all' || 
            ind.sub_strand?.strand?.subject_id === Number(selectedSubjectId.value);
            
        return matchesSearch && matchesGrade && matchesSubject;
    });
});

const stats = computed(() => ({
    total: props.indicators.length,
    subjects: props.subjects.length,
    coreComp: (props.competencies['core'] || []).length,
    customComp: (props.competencies['custom'] || []).length,
}));

const openCompetencyModal = (comp: any = null) => {
    editingCompetency.value = comp;
    if (comp) {
        competencyForm.name = comp.name;
        competencyForm.code = comp.code;
        competencyForm.description = comp.description;
        competencyForm.category = comp.category;
        competencyForm.display_order = comp.display_order;
        competencyForm.is_active = comp.is_active;
    } else {
        competencyForm.reset();
        competencyForm.category = 'custom';
    }
    showCompetencyModal.value = true;
};

const submitCompetency = () => {
    if (editingCompetency.value) {
        competencyForm.put(route('curriculum.competencies.update', editingCompetency.value.id), {
            onSuccess: () => {
                showCompetencyModal.value = false;
                router.reload();
            },
        });
    } else {
        competencyForm.post(route('curriculum.competencies.store'), {
            onSuccess: () => {
                showCompetencyModal.value = false;
                router.reload();
            },
        });
    }
};

const openIndicatorModal = () => {
    indicatorForm.reset();
    showIndicatorModal.value = true;
};

const submitIndicator = () => {
    indicatorForm.post(route('curriculum.competencies.storeIndicator'), {
        onSuccess: () => {
            showIndicatorModal.value = false;
            router.reload();
        },
    });
};

const deleteIndicator = (id: number) => {
    if (confirm('Are you sure you want to purge this indicator node?')) {
        router.delete(route('curriculum.competencies.destroyIndicator', id));
    }
};

const allCore = computed(() => props.competencies['core'] || []);

</script>

<template>
    <Head title="Curriculum Matrix | Competencies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-10 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            
            <!-- Strategic Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl uppercase">Competency Matrix</h1>
                    <p class="text-xs text-muted-foreground uppercase opacity-60">Mapping {{ indicators.length }} Evaluation Outcomes</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <a :href="route('curriculum.competencies.template')">
                            <Download class="mr-2 h-4 w-4 text-primary" />Template
                        </a>
                    </Button>
                    <Button @click="bulkUploadOpen = true" variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted">
                        <Upload class="mr-2 h-4 w-4 text-primary" />Bulk Upload
                    </Button>
                    <div class="flex items-center gap-1 rounded-lg border border-border bg-card p-1 shadow-sm">
                        <Button variant="ghost" size="icon" :class="selectedView === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'grid'" class="h-10 w-10 rounded-lg transition-all">
                            <LayoutGrid class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" :class="selectedView === 'list' ? 'bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary hover:text-white' : 'text-muted-foreground hover:bg-muted'" @click="selectedView = 'list'" class="h-10 w-10 rounded-lg transition-all">
                            <Rows3 class="h-4 w-4" />
                        </Button>
                    </div>
                    <Button @click="openIndicatorModal()" class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                        <Plus class="mr-2 h-4 w-4" />
                        New Indicator
                    </Button>
                </div>
            </div>

            <!-- Curriculum Utility Guide -->
            <div class="overflow-hidden rounded-lg border border-primary/20 bg-primary/5 shadow-sm transition-all hover:bg-primary/[0.07]">
                <div class="flex items-center gap-4 p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary text-white shadow-lg shadow-primary/20">
                        <Info class="h-6 w-6" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <h2 class="text-sm font-bold text-primary tracking-tight uppercase">Management Protocol</h2>
                        <p class="text-[11px] text-muted-foreground max-w-5xl leading-relaxed">
                            Connect indicators to specific curriculum nodes (Subject > Strand > Sub-strand) for granular assessment tracking. Core competencies (COM, CT, DL, etc.) are globally synchronized. Use the bulk engine to migrate entire subject frameworks from CSV templates.
                        </p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2">
                        <Button @click="openCompetencyModal()" variant="outline" class="h-9 rounded-lg border-primary/20 bg-card text-[10px] font-bold tracking-widest text-primary uppercase transition-all hover:bg-primary hover:text-white">Define Competency</Button>
                    </div>
                </div>
            </div>

            <!-- Intelligence Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Total Indicators', val: stats.total, sub: 'Mapped Outcomes', icon: Target, color: 'primary' },
                    { label: 'Learning Areas', val: stats.subjects, sub: 'Active Subjects', icon: BookOpen, color: 'amber-500' },
                    { label: 'Core Standards', val: stats.coreComp, sub: 'National CBC', icon: ShieldCheck, color: 'purple-500' },
                    { label: 'Custom Nodes', val: stats.customComp, sub: 'School Specific', icon: Zap, color: 'emerald-500' }
                ]" :key="idx" class="group relative overflow-hidden rounded-lg border border-border bg-card p-6 transition-all hover:shadow-md">
                     <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-tight">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight">{{ stat.val }}</h3>
                        <span class="text-[10px] font-semibold text-primary uppercase">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Internal Filter Protocol -->
            <div class="overflow-hidden rounded-lg border border-border bg-card shadow-sm transition-all">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <SearchCode class="h-4 w-4 text-primary" />
                        <span class="text-[11px] font-bold text-foreground tracking-widest uppercase">Curriculum Filter Matrix</span>
                    </div>
                </div>
                <div class="p-8">
                    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-muted-foreground opacity-60">Search Outcomes</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input v-model="searchQuery" placeholder="Behavioral criteria..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-muted-foreground opacity-60">Learning Area</Label>
                             <div class="relative">
                                <select v-model="selectedSubjectId" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">Global Subjects</option>
                                    <option v-for="s in subjects" :key="s.id" :value="String(s.id)">{{ (s as any).name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-muted-foreground opacity-60">Target Grade</Label>
                             <div class="relative">
                                <select v-model="selectedGradeId" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">Global Grades</option>
                                    <option v-for="g in grades" :key="g.id" :value="String(g.id)">{{ (g as any).name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                        <div class="space-y-2 text-right pt-6">
                            <Badge v-if="selectedSubjectId !== 'all'" class="bg-primary/10 text-primary border-0 rounded-lg text-[9px] font-bold uppercase">{{ subjects.find((s: any) => s.id == Number(selectedSubjectId))?.name }}</Badge>
                             <Badge v-if="selectedGradeId !== 'all'" class="bg-indigo-50 text-indigo-600 border-0 rounded-lg ml-2 text-[9px] font-bold uppercase">Grade {{ grades.find((g: any) => g.id == Number(selectedGradeId))?.name }}</Badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluation Matrix (Content) -->
            <div v-if="selectedView === 'grid'" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                 <div v-for="indicator in filteredIndicators" :key="indicator.id" 
                      class="group relative flex flex-col overflow-hidden rounded-lg border border-border bg-card shadow-sm transition-all hover:border-primary/20 hover:shadow-lg hover:scale-[1.01]">
                    
                    <div class="p-8 space-y-6">
                         <div class="flex items-start justify-between">
                            <Badge class="bg-primary/10 text-primary border-0 px-3 py-1 text-[8px] font-black tracking-widest uppercase rounded-lg shadow-sm">
                                {{ indicator.competency?.name || 'Standard' }}
                            </Badge>
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-9 w-9 rounded-lg hover:bg-muted text-muted-foreground transition-all">
                                        <MoreHorizontal class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 rounded-lg border-border bg-card p-2 shadow-xl">
                                    <DropdownMenuItem class="rounded-lg px-4 py-2.5 text-[9px] font-black tracking-widest text-muted-foreground uppercase focus:bg-muted">
                                        <Edit class="mr-3 h-3.5 w-3.5 text-amber-500" /> Mutate outcome
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 border-border/50" />
                                     <DropdownMenuItem @click="deleteIndicator(indicator.id)" class="rounded-lg px-4 py-2.5 text-[9px] font-black tracking-widest text-rose-600 uppercase focus:bg-rose-50">
                                        <Trash2 class="mr-3 h-3.5 w-3.5" /> Purge node
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                             </DropdownMenu>
                         </div>

                         <div class="space-y-3">
                             <h3 class="text-sm font-bold tracking-tight text-foreground uppercase group-hover:text-primary transition-colors leading-relaxed line-clamp-3 min-h-[3rem]">{{ indicator.indicator }}</h3>
                             <div class="flex items-center gap-3">
                                 <p class="text-[9px] font-black tracking-[0.2em] text-primary uppercase opacity-60">{{ indicator.code || 'CODE_TBD' }}</p>
                                 <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2 py-0.5 text-[8px] font-black tracking-tight uppercase shadow-sm text-primary">
                                    {{ indicator.grade_level?.name }}
                                 </Badge>
                             </div>
                         </div>

                         <div class="space-y-3 rounded-lg border border-border/50 bg-muted/20 p-5 group-hover:bg-card transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <BookOpen class="h-3.5 w-3.5 text-muted-foreground opacity-40" />
                                    <span class="text-[9px] font-black text-foreground uppercase tracking-tight">{{ indicator.sub_strand?.strand?.subject?.name }}</span>
                                </div>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase opacity-40 tracking-widest">{{ indicator.sub_strand?.strand?.name }}</span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Sub-strand</p>
                                <p class="text-[10px] font-bold text-foreground uppercase line-clamp-1">{{ indicator.sub_strand?.name }}</p>
                            </div>
                         </div>
                    </div>

                    <div class="mt-auto border-t border-border/50 bg-muted/10 px-8 py-4 flex items-center justify-between">
                         <div class="flex items-center gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Node Active</span>
                        </div>
                        <Button variant="ghost" class="h-9 rounded-lg px-4 text-[9px] font-black tracking-widest text-primary uppercase hover:bg-primary/10 transition-all">
                             Context <ArrowRight class="h-3 w-3 ml-2" />
                        </Button>
                    </div>
                 </div>

                 <button @click="openIndicatorModal()" 
                         class="group relative flex flex-col items-center justify-center min-h-[350px] overflow-hidden rounded-lg border-2 border-dashed border-border bg-muted/5 transition-all hover:bg-white hover:border-primary/20">
                    <div class="h-16 w-16 rounded-lg bg-white flex items-center justify-center text-muted-foreground group-hover:bg-primary group-hover:text-white transition-all shadow-sm group-hover:shadow-lg">
                        <Plus class="w-8 h-8" />
                    </div>
                    <div class="mt-6 text-center">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Inject outcome</p>
                        <p class="text-[9px] font-bold text-muted-foreground mt-2 uppercase tracking-widest opacity-40 italic">New curriculum node</p>
                    </div>
                 </button>
            </div>

            <!-- List View -->
            <div v-else class="overflow-hidden rounded-lg border border-border bg-card shadow-sm transition-all">
                 <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="px-8 py-4">Behavioral outcome</th>
                                <th class="px-6 py-4">Mapping</th>
                                <th class="px-6 py-4">Grade</th>
                                <th class="px-6 py-4">Competency</th>
                                <th class="px-8 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                             <tr v-for="indicator in filteredIndicators" :key="indicator.id" class="group transition-all hover:bg-muted/30">
                                <td class="px-8 py-5 max-w-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-white text-[9px] font-black text-primary uppercase shadow-sm transition-all group-hover:bg-primary group-hover:text-white">
                                            {{ indicator.code?.substring(0, 3) || 'IND' }}
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-sm font-bold text-foreground leading-snug group-hover:text-primary transition-colors line-clamp-1">{{ indicator.indicator }}</h4>
                                            <p class="text-[9px] font-bold text-muted-foreground/50 uppercase tracking-widest">{{ indicator.code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-[10px] font-bold text-foreground/70 uppercase truncate max-w-[200px]">
                                        {{ indicator.sub_strand?.strand?.subject?.name }} > {{ indicator.sub_strand?.name }}
                                    </p>
                                </td>
                                <td class="px-6 py-5 text-[10px] font-black text-primary uppercase">{{ indicator.grade_level?.name }}</td>
                                <td class="px-6 py-5">
                                     <Badge class="bg-indigo-600/10 text-indigo-600 border-0 rounded-lg h-7 px-3 text-[8px] font-black tracking-widest uppercase">{{ indicator.competency?.name }}</Badge>
                                </td>
                                <td class="px-8 py-5 text-right">
                                     <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-muted-foreground hover:bg-primary/10 hover:text-primary transition-all"><Edit2 class="h-3.5 w-3.5" /></Button>
                                         <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-muted transition-all"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-lg border-border bg-card p-2 shadow-xl">
                                                <DropdownMenuItem @click="deleteIndicator(indicator.id)" class="rounded-lg px-4 py-2.5 text-[9px] font-black tracking-widest text-rose-600 uppercase focus:bg-rose-50">
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

            <div class="flex h-14 items-center justify-between border-t border-border/50 px-6 bg-muted/5 rounded-lg border">
                <p class="text-[10px] text-muted-foreground font-black uppercase tracking-widest">Synchronized {{ filteredIndicators.length }} outcome nodes</p>
                <div class="flex items-center gap-1">
                    <Button variant="outline" size="sm" class="h-8 rounded-lg px-3 text-[10px] font-black uppercase tracking-widest border-border bg-card hover:bg-muted disabled:opacity-30">Prev</Button>
                    <Button variant="outline" size="sm" class="h-8 w-8 rounded-lg text-xs font-bold border-primary bg-primary text-white shadow-sm">1</Button>
                    <Button variant="outline" size="sm" class="h-8 rounded-lg px-3 text-[10px] font-black uppercase tracking-widest border-border bg-card hover:bg-muted disabled:opacity-30">Next</Button>
                </div>
            </div>
        </div>

        <!-- Modals with smaller radii -->
        <Dialog v-model:open="showCompetencyModal">
            <DialogContent class="bg-card border-border text-foreground rounded-xl p-0 overflow-hidden max-w-lg shadow-2xl">
                <DialogHeader class="p-8 bg-primary text-white relative">
                    <DialogTitle class="text-xl font-bold uppercase tracking-tight">Competency Schema</DialogTitle>
                    <DialogDescription class="text-primary-foreground/70 font-bold text-[9px] uppercase tracking-widest mt-1">Define global pedagogical standard</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCompetency" class="p-8 space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                             <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Label</Label>
                             <Input v-model="competencyForm.name" class="bg-muted/10 border-border rounded-lg h-12 text-sm font-bold" placeholder="Critical Thinking" />
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Ref Code</Label>
                                <Input v-model="competencyForm.code" class="bg-muted/10 border-border rounded-lg h-12 text-sm font-bold uppercase text-center" />
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Rank</Label>
                                <Input v-model="competencyForm.display_order" type="number" class="bg-muted/10 border-border rounded-lg h-12 text-sm font-bold text-center" />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                             <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Specification</Label>
                             <Textarea v-model="competencyForm.description" class="bg-muted/10 border-border rounded-lg min-h-[100px] text-xs font-bold" />
                        </div>
                    </div>
                    <DialogFooter class="p-0">
                        <Button type="submit" class="w-full h-14 bg-primary text-white font-bold uppercase tracking-widest rounded-lg shadow-lg">
                            {{ competencyForm.processing ? 'Syncing...' : 'Register Standard' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showIndicatorModal">
            <DialogContent class="bg-card border-border text-foreground rounded-xl p-0 overflow-hidden max-w-xl shadow-2xl">
                <DialogHeader class="p-8 bg-indigo-600 text-white relative">
                    <DialogTitle class="text-xl font-bold uppercase tracking-tight">Outcome Ingestion</DialogTitle>
                    <DialogDescription class="text-indigo-100/70 font-bold text-[9px] uppercase tracking-widest mt-1">Deploy mapping to curriculum tree</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitIndicator" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                         <div class="space-y-1.5">
                             <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Grade</Label>
                             <Select v-model="indicatorForm.grade_level_id">
                                <SelectTrigger class="bg-muted/10 border-border h-12 rounded-lg text-xs font-bold">
                                    <SelectValue placeholder="Grade" />
                                </SelectTrigger>
                                <SelectContent class="bg-card border-border rounded-lg">
                                    <SelectItem v-for="g in (grades as any[])" :key="g.id" :value="g.id.toString()" class="text-xs font-bold py-3">{{ g.name }}</SelectItem>
                                </SelectContent>
                             </Select>
                        </div>
                        <div class="space-y-1.5">
                             <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Core Link</Label>
                             <Select v-model="indicatorForm.competency_id">
                                <SelectTrigger class="bg-muted/10 border-border h-12 rounded-lg text-xs font-bold">
                                    <SelectValue placeholder="Competency" />
                                </SelectTrigger>
                                <SelectContent class="bg-card border-border rounded-lg">
                                    <SelectItem v-for="c in (allCore as any[])" :key="c.id" :value="c.id.toString()" class="text-xs font-bold py-3">{{ c.name }}</SelectItem>
                                </SelectContent>
                             </Select>
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-border/50 pt-4">
                        <div class="space-y-1.5">
                            <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Subject Area</Label>
                            <Select v-model="indicatorForm.subject_id">
                                <SelectTrigger class="bg-muted/10 border-border h-12 rounded-lg text-xs font-bold">
                                    <SelectValue placeholder="Select Subject" />
                                </SelectTrigger>
                                <SelectContent class="bg-card border-border rounded-lg">
                                    <SelectItem v-for="s in (subjects as any[])" :key="s.id" :value="s.id.toString()" class="text-xs font-bold py-3">{{ s.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Strand</Label>
                                <Select v-model="indicatorForm.strand_id" :disabled="!indicatorForm.subject_id">
                                    <SelectTrigger class="bg-muted/10 border-border h-12 rounded-lg text-xs font-bold">
                                        <SelectValue placeholder="Select Strand" />
                                    </SelectTrigger>
                                    <SelectContent class="bg-card border-border rounded-lg">
                                        <SelectItem v-for="st in (availableStrands as any[])" :key="st.id" :value="st.id.toString()" class="text-xs font-bold py-3">{{ st.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Sub-strand</Label>
                                <Select v-model="indicatorForm.sub_strand_id" :disabled="!indicatorForm.strand_id">
                                    <SelectTrigger class="bg-muted/10 border-border h-12 rounded-lg text-xs font-bold">
                                        <SelectValue placeholder="Select Sub" />
                                    </SelectTrigger>
                                    <SelectContent class="bg-card border-border rounded-lg">
                                        <SelectItem v-for="ss in (availableSubStrands as any[])" :key="ss.id" :value="ss.id.toString()" class="text-xs font-bold py-3">{{ ss.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5 border-t border-border/50 pt-4">
                        <Label class="text-[9px] font-black uppercase text-muted-foreground ml-1">Behavioral Criteria</Label>
                        <Input v-model="indicatorForm.indicator" class="bg-muted/10 border-border rounded-lg h-14 text-sm font-bold" placeholder="Student describes..." />
                    </div>

                    <DialogFooter class="p-0">
                        <Button type="submit" class="w-full h-14 bg-indigo-600 text-white font-bold uppercase tracking-widest rounded-lg shadow-lg">
                            {{ indicatorForm.processing ? 'Integrating Node...' : 'Deploy Indicator' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Bulk Upload Hub -->
        <BulkUploadDialog
            v-model:open="bulkUploadOpen"
            title="Curriculum Ingestion"
            description="Acquire and upload standardized CSV spec (8 columns) to migrate mass indicator datasets."
            template-url="/curriculum/competencies/template"
            upload-url="/curriculum/competencies/import"
            @success="router.reload()"
        />

    </AppLayout>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
