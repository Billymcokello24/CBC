<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookCopy,
    ChevronRight,
    Edit,
    GraduationCap,
    School,
    Users,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grade: {
        id: number;
        name: string;
        code: string;
        category: string;
        level_order: number;
        minimum_age: number | null;
        maximum_age: number | null;
        is_active: boolean;
        lead_name: string | null;
    };
    subjects: Array<{
        id: number;
        name: string;
        code: string;
        subject_type: string;
        learning_area: string | null;
        lessons_per_week: number;
        minutes_per_lesson: number;
        is_compulsory: boolean;
        is_active: boolean;
        subject_is_active: boolean;
    }>;
    classes: Array<{
        id: number;
        name: string;
        code: string;
        stream: string | null;
        academic_year: string | null;
        teacher: string | null;
        students_count: number;
        capacity: number | null;
    }>;
    stats: {
        students_count: number;
        active_students_count: number;
        subjects_count: number;
        compulsory_subjects_count: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
    { title: props.grade.name, href: `/grades/${props.grade.id}` },
];
</script>

<template>
    <Head :title="grade.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mt-2 flex h-full w-full flex-1 animate-in flex-col gap-8 p-8 duration-500 fade-in"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col gap-6 border-b border-slate-100 pb-10 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg transition-transform duration-300 hover:scale-105"
                    >
                        <GraduationCap class="h-8 w-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-4">
                            <h1
                                class="text-4xl font-bold tracking-tight text-slate-900"
                            >
                                {{ grade.name }}
                            </h1>
                            <Badge
                                variant="secondary"
                                class="rounded-lg border-none bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600"
                                >{{ grade.code }}</Badge
                            >
                        </div>
                        <p
                            class="mt-2 flex items-center gap-2 text-sm font-medium text-slate-500"
                        >
                            <School class="h-4 w-4 text-blue-500" />
                            {{ grade.category }} Section • Priority Level:
                            {{ grade.level_order }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 rounded-xl border-slate-200 px-6 text-slate-500 hover:text-slate-700"
                    >
                        <Link href="/grades">Back to List</Link>
                    </Button>
                    <Button
                        as-child
                        class="h-10 rounded-xl bg-slate-900 px-8 text-white shadow-sm transition-all hover:bg-black"
                    >
                        <Link :href="`/grades/${grade.id}/edit`"
                            ><Edit class="mr-2 h-4 w-4" />Edit Grade</Link
                        >
                    </Button>
                </div>
            </div>

            <!-- Overview Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <!-- Grade Lead Card -->
                <div
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Grade Lead
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600"
                        >
                            {{ (grade.lead_name || 'NA').substring(0, 2) }}
                        </div>
                        <p class="truncate text-xs font-bold text-slate-700">
                            {{ grade.lead_name || 'Not assigned' }}
                        </p>
                    </div>
                </div>
                <!-- Min Age Card -->
                <div
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Min Age
                    </p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ grade.minimum_age ?? '—' }}
                        <span class="ml-1 text-xs font-medium text-slate-400"
                            >Years</span
                        >
                    </p>
                </div>
                <!-- Max Age Card -->
                <div
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Max Age
                    </p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ grade.maximum_age ?? '—' }}
                        <span class="ml-1 text-xs font-medium text-slate-400"
                            >Years</span
                        >
                    </p>
                </div>
                <!-- Classes Card -->
                <div
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Classes
                    </p>
                    <p class="text-2xl font-bold text-emerald-600">
                        {{ classes.length }}
                        <span class="ml-1 text-xs font-medium text-slate-400"
                            >Units</span
                        >
                    </p>
                </div>
                <!-- Population Card -->
                <div
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Learners
                    </p>
                    <p class="text-2xl font-bold text-violet-600">
                        {{ stats.students_count }}
                        <span class="ml-1 text-xs font-medium text-slate-400"
                            >Total</span
                        >
                    </p>
                </div>
                <!-- Status Card -->
                <div
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <p
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Status
                    </p>
                    <div class="flex items-center gap-2">
                        <div
                            class="h-2 w-2 rounded-full"
                            :class="
                                grade.is_active
                                    ? 'animate-pulse bg-green-500 shadow-sm'
                                    : 'bg-slate-300'
                            "
                        ></div>
                        <span
                            class="text-xs font-bold"
                            :class="
                                grade.is_active
                                    ? 'text-green-600'
                                    : 'text-slate-400'
                            "
                            >{{ grade.is_active ? 'Active' : 'Locked' }}</span
                        >
                    </div>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Left Section - Classes & Subjects (2 cols) -->
                <div class="space-y-8 lg:col-span-2">
                    <!-- Classes List -->
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                >
                                    <School class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2
                                        class="text-lg leading-none font-bold text-slate-900"
                                    >
                                        Classes
                                    </h2>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Active class units in this grade level
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-slate-200 px-4 text-xs font-bold"
                                as-child
                            >
                                <Link href="/classes">More Classes</Link>
                            </Button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <Link
                                v-for="classroom in classes"
                                :key="classroom.id"
                                :href="`/classes/${classroom.id}`"
                                class="group flex flex-col rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:border-blue-200 hover:bg-white hover:shadow-md"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3
                                            class="font-bold text-slate-900 transition-colors group-hover:text-blue-600"
                                        >
                                            {{ classroom.name }}
                                        </h3>
                                        <div
                                            class="mt-1.5 flex items-center gap-2"
                                        >
                                            <Badge
                                                variant="secondary"
                                                class="rounded-lg border-none bg-white px-2 text-xs font-bold text-slate-500"
                                                >{{ classroom.code }}</Badge
                                            >
                                            <Badge
                                                v-if="classroom.stream"
                                                variant="secondary"
                                                class="rounded-lg border-none bg-blue-50 px-2 text-xs font-bold text-blue-600"
                                                >{{ classroom.stream }}</Badge
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-slate-400 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        <ChevronRight class="h-4 w-4" />
                                    </div>
                                </div>
                                <div
                                    class="mt-4 border-t border-slate-200/50 pt-4"
                                >
                                    <p
                                        class="flex items-center gap-2 text-xs font-medium text-slate-400"
                                    >
                                        <Users class="h-3 w-3" />
                                        Learner Count:
                                        <span class="font-bold text-slate-900"
                                            >{{ classroom.students_count }} /
                                            {{
                                                classroom.capacity ?? '∞'
                                            }}</span
                                        >
                                    </p>
                                    <p
                                        class="mt-1 flex items-center gap-2 text-xs font-medium text-slate-400"
                                    >
                                        <GraduationCap class="h-3 w-3" />
                                        Teacher:
                                        <span
                                            class="font-bold text-slate-900"
                                            >{{
                                                classroom.teacher ||
                                                'Not assigned'
                                            }}</span
                                        >
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Subjects List -->
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <div
                            class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600"
                                >
                                    <BookCopy class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2
                                        class="text-lg leading-none font-bold text-slate-900"
                                    >
                                        Subjects Taught
                                    </h2>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Learning areas assigned to this grade
                                        level
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 rounded-xl border-slate-200 px-4 text-xs font-bold"
                                as-child
                            >
                                <Link :href="`/grades/${grade.id}/subjects`"
                                    >Manage Subjects</Link
                                >
                            </Button>
                        </div>

                        <div
                            v-if="subjects.length === 0"
                            class="rounded-2xl border-2 border-dashed border-slate-100 bg-slate-50/30 p-8 text-center"
                        >
                            <p class="text-sm font-medium text-slate-400">
                                No subjects added to this grade yet.
                            </p>
                        </div>
                        <div v-else class="grid gap-4 md:grid-cols-2">
                            <Link
                                v-for="subject in subjects"
                                :key="subject.id"
                                :href="`/grades/${grade.id}/subjects`"
                                class="group relative rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:border-green-200 hover:bg-white hover:shadow-md"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3
                                            class="font-bold text-slate-900 transition-colors group-hover:text-green-600"
                                        >
                                            {{ subject.name }}
                                        </h3>
                                        <div
                                            class="mt-1.5 flex items-center gap-2"
                                        >
                                            <Badge
                                                variant="secondary"
                                                class="rounded-lg border-none bg-white px-2 text-xs font-bold text-slate-500"
                                                >{{ subject.code }}</Badge
                                            >
                                            <Badge
                                                variant="outline"
                                                :class="[
                                                    'rounded-lg px-2 text-xs font-bold',
                                                    subject.is_compulsory
                                                        ? 'border-green-100 bg-green-50/30 text-green-600'
                                                        : 'border-slate-200 text-slate-400',
                                                ]"
                                                >{{
                                                    subject.is_compulsory
                                                        ? 'Compulsory'
                                                        : 'Optional'
                                                }}</Badge
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="rounded border border-slate-100 bg-white px-2 py-1 text-xs font-bold text-slate-400 shadow-sm transition-colors group-hover:text-green-600"
                                    >
                                        {{ subject.subject_type }}
                                    </div>
                                </div>
                                <div
                                    class="mt-4 flex items-center justify-between text-xs font-medium text-slate-500"
                                >
                                    <span
                                        >Lessons:
                                        <span class="font-bold text-slate-900"
                                            >{{ subject.lessons_per_week }} /
                                            week</span
                                        ></span
                                    >
                                    <span
                                        >Duration:
                                        <span class="font-bold text-slate-900"
                                            >{{
                                                subject.minutes_per_lesson
                                            }}
                                            min</span
                                        ></span
                                    >
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Right Section - Manage Learners (1 col) -->
                <div class="space-y-8">
                    <div
                        class="group relative overflow-hidden rounded-2xl border bg-slate-900 p-8 shadow-xl"
                    >
                        <!-- Decorative glow -->
                        <div
                            class="pointer-events-none absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-blue-600 opacity-20 blur-[80px] transition-opacity group-hover:opacity-40"
                        ></div>

                        <div class="relative z-10 flex flex-col gap-6">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg"
                            >
                                <Users class="h-6 w-6" />
                            </div>
                            <div>
                                <h2
                                    class="text-xl leading-none font-bold text-white"
                                >
                                    Manage Learners
                                </h2>
                                <p
                                    class="mt-3 text-xs leading-relaxed text-slate-400"
                                >
                                    View population details, handle new
                                    enrollments, and manage learner records for
                                    this specific grade level.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pb-2">
                                <div
                                    class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur-sm"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold tracking-wider text-blue-400 uppercase"
                                    >
                                        Total
                                    </p>
                                    <p class="text-xl font-bold text-white">
                                        {{ stats.students_count }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur-sm"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold tracking-wider text-green-400 uppercase"
                                    >
                                        Active
                                    </p>
                                    <p class="text-xl font-bold text-white">
                                        {{ stats.active_students_count }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <Button
                                    as-child
                                    class="h-11 w-full rounded-xl border-0 bg-blue-600 text-xs font-bold text-white shadow-lg shadow-blue-900/40 hover:bg-blue-700"
                                >
                                    <Link :href="`/grades/${grade.id}/students`"
                                        >Open Learner List</Link
                                    >
                                </Button>
                                <Button
                                    variant="outline"
                                    as-child
                                    class="h-11 w-full rounded-xl border-white/20 bg-transparent text-xs font-bold text-white hover:bg-white/10"
                                >
                                    <Link href="/students/create"
                                        >Register New Learner</Link
                                    >
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info / Help Card -->
                    <div
                        class="rounded-2xl border border-blue-50 bg-blue-50/30 p-6"
                    >
                        <h3
                            class="mb-2 flex items-center gap-2 text-sm font-bold text-blue-900"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            Grade Overview
                        </h3>
                        <p class="text-xs leading-relaxed text-blue-700/70">
                            This grade level represents stage
                            {{ grade.level_order }} of the school curriculum.
                            Use this page to monitor capacity and learning
                            areas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
