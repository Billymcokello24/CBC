<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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
        const role = props.preselectedRole.replace('_', ' ').toUpperCase();
        return `ENROLL_${role}_ENTITY`;
    }
    return 'ENROLL_FACULTY_ENTITY';
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Faculty Registry', href: '/staffs' },
    { title: 'Enrollment', href: '/staffs/create' },
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
    password: '',
    password_confirmation: '',
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
    form.post('/teachers', {
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
    <Head title="Faculty Enrollment Hub" />

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
                        <Link href="/staffs">
                            <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">
                                {{ title }}
                            </h1>
                            <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2.5 py-0.5 text-[10px] font-bold tracking-widest text-primary uppercase shadow-sm">Institutional</Badge>
                        </div>
                        <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Synchronizing new personnel to secure faculty registry</p>
                    </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="group relative overflow-hidden rounded-[2.5rem] border border-border bg-card p-10 shadow-sm transition-all hover:border-primary/20">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary/5 transition-transform duration-1000 group-hover:scale-110"></div>
                <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-6">
                        <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-primary text-white shadow-2xl shadow-primary/30">
                            <Users class="h-8 w-8" />
                        </div>
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-foreground uppercase">Bulk Ingestion Matrix</h2>
                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60 max-w-md">Import high-volume personnel records using standardized institutional templates</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button variant="ghost" class="h-12 rounded-2xl px-6 text-[10px] font-bold tracking-widest uppercase hover:bg-muted" as-child>
                            <a href="/staffs/template/download">
                                <Download class="mr-2.5 h-4 w-4 text-primary" />
                                Download Protocol
                            </a>
                        </Button>
                        <Button @click="bulkUploadOpen = true" class="h-12 rounded-2xl bg-slate-900 px-8 text-[10px] font-bold tracking-widest text-white uppercase shadow-xl transition-all hover:bg-slate-800">
                            <Upload class="mr-2.5 h-4 w-4" />
                            Upload CSV Matrix
                        </Button>
                    </div>
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
                                            <div v-if="!form.photo" class="flex h-full w-full items-center justify-center text-white/20"><User class="h-16 w-16" /></div>
                                            <div v-else class="h-full w-full">
                                                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Camera class="h-8 w-8 text-white" />
                                                <span class="text-[9px] font-bold tracking-widest text-white uppercase">Capture</span>
                                            </div>
                                        </div>
                                    </template>
                                </ProfilePhotoUpload>
                                <div class="text-center space-y-1">
                                    <h3 class="text-lg font-bold tracking-tight uppercase">Profile Hub</h3>
                                    <p class="text-[9px] font-bold tracking-widest text-white/40 uppercase">Capturing biometric visual ID</p>
                                </div>
                            </div>
                         </div>
                         <div class="p-10 space-y-8">
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Strategic role *</Label>
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
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Activation state *</Label>
                                <div class="relative">
                                    <select v-model="form.status" class="h-12 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-5 text-xs font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Dormant</option>
                                        <option value="on_leave">External Leave</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Cloud Sync Node -->
                    <div class="flex items-center justify-between rounded-3xl border border-border bg-card p-6 shadow-sm transition-all hover:bg-muted/10">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">System Protocol</p>
                                <p class="text-xs font-bold tracking-tight text-foreground uppercase">Cloud Sync Active</p>
                            </div>
                        </div>
                        <div class="h-2 w-2 animate-pulse rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></div>
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
                                    <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Identity Matrix</h2>
                                    <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Legal and demographic synchronization</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Primary Identifier (First) *</Label>
                                    <Input v-model="form.first_name" placeholder="John" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Legal Alias (Middle)</Label>
                                    <Input v-model="form.middle_name" placeholder="Doe" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Legacy Identifier (Last) *</Label>
                                    <Input v-model="form.last_name" placeholder="Smith" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
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
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Temporal Birth Cycle</Label>
                                    <Input v-model="form.date_of_birth" type="date" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold uppercase focus:bg-background" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">State ID / Passport</Label>
                                    <Input v-model="form.id_number" placeholder="12345678" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
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
                                    <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Professional Designation</h2>
                                    <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Employment and assignment synchronization</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Institutional Key (Staff No) *</Label>
                                    <Input v-model="form.staff_number" placeholder="FAC/001" required class="h-14 rounded-2xl border-border bg-slate-100/50 px-6 text-sm font-black tracking-[0.2em] text-primary uppercase focus:bg-background" />
                                    <InputError :message="form.errors.staff_number" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">External TSC Key</Label>
                                    <Input v-model="form.tsc_number" placeholder="TSC-123456" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase focus:bg-background" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Strategic Department *</Label>
                                    <div class="relative">
                                        <select v-model="form.department_id" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                            <option value="">Select Node</option>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Protocol Contract</Label>
                                    <div class="relative">
                                        <select v-model="form.contract_type" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold tracking-tight uppercase outline-none focus:bg-background">
                                            <option value="Permanent">Permanent</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Part-time">Part-time</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auth Hub -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                        <div class="border-b border-border/50 bg-muted/10 px-10 py-6">
                            <div class="flex items-center gap-4">
                                <Zap class="h-5 w-5 text-primary" />
                                <div class="space-y-0.5">
                                    <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Protocol Auth & Security</h2>
                                    <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Access keys and global communication</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Primary Matrix Email *</Label>
                                    <Input v-model="form.email" type="email" placeholder="faculty@institution.com" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight focus:bg-background" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Secure Phone Key *</Label>
                                    <Input v-model="form.phone" placeholder="+254700000000" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-tight focus:bg-background" />
                                    <InputError :message="form.errors.phone" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Initial Access Key *</Label>
                                    <Input v-model="form.password" type="password" placeholder="MIN_8_CHARS" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-[0.2em] focus:bg-background" />
                                    <InputError :message="form.errors.password" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Confirm Protocol Key *</Label>
                                    <Input v-model="form.password_confirmation" type="password" placeholder="RE_ENTER_KEY" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold tracking-[0.2em] focus:bg-background" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Action Command -->
                    <div class="flex flex-wrap items-center justify-end gap-6 pt-6">
                        <Button variant="ghost" class="h-14 rounded-[1.5rem] px-10 text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase hover:bg-muted" as-child>
                            <Link href="/staffs">Abort Enrollment</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-14 rounded-[1.5rem] bg-primary px-14 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-2xl shadow-primary/30 transition-all hover:scale-[1.05] active:scale-[0.98]">
                            <Loader2 v-if="form.processing" class="mr-3 h-5 w-5 animate-spin" />
                            <UserPlus v-else class="mr-3 h-5 w-5" />
                            Initialize Synchronization
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Strategic Dialogs -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-[480px] rounded-[2.5rem] border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-10">
                    <div class="flex items-center gap-5 mb-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-primary/10 text-primary shadow-sm ring-1 ring-primary/5">
                            <AlertTriangle class="h-7 w-7" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-xl font-bold tracking-tight text-foreground uppercase">Confirmation protocol</h3>
                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Registry integrity check</p>
                        </div>
                    </div>
                    
                    <p class="text-sm font-bold leading-relaxed text-muted-foreground">
                        You are initiating the enrollment of <span class="text-foreground uppercase font-black">{{ form.first_name }} {{ form.last_name }}</span> into the institutional faculty matrix. This action is auditable.
                    </p>
                </div>

                <div class="flex items-center justify-between gap-4 bg-muted/10 p-8 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-12 rounded-2xl px-8 text-[10px] font-bold tracking-widest uppercase">Abort</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="h-12 rounded-2xl bg-primary px-10 text-[10px] font-bold tracking-widest text-white uppercase shadow-lg shadow-primary/20">Authorize Sync</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="successOpen" @update:open="successOpen = $event">
             <DialogContent class="sm:max-w-[480px] rounded-[2.5rem] border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-12 text-center">
                    <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-[2.5rem] bg-emerald-50 text-emerald-500 shadow-sm ring-1 ring-emerald-100">
                        <CheckCircle2 class="h-12 w-12" />
                    </div>
                    <h3 class="text-2xl font-black tracking-tight text-foreground uppercase mb-4">Enrollment Success</h3>
                    <p class="text-sm font-bold text-muted-foreground leading-relaxed uppercase opacity-60">The faculty node has been successfully synchronized and persisted in the global registry.</p>
                </div>
                <div class="flex items-center justify-center p-10 bg-muted/10 border-t border-border/50">
                    <Button @click="resetForm" class="h-14 w-full rounded-2xl bg-slate-900 px-12 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-xl transition-all hover:bg-slate-800">Dismiss Intelligence</Button>
                </div>
            </DialogContent>
        </Dialog>

        <StaffBulkUploadModal v-model:open="bulkUploadOpen" @uploaded="() => router.reload()" />
    </AppLayout>
</template>
