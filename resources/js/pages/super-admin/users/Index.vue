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
    Check
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
} from "@/components/ui/dropdown-menu";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue 
} from "@/components/ui/select";
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
    resetForm.put(route('super-admin.users.reset-password', resettingUser.value.id), {
        onSuccess: () => {
            isResetOpen.value = false;
        },
    });
};

const toggleStatus = (user: User) => {
    if (confirm(`Change status for ${user.name} to ${user.status === 'active' ? 'INACTIVE' : 'ACTIVE'}?`)) {
        router.post(route('super-admin.users.toggle-status', user.id));
    }
};

const handleSearch = debounce(() => {
    router.get(route('super-admin.users.index'), { 
        search: search.value, 
        school_id: schoolFilter.value === 'all' ? null : schoolFilter.value,
        status: statusFilter.value === 'all' ? null : statusFilter.value 
    }, { preserveState: true, replace: true });
}, 500);

watch([schoolFilter, statusFilter], () => {
    handleSearch();
});
</script>

<template>
    <Head title="Identity Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Users</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">Manage platform users and access levels.</p>
                </div>
                <div class="flex items-center gap-3">
                     <div class="px-4 py-2 rounded-xl bg-muted/50 border border-border/50 text-xs font-black uppercase tracking-widest text-muted-foreground dark:border-white/5">
                          {{ users.total.toLocaleString() }} Total Users
                     </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-12 rounded-2xl border border-border bg-card p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center dark:border-white/5">
                    <div class="relative flex-1">
                        <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                        <Input 
                            v-model="search"
                            @input="handleSearch"
                            placeholder="Search by name or email..." 
                            class="pl-10 h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                        />
                    </div>
                    
                    <Select v-model="schoolFilter">
                        <SelectTrigger class="h-11 w-full md:w-64 rounded-xl border-border bg-muted/20 font-bold">
                            <SelectValue placeholder="All Schools" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl border-border shadow-xl">
                            <SelectItem value="all">All Schools</SelectItem>
                            <SelectItem v-for="school in schools" :key="school.id" :value="school.id.toString()">
                                {{ school.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="statusFilter">
                        <SelectTrigger class="h-11 w-full md:w-44 rounded-xl border-border bg-muted/20 font-bold">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl border-border shadow-xl">
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden dark:border-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/30 border-b border-border/50">
                                <th class="p-5 text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest">User Details</th>
                                <th class="p-5 text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest">School</th>
                                <th class="p-5 text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest">Roles</th>
                                <th class="p-5 text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest">Status</th>
                                <th class="p-5 text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-for="user in users.data" :key="user.id" class="group transition-colors hover:bg-muted/10">
                                <td class="p-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center font-black text-primary text-sm border border-primary/10 uppercase">
                                            {{ user.name[0] }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-foreground tracking-tight">{{ user.name }}</span>
                                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <div class="flex items-center gap-2" v-if="user.school">
                                        <div class="h-2 w-2 rounded-full bg-blue-500/20 flex items-center justify-center">
                                             <div class="h-1 w-1 rounded-full bg-blue-500"></div>
                                        </div>
                                        <span class="text-[11px] font-black text-foreground uppercase tracking-tight">{{ user.school.name }}</span>
                                    </div>
                                    <span v-else class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/40 italic">System User</span>
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge v-for="role in user.roles" :key="role.name" class="bg-primary/5 text-primary border-primary/10 font-black text-[8px] uppercase tracking-widest px-2 py-0.5 pointer-events-none">
                                            {{ role.name.replace('_', ' ') }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <div :class="[
                                         'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest',
                                         user.status === 'active' 
                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' 
                                            : 'bg-rose-500/10 text-rose-600 dark:text-rose-400'
                                    ]">
                                        <div :class="['h-1 w-1 rounded-full', user.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500']"></div>
                                        {{ user.status }}
                                    </div>
                                </td>
                                <td class="p-5 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-8 w-8 p-0 rounded-lg hover:bg-muted/50 transition-colors">
                                                <MoreHorizontal class="h-4 w-4 text-muted-foreground/40" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-border bg-card">
                                            <DropdownMenuLabel class="px-2 py-2 text-[9px] font-black uppercase text-muted-foreground/40 tracking-widest">Options</DropdownMenuLabel>
                                            
                                            <DropdownMenuItem @click="openReset(user)" class="font-black text-xs flex items-center px-2 py-2.5 rounded-lg cursor-pointer hover:bg-primary/5 transition-colors">
                                                <Key class="mr-2 h-4 w-4 text-muted-foreground/40" />
                                                Reset Password
                                            </DropdownMenuItem>

                                            <DropdownMenuItem @click="toggleStatus(user)" class="font-black text-xs flex items-center px-2 py-2.5 rounded-lg cursor-pointer transition-colors" :class="user.status === 'active' ? 'hover:bg-rose-500/10 hover:text-rose-600' : 'hover:bg-emerald-500/10 hover:text-emerald-600'">
                                                <UserX v-if="user.status === 'active'" class="mr-2 h-4 w-4 text-muted-foreground/40" />
                                                <UserCheck v-else class="mr-2 h-4 w-4 text-muted-foreground/40" />
                                                {{ user.status === 'active' ? 'Deactivate User' : 'Activate User' }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-5 border-t border-border/50 bg-muted/10 flex items-center justify-between">
                     <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest">
                          Page {{ users.current_page }} of {{ users.last_page }}
                     </p>
                     <div class="flex items-center gap-2">
                           <template v-for="(link, index) in users.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="h-8 px-3 rounded-lg border border-border bg-card text-[10px] font-black uppercase tracking-widest flex items-center justify-center transition-all hover:bg-primary/5 hover:text-primary hover:border-primary/20"
                                    :class="{ 'bg-primary text-white border-primary shadow-lg shadow-primary/20': link.active }"
                                    v-html="link.label"
                                />
                                <span v-else class="h-8 px-3 rounded-lg border border-border/50 text-[10px] font-black uppercase tracking-widest flex items-center justify-center opacity-30 select-none" v-html="link.label"></span>
                           </template>
                     </div>
                </div>
            </div>

            <!-- Password Reset Modal -->
            <Dialog v-model:open="isResetOpen">
                <DialogContent class="sm:max-w-md rounded-2xl p-8 border-border bg-card shadow-2xl">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-black text-foreground tracking-tight">Reset Password</DialogTitle>
                        <DialogDescription class="font-bold text-muted-foreground/60 uppercase text-[9px] tracking-widest mt-1">For user: {{ resettingUser?.name }}</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitReset" class="space-y-4 mt-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">New Password</label>
                            <Input 
                                v-model="resetForm.password" 
                                type="password"
                                placeholder="Enter 8+ characters" 
                                class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                required
                            />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Confirm Password</label>
                            <Input 
                                v-model="resetForm.password_confirmation" 
                                type="password"
                                placeholder="Repeat new password" 
                                class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                required
                            />
                        </div>
                        <DialogFooter class="pt-4 flex flex-col gap-2">
                            <Button type="submit" class="w-full h-11 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all" :disabled="resetForm.processing">
                                {{ resetForm.processing ? 'Saving...' : 'Update Password' }}
                            </Button>
                            <Button type="button" variant="ghost" @click="isResetOpen = false" class="w-full h-10 rounded-xl text-muted-foreground/40 font-black uppercase text-[10px] tracking-widest">Cancel</Button>
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
