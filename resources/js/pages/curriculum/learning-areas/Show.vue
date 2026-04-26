<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Shapes,
    BookOpen,
    ArrowLeft,
    MoreVertical,
    ExternalLink,
    GraduationCap,
    CheckCircle2,
    AlertCircle,
    Info,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learningArea: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
    { title: props.learningArea.name, href: '#' },
];
</script>

<template>
    <Head :title="learningArea.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-8 font-sans duration-500 fade-in"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="space-y-4">
                    <Link
                        href="/curriculum/learning-areas"
                        class="inline-flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase transition-colors hover:text-blue-600"
                    >
                        <ArrowLeft class="h-3 w-3" /> Back to Areas
                    </Link>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600"
                            >
                                <Shapes class="h-5 w-5" />
                            </div>
                            <h1
                                class="text-3xl font-bold tracking-tight text-slate-900 uppercase"
                            >
                                {{ learningArea.name }}
                            </h1>
                        </div>
                        <p class="max-w-2xl text-sm font-medium text-slate-500">
                            {{
                                learningArea.description ||
                                'No detailed description available for this structural domain.'
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pb-2">
                    <Badge
                        variant="outline"
                        class="h-10 rounded-xl border-slate-100 bg-white px-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                    >
                        {{ learningArea.code }}
                    </Badge>
                    <Badge
                        class="h-10 rounded-xl bg-blue-600 px-4 text-xs font-bold tracking-wider text-white uppercase shadow-sm"
                    >
                        {{ learningArea.category || 'Core Area' }}
                    </Badge>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Subjects Sidebar/Info -->
                <div class="space-y-8 lg:col-span-1">
                    <div
                        class="space-y-6 rounded-xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <h4
                            class="flex items-center gap-2 text-xs font-bold tracking-tight text-slate-900 uppercase"
                        >
                            <Info class="h-4 w-4 text-blue-500" /> Area
                            Statistics
                        </h4>

                        <div class="grid gap-4">
                            <div
                                class="group flex cursor-default items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:border-blue-100 hover:bg-white"
                            >
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase transition-colors group-hover:text-slate-600"
                                    >Total Subjects</span
                                >
                                <span
                                    class="text-xl font-bold text-slate-900"
                                    >{{
                                        learningArea.subjects?.length || 0
                                    }}</span
                                >
                            </div>
                            <div
                                class="group flex cursor-default items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:border-blue-100 hover:bg-white"
                            >
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase transition-colors group-hover:text-slate-600"
                                    >Integration Status</span
                                >
                                <Badge
                                    variant="outline"
                                    class="border-emerald-100 bg-emerald-50 px-3 text-xs font-bold tracking-tight text-emerald-600 uppercase"
                                    >Active</Badge
                                >
                            </div>
                        </div>

                        <div class="pt-4">
                            <div
                                class="space-y-2 rounded-2xl border border-dashed border-blue-100 bg-blue-50/50 p-6"
                            >
                                <p
                                    class="flex items-center gap-2 text-xs font-bold tracking-tight text-blue-600 uppercase"
                                >
                                    <GraduationCap class="h-3.5 w-3.5" /> CBC
                                    Alignment
                                </p>
                                <p
                                    class="text-sm leading-relaxed font-medium text-blue-700/70"
                                >
                                    This learning area is mapped to the standard
                                    KICD curriculum framework for this academic
                                    year.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects List -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2
                            class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-900 lowercase"
                        >
                            associated
                            <span class="not- font-bold uppercase"
                                >subjects</span
                            >
                        </h2>
                        <span
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >{{
                                learningArea.subjects?.length || 0
                            }}
                            Identified</span
                        >
                    </div>

                    <div class="grid gap-4">
                        <div
                            v-for="subject in learningArea.subjects"
                            :key="subject.id"
                            class="group flex items-center justify-between rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md"
                        >
                            <div class="flex items-center gap-6">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-slate-400 transition-all group-hover:border-blue-100 group-hover:bg-blue-50 group-hover:text-blue-600"
                                >
                                    <BookOpen class="h-6 w-6" />
                                </div>
                                <div>
                                    <div class="mb-0.5 flex items-center gap-2">
                                        <h3
                                            class="text-lg font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                        >
                                            {{ subject.name }}
                                        </h3>
                                        <Badge
                                            variant="outline"
                                            class="bg-slate-100 text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >{{ subject.code }}</Badge
                                        >
                                    </div>
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        Mapped to Grade Level: All Relevant
                                        Grades
                                    </p>
                                </div>
                            </div>

                            <Link
                                :href="`/curriculum/syllabus/${subject.id}/1`"
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-400 shadow-sm transition-all group-hover:border-slate-900 group-hover:bg-slate-900 group-hover:text-white"
                            >
                                <ExternalLink class="h-4 w-4" />
                            </Link>
                        </div>

                        <!-- Empty Subjects -->
                        <div
                            v-if="!learningArea.subjects?.length"
                            class="rounded-xl border-2 border-dashed border-slate-100 bg-slate-50 py-16 text-center"
                        >
                            <AlertCircle
                                class="mx-auto mb-4 h-10 w-10 text-slate-200"
                            />
                            <h4
                                class="text-lg font-bold tracking-tight text-slate-400 uppercase"
                            >
                                No Subjects Linked
                            </h4>
                            <p
                                class="text-xs font-bold tracking-tight text-slate-300 uppercase"
                            >
                                Allocate subjects to this area in the curriculum
                                settings.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
