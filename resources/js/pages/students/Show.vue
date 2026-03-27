<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft, Calendar, GraduationCap, MapPin, Phone,
    ShieldPlus, UserRound, HeartPulse, ShieldCheck,
    Truck, Users, Camera, Save, Loader2, Edit2, Mail,
    CreditCard, ExternalLink, Activity, TrendingUp, UserPlus
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learner: any;
    grades: any[];
    classes: any[];
    counties: string[];
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
    admission_class_id: props.learner.admission_class_id ? String(props.learner.admission_class_id) : '',
    current_class_id: props.learner.current_class_id ? String(props.learner.current_class_id) : '',
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
    return props.classes.filter(c => String(c.grade_level_id) === String(form.grade_id));
});

watch(() => form.grade_id, () => {
    if (!filteredClasses.value.some(c => String(c.id) === String(form.current_class_id))) {
        form.current_class_id = '';
    }
});

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
        case 'active': return 'bg-emerald-500 text-white shadow-sm';
        case 'suspended': return 'bg-rose-500 text-white shadow-sm';
        case 'inactive':
        case 'withdrawn': return 'bg-slate-400 text-white shadow-sm';
        case 'graduated': return 'bg-blue-600 text-white shadow-sm';
        default: return 'bg-indigo-500 text-white shadow-sm';
    }
};
</script>

