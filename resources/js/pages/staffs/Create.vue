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
        return `Register New ${props.preselectedRole
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')}`;
    }
    return 'Register New User';
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: title.value, href: '/staffs/create' },
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
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-6 duration-700 fade-in"
        >
            <!-- Header section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-5">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-11 w-11 rounded-2xl border-border bg-card shadow-sm transition-all hover:bg-muted"
                    >
                        <Link href="/staffs/directory"
                            ><ArrowLeft class="h-5 w-5 text-muted-foreground"
                        /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1
                                class="text-3xl font-bold tracking-tight text-foreground"
                            >
                                {{ title }}
                            </h1>
                            <Badge
                                variant="outline"
                                class="rounded-lg border-blue-100 bg-blue-50 px-2.5 py-0.5 text-xs font-bold tracking-wider text-blue-600 uppercase"
                            >
                                Institutional
                            </Badge>
                        </div>
                        <p
                            class="mt-1 text-sm font-medium text-muted-foreground"
                        >
                            Onboard a new member to the institution's directory.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md"
            >
                <div
                    class="absolute -top-12 -right-12 h-48 w-48 rounded-full bg-purple-500/5 transition-transform duration-700 group-hover:scale-110"
                ></div>
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-center gap-5">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-200"
                        >
                            <Users class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-foreground">
                                    Bulk Registration
                                </h2>
                                <Badge
                                    class="border-0 bg-emerald-500/10 px-2 py-0 text-xs font-bold text-emerald-600 hover:bg-emerald-500/20"
                                    >EFFICIENT</Badge
                                >
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Import multiple teacher records at once using
                                our standardized template.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button
                            variant="outline"
                            class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold transition-all hover:bg-muted"
                            as-child
                        >
                            <a href="/staffs/template/download">
                                <Download
                                    class="mr-2 h-4 w-4 text-purple-600"
                                />
                                Download Template
                            </a>
                        </Button>
                        <Button
                            class="h-11 rounded-xl border-0 bg-purple-600 px-8 text-xs font-bold text-white shadow-lg shadow-purple-100 transition-all hover:bg-purple-700"
                            @click="bulkUploadOpen = true"
                        >
                            <Upload class="mr-2 h-4 w-4" />
                            Upload CSV File
                        </Button>
                    </div>
                </div>
            </div>

            <form
                @submit.prevent="submit"
                class="grid grid-cols-1 gap-8 pb-12 lg:grid-cols-12"
            >
                <!-- Left Sidebar: Profile Photo & Key Info -->
                <div class="space-y-6 lg:col-span-4">
                    <div
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-xl shadow-purple-500/5 transition-all"
                    >
                        <div
                            class="relative overflow-hidden bg-gradient-to-br from-purple-600 to-indigo-700 p-8 text-white"
                        >
                            <div
                                class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"
                            ></div>
                            <div
                                class="relative z-10 flex flex-col items-center gap-6"
                            >
                                <ProfilePhotoUpload
                                    v-model="form.photo"
                                    :error="form.errors.photo"
                                >
                                    <template #default="{ isUploading }">
                                        <div
                                            class="group relative h-40 w-40 cursor-pointer overflow-hidden rounded-xl border-4 border-white/20 bg-white/10 shadow-lg backdrop-blur-md transition-transform duration-500 hover:scale-[1.02]"
                                        >
                                            <div
                                                v-if="!form.photo"
                                                class="flex h-full w-full items-center justify-center text-white/40"
                                            >
                                                <User class="h-16 w-16" />
                                            </div>
                                            <div v-else class="h-full w-full">
                                                <!-- Preview handled internally by ProfilePhotoUpload in non-auto mode? 
                                                     Actually, I need to show the local preview here. -->
                                                <img
                                                    :src="photoPreview"
                                                    v-if="photoPreview"
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div
                                                class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                            >
                                                <Camera
                                                    class="h-8 w-8 text-white"
                                                />
                                                <span
                                                    class="text-xs font-bold tracking-tight text-white uppercase"
                                                    >Capture</span
                                                >
                                            </div>
                                        </div>
                                    </template>
                                </ProfilePhotoUpload>
                                <div class="text-center">
                                    <h3
                                        class="text-xl font-bold tracking-tight"
                                    >
                                        Profile Picture
                                    </h3>
                                    <p
                                        class="mt-1 text-xs font-medium text-white/60"
                                    >
                                        Capture a professional photo for the
                                        institutional directory.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6 p-8">
                            <div class="space-y-2">
                                <Label
                                    for="role"
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >System Access Role *</Label
                                >
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="h-12 w-full appearance-none rounded-2xl border border-border bg-muted/30 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat px-4 text-sm font-bold tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-purple-600/10"
                                    required
                                >
                                    <option value="">Select Role</option>
                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.name"
                                    >
                                        {{
                                            role.name
                                                .replace('_', ' ')
                                                .toUpperCase()
                                        }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    for="status"
                                    class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Initial Status *</Label
                                >
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="h-12 w-full appearance-none rounded-2xl border border-border bg-muted/30 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat px-4 text-sm font-bold transition-all outline-none focus:ring-2 focus:ring-purple-600/10"
                                    required
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group flex items-center justify-between rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:bg-muted/30"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600"
                            >
                                <Fingerprint class="h-5 w-5" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Auth Protocol
                                </p>
                                <p class="text-sm font-bold text-foreground">
                                    Cloud Sync Active
                                </p>
                            </div>
                        </div>
                        <div
                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        ></div>
                    </div>
                </div>

                <!-- Right Column: Multi-section form -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Personal Information -->
                    <div
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="border-b border-border/50 bg-muted/20 px-8 py-5"
                        >
                            <h2
                                class="flex items-center gap-2 text-base font-bold text-foreground"
                            >
                                <User class="h-5 w-5 text-purple-600" />
                                Personal Information
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Identity and demographic details
                            </p>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="first_name"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >First Name *</Label
                                    >
                                    <Input
                                        id="first_name"
                                        v-model="form.first_name"
                                        placeholder="John"
                                        required
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.first_name"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="middle_name"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Middle Name</Label
                                    >
                                    <Input
                                        id="middle_name"
                                        v-model="form.middle_name"
                                        placeholder="Doe"
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.middle_name"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="last_name"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Last Name *</Label
                                    >
                                    <Input
                                        id="last_name"
                                        v-model="form.last_name"
                                        placeholder="Smith"
                                        required
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.last_name"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="gender"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Gender *</Label
                                    >
                                    <select
                                        id="gender"
                                        v-model="form.gender"
                                        class="h-12 w-full appearance-none rounded-2xl border border-border bg-background bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat px-4 text-sm font-medium transition-all outline-none focus:ring-2 focus:ring-purple-600/10"
                                    >
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <InputError :message="form.errors.gender" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="date_of_birth"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Date of Birth</Label
                                    >
                                    <Input
                                        id="date_of_birth"
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.date_of_birth"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="id_number"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >ID / Passport Number</Label
                                    >
                                    <Input
                                        id="id_number"
                                        v-model="form.id_number"
                                        placeholder="12345678"
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.id_number"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Details -->
                    <div
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="border-b border-border/50 bg-muted/20 px-8 py-5"
                        >
                            <h2
                                class="flex items-center gap-2 text-base font-bold text-foreground"
                            >
                                <Briefcase class="h-5 w-5 text-purple-600" />
                                Professional Details
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Employment and assignment information
                            </p>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="staff_number"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Staff Number *</Label
                                    >
                                    <Input
                                        id="staff_number"
                                        v-model="form.staff_number"
                                        placeholder="TCH1001"
                                        required
                                        class="h-12 rounded-2xl border-border bg-muted/20 px-4 font-bold tracking-tight uppercase transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.staff_number"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="tsc_number"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >TSC Number</Label
                                    >
                                    <Input
                                        id="tsc_number"
                                        v-model="form.tsc_number"
                                        placeholder="TSC123456"
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.tsc_number"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="department_id"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Department *</Label
                                    >
                                    <select
                                        id="department_id"
                                        v-model="form.department_id"
                                        class="h-12 w-full appearance-none rounded-2xl border border-border bg-background bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat px-4 text-sm font-medium transition-all outline-none focus:ring-2 focus:ring-purple-600/10"
                                        required
                                    >
                                        <option value="">
                                            Select Department
                                        </option>
                                        <option
                                            v-for="dept in departments"
                                            :key="dept.id"
                                            :value="dept.id"
                                        >
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.department_id"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="contract_type"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Contract Type</Label
                                    >
                                    <select
                                        id="contract_type"
                                        v-model="form.contract_type"
                                        class="h-12 w-full appearance-none rounded-2xl border border-border bg-background bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat px-4 text-sm font-medium transition-all outline-none focus:ring-2 focus:ring-purple-600/10"
                                    >
                                        <option value="Permanent">
                                            Permanent
                                        </option>
                                        <option value="Contract">
                                            Contract
                                        </option>
                                        <option value="Part-time">
                                            Part-time
                                        </option>
                                        <option value="Internship">
                                            Internship
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Communication -->
                    <div
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="border-b border-border/50 bg-muted/20 px-8 py-5"
                        >
                            <h2
                                class="flex items-center gap-2 text-base font-bold text-foreground"
                            >
                                <Key class="h-5 w-5 text-purple-600" />
                                Account Setup
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Login credentials and communication
                            </p>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="email"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Email Address *</Label
                                    >
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="teacher@example.com"
                                        required
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="phone"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Phone Number *</Label
                                    >
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        placeholder="+2547XXXXXXXX"
                                        required
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError :message="form.errors.phone" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="password"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Initial Password *</Label
                                    >
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        placeholder="Minimum 8 characters"
                                        required
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                    <InputError
                                        :message="form.errors.password"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="password_confirmation"
                                        class="ml-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                        >Confirm Password *</Label
                                    >
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        placeholder="Re-enter password"
                                        required
                                        class="h-12 rounded-2xl border-border bg-background px-4 font-medium transition-all focus:ring-2 focus:ring-purple-600/10"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Hub -->
                    <div
                        class="flex flex-wrap items-center justify-end gap-4 pt-4"
                    >
                        <Button
                            type="button"
                            variant="ghost"
                            class="h-14 rounded-2xl px-10 font-bold text-muted-foreground transition-all hover:text-foreground"
                            as-child
                        >
                            <Link href="/staffs">Cancel Registration</Link>
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-14 rounded-2xl border-0 bg-gradient-to-r from-purple-600 to-indigo-600 px-12 font-bold text-white shadow-xl shadow-purple-500/20 transition-all hover:shadow-purple-500/30"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-5 w-5 animate-spin"
                            />
                            <UserPlus v-else class="mr-2 h-5 w-5" />
                            Register Staff Member
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent
                class="overflow-hidden border-0 p-0 shadow-lg sm:max-w-md"
            >
                <div class="relative p-8 pt-12 text-center">
                    <div
                        class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-500"
                    ></div>
                    <div
                        class="mx-auto mb-6 flex h-20 w-20 animate-in items-center justify-center rounded-full border-4 border-white bg-purple-50 shadow-sm ring-1 ring-purple-100 duration-300 zoom-in"
                    >
                        <AlertTriangle class="h-10 w-10 text-purple-600" />
                    </div>
                    <DialogHeader>
                        <DialogTitle
                            class="text-center text-2xl font-bold text-foreground"
                            >Confirm Registration</DialogTitle
                        >
                        <DialogDescription
                            class="mt-3 px-4 text-center text-sm leading-relaxed text-muted-foreground"
                        >
                            Are you sure you want to register
                            <span class="font-bold text-foreground"
                                >{{ form.first_name }}
                                {{ form.last_name }}</span
                            >
                            as a new teacher staff member?
                        </DialogDescription>
                    </DialogHeader>
                </div>
                <DialogFooter
                    class="flex flex-col gap-2 border-t bg-muted/30 p-6 sm:flex-row sm:justify-center"
                >
                    <Button
                        variant="ghost"
                        @click="confirmOpen = false"
                        class="h-11 w-full rounded-xl px-8 font-bold transition-all hover:bg-background sm:w-auto"
                    >
                        Review Details
                    </Button>
                    <Button
                        @click="confirmSubmit"
                        :disabled="form.processing"
                        class="h-11 w-full rounded-xl border-0 bg-purple-600 px-10 font-bold text-white shadow-lg shadow-purple-100 transition-all hover:bg-purple-700 sm:w-auto"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Yes, Register Staff
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent
                class="overflow-hidden border-0 p-0 shadow-lg sm:max-w-md"
            >
                <div class="relative p-8 pt-12 text-center">
                    <div
                        class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-500"
                    ></div>
                    <div
                        class="bounce-in mx-auto mb-6 flex h-24 w-24 animate-in items-center justify-center rounded-full border-8 border-white bg-emerald-50 shadow-sm ring-1 ring-emerald-100 duration-500 zoom-in"
                    >
                        <CheckCircle2 class="h-12 w-12 text-emerald-600" />
                    </div>
                    <h3 class="text-3xl font-bold text-foreground">
                        Educator Registered!
                    </h3>
                    <p
                        class="mt-4 px-6 text-sm leading-relaxed text-muted-foreground"
                    >
                        Staff member
                        <span class="font-bold text-foreground"
                            >{{ form.first_name }} {{ form.last_name }}</span
                        >
                        has been successfully added to the system directory.
                    </p>
                </div>
                <DialogFooter
                    class="flex flex-col gap-2 border-t bg-muted/30 p-6 sm:flex-row sm:justify-center"
                >
                    <Button
                        variant="outline"
                        @click="resetForm"
                        class="h-11 w-full rounded-xl border-border bg-background px-6 font-bold transition-all hover:bg-muted sm:w-auto"
                    >
                        <UserPlus class="mr-2 h-4 w-4 text-purple-600" />
                        Add Another
                    </Button>
                    <Button
                        as-child
                        class="h-11 w-full rounded-xl border-0 bg-foreground px-8 font-bold text-background shadow-lg shadow-foreground/10 transition-all sm:w-auto"
                    >
                        <Link href="/staffs">
                            <Users class="mr-2 h-4 w-4" />
                            Return to Directory
                        </Link>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <StaffBulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>
