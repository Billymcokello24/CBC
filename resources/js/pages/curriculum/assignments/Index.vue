<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Target,
    Plus,
    Search,
    Filter,
    MoreVertical,
    Eye,
    Edit2,
    Trash2,
    Calendar,
    BookOpen,
    Clock,
    CheckCircle2,
    AlertCircle,
    ChevronRight,
    FileText,
    TrendingUp,
    Users,
    MoreHorizontal,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignments: any[];
    subjects: any[];
    classes: any[];
    children: any[];
    userRole: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '#' },
];

const deleteAssignment = (id: number) => {
    if (confirm('Delete this assignment?')) {
        router.delete(`/curriculum/assignments/${id}`);
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'published':
            return 'bg-emerald-50 text-emerald-600';
        case 'draft':
            return 'bg-slate-50 text-slate-500';
        case 'closed':
            return 'bg-red-50 text-red-600';
        case 'graded':
            return 'bg-blue-50 text-blue-600';
        default:
            return 'bg-slate-50 text-slate-500';
    }
};

const isGuardian = computed(() => props.userRole === 'parent');

const publishedCount = computed(
    () => props.assignments.filter((a) => a.status === 'published').length,
);
const draftCount = computed(
    () => props.assignments.filter((a) => a.status === 'draft').length,
);
const totalSubmissions = computed(() =>
    props.assignments.reduce(
        (acc: number, a: any) => acc + (a.submissions_count || 0),
        0,
    ),
);

// Guardian specific stats
const pendingSubmissions = computed(
    () => props.assignments.filter((a) => !a.submissions?.length).length,
);
const gradedSubmissions = computed(
    () =>
        props.assignments.filter((a) =>
            a.submissions?.some((s: any) => s.status === 'graded'),
        ).length,
);
</script>

