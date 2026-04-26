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
    Database,
    Zap,
    ChevronRight,
    Search,
    FileText,
    Settings,
    CheckCircle2,
    Clock,
    Building2,
    ChevronDown,
    Trash2,
    Edit,
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: props.learner.name, href: `/students/${props.learner.id}` },
];

const activeTab = ref('intelligence');

const tabs = [
    { id: 'intelligence', label: 'Intelligence', icon: Activity },
    { id: 'academic', label: 'Academic', icon: GraduationCap },
    { id: 'identity', label: 'Identity', icon: UserRound },
    { id: 'health', label: 'Bio-Fidelity', icon: HeartPulse },
    { id: 'logistics', label: 'Operational', icon: Truck },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20';
        case 'suspended':
            return 'bg-rose-500 text-white shadow-lg shadow-rose-500/20';
        case 'inactive':
            return 'bg-slate-400 text-white shadow-lg shadow-slate-400/20';
        case 'graduated':
            return 'bg-primary text-white shadow-lg shadow-primary/20';
        default:
            return 'bg-primary text-white shadow-lg shadow-primary/20';
    }
};

const stats = computed(() => [
    { label: 'Academic Load', val: '12 Subjects', sub: 'Assigned', icon: BookOpen },
    { label: 'Attendance Rate', val: '94.2%', sub: 'Real-time', icon: TrendingUp },
    { label: 'Fidelity Score', val: 'A+', sub: 'Performance', icon: ShieldCheck },
]);

// Subjects placeholder (simulated data)
const subjects = [
    { name: 'Mathematics', code: 'MAT', score: '92%', status: 'Stable' },
    { name: 'Language Arts', code: 'ENG', score: '88%', status: 'Active' },
    { name: 'Integrated Science', code: 'SCI', score: '95%', status: 'Exceptional' },
    { name: 'Creative Arts', code: 'ARTS', score: '90%', status: 'Stable' },
];
</script>

