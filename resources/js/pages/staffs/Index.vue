<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Users,
    Search,
    Filter,
    Mail,
    Phone,
    Building2,
    UserPlus,
    BadgeCheck,
    LayoutGrid,
    List as ListIcon,
    ChevronDown,
    GraduationCap,
    Eye,
    Plus,
    MoreHorizontal,
    Edit,
    Trash2,
    ShieldCheck,
    Briefcase,
    UserCheck,
    ChevronRight,
    Home,
    SearchCode,
    Database,
    Zap,
    TrendingUp,
    ShieldAlert,
    ExternalLink,
    Upload,
    FileText,
    CheckSquare,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
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
import StaffBulkUploadModal from './partials/StaffBulkUploadModal.vue';

const props = defineProps<{
    teachers: any;
    departments: any[];
    roles: any[];
    stats: any;
    filters: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/staffs' },
];

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const departmentFilter = ref(props.filters.department_id || 'all');
const roleFilter = ref(props.filters.role || 'all');
const perPage = ref(props.filters.per_page || 20);
const bulkUploadOpen = ref(false);
const selectedStaffIds = ref<number[]>([]);
const isGlobalSelection = ref(false);

const applyFilters = (pageNumber?: number) => {
    router.get(
        '/staffs',
        {
            search: searchQuery.value,
            status: statusFilter.value,
            department_id: departmentFilter.value,
            role: roleFilter.value,
            per_page: perPage.value,
            page: pageNumber,
        },
        { preserveState: true, replace: true },
    );
};

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch(
    [statusFilter, departmentFilter, roleFilter, perPage],
    () => applyFilters(),
);

const toggleSelection = (id: number) => {
    isGlobalSelection.value = false;
    const index = selectedStaffIds.value.indexOf(id);
    if (index === -1) {
        selectedStaffIds.value.push(id);
    } else {
        selectedStaffIds.value.splice(index, 1);
    }
};

const toggleAllSelection = () => {
    if (selectedStaffIds.value.length === props.teachers.data.length || isGlobalSelection.value) {
        selectedStaffIds.value = [];
        isGlobalSelection.value = false;
    } else {
        selectedStaffIds.value = props.teachers.data.map((t: any) => t.id);
    }
};

const selectAllMatching = () => {
    isGlobalSelection.value = true;
    selectedStaffIds.value = props.teachers.data.map((t: any) => t.id);
};

const handleBulkDelete = () => {
    const count = isGlobalSelection.value ? props.stats.total : selectedStaffIds.value.length;
    if (window.confirm(`Are you sure you want to delete ${count} ${isGlobalSelection.value ? 'matching' : 'selected'} staff members? This action is reversible via soft delete.`)) {
        router.post('/staffs/bulk-delete', {
            ids: selectedStaffIds.value,
            all_matching: isGlobalSelection.value,
            filters: isGlobalSelection.value ? {
                search: searchQuery.value,
                status: statusFilter.value,
                department_id: departmentFilter.value,
                role: roleFilter.value,
            } : null
        }, {
            onSuccess: () => {
                selectedStaffIds.value = [];
                isGlobalSelection.value = false;
            }
        });
    }
};

const confirmDelete = (id: number) => {
    if (window.confirm('Are you sure you want to delete this staff member?')) {
        router.delete(`/staffs/${id}`);
    }
};

