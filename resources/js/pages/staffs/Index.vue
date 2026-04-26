<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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

const applyFilters = () => {
    router.get(
        '/staffs',
        {
            search: searchQuery.value,
            status: statusFilter.value,
            department_id: departmentFilter.value,
            role: roleFilter.value,
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
    [statusFilter, departmentFilter, roleFilter],
    () => applyFilters(),
);

const confirmDelete = (id: number) => {
    if (window.confirm('Are you sure you want to delete this staff member?')) {
        router.delete(`/staffs/${id}`);
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
                    <Button as-child class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90">
                        <Link href="/staffs/create">
                            <UserPlus class="mr-2 h-4 w-4" />
                            Add Staff Member
                        </Link>
                    </Button>
                </div>
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
                            <tr class="border-b border-border/50 bg-muted/5 text-xs font-bold text-muted-foreground uppercase">
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
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-border bg-muted">
                                            <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-[10px] font-bold text-primary">
                                                {{ (teacher.name || 'S').charAt(0).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="font-semibold text-foreground">{{ teacher.name }}</p>
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
                                    <Badge :class="getStatusColor(teacher.status)" class="rounded-lg border-0 px-2.5 py-1 text-[10px] font-semibold">
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
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-muted"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-xl border-border bg-card p-2 shadow-xl">
                                                <DropdownMenuItem class="rounded-lg px-3 py-2 text-xs font-semibold text-muted-foreground focus:bg-muted">
                                                    <Mail class="mr-3 h-4 w-4" />
                                                    Send Message
                                                </DropdownMenuItem>
                                                <!-- Other secondary actions can go here -->
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
    </AppLayout>
</template>
