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
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grades: Array<{ id: number; name: string; code: string; level_order: number }>;
    classes: Array<{ id: number; name: string; grade_level_id: number | null; grade_name: string | null; stream_name: string | null }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learners', href: '/students' },
    { title: 'Add Learner', href: '/students/create' },
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
    photo: null as File | null,
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
const createdLearnerId = ref<number | null>(null);

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

const uploadBulkLearners = () => {
    bulkForm.post('/students/bulk-upload', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Learner" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 max-w-[1600px] mx-auto animate-in fade-in duration-700">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="outline" size="icon" as-child class="h-11 w-11 rounded-2xl border-border bg-card shadow-sm hover:bg-muted transition-all">
                        <Link href="/students"><ArrowLeft class="h-5 w-5 text-muted-foreground" /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-foreground">Enroll New Learner</h1>
                            <Badge variant="outline" class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-600 border-indigo-100">
                                Registration
                            </Badge>
                        </div>
                        <p class="text-sm font-medium text-muted-foreground mt-1">Provide the necessary details to register a new student in the system.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 bg-muted/30 p-1.5 rounded-2xl border border-border/50">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-background rounded-xl border border-border shadow-xs">
                        <div class="h-5 w-5 rounded-full bg-indigo-600 flex items-center justify-center text-[10px] font-bold text-white">1</div>
                        <span class="text-xs font-bold text-foreground">Form Entry</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 opacity-50">
                        <div class="h-5 w-5 rounded-full bg-muted flex items-center justify-center text-[10px] font-bold text-muted-foreground">2</div>
                        <span class="text-xs font-bold text-muted-foreground">Review</span>
                    </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm overflow-hidden relative group transition-all hover:shadow-md">
                <div class="absolute -right-10 -bottom-10 opacity-[0.03] group-hover:scale-110 transition-transform duration-1000 text-indigo-600">
                    <Upload class="h-48 w-48" />
                </div>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between relative z-10">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-100/50">
                            <Upload class="h-7 w-7" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-foreground flex items-center gap-2">
                                Bulk Enrollment
                                <Badge variant="secondary" class="text-[9px] font-bold uppercase tracking-tight bg-emerald-50 text-emerald-600 border-emerald-100/50">Efficient</Badge>
                            </h2>
                            <p class="text-sm text-muted-foreground mt-1 max-w-md">Save time by uploading multiple learner records at once using our standardized CSV template.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button variant="outline" class="h-11 border-border font-bold px-6 rounded-xl bg-background hover:bg-muted transition-all" as-child>
                            <a href="/students/template/download">
                                <Download class="mr-2 h-4 w-4 text-emerald-600" />
                                Download Template
                            </a>
                        </Button>
                        <Button class="h-11 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 rounded-xl shadow-lg shadow-indigo-200 transition-all border-0" @click="bulkUploadOpen = true">
                            <Upload class="mr-2 h-4 w-4" />
                            Upload CSV File
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-8 pb-12">
                <!-- Data Forms Split -->
                <div class="grid gap-8 lg:grid-cols-12">
                    <!-- Left: Core Info -->
                    <div class="lg:col-span-8 space-y-8">
                        <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                            <div class="border-b border-border/50 bg-muted/20 px-8 py-5 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                        <GraduationCap class="h-5 w-5 text-indigo-600" />
                                        Learner Information
                                    </h2>
                                    <p class="text-xs text-muted-foreground mt-0.5">Basic personal and identification details</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <ProfilePhotoUpload 
                                        v-model="form.photo" 
                                        :error="form.errors.photo"
                                    />
                                    <Badge variant="outline" class="text-[10px] font-bold text-muted-foreground/60 border-border uppercase tracking-tight h-5 px-2 bg-muted/30">Required</Badge>
                                </div>
                            </div>
                            <div class="p-8">
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="first_name" class="text-xs font-semibold text-foreground ml-1">First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" placeholder="e.g. John" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" required />
                                        <InputError :message="form.errors.first_name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="last_name" class="text-xs font-semibold text-foreground ml-1">Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" placeholder="e.g. Mwangi" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" required />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="admission_number" class="text-xs font-semibold text-foreground ml-1">Admission Number</Label>
                                        <Input id="admission_number" v-model="form.admission_number" placeholder="ADM-001" class="h-11 rounded-xl border-border bg-muted/20 px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold uppercase tracking-tight" required />
                                        <InputError :message="form.errors.admission_number" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="gender" class="text-xs font-semibold text-foreground ml-1">Gender</Label>
                                        <select id="gender" v-model="form.gender" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 outline-none border transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <InputError :message="form.errors.gender" />
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <Label for="date_of_birth" class="text-xs font-semibold text-foreground ml-1">Date of Birth</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" required />
                                        <InputError :message="form.errors.date_of_birth" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Placement -->
                    <div class="lg:col-span-4 space-y-8">
                        <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                             <div class="border-b border-border/50 bg-muted/20 px-6 py-5">
                                <h2 class="text-sm font-bold text-foreground flex items-center gap-2">
                                    <GraduationCap class="h-5 w-5 text-indigo-600" />
                                    School Placement
                                </h2>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="space-y-2">
                                    <Label for="grade_id" class="text-xs font-semibold text-foreground ml-1">Grade Level</Label>
                                    <select id="grade_id" v-model="form.grade_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                        <option value="">Select Grade</option>
                                        <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">
                                            {{ grade.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <Label for="class_id" class="text-xs font-semibold text-foreground ml-1">Stream / Class</Label>
                                    <select id="class_id" v-model="form.class_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 disabled:opacity-40 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" :disabled="!form.grade_id">
                                        <option value="">{{ form.grade_id ? 'Select Stream' : 'Grade Required' }}</option>
                                        <option v-for="schoolClass in filteredClasses" :key="schoolClass.id" :value="String(schoolClass.id)">
                                            {{ schoolClass.name }}{{ schoolClass.stream_name ? ` • ${schoolClass.stream_name}` : '' }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="county" class="text-xs font-semibold text-foreground ml-1">County of Origin</Label>
                                    <select id="county" v-model="form.county" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                        <option value="">Select County</option>
                                        <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                    </select>
                                    <InputError :message="form.errors.county" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="boarding_status" class="text-xs font-semibold text-foreground ml-1">Boarding Status</Label>
                                    <select id="boarding_status" v-model="form.boarding_status" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                        <option value="day">Day Scholar</option>
                                        <option value="boarding">Boarding Student</option>
                                    </select>
                                    <InputError :message="form.errors.boarding_status" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="status" class="text-xs font-semibold text-foreground ml-1">Registration Status</Label>
                                    <select id="status" v-model="form.status" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </div>

                         <div class="p-6 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-100 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                                 <AlertTriangle class="h-20 w-20 text-white" />
                            </div>
                            <h4 class="text-[10px] font-bold uppercase tracking-widest mb-3 opacity-80">Registration Notice</h4>
                            <p class="text-xs font-medium leading-relaxed italic">
                                Please ensure all learner details are accurate. Admission numbers must be unique across the entire school system.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-wrap items-center justify-between gap-6 pt-10 border-t border-border/60">
                    <Button type="button" variant="ghost" class="text-muted-foreground hover:text-foreground font-bold px-8 h-12 rounded-xl transition-all" as-child>
                        <Link href="/students">Cancel Enrollment</Link>
                    </Button>
                    <div class="flex gap-4">
                         <Button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-foreground text-background hover:bg-foreground/90 font-bold shadow-lg shadow-foreground/10 transition-all border-0">
                             <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                             <Save v-else class="mr-2 h-4 w-4" />
                             Enroll Learner
                         </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="rounded-[2.5rem] border-0 p-0 overflow-hidden shadow-2xl max-w-lg bg-card">
                <div class="p-10 text-center space-y-6">
                    <div class="mx-auto h-20 w-20 rounded-3xl bg-amber-50 flex items-center justify-center border-4 border-white shadow-sm">
                        <AlertTriangle class="h-8 w-8 text-amber-500" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-foreground italic">Review Registration</h3>
                        <p class="text-sm text-muted-foreground mt-2 font-medium">Please confirm the details for <span class="text-indigo-600 font-bold decoration-indigo-200 underline underline-offset-4">{{ form.first_name }} {{ form.last_name }}</span> before finalizing.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 py-2">
                        <div class="rounded-2xl bg-muted/30 p-4 border border-border/50">
                            <p class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider mb-1 leading-none">Admission No</p>
                            <p class="font-bold text-foreground text-sm tracking-tight">{{ form.admission_number || 'PENDING' }}</p>
                        </div>
                        <div class="rounded-2xl bg-muted/30 p-4 border border-border/50">
                            <p class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider mb-1 leading-none">Grade Level</p>
                            <p class="font-bold text-foreground truncate text-sm">{{ grades.find(g => String(g.id) === form.grade_id)?.name || 'NONE' }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex p-5 bg-muted/20 border-t border-border/50 gap-4">
                    <Button variant="ghost" class="flex-1 rounded-xl h-12 font-bold text-sm" @click="confirmOpen = false">Edit Details</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl h-12 font-bold text-sm shadow-lg shadow-indigo-100 transition-all border-0">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Confirm Enrollment
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="rounded-[2.5rem] border-0 p-0 overflow-hidden shadow-2xl max-w-md bg-card">
                <div class="p-10 text-center space-y-6">
                    <div class="mx-auto h-24 w-24 rounded-[2.5rem] bg-emerald-50 flex items-center justify-center border-8 border-white shadow-lg rotate-3">
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-foreground tracking-tight italic">Enrollment Successful</h3>
                        <p class="text-sm text-muted-foreground mt-2 font-medium">
                            <span class="text-emerald-600 font-bold">{{ form.first_name }} {{ form.last_name }}</span> has been successfully registered in the system.
                        </p>
                    </div>
                </div>
                <div class="p-6 bg-slate-900 flex flex-col gap-3">
                    <Button variant="outline" @click="resetForm" class="w-full bg-white/10 border-white/20 hover:bg-white/20 text-white rounded-xl h-12 font-bold text-sm transition-all">
                        <UserPlus class="mr-2 h-4 w-4 text-emerald-400" />
                        Enroll Another Learner
                    </Button>
                    <Button as-child class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl h-12 font-bold text-sm shadow-lg shadow-indigo-900/40 transition-all border-0">
                        <Link href="/students">
                            <GraduationCap class="mr-2 h-4 w-4" />
                            View Learner Directory
                        </Link>
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <BulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>
