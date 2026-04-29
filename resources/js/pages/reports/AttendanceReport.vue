<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Download, Users, CheckCircle, AlertTriangle, Activity, Search, CalendarDays, TrendingUp, Loader2 } from 'lucide-vue-next';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Tooltip, Legend, Filler);

const props = defineProps({ classes: Array });

const selectedClass = ref('');
const searchQuery = ref('');
const analysis = ref(null);
const loading = ref(true);
const exporting = ref(false);

const fetchAnalysis = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.reports.attendance-analysis'), {
            params: { class_id: selectedClass.value, search: searchQuery.value }
        });
        analysis.value = response.data;
    } catch(error) {
        console.error("Failed to fetch attendance analysis", error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => fetchAnalysis());
watch([selectedClass], () => fetchAnalysis());

const startExport = async () => {
    exporting.value = true;
    try {
        const { data } = await axios.post(route('exports.start'), {
            type: 'attendance_report',
            filters: { class_id: selectedClass.value }
        });
        window.dispatchEvent(new CustomEvent('export-started', { detail: { id: data.id } }));
    } catch (error) {
        console.error("Failed to start attendance export", error);
    } finally {
        setTimeout(() => { exporting.value = false; }, 2000);
    }
};

let searchTimeout = null;
const onSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchAnalysis(), 400);
};

const getStatusColor = (rate) => {
    if (rate >= 90) return 'bg-emerald-100 text-emerald-800';
    if (rate >= 75) return 'bg-blue-100 text-blue-800';
    return 'bg-rose-100 text-rose-800';
};

const trendChartData = computed(() => ({
    labels: analysis.value?.monthly_trend?.labels || [],
    datasets: [
        {
            label: 'Present',
            data: analysis.value?.monthly_trend?.present || [],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true, tension: 0.4, pointRadius: 4,
        },
        {
            label: 'Total Recorded',
            data: analysis.value?.monthly_trend?.total || [],
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true, tension: 0.4, pointRadius: 4,
        }
    ]
}));

const distributionChartData = computed(() => ({
    labels: ['Present', 'Absent', 'Late'],
    datasets: [{
        data: [
            analysis.value?.distribution?.present || 0,
            analysis.value?.distribution?.absent || 0,
            analysis.value?.distribution?.late || 0,
        ],
        backgroundColor: ['#10b981', '#f43f5e', '#f59e0b'],
        borderWidth: 0,
    }]
}));

const classBarData = computed(() => ({
    labels: (analysis.value?.class_comparison || []).map(c => c.class),
    datasets: [{
        label: 'Attendance Rate (%)',
        data: (analysis.value?.class_comparison || []).map(c => c.rate),
        backgroundColor: '#6366f1',
        borderRadius: 6,
    }]
}));

const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9'} }, x: { grid: { display: false} } } };
const lineOptions = { ...chartOptions, plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, font: { size: 10 } } } } };
const doughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } }, cutout: '65%' };
</script>

