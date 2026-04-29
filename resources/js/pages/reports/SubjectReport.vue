<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    BookOpen, Search, Filter, Download, Printer, 
    ChevronRight, Users, TrendingUp, AlertTriangle,
    BarChart3, PieChart, Info, CheckCircle2,
    ArrowUpRight, ArrowDownRight, Minus, 
    Briefcase, GraduationCap, LayoutDashboard,
    Activity
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import StatCard from '@/components/dashboard/StatCard.vue';
import axios from 'axios';

const props = defineProps<{
    classes: any[];
    subjects: any[];
    terms: any[];
    performanceLevels: any[];
}>();

const breadcrumbs = [
    { title: 'Intelligence', href: '/reports' },
    { title: 'Subject Performance Report', href: '/reports/subject-performance' },
];

const selectedClassId = ref<string | null>(props.classes[0]?.id.toString() || null);
const selectedSubjectId = ref<string | null>(props.subjects[0]?.id.toString() || null);
const selectedTermId = ref<string | null>(props.terms[0]?.id.toString() || null);
const analysisData = ref<any>(null);
const isLoading = ref(false);

const getAnalysis = async () => {
    if (!selectedClassId.value || !selectedSubjectId.value) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get('/api/reports/subject-analysis', {
            params: {
                class_id: selectedClassId.value,
                subject_id: selectedSubjectId.value,
                term_id: selectedTermId.value
            }
        });
        analysisData.value = response.data;
    } catch (error) {
        console.error("Analysis load failed", error);
    } finally {
        isLoading.value = false;
    }
};

const getLevelInfo = (level: number) => {
    return props.performanceLevels.find(l => l.level === level) || props.performanceLevels[1];
};

