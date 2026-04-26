<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Shield,
    ShieldCheck,
    Mail,
} from 'lucide-vue-next';
import { ref } from 'vue';
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

// Mock users for UI demonstration
const users = ref([
    {
        id: 1,
        name: 'Admin User',
        email: 'admin@cbc.edu',
        role: 'Super Admin',
        status: 'active',
        last_login: '2026-03-21 14:30',
    },
    {
        id: 2,
        name: 'Head Teacher',
        email: 'head@cbc.edu',
        role: 'Manager',
        status: 'active',
        last_login: '2026-03-21 09:12',
    },
    {
        id: 3,
        name: 'Main Librarian',
        email: 'library@cbc.edu',
        role: 'Staff',
        status: 'inactive',
        last_login: '2026-02-15 11:45',
    },
]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'Users & Roles', href: '/settings/users' },
];

const getRoleColor = (role: string) => {
    switch (role?.toLowerCase()) {
        case 'super admin':
            return 'bg-violet-600 text-white';
        case 'manager':
            return 'bg-blue-600 text-white';
        case 'teacher':
            return 'bg-emerald-600 text-white';
        default:
            return 'bg-slate-500 text-white';
    }
};
</script>

<template>
    <Head title="Users & Permissions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <Users class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Users & Roles
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school staff accounts, permissions and system
                            access levels
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" class="font-pulsar"
                        ><Shield class="mr-2 h-4 w-4" />Manage
                        Permissions</Button
                    >
                    <Button
                        class="font-pulsar bg-violet-600 hover:bg-violet-700"
                        ><Plus class="mr-2 h-4 w-4" />Invite User</Button
                    >
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm"
            >
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        placeholder="Search name or email..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button variant="outline" size="sm" class="font-pulsar h-10">
                    <Filter class="mr-2 h-4 w-4" />Roles
                </Button>
            </div>

            <div
                class="overflow-hidden overflow-x-auto rounded-2xl border bg-card shadow-sm"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="font-pulsar border-b bg-slate-50">
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                User
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Role
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Last Activity
                            </th>
                            <th
                                class="px-6 py-4 text-right text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="group transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-600 text-xs font-bold text-white"
                                    >
                                        {{ user.name[0].toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ user.name }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge
                                    :class="getRoleColor(user.role)"
                                    class="rounded-full border-0 px-2 py-0.5 text-xs font-bold tracking-tight uppercase"
                                >
                                    {{ user.role }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <div
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="
                                            user.status === 'active'
                                                ? 'bg-emerald-500 shadow-sm'
                                                : 'bg-slate-300'
                                        "
                                    ></div>
                                    <span
                                        class="text-xs font-medium capitalize"
                                        :class="
                                            user.status === 'active'
                                                ? 'text-emerald-700'
                                                : 'text-slate-500'
                                        "
                                        >{{ user.status }}</span
                                    >
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 text-xs font-medium text-slate-500"
                            >
                                {{ user.last_login }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            ><MoreHorizontal class="h-4 w-4"
                                        /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="font-pulsar w-40"
                                    >
                                        <DropdownMenuItem
                                            ><ShieldCheck
                                                class="mr-2 h-4 w-4"
                                            />Edit Permissions</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            ><Mail class="mr-2 h-4 w-4" />Resend
                                            Invite</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            class="font-bold text-rose-600"
                                            ><Trash2
                                                class="mr-2 h-4 w-4"
                                            />Deactivate</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="mt-6 flex flex-col items-center justify-between gap-6 rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-800 to-slate-900 p-6 text-white shadow-xl md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10"
                    >
                        <Shield class="h-6 w-6 text-violet-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">RBAC Hardening Active</h3>
                        <p class="text-sm text-slate-400">
                            System is enforce Role-Based Access Control on all
                            modules.
                        </p>
                    </div>
                </div>
                <Button
                    variant="outline"
                    class="border-white/10 bg-white/5 font-bold text-white hover:bg-white/20"
                    >Audit Access Logs</Button
                >
            </div>
        </div>
    </AppLayout>
</template>
