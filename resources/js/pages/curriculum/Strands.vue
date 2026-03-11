<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Layers3, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    strands: Array<{
        id: number;
        subject_id: number;
        grade_level_id: number;
        name: string;
        code: string;
        description: string | null;
        display_order: number;
        is_active: boolean;
        subject_name: string;
        grade_name: string;
    }>;
    subjects: Array<{ id: number; name: string }>;
    grades: Array<{ id: number; name: string }>;
    stats: { total: number; active: number };
    filters: { search: string; status: string; view: 'grid' | 'list' };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Strands', href: '/curriculum/strands' },
];
</script>

<template>
    <Head title="Strands" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                        <Layers3 class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Strands</h1>
                        <p class="text-muted-foreground">Major topic areas grouped by subject and grade level</p>
                    </div>
                </div>
                <Button as-child><Link href="/curriculum/strands/create"><Plus class="mr-2 h-4 w-4" />Add Strand</Link></Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Total Strands</div>
                    <div class="mt-1 text-3xl font-bold">{{ stats.total }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active }}</div>
                </div>
            </div>

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Strand</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Subject</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Grade</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="strands.length === 0">
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-muted-foreground">No strands available yet.</td>
                            </tr>
                            <tr v-for="strand in strands" :key="strand.id" class="border-b">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ strand.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ strand.code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ strand.subject_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ strand.grade_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ strand.is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
