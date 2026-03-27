<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, GraduationCap, Loader2, Save } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learner: {
        id: number;
        first_name: string;
        middle_name: string | null;
        last_name: string;
        admission_number: string | null;
        gender: string;
        date_of_birth: string | null;
        grade_id: number | null;
        class_id: number | null;
        county: string | null;
        boarding_status: string | null;
        status: string;
        guardian?: {
            name: string;
            email: string | null;
            phone: string | null;
            has_login: boolean;
        } | null;
        photo_url: string | null;
    };
    grades: Array<{ id: number; name: string; code: string; level_order: number }>;
    classes: Array<{ id: number; name: string; grade_level_id: number | null; grade_name: string | null; stream_name: string | null }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learners', href: '/students' },
    { title: 'Edit Learner', href: `/students/${props.learner.id}/edit` },
];

const form = useForm({
    _method: 'PUT',
    first_name: props.learner.first_name,
    middle_name: props.learner.middle_name ?? '',
    last_name: props.learner.last_name,
    admission_number: props.learner.admission_number ?? '',
    gender: props.learner.gender,
    date_of_birth: props.learner.date_of_birth ?? '',
    grade_id: props.learner.grade_id ? String(props.learner.grade_id) : '',
    class_id: props.learner.class_id ? String(props.learner.class_id) : '',
    county: props.learner.county ?? '',
    boarding_status: props.learner.boarding_status ?? 'day',
    status: props.learner.status,
    guardian_name: props.learner.guardian?.name ?? '',
    guardian_email: props.learner.guardian?.email ?? '',
    guardian_phone: props.learner.guardian?.phone ?? '',
    guardian_password: '',
    guardian_password_confirmation: '',
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

const submit = () => {
    form.transform((data) => ({
        ...data,
        class_id: data.class_id ? Number(data.class_id) : null,
    })).put(`/students/${props.learner.id}`);
};
</script>

<template>
    <Head title="Edit Learner" />

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
                            <h1 class="text-3xl font-bold tracking-tight text-foreground">Modify Learner Profile</h1>
                            <Badge variant="outline" class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-600 border-amber-100">
                                Management
                            </Badge>
                        </div>
                        <p class="text-sm font-medium text-muted-foreground mt-1">Update personal, academic, and guardian records for <span class="text-foreground font-bold">{{ learner.first_name }} {{ learner.last_name }}</span>.</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-8 pb-12">
                <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <div class="border-b border-border/50 bg-muted/20 px-8 py-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                <GraduationCap class="h-5 w-5 text-indigo-600" />
                                Learner Information
                            </h2>
                            <p class="text-xs text-muted-foreground mt-0.5">Basic personal and identification details</p>
                        </div>
                        <ProfilePhotoUpload 
                            v-model="form.photo" 
                            :error="form.errors.photo"
                            :current-image="learner.photo_url"
                        />
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="first_name" class="text-xs font-semibold text-foreground ml-1">First Name</Label>
                                <Input id="first_name" v-model="form.first_name" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="middle_name" class="text-xs font-semibold text-foreground ml-1">Middle Name (Optional)</Label>
                                <Input id="middle_name" v-model="form.middle_name" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name" class="text-xs font-semibold text-foreground ml-1">Last Name</Label>
                                <Input id="last_name" v-model="form.last_name" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="admission_number" class="text-xs font-semibold text-foreground ml-1">Admission Number</Label>
                                <Input id="admission_number" v-model="form.admission_number" class="h-11 rounded-xl border-border bg-muted/20 px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold uppercase tracking-tight" />
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
                            <div class="space-y-2">
                                <Label for="date_of_birth" class="text-xs font-semibold text-foreground ml-1">Date of Birth</Label>
                                <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.date_of_birth" />
                            </div>
                             <div class="space-y-2">
                                <Label for="grade_id" class="text-xs font-semibold text-foreground ml-1">Grade Level</Label>
                                <select id="grade_id" v-model="form.grade_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 outline-none border appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="">Select grade</option>
                                    <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                                </select>
                            </div>
                             <div class="space-y-2">
                                <Label for="class_id" class="text-xs font-semibold text-foreground ml-1">Stream / Class</Label>
                                <select id="class_id" v-model="form.class_id" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 outline-none border disabled:opacity-40 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" :disabled="!form.grade_id">
                                    <option value="">{{ form.grade_id ? 'Select stream' : 'Grade required' }}</option>
                                    <option v-for="schoolClass in filteredClasses" :key="schoolClass.id" :value="String(schoolClass.id)">
                                        {{ schoolClass.name }}{{ schoolClass.stream_name ? ` • ${schoolClass.stream_name}` : '' }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.class_id" />
                            </div>
                             <div class="space-y-2">
                                <Label for="county" class="text-xs font-semibold text-foreground ml-1">County of Origin</Label>
                                <select id="county" v-model="form.county" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 outline-none border appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="">Select county</option>
                                    <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                </select>
                                <InputError :message="form.errors.county" />
                            </div>
                             <div class="space-y-2">
                                <Label for="boarding_status" class="text-xs font-semibold text-foreground ml-1">Boarding Status</Label>
                                <select id="boarding_status" v-model="form.boarding_status" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 outline-none border appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="day">Day Scholar</option>
                                    <option value="boarding">Boarding Student</option>
                                </select>
                                <InputError :message="form.errors.boarding_status" />
                            </div>
                             <div class="space-y-2">
                                <Label for="status" class="text-xs font-semibold text-foreground ml-1">Registration Status</Label>
                                <select id="status" v-model="form.status" class="h-11 w-full rounded-xl border-border bg-background px-4 text-sm font-medium focus:ring-2 focus:ring-indigo-600/10 outline-none border appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="graduated">Graduated</option>
                                    <option value="transferred">Transferred</option>
                                    <option value="withdrawn">Withdrawn</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <div class="border-b border-border/50 bg-muted/20 px-8 py-5">
                        <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                            <Save class="h-5 w-5 text-amber-500" />
                            Parent / Guardian Details
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Primary contact for school communications</p>
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="guardian_name" class="text-xs font-semibold text-foreground ml-1">Guardian Full Name</Label>
                                <Input id="guardian_name" v-model="form.guardian_name" placeholder="e.g. Jane Doe" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.guardian_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_email" class="text-xs font-semibold text-foreground ml-1">Guardian Email</Label>
                                <Input id="guardian_email" v-model="form.guardian_email" type="email" placeholder="guardian@example.com" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.guardian_email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_phone" class="text-xs font-semibold text-foreground ml-1">Guardian Phone</Label>
                                <Input id="guardian_phone" v-model="form.guardian_phone" placeholder="+254 XXX XXX XXX" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.guardian_phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_password" class="text-xs font-semibold text-foreground ml-1">Guardian Portal Password</Label>
                                <Input id="guardian_password" v-model="form.guardian_password" type="password" placeholder="Leave blank to keep current" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.guardian_password" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <Label for="guardian_password_confirmation" class="text-xs font-semibold text-foreground ml-1">Confirm Password</Label>
                                <Input id="guardian_password_confirmation" v-model="form.guardian_password_confirmation" type="password" placeholder="Re-enter password" class="h-11 rounded-xl border-border bg-background px-4 focus:ring-2 focus:ring-indigo-600/10 transition-all font-medium" />
                                <InputError :message="form.errors.guardian_password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-wrap items-center justify-between gap-6 pt-10 border-t border-border/60">
                    <div class="hidden sm:flex items-center gap-3 px-4 py-2 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-100/50 shadow-sm">
                        <div class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Cloud Sync Active</span>
                    </div>
                    <div class="flex gap-4 ml-auto">
                        <Button type="button" variant="ghost" class="text-muted-foreground hover:text-foreground font-bold px-8 h-12 rounded-xl transition-all" as-child>
                            <Link href="/students">Discard Changes</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-foreground text-background hover:bg-foreground/90 font-bold shadow-lg shadow-foreground/10 transition-all border-0">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Update Learner Profile
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
