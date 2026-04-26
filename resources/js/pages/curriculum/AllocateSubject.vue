<script setup lang="ts">
/* global route */
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Save,
    GraduationCap,
    CheckCircle2,
    ShieldCheck,
    Square,
    CheckSquare,
    Clock,
    BookOpenCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subject: {
        id: number;
        name: string;
        code: string;
        subject_type: string;
    };
    grades: Array<{
        id: number;
        name: string;
        code: string;
        is_allocated: boolean;
        lessons_per_week: number;
        minutes_per_lesson: number;
        is_compulsory: boolean;
        is_active: boolean;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects Registry', href: '/curriculum/subjects' },
    {
        title: props.subject.name,
        href: `/curriculum/subjects/${props.subject.id}`,
    },
    {
        title: 'Grade Allocation',
        href: `/curriculum/subjects/${props.subject.id}/allocate`,
    },
];

const form = useForm({
    allocations: props.grades.map((grade) => ({
        grade_level_id: grade.id,
        selected: grade.is_allocated,
        lessons_per_week: grade.lessons_per_week,
        minutes_per_lesson: grade.minutes_per_lesson,
        is_compulsory: grade.is_compulsory,
        is_active: grade.is_active,
    })),
});

const selectedCount = computed(
    () => form.allocations.filter((item) => item.selected).length,
);

const submit = () => {
    form.put(`/curriculum/subjects/${props.subject.id}/allocate`);
};

const toggleSelect = (index: number) => {
    form.allocations[index].selected = !form.allocations[index].selected;
};
</script>

