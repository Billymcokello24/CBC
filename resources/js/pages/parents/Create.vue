<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, CheckCircle2, Users, Loader2, 
    Save, UserPlus, AlertTriangle, User, Key, 
    Fingerprint, Mail, Phone, MapPin, Search, Plus
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    students: Array<{ id: number; first_name: string; last_name: string; admission_number: string }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parent Registry', href: '/parents' },
    { title: 'Register Parent', href: '/parents/create' },
];

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    phone: '',
    alternate_phone: '',
    gender: 'male',
    id_number: '',
    occupation: '',
    employer: '',
    work_address: '',
    home_address: '',
    county: '',
    sub_county: '',
    relationship_type: 'Father',
    student_ids: [] as number[],
    password: '',
    password_confirmation: '',
});

const studentSearch = ref('');
const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students.slice(0, 10);
    const search = studentSearch.value.toLowerCase();
    return props.students.filter(s => 
        s.first_name.toLowerCase().includes(search) || 
        s.last_name.toLowerCase().includes(search) || 
        s.admission_number.toLowerCase().includes(search)
    ).slice(0, 10);
});

const toggleStudent = (id: number, checked: boolean) => {
    if (checked) {
        if (!form.student_ids.includes(id)) form.student_ids.push(id);
    } else {
        const index = form.student_ids.indexOf(id);
        if (index > -1) form.student_ids.splice(index, 1);
    }
};

const confirmOpen = ref(false);
const successOpen = ref(false);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.post('/parents', {
        onSuccess: () => {
            successOpen.value = true;
        },
    });
};

const resetForm = () => {
    form.reset();
    successOpen.value = false;
};

const selectedStudentsCount = computed(() => form.student_ids.length);
</script>

