<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft, CheckCircle2, Users, Loader2,
    Save, Edit, AlertTriangle, Briefcase,
    User, Key, Trash2
} from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
}

const props = defineProps<{
    teacher: Teacher;
    departments: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string }>;
    designations: Array<{ id: number; name: string }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: 'Edit Teacher', href: `/teachers/${props.teacher.id}/edit` },
];

const form = useForm({
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
    photo: null as File | null,
});

const confirmOpen = ref(false);
const deleteOpen = ref(false);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.put(`/teachers/${props.teacher.id}`, {
        onSuccess: () => {
            // Success handling via flash messages
        },
    });
};

const deleteTeacher = () => {
    deleteOpen.value = false;
    form.delete(`/teachers/${props.teacher.id}`, {
        onSuccess: () => {
            // Redirect happens in controller
        }
    });
};
</script>

<template>
    <Head :title="`Edit ${teacher.first_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="`/teachers/${teacher.id}`"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Edit Teacher</h1>
                        <p class="text-muted-foreground">Modify professional profile for {{ teacher.first_name }} {{ teacher.last_name }}</p>
                    </div>
                </div>
                <Button variant="destructive" size="sm" @click="deleteOpen = true">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete Teacher
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Personal Information -->
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <User class="h-5 w-5 text-purple-600" />
                            Personal Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="first_name">First Name *</Label>
                                <Input id="first_name" v-model="form.first_name" required />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="middle_name">Middle Name</Label>
                                <Input id="middle_name" v-model="form.middle_name" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name">Last Name *</Label>
                                <Input id="last_name" v-model="form.last_name" required />
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
                                <Input id="id_number" v-model="form.id_number" />
                                <InputError :message="form.errors.id_number" />
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
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="staff_number">Staff Number *</Label>
                                <Input id="staff_number" v-model="form.staff_number" required />
                                <InputError :message="form.errors.staff_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="tsc_number">TSC Number</Label>
                                <Input id="tsc_number" v-model="form.tsc_number" />
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
                                <Label for="contract_type">Contract Type</Label>
                                <select id="contract_type" v-model="form.contract_type" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="basic_salary">Basic Salary (KES)</Label>
                                <Input id="basic_salary" v-model="form.basic_salary" type="number" />
                                <InputError :message="form.errors.basic_salary" />
                            </div>
                            <div class="space-y-2">
                                <Label for="status">Status *</Label>
                                <select id="status" v-model="form.status" class="h-10 w-full rounded-md border bg-background px-3 text-sm" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Setup -->
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <Key class="h-5 w-5 text-purple-600" />
                            Account Credentials
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email">Email Address *</Label>
                                <Input id="email" v-model="form.email" type="email" required />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="phone">Phone Number *</Label>
                                <Input id="phone" v-model="form.phone" required />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password">New Password (Optional)</Label>
                                <Input id="password" v-model="form.password" type="password" placeholder="Leave blank to keep current" />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm New Password</Label>
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="outline" as-child>
                        <Link :href="`/teachers/${teacher.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Update Teacher
                    </Button>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Save Changes?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to update the profile for <strong>{{ `${teacher.first_name} ${teacher.last_name}` }}</strong>?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="confirmOpen = false">Cancel</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing">
                        Confirm Update
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog :open="deleteOpen" @update:open="deleteOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle class="text-destructive">Delete teacher account?</DialogTitle>
                    <DialogDescription>
                        This will permanently delete the teacher record and their associated user account. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="deleteOpen = false">Keep Account</Button>
                    <Button variant="destructive" @click="deleteTeacher" :disabled="form.processing">
                        Yes, Delete Forever
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