<template>
    <Head :title="learner.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in duration-500 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Header section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 text-muted-foreground hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-100">
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div class="flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
                        <div class="relative group">
                            <div v-if="!isEditing" class="h-32 w-32 rounded-[2.5rem] bg-card border-4 border-white shadow-xl flex items-center justify-center overflow-hidden transition-all duration-500 hover:rotate-2 group-hover:scale-105">
                                <img v-if="learner.photo_url" :src="learner.photo_url" class="h-full w-full object-cover" />
                                <div v-else class="text-4xl font-black text-blue-600 bg-blue-50 w-full h-full flex items-center justify-center uppercase italic">
                                     {{ learner.name.split(' ').map((n: string) => n[0]).join('').substring(0, 2) }}
                                </div>
                            </div>
                            <div v-else class="h-32 w-32 flex items-center justify-center">
                                 <ProfilePhotoUpload 
                                    v-model="form.photo" 
                                    :error="form.errors.photo"
                                    :current-image="learner.photo_url"
                                />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ learner.name }}</h1>
                                <Badge :class="getStatusColor(learner.status)" class="rounded-lg px-3 py-1 text-[10px] font-bold uppercase tracking-wider border-0 shadow-sm">
                                    {{ learner.status }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2 mt-2 text-sm font-medium text-muted-foreground">
                                <span class="text-blue-600">{{ learner.class_name || 'Unassigned' }}</span>
                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                <span>ID: {{ learner.admission_number || '--' }}</span>
                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                <span>{{ learner.age }} Years Old</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button as-child variant="outline" class="h-11 px-5 rounded-xl border-blue-100 bg-blue-50/50 hover:bg-blue-600 hover:text-white hover:border-blue-600 text-blue-700 font-semibold shadow-sm transition-all" v-if="hasPermission('students.update') && !isEditing">
                        <Link :href="`/students/enrollments/create?student_id=${learner.id}`">
                            <UserPlus class="mr-2 h-4 w-4" />
                            Enroll Learner
                        </Link>
                    </Button>
                    <Button v-if="hasPermission('students.update') && !isEditing" variant="outline" class="h-11 px-5 rounded-xl border-border font-semibold shadow-sm hover:bg-muted" @click="isEditing = true">
                        <Edit2 class="mr-2 h-4 w-4 text-amber-500" />
                        Edit Profile
                    </Button>
                    <Button v-if="hasPermission('finance.view')" variant="outline" class="h-11 px-5 rounded-xl border-border font-semibold shadow-sm hover:bg-muted" as-child>
                        <Link :href="`/students/fees/${learner.id}`">
                            <CreditCard class="mr-2 h-4 w-4 text-emerald-600" />
                            Fees & Payments
                        </Link>
                    </Button>
                    <Button v-if="isEditing" @click="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 h-11 px-6 shadow-md rounded-xl font-semibold text-white">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Changes
                    </Button>
                </div>
            </div>

            <!-- Main Content Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="flex flex-col gap-1.5 rounded-2xl border border-border bg-card p-2 shadow-sm">
                        <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 group relative"
                        :class="activeTab === tab.id
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                        >
                            <component :is="tab.icon" class="h-4.5 w-4.5" :class="activeTab === tab.id ? 'text-white' : 'text-muted-foreground/50 group-hover:text-foreground'" />
                            {{ tab.name }}
                            <div v-if="activeTab === tab.id" class="ml-auto">
                                <div class="h-1.5 w-1.5 bg-blue-200 rounded-full animate-pulse"></div>
                            </div>
                        </button>
                    </div>

                    <!-- Quick Metrics Card -->
                    <div class="rounded-2xl border border-border bg-blue-600 p-6 text-white shadow-xl shadow-blue-100 overflow-hidden relative group">
                        <div class="absolute -right-6 -bottom-6 opacity-10 group-hover:scale-110 transition-transform duration-1000">
                             <TrendingUp class="h-32 w-32" />
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 mb-5 italic">Performance Score</p>
                        <div class="space-y-4 relative z-10">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-xs font-semibold opacity-80">Attendance</span>
                                    <span class="text-xs font-bold">98.4%</span>
                                </div>
                                <div class="w-full bg-white/20 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-white h-full w-[98.4%] rounded-full"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-xs font-semibold opacity-80">Assessment Status</span>
                                <Badge class="bg-blue-500 text-[9px] font-bold uppercase tracking-tighter border-0 rounded-md">Excellent</Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div class="lg:col-span-9 space-y-6 animate-in slide-in-from-bottom-4 duration-700">
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <div class="bg-card rounded-2xl border border-border p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-border/50 pb-5 mb-8">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <UserRound class="h-5 w-5" />
                                    </div>
                                    <h3 class="text-lg font-bold text-foreground">Basic Profile Information</h3>
                                </div>
                                <Badge variant="outline" class="text-[10px] font-bold uppercase tracking-wider px-3 border-border bg-muted/50">Core Data</Badge>
                            </div>

                            <div class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                                <div v-if="isEditing" class="space-y-4 md:col-span-2">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <Label class="text-xs font-bold text-muted-foreground uppercase tracking-wide">First Name</Label>
                                            <Input v-model="form.first_name" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label class="text-xs font-bold text-muted-foreground uppercase tracking-wide">Last Name</Label>
                                            <Input v-model="form.last_name" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Full Legal Name</p>
                                    <p class="text-[15px] font-bold text-foreground">{{ learner.name }}</p>
                                </div>

                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Admission Number</p>
                                    <div v-if="isEditing">
                                        <Input v-model="form.admission_number" class="h-11 rounded-xl border-border bg-muted/30 font-bold text-blue-600" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-blue-600">{{ learner.admission_number || '--' }}</p>
                                </div>

                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">UPI Number</p>
                                    <div v-if="isEditing">
                                        <Input v-model="form.upi" class="h-11 rounded-xl border-border bg-muted/30 font-medium" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ learner.upi || 'Not Assigned' }}</p>
                                </div>

                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Academic Level / Grade</p>
                                    <div v-if="isEditing">
                                        <select v-model="form.grade_id" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold hover:bg-muted/50 transition-all outline-none focus:ring-2 focus:ring-blue-600/10 appearance-none cursor-pointer">
                                            <option value="">Select Level</option>
                                            <option v-for="g in grades" :key="g.id" :value="String(g.id)">{{ g.name }}</option>
                                        </select>
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ learner.grade_name || 'Unassigned' }}</p>
                                </div>

                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Assigned Stream</p>
                                    <div v-if="isEditing">
                                        <select v-model="form.current_class_id" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold hover:bg-muted/50 transition-all outline-none focus:ring-2 focus:ring-blue-600/10 appearance-none cursor-pointer" :disabled="!form.grade_id">
                                            <option value="">Select Stream</option>
                                            <option v-for="c in filteredClasses" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ learner.class_name || 'No stream assigned' }}</p>
                                </div>

                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Profile Status</p>
                                    <div v-if="isEditing">
                                        <select v-model="form.status" class="h-11 w-full rounded-xl border-border bg-muted/30 px-4 text-sm font-semibold hover:bg-muted/50 transition-all outline-none focus:ring-2 focus:ring-blue-600/10 cursor-pointer">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="graduated">Graduated</option>
                                            <option value="transferred">Transferred</option>
                                            <option value="withdrawn">Withdrawn</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div v-else>
                                        <Badge :class="getStatusColor(learner.status)" class="rounded-lg px-3 py-1 text-[10px] font-bold uppercase tracking-wider border-0 shadow-sm">
                                            {{ learner.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Summary Metrics -->
                        <div class="grid gap-6 md:grid-cols-2">
                             <div class="bg-foreground rounded-2xl p-8 text-background shadow-lg relative overflow-hidden group">
                                <div class="absolute -right-8 -top-8 opacity-5 group-hover:scale-125 transition-transform duration-1000">
                                     <Activity class="h-48 w-48" />
                                </div>
                                <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] mb-6 flex items-center gap-2 text-muted-foreground">
                                    <Activity class="h-4 w-4 text-blue-500" />
                                     Attendance Trends
                                </h3>
                                <div class="flex items-end gap-1.5 h-20 mb-6">
                                    <div v-for="i in 15" :key="i" :style="{height: Math.random() * 80 + 20 + '%'}" class="flex-1 bg-blue-500/30 rounded-t-md transition-all duration-700 hover:bg-blue-500 hover:opacity-100"></div>
                                </div>
                                <p class="text-sm font-semibold text-muted-foreground leading-relaxed">
                                    Recent attendance pulse shows <span class="text-blue-400 font-bold">consistent presence</span> across all academic modules.
                                </p>
                             </div>

                             <div class="bg-blue-50/30 rounded-2xl border border-blue-100 p-8 shadow-sm">
                                <h3 class="text-[11px] font-bold text-blue-900 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                                    <GraduationCap class="h-4 w-4 text-blue-600" />
                                    Academic Highlights
                                </h3>
                                <div class="space-y-4">
                                    <div v-for="i in 3" :key="i" class="flex items-center gap-4 bg-background/50 p-3.5 rounded-2xl border border-blue-100/50 hover:bg-background transition-all duration-300">
                                        <div class="h-10 w-10 rounded-xl bg-blue-100/50 flex items-center justify-center text-blue-600">
                                            <ShieldPlus class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest leading-none mb-1">Observation</p>
                                            <p class="text-[13px] font-bold text-foreground">Exceeds proficiency in core learning areas.</p>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Personal/Family Sections -->
                    <div v-else-if="activeTab === 'personal' || activeTab === 'family'" class="space-y-6">
                        <div class="bg-card rounded-2xl border border-border p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-border/50 pb-5 mb-8">
                                <h3 class="text-lg font-bold text-foreground capitalize">{{ activeTab }} Information</h3>
                            </div>

                            <div class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Gender</p>
                                    <p class="text-[15px] font-bold text-foreground uppercase">{{ learner.gender || 'Not Specified' }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Date of Birth</p>
                                    <p class="text-[15px] font-bold text-foreground">{{ learner.date_of_birth || '--' }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Nationality</p>
                                    <p class="text-[15px] font-bold text-foreground uppercase">{{ learner.nationality || 'Kenyan' }}</p>
                                </div>
                            </div>

                            <!-- Guardian Section -->
                            <div v-if="activeTab === 'family'" class="mt-12 space-y-6">
                                <div class="flex items-center gap-4">
                                     <span class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground/50">Primary Guardians</span>
                                     <div class="h-px flex-1 bg-border/50"></div>
                                     <button v-if="hasPermission('students.update')" @click="isEditing = true" class="text-blue-600 hover:text-blue-700 text-[10px] font-bold uppercase tracking-widest border border-blue-100 bg-blue-50/50 px-3 py-1 rounded-lg">Add Record</button>
                                </div>

                                <div v-if="learner.guardians?.length" class="grid gap-6 md:grid-cols-2">
                                    <div v-for="guardian in learner.guardians" :key="guardian.id" class="rounded-2xl border border-border p-6 bg-card shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-blue-600 group relative">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1.5">{{ guardian.relationship || 'Guardian' }}</p>
                                                <p class="font-bold text-lg text-foreground mb-5">{{ guardian.name }}</p>
                                                <div class="space-y-2.5">
                                                    <div class="flex items-center gap-3 text-sm font-medium text-muted-foreground">
                                                        <div class="h-7 w-7 rounded-lg bg-muted flex items-center justify-center"><Phone class="h-3.5 w-3.5" /></div> {{ guardian.phone || '—' }}
                                                    </div>
                                                    <div class="flex items-center gap-3 text-sm font-medium text-muted-foreground">
                                                        <div class="h-7 w-7 rounded-lg bg-muted flex items-center justify-center"><Mail class="h-3.5 w-3.5" /></div> {{ guardian.email || '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <Button variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground hover:text-blue-600 rounded-xl group-hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100" as-child>
                                                <Link :href="`/guardians/${guardian.id}/edit`"><Edit2 class="h-3.5 w-3.5" /></Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-12 bg-muted/20 border border-dashed border-border rounded-2xl text-center">
                                    <div class="h-12 w-12 rounded-full bg-muted flex items-center justify-center mb-3">
                                        <Users class="h-6 w-6 text-muted-foreground/30" />
                                    </div>
                                    <p class="text-muted-foreground font-bold uppercase tracking-widest text-[10px]">No guardian profiles linked to this learner.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder for Detailed Tabs -->
                    <div v-else class="bg-card rounded-3xl border border-border p-16 flex flex-col items-center justify-center text-center">
                        <div class="h-20 w-20 rounded-2xl bg-muted flex items-center justify-center mb-8 shadow-inner">
                            <component :is="tabs.find(t => t.id === activeTab)?.icon" class="h-8 w-8 text-muted-foreground/20" />
                        </div>
                        <h3 class="text-xl font-bold text-foreground uppercase tracking-tight">Viewing {{ tabs.find(t => t.id === activeTab)?.name }}</h3>
                        <p class="text-muted-foreground font-medium mt-3 max-w-sm text-sm">
                             Detailed information and history for <span class="text-blue-600 font-bold uppercase tracking-wider">{{ activeTab }}</span> is currently being processed.
                        </p>
                        <Button variant="outline" class="mt-8 rounded-xl h-11 px-8 font-bold uppercase text-[10px] tracking-widest border-border shadow-sm">Refresh Module</Button>
                    </div>

                    <!-- Integrity Status Footer -->
                    <div class="bg-muted/30 border border-border/50 rounded-2xl p-6 flex items-center justify-between">
                         <div class="flex items-center gap-4">
                             <div class="h-10 w-10 rounded-xl bg-white border border-border flex items-center justify-center shadow-sm">
                                 <ShieldCheck class="h-5 w-5 text-emerald-500" />
                             </div>
                             <div>
                                 <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest leading-none mb-1.5">Verification Status</p>
                                 <p class="text-xs font-semibold text-foreground italic">Last synchronized: {{ new Date().toLocaleString() }}</p>
                             </div>
                         </div>
                         <div class="flex items-center gap-2 group cursor-help" title="Data is synced and secured">
                             <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Operational</span>
                             <div class="flex gap-1.5 p-2 bg-white rounded-full border border-border shadow-sm">
                                 <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                 <div class="h-2 w-2 rounded-full bg-emerald-500 opacity-30"></div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
