<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Download,
    GraduationCap,
    Loader2,
    Save,
    Upload,
    UserPlus,
    AlertTriangle,
    ChevronDown,
    Fingerprint,
    MapPin,
    ShieldCheck,
    Database,
    Zap,
    Activity,
    Camera,
    Plus,
} from 'lucide-vue-next';
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
    grades: Array<{ id: number; name: string; code: string }>;
    classes: Array<{ id: number; name: string; grade_level_id: number | null }>;
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
    photo: null as File | null,
});

const filteredClasses = computed(() => {
    if (!form.grade_id) return [];
    const selectedGradeId = Number(form.grade_id);
    return props.classes.filter(c => c.grade_level_id === selectedGradeId);
});

const photoPreview = ref<string | undefined>(undefined);
watch(() => form.photo, (newPhoto) => {
    if (newPhoto) {
        const reader = new FileReader();
        reader.onload = (e) => photoPreview.value = e.target?.result as string;
        reader.readAsDataURL(newPhoto);
    } else photoPreview.value = undefined;
});

const confirmOpen = ref(false);
const successOpen = ref(false);
const bulkUploadOpen = ref(false);

const submit = () => { confirmOpen.value = true; };
const confirmSubmit = () => {
    confirmOpen.value = false;
    form.post('/students', {
        onSuccess: () => { successOpen.value = true; },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Student" />

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
                        <Link href="/students">
                            <ArrowLeft class="h-4 w-4 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Add Student
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground">Register a new student to the system</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="ghost" class="h-10 rounded-lg px-4 text-xs font-semibold text-primary hover:bg-primary/5" @click="bulkUploadOpen = true">
                        <Upload class="mr-2 h-4 w-4" />
                        Bulk Upload
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left Form Section -->
                <div class="space-y-8 lg:col-span-4">
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                         <div class="relative overflow-hidden bg-slate-900 p-8 text-white text-center">
                            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                            <div class="relative z-10 space-y-6">
                                <ProfilePhotoUpload v-model="form.photo" :error="form.errors.photo">
                                     <template #default="{ isUploading }">
                                        <div class="group relative mx-auto h-32 w-32 cursor-pointer overflow-hidden rounded-2xl border-2 border-white/10 bg-white/5 transition-all duration-300 hover:scale-105">
                                            <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-white/20">
                                                <Camera class="h-10 w-10" />
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Plus class="h-6 w-6 text-white" />
                                                <span class="text-[10px] font-bold text-white uppercase">Upload Photo</span>
                                            </div>
                                        </div>
                                     </template>
                                </ProfilePhotoUpload>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-semibold text-white/50 uppercase tracking-wider">New Student</h3>
                                    <p class="text-[10px] font-semibold text-primary uppercase">Ready to save</p>
                                </div>
                            </div>
                         </div>
                         <div class="p-8 space-y-6">
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Boarding Status *</Label>
                                <div class="relative">
                                    <select v-model="form.boarding_status" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background" required>
                                        <option value="day">Day Scholar</option>
                                        <option value="boarding">Boarding</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Admission Status *</Label>
                                <div class="relative">
                                    <select v-model="form.status" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-xs font-semibold outline-none focus:bg-background" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="alumni">Alumni</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Info Card -->
                    <div class="flex items-center justify-between rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:bg-muted/5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-foreground">Secure Record</p>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase">Data will be saved securely</p>
                            </div>
                        </div>
                        <Activity class="h-4 w-4 text-primary animate-pulse opacity-40" />
                    </div>
                </div>

                <!-- Right Data Stack -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Personal Info -->
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <div class="border-b border-border/50 bg-muted/5 px-8 py-5 text-xs font-bold text-foreground uppercase">Personal Information</div>
                        <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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
                            </div>
                        </div>
                    </div>

                    <!-- Academic Details -->
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                         <div class="border-b border-border/50 bg-muted/5 px-8 py-5 text-xs font-bold text-foreground uppercase">School Details</div>
                         <div class="p-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2 md:col-span-2">
                                     <Label class="text-xs font-medium text-muted-foreground">Admission Number *</Label>
                                     <Input v-model="form.admission_number" required class="h-10 rounded-lg border-border bg-muted/10 px-4 text-sm font-bold text-primary focus:bg-background" />
                                     <InputError :message="form.errors.admission_number" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Grade *</Label>
                                    <div class="relative">
                                        <select v-model="form.grade_id" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background" required>
                                            <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                    </div>
                                    <InputError :message="form.errors.grade_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Class *</Label>
                                    <div class="relative">
                                        <select v-model="form.class_id" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background" :disabled="!form.grade_id" required>
                                            <option v-for="cls in filteredClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                    </div>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-4 pt-4 border-t border-border/50">
                        <Button variant="ghost" class="h-10 rounded-lg px-6 text-xs font-semibold text-muted-foreground" as-child>
                            <Link href="/students">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Save Student
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirm Dialog -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
             <DialogContent class="sm:max-w-[420px] rounded-2xl border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-8 text-center">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <Fingerprint class="h-8 w-8" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground mb-2">Confirm Registration</h3>
                    <p class="text-sm font-medium text-muted-foreground leading-relaxed">Are you sure you want to register this student? This will create a permanent record in the system.</p>
                </div>
                <div class="flex items-center justify-end gap-3 bg-muted/10 p-4 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-10 rounded-lg px-4 text-xs font-semibold">Cancel</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm">Register Student</Button>
                </div>
            </DialogContent>
        </Dialog>

        <BulkUploadModal :open="bulkUploadOpen" @close="bulkUploadOpen = false" />
    </AppLayout>
</template>
