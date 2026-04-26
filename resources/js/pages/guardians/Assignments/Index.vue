<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    ClipboardList,
    ArrowRight,
    CheckCircle2,
    AlertCircle,
    Clock,
    User,
    ChevronRight,
    FileText,
    Calendar,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    children: Array<{
        id: number;
        name: string;
        class: string | null;
        assignments: Array<{
            id: number;
            title: string;
            subject: string | null;
            class: string | null;
            teacher: string | null;
            assignment_type: string;
            due_date: string | null;
            is_overdue: boolean;
            total_marks: number | null;
            status: string;
            submitted: boolean;
        }>;
    }>;
    total_assignments: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'Assignments', href: '#' },
];

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'graded':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted':
            return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'returned':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        default:
            return 'bg-slate-50 text-slate-500 border-slate-100';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'graded':
            return 'Marked & Graded';
        case 'submitted':
            return 'Pending Review';
        case 'returned':
            return 'Needs Revision';
        case 'pending':
            return 'Not Started';
        default:
            return status.toUpperCase();
    }
};
</script>

<template>
    <Head title="Academic Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1400px] flex-1 animate-in flex-col gap-10 p-6 pb-20 duration-500 fade-in md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 border-b border-slate-100 pb-8 md:flex-row md:items-center"
            >
                <div class="flex items-center gap-5">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-200"
                    >
                        <ClipboardList class="h-7 w-7" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold tracking-tighter text-slate-900 uppercase"
                        >
                            Academic Assignments
                        </h1>
                        <p class="text-sm font-medium text-slate-500">
                            Monitor and assist with your children's learning
                            tasks across all subjects.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div
                        class="flex flex-col items-end rounded-2xl border border-slate-100 bg-white px-5 py-3 shadow-sm"
                    >
                        <span
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                            >Total Pipeline</span
                        >
                        <span
                            class="text-xl leading-none font-bold tracking-tighter text-blue-600"
                            >{{ total_assignments }} Tasks</span
                        >
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="total_assignments === 0"
                class="rounded-xl border border-slate-100 bg-white p-20 text-center shadow-xl shadow-slate-200/20"
            >
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-200"
                >
                    <ClipboardList class="h-10 w-10" />
                </div>
                <h3
                    class="mb-2 text-2xl font-bold tracking-tight text-slate-900 uppercase"
                >
                    No Active Assignments
                </h3>
                <p class="mx-auto max-w-sm text-sm font-medium text-slate-400">
                    Your children currently have no published tasks in their
                    respective classrooms.
                </p>
            </div>

            <!-- Organized Lists by Child -->
            <div v-else class="space-y-16">
                <div
                    v-for="child in children"
                    :key="child.id"
                    class="space-y-6"
                >
                    <!-- Child Section Header -->
                    <div class="flex items-center justify-between px-2">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-base font-bold text-white shadow-lg"
                            >
                                {{ child.name.charAt(0) }}
                            </div>
                            <div>
                                <h2
                                    class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    {{ child.name }}
                                </h2>
                                <p
                                    class="text-xs font-bold tracking-tight text-blue-600 uppercase"
                                >
                                    {{ child.class || 'No Class Assigned' }}
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="`/guardian/children/${child.id}`"
                            class="flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase transition-colors hover:text-blue-600"
                        >
                            View Child Profile <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <!-- Individual Assignments for this Child -->
                    <div
                        v-if="child.assignments.length === 0"
                        class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/20 p-10 text-center"
                    >
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            No individual assignments for this student
                        </p>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-1"
                    >
                        <Link
                            v-for="a in child.assignments"
                            :key="a.id"
                            :href="
                                route('guardian.assignments.show', {
                                    assignment: a.id,
                                    student_id: child.id,
                                })
                            "
                            class="group relative flex flex-col justify-between gap-6 rounded-2xl border border-slate-100 bg-white p-6 transition-all hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/10 active:translate-y-0 lg:flex-row lg:items-center"
                            :class="
                                a.is_overdue && !a.submitted
                                    ? 'border-l-4 border-l-rose-500'
                                    : ''
                            "
                        >
                            <div
                                class="flex min-w-0 flex-1 items-start gap-5 sm:items-center"
                            >
                                <!-- Status Icon -->
                                <div
                                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-110"
                                    :class="
                                        a.submitted
                                            ? 'bg-emerald-50 text-emerald-600'
                                            : a.is_overdue
                                              ? 'bg-rose-50 text-rose-600'
                                              : 'bg-slate-50 text-slate-400'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="a.submitted"
                                        class="h-6 w-6"
                                    />
                                    <AlertCircle
                                        v-else-if="a.is_overdue"
                                        class="h-6 w-6"
                                    />
                                    <FileText v-else class="h-6 w-6" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="mb-1.5 flex flex-wrap items-center gap-2"
                                    >
                                        <Badge
                                            variant="outline"
                                            class="rounded-lg border-slate-100 bg-slate-50 px-2 py-0.5 text-xs font-medium tracking-tight text-slate-500 uppercase transition-colors group-hover:border-blue-100 group-hover:bg-blue-50 group-hover:text-blue-600"
                                        >
                                            {{ a.assignment_type }}
                                        </Badge>
                                        <Badge
                                            class="rounded border-0 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600"
                                        >
                                            {{ a.subject }}
                                        </Badge>
                                    </div>
                                    <h3
                                        class="text-base leading-tight font-bold tracking-tight text-slate-900 transition-colors group-hover:text-blue-600"
                                    >
                                        {{ a.title }}
                                    </h3>
                                    <div
                                        class="mt-2 flex items-center gap-3 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        <span class="flex items-center gap-1.5"
                                            ><User class="h-3 w-3" />
                                            {{ a.teacher }}</span
                                        >
                                        <span
                                            class="h-1 w-1 rounded-full bg-slate-200"
                                        ></span>
                                        <span class="flex items-center gap-1.5"
                                            ><Calendar class="h-3 w-3" /> Due
                                            {{ a.due_date }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between gap-6 border-t border-slate-50 pt-4 lg:justify-end lg:border-t-0 lg:pt-0"
                            >
                                <div class="flex flex-col items-end text-right">
                                    <Badge
                                        :class="[
                                            'rounded-xl border-0 px-4 py-1.5 text-xs font-bold uppercase shadow-sm',
                                            getStatusBadge(a.status),
                                        ]"
                                    >
                                        {{ getStatusLabel(a.status) }}
                                    </Badge>
                                    <p
                                        v-if="a.total_marks"
                                        class="mt-2 px-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Score: {{ a.total_marks }} Pts
                                    </p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-300 transition-all group-hover:bg-blue-600 group-hover:text-white"
                                >
                                    <ArrowRight class="h-5 w-5" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
