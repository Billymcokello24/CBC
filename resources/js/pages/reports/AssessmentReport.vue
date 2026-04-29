<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Download, Zap, BookOpen, Users, BarChart3, Search, Activity, Loader2 } from 'lucide-vue-next';
import { Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

const props = defineProps({ classes: Array });
const selectedClass = ref('');
const searchQuery = ref('');
const analysis = ref(null);
const loading = ref(true);
const exporting = ref(false);

const fetchAnalysis = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.reports.assessment-analysis'), { params: { class_id: selectedClass.value, search: searchQuery.value } });
        analysis.value = response.data;
    } catch(error) { console.error("Failed to fetch assessment analysis", error); }
    finally { loading.value = false; }
};

onMounted(() => fetchAnalysis());
watch([selectedClass], () => fetchAnalysis());

const startExport = async () => {
    exporting.value = true;
    try {
        const { data } = await axios.post(route('exports.start'), {
            type: 'assessment_report',
            filters: { class_id: selectedClass.value }
        });
        window.dispatchEvent(new CustomEvent('export-started', { detail: { id: data.id } }));
    } catch (error) {
        console.error("Failed to start assessment export", error);
    } finally {
        setTimeout(() => { exporting.value = false; }, 2000);
    }
};
let searchTimeout = null;
const onSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchAnalysis(), 400); };

const levelColors = { EE: '#10b981', ME: '#6366f1', AE: '#f59e0b', BE: '#f43f5e' };
const levelLabels = { EE: 'Exceeding', ME: 'Meeting', AE: 'Approaching', BE: 'Below' };

const distributionChart = computed(() => ({
    labels: Object.keys(analysis.value?.distribution || {}).map(k => levelLabels[k] || k),
    datasets: [{ data: Object.values(analysis.value?.distribution || {}), backgroundColor: Object.keys(analysis.value?.distribution || {}).map(k => levelColors[k]), borderWidth: 0 }]
}));

const subjectBarChart = computed(() => ({
    labels: (analysis.value?.subjects || []).map(s => s.subject),
    datasets: [{ label: 'Average Score (%)', data: (analysis.value?.subjects || []).map(s => s.average_score), backgroundColor: '#6366f1', borderRadius: 6 }]
}));

const chartOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } };
const doughnutOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } }, cutout: '65%' };