<template>
    <Head :title="`LEARNER_${learner.name.toUpperCase()}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- High-Fidelity Profile Header -->
            <div class="relative overflow-hidden rounded-[3rem] border border-border bg-card p-10 shadow-sm transition-all hover:border-primary/20">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary/5 blur-3xl"></div>
                <div class="relative flex flex-col gap-10 lg:flex-row lg:items-center">
                    <!-- Biometric Visual ID -->
                    <div class="group relative shrink-0">
                         <div class="relative h-40 w-40 overflow-hidden rounded-[2.5rem] border-4 border-white bg-slate-100 shadow-2xl transition-transform duration-500 hover:scale-105 active:scale-95 shadow-primary/10">
                            <img v-if="learner.photo_url" :src="learner.photo_url" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-primary text-5xl font-black text-white uppercase">
                                {{ learner.first_name[0] }}{{ learner.last_name[0] }}
                            </div>
                         </div>
                         <div class="absolute -bottom-2 -right-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-white p-1 shadow-xl shadow-primary/10">
                            <div :class="getStatusColor(learner.status)" class="h-full w-full rounded-xl flex items-center justify-center">
                                <Activity class="h-5 w-5 text-white" />
                            </div>
                         </div>
                    </div>

                    <!-- Learner Identification -->
                    <div class="flex-1 space-y-6">
                        <div class="space-y-2">
                             <div class="flex flex-wrap items-center gap-4">
                                <h1 class="text-4xl font-black tracking-tight text-foreground uppercase">{{ learner.name }}</h1>
                                <Badge variant="outline" class="rounded-xl border-primary/20 bg-primary/10 px-4 py-1 text-[10px] font-black tracking-[0.2em] text-primary uppercase shadow-sm">
                                    {{ learner.admission_number }}
                                </Badge>
                             </div>
                             <p class="text-[10px] font-bold tracking-[0.3em] text-muted-foreground uppercase opacity-60">
                                {{ learner.grade?.name || 'Academic Core' }} // {{ learner.current_class?.name || 'Link Pending' }}
                             </p>
                        </div>

                        <!-- Real-time Metrics -->
                        <div class="flex flex-wrap gap-10">
                            <div v-for="(stat, idx) in stats" :key="idx" class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted/50 text-primary shadow-sm"><component :is="stat.icon" class="h-5 w-5" /></div>
                                <div class="space-y-0.5">
                                    <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">{{ stat.label }}</p>
                                    <p class="text-sm font-bold tracking-tight text-foreground uppercase">{{ stat.val }} <span class="text-[10px] opacity-40">{{ stat.sub }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Management Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <Button variant="outline" class="h-14 rounded-2xl border-border bg-card px-8 text-[10px] font-black tracking-[0.2em] uppercase hover:bg-muted" as-child>
                             <Link :href="`/students/${learner.id}/edit`">
                                <Edit class="mr-3 h-4 w-4 text-primary" />
                                Mutate Profile
                             </Link>
                        </Button>
                        <Button class="h-14 rounded-2xl bg-slate-900 px-8 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-2xl transition-all hover:scale-[1.05]">
                            <Settings class="mr-3 h-4 w-4" />
                            System Protocols
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Content Matrix -->
            <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-[320px_1fr]">
                <!-- Strategic Navigation Matrix -->
                <aside class="sticky top-28 space-y-3">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="group relative flex w-full items-center gap-4 rounded-3xl p-6 text-sm font-bold transition-all duration-500"
                        :class="activeTab === tab.id
                            ? 'bg-primary text-white shadow-2xl shadow-primary/30 translate-x-3 scale-[1.02]'
                            : 'bg-card text-muted-foreground border border-border/50 hover:border-primary/20 hover:bg-muted/10'"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl transition-colors"
                             :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground group-hover:text-primary'">
                            <component :is="tab.icon" class="h-5 w-5" />
                        </div>
                        <span class="tracking-widest uppercase text-[10px] font-black">{{ tab.label }}</span>
                        <ChevronRight v-if="activeTab === tab.id" class="ml-auto h-4 w-4 animate-pulse" />
                    </button>
                    
                    <div class="mt-12 rounded-3xl border border-primary/10 bg-primary/5 p-8 text-center transition-all hover:bg-primary/10">
                        <ShieldCheck class="mx-auto mb-4 h-10 w-10 text-primary opacity-40" />
                        <h4 class="text-[10px] font-black tracking-widest text-primary uppercase mb-2">Vault Fidelity</h4>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-60 leading-relaxed">Full institutional identity encryption protocol active</p>
                    </div>
                </aside>

                <!-- Intelligence Display -->
                <main class="space-y-12">
                     <!-- Intelligence Tab (Overview) -->
                    <div v-if="activeTab === 'intelligence'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                         <div class="grid gap-8 md:grid-cols-2">
                             <div class="group overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                                <div class="border-b border-border/50 bg-muted/10 px-8 py-6 flex items-center justify-between">
                                    <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Demographic Sync</h3>
                                    <Zap class="h-4 w-4 text-primary animate-pulse" />
                                </div>
                                <div class="p-8 space-y-6">
                                    <div v-for="(val, label) in {
                                        'Biological Mode': learner.gender,
                                        'Temporal Identity': learner.date_of_birth || 'UNCAPTURED',
                                        'Identity Key': learner.upi || 'NONE',
                                        'Birth Protocol': learner.birth_certificate_number || 'NONE',
                                        'Nationality Node': learner.nationality || 'Kenyan',
                                        'Spiritual Alignment': learner.religion || 'NONE'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-black text-foreground uppercase tracking-tight">{{ val }}</span>
                                    </div>
                                </div>
                             </div>

                             <div class="group overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                                <div class="border-b border-border/50 bg-muted/10 px-8 py-6">
                                    <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Academic Placement</h3>
                                </div>
                                <div class="p-8 space-y-6">
                                    <div v-for="(val, label) in {
                                        'Current Node': learner.grade?.name || 'CORE',
                                        'Operational Stream': learner.current_class?.name || 'ALLOC_PENDING',
                                        'Admission Path': learner.admission_class?.name || 'UNALLOCATED',
                                        'Temporal Start': learner.admission_date || 'N/A',
                                        'Boarding Mode': learner.boarding_status || 'DAY',
                                        'Institutional State': learner.status || 'ACTIVE'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-black text-primary uppercase tracking-tight">{{ val }}</span>
                                    </div>
                                </div>
                             </div>
                         </div>
                    </div>

                    <!-- Academic Tab -->
                    <div v-if="activeTab === 'academic'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                        <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                             <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-10 py-6">
                                <h3 class="text-[10px] font-black tracking-widest text-foreground uppercase">Curriculum Load Matrix</h3>
                                <Button variant="ghost" size="sm" class="h-8 rounded-xl text-[9px] font-black uppercase text-primary hover:bg-primary/10">Export Portfolio</Button>
                             </div>
                             <div class="p-10">
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                    <div v-for="sbj in subjects" :key="sbj.code" class="group flex flex-col justify-between rounded-[2rem] border border-border bg-muted/20 p-6 transition-all hover:border-primary/20 hover:bg-card">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="space-y-1">
                                                <p class="text-[9px] font-black tracking-widest text-primary uppercase">{{ sbj.code }}</p>
                                                <p class="text-sm font-black text-foreground uppercase tracking-tight">{{ sbj.name }}</p>
                                            </div>
                                            <div class="text-xl font-black text-primary">{{ sbj.score }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                            <span class="text-[9px] font-black text-muted-foreground uppercase opacity-60">{{ sbj.status }}</span>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Health & Bio-Fidelity -->
                    <div v-if="activeTab === 'health'" class="animate-in fade-in slide-in-from-right-8 duration-700">
                        <div class="grid gap-8 md:grid-cols-2">
                             <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm p-10 space-y-8">
                                <div class="flex items-center gap-6">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-[1.5rem] bg-rose-50 text-rose-500"><HeartPulse class="h-7 w-7" /></div>
                                    <div>
                                        <h3 class="text-sm font-black text-foreground uppercase">Biological properties</h3>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-60">Medical synchronization data</p>
                                    </div>
                                </div>
                                <div class="space-y-6">
                                     <div v-for="(val, label) in {
                                        'Blood Node': learner.blood_group || 'UNCAPTURED',
                                        'Condition Matrix': learner.medical_conditions || 'NULL_SET',
                                        'Allergy Protocol': learner.allergies || 'NONE_DETECTED',
                                        'Special Needs Node': learner.has_special_needs ? 'ACTIVE' : 'NONE',
                                        'Node Fidelity': 'High Verification'
                                    }" :key="label" class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ label }}</span>
                                        <span class="text-xs font-black text-foreground uppercase">{{ val }}</span>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AppLayout>
</template>
