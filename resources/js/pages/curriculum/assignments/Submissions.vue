<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Target,
    ArrowLeft,
    User,
    Calendar,
    Download,
    Eye,
    Search,
    AlertCircle,
    ChevronRight,
    TrendingUp,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignment: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'Submission Scoreboard', href: '#' },
];

const openReview = (submission: any) => {
    router.get(
        route('curriculum.assignments.submissions.review', {
            assignment: props.assignment.id,
            submission: submission.id,
        }),
    );
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'graded':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted':
            return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'pending':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        default:
            return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};
</script>

<template>
    <Head title="Submission Scoreboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1400px] flex-1 animate-in flex-col gap-8 p-8 font-sans duration-500 fade-in"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <Link
                        :href="route('curriculum.assignments.index')"
                        class="group mb-2 flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-blue-600"
                    >
                        <ArrowLeft
                            class="h-3 w-3 transition-transform group-hover:-translate-x-1"
                        />
                        Back to Assignments
                    </Link>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 shadow-sm"
                        >
                            <Target class="h-5 w-5" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Submission Scoreboard
                            </h1>
                            <p class="text-xs font-medium text-slate-500">
                                {{ assignment.title }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-xl border-slate-100 bg-[#f9fafb]/50 px-4 text-xs font-bold text-slate-500 shadow-none transition-all hover:bg-slate-50"
                    >
                        <Download class="mr-2 h-4 w-4" /> Export Results
                    </Button>
                </div>
            </div>

            <!-- Dashboard Statistics -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(val, label) in {
                        Learners: 45,
                        Submissions: assignment.submissions?.length || 0,
                        'To Assess':
                            assignment.submissions?.filter(
                                (s: any) => s.status !== 'graded',
                            ).length || 0,
                        'Class Index': '74%',
                    }"
                    :key="label"
                    class="group flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-all hover:border-blue-200"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            {{ label }}
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ val }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all group-hover:bg-blue-50 group-hover:text-blue-600"
                    >
                        <TrendingUp
                            v-if="label === 'Class Index'"
                            class="h-5 w-5"
                        />
                        <Users
                            v-else-if="label === 'Learners'"
                            class="h-5 w-5"
                        />
                        <FileText v-else class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Learner Directory -->
            <div
                class="min-h-[500px] overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-50 bg-white p-6 md:flex-row md:items-center"
                >
                    <div class="flex items-center gap-3">
                        <h3
                            class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Learner Scoreboard
                        </h3>
                        <Badge
                            class="rounded border-0 bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-600 uppercase"
                            >Live Intake</Badge
                        >
                    </div>
                    <div class="relative w-full md:max-w-md">
                        <Search
                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <Input
                            placeholder="Filter learners..."
                            class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 pl-10 text-xs font-bold shadow-none transition-all focus:bg-white"
                        />
                    </div>
                </div>

                <div
                    v-if="assignment.submissions?.length"
                    class="overflow-x-auto"
                >
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-50 bg-white">
                                <th
                                    class="w-1/3 py-4 pl-6 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Learner Profile
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Date/Time
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-4 text-right text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Performance
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr
                                v-for="submission in assignment.submissions"
                                :key="submission.id"
                                class="group transition-all hover:bg-[#f9fafb]/50"
                            >
                                <td class="py-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-xs font-bold text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white"
                                        >
                                            {{
                                                submission.student?.first_name?.charAt(
                                                    0,
                                                )
                                            }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="line-clamp-1 cursor-pointer text-sm font-bold text-slate-800 transition-colors hover:text-blue-600"
                                                @click="openReview(submission)"
                                            >
                                                {{
                                                    submission.student
                                                        ?.first_name
                                                }}
                                                {{
                                                    submission.student
                                                        ?.last_name
                                                }}
                                            </span>
                                            <span
                                                class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                                >ID: #{{
                                                    submission.student
                                                        ?.admission_number ||
                                                    '001'
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-slate-700"
                                            >{{
                                                new Date(
                                                    submission.submitted_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    { dateStyle: 'medium' },
                                                )
                                            }}</span
                                        >
                                        <span
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >{{
                                                new Date(
                                                    submission.submitted_at,
                                                ).toLocaleTimeString(
                                                    undefined,
                                                    { timeStyle: 'short' },
                                                )
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <Badge
                                        :class="[
                                            'rounded border-0 px-3 py-1 text-xs font-bold uppercase',
                                            getStatusBadge(submission.status),
                                        ]"
                                    >
                                        {{ submission.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="text-lg font-bold tracking-tight text-slate-900"
                                            >{{
                                                submission.marks_obtained !==
                                                null
                                                    ? submission.marks_obtained
                                                    : '--'
                                            }}</span
                                        >
                                        <span
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >/
                                            {{ assignment.total_marks }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="
                                            route(
                                                'curriculum.assignments.submissions.review',
                                                {
                                                    assignment: assignment.id,
                                                    submission: submission.id,
                                                },
                                            )
                                        "
                                        class="inline-flex h-9 items-center rounded-xl bg-blue-50 px-4 text-xs font-bold text-blue-600 uppercase shadow-sm transition-all hover:bg-blue-600 hover:text-white"
                                    >
                                        Review
                                        <ChevronRight
                                            class="ml-1 h-3.5 w-3.5"
                                        />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-32 text-center"
                >
                    <div
                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-slate-50"
                    >
                        <AlertCircle class="h-10 w-10 text-slate-200" />
                    </div>
                    <h3
                        class="mb-2 text-2xl font-bold tracking-tight text-slate-300 uppercase"
                    >
                        No Inbound Data
                    </h3>
                    <p class="text-sm text-slate-400">
                        The class list for this assignment is currently empty.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* High-end list styling */
</style>
