<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Bus,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    ShieldCheck,
    Fuel,
} from 'lucide-vue-next';
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
        case 'active':
            return 'bg-emerald-500 hover:bg-emerald-600';
        case 'maintenance':
            return 'bg-amber-500 hover:bg-amber-600';
        case 'inactive':
            return 'bg-slate-500 hover:bg-slate-600';
        default:
            return 'bg-slate-500';
    }
};
</script>

<template>
    <Head title="Vehicle Inventory" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10"
                    >
                        <Bus class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Vehicles
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school bus fleet and maintenance tracking
                        </p>
                    </div>
                </div>
                <Button as-child class="bg-blue-600 hover:bg-blue-700">
                    <Link href="/transport/vehicles/create">
                        <Plus class="mr-2 h-4 w-4" />Add Vehicle
                    </Link>
                </Button>
            </div>

            <div
                class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm"
            >
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search registration or number..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Status
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="vehicle in vehicles"
                    :key="vehicle.id"
                    class="overflow-hidden rounded-2xl border bg-card shadow-sm transition-all hover:border-blue-200"
                >
                    <div class="p-6">
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <Badge
                                    variant="outline"
                                    class="mb-2 border-blue-100 bg-blue-50 text-xs font-bold tracking-tight text-blue-700 uppercase"
                                >
                                    {{ vehicle.vehicle_type }}
                                </Badge>
                                <h3 class="text-xl font-bold text-slate-900">
                                    {{ vehicle.registration_number }}
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    {{ vehicle.make }} {{ vehicle.model }} ({{
                                        vehicle.year
                                    }})
                                </p>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        ><MoreHorizontal class="h-4 w-4"
                                    /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40">
                                    <DropdownMenuItem
                                        ><Eye
                                            class="mr-2 h-4 w-4"
                                        />Details</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        ><Edit
                                            class="mr-2 h-4 w-4"
                                        />Edit</DropdownMenuItem
                                    >
                                    <DropdownMenuItem class="text-destructive"
                                        ><Trash2
                                            class="mr-2 h-4 w-4"
                                        />Delete</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="mb-6 grid grid-cols-2 gap-4">
                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50 p-3"
                            >
                                <p
                                    class="mb-1 text-xs font-bold text-slate-400 uppercase"
                                >
                                    Capacity
                                </p>
                                <p class="font-bold text-slate-700">
                                    {{ vehicle.capacity }} Seats
                                </p>
                            </div>
                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50 p-3"
                            >
                                <p
                                    class="mb-1 text-xs font-bold text-slate-400 uppercase"
                                >
                                    Fuel Type
                                </p>
                                <p
                                    class="flex items-center font-bold text-slate-700"
                                >
                                    <Fuel class="mr-1 h-3 w-3" />
                                    {{ vehicle.fuel_type }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <Badge
                                :class="getStatusColor(vehicle.status)"
                                class="rounded-full px-3 py-0.5 text-xs font-bold tracking-wider text-white uppercase"
                            >
                                {{ vehicle.status }}
                            </Badge>
                            <div
                                class="flex items-center text-xs text-muted-foreground"
                            >
                                <ShieldCheck
                                    class="mr-1 h-3 w-3 text-emerald-500"
                                />
                                Insured
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
