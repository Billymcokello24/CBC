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
    Home,
    ChevronRight,
    FileText,
    TrendingUp,
    Users,
    MoreHorizontal,
    Archive,
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
    { title: 'Home', href: '/dashboard', icon: Home },
    { title: 'Assignments', href: '/curriculum/assignments' },
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
            return 'bg-primary/10 text-primary';
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
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Assignments</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        {{ isGuardian ? 'Student Assignments' : 'Academic Assignments' }}
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        {{
                            isGuardian
                                ? "Track your children's tasks and academic progress."
                                : 'Manage assignments and track student performance.'
                        }}
                    </p>
                </div>

                <div class="flex items-center gap-3" v-if="!isGuardian">
                    <Link
                        :href="route('curriculum.assignments.vault')"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-border bg-card px-4 text-xs font-semibold text-foreground shadow-sm transition-all hover:bg-muted"
                    >
                        <Archive class="h-4 w-4 text-primary" /> View Vault
                    </Link>
                    <Link
                        href="/curriculum/assignments/create"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-primary px-4 text-xs font-semibold text-white shadow-sm shadow-primary/20 transition-all hover:bg-primary/90"
                    >
                        <Plus class="h-4 w-4" /> Create New
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all"
                        >
                            <FileText class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Total</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">
                        {{ isGuardian ? 'Active Tasks' : 'All Assignments' }}
                    </p>
                    <h3 class="text-2xl font-bold text-foreground">
                        {{ assignments.length }}
                    </h3>
                </div>

                <template v-if="!isGuardian">
                    <div
                        class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20 dark:border-white/5"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all"
                            >
                                <CheckCircle2 class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Live</span>
                        </div>
                        <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Published</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ publishedCount }}</h3>
                    </div>
                    <div
                        class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-orange-500/20 dark:border-white/5"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-all"
                            >
                                <AlertCircle class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Draft</span>
                        </div>
                        <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Saved Drafts</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ draftCount }}</h3>
                    </div>
                    <div
                        class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-purple-500/20 dark:border-white/5"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-all"
                            >
                                <Users class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Engagement</span>
                        </div>
                        <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Submissions</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ totalSubmissions }}</h3>
                    </div>
                </template>

                <template v-else>
                    <div
                        class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-orange-500/20 dark:border-white/5"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-all"
                            >
                                <Clock class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Attention</span>
                        </div>
                        <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Pending</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ pendingSubmissions }}</h3>
                    </div>
                    <div
                        class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20 dark:border-white/5"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all"
                            >
                                <TrendingUp class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Results</span>
                        </div>
                        <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Graded</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ gradedSubmissions }}</h3>
                    </div>
                    <div
                        class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-purple-500/20 dark:border-white/5"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-all"
                            >
                                <Users class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Family</span>
                        </div>
                        <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Children</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ children.length }}</h3>
                    </div>
                </template>
            </div>

            <!-- Main Content -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <!-- Toolbar -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-50 p-6 md:flex-row md:items-center"
                >
                    <div class="relative w-full md:max-w-md">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/40"
                        />
                        <Input
                            placeholder="Find assignments..."
                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-sm font-medium transition-all focus:ring-2 focus:ring-primary/10 focus:bg-background"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <Button
                            variant="outline"
                            class="h-11 rounded-xl border-border bg-muted/20 px-4 text-xs font-bold text-muted-foreground transition-all hover:bg-background hover:text-primary"
                        >
                            <Filter class="mr-2 h-4 w-4 opacity-40" /> Filters
                        </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto" v-if="assignments.length">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5">
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
                        <tbody class="divide-y divide-border/50">
                            <tr
                                v-for="assignment in assignments"
                                :key="assignment.id"
                                class="group transition-all hover:bg-muted/30"
                            >
                                <td class="py-4 pl-6">
                                    <div
                                        class="h-4 w-4 rounded border border-border transition-colors group-hover:border-primary/40"
                                    ></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:bg-primary group-hover:text-white"
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
                                                class="line-clamp-1 text-sm font-bold text-foreground transition-colors group-hover:text-primary"
                                                >{{ assignment.title }}</span
                                            >
                                            <span
                                                class="text-xs font-medium text-muted-foreground/40 uppercase"
                                                >{{
                                                    assignment.assignment_type ||
                                                    'homework'
                                                }}</span
                                            >
                                        </Link>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-muted-foreground"
                                >
                                    {{ assignment.subject?.name || '—' }}
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-muted-foreground"
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
                                            class="cursor-pointer rounded border-0 bg-primary/10 px-3 py-1 text-xs font-bold text-primary shadow-sm transition-all hover:bg-primary hover:text-white"
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
                                        class="rounded border-none bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary"
                                    >
                                        {{ assignment.submissions_count || 0 }}
                                    </Badge>
                                </td>
                                <td
                                    class="px-4 py-4 text-xs font-semibold text-muted-foreground"
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
                                            'rounded border-none px-2 py-0.5 text-xs font-bold uppercase',
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
                                            class="inline-flex h-9 items-center rounded-xl bg-muted/50 px-4 text-xs font-bold text-foreground uppercase border border-border transition-all hover:bg-primary hover:text-white hover:border-primary"
                                        >
                                            Review
                                            <ChevronRight
                                                class="ml-1 h-3.5 w-3.5 opacity-40"
                                            />
                                        </Link>
                                        <Link
                                            :href="`/curriculum/assignments/${assignment.id}`"
                                            v-else
                                            class="inline-flex h-9 items-center rounded-xl bg-primary px-4 text-xs font-bold text-white uppercase shadow-sm transition-all hover:bg-primary/90"
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
                                                    class="h-9 w-9 rounded-xl text-muted-foreground/40 hover:bg-muted hover:text-foreground"
                                                >
                                                    <MoreVertical
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="min-w-[130px] rounded-xl border border-border p-2 shadow-lg"
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
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted text-muted-foreground/20"
                    >
                        <Target class="h-8 w-8" />
                    </div>
                    <div class="space-y-1">
                        <h3
                            class="text-xl font-bold tracking-tight text-foreground"
                        >
                            No Assignments
                        </h3>
                        <p
                            class="mx-auto max-w-xs text-xs font-medium text-muted-foreground/60"
                        >
                            Create your first assignment to start tracking
                            student work.
                        </p>
                    </div>
                    <Link
                        href="/curriculum/assignments/create"
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                    >
                        <Plus class="h-4 w-4" /> New Assignment
                    </Link>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-border/50 bg-muted/5 p-6"
                    v-if="assignments.length"
                >
                    <p class="text-xs font-bold text-slate-400">
                        Found {{ assignments.length }} assignments
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-border px-3 text-xs font-bold text-muted-foreground/30"
                            >Prev</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-border bg-background px-3 text-xs font-bold text-foreground shadow-sm"
                            >1</Button
                        >
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-xl border-border px-3 text-xs font-bold text-muted-foreground/30"
                            >Next</Button
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
