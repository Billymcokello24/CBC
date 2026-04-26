<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    GraduationCap,
    Loader2,
    Save,
    UserRound,
    Calendar,
    ChevronDown,
    Fingerprint,
    MapPin,
    Activity,
    Database,
    AlertTriangle,
    ShieldCheck,
    Zap,
    Camera,
    Plus,
} from 'lucide-vue-next';
import { computed, watch, ref } from 'vue';
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
import ProfilePhotoUpload from '@/components/forms/ProfilePhotoUpload.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learner: any;
    grades: any[];
    classes: any[];
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: props.learner.name, href: `/students/${props.learner.id}` },
    { title: 'Profile Mutation', href: `/students/${props.learner.id}/edit` },
];

const form = useForm({
    first_name: props.learner.first_name,
    middle_name: props.learner.middle_name ?? '',
    last_name: props.learner.last_name,
    admission_number: props.learner.admission_number ?? '',
    gender: props.learner.gender,
    date_of_birth: props.learner.date_of_birth ?? '',
    grade_id: props.learner.grade_id ? String(props.learner.grade_id) : '',
    class_id: props.learner.current_class_id ? String(props.learner.current_class_id) : '',
    county: props.learner.county ?? '',
    boarding_status: props.learner.boarding_status ?? 'day',
    status: props.learner.status,
    photo: null as File | null,
});

const photoPreview = ref<string | undefined>(props.learner.photo_url);
watch(() => form.photo, (newPhoto) => {
    if (newPhoto) {
        const reader = new FileReader();
        reader.onload = (e) => photoPreview.value = e.target?.result as string;
        reader.readAsDataURL(newPhoto);
    }
});

const filteredClasses = computed(() => {
    if (!form.grade_id) return [];
    const selectedGradeId = Number(form.grade_id);
    return props.classes.filter(c => Number(c.grade_level_id) === selectedGradeId);
});

const submit = () => {
    form.post(`/students/${props.learner.id}/update`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`MUTATE_${learner.name.toUpperCase()}`" />

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
                        <Link :href="`/students/${learner.id}`">
                            <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">
                                Profile mutation hub
                            </h1>
                            <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2.5 py-0.5 text-[10px] font-bold tracking-widest text-primary uppercase shadow-sm">Audit Active</Badge>
                        </div>
                        <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Modifying secure identity node for {{ learner.name }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left Intelligence Stack -->
                <div class="space-y-8 lg:col-span-4">
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                         <div class="relative overflow-hidden bg-slate-900 p-10 text-white text-center">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-primary/20 blur-3xl opacity-20"></div>
                            <div class="relative z-10 space-y-8 text-center flex flex-col items-center">
                                <ProfilePhotoUpload v-model="form.photo" :error="form.errors.photo">
                                     <template #default="{ isUploading }">
                                        <div class="group relative mx-auto h-40 w-40 cursor-pointer overflow-hidden rounded-[2.5rem] border-4 border-white/10 bg-white/5 shadow-2xl transition-all duration-500 hover:scale-[1.05] active:scale-95">
                                            <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-white/10 text-4xl font-black">
                                                {{ learner.first_name[0] }}{{ learner.last_name[0] }}
                                            </div>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Camera class="h-8 w-8 text-white" />
                                                <span class="text-[9px] font-bold tracking-widest text-white uppercase">Replace Biometric</span>
                                            </div>
                                        </div>
                                     </template>
                                </ProfilePhotoUpload>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold tracking-[0.3em] text-white/40 uppercase">{{ learner.admission_number }}</h3>
                                    <p class="text-[9px] font-bold tracking-widest text-primary uppercase animate-pulse">Identity Lock Active</p>
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
                                        <option value="alumni">Alumni</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Persistence Node -->
                    <div class="flex items-center justify-between rounded-3xl border border-border bg-card p-6 shadow-sm transition-all hover:bg-muted/10">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-500 shadow-sm"><Database class="h-5 w-5" /></div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Mutation Engine</p>
                                <p class="text-xs font-bold tracking-tight text-foreground uppercase">Sync ready</p>
                            </div>
                        </div>
                        <div class="h-2 w-2 animate-pulse rounded-full bg-indigo-500"></div>
                    </div>
                </div>

                <!-- Right Data Stack -->
                <div class="space-y-12 lg:col-span-8">
                    <!-- Identity Node -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                        <div class="border-b border-border/50 bg-muted/10 px-10 py-6 text-sm font-black tracking-widest text-foreground uppercase">Identity mutation matrix</div>
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
                         <div class="border-b border-border/50 bg-muted/10 px-10 py-6 text-sm font-black tracking-widest text-foreground uppercase">Strategic placement</div>
                         <div class="p-10">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3 md:col-span-2">
                                     <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Admission Key (Staff No) *</Label>
                                     <Input v-model="form.admission_number" required class="h-14 rounded-2xl border-border bg-slate-100/50 px-6 text-sm font-black tracking-[0.2em] text-primary uppercase focus:bg-background" />
                                     <InputError :message="form.errors.admission_number" />
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Grade Assignment *</Label>
                                    <div class="relative">
                                        <select v-model="form.grade_id" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold uppercase outline-none focus:bg-background" required>
                                            <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Stream Synchronization *</Label>
                                    <div class="relative">
                                        <select v-model="form.class_id" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold uppercase outline-none focus:bg-background" :disabled="!form.grade_id" required>
                                            <option v-for="cls in filteredClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                        </select>
                                        <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Final Action Command -->
                    <div class="flex flex-wrap items-center justify-end gap-6 pt-6 border-t border-border/50">
                        <Button variant="ghost" class="h-14 rounded-[1.5rem] px-10 text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase hover:bg-muted" as-child>
                            <Link :href="`/students/${learner.id}`">Abort Mutation</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-14 rounded-[1.5rem] bg-slate-900 px-14 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-2xl transition-all hover:scale-[1.05] active:scale-[0.98]">
                            <Loader2 v-if="form.processing" class="mr-3 h-5 w-5 animate-spin" />
                            <ShieldCheck v-else class="mr-3 h-5 w-5" />
                            Commit institutional mutation
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
