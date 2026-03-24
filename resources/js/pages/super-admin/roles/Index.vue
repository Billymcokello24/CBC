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
        <div class="min-h-full bg-slate-50/50 p-6 lg:p-10 space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between lg:gap-10">
                <div class="space-y-1">
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 leading-none">Global <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">RBAC</span></h1>
                    <p class="text-slate-500 font-medium text-lg">Define centralized role blueprints and inherited permission sets.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <Dialog v-model:open="isCreateOpen">
                        <DialogTrigger as-child>
                            <Button class="relative group h-14 px-8 rounded-2xl bg-slate-900 text-white font-black overflow-hidden shadow-xl shadow-slate-200 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                <Plus class="relative mr-2 h-5 w-5" />
                                <span class="relative">Register Blueprint</span>
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md rounded-[2.5rem] p-10 border-slate-100 shadow-2xl">
                            <DialogHeader>
                                <DialogTitle class="text-2xl font-black text-slate-900">New Role Template</DialogTitle>
                                <DialogDescription class="font-medium text-slate-500">Create a global authority node that will be available to all shards.</DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitCreate" class="space-y-6 mt-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Blueprint Name</label>
                                    <Input 
                                        v-model="createForm.name" 
                                        placeholder="e.g., Regional Inspector" 
                                        class="h-14 rounded-2xl border-slate-200 font-bold focus:ring-blue-500/20"
                                        required
                                    />
                                    <span v-if="createForm.errors.name" class="text-xs font-bold text-rose-500">{{ createForm.errors.name }}</span>
                                </div>
                                <DialogFooter>
                                    <Button type="submit" class="w-full h-14 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-black shadow-lg shadow-blue-100 transition-all" :disabled="createForm.processing">
                                        {{ createForm.processing ? 'Provisioning...' : 'Confirm Blueprint' }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Role Templates Grid -->
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="role in roles" :key="role.id" class="group relative overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm transition-all hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-2">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-blue-50/50 blur-2xl opacity-0 transition-opacity group-hover:opacity-100"></div>
                    
                    <div class="relative flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-transform duration-500 group-hover:rotate-12">
                            <ShieldCheck v-if="role.name === 'super_admin'" class="h-7 w-7" />
                            <Shield v-else class="h-7 w-7" />
                        </div>
                        
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-10 w-10 p-0 rounded-xl hover:bg-slate-50">
                                    <MoreHorizontal class="h-5 w-5 text-slate-400" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 rounded-2xl p-2 shadow-2xl border-slate-100">
                                <DropdownMenuLabel class="px-3 py-2 text-[10px] font-black uppercase text-slate-400 tracking-widest">Blueprint Config</DropdownMenuLabel>
                                <DropdownMenuItem @click="openEdit(role)" class="group font-black text-sm flex items-center px-3 py-3 rounded-xl cursor-pointer hover:bg-blue-50 transition-all">
                                    <Edit2 class="mr-3 h-4 w-4 text-slate-300 group-hover:text-blue-600" />
                                    Edit Capabilities
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="bg-slate-50" />
                                <DropdownMenuItem @click="deleteRole(role)" :disabled="role.name === 'super_admin'" class="group font-black text-sm flex items-center px-3 py-3 rounded-xl text-rose-600 hover:bg-rose-50 cursor-pointer transition-all">
                                    <Trash2 class="mr-3 h-4 w-4 text-rose-300 group-hover:text-rose-500" />
                                    Terminate Blueprint
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="relative mt-8 space-y-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ role.name.replace('_', ' ').toUpperCase() }}</h3>
                            <Badge v-if="role.name === 'super_admin'" class="bg-slate-900 font-black text-[9px] uppercase tracking-widest px-2 py-0.5">Immutable</Badge>
                        </div>
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">{{ role.permissions.length }} Active Capabilities</p>
                    </div>

                    <div class="relative mt-10 flex items-center justify-between">
                        <div class="flex -space-x-3 overflow-hidden">
                             <div v-for="i in 3" :key="i" class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-slate-100 flex items-center justify-center">
                                  <Users class="h-3 w-3 text-slate-300" />
                             </div>
                        </div>
                        <button @click="openEdit(role)" class="flex items-center gap-2 text-xs font-black text-blue-600 uppercase tracking-widest hover:gap-3 transition-all group-hover:mr-2">
                             Full Matrix <ArrowRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Permission Matrix Dialog -->
            <Dialog v-model:open="isEditOpen">
                <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-hidden flex flex-col rounded-[2.5rem] p-0 border-slate-100 shadow-3xl">
                    <DialogHeader class="p-10 border-b border-slate-50 bg-slate-50/20">
                        <div class="flex items-center gap-4">
                             <div class="flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-indigo-50 text-indigo-600">
                                  <Settings2 class="h-8 w-8" />
                             </div>
                             <div>
                                  <DialogTitle class="text-3xl font-black text-slate-900 tracking-tight">Sync Capability <span class="text-indigo-600">Matrix</span></DialogTitle>
                                  <DialogDescription class="font-bold text-slate-500 uppercase tracking-widest text-[10px]">Configuring Authority Set for: {{ selectedRole?.name.toUpperCase() }}</DialogDescription>
                             </div>
                        </div>
                    </DialogHeader>
                    
                    <div class="flex-1 overflow-y-auto p-10 space-y-12">
                         <div v-for="(perms, group) in groupedPermissions" :key="group" class="space-y-6">
                              <div class="flex items-center gap-4">
                                   <div class="h-px flex-1 bg-slate-100"></div>
                                   <h4 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-300">{{ group }} System</h4>
                                   <div class="h-px flex-1 bg-slate-100"></div>
                              </div>
                              
                              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                   <div v-for="permission in perms" :key="permission.id" class="relative group">
                                        <div 
                                             class="flex items-center justify-between p-4 rounded-2xl border transition-all cursor-pointer select-none"
                                             :class="permissionForm.permissions.includes(permission.id) ? 'bg-indigo-50/50 border-indigo-100 ring-1 ring-indigo-50 shadow-sm' : 'bg-white border-slate-100 hover:border-slate-200'"
                                             @click="permissionForm.permissions.includes(permission.id) ? permissionForm.permissions = permissionForm.permissions.filter(id => id !== permission.id) : permissionForm.permissions.push(permission.id)"
                                        >
                                             <div class="flex flex-col">
                                                  <span class="text-xs font-black text-slate-900 tracking-tight">{{ permission.name.split('.')[1].replace('_', ' ').toUpperCase() }}</span>
                                                  <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ permission.name }}</span>
                                             </div>
                                             <div :class="['h-6 w-6 rounded-lg flex items-center justify-center transition-all', permissionForm.permissions.includes(permission.id) ? 'bg-indigo-600 shadow-lg shadow-indigo-100 scale-110' : 'bg-slate-50']">
                                                  <Check v-if="permissionForm.permissions.includes(permission.id)" class="h-4 w-4 text-white" />
                                                  <div v-else class="h-1.5 w-1.5 rounded-full bg-slate-200"></div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <DialogFooter class="p-10 border-t border-slate-50 bg-white shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.05)]">
                        <Button variant="outline" @click="isEditOpen = false" class="h-14 px-8 rounded-2xl border-slate-200 font-black text-slate-500 hover:bg-slate-50 transition-all">
                             Abandon Changes
                        </Button>
                        <Button @click="submitPermissions" class="h-14 px-10 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black shadow-xl shadow-indigo-100 transition-all ml-4" :disabled="permissionForm.processing">
                            {{ permissionForm.processing ? 'Syncing Matrix...' : 'Commit Matrix Update' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
.shadow-3xl {
    box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.15);
}
</style>
