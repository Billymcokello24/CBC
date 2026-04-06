<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
    Sparkles,
    LayoutGrid,
    ListFilter
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
    status: 'draft' | 'pending' | 'approved';
    total_weeks: number;
    lessons_per_week: number;
    subject?: { name: string; id: number };
    grade_level?: { name: string; short_name: string; id: number };
    academic_term?: { name: string; id: number };
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

const breadcrumbs = [
    { title: 'Curriculum', href: '#' },
    { title: 'Planner', href: '#' },
    { title: 'Schemes of Work', href: '/curriculum/planner/schemes' },
];

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
const schemeForm = useForm({
    subject_id: '',
    grade_level_id: '',
    academic_term_id: '',
    title: '',
    description: '',
    total_weeks: 13,
    lessons_per_week: 5,
});

const submitScheme = () => {
    schemeForm.post('/curriculum/planner/schemes', {
        onSuccess: () => {
            isAddingScheme.value = false;
            schemeForm.reset();
        }
    });
};

const deleteScheme = (id: number) => {
    if (confirm('Are you sure you want to delete this scheme?')) {
        router.delete(`/curriculum/planner/schemes/${id}`);
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'pending': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved': return CheckCircle2;
        case 'pending': return Clock;
        default: return FileText;
    }
};
</script>

