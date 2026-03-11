<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GraduationCap } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    grades: Array<{
        id: number;
        name: string;
        code: string;
        category: string;
        level_order: number;
        minimum_age: number | null;
        maximum_age: number | null;
        is_active: boolean;
        classes_count: number;
        students_count: number;
    }>;
    stats: {
        total_grades: number;
        active_grades: number;
        total_classes: number;
        total_students: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
];
</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                    <GraduationCap class="h-6 w-6 text-green-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Grades</h1>
                    <p class="text-muted-foreground">Curriculum grade levels connected to classes and students</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Grades</div><div class="mt-1 text-3xl font-bold">{{ stats.total_grades }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active Grades</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active_grades }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Classes</div><div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.total_classes }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Students</div><div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.total_students }}</div></div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="grade in grades" :key="grade.id" class="rounded-xl border bg-card p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">{{ grade.name }}</h2>
                            <p class="text-sm text-muted-foreground">{{ grade.code }} • {{ grade.category }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs" :class="grade.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700'">
                            {{ grade.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Classes</div><div class="mt-1 font-semibold">{{ grade.classes_count }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Students</div><div class="mt-1 font-semibold">{{ grade.students_count }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Min Age</div><div class="mt-1 font-semibold">{{ grade.minimum_age ?? '—' }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Max Age</div><div class="mt-1 font-semibold">{{ grade.maximum_age ?? '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
