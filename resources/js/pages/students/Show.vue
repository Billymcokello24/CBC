<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    GraduationCap,
    MapPin,
    Phone,
    ShieldPlus,
    UserRound,
    HeartPulse,
    ShieldCheck,
    Truck,
    Users,
    Camera,
    Save,
    Loader2,
    Edit2,
    Mail,
    CreditCard,
    ExternalLink,
    Activity,
    TrendingUp,
    UserPlus,
    Database,
    Zap,
    ChevronRight,
    Search,
    FileText,
    Settings,
    CheckCircle2,
    Clock,
    AlertCircle,
    Trash2,
    UserMinus,
    UserCheck,
    Fingerprint,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learner: any;
    grades: any[];
    classes: any[];
    counties: string[];
    religions: string[];
    languages: string[];
}>();

const { props: pageProps } = usePage<any>();
const permissions = computed(() => pageProps.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: props.learner.name, href: `/students/${props.learner.id}` },
];

const activeTab = ref('overview');
const isEditing = ref(false);

const form = useForm({
    first_name: props.learner.first_name,
    middle_name: props.learner.middle_name ?? '',
    last_name: props.learner.last_name,
    admission_number: props.learner.admission_number ?? '',
    upi: props.learner.upi ?? '',
    gender: props.learner.gender,
    date_of_birth: props.learner.date_of_birth ?? '',
    birth_certificate_number: props.learner.birth_certificate_number ?? '',
    nationality: props.learner.nationality ?? 'Kenyan',
    religion: props.learner.religion ?? '',
    primary_language: props.learner.primary_language ?? 'English',
    secondary_language: props.learner.secondary_language ?? 'Kiswahili',
    admission_date: props.learner.admission_date ?? '',
    admission_class_id: props.learner.admission_class_id
        ? String(props.learner.admission_class_id)
        : '',
    current_class_id: props.learner.current_class_id
        ? String(props.learner.current_class_id)
        : '',
    grade_id: props.learner.grade_id ? String(props.learner.grade_id) : '',
    status: props.learner.status,
    home_address: props.learner.home_address ?? '',
    county: props.learner.county ?? '',
    sub_county: props.learner.sub_county ?? '',
    ward: props.learner.ward ?? '',
    blood_group: props.learner.blood_group ?? '',
    medical_conditions: props.learner.medical_conditions ?? '',
    allergies: props.learner.allergies ?? '',
    has_special_needs: props.learner.has_special_needs ?? false,
    special_needs_details: props.learner.special_needs_details ?? '',
    boarding_status: props.learner.boarding_status ?? 'day',
    hostel_room: props.learner.hostel_room ?? '',
    transport_route: props.learner.transport_route ?? '',
    pickup_point: props.learner.pickup_point ?? '',
    graduation_date: props.learner.graduation_date ?? '',
    withdrawal_date: props.learner.withdrawal_date ?? '',
    withdrawal_reason: props.learner.withdrawal_reason ?? '',
    photo: null as any,

    // Guardian details
    guardian_name: props.learner.guardians?.[0]?.name ?? '',
    guardian_email: props.learner.guardians?.[0]?.email ?? '',
    guardian_phone: props.learner.guardians?.[0]?.phone ?? '',

    _method: 'PUT',
});

