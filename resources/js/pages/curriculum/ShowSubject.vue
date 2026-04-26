<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ArrowLeft,
    Edit,
    GraduationCap,
    Layers,
    CheckCircle2,
    ShieldCheck,
    ChevronRight,
    BadgeCheck,
    Network,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subject: {
        id: number;
        learning_area: string | null;
        department: {
            id: number;
            name: string;
            code: string;
            head_of_department: string | null;
        } | null;
        name: string;
        code: string;
        description: string | null;
        subject_type: string;
        is_examinable: boolean;
        display_order: number;
        is_active: boolean;
        strands: Array<{
            id: number;
            name: string;
            code: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects Registry', href: '/curriculum/subjects' },
    {
        title: props.subject.name,
        href: `/curriculum/subjects/${props.subject.id}`,
    },
];
</script>

<template>
    <Head :title="subject.name" />
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
                        <Link href="/curriculum/subjects"
                            ><ArrowLeft class="h-4 w-4"
                        /></Link>
                    </Button>
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <BookOpen class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ subject.name }}
                        </h1>
                        <p
                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >
                            {{ subject.code }} •
                            {{ subject.learning_area || 'Universal Area' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        as-child
                        class="font-pulsar h-10 border-slate-200"
                    >
                        <Link :href="`/curriculum/subjects/${subject.id}/edit`"
                            ><Edit class="mr-2 h-4 w-4" />Modify
                            Configuration</Link
                        >
                    </Button>
                    <Button
                        class="font-pulsar h-10 border-0 bg-violet-600 shadow-lg hover:bg-violet-700"
                        as-child
                    >
                        <Link
                            :href="`/curriculum/subjects/${subject.id}/allocate`"
                            >Grade Allocation</Link
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
                        <BookOpen
                            class="h-5 w-5 text-violet-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Instructional Nodes
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ subject.strands.length }} Strands
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-emerald-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-emerald-500/10 p-3 transition-all group-hover:bg-emerald-500 group-hover:text-white"
                    >
                        <BadgeCheck
                            class="h-5 w-5 text-emerald-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Operational Pulse
                        </p>
                        <p class="text-xl font-bold text-emerald-600">
                            {{ subject.is_active ? 'ACTIVE' : 'DORMANT' }}
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-blue-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-blue-500/10 p-3 transition-all group-hover:bg-blue-500 group-hover:text-white"
                    >
                        <Layers
                            class="h-5 w-5 text-blue-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Subject Strategy
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-slate-900 uppercase"
                        >
                            {{ subject.subject_type }}
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
                            Logic Consistency
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-indigo-600 uppercase"
                        >
                            SECURED
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subject Details & Strands Matrix -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Knowledge Graph Details -->
                <div class="flex flex-col gap-6 lg:col-span-4">
                    <div
                        class="space-y-6 rounded-xl border bg-card p-8 shadow-sm"
                    >
                        <h2
                            class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Instructional Model
                        </h2>
                        <div
                            class="rounded-2xl border bg-slate-50 p-6 shadow-inner"
                        >
                            <div class="mb-1 text-lg font-bold text-indigo-600">
                                {{
                                    subject.department?.name ||
                                    'Unassigned Department'
                                }}
                            </div>
                            <div
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                {{
                                    subject.department?.code ||
                                    'FACULTY_NODE_MISSING'
                                }}
                                <span
                                    v-if="
                                        subject.department?.head_of_department
                                    "
                                    class="ml-2 border-l pl-2 text-indigo-600 text-slate-900"
                                    >HOD:
                                    {{
                                        subject.department.head_of_department
                                    }}</span
                                >
                            </div>
                        </div>
                        <div class="space-y-4 pt-2">
                            <p
                                class="text-sm leading-relaxed font-medium text-muted-foreground opacity-80"
                            >
                                {{
                                    subject.description ||
                                    'Core academic subject mapping instructional delivery standards to specific grade-level learning milestones.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="group relative overflow-hidden rounded-xl border-[12px] border-slate-900 bg-slate-950 p-10 text-white shadow-lg"
                    >
                        <div
                            class="absolute -right-8 -bottom-8 transform opacity-10 transition-all duration-700 group-hover:scale-110 group-hover:-rotate-12"
                        >
                            <Network class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-6">
                            <h3
                                class="flex items-center gap-3 text-xl font-bold tracking-tighter uppercase"
                            >
                                <BadgeCheck class="h-6 w-6 text-violet-400" />
                                Matrix Specs
                            </h3>
                            <div class="grid gap-4">
                                <div
                                    class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner"
                                >
                                    <span
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Examinable</span
                                    >
                                    <Badge
                                        :class="
                                            subject.is_examinable
                                                ? 'border-emerald-500/30 bg-emerald-500/20 text-emerald-400'
                                                : 'border-slate-500/30 bg-slate-500/20 text-slate-400'
                                        "
                                        class="border text-xs font-medium tracking-tight uppercase"
                                    >
                                        {{
                                            subject.is_examinable
                                                ? 'VERIFIED'
                                                : 'CORE_ONLY'
                                        }}
                                    </Badge>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner"
                                >
                                    <span
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Sequence Order</span
                                    >
                                    <span class="text-xl font-bold text-white"
                                        >#{{ subject.display_order }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner"
                                >
                                    <span
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >System Hash</span
                                    >
                                    <span
                                        class="text-xs font-bold text-slate-500"
                                        >NODE_{{ subject.id }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Strands & Topics Matrix -->
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <div
                        class="shrink-0 overflow-hidden rounded-3xl border border-t-4 border-t-violet-500 bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b bg-slate-50 px-8 py-5"
                        >
                            <h2
                                class="flex items-center gap-3 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                <Layers class="h-4 w-4 text-violet-600" /> Topic
                                Strands Matrix
                            </h2>
                            <Badge
                                variant="outline"
                                class="border-slate-200 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >{{ subject.strands.length }} Total</Badge
                            >
                        </div>

                        <div class="divide-y">
                            <div
                                v-if="subject.strands.length === 0"
                                class="p-16 text-center"
                            >
                                <div
                                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-slate-100 bg-slate-50"
                                >
                                    <Layers class="h-8 w-8 text-slate-300" />
                                </div>
                                <h3
                                    class="mb-1 text-sm font-bold text-slate-900 uppercase"
                                >
                                    No instructional strands found
                                </h3>
                                <p class="text-xs text-slate-400">
                                    Initialize topic strands to begin
                                    instructional mapping.
                                </p>
                                <Button
                                    variant="outline"
                                    class="font-pulsar mt-6 h-9 border-slate-200 text-xs tracking-tight uppercase"
                                    as-child
                                >
                                    <Link
                                        :href="`/curriculum/strands/create?subject_id=${subject.id}`"
                                        >Initialize first strand</Link
                                    >
                                </Button>
                            </div>

                            <div
                                v-for="strand in subject.strands"
                                :key="strand.id"
                                class="group flex items-center justify-between border-l-4 border-l-transparent px-8 py-6 transition-all duration-300 hover:border-l-violet-500 hover:bg-slate-50/70"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-sm font-bold text-slate-400 uppercase shadow-inner transition-all group-hover:bg-violet-600 group-hover:text-white"
                                    >
                                        {{ strand.code.substring(0, 2) }}
                                    </div>
                                    <div>
                                        <div
                                            class="mb-1.5 text-base leading-none font-bold tracking-tight text-slate-900 transition-colors group-hover:text-violet-700"
                                        >
                                            {{ strand.name }}
                                        </div>
                                        <div
                                            class="text-xs font-bold tracking-[0.1em] text-slate-400 uppercase"
                                        >
                                            {{ strand.code }} • Topic
                                            Intelligence
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <Badge
                                        :class="
                                            strand.is_active
                                                ? 'bg-emerald-500 text-white shadow-sm'
                                                : 'bg-slate-300 text-white'
                                        "
                                        class="rounded-full border-0 px-2.5 py-0.5 text-xs font-medium tracking-tight uppercase"
                                    >
                                        {{
                                            strand.is_active
                                                ? 'Active'
                                                : 'Dormant'
                                        }}
                                    </Badge>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 text-slate-300 group-hover:bg-white group-hover:text-violet-600 group-hover:shadow-sm"
                                        as-child
                                    >
                                        <Link
                                            :href="`/curriculum/strands/${strand.id}`"
                                            ><ChevronRight class="h-5 w-5"
                                        /></Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Sync -->
            <div
                class="group mt-2 flex flex-col items-center justify-between gap-6 rounded-3xl border border-slate-800 bg-slate-900 p-5 text-white shadow-lg transition-all hover:bg-slate-950 md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 transition-all group-hover:bg-violet-600 group-hover:text-white"
                    >
                        <Network
                            class="h-5 w-5 text-violet-400 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-white/90 uppercase"
                        >
                            Academic Node Integrity: SECURE
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
                        >Status: Optimized</Button
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
