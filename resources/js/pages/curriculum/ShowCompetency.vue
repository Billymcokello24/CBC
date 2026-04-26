<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import {
    BrainCircuit,
    ArrowLeft,
    Edit,
    Target,
    CheckCircle2,
    ShieldCheck,
    ChevronRight,
    Sparkles,
    Network,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    competency: {
        id: number;
        name: string;
        code: string;
        description: string | null;
        category: string | null;
        display_order: number;
        is_active: boolean;
        indicators: Array<{
            id: number;
            indicator: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Core Competencies', href: '/curriculum/competencies' },
    {
        title: props.competency.name,
        href: `/curriculum/competencies/${props.competency.id}`,
    },
];
</script>

<template>
    <Head :title="competency.name" />
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
                        <Link href="/curriculum/competencies"
                            ><ArrowLeft class="h-4 w-4"
                        /></Link>
                    </Button>
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <BrainCircuit class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ competency.name }}
                        </h1>
                        <p
                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >
                            {{ competency.code }} •
                            {{ competency.category || 'Core Pillar' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        as-child
                        class="font-pulsar h-10 border-slate-200"
                    >
                        <Link
                            :href="`/curriculum/competencies/${competency.id}/edit`"
                            ><Edit class="mr-2 h-4 w-4" />Modify Pillar</Link
                        >
                    </Button>
                    <Button
                        class="font-pulsar h-10 border-0 bg-violet-600 shadow-lg hover:bg-violet-700"
                        as-child
                    >
                        <Link href="/curriculum/competencies/create"
                            >Extend Matrix</Link
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
                        <Sparkles
                            class="h-5 w-5 text-violet-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Pillar Category
                        </p>
                        <p
                            class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            {{ competency.category || 'CORE' }}
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
                            Logic Pulse
                        </p>
                        <p class="text-xl font-bold text-emerald-600">
                            {{ competency.is_active ? 'ACTIVE' : 'DORMANT' }}
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-blue-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-blue-500/10 p-3 transition-all group-hover:bg-blue-500 group-hover:text-white"
                    >
                        <Target
                            class="h-5 w-5 text-blue-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Total Indicators
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ competency.indicators.length }} Nodes
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
                            Sequence Hub
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-indigo-600 uppercase"
                        >
                            ORDER #{{ competency.display_order }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detailed Analysis & Indicators -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Pillar Specification -->
                <div class="flex flex-col gap-6 lg:col-span-12">
                    <div
                        class="relative overflow-hidden rounded-xl border border-t-[12px] border-t-violet-500 bg-card p-10 shadow-sm"
                    >
                        <div
                            class="absolute -top-20 -right-20 rotate-12 opacity-[0.03]"
                        >
                            <BrainCircuit class="h-96 w-96 text-slate-950" />
                        </div>
                        <div class="relative z-10">
                            <h2
                                class="mb-8 flex items-center gap-3 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                <Network class="h-4 w-4 text-violet-600" />
                                Pillar Specification
                            </h2>
                            <div class="max-w-4xl">
                                <p
                                    class="mb-10 border-l-[6px] border-l-violet-200 pl-8 text-2xl leading-relaxed font-medium text-slate-900 opacity-90"
                                >
                                    {{
                                        competency.description ||
                                        'Core competency pillar guiding integrated learner development and cross-cutting academic behavioral indicators within the CBC framework.'
                                    }}
                                </p>
                                <div class="grid gap-6 sm:grid-cols-3">
                                    <div
                                        class="group rounded-3xl border bg-slate-50 p-6 shadow-inner transition-all hover:bg-white hover:shadow-md"
                                    >
                                        <h4
                                            class="mb-2 text-xs font-bold tracking-tight text-slate-400 uppercase group-hover:text-violet-500"
                                        >
                                            Registry Hash
                                        </h4>
                                        <p
                                            class="text-base font-bold text-slate-900 uppercase"
                                        >
                                            {{ competency.code }}
                                        </p>
                                    </div>
                                    <div
                                        class="group rounded-3xl border bg-slate-50 p-6 shadow-inner transition-all hover:bg-white hover:shadow-md"
                                    >
                                        <h4
                                            class="mb-2 text-xs font-bold tracking-tight text-slate-400 uppercase group-hover:text-violet-500"
                                        >
                                            Academic Target
                                        </h4>
                                        <p
                                            class="text-base font-bold text-slate-900 uppercase"
                                        >
                                            {{
                                                competency.category ||
                                                'UNIVERSAL'
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="group rounded-3xl border bg-slate-50 p-6 shadow-inner transition-all hover:bg-white hover:shadow-md"
                                    >
                                        <h4
                                            class="mb-2 text-xs font-bold tracking-tight text-slate-400 uppercase group-hover:text-violet-500"
                                        >
                                            Sequence Index
                                        </h4>
                                        <p
                                            class="text-base font-bold text-slate-900 uppercase"
                                        >
                                            POSITION #{{
                                                competency.display_order
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Indicators Matrix -->
                    <div
                        class="shrink-0 overflow-hidden rounded-xl border border-t-4 border-t-emerald-500 bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b bg-slate-50 px-10 py-6"
                        >
                            <h2
                                class="flex items-center gap-3 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                <Target class="h-4 w-4 text-emerald-600" />
                                Performance Indicators Matrix
                            </h2>
                            <Badge
                                variant="outline"
                                class="border-slate-200 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >{{
                                    competency.indicators.length
                                }}
                                Indicators</Badge
                            >
                        </div>

                        <div class="relative divide-y">
                            <div
                                v-if="competency.indicators.length === 0"
                                class="p-20 text-center"
                            >
                                <div
                                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-slate-100 bg-slate-50"
                                >
                                    <Target class="h-10 w-10 text-slate-300" />
                                </div>
                                <h3
                                    class="mb-2 text-base font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    Matrix is currently empty
                                </h3>
                                <p
                                    class="mx-auto max-w-md text-sm font-medium text-slate-400"
                                >
                                    No performance indicators established for
                                    this pillar. Initialize nodes to begin
                                    behavioral mapping.
                                </p>
                                <Button
                                    class="font-pulsar mt-8 h-10 rounded-xl border-0 bg-slate-900 px-6 text-xs tracking-tight uppercase shadow-xl hover:bg-black"
                                    >Initialize Indicators</Button
                                >
                            </div>

                            <div
                                v-for="(node, index) in competency.indicators"
                                :key="node.id"
                                class="group flex items-center justify-between border-l-[6px] border-l-transparent px-10 py-8 transition-all duration-300 hover:border-l-emerald-500 hover:bg-emerald-50/30"
                            >
                                <div class="flex flex-1 items-center gap-6">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-[12px] font-bold text-slate-400 uppercase shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    <div class="max-w-3xl flex-1">
                                        <div
                                            class="text-lg leading-relaxed font-bold tracking-tight text-slate-900 transition-colors group-hover:text-emerald-700"
                                        >
                                            {{ node.indicator }}
                                        </div>
                                        <div
                                            class="mt-1.5 flex items-center gap-2 text-xs font-bold tracking-[0.1em] text-slate-400 uppercase"
                                        >
                                            <Network class="h-3 w-3" />
                                            Indicator Logic Node #{{ node.id }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8 pl-8">
                                    <div class="flex flex-col items-end gap-1">
                                        <span
                                            class="mb-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Status</span
                                        >
                                        <Badge
                                            :class="
                                                node.is_active
                                                    ? 'bg-emerald-500 text-white shadow-[0_0_10px_rgba(16,185,129,0.4)]'
                                                    : 'bg-slate-300 text-white'
                                            "
                                            class="rounded-full border-0 px-3 py-1 text-xs font-medium tracking-tight uppercase"
                                        >
                                            {{
                                                node.is_active
                                                    ? 'PULSE ACTIVE'
                                                    : 'DORMANT'
                                            }}
                                        </Badge>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 text-slate-300 group-hover:text-emerald-600 hover:bg-white hover:shadow-sm"
                                        as-child
                                    >
                                        <Link
                                            :href="`/curriculum/competencies/${competency.id}/edit`"
                                            ><ChevronRight class="h-6 w-6"
                                        /></Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Matrix Callout -->
            <div
                class="group relative mt-6 flex flex-col items-center justify-between gap-8 overflow-hidden rounded-2xl border-[12px] border-slate-900 bg-slate-950 p-8 text-white shadow-lg md:flex-row"
            >
                <div
                    class="absolute -top-20 -right-20 rotate-45 transform opacity-10 transition-transform duration-700 group-hover:scale-110"
                >
                    <BrainCircuit class="h-64 w-64" />
                </div>
                <div class="relative z-10 flex items-center gap-6">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-inner"
                    >
                        <ShieldCheck class="h-7 w-7 text-violet-400" />
                    </div>
                    <div>
                        <h3
                            class="text-lg font-bold tracking-tighter text-white uppercase"
                        >
                            Competency Pillar Integrity
                        </h3>
                        <p
                            class="mt-1 text-sm font-medium tracking-tight text-slate-400 opacity-80"
                        >
                            This logic node and its indicators are synchronized
                            with the global CBC performance matrix for the 2026
                            Academic Cycle.
                        </p>
                    </div>
                </div>
                <div class="relative z-10 flex gap-3">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-10 cursor-default rounded-2xl border-white/10 bg-white/5 px-6 text-xs font-bold tracking-tight text-white uppercase hover:bg-white/20"
                        >Matrix: Integrated</Button
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
