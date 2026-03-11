<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookCopy, GraduationCap, School, Users } from 'lucide-vue-next';
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                        <GraduationCap class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ grade.name }}</h1>
                        <p class="text-muted-foreground">{{ grade.code }} • {{ grade.category }} • Level {{ grade.level_order }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child><Link :href="`/grades/${grade.id}/edit`">Edit Grade</Link></Button>
                    <Button variant="outline" as-child><Link href="/grades">Back to Grades</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Grade Lead</div><div class="mt-1 font-semibold">{{ grade.lead_name || 'Not assigned' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Minimum Age</div><div class="mt-1 text-2xl font-bold">{{ grade.minimum_age ?? '—' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Maximum Age</div><div class="mt-1 text-2xl font-bold">{{ grade.maximum_age ?? '—' }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Classes</div><div class="mt-1 text-2xl font-bold text-blue-600">{{ classes.length }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Students</div><div class="mt-1 text-2xl font-bold text-violet-600">{{ stats.students_count }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Status</div><div class="mt-1"><Badge>{{ grade.is_active ? 'Active' : 'Inactive' }}</Badge></div></div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Classes Under {{ grade.name }}</h2>
                        <p class="text-sm text-muted-foreground">Open any class to manage its students and operations</p>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <Link v-for="classroom in classes" :key="classroom.id" :href="`/classes/${classroom.id}`" class="rounded-xl border bg-card p-5 transition hover:border-primary/40 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">{{ classroom.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ classroom.code }}<span v-if="classroom.stream"> • {{ classroom.stream }}</span></p>
                                <p class="mt-1 text-xs text-muted-foreground">Teacher: {{ classroom.teacher || 'Not assigned' }}</p>
                            </div>
                            <div class="rounded-full bg-primary/10 p-2 text-primary"><School class="h-4 w-4" /></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Students</div><div class="mt-1 font-semibold">{{ classroom.students_count }}</div></div>
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Capacity</div><div class="mt-1 font-semibold">{{ classroom.capacity ?? '—' }}</div></div>
                        </div>
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Subjects Assigned to {{ grade.name }}</h2>
                        <p class="text-sm text-muted-foreground">Click a subject card to open the full grade subjects page and manage subject actions there.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-muted-foreground">{{ stats.subjects_count }} subject(s)</div>
                        <Button variant="outline" as-child><Link :href="`/grades/${grade.id}/subjects`">Open Subjects Page</Link></Button>
                    </div>
                </div>
                <div v-if="subjects.length === 0" class="rounded-xl border border-dashed p-8 text-sm text-muted-foreground">No subjects have been allocated to this grade yet.</div>
                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <Link v-for="subject in subjects" :key="subject.id" :href="`/grades/${grade.id}/subjects`" class="rounded-xl border bg-card p-5 transition hover:border-primary/40 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">{{ subject.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || 'General' }}</p>
                            </div>
                            <Badge>{{ subject.is_compulsory ? 'Compulsory' : 'Optional' }}</Badge>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Type</div><div class="mt-1 font-semibold">{{ subject.subject_type }}</div></div>
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Lessons/Week</div><div class="mt-1 font-semibold">{{ subject.lessons_per_week }}</div></div>
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Minutes/Lesson</div><div class="mt-1 font-semibold">{{ subject.minutes_per_lesson }}</div></div>
                            <div class="rounded-lg border p-3"><div class="text-muted-foreground">Status</div><div class="mt-1 font-semibold">{{ subject.is_active ? 'Active' : 'Inactive' }}</div></div>
                        </div>
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Students in {{ grade.name }}</h2>
                        <p class="text-sm text-muted-foreground">Open the grade students page to manage all students enrolled under this grade across its classes.</p>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" as-child><Link :href="`/grades/${grade.id}/students`">View Students</Link></Button>
                        <Button variant="outline" as-child><Link href="/students/create">Enroll New Student</Link></Button>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <Link :href="`/grades/${grade.id}/students`" class="rounded-xl border p-5 transition hover:border-primary/40 hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-primary/10 p-2 text-primary"><Users class="h-4 w-4" /></div>
                            <div>
                                <div class="text-sm text-muted-foreground">Total Students</div>
                                <div class="text-2xl font-bold">{{ stats.students_count }}</div>
                            </div>
                        </div>
                    </Link>
                    <Link :href="`/grades/${grade.id}/students?status=active`" class="rounded-xl border p-5 transition hover:border-primary/40 hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-green-500/10 p-2 text-green-600"><GraduationCap class="h-4 w-4" /></div>
                            <div>
                                <div class="text-sm text-muted-foreground">Active Students</div>
                                <div class="text-2xl font-bold">{{ stats.active_students_count }}</div>
                            </div>
                        </div>
                    </Link>
                    <Link :href="`/grades/${grade.id}/subjects`" class="rounded-xl border p-5 transition hover:border-primary/40 hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-blue-500/10 p-2 text-blue-600"><BookCopy class="h-4 w-4" /></div>
                            <div>
                                <div class="text-sm text-muted-foreground">Compulsory Subjects</div>
                                <div class="text-2xl font-bold">{{ stats.compulsory_subjects_count }}</div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
