<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, Plus, Search, Filter, MoreHorizontal, Eye, Download, Send, AlertCircle, CheckCircle2, Clock } from 'lucide-vue-next';
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
    invoices: any; // Paginator object
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Invoices', href: '/finance/invoices' },
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

const getStatusVariant = (status: string) => {
    switch (status.toLowerCase()) {
        case 'paid': return 'default';
        case 'pending': return 'secondary';
        case 'overdue': return 'destructive';
        case 'partial': return 'outline';
        default: return 'secondary';
    }
};

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'paid': return 'bg-emerald-500 hover:bg-emerald-600';
        case 'pending': return 'bg-amber-500 hover:bg-amber-600';
        case 'overdue': return 'bg-rose-500 hover:bg-rose-600';
        case 'partial': return 'bg-blue-500 text-white border-blue-500';
        default: return '';
    }
};

const getStatusIcon = (status: string) => {
    switch (status.toLowerCase()) {
        case 'paid': return CheckCircle2;
        case 'pending': return Clock;
        case 'overdue': return AlertCircle;
        case 'partial': return CheckCircle2;
        default: return Clock;
    }
};

</script>

<template>
    <Head title="Invoices" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <CreditCard class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Invoices</h1>
                        <p class="text-muted-foreground">Manage student fee invoices and billing status</p>
                    </div>
                </div>
                <div class="flex gap-2">
                     <Button variant="outline" as-child>
                        <Link href="/finance/invoices/bulk-generate">
                            Bulk Generate
                        </Link>
                    </Button>
                    <Button as-child class="bg-blue-600 hover:bg-blue-700">
                        <Link href="/finance/invoices/create">
                            <Plus class="mr-2 h-4 w-4" />New Invoice
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search invoices..." class="pl-9 h-10" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10">
                        <Filter class="mr-2 h-4 w-4" />Status
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
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Invoice #</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Student</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Amount</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Balance</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Due Date</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="invoice in invoices.data" :key="invoice.id" class="group hover:bg-slate-50/50 dark:hover:bg-slate-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ invoice.invoice_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ invoice.student?.first_name }} {{ invoice.student?.last_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ invoice.academic_term?.name || 'Term 1' }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ formatCurrency(invoice.total) }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="invoice.balance > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-emerald-600 dark:text-emerald-400 font-bold'">
                                    {{ formatCurrency(invoice.balance) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :variant="getStatusVariant(invoice.status)" 
                                    class="rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider gap-1.5"
                                    :class="getStatusColor(invoice.status)"
                                >
                                    <component :is="getStatusIcon(invoice.status)" class="h-3 w-3" />
                                    {{ invoice.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ new Date(invoice.due_date).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48">
                                        <DropdownMenuItem as-child><Link :href="`/finance/invoices/${invoice.id}`"><Eye class="mr-2 h-4 w-4" />View Invoice</Link></DropdownMenuItem>
                                        <DropdownMenuItem><Download class="mr-2 h-4 w-4" />Download PDF</DropdownMenuItem>
                                        <DropdownMenuItem><Send class="mr-2 h-4 w-4" />Send to Parent</DropdownMenuItem>
                                        <DropdownMenuItem as-child><Link :href="`/finance/payments/create?invoice_id=${invoice.id}`"><Plus class="mr-2 h-4 w-4" />Record Payment</Link></DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="invoices.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center gap-2">
                                    <Clock class="h-10 w-10 opacity-20" />
                                    <p>No invoices found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Simple Pagination -->
            <div v-if="invoices.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in invoices.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
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
