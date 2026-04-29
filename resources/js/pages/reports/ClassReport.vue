<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Users, TrendingUp, GraduationCap, Award, 
    BarChart3, PieChart, Info, Download, Printer,
    Calendar, CheckCircle2, ChevronRight,
    ArrowUpRight, ArrowDownRight, Minus, 
    Layers, Briefcase, Activity
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import StatCard from '@/components/dashboard/StatCard.vue';
import axios from 'axios';

const props = defineProps<{
    classes: any[];
    terms: any[];
    performanceLevels: any[];
}>();

const breadcrumbs = [
    { title: 'Intelligence', href: '/reports' },
    { title: 'Class Performance Summary', href: '/reports/class-summary' },
];

const selectedClassId = ref<string | null>(props.classes[0]?.id.toString() || null);
const selectedTermId = ref<string | null>(props.terms[0]?.id.toString() || null);
const analysisData = ref<any>(null);
const isLoading = ref(false);

const getClassAnalysis = async () => {
    if (!selectedClassId.value) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get('/api/reports/class-analysis', {
            params: {
                class_id: selectedClassId.value,
                term_id: selectedTermId.value
            }
        });
        analysisData.value = response.data;
    } catch (error) {
        console.error("Class analysis load failed", error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getClassAnalysis();
});

