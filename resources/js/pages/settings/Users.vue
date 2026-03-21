<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, Shield, ShieldCheck, Mail } from 'lucide-vue-next';
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
    { id: 1, name: 'Admin User', email: 'admin@cbc.edu', role: 'Super Admin', status: 'active', last_login: '2026-03-21 14:30' },
    { id: 2, name: 'Head Teacher', email: 'head@cbc.edu', role: 'Manager', status: 'active', last_login: '2026-03-21 09:12' },
    { id: 3, name: 'Main Librarian', email: 'library@cbc.edu', role: 'Staff', status: 'inactive', last_login: '2026-02-15 11:45' },
]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'Users & Roles', href: '/settings/users' },
];

const getRoleColor = (role: string) => {
    switch (role?.toLowerCase()) {
        case 'super admin': return 'bg-violet-600 text-white';
        case 'manager': return 'bg-blue-600 text-white';
        case 'teacher': return 'bg-emerald-600 text-white';
        default: return 'bg-slate-500 text-white';
    }
};

</script>

<template>
    <Head title="Users & Permissions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <Users class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Users & Roles</h1>
                        <p class="text-muted-foreground">Manage school staff accounts, permissions and system access levels</p>
                    </div>
                </div>
                <div class="flex gap-2">
                     <Button variant="outline" class="font-pulsar"><Shield class="mr-2 h-4 w-4" />Manage Permissions</Button>
                     <Button class="bg-violet-600 hover:bg-violet-700 font-pulsar"><Plus class="mr-2 h-4 w-4" />Invite User</Button>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input placeholder="Search name or email..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10 font-pulsar">
                    <Filter class="mr-2 h-4 w-4" />Roles
                </Button>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b font-pulsar">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">User</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Role</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Last Activity</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="user in users" :key="user.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-violet-600 flex items-center justify-center font-black text-white text-[10px]">
                                         {{ user.name[0].toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ user.name }}</div>
                                        <div class="text-xs text-slate-400">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :class="getRoleColor(user.role)" class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase border-0 tracking-widest">
                                    {{ user.role }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <div class="h-1.5 w-1.5 rounded-full" :class="user.status === 'active' ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300'"></div>
                                    <span class="text-xs font-medium capitalize" :class="user.status === 'active' ? 'text-emerald-700' : 'text-slate-500'">{{ user.status }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs font-medium">
                                {{ user.last_login }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40 font-pulsar">
                                        <DropdownMenuItem><ShieldCheck class="mr-2 h-4 w-4" />Edit Permissions</DropdownMenuItem>
                                        <DropdownMenuItem><Mail class="mr-2 h-4 w-4" />Resend Invite</DropdownMenuItem>
                                        <DropdownMenuItem class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" />Deactivate</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 p-6 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl border border-slate-700">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center">
                         <Shield class="h-6 w-6 text-violet-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">RBAC Hardening Active</h3>
                        <p class="text-slate-400 text-sm">System is enforce Role-Based Access Control on all modules.</p>
                    </div>
                </div>
                <Button variant="outline" class="bg-white/5 border-white/10 text-white hover:bg-white/20 font-bold">Audit Access Logs</Button>
            </div>
        </div>
    </AppLayout>
</template>
