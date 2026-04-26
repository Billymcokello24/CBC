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
    learners: Array<{
        id: number;
        name: string;
        admission_number: string | null;
        class: string | null;
        status: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/guardian/portal' },
    { title: 'Connected Learners', href: '/guardian/portal' },
];
</script>

<template>
    <Head title="Guardian Portal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="rounded-xl border bg-card p-6">
                <h1 class="text-2xl font-bold tracking-tight">
                    Welcome, {{ guardian.name }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    You can only access the learners linked to your guardian
                    account.
                </p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm text-muted-foreground">Email</div>
                        <div class="font-medium">
                            {{ guardian.email || '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-muted-foreground">Phone</div>
                        <div class="font-medium">
                            {{ guardian.phone || '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Connected Learners
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Open a learner below to view their profile and
                            related academic content.
                        </p>
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ learners.length }} learner(s)
                    </div>
                </div>

                <div
                    v-if="learners.length === 0"
                    class="rounded-xl border border-dashed p-8 text-sm text-muted-foreground"
                >
                    No learners are linked to this guardian account yet.
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="learner in learners"
                        :key="learner.id"
                        class="rounded-xl border p-5 transition-all hover:bg-slate-50/50"
                    >
                        <h3 class="text-lg font-semibold">
                            {{ learner.name }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{
                                learner.admission_number ||
                                'No admission number'
                            }}
                        </p>
                        <div class="mt-4 space-y-2 text-sm">
                            <div>
                                Class:
                                <span
                                    class="font-bold font-medium text-slate-900 uppercase"
                                    >{{ learner.class || 'Unassigned' }}</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                Status:
                                <Badge class="rounded-lg">{{
                                    learner.status
                                }}</Badge>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Button
                                as-child
                                size="sm"
                                class="rounded-xl bg-blue-600 shadow-lg hover:bg-blue-700"
                                ><Link :href="`/students/${learner.id}`"
                                    >Open Profile</Link
                                ></Button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