const downloadPdf = async () => {
    try {
        const response = await axios.post('/exports/start', {
            type: 'staff',
            filters: {
                search: searchQuery.value,
                status: statusFilter.value,
                department_id: departmentFilter.value,
                role: roleFilter.value,
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

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white shadow-sm';
        case 'on_leave':
            return 'bg-amber-500 text-white shadow-sm';
        case 'suspended':
            return 'bg-rose-500 text-white shadow-sm';
        default:
            return 'bg-slate-400 text-white';
    }
};
</script>

<template>
    <Head title="Staff Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Staff Members</h1>
                    <p class="text-sm text-muted-foreground">Manage all school staff and personnel.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        @click="bulkUploadOpen = true"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <Upload class="mr-2 h-4 w-4 text-primary" />
                        Bulk Upload
                    </Button>
                    <Button
                        v-if="selectedStaffIds.length > 0"
                        variant="outline"
                        @click="handleBulkDelete"
                        class="h-10 rounded-lg border-rose-200 bg-rose-50 px-4 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-900/20 dark:border-rose-900 dark:text-rose-400"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Bulk Delete ({{ isGlobalSelection ? stats.total : selectedStaffIds.length }})
                    </Button>
                    <Button
                        variant="outline"
                        @click="downloadPdf"
                        class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted"
                    >
                        <FileText class="mr-2 h-4 w-4 text-rose-500" />
                        Download PDF
                    </Button>
                    <Button as-child class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90">
                        <Link href="/staffs/create">
                            <UserPlus class="mr-2 h-4 w-4" />
                            Add Staff Member
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Global Selection Banner -->
            <div v-if="selectedStaffIds.length === teachers.data.length && teachers.data.length > 0 && !isGlobalSelection && stats.total > teachers.data.length" 
                 class="rounded-xl bg-primary/10 border border-primary/20 p-4 flex items-center justify-between animate-in slide-in-from-top-2">
                <div class="flex items-center gap-3">
                    <CheckSquare class="h-5 w-5 text-primary" />
                    <p class="text-sm font-medium text-primary">All <b>{{ teachers.data.length }}</b> staff on this page are selected.</p>
                </div>
                <Button variant="link" @click="selectAllMatching" class="text-xs font-bold text-primary underline">
                    Select all {{ stats.total }} staff members matching these filters
                </Button>
            </div>
            <div v-if="isGlobalSelection" class="rounded-xl bg-slate-900 border border-slate-800 p-4 flex items-center justify-between text-white animate-in slide-in-from-top-2 shadow-xl">
                 <div class="flex items-center gap-3">
                    <Database class="h-5 w-5 text-primary" />
                    <p class="text-sm font-medium">All <b>{{ stats.total }}</b> staff members are selected.</p>
                </div>
                <Button variant="link" @click="selectedStaffIds = []; isGlobalSelection = false;" class="text-xs font-bold text-white/60 hover:text-white underline">
                    Clear selection
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Total Personnel', val: stats.total, sub: 'All staff members', icon: Users, color: 'text-primary' },
                    { label: 'Active Staff', val: stats.active, sub: 'Currently on duty', icon: UserCheck, color: 'text-emerald-500' },
                    { label: 'Teaching Staff', val: stats.teaching, sub: 'Educators', icon: GraduationCap, color: 'text-blue-500' },
                    { label: 'Administration', val: stats.admins + (stats.non_teaching || 0), sub: 'Support staff', icon: Briefcase, color: 'text-purple-500' }
                ]" :key="idx" class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] transition-transform duration-700">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight" :class="stat.color">{{ stat.val }}</h3>
                        <span class="text-[10px] font-medium text-muted-foreground">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <Filter class="h-4 w-4 text-primary" />
                        <span class="text-xs font-bold text-foreground uppercase">Filter Staff</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 pr-4 border-r border-border">
                            <Label class="text-[10px] font-bold text-muted-foreground uppercase">Show</Label>
                            <select v-model="perPage" class="h-8 rounded-lg border border-border bg-transparent px-2 text-[11px] font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                                <option :value="20">20 / page</option>
                                <option :value="50">50 / page</option>
                                <option :value="100">100 / page</option>
                                <option :value="200">200 / page</option>
                                <option :value="500">500 / page</option>
                            </select>
                        </div>
                        <Button variant="ghost" size="sm" class="h-8 text-xs font-medium">Filter Results</Button>
                    </div>
                </div>
                <div class="p-6">
                     <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Search Staff</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input v-model="searchQuery" placeholder="Name or Email..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 text-xs font-semibold focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Department</Label>
                            <div class="relative">
                                <select v-model="departmentFilter" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background">
                                    <option value="all">All Departments</option>
                                    <option v-for="d in departments" :key="d.id" :value="String(d.id)">{{ d.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Role</Label>
                            <div class="relative">
                                <select v-model="roleFilter" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background">
                                    <option value="all">All Roles</option>
                                    <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-muted-foreground">Status</Label>
                             <div class="relative">
                                <select v-model="statusFilter" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background">
                                    <option value="all">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="w-12 px-6 py-4">
                                    <button @click="toggleAllSelection" class="flex h-4 w-4 items-center justify-center rounded border border-border bg-background transition-all hover:border-primary">
                                        <CheckSquare v-if="selectedStaffIds.length === teachers.data.length && teachers.data.length > 0" class="h-3 w-3 text-primary" />
                                        <div v-else-if="selectedStaffIds.length > 0" class="h-1.5 w-1.5 rounded-sm bg-primary"></div>
                                    </button>
                                </th>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Department</th>
                                <th class="px-6 py-4">Designation</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50 text-xs font-medium">
                            <tr v-for="teacher in teachers.data" :key="teacher.id" class="group transition-all hover:bg-muted/5">
                                <td class="px-6 py-4">
                                    <button @click="toggleSelection(teacher.id)" class="flex h-4 w-4 items-center justify-center rounded border border-border bg-background transition-all hover:border-primary">
                                        <CheckSquare v-if="selectedStaffIds.includes(teacher.id)" class="h-3 w-3 text-primary" />
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-border bg-muted shadow-sm transition-transform group-hover:scale-105">
                                            <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-[10px] font-bold text-primary">
                                                {{ (teacher.name || 'S').charAt(0).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="font-semibold text-foreground group-hover:text-primary transition-colors">{{ teacher.name }}</p>
                                            <p class="text-[10px] text-muted-foreground">{{ teacher.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="outline" class="rounded-lg border-primary/10 bg-primary/5 px-2 py-0.5 text-[10px] font-semibold text-primary">
                                        {{ teacher.department_name || 'GENERAL' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-muted-foreground">{{ teacher.role || 'Personnel' }}</td>
                                <td class="px-6 py-4">
                                    <Badge :class="getStatusColor(teacher.status)" class="rounded-lg border-0 px-2.5 py-1 text-[10px] font-semibold uppercase shadow-sm">
                                        {{ teacher.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary" title="View Profile">
                                            <Link :href="`/staffs/${teacher.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary" title="Edit Staff">
                                            <Link :href="`/staffs/${teacher.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-8 w-8 rounded-lg hover:bg-rose-50 hover:text-rose-600" 
                                            title="Delete Staff"
                                            @click="confirmDelete(teacher.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex h-16 items-center justify-between border-t border-border/50 px-6 bg-muted/5">
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Page {{ teachers.current_page }} of {{ teachers.last_page }}</p>
                    <div class="flex items-center gap-2">
                         <template v-for="(link, i) in teachers.links" :key="i">
                            <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-8 w-8 rounded-lg text-[10px] font-bold transition-all', link.active ? 'border-primary bg-primary text-white shadow-sm' : 'border-border bg-card hover:bg-muted text-muted-foreground']" @click="router.get(link.url!)">{{ link.label }}</Button>
                            <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="router.get(link.url!)">Prev</Button>
                            <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="router.get(link.url!)">Next</Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <StaffBulkUploadModal v-model:open="bulkUploadOpen" @uploaded="applyFilters()" />

    </AppLayout>
</template>
