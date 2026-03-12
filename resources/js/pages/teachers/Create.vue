<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, CheckCircle2, Download, Users, Loader2, 
    Save, Upload, UserPlus, AlertTriangle, Briefcase, 
    User, Key, Fingerprint
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import TeacherBulkUploadModal from './partials/TeacherBulkUploadModal.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    departments: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string }>;
    designations: Array<{ id: number; name: string }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: 'Add Teacher', href: '/teachers/create' },
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

const handlePhotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.photo = target.files[0];
    }
};
</script>

<template>
    <Head title="Add Teacher" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/teachers"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Add New Teacher</h1>
                        <p class="text-muted-foreground">Register a new teaching staff member</p>
                    </div>
                </div>
            </div>

            <!-- Bulk Upload Card -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10">
                                <Upload class="h-4 w-4 text-purple-600" />
                            </div>
                            <h2 class="text-lg font-semibold text-foreground">Bulk Upload Teachers</h2>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Import multiple teachers using a CSV file.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" as-child>
                            <a href="/teachers/template/download">
                                <Download class="mr-2 h-4 w-4" />
                                Download Template
                            </a>
                        </Button>
                        <Button size="sm" @click="bulkUploadOpen = true">
                            <Upload class="mr-2 h-4 w-4" />
                            Start Bulk Upload
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Personal Information -->
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <User class="h-5 w-5 text-purple-600" />
                            Personal Information
                        </h2>
                        <p class="text-xs text-muted-foreground">Identity and demographic details</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="first_name">First Name *</Label>
                                <Input id="first_name" v-model="form.first_name" placeholder="John" required />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="middle_name">Middle Name</Label>
                                <Input id="middle_name" v-model="form.middle_name" placeholder="Doe" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name">Last Name *</Label>
                                <Input id="last_name" v-model="form.last_name" placeholder="Smith" required />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="gender">Gender *</Label>
                                <select id="gender" v-model="form.gender" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError :message="form.errors.gender" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_of_birth">Date of Birth</Label>
                                <Input id="date_of_birth" v-model="form.date_of_birth" type="date" />
                                <InputError :message="form.errors.date_of_birth" />
                            </div>
                            <div class="space-y-2">
                                <Label for="id_number">ID / Passport Number</Label>
                                <Input id="id_number" v-model="form.id_number" placeholder="12345678" />
                                <InputError :message="form.errors.id_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="nationality">Nationality</Label>
                                <Input id="nationality" v-model="form.nationality" placeholder="Kenyan" />
                                <InputError :message="form.errors.nationality" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Details -->
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <Briefcase class="h-5 w-5 text-purple-600" />
                            Professional Details
                        </h2>
                        <p class="text-xs text-muted-foreground">Employment and assignment information</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="staff_number">Staff Number *</Label>
                                <Input id="staff_number" v-model="form.staff_number" placeholder="TCH1001" required />
                                <InputError :message="form.errors.staff_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="tsc_number">TSC Number</Label>
                                <Input id="tsc_number" v-model="form.tsc_number" placeholder="TSC123456" />
                                <InputError :message="form.errors.tsc_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="department_id">Department *</Label>
                                <select id="department_id" v-model="form.department_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm" required>
                                    <option value="">Select Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <InputError :message="form.errors.department_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="staff_category_id">Staff Category</Label>
                                <select id="staff_category_id" v-model="form.staff_category_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="">Select Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="staff_designation_id">Designation</Label>
                                <select id="staff_designation_id" v-model="form.staff_designation_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="">Select Designation</option>
                                    <option v-for="desig in designations" :key="desig.id" :value="desig.id">{{ desig.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="contract_type">Contract Type</Label>
                                <select id="contract_type" v-model="form.contract_type" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="date_joined">Date Joined</Label>
                                <Input id="date_joined" v-model="form.date_joined" type="date" />
                                <InputError :message="form.errors.date_joined" />
                            </div>
                            <div class="space-y-2">
                                <Label for="basic_salary">Basic Salary (KES)</Label>
                                <Input id="basic_salary" v-model="form.basic_salary" type="number" placeholder="50000" />
                                <InputError :message="form.errors.basic_salary" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Setup -->
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <Key class="h-5 w-5 text-purple-600" />
                            Account Setup
                        </h2>
                        <p class="text-xs text-muted-foreground">Login credentials and communication</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email">Email Address *</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="teacher@example.com" required />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="phone">Phone Number *</Label>
                                <Input id="phone" v-model="form.phone" placeholder="+2547XXXXXXXX" required />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password">Password *</Label>
                                <Input id="password" v-model="form.password" type="password" placeholder="Minimum 8 characters" required />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password *</Label>
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="Re-enter password" required />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                            <div class="space-y-2">
                                <Label for="status">Initial Status *</Label>
                                <select id="status" v-model="form.status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="outline" as-child>
                        <Link href="/teachers">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Teacher
                    </Button>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-amber-500" />
                        Confirm Teacher Registration
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to register <strong>{{ form.first_name }} {{ form.last_name }}</strong> as a teacher? 
                        This will create a user account for them.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="confirmOpen = false">Review Details</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Yes, Register Teacher
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="sm:max-w-md p-0 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-50 border-4 border-white shadow-sm ring-1 ring-green-100">
                        <CheckCircle2 class="h-10 w-10 text-green-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-foreground">Teacher Added!</h3>
                    <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                        Teacher <span class="font-semibold text-foreground">{{ form.first_name }} {{ form.last_name }}</span> has been successfully registered. 
                    </p>
                </div>
                <DialogFooter class="flex flex-col gap-2 p-6 bg-muted/30 border-t sm:flex-row sm:justify-center">
                    <Button variant="outline" @click="resetForm" class="w-full sm:w-auto">
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Another
                    </Button>
                    <Button as-child class="w-full sm:w-auto">
                        <Link href="/teachers">
                            <Users class="mr-2 h-4 w-4" />
                            View All Teachers
                        </Link>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <TeacherBulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>