<template>
    <AppLayout>
        <Head title="Attendance Report" />
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8 fade-in">
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Attendance Tracking</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Attendance Report
                    </h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold">
                        Track daily presence, spot trends, and find students who need help
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-end pb-1">
                    <select v-model="selectedClass" class="rounded-xl border border-border bg-card text-xs font-bold h-10 w-full sm:w-[180px] px-3 focus:ring-primary focus:border-primary" id="attendance-class-filter">
                        <option value="">All Classes</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <button @click="startExport" :disabled="exporting"
                       class="inline-flex items-center justify-center h-10 px-4 bg-primary text-primary-foreground rounded-xl shadow-sm text-[11px] font-black uppercase tracking-widest gap-2 hover:opacity-90 transition-all disabled:opacity-50" id="attendance-download-pdf">
                        <Loader2 v-if="exporting" class="w-3.5 h-3.5 animate-spin" />
                        <Download v-else class="w-3.5 h-3.5" /> 
                        {{ exporting ? 'Starting...' : 'Download PDF' }}
                    </button>
                </div>
            </div>

            <div v-if="loading" class="flex flex-col items-center justify-center py-20 opacity-50">
                <Activity class="h-8 w-8 animate-spin mb-4" />
                <span class="text-[10px] font-black uppercase tracking-widest">Loading data...</span>
            </div>

            <div v-else-if="analysis" class="space-y-6">
                <!-- STAT CARDS -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard title="Attendance Rate" :value="`${analysis.stats.overall_rate}%`" subTitle="Average across all days" :icon="Activity" variant="primary" />
                    <StatCard title="Total Students" :value="String(analysis.stats.total_learners)" subTitle="Currently tracked" :icon="Users" variant="info" />
                    <StatCard title="Excellent (95%+)" :value="String(analysis.stats.perfect_attendance)" subTitle="Students with high attendance" :icon="CheckCircle" variant="success" />
                    <StatCard title="Needs Help (<75%)" :value="String(analysis.stats.needs_attention)" subTitle="Students falling behind" :icon="AlertTriangle" variant="danger" />
                </div>
                
                <!-- CHARTS ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Monthly Trend (Line) -->
                    <Card v-if="analysis.monthly_trend?.labels?.length > 0" class="lg:col-span-2 rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-black">Monthly Attendance Trend</CardTitle>
                            <CardDescription class="text-xs">Present students vs total records over the last 6 months</CardDescription>
                        </CardHeader>
                        <CardContent class="h-[260px]">
                            <Line :data="trendChartData" :options="lineOptions" />
                        </CardContent>
                    </Card>

                    <!-- Status Distribution (Doughnut) -->
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-black">Status Breakdown</CardTitle>
                            <CardDescription class="text-xs">Present, absent, and late split</CardDescription>
                        </CardHeader>
                        <CardContent class="h-[260px] flex items-center justify-center">
                            <Doughnut v-if="(analysis.distribution?.present || 0) + (analysis.distribution?.absent || 0) > 0" :data="distributionChartData" :options="doughnutOptions" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No attendance records yet</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Class Comparison Bar (only when viewing all classes) -->
                <Card v-if="analysis.class_comparison?.length > 0" class="rounded-2xl border bg-card shadow-sm">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-black">Attendance by Class</CardTitle>
                        <CardDescription class="text-xs">How each class compares in attendance rate</CardDescription>
                    </CardHeader>
                    <CardContent class="h-[240px]">
                        <Bar :data="classBarData" :options="chartOptions" />
                    </CardContent>
                </Card>

                <!-- SEARCH + DATA TABLE -->
                <Card class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <CardTitle class="text-sm font-black">Student Attendance List</CardTitle>
                                <CardDescription class="text-xs">{{ analysis.learners?.length || 0 }} students showing</CardDescription>
                            </div>
                            <div class="relative w-full sm:w-64">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                                <Input v-model="searchQuery" @input="onSearch" placeholder="Search by name or admission..." class="pl-9 h-9 text-xs rounded-xl" id="attendance-search" />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black">
                                    <tr>
                                        <th class="px-5 py-3">Student</th>
                                        <th class="px-5 py-3">Adm No</th>
                                        <th class="px-5 py-3 text-center">Present</th>
                                        <th class="px-5 py-3 text-center">Absent</th>
                                        <th class="px-5 py-3 text-center">Late</th>
                                        <th class="px-5 py-3 min-w-[160px]">Attendance Rate</th>
                                        <th class="px-5 py-3 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="learner in analysis.learners" :key="learner.adm" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs">{{ learner.name }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground text-xs">{{ learner.adm }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs text-emerald-600 font-bold">{{ learner.present_days }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs text-rose-600 font-bold">{{ learner.absent_days }}</td>
                                        <td class="px-5 py-3.5 text-center text-xs text-amber-600 font-bold">{{ learner.late_days }}</td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2">
                                                <div class="w-full max-w-[100px] h-2 bg-muted rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all" 
                                                         :class="learner.rate >= 90 ? 'bg-emerald-500' : (learner.rate >= 75 ? 'bg-blue-500' : 'bg-rose-500')" 
                                                         :style="{ width: `${learner.rate}%` }"></div>
                                                </div>
                                                <span class="text-[11px] font-black text-foreground w-8">{{ learner.rate }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <span :class="getStatusColor(learner.rate)" class="px-2.5 py-1 text-[9px] uppercase tracking-wider font-black rounded-full">
                                                {{ learner.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!analysis.learners || analysis.learners.length === 0">
                                        <td colspan="7" class="px-5 py-12 text-center text-muted-foreground text-xs font-bold">
                                            No attendance records found. Records will appear here once attendance is marked.
                                        </td>
                                    </tr>
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