const getLevelBadge = (level) => {
    const map = { EE: 'bg-emerald-100 text-emerald-800', ME: 'bg-blue-100 text-blue-800', AE: 'bg-amber-100 text-amber-800', BE: 'bg-rose-100 text-rose-800' };
    return map[level] || 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <AppLayout>
        <Head title="Assessment Report" />
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8 fade-in">
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Academic Assessment</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">Assessment Report</h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold">Subject scores, performance levels, and student progress tracking</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-end pb-1">
                    <select v-model="selectedClass" class="rounded-xl border border-border bg-card text-xs font-bold h-10 w-full sm:w-[180px] px-3 focus:ring-primary focus:border-primary">
                        <option value="">All Classes</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <button @click="startExport" :disabled="exporting" class="inline-flex items-center justify-center h-10 px-4 bg-primary text-primary-foreground rounded-xl shadow-sm text-[11px] font-black uppercase tracking-widest gap-2 hover:opacity-90 transition-all disabled:opacity-50">
                        <Loader2 v-if="exporting" class="w-3.5 h-3.5 animate-spin" />
                        <Download v-else class="w-3.5 h-3.5" /> 
                        {{ exporting ? 'Starting...' : 'Download PDF' }}
                    </button>
                </div>
            </div>

            <div v-if="loading" class="flex flex-col items-center justify-center py-20 opacity-50"><Activity class="h-8 w-8 animate-spin mb-4" /><span class="text-[10px] font-black uppercase tracking-widest">Loading data...</span></div>

            <div v-else-if="analysis" class="space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard title="Subjects Assessed" :value="String(analysis.stats.total_subjects)" subTitle="Learning areas" :icon="BookOpen" variant="primary" />
                    <StatCard title="Students Assessed" :value="String(analysis.stats.total_learners_assessed)" subTitle="With ratings" :icon="Users" variant="info" />
                    <StatCard title="Overall Average" :value="`${analysis.stats.overall_average}%`" subTitle="Across all subjects" :icon="BarChart3" variant="success" />
                    <StatCard title="Total Ratings" :value="String(analysis.stats.total_ratings)" subTitle="Individual entries" :icon="Zap" variant="warning" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <Card v-if="analysis.subjects?.length > 0" class="lg:col-span-2 rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Average Score by Subject</CardTitle><CardDescription class="text-xs">How each subject is performing overall</CardDescription></CardHeader>
                        <CardContent class="h-[260px]"><Bar :data="subjectBarChart" :options="chartOpts" /></CardContent>
                    </Card>
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Performance Levels</CardTitle><CardDescription class="text-xs">EE / ME / AE / BE distribution</CardDescription></CardHeader>
                        <CardContent class="h-[260px] flex items-center justify-center">
                            <Doughnut v-if="analysis.stats.total_learners_assessed > 0" :data="distributionChart" :options="doughnutOpts" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No assessments recorded yet</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Subject table -->
                <Card v-if="analysis.subjects?.length > 0" class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40"><CardTitle class="text-sm font-black">Subject Summary</CardTitle></CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black"><tr><th class="px-5 py-3">Subject</th><th class="px-5 py-3 text-center">Students</th><th class="px-5 py-3">Score</th><th class="px-5 py-3 text-right">Status</th></tr></thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="s in analysis.subjects" :key="s.subject" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs">{{ s.subject }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs text-muted-foreground">{{ s.total_assessed }}</td>
                                        <td class="px-5 py-3.5"><div class="flex items-center gap-2"><div class="w-full max-w-[100px] h-2 bg-muted rounded-full overflow-hidden"><div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${s.average_score}%` }"></div></div><span class="text-[11px] font-black">{{ s.average_score }}%</span></div></td>
                                        <td class="px-5 py-3.5 text-right"><span :class="s.status === 'Excellent' ? 'bg-emerald-100 text-emerald-800' : (s.status === 'Average' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')" class="px-2.5 py-1 text-[9px] uppercase tracking-wider font-black rounded-full">{{ s.status }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Learner list -->
                <Card class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div><CardTitle class="text-sm font-black">Student Performance</CardTitle><CardDescription class="text-xs">{{ analysis.learners?.length || 0 }} students assessed</CardDescription></div>
                            <div class="relative w-full sm:w-64"><Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" /><Input v-model="searchQuery" @input="onSearch" placeholder="Search student..." class="pl-9 h-9 text-xs rounded-xl" /></div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Adm No</th><th class="px-5 py-3 text-center">Subjects</th><th class="px-5 py-3">Score</th><th class="px-5 py-3 text-right">Level</th></tr></thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="l in analysis.learners" :key="l.adm" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs">{{ l.name }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground text-xs">{{ l.adm }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold">{{ l.subjects_count }}</td>
                                        <td class="px-5 py-3.5"><div class="flex items-center gap-2"><div class="w-full max-w-[100px] h-2 bg-muted rounded-full overflow-hidden"><div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${l.score}%` }"></div></div><span class="text-[11px] font-black">{{ l.score }}%</span></div></td>
                                        <td class="px-5 py-3.5 text-right"><span :class="getLevelBadge(l.level)" class="px-2.5 py-1 text-[9px] uppercase tracking-wider font-black rounded-full">{{ l.level }}</span></td>
                                    </tr>
                                    <tr v-if="!analysis.learners?.length"><td colspan="5" class="px-5 py-12 text-center text-muted-foreground text-xs font-bold">No assessment data found.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>.fade-in { animation: fadeIn 0.6s ease forwards; } @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }</style>
