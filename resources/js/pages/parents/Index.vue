<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Users,
    Search,
    Filter,
    Mail,
    Phone,
    UserPlus,
    Eye,
    Plus,
    MoreHorizontal,
    Edit,
    Trash2,
    FileText,
    CheckSquare,
    Square,
    Home,
    ChevronDown,
    ChevronRight,
    Loader2,
    AlertCircle,
    CheckCircle2,
    X,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

interface GuardianRow {
    id: number;
    first_name: string;
    last_name: string;
    name: string;
    email: string | null;
    phone: string;
    id_number: string | null;
    is_active: boolean;
    relationship_type?: string;
    students: Array<{ id: number; first_name: string; last_name: string }>;
}

const props = defineProps<{
    parents: {
        data: GuardianRow[];
        total: number;
        current_page: number;
        last_page: number;
        per_page: number;
        from: number;
        to: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
        per_page?: number;
    };
    stats: {
        total: number;
        active: number;
        new_this_month: number;
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parent Registry', href: '/parents' },
];

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const perPage = ref(props.filters.per_page || 20);
const selectedParentIds = ref<number[]>([]);
const isGlobalSelection = ref(false);

let debounceTimer: any;
const applyFilters = (page = 1) => {
    router.get(
        '/parents',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            per_page: perPage.value,
            page,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 400);
});

watch([selectedStatus, perPage], () => applyFilters());

const toggleSelection = (id: number) => {
    isGlobalSelection.value = false;
    const index = selectedParentIds.value.indexOf(id);
    if (index === -1) {
        selectedParentIds.value.push(id);
    } else {
        selectedParentIds.value.splice(index, 1);
    }
};

const toggleAllSelection = () => {
    if (selectedParentIds.value.length === props.parents.data.length || isGlobalSelection.value) {
        selectedParentIds.value = [];
        isGlobalSelection.value = false;
    } else {
        selectedParentIds.value = props.parents.data.map((p) => p.id);
    }
};

const selectAllMatching = () => {
    isGlobalSelection.value = true;
    selectedParentIds.value = props.parents.data.map((p) => p.id);
};

const handleBulkDelete = async () => {
    const count = isGlobalSelection.value ? props.stats.total : selectedParentIds.value.length;
    if (window.confirm(`Are you sure you want to delete ${count} ${isGlobalSelection.value ? 'matching' : 'selected'} parent accounts? This action will also delete associated user accounts.`)) {
        try {
            const response = await axios.post('/exports/start-delete', {
                type: 'parents',
                ids: selectedParentIds.value,
                all_matching: isGlobalSelection.value,
                filters: isGlobalSelection.value ? {
                    search: searchQuery.value,
                    status: selectedStatus.value,
                } : null
            });
            
            selectedParentIds.value = [];
            isGlobalSelection.value = false;
            
            if (window.dispatchEvent) {
                window.dispatchEvent(new CustomEvent('delete-started', { detail: response.data }));
            }
        } catch (error) {
            console.error('Bulk delete failed:', error);
            alert('Failed to start deletion. Please try again.');
        }
    }
};

