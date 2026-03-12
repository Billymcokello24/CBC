<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    guardian: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
    };
    students: Array<{
        id: number;
        name: string;
        admission_number: string | null;
        class: string | null;
        status: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/guardian/portal' },
    { title: 'My Students', href: '/guardian/portal' },
];
</script>

<template>
    <Head title="Guardian Portal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="rounded-xl border bg-card p-6">
                <h1 class="text-2xl font-bold tracking-tight">Welcome, {{ guardian.name }}</h1>
                <p class="mt-2 text-sm text-muted-foreground">You can only access the students linked to your guardian account.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm text-muted-foreground">Email</div>
                        <div class="font-medium">{{ guardian.email || '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-muted-foreground">Phone</div>
                        <div class="font-medium">{{ guardian.phone || '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Linked Students</h2>
                        <p class="text-sm text-muted-foreground">Open a student below to view only their profile and related content available to guardians.</p>
                    </div>
                    <div class="text-sm text-muted-foreground">{{ students.length }} student(s)</div>
                </div>

                <div v-if="students.length === 0" class="rounded-xl border border-dashed p-8 text-sm text-muted-foreground">
                    No students are linked to this guardian account yet.
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="student in students" :key="student.id" class="rounded-xl border p-5">
                        <h3 class="text-lg font-semibold">{{ student.name }}</h3>
                        <p class="text-sm text-muted-foreground">{{ student.admission_number || 'No admission number' }}</p>
                        <div class="mt-4 space-y-2 text-sm">
                            <div>Class: <span class="font-medium">{{ student.class || 'Unassigned' }}</span></div>
                            <div class="flex items-center gap-2">Status: <Badge>{{ student.status }}</Badge></div>
                        </div>
                        <div class="mt-4">
                            <Button as-child size="sm"><Link :href="`/students/${student.id}`">Open Student</Link></Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
