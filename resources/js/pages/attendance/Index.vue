<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Home,
    ChevronRight,
    Check,
    Clock,
    Users,
    CheckCircle2,
    XCircle,
    Calendar,
    ArrowUpRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendance', href: '/attendance' },
];

const selectedClass = ref('all');
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const classes = ref([
    { id: 1, name: 'Grade 4A', total: 35, present: 33, absent: 2, late: 0 },
    { id: 2, name: 'Grade 4B', total: 32, present: 30, absent: 1, late: 1 },
    { id: 3, name: 'Grade 5A', total: 38, present: 35, absent: 2, late: 1 },
    { id: 4, name: 'Grade 5B', total: 36, present: 36, absent: 0, late: 0 },
    { id: 5, name: 'Grade 6A', total: 40, present: 38, absent: 1, late: 1 },
]);

const getAttendanceRate = (present: number, total: number) =>
    Math.round((present / total) * 100);
</script>

<template>
    <Head title="Attendance Overview" />
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
                        <span>Register</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Attendance</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Register Overview
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Track institutional presence and daily student engagement.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <Calendar class="h-3.5 w-3.5 text-muted-foreground transition-colors group-focus-within:text-primary" />
                        </div>
                        <input
                            type="date"
                            v-model="selectedDate"
                            class="h-11 rounded-xl border border-border bg-card pl-9 pr-4 text-xs font-bold uppercase tracking-tight shadow-sm transition-all focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <Button as-child class="h-11 rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-95">
                        <Link href="/attendance/mark">
                            <Check class="mr-2 h-4 w-4" />Mark Today
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Dashboard Insight Cards -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Present Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-emerald-500/5 blur-2xl transition-all group-hover:bg-emerald-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white">
                                <CheckCircle2 class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-emerald-600/40 uppercase">Journaled</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">1,172</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Confirmed Presence</p>
                        </div>
                    </div>
                </div>

                <!-- Absent Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-rose-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-rose-500/5 blur-2xl transition-all group-hover:bg-rose-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 shadow-sm transition-all group-hover:bg-rose-600 group-hover:text-white">
                                <XCircle class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-rose-600/40 uppercase">Deficit</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">48</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Unaccounted Learners</p>
                        </div>
                    </div>
                </div>

                <!-- Late Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-amber-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-amber-500/5 blur-2xl transition-all group-hover:bg-amber-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 shadow-sm transition-all group-hover:bg-amber-600 group-hover:text-white">
                                <Clock class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-amber-600/40 uppercase">Variance</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">15</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Late Arrivals Recorded</p>
                        </div>
                    </div>
                </div>

                <!-- Rate Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-primary/5 blur-2xl transition-all group-hover:bg-primary/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary shadow-sm transition-all group-hover:bg-primary group-hover:text-white">
                                <Users class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-primary/40 uppercase">Yield</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">94.5%</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Institutional Consistency</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View Section -->
            <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm mx-1">
                <div class="flex flex-col border-b border-border/50 bg-muted/10 px-8 py-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                             <Users class="h-5 w-5" />
                        </div>
                        <h3 class="text-sm font-bold tracking-tight text-foreground uppercase">Classwise Intelligence</h3>
                    </div>
                    
                    <div class="mt-4 sm:mt-0">
                        <Select v-model="selectedClass">
                            <SelectTrigger class="w-full h-11 rounded-2xl border-border bg-background px-5 text-xs font-bold uppercase tracking-tight sm:w-[220px]">
                                <SelectValue placeholder="Institutional Filter" />
                            </SelectTrigger>
                            <SelectContent class="rounded-2xl border-border shadow-2xl">
                                <SelectItem value="all" class="text-xs font-bold uppercase">Global View</SelectItem>
                                <SelectItem
                                    v-for="cls in classes"
                                    :key="cls.id"
                                    :value="String(cls.id)"
                                    class="text-xs font-bold uppercase"
                                >
                                    {{ cls.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left border-collapse">
                        <thead>
                            <tr class="text-muted-foreground/60 border-b border-border/50">
                                <th class="px-8 py-5 text-[10px] font-bold tracking-tighter uppercase">Cluster Node</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold tracking-tighter uppercase">Registry</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold tracking-tighter uppercase text-emerald-600">Present</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold tracking-tighter uppercase text-rose-600">Absent</th>
                                <th class="px-6 py-5 text-center text-[10px] font-bold tracking-tighter uppercase text-amber-600">Late</th>
                                <th class="px-10 py-5 text-[10px] font-bold tracking-tighter uppercase min-w-[180px]">Engagement Rate</th>
                                <th class="px-8 py-5 text-right text-[10px] font-bold tracking-tighter uppercase">Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="cls in classes"
                                :key="cls.id"
                                class="group transition-all hover:bg-muted/10 cursor-pointer"
                            >
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/5 text-primary group-hover:scale-110 transition-all font-black">
                                             {{ cls.name[0] }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-foreground">{{ cls.name }}</span>
                                            <p class="text-[9px] font-bold tracking-tight text-muted-foreground/40 uppercase">Academic Hub</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <Badge variant="outline" class="h-7 rounded-lg border-border bg-muted/30 px-3 text-[10px] font-bold tracking-tight text-foreground tabular-nums">
                                        {{ cls.total }} SOULS
                                    </Badge>
                                </td>
                                <td class="px-6 py-5 text-center text-sm font-bold text-emerald-600 tabular-nums">
                                    {{ cls.present }}
                                </td>
                                <td class="px-6 py-5 text-center text-sm font-bold text-rose-600 tabular-nums">
                                    {{ cls.absent }}
                                </td>
                                <td class="px-6 py-5 text-center text-sm font-bold text-amber-600 tabular-nums">
                                    {{ cls.late }}
                                </td>
                                <td class="px-10 py-5">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center justify-between text-[9px] font-black text-muted-foreground/30 uppercase">
                                            <span>PRECISION</span>
                                            <span class="text-primary">{{ getAttendanceRate(cls.present, cls.total) }}%</span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-muted/40 overflow-hidden shadow-inner">
                                            <div
                                                class="h-full bg-primary transition-all duration-1000 group-hover:scale-x-105"
                                                :style="{ width: `${getAttendanceRate(cls.present, cls.total)}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                        class="h-10 w-10 rounded-2xl text-muted-foreground transition-all hover:bg-primary hover:text-white"
                                    >
                                        <Link :href="`/attendance/class/${cls.id}`">
                                            <ArrowUpRight class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Matrix Synchronization Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-slate-900 dark:bg-black p-10 text-white shadow-2xl">
                 <div class="absolute inset-0 opacity-[0.03] grayscale pointer-events-none">
                    <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                        <defs><pattern id="grid-attendance" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/></pattern></defs>
                        <rect width="100%" height="100%" fill="url(#grid-attendance)" />
                    </svg>
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-10">
                    <div class="flex items-center gap-8">
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/5 border border-white/10 shadow-2xl transition-all hover:bg-primary/20 hover:scale-110">
                             <CheckCircle2 class="h-10 w-10 text-primary" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-xl font-bold tracking-tight">Institutional Attendance Pulse</h4>
                            <p class="text-sm text-white/50 font-medium max-w-xl leading-relaxed">
                                Maintaining synchronization across the academic registry. All presence logs are dynamically calculated and burnt into the institutional records for audit readiness.
                            </p>
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
