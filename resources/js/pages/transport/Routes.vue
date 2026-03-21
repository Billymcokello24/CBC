<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, Map as MapIcon, Users } from 'lucide-vue-next';
import { ref } from 'vue';
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
    routes: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Transport', href: '/transport' },
    { title: 'Routes', href: '/transport/routes' },
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

</script>

<template>
    <Head title="Transport Routes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10">
                        <MapPin class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Active Routes</h1>
                        <p class="text-muted-foreground">Manage transportation routes, zones and student distribution</p>
                    </div>
                </div>
                <Button as-child class="bg-orange-600 hover:bg-orange-700">
                    <Link href="/transport/routes/create">
                        <Plus class="mr-2 h-4 w-4" />New Route
                    </Link>
                </Button>
            </div>

            <div class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search route name or code..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Zones
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="route in routes" :key="route.id" class="rounded-2xl border bg-card overflow-hidden shadow-sm hover:border-orange-200 transition-all flex flex-col">
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <Badge variant="outline" class="mb-2 bg-orange-50 text-orange-700 border-orange-100 uppercase text-[10px] font-bold tracking-widest">
                                    {{ route.code }}
                                </Badge>
                                <h3 class="text-xl font-bold text-slate-900">{{ route.name }}</h3>
                                <p class="text-xs text-muted-foreground mt-1">{{ route.description }}</p>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-slate-600"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40">
                                    <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View Details</DropdownMenuItem>
                                    <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Edit Route</DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Fee</span>
                                <span class="font-bold text-slate-900">{{ formatCurrency(route.fee_amount) }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Distance</span>
                                <span class="font-bold text-slate-900">{{ route.distance_km }} KM</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Allocated</span>
                                <span class="font-bold text-orange-600 flex items-center"><Users class="h-4 w-4 mr-1" />{{ route.allocations_count }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/50 border-t flex items-center justify-between mt-auto">
                        <div class="flex items-center text-xs text-slate-500 font-medium">
                            <MapIcon class="h-3.5 w-3.5 mr-1.5 text-slate-400" /> {{ route.stops?.length || 0 }} Stops
                        </div>
                        <Link :href="`/transport/routes/${route.id}`" class="text-xs font-bold text-orange-600 hover:text-orange-700 uppercase tracking-wider">
                            View on Map
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
