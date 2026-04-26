<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Users,
    Loader2,
    Save,
    Edit,
    AlertTriangle,
    Briefcase,
    User,
    Key,
    Trash2,
    Camera,
    ShieldAlert,
    Database,
    Zap,
    ChevronDown,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Teacher {
    id: number;
    user_id: number;
    staff_number: string;
    tsc_number: string | null;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string;
    phone: string;
    gender: string;
    date_of_birth: string | null;
    id_number: string | null;
    nationality: string;
    status: string;
    date_joined: string | null;
    contract_type: string | null;
    employment_type: string | null;
    basic_salary: number | null;
    department_id: number | null;
    staff_category_id: number | null;
    staff_designation_id: number | null;
    photo_url: string | null;
    user: {
        id: number;
        status: string;
        roles: Array<{ id: number; name: string }>;
    };
}

const props = defineProps<{
    teacher: Teacher;
    departments: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string }>;
    designations: Array<{ id: number; name: string }>;
    counties: string[];
    roles: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Faculty Registry', href: '/staffs' },
    { title: 'Profile Mutation', href: `/staffs/${props.teacher.id}/edit` },
];

const form = useForm({
    _method: 'PUT',
    first_name: props.teacher.first_name,
    middle_name: props.teacher.middle_name ?? '',
    last_name: props.teacher.last_name,
    staff_number: props.teacher.staff_number,
    tsc_number: props.teacher.tsc_number ?? '',
    email: props.teacher.email,
    phone: props.teacher.phone,
    gender: props.teacher.gender,
    date_of_birth: props.teacher.date_of_birth ?? '',
    id_number: props.teacher.id_number ?? '',
    nationality: props.teacher.nationality,
    department_id: props.teacher.department_id ?? '',
    staff_category_id: props.teacher.staff_category_id ?? '',
    staff_designation_id: props.teacher.staff_designation_id ?? '',
    contract_type: props.teacher.contract_type ?? 'Permanent',
    employment_type: props.teacher.employment_type ?? 'Full-time',
    date_joined: props.teacher.date_joined ?? '',
    basic_salary: props.teacher.basic_salary ?? '',
    password: '',
    password_confirmation: '',
    status: props.teacher.status,
    role: props.teacher.user?.roles?.[0]?.name ?? '',
    photo: null as File | null,
});

const photoPreview = ref<string | undefined>(undefined);

watch(
    () => form.photo,
    (newPhoto) => {
        if (newPhoto) {
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(newPhoto);
        } else {
            photoPreview.value = undefined;
        }
    },
);

