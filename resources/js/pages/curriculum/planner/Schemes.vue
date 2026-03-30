<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Plus, Search, Filter, 
    MoreHorizontal, Eye, Edit2, Trash2, 
    Calendar, User, BookOpen, CheckCircle2, 
    Clock, AlertCircle, FileText, Download, ChevronRight, X, GraduationCap,
    TrendingUp, Users, ShieldAlert, CheckSquare, Square
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    schemes: any[];
    subjects: any[];
    grades: any[];
    terms: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
];

// Filtering State
const searchQuery = ref('');
const showFilters = ref(true);
const selectedSubjectId = ref('all');
const selectedGradeId = ref('all');
const selectedStatus = ref('all');

const filteredSchemes = computed(() => {
    return props.schemes.filter(scheme => {
        const matchesSearch = scheme.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            scheme.subject?.name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = selectedSubjectId.value === 'all' || scheme.subject_id == selectedSubjectId.value;
        const matchesGrade = selectedGradeId.value === 'all' || scheme.grade_level_id == selectedGradeId.value;
        const matchesStatus = selectedStatus.value === 'all' || scheme.status === selectedStatus.value;
        
        return matchesSearch && matchesSubject && matchesGrade && matchesStatus;
    });
});

const stats = computed(() => ({
    total: props.schemes.length,
    approved: props.schemes.filter(s => s.status === 'approved').length,
    pending: props.schemes.filter(s => s.status === 'pending').length,
    drafts: props.schemes.filter(s => s.status === 'draft').length,
}));

// Modals State
const showModal = ref(false);
const editingScheme = ref<any>(null);

const form = useForm({
    title: '',
    description: '',
    subject_id: '',
    grade_level_id: '',
    academic_term_id: '',
    total_weeks: 13,
    lessons_per_week: 5,
});

// Feedback Modal State
const showFeedback = ref(false);
const feedbackType = ref<'success' | 'error' | 'info'>('success');
const feedbackMessage = ref('');

const openFeedback = (type: 'success' | 'error' | 'info', message: string) => {
    feedbackType.value = type;
    feedbackMessage.value = message;
    showFeedback.value = true;
};

const openModal = (scheme: any = null) => {
    editingScheme.value = scheme;
    if (scheme) {
        form.title = scheme.title;
        form.description = scheme.description;
        form.subject_id = scheme.subject_id;
        form.grade_level_id = scheme.grade_level_id;
        form.academic_term_id = scheme.academic_term_id;
        form.total_weeks = scheme.total_weeks;
        form.lessons_per_week = scheme.lessons_per_week;
    } else {
        form.reset();
        if (props.terms.length > 0) form.academic_term_id = props.terms[0].id;
    }
    showModal.value = true;
};

