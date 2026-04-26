<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import {
    Layers,
    ArrowLeft,
    Edit,
    GraduationCap,
    CheckCircle2,
    ShieldCheck,
    Target,
    Network,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    strand: {
        id: number;
        name: string;
        code: string;
        description: string | null;
        display_order: number;
        is_active: boolean;
        subject_name: string;
        grade_name: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Strands Registry', href: '/curriculum/strands' },
    {
        title: props.strand.name,
        href: `/curriculum/strands/${props.strand.id}`,
    },
];
</script>

<template>
    <Head :title="strand.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="font-pulsar mx-auto mt-2 flex h-full max-w-[1400px] flex-1 animate-in flex-col gap-6 p-6 duration-500 fade-in"
        >
            <!-- Header section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-10 w-10 shrink-0 border-slate-200"
                    >
                        <Link href="/curriculum/strands"
                            ><ArrowLeft class="h-4 w-4"
                        /></Link>
                    </Button>
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <Layers class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ strand.name }}
                        </h1>
                        <p
                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >
                            {{ strand.code }} • {{ strand.subject_name }} •
                            {{ strand.grade_name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        as-child
                        class="font-pulsar h-10 border-slate-200"
                    >
                        <Link :href="`/curriculum/strands/${strand.id}/edit`"
                            ><Edit class="mr-2 h-4 w-4" />Modify Topic
                            Metadata</Link
                        >
                    </Button>
                    <Button
                        class="font-pulsar h-10 border-0 bg-violet-600 shadow-lg hover:bg-violet-700"
                        as-child
                    >
                        <Link
                            :href="`/curriculum/competencies/create?strand_id=${strand.id}`"
                            >Initialize Indicator</Link
                        >
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-violet-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-violet-500/10 p-3 transition-all group-hover:bg-violet-500 group-hover:text-white"
                    >
                        <Target
                            class="h-5 w-5 text-violet-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Grade Alignment
                        </p>
                        <p
                            class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            {{ strand.grade_name }}
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-emerald-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-emerald-500/10 p-3 transition-all group-hover:bg-emerald-500 group-hover:text-white"
                    >
                        <CheckCircle2
                            class="h-5 w-5 text-emerald-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Logic Status
                        </p>
                        <p class="text-xl font-bold text-emerald-600">
                            {{ strand.is_active ? 'ACTIVE PULSE' : 'DORMANT' }}
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-blue-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-blue-500/10 p-3 transition-all group-hover:bg-blue-500 group-hover:text-white"
                    >
                        <GraduationCap
                            class="h-5 w-5 text-blue-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Academic Host
                        </p>
                        <p
                            class="max-w-[120px] truncate text-xl font-bold tracking-tighter text-slate-900 uppercase"
                        >
                            {{ strand.subject_name }}
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-indigo-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-indigo-500/10 p-3 transition-all group-hover:bg-indigo-500 group-hover:text-white"
                    >
                        <ShieldCheck
                            class="h-5 w-5 text-indigo-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Matrix Hash
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-indigo-600 uppercase"
                        >
                            SECURED
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div
                        class="min-h-[400px] rounded-xl border border-t-8 border-t-violet-500 bg-card p-10 shadow-sm"
                    >
                        <h2
                            class="mb-8 flex items-center gap-3 text-xs font-bold tracking-tight text-slate-900 uppercase"
                        >
                            <Network class="h-4 w-4 text-violet-600" />
                            Instructional Specification
                        </h2>
                        <div class="space-y-8">
                            <div>
                                <h3
                                    class="mb-3 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Topic Objective
                                </h3>
                                <p
                                    class="border-l-4 border-l-violet-200 pl-6 text-lg leading-relaxed font-medium text-slate-900 opacity-90"
                                >
                                    {{
                                        strand.description ||
                                        'This curriculum strand specifies core learning objectives and performance indicators for the specified academic cycle.'
                                    }}
                                </p>
                            </div>
                            <div class="grid gap-8 pt-6 sm:grid-cols-2">
                                <div
                                    class="rounded-2xl border bg-slate-50 p-6 shadow-inner"
                                >
                                    <h4
                                        class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        System Node
                                    </h4>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ strand.code }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border bg-slate-50 p-6 shadow-inner"
                                >
                                    <h4
                                        class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Sequence Position
                                    </h4>
                                    <p class="text-sm font-bold text-slate-900">
                                        #{{ strand.display_order }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-6 lg:col-span-4">
                    <div
                        class="group relative flex-1 overflow-hidden rounded-xl border-[12px] border-slate-900 bg-slate-950 p-10 text-white shadow-lg"
                    >
                        <div
                            class="absolute -right-8 -bottom-8 transform opacity-10 transition-all duration-700 group-hover:scale-110 group-hover:-rotate-12"
                        >
                            <Layers class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-8">
                            <h3
                                class="flex items-center gap-3 text-xl font-bold tracking-tighter uppercase"
                            >
                                <Target class="h-6 w-6 text-violet-400" />
                                Mapping Intelligence
                            </h3>
                            <div class="space-y-6">
                                <div class="border-b border-white/10 pb-6">
                                    <p
                                        class="mb-2 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Academic Host
                                    </p>
                                    <p
                                        class="text-lg font-bold tracking-tight text-white"
                                    >
                                        {{ strand.subject_name }}
                                    </p>
                                </div>
                                <div class="border-b border-white/10 pb-6">
                                    <p
                                        class="mb-2 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Grade Hub
                                    </p>
                                    <p
                                        class="text-lg font-bold tracking-tight text-white"
                                    >
                                        {{ strand.grade_name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="mb-2 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Validation Node
                                    </p>
                                    <Badge
                                        class="border border-violet-500/30 bg-violet-500/20 text-xs font-medium tracking-tight text-violet-400 uppercase"
                                        >Logic: Verified</Badge
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matrix Synced Callout -->
            <div
                class="mt-2 flex flex-col items-center justify-between gap-6 rounded-3xl border border-slate-800 bg-slate-900 p-6 text-white shadow-lg md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10"
                    >
                        <ShieldCheck class="h-5 w-5 text-violet-400" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-white/90 uppercase"
                        >
                            Instructional Mapping Finalized
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-semibold tracking-wider text-slate-400"
                        >
                            Real-time instructional mapping and performance
                            matrix synchronization across the 2026 Academic
                            Cluster.
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 cursor-default rounded-xl border-white/10 bg-white/5 px-4 text-xs font-bold text-white uppercase hover:bg-white/20"
                        >Status: Validated</Button
                    >
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-bold {
    font-weight: 950;
}
</style>
