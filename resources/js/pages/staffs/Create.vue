<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, CheckCircle2, Download, Users, Loader2, 
    Save, Upload, UserPlus, AlertTriangle, Briefcase, 
    User, Key, Fingerprint
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
        return `Register New ${props.preselectedRole.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}`;
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
        <div class="flex h-full flex-1 flex-col gap-8 p-6 max-w-[1600px] mx-auto animate-in fade-in duration-700">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="outline" size="icon" as-child class="h-11 w-11 rounded-2xl border-border bg-card shadow-sm hover:bg-muted transition-all">
                        <Link href="/staffs/directory"><ArrowLeft class="h-5 w-5 text-muted-foreground" /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ title }}</h1>
                            <Badge variant="outline" class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border-blue-100">
                                Institutional
                            </Badge>
                        </div>
                        <p class="text-sm font-medium text-muted-foreground mt-1">Onboard a new member to the institution's directory.</p>
                    </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md group">
                <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-purple-500/5 transition-transform duration-700 group-hover:scale-110"></div>
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-5">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-200">
                            <Users class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-foreground">Bulk Registration</h2>
                                <Badge class="bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 border-0 text-[10px] font-bold px-2 py-0">EFFICIENT</Badge>
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">Import multiple teacher records at once using our standardized template.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button variant="outline" class="h-11 rounded-xl border-border bg-background px-6 font-bold text-xs transition-all hover:bg-muted" as-child>
                            <a href="/staffs/template/download">
                                <Download class="mr-2 h-4 w-4 text-purple-600" />
                                Download Template
                            </a>
                        </Button>
                        <Button class="h-11 rounded-xl bg-purple-600 px-8 font-bold text-xs text-white shadow-lg shadow-purple-100 hover:bg-purple-700 transition-all border-0" @click="bulkUploadOpen = true">
                            <Upload class="mr-2 h-4 w-4" />
                            Upload CSV File
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-8 pb-12">
                <!-- Personal Information -->
                <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <div class="border-b border-border/50 bg-muted/20 px-8 py-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                <User class="h-5 w-5 text-purple-600" />
                                Personal Information
                            </h2>
                            <p class="text-xs text-muted-foreground mt-0.5">Identity and demographic details</p>
                        </div>
                        <ProfilePhotoUpload
                            v-model="form.photo"
                            :error="form.errors.photo"
                        />
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="first_name" class="text-xs font-semibold text-foreground ml-1">First Name *</Label>
                                <Input id="first_name" v-model="form.first_name" placeholder="John" required class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="middle_name" class="text-xs font-semibold text-foreground ml-1">Middle Name</Label>
                                <Input id="middle_name" v-model="form.middle_name" placeholder="Doe" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name" class="text-xs font-semibold text-foreground ml-1">Last Name *</Label>
                                <Input id="last_name" v-model="form.last_name" placeholder="Smith" required class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="gender" class="text-xs font-semibold text-foreground ml-1">Gender *</Label>
                                <select id="gender" v-model="form.gender" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError :message="form.errors.gender" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_of_birth" class="text-xs font-semibold text-foreground ml-1">Date of Birth</Label>
                                <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.date_of_birth" />
                            </div>
                            <div class="space-y-2">
                                <Label for="id_number" class="text-xs font-semibold text-foreground ml-1">ID / Passport Number</Label>
                                <Input id="id_number" v-model="form.id_number" placeholder="12345678" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.id_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="nationality" class="text-xs font-semibold text-foreground ml-1">Nationality</Label>
                                <Input id="nationality" v-model="form.nationality" placeholder="Kenyan" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.nationality" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Details -->
                <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <div class="border-b border-border/50 bg-muted/20 px-8 py-5">
                        <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                            <Briefcase class="h-5 w-5 text-purple-600" />
                            Professional Details
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Employment and assignment information</p>
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="staff_number" class="text-xs font-semibold text-foreground ml-1">Staff Number *</Label>
                                <Input id="staff_number" v-model="form.staff_number" placeholder="TCH1001" required class="h-11 rounded-xl border-border bg-muted/20 px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-bold uppercase tracking-tight" />
                                <InputError :message="form.errors.staff_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="tsc_number" class="text-xs font-semibold text-foreground ml-1">TSC Number</Label>
                                <Input id="tsc_number" v-model="form.tsc_number" placeholder="TSC123456" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.tsc_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="department_id" class="text-xs font-semibold text-foreground ml-1">Department *</Label>
                                <select id="department_id" v-model="form.department_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" required>
                                    <option value="">Select Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <InputError :message="form.errors.department_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="staff_category_id" class="text-xs font-semibold text-foreground ml-1">Staff Category</Label>
                                <select id="staff_category_id" v-model="form.staff_category_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="">Select Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="staff_designation_id" class="text-xs font-semibold text-foreground ml-1">Designation</Label>
                                <select id="staff_designation_id" v-model="form.staff_designation_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="">Select Designation</option>
                                    <option v-for="desig in designations" :key="desig.id" :value="desig.id">{{ desig.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="contract_type" class="text-xs font-semibold text-foreground ml-1">Contract Type</Label>
                                <select id="contract_type" v-model="form.contract_type" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="date_joined" class="text-xs font-semibold text-foreground ml-1">Date Joined</Label>
                                <Input id="date_joined" v-model="form.date_joined" type="date" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.date_joined" />
                            </div>
                            <div class="space-y-2">
                                <Label for="basic_salary" class="text-xs font-semibold text-foreground ml-1">Basic Salary (KES)</Label>
                                <Input id="basic_salary" v-model="form.basic_salary" type="number" placeholder="50000" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.basic_salary" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Setup -->
                <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <div class="border-b border-border/50 bg-muted/20 px-8 py-5">
                        <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                            <Key class="h-5 w-5 text-purple-600" />
                            Account Setup
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Login credentials and communication</p>
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email" class="text-xs font-semibold text-foreground ml-1">Email Address *</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="teacher@example.com" required class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="phone" class="text-xs font-semibold text-foreground ml-1">Phone Number *</Label>
                                <Input id="phone" v-model="form.phone" placeholder="+2547XXXXXXXX" required class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password" class="text-xs font-semibold text-foreground ml-1">Password *</Label>
                                <Input id="password" v-model="form.password" type="password" placeholder="Minimum 8 characters" required class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation" class="text-xs font-semibold text-foreground ml-1">Confirm Password *</Label>
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="Re-enter password" required class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                            <div class="space-y-2">
                                <Label for="status" class="text-xs font-semibold text-foreground ml-1">Initial Status *</Label>
                                <select id="status" v-model="form.status" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                            <div class="space-y-2">
                                <Label for="role" class="text-xs font-semibold text-foreground ml-1">System Access Role *</Label>
                                <select id="role" v-model="form.role" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" required>
                                    <option value="">Select Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name.replace('_', ' ').toUpperCase() }}</option>
                                </select>
                                <InputError :message="form.errors.role" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-wrap items-center justify-between gap-6 pt-10 border-t border-border/60">
                    <div class="hidden sm:flex items-center gap-3 px-4 py-2 rounded-2xl bg-purple-50 text-purple-700 border border-purple-100/50 shadow-sm">
                        <div class="h-2 w-2 rounded-full bg-purple-500 animate-pulse"></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Cloud Sync Active</span>
                    </div>
                    <div class="flex gap-4 ml-auto">
                        <Button type="button" variant="ghost" class="text-muted-foreground hover:text-foreground font-bold px-8 h-12 rounded-xl transition-all" as-child>
                            <Link href="/staffs">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-foreground text-background hover:bg-foreground/90 font-bold shadow-lg shadow-foreground/10 transition-all border-0">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Register Staff
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-md p-0 overflow-hidden border-0 shadow-2xl">
                <div class="relative p-8 text-center pt-12">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-500"></div>
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-purple-50 border-4 border-white shadow-sm ring-1 ring-purple-100 animate-in zoom-in duration-300">
                        <AlertTriangle class="h-10 w-10 text-purple-600" />
                    </div>
                    <DialogHeader>
                        <DialogTitle class="text-2xl font-bold text-center text-foreground">Confirm Registration</DialogTitle>
                        <DialogDescription class="text-sm text-muted-foreground text-center mt-3 px-4 leading-relaxed">
                            Are you sure you want to register <span class="font-bold text-foreground">{{ form.first_name }} {{ form.last_name }}</span> as a new teacher staff member?
                        </DialogDescription>
                    </DialogHeader>
                </div>
                <DialogFooter class="flex flex-col gap-2 p-6 bg-muted/30 border-t sm:flex-row sm:justify-center">
                    <Button variant="ghost" @click="confirmOpen = false" class="w-full sm:w-auto font-bold h-11 px-8 rounded-xl hover:bg-background transition-all">
                        Review Details
                    </Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="w-full sm:w-auto h-11 px-10 rounded-xl bg-purple-600 text-white font-bold shadow-lg shadow-purple-100 hover:bg-purple-700 transition-all border-0">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Yes, Register Staff
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="sm:max-w-md p-0 overflow-hidden border-0 shadow-2xl">
                <div class="relative p-8 text-center pt-12">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-500"></div>
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-emerald-50 border-8 border-white shadow-sm ring-1 ring-emerald-100 animate-in zoom-in bounce-in duration-500">
                        <CheckCircle2 class="h-12 w-12 text-emerald-600" />
                    </div>
                    <h3 class="text-3xl font-bold text-foreground">Educator Registered!</h3>
                    <p class="mt-4 text-sm text-muted-foreground leading-relaxed px-6">
                        Staff member <span class="font-bold text-foreground">{{ form.first_name }} {{ form.last_name }}</span> has been successfully added to the system directory.
                    </p>
                </div>
                <DialogFooter class="flex flex-col gap-2 p-6 bg-muted/30 border-t sm:flex-row sm:justify-center">
                    <Button variant="outline" @click="resetForm" class="w-full sm:w-auto h-11 px-6 rounded-xl border-border bg-background font-bold transition-all hover:bg-muted">
                        <UserPlus class="mr-2 h-4 w-4 text-purple-600" />
                        Add Another
                    </Button>
                    <Button as-child class="w-full sm:w-auto h-11 px-8 rounded-xl bg-foreground text-background font-bold shadow-lg shadow-foreground/10 transition-all border-0">
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
