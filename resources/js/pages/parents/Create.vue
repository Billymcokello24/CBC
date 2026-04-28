<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Users,
    Loader2,
    Save,
    UserPlus,
    AlertTriangle,
    User,
    Key,
    Fingerprint,
    Mail,
    Phone,
    MapPin,
    Search,
    Plus,
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
    students: Array<{
        id: number;
        first_name: string;
        last_name: string;
        admission_number: string;
    }>;
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
});

const studentSearch = ref('');
const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students.slice(0, 10);
    const search = studentSearch.value.toLowerCase();
    return props.students
        .filter(
            (s) =>
                s.first_name.toLowerCase().includes(search) ||
                s.last_name.toLowerCase().includes(search) ||
                s.admission_number.toLowerCase().includes(search),
        )
        .slice(0, 10);
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
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-6 duration-700 fade-in"
        >
            <!-- Header section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-5">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-11 w-11 rounded-2xl border-border bg-card shadow-sm transition-all hover:bg-muted"
                    >
                        <Link href="/parents"
                            ><ArrowLeft class="h-5 w-5 text-muted-foreground"
                        /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1
                                class="text-3xl font-bold tracking-tight text-foreground"
                            >
                                Register Parent
                            </h1>
                            <Badge
                                variant="outline"
                                class="rounded-lg border-blue-100 bg-blue-50 px-2.5 py-0.5 text-xs font-bold tracking-wider text-blue-600 uppercase"
                            >
                                Institutional Parent
                            </Badge>
                        </div>
                        <p
                            class="font-inter mt-1 text-sm font-medium tracking-tight text-blue-600/80 text-muted-foreground"
                        >
                            Onboard a new guardian account linked to their
                            respective learners.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form section -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Left Column: Form Details -->
                <div class="space-y-8 lg:col-span-2">
                    <!-- Personal Details -->
                    <div
                        class="group rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-8 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-transform group-hover:scale-110"
                            >
                                <User class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">
                                Personal Information
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >First Name</Label
                                >
                                <Input
                                    v-model="form.first_name"
                                    placeholder="First Name"
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Middle Name</Label
                                >
                                <Input
                                    v-model="form.middle_name"
                                    placeholder="Middle Name"
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError
                                    :message="form.errors.middle_name"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Last Name</Label
                                >
                                <Input
                                    v-model="form.last_name"
                                    placeholder="Last Name"
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError :message="form.errors.last_name" />
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Gender</Label
                                >
                                <Select v-model="form.gender">
                                    <SelectTrigger
                                        class="h-11 rounded-xl border-border bg-muted/20"
                                    >
                                        <SelectValue
                                            placeholder="Select Gender"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="male"
                                            >Male</SelectItem
                                        >
                                        <SelectItem value="female"
                                            >Female</SelectItem
                                        >
                                        <SelectItem value="other"
                                            >Other</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.gender" />
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >ID Number</Label
                                >
                                <Input
                                    v-model="form.id_number"
                                    placeholder="Enter ID number"
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:ring-2 focus:ring-indigo-600/10"
                                />
                                <InputError :message="form.errors.id_number" />
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Email Address (Login)</Label
                                >
                                <div class="relative">
                                    <Mail
                                        class="absolute top-3 left-3 h-4 w-4 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="email@example.com"
                                        class="h-11 rounded-xl border-border bg-muted/20 pl-10 focus:ring-2 focus:ring-indigo-600/10"
                                    />
                                </div>
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Location -->
                    <div
                        class="group rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-8 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-transform group-hover:scale-110"
                            >
                                <Phone class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">
                                Contact & Location
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Primary Phone</Label
                                >
                                <Input
                                    v-model="form.phone"
                                    placeholder="Enter phone"
                                    class="h-11 rounded-xl border-border bg-muted/20"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Alternate Phone</Label
                                >
                                <Input
                                    v-model="form.alternate_phone"
                                    placeholder="Optional phone"
                                    class="h-11 rounded-xl border-border bg-muted/20"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >County</Label
                                >
                                <Select v-model="form.county">
                                    <SelectTrigger
                                        class="h-11 rounded-xl border-border bg-muted/20"
                                    >
                                        <SelectValue
                                            placeholder="Select County"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="county in counties"
                                            :key="county"
                                            :value="county"
                                            >{{ county }}</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2 lg:col-span-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Home Address</Label
                                >
                                <div class="relative">
                                    <MapPin
                                        class="absolute top-3 left-3 h-4 w-4 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="form.home_address"
                                        placeholder="Physical location details..."
                                        class="h-11 rounded-xl border-border bg-muted/20 pl-10"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Background -->
                    <div
                        class="group rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-8 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600 transition-transform group-hover:scale-110"
                            >
                                <Users class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">
                                Professional & Profile
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Occupation</Label
                                >
                                <Input
                                    v-model="form.occupation"
                                    placeholder="Enter occupation"
                                    class="h-11 rounded-xl border-border bg-muted/20"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                    >Relationship Type</Label
                                >
                                <Select v-model="form.relationship_type">
                                    <SelectTrigger
                                        class="h-11 rounded-xl border-border bg-muted/20"
                                    >
                                        <SelectValue
                                            placeholder="Select Type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Father"
                                            >Father</SelectItem
                                        >
                                        <SelectItem value="Mother"
                                            >Mother</SelectItem
                                        >
                                        <SelectItem value="Guardian"
                                            >Guardian</SelectItem
                                        >
                                        <SelectItem value="Relative"
                                            >Relative</SelectItem
                                        >
                                        <SelectItem value="Sponsor"
                                            >Sponsor</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Student Linkage & Security -->
                <div class="space-y-8">
                    <!-- Student Selection -->
                    <div
                        class="group flex h-[400px] flex-col rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                >
                                    <Plus class="h-5 w-5" />
                                </div>
                                <h2 class="text-xl font-bold text-foreground">
                                    Link Learners
                                </h2>
                            </div>
                            <Badge class="bg-blue-600 font-bold text-white"
                                >{{ selectedStudentsCount }} Selected</Badge
                            >
                        </div>

                        <div class="relative mb-4">
                            <Search
                                class="absolute top-3 left-3 h-4 w-4 text-muted-foreground"
                            />
                            <Input
                                v-model="studentSearch"
                                placeholder="Search learners..."
                                class="h-11 rounded-xl border-border bg-muted/20 pl-10"
                            />
                        </div>

                        <div
                            class="custom-scrollbar flex-1 space-y-2 overflow-y-auto pr-2"
                        >
                            <div
                                v-for="student in filteredStudents"
                                :key="student.id"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border border-transparent p-3 transition-all hover:border-border hover:bg-muted/50"
                                @click="
                                    toggleStudent(
                                        student.id,
                                        !form.student_ids.includes(student.id),
                                    )
                                "
                            >
                                <Checkbox
                                    :id="'student-' + student.id"
                                    :checked="
                                        form.student_ids.includes(student.id)
                                    "
                                    @update:checked="
                                        (checked: boolean) =>
                                            toggleStudent(student.id, checked)
                                    "
                                    class="h-5 w-5 rounded-lg"
                                />
                                <div class="flex flex-col">
                                    <p
                                        class="text-xs font-bold text-foreground"
                                    >
                                        {{ student.first_name }}
                                        {{ student.last_name }}
                                    </p>
                                    <p
                                        class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >
                                        {{ student.admission_number }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <InputError
                            :message="form.errors.student_ids"
                            class="mt-2"
                        />
                    </div>

                    <!-- Security & Login -->
                    <div
                        class="group rounded-2xl border border-border bg-card p-8 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-8 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 transition-transform group-hover:scale-110"
                            >
                                <Key class="h-5 w-5" />
                            </div>
                            <h2 class="text-xl font-bold text-foreground">
                                Security Settings
                            </h2>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start gap-4 rounded-xl bg-indigo-50/50 p-4 border border-indigo-100">
                                <ShieldCheck class="h-5 w-5 text-indigo-600 shrink-0 mt-0.5" />
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-indigo-700 uppercase">Auto-Generated Password</p>
                                    <p class="text-[10px] font-medium text-slate-600 leading-relaxed">
                                        A secure password will be automatically generated and sent to the parent's email. 
                                        They will be required to reset it on their first login.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="space-y-3 pt-6">
                        <Button
                            @click="submit"
                            :disabled="form.processing"
                            class="h-14 w-full rounded-2xl bg-blue-600 text-base font-bold text-white shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-0.5 hover:bg-blue-700"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-5 w-5 animate-spin"
                            />
                            <Save v-else class="mr-2 h-5 w-5" />
                            Complete Registration
                        </Button>
                        <Button
                            variant="ghost"
                            as-child
                            class="h-12 w-full rounded-2xl font-bold text-muted-foreground hover:bg-muted"
                        >
                            <Link href="/parents">Discard & Return</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Dialog -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent
                class="w-[400px] rounded-3xl border-border px-8 py-8"
            >
                <DialogHeader class="items-center space-y-4 text-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                    >
                        <UserPlus class="h-8 w-8" />
                    </div>
                    <DialogTitle class="text-2xl font-bold"
                        >Register Parent?</DialogTitle
                    >
                    <DialogDescription
                        class="text-sm leading-relaxed font-medium"
                    >
                        Are you sure you want to register
                        <span class="font-bold text-foreground"
                            >{{ form.first_name }} {{ form.last_name }}</span
                        >
                        as a parent account?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-8 flex-col gap-3 sm:flex-row">
                    <Button
                        variant="outline"
                        class="h-11 flex-1 rounded-2xl border-border font-bold"
                        @click="confirmOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        class="h-11 flex-1 rounded-2xl bg-blue-600 font-bold text-white hover:bg-blue-700"
                        @click="confirmSubmit"
                        >Yes, Register</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Success Dialog -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent
                class="w-[400px] rounded-3xl border-border px-8 py-8"
            >
                <DialogHeader class="items-center space-y-4 text-center">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                    >
                        <CheckCircle2 class="h-10 w-10" />
                    </div>
                    <DialogTitle class="text-2xl font-bold text-foreground"
                        >Account Created!</DialogTitle
                    >
                    <DialogDescription
                        class="text-sm leading-relaxed font-medium"
                    >
                        The parent account has been successfully registered and
                        linked to the selected students.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-8 flex-col gap-3">
                    <Button
                        class="h-12 w-full rounded-2xl bg-blue-600 text-sm font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-500/30 hover:bg-blue-700"
                        @click="resetForm"
                    >
                        Onboard Another Parent
                    </Button>
                    <Button
                        variant="ghost"
                        as-child
                        class="h-12 w-full rounded-2xl font-bold text-muted-foreground"
                    >
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
