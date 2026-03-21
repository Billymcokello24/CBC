<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Receipt, Plus, Search, Filter, MoreHorizontal, Edit, Trash2, CheckCircle2, XCircle } from 'lucide-vue-next';
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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Fee Structures', href: '/finance/fee-structures' },
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

const filteredStructures = computed(() => {
    if (!searchQuery.value) return props.structures;
    const q = searchQuery.value.toLowerCase();
    return props.structures.filter(s => 
        s.name.toLowerCase().includes(q) || 
        s.grade_level?.name.toLowerCase().includes(q)
    );
});
</script>

<template>
    <Head title="Fee Structures" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <Receipt class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Fee Structures</h1>
                        <p class="text-muted-foreground">Define and manage fee structures for different grades</p>
                    </div>
                </div>
                <Button as-child variant="default" class="bg-indigo-600 hover:bg-indigo-700">
                    <Link href="/finance/fee-structures/create">
                        <Plus class="mr-2 h-4 w-4" />Create Structure
                    </Link>
                </Button>
            </div>

            <div class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search structures..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Filters
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="structure in filteredStructures" :key="structure.id" 
                    class="group relative overflow-hidden rounded-2xl border bg-card p-6 transition-all hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-800"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <Badge variant="outline" class="mb-2 bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-950/30 dark:text-indigo-400 dark:border-indigo-900/50">
                                {{ structure.academic_year?.name || '2026' }}
                            </Badge>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ structure.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ structure.grade_level?.name || 'All Grades' }}</p>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-slate-600"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-40">
                                <DropdownMenuItem as-child><Link :href="`/finance/fee-structures/${structure.id}`"><Eye class="mr-2 h-4 w-4" />View Details</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/finance/fee-structures/${structure.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Tuition Fee</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ formatCurrency(structure.tuition_fee) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Other Fees</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ formatCurrency(structure.other_fee) }}</span>
                        </div>
                        <div class="pt-3 border-t flex justify-between">
                            <span class="font-semibold text-slate-900 dark:text-white">Total</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(structure.total_fee) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <Badge :variant="structure.is_active ? 'default' : 'secondary'" 
                            class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                            :class="structure.is_active ? 'bg-emerald-500 hover:bg-emerald-600' : ''"
                        >
                            {{ structure.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </div>
                
                <div v-if="filteredStructures.length === 0" class="col-span-full py-12 text-center text-muted-foreground">
                    <Receipt class="h-12 w-12 mx-auto mb-4 opacity-20" />
                    <p>No fee structures found.</p>
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
