<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Download, Banknote, BadgeDollarSign, Receipt, AlertCircle, Search, Activity, Loader2 } from 'lucide-vue-next';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Tooltip, Legend, Filler);

const props = defineProps({ classes: Array });
const searchQuery = ref('');
const analysis = ref(null);
const loading = ref(true);
const exporting = ref(false);

const formatCurrency = (value) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(value || 0);

const fetchAnalysis = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.reports.finance-analysis'), { params: { search: searchQuery.value } });
        analysis.value = response.data;
    } catch(error) { console.error("Failed to fetch finance analysis", error); }
    finally { loading.value = false; }
};

onMounted(() => fetchAnalysis());

const startExport = async () => {
    exporting.value = true;
    try {
        const { data } = await axios.post(route('exports.start'), {
            type: 'finance_report',
            filters: {}
        });
        window.dispatchEvent(new CustomEvent('export-started', { detail: { id: data.id } }));
    } catch (error) {
        console.error("Failed to start finance export", error);
    } finally {
        setTimeout(() => { exporting.value = false; }, 2000);
    }
};

let searchTimeout = null;
const onSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchAnalysis(), 400); };

const trendChart = computed(() => ({
    labels: analysis.value?.monthly_trend?.labels || [],
    datasets: [{ label: 'Collections (KES)', data: analysis.value?.monthly_trend?.data || [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, pointRadius: 4 }]
}));

const paidVsOutstanding = computed(() => ({
    labels: Object.keys(analysis.value?.paid_vs_outstanding || {}),
    datasets: [{ data: Object.values(analysis.value?.paid_vs_outstanding || {}), backgroundColor: ['#10b981', '#f43f5e'], borderWidth: 0 }]
}));

const methodChart = computed(() => {
    const breakdown = analysis.value?.method_breakdown || {};
    return {
        labels: Object.keys(breakdown),
        datasets: [{ data: Object.values(breakdown), backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'], borderWidth: 0 }]
    };
});

const chartOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, font: { size: 10 } } } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } };
const doughnutOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } }, cutout: '65%' };
</script>

<template>
    <AppLayout>
        <Head title="Financial Report" />
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8 fade-in">
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Fee Collection</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">Financial Report</h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold">Billing, payments, outstanding balances, and collection trends</p>
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
                    <StatCard title="Total Billed" :value="formatCurrency(analysis.stats.total_billed)" subTitle="Fee invoices sent" :icon="Receipt" variant="primary" />
                    <StatCard title="Total Paid" :value="formatCurrency(analysis.stats.total_paid)" subTitle="Payments received" :icon="Banknote" variant="success" />
                    <StatCard title="Outstanding" :value="formatCurrency(analysis.stats.outstanding)" subTitle="Remaining balance" :icon="AlertCircle" variant="danger" />
                    <StatCard title="Collection Rate" :value="`${analysis.stats.collection_rate}%`" subTitle="Payment efficiency" :icon="BadgeDollarSign" variant="info" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <Card v-if="analysis.monthly_trend?.labels?.length > 0" class="lg:col-span-2 rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Monthly Collection Trend</CardTitle><CardDescription class="text-xs">Amount collected per month over the last 6 months</CardDescription></CardHeader>
                        <CardContent class="h-[260px]"><Line :data="trendChart" :options="chartOpts" /></CardContent>
                    </Card>
                    <Card class="rounded-2xl border bg-card shadow-sm">
                        <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Paid vs Outstanding</CardTitle><CardDescription class="text-xs">Overall collection status</CardDescription></CardHeader>
                        <CardContent class="h-[260px] flex items-center justify-center">
                            <Doughnut v-if="analysis.stats.total_billed > 0" :data="paidVsOutstanding" :options="doughnutOpts" />
                            <p v-else class="text-xs text-muted-foreground font-bold">No financial data yet</p>
                        </CardContent>
                    </Card>
                </div>

                <Card v-if="Object.keys(analysis.method_breakdown || {}).length > 0" class="rounded-2xl border bg-card shadow-sm">
                    <CardHeader class="pb-2"><CardTitle class="text-sm font-black">Payments by Method</CardTitle><CardDescription class="text-xs">How payments were made (M-Pesa, Bank, Cash, etc.)</CardDescription></CardHeader>
                    <CardContent class="h-[220px] flex items-center justify-center max-w-[400px] mx-auto">
                        <Doughnut :data="methodChart" :options="doughnutOpts" />
                    </CardContent>
                </Card>

                <Card class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <CardHeader class="pb-4 border-b border-border/40">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div><CardTitle class="text-sm font-black">Student Fee Balances</CardTitle><CardDescription class="text-xs">{{ analysis.records?.length || 0 }} students with invoices</CardDescription></div>
                            <div class="relative w-full sm:w-64"><Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" /><Input v-model="searchQuery" @input="onSearch" placeholder="Search student..." class="pl-9 h-9 text-xs rounded-xl" /></div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="bg-muted/30 text-muted-foreground text-[10px] uppercase tracking-widest font-black"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Adm No</th><th class="px-5 py-3 text-right">Billed</th><th class="px-5 py-3 text-right">Paid</th><th class="px-5 py-3 text-right">Balance</th><th class="px-5 py-3 text-right">Status</th></tr></thead>
                                <tbody class="divide-y divide-border/30">
                                    <tr v-for="r in analysis.records" :key="r.adm" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground text-xs">{{ r.student }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground text-xs">{{ r.adm }}</td>
                                        <td class="px-5 py-3.5 text-right text-xs font-bold">{{ formatCurrency(r.billed) }}</td>
                                        <td class="px-5 py-3.5 text-right text-xs font-bold text-emerald-600">{{ formatCurrency(r.paid) }}</td>
                                        <td class="px-5 py-3.5 text-right text-xs font-bold text-rose-600">{{ formatCurrency(r.balance) }}</td>
                                        <td class="px-5 py-3.5 text-right"><span :class="r.status === 'Cleared' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'" class="px-2.5 py-1 text-[9px] uppercase tracking-wider font-black rounded-full">{{ r.status }}</span></td>
                                    </tr>
                                    <tr v-if="!analysis.records?.length"><td colspan="6" class="px-5 py-12 text-center text-muted-foreground text-xs font-bold">No financial records found.</td></tr>
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
