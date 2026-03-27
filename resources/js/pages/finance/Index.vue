<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { DollarSign, Plus, Search, TrendingUp, TrendingDown, CreditCard, Receipt, PiggyBank, ArrowUpRight, ArrowDownRight, LayoutDashboard, History, Wallet, Landmark, ChevronRight, ChevronLeft } from 'lucide-vue-next';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';
const props = defineProps<{
    recentPayments: any[];
    stats: {
        total_collections: number;
        pending_fees: number;
        today_collection: number;
        overdue: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pulse', href: '/dashboard' },
    { title: 'Capital Center', href: '/finance' },
];

const formatCurrency = (amount: number | string) => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-KE', { 
        style: 'currency', 
        currency: 'KES', 
        minimumFractionDigits: 0 
    }).format(value || 0);
};
</script>
<template>
    <Head title="Finance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-20 p-6">
            <!-- Simple Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic underline decoration-green-600 decoration-4 underline-offset-8">Capital Center</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest pt-2">
                        Command center for fee logistics and institutional liquidity.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="rounded-xl h-11 px-6 border-border font-black uppercase text-[10px] tracking-widest hover:bg-muted italic" as-child>
                        <Link href="/finance/invoices/create"><Receipt class="mr-2 h-4 w-4 opacity-40" /> Gen Invoice</Link>
                    </Button>
                    <Link
                        href="/finance/payments/create"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-green-600/20 transition-all hover:scale-[1.02] active:scale-[0.98] italic"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Log Payment
                    </Link>
                </div>
            </div>

            <!-- TailPanel Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-green-600/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-green-600/5 flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-all duration-500 shadow-inner">
                            <TrendingUp class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Inflow</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Total Collections</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic tracking-tighter">{{ formatCurrency(stats.total_collections).split('.')[0] }}</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-amber-500/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-amber-500/5 flex items-center justify-center text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500 shadow-inner">
                            <PiggyBank class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Reserves</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Pending Fees</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic tracking-tighter">{{ formatCurrency(stats.pending_fees).split('.')[0] }}</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-blue-600/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-blue-600/5 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-inner">
                            <CreditCard class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Cycle</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Today's Pulse</p>
                    <h3 class="text-2xl font-black text-foreground uppercase italic tracking-tighter">{{ formatCurrency(stats.today_collection).split('.')[0] }}</h3>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-rose-600/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-rose-600/5 flex items-center justify-center text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-all duration-500 shadow-inner">
                            <TrendingDown class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                        </div>
                        <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Debt</span>
                    </div>
                    <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Overdue Leak</p>
                    <h3 class="text-2xl font-black text-rose-600 uppercase italic tracking-tighter">{{ formatCurrency(stats.overdue).split('.')[0] }}</h3>
                </div>
            </div>
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Recent Flow -->
                <div class="lg:col-span-2 rounded-[2rem] border border-border bg-card shadow-sm dark:border-white/5 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between px-8 py-6 border-b border-border/50 bg-muted/5">
                        <div class="flex items-center gap-3">
                            <History class="h-5 w-5 text-blue-600 opacity-60" />
                            <h3 class="font-black text-sm uppercase italic tracking-widest text-foreground">Transaction Logs</h3>
                        </div>
                        <Button variant="ghost" size="sm" class="font-black text-[10px] uppercase tracking-widest text-muted-foreground/60 hover:text-blue-600 italic" as-child>
                            <Link href="/finance/payments">Full Matrix <ChevronRight class="ml-1 h-3 w-3" /></Link>
                        </Button>
                    </div>
                    
                    <div class="divide-y divide-border/30">
                        <div v-if="!(recentPayments?.length)" class="flex flex-col items-center justify-center py-20 bg-muted/10 italic">
                            <p class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-[0.2em]">Zero Activity Detected</p>
                        </div>
                        <div v-for="payment in (recentPayments as any)" :key="payment.id" class="flex items-center justify-between px-8 py-5 hover:bg-muted/30 transition-all group">
                            <div class="flex items-center gap-5">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-600/5 text-green-600 border border-green-600/10 group-hover:bg-green-600 group-hover:text-white transition-all duration-500 shadow-inner">
                                    <Wallet class="h-5 w-5 opacity-40 group-hover:opacity-100" />
                                </div>
                                <div>
                                    <p class="font-black text-foreground uppercase italic tracking-tight text-sm leading-tight mb-1 group-hover:text-green-600 transition-colors">{{ payment.student }}</p>
                                    <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest italic leading-none">{{ payment.method }} • {{ payment.date }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-foreground uppercase italic tracking-tighter text-base mb-1">{{ formatCurrency(payment.amount).split('.')[0] }}</p>
                                <Badge variant="outline" class="rounded-lg h-6 border-border font-black text-[8px] px-3 uppercase italic tracking-[0.15em] opacity-60 group-hover:opacity-100 transition-opacity">
                                    {{ payment.status }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <!-- Pulse Bar -->
                    <div class="mt-auto p-8 bg-muted/5 border-t border-border/50 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.4)]"></div>
                            <span class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] italic">Network Latency: Active</span>
                        </div>
                        <span class="text-[9px] font-black text-muted-foreground/20 uppercase tracking-[0.3em] italic">Verified Ledger</span>
                    </div>
                </div>

                <!-- Quick Control & Breakdown -->
                <div class="space-y-8 flex flex-col">
                    <div class="rounded-[2.5rem] border border-border bg-card p-10 shadow-sm dark:border-white/5 relative overflow-hidden group">
                        <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-blue-600/5 blur-3xl group-hover:bg-blue-600/10 transition-all duration-700"></div>
                        
                        <div class="flex items-center gap-3 mb-8 relative z-10">
                            <LayoutDashboard class="h-5 w-5 text-blue-600 opacity-60" />
                            <h3 class="font-black text-sm uppercase italic tracking-widest text-foreground">Quick Access</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4 relative z-10">
                            <Button variant="outline" class="h-auto flex-col gap-4 p-6 rounded-3xl border-border hover:bg-muted hover:border-blue-600/20 transition-all group/btn" as-child>
                                <Link href="/finance/fee-structures">
                                    <div class="h-10 w-10 rounded-xl bg-muted flex items-center justify-center text-muted-foreground group-hover/btn:bg-blue-600 group-hover/btn:text-white transition-all shadow-inner">
                                        <Landmark class="h-5 w-5" />
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest italic group-hover/btn:text-blue-600">Schematics</span>
                                </Link>
                            </Button>
                            <Button variant="outline" class="h-auto flex-col gap-4 p-6 rounded-3xl border-border hover:bg-muted hover:border-amber-500/20 transition-all group/btn" as-child>
                                <Link href="/finance/invoices">
                                    <div class="h-10 w-10 rounded-xl bg-muted flex items-center justify-center text-muted-foreground group-hover/btn:bg-amber-500 group-hover/btn:text-white transition-all shadow-inner">
                                        <History class="h-5 w-5" />
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest italic group-hover/btn:text-amber-500 font-pulsar">Ledger</span>
                                </Link>
                            </Button>
                            <Button variant="outline" class="h-auto flex-col gap-4 p-6 rounded-3xl border-border hover:bg-muted hover:border-emerald-500/20 transition-all group/btn" as-child>
                                <Link href="/finance/reports">
                                    <div class="h-10 w-10 rounded-xl bg-muted flex items-center justify-center text-muted-foreground group-hover/btn:bg-emerald-500 group-hover/btn:text-white transition-all shadow-inner">
                                        <TrendingUp class="h-5 w-5" />
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest italic group-hover/btn:text-emerald-500">Analytics</span>
                                </Link>
                            </Button>
                            <Button variant="outline" class="h-auto flex-col gap-4 p-6 rounded-3xl border-border hover:bg-muted hover:border-rose-500/20 transition-all group/btn" as-child>
                                <Link href="/finance/expenses">
                                    <div class="h-10 w-10 rounded-xl bg-muted flex items-center justify-center text-muted-foreground group-hover/btn:bg-rose-500 group-hover/btn:text-white transition-all shadow-inner">
                                        <TrendingDown class="h-5 w-5" />
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest italic group-hover/btn:text-rose-500">Outflow</span>
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div class="rounded-[2.5rem] border border-border bg-slate-900 p-10 shadow-2xl dark:bg-black relative overflow-hidden group flex-1">
                        <div class="absolute -right-24 -bottom-24 opacity-5 group-hover:scale-125 transition-all duration-[2000ms] text-white font-black italic uppercase text-8xl select-none pointer-events-none tracking-widest">
                            FUND
                        </div>
                        
                        <div class="flex items-center gap-3 mb-10 relative z-10">
                            <span class="h-1 w-8 bg-blue-600 rounded-full"></span>
                            <h3 class="font-black text-sm uppercase italic tracking-widest text-white">Source Breakdown</h3>
                        </div>

                        <div class="space-y-8 relative z-10">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-white/40 italic">
                                    <span>Mobile Matrix</span>
                                    <span class="text-blue-400">65%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-white/5 border border-white/5 overflow-hidden">
                                    <div class="h-full w-[65%] bg-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-white/40 italic">
                                    <span>Direct Wire</span>
                                    <span class="text-emerald-400">25%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-white/5 border border-white/5 overflow-hidden">
                                    <div class="h-full w-[25%] bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]"></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-white/40 italic">
                                    <span>Physical Asset</span>
                                    <span class="text-amber-400 font-pulsar">10%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-white/5 border border-white/5 overflow-hidden">
                                    <div class="h-full w-[10%] bg-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.5)]"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 flex flex-col items-center justify-center p-6 rounded-3xl bg-white/5 border border-white/10 group-hover:bg-blue-600 transition-all duration-700 relative z-10">
                            <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] mb-2 group-hover:text-white/60 transition-colors italic">System Integrity</p>
                            <p class="text-xs font-black text-white uppercase italic tracking-widest">Balanced & Secured</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
