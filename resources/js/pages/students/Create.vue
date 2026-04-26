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
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl border-slate-200 bg-white shadow-sm hover:bg-slate-50 transition-all shrink-0">
                        <Link href="/students"><ArrowLeft class="h-5 w-5 text-slate-400" /></Link>
                    </Button>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 uppercase italic">Enrollment</h1>
                            <Badge variant="outline" class="rounded-lg px-2 py-0.5 text-[8px] sm:text-[9px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 border-blue-100">
                                Registration
                            </Badge>
                        </div>
                        <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-widest mt-1 italic opacity-70">Establish New Learner Identity</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 sm:gap-3 bg-slate-50/50 p-1.5 rounded-2xl border border-slate-100/50 w-full md:w-auto">
                    <div class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="h-5 w-5 rounded-full bg-blue-600 flex items-center justify-center text-[10px] font-black text-white">1</div>
                        <span class="text-[9px] font-black text-slate-900 uppercase tracking-widest">Entry</span>
                    </div>
                    <div class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 opacity-40">
                        <div class="h-5 w-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500">2</div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Review</span>
                    </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="rounded-3xl border border-slate-100 bg-white p-6 sm:p-8 shadow-sm overflow-hidden relative group transition-all hover:border-blue-100">
                <div class="absolute -right-10 -bottom-10 opacity-5 group-hover:scale-110 transition-transform duration-1000 text-blue-600">
                    <Upload class="h-48 w-48" />
                </div>
                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between relative z-10">
                    <div class="flex items-start sm:items-center gap-5">
                        <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/50 shadow-inner">
                            <Upload class="h-6 w-6 sm:h-7 sm:w-7" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2 uppercase italic">
                                Bulk Ingest
                                <Badge variant="secondary" class="text-[8px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border-emerald-100/50">High Load</Badge>
                            </h2>
                            <p class="text-xs text-slate-400 mt-1.5 max-w-sm font-black uppercase tracking-tight italic opacity-60 leading-relaxed">Modernize enrollment through CSV serialization</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                        <Button variant="outline" class="w-full sm:w-auto h-12 border-slate-200 font-black text-[9px] uppercase tracking-[0.2em] px-6 rounded-xl bg-white hover:bg-slate-50 transition-all italic" as-child>
                            <a href="/students/template/download">
                                <Download class="mr-2 h-4 w-4 text-emerald-500" />
                                Template
                            </a>
                        </Button>
                        <Button class="w-full sm:w-auto h-12 bg-slate-900 hover:bg-slate-800 text-white font-black text-[10px] uppercase tracking-[0.2em] px-8 rounded-xl shadow-xl shadow-slate-900/10 transition-all border-0 italic" @click="bulkUploadOpen = true">
                            <Upload class="mr-2 h-4 w-4" />
                            Upload Data
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 sm:gap-8 pb-12">
                <!-- Data Forms Split -->
                <div class="grid gap-6 sm:gap-8 lg:grid-cols-12">
                    <!-- Left: Core Info -->
                    <div class="lg:col-span-8 space-y-6 sm:gap-8">
                        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                             <div class="border-b border-slate-50 bg-slate-50/30 px-6 sm:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                                        <GraduationCap class="h-5 w-5 text-blue-600" />
                                        Biometric Identity
                                    </h2>
                                    <p class="text-[9px] text-slate-400 mt-1 font-black uppercase tracking-widest italic opacity-60">Primary Demographic Payload</p>
                                </div>
                                <div class="flex items-center gap-4 self-end sm:self-auto">
                                    <ProfilePhotoUpload 
                                        v-model="form.photo" 
                                        :error="form.errors.photo"
                                    />
                                    <Badge variant="outline" class="text-[8px] font-black text-slate-400 border-slate-200 uppercase tracking-widest h-5 px-2 bg-slate-50">Required</Badge>
                                </div>
                            </div>
                            <div class="p-6 sm:p-8">
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="first_name" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Legal First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" placeholder="E.G. JOHN" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" required />
                                        <InputError :message="form.errors.first_name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="last_name" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Legal Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" placeholder="E.G. MWANGI" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" required />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="admission_number" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Registry Identifier</Label>
                                        <Input id="admission_number" v-model="form.admission_number" placeholder="ADM-001" class="h-12 rounded-xl border-slate-200 bg-blue-50/30 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest text-blue-600 italic" required />
                                        <InputError :message="form.errors.admission_number" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="gender" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Gender Bias</Label>
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
                                    <div class="space-y-2 md:col-span-2">
                                        <Label for="date_of_birth" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Birth Epoch</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 focus:bg-white transition-all font-black text-[11px] uppercase tracking-widest" required />
                                        <InputError :message="form.errors.date_of_birth" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Placement -->
                    <div class="lg:col-span-4 space-y-6 sm:space-y-8">
                        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100">
                             <div class="border-b border-slate-50 bg-slate-50/30 px-6 py-5">
                                <h2 class="text-[11px] font-black text-slate-900 flex items-center gap-2 uppercase italic tracking-widest">
                                    <GraduationCap class="h-5 w-5 text-blue-600" />
                                    Placement Logic
                                </h2>
                            </div>
                            <div class="p-6 space-y-5">
                                <div class="space-y-2">
                                    <Label for="grade_id" class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Academic Level</Label>
                                    <div class="relative">
                                        <select id="grade_id" v-model="form.grade_id" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="">Select Level</option>
                                            <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">
                                                {{ grade.name }}
                                            </option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="class_id" class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Stream Context</Label>
                                    <div class="relative">
                                        <select id="class_id" v-model="form.class_id" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none disabled:opacity-40 appearance-none cursor-pointer transition-all" :disabled="!form.grade_id">
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
                                    <Label for="county" class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Regional Origin</Label>
                                    <div class="relative">
                                        <select id="county" v-model="form.county" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="">Select County</option>
                                            <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.county" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="boarding_status" class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Residency</Label>
                                    <div class="relative">
                                        <select id="boarding_status" v-model="form.boarding_status" class="h-11 w-full rounded-xl border-slate-200 bg-white px-4 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-blue-600/5 focus:border-blue-300 outline-none appearance-none cursor-pointer transition-all">
                                            <option value="day">Day Scholar</option>
                                            <option value="boarding">Boarding Hub</option>
                                        </select>
                                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    <InputError :message="form.errors.boarding_status" />
                                </div>
                            </div>
                        </div>

                         <div class="p-6 rounded-3xl bg-slate-900 text-white shadow-xl shadow-slate-200 relative overflow-hidden group border border-slate-800">
                            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform duration-700">
                                 <AlertTriangle class="h-20 w-20 text-white" />
                            </div>
                            <h4 class="text-[9px] font-black uppercase tracking-[0.3em] mb-4 text-blue-500 italic">Validation Guard</h4>
                            <p class="text-[10px] font-bold leading-relaxed italic text-slate-400">
                                Global uniqueness required for ADM-ID sequences. Verify biometric consistency before ingestion.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-10 border-t border-slate-100 italic">
                    <Button type="button" variant="ghost" class="w-full sm:w-auto text-slate-400 hover:text-slate-900 font-black text-[10px] uppercase tracking-widest h-12 px-8 rounded-xl transition-all" as-child>
                        <Link href="/students">Abort Protocol</Link>
                    </Button>
                    <div class="flex gap-4 w-full sm:w-auto">
                         <Button type="submit" :disabled="form.processing" class="flex-1 sm:flex-none h-12 px-10 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-black text-[11px] uppercase tracking-widest shadow-xl shadow-blue-500/20 transition-all border-0">
                             <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                             <Save v-else class="mr-2 h-4 w-4" />
                             Commit Payload
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
