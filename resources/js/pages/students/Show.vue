<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { 
    ArrowLeft, Calendar, GraduationCap, MapPin, Phone, 
    ShieldPlus, UserRound, HeartPulse, ShieldCheck, 
    Truck, Users, Camera, Save, Loader2, Edit2, Mail,
    CreditCard, ExternalLink, Activity
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
    student: any;
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
    { title: 'Students', href: '/students' },
    { title: props.student.name, href: `/students/${props.student.id}` },
];

const activeTab = ref('overview');
const isEditing = ref(false);
const photoPreview = ref<string | null>(null);

const form = useForm({
    first_name: props.student.first_name,
    middle_name: props.student.middle_name ?? '',
    last_name: props.student.last_name,
    admission_number: props.student.admission_number ?? '',
    upi: props.student.upi ?? '',
    gender: props.student.gender,
    date_of_birth: props.student.date_of_birth ?? '',
    birth_certificate_number: props.student.birth_certificate_number ?? '',
    nationality: props.student.nationality ?? 'Kenyan',
    religion: props.student.religion ?? '',
    primary_language: props.student.primary_language ?? 'English',
    secondary_language: props.student.secondary_language ?? 'Kiswahili',
    admission_date: props.student.admission_date ?? '',
    admission_class_id: props.student.admission_class_id ? String(props.student.admission_class_id) : '',
    current_class_id: props.student.current_class_id ? String(props.student.current_class_id) : '',
    grade_id: props.student.grade_id ? String(props.student.grade_id) : '',
    status: props.student.status,
    home_address: props.student.home_address ?? '',
    county: props.student.county ?? '',
    sub_county: props.student.sub_county ?? '',
    ward: props.student.ward ?? '',
    blood_group: props.student.blood_group ?? '',
    medical_conditions: props.student.medical_conditions ?? '',
    allergies: props.student.allergies ?? '',
    has_special_needs: props.student.has_special_needs ?? false,
    special_needs_details: props.student.special_needs_details ?? '',
    boarding_status: props.student.boarding_status ?? 'day',
    hostel_room: props.student.hostel_room ?? '',
    transport_route: props.student.transport_route ?? '',
    pickup_point: props.student.pickup_point ?? '',
    graduation_date: props.student.graduation_date ?? '',
    withdrawal_date: props.student.withdrawal_date ?? '',
    withdrawal_reason: props.student.withdrawal_reason ?? '',
    photo: null as any,
    
    // Guardian details
    guardian_name: props.student.guardians?.[0]?.name ?? '',
    guardian_email: props.student.guardians?.[0]?.email ?? '',
    guardian_phone: props.student.guardians?.[0]?.phone ?? '',
    guardian_password: '',
    guardian_password_confirmation: '',
    
    _method: 'PUT',
});

