<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    ArrowLeft,
    Edit,
    GraduationCap,
    Layers,
    CheckCircle2,
    ShieldCheck,
    ChevronRight,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learningArea: {
        id: number;
        name: string;
        code: string;
        description: string | null;
        category: string | null;
        display_order: number;
        is_active: boolean;
        subjects: Array<{
            id: number;
            name: string;
            code: string;
            subject_type: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
    {
        title: props.learningArea.name,
        href: `/curriculum/learning-areas/${props.learningArea.id}`,
    },
];
</script>

<template>
    <Head :title="learningArea.name" />
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
                        <Link href="/curriculum/learning-areas"
                            ><ArrowLeft class="h-4 w-4"
                        /></Link>
                    </Button>
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10"
                    >
                        <BookOpenCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ learningArea.name }}
                        </h1>
                        <p
                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >
                            {{ learningArea.code }} •
                            {{ learningArea.category || 'General Domain' }}
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
                            :href="`/curriculum/learning-areas/${learningArea.id}/edit`"
                            ><Edit class="mr-2 h-4 w-4" />Modify
                            Configuration</Link
                        >
                    </Button>
                    <Button
                        class="font-pulsar h-10 border-0 bg-violet-600 shadow-lg hover:bg-violet-700"
                        as-child
                    >
                        <Link
                            :href="`/curriculum/subjects/create?area_id=${learningArea.id}`"
                            >Initialize Subject</Link
                        >
                    </Button>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                            Operational Status
                        </p>
                        <p
                            class="text-xl font-bold"
                            :class="
                                learningArea.is_active
                                    ? 'text-emerald-600'
                                    : 'text-slate-400'
                            "
                        >
                            {{
                                learningArea.is_active
                                    ? 'ACTIVE PULSE'
                                    : 'DORMANT'
                            }}
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-violet-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-violet-500/10 p-3 transition-all group-hover:bg-violet-500 group-hover:text-white"
                    >
                        <GraduationCap
                            class="h-5 w-5 text-violet-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Subject Inventory
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ learningArea.subjects.length }} Nodes
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
                            Logic Order
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            POSITION #{{ learningArea.display_order }}
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
                            Security Status
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-indigo-600 uppercase"
                        >
                            SECURED
                        </p>
                    </div>
                </div>
            </div>

            <!-- Details and Subjects -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Details -->
                <div class="flex flex-col gap-6 lg:col-span-4">
                    <div
                        class="space-y-6 rounded-xl border bg-card p-8 shadow-sm"
                    >
                        <h2
                            class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Domain Description
                        </h2>
                        <p
                            class="text-sm leading-relaxed font-medium text-muted-foreground"
                        >
                            {{
                                learningArea.description ||
                                'No descriptive metadata provided for this academic domain in the curriculum registry.'
                            }}
                        </p>
                    </div>

                    <div
                        class="space-y-6 rounded-xl border border-t-8 border-t-violet-500 bg-card p-8 shadow-sm"
                    >
                        <h2
                            class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Metadata Summary
                        </h2>
                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between border-b border-slate-50 py-2"
                            >
                                <span
                                    class="text-xs font-bold text-slate-400 uppercase"
                                    >System ID</span
                                >
                                <span class="text-xs font-bold text-slate-900"
                                    >#{{ learningArea.id }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-slate-50 py-2"
                            >
                                <span
                                    class="text-xs font-bold text-slate-400 uppercase"
                                    >Unique Code</span
                                >
                                <span
                                    class="text-xs font-bold text-slate-900"
                                    >{{ learningArea.code }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-slate-50 py-2"
                            >
                                <span
                                    class="text-xs font-bold text-slate-400 uppercase"
                                    >Academic Group</span
                                >
                                <span
                                    class="text-xs font-bold text-violet-600 uppercase"
                                    >{{
                                        learningArea.category || 'Standard'
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Table -->
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <div
                        class="overflow-hidden rounded-3xl border border-t-4 border-t-emerald-500 bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b bg-slate-50 px-6 py-4"
                        >
                            <h2
                                class="flex items-center gap-2 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Academic Subjects Under This Domain
                            </h2>
                            <Badge
                                variant="outline"
                                class="border-slate-200 text-xs font-bold text-slate-400 uppercase"
                                >{{ learningArea.subjects.length }} Total</Badge
                            >
                        </div>
                        <table class="w-full text-left">
                            <thead>
                                <tr class="font-pulsar border-b bg-white">
                                    <th
                                        class="px-6 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Subject Matrix
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Instructional Model
                                    </th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm">
                                <tr
                                    v-for="subject in learningArea.subjects"
                                    :key="subject.id"
                                    class="group transition-colors hover:bg-slate-50/70"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-600/10 text-xs font-bold text-emerald-700 uppercase transition-all group-hover:bg-emerald-600 group-hover:text-white"
                                            >
                                                {{
                                                    subject.code.substring(0, 2)
                                                }}
                                            </div>
                                            <div>
                                                <div
                                                    class="text-sm font-bold text-slate-900 transition-colors group-hover:text-emerald-700"
                                                >
                                                    {{ subject.name }}
                                                </div>
                                                <div
                                                    class="text-xs font-bold tracking-tighter text-slate-400 uppercase"
                                                >
                                                    {{ subject.code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge
                                            variant="outline"
                                            class="rounded-lg border-0 bg-slate-100 px-2 py-0.5 text-xs font-bold tracking-tighter text-slate-600 uppercase"
                                            >{{
                                                subject.subject_type
                                            }}
                                            Model</Badge
                                        >
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Badge
                                            :class="
                                                subject.is_active
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'bg-slate-300 text-white'
                                            "
                                            class="rounded-full border-0 px-2 py-0.5 text-xs font-medium tracking-tight uppercase"
                                        >
                                            {{
                                                subject.is_active
                                                    ? 'Active'
                                                    : 'Dormant'
                                            }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-slate-400 hover:text-emerald-600"
                                            as-child
                                        >
                                            <Link
                                                :href="`/curriculum/subjects/${subject.id}`"
                                                ><ChevronRight class="h-4 w-4"
                                            /></Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="learningArea.subjects.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center font-medium text-muted-foreground"
                                    >
                                        No instructional nodes established under
                                        this domain.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
