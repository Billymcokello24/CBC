<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    FileText,
    ArrowLeft,
    Download,
    Edit2,
    Trash2,
    CheckCircle2,
    X,
    AlertCircle,
    Calendar,
    User,
    BookOpen,
    Clock,
    ListChecks,
    BookCopy,
    Lightbulb,
    Users,
    ClipboardCheck,
    MapPin,
    Printer,
    Sparkles,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    plan: any;
    school: any;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Academic Planner', href: '/curriculum' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
    { title: 'Detail View', href: '#' },
]);

const statusBadge = computed(() => {
    switch (props.plan.status) {
        case 'approved':
            return {
                variant: 'default',
                label: 'Approved',
                class: 'bg-emerald-500 text-white shadow-emerald-500/20',
            };
        case 'submitted':
            return {
                variant: 'secondary',
                label: 'Waiting for Review',
                class: 'bg-amber-500 text-white shadow-amber-500/20',
            };
        case 'draft':
            return {
                variant: 'outline',
                label: 'Draft',
                class: 'bg-slate-100 text-slate-700',
            };
        default:
            return { variant: 'outline', label: props.plan.status, class: '' };
    }
});

const handleDownload = () => {
    window.location.href = `/curriculum/planner/lesson-plans/${props.plan.id}/download`;
};

const submitPlan = () => {
    router.post(`/curriculum/planner/lesson-plans/${props.plan.id}/submit`);
};

const approvePlan = () => {
    router.post(`/curriculum/planner/lesson-plans/${props.plan.id}/approve`);
};

const rejectPlan = () => {
    const feedback = prompt('Please enter feedback for the teacher:');
    if (feedback !== null) {
        router.post(
            `/curriculum/planner/lesson-plans/${props.plan.id}/reject`,
            { feedback },
        );
    }
};

