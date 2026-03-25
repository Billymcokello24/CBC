<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Shield, 
    Plus, 
    Search, 
    ShieldCheck, 
    MoreHorizontal, 
    Edit2, 
    Trash2,
    Lock,
    Unlock,
    Settings2,
    Check,
    X,
    Filter,
    ArrowRight
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Checkbox } from "@/components/ui/checkbox";
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuLabel, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
} from "@/components/ui/dropdown-menu";

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    roles: Role[];
    permissions: Permission[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Global RBAC', href: '/super-admin/roles' },
];

const createForm = useForm({
    name: '',
});

const permissionForm = useForm({
    permissions: [] as number[],
});

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const selectedRole = ref<Role | null>(null);

const openEdit = (role: Role) => {
    selectedRole.value = role;
    permissionForm.permissions = role.permissions.map(p => p.id);
    isEditOpen.value = true;
};

const submitCreate = () => {
    createForm.post(route('super-admin.roles.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
};

const submitPermissions = () => {
    if (!selectedRole.value) return;
    
    permissionForm.put(route('super-admin.roles.update', selectedRole.value.id), {
        onSuccess: () => {
            isEditOpen.value = false;
        },
    });
};

const deleteRole = (role: Role) => {
    if (confirm(`Terminate global role template "${role.name}"? This will affect all tenants.`)) {
        useForm({}).delete(route('super-admin.roles.destroy', role.id));
    }
};

const groupedPermissions = computed(() => {
    const groups: Record<string, Permission[]> = {};
    props.permissions.forEach(p => {
        const parts = p.name.split('.');
        const group = parts[0] || 'System';
        if (!groups[group]) groups[group] = [];
        groups[group].push(p);
    });
    return groups;
});
</script>

<template>
    <Head title="Global RBAC Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Role Management</h1>
                    <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest mt-0.5">Define system roles and their associated permissions.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <Dialog v-model:open="isCreateOpen">
                        <DialogTrigger as-child>
                            <Button class="h-11 px-6 rounded-xl bg-primary text-white font-black shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                <Plus class="mr-2 h-4 w-4" />
                                <span class="text-xs uppercase tracking-widest">Create Role</span>
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md rounded-2xl p-8 border-border shadow-2xl dark:border-white/5">
                            <DialogHeader>
                                <DialogTitle class="text-xl font-black text-foreground">New Role</DialogTitle>
                                <DialogDescription class="text-[11px] font-bold text-muted-foreground/60 uppercase">Assign a name to your new system role.</DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitCreate" class="space-y-6 mt-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Role Name</label>
                                    <Input 
                                        v-model="createForm.name" 
                                        placeholder="e.g. Content Moderator" 
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                        required
                                    />
                                    <span v-if="createForm.errors.name" class="text-[10px] font-black text-rose-500 uppercase">{{ createForm.errors.name }}</span>
                                </div>
                                <DialogFooter>
                                    <Button type="submit" class="w-full h-11 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all uppercase text-[11px] tracking-widest" :disabled="createForm.processing">
                                        {{ createForm.processing ? 'Creating...' : 'Create Role' }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Role Templates Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="role in roles" :key="role.id" class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:shadow-lg hover:-translate-y-1 dark:border-white/5">
                    <div class="relative flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/5 text-primary border border-primary/10 transition-transform duration-500 group-hover:rotate-6">
                            <ShieldCheck v-if="role.name === 'super_admin'" class="h-6 w-6" />
                            <Shield v-else class="h-6 w-6" />
                        </div>
                        
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-9 w-9 p-0 rounded-lg hover:bg-muted/50 border border-transparent hover:border-border">
                                    <MoreHorizontal class="h-4 w-4 text-muted-foreground/40" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48 rounded-xl p-1 shadow-2xl border-border dark:border-white/5">
                                <DropdownMenuLabel class="px-2 py-1.5 text-[9px] font-black uppercase text-muted-foreground/40 tracking-widest">Options</DropdownMenuLabel>
                                <DropdownMenuItem @click="openEdit(role)" class="group font-black text-[11px] uppercase tracking-wider flex items-center px-2 py-2 rounded-lg cursor-pointer hover:bg-primary/5 hover:text-primary transition-all">
                                    <Edit2 class="mr-2 h-3.5 w-3.5 opacity-40 group-hover:opacity-100" />
                                    Permissions
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="bg-border/50" />
                                <DropdownMenuItem @click="deleteRole(role)" :disabled="role.name === 'super_admin'" class="group font-black text-[11px] uppercase tracking-wider flex items-center px-2 py-2 rounded-lg text-rose-500 hover:bg-rose-500/5 cursor-pointer transition-all">
                                    <Trash2 class="mr-2 h-3.5 w-3.5 opacity-40 group-hover:opacity-100" />
                                    Delete Role
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="relative mt-6 space-y-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-foreground tracking-tight line-clamp-1">{{ role.name.replace('_', ' ').toUpperCase() }}</h3>
                            <Badge v-if="role.name === 'super_admin'" class="bg-primary/10 text-primary border-0 font-black text-[8px] uppercase tracking-widest px-1.5 shadow-none">System</Badge>
                        </div>
                        <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest">{{ role.permissions.length }} Active Permissions</p>
                    </div>

                    <div class="relative mt-8 flex items-center justify-between pt-4 border-t border-border/50">
                        <div class="flex -space-x-2 overflow-hidden">
                             <div v-for="i in 3" :key="i" class="inline-block h-7 w-7 rounded-lg ring-2 ring-card bg-muted flex items-center justify-center">
                                  <Users class="h-3 w-3 text-muted-foreground/30" />
                             </div>
                        </div>
                        <button @click="openEdit(role)" class="flex items-center gap-1.5 text-[9px] font-black text-primary uppercase tracking-widest hover:translate-x-1 transition-all">
                             Manage <ArrowRight class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Permission Matrix Dialog -->
            <Dialog v-model:open="isEditOpen">
                <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-hidden flex flex-col rounded-2xl p-0 border-border shadow-3xl dark:border-white/5">
                    <DialogHeader class="p-8 border-b border-border bg-muted/20">
                        <div class="flex items-center gap-4">
                             <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                  <Settings2 class="h-7 w-7" />
                             </div>
                             <div>
                                  <DialogTitle class="text-xl font-black text-foreground uppercase tracking-tight">Edit Permissions</DialogTitle>
                                  <DialogDescription class="font-black text-muted-foreground/40 uppercase tracking-widest text-[9px]">Configuring access for {{ selectedRole?.name.toUpperCase() }}</DialogDescription>
                             </div>
                        </div>
                    </DialogHeader>
                    
                    <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar">
                         <div v-for="(perms, group) in groupedPermissions" :key="group" class="space-y-4">
                              <div class="flex items-center gap-3">
                                   <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40">{{ group }} Permissions</h4>
                                   <div class="h-px flex-1 bg-border/50"></div>
                              </div>
                              
                              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                   <div v-for="permission in perms" :key="permission.id" class="relative group">
                                        <div 
                                             class="flex items-center justify-between p-4 rounded-xl border transition-all cursor-pointer select-none"
                                             :class="permissionForm.permissions.includes(permission.id) ? 'bg-primary/5 border-primary/20 ring-1 ring-primary/10 border' : 'bg-muted/10 border-transparent hover:bg-muted/30'"
                                             @click="permissionForm.permissions.includes(permission.id) ? permissionForm.permissions = permissionForm.permissions.filter(id => id !== permission.id) : permissionForm.permissions.push(permission.id)"
                                        >
                                             <div class="flex flex-col">
                                                  <span class="text-xs font-black text-foreground tracking-tight uppercase">{{ permission.name.split('.')[1].replace('_', ' ') }}</span>
                                                  <span class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest mt-0.5">{{ permission.name }}</span>
                                             </div>
                                             <div :class="['h-6 w-6 rounded-lg flex items-center justify-center transition-all', permissionForm.permissions.includes(permission.id) ? 'bg-primary shadow-lg shadow-primary/20' : 'bg-muted/50']">
                                                  <Check v-if="permissionForm.permissions.includes(permission.id)" class="h-3.5 w-3.5 text-white" />
                                                  <div v-else class="h-1 w-1 rounded-full bg-muted-foreground/20"></div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <DialogFooter class="p-8 border-t border-border bg-card">
                        <Button variant="ghost" @click="isEditOpen = false" class="h-11 px-6 rounded-xl font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-foreground">
                             Cancel
                        </Button>
                        <Button @click="submitPermissions" class="h-11 px-10 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all ml-3 uppercase text-[11px] tracking-widest" :disabled="permissionForm.processing">
                            {{ permissionForm.processing ? 'Saving...' : 'Save Permissions' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
