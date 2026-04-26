<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Users,
    Search,
    Filter,
    Mail,
    Phone,
    Building2,
    UserCheck,
    BadgeCheck,
    ArrowRight,
    LayoutGrid,
    List as ListIcon,
    ChevronDown,
    GraduationCap,
    Briefcase,
    Eye,
    Plus,
    MoreHorizontal,
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
    role: {
        id: number;
        name: string;
        display_name: string;
    };
    staffs: any;
    departments: any[];
    filters: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    {
        title: props.role.display_name,
        href: `/staffs/directory/${props.role.name}`,
    },
];

const searchQuery = ref(props.filters?.search || '');
const departmentFilter = ref(props.filters?.department_id ?? 'all');
const viewMode = ref(props.filters?.view || 'grid');

const handleSearch = () => {
    router.get(
        `/staffs/directory/${props.role.name}`,
        {
            search: searchQuery.value,
            department_id: departmentFilter.value,
            view: viewMode.value,
        },
        { preserveState: true, replace: true },
    );
};

watch([searchQuery, departmentFilter, viewMode], () => {
    handleSearch();
});

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white';
        case 'on_leave':
            return 'bg-amber-500 text-white';
        default:
            return 'bg-slate-400 text-white';
    }
};
</script>

<template>
    <Head :title="role.display_name + ' Directory'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-8 p-6 pb-20 duration-700 fade-in slide-in-from-bottom-4 md:p-8"
        >
            <!-- Standard Header -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Badge
                            variant="secondary"
                            class="rounded-lg border-blue-100 bg-blue-50 px-2 text-xs font-bold tracking-wider text-blue-700 uppercase hover:bg-blue-50"
                        >
                            {{ role.display_name }}
                        </Badge>
                        <h1
                            class="text-3xl font-bold tracking-tight text-foreground"
                        >
                            Personnel Registry
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Managing {{ staffs.total }} users categorized as
                        {{ role.display_name }}.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="`/staffs/create?role=${role.name}`"
                        class="font-inter inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add {{ role.display_name }}
                    </Link>
                    <div
                        class="flex rounded-xl border border-border bg-muted/50 p-1"
                    >
                        <button
                            @click="viewMode = 'grid'"
                            class="rounded-lg p-2 transition-all"
                            :class="
                                viewMode === 'grid'
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            class="rounded-lg p-2 transition-all"
                            :class="
                                viewMode === 'list'
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                        >
                            <ListIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Toolbar - Integrated -->
            <div
                class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row"
            >
                <div class="relative w-full flex-1 md:max-w-md">
                    <Search
                        class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name or number..."
                        class="h-11 rounded-xl border-none bg-muted/30 pl-11 text-sm focus:ring-2 focus:ring-blue-500/20"
                    />
                </div>

                <div class="flex w-full items-center gap-3 md:w-auto">
                    <Select v-model="departmentFilter">
                        <SelectTrigger
                            class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium md:min-w-[200px]"
                        >
                            <SelectValue placeholder="All Departments" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl">
                            <SelectItem value="all">All Departments</SelectItem>
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
            </div>

            <!-- Grid Listing -->
            <div
                v-if="viewMode === 'grid' && staffs.total > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="item in staffs.data"
                    :key="item.id"
                    class="group relative flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:border-blue-500/30 hover:shadow-md"
                >
                    <div
                        class="flex flex-col items-center space-y-4 text-center"
                    >
                        <div class="relative">
                            <div
                                class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl bg-muted ring-4 ring-muted"
                            >
                                <img
                                    v-if="item.photo_url"
                                    :src="item.photo_url"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="text-2xl font-bold text-blue-600/40 uppercase"
                                >
                                    {{ item.first_name[0]
                                    }}{{ item.last_name[0] }}
                                </div>
                            </div>
                            <div
                                class="absolute -right-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-lg border border-border bg-white shadow-md"
                            >
                                <div
                                    class="h-2.5 w-2.5 rounded-full"
                                    :class="
                                        item.status === 'active'
                                            ? 'bg-emerald-500'
                                            : 'bg-amber-500'
                                    "
                                ></div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <h3
                                class="line-clamp-1 text-lg font-bold text-foreground transition-colors group-hover:text-blue-600"
                            >
                                {{ item.first_name }} {{ item.last_name }}
                            </h3>
                            <p
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                {{ item.staff_number }}
                            </p>
                        </div>

                        <div
                            class="flex w-full flex-col gap-3 border-t border-border pt-4"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="max-w-full truncate rounded-md border-none bg-muted/30 px-2 py-0.5 text-xs font-bold text-muted-foreground uppercase"
                                >
                                    {{
                                        item.department?.name || 'Institutional'
                                    }}
                                </Badge>
                            </div>

                            <Button
                                as-child
                                variant="outline"
                                class="h-10 w-full rounded-xl text-xs font-semibold transition-all hover:bg-blue-600 hover:text-white"
                            >
                                <Link :href="`/staffs/${item.id}`"
                                    >View Profile</Link
                                >
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Listing -->
            <div
                v-else-if="viewMode === 'list' && staffs.total > 0"
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b bg-muted/30">
                            <th
                                class="p-6 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                User Information
                            </th>
                            <th
                                class="p-6 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Department
                            </th>
                            <th
                                class="p-6 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Status
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
                            v-for="item in staffs.data"
                            :key="item.id"
                            class="group transition-all hover:bg-muted/20"
                        >
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted"
                                    >
                                        <img
                                            v-if="item.photo_url"
                                            :src="item.photo_url"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="text-sm font-bold text-blue-600/40 uppercase"
                                        >
                                            {{ item.first_name[0]
                                            }}{{ item.last_name[0] }}
                                        </div>
                                    </div>
                                    <div>
                                        <p
                                            class="leading-tight font-bold text-foreground"
                                        >
                                            {{ item.first_name }}
                                            {{ item.last_name }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >
                                            {{ item.staff_number }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <Badge
                                    variant="outline"
                                    class="rounded-lg border-blue-100 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 uppercase"
                                >
                                    {{
                                        item.department?.name || 'Institutional'
                                    }}
                                </Badge>
                            </td>
                            <td class="p-6 text-center">
                                <div class="flex items-center justify-center">
                                    <Badge
                                        :class="getStatusColor(item.status)"
                                        class="rounded-lg border-none px-3 py-1 text-xs font-bold capitalize uppercase shadow-sm"
                                    >
                                        {{ item.status }}
                                    </Badge>
                                </div>
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
                                        class="rounded-xl"
                                    >
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/staffs/${item.id}`"
                                                class="flex items-center gap-2"
                                            >
                                                <Eye class="h-4 w-4" /> View
                                                Profile
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/staffs/${item.id}/edit`"
                                                class="flex items-center gap-2 text-blue-600"
                                            >
                                                <Edit class="h-4 w-4" /> Edit
                                                User
                                            </Link>
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
                v-else
                class="flex flex-col items-center justify-center space-y-4 rounded-3xl border border-dashed bg-muted/10 py-32"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted"
                >
                    <Users class="h-8 w-8 text-muted-foreground/30" />
                </div>
                <div class="space-y-2 text-center">
                    <h3 class="text-xl leading-tight font-bold text-foreground">
                        No Results Found
                    </h3>
                    <p class="mx-auto max-w-xs text-sm text-muted-foreground">
                        We couldn't find any users in this category matching
                        your criteria.
                    </p>
                </div>
                <Button
                    variant="outline"
                    class="mt-4 rounded-xl"
                    @click="
                        searchQuery = '';
                        departmentFilter = 'all';
                    "
                >
                    Reset Filters
                </Button>
            </div>

            <!-- Pagination -->
            <div
                v-if="staffs.last_page > 1"
                class="flex items-center justify-between border-t border-border pt-8"
            >
                <p class="text-xs font-semibold text-muted-foreground">
                    Showing {{ staffs.from }} to {{ staffs.to }} of
                    {{ staffs.total }} users
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-9 rounded-xl px-4 text-xs font-bold"
                        :disabled="staffs.current_page <= 1"
                        @click="router.get(staffs.prev_page_url)"
                    >
                        Previous
                    </Button>
                    <div
                        class="flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white shadow-sm"
                    >
                        {{ staffs.current_page }}
                    </div>
                    <Button
                        variant="outline"
                        class="h-9 rounded-xl px-4 text-xs font-bold"
                        :disabled="staffs.current_page >= staffs.last_page"
                        @click="router.get(staffs.next_page_url)"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
