<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    Users, ArrowLeft, Mail, Phone, MapPin, 
    Calendar, Briefcase, GraduationCap, 
    BookOpen, FileText, Settings, Edit,
    Camera, CheckCircle2, ShieldCheck,
    Building2, Hash, Contact,
    Globe, Trash2, Save, X, Plus,
    Loader2, AlertCircle, HeartPulse, ShieldPlus
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem } from '@/types';

interface AvailableClass {
    id: number;
    name: string;
    grade: string | null;
    is_assigned: boolean;
    current_teacher: string | null;
}

interface Teacher {
    id: number;
    user_id: number;
    staff_number: string;
    tsc_number: string | null;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    full_name: string;
    email: string;
    phone: string;
    gender: string;
    date_of_birth: string | null;
    id_number: string | null;
    nationality: string;
    photo: string | null;
    status: string;
    date_joined: string | null;
    contract_type: string | null;
    employment_type: string | null;
    basic_salary: number | null;
    alternate_phone: string | null;
    address: string | null;
    county: string | null;
    sub_county: string | null;
    marital_status: string | null;
    blood_group: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    emergency_contact_relationship: string | null;
    bank_name: string | null;
    bank_account_number: string | null;
    bank_branch: string | null;
    nhif_number: string | null;
    nssf_number: string | null;
    kra_pin: string | null;
    notes: string | null;
    photo_url: string | null;
    department: { id: number; name: string } | null;
    staff_category: { id: number; name: string } | null;
    staff_designation: { id: number; name: string } | null;
    classes_as_teacher: Array<{ 
        id: number; 
        name: string; 
        grade_level: { name: string };
        stream: { name: string } | null;
    }>;
    subject_assignments: Array<{
        id: number;
        subject: { id: number; name: string; code: string };
        school_class: { id: number; name: string };
        is_primary_teacher: boolean;
        is_active: boolean;
    }>;
}

const props = defineProps<{
    teacher: Teacher;
    departments: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string }>;
    designations: Array<{ id: number; name: string }>;
    availableClasses: AvailableClass[];
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff Registry', href: '/staffs' },
    { title: props.teacher.full_name, href: `/staffs/${props.teacher.id}` },
];

const activeTab = ref('personal');
const isEditing = ref(false);

const form = useForm({
    _method: 'PATCH',
    first_name: props.teacher.first_name,
    middle_name: props.teacher.middle_name ?? '',
    last_name: props.teacher.last_name,
    email: props.teacher.email,
    phone: props.teacher.phone,
    staff_number: props.teacher.staff_number,
    tsc_number: props.teacher.tsc_number ?? '',
    gender: props.teacher.gender,
    date_of_birth: props.teacher.date_of_birth ?? '',
    id_number: props.teacher.id_number ?? '',
    nationality: props.teacher.nationality,
    department_id: props.teacher.department?.id ?? '',
    staff_category_id: props.teacher.staff_category?.id ?? '',
    staff_designation_id: props.teacher.staff_designation?.id ?? '',
    contract_type: props.teacher.contract_type ?? 'Permanent',
    employment_type: props.teacher.employment_type ?? 'Full-time',
    date_joined: props.teacher.date_joined ?? '',
    basic_salary: props.teacher.basic_salary ?? '',
    alternate_phone: props.teacher.alternate_phone ?? '',
    address: props.teacher.address ?? '',
    county: props.teacher.county ?? '',
    sub_county: props.teacher.sub_county ?? '',
    marital_status: props.teacher.marital_status ?? '',
    blood_group: props.teacher.blood_group ?? '',
    emergency_contact_name: props.teacher.emergency_contact_name ?? '',
    emergency_contact_phone: props.teacher.emergency_contact_phone ?? '',
    emergency_contact_relationship: props.teacher.emergency_contact_relationship ?? '',
    bank_name: props.teacher.bank_name ?? '',
    bank_account_number: props.teacher.bank_account_number ?? '',
    bank_branch: props.teacher.bank_branch ?? '',
    nhif_number: props.teacher.nhif_number ?? '',
    nssf_number: props.teacher.nssf_number ?? '',
    kra_pin: props.teacher.kra_pin ?? '',
    notes: props.teacher.notes ?? '',
    status: props.teacher.status,
    role: props.teacher.staff_designation?.name.toLowerCase().includes('teacher') ? 'teacher' : 'staff', // Fallback
});

