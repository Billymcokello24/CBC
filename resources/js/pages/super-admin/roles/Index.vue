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
    ArrowRight,
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
} from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

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
    permissionForm.permissions = role.permissions.map((p) => p.id);
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

    permissionForm.put(
        route('super-admin.roles.update', selectedRole.value.id),
        {
            onSuccess: () => {
                isEditOpen.value = false;
            },
        },
    );
};

const deleteRole = (role: Role) => {
    if (
        confirm(
            `Terminate global role template "${role.name}"? This will affect all tenants.`,
        )
    ) {
        useForm({}).delete(route('super-admin.roles.destroy', role.id));
    }
};

const groupedPermissions = computed(() => {
    const groups: Record<string, Permission[]> = {};
    props.permissions.forEach((p) => {
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
        <div
            class="animate-in space-y-8 duration-700 fade-in slide-in-from-bottom-4"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground"
                    >
                        Role Management
                    </h1>
                    <p
                        class="mt-0.5 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                    >
                        Define system roles and their associated permissions.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Dialog v-model:open="isCreateOpen">
                        <DialogTrigger as-child>
                            <Button
                                class="h-11 rounded-xl bg-primary px-6 font-bold text-white shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                <span class="text-xs tracking-tight uppercase"
                                    >Create Role</span
                                >
                            </Button>
                        </DialogTrigger>
                        <DialogContent
                            class="rounded-2xl border-border p-8 shadow-lg sm:max-w-md dark:border-white/5"
                        >
                            <DialogHeader>
                                <DialogTitle
                                    class="text-xl font-bold text-foreground"
                                    >New Role</DialogTitle
                                >
                                <DialogDescription
                                    class="text-sm font-bold text-muted-foreground/60 uppercase"
                                    >Assign a name to your new system
                                    role.</DialogDescription
                                >
                            </DialogHeader>
                            <form
                                @submit.prevent="submitCreate"
                                class="mt-4 space-y-6"
                            >
                                <div class="space-y-2">
                                    <label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Role Name</label
                                    >
                                    <Input
                                        v-model="createForm.name"
                                        placeholder="e.g. Content Moderator"
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                        required
                                    />
                                    <span
                                        v-if="createForm.errors.name"
                                        class="text-xs font-bold text-rose-500 uppercase"
                                        >{{ createForm.errors.name }}</span
                                    >
                                </div>
                                <DialogFooter>
                                    <Button
                                        type="submit"
                                        class="h-11 w-full rounded-xl bg-primary text-sm font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                                        :disabled="createForm.processing"
                                    >
                                        {{
                                            createForm.processing
                                                ? 'Creating...'
                                                : 'Create Role'
                                        }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Role Templates Grid -->
            <div
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg dark:border-white/5"
                >
                    <div class="relative flex items-center justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl border border-primary/10 bg-primary/5 text-primary transition-transform duration-500 group-hover:rotate-6"
                        >
                            <ShieldCheck
                                v-if="role.name === 'super_admin'"
                                class="h-6 w-6"
                            />
                            <Shield v-else class="h-6 w-6" />
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    class="h-9 w-9 rounded-lg border border-transparent p-0 hover:border-border hover:bg-muted/50"
                                >
                                    <MoreHorizontal
                                        class="h-4 w-4 text-muted-foreground/40"
                                    />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-48 rounded-xl border-border p-1 shadow-lg dark:border-white/5"
                            >
                                <DropdownMenuLabel
                                    class="px-2 py-1.5 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >Options</DropdownMenuLabel
                                >
                                <DropdownMenuItem
                                    @click="openEdit(role)"
                                    class="group flex cursor-pointer items-center rounded-lg px-2 py-2 text-sm font-bold tracking-wider uppercase transition-all hover:bg-primary/5 hover:text-primary"
                                >
                                    <Edit2
                                        class="mr-2 h-3.5 w-3.5 opacity-40 group-hover:opacity-100"
                                    />
                                    Permissions
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="bg-border/50" />
                                <DropdownMenuItem
                                    @click="deleteRole(role)"
                                    :disabled="role.name === 'super_admin'"
                                    class="group flex cursor-pointer items-center rounded-lg px-2 py-2 text-sm font-bold tracking-wider text-rose-500 uppercase transition-all hover:bg-rose-500/5"
                                >
                                    <Trash2
                                        class="mr-2 h-3.5 w-3.5 opacity-40 group-hover:opacity-100"
                                    />
                                    Delete Role
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="relative mt-6 space-y-1">
                        <div class="flex items-center gap-2">
                            <h3
                                class="line-clamp-1 text-lg font-bold tracking-tight text-foreground"
                            >
                                {{ role.name.replace('_', ' ').toUpperCase() }}
                            </h3>
                            <Badge
                                v-if="role.name === 'super_admin'"
                                class="border-0 bg-primary/10 px-1.5 text-xs font-bold tracking-tight text-primary uppercase shadow-none"
                                >System</Badge
                            >
                        </div>
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                        >
                            {{ role.permissions.length }} Active Permissions
                        </p>
                    </div>

                    <div
                        class="relative mt-8 flex items-center justify-between border-t border-border/50 pt-4"
                    >
                        <div class="flex -space-x-2 overflow-hidden">
                            <div
                                v-for="i in 3"
                                :key="i"
                                class="flex inline-block h-7 w-7 items-center justify-center rounded-lg bg-muted ring-2 ring-card"
                            >
                                <Users
                                    class="h-3 w-3 text-muted-foreground/30"
                                />
                            </div>
                        </div>
                        <button
                            @click="openEdit(role)"
                            class="flex items-center gap-1.5 text-xs font-bold tracking-tight text-primary uppercase transition-all hover:translate-x-1"
                        >
                            Manage <ArrowRight class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Permission Matrix Dialog -->
            <Dialog v-model:open="isEditOpen">
                <DialogContent
                    class="shadow-3xl flex max-h-[90vh] flex-col overflow-hidden rounded-2xl border-border p-0 sm:max-w-4xl dark:border-white/5"
                >
                    <DialogHeader
                        class="border-b border-border bg-muted/20 p-8"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary"
                            >
                                <Settings2 class="h-7 w-7" />
                            </div>
                            <div>
                                <DialogTitle
                                    class="text-xl font-bold tracking-tight text-foreground uppercase"
                                    >Edit Permissions</DialogTitle
                                >
                                <DialogDescription
                                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >Configuring access for
                                    {{
                                        selectedRole?.name.toUpperCase()
                                    }}</DialogDescription
                                >
                            </div>
                        </div>
                    </DialogHeader>

                    <div
                        class="custom-scrollbar flex-1 space-y-10 overflow-y-auto p-8"
                    >
                        <div
                            v-for="(perms, group) in groupedPermissions"
                            :key="group"
                            class="space-y-4"
                        >
                            <div class="flex items-center gap-3">
                                <h4
                                    class="text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    {{ group }} Permissions
                                </h4>
                                <div class="h-px flex-1 bg-border/50"></div>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div
                                    v-for="permission in perms"
                                    :key="permission.id"
                                    class="group relative"
                                >
                                    <div
                                        class="flex cursor-pointer items-center justify-between rounded-xl border p-4 transition-all select-none"
                                        :class="
                                            permissionForm.permissions.includes(
                                                permission.id,
                                            )
                                                ? 'border border-primary/20 bg-primary/5 ring-1 ring-primary/10'
                                                : 'border-transparent bg-muted/10 hover:bg-muted/30'
                                        "
                                        @click="
                                            permissionForm.permissions.includes(
                                                permission.id,
                                            )
                                                ? (permissionForm.permissions =
                                                      permissionForm.permissions.filter(
                                                          (id) =>
                                                              id !==
                                                              permission.id,
                                                      ))
                                                : permissionForm.permissions.push(
                                                      permission.id,
                                                  )
                                        "
                                    >
                                        <div class="flex flex-col">
                                            <span
                                                class="text-xs font-bold tracking-tight text-foreground uppercase"
                                                >{{
                                                    permission.name
                                                        .split('.')[1]
                                                        .replace('_', ' ')
                                                }}</span
                                            >
                                            <span
                                                class="mt-0.5 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                                >{{ permission.name }}</span
                                            >
                                        </div>
                                        <div
                                            :class="[
                                                'flex h-6 w-6 items-center justify-center rounded-lg transition-all',
                                                permissionForm.permissions.includes(
                                                    permission.id,
                                                )
                                                    ? 'bg-primary shadow-lg shadow-primary/20'
                                                    : 'bg-muted/50',
                                            ]"
                                        >
                                            <Check
                                                v-if="
                                                    permissionForm.permissions.includes(
                                                        permission.id,
                                                    )
                                                "
                                                class="h-3.5 w-3.5 text-white"
                                            />
                                            <div
                                                v-else
                                                class="h-1 w-1 rounded-full bg-muted-foreground/20"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="border-t border-border bg-card p-8">
                        <Button
                            variant="ghost"
                            @click="isEditOpen = false"
                            class="h-11 rounded-xl px-6 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase hover:text-foreground"
                        >
                            Cancel
                        </Button>
                        <Button
                            @click="submitPermissions"
                            class="ml-3 h-11 rounded-xl bg-primary px-10 text-sm font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                            :disabled="permissionForm.processing"
                        >
                            {{
                                permissionForm.processing
                                    ? 'Saving...'
                                    : 'Save Permissions'
                            }}
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
