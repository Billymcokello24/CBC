<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    CheckCircle2,
    Download,
    Edit,
    Eye,
    Filter,
    GraduationCap,
    MoreHorizontal,
    Plus,
    Search,
    ShieldAlert,
    Trash2,
    ChevronDown,
    ChevronUp,
    ChevronRight,
    ArrowUpCircle,
    FileText,
    Users,
    TrendingUp,
    CheckSquare,
    Square,
    UserPlus,
    Home,
    Upload,
    Activity,
    Database,
    Zap,
    Loader2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import BulkUploadModal from './partials/BulkUploadModal.vue';

interface LearnerRow {
    id: number;
    admission_number: string | null;
    name: string;
    gender: string;
    class: string | null;
    class_id: number | null;
    status: string;
    photo_url: string | null;
    age: number | null;
}

const { props: pageProps } = usePage<any>();
const permissions = computed(() => pageProps.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

interface PaginatedLearners {
    data: LearnerRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    learners: PaginatedLearners;
    stats: {
        total: number;
        active: number;
        withdrawn: number;
        new_this_month: number;
        growth: number;
    };
    filters: {
        search: string;
        status: string;
        class_id: number | null;
        gender: string;
        per_page: number;
        show_filters: boolean;
    };
    classes: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
    genderOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedClassId = ref<string>(
    props.filters.class_id ? String(props.filters.class_id) : '',
);
const selectedGender = ref(props.filters.gender ?? 'all');
const showFilters = ref(props.filters.show_filters ?? true);
const perPage = ref(props.filters.per_page ?? 15);

const actionForm = useForm({});
const promotionForm = useForm<{ learner_ids: number[] }>({ learner_ids: [] });
const selectedLearnerIds = ref<number[]>([]);
const confirmOpen = ref(false);
const promoteOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete'>('suspend');
const selectedLearner = ref<LearnerRow | null>(null);
const bulkUploadOpen = ref(false);
const promotionTargetClass = ref('');

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (page = 1) => {
    router.get(
        '/students',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            class_id: selectedClassId.value || undefined,
            gender: selectedGender.value !== 'all' ? selectedGender.value : undefined,
            show_filters: showFilters.value,
            per_page: perPage.value,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch(
    [selectedStatus, selectedClassId, selectedGender, perPage],
    () => applyFilters(),
);

const activeRate = computed(() => {
    if (!props.stats.total) return 0;
    return Math.round((props.stats.active / props.stats.total) * 100);
});

const toggleSelection = (id: number) => {
    const index = selectedLearnerIds.value.indexOf(id);
    if (index === -1) {
        selectedLearnerIds.value.push(id);
    } else {
        selectedLearnerIds.value.splice(index, 1);
    }
};

const toggleAllSelection = () => {
    if (selectedLearnerIds.value.length === props.learners.data.length) {
        selectedLearnerIds.value = [];
    } else {
        selectedLearnerIds.value = props.learners.data.map((l) => l.id);
    }
};

const openConfirm = (mode: 'suspend' | 'activate' | 'delete', learner: LearnerRow) => {
    confirmMode.value = mode;
    selectedLearner.value = learner;
    confirmOpen.value = true;
};

const handleAction = () => {
    if (!selectedLearner.value) return;
    
    const url = confirmMode.value === 'delete' 
        ? `/students/${selectedLearner.value.id}`
        : `/students/${selectedLearner.value.id}/${confirmMode.value}`;
        
    const method = confirmMode.value === 'delete' ? 'delete' : 'post';
    
    actionForm[method](url, {
        onSuccess: () => {
            confirmOpen.value = false;
            router.reload();
        },
    });
};

const handleBulkPromotion = () => {
    promotionForm.learner_ids = selectedLearnerIds.value;
    promotionForm.post(`/students/bulk-promote/${promotionTargetClass.value}`, {
        onSuccess: () => {
            promoteOpen.value = false;
            selectedLearnerIds.value = [];
            router.reload();
        },
    });
};

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white shadow-sm';
        case 'suspended':
            return 'bg-rose-500 text-white shadow-sm';
        case 'inactive':
        case 'withdrawn':
            return 'bg-slate-400 text-white shadow-sm';
        case 'graduated':
            return 'bg-blue-600 text-white shadow-sm';
        default:
            return 'bg-primary text-white shadow-sm';
    }
};

const downloadPdf = async () => {
    try {
        const response = await axios.post('/exports/start', {
            type: 'students',
            filters: {
                search: searchQuery.value,
                status: selectedStatus.value,
                class_id: selectedClassId.value ? String(selectedClassId.value) : '',
                gender: selectedGender.value,
            }
        });
        
        if (window.dispatchEvent) {
            window.dispatchEvent(new CustomEvent('export-started', { detail: response.data }));
        }
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to start export. Please try again.');
    }
};
</script>

<template>
    <Head title="Student Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Students</h1>
                    <p class="text-xs text-muted-foreground">Managing all students in the school</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        @click="bulkUploadOpen = true"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <Upload class="mr-2 h-4 w-4 text-primary" />
                        Add Many Students
                    </Button>
                    <Button
                        variant="outline"
                        @click="downloadPdf"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <FileText class="mr-2 h-4 w-4 text-rose-500" />
                        Download PDF
                    </Button>
                    <Button as-child class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                        <Link href="/students/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Student
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Total Students', val: stats.total, sub: `+${stats.new_this_month} this month`, icon: Users },
                    { label: 'Active Students', val: stats.active, sub: `${activeRate}% of total`, icon: Activity },
                    { label: 'Recent Growth', val: `${stats.growth}%`, sub: 'Student intake', icon: TrendingUp },
                    { label: 'Withdrawn', val: stats.withdrawn, sub: 'Inactive records', icon: ShieldAlert }
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

            <!-- Filters -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-primary" />
                        <span class="text-xs font-semibold text-foreground">Filter Students</span>
                    </div>
                    <Button variant="ghost" size="sm" @click="showFilters = !showFilters" class="h-8 text-xs font-medium">
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                </div>
                <div v-show="showFilters" class="p-6">
                     <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Search Student</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input v-model="searchQuery" placeholder="Name or admission number..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Class</Label>
                            <div class="relative">
                                <select v-model="selectedClassId" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="">All Classes</option>
                                    <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Status</Label>
                            <div class="relative">
                                <select v-model="selectedStatus" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">All Status</option>
                                    <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Gender</Label>
                             <div class="relative">
                                <select v-model="selectedGender" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm outline-none focus:bg-background focus:ring-2 focus:ring-primary/10">
                                    <option value="all">All Gender</option>
                                    <option v-for="opt in genderOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="w-12 px-6 py-4">
                                    <button @click="toggleAllSelection" class="flex h-4 w-4 items-center justify-center rounded border border-border bg-background transition-all hover:border-primary">
                                        <CheckSquare v-if="selectedLearnerIds.length === learners.data.length" class="h-3 w-3 text-primary" />
                                        <div v-else-if="selectedLearnerIds.length > 0" class="h-1.5 w-1.5 rounded-sm bg-primary"></div>
                                    </button>
                                </th>
                                <th class="px-6 py-4">Student Details</th>
                                <th class="px-6 py-4">Admission No.</th>
                                <th class="px-6 py-4">Class</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="learner in learners.data" :key="learner.id" class="group transition-all hover:bg-muted/20">
                                <td class="px-6 py-4">
                                    <button @click="toggleSelection(learner.id)" class="flex h-4 w-4 items-center justify-center rounded border border-border bg-background transition-all hover:border-primary">
                                        <CheckSquare v-if="selectedLearnerIds.includes(learner.id)" class="h-3 w-3 text-primary" />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-border bg-muted shadow-sm transition-transform group-hover:scale-105">
                                            <img v-if="learner.photo_url" :src="learner.photo_url" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-xs font-semibold text-primary">{{ learner.name.charAt(0).toUpperCase() }}</div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-semibold tracking-tight text-foreground group-hover:text-primary transition-colors">{{ learner.name }}</p>
                                            <p class="text-[10px] font-medium text-muted-foreground uppercase">{{ learner.gender }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="outline" class="rounded-md border-primary/10 bg-primary/5 px-2 py-0.5 text-[10px] font-semibold text-primary">
                                        {{ learner.admission_number || '--' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-foreground">{{ learner.class || 'Unassigned' }}</td>
                                <td class="px-6 py-4">
                                    <Badge :class="getStatusColor(learner.status)" class="rounded-md border-0 px-2 py-0.5 text-[10px] font-semibold uppercase shadow-sm">
                                        {{ learner.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary">
                                            <Link :href="`/students/${learner.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary" v-if="hasPermission('students.update')">
                                            <Link :href="`/students/${learner.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-8 w-8 rounded-lg hover:bg-rose-50 hover:text-rose-600" 
                                            v-if="hasPermission('students.delete')"
                                            @click="openConfirm('delete', learner)"
                                            title="Delete Student"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-muted"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-xl border-border bg-card p-2 shadow-lg">
                                                <DropdownMenuItem v-if="learner.status !== 'suspended' && hasPermission('students.update')" @click="openConfirm('suspend', learner)" class="rounded-lg px-3 py-2 text-xs font-medium text-rose-500 focus:bg-rose-50 focus:text-rose-600">
                                                    <ShieldAlert class="mr-2 h-4 w-4" />
                                                    Suspend Student
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-else-if="hasPermission('students.update')" @click="openConfirm('activate', learner)" class="rounded-lg px-3 py-2 text-xs font-medium text-emerald-600 focus:bg-emerald-50 focus:text-emerald-700">
                                                    <CheckCircle2 class="mr-2 h-4 w-4" />
                                                    Activate Student
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex h-16 items-center justify-between border-t border-border/50 px-6 bg-muted/5">
                    <p class="text-xs text-muted-foreground font-medium">Showing {{ learners.from || 0 }} - {{ learners.to || 0 }} of {{ stats.total }} students</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="(link, i) in learners.links" :key="i">
                            <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-8 w-8 rounded-lg text-xs font-medium transition-all', link.active ? 'border-primary bg-primary text-white shadow-sm' : 'border-border bg-card hover:bg-muted text-muted-foreground']" @click="applyFilters(Number(link.label))">{{ link.label }}</Button>
                            <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-xs font-medium border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="link.url && applyFilters(learners.current_page - 1)">Prev</Button>
                            <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-xs font-medium border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="link.url && applyFilters(learners.current_page + 1)">Next</Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Selection Action Bar -->
        <div v-if="selectedLearnerIds.length > 0" class="fixed bottom-10 left-1/2 z-50 -translate-x-1/2 animate-in fade-in slide-in-from-bottom-10">
            <div class="flex items-center gap-6 rounded-2xl border border-primary/20 bg-slate-900 px-6 py-4 text-white shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/20"><Users class="h-5 w-5 text-primary" /></div>
                    <div class="space-y-0.5">
                        <p class="text-xs font-semibold tracking-tight">{{ selectedLearnerIds.length }} students selected</p>
                        <p class="text-[10px] font-medium text-white/50 uppercase">Selection Actions</p>
                    </div>
                </div>
                <div class="h-10 w-px bg-white/10 mx-2"></div>
                <div class="flex items-center gap-2">
                    <Button @click="promoteOpen = true" class="h-10 rounded-xl bg-primary px-6 text-xs font-semibold text-white shadow-lg hover:bg-primary/90 transition-all">Bulk Promote</Button>
                    <Button variant="ghost" @click="selectedLearnerIds = []" class="h-10 rounded-xl px-4 text-xs font-medium text-slate-400 hover:text-white">Clear</Button>
                </div>
            </div>
        </div>

        <!-- Confirm Dialog -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-[420px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl" :class="confirmMode === 'delete' ? 'bg-rose-50 text-rose-500' : 'bg-primary/10 text-primary'">
                            <ShieldAlert class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground">Confirm Action</h3>
                            <p class="text-xs text-muted-foreground font-medium">Please review before proceeding</p>
                        </div>
                    </div>
                    <p class="text-sm font-medium leading-relaxed text-muted-foreground">
                        Are you sure you want to <span class="text-rose-600 font-bold">{{ confirmMode }}</span> student <span class="text-foreground font-bold">{{ selectedLearner?.name }}</span>? This action may affect student records.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 bg-muted/10 p-4 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-10 rounded-lg px-4 text-xs font-semibold">Cancel</Button>
                    <Button @click="handleAction" :disabled="actionForm.processing" :class="['h-10 rounded-lg px-6 text-xs font-semibold text-white shadow-sm', confirmMode === 'delete' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-primary hover:bg-primary/90']">Confirm and Continue</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="promoteOpen" @update:open="promoteOpen = $event">
             <DialogContent class="sm:max-w-[450px] rounded-2xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"><ArrowUpCircle class="h-6 w-6" /></div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground">Promote Students</h3>
                            <p class="text-xs text-muted-foreground font-medium">Move students to a new class</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/30">
                            <p class="text-sm font-semibold text-foreground">{{ selectedLearnerIds.length }} students selected for promotion.</p>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Target Class</Label>
                            <div class="relative">
                                <select v-model="promotionTargetClass" class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/10 px-4 text-sm font-medium outline-none focus:bg-background">
                                    <option value="">Select target class</option>
                                    <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/40" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 bg-muted/10 p-4 border-t border-border/50">
                    <Button variant="ghost" @click="promoteOpen = false" class="h-10 rounded-lg px-4 text-xs font-semibold">Cancel</Button>
                    <Button @click="handleBulkPromotion" :disabled="promotionForm.processing || !promotionTargetClass" class="h-10 rounded-lg bg-emerald-600 px-6 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all">Promote Students</Button>
                </div>
            </DialogContent>
        </Dialog>
        
        <BulkUploadModal v-model:open="bulkUploadOpen" @uploaded="applyFilters()" />
    </AppLayout>
</template>
