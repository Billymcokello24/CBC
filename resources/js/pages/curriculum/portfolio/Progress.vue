<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    GraduationCap,
    BookOpen,
    CheckCircle2,
    ArrowLeft,
    Search,
    Filter,
    Calendar,
    User,
    Award,
    Zap,
    Activity,
    Info,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: any;
    achievements: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Student Progress', href: '#' },
];

const getLevelColor = (level: string) => {
    switch (level) {
        case 'EE':
            return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        case 'ME':
            return 'text-blue-600 bg-blue-50 border-blue-100';
        case 'AE':
            return 'text-amber-600 bg-amber-50 border-amber-100';
        case 'BE':
            return 'text-red-600 bg-red-50 border-red-100';
        default:
            return 'text-slate-600 bg-slate-50 border-slate-100';
    }
};

const getLevelName = (level: string) => {
    switch (level) {
        case 'EE':
            return 'Excellent';
        case 'ME':
            return 'Good';
        case 'AE':
            return 'Fair';
        case 'BE':
            return 'Needs Work';
        default:
            return level;
    }
};
</script>

<template>
    <Head :title="`${student.name} - Progress`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-8 font-sans duration-500 fade-in"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm"
                        >
                            <GraduationCap class="h-5 w-5" />
                        </div>
                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-900"
                        >
                            {{ student.name }}
                        </h1>
                    </div>
                    <p
                        class="ml-14 text-sm font-medium tracking-tight text-slate-500"
                    >
                        View student performance and learning progress.
                    </p>
                </div>
            </div>

            <!-- Dashboard -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Achievements -->
                <div class="space-y-8 lg:col-span-2">
                    <div
                        class="min-h-[500px] overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/20 p-6"
                        >
                            <h3
                                class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Learning Goals
                            </h3>
                            <Badge
                                variant="outline"
                                class="border-blue-100 bg-white px-3 py-1 text-xs font-bold tracking-wider text-blue-600 uppercase"
                                >{{ achievements.length }} Goals Met</Badge
                            >
                        </div>

                        <div v-if="achievements.length" class="space-y-4 p-6">
                            <div
                                v-for="achievement in achievements"
                                :key="achievement.id"
                                class="group rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:border-blue-200 hover:bg-white hover:shadow-sm"
                            >
                                <div
                                    class="mb-3 flex items-start justify-between"
                                >
                                    <div class="flex gap-4">
                                        <div
                                            :class="[
                                                'flex h-10 w-10 items-center justify-center rounded-xl border text-sm font-bold transition-all',
                                                getLevelColor(
                                                    achievement.achievement_level,
                                                ),
                                            ]"
                                        >
                                            {{ achievement.achievement_level }}
                                        </div>
                                        <div class="space-y-1">
                                            <h4
                                                class="text-base font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                                            >
                                                {{
                                                    achievement.learning_outcome
                                                        ?.outcome
                                                }}
                                            </h4>
                                            <p
                                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                Checked on
                                                {{
                                                    achievement.assessment_date
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="border-slate-100 bg-white text-xs font-bold tracking-wider uppercase"
                                        >{{
                                            getLevelName(
                                                achievement.achievement_level,
                                            )
                                        }}</Badge
                                    >
                                </div>

                                <div
                                    v-if="achievement.remarks"
                                    class="pl-14 text-xs text-slate-500"
                                >
                                    "{{ achievement.remarks }}"
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-32 text-center">
                            <Award
                                class="mx-auto mb-4 h-12 w-12 text-slate-200"
                            />
                            <h3
                                class="mb-2 text-xl font-bold tracking-tight text-slate-400 uppercase"
                            >
                                No progress yet
                            </h3>
                            <p class="text-sm text-slate-400">
                                Progress will appear here once assessments are
                                done.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="space-y-8">
                    <div
                        class="group relative overflow-hidden rounded-3xl bg-slate-900 p-8 text-white shadow-xl"
                    >
                        <div
                            class="absolute -top-10 -right-10 opacity-10 transition-transform duration-700 group-hover:scale-110"
                        >
                            <Activity class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-8">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="flex items-center gap-3 text-lg font-bold tracking-tight uppercase"
                                >
                                    <Info class="h-5 w-5 text-blue-400" />
                                    Completion
                                </h3>
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 font-bold text-blue-400 shadow-sm"
                                >
                                    {{
                                        Math.round(
                                            (achievements.length / 25) * 100,
                                        )
                                    }}%
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div
                                    v-for="(count, level) in {
                                        EE: achievements.filter(
                                            (a) => a.achievement_level === 'EE',
                                        ).length,
                                        ME: achievements.filter(
                                            (a) => a.achievement_level === 'ME',
                                        ).length,
                                        AE: achievements.filter(
                                            (a) => a.achievement_level === 'AE',
                                        ).length,
                                        BE: achievements.filter(
                                            (a) => a.achievement_level === 'BE',
                                        ).length,
                                    }"
                                    :key="level"
                                    class="space-y-2"
                                >
                                    <div
                                        class="flex items-center justify-between text-xs font-bold tracking-wider uppercase"
                                    >
                                        <span class="text-slate-400">{{
                                            getLevelName(level)
                                        }}</span>
                                        <span class="text-white">{{
                                            count
                                        }}</span>
                                    </div>
                                    <div
                                        class="h-1.5 w-full rounded-full bg-white/5"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-700"
                                            :class="
                                                getLevelColor(level)
                                                    .split(' ')[0]
                                                    .replace('text-', 'bg-')
                                            "
                                            :style="{
                                                width: `${(count / Math.max(1, achievements.length)) * 100}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <p
                                class="border-t border-white/5 pt-6 text-xs leading-relaxed font-medium text-slate-400"
                            >
                                Progress is calculated based on completed
                                learning goals and syllabus updates.
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
