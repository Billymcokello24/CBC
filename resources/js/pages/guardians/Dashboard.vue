<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    Users, BookOpen, ClipboardList, Clock, TrendingUp, CheckCircle2,
    AlertCircle, ArrowRight, GraduationCap, Calendar
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
    { label: 'My Children', value: props.stats.total_children, icon: Users, color: 'bg-blue-500', href: '/guardian/children' },
    { label: 'Published Assignments', value: props.stats.total_assignments, icon: ClipboardList, color: 'bg-violet-500', href: '/guardian/assignments' },
    { label: 'Pending Submissions', value: props.stats.pending_submissions, icon: AlertCircle, color: 'bg-amber-500', href: '/guardian/assignments' },
    { label: 'Submitted', value: props.stats.submitted, icon: CheckCircle2, color: 'bg-emerald-500', href: '/guardian/assignments' },
    { label: 'Graded', value: props.stats.graded, icon: GraduationCap, color: 'bg-rose-500', href: '/guardian/assignments' },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active': return 'bg-emerald-500 text-white';
        case 'suspended': return 'bg-rose-500 text-white';
        case 'inactive':
        case 'withdrawn': return 'bg-slate-400 text-white';
        case 'graduated': return 'bg-blue-600 text-white';
        default: return 'bg-indigo-500 text-white';
    }
};
</script>

<template>
    <Head title="Parent Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 md:p-8 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Welcome Banner -->
            <div class="relative rounded-2xl border bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white shadow-xl overflow-hidden">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/5"></div>
                <div class="absolute right-10 bottom-5 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative z-10">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-blue-200">Parent Portal</p>
                    <h1 class="text-3xl font-bold tracking-tight mt-2">Welcome, {{ guardian.name }}</h1>
                    <p class="mt-2 text-sm text-blue-100 max-w-lg">
                        Track your children's academic progress, assignments, attendance, and more — all in one place.
                    </p>
                    <div class="flex items-center gap-6 mt-6">
                        <div v-if="guardian.email" class="flex items-center gap-2 text-xs text-blue-200">
                            <span class="font-semibold">{{ guardian.email }}</span>
                        </div>
                        <div v-if="guardian.phone" class="flex items-center gap-2 text-xs text-blue-200">
                            <span class="font-semibold">{{ guardian.phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
                <Link
                    v-for="stat in statCards" :key="stat.label"
                    :href="stat.href"
                    class="rounded-2xl border bg-card p-5 shadow-sm hover:shadow-md transition-all duration-300 group hover:border-blue-200 cursor-pointer"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div :class="stat.color" class="h-10 w-10 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <component :is="stat.icon" class="h-5 w-5" />
                        </div>
                        <ArrowRight class="h-4 w-4 text-muted-foreground/30 group-hover:text-blue-500 transition-colors" />
                    </div>
                    <p class="text-3xl font-bold text-foreground">{{ stat.value }}</p>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground mt-1">{{ stat.label }}</p>
                </Link>
            </div>

            <!-- Children Cards -->
            <div class="rounded-2xl border bg-card p-7 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-foreground">My Children</h2>
                            <p class="text-xs text-muted-foreground">Select a child to view their full academic profile.</p>
                        </div>
                    </div>
                    <Button as-child variant="outline" size="sm" class="rounded-xl">
                        <Link href="/guardian/children">View All</Link>
                    </Button>
                </div>

                <div v-if="children.length === 0" class="rounded-xl border border-dashed p-12 text-center text-muted-foreground">
                    <Users class="h-10 w-10 mx-auto mb-3 opacity-30" />
                    <p class="text-sm font-semibold">No learners linked to your account yet.</p>
                </div>

                <div v-else class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="child in children" :key="child.id"
                        :href="`/guardian/children/${child.id}`"
                        class="group rounded-2xl border p-6 transition-all duration-300 hover:border-blue-200 hover:shadow-lg cursor-pointer bg-card relative overflow-hidden"
                    >
                        <div class="absolute top-0 right-0 h-20 w-20 rounded-bl-[3rem] bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex items-center gap-4 relative z-10">
                            <div class="h-14 w-14 rounded-2xl overflow-hidden shadow-md bg-muted flex-shrink-0 ring-2 ring-white">
                                <img v-if="child.photo_url" :src="child.photo_url" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white text-xl font-black">
                                    {{ child.name?.[0] || 'S' }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-foreground truncate">{{ child.name }}</h3>
                                <p class="text-xs text-muted-foreground">{{ child.admission_number || 'No admission number' }}</p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-2 text-sm relative z-10">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">Class</span>
                                <span class="font-bold text-foreground text-xs uppercase">{{ child.class || 'Unassigned' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">Status</span>
                                <Badge :class="getStatusColor(child.status)" class="rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider border-0">
                                    {{ child.status }}
                                </Badge>
                            </div>
                        </div>
                        <div class="mt-5 flex items-center gap-2 text-xs font-bold text-blue-600 group-hover:text-blue-700 transition-colors relative z-10">
                            Open Profile
                            <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
