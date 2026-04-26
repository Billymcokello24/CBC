<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    DollarSign,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Printer,
    FileText,
    CheckCircle2,
    List,
    ChevronRight,
    ChevronLeft,
    Wallet,
    Landmark,
    History,
    Home,
} from 'lucide-vue-next';
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
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Finance', href: '/finance' },
    { title: 'Payments', href: '/finance/payments' },
];

const searchQuery = ref('');
const formatCurrency = (amount: number | string) => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const getMethodColor = (method: string) => {
    switch (method.toLowerCase()) {
        case 'm-pesa':
            return 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/50';
        case 'bank transfer':
            return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-900/50';
        case 'cash':
            return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/50';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-950/30 dark:text-slate-400 dark:border-slate-900/50';
    }
};
</script>

<template>
    <Head title="Payments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-12 p-6 pb-20 duration-1000 fade-in slide-in-from-bottom-4"
        >
            <!-- Simple Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight text-foreground underline decoration-emerald-600 decoration-4 underline-offset-8"
                    >
                        Payments
                    </h1>
                    <p
                        class="pt-2 text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        View all fee payments and transaction records.
                    </p>
                </div>

                <Link
                    href="/finance/payments/create"
                    class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-8 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    Log New Payment
                </Link>
            </div>

            <!-- Precision Toolbar -->
            <div
                class="flex flex-col items-center gap-4 rounded-xl border border-border bg-card p-4 shadow-sm backdrop-blur-md md:flex-row dark:bg-card/50"
            >
                <div class="group relative w-full flex-1">
                    <Search
                        class="absolute top-1/2 left-6 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-emerald-600"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="SEARCH PAYMENTS..."
                        class="h-14 rounded-2xl border-transparent bg-muted/20 pl-14 text-xs font-bold tracking-tight uppercase transition-all focus:border-emerald-600/20"
                    />
                </div>
                <div class="flex w-full items-center gap-3 md:w-auto">
                    <Button
                        variant="outline"
                        class="h-14 flex-1 rounded-2xl border-border px-8 text-xs font-bold tracking-tight uppercase hover:bg-muted md:flex-none"
                    >
                        <Filter class="mr-3 h-4 w-4 opacity-40" /> Method
                    </Button>
                    <Button
                        variant="outline"
                        class="h-14 flex-1 rounded-2xl border-border px-8 text-xs font-bold tracking-tight uppercase hover:bg-muted md:flex-none"
                    >
                        Flow Cycle
                    </Button>
                </div>
            </div>

            <!-- Transaction Table -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5">
                                <th
                                    class="px-8 py-6 text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Payment ID
                                </th>
                                <th
                                    class="px-8 py-6 text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Student
                                </th>
                                <th
                                    class="px-8 py-6 text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Reference
                                </th>
                                <th
                                    class="px-8 py-6 text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Amount
                                </th>
                                <th
                                    class="px-8 py-6 text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Method
                                </th>
                                <th
                                    class="px-8 py-6 text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-8 py-6 text-right text-xs font-medium tracking-tight text-muted-foreground text-muted-foreground/40 uppercase"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="payment in payments.data"
                                :key="payment.id"
                                class="group transition-all duration-300 hover:bg-muted/30"
                            >
                                <td class="px-8 py-6">
                                    <span
                                        class="text-xs font-bold tracking-tight text-foreground uppercase transition-colors group-hover:text-emerald-600"
                                    >
                                        {{
                                            payment.transaction_id ||
                                            'LOG-' + payment.id
                                        }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div
                                        class="text-sm font-bold tracking-tight text-foreground transition-colors group-hover:text-emerald-600"
                                    >
                                        {{ payment.student?.first_name }}
                                        {{ payment.student?.last_name }}
                                    </div>
                                </td>
                                <td
                                    class="px-8 py-6 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    {{
                                        payment.invoice?.invoice_number ||
                                        'Direct'
                                    }}
                                </td>
                                <td
                                    class="px-8 py-6 text-sm font-bold tracking-tighter text-foreground"
                                >
                                    {{
                                        formatCurrency(payment.amount).split(
                                            '.',
                                        )[0]
                                    }}
                                </td>
                                <td class="px-8 py-6">
                                    <Badge
                                        variant="outline"
                                        class="gap-2 rounded-lg border-border px-4 py-1.5 text-xs font-bold tracking-tight uppercase shadow-sm transition-all group-hover:border-emerald-600/20"
                                        :class="
                                            getMethodColor(
                                                payment.payment_method,
                                            )
                                        "
                                    >
                                        <div
                                            class="h-1.5 w-1.5 animate-pulse rounded-full bg-current"
                                        ></div>
                                        {{ payment.payment_method }}
                                    </Badge>
                                </td>
                                <td
                                    class="px-8 py-6 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    {{
                                        new Date(payment.payment_date)
                                            .toLocaleDateString('en-GB', {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric',
                                            })
                                            .toUpperCase()
                                    }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="group/btn h-10 w-10 rounded-xl hover:bg-muted"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4 text-muted-foreground/40 transition-colors group-hover/btn:text-foreground"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="w-56 rounded-2xl border-border bg-card p-2 shadow-lg"
                                        >
                                            <DropdownMenuItem
                                                class="h-11 cursor-pointer rounded-xl px-4 text-xs font-bold tracking-tight uppercase"
                                            >
                                                <Eye
                                                    class="mr-3 h-4 w-4 text-emerald-600"
                                                />
                                                View Details
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                class="h-11 cursor-pointer rounded-xl px-4 text-xs font-bold tracking-tight uppercase"
                                            >
                                                <Printer
                                                    class="mr-3 h-4 w-4 text-blue-600"
                                                />
                                                Print Receipt
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                class="h-11 cursor-pointer rounded-xl px-4 text-xs font-bold tracking-tight uppercase"
                                            >
                                                <FileText
                                                    class="mr-3 h-4 w-4 text-amber-600"
                                                />
                                                Email Receipt
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="payments.data.length === 0"
                    class="flex flex-col items-center justify-center bg-muted/5 py-40"
                >
                    <History class="mb-6 h-16 w-16 text-muted-foreground/10" />
                    <p
                        class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                    >
                        No Payments found
                    </p>
                </div>

                <!-- Pulse Footer -->
                <div
                    class="flex flex-col items-center justify-between gap-6 border-t border-border/50 bg-muted/5 px-10 py-8 md:flex-row"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-500 shadow-sm"
                        ></div>
                        <span
                            class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >System Status: Online</span
                        >
                    </div>

                    <!-- Matrix Pagination -->
                    <div
                        v-if="payments.links.length > 3"
                        class="flex items-center gap-2"
                    >
                        <template v-for="(link, i) in payments.links" :key="i">
                            <Button
                                v-if="link.url || link.label.includes('...')"
                                variant="outline"
                                size="sm"
                                :disabled="!link.url"
                                :class="[
                                    'h-12 min-w-[3rem] rounded-2xl border-border px-4 text-sm font-bold tracking-tighter shadow-sm transition-all duration-300',
                                    link.active
                                        ? 'scale-105 border-emerald-600 bg-emerald-600 text-white shadow-inner shadow-emerald-600/20'
                                        : 'text-foreground hover:bg-muted',
                                ]"
                                @click="link.url && router.get(link.url)"
                            >
                                <span
                                    v-html="
                                        link.label
                                            .replace('Previous', '')
                                            .replace('Next', '')
                                    "
                                ></span>
                                <ChevronLeft
                                    v-if="link.label.includes('Previous')"
                                    class="h-4 w-4"
                                />
                                <ChevronRight
                                    v-if="link.label.includes('Next')"
                                    class="h-4 w-4"
                                />
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