<template>
    <Head title="Schemes of Work" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1.5">
                    <h1 class="text-3xl font-black tracking-tight text-foreground flex items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                            <LayoutGrid class="h-6 w-6" />
                        </div>
                        Curriculum Planner
                    </h1>
                    <p class="text-[15px] text-muted-foreground font-medium italic">
                        Master instructional scheduling and matrix development for the current academic cycle.
                    </p>
                </div>
                
                <div class="flex items-center gap-3 bg-muted/20 p-1.5 rounded-[2rem] border border-border/40 backdrop-blur-sm">
                    <Button variant="ghost" class="h-10 px-5 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-background transition-all" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4 opacity-70" />
                        {{ showFilters ? 'Hide Engine' : 'Filter Logic' }}
                    </Button>
                    <Button
                        @click="isAddingScheme = true"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 h-10 text-xs font-black uppercase tracking-[0.15em] text-white shadow-xl shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        New Scheme
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats Preview -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div v-for="(stat, idx) in [
                    { label: 'Total active', count: filteredSchemes.length, icon: LayoutGrid, color: 'text-blue-600', bg: 'bg-blue-50' },
                    { label: 'Pending review', count: filteredSchemes.filter(s => s.status === 'pending').length, icon: Clock, color: 'text-amber-600', bg: 'bg-amber-50' },
                    { label: 'Operational', count: filteredSchemes.filter(s => s.status === 'approved').length, icon: CheckCircle2, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                    { label: 'Avg Intensity', count: '5 L/Wk', icon: Sparkles, color: 'text-indigo-600', bg: 'bg-indigo-50' }
                ]" :key="idx" class="bg-card/40 border border-border/40 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div :class="['h-12 w-12 rounded-2xl flex items-center justify-center transition-all group-hover:scale-110 duration-500', stat.bg]">
                            <component :is="stat.icon" :class="['h-6 w-6', stat.color]" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 leading-none mb-1.5">{{ stat.label }}</p>
                            <h3 class="text-2xl font-black text-foreground tracking-tight leading-none">{{ stat.count }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="rounded-[2.5rem] border border-border bg-card shadow-2xl dark:border-white/5 overflow-hidden transition-all duration-500">
                <!-- Toolbar -->
                <div class="p-8 border-b border-border/50 space-y-6 bg-muted/5">
                    <div class="flex flex-col md:flex-row gap-6 items-center justify-between">
                        <div class="relative w-full md:max-w-xl group">
                            <Search class="absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-blue-600" />
                            <Input v-model="searchQuery" placeholder="Search operational matrices by title or subject..." class="pl-11 h-12 bg-background border-border/60 rounded-2xl focus:ring-4 focus:ring-blue-600/5 transition-all text-sm font-medium shadow-sm" />
                        </div>
                        
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-12 px-6 rounded-2xl border-border font-bold text-[10px] uppercase tracking-widest bg-background hover:bg-muted/50 shadow-sm">
                                        <ListFilter class="mr-2.5 h-4 w-4 opacity-70" /> Batch Actions <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 p-2 rounded-2xl border border-border shadow-2xl">
                                    <DropdownMenuItem class="rounded-xl px-3 py-2 text-xs font-bold uppercase tracking-wider"><FileText class="mr-3 h-4 w-4 text-blue-500" /> Export Selected</DropdownMenuItem>
                                    <DropdownMenuSeparator class="my-1.5 bg-border/20" />
                                    <DropdownMenuItem class="text-rose-500 rounded-xl px-3 py-2 text-xs font-bold uppercase tracking-wider"><Trash2 class="mr-3 h-4 w-4" /> Bulk Archive</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Filters Engine -->
                    <div v-if="showFilters" class="grid gap-4 pt-2 md:grid-cols-3 animate-in slide-in-from-top-4 duration-300">
                        <div class="space-y-2">
                             <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Subject Discipline</Label>
                             <select v-model="selectedSubject" class="h-12 w-full rounded-2xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">Global Disciplines</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Grade Context</Label>
                             <select v-model="selectedGrade" class="h-12 w-full rounded-2xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">All Placement Levels</option>
                                <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Academic Term</Label>
                             <select v-model="selectedTerm" class="h-12 w-full rounded-2xl border-border/60 bg-background px-4 text-xs font-bold uppercase tracking-wider focus:ring-4 focus:ring-blue-600/5 outline-none appearance-none cursor-pointer hover:border-blue-500/30 transition-all shadow-sm">
                                <option value="all">Full Academic Cycle</option>
                                <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.name }}</option>
                             </select>
                        </div>
                    </div>
                </div>

                <!-- Main Matrix Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/10 text-muted-foreground border-b border-border/40">
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Matrix Title</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Context / Term</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Intensity</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/20">
                            <tr 
                                v-for="scheme in filteredSchemes" 
                                :key="scheme.id" 
                                class="hover:bg-muted/20 transition-all group cursor-pointer relative"
                                @click="router.visit(`/curriculum/planner/schemes/${scheme.id}`)"
                            >
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="h-12 w-12 flex-shrink-0 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center transition-all group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/30">
                                            <BookOpen class="h-6 w-6" />
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[15px] font-black text-foreground tracking-tight group-hover:text-blue-600 transition-colors">{{ scheme.title }}</span>
                                            <div class="flex items-center gap-2">
                                                <Badge variant="outline" class="text-[8px] font-black bg-muted/50 rounded-md py-0 px-2 uppercase tracking-widest border-border/50">{{ scheme.subject?.name }}</Badge>
                                                <span class="text-[10px] text-muted-foreground font-bold italic opacity-60">Prepared by {{ scheme.prepared_by_user?.name || 'Academic Dept' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-[13px] font-bold text-foreground">
                                            <GraduationCap class="h-3.5 w-3.5 text-blue-500/70" />
                                            {{ scheme.grade_level?.name }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase tracking-widest opacity-60 ml-0.5">
                                            <Calendar class="h-3 w-3" />
                                            {{ scheme.academic_term?.name || 'TBD' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[14px] font-black text-foreground tabular-nums">{{ scheme.total_weeks }} Weeks</span>
                                            <span class="text-[10px] text-muted-foreground font-bold tracking-tight uppercase opacity-60">{{ scheme.lessons_per_week }} Lessons/Wk</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                     <Badge variant="outline" :class="[getStatusVariant(scheme.status), 'rounded-xl px-4 py-1.5 text-[9px] font-black uppercase tracking-[0.2em] border-2 shadow-sm flex items-center gap-2 w-fit transform transition-all group-hover:scale-105']">
                                         <component :is="getStatusIcon(scheme.status)" class="h-3.5 w-3.5" />
                                         {{ scheme.status }}
                                     </Badge>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                                        <Link :href="`/curriculum/planner/schemes/${scheme.id}`" class="h-10 w-10 flex items-center justify-center rounded-2xl bg-slate-900 text-white hover:bg-black transition-all shadow-lg active:scale-95"><ChevronRight class="h-5 w-5" /></Link>
                                        
                                        <DropdownMenu @click.stop>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-10 w-10 rounded-2xl hover:bg-muted font-black border border-border/20 transition-all"><MoreHorizontal class="h-5 w-5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-64 p-2 rounded-2xl border border-border shadow-2xl backdrop-blur-xl bg-card/95">
                                                <div class="px-3 py-2.5 mb-1 border-b border-border/10">
                                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">Operations Suite</p>
                                                </div>
                                                <DropdownMenuItem as-child class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider group">
                                                    <Link :href="`/curriculum/planner/schemes/${scheme.id}`">
                                                        <FileText class="mr-3 h-4 w-4 opacity-60 transition-colors group-hover:text-blue-600" /> Instructional Matrix
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider group">
                                                    <Edit class="mr-3 h-4 w-4 opacity-60 transition-colors group-hover:text-amber-600" /> Adjust Parameters
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1.5 bg-border/5" />
                                                <DropdownMenuItem class="text-rose-600 rounded-xl px-3 py-2.5 font-bold text-[11px] uppercase tracking-wider hover:bg-rose-600 hover:text-white transition-colors" @click="deleteScheme(scheme.id)">
                                                    <Trash2 class="mr-3 h-4 w-4" /> Purge Matrix
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredSchemes.length === 0">
                                <td colspan="5" class="px-8 py-32 text-center text-muted-foreground bg-muted/5">
                                    <div class="flex flex-col items-center gap-6 max-w-sm mx-auto">
                                        <div class="h-24 w-24 rounded-[2.5rem] bg-muted flex items-center justify-center text-muted-foreground/30 border border-border/50 shadow-inner">
                                            <LayoutGrid class="h-12 w-12" />
                                        </div>
                                        <div class="space-y-2">
                                            <h3 class="text-xl font-black text-foreground tracking-tight">Zero Matrices Found</h3>
                                            <p class="text-sm font-medium leading-relaxed italic">No schemes of work match your current selection engine. Adjust filters or initialize a new sequence.</p>
                                        </div>
                                        <Button @click="isAddingScheme = true" class="bg-blue-600 hover:bg-blue-700 text-white rounded-2xl h-12 px-8 font-black uppercase tracking-[0.15em] shadow-xl shadow-blue-600/20 active:scale-95 transition-all mt-4">
                                            <Plus class="mr-2 h-4 w-4" /> Create Matrix
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Initialize New Matrix Modal -->
        <Dialog v-model:open="isAddingScheme">
            <DialogContent class="sm:max-w-[650px] rounded-[2.5rem] border-border bg-card shadow-2xl p-0 overflow-hidden">
                <DialogHeader class="p-10 pb-6 bg-muted/5 border-b border-border/30 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-600"></div>
                    <div class="flex items-center gap-6 relative z-10">
                        <div class="h-16 w-16 rounded-[2rem] bg-blue-600 flex items-center justify-center text-white shadow-2xl shadow-blue-600/30">
                            <Plus class="h-8 w-8" />
                        </div>
                        <div>
                            <DialogTitle class="text-3xl font-black tracking-tight text-foreground">Initialize Matrix</DialogTitle>
                            <p class="text-[10px] text-muted-foreground font-black uppercase tracking-[0.2em] mt-1 opacity-70">
                                Strategic Instructional Provisioning
                            </p>
                        </div>
                    </div>
                </DialogHeader>

                <form @submit.prevent="submitScheme" class="p-10 space-y-8">
                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Subject Discipline</Label>
                            <select v-model="schemeForm.subject_id" class="w-full h-12 px-5 rounded-2xl border border-border/60 bg-muted/20 text-xs font-bold uppercase tracking-wider outline-none focus:ring-4 focus:ring-blue-600/5 focus:bg-background transition-all" required>
                                <option value="" disabled>Select Subject</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Grade Level</Label>
                            <select v-model="schemeForm.grade_level_id" class="w-full h-12 px-5 rounded-2xl border border-border/60 bg-muted/20 text-xs font-bold uppercase tracking-wider outline-none focus:ring-4 focus:ring-blue-600/5 focus:bg-background transition-all" required>
                                <option value="" disabled>Select Level</option>
                                <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Academic Term</Label>
                            <select v-model="schemeForm.academic_term_id" class="w-full h-12 px-5 rounded-2xl border border-border/60 bg-muted/20 text-xs font-bold uppercase tracking-wider outline-none focus:ring-4 focus:ring-blue-600/5 focus:bg-background transition-all" required>
                                <option value="" disabled>Select Term</option>
                                <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Matrix Title</Label>
                            <Input v-model="schemeForm.title" placeholder="e.g. Mathematics Term 1 2024" class="rounded-2xl border-border/60 h-12 bg-muted/20 focus:bg-background transition-all font-bold text-sm px-5 shadow-inner" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Total Sequence (Weeks)</Label>
                            <Input v-model="schemeForm.total_weeks" type="number" class="rounded-2xl border-border/60 h-12 bg-muted/20 focus:bg-background transition-all font-bold text-sm px-5" required />
                        </div>
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Intensity (Lessons/Week)</Label>
                            <Input v-model="schemeForm.lessons_per_week" type="number" class="rounded-2xl border-border/60 h-12 bg-muted/20 focus:bg-background transition-all font-bold text-sm px-5" required />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground ml-3">Strategic Description (Optional)</Label>
                        <Textarea v-model="schemeForm.description" placeholder="Outcome objectives and strategic overview..." class="rounded-2xl border-border/60 bg-muted/20 focus:bg-background transition-all font-medium text-sm p-5 min-h-[100px] leading-relaxed" />
                    </div>

                    <DialogFooter class="pt-6 border-t border-border/30 flex justify-between sm:justify-between items-center bg-muted/5 -m-10 p-10 mt-6">
                        <Button type="button" variant="ghost" @click="isAddingScheme = false" class="h-12 px-8 rounded-2xl font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-foreground transition-all">Cancel Request</Button>
                        <Button type="submit" :disabled="schemeForm.processing" class="h-12 px-10 rounded-2xl bg-slate-900 hover:bg-black text-white shadow-2xl shadow-slate-900/10 font-black text-[10px] uppercase tracking-[0.2em] transition-all active:scale-95 flex items-center gap-2">
                            <Sparkles class="h-3.5 w-3.5" /> 
                            {{ schemeForm.processing ? 'Initializing...' : 'Commit Matrix' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
/* Ensure clean styles */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1.25rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 3rem;
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
