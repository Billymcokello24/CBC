<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Users, ArrowLeft, Mail, Phone, MapPin, 
    Calendar, Briefcase, GraduationCap, 
    BookOpen, FileText, Settings, Edit,
    Camera, CheckCircle2, ShieldCheck,
    Building2, Hash, Contact,
    Globe, Trash2, Save, X, Plus
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem } from '@/types';

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
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: props.teacher.full_name, href: `/teachers/${props.teacher.id}` },
];

const activeTab = ref('overview');
const isEditing = ref(false);

const form = useForm({
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
    status: props.teacher.status,
    photo: null as File | null,
    password: '',
    password_confirmation: '',
});

const saveChanges = () => {
    form.put(`/teachers/${props.teacher.id}`, {
        preserveScroll: true,
        onSuccess: () => isEditing.value = false,
    });
};

const handlePhotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.photo = target.files[0];
    }
};
</script>

<template>
    <Head :title="teacher.full_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Profile Header -->
            <div class="relative overflow-hidden rounded-2xl border bg-card shadow-sm">
                <!-- Background Decoration -->
                <div class="absolute inset-0 bg-linear-to-br from-purple-500/5 via-transparent to-transparent"></div>
                
                <div class="relative flex flex-col gap-6 p-6 md:flex-row md:items-center">
                    <!-- Photo Section -->
                    <div class="relative group">
                        <div class="h-32 w-32 overflow-hidden rounded-full border-4 border-background bg-muted shadow-md">
                            <img v-if="teacher.photo" :src="`/storage/${teacher.photo}`" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-4xl font-bold text-purple-600">
                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                            </div>
                        </div>
                        <div v-if="isEditing" class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 rounded-full cursor-pointer transition-opacity">
                            <Camera class="h-6 w-6 text-white mb-1" />
                            <span class="text-[10px] text-white font-medium">CHANGE</span>
                            <input type="file" @change="handlePhotoChange" class="hidden" accept="image/*" />
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight">{{ teacher.full_name }}</h1>
                            <Badge :variant="teacher.status === 'active' ? 'default' : 'secondary'" class="capitalize text-xs">
                                {{ teacher.status }}
                            </Badge>
                        </div>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <Hash class="h-4 w-4" />
                                <span>{{ teacher.staff_number }}</span>
                            </div>
                            <div v-if="teacher.tsc_number" class="flex items-center gap-2 text-purple-600 font-medium">
                                <Contact class="h-4 w-4 text-purple-500" />
                                <span>TSC: {{ teacher.tsc_number }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Building2 class="h-4 w-4" />
                                <span>{{ teacher.department?.name || 'No Department' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center gap-2 md:self-start">
                        <template v-if="!isEditing">
                            <Button variant="outline" size="sm" @click="isEditing = true">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit Profile
                            </Button>
                        </template>
                        <template v-else>
                            <Button variant="outline" size="sm" @click="isEditing = false">
                                <X class="mr-2 h-4 w-4" />
                                Cancel
                            </Button>
                            <Button size="sm" @click="saveChanges" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Save Changes
                            </Button>
                        </template>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-t bg-muted/30 px-6">
                    <div class="flex gap-8 overflow-x-auto scrollbar-hide">
                        <button 
                            v-for="tab in [
                                { id: 'overview', label: 'Overview', icon: Users },
                                { id: 'professional', label: 'Professional', icon: Briefcase },
                                { id: 'assignments', label: 'Assignments', icon: BookOpen },
                                { id: 'qualifications', label: 'Qualifications', icon: GraduationCap },
                            ]" 
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="flex items-center gap-2 border-b-2 py-4 text-sm font-medium transition-colors"
                            :class="activeTab === tab.id ? 'border-purple-600 text-purple-600' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="grid gap-6">
                <!-- Overview Tab -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Quick Stats Card -->
                        <div class="rounded-xl border bg-card p-6 shadow-sm">
                            <h3 class="font-semibold mb-4 flex items-center gap-2">
                                <Users class="h-5 w-5 text-purple-600" />
                                Class Teacher of
                            </h3>
                            <div v-if="teacher.classes_as_teacher.length" class="space-y-3">
                                <div v-for="cls in teacher.classes_as_teacher" :key="cls.id" class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-purple-100 text-purple-700 font-bold">
                                            {{ cls.name[0] }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm">{{ cls.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ cls.grade_level.name }}</p>
                                        </div>
                                    </div>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/classes/${cls.id}`">View Class</Link>
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="text-center py-6 text-muted-foreground text-sm">
                                Not assigned as a class teacher.
                            </div>
                        </div>

                        <!-- Contact Info Card -->
                        <div class="rounded-xl border bg-card p-6 shadow-sm">
                            <h3 class="font-semibold mb-4">Contact Details</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                        <Mail class="h-4 w-4" />
                                    </div>
                                    <div v-if="!isEditing">{{ teacher.email }}</div>
                                    <Input v-else v-model="form.email" class="h-8 py-0" />
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-green-50 text-green-600">
                                        <Phone class="h-4 w-4" />
                                    </div>
                                    <div v-if="!isEditing">{{ teacher.phone }}</div>
                                    <Input v-else v-model="form.phone" class="h-8 py-0" />
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-purple-50 text-purple-600">
                                        <Calendar class="h-4 w-4" />
                                    </div>
                                    <span>Joined {{ teacher.date_joined || 'date unknown' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Tab -->
                <div v-if="activeTab === 'professional'" class="space-y-6">
                    <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
                        <div class="border-b bg-muted/30 px-6 py-4">
                            <h3 class="font-semibold">Employment Details</h3>
                        </div>
                        <div class="p-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">DEPARTMENT</Label>
                                <div v-if="!isEditing" class="font-medium">{{ teacher.department?.name || '—' }}</div>
                                <select v-else v-model="form.department_id" class="h-9 w-full rounded-md border bg-background px-3 text-sm">
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">DESIGNATION</Label>
                                <div v-if="!isEditing" class="font-medium">{{ teacher.staff_designation?.name || '—' }}</div>
                                <select v-else v-model="form.staff_designation_id" class="h-9 w-full rounded-md border bg-background px-3 text-sm">
                                    <option v-for="desig in designations" :key="desig.id" :value="desig.id">{{ desig.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">CONTRACT TYPE</Label>
                                <div v-if="!isEditing" class="font-medium">{{ teacher.contract_type || '—' }}</div>
                                <select v-else v-model="form.contract_type" class="h-9 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Part-time">Part-time</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">BASIC SALARY</Label>
                                <div v-if="!isEditing" class="font-medium">KES {{ teacher.basic_salary?.toLocaleString() || '0' }}</div>
                                <Input v-else v-model="form.basic_salary" type="number" class="h-9" />
                            </div>
                            <div v-if="isEditing" class="space-y-1 md:col-span-2">
                                <Label class="text-xs text-muted-foreground font-semibold">ACCOUNT PASSWORD (LEAVE BLANK TO KEEP CURRENT)</Label>
                                <div class="grid grid-cols-2 gap-4">
                                    <Input v-model="form.password" type="password" placeholder="New Password" class="h-9" />
                                    <Input v-model="form.password_confirmation" type="password" placeholder="Confirm" class="h-9" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
                        <div class="border-b bg-muted/30 px-6 py-4">
                            <h3 class="font-semibold">Personal Backup Information</h3>
                        </div>
                        <div class="p-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">ID / PASSPORT</Label>
                                <div v-if="!isEditing" class="font-medium">{{ teacher.id_number || '—' }}</div>
                                <Input v-else v-model="form.id_number" class="h-9" />
                            </div>
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">GENDER</Label>
                                <div v-if="!isEditing" class="font-medium capitalize">{{ teacher.gender }}</div>
                                <select v-else v-model="form.gender" class="h-9 w-full rounded-md border bg-background px-3 text-sm">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-xs text-muted-foreground font-semibold">DATE OF BIRTH</Label>
                                <div v-if="!isEditing" class="font-medium">{{ teacher.date_of_birth || '—' }}</div>
                                <Input v-else v-model="form.date_of_birth" type="date" class="h-9" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignments Tab -->
                <div v-if="activeTab === 'assignments'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Subject Allocations</h3>
                        <Button size="sm" as-child>
                            <Link href="/classes/allocations">
                                <Plus class="h-4 w-4 mr-2" /> Manage Allocations
                            </Link>
                        </Button>
                    </div>

                    <div v-if="teacher.subject_assignments.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="assignment in teacher.subject_assignments" :key="assignment.id" class="rounded-xl border bg-card p-4 shadow-sm relative overflow-hidden group">
                            <div v-if="assignment.is_primary_teacher" class="absolute -right-6 top-2 rotate-45 bg-purple-600 text-[10px] text-white py-1 px-8 font-bold shadow-sm">
                                PRIMARY
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-purple-100 text-purple-700 font-bold shrink-0">
                                    {{ assignment.subject.name[0] }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold truncate">{{ assignment.subject.name }}</h4>
                                    <p class="text-xs text-muted-foreground">{{ assignment.subject.code }}</p>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-sm">
                                    <Building2 class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ assignment.school_class.name }}</span>
                                </div>
                                <Badge :variant="assignment.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                    {{ assignment.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="rounded-xl border border-dashed bg-muted/10 p-12 text-center">
                        <BookOpen class="h-12 w-12 text-muted-foreground mx-auto mb-4 opacity-30" />
                        <h3 class="font-semibold text-lg text-muted-foreground">No Subjects Allocated</h3>
                        <p class="text-sm text-muted-foreground mt-1 max-w-sm mx-auto italic">
                            This teacher hasn't been assigned any subjects for the current academic year yet.
                        </p>
                    </div>
                </div>

                <div v-if="activeTab === 'qualifications'" class="rounded-xl border border-dashed bg-muted/10 p-12 text-center">
                    <GraduationCap class="h-12 w-12 text-muted-foreground mx-auto mb-4 opacity-30" />
                    <h3 class="font-semibold text-lg text-muted-foreground">Academic Profile</h3>
                    <p class="text-sm text-muted-foreground mt-1 max-w-sm mx-auto">
                        Track certificates, degrees, and professional certifications.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
