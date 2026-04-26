<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Bus,
    MapPin,
    Users,
    Settings,
    Plus,
    ArrowRight,
    Navigation,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        total_vehicles: number;
        active_routes: number;
        total_allocated_students: number;
        available_capacity: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Transport', href: '/transport' },
];
</script>

<template>
    <Head title="Transport Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10"
                    >
                        <Bus class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Transport Management
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school fleet, routes and student
                            transportation
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child
                        ><Link href="/transport/routes"
                            ><Navigation class="mr-2 h-4 w-4" />View
                            Routes</Link
                        ></Button
                    >
                    <Button
                        as-child
                        class="font-pulsar bg-orange-600 hover:bg-orange-700"
                        ><Link href="/transport/vehicles/create"
                            ><Plus class="mr-2 h-4 w-4" />Add Vehicle</Link
                        ></Button
                    >
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2">
                            <Bus class="h-5 w-5 text-blue-600" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Total Vehicles
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.total_vehicles }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-orange-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-orange-500/10 p-2">
                            <MapPin class="h-5 w-5 text-orange-600" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Active Routes
                        </p>
                        <p class="text-2xl font-bold text-orange-600">
                            {{ stats.active_routes }}
                        </p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-emerald-500/10 p-2">
                            <Users class="h-5 w-5 text-emerald-600" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Allocated Students
                        </p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ stats.total_allocated_students }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-blue-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2">
                            <Settings class="h-5 w-5 text-blue-600" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Remaining Capacity
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ stats.available_capacity }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold">Quick Actions</h2>
                    <div class="grid gap-3">
                        <Link
                            href="/transport/routes/create"
                            class="group flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-colors hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm"
                                >
                                    <MapPin class="h-5 w-5 text-orange-500" />
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">
                                        Create New Route
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Define zones and stops for student
                                        pickup
                                    </div>
                                </div>
                            </div>
                            <ArrowRight
                                class="h-4 w-4 text-slate-300 transition-colors group-hover:text-orange-500"
                            />
                        </Link>
                        <Link
                            href="/transport/vehicles"
                            class="group flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-colors hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm"
                                >
                                    <Bus class="h-5 w-5 text-blue-500" />
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">
                                        Vehicle Inventory
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        View status and maintenance for school
                                        fleet
                                    </div>
                                </div>
                            </div>
                            <ArrowRight
                                class="h-4 w-4 text-slate-300 transition-colors group-hover:text-blue-500"
                            />
                        </Link>
                    </div>
                </div>

                <div
                    class="rounded-xl border bg-gradient-to-br from-orange-500 to-orange-600 p-8 text-white shadow-lg"
                >
                    <h2 class="mb-2 text-2xl font-bold">
                        Transport Optimization
                    </h2>
                    <p class="mb-6 text-orange-50/80">
                        Allocate students to routes, track vehicle capacity and
                        optimize school arrivals.
                    </p>
                    <div class="flex gap-4">
                        <Button
                            variant="secondary"
                            as-child
                            class="border-0 bg-white font-bold text-orange-600 shadow-md hover:bg-orange-50"
                        >
                            <Link href="/transport/allocations"
                                >Manage Allocations</Link
                            >
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