const submit = () => {
    form.post(`/students/${props.learner.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const filteredClasses = computed(() => {
    if (!form.grade_id) return [];
    return props.classes.filter(
        (c) => String(c.grade_level_id) === String(form.grade_id),
    );
});

watch(
    () => form.grade_id,
    () => {
        if (
            !filteredClasses.value.some(
                (c) => String(c.id) === String(form.current_class_id),
            )
        ) {
            form.current_class_id = '';
        }
    },
);

const tabs = [
    { id: 'overview', name: 'Overview', icon: Activity, color: 'text-primary' },
    { id: 'personal', name: 'Personal', icon: UserRound, color: 'text-blue-500' },
    { id: 'academic', name: 'Academic', icon: GraduationCap, color: 'text-emerald-500' },
    { id: 'health', name: 'Health', icon: HeartPulse, color: 'text-rose-500' },
    { id: 'location', name: 'Location', icon: MapPin, color: 'text-amber-500' },
    { id: 'logistics', name: 'Logistics', icon: Truck, color: 'text-indigo-500' },
    { id: 'family', name: 'Family', icon: Users, color: 'text-purple-500' },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white shadow-sm';
        case 'suspended':
            return 'bg-amber-500 text-white shadow-sm';
        case 'inactive':
        case 'withdrawn':
            return 'bg-rose-500 text-white shadow-sm';
        case 'graduated':
            return 'bg-blue-600 text-white shadow-sm';
        default:
            return 'bg-slate-400 text-white shadow-sm';
    }
};

const showWithdrawDialog = ref(false);
const withdrawForm = useForm({
    withdrawal_date: new Date().toISOString().split('T')[0],
    withdrawal_reason: '',
});

const handleWithdraw = () => {
    withdrawForm.patch(`/students/${props.learner.id}/withdraw`, {
        onSuccess: () => {
            showWithdrawDialog.value = false;
            withdrawForm.reset();
        },
    });
};

const handleStatusChange = (action: 'suspend' | 'activate') => {
    router.patch(`/students/${props.learner.id}/${action}`, {}, {
        preserveScroll: true,
    });
};

const handleDelete = () => {
    if (confirm('Are you absolutely sure you want to delete this student record? This action cannot be undone.')) {
        router.delete(`/students/${props.learner.id}`);
    }
};
</script>

<template>
    <Head :title="learner.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div
                    class="flex flex-col items-center gap-6 text-center md:flex-row md:text-left"
                >
                    <div class="flex w-full items-center gap-4 md:w-auto">
                        <Button
                            variant="ghost"
                            size="icon"
                            as-child
                            class="h-10 w-10 shrink-0 rounded-xl border border-border bg-card shadow-sm transition-all hover:bg-muted sm:h-12 sm:w-12 sm:rounded-2xl"
                        >
                            <Link href="/students">
                                <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                            </Link>
                        </Button>
                        <h1
                            class="truncate text-xl font-bold tracking-tight text-foreground uppercase md:hidden"
                        >
                            {{ learner.name }}
                        </h1>
                    </div>

                    <div
                        class="flex w-full flex-col items-center gap-6 md:flex-row md:gap-8"
                    >
                        <!-- Profile Hub -->
                        <div class="group relative">
                            <ProfilePhotoUpload
                                :upload-url="`/students/${learner.id}/photo`"
                                @uploaded="() => router.reload()"
                            >
                                <template #default="{ isUploading }">
                                    <div
                                        class="relative h-28 w-28 overflow-hidden rounded-3xl border-4 border-card bg-muted shadow-2xl ring-1 ring-border transition-transform duration-500 group-hover:scale-[1.05] md:h-32 md:w-32"
                                    >
                                        <img
                                            v-if="learner.photo_url"
                                            :src="learner.photo_url"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary to-primary/60 text-3xl font-bold text-white"
                                        >
                                            {{ learner.name?.charAt(0).toUpperCase() || 'S' }}
                                        </div>
                                        <div
                                            class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/40 transition-opacity"
                                            :class="isUploading ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                        >
                                            <Loader2 v-if="isUploading" class="h-6 w-6 animate-spin text-white" />
                                            <Camera v-else class="h-6 w-6 text-white" />
                                        </div>
                                    </div>
                                </template>
                            </ProfilePhotoUpload>
                        </div>
                        <div>
                            <div class="hidden items-center gap-3 md:flex">
                                <div v-if="isEditing" class="flex items-center gap-2 overflow-x-auto pb-1 max-w-md">
                                    <Input v-model="form.first_name" class="h-10 min-w-[120px] text-lg font-bold rounded-lg border-primary/20" placeholder="First Name" />
                                    <Input v-model="form.middle_name" class="h-10 min-w-[120px] text-lg font-bold rounded-lg border-primary/20" placeholder="Middle" />
                                    <Input v-model="form.last_name" class="h-10 min-w-[120px] text-lg font-bold rounded-lg border-primary/20" placeholder="Last Name" />
                                </div>
                                <h1
                                    v-else
                                    class="text-3xl font-bold tracking-tight text-foreground"
                                >
                                    {{ learner.name }}
                                </h1>
                                <Badge
                                    v-if="!isEditing"
                                    :class="getStatusColor(learner.status)"
                                    class="rounded-lg border-0 px-3 py-1 text-[10px] font-bold tracking-tight uppercase"
                                >
                                    {{ learner.status }}
                                </Badge>
                            </div>
                            <div v-if="isEditing" class="leading-tight md:hidden mb-2">
                                <Input v-model="form.first_name" class="h-9 mb-1 text-sm font-bold rounded-lg" placeholder="First Name" />
                                <div class="flex gap-1">
                                    <Input v-model="form.middle_name" class="h-9 text-xs" placeholder="Middle" />
                                    <Input v-model="form.last_name" class="h-9 text-xs" placeholder="Last Name" />
                                </div>
                            </div>
                            <h2
                                v-else
                                class="text-2xl leading-tight font-bold tracking-tight text-foreground uppercase md:hidden"
                            >
                                {{ learner.name }}
                            </h2>

                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60 md:justify-start"
                            >
                                <div
                                    class="flex items-center gap-2 rounded-lg bg-primary/5 px-2.5 py-1 text-primary"
                                >
                                    <GraduationCap class="h-3 w-3" />
                                    {{ learner.class_name || 'UNASSIGNED' }}
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-lg bg-muted/50 px-2.5 py-1"
                                >
                                    <Activity class="h-3 w-3" />
                                    ID: {{ learner.admission_number || '--' }}
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-lg bg-muted/50 px-2.5 py-1"
                                >
                                    <Calendar class="h-3 w-3" />
                                    {{ learner.age }} YRS
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex w-full flex-wrap items-center justify-center gap-3 md:w-auto md:justify-end"
                >
                    <Button
                        v-if="isEditing"
                        @click="submit"
                        :disabled="form.processing"
                        class="h-10 w-full rounded-lg bg-primary px-8 text-xs font-semibold text-white transition-all sm:w-auto"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Changes
                    </Button>

                    <template v-else>
                        <Button
                            as-child
                            variant="outline"
                            class="h-10 flex-1 rounded-lg border-primary/20 bg-primary/5 px-4 text-xs font-semibold text-primary transition-all hover:bg-primary hover:text-white sm:flex-none"
                            v-if="hasPermission('students.update')"
                        >
                            <Link :href="`/students/enrollments/create?student_id=${learner.id}`">
                                <UserPlus class="mr-2 h-4 w-4" />
                                Enroll
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            class="h-10 flex-1 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted sm:flex-none"
                            @click="isEditing = true"
                            v-if="hasPermission('students.update')"
                        >
                            <Edit2 class="mr-2 h-4 w-4 text-primary" />
                            Edit Profile
                        </Button>
                        <Button
                            v-if="hasPermission('finance.view')"
                            variant="outline"
                            class="h-10 w-10 shrink-0 rounded-lg border-border bg-card p-0 text-xs font-semibold hover:bg-muted sm:w-auto sm:px-4"
                            as-child
                            title="Finance"
                        >
                            <Link :href="`/students/fees/${learner.id}`">
                                <CreditCard class="h-4 w-4 text-emerald-500 sm:mr-2" />
                                <span class="hidden sm:inline">Finance</span>
                            </Link>
                        </Button>

                        <div class="flex items-center gap-2" v-if="hasPermission('students.update') && !isEditing">
                            <Button
                                v-if="learner.status === 'active'"
                                variant="outline"
                                @click="handleStatusChange('suspend')"
                                class="h-10 w-10 shrink-0 rounded-lg border-amber-200 bg-amber-50 p-0 text-amber-600 hover:bg-amber-100 sm:w-auto sm:px-4"
                                title="Suspend"
                            >
                                <UserMinus class="h-4 w-4 sm:mr-2" />
                                <span class="hidden sm:inline text-[10px]">Suspend</span>
                            </Button>
                            <Button
                                v-if="learner.status === 'suspended'"
                                variant="outline"
                                @click="handleStatusChange('activate')"
                                class="h-10 w-10 shrink-0 rounded-lg border-emerald-200 bg-emerald-50 p-0 text-emerald-600 hover:bg-emerald-100 sm:w-auto sm:px-4"
                                title="Activate"
                            >
                                <UserCheck class="h-4 w-4 sm:mr-2" />
                                <span class="hidden sm:inline text-[10px]">Activate</span>
                            </Button>
                            <Button
                                v-if="learner.status !== 'withdrawn' && learner.status !== 'graduated'"
                                variant="outline"
                                @click="showWithdrawDialog = true"
                                class="h-10 w-10 shrink-0 rounded-lg border-rose-200 bg-rose-50 p-0 text-rose-600 hover:bg-rose-100 sm:w-auto sm:px-4"
                                title="Withdraw"
                            >
                                <AlertCircle class="h-4 w-4 sm:mr-2" />
                                <span class="hidden sm:inline text-[10px]">Withdraw</span>
                            </Button>
                            <Button
                                v-if="hasPermission('students.delete')"
                                variant="outline"
                                @click="handleDelete"
                                class="h-10 w-10 shrink-0 rounded-lg border-slate-200 bg-slate-50 p-0 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 sm:w-auto sm:px-4"
                                title="Delete Registry"
                            >
                                <Trash2 class="h-4 w-4 sm:mr-2" />
                                <span class="hidden sm:inline text-[10px]">Delete</span>
                            </Button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Profile Intelligence Grid -->
            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Navigation Column -->
                <div class="space-y-8 lg:col-span-3">
                    <nav
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="border-b border-border/50 bg-muted/10 px-6 py-4"
                        >
                            <p
                                class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Menu
                            </p>
                        </div>
                        <div class="p-2">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="group relative flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left transition-all duration-300"
                                :class="
                                    activeTab === tab.id
                                        ? 'bg-primary text-white shadow-lg shadow-primary/20'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                "
                            >
                                <component
                                    :is="tab.icon"
                                    class="h-4 w-4 transition-transform group-hover:scale-110"
                                    :class="activeTab === tab.id ? 'text-white' : tab.color"
                                />
                                <span class="text-xs font-bold uppercase">
                                    {{ tab.name }}
                                </span>
                                <ChevronRight
                                    v-if="activeTab === tab.id"
                                    class="ml-auto h-3 w-3 opacity-50"
                                />
                            </button>
                        </div>
                    </nav>

                    <!-- Profile Stability Card -->
                    <div
                        class="group relative overflow-hidden rounded-xl border border-border bg-slate-900 p-8 text-white shadow-xl shadow-slate-200"
                    >
                        <div
                            class="absolute -right-6 -bottom-6 rotate-12 text-white opacity-[0.03] transition-transform duration-700 group-hover:scale-110"
                        >
                            <TrendingUp class="h-24 w-24" />
                        </div>
                        <h4
                            class="mb-4 text-[10px] font-bold tracking-widest text-primary uppercase"
                        >
                            Performance
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Attendance</span>
                                    <span class="text-xs font-bold tabular-nums">{{ learner.attendance_percentage }}%</span>
                                </div>
                                <div class="h-1 w-full overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-primary" :style="{ width: `${learner.attendance_percentage}%` }"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Rating</span>
                                <Badge class="rounded-lg border-primary/20 bg-primary/20 px-2 py-0.5 text-[9px] font-bold text-primary uppercase">
                                    {{ learner.performance_rating }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display Column -->
                <div class="lg:col-span-9">
                    <div
                        v-if="activeTab === 'overview'"
                        class="animate-in space-y-8 duration-700 fade-in slide-in-from-right-4"
                    >
                        <!-- Top Matrix: Vital Data -->
                        <div
                            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                        >
                            <div
                                class="flex flex-col justify-between gap-4 border-b border-border/50 bg-muted/10 px-8 py-6 sm:flex-row sm:items-center"
                            >
                                <div class="space-y-1">
                                    <h3
                                        class="flex items-center gap-2.5 text-sm font-bold tracking-tight text-foreground uppercase"
                                    >
                                        <Activity class="h-5 w-5 text-primary" />
                                        Student Overview
                                    </h3>
                                    <p
                                        class="text-[10px] font-bold tracking-tight text-muted-foreground uppercase opacity-50"
                                    >
                                        General Information
                                    </p>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-3">
                                    <!-- Identification Node -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <Fingerprint class="h-4 w-4" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-60">Admission No</span>
                                        </div>
                                        <div class="space-y-1">
                                            <Input v-if="isEditing" v-model="form.admission_number" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold tracking-tight text-foreground uppercase">
                                                {{ learner.admission_number || '--' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Placement Node -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <GraduationCap class="h-4 w-4" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-60">Grade & Class</span>
                                        </div>
                                        <div class="space-y-1" v-if="isEditing">
                                            <Select v-model="form.grade_id">
                                                <SelectTrigger class="h-9 rounded-lg">
                                                    <SelectValue placeholder="Grade" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="grade in grades" :key="grade.id" :value="String(grade.id)">
                                                        {{ grade.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <Select v-model="form.current_class_id" class="mt-2" v-if="form.grade_id">
                                                <SelectTrigger class="h-9 rounded-lg">
                                                    <SelectValue placeholder="Class" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="cls in filteredClasses" :key="cls.id" :value="String(cls.id)">
                                                        {{ cls.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-1" v-else>
                                            <p class="text-xs font-bold tracking-tight text-foreground uppercase">
                                                {{ learner.grade_name || 'UNASSIGNED' }} / {{ learner.class_name || 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Status Node -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <Activity class="h-4 w-4" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-60">Status</span>
                                        </div>
                                        <div class="space-y-1">
                                            <Select v-if="isEditing" v-model="form.status">
                                                <SelectTrigger class="h-9 rounded-lg">
                                                    <SelectValue placeholder="Status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="active">Active</SelectItem>
                                                    <SelectItem value="suspended">Suspended</SelectItem>
                                                    <SelectItem value="withdrawn">Withdrawn</SelectItem>
                                                    <SelectItem value="graduated">Graduated</SelectItem>
                                                    <SelectItem value="inactive">Inactive</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <Badge v-else :class="getStatusColor(learner.status)" class="h-6 rounded-lg px-2 text-[9px] font-bold uppercase transition-all">
                                                {{ learner.status }}
                                            </Badge>
                                        </div>
                                    </div>

                                     <!-- Temporal Node -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <Clock class="h-4 w-4" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-60">Birth Date / Age</span>
                                        </div>
                                        <div class="space-y-1">
                                            <div v-if="isEditing" class="space-y-1">
                                                <Input type="date" v-model="form.date_of_birth" class="h-9 rounded-lg" />
                                                <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">Correction Protocol</p>
                                            </div>
                                            <template v-else>
                                                <p class="text-xs font-bold tracking-tight text-foreground">
                                                    {{ learner.age }} Years
                                                </p>
                                                <p class="text-[10px] font-medium text-muted-foreground">Born: {{ learner.date_of_birth }}</p>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Language Node -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <Zap class="h-4 w-4" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-60">Protocol</span>
                                        </div>
                                        <div class="space-y-1">
                                            <Input v-if="isEditing" v-model="form.primary_language" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold tracking-tight text-foreground uppercase">
                                                {{ learner.primary_language || 'ENGLISH' }}
                                            </p>
                                            <p class="text-[10px] font-medium text-muted-foreground">Primary Communication</p>
                                        </div>
                                    </div>

                                    <!-- UPI Node -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <Database class="h-4 w-4" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-60">Global UPI</span>
                                        </div>
                                        <div class="space-y-1">
                                            <Input v-if="isEditing" v-model="form.upi" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold tracking-tight text-primary">
                                                {{ learner.upi || 'NOT_FOUND' }}
                                            </p>
                                            <p class="text-[10px] font-medium text-muted-foreground">Institutional Identifier</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pulse Charts: Aesthetic Metrics -->
                        <div class="grid gap-8 md:grid-cols-2">
                             <div
                                class="group relative overflow-hidden rounded-xl border border-border bg-card p-8 shadow-sm transition-all hover:border-primary/20"
                            >
                                <div class="mb-8 flex items-center justify-between">
                                    <h4 class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Engagement Matrix</h4>
                                    <Badge variant="secondary" class="bg-primary/5 text-primary border-none text-[9px] font-bold uppercase tracking-tight">Active</Badge>
                                </div>
                                <div class="flex h-32 items-end gap-1.5 px-2">
                                    <div v-for="i in 12" :key="i" 
                                        class="flex-1 rounded-t-lg bg-primary/20 transition-all duration-500 hover:bg-primary active:scale-95"
                                        :style="{ height: (30 + Math.random() * 70) + '%' }">
                                    </div>
                                </div>
                                <div class="mt-6 flex items-center justify-between border-t border-border/50 pt-6">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-bold tracking-tight text-foreground uppercase">Presence Index</p>
                                        <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">98% Continuity</p>
                                    </div>
                                    <Activity class="h-5 w-5 text-emerald-500" />
                                </div>
                            </div>

                            <div
                                class="group relative overflow-hidden rounded-3xl border border-border bg-card p-8 shadow-sm transition-all hover:border-primary/20"
                            >
                                <div class="mb-8 flex items-center justify-between">
                                    <h4 class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Academic Altitude</h4>
                                    <Badge variant="secondary" class="bg-indigo-50 text-indigo-600 border-none text-[9px] font-bold uppercase tracking-tight">Ascending</Badge>
                                </div>
                                <div class="relative h-32 w-full overflow-hidden rounded-2xl bg-muted/30">
                                    <!-- Decorative SVG Wave -->
                                    <svg viewBox="0 0 400 100" class="absolute bottom-0 h-full w-full opacity-30">
                                        <path d="M0 80 Q 100 20 200 80 T 400 50 L 400 100 L 0 100 Z" fill="currentColor" class="text-primary" />
                                    </svg>
                                    <div class="absolute inset-x-8 top-8 flex flex-col items-center justify-center">
                                        <span class="text-4xl font-black tracking-tight text-foreground">{{ learner.performance_value }}</span>
                                        <span class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Avg Rating Intensity</span>
                                    </div>
                                </div>
                                <div class="mt-6 flex items-center justify-between border-t border-border/50 pt-6">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-bold tracking-tight text-foreground uppercase">Growth Velocity</p>
                                        <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">+12% Iteration</p>
                                    </div>
                                    <TrendingUp class="h-5 w-5 text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Sub-Tab Rendering -->
                    <div v-else class="animate-in space-y-8 duration-700 fade-in slide-in-from-right-4">
                        <div
                            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                        >
                            <div
                                class="flex flex-col justify-between gap-4 border-b border-border/50 bg-muted/10 px-8 py-6 sm:flex-row sm:items-center"
                            >
                                <div class="space-y-1">
                                    <h3
                                        class="flex items-center gap-2.5 text-sm font-bold tracking-tight text-foreground uppercase"
                                    >
                                        <component :is="tabs.find(t => t.id === activeTab)?.icon" class="h-5 w-5 text-primary" />
                                        {{ tabs.find(t => t.id === activeTab)?.name }}
                                    </h3>
                                    <p
                                        class="text-[10px] font-bold tracking-tight text-muted-foreground uppercase opacity-50"
                                    >
                                        Detailed Information
                                    </p>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-3">
                                    <!-- Render relevant fields based on activeTab -->
                                    <template v-if="activeTab === 'personal'">
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Legal Gender</p>
                                            <Select v-if="isEditing" v-model="form.gender">
                                                <SelectTrigger class="h-9 rounded-lg">
                                                    <SelectValue placeholder="Gender" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="male">Male</SelectItem>
                                                    <SelectItem value="female">Female</SelectItem>
                                                    <SelectItem value="other">Other</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.gender || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Temporal Node (DOB)</p>
                                            <Input v-if="isEditing" type="date" v-model="form.date_of_birth" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.date_of_birth || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Global UPI</p>
                                            <Input v-if="isEditing" v-model="form.upi" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-primary">{{ learner.upi || 'NOT SET' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Birth Certificate</p>
                                            <Input v-if="isEditing" v-model="form.birth_certificate_number" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground">{{ learner.birth_certificate_number || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Nationality</p>
                                            <Input v-if="isEditing" v-model="form.nationality" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.nationality || 'KENYAN' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Religion</p>
                                            <Input v-if="isEditing" v-model="form.religion" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.religion || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Primary Language</p>
                                            <Input v-if="isEditing" v-model="form.primary_language" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.primary_language || 'ENGLISH' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Secondary Language</p>
                                            <Input v-if="isEditing" v-model="form.secondary_language" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.secondary_language || '--' }}</p>
                                        </div>
                                    </template>

                                    <template v-if="activeTab === 'academic'">
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Admission Registry Node</p>
                                            <div v-if="isEditing" class="space-y-2">
                                                <Select v-model="form.grade_id">
                                                    <SelectTrigger class="h-9 rounded-lg">
                                                        <SelectValue placeholder="Grade" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="grade in grades" :key="grade.id" :value="String(grade.id)">
                                                            {{ grade.name }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <Select v-model="form.current_class_id" v-if="form.grade_id">
                                                    <SelectTrigger class="h-9 rounded-lg">
                                                        <SelectValue placeholder="Class" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="cls in filteredClasses" :key="cls.id" :value="String(cls.id)">
                                                            {{ cls.name }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <p v-else class="text-xs font-bold text-foreground uppercase">
                                                {{ learner.grade_name || 'UNASSIGNED' }} / {{ learner.class_name || 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Admission Date</p>
                                            <Input v-if="isEditing" type="date" v-model="form.admission_date" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground">{{ learner.admission_date || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Graduation Predicted</p>
                                            <Input v-if="isEditing" type="date" v-model="form.graduation_date" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground">{{ learner.graduation_date || 'TBD' }}</p>
                                        </div>

                                        <!-- Enrollment History Matrix -->
                                        <div class="lg:col-span-3 pt-6">
                                            <div class="flex items-center gap-3 mb-6">
                                                <div class="h-1.5 w-8 rounded-full bg-primary"></div>
                                                <h4 class="text-[10px] font-bold tracking-widest text-foreground uppercase">Enrollment History</h4>
                                            </div>
                                            
                                            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                                                <table class="w-full text-left">
                                                    <thead>
                                                        <tr class="border-b border-border bg-muted/20">
                                                            <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Academic Context</th>
                                                            <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Grade & Class</th>
                                                            <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Date</th>
                                                            <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-border/50">
                                                        <tr v-for="enrollment in learner.enrollments" :key="enrollment.id" class="hover:bg-muted/10 transition-colors">
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs font-bold text-foreground">{{ enrollment.academic_year }}</p>
                                                                <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">{{ enrollment.academic_term }}</p>
                                                            </td>
                                                            <td class="px-6 py-4 text-xs font-bold text-foreground uppercase">
                                                                {{ enrollment.class_name }}
                                                            </td>
                                                            <td class="px-6 py-4 text-xs font-bold text-muted-foreground tabular-nums">
                                                                {{ enrollment.enrollment_date }}
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <Badge :class="enrollment.status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-100 text-slate-500'" 
                                                                    class="rounded-lg border-0 px-2 py-0.5 text-[9px] font-bold uppercase">
                                                                    {{ enrollment.status }}
                                                                </Badge>
                                                            </td>
                                                        </tr>
                                                        <tr v-if="!learner.enrollments?.length">
                                                            <td colspan="4" class="px-6 py-12 text-center text-[10px] font-bold text-muted-foreground uppercase opacity-50">
                                                                No prior enrollment cycles documented.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>

                                    <template v-if="activeTab === 'health'">
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Blood Matrix</p>
                                            <Input v-if="isEditing" v-model="form.blood_group" class="h-9 rounded-lg" placeholder="e.g. A+" />
                                            <p v-else class="text-xs font-bold text-rose-500">{{ learner.blood_group || '--' }}</p>
                                        </div>
                                        <div class="space-y-4 lg:col-span-2">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Medical Status</p>
                                            <Textarea v-if="isEditing" v-model="form.medical_conditions" class="min-h-[60px] rounded-lg" placeholder="List any medical conditions..." />
                                            <p v-else class="text-xs font-bold text-foreground leading-relaxed">{{ learner.medical_conditions || 'NO ANOMALIES DETECTED' }}</p>
                                        </div>
                                        <div class="space-y-4 lg:col-span-3">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Allergy Protocols</p>
                                            <Textarea v-if="isEditing" v-model="form.allergies" class="min-h-[60px] rounded-lg" placeholder="List any allergies..." />
                                            <p v-else class="text-xs font-bold text-foreground whitespace-pre-wrap">{{ learner.allergies || 'NONE REPORTED' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Special Needs</p>
                                            <Select v-if="isEditing" v-model="form.has_special_needs" @update:model-value="(val) => form.has_special_needs = val === 'true'">
                                                <SelectTrigger class="h-9 rounded-lg">
                                                    <SelectValue placeholder="Special Needs?" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="true">Yes</SelectItem>
                                                    <SelectItem value="false">No</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <Badge v-else :variant="learner.has_special_needs ? 'destructive' : 'outline'" class="h-6 rounded-lg px-2 text-[9px] font-bold uppercase transition-all">
                                                {{ learner.has_special_needs ? 'YES' : 'NO' }}
                                            </Badge>
                                        </div>
                                        <div v-if="isEditing || learner.has_special_needs" class="space-y-4 lg:col-span-2">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Special Needs Details</p>
                                            <Textarea v-if="isEditing" v-model="form.special_needs_details" class="min-h-[60px] rounded-lg" placeholder="Provide details if any..." />
                                            <p v-else class="text-xs font-bold text-foreground leading-relaxed">{{ learner.special_needs_details || 'N/A' }}</p>
                                        </div>
                                    </template>

                                    <template v-if="activeTab === 'location'">
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">County Node</p>
                                            <Input v-if="isEditing" v-model="form.county" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.county || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Sub-County Node</p>
                                            <Input v-if="isEditing" v-model="form.sub_county" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.sub_county || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Ward Identifier</p>
                                            <Input v-if="isEditing" v-model="form.ward" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.ward || '--' }}</p>
                                        </div>
                                        <div class="space-y-4 lg:col-span-2">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Habitat Coordinate (Address)</p>
                                            <Input v-if="isEditing" v-model="form.home_address" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase leading-relaxed">{{ learner.home_address || 'NOT LOGGED' }}</p>
                                        </div>
                                    </template>

                                    <template v-if="activeTab === 'logistics'">
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Boarding Config</p>
                                            <Select v-if="isEditing" v-model="form.boarding_status">
                                                <SelectTrigger class="h-9 rounded-lg">
                                                    <SelectValue placeholder="Boarding?" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="day">Day</SelectItem>
                                                    <SelectItem value="boarding">Boarding</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.boarding_status || '--' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Hostel Allocation</p>
                                            <Input v-if="isEditing" v-model="form.hostel_room" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.hostel_room || 'N/A' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Transport Protocol</p>
                                            <Input v-if="isEditing" v-model="form.transport_route" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.transport_route || 'NONE' }}</p>
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Pickup Point</p>
                                            <Input v-if="isEditing" v-model="form.pickup_point" class="h-9 rounded-lg" />
                                            <p v-else class="text-xs font-bold text-foreground uppercase">{{ learner.pickup_point || 'NONE' }}</p>
                                        </div>
                                    </template>

                                    <template v-if="activeTab === 'family'">
                                        <div v-if="isEditing" class="space-y-6 lg:col-span-3">
                                            <div class="rounded-2xl border border-border bg-card p-6 space-y-4 shadow-sm">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                        <UserRound class="h-5 w-5" />
                                                    </div>
                                                    <div>
                                                        <p class="text-[10px] font-bold tracking-widest text-foreground uppercase">Primary Guardian</p>
                                                        <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">Edit Linkage Records</p>
                                                    </div>
                                                </div>
                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div class="space-y-1.5">
                                                        <Label class="text-[9px] font-bold uppercase opacity-60">Full Name</Label>
                                                        <Input v-model="form.guardian_name" class="h-9 rounded-lg" />
                                                    </div>
                                                    <div class="space-y-1.5">
                                                        <Label class="text-[9px] font-bold uppercase opacity-60">Mobile Node</Label>
                                                        <Input v-model="form.guardian_phone" class="h-9 rounded-lg" />
                                                    </div>
                                                    <div class="space-y-1.5 sm:col-span-2">
                                                        <Label class="text-[9px] font-bold uppercase opacity-60">Postal Coordinate (Email)</Label>
                                                        <Input v-model="form.guardian_email" class="h-9 rounded-lg" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <template v-else>
                                            <div v-for="guardian in learner.guardians" :key="guardian.id" class="space-y-6 lg:col-span-3">
                                                <div class="flex items-center gap-6 rounded-2xl border border-border bg-muted/20 p-5">
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20">
                                                        <UserRound class="h-6 w-6" />
                                                    </div>
                                                    <div class="space-y-1">
                                                        <p class="text-xs font-bold tracking-tight text-foreground uppercase">{{ guardian.name }}</p>
                                                        <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-50">{{ guardian.relationship || 'GUARDIAN' }}</p>
                                                    </div>
                                                    <div class="ml-auto flex items-center gap-3">
                                                        <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl" as-child>
                                                            <a :href="`tel:${guardian.phone}`"><Phone class="h-4 w-4 text-primary" /></a>
                                                        </Button>
                                                        <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl" as-child>
                                                            <a :href="`mailto:${guardian.email}`"><Mail class="h-4 w-4 text-indigo-500" /></a>
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p v-if="!learner.guardians?.length" class="text-xs font-bold text-muted-foreground uppercase opacity-60 lg:col-span-3 text-center py-8">
                                                No family linkage detected in registry.
                                            </p>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:open="showWithdrawDialog">
            <DialogContent class="sm:max-w-[425px] rounded-3xl border-border bg-card p-6 shadow-2xl">
                <DialogHeader>
                    <DialogTitle class="text-lg font-bold tracking-tight text-foreground">Withdraw Learner</DialogTitle>
                    <DialogDescription class="text-xs font-medium text-muted-foreground">
                        Formalize the withdrawal of {{ learner.name }} from the institution.
                    </DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="handleWithdraw" class="mt-6 space-y-5">
                    <div class="space-y-2">
                        <Label for="withdrawal_date" class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase">Withdrawal Date</Label>
                        <Input 
                            id="withdrawal_date" 
                            type="date" 
                            v-model="withdrawForm.withdrawal_date"
                            class="h-11 rounded-xl border-border bg-muted/20 focus:ring-primary/20"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="withdrawal_reason" class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase">Reason for Withdrawal</Label>
                        <Textarea 
                            id="withdrawal_reason" 
                            v-model="withdrawForm.withdrawal_reason"
                            placeholder="Provide a formal reason for this withdrawal..."
                            class="min-h-[100px] rounded-xl border-border bg-muted/20 focus:ring-primary/20"
                            required
                        />
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="showWithdrawDialog = false" class="rounded-xl">Cancel</Button>
                        <Button type="submit" :disabled="withdrawForm.processing" class="rounded-xl px-8 shadow-lg shadow-primary/20">
                            <Loader2 v-if="withdrawForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Confirm Withdrawal
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
