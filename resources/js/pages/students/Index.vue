<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
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
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
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
</script>

<template>
    <Head title="Learner Intelligence Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black">Registry Matrix</h1>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Synchronizing {{ stats.total }} Institutional Assets</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        @click="bulkUploadOpen = true"
                        class="h-12 rounded-2xl border-border bg-card px-6 text-[10px] font-bold tracking-widest uppercase hover:bg-muted"
                    >
                        <Upload class="mr-2.5 h-4 w-4 text-primary" />
                        Bulk Matrix
                    </Button>
                    <Button as-child class="h-12 rounded-2xl bg-primary px-8 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <Link href="/students/create">
                            <Plus class="mr-2.5 h-4 w-4" />
                            Enroll Entity
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Matrix -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Global Registry', val: stats.total, sub: `+${stats.new_this_month} Month`, icon: Users },
                    { label: 'Active Pipeline', val: stats.active, sub: `${activeRate}% Fidelity`, icon: Activity },
                    { label: 'Growth Vector', val: `${stats.growth}%`, sub: 'Peak Iteration', icon: TrendingUp },
                    { label: 'Registry Exit', val: stats.withdrawn, sub: 'Dormant Nodes', icon: ShieldAlert }
                ]" :key="idx" class="group relative overflow-hidden rounded-3xl border border-border bg-card p-6 transition-all hover:border-primary/20">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight">{{ stat.val }}</h3>
                        <span class="text-[9px] font-bold text-primary uppercase">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Intelligence Hub (Filters) -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm">
                <div class="flex h-14 items-center justify-between border-b border-border/50 bg-muted/10 px-8">
                    <div class="flex items-center gap-3">
                        <Search class="h-4 w-4 text-primary" />
                        <span class="text-[10px] font-bold tracking-widest text-foreground uppercase">Registry Filters</span>
                    </div>
                    <Button variant="ghost" size="sm" @click="showFilters = !showFilters" class="h-8 text-[10px] font-bold uppercase transition-all">
                        {{ showFilters ? 'Hide Parameters' : 'Show Parameters' }}
                    </Button>
                </div>
                <div v-show="showFilters" class="p-8">
                     <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Global Search</Label>
                            <div class="relative">
                                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                <Input v-model="searchQuery" placeholder="Name or Identifier..." class="h-12 rounded-xl border-border bg-muted/20 pl-11 pr-4 text-xs font-bold uppercase focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Strategic Stream</Label>
                            <div class="relative">
                                <select v-model="selectedClassId" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background focus:ring-4 focus:ring-primary/5">
                                    <option value="">Aesthetic Channels</option>
                                    <option v-for="c in classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Registry State</Label>
                            <div class="relative">
                                <select v-model="selectedStatus" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background focus:ring-4 focus:ring-primary/5">
                                    <option value="all">All States</option>
                                    <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Identity Status</Label>
                             <div class="relative">
                                <select v-model="selectedGender" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background focus:ring-4 focus:ring-primary/5">
                                    <option value="all">Global Matrix</option>
                                    <option v-for="opt in genderOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Matrix -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/10 text-[10px] font-bold tracking-widest text-muted-foreground uppercase">
                                <th class="w-12 px-6 py-5">
                                    <button @click="toggleAllSelection" class="flex h-5 w-5 items-center justify-center rounded-md border border-border bg-background transition-all hover:border-primary">
                                        <CheckSquare v-if="selectedLearnerIds.length === learners.data.length" class="h-3.5 w-3.5 text-primary" />
                                        <div v-else-if="selectedLearnerIds.length > 0" class="h-2 w-2 rounded-sm bg-primary"></div>
                                    </button>
                                </th>
                                <th class="px-6 py-5">Institutional Identity</th>
                                <th class="px-6 py-5">Registry Key</th>
                                <th class="px-6 py-5">Placement Slot</th>
                                <th class="px-6 py-5">Pulse Status</th>
                                <th class="px-6 py-5 text-right font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="learner in learners.data" :key="learner.id" class="group transition-all hover:bg-muted/30">
                                <td class="px-6 py-5">
                                    <button @click="toggleSelection(learner.id)" class="flex h-5 w-5 items-center justify-center rounded-md border border-border bg-background transition-all hover:border-primary">
                                        <CheckSquare v-if="selectedLearnerIds.includes(learner.id)" class="h-3.5 w-3.5 text-primary" />
                                    </button>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl border border-border bg-muted shadow-sm transition-transform group-hover:scale-105">
                                            <img v-if="learner.photo_url" :src="learner.photo_url" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-xs font-bold text-primary">{{ learner.name.charAt(0).toUpperCase() }}</div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-xs font-bold tracking-tight text-foreground group-hover:text-primary transition-colors uppercase">{{ learner.name }}</p>
                                            <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">{{ learner.gender }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <Badge variant="outline" class="rounded-lg border-primary/10 bg-primary/5 px-2 py-0.5 text-[10px] font-bold tracking-tight text-primary uppercase">
                                        {{ learner.admission_number || '--' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-5 text-xs font-bold tracking-tight text-foreground uppercase">{{ learner.class || 'UNASSIGNED' }}</td>
                                <td class="px-6 py-5">
                                    <Badge :class="getStatusColor(learner.status)" class="rounded-lg border-0 px-2.5 py-1 text-[9px] font-bold tracking-tight uppercase shadow-sm">
                                        {{ learner.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm">
                                            <Link :href="`/students/${learner.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm" v-if="hasPermission('students.update')">
                                            <Link :href="`/students/${learner.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-2xl border-border bg-card p-2 shadow-2xl">
                                                <DropdownMenuItem v-if="learner.status !== 'suspended' && hasPermission('students.update')" @click="openConfirm('suspend', learner)" class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-rose-500 uppercase focus:bg-rose-50">
                                                    <ShieldAlert class="mr-3 h-4 w-4" />
                                                    Suspend Profile
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-else-if="hasPermission('students.update')" @click="openConfirm('activate', learner)" class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-emerald-600 uppercase focus:bg-emerald-50">
                                                    <CheckCircle2 class="mr-3 h-4 w-4" />
                                                    Restore Access
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 border-border/50" />
                                                <DropdownMenuItem @click="openConfirm('delete', learner)" class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-rose-600 uppercase focus:bg-rose-50" v-if="hasPermission('students.delete')">
                                                    <Trash2 class="mr-3 h-4 w-4" />
                                                    Purge Entity
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Strategic Pagination -->
                <div class="flex h-20 items-center justify-between border-t border-border/50 px-8 bg-muted/5">
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Registry Point: {{ learners.from || 0 }} - {{ learners.to || 0 }} of {{ stats.total }}</p>
                    <div class="flex items-center gap-2">
                        <template v-for="(link, i) in learners.links" :key="i">
                            <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-9 w-9 rounded-xl text-[10px] font-bold tracking-tight transition-all', link.active ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20' : 'border-border bg-card hover:bg-muted text-muted-foreground']" @click="applyFilters(Number(link.label))">{{ link.label }}</Button>
                            <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-9 rounded-xl px-4 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="link.url && applyFilters(learners.current_page - 1)">Prev</Button>
                            <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-9 rounded-xl px-4 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="link.url && applyFilters(learners.current_page + 1)">Next</Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Selection Action Bar -->
        <div v-if="selectedLearnerIds.length > 0" class="fixed bottom-10 left-1/2 z-50 -translate-x-1/2 animate-in fade-in slide-in-from-bottom-10">
            <div class="flex items-center gap-6 rounded-3xl border border-primary/20 bg-slate-900 px-8 py-5 text-white shadow-2xl shadow-primary/20">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/20"><Users class="h-5 w-5 text-primary" /></div>
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold tracking-widest uppercase opacity-60">Selection Matrix</p>
                        <p class="text-sm font-bold tracking-tight">{{ selectedLearnerIds.length }} Nodes Targeted</p>
                    </div>
                </div>
                <div class="h-10 w-px bg-white/10 mx-2"></div>
                <div class="flex items-center gap-3">
                    <Button @click="promoteOpen = true" class="h-12 rounded-2xl bg-primary px-8 text-[10px] font-bold tracking-widest text-white uppercase shadow-lg shadow-primary/20 transition-all hover:scale-[1.05]">Bulk Promote</Button>
                    <Button variant="ghost" @click="selectedLearnerIds = []" class="h-12 rounded-2xl px-6 text-[10px] font-bold tracking-widest text-slate-400 uppercase hover:text-white">Clear Matrix</Button>
                </div>
            </div>
        </div>

        <!-- Dialogs -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-[480px] rounded-3xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl" :class="confirmMode === 'delete' ? 'bg-rose-50 text-rose-500' : 'bg-primary/10 text-primary'">
                            <ShieldAlert class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">Critical Protocol</h3>
                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Registry Integrity Guard</p>
                        </div>
                    </div>
                    <p class="text-sm font-bold leading-relaxed text-muted-foreground">
                        Initialize <span class="text-rose-600 underline decoration-2 underline-offset-4">{{ confirmMode }} event</span> for <span class="text-foreground uppercase">{{ selectedLearner?.name }}</span>? Institutional synchronization will be modified.
                    </p>
                </div>
                <div class="flex items-center justify-between gap-4 bg-muted/10 p-6 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-12 rounded-2xl px-8 text-[10px] font-bold tracking-widest uppercase">Abort</Button>
                    <Button @click="handleAction" :disabled="actionForm.processing" class="h-12 rounded-2xl bg-rose-600 px-10 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl">Execute Protocol</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="promoteOpen" @update:open="promoteOpen = $event">
             <DialogContent class="sm:max-w-[500px] rounded-3xl border-border bg-card p-0 shadow-2xl overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500"><ArrowUpCircle class="h-6 w-6" /></div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">Promotion Hub</h3>
                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Academic elevation matrix</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="p-5 rounded-2xl border border-primary/10 bg-primary/5">
                            <p class="text-[10px] font-bold tracking-widest text-primary uppercase opacity-60 mb-1">Target Selection</p>
                            <p class="text-sm font-black text-foreground">{{ selectedLearnerIds.length }} Nodes identified for elevation.</p>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Target Stream Channel</Label>
                            <div class="relative">
                                <select v-model="promotionTargetClass" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-5 text-sm font-bold uppercase outline-none focus:bg-background">
                                    <option value="">Select Target Channel</option>
                                    <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-4 bg-muted/10 p-6 border-t border-border/50">
                    <Button variant="ghost" @click="promoteOpen = false" class="h-12 rounded-2xl px-8 text-[10px] font-bold tracking-widest uppercase">Abort</Button>
                    <Button @click="handleBulkPromotion" :disabled="promotionForm.processing || !promotionTargetClass" class="h-12 rounded-2xl bg-emerald-600 px-10 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl transition-all hover:scale-[1.02]">Initialize Elevation</Button>
                </div>
            </DialogContent>
        </Dialog>
        
        <BulkUploadModal v-model:open="bulkUploadOpen" @uploaded="applyFilters()" />
    </AppLayout>
</template>
