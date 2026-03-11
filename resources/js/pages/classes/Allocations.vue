<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Layers3 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    allocations: Array<{
        id: number;
        name: string;
        grade: string | null;
        stream: string | null;
        capacity: number | null;
        students: number;
        available_slots: number;
        academic_year: string | null;
        teacher: string | null;
    }>;
    stats: {
        classes_with_space: number;
        full_classes: number;
        total_capacity: number;
        occupied_slots: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
    { title: 'Allocations', href: '/classes/allocations' },
];
</script>

<template>
    <Head title="Class Allocations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10">
                        <Layers3 class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Class Allocations</h1>
                        <p class="text-muted-foreground">See class capacity and space available for student placement</p>
                    </div>
                </div>
                <Button variant="outline" as-child><Link href="/classes">Back to Classes</Link></Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Classes With Space</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.classes_with_space }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Full Classes</div><div class="mt-1 text-3xl font-bold text-red-600">{{ stats.full_classes }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Capacity</div><div class="mt-1 text-3xl font-bold">{{ stats.total_capacity }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Occupied Slots</div><div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.occupied_slots }}</div></div>
            </div>

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Year</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Teacher</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Occupied</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in allocations" :key="row.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ row.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ row.grade || '—' }}<span v-if="row.stream"> • {{ row.stream }}</span></div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ row.academic_year || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ row.teacher || 'Not assigned' }}</td>
                                <td class="px-4 py-3 text-sm">{{ row.students }}/{{ row.capacity || '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="row.available_slots > 0 ? 'text-green-600' : 'text-red-600'">{{ row.available_slots }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
