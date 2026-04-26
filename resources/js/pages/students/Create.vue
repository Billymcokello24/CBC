<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Download,
    GraduationCap,
    Loader2,
    Save,
    Upload,
    UserPlus,
    AlertTriangle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import BulkUploadModal from './partials/BulkUploadModal.vue';
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grades: Array<{
        id: number;
        name: string;
        code: string;
        level_order: number;
    }>;
    classes: Array<{
        id: number;
        name: string;
        grade_level_id: number | null;
        grade_name: string | null;
        stream_name: string | null;
    }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learners', href: '/students' },
    { title: 'Add Learner', href: '/students/create' },
];

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    admission_number: '',
    gender: 'male',
    date_of_birth: '',
    grade_id: '',
    class_id: '',
    county: '',
    boarding_status: 'day',
    status: 'active',
    photo: null as File | null,
});

const filteredClasses = computed(() => {
    if (!form.grade_id) return [];
    const selectedGradeId = Number(form.grade_id);

    return props.classes.filter(
        (schoolClass) => schoolClass.grade_level_id === selectedGradeId,
    );
});

watch(
    () => form.grade_id,
    () => {
        if (
            !filteredClasses.value.some(
                (schoolClass) => String(schoolClass.id) === form.class_id,
            )
        ) {
            form.class_id = '';
        }
    },
);

const confirmOpen = ref(false);
const successOpen = ref(false);
const bulkUploadOpen = ref(false);
const createdLearnerId = ref<number | null>(null);

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.transform((data) => ({
        ...data,
        class_id: data.class_id ? Number(data.class_id) : null,
    })).post('/students', {
        onSuccess: (page) => {
            const flash = (page.props as any).flash;
            if (flash?.success) {
                successOpen.value = true;
            }
        },
    });
};

const resetForm = () => {
    form.reset();
    successOpen.value = false;
};

const bulkForm = useForm<{ file: File | null }>({ file: null });
const bulkFileName = ref('');

const handleBulkFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    bulkForm.file = file;
    bulkFileName.value = file?.name ?? '';
};