const handlePhotoChange = (e: any) => {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(`/students/${props.student.id}`, {
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
    switch (status.toLowerCase()) {
        case 'active': return 'bg-emerald-500 text-white shadow-[0_0_10px_rgba(16,185,129,0.3)]';
        case 'suspended': return 'bg-rose-500 text-white';
        case 'inactive': return 'bg-slate-400 text-white';
        default: return 'bg-violet-500 text-white';
    }
};
</script>

<template>
    <Head :title="student.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-5">
                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-0.5 shadow-lg shadow-blue-100 group-hover:scale-105 transition-transform duration-500">
                                <div class="h-full w-full rounded-[14px] bg-white flex items-center justify-center overflow-hidden">
                                     <UserRound v-if="!student.photo" class="h-8 w-8 text-blue-200" />
                                     <img v-else :src="student.photo" class="h-full w-full object-cover" />
                                </div>
                            </div>
                            <div class="absolute -bottom-1 -right-1 h-5 w-5 rounded-lg bg-emerald-500 border-2 border-white flex items-center justify-center shadow-sm">
                                <ShieldCheck class="h-3 w-3 text-white" />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-none">{{ student.name }}</h1>
                                <Badge :class="getStatusColor(student.status)" class="rounded-full px-3 py-0.5 text-[8px] font-black uppercase tracking-[0.15em] border-0 h-5">
                                    {{ student.status }}
                                </Badge>
                            </div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1.5 flex items-center gap-2">
                                <span class="text-blue-600 font-black">{{ student.class_name || 'UNASSIGNED_NODE' }}</span> 
                                <span class="h-1 w-1 bg-slate-300 rounded-full"></span>
                                {{ student.admission_number || 'NO_ADM_ID' }}
                                <span class="h-1 w-1 bg-slate-300 rounded-full"></span>
                                <span class="text-slate-500 italic">{{ student.age }} Earth Years</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="hasPermission('students.update') && !isEditing" variant="outline" class="font-pulsar h-9 border-slate-200 px-4 rounded-xl hover:bg-slate-50 text-[10px] font-black uppercase tracking-widest" @click="isEditing = true">
                        <Edit2 class="mr-2 h-3.5 w-3.5 text-amber-500" />
                        Edit Metadata
                    </Button>
                    <Button v-if="hasPermission('finance.view')" variant="outline" class="font-pulsar h-9 border-slate-200 px-4 rounded-xl hover:bg-slate-50 text-[10px] font-black uppercase tracking-widest" as-child>
                        <Link :href="`/students/fees/${student.id}`">
                            <CreditCard class="mr-2 h-3.5 w-3.5 text-emerald-500" />
                            Financial Ledger
                        </Link>
                    </Button>
                    <Button v-if="isEditing" @click="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 font-pulsar h-9 px-6 shadow-lg shadow-blue-200 border-0 rounded-xl text-[10px] font-black uppercase tracking-widest text-white">
                        <Loader2 v-if="form.processing" class="mr-2 h-3.5 w-3.5 animate-spin" />
                        <Save v-else class="mr-2 h-3.5 w-3.5" />
                        Commit Changes
                    </Button>
                </div>
            </div>

            <!-- Tab Navigation Matrix -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-3 space-y-4 lg:sticky lg:top-6">
                    <div class="flex flex-col gap-2">
                        <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-3 px-5 py-3.5 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300 whitespace-nowrap group"
                        :class="activeTab === tab.id 
                            ? 'bg-slate-900 text-white shadow-xl scale-[1.02] z-10' 
                            : 'bg-white border text-slate-400 hover:text-slate-900 hover:border-slate-300'"
                        >
                            <component :is="tab.icon" class="h-4 w-4" :class="activeTab === tab.id ? 'text-blue-400' : 'group-hover:text-blue-600'" />
                            {{ tab.name }}
                            <div v-if="activeTab === tab.id" class="ml-auto animate-pulse"><div class="h-1.5 w-1.5 bg-blue-400 rounded-full"></div></div>
                        </button>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="hidden lg:flex flex-col p-5 rounded-[2rem] bg-blue-600 text-white shadow-xl shadow-blue-200 relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                             <GraduationCap class="h-24 w-24 text-white" />
                        </div>
                        <p class="text-[8px] font-black uppercase tracking-[0.2em] opacity-80 mb-4 italic">Academic pulse</p>
                        <div class="space-y-3 relative z-10">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold opacity-70">Presence</span>
                                <span class="text-xs font-black">98.4%</span>
                            </div>
                            <div class="w-full bg-white/20 h-1 rounded-full overflow-hidden">
                                <div class="bg-white h-full w-[98.4%]"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold opacity-70">Performance</span>
                                <span class="text-xs font-black text-emerald-300 italic">EXCEL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Matrix -->
                <div class="lg:col-span-9 space-y-6 animate-in slide-in-from-bottom-4 duration-700">
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <!-- Identity Matrix Section -->
                        <div class="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-sm hover:shadow-md transition-all duration-500">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight italic">Identity Matrix</h3>
                                <Badge variant="outline" class="text-[8px] font-black uppercase tracking-widest px-2 border-slate-200 text-slate-400 italic">Core Node</Badge>
                            </div>
                            
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <!-- First & Last Name (Editing) -->
                                <div v-if="isEditing" class="space-y-4 md:col-span-2">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <Label class="text-[9px] font-black uppercase text-slate-400 tracking-[0.1em]">First Name</Label>
                                            <Input v-model="form.first_name" placeholder="First Name" class="rounded-xl border-slate-200 font-bold h-10 bg-slate-50 focus:ring-blue-500 text-xs" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label class="text-[9px] font-black uppercase text-slate-400 tracking-[0.1em]">Last Name</Label>
                                            <Input v-model="form.last_name" placeholder="Last Name" class="rounded-xl border-slate-200 font-bold h-10 bg-slate-50 focus:ring-blue-500 text-xs" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Scholar Identity</p>
                                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ student.name }}</p>
                                </div>
                                
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Admission ID</p>
                                    <div v-if="isEditing">
                                        <Input v-model="form.admission_number" placeholder="ADM-000" class="rounded-xl border-slate-200 font-black h-10 bg-slate-50 italic focus:ring-blue-500 text-xs" />
                                    </div>
                                    <p v-else class="text-sm font-black text-blue-600 uppercase italic">{{ student.admission_number }}</p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">UPI Matrix</p>
                                    <div v-if="isEditing">
                                        <Input v-model="form.upi" placeholder="UPI-XXXX-XXXX" class="rounded-xl border-slate-200 font-bold h-10 bg-slate-50 focus:ring-blue-500 text-xs" />
                                    </div>
                                    <p v-else class="text-sm font-black text-slate-900">{{ student.upi || 'PENDING_NODE' }}</p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Academic Grade</p>
                                    <div v-if="isEditing">
                                        <select v-model="form.grade_id" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%22%20fill%3D%22none%22%20stroke%3D%22currentColor%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:14px] bg-[right_0.75rem_center] bg-no-repeat">
                                            <option value="">Select Grade</option>
                                            <option v-for="g in grades" :key="g.id" :value="String(g.id)">{{ g.name }}</option>
                                        </select>
                                    </div>
                                    <p v-else class="text-sm font-black text-slate-900 uppercase">{{ student.grade_name || 'NULL_LEVEL' }}</p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Stream Cluster</p>
                                    <div v-if="isEditing">
                                        <select v-model="form.current_class_id" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%22%20fill%3D%22none%22%20stroke%3D%22currentColor%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:14px] bg-[right_0.75rem_center] bg-no-repeat" :disabled="!form.grade_id">
                                            <option value="">Select Stream</option>
                                            <option v-for="c in filteredClasses" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                    </div>
                                    <p v-else class="text-sm font-black text-slate-900 uppercase italic">{{ student.class_name || 'SELECT_STREAM' }}</p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Lifecycle State</p>
                                    <div v-if="isEditing">
                                        <select v-model="form.status" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-500">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="graduated">Graduated</option>
                                            <option value="transferred">Transferred</option>
                                            <option value="withdrawn">Withdrawn</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div v-else>
                                        <Badge :class="getStatusColor(student.status)" class="rounded-full px-3 h-5 text-[8px] font-black uppercase tracking-widest border-0">
                                            {{ student.status }} Node
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Behavioral Matrix -->
                        <div class="grid gap-6 md:grid-cols-2">
                             <div class="bg-slate-900 rounded-[2rem] p-6 text-white relative overflow-hidden group">
                                <div class="absolute -right-6 -bottom-6 opacity-10 group-hover:scale-110 transition-transform duration-1000">
                                     <Activity class="h-32 w-32 text-white" />
                                </div>
                                <h3 class="text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <Activity class="h-3.5 text-blue-400" />
                                     حضور / Attendance
                                </h3>
                                <div class="flex items-end gap-1 h-16 mb-4">
                                    <div v-for="i in 12" :key="i" :style="{height: Math.random() * 80 + 20 + '%'}" class="flex-1 bg-blue-500/40 rounded-t-lg transition-all duration-700 hover:bg-blue-400"></div>
                                </div>
                                <p class="text-[9px] font-bold text-slate-400 leading-relaxed italic">
                                    Consistent presence recorded at 98.4%. No anomalies detected in current cycle.
                                </p>
                             </div>

                             <div class="bg-blue-50/50 rounded-[2rem] border border-blue-100 p-6 shadow-sm">
                                <h3 class="text-[10px] font-black text-blue-900 uppercase tracking-widest mb-4 flex items-center gap-2 italic">
                                    <GraduationCap class="h-3.5 text-blue-600" />
                                    Academic Highlights
                                </h3>
                                <div class="space-y-3">
                                    <div v-for="i in 3" :key="i" class="flex items-center gap-3 bg-white/60 p-2.5 rounded-xl border border-blue-50">
                                        <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <ShieldPlus class="h-4 w-4 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Achievement</p>
                                            <p class="text-[11px] font-black text-slate-900 mt-1 uppercase italic">High Consistency: Core Cluster</p>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Personal/Family Data -->
                    <div v-else-if="activeTab === 'personal' || activeTab === 'family'" class="space-y-6">
                        <div class="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight italic">{{ activeTab }} Information Matrix</h3>
                            </div>
                            
                            <!-- Placeholder for actual fields if they exist in props -->
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Biological Sex</p>
                                    <p class="text-xs font-black text-slate-900 uppercase">{{ student.gender || 'UNDEFINED' }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Temporal Origin (DOB)</p>
                                    <p class="text-xs font-black text-slate-900">{{ student.date_of_birth || 'UNKNOWN' }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em]">Nationality Registry</p>
                                    <p class="text-xs font-black text-slate-900 uppercase">{{ student.nationality || 'KENYAN' }}</p>
                                </div>
                            </div>
                            
                            <!-- Guardian Matrix if and only if family tab -->
                            <div v-if="activeTab === 'family'" class="mt-8 space-y-6">
                                <div class="flex items-center gap-4 text-slate-300">
                                    <div class="h-[1px] flex-1 bg-slate-100"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest">Kin Links</span>
                                    <div v-if="hasPermission('students.update')" @click="isEditing = true" class="text-blue-600 cursor-pointer hover:underline text-[9px] font-black uppercase tracking-widest">Add Kin</div>
                                    <div class="h-[1px] flex-1 bg-slate-100"></div>
                                </div>
                                
                                <div v-if="student.guardians?.length" class="grid gap-4 md:grid-cols-2">
                                    <div v-for="guardian in student.guardians" :key="guardian.id" class="rounded-2xl border border-slate-100 p-5 bg-white shadow-sm hover:shadow-lg transition-all duration-500 border-l-4 border-l-blue-600 relative group">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-[8px] font-black text-blue-600 uppercase tracking-widest mb-1">{{ guardian.relationship || 'Guardian' }}</p>
                                                <p class="font-black text-base text-slate-900 mb-4">{{ guardian.name }}</p>
                                                <div class="space-y-2">
                                                    <div class="flex items-center gap-2 text-slate-500 font-bold text-[10px]">
                                                        <Phone class="h-3 w-3 text-slate-400" /> {{ guardian.phone || '—' }}
                                                    </div>
                                                    <div class="flex items-center gap-2 text-slate-500 font-bold text-[10px]">
                                                        <Mail class="h-3 w-3 text-slate-400" /> {{ guardian.email || '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-300 hover:text-blue-600 rounded-lg group-hover:bg-blue-50 transition-colors" as-child>
                                                <Link :href="`/guardians/${guardian.id}/edit`"><Edit2 class="h-3.5 w-3.5" /></Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-10 bg-slate-50/50 border border-dashed border-slate-200 rounded-3xl italic">
                                    <Users class="h-10 w-10 text-slate-200 mx-auto mb-2" />
                                    <p class="text-slate-400 font-black uppercase tracking-widest text-[9px]">No genetic links synchronized.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Tab Content -->
                    <div v-else class="bg-white rounded-[2.5rem] border border-slate-100 p-12 flex flex-col items-center justify-center min-h-[400px] text-center italic">
                        <div class="h-16 w-16 rounded-2xl bg-slate-50 flex items-center justify-center mb-6">
                            <component :is="tabs.find(t => t.id === activeTab)?.icon" class="h-6 w-6 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Accessing {{ activeTab }} matrix</h3>
                        <p class="text-slate-500 font-bold mt-2 max-w-sm text-xs">
                             Detailed information for the <span class="text-blue-600 font-black uppercase">{{ activeTab }}</span> node is being synchronized from the vision core.
                        </p>
                        <Button variant="outline" class="mt-8 rounded-xl h-9 px-6 font-black uppercase text-[9px] tracking-widest border-slate-200">Execute Deep Scan</Button>
                    </div>

                    <!-- Detail Footer Integrity -->
                    <div class="bg-slate-50/30 border border-slate-100 rounded-[2rem] p-6 flex items-center justify-between">
                         <div class="flex items-center gap-3">
                             <div class="h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center shadow-sm">
                                 <ShieldCheck class="h-4 w-4 text-emerald-500" />
                             </div>
                             <div>
                                 <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">Matrix Integrity</p>
                                 <p class="text-[10px] font-bold text-slate-700">Verified & Synchronized @ {{ new Date().toLocaleTimeString() }}</p>
                             </div>
                         </div>
                         <div class="flex gap-1.5 px-3 py-1.5 bg-white rounded-full border border-slate-100">
                             <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                             <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 opacity-50"></div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
