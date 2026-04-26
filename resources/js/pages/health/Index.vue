<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    Stethoscope,
    AlertCircle,
    Calendar,
    Plus,
    ArrowUpRight,
    ClipboardList,
    Home,
    ChevronRight,
    Users,
    HeartPulse,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        total_visits_today: number;
        referred_cases: number;
        common_condition: string;
        medical_alerts: number;
    };
    recentVisits: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Health & Wellness', href: '/health' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Health & Wellness" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Home class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Support</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Health System</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Clinic Intelligence
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Monitor institutional vitals and student wellness protocols.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted">
                        <Link href="/health/records">
                            <ClipboardList class="mr-2 h-4 w-4 opacity-40" />Medical Records
                        </Link>
                    </Button>
                    <Button as-child class="h-11 rounded-xl bg-rose-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-rose-500/30 transition-all hover:scale-[1.02] hover:bg-rose-700 active:scale-95">
                        <Link href="/health/visits/create">
                            <Plus class="mr-2 h-4 w-4" />Log Encounter
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Modern Dashboard Cards -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Visits Today -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-rose-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-rose-500/5 blur-2xl transition-all group-hover:bg-rose-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 shadow-sm transition-all group-hover:bg-rose-600 group-hover:text-white">
                                <Stethoscope class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-rose-600/40 uppercase">Encounters</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_visits_today }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Visits Synchronized Today</p>
                        </div>
                    </div>
                </div>

                <!-- Referrals -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-orange-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-orange-500/5 blur-2xl transition-all group-hover:bg-orange-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-600 shadow-sm transition-all group-hover:bg-orange-600 group-hover:text-white">
                                <AlertCircle class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-orange-600/40 uppercase">Escalations</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.referred_cases }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Referred to Facilities</p>
                        </div>
                    </div>
                </div>

                <!-- Condition Trends -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-blue-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-blue-500/5 blur-2xl transition-all group-hover:bg-blue-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white">
                                <Activity class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-blue-600/40 uppercase">Pulse</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-foreground truncate">{{ stats.common_condition || 'Normal' }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Predominant Condition</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Alerts -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-primary/5 blur-2xl transition-all group-hover:bg-primary/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary shadow-sm transition-all group-hover:bg-primary group-hover:text-white">
                                <HeartPulse class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-primary/40 uppercase">Critical</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.medical_alerts }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Active Medical Watches</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Split -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 px-1">
                <!-- Recent Encounters -->
                <div class="lg:col-span-2 overflow-hidden rounded-3xl border border-border bg-card shadow-sm">
                    <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600">
                                <Activity class="h-5 w-5" />
                            </div>
                            <h3 class="text-sm font-bold tracking-tight text-foreground uppercase">Live Encounter Log</h3>
                        </div>
                        <Button variant="ghost" size="sm" as-child class="h-9 rounded-xl text-[10px] font-bold tracking-tight uppercase hover:bg-muted">
                            <Link href="/health/visits">View Archive</Link>
                        </Button>
                    </div>

                    <div class="divide-y divide-border/30">
                        <div
                            v-for="visit in recentVisits"
                            :key="visit.id"
                            class="group flex items-center justify-between p-6 transition-all hover:bg-muted/10"
                        >
                            <div class="flex items-center gap-5">
                                <div class="px-3 py-1 rounded-xl bg-muted/30 border border-border/50 text-center min-w-[60px]">
                                    <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Node</p>
                                    <p class="text-xs font-bold text-foreground">{{ visit.student?.admission_number || 'REG' }}</p>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-foreground group-hover:text-rose-600 transition-colors uppercase">
                                            {{ visit.student?.name || (visit.student?.first_name + ' ' + visit.student?.last_name) }}
                                        </h4>
                                        <Badge v-if="visit.referred_to_hospital" variant="destructive" class="h-5 rounded-lg text-[9px] font-bold tracking-tighter uppercase px-2 bg-rose-600">REFERRED</Badge>
                                    </div>
                                    <p class="text-[10px] font-bold tracking-tight text-muted-foreground/50 uppercase mt-0.5">
                                        {{ visit.chief_complaint }} <span class="mx-1 opacity-30">•</span> {{ visit.diagnosis }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">{{ formatDate(visit.visit_datetime) }}</span>
                                <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg opacity-0 group-hover:opacity-100 transition-all">
                                    <Link :href="`/health/visits/${visit.id}`"><ArrowUpRight class="h-4 w-4" /></Link>
                                </Button>
                            </div>
                        </div>
                        <div v-if="recentVisits.length === 0" class="flex flex-col items-center justify-center p-20 text-center">
                            <div class="h-16 w-16 rounded-3xl bg-muted/20 flex items-center justify-center mb-4">
                                <Stethoscope class="h-8 w-8 text-muted-foreground/20" />
                            </div>
                            <p class="text-xs font-bold text-muted-foreground/40 uppercase tracking-widest">No active cases reported today</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Modules -->
                <div class="space-y-6">
                    <!-- Emergency Protocol Banner -->
                    <div class="relative overflow-hidden rounded-3xl bg-slate-900 dark:bg-black p-8 text-white shadow-2xl">
                        <div class="absolute inset-0 opacity-[0.05] grayscale pointer-events-none">
                            <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                                <defs><pattern id="grid-health" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/></pattern></defs>
                                <rect width="100%" height="100%" fill="url(#grid-health)" />
                            </svg>
                        </div>
                        
                        <div class="relative z-10 space-y-6">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 border border-white/10 shadow-2xl transition-all hover:bg-rose-500/20 hover:scale-110">
                                <AlertCircle class="h-8 w-8 text-rose-500" />
                            </div>
                            <div class="space-y-2">
                                <h4 class="text-xl font-bold tracking-tight">Institutional Protocols</h4>
                                <p class="text-xs text-white/50 font-bold uppercase tracking-tight leading-relaxed">
                                    Zero-latency access to student emergency matrices and medical journals. Ensure all encounters are logged for audit synchronization.
                                </p>
                            </div>
                            <Button as-child class="w-full h-12 rounded-2xl bg-white font-bold text-[10px] tracking-tight text-slate-900 uppercase hover:bg-rose-50 transition-all active:scale-95">
                                <Link href="/health/records">Access Bio-Profiles <ArrowUpRight class="ml-2 h-4 w-4" /></Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Upcoming Screenings Card -->
                    <div class="rounded-3xl border border-border bg-card p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-8 w-8 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600">
                                <Calendar class="h-4 w-4" />
                            </div>
                            <h3 class="text-xs font-bold tracking-tight text-foreground uppercase">Scheduled Screenings</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="group flex items-center gap-4 rounded-2xl border border-border/50 bg-muted/10 p-4 transition-all hover:bg-background hover:border-blue-500/20 cursor-pointer">
                                <div class="h-10 w-10 shrink-0 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <HeartPulse class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-foreground uppercase">General Review</p>
                                    <p class="text-[9px] font-bold text-muted-foreground/40 uppercase mt-0.5">Grade 4 • Next Week</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
