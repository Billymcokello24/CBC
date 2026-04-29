<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Download, Users, UserPlus, UserCheck, Search, Activity, TrendingUp, Loader2 } from 'lucide-vue-next';
import { Doughnut, Bar, Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Tooltip, Legend, Filler);

const props = defineProps({ classes: Array });
const analysis = ref(null);
const loading = ref(true);
const exporting = ref(false);
const searchQuery = ref('');

const fetchAnalysis = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.reports.enrollment-analysis'), { params: { search: searchQuery.value } });
        analysis.value = response.data;
    } catch(error) { console.error("Failed to fetch enrollment analysis", error); }
    finally { loading.value = false; }
};

onMounted(() => fetchAnalysis());

const startExport = async () => {
    exporting.value = true;
    try {
        const { data } = await axios.post(route('exports.start'), {
            type: 'enrollment_report',
            filters: {}
        });
        window.dispatchEvent(new CustomEvent('export-started', { detail: { id: data.id } }));
    } catch (error) {
        console.error("Failed to start enrollment export", error);
    } finally {
        setTimeout(() => { exporting.value = false; }, 2000);
    }
};

let searchTimeout = null;
const onSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchAnalysis(), 400); };

const genderChart = computed(() => ({
    labels: Object.keys(analysis.value?.gender_split || {}),
    datasets: [{ data: Object.values(analysis.value?.gender_split || {}), backgroundColor: ['#6366f1', '#ec4899'], borderWidth: 0 }]
}));

const gradeBarChart = computed(() => ({
    labels: (analysis.value?.grade_totals || []).map(g => g.grade),
    datasets: [{ label: 'Students', data: (analysis.value?.grade_totals || []).map(g => g.count), backgroundColor: '#6366f1', borderRadius: 6 }]
}));

const growthLineChart = computed(() => ({
    labels: analysis.value?.growth_trend?.labels || [],
    datasets: [{ label: 'New Enrollments', data: analysis.value?.growth_trend?.data || [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, pointRadius: 4 }]
}));

const chartOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } };
const doughnutOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } }, cutout: '65%' };
const lineOpts = { ...chartOpts, plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, font: { size: 10 } } } } };
</script>

<template>
    <AppLayout>
        <Head title="Enrollment Report" />
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8 fade-in">
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Enrollment Data</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">Enrollment Report</h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold">Student distribution across classes, gender breakdown, and growth trends</p>
                </div>
                <div class="flex items-end pb-1">
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
                    <StatCard title="Total Students" :value="String(analysis.stats.total_learners)" subTitle="All registered" :icon="Users" variant="primary" />
                    <StatCard title="Active" :value="String(analysis.stats.active)" subTitle="Currently attending" :icon="UserCheck" variant="success" />
                    <StatCard title="Boys" :value="String(analysis.stats.boys)" subTitle="Male students" :icon="UserPlus" variant="info" />
                    <StatCard title="Girls" :value="String(analysis.stats.girls)" subTitle="Female students" :icon="UserPlus" variant="warning" />
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <Card v-if="analysis.growth_trend?.labels?.length > 0" class="lg:col-span-2 rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Enrollment Growth</CardTitle><CardDescription class="text-xs">New students enrolled per month over the last 6 months</CardDescription></CardHeader>
                        <CardContent class="h-[260px]"><Line :data="growthLineChart" :options="lineOpts" /></CardContent>
                    </Card>
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Gender Split</CardTitle><CardDescription class="text-xs">Boys vs Girls</CardDescription></CardHeader>
                        <CardContent class="h-[260px] flex items-center justify-center">
                            <Doughnut v-if="analysis.stats.total_learners > 0" :data="genderChart" :options="doughnutOpts" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No students enrolled yet</p>
                        </CardContent>
                    </Card>
                </div>

                <Card v-if="analysis.grade_totals?.length > 0" class="rounded-2xl border bg-card shadow-sm">
                    <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Students by Grade Level</CardTitle><CardDescription class="text-xs">How many students are in each grade</CardDescription></CardHeader>
                    <CardContent class="h-[240px]"><Bar :data="gradeBarChart" :options="chartOpts" /></CardContent>
                </Card>

                <Card class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div><CardTitle class="text-sm font-black">Class Breakdown</CardTitle><CardDescription class="text-xs">{{ analysis.classes?.length || 0 }} classes</CardDescription></div>
                            <div class="relative w-full sm:w-64"><Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" /><Input v-model="searchQuery" @input="onSearch" placeholder="Search class or grade..." class="pl-9 h-9 text-xs rounded-xl" /></div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black">
                                    <tr><th class="px-5 py-3">Class</th><th class="px-5 py-3">Grade</th><th class="px-5 py-3 text-center">Boys</th><th class="px-5 py-3 text-center">Girls</th><th class="px-5 py-3 text-center">Total</th><th class="px-5 py-3 text-right">Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="c in analysis.classes" :key="c.class" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs">{{ c.class }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground text-xs">{{ c.grade }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold text-indigo-600">{{ c.boys }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-bold text-pink-600">{{ c.girls }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs font-black text-foreground">{{ c.total }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            <span :class="c.status === 'Optimal' ? 'bg-emerald-100 text-emerald-800' : (c.status === 'Empty' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-800')" class="px-2.5 py-1 text-[9px] uppercase tracking-wider font-black rounded-full">{{ c.status }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!analysis.classes?.length"><td colspan="6" class="px-5 py-12 text-center text-muted-foreground text-xs font-bold">No classes found.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-in { animation: fadeIn 0.6s ease forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>