const deletePlan = () => {
    if (confirm('Are you sure you want to delete this lesson plan?')) {
        router.delete(`/curriculum/planner/lesson-plans/${props.plan.id}`, {
            onSuccess: () =>
                router.visit(
                    `/curriculum/planner/lesson-plans/class/${props.plan.class_id}/subject/${props.plan.subject_id}`,
                ),
        });
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="'Lesson Plan - ' + plan.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Page Header (Aligned with Students Page) -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10"
                    >
                        <FileText class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ plan.title }}
                        </h1>
                        <p class="text-muted-foreground">
                            {{ plan.classroom?.name }} • Week
                            {{ plan.week_number }} • Period
                            {{ plan.period_number }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link
                            :href="`/curriculum/planner/lesson-plans/class/${plan.class_id}/subject/${plan.subject_id}`"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Back to Plans
                        </Link>
                    </Button>
                    <Button variant="outline" @click="handleDownload">
                        <Download class="mr-2 h-4 w-4" /> Export PDF
                    </Button>
                    <template v-if="plan.status === 'draft'">
                        <Button @click="submitPlan">Submit Review</Button>
                    </template>
                    <template
                        v-if="
                            $page.props.auth.user.roles.some((r) =>
                                [
                                    'admin',
                                    'school_admin',
                                    'school_principal',
                                ].includes(r.name),
                            ) && plan.status === 'submitted'
                        "
                    >
                        <Button
                            class="bg-green-600 hover:bg-green-700"
                            @click="approvePlan"
                            >Approve</Button
                        >
                        <Button variant="destructive" @click="rejectPlan"
                            >Reject</Button
                        >
                    </template>
                </div>
            </div>

            <!-- Stats Grid (Aligned with Students Page) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm font-medium text-muted-foreground">
                        Teacher
                    </div>
                    <div class="mt-2 text-xl font-bold">
                        {{ plan.teacher?.user?.name || 'N/A' }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm font-medium text-muted-foreground">
                        Subject
                    </div>
                    <div class="mt-2 text-xl font-bold text-primary">
                        {{ plan.subject?.name || 'N/A' }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm font-medium text-muted-foreground">
                        Scheduled Date
                    </div>
                    <div class="mt-2 text-xl font-bold">
                        {{ formatDate(plan.lesson_date) }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm font-medium text-muted-foreground">
                        Enrollment
                    </div>
                    <div class="mt-2 text-xl font-bold">
                        {{ plan.number_of_learners || 0 }} Students
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Strategic Sidebar (Aligned with StudentShow Sidebar Arrangement) -->
                <div class="space-y-6 lg:col-span-3">
                    <div class="space-y-6 rounded-xl border bg-card p-5">
                        <div class="space-y-2">
                            <h4
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Curriculum Context
                            </h4>
                            <div class="space-y-1">
                                <div
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    {{ plan.strand?.name }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ plan.sub_strand?.name }}
                                </div>
                            </div>
                        </div>
                        <Separator />
                        <div class="space-y-2">
                            <h4
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Strategic Questions
                            </h4>
                            <p
                                class="text-sm leading-relaxed font-medium text-primary"
                            >
                                "{{ plan.inquiry_questions }}"
                            </p>
                        </div>
                        <Separator />
                        <div class="space-y-3">
                            <h4
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Key Vocabulary
                            </h4>
                            <div class="flex flex-wrap gap-1.5">
                                <Badge
                                    v-for="word in plan.key_vocabulary?.split(
                                        ',',
                                    ) || []"
                                    :key="word"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    {{ word.trim() }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group relative space-y-4 overflow-hidden rounded-xl border bg-primary p-6 text-primary-foreground shadow-lg shadow-primary/10"
                    >
                        <div
                            class="absolute -right-4 -bottom-4 opacity-10 transition-transform duration-700 group-hover:scale-110"
                        >
                            <BookCopy class="h-24 w-24" />
                        </div>
                        <h4 class="relative z-10 font-semibold">
                            Instructional Aids
                        </h4>
                        <p
                            class="relative z-10 text-xs leading-relaxed text-primary-foreground/90"
                        >
                            {{
                                plan.teaching_aids ||
                                'No special materials listed.'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Main Pedagogical Flow -->
                <div
                    class="animate-in space-y-6 duration-700 slide-in-from-bottom-4 lg:col-span-9"
                >
                    <div class="rounded-xl border bg-card">
                        <div class="border-b px-6 py-4">
                            <h3
                                class="flex items-center gap-2 font-semibold text-slate-900"
                            >
                                <Sparkles class="h-4 w-4 text-primary" />
                                Outcomes & Goals
                            </h3>
                        </div>
                        <div class="space-y-6 p-6">
                            <div
                                class="rounded-lg border bg-muted/30 p-4 text-slate-700"
                            >
                                "{{ plan.learning_outcomes }}"
                            </div>
                            <div class="grid gap-6 sm:grid-cols-3">
                                <div class="space-y-3">
                                    <h4
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Competencies
                                    </h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            v-for="c in plan.core_competencies"
                                            :key="c"
                                            variant="outline"
                                            >{{ c }}</Badge
                                        >
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <h4
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Values
                                    </h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            v-for="v in plan.values"
                                            :key="v"
                                            variant="secondary"
                                            >{{ v }}</Badge
                                        >
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <h4
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        PCI Points
                                    </h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            v-for="p in plan.pci"
                                            :key="p"
                                            variant="outline"
                                            >{{ p }}</Badge
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card">
                        <div class="border-b px-6 py-4">
                            <h3
                                class="flex items-center gap-2 font-semibold text-slate-900"
                            >
                                <Users class="h-4 w-4 text-primary" />
                                Instructional Delivery
                            </h3>
                        </div>
                        <div class="divide-y">
                            <div class="space-y-3 p-6">
                                <h4
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Introduction
                                </h4>
                                <p
                                    class="text-sm leading-relaxed text-slate-600"
                                >
                                    {{ plan.introduction }}
                                </p>
                            </div>
                            <div class="space-y-4 p-6">
                                <h4
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Core Activities
                                </h4>
                                <div
                                    v-if="
                                        Array.isArray(plan.learning_activities)
                                    "
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="(
                                            act, i
                                        ) in plan.learning_activities"
                                        :key="i"
                                        class="flex items-start gap-4 text-sm"
                                    >
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary"
                                        >
                                            {{ i + 1 }}
                                        </div>
                                        <p
                                            class="pt-0.5 leading-relaxed text-slate-600"
                                        >
                                            {{ act }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                    <div
                                        class="rounded-xl border border-dashed bg-muted/30 p-4 text-sm"
                                    >
                                        <span
                                            class="mb-2 block text-xs font-bold text-muted-foreground uppercase"
                                            >Teacher Actions</span
                                        >
                                        <p
                                            class="leading-relaxed text-slate-600"
                                        >
                                            {{ plan.teacher_activities }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-dashed bg-muted/30 p-4 text-sm"
                                    >
                                        <span
                                            class="mb-2 block text-xs font-bold text-muted-foreground uppercase"
                                            >Learner Actions</span
                                        >
                                        <p
                                            class="leading-relaxed text-slate-600"
                                        >
                                            {{ plan.learner_activities }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3 p-6">
                                <h4
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Conclusion
                                </h4>
                                <p
                                    class="text-sm leading-relaxed font-medium text-slate-600"
                                >
                                    {{ plan.conclusion }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <div class="grid gap-8 sm:grid-cols-2">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-2 w-2 rounded-full bg-emerald-500"
                                    ></div>
                                    <h4
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        Assessment
                                    </h4>
                                </div>
                                <p
                                    class="text-sm leading-relaxed text-slate-600"
                                >
                                    {{ plan.assessment_methods }}
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-2 w-2 rounded-full bg-amber-500"
                                    ></div>
                                    <h4
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        Reflection
                                    </h4>
                                </div>
                                <p
                                    class="text-sm leading-relaxed text-slate-600"
                                >
                                    "{{
                                        plan.reflection ||
                                        'Pending execution...'
                                    }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.italic {
    font-style:;
}
</style>