const confirmOpen = ref(false);
const deleteOpen = ref(false);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.post(`/staffs/${props.teacher.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia flash
        },
    });
};

const deleteTeacher = () => {
    deleteOpen.value = false;
    form.delete(`/staffs/${props.teacher.id}`);
};

const fullName = computed(() => `${props.teacher.first_name} ${props.teacher.last_name}`);
</script>

<template>
    <Head :title="`MUTATE_${fullName.toUpperCase()}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-6">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-12 w-12 rounded-2xl border-border bg-card shadow-sm transition-all hover:bg-muted"
                    >
                        <Link :href="`/staffs/${teacher.id}`">
                            <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">
                                Mutate faculty node
                            </h1>
                            <Badge variant="outline" class="rounded-lg border-rose-100 bg-rose-50 px-2.5 py-0.5 text-[10px] font-bold tracking-widest text-rose-600 uppercase shadow-sm">Audit Active</Badge>
                        </div>
                        <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Modifying secure registry entry for {{ fullName }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="ghost" class="h-12 rounded-2xl px-6 text-[10px] font-bold tracking-widest text-rose-600 uppercase hover:bg-rose-50" @click="deleteOpen = true">
                        <Trash2 class="mr-2.5 h-4 w-4" />
                        Purge Entity
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left Intelligence Stack -->
                <div class="space-y-8 lg:col-span-4">
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                         <div class="relative overflow-hidden bg-slate-900 p-10 text-white">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                            <div class="relative z-10 flex flex-col items-center gap-8">
                                <ProfilePhotoUpload v-model="form.photo" :error="form.errors.photo">
                                    <template #default="{ isUploading }">
                                        <div class="group relative h-40 w-40 cursor-pointer overflow-hidden rounded-[2rem] border-4 border-white/10 bg-white/5 shadow-2xl transition-transform duration-500 hover:scale-[1.05]">
                                            <img v-if="photoPreview || teacher.photo_url" :src="photoPreview || teacher.photo_url!" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-white/20 text-4xl font-black">
                                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Camera class="h-8 w-8 text-white" />
                                                <span class="text-[9px] font-bold tracking-widest text-white uppercase">Replace biometric</span>
                                            </div>
                                        </div>
                                    </template>
                                </ProfilePhotoUpload>
                                <div class="text-center space-y-1">
                                    <h3 class="text-lg font-bold tracking-tight uppercase">{{ fullName }}</h3>
                                    <p class="text-[9px] font-bold tracking-widest text-white/40 uppercase">{{ teacher.staff_number }} // ACTIVE_SYNC</p>
                                </div>
                            </div>
                         </div>
                         <div class="p-10 space-y-8">
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">System role designation*</Label>
                                <div class="relative">
                                    <select v-model="form.role" class="h-12 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-5 text-xs font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                        <option value="">Select Protocol</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name.replace('_', ' ').toUpperCase() }}</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Operational status *</Label>
                                <div class="relative">
                                    <select v-model="form.status" class="h-12 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-5 text-xs font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Dormant</option>
                                        <option value="on_leave">External Leave</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                                <InputError :message="form.errors.status" />
                            </div>
                         </div>
                    </div>

                    <!-- Persistence Node -->
                    <div class="flex items-center justify-between rounded-3xl border border-border bg-card p-6 shadow-sm transition-all hover:bg-muted/10">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-500 shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Mutation Engine</p>
                                <p class="text-xs font-bold tracking-tight text-foreground uppercase">Sync ready</p>
                            </div>
                        </div>
                        <div class="h-2 w-2 animate-pulse rounded-full bg-indigo-500"></div>
                    </div>
                </div>

                <!-- Right Data Stack -->
                <div class="space-y-12 lg:col-span-8">
                    <!-- Personal Identity -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                        <div class="border-b border-border/50 bg-muted/10 px-10 py-6">
                            <div class="flex items-center gap-4">
                                <User class="h-5 w-5 text-primary" />
                                <div class="space-y-0.5">
                                    <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Identity mutation</h2>
                                    <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Modifying legal identity properties</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">First Identifier *</Label>
                                    <Input v-model="form.first_name" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Middle Identifier</Label>
                                    <Input v-model="form.middle_name" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Last Identifier *</Label>
                                    <Input v-model="form.last_name" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
                                    <InputError :message="form.errors.last_name" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Biological Mode *</Label>
                                    <div class="relative">
                                        <select v-model="form.gender" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase outline-none focus:bg-background">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Node -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                        <div class="border-b border-border/50 bg-muted/10 px-10 py-6">
                            <div class="flex items-center gap-4">
                                <Briefcase class="h-5 w-5 text-primary" />
                                <div class="space-y-0.5">
                                    <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Strategic assignment</h2>
                                    <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Employment and assignment mutation</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Institutional Key (Staff No) *</Label>
                                    <Input v-model="form.staff_number" required class="h-14 rounded-2xl border-border bg-slate-100/50 px-6 text-sm font-black tracking-[0.2em] text-primary uppercase focus:bg-background" />
                                    <InputError :message="form.errors.staff_number" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Strategic Department *</Label>
                                    <div class="relative">
                                        <select v-model="form.department_id" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auth Node -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                        <div class="border-b border-border/50 bg-muted/10 px-10 py-6">
                            <div class="flex items-center gap-4">
                                <Zap class="h-5 w-5 text-primary" />
                                <div class="space-y-0.5">
                                    <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Auth Protocol Matrix</h2>
                                    <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Communication and access key mutation</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Matrix Email *</Label>
                                    <Input v-model="form.email" type="email" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight focus:bg-background" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Secure Phone Key *</Label>
                                    <Input v-model="form.phone" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight focus:bg-background" />
                                    <InputError :message="form.errors.phone" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">New Access Key (Optional)</Label>
                                    <Input v-model="form.password" type="password" placeholder="LEAVE_BLANK_TO_PRESERVE" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-[0.2em] focus:bg-background" />
                                    <InputError :message="form.errors.password" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Confirm Key Mutation</Label>
                                    <Input v-model="form.password_confirmation" type="password" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-[0.2em] focus:bg-background" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Action Command -->
                    <div class="flex flex-wrap items-center justify-end gap-6 pt-6">
                        <Button variant="ghost" class="h-14 rounded-[1.5rem] px-10 text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase hover:bg-muted" as-child>
                            <Link :href="`/staffs/${teacher.id}`">Abort Mutation</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-14 rounded-[1.5rem] bg-slate-900 px-14 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-2xl transition-all hover:scale-[1.05] active:scale-[0.98]">
                            <Loader2 v-if="form.processing" class="mr-3 h-5 w-5 animate-spin" />
                            <Save v-else class="mr-3 h-5 w-5" />
                            Commit protocol mutation
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Strategic Dialogs -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-[480px] rounded-[2.5rem] border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-10 text-center">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-primary/10 text-primary shadow-sm">
                        <ShieldAlert class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-bold tracking-tight text-foreground uppercase mb-4">Confirm Mutation</h3>
                    <p class="text-sm font-bold text-muted-foreground leading-relaxed uppercase opacity-60">Saving changes to the faculty node will synchronize these properties across the institutional registry.</p>
                </div>
                <div class="flex items-center justify-between gap-4 bg-muted/10 p-8 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-12 rounded-2xl px-8 text-[10px] font-bold tracking-widest uppercase">Abort</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="h-12 rounded-2xl bg-primary px-10 text-[10px] font-bold tracking-widest text-white uppercase shadow-lg">Commit Sync</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="deleteOpen" @update:open="deleteOpen = $event">
             <DialogContent class="sm:max-w-[480px] rounded-[2.5rem] border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-10 text-center text-rose-600">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-50 shadow-sm">
                        <Trash2 class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-bold tracking-tight uppercase mb-4">Purge Protocol</h3>
                    <p class="text-sm font-bold leading-relaxed uppercase opacity-60">This will permanently erase the faculty node and all associated access keys. This action is irreversible.</p>
                </div>
                <div class="flex items-center justify-between gap-4 bg-muted/10 p-8 border-t border-border/50">
                    <Button variant="ghost" @click="deleteOpen = false" class="h-12 rounded-2xl px-8 text-[10px] font-bold tracking-widest uppercase">Abort</Button>
                    <Button @click="deleteTeacher" :disabled="form.processing" class="h-12 rounded-2xl bg-rose-600 px-10 text-[10px] font-bold tracking-widest text-white uppercase shadow-lg shadow-rose-600/20">Purge Entity</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
