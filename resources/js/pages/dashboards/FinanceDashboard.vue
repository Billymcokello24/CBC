<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from "@/components/dashboard/StatCard.vue";
import { 
    CreditCard, 
    Wallet,
    TrendingUp,
    Zap,
    Users,
    ArrowUpRight
} from 'lucide-vue-next';

interface Props {
    feeCollection: number;
    pendingFees: number;
    todayPayments: number;
    monthlyPayments: number;
    totalStudents: number;
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    { title: 'Collection (Month)', value: 'KES ' + props.feeCollection.toLocaleString(), icon: Wallet, color: 'blue' },
    { title: 'Pending Fees', value: 'KES ' + props.pendingFees.toLocaleString(), icon: CreditCard, color: 'rose' },
    { title: 'Today\'s Payments', value: 'KES ' + props.todayPayments.toLocaleString(), icon: ArrowUpRight, color: 'emerald' },
    { title: 'Total Students', value: props.totalStudents.toString(), icon: Users, color: 'indigo' },
];
</script>

<template>
    <Head title="Finance Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Finance Overview</h2>
                    <p class="text-slate-500 text-sm">School Financial Dashboard</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard 
                    v-for="stat in stats" 
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                    :color="stat.color"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <TrendingUp class="w-5 h-5 text-emerald-500" />
                        Transactions Summary
                    </h3>
                    
                    <div class="space-y-6">
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Monthly Count</p>
                                <h4 class="text-xl font-bold text-emerald-900">{{ monthlyPayments }}</h4>
                            </div>
                            <div class="p-3 bg-white/50 rounded-xl">
                                <ArrowUpRight class="w-6 h-6 text-emerald-600" />
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Active Learners</p>
                                <h4 class="text-xl font-bold text-blue-900">{{ totalStudents }}</h4>
                            </div>
                            <div class="p-3 bg-white/50 rounded-xl">
                                <Users class="w-6 h-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex items-center justify-center">
                    <div class="text-center space-y-4">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto">
                            <Zap class="w-10 h-10 text-slate-300" />
                        </div>
                        <h4 class="text-slate-800 font-bold">More Financial Insights</h4>
                        <p class="text-slate-500 text-sm max-w-xs mx-auto">Detailed reports and fee structure management coming soon.</p>
                        <button class="px-6 py-2 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-700 transition-colors">
                            Go to Finance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
