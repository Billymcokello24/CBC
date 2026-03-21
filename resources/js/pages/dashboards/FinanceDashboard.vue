<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { DollarSign, Users, TrendingUp, AlertCircle } from 'lucide-vue-next';

const props = defineProps<{
    dashboardType: string;
    feeCollection: number;
    pendingFees: number;
    todayPayments: number;
    monthlyPayments: number;
    totalStudents: number;
    notificationsCount: number;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 0 }).format(amount);
};
</script>

<template>
    <AppLayout>
        <Head title="Finance Dashboard" />
        <div class="mx-auto max-w-[90%] py-8 space-y-8">
            <div class="bg-gradient-to-r from-amber-600 to-orange-500 rounded-2xl p-8 text-white shadow-xl">
                <h1 class="text-3xl font-black">Finance Overview 💰</h1>
                <p class="mt-2 text-amber-100 text-lg">Fee collections, payments, and financial health at a glance.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-green-50 flex items-center justify-center"><DollarSign class="h-6 w-6 text-green-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Monthly Collection</p><p class="text-xl font-black text-gray-900">{{ formatCurrency(feeCollection) }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-red-50 flex items-center justify-center"><AlertCircle class="h-6 w-6 text-red-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Pending Fees</p><p class="text-xl font-black text-gray-900">{{ formatCurrency(pendingFees) }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center"><TrendingUp class="h-6 w-6 text-blue-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Today's Payments</p><p class="text-xl font-black text-gray-900">{{ formatCurrency(todayPayments) }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-purple-50 flex items-center justify-center"><Users class="h-6 w-6 text-purple-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Active Students</p><p class="text-2xl font-black text-gray-900">{{ totalStudents }}</p></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-8 text-center">
                <DollarSign class="h-16 w-16 text-amber-300 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-gray-900 mb-2">Financial Reports</h3>
                <p class="text-gray-500">Navigate to Finance → Fee Reports for detailed breakdowns, or Reports → Financial for analytics.</p>
            </div>
        </div>
    </AppLayout>
</template>
