<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Users,
    BookOpen,
    ChevronRight,
    School,
    User,
    ArrowLeft,
    Plus,
    BookCopy,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grade: any;
    classes: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
    { title: props.grade.name, href: '#' },
];
</script>

<template>
    <Head :title="`${grade.name} - Classes`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-8 p-6 pb-20 duration-700 fade-in slide-in-from-bottom-4 md:p-8"
        >
            <!-- Navigation & Header -->
            <div
                class="flex flex-col gap-6 border-b border-border/40 pb-6 md:flex-row md:items-center"
            >
                <Link href="/curriculum/planner/lesson-plans">
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-10 w-10 rounded-full border-border bg-background shadow-sm transition-all hover:bg-muted"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div class="space-y-1">
                    <h1
                        class="flex items-center gap-3 text-3xl font-bold tracking-tight text-foreground"
                    >
                        {{ grade.name }}
                        <Badge
                            variant="outline"
                            class="border-primary/20 bg-primary/5 py-1 text-xs text-primary"
                            >{{ grade.code }}</Badge
                        >
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Select a specific class context to manage its daily
                        lesson plans.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        @click="
                            router.get(
                                `/curriculum/planner/lesson-plans/class/${classes[0]?.id}/subjects?showBulk=true`,
                            )
                        "
                        class="inline-flex h-11 items-center justify-center rounded-2xl border-border px-6 text-xs font-medium tracking-tight uppercase shadow-sm transition-all hover:bg-muted"
                        v-if="classes.length > 0"
                    >
                        <BookCopy class="mr-2.5 h-4 w-4" /> Bulk Upload
                    </Button>
                    <Button
                        @click="
                            router.get(
                                `/curriculum/planner/lesson-plans/class/${classes[0]?.id}/subjects?addNew=true`,
                            )
                        "
                        class="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-8 text-xs font-medium tracking-tight text-primary-foreground uppercase shadow-xl shadow-primary/20 transition-all hover:opacity-90 active:scale-95"
                        v-if="classes.length > 0"
                    >
                        <Plus class="mr-2.5 h-4 w-4" /> New Lesson
                    </Button>
                </div>
            </div>

            <!-- Classes Grid -->
            <div
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <Link
                    v-for="c in classes"
                    :key="c.id"
                    :href="`/curriculum/planner/lesson-plans/class/${c.id}/subjects`"
                    class="group relative flex min-h-[220px] flex-col justify-between overflow-hidden rounded-3xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-primary/5"
                >
                    <div
                        class="pointer-events-none absolute inset-0 bg-gradient-to-br from-transparent to-primary/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    ></div>

                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary transition-all duration-300 group-hover:scale-110 group-hover:bg-primary group-hover:text-primary-foreground group-hover:shadow-lg group-hover:shadow-primary/30"
                            >
                                <School class="h-6 w-6" />
                            </div>
                            <Badge
                                variant="outline"
                                class="border border-border/50 bg-card px-3 py-1 text-xs font-bold tracking-tight text-foreground uppercase shadow-sm"
                                >Class Context</Badge
                            >
                        </div>
                        <h3
                            class="text-xl font-bold tracking-tight text-foreground transition-colors group-hover:text-primary"
                        >
                            {{ c.name }}
                        </h3>
                        <p
                            class="mt-2 flex items-center gap-1.5 text-sm font-bold tracking-wider text-muted-foreground/80 uppercase opacity-80"
                        >
                            <User class="h-3.5 w-3.5" />
                            Teacher:
                            {{
                                c.class_teacher?.first_name
                                    ? `${c.class_teacher.first_name} ${c.class_teacher.last_name}`
                                    : 'Not Assigned'
                            }}
                        </p>
                    </div>

                    <div
                        class="relative z-10 mt-6 flex items-center justify-between border-t border-border/40 pt-5"
                    >
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50/50"
                            >
                                <BookOpen class="h-4 w-4 text-blue-500" />
                            </div>
                            <span class="text-sm font-bold text-foreground">
                                {{ c.lesson_plans_count || 0 }}
                                <span
                                    class="ml-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >Plans</span
                                >
                            </span>
                        </div>
                        <div
                            class="flex h-8 w-8 transform items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg shadow-primary/20 transition-transform duration-300 group-hover:translate-x-1"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </div>
                </Link>

                <div
                    v-if="classes.length === 0"
                    class="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-border/60 bg-card py-20 text-center"
                >
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted/50 text-muted-foreground"
                    >
                        <School class="h-8 w-8 opacity-50" />
                    </div>
                    <h3
                        class="text-lg font-bold tracking-tight text-foreground"
                    >
                        No Classes Assigned
                    </h3>
                    <p
                        class="mt-2 max-w-[300px] text-sm leading-relaxed text-muted-foreground"
                    >
                        There are no classes found under {{ grade.name }} for
                        your account context.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
