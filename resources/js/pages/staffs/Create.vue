<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Download,
    Users,
    Loader2,
    Save,
    Upload,
    UserPlus,
    AlertTriangle,
    Briefcase,
    User,
    Key,
    Fingerprint,
    Camera,
    ShieldCheck,
    Database,
    Zap,
    TrendingUp,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import StaffBulkUploadModal from './partials/StaffBulkUploadModal.vue';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    departments: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string }>;
    designations: Array<{ id: number; name: string }>;
    roles: Array<{ id: number; name: string }>;
    counties: string[];
    preselectedRole?: string;
}>();

const title = computed(() => {
    if (props.preselectedRole) {
        const role = props.preselectedRole.replace('_', ' ');
        return `Add ${role.charAt(0).toUpperCase() + role.slice(1)}`;
    }
    return 'Add Staff Member';
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/staffs' },
    { title: 'Add Staff', href: '/staffs/create' },
];

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    staff_number: '',
    tsc_number: '',
    email: '',
    phone: '',
    gender: 'male',
    date_of_birth: '',
    id_number: '',
    nationality: 'Kenyan',
    department_id: '',
    staff_category_id: '',
    staff_designation_id: '',
    contract_type: 'Permanent',
    employment_type: 'Full-time',
    date_joined: new Date().toISOString().split('T')[0],
    basic_salary: '',
    status: 'active',
    photo: null as File | null,
    role: props.preselectedRole || '',
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
const successOpen = ref(false);
const bulkUploadOpen = ref(false);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.post('/staffs', {
        onSuccess: (page) => {
            const flash = (page.props as any).flash;
            if (flash?.success) {
                successOpen.value = true;
            }
        },
    });
};

const resetForm = () => {
    form.reset();
    successOpen.value = false;
};
</script>

<template>
    <Head title="Add Staff Member" />

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
                        <Link href="/staffs">
                            <ArrowLeft class="h-4 w-4 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                {{ title }}
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground">Add a new staff member to the school records.</p>
                    </div>
                </div>
            </div>

            <!-- Bulk Import -->
            <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-8 shadow-sm transition-all">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary/5"></div>
                <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20">
                            <Users class="h-7 w-7" />
                        </div>
                        <div class="space-y-1">
                            <h2 class="text-lg font-bold tracking-tight text-foreground uppercase">Bulk Import</h2>
                            <p class="text-xs font-medium text-muted-foreground max-w-md">Import multiple staff members at once using a CSV template.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button variant="ghost" class="h-10 rounded-lg px-6 text-xs font-semibold uppercase hover:bg-muted" as-child>
                            <a href="/staffs/template/download">
                                <Download class="mr-2 h-4 w-4 text-primary" />
                                Download Template
                            </a>
                        </Button>
                        <Button @click="bulkUploadOpen = true" class="h-10 rounded-lg bg-slate-900 px-8 text-xs font-semibold text-white uppercase shadow-sm transition-all hover:bg-slate-800">
                            <Upload class="mr-2 h-4 w-4" />
                            Upload CSV
                        </Button>
                    </div>
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
                                            <div v-if="!form.photo" class="flex h-full w-full items-center justify-center text-white/20"><User class="h-12 w-12" /></div>
                                            <div v-else class="h-full w-full">
                                                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Camera class="h-6 w-6 text-white" />
                                                <span class="text-[10px] font-bold text-white uppercase">Upload</span>
                                            </div>
                                        </div>
                                    </template>
                                </ProfilePhotoUpload>
                                <div class="text-center space-y-1">
                                    <h3 class="text-sm font-bold uppercase">Staff Photo</h3>
                                    <p class="text-[10px] font-medium text-white/40 uppercase">Upload a recent professional photo</p>
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
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- System Info -->
                    <div class="flex items-center justify-between rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-foreground uppercase">Cloud Sync</p>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase">System Status: Active</p>
                            </div>
                        </div>
                        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
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
                                    <Input v-model="form.first_name" placeholder="John" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Middle Name</Label>
                                    <Input v-model="form.middle_name" placeholder="Doe" class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Last Name *</Label>
                                    <Input v-model="form.last_name" placeholder="Smith" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
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
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Date of Birth</Label>
                                    <Input v-model="form.date_of_birth" type="date" class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">ID / Passport Number</Label>
                                    <Input v-model="form.id_number" placeholder="12345678" class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
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
                                    <Input v-model="form.staff_number" placeholder="STF/001" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-bold text-primary focus:bg-background" />
                                    <InputError :message="form.errors.staff_number" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">TSC Number</Label>
                                    <Input v-model="form.tsc_number" placeholder="TSC-123456" class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Department *</Label>
                                    <div class="relative">
                                        <select v-model="form.department_id" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background" required>
                                            <option value="">Select Department</option>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Contract Type</Label>
                                    <div class="relative">
                                        <select v-model="form.contract_type" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background">
                                            <option value="Permanent">Permanent</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Part-time">Part-time</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Security -->
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
                                    <Input v-model="form.email" type="email" placeholder="staff@school.com" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Phone Number *</Label>
                                    <Input v-model="form.phone" placeholder="+254..." required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-semibold focus:bg-background" />
                                    <InputError :message="form.errors.phone" />
                                </div>
                            </div>
                            <div class="mt-6 flex items-start gap-4 rounded-lg bg-primary/5 p-4 border border-primary/10">
                                <ShieldCheck class="h-5 w-5 text-primary shrink-0 mt-0.5" />
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-primary uppercase">Automated Onboarding</p>
                                    <p class="text-[10px] font-medium text-muted-foreground leading-relaxed">
                                        A secure password will be automatically generated and sent to the staff member's email. 
                                        They will be required to change it upon their first login.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-4 pt-4 border-t border-border/50">
                        <Button variant="ghost" class="h-10 rounded-lg px-6 text-xs font-semibold text-muted-foreground" as-child>
                            <Link href="/staffs">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <UserPlus v-else class="mr-2 h-4 w-4" />
                            Save Staff Member
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Strategic Dialogs -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-[480px] rounded-xl border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <AlertTriangle class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold tracking-tight text-foreground uppercase">Confirm Addition</h3>
                        </div>
                    </div>
                    
                    <p class="text-sm font-medium leading-relaxed text-muted-foreground">
                        Are you sure you want to add <span class="text-foreground font-bold">{{ form.first_name }} {{ form.last_name }}</span> to the school staff records?
                    </p>
                </div>
 
                <div class="flex items-center justify-end gap-3 bg-muted/5 p-6 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-10 rounded-lg px-6 text-xs font-semibold">Cancel</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white">Confirm</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="successOpen" @update:open="successOpen = $event">
             <DialogContent class="sm:max-w-[480px] rounded-xl border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-10 text-center">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                        <CheckCircle2 class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-bold tracking-tight text-foreground uppercase mb-2">Staff Registered</h3>
                    <p class="text-sm font-medium text-muted-foreground">The staff member has been successfully added to the system.</p>
                </div>
                <div class="flex items-center justify-center p-6 bg-muted/5 border-t border-border/50">
                    <Button @click="resetForm" class="h-10 w-full rounded-lg bg-slate-900 px-8 text-xs font-semibold text-white uppercase shadow-sm transition-all hover:bg-slate-800">Go to Staff List</Button>
                </div>
            </DialogContent>
        </Dialog>

        <StaffBulkUploadModal v-model:open="bulkUploadOpen" @uploaded="() => router.reload()" />
    </AppLayout>
</template>
