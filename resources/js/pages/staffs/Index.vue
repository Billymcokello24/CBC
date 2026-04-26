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
                        <span class="font-medium tracking-tight text-foreground uppercase">Staff Registry</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Staff Members
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage teachers and school staff information.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/staffs/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all hover:bg-primary/90"
                    >
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add New Staff
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <Users class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Total Staff
                            </p>
                            <h3 class="text-2xl font-bold text-foreground">
                                {{ stats.total }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <UserCheck class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Active Now
                            </p>
                            <h3 class="text-2xl font-bold text-foreground">
                                {{ stats.active }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                        >
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Academic
                            </p>
                            <h3 class="text-2xl font-bold text-foreground">
                                {{ stats.teaching }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                        >
                            <Briefcase class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Admin/Support
                            </p>
                            <h3 class="text-2xl font-bold text-foreground">
                                {{ stats.admins + stats.non_teaching }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div
                class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-border/50 bg-card/60 p-4 shadow-sm ring-1 ring-black/[0.02] backdrop-blur-sm md:flex-row"
            >
                <div class="flex w-full flex-1 items-center gap-4">
                    <div class="relative max-w-sm flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by name or email..."
                            class="h-10 rounded-xl border-border bg-background pl-10 text-sm shadow-sm transition-all focus:ring-2 focus:ring-primary/10"
                        />
                    </div>

                    <Select v-model="roleFilter">
                        <SelectTrigger
                            class="h-10 w-[180px] rounded-xl border-border bg-background font-medium shadow-sm focus:ring-2 focus:ring-primary/10"
                        >
                            <SelectValue placeholder="All Roles" />
                        </SelectTrigger>
                        <SelectContent
                            class="rounded-xl border-border shadow-xl"
                        >
                            <SelectItem value="all">All Roles</SelectItem>
                            <SelectItem
                                v-for="r in roles"
                                :key="r.id"
                                :value="r.name"
                            >
                                {{ r.display_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="departmentFilter">
                        <SelectTrigger
                            class="h-10 w-[180px] rounded-xl border-border bg-background font-medium shadow-sm focus:ring-2 focus:ring-primary/10"
                        >
                            <SelectValue placeholder="Departments" />
                        </SelectTrigger>
                        <SelectContent
                            class="rounded-xl border-border shadow-xl"
                        >
                            <SelectItem value="all">All Depts</SelectItem>
                            <SelectItem
                                v-for="dept in departments"
                                :key="dept.id"
                                :value="dept.id.toString()"
                            >
                                {{ dept.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div
                    class="flex items-center gap-2 rounded-xl border border-border/50 bg-muted/30 p-1 shadow-inner"
                >
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-lg transition-all"
                        :class="
                            viewMode === 'grid'
                                ? 'bg-background text-foreground shadow-sm ring-1 ring-border/50'
                                : 'text-muted-foreground'
                        "
                        @click="viewMode = 'grid'"
                    >
                        <LayoutGrid class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-lg transition-all"
                        :class="
                            viewMode === 'list'
                                ? 'bg-background text-foreground shadow-sm ring-1 ring-border/50'
                                : 'text-muted-foreground'
                        "
                        @click="viewMode = 'list'"
                    >
                        <ListIcon class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Staff Grid -->
            <div
                v-if="viewMode === 'grid'"
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="teacher in teachers.data"
                    :key="teacher.id"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <Link
                        :href="`/staffs/${teacher.id}`"
                        class="flex flex-col items-center space-y-4 text-center"
                    >
                        <div class="relative">
                            <div
                                class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl bg-muted/30 shadow-inner ring-4 ring-background transition-transform duration-500 group-hover:scale-110"
                            >
                                <img
                                    v-if="teacher.photo_url"
                                    :src="teacher.photo_url"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="text-2xl font-bold text-muted-foreground/40"
                                >
                                    {{ teacher.first_name[0]
                                    }}{{ teacher.last_name[0] }}
                                </div>
                            </div>
                            <div
                                class="absolute -right-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-lg border-2 border-white shadow-sm"
                                :class="getStatusColor(teacher.status)"
                            >
                                <BadgeCheck
                                    v-if="teacher.status === 'active'"
                                    class="h-3 w-3 text-white"
                                />
                            </div>
                        </div>

                        <div class="w-full space-y-1">
                            <h4
                                class="truncate px-2 font-bold text-foreground transition-colors group-hover:text-primary"
                            >
                                {{ teacher.first_name }} {{ teacher.last_name }}
                            </h4>
                            <div class="flex items-center justify-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="h-5 rounded-lg border-none bg-primary/10 px-2 text-xs font-medium tracking-tight text-primary uppercase"
                                >
                                    {{
                                        teacher.department?.name || 'Unassigned'
                                    }}
                                </Badge>
                            </div>
                        </div>
                    </Link>

                    <div
                        class="mt-4 grid w-full grid-cols-2 gap-2 border-t border-slate-50 pt-4"
                    >
                        <Link
                            :href="`/staffs/${teacher.id}`"
                            class="flex h-10 items-center justify-center gap-2 rounded-xl bg-foreground text-xs font-bold text-background shadow-sm transition-all hover:bg-primary"
                        >
                            <Eye class="h-3.5 w-3.5" /> View
                        </Link>
                        <Link
                            :href="`/staffs/${teacher.id}/edit`"
                            class="flex h-10 items-center justify-center gap-2 rounded-xl border border-border bg-muted/30 text-xs font-bold text-muted-foreground transition-all hover:bg-background hover:text-primary hover:shadow-sm"
                        >
                            <Edit class="h-3.5 w-3.5" /> Edit
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Staff Table (List View) -->
            <div
                v-else
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-border/50 bg-muted/5">
                            <th
                                class="p-6 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                            >
                                Personnel
                            </th>
                            <th
                                class="p-6 text-center text-xs font-medium tracking-tight text-slate-500 uppercase"
                            >
                                Department
                            </th>
                            <th
                                class="p-6 text-center text-xs font-medium tracking-tight text-slate-500 uppercase"
                            >
                                Roles
                            </th>
                            <th
                                class="p-6 text-center text-xs font-medium tracking-tight text-slate-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="p-6 text-right text-xs font-medium tracking-tight text-slate-500 uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr
                            v-for="teacher in teachers.data"
                            :key="teacher.id"
                            @click="router.visit(`/staffs/${teacher.id}`)"
                            class="group cursor-pointer transition-colors hover:bg-slate-50/40"
                        >
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-muted/30 text-sm font-bold text-muted-foreground/40 shadow-inner transition-all group-hover:bg-primary group-hover:text-white"
                                    >
                                        <img
                                            v-if="teacher.photo_url"
                                            :src="teacher.photo_url"
                                            class="h-full w-full object-cover"
                                        />
                                        <template v-else
                                            >{{ teacher.first_name[0]
                                            }}{{
                                                teacher.last_name[0]
                                            }}</template
                                        >
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="leading-none font-bold text-foreground transition-colors group-hover:text-primary"
                                        >
                                            {{ teacher.first_name }}
                                            {{ teacher.last_name }}
                                        </p>
                                        <div
                                            class="flex items-center gap-2 opacity-60"
                                        >
                                            <Mail class="h-3 w-3" />
                                            <p
                                                class="text-xs font-bold text-slate-500"
                                            >
                                                {{
                                                    teacher.user?.email || 'N/A'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <Badge
                                    variant="outline"
                                    class="rounded-lg border-slate-100 px-3 py-1 font-bold"
                                >
                                    {{ teacher.department?.name || 'N/A' }}
                                </Badge>
                            </td>
                            <td class="p-6 text-center">
                                <div
                                    class="flex flex-wrap items-center justify-center gap-1.5"
                                >
                                    <Badge
                                        v-for="role in teacher.user?.roles"
                                        :key="role.id"
                                        variant="secondary"
                                        class="rounded border-none bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary uppercase"
                                    >
                                        {{ role.name.replace('_', ' ') }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <div
                                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium tracking-tight uppercase"
                                    :class="getStatusColor(teacher.status)"
                                >
                                    <span
                                        class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"
                                    ></span>
                                    {{ teacher.status }}
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 min-w-10 rounded-xl transition-all hover:bg-white hover:text-blue-600 hover:shadow-lg"
                                        as-child
                                    >
                                        <Link :href="`/staffs/${teacher.id}`">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 min-w-10 rounded-xl transition-all hover:bg-white hover:text-blue-600 hover:shadow-lg"
                                        as-child
                                    >
                                        <Link
                                            :href="`/staffs/${teacher.id}/edit`"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-10 w-10 min-w-10 rounded-xl transition-all hover:bg-white"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="min-w-[180px] rounded-2xl border-slate-200 p-2 shadow-xl"
                                        >
                                            <DropdownMenuItem
                                                @click="deleteStaff(teacher.id)"
                                                class="cursor-pointer rounded-xl p-3 text-rose-600 focus:bg-rose-50 focus:text-rose-600"
                                            >
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Delete Account
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div
                v-if="teachers.data.length === 0"
                class="flex flex-col items-center justify-center space-y-4 rounded-2xl border-2 border-dashed border-border/60 bg-muted/10 py-32"
            >
                <div
                    class="flex h-24 w-24 -rotate-6 transform items-center justify-center rounded-xl border-8 border-background bg-muted/50 font-bold text-muted-foreground/20 shadow-xl"
                >
                    ?
                </div>
                <div class="space-y-2 text-center">
                    <p class="text-xl font-bold text-foreground">
                        No members found
                    </p>
                    <p class="text-sm font-medium text-muted-foreground">
                        Try adjusting your filters or search keywords.
                    </p>
                </div>
                <Button
                    variant="outline"
                    class="mt-6 h-12 rounded-xl px-8 font-medium tracking-tight uppercase"
                    @click="
                        searchQuery = '';
                        statusFilter = 'all';
                        departmentFilter = 'all';
                        roleFilter = 'all';
                    "
                >
                    Clear Filters
                </Button>
            </div>

            <!-- Pagination -->
            <div
                v-if="teachers.last_page > 1"
                class="flex items-center justify-between border-t border-border px-4 pt-8"
            >
                <div
                    class="pl-2 text-sm font-bold tracking-tight text-muted-foreground uppercase"
                >
                    Showing {{ teachers.from }} - {{ teachers.to }} of
                    {{ teachers.total }} personnel
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-12 w-12 rounded-2xl border-slate-200 shadow-sm"
                        :disabled="!teachers.prev_page_url"
                        as-child
                    >
                        <Link
                            :href="teachers.prev_page_url || '#'"
                            :preserve-state="true"
                        >
                            <ChevronDown class="h-5 w-5 rotate-90" />
                        </Link>
                    </Button>
                    <div
                        class="flex items-center gap-1.5 px-4 text-sm font-bold"
                    >
                        Page
                        <span class="text-blue-600">{{
                            teachers.current_page
                        }}</span>
                        of {{ teachers.last_page }}
                    </div>
                    <Button
                        variant="outline"
                        class="h-12 w-12 rounded-2xl border-slate-200 shadow-sm"
                        :disabled="!teachers.next_page_url"
                        as-child
                    >
                        <Link
                            :href="teachers.next_page_url || '#'"
                            :preserve-state="true"
                        >
                            <ChevronDown class="h-5 w-5 -rotate-90" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
