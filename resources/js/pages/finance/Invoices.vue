<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CreditCard, Plus, Search, Filter, MoreHorizontal, Eye, Download, Send, AlertCircle, CheckCircle2, Clock, ChevronRight, ChevronLeft, Receipt, Landmark, History } from 'lucide-vue-next';
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
    { title: 'Pulse', href: '/dashboard' },
    { title: 'Capital Center', href: '/finance' },
    { title: 'Ledger Matrix', href: '/finance/invoices' },
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
        <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-20 p-6">
            <!-- Simple Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic underline decoration-blue-600 decoration-4 underline-offset-8">Ledger Matrix</h1>
                    <p class="text-[11px] font-black text-muted-foreground/60 uppercase tracking-widest pt-2">
                        Institutional billing units & liquidity tracking.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="rounded-xl h-11 px-6 border-border font-black uppercase text-[10px] tracking-widest hover:bg-muted italic" as-child>
                        <Link href="/finance/invoices/bulk-generate">Bulk Pulse</Link>
                    </Button>
                    <Link
                        href="/finance/invoices/create"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98] italic"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Gen Ledger
                    </Link>
                </div>
            </div>

            <!-- Precision Toolbar -->
            <div class="flex flex-col md:flex-row items-center gap-4 bg-card p-4 rounded-[2rem] border border-border shadow-sm dark:bg-card/50 backdrop-blur-md">
                <div class="relative flex-1 w-full group">
                    <Search class="absolute left-6 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 group-focus-within:text-blue-600 transition-colors" />
                    <Input 
                        v-model="searchQuery" 
                        placeholder="SCAN LEDGER..." 
                        class="pl-14 h-14 bg-muted/20 border-transparent focus:border-blue-600/20 rounded-2xl font-black text-[10px] uppercase tracking-widest italic transition-all"
                    />
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Button variant="outline" class="h-14 px-8 rounded-2xl border-border font-black text-[10px] uppercase tracking-widest italic hover:bg-muted flex-1 md:flex-none">
                        <Filter class="mr-3 h-4 w-4 opacity-40" /> Status
                    </Button>
                    <Button variant="outline" class="h-14 px-8 rounded-2xl border-border font-black text-[10px] uppercase tracking-widest italic hover:bg-muted flex-1 md:flex-none">
                        Cycle Range
                    </Button>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="rounded-[2.5rem] border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/5 border-b border-border/50">
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Unit ID</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Entity</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Valuation</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Deficit</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Status</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Expiry</th>
                                <th class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/40 italic">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-for="invoice in invoices.data" :key="invoice.id" class="group hover:bg-muted/30 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <span class="font-black text-[10px] text-foreground uppercase tracking-widest italic group-hover:text-blue-600 transition-colors">
                                        {{ invoice.invoice_number }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="font-black text-sm text-foreground uppercase italic tracking-tight mb-1">{{ invoice.student?.first_name }} {{ invoice.student?.last_name }}</div>
                                    <div class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest italic">{{ invoice.academic_term?.name || 'Cycle 1' }}</div>
                                </td>
                                <td class="px-8 py-6 font-black text-sm text-foreground uppercase italic tracking-tighter">
                                    {{ formatCurrency(invoice.total).split('.')[0] }}
                                </td>
                                <td class="px-8 py-6">
                                    <span :class="[
                                        'text-sm font-black uppercase italic tracking-tighter',
                                        invoice.balance > 0 ? 'text-rose-600' : 'text-emerald-600'
                                    ]">
                                        {{ formatCurrency(invoice.balance).split('.')[0] }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <Badge 
                                        variant="outline"
                                        class="rounded-lg px-4 py-1.5 border-border font-black text-[9px] uppercase tracking-widest italic gap-2 shadow-sm group-hover:border-blue-600/20 transition-all"
                                        :class="getStatusColor(invoice.status)"
                                    >
                                        <div class="h-1.5 w-1.5 rounded-full bg-current animate-pulse"></div>
                                        {{ invoice.status }}
                                    </Badge>
                                </td>
                                <td class="px-8 py-6 text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest italic">
                                    {{ new Date(invoice.due_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase() }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl hover:bg-muted group/btn">
                                                <MoreHorizontal class="h-4 w-4 text-muted-foreground/40 group-hover/btn:text-foreground transition-colors" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-56 p-2 rounded-2xl border-border bg-card shadow-2xl">
                                            <DropdownMenuItem as-child class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer">
                                                <Link :href="`/finance/invoices/${invoice.id}`"><Eye class="mr-3 h-4 w-4 text-blue-600" /> Pulse Unit</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer">
                                                <Download class="mr-3 h-4 w-4 text-emerald-600" /> Extract PDF
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer text-rose-600">
                                                <Send class="mr-3 h-4 w-4" /> Dispatch Asset
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer bg-blue-600/5 text-blue-600">
                                                <Link :href="`/finance/payments/create?invoice_id=${invoice.id}`"><Plus class="mr-3 h-4 w-4" /> Log Liquidity</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="invoices.data.length === 0" class="flex flex-col items-center justify-center py-40 bg-muted/5 italic">
                    <History class="h-16 w-16 text-muted-foreground/10 mb-6" />
                    <p class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-[0.3em]">No Ledger Activity found</p>
                </div>

                <!-- Pulse Footer -->
                <div class="px-10 py-8 bg-muted/5 border-t border-border/50 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="h-2 w-2 rounded-full bg-blue-600 animate-pulse shadow-[0_0_8px_rgba(37,99,235,0.4)]"></div>
                        <span class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] italic">Ledger Sync: Operational</span>
                    </div>
                    
                    <!-- Matrix Pagination -->
                    <div v-if="invoices.links.length > 3" class="flex items-center gap-2">
                        <template v-for="(link, i) in invoices.links" :key="i">
                            <Button 
                                v-if="link.url || link.label.includes('...')"
                                variant="outline" 
                                size="sm" 
                                :disabled="!link.url"
                                :class="[
                                    'h-12 min-w-[3rem] px-4 rounded-2xl border-border font-black text-[11px] transition-all duration-300 uppercase italic tracking-tighter shadow-sm',
                                    link.active ? 'bg-blue-600 text-white border-blue-600 shadow-blue-600/20 scale-105 shadow-inner' : 'text-foreground hover:bg-muted'
                                ]"
                                @click="link.url && router.get(link.url)"
                            >
                                <span v-html="link.label.replace('Previous', '').replace('Next', '')"></span>
                                <ChevronLeft v-if="link.label.includes('Previous')" class="h-4 w-4" />
                                <ChevronRight v-if="link.label.includes('Next')" class="h-4 w-4" />
                            </Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