const uploadBulkLearners = () => {
    bulkForm.post('/students/bulk-upload', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Learner" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-6 duration-700 fade-in"
        >
            <!-- Header section -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-start gap-4 sm:items-center sm:gap-5">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-10 w-10 shrink-0 rounded-xl border-slate-200 bg-white shadow-sm transition-all hover:bg-slate-50 sm:h-11 sm:w-11 sm:rounded-2xl"
                    >
                        <Link href="/students"
                            ><ArrowLeft class="h-5 w-5 text-slate-400"
                        /></Link>
                    </Button>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                Enrollment
                            </h1>
                            <Badge
                                variant="outline"
                                class="rounded-lg border-blue-100 bg-blue-50 px-2 py-0.5 text-xs font-medium tracking-tight text-blue-600 uppercase sm:text-xs"
                            >
                                Registration
                            </Badge>
                        </div>
                        <p
                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase sm:text-xs"
                        >
                            Establish New Learner Identity
                        </p>
                    </div>
                </div>

                <div
                    class="flex w-full items-center gap-2 rounded-2xl border border-slate-100/50 bg-slate-50/50 p-1.5 sm:gap-3 md:w-auto"
                >
                    <div
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm md:flex-none"
                    >
                        <div
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white"
                        >
                            1
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >Entry</span
                        >
                    </div>
                    <div
                        class="flex flex-1 items-center justify-center gap-2 px-3 py-2 opacity-40 md:flex-none"
                    >
                        <div
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-500"
                        >
                            2
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Review</span
                        >
                    </div>
                </div>
            </div>

            <!-- Bulk Hub -->
            <div
                class="group relative overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all hover:border-blue-100 sm:p-8"
            >
                <div
                    class="absolute -right-10 -bottom-10 text-blue-600 opacity-5 transition-transform duration-1000 group-hover:scale-110"
                >
                    <Upload class="h-48 w-48" />
                </div>
                <div
                    class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-start gap-5 sm:items-center">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-blue-100/50 bg-blue-50 text-blue-600 shadow-inner sm:h-14 sm:w-14"
                        >
                            <Upload class="h-6 w-6 sm:h-7 sm:w-7" />
                        </div>
                        <div>
                            <h2
                                class="flex items-center gap-2 text-lg font-bold text-slate-900"
                            >
                                Bulk Ingest
                                <Badge
                                    variant="secondary"
                                    class="border-emerald-100/50 bg-emerald-50 text-xs font-medium tracking-tight text-emerald-600 uppercase"
                                    >High Load</Badge
                                >
                            </h2>
                            <p
                                class="mt-1.5 max-w-sm text-xs leading-relaxed font-bold tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                            >
                                Modernize enrollment through CSV serialization
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex w-full flex-col items-center gap-3 sm:flex-row lg:w-auto"
                    >
                        <Button
                            variant="outline"
                            class="h-12 w-full rounded-xl border-slate-200 bg-white px-6 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:w-auto"
                            as-child
                        >
                            <a href="/students/template/download">
                                <Download
                                    class="mr-2 h-4 w-4 text-emerald-500"
                                />
                                Template
                            </a>
                        </Button>
                        <Button
                            class="h-12 w-full rounded-xl border-0 bg-slate-900 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 sm:w-auto"
                            @click="bulkUploadOpen = true"
                        >
                            <Upload class="mr-2 h-4 w-4" />
                            Upload Data
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 pb-12 sm:gap-8">
                <!-- Data Forms Split -->
                <div class="grid gap-6 sm:gap-8 lg:grid-cols-12">
                    <!-- Left: Core Info -->
                    <div class="space-y-6 sm:gap-8 lg:col-span-8">
                        <div
                            class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100"
                        >
                            <div
                                class="flex flex-col justify-between gap-4 border-b border-slate-50 bg-slate-50/30 px-6 py-5 sm:flex-row sm:items-center sm:px-8"
                            >
                                <div>
                                    <h2
                                        class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900"
                                    >
                                        <GraduationCap
                                            class="h-5 w-5 text-blue-600"
                                        />
                                        Biometric Identity
                                    </h2>
                                    <p
                                        class="mt-1 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase opacity-80"
                                    >
                                        Primary Demographic Payload
                                    </p>
                                </div>
                                <div
                                    class="flex items-center gap-4 self-end sm:self-auto"
                                >
                                    <ProfilePhotoUpload
                                        v-model="form.photo"
                                        :error="form.errors.photo"
                                    />
                                    <Badge
                                        variant="outline"
                                        class="h-5 border-slate-200 bg-slate-50 px-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Required</Badge
                                    >
                                </div>
                            </div>
                            <div class="p-6 sm:p-8">
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label
                                            for="first_name"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Legal First Name</Label
                                        >
                                        <Input
                                            id="first_name"
                                            v-model="form.first_name"
                                            placeholder="E.G. JOHN"
                                            class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.first_name"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label
                                            for="last_name"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Legal Last Name</Label
                                        >
                                        <Input
                                            id="last_name"
                                            v-model="form.last_name"
                                            placeholder="E.G. MWANGI"
                                            class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.last_name"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label
                                            for="admission_number"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Registry Identifier</Label
                                        >
                                        <Input
                                            id="admission_number"
                                            v-model="form.admission_number"
                                            placeholder="ADM-001"
                                            class="h-12 rounded-xl border-slate-200 bg-blue-50/30 px-4 text-sm font-bold tracking-tight text-blue-600 uppercase transition-all focus:bg-white"
                                            required
                                        />
                                        <InputError
                                            :message="
                                                form.errors.admission_number
                                            "
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label
                                            for="gender"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Gender Bias</Label
                                        >
                                        <div class="relative">
                                            <select
                                                id="gender"
                                                v-model="form.gender"
                                                class="h-12 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                            >
                                                <option value="male">
                                                    Male
                                                </option>
                                                <option value="female">
                                                    Female
                                                </option>
                                                <option value="other">
                                                    Other
                                                </option>
                                            </select>
                                            <ChevronDown
                                                class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                            />
                                        </div>
                                        <InputError
                                            :message="form.errors.gender"
                                        />
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <Label
                                            for="date_of_birth"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Birth Epoch</Label
                                        >
                                        <Input
                                            id="date_of_birth"
                                            v-model="form.date_of_birth"
                                            type="date"
                                            class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm font-bold tracking-tight uppercase transition-all focus:bg-white"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.date_of_birth"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Placement -->
                    <div class="space-y-6 sm:space-y-8 lg:col-span-4">
                        <div
                            class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all hover:border-blue-100"
                        >
                            <div
                                class="border-b border-slate-50 bg-slate-50/30 px-6 py-5"
                            >
                                <h2
                                    class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900"
                                >
                                    <GraduationCap
                                        class="h-5 w-5 text-blue-600"
                                    />
                                    Placement Logic
                                </h2>
                            </div>
                            <div class="space-y-5 p-6">
                                <div class="space-y-2">
                                    <Label
                                        for="grade_id"
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Academic Level</Label
                                    >
                                    <div class="relative">
                                        <select
                                            id="grade_id"
                                            v-model="form.grade_id"
                                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option value="">
                                                Select Level
                                            </option>
                                            <option
                                                v-for="grade in grades"
                                                :key="grade.id"
                                                :value="String(grade.id)"
                                            >
                                                {{ grade.name }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="class_id"
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Stream Context</Label
                                    >
                                    <div class="relative">
                                        <select
                                            id="class_id"
                                            v-model="form.class_id"
                                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5 disabled:opacity-40"
                                            :disabled="!form.grade_id"
                                        >
                                            <option value="">
                                                {{
                                                    form.grade_id
                                                        ? 'Select Stream'
                                                        : 'Level Required'
                                                }}
                                            </option>
                                            <option
                                                v-for="schoolClass in filteredClasses"
                                                :key="schoolClass.id"
                                                :value="String(schoolClass.id)"
                                            >
                                                {{ schoolClass.name
                                                }}{{
                                                    schoolClass.stream_name
                                                        ? ` • ${schoolClass.stream_name}`
                                                        : ''
                                                }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.class_id"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="county"
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Regional Origin</Label
                                    >
                                    <div class="relative">
                                        <select
                                            id="county"
                                            v-model="form.county"
                                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
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
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError :message="form.errors.county" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="boarding_status"
                                        class="ml-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Residency</Label
                                    >
                                    <div class="relative">
                                        <select
                                            id="boarding_status"
                                            v-model="form.boarding_status"
                                            class="h-11 w-full cursor-pointer appearance-none rounded-xl border-slate-200 bg-white px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-600/5"
                                        >
                                            <option value="day">
                                                Day Scholar
                                            </option>
                                            <option value="boarding">
                                                Boarding Hub
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.boarding_status"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="group relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 p-6 text-white shadow-xl shadow-slate-200"
                        >
                            <div
                                class="absolute -top-4 -right-4 opacity-5 transition-transform duration-700 group-hover:scale-110"
                            >
                                <AlertTriangle class="h-20 w-20 text-white" />
                            </div>
                            <h4
                                class="mb-4 text-xs font-bold tracking-tight text-blue-500 uppercase"
                            >
                                Validation Guard
                            </h4>
                            <p
                                class="text-xs leading-relaxed font-bold text-slate-400"
                            >
                                Global uniqueness required for ADM-ID sequences.
                                Verify biometric consistency before ingestion.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Hub -->
                <div
                    class="flex flex-col items-center justify-between gap-6 border-t border-slate-100 pt-10 sm:flex-row"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-12 w-full rounded-xl px-8 text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900 sm:w-auto"
                        as-child
                    >
                        <Link href="/students">Abort Protocol</Link>
                    </Button>
                    <div class="flex w-full gap-4 sm:w-auto">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-12 flex-1 rounded-xl border-0 bg-blue-600 px-10 text-sm font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700 sm:flex-none"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Commit Payload
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent
                class="max-w-lg overflow-hidden rounded-xl border-0 bg-card p-0 shadow-lg"
            >
                <div class="space-y-6 p-10 text-center">
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl border-4 border-white bg-amber-50 shadow-sm"
                    >
                        <AlertTriangle class="h-8 w-8 text-amber-500" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-foreground">
                            Review Registration
                        </h3>
                        <p
                            class="mt-2 text-sm font-medium text-muted-foreground"
                        >
                            Please confirm the details for
                            <span
                                class="font-bold text-indigo-600 underline decoration-indigo-200 underline-offset-4"
                                >{{ form.first_name }}
                                {{ form.last_name }}</span
                            >
                            before finalizing.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 py-2">
                        <div
                            class="rounded-2xl border border-border/50 bg-muted/30 p-4"
                        >
                            <p
                                class="mb-1 text-xs leading-none font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Admission No
                            </p>
                            <p
                                class="text-sm font-bold tracking-tight text-foreground"
                            >
                                {{ form.admission_number || 'PENDING' }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/50 bg-muted/30 p-4"
                        >
                            <p
                                class="mb-1 text-xs leading-none font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Grade Level
                            </p>
                            <p
                                class="truncate text-sm font-bold text-foreground"
                            >
                                {{
                                    grades.find(
                                        (g) => String(g.id) === form.grade_id,
                                    )?.name || 'NONE'
                                }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="flex gap-4 border-t border-border/50 bg-muted/20 p-5"
                >
                    <Button
                        variant="ghost"
                        class="h-12 flex-1 rounded-xl text-sm font-bold"
                        @click="confirmOpen = false"
                        >Edit Details</Button
                    >
                    <Button
                        @click="confirmSubmit"
                        :disabled="form.processing"
                        class="h-12 flex-1 rounded-xl border-0 bg-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Confirm Enrollment
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent
                class="max-w-md overflow-hidden rounded-xl border-0 bg-card p-0 shadow-lg"
            >
                <div class="space-y-6 p-10 text-center">
                    <div
                        class="mx-auto flex h-24 w-24 rotate-3 items-center justify-center rounded-xl border-8 border-white bg-emerald-50 shadow-lg"
                    >
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                    </div>
                    <div>
                        <h3
                            class="text-2xl font-bold tracking-tight text-foreground"
                        >
                            Enrollment Successful
                        </h3>
                        <p
                            class="mt-2 text-sm font-medium text-muted-foreground"
                        >
                            <span class="font-bold text-emerald-600"
                                >{{ form.first_name }}
                                {{ form.last_name }}</span
                            >
                            has been successfully registered in the system.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 bg-slate-900 p-6">
                    <Button
                        variant="outline"
                        @click="resetForm"
                        class="h-12 w-full rounded-xl border-white/20 bg-white/10 text-sm font-bold text-white transition-all hover:bg-white/20"
                    >
                        <UserPlus class="mr-2 h-4 w-4 text-emerald-400" />
                        Enroll Another Learner
                    </Button>
                    <Button
                        as-child
                        class="h-12 w-full rounded-xl border-0 bg-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-900/40 transition-all hover:bg-indigo-700"
                    >
                        <Link href="/students">
                            <GraduationCap class="mr-2 h-4 w-4" />
                            View Learner Directory
                        </Link>
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <BulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>
