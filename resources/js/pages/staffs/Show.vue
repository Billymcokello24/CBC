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
    emergency_contact_relationship:
        props.teacher.emergency_contact_relationship ?? '',
    bank_name: props.teacher.bank_name ?? '',
    bank_account_number: props.teacher.bank_account_number ?? '',
    bank_branch: props.teacher.bank_branch ?? '',
    nhif_number: props.teacher.nhif_number ?? '',
    nssf_number: props.teacher.nssf_number ?? '',
    kra_pin: props.teacher.kra_pin ?? '',
    notes: props.teacher.notes ?? '',
    status: props.teacher.status,
    role: props.teacher.staff_designation?.name
        .toLowerCase()
        .includes('teacher')
        ? 'teacher'
        : 'staff', // Fallback
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
        case 'active':
            return 'bg-emerald-500 text-white shadow-emerald-200/50';
        case 'on_leave':
            return 'bg-amber-500 text-white shadow-amber-200/50';
        case 'suspended':
            return 'bg-rose-500 text-white shadow-rose-200/50';
        default:
            return 'bg-slate-400 text-white';
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
        <div
            class="mx-auto max-w-[1400px] animate-in space-y-8 p-6 pb-20 duration-700 fade-in slide-in-from-bottom-4 md:p-8"
        >
            <!-- Dynamic Subheader -->
            <div
                class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-5">
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                        class="h-10 w-10 rounded-xl border border-transparent text-muted-foreground transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600"
                    >
                        <Link href="/staffs"
                            ><ArrowLeft class="h-5 w-5"
                        /></Link>
                    </Button>
                    <div
                        class="flex flex-col items-center gap-8 text-center md:flex-row md:text-left"
                    >
                        <!-- Profile Photo -->
                        <div class="group relative">
                            <ProfilePhotoUpload
                                :upload-url="`/staffs/${teacher.id}/photo`"
                                @uploaded="() => router.reload()"
                            >
                                <template #default="{ isUploading }">
                                    <div
                                        class="relative h-28 w-28 overflow-hidden rounded-xl border-4 border-white bg-muted shadow-lg ring-1 ring-slate-200/50 transition-transform duration-500 group-hover:scale-[1.02] md:h-36 dark:border-zinc-900 dark:ring-white/5"
                                    >
                                        <img
                                            v-if="teacher.photo_url"
                                            :src="teacher.photo_url"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-600 text-4xl font-bold text-white"
                                        >
                                            {{ teacher.first_name[0]
                                            }}{{ teacher.last_name[0] }}
                                        </div>
                                        <div
                                            class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/40 transition-opacity"
                                            :class="
                                                isUploading
                                                    ? 'opacity-100'
                                                    : 'opacity-0 group-hover:opacity-100'
                                            "
                                        >
                                            <Loader2
                                                v-if="isUploading"
                                                class="h-8 w-8 animate-spin text-white"
                                            />
                                            <Camera
                                                v-else
                                                class="h-8 w-8 text-white"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </ProfilePhotoUpload>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1
                                    class="text-3xl font-bold tracking-tight text-foreground"
                                >
                                    {{ teacher.full_name }}
                                </h1>
                                <Badge
                                    :class="getStatusColor(teacher.status)"
                                    class="rounded-lg border-0 px-3 py-1 text-xs font-bold tracking-wider uppercase shadow-sm"
                                >
                                    {{ teacher.status }}
                                </Badge>
                            </div>
                            <div
                                class="mt-2 flex items-center gap-2 text-sm font-medium text-muted-foreground"
                            >
                                <span
                                    class="rounded-lg border border-blue-100/50 bg-blue-50 px-2.5 py-1 text-xs font-bold tracking-tighter text-blue-600 uppercase"
                                    >{{ teacher.staff_number }}</span
                                >
                                <span
                                    class="h-1 w-1 rounded-full bg-slate-300"
                                ></span>
                                <span>{{
                                    teacher.department?.name || 'Unassigned'
                                }}</span>
                                <span
                                    v-if="teacher.tsc_number"
                                    class="h-1 w-1 rounded-full bg-slate-300"
                                ></span>
                                <span
                                    v-if="teacher.tsc_number"
                                    class="font-bold text-indigo-600"
                                    >TSC: {{ teacher.tsc_number }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center gap-3 self-center rounded-2xl border border-slate-100 bg-white/50 p-2 shadow-sm backdrop-blur-sm md:self-auto dark:border-zinc-800 dark:bg-zinc-900/50"
                >
                    <template v-if="!isEditing">
                        <Button
                            @click="isEditing = true"
                            variant="ghost"
                            class="h-11 gap-2 rounded-xl px-6 font-bold text-slate-600 transition-all hover:bg-blue-50/50 hover:text-blue-600"
                        >
                            <Edit class="h-4 w-4" />
                            Edit Personnel
                        </Button>
                        <Button
                            variant="ghost"
                            class="h-11 w-11 rounded-xl border border-transparent p-0 text-slate-400 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </template>
                    <template v-else>
                        <Button
                            @click="isEditing = false"
                            variant="ghost"
                            class="h-11 rounded-xl px-6 font-bold text-slate-500 transition-all hover:bg-slate-50"
                        >
                            Cancel
                        </Button>
                        <Button
                            @click="saveChanges"
                            :disabled="form.processing"
                            class="h-11 gap-2 rounded-xl bg-slate-900 px-8 font-bold text-white shadow-xl shadow-slate-200 transition-all hover:bg-black dark:shadow-none"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <Save v-else class="h-4 w-4" />
                            Save Profile
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Profile Content Area -->
            <div
                class="grid grid-cols-1 items-start gap-8 lg:grid-cols-[280px_1fr]"
            >
                <!-- Navigation Sidebar -->
                <aside
                    class="sticky top-24 flex flex-col gap-2 rounded-xl border border-slate-100 bg-white/60 p-2 shadow-sm backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/60"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="group relative flex items-center gap-3 rounded-2xl px-5 py-4 text-sm font-bold transition-all duration-300"
                        :class="
                            activeTab === tab.id
                                ? 'translate-x-1 bg-slate-900 text-white shadow-lg shadow-slate-200 dark:shadow-none'
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-zinc-800 dark:hover:text-white'
                        "
                    >
                        <component
                            :is="tab.icon"
                            class="h-4 w-4"
                            :class="
                                activeTab === tab.id
                                    ? 'text-blue-400'
                                    : 'text-slate-400 group-hover:text-slate-600'
                            "
                        />
                        {{ tab.label }}
                        <div
                            v-if="activeTab === tab.id"
                            class="absolute left-0 h-6 w-1 rounded-full bg-blue-500"
                        ></div>
                    </button>
                </aside>

                <!-- Information Cards -->
                <main class="space-y-6">
                    <!-- Personal Info Tab -->
                    <div
                        v-if="activeTab === 'personal'"
                        class="animate-in duration-500 fade-in slide-in-from-right-4"
                    >
                        <Card
                            class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <Users class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Personal Identity</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Core biographical information and
                                            records.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
                                >
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >First Name</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.first_name }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.first_name"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                        <InputError
                                            :message="form.errors.first_name"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Middle Name</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.middle_name || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.middle_name"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Last Name</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.last_name }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.last_name"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Gender</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 capitalize dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.gender }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.gender"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Date of Birth</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.date_of_birth ||
                                                '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.date_of_birth"
                                            type="date"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >ID / Passport Number</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.id_number || '—' }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.id_number"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Nationality</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.nationality }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.nationality"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Marital Status</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.marital_status ||
                                                '—'
                                            }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.marital_status"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option value="">
                                                Select Status
                                            </option>
                                            <option value="Single">
                                                Single
                                            </option>
                                            <option value="Married">
                                                Married
                                            </option>
                                            <option value="Divorced">
                                                Divorced
                                            </option>
                                            <option value="Widowed">
                                                Widowed
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Blood Group</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.blood_group || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.blood_group"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                            placeholder="e.g O+"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Contact & Email Card -->
                        <Card
                            class="mt-8 overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <Mail class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Channels</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Primary contact and system access
                                            details.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2"
                                >
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Primary Email</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.email }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.email"
                                            type="email"
                                            class="h-11 rounded-xl border-slate-200 bg-white font-bold"
                                        />
                                        <InputError
                                            :message="form.errors.email"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Primary Phone</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.phone }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.phone"
                                            class="h-11 rounded-xl border-slate-200 bg-white font-bold"
                                        />
                                        <InputError
                                            :message="form.errors.phone"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Alternate Phone</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.alternate_phone ||
                                                '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.alternate_phone"
                                            class="h-11 rounded-xl border-slate-200 bg-white font-bold"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Employment Tab -->
                    <div
                        v-if="activeTab === 'employment'"
                        class="animate-in duration-500 fade-in slide-in-from-right-4"
                    >
                        <Card
                            class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <Briefcase class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Career Record</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Institutional placement and
                                            contract terms.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
                                >
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Department</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.department
                                                    ?.name || 'Unassigned'
                                            }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.department_id"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option
                                                v-for="dept in departments"
                                                :key="dept.id"
                                                :value="dept.id"
                                            >
                                                {{ dept.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Category</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.staff_category
                                                    ?.name || '—'
                                            }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.staff_category_id"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option
                                                v-for="cat in categories"
                                                :key="cat.id"
                                                :value="cat.id"
                                            >
                                                {{ cat.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Designation</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.staff_designation
                                                    ?.name || '—'
                                            }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.staff_designation_id"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold ring-offset-background focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option
                                                v-for="desig in designations"
                                                :key="desig.id"
                                                :value="desig.id"
                                            >
                                                {{ desig.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Contract Type</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.contract_type ||
                                                '—'
                                            }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.contract_type"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold"
                                        >
                                            <option value="Permanent">
                                                Permanent
                                            </option>
                                            <option value="Contract">
                                                Contract
                                            </option>
                                            <option value="Probation">
                                                Probation
                                            </option>
                                            <option value="Internship">
                                                Internship
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Employment Type</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.employment_type ||
                                                '—'
                                            }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.employment_type"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold"
                                        >
                                            <option value="Full-time">
                                                Full-time
                                            </option>
                                            <option value="Part-time">
                                                Part-time
                                            </option>
                                            <option value="Consultant">
                                                Consultant
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Date Joined</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.date_joined || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.date_joined"
                                            type="date"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Academic/Assignments Tab -->
                    <div
                        v-if="activeTab === 'academic'"
                        class="animate-in duration-500 fade-in slide-in-from-right-4"
                    >
                        <Card
                            class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <BookOpen class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Active Assignments</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Class leadership and subject
                                            allocations.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2"
                                >
                                    <div class="space-y-4">
                                        <h3
                                            class="flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            <Building2 class="h-3.5 w-3.5" />
                                            Class Teacher For
                                        </h3>
                                        <div
                                            v-if="
                                                teacher.classes_as_teacher
                                                    .length
                                            "
                                            class="space-y-3"
                                        >
                                            <div
                                                v-for="cls in teacher.classes_as_teacher"
                                                :key="cls.id"
                                                class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-zinc-800 dark:bg-zinc-900"
                                            >
                                                <div>
                                                    <p
                                                        class="font-bold text-slate-900 dark:text-white"
                                                    >
                                                        {{ cls.name }}
                                                    </p>
                                                    <p
                                                        class="text-xs font-bold text-blue-600 uppercase"
                                                    >
                                                        {{
                                                            cls.grade_level.name
                                                        }}
                                                    </p>
                                                </div>
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="h-10 w-10 rounded-xl text-slate-400 hover:text-blue-600"
                                                    as-child
                                                >
                                                    <Link
                                                        :href="`/classes/${cls.id}`"
                                                        ><Eye class="h-4 w-4"
                                                    /></Link>
                                                </Button>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-32 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 text-slate-400"
                                        >
                                            <Users
                                                class="mb-2 h-8 w-8 opacity-20"
                                            />
                                            <p class="text-xs font-medium">
                                                No Class Assigned
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <h3
                                            class="flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            <BookOpen class="h-3.5 w-3.5" />
                                            Subject Allocations
                                        </h3>
                                        <div
                                            v-if="
                                                teacher.subject_assignments
                                                    .length
                                            "
                                            class="grid gap-3"
                                        >
                                            <div
                                                v-for="assignment in teacher.subject_assignments"
                                                :key="assignment.id"
                                                class="group flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
                                            >
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <div
                                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 font-bold text-blue-600"
                                                    >
                                                        {{
                                                            assignment.subject
                                                                .name[0]
                                                        }}
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="font-bold text-slate-900 dark:text-white"
                                                        >
                                                            {{
                                                                assignment
                                                                    .subject
                                                                    .name
                                                            }}
                                                        </p>
                                                        <p
                                                            class="text-xs font-bold text-slate-400 uppercase"
                                                        >
                                                            {{
                                                                assignment
                                                                    .school_class
                                                                    .name
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <Badge
                                                    v-if="
                                                        assignment.is_primary_teacher
                                                    "
                                                    class="border-none bg-indigo-50 text-xs font-bold text-indigo-600"
                                                    >HEAD</Badge
                                                >
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-32 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 text-slate-400"
                                        >
                                            <BookOpen
                                                class="mb-2 h-8 w-8 opacity-20"
                                            />
                                            <p class="text-xs font-medium">
                                                No Subjects Allocated
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Location Tab -->
                    <div
                        v-if="activeTab === 'location'"
                        class="animate-in duration-500 fade-in slide-in-from-right-4"
                    >
                        <Card
                            class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <MapPin class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Residential Details</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Current address and geographical
                                            records.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
                                >
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >County</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.county || '—' }}
                                        </div>
                                        <select
                                            v-else
                                            v-model="form.county"
                                            class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold"
                                        >
                                            <option value="">
                                                Select County
                                            </option>
                                            <option
                                                v-for="county in counties"
                                                :key="county"
                                                :value="county"
                                            >
                                                {{ county }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Sub County</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.sub_county || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.sub_county"
                                            class="h-11 rounded-xl border-slate-200 bg-white font-bold"
                                        />
                                    </div>

                                    <div
                                        class="col-span-1 space-y-2.5 md:col-span-2"
                                    >
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Detailed Address</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="min-h-[6rem] rounded-xl border border-slate-100 bg-slate-50/50 p-4 font-medium text-slate-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-slate-400"
                                        >
                                            {{
                                                props.teacher.address ||
                                                'No specific address details provided.'
                                            }}
                                        </div>
                                        <textarea
                                            v-else
                                            v-model="form.address"
                                            class="min-h-[6rem] w-full rounded-xl border border-slate-200 bg-white p-4 text-sm font-bold"
                                        ></textarea>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Emergency Contacts -->
                        <Card
                            class="mt-8 overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-rose-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <AlertCircle class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Crisis Point</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Who to call in case of
                                            emergency.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-3"
                                >
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Guardian Name</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher
                                                    .emergency_contact_name ||
                                                '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="
                                                form.emergency_contact_name
                                            "
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Relationship</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher
                                                    .emergency_contact_relationship ||
                                                '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="
                                                form.emergency_contact_relationship
                                            "
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Urgent Phone</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold tracking-tighter text-rose-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-rose-400"
                                        >
                                            {{
                                                props.teacher
                                                    .emergency_contact_phone ||
                                                '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="
                                                form.emergency_contact_phone
                                            "
                                            class="h-11 rounded-xl border-slate-200 bg-white font-bold"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Financial Tab -->
                    <div
                        v-if="activeTab === 'financial'"
                        class="animate-in duration-500 fade-in slide-in-from-right-4"
                    >
                        <Card
                            class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <Globe class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Banking & Payroll</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >Salary disbursement and statutory
                                            records.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
                                >
                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Bank Institution</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{ props.teacher.bank_name || '—' }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.bank_name"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Branch Name</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher.bank_branch || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.bank_branch"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Account Number</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold tracking-tighter text-slate-900 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-white"
                                        >
                                            {{
                                                props.teacher
                                                    .bank_account_number || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.bank_account_number"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Basic Salary</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-emerald-100 bg-emerald-50 px-4 font-bold text-emerald-600 dark:border-emerald-800 dark:bg-emerald-900/20"
                                        >
                                            KES
                                            {{
                                                props.teacher.basic_salary
                                                    ? Number(
                                                          props.teacher
                                                              .basic_salary,
                                                      ).toLocaleString()
                                                    : '0.00'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.basic_salary"
                                            type="number"
                                            class="h-11 rounded-xl border-slate-200 bg-white font-bold"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >KRA PIN</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold dark:border-zinc-800 dark:bg-zinc-900/50"
                                        >
                                            {{ props.teacher.kra_pin || '—' }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.kra_pin"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >NHIF NUMBER</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold dark:border-zinc-800 dark:bg-zinc-900/50"
                                        >
                                            {{
                                                props.teacher.nhif_number || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.nhif_number"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>

                                    <div class="space-y-2.5">
                                        <Label
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >NSSF NUMBER</Label
                                        >
                                        <div
                                            v-if="!isEditing"
                                            class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold dark:border-zinc-800 dark:bg-zinc-900/50"
                                        >
                                            {{
                                                props.teacher.nssf_number || '—'
                                            }}
                                        </div>
                                        <Input
                                            v-else
                                            v-model="form.nssf_number"
                                            class="h-11 rounded-xl border-slate-200 bg-white"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Security Tab -->
                    <div
                        v-if="activeTab === 'security'"
                        class="animate-in duration-500 fade-in slide-in-from-right-4"
                    >
                        <Card
                            class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-rose-600 shadow-sm dark:bg-zinc-800"
                                    >
                                        <ShieldPlus class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold tracking-tight text-slate-900 uppercase dark:text-white"
                                            >Vault & Access</CardTitle
                                        >
                                        <CardDescription
                                            class="font-medium tracking-tight text-slate-500"
                                            >System permissions and security
                                            settings.</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-8">
                                <div
                                    class="grid grid-cols-1 gap-8 md:grid-cols-2"
                                >
                                    <div class="space-y-6">
                                        <p
                                            class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Security overrides are restricted to
                                            administrators.
                                        </p>
                                        <div class="space-y-4">
                                            <div class="space-y-2.5">
                                                <Label
                                                    class="pl-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                                    >Login Status</Label
                                                >
                                                <div
                                                    v-if="!isEditing"
                                                    class="flex h-11 items-center rounded-xl border border-slate-100 bg-slate-50/50 px-4 font-bold capitalize dark:border-zinc-800 dark:bg-zinc-900/50"
                                                >
                                                    {{ props.teacher.status }}
                                                </div>
                                                <select
                                                    v-else
                                                    v-model="form.status"
                                                    class="flex h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold"
                                                >
                                                    <option value="active">
                                                        Active
                                                    </option>
                                                    <option value="inactive">
                                                        Inactive
                                                    </option>
                                                    <option value="on_leave">
                                                        On Leave
                                                    </option>
                                                    <option value="suspended">
                                                        Suspended
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-col items-center justify-center rounded-xl border border-slate-100 bg-slate-50 p-6 text-center dark:border-zinc-800 dark:bg-zinc-900"
                                    >
                                        <ShieldCheck
                                            class="mb-4 h-12 w-12 text-slate-300"
                                        />
                                        <p
                                            class="text-xs leading-relaxed font-bold tracking-tight text-slate-500 uppercase"
                                        >
                                            System logs for this user are
                                            available in the audit dashboard.
                                        </p>
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
