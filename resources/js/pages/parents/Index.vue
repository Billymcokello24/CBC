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
    Heart,
    Home,
    MapPin,
    PhoneForwarded,
    ShieldCheck,
    Edit,
    Trash2,
    ChevronRight,
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

const props = defineProps<{
    parents: {
        data: any[];
        total: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: {
        search?: string;
    };
    stats: {
        total: number;
        active: number;
        new_this_month: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parent Registry', href: '/parents' },
];

const searchQuery = ref(props.filters.search || '');
const viewMode = ref('grid');

let debounceTimer: any;
const applyFilters = () => {
    router.get(
        '/parents',
        {
            search: searchQuery.value,
        },
        { preserveState: true, replace: true },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 400);
});

const getStatusColor = (active: boolean) => {
    return active
        ? 'bg-emerald-500 text-white shadow-sm'
        : 'bg-slate-400 text-white';
};

const deleteParent = (id: number) => {
    if (
        confirm(
            'Are you sure you want to delete this parent account? This will unlink all students.',
        )
    ) {
        router.delete(`/parents/${id}`);
    }
};
</script>

<template>
    <Head title="Parent Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">People</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Parents</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Family Registry
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage parents and legal guardians associated with your students.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'"
                    >
                        <component
                            :is="viewMode === 'grid' ? ListIcon : LayoutGrid"
                            class="mr-2 h-4 w-4 opacity-70"
                        />
                        {{ viewMode === 'grid' ? 'Table View' : 'Grid View' }}
                    </Button>
                    <Link
                        href="/parents/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] hover:bg-primary/90 active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add Parent
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-3 sm:gap-6">
                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Total Parents</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total.toLocaleString() }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Registered Guardians</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-emerald-200 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                            <ShieldCheck class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Active Now</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active.toLocaleString() }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Accounts Active</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <UserPlus class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Growth</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">+{{ stats.new_this_month }}</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">This Month Only</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row">
                <div class="group relative w-full md:max-w-md">
                    <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name, phone or email..."
                        class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold tracking-tight uppercase transition-all focus:bg-background"
                    />
                </div>
                
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                    >
                        <Filter class="mr-2 h-3.5 w-3.5 opacity-70" />
                        Refine Search
                    </Button>
                </div>
            </div>

            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid' && parents.total > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 px-1"
            >
                <div
                    v-for="parent in parents.data"
                    :key="parent.id"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:scale-[1.02] hover:border-primary/20 hover:shadow-xl"
                >
                    <div class="mb-6 flex items-start justify-between">
                        <div class="relative">
                            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-primary/10 transition-transform group-hover:scale-110">
                                <img
                                    v-if="parent.photo"
                                    :src="parent.photo"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="text-xl font-bold text-primary"
                                >
                                    {{ parent.first_name[0] }}{{ parent.last_name[0] }}
                                </div>
                            </div>
                            <div 
                                class="absolute -right-1 -top-1 h-3.5 w-3.5 rounded-full border-2 border-card bg-emerald-500"
                                v-if="parent.is_active"
                            ></div>
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg opacity-40 hover:opacity-100">
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-52 rounded-xl border-border p-2 shadow-xl">
                                <DropdownMenuItem as-child class="rounded-lg py-2.5 text-xs font-bold">
                                    <Link :href="`/parents/${parent.id}`" class="flex w-full items-center">
                                        <Eye class="mr-3 h-4 w-4 opacity-60" /> View Detailed
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child class="rounded-lg py-2.5 text-xs font-bold text-primary">
                                    <Link :href="`/parents/${parent.id}/edit`" class="flex w-full items-center">
                                        <Edit class="mr-3 h-4 w-4 opacity-60" /> Edit Record
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="my-1" />
                                <DropdownMenuItem @click="deleteParent(parent.id)" class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                    <Trash2 class="mr-3 h-4 w-4 opacity-60" /> Purge Account
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="line-clamp-1 text-base font-bold text-foreground">
                                {{ parent.first_name }} {{ parent.last_name }}
                            </h3>
                            <div class="mt-1 flex items-center gap-2 text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">
                                ID: #{{ parent.id_number || parent.id.toString().padStart(4, '0') }}
                            </div>
                        </div>

                        <div class="flex flex-col space-y-2 border-t border-border/50 pt-4">
                            <div class="flex items-center gap-2 text-xs font-bold text-foreground/80">
                                <Phone class="h-3.5 w-3.5 text-primary opacity-60" />
                                {{ parent.phone }}
                            </div>
                            <div v-if="parent.email" class="flex max-w-full items-center gap-2 truncate text-[10px] font-bold text-muted-foreground uppercase opacity-60">
                                <Mail class="h-3.5 w-3.5" />
                                {{ parent.email }}
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 pt-2">
                             <Badge
                                v-for="student in parent.students"
                                :key="student.id"
                                variant="outline"
                                class="rounded-lg border-none bg-primary/5 px-2 py-0.5 text-[10px] font-bold text-primary uppercase"
                            >
                                {{ student.first_name }}
                            </Badge>
                            <span v-if="!parent.students?.length" class="text-[10px] font-bold text-muted-foreground/30 uppercase">No students</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div
                v-else-if="viewMode === 'list' && parents.total > 0"
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-muted-foreground">
                                <th class="px-6 py-5 text-xs font-bold tracking-tight uppercase">Parent Identity</th>
                                <th class="px-6 py-5 text-xs font-bold tracking-tight uppercase">Children</th>
                                <th class="px-6 py-5 text-xs font-bold tracking-tight uppercase">Contact info</th>
                                <th class="px-6 py-5 text-center text-xs font-bold tracking-tight uppercase">Account</th>
                                <th class="px-6 py-5 text-right text-xs font-bold tracking-tight uppercase px-8">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="parent in parents.data"
                                :key="parent.id"
                                class="group transition-all hover:bg-muted/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 overflow-hidden rounded-xl bg-primary/10 text-xs font-bold text-primary transition-all group-hover:scale-110">
                                            <img
                                                v-if="parent.photo"
                                                :src="parent.photo"
                                                class="h-full w-full object-cover"
                                            />
                                            <div v-else class="flex h-full w-full items-center justify-center uppercase">
                                                {{ parent.first_name[0] }}{{ parent.last_name[0] }}
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-foreground">
                                                {{ parent.first_name }} {{ parent.last_name }}
                                            </p>
                                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground uppercase opacity-40">
                                                ID: {{ parent.id_number || 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="student in parent.students"
                                            :key="student.id"
                                            class="rounded-lg border-none bg-primary/5 px-2 py-0.5 text-[10px] font-bold text-primary uppercase"
                                        >
                                            {{ student.first_name }} {{ student.last_name }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center gap-2 text-xs font-bold text-foreground">
                                            <Phone class="h-3.5 w-3.5 text-primary opacity-60" />
                                            {{ parent.phone }}
                                        </div>
                                        <div v-if="parent.email" class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">
                                            {{ parent.email }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="border-none px-3 py-1 text-[10px] font-bold tracking-tight uppercase"
                                        :class="parent.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-500'"
                                    >
                                        {{ parent.is_active ? 'Active' : 'Locked' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right px-8">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/parents/${parent.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            as-child
                                        >
                                            <Link :href="`/parents/${parent.id}/edit`"><Edit class="h-4 w-4" /></Link>
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
                                                <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold">
                                                    <BadgeCheck class="mr-3 h-4 w-4 opacity-60" />
                                                    Verify Account
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1" />
                                                <DropdownMenuItem @click="deleteParent(parent.id)" class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                                    <Trash2 class="mr-3 h-4 w-4 opacity-60" />
                                                    Purge Record
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
                v-if="parents.total === 0"
                class="flex flex-col items-center justify-center space-y-6 rounded-3xl border border-dashed border-border/80 bg-muted/5 py-40"
            >
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-2xl bg-muted/20 text-muted-foreground/20"
                >
                    <Users class="h-10 w-10" />
                </div>
                <div class="space-y-2 text-center">
                    <h3 class="text-xl font-bold text-foreground">
                        No Parents Records
                    </h3>
                    <p class="mx-auto max-w-sm text-sm font-medium text-muted-foreground/60">
                        We couldn't find any parent entries matching your criteria. Try adjusting your search queries.
                    </p>
                </div>
                <Button
                    variant="outline"
                    class="h-11 rounded-xl px-8 text-xs font-bold tracking-tight uppercase"
                    @click="searchQuery = ''"
                >
                    Reset Filter
                </Button>
            </div>

            <!-- Pagination -->
            <div
                v-if="parents.last_page > 1"
                class="flex flex-col items-center justify-between gap-4 border-t border-border/50 bg-muted/5 px-6 py-6 md:flex-row shadow-sm rounded-2xl"
            >
                <p class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase">
                    Showing {{ parents.from }} to {{ parents.to }} of {{ parents.total }} family entries
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-9 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted"
                        :disabled="parents.current_page <= 1"
                        @click="router.get(parents.prev_page_url || '')"
                    >
                        Previous
                    </Button>
                    <div
                        class="flex h-9 min-w-[36px] items-center justify-center rounded-xl bg-primary text-xs font-bold text-white shadow-lg shadow-primary/20"
                    >
                        {{ parents.current_page }} / {{ parents.last_page }}
                    </div>
                    <Button
                        variant="outline"
                        class="h-9 rounded-xl border-border px-4 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted"
                        :disabled="parents.current_page >= parents.last_page"
                        @click="router.get(parents.next_page_url || '')"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
