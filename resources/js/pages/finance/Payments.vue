<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { DollarSign, Plus, Search, Filter, MoreHorizontal, Eye, Printer, FileText, CheckCircle2, List } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    payments: any; // Paginator object
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Payments', href: '/finance/payments' },
];

const searchQuery = ref('');
const formatCurrency = (amount: number | string) => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-KE', { 
        style: 'currency', 
        currency: 'KES', 
        minimumFractionDigits: 0 
    }).format(value || 0);
};

const getMethodColor = (method: string) => {
    switch (method.toLowerCase()) {
        case 'm-pesa': return 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/50';
        case 'bank transfer': return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-900/50';
        case 'cash': return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/50';
        default: return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-950/30 dark:text-slate-400 dark:border-slate-900/50';
    }
};

</script>

<template>
    <Head title="Payments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                        <DollarSign class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Fee Payments</h1>
                        <p class="text-muted-foreground">Transaction history and payment records</p>
                    </div>
                </div>
                <Button as-child class="bg-emerald-600 hover:bg-emerald-700">
                    <Link href="/finance/payments/create">
                        <Plus class="mr-2 h-4 w-4" />Record New Payment
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search transactions..." class="pl-9 h-10" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10">
                        <Filter class="mr-2 h-4 w-4" />Method
                    </Button>
                    <Button variant="outline" size="sm" class="h-10">
                        Date Range
                    </Button>
                </div>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Receipt #</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Student</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Invoice #</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Amount</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Method</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Date</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="payment in payments.data" :key="payment.id" class="group hover:bg-slate-50/50 dark:hover:bg-slate-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-slate-900 dark:text-white uppercase tracking-tighter">{{ payment.transaction_id || 'REC-' + payment.id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ payment.student?.first_name }} {{ payment.student?.last_name }}</div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-600 dark:text-slate-400">
                                {{ payment.invoice?.invoice_number || 'Direct Payment' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-base">
                                    {{ formatCurrency(payment.amount) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Badge variant="outline" 
                                    class="rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                    :class="getMethodColor(payment.payment_method)"
                                >
                                    {{ payment.payment_method }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ new Date(payment.payment_date).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48">
                                        <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View Details</DropdownMenuItem>
                                        <DropdownMenuItem><Printer class="mr-2 h-4 w-4" />Print Receipt</DropdownMenuItem>
                                        <DropdownMenuItem><FileText class="mr-2 h-4 w-4" />Email Receipt</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center gap-2">
                                    <List class="h-10 w-10 opacity-20" />
                                    <p>No payment transactions found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="payments.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in payments.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors"
                            :class="link.active ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
                            v-html="link.label"
                        />
                        <span v-else 
                            class="px-4 py-2 text-sm rounded-lg border bg-slate-100 text-slate-400 cursor-not-allowed opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
