<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    DollarSign,
    Plus,
    Search,
    TrendingUp,
    TrendingDown,
    CreditCard,
    Receipt,
    PiggyBank,
    ArrowUpRight,
    ArrowDownRight,
    LayoutDashboard,
    History,
    Wallet,
    Landmark,
    ChevronRight,
    ChevronLeft,
} from 'lucide-vue-next';
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
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Finance', href: '/finance' },
];

import { Home } from 'lucide-vue-next';

const formatCurrency = (amount: number | string) => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
    }).format(value || 0);
};
</script>
<template>
    <Head title="Finance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-12 p-6 pb-20 duration-1000 fade-in slide-in-from-bottom-4"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Finance</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Finance Overview
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage fee collections, invoices, and payments.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                        as-child
                    >
                        <Link href="/finance/invoices/create"
                            ><Receipt class="mr-2 h-4 w-4 opacity-40" /> New Invoice</Link
                        >
                    </Button>
                    <Link
                        href="/finance/payments/create"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-8 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Log Payment
                    </Link>
                </div>
            </div>

            <!-- TailPanel Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm"
                        >
                            <TrendingUp
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Total Collections</span
                        >
                    </div>

                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Total Collections
                    </p>
                    <h3
                        class="text-2xl font-bold tracking-tighter text-foreground"
                    >
                        {{
                            formatCurrency(stats.total_collections).split(
                                '.',
                            )[0]
                        }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-amber-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <PiggyBank
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Pending Fees</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Pending Fees
                    </p>
                    <h3
                        class="text-2xl font-bold tracking-tighter text-foreground"
                    >
                        {{ formatCurrency(stats.pending_fees).split('.')[0] }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm"
                        >
                            <CreditCard
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Today</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Today's Pulse
                    </p>
                    <h3
                        class="text-2xl font-bold tracking-tighter text-foreground"
                    >
                        {{
                            formatCurrency(stats.today_collection).split('.')[0]
                        }}
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-rose-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 group-hover:bg-rose-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <TrendingDown
                                class="h-5 w-5"
                            />
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >Debt</span
                        >
                    </div>
                    <p
                        class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Overdue Leak
                    </p>
                    <h3
                        class="text-2xl font-bold tracking-tighter text-rose-600"
                    >
                        {{ formatCurrency(stats.overdue).split('.')[0] }}
                    </h3>
                </div>
            </div>
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Recent Flow -->
                <div
                    class="flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-sm lg:col-span-2 dark:border-white/5"
                >
                    <div
                        class="flex items-center justify-between border-b border-border/50 bg-muted/5 px-8 py-6"
                    >
                        <div class="flex items-center gap-3">
                            <History class="h-5 w-5 text-primary/60" />
                            <h3
                                class="text-sm font-bold tracking-tight text-foreground uppercase"
                            >
                                Recent Payments
                            </h3>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-xs font-bold tracking-tight text-muted-foreground/60 uppercase hover:text-primary"
                            as-child
                        >
                            <Link href="/finance/payments"
                                >View All <ChevronRight class="ml-1 h-3 w-3"
                            /></Link>
                        </Button>
                    </div>

                    <div class="divide-y divide-border/30">
                        <div
                            v-if="!recentPayments?.length"
                            class="flex flex-col items-center justify-center bg-muted/10 py-20"
                        >
                            <p
                                class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                            >
                                No recent payments found
                            </p>
                        </div>
                        <div
                            v-for="payment in recentPayments as any"
                            :key="payment.id"
                            class="group flex items-center justify-between px-8 py-5 transition-all hover:bg-muted/30"
                        >
                            <div class="flex items-center gap-5">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-green-600/10 bg-green-600/5 text-green-600 shadow-inner transition-all duration-500 group-hover:bg-green-600 group-hover:text-white"
                                >
                                    <Wallet
                                        class="h-5 w-5 opacity-40 group-hover:opacity-100"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-sm leading-tight font-bold tracking-tight text-foreground transition-colors group-hover:text-green-600"
                                    >
                                        {{ payment.student }}
                                    </p>
                                    <p
                                        class="text-xs leading-none font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        {{ payment.method }} •
                                        {{ payment.date }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="mb-1 text-base font-bold tracking-tighter text-foreground"
                                >
                                    {{
                                        formatCurrency(payment.amount).split(
                                            '.',
                                        )[0]
                                    }}
                                </p>
                                <Badge
                                    variant="outline"
                                    class="h-6 rounded-lg border-border px-3 text-xs font-bold tracking-[0.15em] opacity-60 transition-opacity group-hover:opacity-100"
                                >
                                    {{ payment.status }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <!-- Pulse Bar -->
                    <div
                        class="mt-auto flex items-center justify-between border-t border-border/50 bg-muted/5 p-8"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="h-2 w-2 animate-pulse rounded-full bg-green-500 shadow-sm"
                            ></div>
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >System Status: Online</span
                            >
                        </div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/20 uppercase"
                            >Verified Records</span
                        >
                    </div>
                </div>

                <!-- Quick Control & Breakdown -->
                <div class="flex flex-col space-y-8">
                    <div
                        class="group relative overflow-hidden rounded-xl border border-border bg-card p-10 shadow-sm dark:border-white/5"
                    >
                        <div
                            class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-blue-600/5 blur-3xl transition-all duration-700 group-hover:bg-blue-600/10"
                        ></div>

                        <div class="relative z-10 mb-8 flex items-center gap-3">
                            <LayoutDashboard
                                class="h-5 w-5 text-blue-600 opacity-60"
                            />
                            <h3
                                class="text-sm font-bold tracking-tight text-foreground"
                            >
                                Quick Access
                            </h3>
                        </div>

                        <div class="relative z-10 grid grid-cols-2 gap-4">
                            <Button
                                variant="outline"
                                class="group/btn h-auto flex-col gap-4 rounded-3xl border-border p-6 transition-all hover:border-blue-600/20 hover:bg-muted"
                                as-child
                            >
                                <Link href="/finance/fee-structures">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted text-muted-foreground shadow-inner transition-all group-hover/btn:bg-blue-600 group-hover/btn:text-white"
                                    >
                                        <Landmark class="h-5 w-5" />
                                    </div>
                                    <span
                                        class="text-xs font-medium tracking-tight uppercase group-hover/btn:text-blue-600"
                                        >Fees</span
                                    >
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                class="group/btn h-auto flex-col gap-4 rounded-3xl border-border p-6 transition-all hover:border-amber-500/20 hover:bg-muted"
                                as-child
                            >
                                <Link href="/finance/invoices">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted text-muted-foreground shadow-inner transition-all group-hover/btn:bg-amber-500 group-hover/btn:text-white"
                                    >
                                        <History class="h-5 w-5" />
                                    </div>
                                    <span
                                        class="font-pulsar text-xs font-medium tracking-tight uppercase group-hover/btn:text-amber-500"
                                        >Invoices</span
                                    >
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                class="group/btn h-auto flex-col gap-4 rounded-3xl border-border p-6 transition-all hover:border-emerald-500/20 hover:bg-muted"
                                as-child
                            >
                                <Link href="/finance/reports">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted text-muted-foreground shadow-inner transition-all group-hover/btn:bg-emerald-500 group-hover/btn:text-white"
                                    >
                                        <TrendingUp class="h-5 w-5" />
                                    </div>
                                    <span
                                        class="text-xs font-medium tracking-tight uppercase group-hover/btn:text-emerald-500"
                                        >Reports</span
                                    >
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                class="group/btn h-auto flex-col gap-4 rounded-3xl border-border p-6 transition-all hover:border-rose-500/20 hover:bg-muted"
                                as-child
                            >
                                <Link href="/finance/expenses">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted text-muted-foreground shadow-inner transition-all group-hover/btn:bg-rose-500 group-hover/btn:text-white"
                                    >
                                        <TrendingDown class="h-5 w-5" />
                                    </div>
                                    <span
                                        class="text-xs font-medium tracking-tight uppercase group-hover/btn:text-rose-500"
                                        >Expenses</span
                                    >
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div
                        class="group relative flex-1 overflow-hidden rounded-xl border border-border bg-slate-900 p-10 shadow-lg dark:bg-black"
                    >
                        <div
                            class="pointer-events-none absolute -right-24 -bottom-24 text-8xl font-bold tracking-tight text-white uppercase opacity-5 transition-all duration-[2000ms] select-none group-hover:scale-125"
                        >
                            FUND
                        </div>

                        <div
                            class="relative z-10 mb-10 flex items-center gap-3"
                        >
                            <span
                                class="h-1 w-8 rounded-full bg-blue-600"
                            ></span>
                            <h3
                                class="text-sm font-bold tracking-tight text-white"
                            >
                                Payment Methods
                            </h3>
                        </div>

                        <div class="relative z-10 space-y-8">
                            <div class="space-y-3">
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight text-white/40 uppercase"
                                >
                                    <span>Mobile Money</span>
                                    <span class="text-blue-400">65%</span>
                                </div>
                                <div
                                    class="h-1.5 overflow-hidden rounded-full border border-white/5 bg-white/5"
                                >
                                    <div
                                        class="h-full w-[65%] bg-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.5)]"
                                    ></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight text-white/40 uppercase"
                                >
                                    <span>Bank Transfer</span>
                                    <span class="text-emerald-400">25%</span>
                                </div>
                                <div
                                    class="h-1.5 overflow-hidden rounded-full border border-white/5 bg-white/5"
                                >
                                    <div
                                        class="h-full w-[25%] bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]"
                                    ></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight text-white/40 uppercase"
                                >
                                    <span>Physical Asset</span>
                                    <span class="font-pulsar text-amber-400"
                                        >Cash / Other</span
                                    >
                                </div>
                                <div
                                    class="h-1.5 overflow-hidden rounded-full border border-white/5 bg-white/5"
                                >
                                    <div
                                        class="h-full w-[10%] bg-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.5)]"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="relative z-10 mt-12 flex flex-col items-center justify-center rounded-3xl border border-white/10 bg-white/5 p-6 transition-all duration-700 group-hover:bg-blue-600"
                        >
                            <p
                                class="mb-2 text-xs font-bold tracking-tight text-white/40 uppercase transition-colors group-hover:text-white/60"
                            >
                                System Integrity
                            </p>
                            <p
                                class="text-xs font-bold tracking-tight text-white"
                            >
                                Balanced & Secured
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
