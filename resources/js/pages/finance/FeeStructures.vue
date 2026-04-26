<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Receipt,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Edit,
    Trash2,
    CheckCircle2,
    XCircle,
    ChevronRight,
    ChevronLeft,
    Eye,
    LayoutDashboard,
    History,
    Landmark,
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
    structures: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Finance', href: '/finance' },
    { title: 'Fees', href: '/finance/fee-structures' },
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

const filteredStructures = computed(() => {
    if (!searchQuery.value) return props.structures;
    const q = searchQuery.value.toLowerCase();
    return props.structures.filter(
        (s) =>
            s.name.toLowerCase().includes(q) ||
            s.grade_level?.name.toLowerCase().includes(q),
    );
});
</script>

<template>
    <Head title="Fee Structures" />
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
                        class="text-3xl font-bold tracking-tight text-foreground underline decoration-indigo-600 decoration-4 underline-offset-8"
                    >
                        Fee Structures
                    </h1>
                    <p
                        class="pt-2 text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Set up school fees for each class and term.
                    </p>
                </div>

                <Link
                    href="/finance/fee-structures/create"
                    class="inline-flex h-11 items-center justify-center rounded-xl bg-indigo-600 px-8 text-xs font-medium tracking-tight text-white uppercase shadow-lg shadow-indigo-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Fee Structure
                </Link>
            </div>

            <!-- Precision Toolbar -->
            <div
                class="flex flex-col items-center gap-4 rounded-xl border border-border bg-card p-4 shadow-sm backdrop-blur-md md:flex-row dark:bg-card/50"
            >
                <div class="group relative w-full flex-1">
                    <Search
                        class="absolute top-1/2 left-6 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-indigo-600"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="SEARCH FEES..."
                        class="h-14 rounded-2xl border-transparent bg-muted/20 pl-14 text-xs font-bold tracking-tight uppercase transition-all focus:border-indigo-600/20"
                    />
                </div>
                <Button
                    variant="outline"
                    class="h-14 w-full rounded-2xl border-border px-8 text-xs font-bold tracking-tight uppercase hover:bg-muted md:w-auto"
                >
                    <Filter class="mr-3 h-4 w-4 opacity-40" /> Filters
                </Button>
            </div>

            <!-- Schematic Grid -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="structure in filteredStructures"
                    :key="structure.id"
                    class="group relative overflow-hidden rounded-xl border border-border bg-card p-10 shadow-sm transition-all duration-500 hover:border-indigo-600/20 hover:shadow-lg dark:border-white/5"
                >
                    <div
                        class="absolute -top-16 -right-16 h-32 w-32 rounded-full bg-indigo-600/5 blur-3xl transition-all duration-700 group-hover:bg-indigo-600/10"
                    ></div>

                    <div class="relative z-10 flex items-start justify-between">
                        <div class="space-y-3">
                            <Badge
                                variant="outline"
                                class="h-7 rounded-lg border-border bg-indigo-600/5 px-3 text-xs font-bold tracking-tight text-indigo-600"
                            >
                                CYCLE
                                {{ structure.academic_year?.name || '2026' }}
                            </Badge>
                            <h3
                                class="text-xl leading-tight font-bold tracking-tight text-foreground transition-colors group-hover:text-indigo-600"
                            >
                                {{ structure.name }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <Landmark
                                    class="h-3 w-3 text-muted-foreground/40"
                                />
                                <span
                                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >{{
                                        structure.grade_level?.name ||
                                        'ALL UNITS'
                                    }}</span
                                >
                            </div>
                        </div>
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
                                class="w-48 rounded-2xl border-border bg-card p-2 shadow-lg"
                            >
                                <DropdownMenuItem
                                    as-child
                                    class="h-11 cursor-pointer rounded-xl px-4 text-xs font-bold tracking-tight uppercase"
                                >
                                    <Link
                                        :href="`/finance/fee-structures/${structure.id}`"
                                        ><Eye
                                            class="mr-3 h-4 w-4 text-blue-600"
                                        />
                                        View Details</Link
                                    >
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    as-child
                                    class="h-11 cursor-pointer rounded-xl px-4 text-xs font-bold tracking-tight uppercase"
                                >
                                    <Link
                                        :href="`/finance/fee-structures/${structure.id}/edit`"
                                        ><Edit
                                            class="mr-3 h-4 w-4 text-emerald-600"
                                        />
                                        Edit</Link
                                    >
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="h-11 cursor-pointer rounded-xl px-4 text-xs font-bold tracking-tight text-rose-600 uppercase"
                                >
                                    <Trash2 class="mr-3 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="relative z-10 mt-10 space-y-6">
                        <div
                            class="group/item flex items-center justify-between rounded-2xl p-4 transition-colors hover:bg-muted/50"
                        >
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                                >Tuition Fees</span
                            >
                            <span
                                class="text-sm font-bold tracking-tighter text-foreground"
                                >{{
                                    formatCurrency(structure.tuition_fee).split(
                                        '.',
                                    )[0]
                                }}</span
                            >
                        </div>
                        <div
                            class="group/item flex items-center justify-between rounded-2xl p-4 transition-colors hover:bg-muted/50"
                        >
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                                >Other Fees</span
                            >
                            <span
                                class="text-sm font-bold tracking-tighter text-foreground"
                                >{{
                                    formatCurrency(structure.other_fee).split(
                                        '.',
                                    )[0]
                                }}</span
                            >
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-border/50 px-4 pt-6"
                        >
                            <span
                                class="text-sm font-bold tracking-tight text-foreground uppercase"
                                >Total Amount</span
                            >
                            <span
                                class="text-2xl font-bold tracking-tighter text-indigo-600"
                                >{{
                                    formatCurrency(structure.total_fee).split(
                                        '.',
                                    )[0]
                                }}</span
                            >
                        </div>
                    </div>

                    <div
                        class="relative z-10 mt-8 flex items-center justify-between"
                    >
                        <Badge
                            variant="outline"
                            class="h-8 gap-2 rounded-full border-border px-5 text-xs font-bold tracking-tight uppercase transition-all"
                            :class="
                                structure.is_active
                                    ? 'border-emerald-600/20 bg-emerald-600/10 text-emerald-600'
                                    : 'bg-muted text-muted-foreground'
                            "
                        >
                            <div
                                class="h-1.5 w-1.5 rounded-full bg-current"
                                :class="
                                    structure.is_active ? 'animate-pulse' : ''
                                "
                            ></div>
                            {{ structure.is_active ? 'Active' : 'Inactive' }}
                        </Badge>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-border bg-muted/50 shadow-inner transition-all duration-500 group-hover:border-indigo-600 group-hover:bg-indigo-600"
                        >
                            <ChevronRight
                                class="h-4 w-4 text-muted-foreground/40 transition-colors group-hover:text-white"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="filteredStructures.length === 0"
                    class="col-span-full rounded-2xl border border-dashed border-border bg-muted/5 py-40 text-center"
                >
                    <Receipt
                        class="mx-auto mb-8 h-20 w-20 text-muted-foreground/10"
                    />
                    <p
                        class="text-sm font-bold tracking-tight text-muted-foreground/30 uppercase"
                    >
                        No schematic data detected
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-pulsar {
    font-family: 'Inter', sans-serif;
}
</style>
