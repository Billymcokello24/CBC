<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookCopy, ChevronRight, Edit, GraduationCap, School, Users } from 'lucide-vue-next';
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
        <div class="flex h-full flex-1 flex-col gap-8 p-8 mt-2 w-full animate-in fade-in duration-500">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-10">
                <div class="flex items-center gap-6">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg transition-transform hover:scale-105 duration-300">
                        <GraduationCap class="h-8 w-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-4">
                            <h1 class="text-4xl font-bold tracking-tight text-slate-900">{{ grade.name }}</h1>
                            <Badge variant="secondary" class="rounded-lg px-3 py-1 text-xs font-bold bg-blue-50 text-blue-600 border-none">{{ grade.code }}</Badge>
                        </div>
                        <p class="text-slate-500 font-medium text-sm mt-2 flex items-center gap-2">
                            <School class="h-4 w-4 text-blue-500" />
                            {{ grade.category }} Section • Priority Level: {{ grade.level_order }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-10 text-slate-500 hover:text-slate-700 px-6 rounded-xl border-slate-200">
                        <Link href="/grades">Back to List</Link>
                    </Button>
                    <Button as-child class="bg-slate-900 hover:bg-black text-white shadow-sm h-10 px-8 rounded-xl transition-all">
                        <Link :href="`/grades/${grade.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Grade</Link>
                    </Button>
                </div>
            </div>

            <!-- Overview Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <!-- Grade Lead Card -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Grade Lead</p>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center font-bold text-blue-600 text-xs">
                            {{ (grade.lead_name || 'NA').substring(0,2) }}
                        </div>
                        <p class="text-xs font-bold text-slate-700 truncate">{{ grade.lead_name || 'Not assigned' }}</p>
                    </div>
                </div>
                <!-- Min Age Card -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Min Age</p>
                    <p class="text-2xl font-bold text-blue-600">{{ grade.minimum_age ?? '—' }} <span class="text-xs text-slate-400 font-medium ml-1">Years</span></p>
                </div>
                <!-- Max Age Card -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Max Age</p>
                    <p class="text-2xl font-bold text-blue-600">{{ grade.maximum_age ?? '—' }} <span class="text-xs text-slate-400 font-medium ml-1">Years</span></p>
                </div>
                <!-- Classes Card -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Classes</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ classes.length }} <span class="text-xs text-slate-400 font-medium ml-1">Units</span></p>
                </div>
                <!-- Population Card -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Learners</p>
                    <p class="text-2xl font-bold text-violet-600">{{ stats.students_count }} <span class="text-xs text-slate-400 font-medium ml-1">Total</span></p>
                </div>
                <!-- Status Card -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Status</p>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full" :class="grade.is_active ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)] animate-pulse' : 'bg-slate-300'"></div>
                        <span class="text-xs font-bold" :class="grade.is_active ? 'text-green-600' : 'text-slate-400'">{{ grade.is_active ? 'Active' : 'Locked' }}</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Left Section - Classes & Subjects (2 cols) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Classes List -->
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <School class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900 leading-none">Classes</h2>
                                    <p class="text-xs text-muted-foreground mt-1">Active class units in this grade level</p>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" class="rounded-xl px-4 h-9 text-xs font-bold border-slate-200" as-child>
                                <Link href="/classes">More Classes</Link>
                            </Button>
                        </div>
                        
                        <div class="grid gap-4 md:grid-cols-2">
                            <Link v-for="classroom in classes" :key="classroom.id" :href="`/classes/${classroom.id}`" class="group flex flex-col rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:bg-white hover:border-blue-200 hover:shadow-md">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ classroom.name }}</h3>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <Badge variant="secondary" class="rounded-lg px-2 text-[10px] font-bold bg-white text-slate-500 border-none">{{ classroom.code }}</Badge>
                                            <Badge v-if="classroom.stream" variant="secondary" class="rounded-lg px-2 text-[10px] font-bold bg-blue-50 text-blue-600 border-none">{{ classroom.stream }}</Badge>
                                        </div>
                                    </div>
                                    <div class="h-8 w-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                        <ChevronRight class="h-4 w-4" />
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-slate-200/50">
                                    <p class="text-[10px] font-medium text-slate-400 flex items-center gap-2">
                                        <Users class="h-3 w-3" />
                                        Learner Count: <span class="text-slate-900 font-bold">{{ classroom.students_count }} / {{ classroom.capacity ?? '∞' }}</span>
                                    </p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1 flex items-center gap-2">
                                        <GraduationCap class="h-3 w-3" />
                                        Teacher: <span class="text-slate-900 font-bold">{{ classroom.teacher || 'Not assigned' }}</span>
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Subjects List -->
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600">
                                    <BookCopy class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900 leading-none">Subjects Taught</h2>
                                    <p class="text-xs text-muted-foreground mt-1">Learning areas assigned to this grade level</p>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" class="rounded-xl px-4 h-9 text-xs font-bold border-slate-200" as-child>
                                <Link :href="`/grades/${grade.id}/subjects`">Manage Subjects</Link>
                            </Button>
                        </div>

                        <div v-if="subjects.length === 0" class="rounded-2xl border-2 border-dashed border-slate-100 p-8 text-center bg-slate-50/30">
                            <p class="text-sm font-medium text-slate-400">No subjects added to this grade yet.</p>
                        </div>
                        <div v-else class="grid gap-4 md:grid-cols-2">
                            <Link v-for="subject in subjects" :key="subject.id" :href="`/grades/${grade.id}/subjects`" class="group relative rounded-2xl border border-slate-100 bg-slate-50 p-5 transition-all hover:bg-white hover:border-green-200 hover:shadow-md">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-bold text-slate-900 group-hover:text-green-600 transition-colors">{{ subject.name }}</h3>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <Badge variant="secondary" class="rounded-lg px-2 text-[10px] font-bold bg-white text-slate-500 border-none">{{ subject.code }}</Badge>
                                            <Badge variant="outline" :class="['rounded-lg px-2 text-[10px] font-bold', subject.is_compulsory ? 'border-green-100 text-green-600 bg-green-50/30' : 'border-slate-200 text-slate-400']">{{ subject.is_compulsory ? 'Compulsory' : 'Optional' }}</Badge>
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-bold px-2 py-1 rounded bg-white border border-slate-100 shadow-sm text-slate-400 group-hover:text-green-600 transition-colors">
                                        {{ subject.subject_type }}
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center justify-between text-[10px] font-medium text-slate-500">
                                    <span>Lessons: <span class="text-slate-900 font-bold">{{ subject.lessons_per_week }} / week</span></span>
                                    <span>Duration: <span class="text-slate-900 font-bold">{{ subject.minutes_per_lesson }} min</span></span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Right Section - Manage Learners (1 col) -->
                <div class="space-y-8">
                    <div class="rounded-2xl border bg-slate-900 p-8 shadow-xl relative overflow-hidden group">
                        <!-- Decorative glow -->
                        <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-blue-600 blur-[80px] opacity-20 pointer-events-none group-hover:opacity-40 transition-opacity"></div>
                        
                        <div class="relative z-10 flex flex-col gap-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg">
                                <Users class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white leading-none">Manage Learners</h2>
                                <p class="text-slate-400 text-xs mt-3 leading-relaxed">
                                    View population details, handle new enrollments, and manage learner records for this specific grade level.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pb-2">
                                <div class="bg-white/5 border border-white/10 rounded-xl p-3 backdrop-blur-sm">
                                    <p class="text-[9px] font-bold text-blue-400 uppercase tracking-wider mb-1">Total</p>
                                    <p class="text-xl font-bold text-white">{{ stats.students_count }}</p>
                                </div>
                                <div class="bg-white/5 border border-white/10 rounded-xl p-3 backdrop-blur-sm">
                                    <p class="text-[9px] font-bold text-green-400 uppercase tracking-wider mb-1">Active</p>
                                    <p class="text-xl font-bold text-white">{{ stats.active_students_count }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <Button as-child class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs h-11 rounded-xl shadow-lg shadow-blue-900/40 border-0">
                                    <Link :href="`/grades/${grade.id}/students`">Open Learner List</Link>
                                </Button>
                                <Button variant="outline" as-child class="w-full bg-transparent border-white/20 hover:bg-white/10 text-white font-bold text-xs h-11 rounded-xl">
                                    <Link href="/students/create">Register New Learner</Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info / Help Card -->
                    <div class="rounded-2xl border border-blue-50 bg-blue-50/30 p-6">
                        <h3 class="font-bold text-blue-900 text-sm mb-2 flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4" />
                            Grade Overview
                        </h3>
                        <p class="text-blue-700/70 text-xs leading-relaxed">
                            This grade level represents stage {{ grade.level_order }} of the school curriculum. Use this page to monitor capacity and learning areas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
