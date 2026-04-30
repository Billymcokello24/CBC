<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    ArrowLeft, 
    CreditCard, 
    FileText, 
    Calendar,
    ChevronRight,
    TrendingUp,
    AlertCircle,
    CheckCircle2,
    Clock
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    learner: {
        id: number;
        name: string;
        admission_number: string;
        class: string;
    };
    fees: any[];
    summary: {
        total: number;
        paid: number;
        balance: number;
    };
}>();

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'paid':
            return 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20';
        case 'partial':
            return 'bg-amber-500/10 text-amber-600 border-amber-500/20';
        case 'pending':
            return 'bg-rose-500/10 text-rose-600 border-rose-500/20';
        default:
            return 'bg-slate-500/10 text-slate-600 border-slate-500/20';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount);
};
</script>

<template>
    <Head :title="`Finance - ${learner.name}`" />

    <AppLayout>
        <div class="space-y-6 p-4 sm:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" class="h-10 w-10 shrink-0 rounded-xl" as-child>
                        <Link :href="`/students/${learner.id}`">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-foreground">{{ learner.name }}</h1>
                        <div class="mt-1 flex items-center gap-2 overflow-hidden text-ellipsis whitespace-nowrap">
                            <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Finance Portfolio</span>
                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                            <span class="text-[10px] font-bold tracking-widest text-primary uppercase">{{ learner.admission_number }}</span>
                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                            <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ learner.class }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" class="rounded-xl shadow-sm" as-child>
                        <Link :href="`/finance/payments/create?student_id=${learner.id}`">
                            <CreditCard class="mr-2 h-4 w-4 text-emerald-500" />
                            Receive Payment
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Financial Summary Cards -->
            <div class="grid gap-6 sm:grid-cols-3">
                <Card class="overflow-hidden rounded-3xl border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Total Ledger</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <FileText class="h-4 w-4" />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-black tracking-tight text-foreground">{{ formatCurrency(summary.total) }}</p>
                        <p class="mt-1 text-[9px] font-bold text-muted-foreground uppercase">Accumulated Lifetime Charges</p>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden rounded-3xl border-border bg-card shadow-sm transition-all hover:shadow-md">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Paid Credits</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500">
                                <CheckCircle2 class="h-4 w-4" />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-black tracking-tight text-foreground">{{ formatCurrency(summary.paid) }}</p>
                        <p class="mt-1 text-[9px] font-bold text-emerald-600 uppercase">Settled Obligations</p>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden rounded-3xl border-rose-100 bg-rose-50/30 shadow-sm transition-all hover:shadow-md dark:border-rose-900/20 dark:bg-rose-950/10">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold tracking-widest text-rose-600 uppercase opacity-80">Outstanding Debt</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500/10 text-rose-500">
                                <AlertCircle class="h-4 w-4" />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-black tracking-tight text-rose-600">{{ formatCurrency(summary.balance) }}</p>
                        <p class="mt-1 text-[9px] font-bold text-rose-600 uppercase">Immediate Liability</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Fee Timeline -->
            <Card class="overflow-hidden rounded-3xl border-border bg-card shadow-sm">
                <CardHeader class="border-b border-border bg-muted/20 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-1.5 w-8 rounded-full bg-primary"></div>
                        <div>
                            <CardTitle class="text-[10px] font-bold tracking-widest text-foreground uppercase">Financial Chronology</CardTitle>
                            <CardDescription class="text-[9px] font-bold text-muted-foreground uppercase opacity-60">Itemized Fee Obligations by Term</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-muted/10">
                                <tr class="border-b border-border">
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Academic Period</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest text-right">Total Charge</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest text-right">Paid Amount</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest text-right">Balance</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Due Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/50">
                                <tr v-for="fee in fees" :key="fee.id" class="group hover:bg-muted/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-2 rounded-full bg-slate-300 group-hover:bg-primary transition-colors"></div>
                                            <p class="text-xs font-bold text-foreground uppercase">{{ fee.term || 'General Fees' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-bold text-foreground tabular-nums">
                                        {{ formatCurrency(fee.total) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-bold text-emerald-600 tabular-nums">
                                        {{ formatCurrency(fee.paid) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-bold tabular-nums" :class="fee.balance > 0 ? 'text-rose-500' : 'text-slate-400'">
                                        {{ formatCurrency(fee.balance) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge :class="getStatusColor(fee.status)" class="rounded-lg border px-2 py-0.5 text-[9px] font-bold uppercase shadow-none">
                                            {{ fee.status }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-muted-foreground uppercase opacity-60">
                                            <Calendar class="h-3 w-3" />
                                            {{ fee.due_date || 'N/A' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!fees.length">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3 opacity-40">
                                            <AlertCircle class="h-8 w-8 text-muted-foreground" />
                                            <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase">No financial records found for this learner.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
