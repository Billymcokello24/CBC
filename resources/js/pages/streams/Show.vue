<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Rows3, School } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stream: {
        id: number;
        name: string;
        code: string;
        capacity: number | null;
        is_active: boolean;
        lead_name: string | null;
    };
    classes: Array<{
        id: number;
        name: string;
        code: string;
        grade: string | null;
        academic_year: string | null;
        teacher: string | null;
        students_count: number;
        capacity: number | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Streams', href: '/streams' },
    { title: props.stream.name, href: `/streams/${props.stream.id}` },
];
</script>

<template>
    <Head :title="stream.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="w-full animate-in space-y-12 px-4 py-12 duration-500 fade-in sm:px-6 lg:px-8"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg"
                    >
                        <Rows3 class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                {{ stream.name }}
                            </h1>
                            <Badge
                                variant="secondary"
                                class="h-5 rounded-full border-none bg-blue-50 px-3 py-0.5 text-xs font-bold text-blue-600 uppercase"
                                >{{ stream.code }}</Badge
                            >
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Management overview for academic stream.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-slate-200 px-6 text-xs font-bold tracking-wider uppercase shadow-sm transition-all hover:bg-slate-50"
                        as-child
                        ><Link :href="`/streams/${stream.id}/edit`"
                            >Stream Settings</Link
                        ></Button
                    >
                    <Button
                        variant="ghost"
                        class="h-11 rounded-xl px-4 text-xs font-bold tracking-wider text-slate-500 uppercase hover:text-slate-700"
                        as-child
                        ><Link href="/streams">Back to Streams</Link></Button
                    >
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Stream Head
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold text-blue-600"
                        >
                            {{ stream.lead_name?.charAt(0) || '?' }}
                        </div>
                        <p class="font-bold text-slate-900">
                            {{ stream.lead_name || 'Not assigned' }}
                        </p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Class Capacity
                    </p>
                    <p class="text-3xl font-bold text-slate-900">
                        {{ stream.capacity ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Total Classes
                    </p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ classes.length }}
                    </p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Stream Status
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <div
                            class="h-2 w-2 rounded-full"
                            :class="
                                stream.is_active
                                    ? 'animate-pulse bg-emerald-500'
                                    : 'bg-slate-300'
                            "
                        ></div>
                        <span
                            class="text-sm font-bold"
                            :class="
                                stream.is_active
                                    ? 'text-emerald-600'
                                    : 'text-slate-400'
                            "
                            >{{
                                stream.is_active ? 'Active' : 'Inactive'
                            }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Classes Section -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        Classes in this Stream
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Open any class to manage its learners and operations.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="classroom in classes"
                        :key="classroom.id"
                        :href="`/classes/${classroom.id}`"
                        class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-all hover:border-blue-100 hover:shadow-lg"
                    >
                        <div
                            class="mb-6 flex items-start justify-between gap-4"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                >
                                    {{ classroom.name }}
                                </h3>
                                <p
                                    class="mt-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{ classroom.code
                                    }}<span v-if="classroom.grade" class="ml-2"
                                        >• {{ classroom.grade }}</span
                                    >
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-blue-50 p-2.5 text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white"
                            >
                                <School class="h-5 w-5" />
                            </div>
                        </div>

                        <div
                            class="mb-6 flex items-center gap-3 rounded-xl bg-slate-50 p-3"
                        >
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-100 bg-white text-xs font-bold text-slate-600 uppercase"
                            >
                                {{ classroom.teacher?.charAt(0) || '?' }}
                            </div>
                            <p
                                class="truncate text-xs font-bold text-slate-600 uppercase"
                            >
                                Teacher:
                                {{ classroom.teacher || 'Not assigned' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm"
                            >
                                <p
                                    class="mb-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Learners
                                </p>
                                <p class="text-base font-bold text-blue-600">
                                    {{ classroom.students_count }}
                                </p>
                            </div>
                            <div
                                class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm"
                            >
                                <p
                                    class="mb-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Capacity
                                </p>
                                <p class="text-base font-bold text-slate-900">
                                    {{ classroom.capacity ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