const deleteParent = (id: number) => {
    if (confirm('Are you sure you want to delete this parent account? This will also remove the user account.')) {
        router.delete(`/parents/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                selectedParentIds.value = selectedParentIds.value.filter(pId => pId !== id);
            }
        });
    }
};

const downloadPdf = async () => {
    try {
        const response = await axios.post('/exports/start', {
            type: 'parents',
            filters: {
                search: searchQuery.value,
                status: selectedStatus.value,
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

const getStatusColor = (active: boolean) => {
    return active
        ? 'bg-emerald-500 text-white shadow-sm'
        : 'bg-slate-400 text-white shadow-sm';
};

</script>

<template>
    <Head title="Parent Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Page Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Parent Registry</h1>
                    <p class="text-xs text-muted-foreground uppercase tracking-widest font-bold opacity-60">Management of school guardians</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        @click="downloadPdf"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <FileText class="mr-2 h-4 w-4 text-rose-500" />
                        Export PDF
                    </Button>
                    <template v-if="selectedParentIds.length > 0">
                        <Button
                            variant="outline"
                            @click="handleBulkDelete"
                            class="h-10 rounded-lg border-rose-200 bg-rose-50 px-4 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Bulk Delete ({{ isGlobalSelection ? stats.total : selectedParentIds.length }})
                        </Button>
                    </template>
                    <Button as-child class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                        <Link href="/parents/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Parent
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-3 sm:gap-6">
                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Total Parents</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total.toLocaleString() }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Registered Guardians</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-emerald-200">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Active Now</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active.toLocaleString() }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Accounts Active</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Plus class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Growth</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">+{{ stats.new_this_month }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">This Month Only</p>
                </div>
            </div>

            <!-- Global Selection Banner -->
            <div v-if="selectedParentIds.length === parents.data.length && parents.data.length > 0 && !isGlobalSelection && stats.total > parents.data.length" 
                 class="rounded-xl bg-primary/10 border border-primary/20 p-4 flex items-center justify-between animate-in slide-in-from-top-2 mx-1">
                <div class="flex items-center gap-3">
                    <CheckSquare class="h-5 w-5 text-primary" />
                    <p class="text-sm font-medium text-primary">All {{ selectedParentIds.length }} parents on this page are selected.</p>
                </div>
                <Button variant="link" @click="selectAllMatching" class="text-sm font-bold text-primary hover:no-underline">
                    Select all {{ stats.total }} matching parent records instead
                </Button>
            </div>

            <div v-else-if="isGlobalSelection" 
                 class="rounded-xl bg-slate-900 border border-slate-800 p-4 flex items-center justify-between animate-in slide-in-from-top-2 mx-1">
                <div class="flex items-center gap-3">
                    <CheckSquare class="h-5 w-5 text-emerald-400" />
                    <p class="text-sm font-medium text-white">Awesome! You've selected all {{ stats.total }} matching parents across all pages.</p>
                </div>
                <Button variant="link" @click="toggleAllSelection" class="text-sm font-bold text-emerald-400 hover:no-underline">
                    Clear Global Selection
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row md:items-center md:justify-between px-1 mx-1">
                <div class="flex flex-1 items-center gap-4">
                    <div class="group relative flex-1 max-w-md">
                        <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Find parents by name, email, or phone..."
                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-semibold transition-all focus:bg-background"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                         <span class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">Status:</span>
                         <div class="relative">
                            <select v-model="selectedStatus" class="h-10 cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 pr-10 text-[10px] font-bold uppercase outline-none hover:bg-muted/40 transition-all">
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                            <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/40" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                         <span class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">Show:</span>
                         <div class="relative">
                            <select v-model="perPage" class="h-10 cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 pr-10 text-[10px] font-bold uppercase outline-none hover:bg-muted/40 transition-all">
                                <option :value="20">20 Rows</option>
                                <option :value="50">50 Rows</option>
                                <option :value="100">100 Rows</option>
                                <option :value="200">200 Rows</option>
                                <option :value="500">500 Rows</option>
                            </select>
                            <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/40" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm mx-1">
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-muted-foreground/60 transition-colors">
                                <th class="w-16 px-6 py-5">
                                    <button @click="toggleAllSelection" class="flex h-5 w-5 items-center justify-center rounded border border-border bg-background transition-colors hover:border-primary">
                                        <CheckSquare v-if="selectedParentIds.length === parents.data.length && parents.data.length > 0" class="h-4 w-4 text-primary" />
                                        <Square v-else class="h-4 w-4 text-muted-foreground/20" />
                                    </button>
                                </th>
                                <th class="px-6 py-5 text-[10px] font-bold tracking-widest uppercase">Guardian Profile</th>
                                <th class="px-6 py-5 text-[10px] font-bold tracking-widest uppercase">Associated Students</th>
                                <th class="px-6 py-5 text-[10px] font-bold tracking-widest uppercase text-center">Relationship</th>
                                <th class="px-6 py-5 text-[10px] font-bold tracking-widest uppercase">Contact details</th>
                                <th class="px-6 py-5 text-[10px] font-bold tracking-widest uppercase">Status</th>
                                <th class="px-6 py-5 text-right text-[10px] font-bold tracking-widest uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="parent in parents.data"
                                :key="parent.id"
                                class="group transition-all hover:bg-muted/30"
                                :class="selectedParentIds.includes(parent.id) ? 'bg-primary/5' : ''"
                            >
                                <td class="px-6 py-4">
                                    <button @click.stop="toggleSelection(parent.id)" class="flex h-5 w-5 items-center justify-center rounded border border-border bg-background transition-colors hover:border-primary">
                                        <CheckSquare v-if="selectedParentIds.includes(parent.id)" class="h-4 w-4 text-primary" />
                                        <Square v-else class="h-4 w-4 text-muted-foreground/20" />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-border bg-muted shadow-sm transition-transform group-hover:scale-105">
                                            <div class="flex h-full w-full items-center justify-center bg-primary/10 text-[10px] font-bold text-primary">
                                                {{ parent.first_name[0] }}{{ parent.last_name[0] }}
                                            </div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="font-semibold text-foreground group-hover:text-primary transition-colors text-sm">{{ parent.first_name }} {{ parent.last_name }}</p>
                                            <p class="text-[10px] text-muted-foreground font-bold tracking-widest uppercase opacity-40">ID: {{ parent.id_number || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="student in parent.students"
                                            :key="student.id"
                                            class="rounded-lg border-none bg-primary/5 px-2.5 py-0.5 text-[10px] font-bold text-primary uppercase shadow-sm"
                                        >
                                            {{ student.first_name }} {{ student.last_name }}
                                        </Badge>
                                        <p v-if="!parent.students?.length" class="text-[10px] text-muted-foreground font-bold uppercase opacity-30 italic">No linked children</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge variant="outline" class="rounded-lg border-primary/10 bg-primary/5 px-2 py-0.5 text-[10px] font-bold text-primary uppercase">
                                        {{ parent.relationship_type || 'Guardian' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center gap-2 text-xs font-bold text-foreground/80">
                                            <Phone class="h-3.5 w-3.5 text-primary opacity-60" />
                                            {{ parent.phone }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-muted-foreground uppercase opacity-60">
                                            <Mail class="h-3.5 w-3.5" />
                                            {{ parent.email || 'NO EMAIL' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :class="getStatusColor(parent.is_active)" class="rounded-lg border-0 px-2.5 py-1 text-[10px] font-bold uppercase shadow-sm">
                                        {{ parent.is_active ? 'Active' : 'Locked' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0">
                                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary" title="View Profile">
                                            <Link :href="`/parents/${parent.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary" title="Edit Parent">
                                            <Link :href="`/parents/${parent.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-8 w-8 rounded-lg hover:bg-rose-50 hover:text-rose-600" 
                                            title="Purge Record"
                                            @click="deleteParent(parent.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="parents.total === 0" class="flex flex-col items-center justify-center py-32 text-center">
                    <div class="h-16 w-16 rounded-2xl bg-muted/30 flex items-center justify-center mb-4">
                        <Users class="h-8 w-8 text-muted-foreground/30" />
                    </div>
                    <h3 class="text-sm font-bold text-foreground">No Parents Found</h3>
                    <p class="text-xs text-muted-foreground mt-1 max-w-[250px]">We couldn't find any guardian records matching your search filters.</p>
                    <Button variant="outline" @click="searchQuery = ''; selectedStatus = 'all'" class="mt-6 h-9 rounded-xl text-[10px] font-bold uppercase px-6">Clear All Filters</Button>
                </div>

                <!-- Pagination -->
                <div class="flex h-16 items-center justify-between border-t border-border/50 px-6 bg-muted/5">
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Row {{ parents.from }} - {{ parents.to }} OF {{ stats.total }} TOTAL</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="(link, i) in parents.links" :key="i">
                            <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-8 w-8 rounded-lg text-[10px] font-bold transition-all', link.active ? 'border-primary bg-primary text-white shadow-sm' : 'border-border bg-card hover:bg-muted text-muted-foreground']" @click="applyFilters(Number(link.label))">{{ link.label }}</Button>
                            <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="applyFilters(parents.current_page - 1)">Prev</Button>
                            <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="applyFilters(parents.current_page + 1)">Next</Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
