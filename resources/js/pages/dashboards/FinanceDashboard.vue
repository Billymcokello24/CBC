<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import {
    CreditCard,
    Wallet,
    TrendingUp,
    Zap,
    Users,
    ArrowUpRight,
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
    {
        title: 'Collection (Month)',
        value: 'KES ' + props.feeCollection.toLocaleString(),
        icon: Wallet,
        color: 'blue',
    },
    {
        title: 'Pending Fees',
        value: 'KES ' + props.pendingFees.toLocaleString(),
        icon: CreditCard,
        color: 'rose',
    },
    {
        title: "Today's Payments",
        value: 'KES ' + props.todayPayments.toLocaleString(),
        icon: ArrowUpRight,
        color: 'emerald',
    },
    {
        title: 'Total Students',
        value: props.totalStudents.toString(),
        icon: Users,
        color: 'indigo',
    },
];
</script>

<template>
    <Head title="Finance Dashboard" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Finance Overview
                    </h2>
                    <p class="text-sm text-slate-500">
                        School Financial Dashboard
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    v-for="stat in stats"
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                    :color="stat.color"
                />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <TrendingUp class="h-5 w-5 text-emerald-500" />
                        Transactions Summary
                    </h3>

                    <div class="space-y-6">
                        <div
                            class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold tracking-wider text-emerald-600 uppercase"
                                >
                                    Monthly Count
                                </p>
                                <h4 class="text-xl font-bold text-emerald-900">
                                    {{ monthlyPayments }}
                                </h4>
                            </div>
                            <div class="rounded-xl bg-white/50 p-3">
                                <ArrowUpRight
                                    class="h-6 w-6 text-emerald-600"
                                />
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-2xl border border-blue-100 bg-blue-50 p-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold tracking-wider text-blue-600 uppercase"
                                >
                                    Active Learners
                                </p>
                                <h4 class="text-xl font-bold text-blue-900">
                                    {{ totalStudents }}
                                </h4>
                            </div>
                            <div class="rounded-xl bg-white/50 p-3">
                                <Users class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-center rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-4 text-center">
                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Zap class="h-10 w-10 text-slate-300" />
                        </div>
                        <h4 class="font-bold text-slate-800">
                            More Financial Insights
                        </h4>
                        <p class="mx-auto max-w-xs text-sm text-slate-500">
                            Detailed reports and fee structure management coming
                            soon.
                        </p>
                        <button
                            class="rounded-xl bg-slate-800 px-6 py-2 text-sm font-bold text-white transition-colors hover:bg-slate-700"
                        >
                            Go to Finance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