const getLevelColor = (label: string) => {
    switch (label) {
        case 'EE': return 'bg-emerald-500';
        case 'ME': return 'bg-blue-500';
        case 'AE': return 'bg-amber-500';
        case 'BE': return 'bg-rose-500';
        default: return 'bg-slate-500';
    }
};

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head title="Class Performance Summary" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-3 p-3 fade-in sm:p-5">
            
            <!-- HEADER SECTION -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4 mb-2 print-hide">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Strategic Hub</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Class Matrix Executive
                    </h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold flex items-center gap-2">
                        Aggregated academic intelligence for institutional class nodes.
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <Button variant="outline" class="h-10 text-[11px] font-black uppercase tracking-widest bg-white" @click="triggerPrint">
                        <Printer class="mr-2 h-3.5 w-3.5" /> Print PDF
                    </Button>
                </div>
            </div>

            <!-- FILTERS -->
            <Card class="rounded-2xl border bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden mb-4 print-hide">
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Academic Node (Class)</label>
                            <Select v-model="selectedClassId" @update:modelValue="getClassAnalysis">
                                <SelectTrigger class="h-10 text-xs">
                                    <SelectValue placeholder="Select Class" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in classes" :key="c.id" :value="c.id.toString()">{{ c.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Evaluation Period</label>
                            <Select v-model="selectedTermId" @update:modelValue="getClassAnalysis">
                                <SelectTrigger class="h-10 text-xs">
                                    <SelectValue placeholder="Select Term" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in terms" :key="t.id" :value="t.id.toString()">{{ t.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button @click="getClassAnalysis" variant="default" class="h-10 text-xs font-black uppercase tracking-widest">
                             Aggregate Intelligence 
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div v-if="isLoading" class="min-h-[400px] flex flex-col items-center justify-center gap-4 opacity-50">
                <Activity class="h-8 w-8 animate-spin" />
                <span class="text-[10px] font-black uppercase tracking-widest">Synthesizing Class Matrix...</span>
            </div>

            <div v-else-if="analysisData" class="grid grid-cols-1 lg:grid-cols-12 gap-4 fade-in">
                
                <!-- TOP STRATEGIC STATS -->
                <div class="lg:col-span-12 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard 
                        title="Enrollment" 
                        :value="analysisData.stats.total_students.toString()" 
                        :icon="Users" 
                        variant="info" 
                        sub-title="Active Learners" 
                    />
                    <StatCard 
                        title="Mastery Average" 
                        :value="analysisData.stats.average_mastery.toString() + '%'" 
                        :icon="TrendingUp" 
                        variant="success" 
                        :trend="{ value: 3.2, direction: 'up' }"
                    />
                    <StatCard 
                        title="Attendance Profile" 
                        :value="analysisData.stats.attendance_rate.toString() + '%'" 
                        :icon="Calendar" 
                        variant="primary" 
                        sub-title="Reliability Index" 
                    />
                    <StatCard 
                        title="Reports Published" 
                        :value="analysisData.stats.published_reports.toString()" 
                        :icon="CheckCircle2" 
                        variant="warning" 
                        sub-title="Term Archive" 
                    />
                </div>

                <!-- LEFT COLUMN: SUBJECTS & PERFORMANCE -->
                <div class="lg:col-span-8 space-y-4">
                    
                    <!-- SUBJECTS RANKING (Standard data table structure) -->
                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between border-b px-5 py-4 bg-muted/5">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/70">Learning Area Calibration</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-muted/10 border-b border-border/40 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                                        <th class="px-5 py-3">Subject / Area</th>
                                        <th class="px-5 py-3 text-center">Mastery Score</th>
                                        <th class="px-5 py-3 w-1/3">Velocity</th>
                                        <th class="px-5 py-3 text-center">Trend</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/20">
                                    <tr v-for="item in analysisData.subject_mastery" :key="item.subject" class="hover:bg-muted/5 transition-colors group">
                                        <td class="px-5 py-3">
                                            <span class="text-xs font-black text-foreground">{{ item.subject }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-xs font-black text-primary">{{ item.mastery }}%</span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
                                                <div class="h-full bg-primary transition-all duration-1000" :style="{ width: `${item.mastery}%` }"></div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <div class="flex justify-center">
                                                <ArrowUpRight v-if="item.trend === 'up'" class="h-4 w-4 text-emerald-500" />
                                                <ArrowDownRight v-else-if="item.trend === 'down'" class="h-4 w-4 text-rose-500" />
                                                <Minus v-else class="h-4 w-4 text-muted-foreground/30" />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ACADEMIC DISTRIBUTION -->
                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden p-5">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/70 mb-5">Competency Distribution Ledger</h3>
                        
                        <div class="h-8 w-full flex rounded-lg overflow-hidden shadow-sm border border-border">
                            <div v-for="seg in analysisData.distribution" :key="seg.label"
                                :class="['h-full flex items-center justify-center text-[9px] font-black text-white hover:opacity-90 transition-opacity', getLevelColor(seg.label)]"
                                :style="{ width: `${(seg.value / analysisData.stats.total_students) * 100}%` }"
                                :title="seg.label + ' (' + seg.value + ')'">
                                <span class="truncate px-1">{{ seg.label }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div v-for="seg in analysisData.distribution" :key="seg.label" class="flex items-center gap-3 p-3 rounded-xl bg-muted/10 border border-border/20">
                                <div :class="['h-2 w-2 rounded-full shrink-0', getLevelColor(seg.label)]"></div>
                                <div class="space-y-0.5">
                                    <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase">{{ seg.label }} Volume</p>
                                    <p class="text-xs font-black text-foreground">{{ seg.value }} Learners</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: TOP PERFORMERS & REMARKS -->
                <div class="lg:col-span-4 space-y-4">
                    
                    <!-- TOP ACHIEVERS (Standard Dashboard Card) -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm overflow-hidden">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-amber-600 flex items-center gap-2 mb-4">
                            <Award class="h-4 w-4" />
                            Top Achievers Circle
                        </h3>
                        <div class="space-y-3">
                            <div v-for="(p, i) in analysisData.top_performers" :key="p.name" 
                                class="flex items-center justify-between p-3 rounded-xl bg-muted/5 border border-border/40 hover:bg-muted/20 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="h-6 w-6 rounded-md bg-amber-500/10 text-amber-600 flex items-center justify-center font-black text-[9px] border border-amber-500/20">
                                        {{ i + 1 }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-black text-foreground">{{ p.name }}</p>
                                    </div>
                                </div>
                                <Badge variant="outline" class="font-black text-[9px] border-primary/20 bg-primary/5 text-primary">
                                    {{ p.score }}%
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <!-- DEMOGRAPHICS -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <Users class="h-4 w-4 text-primary" />
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground">Gender Distribution</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black uppercase text-blue-500">Boys</span>
                                    <p class="text-xl font-black">{{ analysisData.stats.boys }}</p>
                                </div>
                                <div class="space-y-1 text-right">
                                    <span class="text-[10px] font-black uppercase text-rose-500">Girls</span>
                                    <p class="text-xl font-black">{{ analysisData.stats.girls }}</p>
                                </div>
                            </div>
                            <div class="h-2 w-full bg-muted rounded-full overflow-hidden flex">
                                <div class="h-full bg-blue-500" :style="{ width: `${(analysisData.stats.boys / analysisData.stats.total_students) * 100}%` }"></div>
                                <div class="h-full bg-rose-500" :style="{ width: `${(analysisData.stats.girls / analysisData.stats.total_students) * 100}%` }"></div>
                            </div>
                        </div>
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
</style>
