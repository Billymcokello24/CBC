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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/staffs' },
    { title: 'Edit Staff', href: `/staffs/${props.teacher.id}/edit` },
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
    <Head :title="`Edit ${fullName}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-10 w-10 rounded-lg border-border bg-card shadow-sm transition-all hover:bg-muted"
                    >
                        <Link :href="`/staffs/${teacher.id}`">
                            <ArrowLeft class="h-4 w-4 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Edit Staff Member
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground">Update information for {{ fullName }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="ghost" class="h-10 rounded-lg px-6 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="deleteOpen = true">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left Stack -->
                <div class="space-y-8 lg:col-span-4">
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                         <div class="relative overflow-hidden bg-slate-900 p-8 text-white">
                            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                            <div class="relative z-10 flex flex-col items-center gap-6">
                                <ProfilePhotoUpload v-model="form.photo" :error="form.errors.photo">
                                    <template #default="{ isUploading }">
                                        <div class="group relative h-32 w-32 cursor-pointer overflow-hidden rounded-xl border-2 border-white/10 bg-white/5 transition-transform duration-300 hover:scale-105">
                                            <img v-if="photoPreview || teacher.photo_url" :src="photoPreview || teacher.photo_url!" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-white/20 text-3xl font-bold">
                                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Camera class="h-6 w-6 text-white" />
                                                <span class="text-[10px] font-bold text-white uppercase">Change</span>
                                            </div>
                                        </div>
                                    </template>
                                </ProfilePhotoUpload>
                                <div class="text-center space-y-1">
                                    <h3 class="text-sm font-bold uppercase">{{ fullName }}</h3>
                                    <p class="text-[10px] font-medium text-white/40 uppercase">{{ teacher.staff_number }}</p>
                                </div>
                            </div>
                         </div>
                         <div class="p-8 space-y-6">
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Staff Role *</Label>
                                <div class="relative">
                                    <select v-model="form.role" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background" required>
                                        <option value="">Select Role</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name.replace('_', ' ').toUpperCase() }}</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Status *</Label>
                                <div class="relative">
                                    <select v-model="form.status" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="on_leave">On Leave</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                                <InputError :message="form.errors.status" />
                            </div>
                         </div>
                    </div>

                    <!-- Info Card -->
                    <div class="flex items-center justify-between rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-foreground uppercase">Secure Sync</p>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase">Records are up to date</p>
                            </div>
                        </div>
                        <div class="h-2 w-2 rounded-full bg-primary opacity-40"></div>
                    </div>
                </div>

                <!-- Right Stack -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Personal Info -->
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/5 px-8 py-5">
                            <div class="flex items-center gap-3">
                                <User class="h-5 w-5 text-primary" />
                                <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Personal Information</h2>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">First Name *</Label>
                                    <Input v-model="form.first_name" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Middle Name</Label>
                                    <Input v-model="form.middle_name" class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Last Name *</Label>
                                    <Input v-model="form.last_name" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.last_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Gender *</Label>
                                    <div class="relative">
                                        <select v-model="form.gender" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/5 px-8 py-5">
                            <div class="flex items-center gap-3">
                                <Briefcase class="h-5 w-5 text-primary" />
                                <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Employment Details</h2>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Staff Number *</Label>
                                    <Input v-model="form.staff_number" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-bold text-primary focus:bg-background" />
                                    <InputError :message="form.errors.staff_number" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Department *</Label>
                                    <div class="relative">
                                        <select v-model="form.department_id" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background" required>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Details -->
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/5 px-8 py-5">
                            <div class="flex items-center gap-3">
                                <Zap class="h-5 w-5 text-primary" />
                                <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Account Security</h2>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Email Address *</Label>
                                    <Input v-model="form.email" type="email" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                 <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Phone Number *</Label>
                                    <Input v-model="form.phone" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.phone" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-4 pt-4 border-t border-border/50">
                        <Button variant="ghost" class="h-10 rounded-lg px-6 text-xs font-semibold text-muted-foreground" as-child>
                            <Link :href="`/staffs/${teacher.id}`">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Strategic Dialogs -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-[480px] rounded-xl border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-8 text-center">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-lg bg-primary/10 text-primary shadow-sm">
                        <ShieldAlert class="h-8 w-8" />
                    </div>
                    <h3 class="text-lg font-bold tracking-tight text-foreground uppercase mb-2">Confirm Changes</h3>
                    <p class="text-sm font-medium text-muted-foreground">Are you sure you want to save these changes to the staff record?</p>
                </div>
                <div class="flex items-center justify-end gap-3 bg-muted/5 p-6 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-10 rounded-lg px-6 text-xs font-semibold">Cancel</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white">Confirm</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="deleteOpen" @update:open="deleteOpen = $event">
             <DialogContent class="sm:max-w-[480px] rounded-xl border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-8 text-center text-rose-600">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-lg bg-rose-50">
                        <Trash2 class="h-8 w-8" />
                    </div>
                    <h3 class="text-lg font-bold tracking-tight uppercase mb-2">Delete Staff Member</h3>
                    <p class="text-sm font-medium leading-relaxed">This will permanently delete the staff record and all associated access. This action cannot be undone.</p>
                </div>
                <div class="flex items-center justify-end gap-3 bg-muted/5 p-6 border-t border-border/50">
                    <Button variant="ghost" @click="deleteOpen = false" class="h-10 rounded-lg px-6 text-xs font-semibold">Cancel</Button>
                    <Button @click="deleteTeacher" :disabled="form.processing" class="h-10 rounded-lg bg-rose-600 px-8 text-xs font-semibold text-white shadow-sm">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
