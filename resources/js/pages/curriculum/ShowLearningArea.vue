<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpenCheck } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learningArea: {
        id: number;
        name: string;
        code: string;
        description: string | null;
        category: string | null;
        display_order: number;
        is_active: boolean;
        subjects: Array<{
            id: number;
            name: string;
            code: string;
            subject_type: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
    { title: props.learningArea.name, href: `/curriculum/learning-areas/${props.learningArea.id}` },
];
</script>

<template>
    <Head :title="learningArea.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10"><BookOpenCheck class="h-6 w-6 text-emerald-600" /></div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ learningArea.name }}</h1>
                        <p class="text-muted-foreground">{{ learningArea.code }} • {{ learningArea.category || 'General' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child><Link :href="`/curriculum/learning-areas/${learningArea.id}/edit`">Edit</Link></Button>
                    <Button variant="outline" as-child><Link href="/curriculum/learning-areas">Back</Link></Button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Status</div><div class="mt-2"><Badge>{{ learningArea.is_active ? 'Active' : 'Inactive' }}</Badge></div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Display Order</div><div class="mt-1 text-2xl font-bold">{{ learningArea.display_order }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Subjects</div><div class="mt-1 text-2xl font-bold">{{ learningArea.subjects.length }}</div></div>
            </div>
            <div class="rounded-xl border bg-card p-6"><h2 class="text-lg font-semibold">Description</h2><p class="mt-2 text-sm text-muted-foreground">{{ learningArea.description || 'No description provided.' }}</p></div>
            <div class="rounded-xl border bg-card p-6">
                <h2 class="text-lg font-semibold">Subjects Under This Area</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="border-b bg-muted/50"><th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Subject</th><th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Type</th><th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th></tr></thead>
                        <tbody>
                            <tr v-if="learningArea.subjects.length === 0"><td colspan="3" class="px-4 py-10 text-center text-sm text-muted-foreground">No subjects assigned yet.</td></tr>
                            <tr v-for="subject in learningArea.subjects" :key="subject.id" class="border-b"><td class="px-4 py-3"><div class="font-medium">{{ subject.name }}</div><div class="text-xs text-muted-foreground">{{ subject.code }}</div></td><td class="px-4 py-3 text-sm">{{ subject.subject_type }}</td><td class="px-4 py-3 text-sm">{{ subject.is_active ? 'Active' : 'Inactive' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
