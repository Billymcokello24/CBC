<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    GraduationCap,
    Loader2,
    Save,
    UserRound,
    Calendar,
    ChevronDown,
    Fingerprint,
    MapPin,
    Activity,
    Database,
    AlertTriangle,
    ShieldCheck,
    Zap,
    Camera,
    Plus,
} from 'lucide-vue-next';
import { computed, watch, ref } from 'vue';
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
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learner: any;
    grades: any[];
    classes: any[];
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: props.learner.name, href: `/students/${props.learner.id}` },
    { title: 'Edit Student', href: `/students/${props.learner.id}/edit` },
];

const form = useForm({
    first_name: props.learner.first_name,
    middle_name: props.learner.middle_name ?? '',
    last_name: props.learner.last_name,
    admission_number: props.learner.admission_number ?? '',
    gender: props.learner.gender,
    date_of_birth: props.learner.date_of_birth ?? '',
    grade_id: props.learner.grade_id ? String(props.learner.grade_id) : '',
    class_id: props.learner.current_class_id ? String(props.learner.current_class_id) : '',
    county: props.learner.county ?? '',
    boarding_status: props.learner.boarding_status ?? 'day',
    status: props.learner.status,
    photo: null as File | null,
});

const photoPreview = ref<string | undefined>(props.learner.photo_url);
watch(() => form.photo, (newPhoto) => {
    if (newPhoto) {
        const reader = new FileReader();
        reader.onload = (e) => photoPreview.value = e.target?.result as string;
        reader.readAsDataURL(newPhoto);
    }
});

const filteredClasses = computed(() => {
    if (!form.grade_id) return [];
    const selectedGradeId = Number(form.grade_id);
    return props.classes.filter(c => Number(c.grade_level_id) === selectedGradeId);
});

const submit = () => {
    form.post(`/students/${props.learner.id}/update`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${learner.name}`" />

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
                        <Link :href="`/students/${learner.id}`">
                            <ArrowLeft class="h-4 w-4 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Edit Student
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground">Update information for {{ learner.name }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left Profile Card -->
                <div class="space-y-8 lg:col-span-4">
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                         <div class="relative overflow-hidden bg-slate-900 p-8 text-white text-center">
                            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                            <div class="relative z-10 space-y-6 text-center flex flex-col items-center">
                                <ProfilePhotoUpload v-model="form.photo" :error="form.errors.photo">
                                     <template #default="{ isUploading }">
                                        <div class="group relative mx-auto h-32 w-32 cursor-pointer overflow-hidden rounded-2xl border-2 border-white/10 bg-white/5 transition-all duration-300 hover:scale-105">
                                            <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-white/20 text-3xl font-bold">
                                                {{ learner.first_name[0] }}{{ learner.last_name[0] }}
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Camera class="h-6 w-6 text-white" />
                                                <span class="text-[10px] font-bold text-white uppercase">Change Photo</span>
                                            </div>
                                        </div>
                                     </template>
                                </ProfilePhotoUpload>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-semibold text-white/50 uppercase tracking-wider">{{ learner.admission_number }}</h3>
                                    <p class="text-[10px] font-semibold text-primary uppercase">Active Student</p>
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
                    <div class="flex items-center justify-between rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-foreground">Secure Record</p>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase">Updates are recorded in the audit log</p>
                            </div>
                        </div>
                        <div class="h-2 w-2 rounded-full bg-primary opacity-40"></div>
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

                    <!-- Academic Placement -->
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                         <div class="border-b border-border/50 bg-muted/5 px-8 py-5 text-xs font-bold text-foreground uppercase">Academic Placement</div>
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
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-muted-foreground">Class *</Label>
                                    <div class="relative">
                                        <select v-model="form.class_id" class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-border bg-muted/10 px-4 text-sm font-semibold outline-none focus:bg-background" :disabled="!form.grade_id" required>
                                            <option v-for="cls in filteredClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-4 pt-4 border-t border-border/50">
                        <Button variant="ghost" class="h-10 rounded-lg px-6 text-xs font-semibold text-muted-foreground" as-child>
                            <Link :href="`/students/${learner.id}`">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-10 rounded-lg bg-primary px-8 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
