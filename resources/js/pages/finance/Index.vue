<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { DollarSign, Plus, Search, TrendingUp, TrendingDown, CreditCard, Receipt, PiggyBank, ArrowUpRight, ArrowDownRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
];
const recentPayments = ref([
    { id: 1, student: 'John Kamau', amount: 25000, date: '2026-03-10', method: 'M-Pesa', status: 'completed' },
    { id: 2, student: 'Mary Wanjiku', amount: 15000, date: '2026-03-10', method: 'Bank Transfer', status: 'completed' },
    { id: 3, student: 'Peter Ochieng', amount: 30000, date: '2026-03-09', method: 'M-Pesa', status: 'completed' },
    { id: 4, student: 'Sarah Muthoni', amount: 20000, date: '2026-03-09', method: 'Cash', status: 'pending' },
]);
const formatCurrency = (amount: number) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 0 }).format(amount);
</script>
<template>
    <Head title="Finance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                        <DollarSign class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Finance</h1>
                        <p class="text-muted-foreground">Manage fees, payments and financial reports</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child><Link href="/finance/invoices/create"><Receipt class="mr-2 h-4 w-4" />Generate Invoice</Link></Button>
                    <Button as-child><Link href="/finance/payments/create"><Plus class="mr-2 h-4 w-4" />Record Payment</Link></Button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-6 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-green-500/10 p-2"><TrendingUp class="h-5 w-5 text-green-600" /></div>
                        <span class="flex items-center text-sm text-green-600"><ArrowUpRight class="h-4 w-4" />12%</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Total Collections</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(2850000) }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 dark:from-amber-950/50 dark:to-amber-900/30">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-amber-500/10 p-2"><PiggyBank class="h-5 w-5 text-amber-600" /></div>
                        <span class="flex items-center text-sm text-amber-600"><ArrowDownRight class="h-4 w-4" />5%</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Pending Fees</p>
                        <p class="text-2xl font-bold text-amber-600">{{ formatCurrency(450000) }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2"><CreditCard class="h-5 w-5 text-blue-600" /></div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Today's Collection</p>
                        <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(125000) }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-red-50 to-red-100/50 p-6 dark:from-red-950/50 dark:to-red-900/30">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-red-500/10 p-2"><TrendingDown class="h-5 w-5 text-red-600" /></div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Overdue</p>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency(85000) }}</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl border bg-card">
                    <div class="flex items-center justify-between border-b p-4">
                        <h3 class="font-semibold">Recent Payments</h3>
                        <Button variant="ghost" size="sm" as-child><Link href="/finance/payments">View All</Link></Button>
                    </div>
                    <div class="divide-y">
                        <div v-for="payment in recentPayments" :key="payment.id" class="flex items-center justify-between p-4 hover:bg-muted/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                    <DollarSign class="h-5 w-5 text-green-600" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ payment.student }}</p>
                                    <p class="text-sm text-muted-foreground">{{ payment.method }} • {{ payment.date }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">{{ formatCurrency(payment.amount) }}</p>
                                <Badge :variant="payment.status === 'completed' ? 'default' : 'secondary'" class="mt-1">{{ payment.status }}</Badge>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="rounded-xl border bg-card p-6">
                        <h3 class="font-semibold mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <Button variant="outline" class="h-auto flex-col gap-2 p-4" as-child>
                                <Link href="/finance/fee-structures"><Receipt class="h-5 w-5" /><span class="text-xs">Fee Structure</span></Link>
                            </Button>
                            <Button variant="outline" class="h-auto flex-col gap-2 p-4" as-child>
                                <Link href="/finance/invoices"><CreditCard class="h-5 w-5" /><span class="text-xs">Invoices</span></Link>
                            </Button>
                            <Button variant="outline" class="h-auto flex-col gap-2 p-4" as-child>
                                <Link href="/finance/reports"><TrendingUp class="h-5 w-5" /><span class="text-xs">Reports</span></Link>
                            </Button>
                            <Button variant="outline" class="h-auto flex-col gap-2 p-4" as-child>
                                <Link href="/finance/expenses"><TrendingDown class="h-5 w-5" /><span class="text-xs">Expenses</span></Link>
                            </Button>
                        </div>
                    </div>
                    <div class="rounded-xl border bg-card p-6">
                        <h3 class="font-semibold mb-4">Collection by Method</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between"><span class="text-sm">M-Pesa</span><span class="font-medium">65%</span></div>
                            <div class="h-2 rounded-full bg-muted"><div class="h-full w-[65%] rounded-full bg-green-500"></div></div>
                            <div class="flex items-center justify-between"><span class="text-sm">Bank Transfer</span><span class="font-medium">25%</span></div>
                            <div class="h-2 rounded-full bg-muted"><div class="h-full w-[25%] rounded-full bg-blue-500"></div></div>
                            <div class="flex items-center justify-between"><span class="text-sm">Cash</span><span class="font-medium">10%</span></div>
                            <div class="h-2 rounded-full bg-muted"><div class="h-full w-[10%] rounded-full bg-amber-500"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
