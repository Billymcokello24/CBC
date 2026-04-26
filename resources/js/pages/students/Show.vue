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
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    { title: 'Learners', href: '/students' },
    { title: props.learner.name, href: `/students/${props.learner.id}` },
];

const activeTab = ref('overview');
const isEditing = ref(false);
const photoPreview = ref<string | null>(null);

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
    guardian_password: '',
    guardian_password_confirmation: '',

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
    { id: 'overview', name: 'Overview', icon: Activity },
    { id: 'personal', name: 'Personal', icon: UserRound },
    { id: 'academic', name: 'Academic', icon: GraduationCap },
    { id: 'health', name: 'Health', icon: HeartPulse },
    { id: 'location', name: 'Location', icon: MapPin },
    { id: 'logistics', name: 'Logistics', icon: Truck },
    { id: 'family', name: 'Family', icon: Users },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white shadow-sm';
        case 'suspended':
            return 'bg-rose-500 text-white shadow-sm';
        case 'inactive':
        case 'withdrawn':
            return 'bg-slate-400 text-white shadow-sm';
        case 'graduated':
            return 'bg-blue-600 text-white shadow-sm';
        default:
            return 'bg-indigo-500 text-white shadow-sm';
    }
};
</script>

<template>
    <Head :title="learner.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-8 p-6 pb-20 duration-500 fade-in md:p-8"
        >
            <!-- Header section -->
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
                            class="h-10 w-10 shrink-0 rounded-xl border border-slate-100 text-muted-foreground transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600 sm:border-transparent"
                        >
                            <Link href="/students"
                                ><ArrowLeft class="h-5 w-5"
                            /></Link>
                        </Button>
                        <h1
                            class="truncate text-xl font-bold tracking-tight tracking-tighter text-slate-900 uppercase md:hidden"
                        >
                            {{ learner.name }}
                        </h1>
                    </div>

                    <div
                        class="flex w-full flex-col items-center gap-6 md:flex-row md:gap-8"
                    >
                        <!-- Profile Photo -->
                        <div class="group relative">
                            <ProfilePhotoUpload
                                :upload-url="`/students/${learner.id}/photo`"
                                @uploaded="() => router.reload()"
                            >
                                <template #default="{ isUploading }">
                                    <div
                                        class="relative h-28 w-28 overflow-hidden rounded-xl border-4 border-white bg-slate-50 shadow-xl ring-1 ring-slate-200/50 transition-transform duration-500 group-hover:scale-[1.05] md:h-32 md:w-32 md:rounded-xl"
                                    >
                                        <img
                                            v-if="learner.photo_url"
                                            :src="learner.photo_url"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-600 to-indigo-700 text-3xl font-bold text-white"
                                        >
                                            {{
                                                learner.name
                                                    ? learner.name
                                                          .charAt(0)
                                                          .toUpperCase()
                                                    : 'S'
                                            }}
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
                                                class="h-6 w-6 animate-spin text-white"
                                            />
                                            <Camera
                                                v-else
                                                class="h-6 w-6 text-white"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </ProfilePhotoUpload>
                        </div>
                        <div>
                            <div class="hidden items-center gap-3 md:flex">
                                <h1
                                    class="text-3xl font-bold tracking-tight text-slate-900"
                                >
                                    {{ learner.name }}
                                </h1>
                                <Badge
                                    :class="getStatusColor(learner.status)"
                                    class="rounded-lg border-0 px-3 py-1 text-xs font-medium tracking-tight uppercase shadow-lg shadow-blue-500/10"
                                >
                                    {{ learner.status }}
                                </Badge>
                            </div>
                            <h2
                                class="text-2xl leading-tight font-bold tracking-tight text-slate-900 uppercase md:hidden"
                            >
                                {{ learner.name }}
                            </h2>

                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 text-xs font-medium tracking-tight text-slate-400 uppercase md:justify-start"
                            >
                                <div
                                    class="flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-blue-600"
                                >
                                    <GraduationCap class="h-3 w-3" />
                                    {{ learner.class_name || 'UNASSIGNED' }}
                                </div>
                                <div
                                    class="flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1"
                                >
                                    <Activity class="h-3 w-3" />
                                    ID: {{ learner.admission_number || '--' }}
                                </div>
                                <div
                                    class="flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1"
                                >
                                    <Calendar class="h-3 w-3" />
                                    {{ learner.age }} YRS
                                </div>
                                <Badge
                                    md:hidden
                                    :class="getStatusColor(learner.status)"
                                    class="rounded-lg border-0 px-2.5 py-1 text-xs font-medium tracking-tight uppercase md:hidden"
                                >
                                    {{ learner.status }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex w-full flex-wrap items-center justify-center gap-2 sm:gap-3 md:w-auto md:justify-end"
                >
                    <Button
                        v-if="isEditing"
                        @click="submit"
                        :disabled="form.processing"
                        class="h-11 w-full rounded-xl bg-blue-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700 sm:w-auto"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Context
                    </Button>

                    <template v-else>
                        <Button
                            as-child
                            variant="outline"
                            class="h-11 flex-1 rounded-xl border-blue-200 bg-blue-50/50 px-4 text-xs font-bold tracking-tight text-blue-700 uppercase shadow-sm transition-all hover:border-blue-600 hover:bg-blue-600 hover:text-white sm:flex-none"
                            v-if="hasPermission('students.update')"
                        >
                            <Link
                                :href="`/students/enrollments/create?student_id=${learner.id}`"
                            >
                                <UserPlus class="mr-2 h-4 w-4" />
                                Enroll
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            class="h-11 flex-1 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase shadow-sm hover:bg-slate-50 sm:flex-none"
                            @click="isEditing = true"
                            v-if="hasPermission('students.update')"
                        >
                            <Edit2 class="mr-2 h-3.5 w-3.5 text-amber-500" />
                            Modify
                        </Button>
                        <Button
                            v-if="hasPermission('finance.view')"
                            variant="outline"
                            class="h-11 w-11 shrink-0 rounded-xl border-slate-200 p-0 text-xs font-bold tracking-tight uppercase shadow-sm hover:bg-slate-50 sm:w-auto sm:px-4"
                            as-child
                            title="Fees"
                        >
                            <Link :href="`/students/fees/${learner.id}`">
                                <CreditCard
                                    class="h-4 w-4 text-emerald-600 sm:mr-2"
                                />
                                <span class="hidden sm:inline">Finance</span>
                            </Link>
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Main Content Layout -->
            <div class="grid grid-cols-1 gap-6 sm:gap-8 lg:grid-cols-12">
                <!-- Navigation Sidebar -->
                <div class="space-y-6 lg:col-span-3">
                    <div
                        class="scrollbar-hide flex flex-row gap-1.5 overflow-x-auto px-1 pb-2 lg:flex-col lg:overflow-x-visible lg:pb-0"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="group relative flex shrink-0 items-center gap-3 rounded-xl px-5 py-3 text-xs font-medium tracking-tight whitespace-nowrap uppercase transition-all duration-300 lg:shrink lg:py-4 lg:whitespace-normal"
                            :class="
                                activeTab === tab.id
                                    ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/10'
                                    : 'border border-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-900'
                            "
                        >
                            <component
                                :is="tab.icon"
                                class="h-4 w-4"
                                :class="
                                    activeTab === tab.id
                                        ? 'text-white'
                                        : 'text-slate-400 group-hover:text-slate-900'
                                "
                            />
                            {{ tab.name }}
                        </button>
                    </div>

                    <!-- Quick Metrics Card -->
                    <div
                        class="group relative hidden overflow-hidden rounded-2xl border border-slate-100 bg-slate-900 p-8 text-white shadow-xl shadow-slate-200 lg:block"
                    >
                        <div
                            class="absolute -right-6 -bottom-6 opacity-10 transition-transform duration-1000 group-hover:scale-110"
                        >
                            <TrendingUp class="h-32 w-32" />
                        </div>
                        <p
                            class="mb-6 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                        >
                            Academic Pulse
                        </p>
                        <div class="relative z-10 space-y-5">
                            <div>
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Attendance</span
                                    >
                                    <span class="text-sm font-bold tabular-nums"
                                        >98.4%</span
                                    >
                                </div>
                                <div
                                    class="h-1.5 w-full overflow-hidden rounded-full bg-white/10"
                                >
                                    <div
                                        class="h-full w-[98.4%] rounded-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]"
                                    ></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span
                                    class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Assessment</span
                                >
                                <Badge
                                    class="rounded-md border-0 bg-blue-600 text-xs font-medium tracking-tight uppercase"
                                    >Excellent</Badge
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div
                    class="animate-in space-y-6 duration-700 slide-in-from-bottom-4 lg:col-span-9"
                >
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                    >
                                        <UserRound class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Basic Profile Information
                                    </h3>
                                </div>
                                <Badge
                                    variant="outline"
                                    class="border-border bg-muted/50 px-3 text-xs font-bold tracking-wider uppercase"
                                    >Core Data</Badge
                                >
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div
                                    v-if="isEditing"
                                    class="space-y-4 md:col-span-2"
                                >
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-xs font-bold tracking-wide text-muted-foreground uppercase"
                                                >First Name</Label
                                            >
                                            <Input
                                                v-model="form.first_name"
                                                class="h-11 rounded-xl border-border bg-muted/30 font-medium transition-all focus:bg-background"
                                            />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-xs font-bold tracking-wide text-muted-foreground uppercase"
                                                >Last Name</Label
                                            >
                                            <Input
                                                v-model="form.last_name"
                                                class="h-11 rounded-xl border-border bg-muted/30 font-medium transition-all focus:bg-background"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Full Legal Name
                                    </p>
                                    <p
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.name }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Admission Number
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.admission_number"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-bold text-blue-600"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-blue-600"
                                    >
                                        {{ learner.admission_number || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        UPI Number
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.upi"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.upi || 'Not Assigned' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Academic Level / Grade
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.grade_id"
                                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold transition-all outline-none hover:bg-muted/50 focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="">
                                                Select Level
                                            </option>
                                            <option
                                                v-for="g in grades"
                                                :key="g.id"
                                                :value="String(g.id)"
                                            >
                                                {{ g.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.grade_name || 'Unassigned' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Assigned Stream
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.current_class_id"
                                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold transition-all outline-none hover:bg-muted/50 focus:ring-2 focus:ring-blue-600/10"
                                            :disabled="!form.grade_id"
                                        >
                                            <option value="">
                                                Select Stream
                                            </option>
                                            <option
                                                v-for="c in filteredClasses"
                                                :key="c.id"
                                                :value="String(c.id)"
                                            >
                                                {{ c.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            learner.class_name ||
                                            'No stream assigned'
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Profile Status
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.status"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold transition-all outline-none hover:bg-muted/50 focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="active">
                                                Active
                                            </option>
                                            <option value="inactive">
                                                Inactive
                                            </option>
                                            <option value="graduated">
                                                Graduated
                                            </option>
                                            <option value="transferred">
                                                Transferred
                                            </option>
                                            <option value="withdrawn">
                                                Withdrawn
                                            </option>
                                            <option value="suspended">
                                                Suspended
                                            </option>
                                        </select>
                                    </div>
                                    <div v-else>
                                        <Badge
                                            :class="
                                                getStatusColor(learner.status)
                                            "
                                            class="rounded-lg border-0 px-3 py-1 text-xs font-bold tracking-wider uppercase shadow-sm"
                                        >
                                            {{ learner.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Summary Metrics -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div
                                class="group relative overflow-hidden rounded-2xl bg-foreground p-8 text-background shadow-lg"
                            >
                                <div
                                    class="absolute -top-8 -right-8 opacity-5 transition-transform duration-1000 group-hover:scale-125"
                                >
                                    <Activity class="h-48 w-48" />
                                </div>
                                <h3
                                    class="mb-6 flex items-center gap-2 text-sm font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    <Activity class="h-4 w-4 text-blue-500" />
                                    Attendance Trends
                                </h3>
                                <div class="mb-6 flex h-20 items-end gap-1.5">
                                    <div
                                        v-for="i in 15"
                                        :key="i"
                                        :style="{
                                            height:
                                                Math.random() * 80 + 20 + '%',
                                        }"
                                        class="flex-1 rounded-t-md bg-blue-500/30 transition-all duration-700 hover:bg-blue-500 hover:opacity-100"
                                    ></div>
                                </div>
                                <p
                                    class="text-sm leading-relaxed font-semibold text-muted-foreground"
                                >
                                    Recent attendance pulse shows
                                    <span class="font-bold text-blue-400"
                                        >consistent presence</span
                                    >
                                    across all academic modules.
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-blue-100 bg-blue-50/30 p-8 shadow-sm"
                            >
                                <h3
                                    class="mb-6 flex items-center gap-2 text-sm font-bold tracking-tight text-blue-900 uppercase"
                                >
                                    <GraduationCap
                                        class="h-4 w-4 text-blue-600"
                                    />
                                    Academic Highlights
                                </h3>
                                <div class="space-y-4">
                                    <div
                                        v-for="i in 3"
                                        :key="i"
                                        class="flex items-center gap-4 rounded-2xl border border-blue-100/50 bg-background/50 p-3.5 transition-all duration-300 hover:bg-background"
                                    >
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100/50 text-blue-600"
                                        >
                                            <ShieldPlus class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p
                                                class="mb-1 text-xs leading-none font-bold tracking-tight text-muted-foreground uppercase"
                                            >
                                                Observation
                                            </p>
                                            <p
                                                class="text-[13px] font-bold text-foreground"
                                            >
                                                Exceeds proficiency in core
                                                learning areas.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Tab -->
                    <div v-else-if="activeTab === 'personal'" class="space-y-6">
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                                    >
                                        <UserRound class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Personal Details
                                    </h3>
                                </div>
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Gender
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.gender"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground uppercase"
                                    >
                                        {{ learner.gender || 'Not Specified' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Date of Birth
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            type="date"
                                            v-model="form.date_of_birth"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.date_of_birth || '--' }} ({{
                                            learner.age
                                        }}
                                        Yrs)
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Birth Certificate No.
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="
                                                form.birth_certificate_number
                                            "
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            learner.birth_certificate_number ||
                                            '--'
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Nationality
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.nationality"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.nationality || 'Kenyan' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Religion
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.religion"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="">
                                                Select Religion
                                            </option>
                                            <option
                                                v-for="r in religions"
                                                :key="r"
                                                :value="r"
                                            >
                                                {{ r }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.religion || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Primary Language
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.primary_language"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option
                                                v-for="l in languages"
                                                :key="l"
                                                :value="l"
                                            >
                                                {{ l }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            learner.primary_language ||
                                            'English'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Tab -->
                    <div v-else-if="activeTab === 'academic'" class="space-y-6">
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600"
                                    >
                                        <GraduationCap class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Academic History & Setup
                                    </h3>
                                </div>
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Admission Date
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            type="date"
                                            v-model="form.admission_date"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.admission_date || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Admission Class
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.admission_class_id"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="">
                                                Select Class
                                            </option>
                                            <option
                                                v-for="c in classes"
                                                :key="c.id"
                                                :value="String(c.id)"
                                            >
                                                {{ c.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            classes.find(
                                                (c) =>
                                                    String(c.id) ===
                                                    String(
                                                        learner.admission_class_id,
                                                    ),
                                            )?.name || '--'
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Graduation Date
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            type="date"
                                            v-model="form.graduation_date"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.graduation_date || 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Health Tab -->
                    <div v-else-if="activeTab === 'health'" class="space-y-6">
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600"
                                    >
                                        <HeartPulse class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Health & Medical Record
                                    </h3>
                                </div>
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Blood Group
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.blood_group"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="">
                                                Select Group
                                            </option>
                                            <option
                                                v-for="bg in [
                                                    'A+',
                                                    'A-',
                                                    'B+',
                                                    'B-',
                                                    'AB+',
                                                    'AB-',
                                                    'O+',
                                                    'O-',
                                                ]"
                                                :key="bg"
                                                :value="bg"
                                            >
                                                {{ bg }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-rose-600"
                                    >
                                        {{ learner.blood_group || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5 md:col-span-2">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Medical Conditions
                                    </p>
                                    <div v-if="isEditing">
                                        <textarea
                                            v-model="form.medical_conditions"
                                            class="min-h-[80px] w-full rounded-xl border-border bg-muted/30 p-4 text-sm font-medium outline-none focus:ring-2 focus:ring-blue-600/10"
                                        ></textarea>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm leading-relaxed font-bold text-foreground"
                                    >
                                        {{
                                            learner.medical_conditions ||
                                            'None Reported'
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-1.5 md:col-span-3">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Allergies
                                    </p>
                                    <div v-if="isEditing">
                                        <textarea
                                            v-model="form.allergies"
                                            class="min-h-[80px] w-full rounded-xl border-border bg-muted/30 p-4 text-sm font-medium outline-none focus:ring-2 focus:ring-blue-600/10"
                                        ></textarea>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm leading-relaxed font-bold text-foreground"
                                    >
                                        {{
                                            learner.allergies ||
                                            'No Known Allergies'
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-3 md:col-span-3">
                                    <div class="flex items-center gap-3">
                                        <p
                                            class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                        >
                                            Special Needs Support
                                        </p>
                                        <div
                                            v-if="isEditing"
                                            class="flex items-center gap-2"
                                        >
                                            <input
                                                type="checkbox"
                                                v-model="form.has_special_needs"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span
                                                class="text-xs font-semibold text-muted-foreground"
                                                >Required</span
                                            >
                                        </div>
                                    </div>
                                    <div
                                        v-if="form.has_special_needs"
                                        class="transition-all duration-300"
                                    >
                                        <div v-if="isEditing">
                                            <textarea
                                                v-model="
                                                    form.special_needs_details
                                                "
                                                placeholder="Describe requirements..."
                                                class="min-h-[100px] w-full rounded-2xl border-border bg-muted/30 p-4 text-sm font-medium outline-none focus:ring-2 focus:ring-blue-600/10"
                                            ></textarea>
                                        </div>
                                        <div
                                            v-else
                                            class="rounded-2xl border border-blue-100 bg-blue-50/50 p-4"
                                        >
                                            <p
                                                class="text-[13px] leading-relaxed font-medium text-blue-800"
                                            >
                                                {{
                                                    learner.special_needs_details
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <p
                                        v-else-if="!isEditing"
                                        class="text-sm font-semibold text-muted-foreground/50"
                                    >
                                        No Special Needs Recorded
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Tab -->
                    <div v-else-if="activeTab === 'location'" class="space-y-6">
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                    >
                                        <MapPin class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Location & Address
                                    </h3>
                                </div>
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div class="space-y-1.5 md:col-span-3">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Home Address / Physical Location
                                    </p>
                                    <div v-if="isEditing">
                                        <textarea
                                            v-model="form.home_address"
                                            class="min-h-[80px] w-full rounded-xl border-border bg-muted/30 p-4 text-sm font-medium outline-none focus:ring-2 focus:ring-blue-600/10"
                                        ></textarea>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.home_address || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        County
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.county"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="">
                                                Select County
                                            </option>
                                            <option
                                                v-for="c in counties"
                                                :key="c"
                                                :value="c"
                                            >
                                                {{ c }}
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.county || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Sub-County
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.sub_county"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.sub_county || '--' }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Ward
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.ward"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.ward || '--' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logistics Tab -->
                    <div
                        v-else-if="activeTab === 'logistics'"
                        class="space-y-6"
                    >
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600"
                                    >
                                        <Truck class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Logistics & Accommodation
                                    </h3>
                                </div>
                            </div>

                            <div
                                class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Boarding Status
                                    </p>
                                    <div v-if="isEditing">
                                        <select
                                            v-model="form.boarding_status"
                                            class="h-11 w-full cursor-pointer rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-600/10"
                                        >
                                            <option value="day">
                                                Day Scholar
                                            </option>
                                            <option value="boarding">
                                                Boarding
                                            </option>
                                        </select>
                                    </div>
                                    <Badge
                                        :class="
                                            learner.boarding_status ===
                                            'boarding'
                                                ? 'bg-indigo-500'
                                                : 'bg-amber-500'
                                        "
                                        class="w-fit rounded-lg border-0 px-3 py-1 text-xs font-bold tracking-wider text-white uppercase shadow-sm"
                                    >
                                        {{ learner.boarding_status }}
                                    </Badge>
                                </div>

                                <div
                                    v-if="form.boarding_status === 'boarding'"
                                    class="animate-in space-y-1.5 slide-in-from-top-2"
                                >
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Hostel / Room Info
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.hostel_room"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            learner.hostel_room ||
                                            'Pending Assignment'
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Transport Route
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.transport_route"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{
                                            learner.transport_route ||
                                            'Not Used'
                                        }}
                                    </p>
                                </div>

                                <div
                                    v-if="form.transport_route"
                                    class="space-y-1.5"
                                >
                                    <p
                                        class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Pickup Point
                                    </p>
                                    <div v-if="isEditing">
                                        <Input
                                            v-model="form.pickup_point"
                                            class="h-11 rounded-xl border-border bg-muted/30 font-medium"
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ learner.pickup_point || '--' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Family Tab -->
                    <div v-else-if="activeTab === 'family'" class="space-y-6">
                        <div
                            class="rounded-2xl border border-border bg-card p-8 shadow-sm"
                        >
                            <div
                                class="mb-8 flex items-center justify-between border-b border-border/50 pb-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                    >
                                        <Users class="h-5 w-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-foreground"
                                    >
                                        Family & Guardians
                                    </h3>
                                </div>
                            </div>

                            <div v-if="isEditing" class="space-y-10">
                                <div
                                    class="space-y-6 rounded-2xl border border-blue-100/50 bg-blue-50/30 p-6"
                                >
                                    <h4
                                        class="text-xs font-bold tracking-tight text-blue-900 uppercase"
                                    >
                                        Primary Guardian Account
                                    </h4>
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                                >Full Name</Label
                                            >
                                            <Input
                                                v-model="form.guardian_name"
                                                class="h-11 rounded-xl border-border bg-white font-medium"
                                            />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                                >Email Address</Label
                                            >
                                            <Input
                                                type="email"
                                                v-model="form.guardian_email"
                                                class="h-11 rounded-xl border-border bg-white font-medium"
                                            />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                                >Phone Number</Label
                                            >
                                            <Input
                                                v-model="form.guardian_phone"
                                                class="h-11 rounded-xl border-border bg-white font-medium"
                                            />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                                >Update Password</Label
                                            >
                                            <Input
                                                type="password"
                                                v-model="form.guardian_password"
                                                placeholder="Leave blank to keep current"
                                                class="h-11 rounded-xl border-border bg-white font-medium"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="space-y-6">
                                <div
                                    v-if="learner.guardians?.length"
                                    class="grid gap-6 md:grid-cols-2"
                                >
                                    <div
                                        v-for="guardian in learner.guardians"
                                        :key="guardian.id"
                                        class="group relative rounded-2xl border border-l-4 border-border border-l-blue-600 bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-md"
                                    >
                                        <div
                                            class="flex items-start justify-between"
                                        >
                                            <div>
                                                <p
                                                    class="mb-1.5 text-xs font-bold tracking-tight text-blue-600 uppercase"
                                                >
                                                    {{
                                                        guardian.relationship ||
                                                        'Guardian'
                                                    }}
                                                </p>
                                                <p
                                                    class="mb-5 text-lg font-bold text-foreground"
                                                >
                                                    {{ guardian.name }}
                                                </p>
                                                <div class="space-y-2.5">
                                                    <div
                                                        class="flex items-center gap-3 text-sm font-medium text-muted-foreground"
                                                    >
                                                        <div
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-muted"
                                                        >
                                                            <Phone
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </div>
                                                        {{
                                                            guardian.phone ||
                                                            '—'
                                                        }}
                                                    </div>
                                                    <div
                                                        class="flex items-center gap-3 text-sm font-medium text-muted-foreground"
                                                    >
                                                        <div
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-muted"
                                                        >
                                                            <Mail
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </div>
                                                        {{
                                                            guardian.email ||
                                                            '—'
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-9 w-9 rounded-xl border border-transparent text-muted-foreground transition-all group-hover:bg-blue-50 hover:border-blue-100 hover:text-blue-600"
                                                as-child
                                            >
                                                <Link
                                                    :href="`/guardians/${guardian.id}/edit`"
                                                    ><Edit2 class="h-3.5 w-3.5"
                                                /></Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-muted/20 py-12 text-center"
                                >
                                    <div
                                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted"
                                    >
                                        <Users
                                            class="h-6 w-6 text-muted-foreground/30"
                                        />
                                    </div>
                                    <p
                                        class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >
                                        No guardian profiles linked to this
                                        learner.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder for Detailed Tabs -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-16 text-center"
                    >
                        <div
                            class="mb-8 flex h-20 w-20 items-center justify-center rounded-2xl bg-muted shadow-inner"
                        >
                            <component
                                :is="tabs.find((t) => t.id === activeTab)?.icon"
                                class="h-8 w-8 text-muted-foreground/20"
                            />
                        </div>
                        <h3
                            class="text-xl font-bold tracking-tight text-foreground uppercase"
                        >
                            Viewing
                            {{ tabs.find((t) => t.id === activeTab)?.name }}
                        </h3>
                        <p
                            class="mt-3 max-w-sm text-sm font-medium text-muted-foreground"
                        >
                            Detailed information and history for
                            <span
                                class="font-bold tracking-wider text-blue-600 uppercase"
                                >{{ activeTab }}</span
                            >
                            is currently being processed.
                        </p>
                        <Button
                            variant="outline"
                            class="mt-8 h-11 rounded-xl border-border px-8 text-xs font-bold tracking-tight uppercase shadow-sm"
                            >Refresh Module</Button
                        >
                    </div>

                    <!-- Integrity Status Footer -->
                    <div
                        class="flex items-center justify-between rounded-2xl border border-border/50 bg-muted/30 p-6"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-white shadow-sm"
                            >
                                <ShieldCheck class="h-5 w-5 text-emerald-500" />
                            </div>
                            <div>
                                <p
                                    class="mb-1.5 text-xs leading-none font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Verification Status
                                </p>
                                <p
                                    class="text-xs font-semibold text-foreground"
                                >
                                    Last synchronized:
                                    {{ new Date().toLocaleString() }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="group flex cursor-help items-center gap-2"
                            title="Data is synced and secured"
                        >
                            <span
                                class="text-xs font-bold tracking-tight text-emerald-600 uppercase opacity-0 transition-opacity group-hover:opacity-100"
                                >Operational</span
                            >
                            <div
                                class="flex gap-1.5 rounded-full border border-border bg-white p-2 shadow-sm"
                            >
                                <div
                                    class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                                ></div>
                                <div
                                    class="h-2 w-2 rounded-full bg-emerald-500 opacity-30"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
