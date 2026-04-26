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
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Faculty Registry', href: '/staffs' },
    { title: props.teacher.full_name, href: `/staffs/${props.teacher.id}` },
];

const activeTab = ref('intelligence');

const tabs = [
    { id: 'intelligence', label: 'Intelligence', icon: Activity },
    { id: 'professional', label: 'Professional', icon: Briefcase },
    { id: 'academic', label: 'Academic', icon: GraduationCap },
    { id: 'contact', label: 'Communication', icon: Contact },
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
    { label: 'Assigned Nodes', val: props.teacher.classes_as_teacher.length, sub: 'Classes', icon: Building2 },
    { label: 'Subject Loads', val: props.teacher.subject_assignments.length, sub: 'Curriculum', icon: BookOpen },
    { label: 'Fidelity Score', val: '98%', sub: 'Active sync', icon: ShieldCheck },
]);
</script>

<template>
    <Head :title="`FACULTY_${teacher.full_name.toUpperCase()}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- High-Fidelity Profile Header -->
            <div class="relative overflow-hidden rounded-[3rem] border border-border bg-card p-10 shadow-sm transition-all hover:border-primary/20">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary/5 blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>
                <div class="relative flex flex-col gap-10 lg:flex-row lg:items-center">
                    <!-- Biometric Visual ID -->
                    <div class="group relative shrink-0">
                         <div class="relative h-40 w-40 overflow-hidden rounded-[2.5rem] border-4 border-white bg-slate-100 shadow-2xl transition-transform duration-500 hover:scale-105 active:scale-95 shadow-primary/10">
                            <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-primary text-5xl font-black text-white">
                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                            </div>
                         </div>
                         <div class="absolute -bottom-2 -right-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-white p-1 shadow-xl">
                            <div :class="getStatusColor(teacher.status)" class="h-full w-full rounded-xl flex items-center justify-center">
                                <Activity class="h-5 w-5 text-white" />
                            </div>
                         </div>
                    </div>

                    <!-- Faculty Identification -->
                    <div class="flex-1 space-y-6">
                        <div class="space-y-2">
                             <div class="flex flex-wrap items-center gap-4">
                                <h1 class="text-4xl font-black tracking-tight text-foreground uppercase">{{ teacher.full_name }}</h1>
                                <Badge variant="outline" class="rounded-xl border-primary/20 bg-primary/10 px-4 py-1 text-[10px] font-black tracking-[0.2em] text-primary uppercase shadow-sm">
                                    {{ teacher.staff_number }}
                                </Badge>
                             </div>
                             <p class="text-[10px] font-bold tracking-[0.3em] text-muted-foreground uppercase opacity-60">
                                {{ teacher.staff_designation?.name || 'Unassigned' }} // {{ teacher.department?.name || 'Academic Core' }}
                             </p>
                        </div>

                        <!-- Real-time Metrics -->
                        <div class="flex flex-wrap gap-10">
                            <div v-for="(stat, idx) in stats" :key="idx" class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted/50 text-primary shadow-sm"><component :is="stat.icon" class="h-5 w-5" /></div>
                                <div class="space-y-0.5">
                                    <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">{{ stat.label }}</p>
                                    <p class="text-sm font-bold tracking-tight text-foreground uppercase">{{ stat.val }} <span class="text-[10px] opacity-40">{{ stat.sub }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Management Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <Button variant="outline" class="h-14 rounded-2xl border-border bg-card px-8 text-[10px] font-black tracking-[0.2em] uppercase hover:bg-muted" as-child>
                             <Link :href="`/staffs/${teacher.id}/edit`">
                                <Edit class="mr-3 h-4 w-4 text-primary" />
                                Mutate Node
                             </Link>
                        </Button>
                        <Button class="h-14 rounded-2xl bg-slate-900 px-8 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-2xl transition-all hover:scale-[1.05]">
                            <Settings class="mr-3 h-4 w-4" />
                            Security Keys
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Content Matrix -->
            <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-[320px_1fr]">
                <!-- Strategic Navigation Matrix -->
                <aside class="sticky top-28 space-y-3">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="group relative flex w-full items-center gap-4 rounded-3xl p-6 text-sm font-bold transition-all duration-500"
                        :class="activeTab === tab.id
                            ? 'bg-primary text-white shadow-2xl shadow-primary/30 translate-x-3 scale-[1.02]'
                            : 'bg-card text-muted-foreground border border-border/50 hover:border-primary/20 hover:bg-muted/10'"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl transition-colors"
                             :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground group-hover:text-primary'">
                            <component :is="tab.icon" class="h-5 w-5" />
                        </div>
                        <span class="tracking-widest uppercase text-[10px] font-black">{{ tab.label }}</span>
                        <ChevronRight v-if="activeTab === tab.id" class="ml-auto h-4 w-4 animate-pulse" />
                    </button>
                    
                    <div class="mt-12 rounded-3xl border border-primary/10 bg-primary/5 p-8 text-center">
                        <ShieldCheck class="mx-auto mb-4 h-10 w-10 text-primary opacity-40" />
                        <h4 class="text-[10px] font-black tracking-widest text-primary uppercase mb-2">Vault Fidelity</h4>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">Full institutional data encryption protocol active</p>
                    </div>
                </aside>

                <!-- Intelligence Display -->
                <main class="space-y-12">
                     <!-- Intelligence Tab (Overview) -->
                    <div v-if="activeTab === 'intelligence'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                         <div class="grid gap-8 md:grid-cols-2">
                             <div class="group overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                                <div class="border-b border-border/50 bg-muted/10 px-8 py-6">
                                    <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Identity synchronization</h3>
                                </div>
                                <div class="p-8 space-y-6">
                                    <div v-for="(val, label) in {
                                        'Full Legal Name': teacher.full_name,
                                        'Biological Mode': teacher.gender,
                                        'Temporal Identity': teacher.date_of_birth || 'N/A',
                                        'Nationality': teacher.nationality,
                                        'Marital State': teacher.marital_status || 'Single',
                                        'State ID Key': teacher.id_number || 'N/A'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-black text-foreground uppercase">{{ val }}</span>
                                    </div>
                                </div>
                             </div>

                             <div class="group overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                                <div class="border-b border-border/50 bg-muted/10 px-8 py-6">
                                    <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Institutional telemetry</h3>
                                </div>
                                <div class="p-8 space-y-6">
                                    <div v-for="(val, label) in {
                                        'Operational Key': teacher.staff_number,
                                        'External TSC Key': teacher.tsc_number || 'NONE',
                                        'Strategic Dept': teacher.department?.name || 'CORE',
                                        'Node Category': teacher.staff_category?.name || 'GENERAL',
                                        'Contract Matrix': teacher.contract_type || 'PERMANENT',
                                        'Ingression Date': teacher.date_joined || 'N/A'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-black text-primary uppercase">{{ val }}</span>
                                    </div>
                                </div>
                             </div>
                         </div>
                    </div>

                    <!-- Academic Tab -->
                    <div v-if="activeTab === 'academic'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                        <div class="space-y-12">
                             <!-- Assigned Classes -->
                             <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                                <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-10 py-6">
                                    <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Primary stream assignments</h3>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-xl text-[9px] font-black uppercase text-primary hover:bg-primary/10">Manage streams</Button>
                                </div>
                                <div class="p-10">
                                    <div v-if="teacher.classes_as_teacher.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                        <div v-for="cls in teacher.classes_as_teacher" :key="cls.id" class="group flex items-center justify-between rounded-[2rem] border border-border bg-muted/20 p-6 transition-all hover:border-primary/20 hover:bg-card">
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-black tracking-widest text-primary uppercase">{{ cls.grade_level?.name }}</p>
                                                <p class="text-sm font-black text-foreground uppercase">{{ cls.name }}</p>
                                            </div>
                                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-border/30"><Building2 class="h-5 w-5 text-muted-foreground" /></div>
                                        </div>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-[2.5rem] bg-muted/50 text-muted-foreground opacity-30"><AlertCircle class="h-8 w-8" /></div>
                                        <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40">No primary stream assignments identified</p>
                                    </div>
                                </div>
                             </div>

                             <!-- Subject Ingestion Matrix -->
                             <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                                <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-10 py-6">
                                    <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Subject Load Matrix</h3>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-xl text-[9px] font-black uppercase text-primary hover:bg-primary/10">Modify matrix</Button>
                                </div>
                                <div class="p-10">
                                    <div v-if="teacher.subject_assignments.length > 0" class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="text-[9px] font-black tracking-[0.2em] text-muted-foreground uppercase">
                                                    <th class="pb-6">Subject Knowledge Node</th>
                                                    <th class="pb-6">Temporal Channel</th>
                                                    <th class="pb-6">Access Protocol</th>
                                                    <th class="pb-6 text-right">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border/50">
                                                <tr v-for="sa in teacher.subject_assignments" :key="sa.id" class="group transition-all">
                                                    <td class="py-5">
                                                        <div class="flex items-center gap-4">
                                                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/5 text-primary text-[10px] font-black border border-primary/10">{{ sa.subject.code }}</div>
                                                            <span class="text-xs font-black text-foreground uppercase">{{ sa.subject.name }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-5 text-xs font-bold text-muted-foreground uppercase">{{ sa.school_class.name }}</td>
                                                    <td class="py-5 uppercase">
                                                        <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 text-[9px] font-black tracking-widest text-primary">{{ sa.is_primary_teacher ? 'Primary Node' : 'Secondary' }}</Badge>
                                                    </td>
                                                    <td class="py-5 text-right">
                                                         <div class="flex h-2.5 w-2.5 ml-auto rounded-full" :class="sa.is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]'"></div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-12 text-center opacity-30">
                                        <BookOpen class="mb-4 h-10 w-10" />
                                        <p class="text-[10px] font-black tracking-widest uppercase">Null Subject Matrix</p>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                    
                    <!-- Professional & Financial (Compact) -->
                     <div v-if="activeTab === 'professional' || activeTab === 'financial'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                        <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm">
                            <div class="border-b border-border/50 bg-muted/10 px-10 py-6">
                                <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">{{ activeTab }} protocol data</h3>
                            </div>
                            <div class="p-10">
                                <div class="grid gap-10 md:grid-cols-2">
                                     <div v-for="(val, label) in (activeTab === 'professional' ? {
                                        'Employment Type': teacher.employment_type || 'Full-time',
                                        'Strategic Category': teacher.staff_category?.name || 'General',
                                        'Designation Key': teacher.staff_designation?.name || 'N/A',
                                        'Persistence Mode': teacher.contract_type || 'Permanent'
                                    } : {
                                        'Base Salary Matrix': `KES ${teacher.basic_salary?.toLocaleString() || '0'}`,
                                        'NHIF Identification': teacher.nhif_number || 'N/A',
                                        'NSSF Identification': teacher.nssf_number || 'N/A',
                                        'KRA PIN Key': teacher.kra_pin || 'N/A',
                                        'Financial Node': teacher.bank_name || 'N/A',
                                        'Account Protocol': teacher.bank_account_number || 'N/A'
                                    })" :key="label" class="space-y-2">
                                        <p class="text-[9px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-40">{{ label }}</p>
                                        <p class="text-sm font-black text-foreground uppercase">{{ val }}</p>
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