onMounted(() => {
    getAnalysis();
});

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head title="Subject Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-3 p-3 fade-in sm:p-5">
            
            <!-- HEADER SECTION -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4 mb-2 print-hide">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Diagnostic Analytics</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Subject Performance
                    </h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold flex items-center gap-2">
                        Data-driven metrics for institutional learning nodes.
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <Button variant="outline" class="h-10 text-[11px] font-black uppercase tracking-widest bg-white" @click="triggerPrint">
                        <Printer class="mr-2 h-3.5 w-3.5" /> Print PDF
                    </Button>
                </div>
            </div>

            <!-- FILTERS BAR -->
            <Card class="rounded-2xl border bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden mb-4 print-hide">
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Learning Node</label>
                            <Select v-model="selectedClassId" @update:modelValue="getAnalysis">
                                <SelectTrigger class="h-10 text-xs">
                                    <SelectValue placeholder="Select Class" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in classes" :key="c.id" :value="c.id.toString()">{{ c.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Subject Area</label>
                            <Select v-model="selectedSubjectId" @update:modelValue="getAnalysis">
                                <SelectTrigger class="h-10 text-xs">
                                    <SelectValue placeholder="Select Subject" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in subjects" :key="s.id" :value="s.id.toString()">{{ s.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Academic Term</label>
                            <Select v-model="selectedTermId" @update:modelValue="getAnalysis">
                                <SelectTrigger class="h-10 text-xs">
                                    <SelectValue placeholder="Select Term" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in terms" :key="t.id" :value="t.id.toString()">{{ t.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button @click="getAnalysis" variant="default" class="h-10 text-xs font-black uppercase tracking-widest">
                            Apply Filter
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div v-if="isLoading" class="min-h-[400px] flex flex-col items-center justify-center gap-4 opacity-50">
                <Activity class="h-8 w-8 animate-spin" />
                <span class="text-[10px] font-black uppercase tracking-widest">Calibrating Analytics...</span>
            </div>

            <div v-else-if="analysisData" class="grid grid-cols-1 lg:grid-cols-12 gap-4 fade-in">
                
                <!-- TOP STATS -->
                <div class="lg:col-span-12 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard 
                        title="Class Average" 
                        :value="analysisData.stats.average_score.toString() + '%'" 
                        :icon="TrendingUp" 
                        variant="primary" 
                        sub-title="+2.4% from last term" 
                    />
                    <StatCard 
                        title="Learners Tested" 
                        :value="analysisData.stats.total_learners.toString()" 
                        :icon="Users" 
                        variant="info" 
                        sub-title="Active roster" 
                    />
                    <StatCard 
                        title="Target Mastery" 
                        value="82%" 
                        :icon="CheckCircle2" 
                        variant="success" 
                        sub-title="Exceeding/Meeting" 
                    />
                    <StatCard 
                        title="Critical Need" 
                        :value="analysisData.stats.below.toString()" 
                        :icon="AlertTriangle" 
                        variant="danger" 
                        sub-title="Below Expectation" 
                    />
                </div>

                <!-- MAIN ANALYSIS -->
                <div class="lg:col-span-8 space-y-4">
                    
                    <!-- LEARNER ROSTER (Standard Table Layout) -->
                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between border-b px-5 py-4 bg-muted/5">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/70">Academic Matrix: {{ analysisData.subject }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-muted/10 border-b border-border/40 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                                        <th class="px-5 py-3">Learner</th>
                                        <th class="px-5 py-3 text-center">Mastery Status</th>
                                        <th class="px-5 py-3 text-center">Participation</th>
                                        <th class="px-5 py-3 text-center">Trend</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/20">
                                    <tr v-for="l in analysisData.learners" :key="l.adm" class="hover:bg-muted/5 transition-colors group">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <Avatar class="h-8 w-8 ring-1 ring-border/50">
                                                    <AvatarFallback class="bg-primary/5 text-primary text-[10px] font-black uppercase">{{ l.name.substring(0, 2) }}</AvatarFallback>
                                                </Avatar>
                                                <div class="flex flex-col leading-none">
                                                    <span class="text-xs font-black text-foreground">{{ l.name }}</span>
                                                    <span class="text-[9px] text-muted-foreground font-black uppercase mt-1 opacity-70">ADM: {{ l.adm }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <Badge variant="outline" class="font-black text-[9px] uppercase tracking-widest bg-primary/5 border-primary/20 text-primary">
                                                {{ getLevelInfo(l.level).code }}
                                            </Badge>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-[10px] font-bold text-muted-foreground">{{ l.participation }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <div class="flex justify-center">
                                                <ArrowUpRight v-if="l.trend === 'up'" class="h-4 w-4 text-emerald-500" />
                                                <ArrowDownRight v-else-if="l.trend === 'down'" class="h-4 w-4 text-rose-500" />
                                                <Minus v-else class="h-4 w-4 text-slate-300" />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR DIAGNOSTICS -->
                <div class="lg:col-span-4 space-y-4">
                    
                    <!-- INTERVENTION NEEDED (Standard Card Variant) -->
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/30 p-5 shadow-sm">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-rose-600 flex items-center gap-2 mb-4">
                            <AlertTriangle class="h-4 w-4" />
                            Priority Interventions
                        </h3>
                        <div class="space-y-3">
                            <div v-for="item in analysisData.interventions" :key="item.area" class="p-3 bg-white rounded-xl border border-rose-100 flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <h5 class="text-xs font-black text-foreground">{{ item.area }}</h5>
                                    <Badge variant="destructive" class="text-[8px] px-1.5 py-0 font-black uppercase">
                                        {{ item.priority }}
                                    </Badge>
                                </div>
                                <p class="text-[10px] font-bold text-muted-foreground">{{ item.count }} Learners affected</p>
                            </div>
                        </div>
                    </div>

                    <!-- TEACHER PROFILE SUMMARY -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <BookOpen class="h-4 w-4 text-primary" />
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground">Assigned Educator</h3>
                        </div>
                        <div class="space-y-4">
                            <h4 class="text-lg font-black tracking-tight">Lead: Mr. Robert Fox</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-muted/30 rounded-xl p-3 border border-border/40">
                                    <p class="text-[9px] font-black uppercase text-muted-foreground mb-1">Marking Load</p>
                                    <p class="text-sm font-black text-foreground">94% Done</p>
                                </div>
                                <div class="bg-muted/30 rounded-xl p-3 border border-border/40">
                                    <p class="text-[9px] font-black uppercase text-muted-foreground mb-1">Feedback Velocity</p>
                                    <p class="text-sm font-black text-foreground">2.4 Days</p>
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
.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
