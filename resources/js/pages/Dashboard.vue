<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Activity,
    GraduationCap,
    BookOpen,
    Layers,
    Building2,
    History,
    ArrowRight,
    Monitor,
    Home,
    UserCircle,
    Search
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import ChartCard from '@/components/dashboard/ChartCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

interface Props {
    dashboardType: string;
    stats: {
        total_learners: number;
        total_teachers: number;
        total_classes: number;
        attendance_rate: number;
        total_departments: number;
        total_subjects: number;
    };
    classCapacity: {
        labels: string[];
        actual: number[];
        expected: number[];
    };
    weeklyAttendance: {
        labels: string[];
        datasets: Array<{ label: string; data: number[]; color?: string }>;
    };
    enrollmentTrends: {
        labels: string[];
        datasets: Array<{ label: string; data: number[]; color?: string }>;
    };
    subjectAnalytics: Array<{
        name: string;
        code: string;
        avg_score: number;
    }>;
    recentActivities: Array<{
        id: number;
        description: string;
        time: string;
    }>;
    streamAnalytics: Array<{
        name: string;
        student_count: number;
    }>;
    learnerQuickAccess: Array<{
        id: number;
        name: string;
        class: string;
        photo: string;
        admission_number: string;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Institutional Control', href: '/dashboard' },
];

const currentTime = ref(new Date());
let timeInterval: any;

onMounted(() => {
    timeInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timeInterval);
});

const clockTime = computed(() => {
    return currentTime.value.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
});

const greeting = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
});

const capacityData = computed(() => ({
    labels: props.classCapacity.labels,
    datasets: [
        { label: 'Current Enrolled', data: props.classCapacity.actual, color: 'rgb(59, 130, 246)' },
        { label: 'Licensed Capacity', data: props.classCapacity.expected, color: 'rgb(226, 232, 240)' }
    ]
}));

const growthData = computed(() => ({
    labels: props.enrollmentTrends.labels,
    datasets: props.enrollmentTrends.datasets
}));
</script>

