<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Bus,
    MapPin,
    Users,
    Settings,
    Plus,
    ArrowUpRight,
    Navigation,
    Home,
    ChevronRight,
    ShieldCheck,
    Wrench,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
    <Head title="Transport Intelligence" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Home class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Logistics</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Fleet Management</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Logistics Overview
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Manage institutional mobility, route synchronization, and fleet status.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted">
                        <Link href="/transport/routes">
                            <Navigation class="mr-2 h-4 w-4 opacity-40" />Active Routes
                        </Link>
                    </Button>
                    <Button as-child class="h-11 rounded-xl bg-orange-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-orange-500/30 transition-all hover:scale-[1.02] hover:bg-orange-700 active:scale-95 text-white">
                        <Link href="/transport/vehicles/create">
                            <Plus class="mr-2 h-4 w-4" />Register Vehicle
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Intelligence Cards -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Fleet -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-blue-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-blue-500/5 blur-2xl transition-all group-hover:bg-blue-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white">
                                <Bus class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-blue-600/40 uppercase">Fleet</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_vehicles }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Operational Asset Units</p>
                        </div>
                    </div>
                </div>

                <!-- Active Routes -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-orange-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-orange-500/5 blur-2xl transition-all group-hover:bg-orange-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-600 shadow-sm transition-all group-hover:bg-orange-600 group-hover:text-white">
                                <MapPin class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-orange-600/40 uppercase">Topology</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active_routes }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Synchronized Delivery Routes</p>
                        </div>
                    </div>
                </div>

                <!-- Allocated Students -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-emerald-500/5 blur-2xl transition-all group-hover:bg-emerald-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white">
                                <Users class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-emerald-600/40 uppercase">Manifest</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_allocated_students }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Learners Bound to Logistics</p>
                        </div>
                    </div>
                </div>

                <!-- Available Capacity -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-primary/5 blur-2xl transition-all group-hover:bg-primary/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary shadow-sm transition-all group-hover:bg-primary group-hover:text-white">
                                <Settings class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-primary/40 uppercase">Utilization</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.available_capacity }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Residual System Capacity</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Split -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 px-1">
                <!-- Strategic Actions -->
                <div class="rounded-3xl border border-border bg-card shadow-sm overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-600">
                                <Navigation class="h-5 w-5" />
                            </div>
                            <h3 class="text-sm font-bold tracking-tight text-foreground uppercase">Logistics Matrix</h3>
                        </div>
                        <span class="text-[10px] font-bold text-muted-foreground/40 uppercase">Control Panel</span>
                    </div>

                    <div class="p-8 space-y-4">
                        <Link
                            href="/transport/routes/create"
                            class="group block p-6 rounded-2xl border border-border bg-muted/20 transition-all hover:bg-background hover:border-orange-500/20 hover:shadow-lg"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm border border-border/50 group-hover:bg-orange-500 group-hover:text-white transition-all">
                                        <MapPin class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-foreground uppercase">Topology Definition</h4>
                                        <p class="text-[10px] font-bold text-muted-foreground/40 uppercase mt-1">Designate terminal zones and pickup clusters</p>
                                    </div>
                                </div>
                                <ArrowUpRight class="h-5 w-5 text-muted-foreground/20 group-hover:text-orange-500 transition-all" />
                            </div>
                        </Link>

                        <Link
                            href="/transport/vehicles"
                            class="group block p-6 rounded-2xl border border-border bg-muted/20 transition-all hover:bg-background hover:border-blue-500/20 hover:shadow-lg"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm border border-border/50 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                        <Bus class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-foreground uppercase">Fleet Registry</h4>
                                        <p class="text-[10px] font-bold text-muted-foreground/40 uppercase mt-1">Monitor asset health and maintenance cycles</p>
                                    </div>
                                </div>
                                <ArrowUpRight class="h-5 w-5 text-muted-foreground/20 group-hover:text-blue-500 transition-all" />
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Strategic Intelligence Area -->
                <div class="relative overflow-hidden rounded-3xl bg-slate-900 dark:bg-black p-10 text-white shadow-2xl flex flex-col justify-between">
                    <div class="absolute inset-0 opacity-[0.03] grayscale pointer-events-none">
                        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                            <defs><pattern id="grid-transport" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/></pattern></defs>
                            <rect width="100%" height="100%" fill="url(#grid-transport)" />
                        </svg>
                    </div>
                    
                    <div class="relative z-10 space-y-8">
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/5 border border-white/10 shadow-2xl transition-all hover:bg-orange-500/20 hover:scale-110">
                            <Navigation class="h-10 w-10 text-orange-500" />
                        </div>
                        <div class="space-y-3">
                            <h4 class="text-2xl font-bold tracking-tight">Mobility Optimization</h4>
                            <p class="text-sm text-white/50 font-medium leading-relaxed max-w-sm">
                                Synchronize institutional arrivals through advanced route allocation and manifest management. Maintain fleet integrity via real-time capacity audits.
                            </p>
                        </div>
                    </div>

                    <div class="relative z-10 pt-10">
                        <div class="flex gap-4">
                            <Button as-child class="h-12 flex-1 rounded-2xl bg-white font-bold text-[10px] tracking-tight text-slate-900 uppercase hover:bg-orange-50 transition-all active:scale-95">
                                <Link href="/transport/allocations">Allocate Manifest <ArrowUpRight class="ml-2 h-4 w-4" /></Link>
                            </Button>
                            <Button variant="outline" as-child class="h-12 w-12 rounded-2xl border-white/10 bg-white/5 text-white hover:bg-white/10 transition-all">
                                <Link href="/transport/settings"><Wrench class="h-4 w-4" /></Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
