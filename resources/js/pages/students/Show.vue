<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, Calendar, GraduationCap, MapPin, Phone, 
    ShieldPlus, UserRound, HeartPulse, ShieldCheck, 
    Truck, Users, Camera, Save, Loader2, Edit2, Mail
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
    { id: 'overview', name: 'Overview', icon: ShieldCheck },
    { id: 'personal', name: 'Personal', icon: UserRound },
    { id: 'academic', name: 'Academic', icon: GraduationCap },
    { id: 'health', name: 'Health', icon: HeartPulse },
    { id: 'location', name: 'Location', icon: MapPin },
    { id: 'logistics', name: 'Logistics', icon: Truck },
    { id: 'family', name: 'Family', icon: Users },
];
</script>

<template>
    <Head :title="student.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div class="relative group">
                        <div class="h-16 w-16 overflow-hidden rounded-full border-2 border-primary/20 bg-muted shrink-0">
                            <img v-if="photoPreview || student.photo" :src="photoPreview || `/storage/${student.photo}`" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-indigo-500/10 text-indigo-600 font-bold text-xl">
                                {{ student.first_name[0] }}{{ student.last_name[0] }}
                            </div>
                        </div>
                        <div v-if="isEditing" class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 rounded-full cursor-pointer transition-opacity">
                            <Camera class="h-6 w-6 text-white mb-1" />
                            <span class="text-[10px] text-white font-medium">CHANGE</span>
                            <input type="file" @change="handlePhotoChange" class="hidden" accept="image/*" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">{{ student.name }}</h1>
                            <Badge :variant="student.status === 'active' ? 'default' : 'secondary'">{{ student.status }}</Badge>
                        </div>
                        <p class="text-muted-foreground">{{ student.class_name || 'Unassigned' }} • {{ student.admission_number || 'No Admission No.' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <template v-if="!isEditing">
                        <Button variant="outline" @click="isEditing = true">Edit Profile</Button>
                        <Button variant="outline" as-child><Link :href="`/students/fees/${student.id}`">Fees</Link></Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" @click="isEditing = false">Cancel</Button>
                        <Button @click="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Main Content with Tabs -->
            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Tabs Sidebar -->
                <div class="w-full lg:w-64 flex flex-row lg:flex-col gap-1 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 scrollbar-hide">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors whitespace-nowrap"
                        :class="activeTab === tab.id ? 'bg-primary text-primary-foreground shadow-sm' : 'hover:bg-muted'"
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.name }}
                    </button>
                    <div class="flex-1"></div>
                </div>

                <!-- Tab Content -->
                <div class="flex-1 rounded-xl border bg-card shadow-sm overflow-hidden min-h-[500px]">
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Student Overview</h3>
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Full Name</Label>
                                    <p class="font-medium capitalize py-2" v-if="!isEditing">{{ student.name }}</p>
                                    <div v-else class="flex gap-2">
                                        <Input v-model="form.first_name" placeholder="First Name" />
                                        <Input v-model="form.last_name" placeholder="Last Name" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Admission No.</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.admission_number || '—' }}</p>
                                    <Input v-else v-model="form.admission_number" placeholder="ADM001" />
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">UPI Number</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.upi || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.upi" placeholder="UPI123456" />
                                        <InputError :message="form.errors.upi" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Current Class</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.class_name || 'Unassigned' }}</p>
                                    <div v-else class="space-y-2">
                                        <div class="space-y-1">
                                            <select v-model="form.grade_id" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                                <option value="">Select Grade</option>
                                                <option v-for="g in grades" :key="g.id" :value="String(g.id)">{{ g.name }}</option>
                                            </select>
                                            <InputError :message="form.errors.grade_id" />
                                        </div>
                                        <div class="space-y-1">
                                            <select v-model="form.current_class_id" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!form.grade_id">
                                                <option value="">Select Class</option>
                                                <option v-for="c in filteredClasses" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                            </select>
                                            <InputError :message="form.errors.current_class_id" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Status</Label>
                                    <div class="py-2" v-if="!isEditing"><Badge :variant="student.status === 'active' ? 'default' : 'secondary'">{{ student.status }}</Badge></div>
                                    <div v-else class="space-y-1">
                                        <select v-model="form.status" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="graduated">Graduated</option>
                                            <option value="transferred">Transferred</option>
                                            <option value="withdrawn">Withdrawn</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                        <InputError :message="form.errors.status" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Tab -->
                        <div v-else-if="activeTab === 'personal'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Personal Details</h3>
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Gender</Label>
                                    <p class="font-medium capitalize py-2" v-if="!isEditing">{{ student.gender }}</p>
                                    <div v-else class="space-y-1">
                                        <select v-model="form.gender" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <InputError :message="form.errors.gender" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Date of Birth</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.date_of_birth || '—' }} ({{ student.age }} yrs)</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.date_of_birth" type="date" />
                                        <InputError :message="form.errors.date_of_birth" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Birth Cert No.</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.birth_certificate_number || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.birth_certificate_number" />
                                        <InputError :message="form.errors.birth_certificate_number" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Nationality</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.nationality || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.nationality" />
                                        <InputError :message="form.errors.nationality" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Religion</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.religion || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.religion" />
                                        <InputError :message="form.errors.religion" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Primary Language</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.primary_language || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.primary_language" />
                                        <InputError :message="form.errors.primary_language" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Tab -->
                        <div v-else-if="activeTab === 'academic'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Academic History</h3>
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Admission Date</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.admission_date || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.admission_date" type="date" />
                                        <InputError :message="form.errors.admission_date" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Admission Class</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.admission_class_id || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <select v-model="form.admission_class_id" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option value="">Select Class</option>
                                            <option v-for="c in props.classes" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                        <InputError :message="form.errors.admission_class_id" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Graduation Date</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.graduation_date || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.graduation_date" type="date" />
                                        <InputError :message="form.errors.graduation_date" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Health Tab -->
                        <div v-else-if="activeTab === 'health'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Medical & Health</h3>
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Blood Group</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.blood_group || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <select v-model="form.blood_group" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option v-for="bg in ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']" :key="bg" :value="bg">{{ bg }}</option>
                                        </select>
                                        <InputError :message="form.errors.blood_group" />
                                    </div>
                                </div>
                                <div class="space-y-1 md:col-span-2">
                                    <Label class="text-muted-foreground">Allergies</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.allergies || 'None recorded' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.allergies" placeholder="e.g. Peanuts, Penicillin" />
                                        <InputError :message="form.errors.allergies" />
                                    </div>
                                </div>
                                <div class="space-y-1 md:col-span-3">
                                    <Label class="text-muted-foreground">Medical Conditions</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.medical_conditions || 'None recorded' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.medical_conditions" />
                                        <InputError :message="form.errors.medical_conditions" />
                                    </div>
                                </div>
                                <div class="space-y-3 md:col-span-3">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="special_needs" v-model="form.has_special_needs" :disabled="!isEditing" />
                                        <Label for="special_needs">Has Special Needs</Label>
                                    </div>
                                    <div v-if="form.has_special_needs" class="space-y-1">
                                        <Label class="text-muted-foreground">Special Needs Details</Label>
                                        <p class="font-medium py-2" v-if="!isEditing">{{ student.special_needs_details }}</p>
                                        <div v-else class="space-y-1">
                                            <Input v-model="form.special_needs_details" />
                                            <InputError :message="form.errors.special_needs_details" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div v-else-if="activeTab === 'location'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Address & Location</h3>
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1 md:col-span-3">
                                    <Label class="text-muted-foreground">Home Address</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.home_address || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.home_address" />
                                        <InputError :message="form.errors.home_address" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">County</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.county || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <select v-model="form.county" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option value="">Select County</option>
                                            <option v-for="c in counties" :key="c" :value="c">{{ c }}</option>
                                        </select>
                                        <InputError :message="form.errors.county" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Sub County</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.sub_county || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.sub_county" />
                                        <InputError :message="form.errors.sub_county" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Ward</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.ward || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.ward" />
                                        <InputError :message="form.errors.ward" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logistics Tab -->
                        <div v-else-if="activeTab === 'logistics'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Logistics & Boarding</h3>
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Boarding Status</Label>
                                    <p class="font-medium capitalize py-2" v-if="!isEditing">{{ student.boarding_status }}</p>
                                    <div v-else class="space-y-1">
                                        <select v-model="form.boarding_status" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option value="day">Day</option>
                                            <option value="boarding">Boarding</option>
                                        </select>
                                        <InputError :message="form.errors.boarding_status" />
                                    </div>
                                </div>
                                <div v-if="form.boarding_status === 'boarding'" class="space-y-1">
                                    <Label class="text-muted-foreground">Hostel Room</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.hostel_room || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.hostel_room" />
                                        <InputError :message="form.errors.hostel_room" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Transport Route</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.transport_route || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.transport_route" />
                                        <InputError :message="form.errors.transport_route" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-muted-foreground">Pickup Point</Label>
                                    <p class="font-medium py-2" v-if="!isEditing">{{ student.pickup_point || '—' }}</p>
                                    <div v-else class="space-y-1">
                                        <Input v-model="form.pickup_point" />
                                        <InputError :message="form.errors.pickup_point" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Family Tab -->
                        <div v-else-if="activeTab === 'family'" class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Guardians & Family</h3>
                            
                            <div v-if="isEditing" class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Primary Guardian Name</Label>
                                    <Input v-model="form.guardian_name" placeholder="Full Name" />
                                    <InputError :message="form.errors.guardian_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Guardian Email</Label>
                                    <Input v-model="form.guardian_email" type="email" placeholder="email@example.com" />
                                    <InputError :message="form.errors.guardian_email" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Guardian Phone</Label>
                                    <Input v-model="form.guardian_phone" placeholder="0712345678" />
                                    <InputError :message="form.errors.guardian_phone" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Update Password (Optional)</Label>
                                    <Input v-model="form.guardian_password" type="password" placeholder="Min 8 characters" />
                                    <InputError :message="form.errors.guardian_password" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Confirm Password</Label>
                                    <Input v-model="form.guardian_password_confirmation" type="password" placeholder="Repeat password" />
                                    <InputError :message="form.errors.guardian_password_confirmation" />
                                </div>
                            </div>

                            <div v-else-if="student.guardians.length" class="grid gap-4 md:grid-cols-2">
                                <div v-for="guardian in student.guardians" :key="guardian.id" class="rounded-xl border p-4 group relative overflow-hidden bg-muted/30">
                                    <div class="absolute inset-y-0 left-0 w-1 bg-primary"></div>
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-bold text-lg">{{ guardian.name }}</p>
                                            <div class="mt-2 space-y-1 text-sm text-muted-foreground">
                                                <div class="flex items-center gap-2"><Phone class="h-3 w-3" /> {{ guardian.phone || '—' }}</div>
                                                <div class="flex items-center gap-2"><Mail class="h-3 w-3" /> {{ guardian.email || '—' }}</div>
                                            </div>
                                            <Badge v-if="guardian.has_login" variant="outline" class="mt-2 text-[10px] h-5 bg-green-50 text-green-700 border-green-200">Active Login</Badge>
                                        </div>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 opacity-0 group-hover:opacity-100 transition-opacity" as-child>
                                            <Link :href="`/guardians/${guardian.id}/edit`"><Edit2 class="h-4 w-4" /></Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 border-2 border-dashed rounded-xl">
                                <Users class="h-12 w-12 text-muted-foreground/30 mx-auto" />
                                <p class="mt-2 text-muted-foreground">No guardians linked to this student.</p>
                                <Button variant="outline" size="sm" class="mt-4" @click="isEditing = true">Add Guardian</Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
