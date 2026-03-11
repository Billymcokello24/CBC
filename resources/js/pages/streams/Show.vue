<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Rows3, School } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stream: {
        id: number;
        name: string;
        code: string;
        capacity: number | null;
        is_active: boolean;
        lead_name: string | null;
    };
    classes: Array<{
        id: number;
        name: string;
        code: string;
        grade: string | null;
        academic_year: string | null;
        teacher: string | null;
        students_count: number;
        capacity: number | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Streams', href: '/streams' },
    { title: props.stream.name, href: `/streams/${props.stream.id}` },
];
</script>

<template>
    <Head :title="stream.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10">
                        <Rows3 class="h-6 w-6 text-cyan-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ stream.name }}</h1>
                        <p class="text-muted-foreground">{{ stream.code }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child><Link :href="`/streams/${stream.id}/edit`">Edit Stream</Link></Button>
                    <Button variant="outline" as-child><Link href="/streams">Back to Streams</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Lead</div><div class="mt-1 font-semibold">{{ stream.lead_name || 'Not assigned' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Capacity</div><div class="mt-1 text-2xl font-bold">{{ stream.capacity ?? '—' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Classes</div><div class="mt-1 text-2xl font-bold text-blue-600">{{ classes.length }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Status</div><div class="mt-1"><Badge>{{ stream.is_active ? 'Active' : 'Inactive' }}</Badge></div></div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Classes Under {{ stream.name }}</h2>
                        <p class="text-sm text-muted-foreground">Open any class to manage its students and operations</p>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <Link v-for="classroom in classes" :key="classroom.id" :href="`/classes/${classroom.id}`" class="rounded-xl border bg-card p-5 transition hover:border-primary/40 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">{{ classroom.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ classroom.code }}<span v-if="classroom.grade"> • {{ classroom.grade }}</span></p>
                                <p class="mt-1 text-xs text-muted-foreground">Teacher: {{ classroom.teacher || 'Not assigned' }}</p>
                            </div>
                            <div class="rounded-full bg-primary/10 p-2 text-primary">
                                <School class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Students</div><div class="mt-1 font-semibold">{{ classroom.students_count }}</div></div>
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Capacity</div><div class="mt-1 font-semibold">{{ classroom.capacity ?? '—' }}</div></div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
