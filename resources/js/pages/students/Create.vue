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
    ChevronDown,
    Fingerprint,
    MapPin,
    ShieldCheck,
    Database,
    Zap,
    Activity,
    Camera,
    Plus,
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
    grades: Array<{ id: number; name: string; code: string }>;
    classes: Array<{ id: number; name: string; grade_level_id: number | null }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: 'Ingestion', href: '/students/create' },
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
    return props.classes.filter(c => c.grade_level_id === selectedGradeId);
});

const photoPreview = ref<string | undefined>(undefined);
watch(() => form.photo, (newPhoto) => {
    if (newPhoto) {
        const reader = new FileReader();
        reader.onload = (e) => photoPreview.value = e.target?.result as string;
        reader.readAsDataURL(newPhoto);
    } else photoPreview.value = undefined;
});

const confirmOpen = ref(false);
const successOpen = ref(false);
const bulkUploadOpen = ref(false);

const submit = () => { confirmOpen.value = true; };
const confirmSubmit = () => {
    confirmOpen.value = false;
    form.post('/students', {
        onSuccess: () => { successOpen.value = true; },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Learner Ingestion Hub" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-6">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-12 w-12 rounded-2xl border-border bg-card shadow-sm transition-all hover:bg-muted"
                    >
                        <Link href="/students">
                            <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">
                                Learner ingestion hub
                            </h1>
                            <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2.5 py-0.5 text-[10px] font-bold tracking-widest text-primary uppercase shadow-sm">Registration Active</Badge>
                        </div>
                        <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Establishing new institutional identity node</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="ghost" class="h-12 rounded-2xl px-6 text-[10px] font-bold tracking-widest text-primary uppercase hover:bg-primary/5 mr-2" @click="bulkUploadOpen = true">
                        <Upload class="mr-2.5 h-4 w-4" />
                        Bulk Synchronize
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left Ingestion Node -->
                <div class="space-y-8 lg:col-span-4">
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                         <div class="relative overflow-hidden bg-slate-900 p-10 text-white text-center">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                            <div class="relative z-10 space-y-8">
                                <ProfilePhotoUpload v-model="form.photo" :error="form.errors.photo">
                                     <template #default="{ isUploading }">
                                        <div class="group relative mx-auto h-40 w-40 cursor-pointer overflow-hidden rounded-[2rem] border-4 border-white/10 bg-white/5 shadow-2xl transition-all duration-500 hover:scale-[1.05] active:scale-95">
                                            <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-white/10">
                                                <Camera class="h-12 w-12" />
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Plus class="h-8 w-8 text-white" />
                                                <span class="text-[9px] font-bold tracking-widest text-white uppercase">Capture Biometric</span>
                                            </div>
                                        </div>
                                     </template>
                                </ProfilePhotoUpload>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold tracking-[0.3em] text-white/40 uppercase">Awaiting Identity</h3>
                                    <p class="text-[9px] font-bold tracking-widest text-primary uppercase animate-pulse">Sync ready</p>
                                </div>
                            </div>
                         </div>
                         <div class="p-10 space-y-8">
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Boarding Status *</Label>
                                <div class="relative">
                                    <select v-model="form.boarding_status" class="h-12 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-5 text-xs font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                        <option value="day">Day Protocol</option>
                                        <option value="boarding">Full Boarding</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Operational Status *</Label>
                                <div class="relative">
                                    <select v-model="form.status" class="h-12 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-5 text-xs font-bold tracking-tight uppercase outline-none focus:bg-background" required>
                                        <option value="active">Active Enrollment</option>
                                        <option value="inactive">Dormant Node</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="alumni">Alumni Transfer</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Persistence Node -->
                    <div class="flex items-center justify-between rounded-3xl border border-border bg-card p-6 shadow-sm transition-all hover:bg-muted/10">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Encryption engine</p>
                                <p class="text-xs font-bold tracking-tight text-foreground uppercase">Sync Protocol Alpha</p>
                            </div>
                        </div>
                        <Activity class="h-5 w-5 text-primary opacity-20 animate-pulse" />
                    </div>
                </div>

                <!-- Right Data Stack -->
                <div class="space-y-12 lg:col-span-8">
                    <!-- Identity Node -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                        <div class="border-b border-border/50 bg-muted/10 px-10 py-6 text-sm font-black tracking-widest text-foreground uppercase">Core identity nodes</div>
                        <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">First Identifier *</Label>
                                    <Input v-model="form.first_name" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold uppercase focus:bg-background" />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Middle Identifier</Label>
                                    <Input v-model="form.middle_name" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold uppercase focus:bg-background" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Last Identifier *</Label>
                                    <Input v-model="form.last_name" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold uppercase focus:bg-background" />
                                    <InputError :message="form.errors.last_name" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Assignment -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                         <div class="border-b border-border/50 bg-muted/10 px-10 py-6 text-sm font-black tracking-widest text-foreground uppercase">Academic synchronization</div>
                         <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3 md:col-span-2">
                                     <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Admission Key (NEMIS/Institutional) *</Label>
                                     <Input v-model="form.admission_number" required class="h-14 rounded-2xl border-border bg-slate-100/50 px-6 text-sm font-black tracking-[0.2em] text-primary uppercase focus:bg-background" />
                                     <InputError :message="form.errors.admission_number" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Target Grade *</Label>
                                    <div class="relative">
                                        <select v-model="form.grade_id" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold uppercase outline-none focus:bg-background" required>
                                            <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                    <InputError :message="form.errors.grade_id" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Operational Stream *</Label>
                                    <div class="relative">
                                        <select v-model="form.class_id" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold uppercase outline-none focus:bg-background" :disabled="!form.grade_id" required>
                                            <option v-for="cls in filteredClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Final Action Hub -->
                    <div class="flex flex-wrap items-center justify-end gap-6 pt-6 border-t border-border/50">
                        <Button variant="ghost" class="h-14 rounded-[1.5rem] px-10 text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase hover:bg-muted" as-child>
                            <Link href="/students">Abort Ingestion</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-14 rounded-[1.5rem] bg-primary px-14 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-2xl shadow-primary/30 transition-all hover:scale-[1.05] active:scale-[0.98]">
                            <Loader2 v-if="form.processing" class="mr-3 h-5 w-5 animate-spin" />
                            <ShieldCheck v-else class="mr-3 h-5 w-5" />
                            Commit institutional identity
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Strategic Dialogs -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
             <DialogContent class="sm:max-w-[480px] rounded-[2.5rem] border-border bg-card p-0 overflow-hidden shadow-2xl">
                <div class="p-10 text-center">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-primary/10 text-primary shadow-sm shadow-primary/10">
                        <Fingerprint class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-foreground uppercase mb-4">Confirm Ingestion</h3>
                    <p class="text-sm font-bold text-muted-foreground leading-relaxed uppercase opacity-60 tracking-wider">Synchronizing a new learner node will establish a permanent record in the institutional matrix.</p>
                </div>
                <div class="flex items-center justify-between gap-4 bg-muted/10 p-8 border-t border-border/50">
                    <Button variant="ghost" @click="confirmOpen = false" class="h-12 rounded-2xl px-8 text-[10px] font-bold tracking-widest uppercase">Abort</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="h-12 rounded-2xl bg-primary px-10 text-[10px] font-bold tracking-widest text-white uppercase shadow-lg shadow-primary/20">Commit Sync</Button>
                </div>
            </DialogContent>
        </Dialog>

        <BulkUploadModal :open="bulkUploadOpen" @close="bulkUploadOpen = false" />
    </AppLayout>
</template>
