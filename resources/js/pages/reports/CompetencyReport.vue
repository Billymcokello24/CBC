<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Download, CheckCircle, Award, Target, Search, Activity, Loader2 } from 'lucide-vue-next';
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
        const response = await axios.get(route('api.reports.competency-analysis'), { params: { class_id: selectedClass.value, search: searchQuery.value } });
        analysis.value = response.data;
    } catch(error) { console.error("Failed to fetch competency analysis", error); }
    finally { loading.value = false; }
};


const levelColors = { EE: '#10b981', ME: '#6366f1', AE: '#f59e0b', BE: '#f43f5e' };
const levelLabels = { EE: 'Exceeding', ME: 'Meeting', AE: 'Approaching', BE: 'Below' };

const distributionChart = computed(() => ({
    labels: Object.keys(analysis.value?.distribution || {}).map(k => levelLabels[k] || k),
    datasets: [{ data: Object.values(analysis.value?.distribution || {}), backgroundColor: Object.keys(analysis.value?.distribution || {}).map(k => levelColors[k]), borderWidth: 0 }]
}));

const competencyBarChart = computed(() => {
    const comps = analysis.value?.competencies || [];
    return {
        labels: comps.map(c => c.competency.length > 20 ? c.competency.substring(0, 20) + '...' : c.competency),
        datasets: [
            { label: 'EE', data: comps.map(c => c.ee), backgroundColor: '#10b981', borderRadius: 4 },
            { label: 'ME', data: comps.map(c => c.me), backgroundColor: '#6366f1', borderRadius: 4 },
            { label: 'AE', data: comps.map(c => c.ae), backgroundColor: '#f59e0b', borderRadius: 4 },
            { label: 'BE', data: comps.map(c => c.be), backgroundColor: '#f43f5e', borderRadius: 4 },
        ]
    };
});

const chartOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, font: { size: 10 } } } }, scales: { y: { beginAtZero: true, stacked: true, grid: { color: '#f1f5f9' } }, x: { stacked: true, grid: { display: false } } } };
const doughnutOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } }, cutout: '65%' };

onMounted(() => fetchAnalysis());
watch([selectedClass], () => fetchAnalysis());

const startExport = async () => {
    exporting.value = true;
    try {
        const { data } = await axios.post(route('exports.start'), {
            type: 'competency_report',
            filters: { class_id: selectedClass.value }
        });
        window.dispatchEvent(new CustomEvent('export-started', { detail: { id: data.id } }));
    } catch (error) {
        console.error("Failed to start competency export", error);
    } finally {
        setTimeout(() => { exporting.value = false; }, 2000);
    }
};

let searchTimeout = null;
const onSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchAnalysis(), 400); };
</script>

<template>
    <AppLayout>
        <Head title="Competency Report" />
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8 fade-in">
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Competency Mastery</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">Competency Report</h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold">Deep dive into competency distribution and mastery levels across subjects</p>
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
                    <StatCard title="Competencies Tracked" :value="String(analysis.stats.total_tracked)" subTitle="Unique skills" :icon="Target" variant="primary" />
                    <StatCard title="Total Ratings" :value="String(analysis.stats.total_ratings)" subTitle="Individual entries" :icon="Award" variant="info" />
                    <StatCard title="Exceeding (EE)" :value="String(analysis.stats.overall_ee)" subTitle="Top performers" :icon="CheckCircle" variant="success" />
                    <StatCard title="Meeting (ME)" :value="String(analysis.stats.overall_me)" subTitle="On track" :icon="CheckCircle" variant="warning" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <Card v-if="analysis.competencies?.length > 0" class="lg:col-span-2 rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Competency Levels (Stacked)</CardTitle><CardDescription class="text-xs">How each competency is rated across EE, ME, AE, BE</CardDescription></CardHeader>
                        <CardContent class="h-[280px]"><Bar :data="competencyBarChart" :options="chartOpts" /></CardContent>
                    </Card>
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Overall Distribution</CardTitle><CardDescription class="text-xs">All ratings combined</CardDescription></CardHeader>
                        <CardContent class="h-[280px] flex items-center justify-center">
                            <Doughnut v-if="analysis.stats.total_ratings > 0" :data="distributionChart" :options="doughnutOpts" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No competency data yet</p>
                        </CardContent>
                    </Card>
                </div>

                <Card class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div><CardTitle class="text-sm font-black">Competency Details</CardTitle><CardDescription class="text-xs">{{ analysis.competencies?.length || 0 }} competencies tracked</CardDescription></div>
                            <div class="relative w-full sm:w-64"><Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" /><Input v-model="searchQuery" @input="onSearch" placeholder="Search competency..." class="pl-9 h-9 text-xs rounded-xl" /></div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black"><tr><th class="px-5 py-3">Competency</th><th class="px-5 py-3">Subject</th><th class="px-5 py-3 text-center">EE</th><th class="px-5 py-3 text-center">ME</th><th class="px-5 py-3 text-center">AE</th><th class="px-5 py-3 text-center">BE</th><th class="px-5 py-3 text-center">Total</th></tr></thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="c in analysis.competencies" :key="c.competency" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs max-w-[200px] truncate">{{ c.competency }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground text-xs">{{ c.subject }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold text-emerald-600">{{ c.ee }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold text-indigo-600">{{ c.me }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold text-amber-600">{{ c.ae }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold text-rose-600">{{ c.be }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-black">{{ c.total }}</td>
                                    </tr>
                                    <tr v-if="!analysis.competencies?.length"><td colspan="7" class="px-5 py-12 text-center text-muted-foreground text-xs font-bold">No competency mastery data found.</td></tr>
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
