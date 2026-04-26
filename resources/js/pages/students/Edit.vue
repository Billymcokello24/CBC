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
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl border-slate-200 bg-white shadow-sm hover:bg-slate-50 transition-all shrink-0">
                        <Link href="/students"><ArrowLeft class="h-5 w-5 text-slate-400" /></Link>
                    </Button>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 uppercase italic">Edit Profile</h1>
                            <Badge variant="outline" class="rounded-lg px-2 py-0.5 text-[8px] sm:text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border-amber-100">
                                Management
                            </Badge>
                        </div>
                        <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-widest mt-1 italic opacity-70">Synchronzing: {{ learner.first_name }} {{ learner.last_name }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 sm:gap-8 pb-12">
                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                    <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                                <GraduationCap class="h-5 w-5 text-blue-600" />
                                Biological Context
                            </h2>
                            <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Primary Identity Payload</p>
                        </div>
                        <div class="self-end sm:self-auto">
                            <ProfilePhotoUpload 
                                v-model="form.photo" 
                                :error="form.errors.photo"
                                :current-image="learner.photo_url"
                            />
                        </div>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="first_name" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Legal First Name</Label>
                                <Input id="first_name" v-model="form.first_name" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="middle_name" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Middle String (Opt)</Label>
                                <Input id="middle_name" v-model="form.middle_name" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Legal Last Name</Label>
                                <Input id="last_name" v-model="form.last_name" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="admission_number" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">System Handle (ADM)</Label>
                                <Input id="admission_number" v-model="form.admission_number" class="h-12 rounded-xl border-slate-200 bg-blue-50/30 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest text-blue-600 italic" />
                                <InputError :message="form.errors.admission_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="gender" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Morphology</Label>
                                <div class="relative">
                                    <select id="gender" v-model="form.gender" class="h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none transition-all appearance-none cursor-pointer">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.gender" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_of_birth" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Birth Epoch</Label>
                                <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.date_of_birth" />
                            </div>
                             <div class="space-y-2">
                                <Label for="grade_id" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Academic Rank</Label>
                                <div class="relative">
                                    <select id="grade_id" v-model="form.grade_id" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="">Select Level</option>
                                        <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                            </div>
                             <div class="space-y-2">
                                <Label for="class_id" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Stream Context</Label>
                                <div class="relative">
                                    <select id="class_id" v-model="form.class_id" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none disabled:opacity-40 appearance-none cursor-pointer transition-all" :disabled="!form.grade_id">
                                        <option value="">{{ form.grade_id ? 'Select Stream' : 'Level Required' }}</option>
                                        <option v-for="schoolClass in filteredClasses" :key="schoolClass.id" :value="String(schoolClass.id)">
                                            {{ schoolClass.name }}{{ schoolClass.stream_name ? ` • ${schoolClass.stream_name}` : '' }}
                                        </option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.class_id" />
                            </div>
                             <div class="space-y-2">
                                <Label for="county" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Regional Origin</Label>
                                <div class="relative">
                                    <select id="county" v-model="form.county" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="">Select County</option>
                                        <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.county" />
                            </div>
                             <div class="space-y-2">
                                <Label for="boarding_status" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Residency Status</Label>
                                <div class="relative">
                                    <select id="boarding_status" v-model="form.boarding_status" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="day">Day Scholar</option>
                                        <option value="boarding">Boarding Hub</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.boarding_status" />
                            </div>
                             <div class="space-y-2">
                                <Label for="status" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Registry Pulse</Label>
                                <div class="relative">
                                    <select id="status" v-model="form.status" class="h-12 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="graduated">Graduated</option>
                                        <option value="transferred">Transferred</option>
                                        <option value="withdrawn">Withdrawn</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                    <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                            <Save class="h-5 w-5 text-amber-500" />
                            External Linkage (Guardian)
                        </h2>
                        <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Primary Propagation Node</p>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="guardian_name" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Full Legal Name</Label>
                                <Input id="guardian_name" v-model="form.guardian_name" placeholder="E.G. JANE DOE" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.guardian_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_email" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Digital Address</Label>
                                <Input id="guardian_email" v-model="form.guardian_email" type="email" placeholder="GUARDIAN@NODE.NET" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.guardian_email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_phone" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Comms Protocol (VOICE)</Label>
                                <Input id="guardian_phone" v-model="form.guardian_phone" placeholder="+254 XXX XXX XXX" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.guardian_phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="guardian_password" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Portal Verification Code</Label>
                                <Input id="guardian_password" v-model="form.guardian_password" type="password" placeholder="LEAVE VOID FOR NO CHANGE" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.guardian_password" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <Label for="guardian_password_confirmation" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Recode Verification</Label>
                                <Input id="guardian_password_confirmation" v-model="form.guardian_password_confirmation" type="password" placeholder="RE-ENTER CODE" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" />
                                <InputError :message="form.errors.guardian_password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-10 border-t border-slate-100 italic">
                    <div class="hidden sm:flex items-center gap-3 px-5 py-2 rounded-2xl bg-blue-50 text-blue-700 border border-blue-100/50 shadow-sm">
                        <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest">Active Neural Sync</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <Button type="button" variant="ghost" class="w-full sm:w-auto text-slate-400 hover:text-slate-900 font-black text-[10px] uppercase tracking-widest h-12 px-8 rounded-xl transition-all" as-child>
                            <Link href="/students">Abort Modify</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto h-12 px-10 rounded-xl bg-slate-900 text-white hover:bg-slate-800 font-black text-[11px] uppercase tracking-widest shadow-xl shadow-slate-900/10 transition-all border-0">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Update Registry
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
