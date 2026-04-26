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
            class="mx-auto max-w-[1600px] animate-in space-y-8 p-6 pb-20 duration-700 fade-in slide-in-from-bottom-4 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight text-foreground"
                    >
                        Parent Registry
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Manage primary guardians and institutional parents.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-border px-4 font-medium hover:bg-muted"
                        @click="
                            viewMode = viewMode === 'grid' ? 'list' : 'grid'
                        "
                    >
                        <component
                            :is="viewMode === 'grid' ? ListIcon : LayoutGrid"
                            class="mr-2 h-4 w-4 opacity-70"
                        />
                        {{ viewMode === 'grid' ? 'List View' : 'Grid View' }}
                    </Button>
                    <Link
                        href="/parents/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
                    >
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Parent
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            Total Parents
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            {{ stats.total.toLocaleString() }}
                        </h3>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"
                    >
                        <Users class="h-6 w-6" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            Active Accounts
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            {{ stats.active.toLocaleString() }}
                        </h3>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                    >
                        <ShieldCheck class="h-6 w-6" />
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <div>
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            New Registrations
                        </p>
                        <h3 class="text-2xl font-bold text-foreground">
                            +{{ stats.new_this_month }}
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            This Month
                        </p>
                    </div>
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50/50 text-blue-500"
                    >
                        <UserPlus class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <Search
                    class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Search by name, phone or email..."
                    class="h-11 rounded-xl border-border bg-card pl-11 text-sm focus:ring-2 focus:ring-indigo-600/10"
                />
            </div>

            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid' && parents.total > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="parent in parents.data"
                    :key="parent.id"
                    class="group relative flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:border-blue-500/30 hover:shadow-md"
                >
                    <div
                        class="flex flex-col items-center space-y-4 text-center"
                    >
                        <div class="relative">
                            <div
                                class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-muted ring-4 ring-muted"
                            >
                                <img
                                    v-if="parent.photo"
                                    :src="parent.photo"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="text-xl font-bold text-blue-600/40 uppercase"
                                >
                                    {{ parent.first_name[0]
                                    }}{{ parent.last_name[0] }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <h3
                                class="line-clamp-1 text-lg font-bold text-foreground transition-colors group-hover:text-blue-600"
                            >
                                {{ parent.first_name }} {{ parent.last_name }}
                            </h3>
                            <div
                                class="flex items-center justify-center gap-1.5 text-xs font-medium text-muted-foreground"
                            >
                                <Phone class="h-3 w-3" />
                                {{ parent.phone }}
                            </div>
                        </div>

                        <!-- Children Pills -->
                        <div
                            class="flex flex-wrap items-center justify-center gap-2 pt-2"
                        >
                            <Badge
                                v-for="student in parent.students"
                                :key="student.id"
                                variant="outline"
                                class="max-w-[120px] truncate rounded-lg border-blue-100 bg-blue-50/50 px-2 py-0.5 text-xs font-bold text-blue-700"
                            >
                                {{ student.first_name }}
                            </Badge>
                            <span
                                v-if="!parent.students?.length"
                                class="text-xs text-muted-foreground"
                                >No students linked</span
                            >
                        </div>

                        <div class="mt-auto w-full border-t border-border pt-4">
                            <Button
                                as-child
                                variant="link"
                                class="h-8 w-full p-0 text-xs font-bold text-blue-600 hover:text-blue-700"
                            >
                                <Link :href="`/parents/${parent.id}`">
                                    Full Directory Entry
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div
                v-else-if="viewMode === 'list' && parents.total > 0"
                class="animate-in overflow-hidden rounded-2xl border border-border bg-card shadow-sm duration-500 fade-in"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b bg-muted/30">
                            <th
                                class="p-6 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Parent Identity
                            </th>
                            <th
                                class="p-6 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Children / Dependents
                            </th>
                            <th
                                class="p-6 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Primary Contact
                            </th>
                            <th
                                class="p-6 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Account
                            </th>
                            <th
                                class="p-6 text-right text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="parent in parents.data"
                            :key="parent.id"
                            class="group transition-all hover:bg-muted/10"
                        >
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-muted"
                                    >
                                        <img
                                            v-if="parent.photo"
                                            :src="parent.photo"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="text-sm font-bold text-blue-600/40 uppercase"
                                        >
                                            {{ parent.first_name[0]
                                            }}{{ parent.last_name[0] }}
                                        </div>
                                    </div>
                                    <div>
                                        <p
                                            class="leading-tight font-bold text-foreground"
                                        >
                                            {{ parent.first_name }}
                                            {{ parent.last_name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >
                                            ID: {{ parent.id_number || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="student in parent.students"
                                        :key="student.id"
                                        class="border-blue-100 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 uppercase"
                                    >
                                        {{ student.first_name }}
                                        {{ student.last_name }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="space-y-1">
                                    <div
                                        class="flex items-center gap-2 text-sm font-medium text-foreground"
                                    >
                                        <Phone
                                            class="h-3.5 w-3.5 text-blue-500"
                                        />
                                        {{ parent.phone }}
                                    </div>
                                    <div
                                        class="flex max-w-[200px] items-center gap-2 truncate text-xs text-muted-foreground"
                                    >
                                        <Mail class="h-3 w-3" />
                                        {{ parent.email }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <Badge
                                    :class="
                                        parent.is_active
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-slate-400 text-white'
                                    "
                                    class="rounded-lg border-none px-3 py-1 text-xs font-bold uppercase"
                                >
                                    {{ parent.is_active ? 'Active' : 'Locked' }}
                                </Badge>
                            </td>
                            <td class="p-6 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg text-muted-foreground hover:text-foreground"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="rounded-xl border-border shadow-lg"
                                    >
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/parents/${parent.id}`"
                                                class="flex items-center gap-2"
                                            >
                                                <Eye class="h-4 w-4" /> View
                                                Profile
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/parents/${parent.id}/edit`"
                                                class="flex items-center gap-2 text-blue-600"
                                            >
                                                <Edit class="h-4 w-4" /> Edit
                                                Record
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="deleteParent(parent.id)"
                                            class="flex items-center gap-2 text-red-600"
                                        >
                                            <Trash2 class="h-4 w-4" /> Delete
                                            Account
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div
                v-if="parents.total === 0"
                class="flex flex-col items-center justify-center space-y-4 rounded-3xl border border-dashed border-border/60 bg-muted/10 py-32"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted"
                >
                    <Users class="h-8 w-8 text-muted-foreground/30" />
                </div>
                <div class="space-y-2 text-center">
                    <h3 class="text-xl font-bold text-foreground">
                        No Parents Found
                    </h3>
                    <p class="mx-auto max-w-xs text-sm text-muted-foreground">
                        Try adjusting your search or add a new parent to the
                        registry.
                    </p>
                </div>
                <Button
                    variant="outline"
                    class="mt-4 rounded-xl"
                    @click="searchQuery = ''"
                >
                    Reset Search
                </Button>
            </div>

            <!-- Pagination -->
            <div
                v-if="parents.last_page > 1"
                class="flex items-center justify-between border-t border-border pt-8"
            >
                <p class="text-xs font-semibold text-muted-foreground">
                    Showing {{ parents.from }} to {{ parents.to }} of
                    {{ parents.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-9 rounded-xl px-4 text-xs font-bold"
                        :disabled="parents.current_page <= 1"
                        @click="router.get(parents.prev_page_url || '')"
                    >
                        Previous
                    </Button>
                    <div
                        class="flex h-9 min-w-[36px] items-center justify-center rounded-xl bg-blue-600 text-xs font-bold text-white shadow-sm shadow-blue-200"
                    >
                        {{ parents.current_page }}
                    </div>
                    <Button
                        variant="outline"
                        class="h-9 rounded-xl px-4 text-xs font-bold"
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
