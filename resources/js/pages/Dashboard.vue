<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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
    auditLogs: Array<{
        id: number;
        event: string;
        description: string;
        who: string;
        role: string;
        gadget: string;
        ip: string;
        where: string;
        when: string;
        time_ago: string;
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

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);

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

// Smart gadget icon helper
const getGadgetIcon = (userAgent: string) => {
    const ua = userAgent.toLowerCase();
    if (ua.includes('mobile') || ua.includes('android') || ua.includes('iphone')) return 'Smartphone';
    return 'Desktop';
};
</script>

<template>
    <Head title="Institutional Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-3 p-3 fade-in sm:p-5">
            
            <!-- HEADER SECTION -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4 mb-2">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Institutional Command Center</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        {{ greeting }}, <span class="text-primary/90">{{ user.name.split(' ')[0] }}</span>
                    </h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold flex items-center gap-2">
                        <Calendar class="h-3 w-3 opacity-50" />
                        {{ formattedDate }} <span class="hidden sm:inline">•</span> <span class="text-primary/60">Oversight Active</span>
                    </p>
                </div>

                <!-- CHRONO CLOCK -->
                <div class="relative group w-full md:w-auto hidden md:block">
                    <div class="absolute -inset-4 bg-gradient-to-r from-primary/10 to-emerald-500/10 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative flex flex-col items-center md:items-end bg-card/60 backdrop-blur-xl px-6 py-3 rounded-[2rem] border border-border/40 shadow-xl shadow-primary/5 transition-all hover:scale-[1.02] hover:bg-card">
                        <span class="text-3xl sm:text-4xl font-black tabular-nums tracking-[-0.05em] text-foreground flex items-baseline gap-1">
                            {{ clockTime.split(':')[0] }}:{{ clockTime.split(':')[1] }}
                            <span class="text-primary text-2xl opacity-80">{{ clockTime.split(':')[2].split(' ')[0] }}</span>
                            <span class="text-[10px] sm:text-[12px] uppercase font-black text-muted-foreground ml-1">{{ clockTime.split(' ')[1] }}</span>
                        </span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60">Operational Sync</span>
                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRIMARY METRICS (Department Card Removed) -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <Link v-for="(stat, i) in [
                    { title: 'Learners', val: props.stats.total_learners, subtitle: 'Registry', icon: Users, variant: 'info', href: '/students' },
                    { title: 'Teachers', val: props.stats.total_teachers, subtitle: 'Faculty', icon: GraduationCap, variant: 'success', href: '/staffs' },
                    { title: 'Classes', val: props.stats.total_classes, subtitle: 'Academic', icon: Layers, variant: 'danger', href: '/classes' },
                    { title: 'Subjects', val: props.stats.total_subjects, subtitle: 'Curriculum', icon: BookOpen, variant: 'primary', href: '/curriculum/subjects' },
                    { title: 'Attendance', val: props.stats.attendance_rate + '%', subtitle: 'Global Rate', icon: Activity, variant: 'success', href: '/attendance' }
                ]" :key="i" :href="stat.href" class="transition-all hover:-translate-y-1 group">
                    <StatCard :title="stat.title" :value="stat.val.toString()" :icon="stat.icon" :variant="stat.variant as any" :sub-title="stat.subtitle" />
                </Link>
            </div>

            <div class="grid gap-4 lg:grid-cols-12">
                
                <!-- ANALYTICS PANEL (LHS) -->
                <div class="lg:col-span-8 space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <!-- REAL DATA: Capacity Engagement -->
                        <ChartCard title="Institutional Capacity Engagement" chart-type="line" :data="capacityData" :height="260" />
                        <!-- REAL DATA: Institutional Acquisition Trend -->
                        <ChartCard title="Institutional Growth Trend" chart-type="line" :data="growthData" :height="260" />
                    </div>

                    <!-- PERFORMANCE TABLE -->
                    <div class="rounded-2xl border bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden h-[340px]">
                        <div class="flex items-center justify-between border-b px-5 py-4 bg-muted/5">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/70">Academic Proficiency Index</h3>
                            <Link href="/curriculum/subjects" class="text-[9px] font-black text-primary hover:underline uppercase">Subject Data</Link>
                        </div>
                        <div class="px-5 py-2">
                            <table class="w-full text-left">
                                <tbody class="divide-y divide-border/20">
                                    <tr v-for="subject in props.subjectAnalytics" :key="subject.code" class="group hover:bg-muted/10 transition-colors">
                                        <td class="py-3">
                                            <span class="text-xs font-black block text-foreground">{{ subject.name }}</span>
                                            <span class="text-[9px] font-mono opacity-40 uppercase tracking-tighter">{{ subject.code }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1 h-1 rounded-full bg-muted overflow-hidden max-w-[200px]">
                                                    <div class="h-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.4)]" :style="`width: ${subject.avg_score}%`"></div>
                                                </div>
                                                <span class="text-[11px] font-black text-foreground w-10 text-right">{{ Math.round(subject.avg_score) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- OPERATIONAL & AUDIT HUB (RHS) -->
                <div class="lg:col-span-4 space-y-4">
                    
                    <!-- LEARNER DIRECTORY -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm h-[320px] flex flex-col bg-gradient-to-b from-card to-muted/5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground flex items-center gap-2">
                                <UserCircle class="h-4 w-4 text-primary" />
                                Learner Hub
                            </h3>
                            <Link href="/students" class="text-[9px] font-black text-primary hover:underline uppercase">Registry</Link>
                        </div>
                        <div class="space-y-2 flex-1 overflow-y-auto pr-1">
                            <Link v-for="learner in props.learnerQuickAccess" :key="learner.id" 
                                :href="`/students/${learner.id}`" 
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-muted/10 border border-border/20 hover:bg-primary/[0.03] hover:border-primary/20 transition-all group">
                                <Avatar class="h-9 w-9 ring-2 ring-background">
                                    <AvatarFallback class="bg-primary/5 text-primary text-[10px] font-black uppercase">{{ learner.name.substring(0, 2) }}</AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col leading-none min-w-0">
                                    <span class="text-[11px] font-black text-foreground truncate group-hover:text-primary transition-colors">{{ learner.name }}</span>
                                    <span class="text-[9px] text-muted-foreground font-black uppercase mt-1 opacity-60">{{ learner.class }}</span>
                                </div>
                                <ArrowRight class="h-3 w-3 ml-auto text-muted-foreground/30 group-hover:text-primary group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>
                    </div>

                    <!-- AUDIT LOGS (REAL-TIME ADAPTIVE FEED) -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm h-[450px] flex flex-col bg-slate-900/5 transition-all hover:shadow-lg">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex flex-col">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground flex items-center gap-2">
                                    <History class="h-4 w-4 text-emerald-500" />
                                    School Audit Trail
                                </h3>
                                <span class="text-[8px] font-black text-muted-foreground uppercase mt-1 opacity-50">Authorized Personnel Actions</span>
                            </div>
                            <Badge variant="outline" class="h-4 text-[8px] font-black border-emerald-500/20 text-emerald-600 bg-emerald-500/5">Scoped: School Only</Badge>
                        </div>

                        <div class="space-y-4 flex-1 overflow-y-auto pr-2 custom-scrollbar mb-4">
                            <div v-for="log in props.auditLogs" :key="log.id" class="relative pl-6 pb-4 border-l border-border/60 last:border-0 group">
                                <!-- Action Dot -->
                                <div class="absolute -left-[5px] top-0 h-2.5 w-2.5 rounded-full border-2 border-card bg-emerald-500 group-hover:scale-125 transition-transform shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                                
                                <div class="flex flex-col gap-1.5 p-3 rounded-2xl bg-white/40 border border-border/20 shadow-sm transition-all hover:border-primary/20 hover:bg-white overflow-hidden">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-black text-foreground group-hover:text-primary transition-colors truncate max-w-[120px]">{{ log.who }}</span>
                                        <span class="text-[9px] font-black text-muted-foreground bg-muted/40 px-2 py-0.5 rounded-full uppercase leading-none">{{ log.role }}</span>
                                    </div>
                                    
                                    <p class="text-[10px] font-bold text-foreground/80 leading-snug">
                                        {{ log.description }}
                                        <span class="text-[9px] font-black text-primary/60 italic ml-1 select-none">#{{ log.event }}</span>
                                    </p>

                                    <!-- AGENT & IP (THE GADGET DETAIL) -->
                                    <div class="flex items-center gap-2 sm:gap-4 mt-2 pt-2 border-t border-border/30">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <component :is="getGadgetIcon(log.gadget) === 'Smartphone' ? 'Smartphone' : 'Monitor'" class="h-2.5 w-2.5 text-muted-foreground opacity-50 shrink-0" />
                                            <span class="text-[9px] font-black text-muted-foreground uppercase opacity-70 truncate" :title="log.gadget">{{ log.gadget.split(' ')[0] }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <div class="h-1 w-1 rounded-full bg-emerald-500"></div>
                                            <span class="text-[9px] font-mono font-black text-muted-foreground opacity-70">{{ log.ip }}</span>
                                        </div>
                                        <span class="text-[9px] font-black text-muted-foreground/40 ml-auto uppercase tracking-tighter shrink-0">{{ log.when.split(' ')[2] }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- EMPTY STATE -->
                            <div v-if="props.auditLogs.length === 0" class="flex flex-col items-center justify-center py-10 text-center space-y-3 opacity-30 grayscale">
                                <History class="h-8 w-8" />
                                <span class="text-[10px] font-black uppercase tracking-widest">No matching school logs captured</span>
                            </div>
                        </div>

                        <!-- DEEP LINK TO FULL AUDIT PAGE -->
                        <Link :href="route('audit-logs.index')" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-500/5 text-emerald-600 border border-emerald-500/10 hover:bg-emerald-500/10 transition-all group/btn mt-auto">
                            <span class="text-[10px] font-black uppercase tracking-widest">View Comprehensive Trail</span>
                            <ArrowRight class="h-3 w-3 group-hover/btn:translate-x-1 transition-transform" />
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

.custom-scrollbar::-webkit-scrollbar {
    width: 3px;
}

.custom-scrollbar::-webkit-scrollbar-track {
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