<template>
    <Head :title="`Allocate ${subject.name}`" />
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
                        <Link :href="`/curriculum/subjects/${subject.id}`"
                            ><ArrowLeft class="h-4 w-4"
                        /></Link>
                    </Button>
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <BookOpenCheck class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Grade Allocation Matrix
                        </h1>
                        <p
                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >
                            {{ subject.name }} • {{ subject.code }} •
                            {{ subject.subject_type }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center gap-3 rounded-xl border bg-card px-5 py-2 text-sm shadow-sm"
                    >
                        <span
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Selected Hubs</span
                        >
                        <span class="text-lg font-bold text-violet-600">{{
                            selectedCount
                        }}</span>
                    </div>
                    <Button
                        @click="submit"
                        :disabled="form.processing"
                        class="font-pulsar h-10 border-0 bg-slate-900 px-6 shadow-lg hover:bg-black"
                    >
                        <Save class="mr-2 h-4 w-4" />Store Matrix
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                            Available Grades
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ grades.length }} Levels
                        </p>
                    </div>
                </div>
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
                            Allocation Pulse
                        </p>
                        <p class="text-xl font-bold text-emerald-600">
                            {{ selectedCount }} Active
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-blue-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-blue-500/10 p-3 transition-all group-hover:bg-blue-500 group-hover:text-white"
                    >
                        <Clock
                            class="h-5 w-5 text-blue-600 group-hover:text-white"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Typical Weight
                        </p>
                        <p
                            class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            35 MINS / SESSION
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

            <!-- Allocation Table -->
            <div
                class="overflow-hidden rounded-3xl border border-t-8 border-t-violet-500 bg-card shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b bg-slate-50 px-8 py-5"
                >
                    <h2
                        class="flex items-center gap-3 text-xs font-bold tracking-tight text-slate-900 uppercase"
                    >
                        <BookOpenCheck class="h-4 w-4 text-violet-600" /> Grade
                        Level Mapping
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="font-pulsar border-b bg-white">
                                <th
                                    class="w-16 px-8 py-4 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Select
                                </th>
                                <th
                                    class="px-8 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Grade Hub
                                </th>
                                <th
                                    class="px-8 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Sessions / Week
                                </th>
                                <th
                                    class="px-8 py-4 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Duration (Mins)
                                </th>
                                <th
                                    class="px-8 py-4 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Compulsory
                                </th>
                                <th
                                    class="px-8 py-4 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >
                                    Logic Active
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <tr
                                v-for="(allocation, index) in form.allocations"
                                :key="allocation.grade_level_id"
                                class="group border-l-4 border-l-transparent transition-colors"
                                :class="[
                                    allocation.selected
                                        ? 'border-l-violet-500 bg-violet-50/30'
                                        : 'hover:bg-slate-50/70',
                                ]"
                            >
                                <td class="px-8 py-4 text-center">
                                    <button
                                        @click="toggleSelect(index)"
                                        type="button"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-white shadow-sm transition-all"
                                        :class="
                                            allocation.selected
                                                ? 'border-violet-600 bg-violet-600 text-white'
                                                : 'border-slate-300'
                                        "
                                    >
                                        <component
                                            :is="
                                                allocation.selected
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </td>
                                <td class="px-8 py-4">
                                    <div>
                                        <div
                                            class="mb-1 text-base leading-none font-bold tracking-tight text-slate-900 transition-colors group-hover:text-violet-700"
                                        >
                                            {{ grades[index].name }}
                                        </div>
                                        <div
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            {{ grades[index].code }} • Hub Node
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="relative max-w-[100px]">
                                        <Input
                                            v-model="
                                                allocation.lessons_per_week
                                            "
                                            type="number"
                                            min="1"
                                            :disabled="!allocation.selected"
                                            class="h-9 border-slate-200 text-center font-bold focus:ring-violet-500"
                                        />
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="relative max-w-[100px]">
                                        <Input
                                            v-model="
                                                allocation.minutes_per_lesson
                                            "
                                            type="number"
                                            min="1"
                                            :disabled="!allocation.selected"
                                            class="h-9 border-slate-200 text-center font-bold focus:ring-violet-500"
                                        />
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    <button
                                        @click="
                                            allocation.selected &&
                                            (allocation.is_compulsory =
                                                !allocation.is_compulsory)
                                        "
                                        :disabled="!allocation.selected"
                                        type="button"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-white shadow-sm transition-all disabled:opacity-30"
                                        :class="
                                            allocation.is_compulsory
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-slate-300'
                                        "
                                    >
                                        <component
                                            :is="
                                                allocation.is_compulsory
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    <button
                                        @click="
                                            allocation.selected &&
                                            (allocation.is_active =
                                                !allocation.is_active)
                                        "
                                        :disabled="!allocation.selected"
                                        type="button"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded-lg border-2 bg-white shadow-sm transition-all disabled:opacity-30"
                                        :class="
                                            allocation.is_active
                                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                                : 'border-slate-300'
                                        "
                                    >
                                        <component
                                            :is="
                                                allocation.is_active
                                                    ? CheckSquare
                                                    : Square
                                            "
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Sync -->
            <div
                class="mt-2 flex flex-col items-center justify-between gap-6 rounded-3xl border border-slate-800 bg-slate-900 p-6 text-white shadow-lg md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10"
                    >
                        <Save class="h-5 w-5 text-violet-400" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-white/90 uppercase"
                        >
                            Allocation Matrix Optimization
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-semibold tracking-wider text-slate-400"
                        >
                            Store your grade-level mapping to finalize the
                            instructional pulse for this subject node.
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Button
                        variant="outline"
                        type="button"
                        as-child
                        class="h-10 rounded-xl border-white/10 bg-white/5 px-6 text-xs font-bold tracking-tight text-white uppercase hover:bg-white/20"
                    >
                        <Link :href="`/curriculum/subjects/${subject.id}`"
                            >Discard Changes</Link
                        >
                    </Button>
                    <Button
                        @click="submit"
                        :disabled="form.processing"
                        class="font-pulsar h-10 rounded-xl border-0 bg-violet-600 px-8 text-xs tracking-tight uppercase shadow-xl hover:bg-violet-700"
                    >
                        Finalize Matrix
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-bold {
    font-weight: 950;
}
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
    opacity: 1;
}
</style>
