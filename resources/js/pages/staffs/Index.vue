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
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    teachers: any;
    departments: any[];
    roles: any[];
    stats: any;
    filters: any;
    availableClasses: any[];
    availableSubjects: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Staff', href: '/staffs' },
];

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const departmentFilter = ref(props.filters.department_id || 'all');
const roleFilter = ref(props.filters.role || 'all');
const viewMode = ref(props.filters.view || 'grid');

const applyFilters = () => {
    router.get(
        '/staffs',
        {
            search: searchQuery.value,
            status: statusFilter.value,
            department_id: departmentFilter.value,
            role: roleFilter.value,
            view: viewMode.value,
        },
        { preserveState: true, replace: true },
    );
};

watch(
    [searchQuery, statusFilter, departmentFilter, roleFilter, viewMode],
    () => {
        applyFilters();
    },
);

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

const deleteStaff = (id: number) => {
    if (confirm('Are you sure you want to delete this staff member?')) {
        router.delete(`/staffs/${id}`);
    }
};
</script>

<template>
    <Head title="Staff Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">People</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Staff Registry</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Staff Management
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage teachers, administrators, and support staff records.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/staffs/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] hover:bg-primary/90 active:scale-95"
                    >
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add New Staff
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 px-1">
                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Total Employees</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Total Personnel</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-emerald-200 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                            <UserCheck class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Active Now</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Currently On-Duty</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <GraduationCap class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Academic</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.teaching }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Teaching Staff</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-purple-200 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all shadow-sm">
                            <Briefcase class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Support</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.admins + stats.non_teaching }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Administration</p>
                </div>
            </div>

            <!-- Toolbar & Filter -->
            <div class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row">
                <div class="flex w-full flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="group relative w-full sm:max-w-xs">
                        <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Find by name or email..."
                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold tracking-tight uppercase transition-all focus:bg-background"
                        />
                    </div>

                    <div class="flex gap-2 w-full sm:w-auto">
                        <Select v-model="roleFilter">
                            <SelectTrigger class="h-11 w-full sm:w-[160px] rounded-xl border-border bg-muted/20 text-xs font-bold uppercase tracking-tight">
                                <SelectValue placeholder="All Roles" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl border-border shadow-xl">
                                <SelectItem value="all" class="text-xs font-bold uppercase tracking-tight">All Roles</SelectItem>
                                <SelectItem
                                    v-for="r in roles"
                                    :key="r.id"
                                    :value="r.name"
                                    class="text-xs font-bold uppercase tracking-tight"
                                >
                                    {{ r.display_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="departmentFilter">
                            <SelectTrigger class="h-11 w-full sm:w-[160px] rounded-xl border-border bg-muted/20 text-xs font-bold uppercase tracking-tight">
                                <SelectValue placeholder="Departments" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl border-border shadow-xl">
                                <SelectItem value="all" class="text-xs font-bold uppercase tracking-tight">All Depts</SelectItem>
                                <SelectItem
                                    v-for="dept in departments"
                                    :key="dept.id"
                                    :value="dept.id.toString()"
                                    class="text-xs font-bold uppercase tracking-tight"
                                >
                                    {{ dept.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 rounded-xl border border-border bg-muted/20 p-1.5 shadow-inner">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-9 w-9 rounded-lg transition-all"
                        :class="viewMode === 'grid' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground/40'"
                        @click="viewMode = 'grid'"
                    >
                        <LayoutGrid class="h-4.5 w-4.5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-9 w-9 rounded-lg transition-all"
                        :class="viewMode === 'list' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground/40'"
                        @click="viewMode = 'list'"
                    >
                        <ListIcon class="h-4.5 w-4.5" />
                    </Button>
                </div>
            </div>

            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid' && teachers.data.length > 0"
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 px-1"
            >
                <div
                    v-for="teacher in teachers.data"
                    :key="teacher.id"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/30 dark:border-white/5"
                >
                    <div class="flex flex-col items-center gap-4 text-center">
                        <div class="relative">
                            <div class="h-20 w-20 overflow-hidden rounded-2xl border border-border shadow-sm group-hover:border-primary/50 transition-all">
                                <img
                                    v-if="teacher.photo_url"
                                    :src="teacher.photo_url"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-muted text-xl font-bold text-muted-foreground uppercase"
                                >
                                    {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                </div>
                            </div>
                            <div
                                class="absolute -right-1 -bottom-1 h-3.5 w-3.5 rounded-full border-2 border-background ring-1 ring-border shadow-sm"
                                :class="teacher.status === 'active' ? 'bg-emerald-500' : 'bg-amber-500'"
                            ></div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-bold text-foreground group-hover:text-primary transition-colors">
                                {{ teacher.first_name }} {{ teacher.last_name }}
                            </h4>
                            <p class="mt-0.5 truncate text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">
                                {{ teacher.department?.name || 'Unassigned' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-2">
                        <Button
                            as-child
                            variant="secondary"
                            class="h-10 flex-1 rounded-xl bg-muted/40 text-[10px] font-bold tracking-tight uppercase transition-all hover:bg-primary/5 hover:text-primary"
                        >
                            <Link :href="`/staffs/${teacher.id}`">
                                <Eye class="mr-2 h-3.5 w-3.5" />
                                Details
                            </Link>
                        </Button>
                        <Button
                            as-child
                            variant="secondary"
                            size="icon"
                            class="h-10 w-10 rounded-xl bg-muted/40 text-muted-foreground transition-all hover:bg-primary hover:text-white"
                        >
                            <Link :href="`/staffs/${teacher.id}/edit`">
                                <Edit class="h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div
                v-else-if="viewMode === 'list' && teachers.data.length > 0"
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-muted-foreground">
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase">Staff Member</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase text-center">Department</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase text-center">Roles</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase text-center">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold tracking-tight uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="teacher in teachers.data"
                                :key="teacher.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 overflow-hidden rounded-xl border border-border shadow-sm group-hover:border-primary/50 transition-colors">
                                            <img
                                                v-if="teacher.photo_url"
                                                :src="teacher.photo_url"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center bg-muted text-[10px] font-bold text-muted-foreground uppercase"
                                            >
                                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col">
                                            <Link
                                                :href="`/staffs/${teacher.id}`"
                                                class="text-sm font-bold text-foreground transition-colors group-hover:text-primary"
                                            >
                                                {{ teacher.first_name }} {{ teacher.last_name }}
                                            </Link>
                                            <span class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">
                                                {{ teacher.user?.email || 'No Email' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge variant="outline" class="h-6 rounded-lg border-none bg-primary/5 px-2 text-[10px] font-bold tracking-tight text-primary uppercase">
                                        {{ teacher.department?.name || 'N/A' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                        <Badge
                                            v-for="role in teacher.user?.roles"
                                            :key="role.id"
                                            variant="secondary"
                                            class="h-5 rounded-md border-none bg-muted/50 px-1.5 text-[9px] font-bold tracking-tight text-muted-foreground uppercase"
                                        >
                                            {{ role.name.replace('_', ' ') }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="border-none px-3 py-1 text-[10px] font-bold tracking-tight uppercase"
                                        :class="teacher.status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'"
                                    >
                                        {{ teacher.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1 px-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/staffs/${teacher.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/staffs/${teacher.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-muted-foreground hover:bg-muted"
                                                >
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border-border p-2 shadow-xl"
                                            >
                                                <DropdownMenuItem
                                                    @click="deleteStaff(teacher.id)"
                                                    class="rounded-lg py-2.5 text-xs font-bold text-rose-500"
                                                >
                                                    <Trash2 class="mr-3 h-4 w-4 opacity-60" />
                                                    Remove Employee
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

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center space-y-4 rounded-2xl border-2 border-dashed border-border bg-muted/20 py-24 text-center px-6"
            >
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted shadow-inner">
                    <Users class="h-10 w-10 text-muted-foreground/20" />
                </div>
                <div class="max-w-xs space-y-2">
                    <h3 class="text-lg font-bold text-foreground">No records found</h3>
                    <p class="text-xs font-medium text-muted-foreground">
                        We couldn't find any staff members matching your current filters.
                    </p>
                </div>
                <Button
                    variant="outline"
                    class="h-10 rounded-xl px-6 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                    @click="
                        searchQuery = '';
                        statusFilter = 'all';
                        departmentFilter = 'all';
                        roleFilter = 'all';
                    "
                >
                    Reset filters
                </Button>
            </div>

            <!-- Footer Pagination -->
            <div
                v-if="teachers.last_page > 1"
                class="flex flex-col items-center justify-between gap-4 border-t border-border/50 bg-muted/5 px-6 py-4 md:flex-row"
            >
                <p class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase">
                    Showing {{ teachers.from }} - {{ teachers.to }} of {{ teachers.total }} staff records
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-10 w-10 rounded-xl border-border bg-background p-0 transition-all hover:bg-muted"
                        :disabled="!teachers.prev_page_url"
                        as-child
                    >
                        <Link :href="teachers.prev_page_url || '#'" :preserve-state="true" class="flex items-center justify-center">
                            <ChevronDown class="h-4 w-4 rotate-90" />
                        </Link>
                    </Button>
                    <div class="flex items-center gap-2 px-4 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        Page <span class="text-primary">{{ teachers.current_page }}</span> of {{ teachers.last_page }}
                    </div>
                    <Button
                        variant="outline"
                        class="h-10 w-10 rounded-xl border-border bg-background p-0 transition-all hover:bg-muted"
                        :disabled="!teachers.next_page_url"
                        as-child
                    >
                        <Link :href="teachers.next_page_url || '#'" :preserve-state="true" class="flex items-center justify-center">
                            <ChevronDown class="h-4 w-4 -rotate-90" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
