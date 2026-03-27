<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Receipt, Plus, Search, Filter, MoreHorizontal, Edit, Trash2, CheckCircle2, XCircle, ChevronRight, ChevronLeft, Eye, LayoutDashboard, History, Landmark } from 'lucide-vue-next';
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
    { title: 'Pulse', href: '/dashboard' },
    { title: 'Capital Center', href: '/finance' },
    { title: 'Fee Schematic', href: '/finance/fee-structures' },
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
        <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-20 p-6">
            <!-- Simple Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic underline decoration-indigo-600 decoration-4 underline-offset-8">Fee Schematic</h1>
                    <p class="text-[11px] font-black text-muted-foreground/60 uppercase tracking-widest pt-2">
                        Architectural fee blueprints and allocation structures.
                    </p>
                </div>
                
                <Link
                    href="/finance/fee-structures/create"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-600/20 transition-all hover:scale-[1.02] active:scale-[0.98] italic"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Schematic
                </Link>
            </div>

            <!-- Precision Toolbar -->
            <div class="flex flex-col md:flex-row items-center gap-4 bg-card p-4 rounded-[2rem] border border-border shadow-sm dark:bg-card/50 backdrop-blur-md">
                <div class="relative flex-1 w-full group">
                    <Search class="absolute left-6 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 group-focus-within:text-indigo-600 transition-colors" />
                    <Input 
                        v-model="searchQuery" 
                        placeholder="SCAN SCHEMATICS..." 
                        class="pl-14 h-14 bg-muted/20 border-transparent focus:border-indigo-600/20 rounded-2xl font-black text-[10px] uppercase tracking-widest italic transition-all"
                    />
                </div>
                <Button variant="outline" class="h-14 px-8 rounded-2xl border-border font-black text-[10px] uppercase tracking-widest italic hover:bg-muted w-full md:w-auto">
                    <Filter class="mr-3 h-4 w-4 opacity-40" /> Filters
                </Button>
            </div>

            <!-- Schematic Grid -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="structure in filteredStructures" :key="structure.id" 
                    class="group relative overflow-hidden rounded-[2.5rem] border border-border bg-card p-10 shadow-sm transition-all duration-500 hover:shadow-2xl hover:border-indigo-600/20 dark:border-white/5"
                >
                    <div class="absolute -right-16 -top-16 h-32 w-32 rounded-full bg-indigo-600/5 blur-3xl group-hover:bg-indigo-600/10 transition-all duration-700"></div>
                    
                    <div class="flex items-start justify-between relative z-10">
                        <div class="space-y-3">
                            <Badge variant="outline" class="rounded-lg h-7 border-border bg-indigo-600/5 text-indigo-600 font-black text-[8px] px-3 uppercase italic tracking-widest">
                                CYCLE {{ structure.academic_year?.name || '2026' }}
                            </Badge>
                            <h3 class="text-xl font-black text-foreground uppercase italic tracking-tight group-hover:text-indigo-600 transition-colors leading-tight">{{ structure.name }}</h3>
                            <div class="flex items-center gap-2">
                                <Landmark class="h-3 w-3 text-muted-foreground/40" />
                                <span class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest italic">{{ structure.grade_level?.name || 'ALL UNITS' }}</span>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl hover:bg-muted group/btn">
                                    <MoreHorizontal class="h-4 w-4 text-muted-foreground/40 group-hover/btn:text-foreground transition-colors" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48 p-2 rounded-2xl border-border bg-card shadow-2xl">
                                <DropdownMenuItem as-child class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer">
                                    <Link :href="`/finance/fee-structures/${structure.id}`"><Eye class="mr-3 h-4 w-4 text-blue-600" /> Pulse Unit</Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer">
                                    <Link :href="`/finance/fee-structures/${structure.id}/edit`"><Edit class="mr-3 h-4 w-4 text-emerald-600" /> Modify</Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem class="rounded-xl h-11 px-4 font-black text-[10px] uppercase tracking-widest italic cursor-pointer text-rose-600">
                                    <Trash2 class="mr-3 h-4 w-4" /> Purge
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="mt-10 space-y-6 relative z-10">
                        <div class="flex justify-between items-center group/item p-4 rounded-2xl hover:bg-muted/50 transition-colors">
                            <span class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest italic">Core Tuition</span>
                            <span class="font-black text-sm text-foreground uppercase italic tracking-tighter">{{ formatCurrency(structure.tuition_fee).split('.')[0] }}</span>
                        </div>
                        <div class="flex justify-between items-center group/item p-4 rounded-2xl hover:bg-muted/50 transition-colors">
                            <span class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest italic">Ancillary Fees</span>
                            <span class="font-black text-sm text-foreground uppercase italic tracking-tighter">{{ formatCurrency(structure.other_fee).split('.')[0] }}</span>
                        </div>
                        
                        <div class="pt-6 border-t border-border/50 flex justify-between items-center px-4">
                            <span class="text-[11px] font-black text-foreground uppercase tracking-[0.2em] italic">Aggregate</span>
                            <span class="text-2xl font-black text-indigo-600 uppercase italic tracking-tighter">{{ formatCurrency(structure.total_fee).split('.')[0] }}</span>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-between relative z-10">
                        <Badge variant="outline" 
                            class="rounded-full h-8 px-5 border-border font-black text-[9px] uppercase tracking-[0.2em] italic gap-2 transition-all"
                            :class="structure.is_active ? 'bg-emerald-600/10 text-emerald-600 border-emerald-600/20' : 'bg-muted text-muted-foreground'"
                        >
                            <div class="h-1.5 w-1.5 rounded-full bg-current" :class="structure.is_active ? 'animate-pulse' : ''"></div>
                            {{ structure.is_active ? 'Active Flow' : 'Halted' }}
                        </Badge>
                        
                        <div class="h-10 w-10 rounded-full bg-muted/50 flex items-center justify-center border border-border group-hover:bg-indigo-600 group-hover:border-indigo-600 transition-all duration-500 shadow-inner">
                            <ChevronRight class="h-4 w-4 text-muted-foreground/40 group-hover:text-white transition-colors" />
                        </div>
                    </div>
                </div>
                
                <div v-if="filteredStructures.length === 0" class="col-span-full py-40 text-center bg-muted/5 rounded-[3rem] border border-dashed border-border italic">
                    <Receipt class="h-20 w-20 mx-auto mb-8 text-muted-foreground/10" />
                    <p class="text-[11px] font-black text-muted-foreground/30 uppercase tracking-[0.3em]">No schematic data detected</p>
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