const submit = () => {
    if (editingScheme.value) {
        form.put(route('curriculum.planner.schemes.update', editingScheme.value.id), {
            onSuccess: () => {
                showModal.value = false;
                editingScheme.value = null;
            },
        });
    } else {
        form.post(route('curriculum.planner.schemes.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const submitForReview = (scheme: any) => {
    useForm({}).post(route('curriculum.planner.schemes.submit', scheme.id), {
        onSuccess: () => openFeedback('success', 'Scheme of work submitted for review.')
    });
};

const approveScheme = (scheme: any) => {
    useForm({}).post(route('curriculum.planner.schemes.approve', scheme.id), {
        onSuccess: () => openFeedback('success', 'Scheme of work approved successfully.')
    });
};

const rejectScheme = (scheme: any) => {
    const feedback = prompt('Provide feedback for revision (optional):');
    if (feedback !== null) {
        useForm({ feedback }).post(route('curriculum.planner.schemes.reject', scheme.id), {
            onSuccess: () => openFeedback('info', 'Scheme returned for revision.')
        });
    }
};

const deleteScheme = (scheme: any) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        useForm({}).delete(route('curriculum.planner.schemes.destroy', scheme.id), {
            onSuccess: () => openFeedback('success', 'Scheme removed successfully.')
        });
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'pending': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'draft': return 'bg-slate-50 text-slate-600 border-slate-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};
</script>

<template>
    <Head title="Scheme of Work Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Scheme of Work</h1>
                    <p class="text-[15px] text-muted-foreground">
                        Plan your termly teaching schedule and track approval status.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-medium hover:bg-muted">
                        <Download class="mr-2 h-4 w-4 opacity-70" />
                        Export
                    </Button>
                    <Button
                        @click="openModal()"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Create Plan
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Plans</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.total.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <BookOpen class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Approved</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.approved.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                         <CheckCircle2 class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Waiting Review</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.pending.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <Clock class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Drafts</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.drafts.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600">
                        <FileText class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden">
                <!-- Toolbar -->
                <div class="p-6 border-b border-border/50 space-y-4">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative w-full md:max-w-md">
                            <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search by plan title or subject..." class="pl-10 h-11 bg-muted/50 border-border rounded-xl focus:bg-background transition-all" />
                        </div>
                        
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <Button variant="outline" class="h-11 border-border font-semibold px-4 rounded-xl whitespace-nowrap bg-background" @click="showFilters = !showFilters">
                                <Filter class="mr-2 h-4 w-4 opacity-70" /> {{ showFilters ? 'Hide Filters' : 'More Filters' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Filters Drawer -->
                    <div v-if="showFilters" class="grid gap-4 pt-4 md:grid-cols-2 lg:grid-cols-4 animate-in slide-in-from-top-2 duration-200">
                        <select v-model="selectedStatus" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 outline-none appearance-none cursor-pointer hover:bg-muted/50 transition-all border">
                            <option value="all">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                        <select v-model="selectedSubjectId" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 outline-none appearance-none cursor-pointer hover:bg-muted/50 transition-all border">
                            <option value="all">All Subjects</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="String(subject.id)">{{ subject.name }}</option>
                        </select>
                        <select v-model="selectedGradeId" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 outline-none appearance-none cursor-pointer hover:bg-muted/50 transition-all border">
                            <option value="all">All Grades</option>
                            <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-muted/30 text-muted-foreground border-b border-border/50">
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Plan Profile</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Term / Schedule</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Facilitator</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr 
                                v-for="scheme in filteredSchemes" 
                                :key="scheme.id" 
                                class="hover:bg-muted/30 transition-colors group cursor-pointer"
                                @click="router.visit(`/curriculum/planner/schemes/${scheme.id}`)"
                            >
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center font-bold text-blue-600 text-xs shadow-sm group-hover:border-blue-400 transition-all">
                                             {{ scheme.subject?.name?.[0] }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-foreground">{{ scheme.title }}</span>
                                            <span class="text-xs text-muted-foreground font-medium uppercase tracking-wider">{{ scheme.subject?.name }} • {{ scheme.grade_level?.name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex flex-col">
                                        <span class="text-foreground">{{ scheme.academic_term?.name || 'Current Term' }}</span>
                                        <span class="text-[10px] text-muted-foreground uppercase font-semibold">{{ scheme.total_weeks }} Weeks • {{ scheme.lessons_per_week }} L/Wk</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground font-medium">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center">
                                            <User class="h-4 w-4 text-slate-400" />
                                        </div>
                                        <span>{{ scheme.prepared_by?.name || 'Assigned Teacher' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <Badge variant="outline" class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider" :class="getStatusColor(scheme.status)">
                                         {{ scheme.status }}
                                     </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Link :href="`/curriculum/planner/schemes/${scheme.id}`" class="h-9 w-9 flex items-center justify-center rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all text-muted-foreground border border-transparent hover:border-blue-100 shadow-none" @click.stop><Eye class="h-4.5 w-4.5" /></Link>
                                        <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-amber-50 hover:text-amber-600 border border-transparent hover:border-amber-100 transition-all" @click.stop="openModal(scheme)"><Edit2 class="h-4.5 w-4.5" /></Button>
                                        
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted transition-all" @click.stop><MoreHorizontal class="h-4.5 w-4.5" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 p-2 rounded-xl border border-border">
                                                <DropdownMenuItem as-child class="rounded-lg"><Link :href="`/curriculum/planner/schemes/${scheme.id}`"><Eye class="mr-2 h-4 w-4" /> View Details</Link></DropdownMenuItem>
                                                <DropdownMenuItem class="rounded-lg" @click="openModal(scheme)"><Edit2 class="mr-2 h-4 w-4" /> Edit Plan</DropdownMenuItem>
                                                
                                                <template v-if="scheme.status === 'draft'">
                                                    <DropdownMenuItem @click="submitForReview(scheme)" class="rounded-lg text-blue-600">
                                                        <CheckCircle2 class="mr-2 h-4 w-4" /> Submit to HOD
                                                    </DropdownMenuItem>
                                                </template>

                                                <template v-if="scheme.status === 'pending'">
                                                    <DropdownMenuItem @click="approveScheme(scheme)" class="rounded-lg text-emerald-600 font-bold">
                                                        <CheckCircle2 class="mr-2 h-4 w-4" /> Approve Plan
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="rejectScheme(scheme)" class="rounded-lg text-red-600 font-bold">
                                                        <AlertCircle class="mr-2 h-4 w-4" /> Reject Plan
                                                    </DropdownMenuItem>
                                                </template>
                                                
                                                <DropdownMenuSeparator class="my-1 bg-border/50" />
                                                <DropdownMenuItem class="text-rose-600 rounded-lg group" @click="deleteScheme(scheme)"><Trash2 class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100" /> Delete Plan</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredSchemes.length === 0">
                                <td colspan="5" class="px-6 py-24 text-center text-muted-foreground italic font-medium">
                                    No teaching plans found matching your criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-[500px] rounded-[2rem] border-border bg-background shadow-2xl p-0 overflow-hidden">
                <DialogHeader class="p-8 pb-4">
                    <DialogTitle class="text-2xl font-bold tracking-tight">{{ editingScheme ? 'Edit Scheme' : 'New Scheme of Work' }}</DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground italic">
                        {{ editingScheme ? 'Update teaching plan details.' : 'Define the framework for your termly teaching.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="grid gap-6 p-8 pt-4">
                    <div class="grid gap-2">
                        <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Plan Title</Label>
                        <Input v-model="form.title" placeholder="e.g. Mathematics - Term 1 Plan" class="rounded-xl border-border bg-muted/30 italic text-sm h-11 focus:bg-background transition-all" required />
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Subject</Label>
                            <select v-model="form.subject_id" class="w-full rounded-xl border-border bg-muted/30 px-3 h-11 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-600/20 transition-all outline-none border cursor-pointer hover:bg-muted/50" required>
                                <option value="" disabled>Select Subject</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Grade</Label>
                            <select v-model="form.grade_level_id" class="w-full rounded-xl border-border bg-muted/30 px-3 h-11 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-600/20 transition-all outline-none border cursor-pointer hover:bg-muted/50" required>
                                <option value="" disabled>Select Grade</option>
                                <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Academic Term</Label>
                        <select v-model="form.academic_term_id" class="w-full rounded-xl border-border bg-muted/30 px-3 h-11 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-600/20 transition-all outline-none border cursor-pointer hover:bg-muted/50" required>
                            <option value="" disabled>Select Term</option>
                            <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }} ({{ t.academic_year?.name }})</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Total Weeks</Label>
                            <Input type="number" v-model="form.total_weeks" class="rounded-xl border-border bg-muted/30 h-11 font-bold focus:bg-background transition-all" required />
                        </div>
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Lessons / Week</Label>
                            <Input type="number" v-model="form.lessons_per_week" class="rounded-xl border-border bg-muted/30 h-11 font-bold focus:bg-background transition-all" required />
                        </div>
                    </div>
                    
                    <div class="grid gap-2">
                        <Label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Notes (Optional)</Label>
                        <Textarea v-model="form.description" placeholder="Any additional context..." class="rounded-xl border-border bg-muted/30 italic text-sm min-h-[80px] focus:bg-background transition-all" />
                    </div>

                    <DialogFooter class="p-0">
                        <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-sm text-white h-12 shadow-md transition-all">
                            {{ form.processing ? 'Syncing...' : (editingScheme ? 'Update Plan' : 'Create Plan') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Dynamic Feedback Modal -->
        <Dialog v-model:open="showFeedback">
            <DialogContent class="sm:max-w-[400px] p-0 overflow-hidden rounded-[2.5rem] border-0 shadow-2xl">
                <div :class="[
                    'p-8 text-center space-y-6',
                    feedbackType === 'success' ? 'bg-emerald-50' : feedbackType === 'error' ? 'bg-rose-50' : 'bg-blue-50'
                ]">
                    <div :class="[
                        'mx-auto h-20 w-20 rounded-full flex items-center justify-center animate-in zoom-in duration-500',
                        feedbackType === 'success' ? 'bg-emerald-100 text-emerald-600' : feedbackType === 'error' ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-600'
                    ]">
                        <CheckCircle2 v-if="feedbackType === 'success'" class="h-10 w-10" />
                        <AlertCircle v-else-if="feedbackType === 'error'" class="h-10 w-10" />
                        <Info v-else class="h-10 w-10" />
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="text-xl font-black tracking-tight text-slate-900">
                            {{ feedbackType === 'success' ? 'Success!' : feedbackType === 'error' ? 'Oops!' : 'Note' }}
                        </h3>
                        <p class="text-sm font-medium text-slate-500 leading-relaxed">
                            {{ feedbackMessage }}
                        </p>
                    </div>

                    <Button 
                        @click="showFeedback = false" 
                        :class="[
                            'w-full h-12 rounded-2xl font-bold text-xs uppercase tracking-widest shadow-lg transition-all active:scale-[0.98]',
                            feedbackType === 'success' ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/20' : 
                            feedbackType === 'error' ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-500/20' : 
                            'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/20'
                        ]"
                    >
                        Continue
                    </Button>
                </div>
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
</style>
