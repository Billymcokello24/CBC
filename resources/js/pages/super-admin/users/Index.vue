<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Fingerprint,
    Search,
    Filter,
    MoreHorizontal,
    ShieldAlert,
    Key,
    UserX,
    UserCheck,
    ExternalLink,
    Mail,
    Phone,
    Building2,
    Calendar,
    ChevronDown,
    ArrowUpDown,
    Check,
    Home,
    ChevronRight,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';
import debounce from 'lodash/debounce';

interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    status: 'active' | 'inactive';
    created_at: string;
    school?: { id: number; name: string };
    roles: Array<{ name: string }>;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    schools: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        school_id?: string | number;
        status?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Identity Registry', href: '/super-admin/users' },
];

const search = ref(props.filters.search || '');
const schoolFilter = ref(props.filters.school_id?.toString() || 'all');
const statusFilter = ref(props.filters.status || 'all');

const isResetOpen = ref(false);
const resettingUser = ref<User | null>(null);
const resetForm = useForm({
    password: '',
    password_confirmation: '',
});

const openReset = (user: User) => {
    resettingUser.value = user;
    resetForm.reset();
    isResetOpen.value = true;
};

const submitReset = () => {
    if (!resettingUser.value) return;
    resetForm.put(
        route('super-admin.users.reset-password', resettingUser.value.id),
        {
            onSuccess: () => {
                isResetOpen.value = false;
            },
        },
    );
};

const toggleStatus = (user: User) => {
    if (
        confirm(
            `Change status for ${user.name} to ${user.status === 'active' ? 'INACTIVE' : 'ACTIVE'}?`,
        )
    ) {
        router.post(route('super-admin.users.toggle-status', user.id));
    }
};

const handleSearch = debounce(() => {
    router.get(
        route('super-admin.users.index'),
        {
            search: search.value,
            school_id: schoolFilter.value === 'all' ? null : schoolFilter.value,
            status: statusFilter.value === 'all' ? null : statusFilter.value,
        },
        { preserveState: true, replace: true },
    );
}, 500);

watch([schoolFilter, statusFilter], () => {
    handleSearch();
});
</script>