<template>
    <Head title="Assignments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 flex-col gap-6 bg-[#f9fafb]/30 p-6 font-sans"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900"
                    >
                        {{ isGuardian ? 'Student Assignments' : 'Assignments' }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{
                            isGuardian
                                ? "Keep track of your children's academic tasks and progress."
                                : 'Manage tasks and track student progress.'
                        }}
                    </p>
                </div>

                <div class="flex items-center gap-3" v-if="!isGuardian">
                    <Link
                        :href="route('curriculum.assignments.vault')"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-slate-900 px-4 text-xs font-semibold text-white shadow-sm transition-all hover:bg-slate-800"
                    >
                        <Archive class="h-4 w-4 text-blue-400" /> Academic Vault
                    </Link>
                    <Link
                        href="/curriculum/assignments/create"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" /> New Assignment
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="space-y-0.5">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            {{ isGuardian ? 'Active Tasks' : 'Total' }}
                        </p>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ assignments.length }}
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <FileText class="h-5 w-5" />
                    </div>
                </div>

                <template v-if="!isGuardian">
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <p
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Published
                            </p>
                            <h2
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ publishedCount }}
                            </h2>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"
                        >
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <p
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Drafts
                            </p>
                            <h2
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ draftCount }}
                            </h2>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500"
                        >
                            <AlertCircle class="h-5 w-5" />
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <p
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Submissions
                            </p>
                            <h2
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ totalSubmissions }}
                            </h2>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <p
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Pending
                            </p>
                            <h2
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ pendingSubmissions }}
                            </h2>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500"
                        >
                            <Clock class="h-5 w-5" />
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <p
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Graded
                            </p>
                            <h2
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ gradedSubmissions }}
                            </h2>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"
                        >
                            <TrendingUp class="h-5 w-5" />
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <p
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Children
                            </p>
                            <h2
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ children.length }}
                            </h2>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                    </div>
                </template>
            </div>

            <!-- Main Content -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <!-- Toolbar -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-50 p-6 md:flex-row md:items-center"
                >
                    <div class="relative w-full md:max-w-md">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <Input
                            placeholder="Search assignments..."
                            class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 pl-10 text-sm font-medium shadow-none transition-all focus:bg-white"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <Button
                            variant="outline"
                            class="h-11 rounded-xl border-slate-100 bg-[#f9fafb]/50 px-4 text-xs font-bold text-slate-500 shadow-none transition-all hover:bg-slate-50"
                        >
                            <Filter class="mr-2 h-4 w-4" /> Filters
                        </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto" v-if="assignments.length">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-50 bg-white">
                                <th class="w-10 py-4 pl-6">
                                    <div
                                        class="h-4 w-4 rounded border border-slate-200"
                                    ></div>
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Assignment
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Subject
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Class
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Submissions
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Due Date
                                </th>
                                <th
                                    class="px-4 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Status
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
                                v-for="assignment in assignments"
                                :key="assignment.id"
                                class="group transition-all hover:bg-[#f9fafb]/50"
                            >
                                <td class="py-4 pl-6">
                                    <div
                                        class="h-4 w-4 rounded border border-slate-200 transition-colors group-hover:border-blue-400"
                                    ></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white"
                                        >
                                            <FileText class="h-4 w-4" />
                                        </div>
                                        <Link
                                            :href="
                                                route(
                                                    'curriculum.assignments.show',
                                                    assignment.id,
                                                )
                                            "
                                            class="flex flex-col"
                                        >
                                            <span
                                                class="line-clamp-1 text-sm font-bold text-slate-800 transition-colors hover:text-blue-600"
                                                >{{ assignment.title }}</span
                                            >
                                            <span
                                                class="text-xs font-medium text-slate-400 uppercase"
                                                >{{
                                                    assignment.assignment_type ||
                                                    'homework'
                                                }}</span
                                            >
                                        </Link>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-slate-600"
                                >
                                    {{ assignment.subject?.name || '—' }}
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-slate-600"
                                >
                                    {{ assignment.classroom?.name || '—' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <Link
                                        v-if="!isGuardian"
                                        :href="
                                            route(
                                                'curriculum.assignments.submissions',
                                                assignment.id,
                                            )
                                        "
                                    >
                                        <Badge
                                            class="cursor-pointer rounded border-0 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600 shadow-sm transition-all hover:bg-blue-600 hover:text-white"
                                        >
                                            {{
                                                assignment.submissions_count ||
                                                0
                                            }}
                                            Submissions
                                        </Badge>
                                    </Link>
                                    <Badge
                                        v-else
                                        class="rounded border-0 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600"
                                    >
                                        {{ assignment.submissions_count || 0 }}
                                    </Badge>
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-slate-600"
                                >
                                    {{
                                        assignment.due_date
                                            ? new Date(
                                                  assignment.due_date,
                                              ).toLocaleDateString()
                                            : '—'
                                    }}
                                </td>
                                <td class="px-4 py-4">
                                    <Badge
                                        :class="[
                                            'rounded border-0 px-2 py-0.5 text-xs font-bold uppercase',
                                            getStatusBadge(assignment.status),
                                        ]"
                                    >
                                        {{ assignment.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/curriculum/assignments/${assignment.id}/submissions`"
                                            v-if="!isGuardian"
                                            class="inline-flex h-9 items-center rounded-xl bg-blue-50 px-4 text-xs font-bold text-blue-600 uppercase transition-all hover:bg-blue-600 hover:text-white"
                                        >
                                            Review
                                            <ChevronRight
                                                class="ml-1 h-3.5 w-3.5"
                                            />
                                        </Link>
                                        <Link
                                            :href="`/curriculum/assignments/${assignment.id}`"
                                            v-else
                                            class="inline-flex h-9 items-center rounded-xl bg-blue-50 px-4 text-xs font-bold text-blue-600 uppercase transition-all hover:bg-blue-600 hover:text-white"
                                        >
                                            Open Task
                                            <ChevronRight
                                                class="ml-1 h-3.5 w-3.5"
                                            />
                                        </Link>

                                        <DropdownMenu v-if="!isGuardian">
                                            <DropdownMenuTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-slate-400 hover:bg-slate-100"
                                                >
                                                    <MoreVertical
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="min-w-[130px] rounded-xl p-2"
                                            >
                                                <Link
                                                    :href="`/curriculum/assignments/${assignment.id}`"
                                                >
                                                    <DropdownMenuItem
                                                        class="cursor-pointer rounded-lg text-xs font-bold"
                                                    >
                                                        <Eye
                                                            class="mr-2 h-3.5 w-3.5"
                                                        />
                                                        View
                                                    </DropdownMenuItem>
                                                </Link>
                                                <Link
                                                    :href="`/curriculum/assignments/${assignment.id}/edit`"
                                                >
                                                    <DropdownMenuItem
                                                        class="cursor-pointer rounded-lg text-xs font-bold"
                                                    >
                                                        <Edit2
                                                            class="mr-2 h-3.5 w-3.5"
                                                        />
                                                        Edit
                                                    </DropdownMenuItem>
                                                </Link>
                                                <DropdownMenuItem
                                                    @click="
                                                        deleteAssignment(
                                                            assignment.id,
                                                        )
                                                    "
                                                    class="rounded-lg text-xs font-bold text-red-600"
                                                >
                                                    <Trash2
                                                        class="mr-2 h-3.5 w-3.5"
                                                    />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-4 bg-white py-20 text-center"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-200"
                    >
                        <Target class="h-8 w-8" />
                    </div>
                    <div class="space-y-1">
                        <h3
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            No Assignments
                        </h3>
                        <p
                            class="mx-auto max-w-xs text-xs font-medium text-slate-400"
                        >
                            Create your first assignment to start tracking
                            student work.
                        </p>
                    </div>
                    <Link
                        href="/curriculum/assignments/create"
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-blue-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-sm transition-all hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" /> New Assignment
                    </Link>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-slate-50 bg-white p-6"
                    v-if="assignments.length"
                >
                    <p class="text-xs font-bold text-slate-400">
                        Found {{ assignments.length }} assignments
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-100 px-3 text-xs font-bold text-slate-300"
                            >Prev</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-200 px-3 text-xs font-bold text-slate-900"
                            >1</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-slate-100 px-3 text-xs font-bold text-slate-300"
                            >Next</Button
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
