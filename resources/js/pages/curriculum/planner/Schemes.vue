<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    ChevronDown,
    ChevronRight,
    Edit,
    FileText,
    Filter,
    GraduationCap,
    MoreHorizontal,
    Plus,
    Search,
    Trash2,
    CheckCircle2,
    Clock,
    AlertCircle,
    Sparkles,
    LayoutGrid,
    ListFilter,
    Users,
    TrendingUp,
    FileCheck,
    Eye
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface Scheme {
    id: number;
    title: string;
    status: 'draft' | 'submitted' | 'approved' | 'rejected';
    total_weeks: number;
    lessons_per_week: number;
    subject?: { name: string; id: number };
    grade_level?: { name: string; short_name: string; id: number };
    academic_term?: { name: string; id: number };
    description?: string;
    prepared_by_user?: { name: string };
    created_at: string;
}

interface Props {
    schemes: Scheme[];
    subjects: Array<{ id: number; name: string }>;
    grades: Array<{ id: number; name: string }>;
    terms: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const page = usePage<{ flash: { success?: string; error?: string } }>();

const breadcrumbs = [
    { title: 'Curriculum', href: '#' },
    { title: 'Academic Planner', href: '#' },
    { title: 'Schemes of Work', href: '/curriculum/planner/schemes' },
];

const showToast = ref(false);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

watch(flashSuccess, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => showToast.value = false, 3000);
    }
});

watch(flashError, (val) => {
    if (val) {
        showToast.value = true;
        setTimeout(() => showToast.value = false, 3000);
    }
});

// Filtering
const searchQuery = ref('');
const selectedSubject = ref('all');
const selectedGrade = ref('all');
const selectedTerm = ref('all');
const showFilters = ref(false);

const filteredSchemes = computed(() => {
    return props.schemes.filter(scheme => {
        const matchesSearch = scheme.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             scheme.subject?.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = selectedSubject.value === 'all' || scheme.subject?.id === Number(selectedSubject.value);
        const matchesGrade = selectedGrade.value === 'all' || scheme.grade_level?.id === Number(selectedGrade.value);
        const matchesTerm = selectedTerm.value === 'all' || scheme.academic_term?.id === Number(selectedTerm.value);
        
        return matchesSearch && matchesSubject && matchesGrade && matchesTerm;
    });
});

// Modal Logic
const isAddingScheme = ref(false);
const isEditing = ref(false);
const selectedSchemeId = ref<number | null>(null);

