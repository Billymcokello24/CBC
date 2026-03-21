<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, GraduationCap, Loader2, Save } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: {
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
    };
    grades: Array<{ id: number; name: string; code: string; level_order: number }>;
    classes: Array<{ id: number; name: string; grade_level_id: number | null; grade_name: string | null; stream_name: string | null }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Edit Student', href: `/students/${props.student.id}/edit` },
];

const form = useForm({
    first_name: props.student.first_name,
    middle_name: props.student.middle_name ?? '',
    last_name: props.student.last_name,
    admission_number: props.student.admission_number ?? '',
    gender: props.student.gender,
    date_of_birth: props.student.date_of_birth ?? '',
    grade_id: props.student.grade_id ? String(props.student.grade_id) : '',
    class_id: props.student.class_id ? String(props.student.class_id) : '',
    county: props.student.county ?? '',
    boarding_status: props.student.boarding_status ?? 'day',
    status: props.student.status,
    guardian_name: props.student.guardian?.name ?? '',
    guardian_email: props.student.guardian?.email ?? '',
    guardian_phone: props.student.guardian?.phone ?? '',
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

const submit = () => {
    form.transform((data) => ({
        ...data,
        class_id: data.class_id ? Number(data.class_id) : null,
    })).put(`/students/${props.student.id}`);
};
</script>

<template>
    <Head title="Edit Student" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-0 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
                <Button variant="ghost" size="icon" as-child class="h-10 w-10 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl border border-transparent hover:border-blue-100 transition-all">
                    <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                </Button>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-none">Modify Scholar</h1>
                        <Badge variant="outline" class="rounded-full px-3 py-0.5 h-5 text-[7px] font-black uppercase tracking-[0.2em] border-blue-200 text-blue-500 italic bg-blue-50/50">Node Reconfiguration</Badge>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-70">Updating student identity and credentials within the vision matrix</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <div class="overflow-hidden rounded-[1.5rem] border bg-white shadow-sm transition-all hover:shadow-md border-t-4 border-t-slate-900">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                        <h2 class="text-base font-black text-slate-900 uppercase tracking-tight flex items-center gap-2 italic">
                            <GraduationCap class="h-4 w-4 text-blue-600" />
                            Scholar Core Identity
                        </h2>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 opacity-70">Primary foundational metadata</p>
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-1.5">
                                <Label for="first_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Scholar First Name</Label>
                                <Input id="first_name" v-model="form.first_name" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="middle_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Middle Name (Optional)</Label>
                                <Input id="middle_name" v-model="form.middle_name" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="last_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Scholar Last Name</Label>
                                <Input id="last_name" v-model="form.last_name" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="admission_number" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Admission Node ID</Label>
                                <Input id="admission_number" v-model="form.admission_number" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-black px-4 italic focus:ring-blue-500 text-xs uppercase tracking-tight" />
                                <InputError :message="form.errors.admission_number" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="gender" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Biological Identity</Label>
                                <select id="gender" v-model="form.gender" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-xs font-black uppercase tracking-widest focus:ring-blue-500 outline-none transition-all">
                                    <option value="male">Male Node</option>
                                    <option value="female">Female Node</option>
                                    <option value="other">Non-Binary Node</option>
                                </select>
                                <InputError :message="form.errors.gender" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="date_of_birth" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Temporal Origin (DOB)</Label>
                                <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-black px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.date_of_birth" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="grade_id" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Target Grade</Label>
                                <select id="grade_id" v-model="form.grade_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 outline-none appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="">Select level</option>
                                    <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="class_id" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Stream Matrix</Label>
                                <select id="class_id" v-model="form.class_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 outline-none disabled:opacity-40 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all" :disabled="!form.grade_id">
                                    <option value="">{{ form.grade_id ? 'Select stream' : 'Grade required' }}</option>
                                    <option v-for="schoolClass in filteredClasses" :key="schoolClass.id" :value="String(schoolClass.id)">
                                        {{ schoolClass.name }}{{ schoolClass.stream_name ? ` • ${schoolClass.stream_name}` : '' }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.class_id" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="county" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Territorial County</Label>
                                <select id="county" v-model="form.county" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 outline-none appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="">Select county</option>
                                    <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                </select>
                                <InputError :message="form.errors.county" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="boarding_status" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Logistics Hub</Label>
                                <select id="boarding_status" v-model="form.boarding_status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 outline-none appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="day">Day Node</option>
                                    <option value="boarding">Boarding Hub</option>
                                </select>
                                <InputError :message="form.errors.boarding_status" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="status" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Matrix Status</Label>
                                <select id="status" v-model="form.status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 outline-none appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1rem_center] bg-no-repeat transition-all">
                                    <option value="active">Active Presence</option>
                                    <option value="inactive">Inactive Matrix</option>
                                    <option value="graduated">Graduated Core</option>
                                    <option value="transferred">Transferred Node</option>
                                    <option value="withdrawn">Withdrawn Matrix</option>
                                    <option value="suspended">Locked Terminal</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[1.5rem] border bg-white shadow-sm transition-all hover:shadow-md border-t-4 border-t-amber-500">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                        <h2 class="text-base font-black text-slate-900 uppercase tracking-tight flex items-center gap-2 italic">
                            <Save class="h-4 w-4 text-amber-500" />
                            Kin Link & Credentials
                        </h2>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 opacity-70">Parent portal access synchronization</p>
                    </div>
                    <div class="p-8">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="guardian_name" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Guardian / Parent Name</Label>
                                <Input id="guardian_name" v-model="form.guardian_name" placeholder="Jane Wanjiru" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.guardian_name" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="guardian_email" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Comms Email</Label>
                                <Input id="guardian_email" v-model="form.guardian_email" type="email" placeholder="parent@vision.com" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.guardian_email" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="guardian_phone" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Mobile Link</Label>
                                <Input id="guardian_phone" v-model="form.guardian_phone" placeholder="+2547XXXXXXXX" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs" />
                                <InputError :message="form.errors.guardian_phone" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="guardian_password" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Access Key (Password)</Label>
                                <Input id="guardian_password" v-model="form.guardian_password" type="password" placeholder="Leave blank to preserve current" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs font-mono" />
                                <InputError :message="form.errors.guardian_password" />
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <Label for="guardian_password_confirmation" class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Confirm Access Key</Label>
                                <Input id="guardian_password_confirmation" v-model="form.guardian_password_confirmation" type="password" placeholder="Re-enter access key" class="h-10 rounded-xl border-slate-200 bg-slate-50 font-bold px-4 focus:ring-blue-500 text-xs font-mono" />
                                <InputError :message="form.errors.guardian_password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-6 pt-8 border-t border-slate-100">
                     <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 italic font-black uppercase text-[7px] tracking-[0.2em]">
                        Precision Sync Enabled <Loader2 class="h-3 w-3 animate-pulse" />
                     </div>
                    <div class="flex gap-3 ml-auto">
                        <Button type="button" variant="ghost" class="text-slate-400 hover:text-slate-900 font-black uppercase text-[9px] tracking-widest h-11 px-8 rounded-xl" as-child>
                            <Link href="/students">Abort Edits</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="bg-slate-900 hover:bg-slate-800 text-white font-black uppercase text-[9px] tracking-widest h-11 px-10 rounded-xl shadow-lg shadow-slate-100 border-0">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Reconfigure Node
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