const saveChanges = () => {
    form.post(`/staffs/${props.teacher.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            router.reload();
        },
    });
};

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active': return 'bg-emerald-500 text-white shadow-emerald-200/50';
        case 'on_leave': return 'bg-amber-500 text-white shadow-amber-200/50';
        case 'suspended': return 'bg-rose-500 text-white shadow-rose-200/50';
        default: return 'bg-slate-400 text-white';
    }
};

const tabs = [
    { id: 'personal', label: 'Personal', icon: Users },
    { id: 'employment', label: 'Employment', icon: Briefcase },
    { id: 'academic', label: 'Academic', icon: GraduationCap },
    { id: 'location', label: 'Location', icon: MapPin },
    { id: 'financial', label: 'Financial', icon: ShieldCheck },
    { id: 'security', label: 'Security', icon: ShieldPlus },
];
</script>

<template>
    <Head :title="teacher.full_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1400px] mx-auto pb-20 p-6 md:p-8">
            
            <!-- Dynamic Subheader -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 text-muted-foreground hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-100">
                        <Link href="/staffs"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div class="flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
                        <!-- Profile Photo -->
                        <div class="relative group">
                            <ProfilePhotoUpload 
                                :upload-url="`/staffs/${teacher.id}/photo`" 
                                @uploaded="() => router.reload()"
                            >
                                <template #default="{ isUploading }">
                                    <div class="h-28 w-28 md:h-36 rounded-[2.5rem] overflow-hidden border-4 border-white dark:border-zinc-900 shadow-2xl bg-muted ring-1 ring-slate-200/50 dark:ring-white/5 relative transition-transform duration-500 group-hover:scale-[1.02]">
                                        <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                        <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-600 text-white text-4xl font-black">
                                            {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                        </div>
                                        <div class="absolute inset-0 bg-black/40 transition-opacity flex items-center justify-center cursor-pointer" :class="isUploading ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                            <Loader2 v-if="isUploading" class="h-8 w-8 text-white animate-spin" />
                                            <Camera v-else class="h-8 w-8 text-white" />
                                        </div>
                                    </div>
                                </template>
                            </ProfilePhotoUpload>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ teacher.full_name }}</h1>
                                <Badge :class="getStatusColor(teacher.status)" class="rounded-lg px-3 py-1 text-[10px] font-bold uppercase tracking-wider border-0 shadow-sm">
                                    {{ teacher.status }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2 mt-2 text-sm font-medium text-muted-foreground">
                                <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-lg border border-blue-100/50 font-bold uppercase tracking-tighter text-[10px]">{{ teacher.staff_number }}</span>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span>{{ teacher.department?.name || 'Unassigned' }}</span>
                                <span v-if="teacher.tsc_number" class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span v-if="teacher.tsc_number" class="text-indigo-600 font-bold italic">TSC: {{ teacher.tsc_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-white/50 dark:bg-zinc-900/50 p-2 rounded-2xl border border-slate-100 dark:border-zinc-800 backdrop-blur-sm self-center md:self-auto shadow-sm">
                    <template v-if="!isEditing">
                        <Button @click="isEditing = true" variant="ghost" class="rounded-xl h-11 px-6 font-bold text-slate-600 hover:text-blue-600 hover:bg-blue-50/50 transition-all gap-2">
                            <Edit class="h-4 w-4" />
                            Edit Personnel
                        </Button>
                        <Button variant="ghost" class="rounded-xl h-11 w-11 p-0 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all border border-transparent hover:border-rose-100">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </template>
                    <template v-else>
                        <Button @click="isEditing = false" variant="ghost" class="rounded-xl h-11 px-6 font-bold text-slate-500 hover:bg-slate-50 transition-all">
                            Cancel
                        </Button>
                        <Button @click="saveChanges" :disabled="form.processing" class="rounded-xl h-11 px-8 font-bold bg-slate-900 hover:bg-black text-white shadow-xl shadow-slate-200 dark:shadow-none transition-all gap-2">
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <Save v-else class="h-4 w-4" />
                            Save Profile
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Profile Content Area -->
            <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 items-start">
                <!-- Navigation Sidebar -->
                <aside class="flex flex-col gap-2 p-2 bg-white/60 dark:bg-zinc-900/60 rounded-[2rem] border border-slate-100 dark:border-zinc-800 backdrop-blur-sm sticky top-24 shadow-sm">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-3 px-5 py-4 rounded-2xl text-sm font-bold transition-all duration-300 group relative"
                        :class="activeTab === tab.id 
                            ? 'bg-slate-900 text-white shadow-lg shadow-slate-200 dark:shadow-none translate-x-1' 
                            : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-zinc-800 hover:text-slate-900 dark:hover:text-white'"
                    >
                        <component :is="tab.icon" class="h-4 w-4" :class="activeTab === tab.id ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-600'" />
                        {{ tab.label }}
                        <div v-if="activeTab === tab.id" class="absolute left-0 w-1 h-6 bg-blue-500 rounded-full"></div>
                    </button>
                </aside>

                <!-- Information Cards -->
                <main class="space-y-6">
                    <!-- Personal Info Tab -->
                    <div v-if="activeTab === 'personal'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-blue-600">
                                        <Users class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Personal Identity</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Core biographical information and records.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">First Name</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.first_name }}</div>
                                        <Input v-else v-model="form.first_name" class="h-11 rounded-xl border-slate-200 bg-white" />
                                        <InputError :message="form.errors.first_name" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Middle Name</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.middle_name || '—' }}</div>
                                        <Input v-else v-model="form.middle_name" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Last Name</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.last_name }}</div>
                                        <Input v-else v-model="form.last_name" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Gender</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white capitalize">{{ props.teacher.gender }}</div>
                                        <select v-else v-model="form.gender" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Date of Birth</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.date_of_birth || '—' }}</div>
                                        <Input v-else v-model="form.date_of_birth" type="date" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">ID / Passport Number</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.id_number || '—' }}</div>
                                        <Input v-else v-model="form.id_number" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Nationality</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.nationality }}</div>
                                        <Input v-else v-model="form.nationality" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                    
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Marital Status</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.marital_status || '—' }}</div>
                                        <select v-else v-model="form.marital_status" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            <option value="">Select Status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Blood Group</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.blood_group || '—' }}</div>
                                        <Input v-else v-model="form.blood_group" class="h-11 rounded-xl border-slate-200 bg-white" placeholder="e.g O+" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Contact & Email Card -->
                        <Card class="mt-8 border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-blue-600">
                                        <Mail class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Channels</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Primary contact and system access details.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Primary Email</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.email }}</div>
                                        <Input v-else v-model="form.email" type="email" class="h-11 rounded-xl border-slate-200 bg-white font-bold" />
                                        <InputError :message="form.errors.email" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Primary Phone</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.phone }}</div>
                                        <Input v-else v-model="form.phone" class="h-11 rounded-xl border-slate-200 bg-white font-bold" />
                                        <InputError :message="form.errors.phone" />
                                    </div>
                                    
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Alternate Phone</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.alternate_phone || '—' }}</div>
                                        <Input v-else v-model="form.alternate_phone" class="h-11 rounded-xl border-slate-200 bg-white font-bold" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Employment Tab -->
                    <div v-if="activeTab === 'employment'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-blue-600">
                                        <Briefcase class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Career Record</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Institutional placement and contract terms.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Department</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.department?.name || 'Unassigned' }}</div>
                                        <select v-else v-model="form.department_id" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Category</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.staff_category?.name || '—' }}</div>
                                        <select v-else v-model="form.staff_category_id" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Designation</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.staff_designation?.name || '—' }}</div>
                                        <select v-else v-model="form.staff_designation_id" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            <option v-for="desig in designations" :key="desig.id" :value="desig.id">{{ desig.name }}</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Contract Type</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.contract_type || '—' }}</div>
                                        <select v-else v-model="form.contract_type" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold">
                                            <option value="Permanent">Permanent</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Probation">Probation</option>
                                            <option value="Internship">Internship</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Employment Type</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.employment_type || '—' }}</div>
                                        <select v-else v-model="form.employment_type" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold">
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Consultant">Consultant</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Date Joined</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.date_joined || '—' }}</div>
                                        <Input v-else v-model="form.date_joined" type="date" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Academic/Assignments Tab -->
                    <div v-if="activeTab === 'academic'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-blue-600">
                                        <BookOpen class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Active Assignments</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Class leadership and subject allocations.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <Building2 class="h-3.5 w-3.5" /> Class Teacher For
                                        </h3>
                                        <div v-if="teacher.classes_as_teacher.length" class="space-y-3">
                                            <div v-for="cls in teacher.classes_as_teacher" :key="cls.id" class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800">
                                                <div>
                                                    <p class="font-black text-slate-900 dark:text-white">{{ cls.name }}</p>
                                                    <p class="text-[10px] font-bold text-blue-600 uppercase">{{ cls.grade_level.name }}</p>
                                                </div>
                                                <Button size="icon" variant="ghost" class="h-10 w-10 text-slate-400 hover:text-blue-600 rounded-xl" as-child>
                                                    <Link :href="`/classes/${cls.id}`"><Eye class="h-4 w-4" /></Link>
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="h-32 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 text-slate-400">
                                            <Users class="h-8 w-8 mb-2 opacity-20" />
                                            <p class="text-xs font-medium uppercase italic">No Class Assigned</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <BookOpen class="h-3.5 w-3.5" /> Subject Allocations
                                        </h3>
                                        <div v-if="teacher.subject_assignments.length" class="grid gap-3">
                                            <div v-for="assignment in teacher.subject_assignments" :key="assignment.id" class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 flex items-center justify-between group">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black italic">
                                                        {{ assignment.subject.name[0] }}
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-slate-900 dark:text-white">{{ assignment.subject.name }}</p>
                                                        <p class="text-[10px] text-slate-400 font-bold uppercase">{{ assignment.school_class.name }}</p>
                                                    </div>
                                                </div>
                                                <Badge v-if="assignment.is_primary_teacher" class="bg-indigo-50 text-indigo-600 border-none text-[8px] font-black">HEAD</Badge>
                                            </div>
                                        </div>
                                        <div v-else class="h-32 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 text-slate-400">
                                            <BookOpen class="h-8 w-8 mb-2 opacity-20" />
                                            <p class="text-xs font-medium uppercase italic">No Subjects Allocated</p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Location Tab -->
                    <div v-if="activeTab === 'location'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-blue-600">
                                        <MapPin class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Residential Details</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Current address and geographical records.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">County</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.county || '—' }}</div>
                                        <select v-else v-model="form.county" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold">
                                            <option value="">Select County</option>
                                            <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Sub County</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.sub_county || '—' }}</div>
                                        <Input v-else v-model="form.sub_county" class="h-11 rounded-xl border-slate-200 bg-white font-bold" />
                                    </div>

                                    <div class="col-span-1 md:col-span-2 space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Detailed Address</Label>
                                        <div v-if="!isEditing" class="min-h-[6rem] p-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-medium text-slate-600 dark:text-slate-400 italic">{{ props.teacher.address || 'No specific address details provided.' }}</div>
                                        <textarea v-else v-model="form.address" class="w-full min-h-[6rem] rounded-xl border border-slate-200 bg-white p-4 text-sm font-bold"></textarea>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <!-- Emergency Contacts -->
                        <Card class="mt-8 border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-rose-600">
                                        <AlertCircle class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Crisis Point</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Who to call in case of emergency.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Guardian Name</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.emergency_contact_name || '—' }}</div>
                                        <Input v-else v-model="form.emergency_contact_name" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Relationship</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.emergency_contact_relationship || '—' }}</div>
                                        <Input v-else v-model="form.emergency_contact_relationship" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Urgent Phone</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-rose-600 dark:text-rose-400 italic tracking-tighter">{{ props.teacher.emergency_contact_phone || '—' }}</div>
                                        <Input v-else v-model="form.emergency_contact_phone" class="h-11 rounded-xl border-slate-200 bg-white font-bold" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Financial Tab -->
                    <div v-if="activeTab === 'financial'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-blue-600">
                                        <Globe class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Banking & Payroll</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">Salary disbursement and statutory records.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Bank Institution</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white">{{ props.teacher.bank_name || '—' }}</div>
                                        <Input v-else v-model="form.bank_name" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Branch Name</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic">{{ props.teacher.bank_branch || '—' }}</div>
                                        <Input v-else v-model="form.bank_branch" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Account Number</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold text-slate-900 dark:text-white italic tracking-tighter">{{ props.teacher.bank_account_number || '—' }}</div>
                                        <Input v-else v-model="form.bank_account_number" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Basic Salary</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 font-black text-emerald-600 italic">KES {{ props.teacher.basic_salary ? Number(props.teacher.basic_salary).toLocaleString() : '0.00' }}</div>
                                        <Input v-else v-model="form.basic_salary" type="number" class="h-11 rounded-xl border-slate-200 bg-white font-bold" />
                                    </div>
                                    
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">KRA PIN</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold">{{ props.teacher.kra_pin || '—' }}</div>
                                        <Input v-else v-model="form.kra_pin" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                    
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">NHIF NUMBER</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold italic">{{ props.teacher.nhif_number || '—' }}</div>
                                        <Input v-else v-model="form.nhif_number" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                    
                                    <div class="space-y-2.5">
                                        <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">NSSF NUMBER</Label>
                                        <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold italic">{{ props.teacher.nssf_number || '—' }}</div>
                                        <Input v-else v-model="form.nssf_number" class="h-11 rounded-xl border-slate-200 bg-white" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Security Tab -->
                    <div v-if="activeTab === 'security'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                            <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center text-rose-600">
                                        <ShieldPlus class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Vault & Access</CardTitle>
                                        <CardDescription class="text-slate-500 font-medium tracking-tight">System permissions and security settings.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-6">
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest pl-1 italic">Security overrides are restricted to administrators.</p>
                                        <div class="space-y-4">
                                            <div class="space-y-2.5">
                                                <Label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Login Status</Label>
                                                <div v-if="!isEditing" class="h-11 flex items-center px-4 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800 font-bold capitalize">{{ props.teacher.status }}</div>
                                                <select v-else v-model="form.status" class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold">
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                    <option value="on_leave">On Leave</option>
                                                    <option value="suspended">Suspended</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6 rounded-[2rem] bg-slate-50 dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 flex flex-col items-center justify-center text-center">
                                        <ShieldCheck class="h-12 w-12 text-slate-300 mb-4" />
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest italic leading-relaxed">System logs for this user are available in the audit dashboard.</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </main>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
