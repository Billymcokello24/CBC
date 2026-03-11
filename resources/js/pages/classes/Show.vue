<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { School } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    classroom: {
        id: number;
        name: string;
        code: string;
        grade: string | null;
        stream: string | null;
        academic_year: string | null;
        capacity: number | null;
        students_count: number;
        utilization: number;
        is_active: boolean;
    };
    students: Array<{
        id: number;
        name: string;
        admission_number: string | null;
        gender: string;
        status: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
    { title: props.classroom.name, href: `/classes/${props.classroom.id}` },
];
</script>

<template>
    <Head :title="classroom.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <School class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ classroom.name }}</h1>
                        <p class="text-muted-foreground">{{ classroom.grade || 'Unknown grade' }}<span v-if="classroom.stream"> • {{ classroom.stream }}</span><span v-if="classroom.academic_year"> • {{ classroom.academic_year }}</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child><Link href="/classes">Back to Classes</Link></Button>
                    <Button variant="outline" as-child><Link href="/students/enrollments">Enrollments</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Code</div><div class="mt-1 text-2xl font-bold">{{ classroom.code }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Capacity</div><div class="mt-1 text-2xl font-bold">{{ classroom.capacity ?? '—' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Students</div><div class="mt-1 text-2xl font-bold text-blue-600">{{ classroom.students_count }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Status</div><div class="mt-1"><Badge>{{ classroom.is_active ? 'Active' : 'Inactive' }}</Badge></div></div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Class Roster</h2>
                        <p class="text-sm text-muted-foreground">Students currently assigned to this class</p>
                    </div>
                    <div class="text-sm text-muted-foreground">Utilization: {{ classroom.utilization }}%</div>
                </div>
                <div class="h-2 rounded-full bg-muted mb-6">
                    <div class="h-full rounded-full bg-indigo-500" :style="{ width: `${classroom.utilization}%` }"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Adm. No</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Gender</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.length === 0">
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-muted-foreground">No active students are assigned to this class.</td>
                            </tr>
                            <tr v-for="student in students" :key="student.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 font-medium">{{ student.name }}</td>
                                <td class="px-4 py-3 text-sm">{{ student.admission_number || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ student.gender }}</td>
                                <td class="px-4 py-3"><Badge variant="outline">{{ student.status }}</Badge></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
