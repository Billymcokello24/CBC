<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BrainCircuit, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    competencies: Array<{
        id: number;
        name: string;
        code: string;
        description: string | null;
        category: string | null;
        display_order: number;
        is_active: boolean;
        indicators_count: number;
    }>;
    stats: { total: number; active: number; indicators: number };
    filters: { search: string; status: string; view: 'grid' | 'list' };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Competencies', href: '/curriculum/competencies' },
];
</script>

<template>
    <Head title="Competencies" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10"><BrainCircuit class="h-6 w-6 text-violet-600" /></div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Competencies</h1>
                        <p class="text-muted-foreground">Core CBC competencies and linked indicators</p>
                    </div>
                </div>
                <Button as-child><Link href="/curriculum/competencies/create"><Plus class="mr-2 h-4 w-4" />Add Competency</Link></Button>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Competencies</div><div class="mt-1 text-3xl font-bold">{{ stats.total }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Indicators</div><div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.indicators }}</div></div>
            </div>
            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Competency</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Indicators</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="competencies.length === 0"><td colspan="4" class="px-4 py-10 text-center text-sm text-muted-foreground">No competencies available yet.</td></tr>
                            <tr v-for="competency in competencies" :key="competency.id" class="border-b">
                                <td class="px-4 py-3"><div class="font-medium">{{ competency.name }}</div><div class="text-xs text-muted-foreground">{{ competency.code }}</div></td>
                                <td class="px-4 py-3 text-sm">{{ competency.category || 'General' }}</td>
                                <td class="px-4 py-3 text-sm">{{ competency.indicators_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ competency.is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