const schemeForm = useForm({
    subject_id: '',
    grade_level_id: '',
    academic_term_id: '',
    title: '',
    description: '',
    total_weeks: 13,
    lessons_per_week: 5,
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedSchemeId.value = null;
    schemeForm.reset();
    isAddingScheme.value = true;
};

const editScheme = (scheme: Scheme) => {
    isEditing.value = true;
    selectedSchemeId.value = scheme.id;
    schemeForm.subject_id = scheme.subject?.id ? String(scheme.subject.id) : '';
    schemeForm.grade_level_id = scheme.grade_level?.id ? String(scheme.grade_level.id) : '';
    schemeForm.academic_term_id = scheme.academic_term?.id ? String(scheme.academic_term.id) : '';
    schemeForm.title = scheme.title;
    schemeForm.description = scheme.description || '';
    schemeForm.total_weeks = scheme.total_weeks;
    schemeForm.lessons_per_week = scheme.lessons_per_week;
    isAddingScheme.value = true;
};

const submitScheme = () => {
    if (isEditing.value && selectedSchemeId.value) {
        schemeForm.put(`/curriculum/planner/schemes/${selectedSchemeId.value}`, {
            onSuccess: () => {
                isAddingScheme.value = false;
                schemeForm.reset();
            }
        });
    } else {
        schemeForm.post('/curriculum/planner/schemes', {
            onSuccess: () => {
                isAddingScheme.value = false;
                schemeForm.reset();
            }
        });
    }
};

const deleteScheme = (id: number) => {
    if (confirm('Are you sure you want to delete this scheme?')) {
        router.delete(`/curriculum/planner/schemes/${id}`);
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'rejected': return 'bg-rose-50 text-rose-600 border-rose-100';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

const updateStatus = (id: number, action: string) => {
    router.post(`/curriculum/planner/schemes/${id}/${action}`);
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved': return CheckCircle2;
        case 'submitted': return Clock;
        case 'rejected': return AlertCircle;
        default: return FileText;
    }
};
</script>

<template>
    <Head title="Schemes of Work" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Notifications -->
        <div v-if="showToast && (flashSuccess || flashError)" class="fixed top-8 right-8 z-[100] animate-in slide-in-from-right-4 duration-300">
            <div :class="['flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border backdrop-blur-xl', flashSuccess ? 'bg-emerald-50/90 border-emerald-200 text-emerald-800' : 'bg-rose-50/90 border-rose-200 text-rose-800']">
                <div :class="['h-8 w-8 rounded-xl flex items-center justify-center', flashSuccess ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white']">
                    <CheckCircle2 v-if="flashSuccess" class="h-5 w-5" />
                    <Sparkles v-else class="h-5 w-5" />
                </div>
                <div class="flex flex-col">
                    <p class="text-[13px] font-bold tracking-tight">{{ flashSuccess || flashError }}</p>
                    <p class="text-[10px] opacity-70 font-medium">Operation completed successfully</p>
                </div>
            </div>
        </div>

        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Schemes of Work</h1>
                    <p class="text-[15px] text-muted-foreground">
                        Manage academic instructional schedules and lesson sequences for the current term.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-medium hover:bg-muted" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4 opacity-70" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Scheme
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Schemes</p>
                        <h3 class="text-2xl font-bold text-foreground tracking-tight">{{ filteredSchemes.length }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-500">
                        <FileText class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Pending Review</p>
                        <h3 class="text-2xl font-bold text-foreground tracking-tight">{{ filteredSchemes.filter(s => s.status === 'submitted').length }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform duration-500">
                        <Clock class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Approved</p>
                        <h3 class="text-2xl font-bold text-foreground tracking-tight">{{ filteredSchemes.filter(s => s.status === 'approved').length }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-500">
                        <CheckCircle2 class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Avg Schedule</p>
                        <h3 class="text-2xl font-bold text-foreground tracking-tight">5 L/Wk</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-500">
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Schemes Table -->
            <div class="rounded-[2rem] border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden transition-all duration-500">
                <!-- Toolbar -->
                <div class="p-6 border-b border-border/50 space-y-4 bg-muted/5">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative w-full md:max-w-md group">
                            <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-blue-600" />
                            <Input v-model="searchQuery" placeholder="Search schemes by title or subject..." class="pl-10 h-11 bg-background border-border/60 rounded-xl focus:ring-4 focus:ring-blue-600/5 transition-all text-sm shadow-sm" />
                        </div>
                        
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-11 px-5 rounded-xl border-border font-bold text-[10px] uppercase tracking-widest bg-background hover:bg-muted/50 shadow-sm">
                                        <ListFilter class="mr-2 h-4 w-4 opacity-70" /> Batch Actions <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-border shadow-2xl">
                                    <DropdownMenuItem class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider"><FileText class="mr-3 h-4 w-4 text-blue-500" /> Export Selected</DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1 bg-border/20" />
                                    <DropdownMenuItem class="text-rose-500 rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider"><Trash2 class="mr-3 h-4 w-4" /> Bulk Delete</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div v-if="showFilters" class="grid gap-4 pt-2 md:grid-cols-3 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="space-y-1.5">
                             <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Subject</Label>
                             <select v-model="selectedSubject" class="h-11 w-full rounded-xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">All Subjects</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                             </select>
                        </div>
                        <div class="space-y-1.5">
                             <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Grade Level</Label>
                             <select v-model="selectedGrade" class="h-11 w-full rounded-xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">All Grades</option>
                                <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                             </select>
                        </div>
                        <div class="space-y-1.5">
                             <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground ml-1">Academic Term</Label>
                             <select v-model="selectedTerm" class="h-11 w-full rounded-xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">All Terms</option>
                                <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.name }}</option>
                             </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/5 text-muted-foreground border-b border-border/40">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest">Scheme Details</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest">Placement & Term</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-center">Schedule</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/20 uppercase">
                            <tr 
                                v-for="scheme in filteredSchemes" 
                                :key="scheme.id" 
                                class="hover:bg-muted/10 transition-all group cursor-pointer"
                                @click="router.visit(`/curriculum/planner/schemes/${scheme.id}`)"
                            >
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-11 w-11 flex-shrink-0 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center transition-all group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/30">
                                            <FileText class="h-5.5 w-5.5" />
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-sm font-bold text-foreground tracking-tight group-hover:text-blue-600 transition-colors uppercase leading-none">{{ scheme.title }}</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <Badge variant="outline" class="text-[8px] font-bold bg-muted/50 rounded-md py-0 px-2 uppercase tracking-widest border-border/50">{{ scheme.subject?.name }}</Badge>
                                                <span class="text-[9px] text-muted-foreground font-medium italic opacity-60 normal-case">By {{ scheme.prepared_by_user?.name || 'Academic Dept' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-[12px] font-bold text-foreground leading-none">
                                            <GraduationCap class="h-3.5 w-3.5 text-blue-500/70" />
                                            {{ scheme.grade_level?.name }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[9px] font-bold text-muted-foreground uppercase tracking-widest opacity-60 ml-0.5 leading-none mt-1">
                                            <Calendar class="h-3 w-3" />
                                            {{ scheme.academic_term?.name || 'Yearly' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[13px] font-bold text-foreground tabular-nums leading-none">{{ scheme.total_weeks }} Wks</span>
                                        <span class="text-[9px] text-muted-foreground font-bold tracking-tight uppercase opacity-60 leading-none mt-1.5">{{ scheme.lessons_per_week }} L/Wk</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                     <div class="flex justify-center">
                                        <Badge variant="outline" :class="[getStatusVariant(scheme.status), 'rounded-lg px-3 py-1 text-[9px] font-bold uppercase tracking-widest border transition-all group-hover:scale-105 flex items-center gap-1.5']">
                                             <component :is="getStatusIcon(scheme.status)" class="h-3.5 w-3.5" />
                                             {{ scheme.status }}
                                         </Badge>
                                     </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" @click.stop="router.visit(`/curriculum/planner/schemes/${scheme.id}`)" class="h-9 w-9 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all text-muted-foreground">
                                            <Eye class="h-4.5 w-4.5" />
                                        </Button>
                                        
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child @click.stop>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted font-black transition-all"><MoreHorizontal class="h-4.5 w-4.5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-border shadow-2xl">
                                                <DropdownMenuItem @click="router.visit(`/curriculum/planner/schemes/${scheme.id}`)" class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider group">
                                                    <FileCheck class="mr-3 h-4 w-4 opacity-60 group-hover:text-blue-600" /> View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click.stop="editScheme(scheme)" class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider group">
                                                    <Edit class="mr-3 h-4 w-4 opacity-60 group-hover:text-amber-600" /> Edit Parameters
                                                </DropdownMenuItem>

                                                <template v-if="scheme.status === 'draft'">
                                                    <DropdownMenuSeparator class="my-1 bg-border/5" />
                                                    <DropdownMenuItem @click.stop="updateStatus(scheme.id, 'submit')" class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50/50">
                                                        <Sparkles class="mr-3 h-4 w-4" /> Submit for Review
                                                    </DropdownMenuItem>
                                                </template>

                                                <template v-if="scheme.status === 'submitted'">
                                                    <DropdownMenuSeparator class="my-1 bg-border/5" />
                                                    <DropdownMenuItem @click.stop="updateStatus(scheme.id, 'approve')" class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50/50">
                                                        <CheckCircle2 class="mr-3 h-4 w-4" /> Approve Scheme
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click.stop="updateStatus(scheme.id, 'reject')" class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50/50">
                                                        <Clock class="mr-3 h-4 w-4" /> Return to Draft
                                                    </DropdownMenuItem>
                                                </template>

                                                <DropdownMenuSeparator class="my-1 bg-border/5" />
                                                <DropdownMenuItem class="text-rose-600 rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider hover:bg-rose-50 transition-colors" @click.stop="deleteScheme(scheme.id)">
                                                    <Trash2 class="mr-3 h-4 w-4" /> Delete Scheme
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredSchemes.length === 0">
                                <td colspan="5" class="px-8 py-32 text-center text-muted-foreground bg-muted/5">
                                    <div class="flex flex-col items-center gap-4 max-w-sm mx-auto">
                                        <div class="h-20 w-20 rounded-3xl bg-muted/50 flex items-center justify-center text-muted-foreground/30 border border-border/50 shadow-inner">
                                            <FileText class="h-10 w-10" />
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-lg font-bold text-foreground">No Schemes Found</h3>
                                            <p class="text-xs font-medium italic">We couldn't find any schemes of work matching your criteria.</p>
                                        </div>
                                        <Button @click="isAddingScheme = true" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl h-10 px-6 font-bold uppercase tracking-widest shadow-sm transition-all mt-4">
                                            <Plus class="mr-2 h-4 w-4" /> Create Scheme
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- New Scheme Modal -->
        <Dialog v-model:open="isAddingScheme">
            <DialogContent class="sm:max-w-[600px] rounded-[2rem] border-border bg-card shadow-2xl p-0 overflow-hidden">
                <DialogHeader class="p-8 pb-6 border-b border-border/10 bg-muted/5">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                            <Plus class="h-6 w-6" />
                        </div>
                        <div>
                            <DialogTitle class="text-xl font-bold tracking-tight text-foreground">{{ isEditing ? 'Edit Scheme of Work' : 'New Scheme of Work' }}</DialogTitle>
                            <p class="text-[11px] text-muted-foreground font-medium mt-0.5">
                                Initialize a new instructional sequence for your classes.
                            </p>
                        </div>
                    </div>
                </DialogHeader>

                <form @submit.prevent="submitScheme" class="p-8 space-y-6">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Subject</Label>
                                <select v-model="schemeForm.subject_id" class="w-full h-11 px-4 rounded-xl border border-border/60 bg-muted/20 text-xs font-bold uppercase tracking-wider outline-none focus:ring-4 focus:ring-blue-600/5 focus:bg-background transition-all appearance-none cursor-pointer" required>
                                    <option value="" disabled>Select Subject</option>
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Grade Level</Label>
                                <select v-model="schemeForm.grade_level_id" class="w-full h-11 px-4 rounded-xl border border-border/60 bg-muted/20 text-xs font-bold uppercase tracking-wider outline-none focus:ring-4 focus:ring-blue-600/5 focus:bg-background transition-all appearance-none cursor-pointer" required>
                                    <option value="" disabled>Select Grade</option>
                                    <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Academic Term</Label>
                                <select v-model="schemeForm.academic_term_id" class="w-full h-11 px-4 rounded-xl border border-border/60 bg-muted/20 text-xs font-bold uppercase tracking-wider outline-none focus:ring-4 focus:ring-blue-600/5 focus:bg-background transition-all appearance-none cursor-pointer" required>
                                    <option value="" disabled>Select Term</option>
                                    <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Scheme Title</Label>
                                <Input v-model="schemeForm.title" placeholder="e.g. Mathematics - Term 1" class="rounded-xl border-border/60 h-11 bg-muted/20 focus:bg-background transition-all font-bold text-sm px-4 shadow-sm" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Total Weeks</Label>
                                <Input v-model="schemeForm.total_weeks" type="number" class="rounded-xl border-border/60 h-11 bg-muted/20 focus:bg-background transition-all font-bold text-sm px-4" required />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Lessons per Week</Label>
                                <Input v-model="schemeForm.lessons_per_week" type="number" class="rounded-xl border-border/60 h-11 bg-muted/20 focus:bg-background transition-all font-bold text-sm px-4" required />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Description (Optional)</Label>
                            <Textarea v-model="schemeForm.description" placeholder="Outcome objectives and overview..." class="rounded-xl border-border/60 bg-muted/20 focus:bg-background transition-all font-medium text-sm p-4 min-h-[100px] leading-relaxed" />
                        </div>
                    </div>

                    <DialogFooter class="pt-6 border-t border-border/10 flex justify-between sm:justify-between items-center bg-muted/5 -m-8 p-8 mt-4">
                        <Button type="button" variant="ghost" @click="isAddingScheme = false" class="h-11 px-6 rounded-xl font-bold text-[11px] uppercase tracking-widest text-muted-foreground hover:text-foreground transition-all">Cancel</Button>
                        <Button type="submit" :disabled="schemeForm.processing" class="h-11 px-8 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 font-bold text-[11px] uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                            <Sparkles class="h-3.5 w-3.5" /> 
                            {{ schemeForm.processing ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Scheme' : 'Create Scheme') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
