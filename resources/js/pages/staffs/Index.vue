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
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Faculty Registry', href: '/staffs' },
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
    <Head title="Faculty Intelligence Matrix" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">Faculty matrix</h1>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Managing {{ stats.total }} Institutional Assets</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button as-child class="h-12 rounded-2xl bg-primary px-8 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <Link href="/staffs/create">
                            <UserPlus class="mr-2.5 h-4 w-4" />
                            Enroll faculty
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Matrix -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Total Personnel', val: stats.total, sub: 'Active Registry', icon: Users, color: 'text-primary' },
                    { label: 'On-Duty Assets', val: stats.active, sub: 'Operational Capacity', icon: UserCheck, color: 'text-emerald-500' },
                    { label: 'Teaching Nodes', val: stats.teaching, sub: 'Academic Engine', icon: GraduationCap, color: 'text-blue-500' },
                    { label: 'Administration', val: stats.admins + (stats.non_teaching || 0), sub: 'Strategic Oversight', icon: Briefcase, color: 'text-purple-500' }
                ]" :key="idx" class="group relative overflow-hidden rounded-3xl border border-border bg-card p-6 transition-all hover:border-primary/20">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-24 w-24" />
                    </div>
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight" :class="stat.color">{{ stat.val }}</h3>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase opacity-40">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- Faculty Intelligence Hub -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm">
                <div class="flex h-14 items-center justify-between border-b border-border/50 bg-muted/10 px-8">
                    <div class="flex items-center gap-3">
                        <SearchCode class="h-4 w-4 text-primary" />
                        <span class="text-[10px] font-bold tracking-widest text-foreground uppercase">Faculty Search Protocol</span>
                    </div>
                </div>
                <div class="p-8">
                     <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Personnel Identification</Label>
                            <div class="relative">
                                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                <Input v-model="searchQuery" placeholder="Name or Email..." class="h-12 rounded-xl border-border bg-muted/20 pl-11 pr-4 text-xs font-bold uppercase focus:bg-background" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Department Node</Label>
                            <div class="relative">
                                <select v-model="departmentFilter" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">Global Matrix</option>
                                    <option v-for="d in departments" :key="d.id" :value="String(d.id)">{{ d.name }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Role Designation</Label>
                            <div class="relative">
                                <select v-model="roleFilter" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">All Designations</option>
                                    <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Pulse Mode</Label>
                             <div class="relative">
                                <select v-model="statusFilter" class="h-12 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                    <option value="all">Global Status</option>
                                    <option value="active">Active</option>
                                    <option value="on_leave">External Leave</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faculty Matrix Data -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/10 text-[10px] font-bold tracking-widest text-muted-foreground uppercase">
                                <th class="px-8 py-5">Personnel Identity</th>
                                <th class="px-6 py-5">Strategic Node</th>
                                <th class="px-6 py-5">Designation</th>
                                <th class="px-6 py-5">Pulse status</th>
                                <th class="px-8 py-5 text-right font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="teacher in teachers.data" :key="teacher.id" class="group transition-all hover:bg-muted/30">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl border border-border bg-muted shadow-sm transition-transform group-hover:scale-105">
                                            <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-xs font-bold text-primary">{{ teacher.name.charAt(0).toUpperCase() }}</div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-xs font-bold tracking-tight text-foreground group-hover:text-primary transition-colors uppercase">{{ teacher.name }}</p>
                                            <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">{{ teacher.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <Badge variant="outline" class="rounded-lg border-primary/10 bg-primary/5 px-2 py-0.5 text-[9px] font-bold tracking-tight text-primary uppercase">
                                        {{ teacher.department_name || 'GENERAL' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-5 text-xs font-bold tracking-tight text-foreground uppercase opacity-70">{{ teacher.role || 'Personnel' }}</td>
                                <td class="px-6 py-5">
                                    <Badge :class="getStatusColor(teacher.status)" class="rounded-lg border-0 px-2.5 py-1 text-[9px] font-bold tracking-tight uppercase shadow-sm">
                                        {{ teacher.status }}
                                    </Badge>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm">
                                            <Link :href="`/staffs/${teacher.id}`"><ExternalLink class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary shadow-sm">
                                            <Link :href="`/staffs/${teacher.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-muted"><MoreHorizontal class="h-4 w-4 text-muted-foreground" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 rounded-2xl border-border bg-card p-2 shadow-2xl">
                                                <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-muted-foreground uppercase focus:bg-muted">
                                                    <Mail class="mr-3 h-4 w-4" />
                                                    Send Protocol
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1 border-border/50" />
                                                <DropdownMenuItem class="rounded-xl px-4 py-2.5 text-[10px] font-bold tracking-tight text-rose-600 uppercase focus:bg-rose-50" @click="() => router.delete(`/staffs/${teacher.id}`)">
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
                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Faculty Registry: Page {{ teachers.current_page }} of {{ teachers.last_page }}</p>
                    <div class="flex items-center gap-2">
                         <template v-for="(link, i) in teachers.links" :key="i">
                            <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-9 w-9 rounded-xl text-[10px] font-bold tracking-tight transition-all', link.active ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20' : 'border-border bg-card hover:bg-muted text-muted-foreground']" @click="router.get(link.url!)">{{ link.label }}</Button>
                            <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-9 rounded-xl px-4 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="router.get(link.url!)">Prev</Button>
                            <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-9 rounded-xl px-4 text-[10px] font-bold uppercase border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" @click="router.get(link.url!)">Next</Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