<template>
    <Head title="Institutional Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-3 p-3 fade-in sm:p-5">
            
            <!-- HEADER SECTION -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Institution Command Center</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl">
                        {{ greeting }}, Admin
                    </h1>
                    <p class="text-xs text-muted-foreground font-medium">
                        {{ formattedDate }} • Institutional Oversight Active
                    </p>
                </div>

                <!-- CHRONO CLOCK -->
                <div class="relative group">
                    <div class="absolute -inset-4 bg-gradient-to-r from-primary/10 to-emerald-500/10 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative flex flex-col items-end bg-card/40 backdrop-blur-md px-6 py-2 rounded-2xl border border-border/40 shadow-sm">
                        <span class="text-3xl font-black tabular-nums tracking-[-0.05em] text-foreground flex items-baseline gap-1">
                            {{ clockTime.split(':')[0] }}:{{ clockTime.split(':')[1] }}
                            <span class="text-primary text-xl opacity-80">{{ clockTime.split(':')[2].split(' ')[0] }}</span>
                            <span class="text-[10px] uppercase font-bold text-muted-foreground ml-1">{{ clockTime.split(' ')[1] }}</span>
                        </span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[8px] font-black uppercase tracking-widest text-muted-foreground">Operational Time</span>
                            <Monitor class="h-2.5 w-2.5 text-emerald-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRIMARY METRICS -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <Link v-for="(stat, i) in [
                    { title: 'Learners', val: props.stats.total_learners, subtitle: 'Registry', icon: Users, variant: 'info', href: '/students' },
                    { title: 'Teachers', val: props.stats.total_teachers, subtitle: 'Faculty', icon: GraduationCap, variant: 'success', href: '/staffs' },
                    { title: 'Classes', val: props.stats.total_classes, subtitle: 'Academic', icon: Layers, variant: 'danger', href: '/classes' },
                    { title: 'Subjects', val: props.stats.total_subjects, subtitle: 'Curriculum', icon: BookOpen, variant: 'primary', href: '/curriculum/subjects' },
                    { title: 'Departments', val: props.stats.total_departments, subtitle: 'Org Units', icon: Building2, variant: 'warning', href: '/departments' },
                    { title: 'Attendance', val: props.stats.attendance_rate + '%', subtitle: 'Global Rate', icon: Activity, variant: 'success', href: '/attendance' }
                ]" :key="i" :href="stat.href" class="transition-all hover:-translate-y-1 group">
                    <StatCard :title="stat.title" :value="stat.val.toString()" :icon="stat.icon" :variant="stat.variant as any" :sub-title="stat.subtitle" />
                </Link>
            </div>

            <div class="grid gap-4 lg:grid-cols-12">
                
                <!-- ANALYTICS PANEL -->
                <div class="lg:col-span-9 space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <!-- REAL DATA: Capacity Engagement (Prioritizes populated classes) -->
                        <Link href="/classes" class="block">
                            <ChartCard title="Institutional Capacity Engagement" chart-type="line" :data="capacityData" :height="260" />
                        </Link>
                        <!-- REAL DATA: Institutional Acquisition Trend (Uses Student admission data) -->
                        <Link href="/students" class="block">
                            <ChartCard title="Institutional Acquisition Trend" chart-type="line" :data="growthData" :height="260" />
                        </Link>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- PROFICIENCY TABLE -->
                        <div class="rounded-2xl border bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden min-h-[300px]">
                            <div class="flex items-center justify-between border-b px-5 py-4 bg-muted/5">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/70">Top Performance Areas</h3>
                                <Link href="/curriculum/subjects" class="text-[9px] font-black text-primary hover:underline uppercase">Full Subjects</Link>
                            </div>
                            <div class="px-5 py-2">
                                <table class="w-full text-left">
                                    <tbody class="divide-y divide-border/40">
                                        <tr v-for="subject in props.subjectAnalytics.slice(0, 5)" :key="subject.code" class="group hover:bg-muted/30 transition-colors">
                                            <td class="py-3">
                                                <span class="text-xs font-bold block text-foreground">{{ subject.name }}</span>
                                                <span class="text-[9px] font-mono opacity-40 uppercase">{{ subject.code }}</span>
                                            </td>
                                            <td class="py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-20 h-1.5 rounded-full bg-muted overflow-hidden">
                                                        <div class="h-full bg-primary" :style="`width: ${subject.avg_score}%`"></div>
                                                    </div>
                                                    <span class="text-[10px] font-black text-foreground">{{ Math.round(subject.avg_score) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- OPERATIONAL LOGS -->
                        <div class="rounded-2xl border bg-card p-5 shadow-sm min-h-[300px]">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/80 flex items-center gap-2 mb-5">
                                <History class="h-4 w-4 text-emerald-500" />
                                Institutional Event Log
                            </h3>
                            <div class="space-y-4">
                                <div v-for="(activity, i) in props.recentActivities.slice(0, 4)" :key="i" class="flex gap-4 group cursor-pointer" @click="$inertia.visit('/reports/analytics')">
                                    <div class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0 shadow-[0_0_8px_rgba(16,185,129,0.3)] group-hover:scale-125 transition-transform"></div>
                                    <div class="flex flex-col leading-tight border-b border-border/20 pb-2 w-full">
                                        <span class="text-[11px] font-bold text-foreground leading-snug group-hover:text-primary transition-colors">{{ activity.description }}</span>
                                        <span class="text-[9px] text-muted-foreground uppercase mt-1 font-black opacity-40">{{ activity.time }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEW: LEARNER PROFILE DIRECTORY (Replaces Agenda/Admissions) -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="rounded-2xl border bg-card p-5 shadow-sm min-h-[620px] bg-gradient-to-b from-card to-muted/5 flex flex-col">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex flex-col">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground">Learner Profiles</h3>
                                <span class="text-[9px] text-muted-foreground font-bold italic">Direct Registry Access</span>
                            </div>
                            <UserCircle class="h-4 w-4 text-primary" />
                        </div>

                        <!-- Mini Search UI -->
                        <div class="relative mb-6 group">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <Input placeholder="Search learner..." class="h-9 pl-8 text-[11px] rounded-xl border-border/40 focus:ring-1 focus:ring-primary/20 bg-muted/20" />
                        </div>

                        <div class="space-y-3 flex-1 overflow-y-auto pr-1">
                            <Link v-for="learner in props.learnerQuickAccess" :key="learner.id" 
                                :href="`/students/${learner.id}`" 
                                class="flex items-center gap-3 p-3 rounded-2xl bg-muted/10 border border-transparent hover:border-primary/20 hover:bg-white dark:hover:bg-black/40 transition-all hover:shadow-md group">
                                <Avatar class="h-10 w-10 ring-2 ring-background transition-transform group-hover:scale-105">
                                    <AvatarFallback class="bg-primary/5 text-primary text-xs font-black uppercase">{{ learner.name.substring(0, 2) }}</AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col leading-none min-w-0">
                                    <span class="text-[11px] font-black text-foreground truncate group-hover:text-primary transition-colors">{{ learner.name }}</span>
                                    <span class="text-[9px] text-muted-foreground font-black uppercase mt-1 opacity-60">{{ learner.class }}</span>
                                    <span class="text-[8px] text-primary/40 font-mono mt-1 font-bold group-hover:opacity-100 transition-opacity">#{{ learner.admission_number }}</span>
                                </div>
                                <ArrowRight class="h-3 w-3 ml-auto text-muted-foreground/30 group-hover:text-primary transition-all group-hover:translate-x-1" />
                            </Link>
                        </div>

                        <Link href="/students" class="mt-6 text-center text-[10px] font-black uppercase tracking-widest text-primary hover:underline bg-primary/5 py-3 rounded-xl border border-primary/10 transition-all">
                            Open Global Directory
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar {
    width: 3px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: hsl(var(--primary) / 0.15);
    border-radius: 10px;
}
</style>

<style scoped>
.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: hsl(var(--primary) / 0.15);
    border-radius: 10px;
}

.bg-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>


<style scoped>
/* No extra styles needed, matching standard aesthetics */
</style>
