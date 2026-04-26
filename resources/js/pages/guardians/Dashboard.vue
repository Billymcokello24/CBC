<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    Users,
    BookOpen,
    ClipboardList,
    Clock,
    TrendingUp,
    CheckCircle2,
    AlertCircle,
    ArrowRight,
    GraduationCap,
    Calendar,
} from 'lucide-vue-next';

const props = defineProps<{
    guardian: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        relationship_type: string | null;
    };
    children: Array<{
        id: number;
        name: string;
        admission_number: string | null;
        class: string | null;
        status: string;
        photo_url: string | null;
        gender: string | null;
    }>;
    stats: {
        total_children: number;
        total_assignments: number;
        pending_submissions: number;
        submitted: number;
        graded: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
];

const statCards = [
    {
        label: 'My Children',
        value: props.stats.total_children,
        icon: Users,
        color: 'bg-blue-500',
        href: '/guardian/children',
    },
    {
        label: 'Published Assignments',
        value: props.stats.total_assignments,
        icon: ClipboardList,
        color: 'bg-violet-500',
        href: '/guardian/assignments',
    },
    {
        label: 'Pending Submissions',
        value: props.stats.pending_submissions,
        icon: AlertCircle,
        color: 'bg-amber-500',
        href: '/guardian/assignments',
    },
    {
        label: 'Submitted',
        value: props.stats.submitted,
        icon: CheckCircle2,
        color: 'bg-emerald-500',
        href: '/guardian/assignments',
    },
    {
        label: 'Graded',
        value: props.stats.graded,
        icon: GraduationCap,
        color: 'bg-rose-500',
        href: '/guardian/assignments',
    },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white';
        case 'suspended':
            return 'bg-rose-500 text-white';
        case 'inactive':
        case 'withdrawn':
            return 'bg-slate-400 text-white';
        case 'graduated':
            return 'bg-blue-600 text-white';
        default:
            return 'bg-indigo-500 text-white';
    }
};
</script>

<template>
    <Head title="Parent Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-6 duration-500 fade-in md:p-8"
        >
            <!-- Welcome Banner -->
            <div
                class="relative overflow-hidden rounded-2xl border bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white shadow-xl"
            >
                <div
                    class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/5"
                ></div>
                <div
                    class="absolute right-10 bottom-5 h-24 w-24 rounded-full bg-white/5"
                ></div>
                <div class="relative z-10">
                    <p
                        class="text-sm font-medium tracking-tight text-blue-200 text-muted-foreground uppercase"
                    >
                        Parent Portal
                    </p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight">
                        Welcome, {{ guardian.name }}
                    </h1>
                    <p class="mt-2 max-w-lg text-sm text-blue-100">
                        Track your children's academic progress, assignments,
                        attendance, and more — all in one place.
                    </p>
                    <div class="mt-6 flex items-center gap-6">
                        <div
                            v-if="guardian.email"
                            class="flex items-center gap-2 text-xs text-blue-200"
                        >
                            <span class="font-semibold">{{
                                guardian.email
                            }}</span>
                        </div>
                        <div
                            v-if="guardian.phone"
                            class="flex items-center gap-2 text-xs text-blue-200"
                        >
                            <span class="font-semibold">{{
                                guardian.phone
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <Link
                    v-for="stat in statCards"
                    :key="stat.label"
                    :href="stat.href"
                    class="group cursor-pointer rounded-2xl border bg-card p-5 shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-md"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div
                            :class="stat.color"
                            class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-lg"
                        >
                            <component :is="stat.icon" class="h-5 w-5" />
                        </div>
                        <ArrowRight
                            class="h-4 w-4 text-muted-foreground/30 transition-colors group-hover:text-blue-500"
                        />
                    </div>
                    <p class="text-3xl font-bold text-foreground">
                        {{ stat.value }}
                    </p>
                    <p
                        class="mt-1 text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        {{ stat.label }}
                    </p>
                </Link>
            </div>

            <!-- Children Cards -->
            <div class="rounded-2xl border bg-card p-7 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-foreground">
                                My Children
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                Select a child to view their full academic
                                profile.
                            </p>
                        </div>
                    </div>
                    <Button
                        as-child
                        variant="outline"
                        size="sm"
                        class="rounded-xl"
                    >
                        <Link href="/guardian/children">View All</Link>
                    </Button>
                </div>

                <div
                    v-if="children.length === 0"
                    class="rounded-xl border border-dashed p-12 text-center text-muted-foreground"
                >
                    <Users class="mx-auto mb-3 h-10 w-10 opacity-30" />
                    <p class="text-sm font-semibold">
                        No learners linked to your account yet.
                    </p>
                </div>

                <div v-else class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="child in children"
                        :key="child.id"
                        :href="`/guardian/children/${child.id}`"
                        class="group relative cursor-pointer overflow-hidden rounded-2xl border bg-card p-6 transition-all duration-300 hover:border-blue-200 hover:shadow-lg"
                    >
                        <div
                            class="absolute top-0 right-0 h-20 w-20 rounded-bl-[3rem] bg-gradient-to-br from-blue-50 to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div
                                class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-2xl bg-muted shadow-md ring-2 ring-white"
                            >
                                <img
                                    v-if="child.photo_url"
                                    :src="child.photo_url"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-xl font-bold text-white"
                                >
                                    {{ child.name?.[0] || 'S' }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h3
                                    class="truncate text-base font-bold text-foreground"
                                >
                                    {{ child.name }}
                                </h3>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        child.admission_number ||
                                        'No admission number'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="relative z-10 mt-5 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground"
                                    >Class</span
                                >
                                <span
                                    class="text-xs font-bold text-foreground uppercase"
                                    >{{ child.class || 'Unassigned' }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground"
                                    >Status</span
                                >
                                <Badge
                                    :class="getStatusColor(child.status)"
                                    class="rounded-lg border-0 px-2.5 py-0.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    {{ child.status }}
                                </Badge>
                            </div>
                        </div>
                        <div
                            class="relative z-10 mt-5 flex items-center gap-2 text-xs font-bold text-blue-600 transition-colors group-hover:text-blue-700"
                        >
                            Open Profile
                            <ArrowRight
                                class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                            />
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
