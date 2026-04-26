<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    Save,
    Clock,
    BookOpen,
    GraduationCap,
    ArrowRight,
    Settings2,
    Home,
    ChevronRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    settings: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'Academic Year', href: '/settings/academic-year' },
];
</script>

<template>
    <Head title="Academic Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <div class="flex flex-col gap-4 border-b border-sidebar-border pb-8 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Settings</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Academic Year</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Academic Year
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Configure terms, session dates and global academic behavior
                    </p>
                </div>
                <Button class="font-pulsar bg-blue-600 hover:bg-blue-700"
                    ><Save class="mr-2 h-4 w-4" />Save All</Button
                >
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-6">
                    <div
                        class="overflow-hidden rounded-2xl border border-l-4 border-l-blue-500 bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b bg-slate-50 px-6 py-4"
                        >
                            <h3
                                class="flex items-center gap-2 font-bold text-blue-800"
                            >
                                <Clock class="h-4 w-4" /> Current Term Session
                            </h3>
                            <Badge
                                class="bg-blue-600 text-xs font-medium tracking-tight uppercase"
                                >ACTIVE</Badge
                            >
                        </div>
                        <div class="space-y-4 p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label
                                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Start Date</Label
                                    >
                                    <Input type="date" value="2026-01-05" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label
                                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >End Date</Label
                                    >
                                    <Input type="date" value="2026-04-10" />
                                </div>
                            </div>
                            <div class="pt-2">
                                <p class="text-xs font-medium text-slate-400">
                                    * These dates define automatic attendance
                                    and report card generation cycles.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border bg-card shadow-sm"
                    >
                        <div class="border-b bg-slate-50 px-6 py-4">
                            <h3 class="flex items-center gap-2 font-bold">
                                <BookOpen class="h-4 w-4 text-slate-400" />
                                Subjects & Grading
                            </h3>
                        </div>
                        <div class="space-y-6 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">
                                        Auto-Calculate Mean Scores
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Sum subject scores for final report
                                        prints
                                    </p>
                                </div>
                                <div
                                    class="relative h-6 w-12 cursor-pointer rounded-full border border-blue-200 bg-blue-100"
                                >
                                    <div
                                        class="absolute top-1 right-1 h-4 w-4 rounded-full bg-blue-600 shadow-sm"
                                    ></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">
                                        Show Grade Percentages
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Display numerical values alongside
                                        rubric levels
                                    </p>
                                </div>
                                <div
                                    class="relative h-6 w-12 cursor-pointer rounded-full border border-slate-200 bg-slate-100"
                                >
                                    <div
                                        class="absolute top-1 left-1 h-4 w-4 rounded-full border border-slate-300 bg-white shadow-sm"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="relative flex flex-col justify-between overflow-hidden rounded-2xl border bg-slate-900 p-8 text-white shadow-xl"
                >
                    <div class="absolute -top-10 -right-10 opacity-10">
                        <Settings2 class="h-48 w-48 rotate-12 text-white" />
                    </div>
                    <div>
                        <div
                            class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/10"
                        >
                            <GraduationCap class="h-6 w-6 text-blue-400" />
                        </div>
                        <h2 class="mb-4 text-2xl font-bold">
                            Academic Master Data
                        </h2>
                        <p class="mb-8 text-sm leading-relaxed text-slate-400">
                            Configure standard grading formats (Competency
                            Levels vs Numerical), learning area clusters and
                            global subject availability for the 2026 academic
                            year.
                        </p>
                    </div>

                    <div class="font-pulsar grid gap-3">
                        <Button
                            class="w-full justify-between border-0 bg-blue-600 font-bold shadow-md transition-transform hover:scale-[1.01] hover:bg-blue-700"
                        >
                            Manage Grading Rubrics
                            <ArrowRight class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="w-full justify-between border-white/10 bg-white/5 font-bold text-white hover:bg-white/10"
                        >
                            Subject Master List <ArrowRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
