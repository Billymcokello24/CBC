<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Download, GraduationCap, Loader2, Save, Upload, UserPlus, AlertTriangle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
import BulkUploadModal from './partials/BulkUploadModal.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grades: Array<{ id: number; name: string; code: string; level_order: number }>;
    classes: Array<{ id: number; name: string; grade_level_id: number | null; grade_name: string | null; stream_name: string | null }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Add Student', href: '/students/create' },
];

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    admission_number: '',
    gender: 'male',
    date_of_birth: '',
    grade_id: '',
    class_id: '',
    county: '',
    boarding_status: 'day',
    status: 'active',
    guardian_name: '',
    guardian_email: '',
    guardian_phone: '',
    guardian_password: '',
    guardian_password_confirmation: '',
});

const filteredClasses = computed(() => {
    if (!form.grade_id) return [];
    const selectedGradeId = Number(form.grade_id);

    return props.classes.filter((schoolClass) => schoolClass.grade_level_id === selectedGradeId);
});

watch(
    () => form.grade_id,
    () => {
        if (!filteredClasses.value.some((schoolClass) => String(schoolClass.id) === form.class_id)) {
            form.class_id = '';
        }
    },
);

const confirmOpen = ref(false);
const successOpen = ref(false);
const bulkUploadOpen = ref(false);
const createdStudentId = ref<number | null>(null);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.transform((data) => ({
        ...data,
        class_id: data.class_id ? Number(data.class_id) : null,
    })).post('/students', {
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

const bulkForm = useForm<{ file: File | null }>({ file: null });
const bulkFileName = ref('');

const handleBulkFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    bulkForm.file = file;
    bulkFileName.value = file?.name ?? '';
};

const uploadBulkStudents = () => {
    bulkForm.post('/students/bulk-upload', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Student" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Add New Student</h1>
                        <p class="text-muted-foreground">Register a new student in the system</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                                <Upload class="h-4 w-4 text-primary" />
                            </div>
                            <h2 class="text-lg font-semibold text-foreground">Bulk Upload Students</h2>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Effortlessly add or update student records using our CSV template. Missing academic structures are automatically synchronized.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" as-child>
                            <a href="/students/template/download">
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
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <GraduationCap class="h-5 w-5 text-primary" />
                            Student Information
                        </h2>
                        <p class="text-xs text-muted-foreground">Basic identity and academic placement</p>
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
                                <Input id="middle_name" v-model="form.middle_name" placeholder="Kamau" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name">Last Name *</Label>
                                <Input id="last_name" v-model="form.last_name" placeholder="Mwangi" required />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="admission_number">Admission Number *</Label>
                                <Input id="admission_number" v-model="form.admission_number" placeholder="STU00666" required />
                                <InputError :message="form.errors.admission_number" />
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
                                <Label for="date_of_birth">Date of Birth *</Label>
                                <Input id="date_of_birth" v-model="form.date_of_birth" type="date" required />
                                <InputError :message="form.errors.date_of_birth" />
                            </div>
                            <div class="space-y-2">
                                <Label for="grade_id">Grade</Label>
                                <select id="grade_id" v-model="form.grade_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="">Select grade</option>
                                    <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">
                                        {{ grade.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="class_id">Class</Label>
                                <select id="class_id" v-model="form.class_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm" :disabled="!form.grade_id">
                                    <option value="">{{ form.grade_id ? 'Select class' : 'Select grade first' }}</option>
                                    <option v-for="schoolClass in filteredClasses" :key="schoolClass.id" :value="String(schoolClass.id)">
                                        {{ schoolClass.name }}{{ schoolClass.stream_name ? ` • ${schoolClass.stream_name}` : '' }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.class_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="county">County</Label>
                                <select id="county" v-model="form.county" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="">Select county</option>
                                    <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                </select>
                                <InputError :message="form.errors.county" />
                            </div>
                            <div class="space-y-2">
                                <Label for="boarding_status">Boarding Status *</Label>
                                <select id="boarding_status" v-model="form.boarding_status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="day">Day</option>
                                    <option value="boarding">Boarding</option>
                                </select>
                                <InputError :message="form.errors.boarding_status" />
                            </div>
                            <div class="space-y-2">
                                <Label for="status">Initial Status *</Label>
                                <select id="status" v-model="form.status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="border-b bg-muted/30 px-6 py-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <Save class="h-5 w-5 text-primary" />
                            Guardian / Parent Credentials
                        </h2>
                        <p class="text-xs text-muted-foreground">Setup account for parent/guardian portal access</p>
                    </div>
                    <div class="p-6">
                        <p class="mb-6 text-sm text-muted-foreground">
                            Optional. Add parent login details now so the guardian can sign in and access this student's content.
                        </p>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="guardian_name">Guardian / Parent Name</Label>
                                <Input id="guardian_name" v-model="form.guardian_name" placeholder="Jane Wanjiru" />
                                <InputError :message="form.errors.guardian_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_email">Guardian / Parent Email</Label>
                                <Input id="guardian_email" v-model="form.guardian_email" type="email" placeholder="parent@example.com" />
                                <InputError :message="form.errors.guardian_email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_phone">Guardian / Parent Phone</Label>
                                <Input id="guardian_phone" v-model="form.guardian_phone" placeholder="+2547XXXXXXXX" />
                                <InputError :message="form.errors.guardian_phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_password">Password</Label>
                                <Input id="guardian_password" v-model="form.guardian_password" type="password" placeholder="Minimum 8 characters" />
                                <InputError :message="form.errors.guardian_password" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <Label for="guardian_password_confirmation">Confirm Password</Label>
                                <Input id="guardian_password_confirmation" v-model="form.guardian_password_confirmation" type="password" placeholder="Re-enter password" />
                                <InputError :message="form.errors.guardian_password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="outline" as-child>
                        <Link href="/students">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Student
                    </Button>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-warning" />
                        Confirm Student Registration
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to register <strong>{{ form.first_name }} {{ form.last_name }}</strong>? 
                        Please ensure all details are correct before proceeding.
                    </DialogDescription>
                </DialogHeader>
                <div class="grid grid-cols-2 gap-4 py-4 text-sm">
                    <div class="rounded-lg bg-muted p-2">
                        <p class="text-xs text-muted-foreground uppercase font-semibold">Admission No</p>
                        <p>{{ form.admission_number }}</p>
                    </div>
                    <div class="rounded-lg bg-muted p-2">
                        <p class="text-xs text-muted-foreground uppercase font-semibold">Gender</p>
                        <p class="capitalize">{{ form.gender }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="confirmOpen = false">Review Details</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Yes, Register Student
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="sm:max-w-md p-0 overflow-hidden">
                <DialogHeader class="flex flex-col items-center justify-center px-6 pt-8 pb-4 text-center">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-50 border-4 border-white shadow-sm ring-1 ring-green-100">
                        <CheckCircle2 class="h-10 w-10 text-green-600" />
                    </div>
                    <DialogTitle class="text-2xl font-bold text-foreground">All Set!</DialogTitle>
                    <DialogDescription class="mt-2 text-sm text-muted-foreground leading-relaxed">
                        Student <span class="font-semibold text-foreground">{{ form.first_name }} {{ form.last_name }}</span> has been successfully registered. 
                        What would you like to do next?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-col gap-2 p-6 bg-muted/30 border-t sm:flex-row sm:justify-center">
                    <Button variant="outline" @click="resetForm" class="w-full sm:w-auto">
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Another
                    </Button>
                    <Button as-child class="w-full sm:w-auto">
                        <Link href="/students">
                            <GraduationCap class="mr-2 h-4 w-4" />
                            View All Students
                        </Link>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <BulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>
