<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Users,
    ArrowLeft,
    Mail,
    Phone,
    MapPin,
    Calendar,
    Briefcase,
    GraduationCap,
    BookOpen,
    FileText,
    Settings,
    Edit,
    Camera,
    CheckCircle2,
    ShieldCheck,
    Building2,
    Hash,
    Contact,
    Globe,
    Trash2,
    Save,
    X,
    Plus,
    Loader2,
    AlertCircle,
    HeartPulse,
    ShieldPlus,
    Activity,
    Database,
    Zap,
    ExternalLink,
    ChevronRight,
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
    availableClasses: AvailableClass[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/staffs' },
    { title: props.teacher.full_name, href: `/staffs/${props.teacher.id}` },
];

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', label: 'Overview', icon: Activity },
    { id: 'professional', label: 'Employment', icon: Briefcase },
    { id: 'academic', label: 'Academic', icon: GraduationCap },
    { id: 'contact', label: 'Contact', icon: Contact },
    { id: 'financial', label: 'Financial', icon: ShieldCheck },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20';
        case 'on_leave':
            return 'bg-amber-500 text-white shadow-lg shadow-amber-500/20';
        case 'suspended':
            return 'bg-rose-500 text-white shadow-lg shadow-rose-500/20';
        default:
            return 'bg-slate-400 text-white';
    }
};

const stats = computed(() => [
    { label: 'Assigned', val: props.teacher.classes_as_teacher.length, sub: 'Classes', icon: Building2 },
    { label: 'Teaching', val: props.teacher.subject_assignments.length, sub: 'Subjects', icon: BookOpen },
    { label: 'Profile', val: '100%', sub: 'Complete', icon: ShieldCheck },
]);
</script>

