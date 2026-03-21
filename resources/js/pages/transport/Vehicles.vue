<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Bus, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, ShieldCheck, Fuel } from 'lucide-vue-next';
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
    vehicles: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Transport', href: '/transport' },
    { title: 'Vehicles', href: '/transport/vehicles' },
];

const searchQuery = ref('');

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active': return 'bg-emerald-500 hover:bg-emerald-600';
        case 'maintenance': return 'bg-amber-500 hover:bg-amber-600';
        case 'inactive': return 'bg-slate-500 hover:bg-slate-600';
        default: return 'bg-slate-500';
    }
};
</script>

<template>
    <Head title="Vehicle Inventory" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <Bus class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Vehicles</h1>
                        <p class="text-muted-foreground">Manage school bus fleet and maintenance tracking</p>
                    </div>
                </div>
                <Button as-child class="bg-blue-600 hover:bg-blue-700">
                    <Link href="/transport/vehicles/create">
                        <Plus class="mr-2 h-4 w-4" />Add Vehicle
                    </Link>
                </Button>
            </div>

            <div class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search registration or number..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Status
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-2xl border bg-card overflow-hidden shadow-sm hover:border-blue-200 transition-all">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <Badge variant="outline" class="mb-2 bg-blue-50 text-blue-700 border-blue-100 uppercase text-[10px] font-bold tracking-widest">
                                    {{ vehicle.vehicle_type }}
                                </Badge>
                                <h3 class="text-xl font-bold text-slate-900">{{ vehicle.registration_number }}</h3>
                                <p class="text-sm text-muted-foreground">{{ vehicle.make }} {{ vehicle.model }} ({{ vehicle.year }})</p>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40">
                                    <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />Details</DropdownMenuItem>
                                    <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Edit</DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] uppercase text-slate-400 font-bold mb-1">Capacity</p>
                                <p class="font-bold text-slate-700">{{ vehicle.capacity }} Seats</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] uppercase text-slate-400 font-bold mb-1">Fuel Type</p>
                                <p class="font-bold text-slate-700 flex items-center"><Fuel class="h-3 w-3 mr-1" /> {{ vehicle.fuel_type }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <Badge :class="getStatusColor(vehicle.status)" class="rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                {{ vehicle.status }}
                            </Badge>
                            <div class="flex items-center text-xs text-muted-foreground">
                                <ShieldCheck class="h-3 w-3 mr-1 text-emerald-500" /> Insured
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
