<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Download, Users, UserCircle, Search, Activity, Loader2 } from 'lucide-vue-next';
import { Doughnut, Bar, Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Tooltip, Legend, Filler);

const props = defineProps({ classes: Array });
const selectedClass = ref('');
const selectedGender = ref('');
const selectedStatus = ref('');
const searchQuery = ref('');
const analysis = ref(null);
const loading = ref(true);
const exporting = ref(false);

const fetchAnalysis = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.reports.learner-analysis'), { params: { class_id: selectedClass.value, gender: selectedGender.value, status: selectedStatus.value, search: searchQuery.value } });
        analysis.value = response.data;
    } catch(error) { console.error("Failed to fetch learner analysis", error); }
    finally { loading.value = false; }
};

onMounted(() => fetchAnalysis());
watch([selectedClass, selectedGender, selectedStatus], () => fetchAnalysis());

const startExport = async () => {
    exporting.value = true;
    try {
        const { data } = await axios.post(route('exports.start'), {
            type: 'learner_report',
            filters: { class_id: selectedClass.value, gender: selectedGender.value, status: selectedStatus.value }
        });
        window.dispatchEvent(new CustomEvent('export-started', { detail: { id: data.id } }));
    } catch (error) {
        console.error("Failed to start learner export", error);
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

const statusChart = computed(() => ({
    labels: Object.keys(analysis.value?.status_distribution || {}),
    datasets: [{ data: Object.values(analysis.value?.status_distribution || {}), backgroundColor: ['#10b981', '#f43f5e', '#6366f1', '#f59e0b'], borderWidth: 0 }]
}));

const classBarChart = computed(() => ({
    labels: (analysis.value?.class_enrollment || []).map(c => c.class),
    datasets: [{ label: 'Students', data: (analysis.value?.class_enrollment || []).map(c => c.count), backgroundColor: '#6366f1', borderRadius: 6 }]
}));

const growthChart = computed(() => ({
    labels: analysis.value?.growth_trend?.labels || [],
    datasets: [{ label: 'New Students', data: analysis.value?.growth_trend?.data || [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, pointRadius: 4 }]
}));

const chartOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } };
const doughnutOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } }, cutout: '65%' };
const lineOpts = { ...chartOpts, plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, font: { size: 10 } } } } };

const getStatusBadge = (status) => {
    const map = { Active: 'bg-emerald-100 text-emerald-800', Suspended: 'bg-rose-100 text-rose-800', Graduated: 'bg-blue-100 text-blue-800', Withdrawn: 'bg-slate-100 text-slate-600' };
    return map[status] || 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <AppLayout>
        <Head title="Learner Profile Report" />
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8 fade-in">
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Student Profiles</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">Learner Report</h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold">Full list of students with gender, class, and status breakdown</p>
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
                    <StatCard title="Total Students" :value="String(analysis.stats.total)" subTitle="All registered" :icon="Users" variant="primary" />
                    <StatCard title="Active" :value="String(analysis.stats.active)" subTitle="Currently attending" :icon="UserCircle" variant="success" />
                    <StatCard title="Male" :value="String(analysis.stats.male)" subTitle="Male population" :icon="UserCircle" variant="info" />
                    <StatCard title="Female" :value="String(analysis.stats.female)" subTitle="Female population" :icon="UserCircle" variant="warning" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Gender Split</CardTitle></CardHeader>
                        <CardContent class="h-[200px] flex items-center justify-center">
                            <Doughnut v-if="analysis.stats.total > 0" :data="genderChart" :options="doughnutOpts" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No students yet</p>
                        </CardContent>
                    </Card>
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Status Breakdown</CardTitle></CardHeader>
                        <CardContent class="h-[200px] flex items-center justify-center">
                            <Doughnut v-if="analysis.stats.total > 0" :data="statusChart" :options="doughnutOpts" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No data</p>
                        </CardContent>
                    </Card>
                    <Card v-if="analysis.class_enrollment?.length > 0" class="lg:col-span-2 rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Students per Class</CardTitle></CardHeader>
                        <CardContent class="h-[200px]"><Bar :data="classBarChart" :options="chartOpts" /></CardContent>
                    </Card>
                </div>

                <Card v-if="analysis.growth_trend?.labels?.length > 0" class="rounded-2xl border bg-card shadow-sm">
                    <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Enrollment Growth Trend</CardTitle><CardDescription class="text-xs">New students added per month (last 6 months)</CardDescription></CardHeader>
                    <CardContent class="h-[220px]"><Line :data="growthChart" :options="lineOpts" /></CardContent>
                </Card>

                <!-- Filters + Data Table -->
                <Card class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div><CardTitle class="text-sm font-black">Student Directory</CardTitle><CardDescription class="text-xs">{{ analysis.learners?.length || 0 }} students</CardDescription></div>
                            <div class="flex flex-wrap gap-2 items-center">
                                <select v-model="selectedClass" class="rounded-xl border border-border bg-card text-[10px] font-bold h-8 px-2">
                                    <option value="">All Classes</option>
                                    <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <select v-model="selectedGender" class="rounded-xl border border-border bg-card text-[10px] font-bold h-8 px-2">
                                    <option value="">All Genders</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <select v-model="selectedStatus" class="rounded-xl border border-border bg-card text-[10px] font-bold h-8 px-2">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="graduated">Graduated</option>
                                </select>
                                <div class="relative w-48"><Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground" /><Input v-model="searchQuery" @input="onSearch" placeholder="Search..." class="pl-8 h-8 text-[10px] rounded-xl" /></div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black"><tr><th class="px-5 py-3">Student Name</th><th class="px-5 py-3">Adm No</th><th class="px-5 py-3">Gender</th><th class="px-5 py-3">Class</th><th class="px-5 py-3 text-right">Status</th></tr></thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="l in analysis.learners" :key="l.adm" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs">{{ l.name }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground text-xs">{{ l.adm }}</td>
                                        <td class="px-5 py-3.5 text-xs">{{ l.gender }}</td>
                                        <td class="px-5 py-3.5 text-xs">{{ l.class }}</td>
                                        <td class="px-5 py-3.5 text-right"><span :class="getStatusBadge(l.status)" class="px-2.5 py-1 text-[9px] uppercase tracking-wider font-black rounded-full">{{ l.status }}</span></td>
                                    </tr>
                                    <tr v-if="!analysis.learners?.length"><td colspan="5" class="px-5 py-12 text-center text-muted-foreground text-xs font-bold">No students found matching your filters.</td></tr>
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