<template>
    <Head title="Register Parent" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 max-w-[1600px] mx-auto animate-in fade-in duration-700">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="outline" size="icon" as-child class="h-11 w-11 rounded-2xl border-border bg-card shadow-sm hover:bg-muted transition-all">
                        <Link href="/parents"><ArrowLeft class="h-5 w-5 text-muted-foreground" /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-foreground">Register Parent</h1>
                            <Badge variant="outline" class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border-blue-100">
                                Institutional Parent
                            </Badge>
                        </div>
                        <p class="text-sm font-medium text-muted-foreground mt-1 text-blue-600/80 italic font-inter italic tracking-tight">Onboard a new guardian account linked to their respective learners.</p>
                    </div>
                </div>
            </div>

            <!-- Form section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Form Details -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Personal Details -->
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md group">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                                <User class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">Personal Information</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">First Name</Label>
                                <Input v-model="form.first_name" placeholder="First Name" class="h-11 rounded-xl bg-muted/20 border-border focus:ring-2 focus:ring-indigo-600/10" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Middle Name</Label>
                                <Input v-model="form.middle_name" placeholder="Middle Name" class="h-11 rounded-xl bg-muted/20 border-border focus:ring-2 focus:ring-indigo-600/10" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Last Name</Label>
                                <Input v-model="form.last_name" placeholder="Last Name" class="h-11 rounded-xl bg-muted/20 border-border focus:ring-2 focus:ring-indigo-600/10" />
                                <InputError :message="form.errors.last_name" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Gender</Label>
                                <Select v-model="form.gender">
                                    <SelectTrigger class="h-11 rounded-xl bg-muted/20 border-border">
                                        <SelectValue placeholder="Select Gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">Female</SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.gender" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">ID Number</Label>
                                <Input v-model="form.id_number" placeholder="Enter ID number" class="h-11 rounded-xl bg-muted/20 border-border focus:ring-2 focus:ring-indigo-600/10" />
                                <InputError :message="form.errors.id_number" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Email Address (Login)</Label>
                                <div class="relative">
                                    <Mail class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input v-model="form.email" type="email" placeholder="email@example.com" class="pl-10 h-11 rounded-xl bg-muted/20 border-border focus:ring-2 focus:ring-indigo-600/10" />
                                </div>
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Location -->
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md group">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                                <Phone class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">Contact & Location</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Primary Phone</Label>
                                <Input v-model="form.phone" placeholder="Enter phone" class="h-11 rounded-xl bg-muted/20 border-border" />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Alternate Phone</Label>
                                <Input v-model="form.alternate_phone" placeholder="Optional phone" class="h-11 rounded-xl bg-muted/20 border-border" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">County</Label>
                                <Select v-model="form.county">
                                    <SelectTrigger class="h-11 rounded-xl bg-muted/20 border-border">
                                        <SelectValue placeholder="Select County" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="county in counties" :key="county" :value="county">{{ county }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2 lg:col-span-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Home Address</Label>
                                <div class="relative">
                                    <MapPin class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input v-model="form.home_address" placeholder="Physical location details..." class="pl-10 h-11 rounded-xl bg-muted/20 border-border" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Background -->
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md group">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="h-10 w-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 group-hover:scale-110 transition-transform">
                                <Users class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">Professional & Profile</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Occupation</Label>
                                <Input v-model="form.occupation" placeholder="Enter occupation" class="h-11 rounded-xl bg-muted/20 border-border" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Relationship Type</Label>
                                <Select v-model="form.relationship_type">
                                    <SelectTrigger class="h-11 rounded-xl bg-muted/20 border-border">
                                        <SelectValue placeholder="Select Type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Father">Father</SelectItem>
                                        <SelectItem value="Mother">Mother</SelectItem>
                                        <SelectItem value="Guardian">Guardian</SelectItem>
                                        <SelectItem value="Relative">Relative</SelectItem>
                                        <SelectItem value="Sponsor">Sponsor</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Student Linkage & Security -->
                <div class="space-y-8">
                    <!-- Student Selection -->
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md group flex flex-col h-[400px]">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                    <Plus class="h-5 w-5" />
                                </div>
                                <h2 class="text-xl font-bold text-foreground">Link Learners</h2>
                            </div>
                            <Badge class="bg-blue-600 text-white font-bold">{{ selectedStudentsCount }} Selected</Badge>
                        </div>
                        
                        <div class="relative mb-4">
                            <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input v-model="studentSearch" placeholder="Search learners..." class="pl-10 h-11 rounded-xl bg-muted/20 border-border" />
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                            <div 
                                v-for="student in filteredStudents" 
                                :key="student.id" 
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-muted/50 transition-all border border-transparent hover:border-border cursor-pointer"
                                @click="toggleStudent(student.id, !form.student_ids.includes(student.id))"
                            >
                                <Checkbox 
                                    :id="'student-' + student.id" 
                                    :checked="form.student_ids.includes(student.id)" 
                                    @update:checked="(checked: boolean) => toggleStudent(student.id, checked)"
                                    class="rounded-lg h-5 w-5"
                                />
                                <div class="flex flex-col">
                                    <p class="text-xs font-bold text-foreground">
                                        {{ student.first_name }} {{ student.last_name }}
                                    </p>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">{{ student.admission_number }}</p>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.student_ids" class="mt-2" />
                    </div>

                    <!-- Security & Login -->
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md group">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="h-10 w-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                                <Key class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">Security Settings</h2>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Account Password</Label>
                                <Input v-model="form.password" type="password" placeholder="Min 8 characters" class="h-11 rounded-xl bg-muted/20 border-border" />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Confirm Password</Label>
                                <Input v-model="form.password_confirmation" type="password" placeholder="Confirm password" class="h-11 rounded-xl bg-muted/20 border-border" />
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="space-y-3 pt-6">
                        <Button
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full h-14 rounded-2xl bg-blue-600 text-white font-bold text-base shadow-xl shadow-indigo-600/20 hover:bg-blue-700 hover:-translate-y-0.5 transition-all"
                        >
                            <Loader2 v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                            <Save v-else class="mr-2 h-5 w-5" />
                            Complete Registration
                        </Button>
                        <Button
                            variant="ghost"
                            as-child
                            class="w-full h-12 rounded-2xl text-muted-foreground font-bold hover:bg-muted"
                        >
                            <Link href="/parents">Discard & Return</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Dialog -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="rounded-3xl border-border px-8 py-8 w-[400px]">
                <DialogHeader class="items-center text-center space-y-4">
                    <div class="h-16 w-16 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                        <UserPlus class="h-8 w-8" />
                    </div>
                    <DialogTitle class="text-2xl font-black">Register Parent?</DialogTitle>
                    <DialogDescription class="text-sm font-medium leading-relaxed">
                        Are you sure you want to register <span class="text-foreground font-bold">{{ form.first_name }} {{ form.last_name }}</span> as a parent account?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-8 flex-col sm:flex-row gap-3">
                    <Button variant="outline" class="flex-1 rounded-2xl h-11 border-border font-bold" @click="confirmOpen = false">Cancel</Button>
                    <Button class="flex-1 rounded-2xl h-11 bg-blue-600 text-white font-bold hover:bg-blue-700" @click="confirmSubmit">Yes, Register</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Success Dialog -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="rounded-3xl border-border px-8 py-8 w-[400px]">
                <DialogHeader class="items-center text-center space-y-4">
                    <div class="h-20 w-20 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <CheckCircle2 class="h-10 w-10" />
                    </div>
                    <DialogTitle class="text-2xl font-black text-foreground">Account Created!</DialogTitle>
                    <DialogDescription class="text-sm font-medium leading-relaxed">
                        The parent account has been successfully registered and linked to the selected students.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-8 flex-col gap-3">
                    <Button class="w-full rounded-2xl h-12 bg-blue-600 text-white font-black text-sm uppercase tracking-widest shadow-lg shadow-blue-500/30 hover:bg-blue-700" @click="resetForm">
                        Onboard Another Parent
                    </Button>
                    <Button variant="ghost" as-child class="w-full rounded-2xl h-12 font-bold text-muted-foreground">
                        <Link href="/parents">View Registry</Link>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