<template>
    <Head :title="`Staff Profile: ${teacher.full_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Profile Header -->
            <div class="relative overflow-hidden rounded-xl border border-border bg-card p-10 shadow-sm">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary/5 blur-3xl"></div>
                <div class="relative flex flex-col gap-10 lg:flex-row lg:items-center">
                    <!-- Profile Photo -->
                    <div class="group relative shrink-0">
                         <div class="relative h-40 w-40 overflow-hidden border-2 border-primary/10 rounded-xl bg-slate-50 shadow-lg">
                            <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-primary text-5xl font-black text-white">
                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                            </div>
                         </div>
                         <div class="absolute -bottom-2 -right-2 flex h-10 w-10 items-center justify-center rounded-lg bg-white p-1 shadow-md">
                            <div :class="getStatusColor(teacher.status)" class="h-full w-full rounded-md flex items-center justify-center">
                                <Activity class="h-4 w-4 text-white" />
                            </div>
                         </div>
                    </div>

                    <!-- Staff Details -->
                    <div class="flex-1 space-y-6">
                        <div class="space-y-2">
                             <div class="flex flex-wrap items-center gap-4">
                                <h1 class="text-4xl font-bold tracking-tight text-foreground">{{ teacher.full_name }}</h1>
                                <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/10 px-4 py-1 text-xs font-bold text-primary uppercase">
                                    {{ teacher.staff_number }}
                                </Badge>
                             </div>
                             <p class="text-sm font-medium text-muted-foreground uppercase opacity-60">
                                {{ teacher.staff_designation?.name || 'Unassigned' }} // {{ teacher.department?.name || 'Academic Core' }}
                             </p>
                        </div>

                        <!-- Metrics -->
                        <div class="flex flex-wrap gap-10">
                            <div v-for="(stat, idx) in stats" :key="idx" class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted/50 text-primary"><component :is="stat.icon" class="h-5 w-5" /></div>
                                <div class="space-y-0.5">
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-40">{{ stat.label }}</p>
                                    <p class="text-sm font-bold text-foreground uppercase">{{ stat.val }} <span class="text-[10px] opacity-40">{{ stat.sub }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <Button variant="outline" class="h-10 rounded-lg px-6 text-xs font-semibold uppercase" as-child>
                             <Link :href="`/staffs/${teacher.id}/edit`">
                                <Edit class="mr-2 h-4 w-4 text-primary" />
                                Edit Profile
                             </Link>
                        </Button>
                        <Button class="h-10 rounded-lg bg-slate-900 px-6 text-xs font-semibold text-white uppercase shadow-sm">
                            <Settings class="mr-2 h-4 w-4" />
                            Settings
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Content Matrix -->
            <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-[320px_1fr]">
                <!-- Navigation -->
                <aside class="sticky top-28 space-y-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="group relative flex w-full items-center gap-4 rounded-xl p-4 text-sm font-semibold transition-all duration-300"
                        :class="activeTab === tab.id
                            ? 'bg-primary text-white shadow-lg shadow-primary/20'
                            : 'bg-card text-muted-foreground border border-border/50 hover:border-primary/20 hover:bg-muted/10'"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors"
                             :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground group-hover:text-primary'">
                            <component :is="tab.icon" class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-bold uppercase">{{ tab.label }}</span>
                        <ChevronRight v-if="activeTab === tab.id" class="ml-auto h-4 w-4" />
                    </button>
                    
                    <div class="mt-8 rounded-xl border border-primary/10 bg-primary/5 p-6 text-center">
                        <ShieldCheck class="mx-auto mb-3 h-8 w-8 text-primary opacity-30" />
                        <h4 class="text-[10px] font-bold text-primary uppercase mb-1">Status</h4>
                        <p class="text-[10px] font-medium text-muted-foreground uppercase opacity-60">Verified School Record</p>
                    </div>
                </aside>

                <!-- Intelligence Display -->
                <main class="space-y-12">
                     <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                         <div class="grid gap-8 md:grid-cols-2">
                             <div class="group overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                                <div class="border-b border-border/50 bg-muted/5 px-8 py-5">
                                    <h3 class="text-xs font-bold text-foreground uppercase">Personal Information</h3>
                                </div>
                                <div class="p-8 space-y-5">
                                    <div v-for="(val, label) in {
                                        'Full Name': teacher.full_name,
                                        'Gender': teacher.gender,
                                        'Date of Birth': teacher.date_of_birth || 'N/A',
                                        'Nationality': teacher.nationality,
                                        'Marital Status': teacher.marital_status || 'Single',
                                        'ID Number': teacher.id_number || 'N/A'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-bold text-foreground uppercase">{{ val }}</span>
                                    </div>
                                </div>
                             </div>
 
                             <div class="group overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                                <div class="border-b border-border/50 bg-muted/5 px-8 py-5">
                                    <h3 class="text-xs font-bold text-foreground uppercase">Employment Information</h3>
                                </div>
                                <div class="p-8 space-y-5">
                                    <div v-for="(val, label) in {
                                        'Staff Number': teacher.staff_number,
                                        'TSC Number': teacher.tsc_number || 'NONE',
                                        'Department': teacher.department?.name || 'CORE',
                                        'Category': teacher.staff_category?.name || 'GENERAL',
                                        'Contract Type': teacher.contract_type || 'PERMANENT',
                                        'Join Date': teacher.date_joined || 'N/A'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-bold text-primary uppercase">{{ val }}</span>
                                    </div>
                                </div>
                             </div>
                         </div>
                    </div>

                    <!-- Academic Tab -->
                    <div v-if="activeTab === 'academic'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                        <div class="space-y-12">
                             <!-- Assigned Classes -->
                             <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                                <div class="flex items-center justify-between border-b border-border/50 bg-muted/5 px-8 py-5">
                                    <h3 class="text-xs font-bold text-foreground uppercase">Assigned Classes</h3>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-lg text-[10px] font-bold uppercase text-primary">Manage Classes</Button>
                                </div>
                                <div class="p-8">
                                    <div v-if="teacher.classes_as_teacher.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                        <div v-for="cls in teacher.classes_as_teacher" :key="cls.id" class="group flex items-center justify-between rounded-xl border border-border bg-muted/10 p-5 transition-all hover:bg-card">
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-bold text-primary uppercase">{{ cls.grade_level?.name }}</p>
                                                <p class="text-sm font-bold text-foreground uppercase">{{ cls.name }}</p>
                                            </div>
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm ring-1 ring-border/30"><Building2 class="h-5 w-5 text-muted-foreground" /></div>
                                        </div>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-muted/50 text-muted-foreground opacity-30"><AlertCircle class="h-6 w-6" /></div>
                                        <p class="text-xs font-medium text-muted-foreground opacity-60">No classes assigned yet.</p>
                                    </div>
                                </div>
                             </div>

                             <!-- Subject Assignments -->
                             <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                                <div class="flex items-center justify-between border-b border-border/50 bg-muted/5 px-8 py-5">
                                    <h3 class="text-xs font-bold text-foreground uppercase">Subject Assignments</h3>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-lg text-[10px] font-bold uppercase text-primary">Manage Subjects</Button>
                                </div>
                                <div class="p-8">
                                    <div v-if="teacher.subject_assignments.length > 0" class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="text-[10px] font-bold text-muted-foreground uppercase">
                                                    <th class="pb-4">Subject</th>
                                                    <th class="pb-4">Class</th>
                                                    <th class="pb-4">Role</th>
                                                    <th class="pb-4 text-right">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border/50">
                                                <tr v-for="sa in teacher.subject_assignments" :key="sa.id">
                                                    <td class="py-4">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/5 text-primary text-[10px] font-bold border border-primary/10">{{ sa.subject.code }}</div>
                                                            <span class="text-xs font-bold text-foreground uppercase">{{ sa.subject.name }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 text-xs font-semibold text-muted-foreground">{{ sa.school_class.name }}</td>
                                                    <td class="py-4 uppercase">
                                                        <Badge variant="outline" class="rounded-md border-primary/20 bg-primary/5 text-[9px] font-bold text-primary">{{ sa.is_primary_teacher ? 'Primary' : 'Secondary' }}</Badge>
                                                    </td>
                                                    <td class="py-4 text-right">
                                                         <div class="flex h-2 w-2 ml-auto rounded-full" :class="sa.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-12 text-center opacity-30">
                                        <BookOpen class="mb-4 h-10 w-10" />
                                        <p class="text-xs font-bold uppercase">No subjects assigned.</p>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                    
                    <!-- Other Tabs -->
                     <div v-if="activeTab === 'professional' || activeTab === 'financial'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                            <div class="border-b border-border/50 bg-muted/5 px-8 py-5">
                                <h3 class="text-xs font-bold text-foreground uppercase">{{ activeTab }} Information</h3>
                            </div>
                            <div class="p-8">
                                <div class="grid gap-8 md:grid-cols-2">
                                     <div v-for="(val, label) in (activeTab === 'professional' ? {
                                        'Employment Type': teacher.employment_type || 'Full-time',
                                        'Staff Category': teacher.staff_category?.name || 'General',
                                        'Designation': teacher.staff_designation?.name || 'N/A',
                                        'Contract Type': teacher.contract_type || 'Permanent'
                                    } : {
                                        'Base Salary': `KES ${teacher.basic_salary?.toLocaleString() || '0'}`,
                                        'NHIF Number': teacher.nhif_number || 'N/A',
                                        'NSSF Number': teacher.nssf_number || 'N/A',
                                        'KRA PIN': teacher.kra_pin || 'N/A',
                                        'Bank Name': teacher.bank_name || 'N/A',
                                        'Account Number': teacher.bank_account_number || 'N/A'
                                    })" :key="label" class="space-y-1">
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-40">{{ label }}</p>
                                        <p class="text-sm font-bold text-foreground uppercase">{{ val }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AppLayout>
</template>
