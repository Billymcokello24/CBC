<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Home,
    Users,
    Bed,
    ShieldCheck,
    Plus,
    ArrowRight,
    Building2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        total_hostels: number;
        total_capacity: number;
        current_occupancy: number;
        available_beds: number;
    };
    hostels: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Hostels', href: '/hostels' },
];

const getOccupancyColor = (current: number, total: number) => {
    const percentage = (current / total) * 100;
    if (percentage >= 95) return 'text-rose-600 bg-rose-50 border-rose-100';
    if (percentage >= 80) return 'text-amber-600 bg-amber-50 border-amber-100';
    return 'text-emerald-600 bg-emerald-50 border-emerald-100';
};
</script>

<template>
    <Head title="Hostel Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10"
                    >
                        <Home class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Hostels
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school boarding facilities and student
                            allocations
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child
                        ><Link href="/hostels/allocations"
                            >View Allocations</Link
                        ></Button
                    >
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-700"
                        ><Plus class="mr-2 h-4 w-4" />New Hostel</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2">
                            <Building2 class="h-5 w-5 text-blue-600" />
                        </div>
                        <span class="text-xs font-bold text-blue-600 uppercase"
                            >Hostels</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Total Units</p>
                        <p class="text-2xl font-bold">
                            {{ stats.total_hostels }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-indigo-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-indigo-500/10 p-2">
                            <Bed class="h-5 w-5 text-indigo-600" />
                        </div>
                        <span
                            class="text-xs font-bold text-indigo-600 uppercase"
                            >Capacity</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Total Beds</p>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ stats.total_capacity }}
                        </p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-emerald-500/10 p-2">
                            <Users class="h-5 w-5 text-emerald-600" />
                        </div>
                        <span
                            class="text-xs font-bold text-emerald-600 uppercase"
                            >Occupied</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Students In</p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ stats.current_occupancy }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-amber-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-amber-500/10 p-2">
                            <ShieldCheck class="h-5 w-5 text-amber-600" />
                        </div>
                        <span class="text-xs font-bold text-amber-600 uppercase"
                            >Vacant</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Available</p>
                        <p class="text-2xl font-bold text-amber-600">
                            {{ stats.available_beds }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <h2 class="text-lg font-bold">Hostel Block Status</h2>
                    <div
                        v-for="hostel in hostels"
                        :key="hostel.id"
                        class="rounded-xl border bg-card p-5 shadow-sm transition-colors hover:border-indigo-200"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold">
                                    {{ hostel.name }}
                                </h3>
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    {{ hostel.gender }} Hostel •
                                    {{ hostel.code }}
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="rounded-full px-2 py-0.5 text-xs font-bold"
                                :class="
                                    getOccupancyColor(
                                        hostel.current_occupancy,
                                        hostel.total_capacity,
                                    )
                                "
                            >
                                {{ hostel.current_occupancy }} /
                                {{ hostel.total_capacity }} Occupied
                            </Badge>
                        </div>
                        <div class="mb-4 h-2 w-full rounded-full bg-slate-100">
                            <div
                                class="h-2 rounded-full bg-indigo-500 transition-all duration-500"
                                :style="`width: ${(hostel.current_occupancy / hostel.total_capacity) * 100}%`"
                            ></div>
                        </div>
                        <div
                            class="flex items-center justify-between text-xs text-muted-foreground"
                        >
                            <span>Warden: {{ hostel.warden_name }}</span>
                            <Link
                                :href="`/hostels/${hostel.id}`"
                                class="font-bold text-indigo-600 hover:underline"
                                >Manage Block</Link
                            >
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="rounded-xl border bg-slate-900 p-8 text-white shadow-xl"
                    >
                        <h2 class="font-pulsar mb-2 text-2xl font-bold">
                            Allocation Workflow
                        </h2>
                        <p class="mb-6 text-sm text-slate-400">
                            Efficiently manage bed space and student residency
                            cycles.
                        </p>
                        <div class="grid gap-3">
                            <Button
                                as-child
                                size="lg"
                                class="w-full justify-between bg-indigo-600 hover:bg-indigo-700"
                            >
                                <Link href="/hostels/allocations/create"
                                    >Issue New Bed Base
                                    <ArrowRight class="h-4 w-4"
                                /></Link>
                            </Button>
                            <Button
                                variant="outline"
                                as-child
                                size="lg"
                                class="w-full justify-between border-white/20 bg-white/10 text-white hover:bg-white/20"
                            >
                                <Link href="/hostels/allocations"
                                    >Review Occupancy Log
                                    <ArrowRight class="h-4 w-4"
                                /></Link>
                            </Button>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 font-bold">Resource Maintenance</h3>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Track furniture, repairs and facility status across
                            all hostel blocks.
                        </p>
                        <Button variant="secondary" class="w-full"
                            >View Maintenance Log</Button
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
