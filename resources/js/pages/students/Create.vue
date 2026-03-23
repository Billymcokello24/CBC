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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-0 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl border border-transparent hover:border-blue-100 transition-all">
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-none">Add Learner</h1>
                            <Badge variant="outline" class="rounded-full px-3 py-0.5 h-5 text-[7px] font-black uppercase tracking-[0.2em] border-blue-200 text-blue-500 italic bg-blue-50/50">Core Intake</Badge>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-70">Fill basic learner details and guardian contact</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                     <div class="flex -space-x-2">
                         <div class="h-8 w-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[9px] font-black text-slate-400 shadow-sm">1</div>
                         <div class="h-8 w-8 rounded-full border-2 border-white bg-blue-600 flex items-center justify-center text-[9px] font-black text-white shadow-md">2</div>
                         <div class="h-8 w-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[9px] font-black text-slate-400 shadow-sm">3</div>
                     </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div class="rounded-[1.5rem] bg-slate-900 p-6 text-white shadow-xl shadow-slate-100 relative overflow-hidden group border-t-4 border-t-blue-600">
                <div class="absolute -right-10 -bottom-10 opacity-10 group-hover:scale-110 transition-transform duration-1000 text-blue-500">
                    <Upload class="h-48 w-48" />
                </div>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between relative z-10">
                    <div class="max-w-2xl">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-900/40">
                                <Upload class="h-5 w-5 text-white" />
                            </div>
                            <h2 class="text-lg font-black uppercase tracking-tight italic">Guardian & Access</h2>
                        </div>
                        <p class="text-slate-400 font-bold leading-relaxed text-[11px] opacity-80 uppercase tracking-tight">
                            Synchronize entire student populations via CSV matrix synchronization. Our engine maps academic structures in real-time.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button variant="outline" class="bg-white/5 border-white/10 hover:bg-white/10 text-white font-black uppercase text-[9px] tracking-widest h-10 px-6 rounded-xl" as-child>
                            <a href="/students/template/download">
                                <Download class="mr-2 h-3.5 w-3.5 text-emerald-400" />
                                Template.csv
                            </a>
                        </Button>
                        <Button class="bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-[9px] tracking-widest h-10 px-8 rounded-xl shadow-lg shadow-blue-900/20 border-0" @click="bulkUploadOpen = true">
                            <Upload class="mr-2 h-3.5 w-3.5" />
                            Upload CSV
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-10">
                <!-- Data Forms Split -->
                <div class="grid gap-10 lg:grid-cols-12">
                    <!-- Left: Core Info -->
                    <div class="lg:col-span-8 space-y-8">
                        <div class="overflow-hidden rounded-[1.5rem] border bg-white shadow-sm transition-all hover:shadow-md border-t-4 border-t-slate-900">
                            <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-5 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-black text-slate-900 uppercase tracking-tight flex items-center gap-2 italic">
                                        <GraduationCap class="h-4 w-4 text-blue-600" />
                                        Identity & Academic Pulse
                                    </h2>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 opacity-70">Foundational scholarship metadata</p>
                                </div>
                                <Badge variant="outline" class="text-[7px] font-black text-slate-300 border-slate-200 uppercase tracking-widest h-4 px-2">Required</Badge>
                            </div>
                            <div class="p-8">
                                <div class="grid gap-8 md:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <Label for="first_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Learner First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" placeholder="John" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" required />
                                        <InputError :message="form.errors.first_name" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="last_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Learner Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" placeholder="Mwangi" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" required />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="admission_number" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Admission ID</Label>
                                        <Input id="admission_number" v-model="form.admission_number" placeholder="ADM-001" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-black px-4 italic focus:ring-blue-500 text-xs uppercase tracking-tight" required />
                                        <InputError :message="form.errors.admission_number" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="gender" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Gender</Label>
                                        <select id="gender" v-model="form.gender" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-xs font-black uppercase tracking-widest focus:ring-blue-500 outline-none border transition-all">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <InputError :message="form.errors.gender" />
                                    </div>
                                    <div class="space-y-1.5 md:col-span-2">
                                        <Label for="date_of_birth" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Date of Birth</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-black px-4 focus:ring-blue-500 text-xs" required />
                                        <InputError :message="form.errors.date_of_birth" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Info -->
                        <div class="overflow-hidden rounded-[1.5rem] border bg-white shadow-sm transition-all hover:shadow-md border-t-4 border-t-amber-500">
                             <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                                <h2 class="text-base font-black text-slate-900 uppercase tracking-tight flex items-center gap-2 italic">
                                    <Save class="h-4 w-4 text-amber-500" />
                                    Guardian & Access
                                </h2>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 opacity-70">Parent portal synchronization</p>
                            </div>
                            <div class="p-8">
                                <div class="grid gap-8 md:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <Label for="guardian_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Guardian Name</Label>
                                        <Input id="guardian_name" v-model="form.guardian_name" placeholder="Full Name" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                        <InputError :message="form.errors.guardian_name" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="guardian_email" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Comms Email</Label>
                                        <Input id="guardian_email" v-model="form.guardian_email" type="email" placeholder="guardian@vision.com" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                        <InputError :message="form.errors.guardian_email" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="guardian_phone" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Mobile Link</Label>
                                        <Input id="guardian_phone" v-model="form.guardian_phone" placeholder="+254 XXX XXX XXX" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                        <InputError :message="form.errors.guardian_phone" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="guardian_password" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Access Key (Password)</Label>
                                        <Input id="guardian_password" v-model="form.guardian_password" type="password" placeholder="Min 8 characters" class="h-11 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                        <InputError :message="form.errors.guardian_password" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Placement -->
                    <div class="lg:col-span-4 space-y-8">
                        <div class="overflow-hidden rounded-[1.5rem] border bg-white shadow-sm transition-all hover:shadow-md border-t-4 border-t-blue-600">
                             <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                                <h2 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-2 italic">
                                    <GraduationCap class="h-4 w-4 text-blue-600" />
                                    Academic Alignment
                                </h2>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="space-y-1.5">
                                    <Label for="grade_id" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Target Grade</Label>
                                    <select id="grade_id" v-model="form.grade_id" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                        <option value="">Select Level</option>
                                        <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">
                                            {{ grade.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="class_id" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Stream Matrix</Label>
                                    <select id="class_id" v-model="form.class_id" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 disabled:opacity-40 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat" :disabled="!form.grade_id">
                                        <option value="">{{ form.grade_id ? 'Select Stream' : 'Grade Required' }}</option>
                                        <option v-for="schoolClass in filteredClasses" :key="schoolClass.id" :value="String(schoolClass.id)">
                                            {{ schoolClass.name }}{{ schoolClass.stream_name ? ` • ${schoolClass.stream_name}` : '' }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="county" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Territorial County</Label>
                                    <select id="county" v-model="form.county" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                        <option value="">Select County</option>
                                        <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                    </select>
                                    <InputError :message="form.errors.county" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="boarding_status" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Logistics Hub</Label>
                                    <select id="boarding_status" v-model="form.boarding_status" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                        <option value="day">Day Node</option>
                                        <option value="boarding">Boarding Hub</option>
                                    </select>
                                    <InputError :message="form.errors.boarding_status" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="status" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Initial Pulse</Label>
                                    <select id="status" v-model="form.status" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat">
                                        <option value="active">Active Presence</option>
                                        <option value="inactive">Inactive Matrix</option>
                                        <option value="suspended">Locked Terminal</option>
                                    </select>
                                    <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </div>

                         <div class="p-6 rounded-[1.5rem] bg-blue-600 text-white shadow-lg shadow-blue-100 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                                 <AlertTriangle class="h-20 w-20 text-white" />
                            </div>
                            <h4 class="text-[10px] font-black uppercase tracking-widest mb-3">Precision Note</h4>
                            <p class="text-[9px] font-bold opacity-80 leading-relaxed italic">
                                Initializing a student record requires absolute data integrity. Once established, the admission node becomes the primary identifier for the vision core.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div class="flex flex-wrap items-center justify-between gap-6 pt-8 border-t border-slate-100">
                    <Button type="button" variant="ghost" class="text-slate-400 hover:text-slate-900 font-black uppercase text-[9px] tracking-widest h-11 px-8 rounded-xl" as-child>
                        <Link href="/students">Cancel</Link>
                    </Button>
                    <div class="flex gap-3">
                         <Button type="submit" :disabled="form.processing" class="bg-slate-900 hover:bg-slate-800 text-white font-black uppercase text-[9px] tracking-widest h-11 px-10 rounded-xl shadow-lg shadow-slate-100 border-0">
                             <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                             <Save v-else class="mr-2 h-4 w-4" />
                             Save Learner
                         </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="rounded-[2.5rem] border-0 p-0 overflow-hidden shadow-2xl max-w-lg">
                <div class="p-10 text-center space-y-6">
                    <div class="mx-auto h-20 w-20 rounded-[1.5rem] bg-amber-50 flex items-center justify-center border-4 border-white shadow-sm">
                        <AlertTriangle class="h-8 w-8 text-amber-500" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Final Verification</h3>
                        <p class="text-[11px] text-slate-500 font-bold mt-2 leading-relaxed uppercase tracking-tight">Ready to add <span class="text-blue-600 underline decoration-blue-200 underline-offset-4">{{ form.first_name }} {{ form.last_name }}</span>?</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 py-2">
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                            <p class="text-[8px] text-slate-400 uppercase font-black tracking-widest mb-1 leading-none">Node ID</p>
                            <p class="font-black text-slate-900 italic uppercase tracking-tighter text-sm">{{ form.admission_number || 'PENDING' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                            <p class="text-[8px] text-slate-400 uppercase font-black tracking-widest mb-1 leading-none">Matrix Group</p>
                            <p class="font-black text-slate-900 truncate text-sm uppercase">{{ grades.find(g => String(g.id) === form.grade_id)?.name || 'NONE' }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex p-4 bg-slate-50 border-t border-slate-100 gap-3">
                    <Button variant="ghost" class="flex-1 rounded-xl h-12 font-black uppercase text-[9px] tracking-widest" @click="confirmOpen = false">Review Again</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl h-12 font-black uppercase text-[9px] tracking-widest shadow-lg shadow-blue-100">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Execute Intake
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="rounded-[2.5rem] border-0 p-0 overflow-hidden shadow-2xl max-w-md">
                <div class="p-10 text-center space-y-6">
                    <div class="mx-auto h-24 w-24 rounded-[2rem] bg-emerald-50 flex items-center justify-center border-8 border-white shadow-lg rotate-3">
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Mission Success</h3>
                        <p class="text-[11px] text-slate-500 font-bold mt-2 leading-relaxed uppercase tracking-tight">
                            Learner <span class="text-emerald-600">{{ form.first_name }} {{ form.last_name }}</span> has been added.
                        </p>
                    </div>
                </div>
                <div class="p-6 bg-slate-900 flex flex-col gap-2">
                    <Button variant="outline" @click="resetForm" class="w-full bg-white/5 border-white/10 hover:bg-white/10 text-white rounded-xl h-12 font-black uppercase text-[9px] tracking-widest">
                        <UserPlus class="mr-2 h-3.5 w-3.5 text-emerald-400" />
                        Add Another Learner
                    </Button>
                    <Button as-child class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl h-12 font-black uppercase text-[9px] tracking-widest shadow-lg shadow-blue-900/20">
                        <Link href="/students">
                            <GraduationCap class="mr-2 h-3.5 w-3.5" />
                            Back to Learners
                        </Link>
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <BulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>