<template>
    <Head title="Identity Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Global Dashboard</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Identity Registry</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Users
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage platform users and access levels.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="rounded-xl border border-border/50 bg-muted/50 px-4 py-2 text-xs font-medium tracking-tight text-muted-foreground uppercase dark:border-white/5"
                    >
                        {{ users.total.toLocaleString() }} Total Users
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div
                    class="flex flex-col items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row lg:col-span-12 dark:border-white/5"
                >
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/40"
                        />
                        <Input
                            v-model="search"
                            @input="handleSearch"
                            placeholder="Search by name or email..."
                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold focus:ring-primary/20"
                        />
                    </div>

                    <Select v-model="schoolFilter">
                        <SelectTrigger
                            class="h-11 w-full rounded-xl border-border bg-muted/20 font-bold md:w-64"
                        >
                            <SelectValue placeholder="All Schools" />
                        </SelectTrigger>
                        <SelectContent
                            class="rounded-xl border-border shadow-xl"
                        >
                            <SelectItem value="all">All Schools</SelectItem>
                            <SelectItem
                                v-for="school in schools"
                                :key="school.id"
                                :value="school.id.toString()"
                            >
                                {{ school.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="statusFilter">
                        <SelectTrigger
                            class="h-11 w-full rounded-xl border-border bg-muted/20 font-bold md:w-44"
                        >
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent
                            class="rounded-xl border-border shadow-xl"
                        >
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Table -->
            <div class="flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5">
                                <th class="px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    User Details
                                </th>
                                <th class="px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    School
                                </th>
                                <th class="px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    Roles
                                </th>
                                <th class="px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    Status
                                </th>
                                <th class="px-5 py-4 sm:px-6 text-right text-xs font-bold tracking-tight text-foreground uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="group transition-all hover:bg-muted/30"
                            >
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-sm font-bold text-primary uppercase"
                                        >
                                            {{ user.name[0] }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold tracking-tight text-foreground"
                                                >{{ user.name }}</span
                                            >
                                            <span
                                                class="text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                                                >{{ user.email }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <div
                                        class="flex items-center gap-2"
                                        v-if="user.school"
                                    >
                                        <div
                                            class="flex h-2 w-2 items-center justify-center rounded-full bg-blue-500/20"
                                        >
                                            <div
                                                class="h-1 w-1 rounded-full bg-blue-500"
                                            ></div>
                                        </div>
                                        <span
                                            class="text-sm font-bold tracking-tight text-foreground uppercase"
                                            >{{ user.school.name }}</span
                                        >
                                    </div>
                                    <span
                                        v-else
                                        class="text-xs font-medium tracking-tight text-muted-foreground/40 uppercase"
                                        >System User</span
                                    >
                                </td>
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role.name"
                                            class="pointer-events-none border-primary/10 bg-primary/5 px-2 py-0.5 text-xs font-bold tracking-tight text-primary uppercase"
                                        >
                                            {{ role.name.replace('_', ' ') }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <div
                                        :class="[
                                            'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium tracking-tight uppercase',
                                            user.status === 'active'
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'h-1 w-1 rounded-full',
                                                user.status === 'active'
                                                    ? 'animate-pulse bg-emerald-500'
                                                    : 'bg-rose-500',
                                            ]"
                                        ></div>
                                        {{ user.status }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right sm:px-6 sm:py-4">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="h-8 w-8 rounded-lg p-0 transition-colors hover:bg-muted/50"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4 text-muted-foreground/40"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="w-56 rounded-xl border-border bg-card p-2 shadow-xl"
                                        >
                                            <DropdownMenuLabel
                                                class="px-2 py-2 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                                >Options</DropdownMenuLabel
                                            >

                                            <DropdownMenuItem
                                                @click="openReset(user)"
                                                class="flex cursor-pointer items-center rounded-lg px-2 py-2.5 text-xs font-bold transition-colors hover:bg-primary/5"
                                            >
                                                <Key
                                                    class="mr-2 h-4 w-4 text-muted-foreground/40"
                                                />
                                                Reset Password
                                            </DropdownMenuItem>

                                            <DropdownMenuItem
                                                @click="toggleStatus(user)"
                                                class="flex cursor-pointer items-center rounded-lg px-2 py-2.5 text-xs font-bold transition-colors"
                                                :class="
                                                    user.status === 'active'
                                                        ? 'hover:bg-rose-500/10 hover:text-rose-600'
                                                        : 'hover:bg-emerald-500/10 hover:text-emerald-600'
                                                "
                                            >
                                                <UserX
                                                    v-if="
                                                        user.status === 'active'
                                                    "
                                                    class="mr-2 h-4 w-4 text-muted-foreground/40"
                                                />
                                                <UserCheck
                                                    v-else
                                                    class="mr-2 h-4 w-4 text-muted-foreground/40"
                                                />
                                                {{
                                                    user.status === 'active'
                                                        ? 'Deactivate User'
                                                        : 'Activate User'
                                                }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex flex-col gap-4 border-t border-border/50 bg-muted/5 p-4 sm:flex-row sm:items-center sm:justify-between px-5 sm:px-6">
                    <p class="text-xs font-bold tracking-tight text-muted-foreground uppercase">
                        Page {{ users.current_page }} of {{ users.last_page }}
                    </p>
                    <div class="flex items-center gap-2">
                        <template
                            v-for="(link, index) in users.links"
                            :key="index"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="flex h-8 items-center justify-center rounded-lg border border-border bg-card px-3 text-xs font-medium tracking-tight uppercase transition-all hover:border-primary/20 hover:bg-primary/5 hover:text-primary"
                                :class="{
                                    'border-primary bg-primary text-white shadow-lg shadow-primary/20':
                                        link.active,
                                }"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="flex h-8 items-center justify-center rounded-lg border border-border/50 px-3 text-xs font-medium tracking-tight uppercase opacity-30 select-none"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Password Reset Modal -->
            <Dialog v-model:open="isResetOpen">
                <DialogContent
                    class="rounded-2xl border-border bg-card p-8 shadow-lg sm:max-w-md"
                >
                    <DialogHeader>
                        <DialogTitle
                            class="text-xl font-bold tracking-tight text-foreground"
                            >Reset Password</DialogTitle
                        >
                        <DialogDescription
                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                            >For user:
                            {{ resettingUser?.name }}</DialogDescription
                        >
                    </DialogHeader>
                    <form @submit.prevent="submitReset" class="mt-6 space-y-4">
                        <div class="space-y-2">
                            <label
                                class="text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                >New Password</label
                            >
                            <Input
                                v-model="resetForm.password"
                                type="password"
                                placeholder="Enter 8+ characters"
                                class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                required
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                >Confirm Password</label
                            >
                            <Input
                                v-model="resetForm.password_confirmation"
                                type="password"
                                placeholder="Repeat new password"
                                class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                required
                            />
                        </div>
                        <DialogFooter class="flex flex-col gap-2 pt-4">
                            <Button
                                type="submit"
                                class="h-11 w-full rounded-xl bg-primary font-bold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                                :disabled="resetForm.processing"
                            >
                                {{
                                    resetForm.processing
                                        ? 'Saving...'
                                        : 'Update Password'
                                }}
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                @click="isResetOpen = false"
                                class="h-10 w-full rounded-xl text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >Cancel</Button
                            >
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.12em;
}
</style>
