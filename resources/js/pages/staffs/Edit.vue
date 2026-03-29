<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft, CheckCircle2, Users, Loader2,
    Save, Edit, AlertTriangle, Briefcase,
    User, Key, Trash2
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import { Camera } from 'lucide-vue-next';
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
    { title: 'Staffs', href: '/staffs' },
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
    password: '',
    password_confirmation: '',
    status: props.teacher.status,
    role: props.teacher.user?.roles?.[0]?.name ?? '',
    photo: null as File | null,
});

const photoPreview = ref<string | undefined>(undefined);

watch(() => form.photo, (newPhoto) => {
    if (newPhoto) {
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(newPhoto);
    } else {
        photoPreview.value = undefined;
    }
});

const confirmOpen = ref(false);
const deleteOpen = ref(false);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    // Note: Using post with _method: 'PUT' for file upload compatibility
    form.post(`/staffs/${props.teacher.id}`, {
        onSuccess: () => {
            // Success handling via flash messages
        },
    });
};

const deleteTeacher = () => {
    deleteOpen.value = false;
    form.delete(`/staffs/${props.teacher.id}`, {
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
                        <Link :href="`/staffs/${teacher.id}`"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Edit Staff</h1>
                        <p class="text-muted-foreground">Modify professional profile for {{ teacher.first_name }} {{ teacher.last_name }}</p>
                    </div>
                </div>
                <Button variant="destructive" size="sm" @click="deleteOpen = true">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete Staff
                </Button>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 pb-12">
                <!-- Left Sidebar: Profile Photo & Key Info -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-border bg-card shadow-xl shadow-purple-500/5 transition-all">
                        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 p-8 text-white relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
                            <div class="relative z-10 flex flex-col items-center gap-6">
                                <ProfilePhotoUpload
                                    v-model="form.photo"
                                    :error="form.errors.photo"
                                >
                                    <template #default="{ isUploading }">
                                        <div class="h-40 w-40 rounded-[2.5rem] overflow-hidden border-4 border-white/20 shadow-2xl bg-white/10 backdrop-blur-md relative group cursor-pointer transition-transform duration-500 hover:scale-[1.02]">
                                            <img v-if="photoPreview || teacher.photo_url" :src="photoPreview || (teacher.photo_url ?? undefined)" class="h-full w-full object-cover" />
                                            <div v-else class="h-full w-full flex items-center justify-center text-white/40 font-bold text-4xl">
                                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                            </div>
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                                <Camera class="h-8 w-8 text-white" />
                                                <span class="text-[10px] font-bold uppercase tracking-widest text-white">Capture New</span>
                                            </div>
                                        </div>
                                    </template>
                                </ProfilePhotoUpload>
                                <div class="text-center">
                                    <h3 class="text-xl font-bold tracking-tight">{{ teacher.first_name }} {{ teacher.last_name }}</h3>
                                    <p class="text-xs text-white/60 mt-1 font-medium">Update institutional profile photo.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="space-y-2">
                                <Label for="role" class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">System Access Role *</Label>
                                <select id="role" v-model="form.role" class="h-12 w-full rounded-2xl border-border bg-muted/30 px-4 text-sm font-bold focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat uppercase tracking-tight" required>
                                    <option value="">Select Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name.replace('_', ' ').toUpperCase() }}</option>
                                </select>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div class="space-y-2">
                                <Label for="status" class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Current Status *</Label>
                                <select id="status" v-model="form.status" class="h-12 w-full rounded-2xl border-border bg-muted/30 px-4 text-sm font-bold focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-border bg-card p-6 shadow-sm flex items-center justify-between group transition-all hover:bg-muted/30">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-600">
                                <Briefcase class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Employee ID</p>
                                <p class="text-sm font-bold text-foreground">{{ teacher.staff_number }}</p>
                            </div>
                        </div>
                        <Badge variant="outline" class="rounded-lg bg-purple-50 text-purple-600 border-purple-100 font-bold uppercase text-[9px]">Verified</Badge>
                    </div>
                </div>

                <!-- Right Column: Form Sections -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Personal Information -->
                    <div class="overflow-hidden rounded-[2rem] border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/20 px-8 py-5">
                            <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                <User class="h-5 w-5 text-purple-600" />
                                Personal Information
                            </h2>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">First Name *</Label>
                                    <Input v-model="form.first_name" required class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Middle Name</Label>
                                    <Input v-model="form.middle_name" class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                    <InputError :message="form.errors.middle_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Last Name *</Label>
                                    <Input v-model="form.last_name" required class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                    <InputError :message="form.errors.last_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Gender *</Label>
                                    <select v-model="form.gender" class="h-12 w-full rounded-2xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <InputError :message="form.errors.gender" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Details -->
                    <div class="overflow-hidden rounded-[2rem] border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/20 px-8 py-5">
                            <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                <Briefcase class="h-5 w-5 text-purple-600" />
                                Professional Details
                            </h2>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Staff Number *</Label>
                                    <Input v-model="form.staff_number" required class="h-12 rounded-2xl border-border bg-muted/20 px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-bold uppercase tracking-tight" />
                                    <InputError :message="form.errors.staff_number" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Department *</Label>
                                    <select v-model="form.department_id" class="h-12 w-full rounded-2xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-purple-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" required>
                                        <option value="">Select Department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.department_id" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Credentials -->
                    <div class="overflow-hidden rounded-[2rem] border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/20 px-8 py-5">
                            <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                <Key class="h-5 w-5 text-purple-600" />
                                Account Credentials
                            </h2>
                        </div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Email Address *</Label>
                                    <Input v-model="form.email" type="email" required class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Phone Number *</Label>
                                    <Input v-model="form.phone" required class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                    <InputError :message="form.errors.phone" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">New Password (Optional)</Label>
                                    <Input v-model="form.password" type="password" placeholder="Leave blank to keep current" class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                    <InputError :message="form.errors.password" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest ml-1">Confirm New Password</Label>
                                    <Input v-model="form.password_confirmation" type="password" class="h-12 rounded-2xl border-border bg-background px-4 focus:ring-2 focus:ring-purple-600/10 transition-all font-medium" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <Button type="button" variant="ghost" class="text-muted-foreground hover:text-foreground font-bold px-10 h-14 rounded-2xl transition-all" as-child>
                            <Link :href="`/staffs/${teacher.id}`">Cancel Changes</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-14 px-12 rounded-2xl bg-foreground text-background hover:bg-foreground/90 font-bold shadow-xl shadow-foreground/10 transition-all border-0">
                            <Loader2 v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                            <Save v-else class="mr-2 h-5 w-5" />
                            Update Institutional Profile
                        </Button>
                    </div>
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
                    <DialogTitle class="text-destructive">Delete staff account?</DialogTitle>
                    <DialogDescription>
                        This will permanently delete the staff record and their associated user account. This action cannot be undone.
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
