<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, PlusSquare } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    grades: Array<{ id: number; name: string; code: string }>;
    streams: Array<{ id: number; name: string; code: string }>;
    academicYears: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
    { title: 'Add Class', href: '/classes/create' },
];

const form = useForm({
    name: '',
    code: '',
    grade_level_id: '',
    stream_id: '',
    academic_year_id: '',
    capacity: 40,
    is_active: true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        grade_level_id: Number(data.grade_level_id),
        stream_id: data.stream_id ? Number(data.stream_id) : null,
        academic_year_id: Number(data.academic_year_id),
        capacity: Number(data.capacity),
        is_active: Boolean(data.is_active),
    })).post('/classes');
};

import { watch } from 'vue';

watch(
    [() => form.grade_level_id, () => form.stream_id],
    ([gradeId, streamId]) => {
        if (!gradeId) return;

        const grade = props.grades.find(
            (g) => String(g.id) === String(gradeId),
        );
        const stream = props.streams.find(
            (s) => String(s.id) === String(streamId),
        );

        if (grade) {
            form.name = grade.name + (stream ? ' ' + stream.name : '');
            form.code = grade.code + (stream ? stream.code : '');
        }
    },
);
</script>

<template>
    <Head title="Add Class" />

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
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary text-white shadow-md"
                    >
                        <PlusSquare class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                Add Class
                            </h1>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground font-medium">
                            Create a new class and assign its grade, stream and year.
                        </p>
                    </div>
                </div>
                <Button
                    variant="outline"
                    as-child
                    class="h-10 rounded-lg border-border px-6 text-xs font-bold uppercase transition-all"
                >
                    <Link href="/classes"
                        ><ArrowLeft class="mr-2 h-4 w-4" />Back to List</Link
                    >
                </Button>
            </div>

            <div class="max-w-4xl">
                <form @submit.prevent="submit" class="space-y-8">
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="border-b bg-muted/50 px-8 py-6"
                        >
                            <h2 class="text-lg font-bold text-foreground"
                                >Basic Configuration</h2
                            >
                            <p class="text-xs font-medium text-muted-foreground"
                                >Configure the primary details of the class.</p
                            >
                        </div>
                        <div class="p-8">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label
                                        for="name"
                                        class="ml-1 text-[10px] font-bold tracking-wider text-muted-foreground uppercase opacity-40"
                                        >Class Name</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g. GRADE 1 RED"
                                        class="h-11 rounded-lg border-border"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-3">
                                    <Label
                                        for="code"
                                        class="ml-1 text-[10px] font-bold tracking-wider text-muted-foreground uppercase opacity-40"
                                        >Class Code</Label
                                    >
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        placeholder="e.g. G1R"
                                        class="h-11 rounded-lg border-border uppercase"
                                    />
                                    <InputError :message="form.errors.code" />
                                </div>
                                <div class="space-y-3">
                                    <Label
                                        for="capacity"
                                        class="ml-1 text-[10px] font-bold tracking-wider text-muted-foreground uppercase opacity-40"
                                        >Class Capacity</Label
                                    >
                                    <Input
                                        id="capacity"
                                        v-model="form.capacity"
                                        type="number"
                                        min="1"
                                        class="h-11 rounded-lg border-border"
                                    />
                                    <InputError
                                        :message="form.errors.capacity"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="border-b bg-muted/50 px-8 py-6"
                        >
                            <h2 class="text-lg font-bold text-foreground"
                                >Academic Setup</h2
                            >
                            <p class="text-xs font-medium text-muted-foreground"
                                >Assign the class to its respective grade, stream and year.</p
                            >
                        </div>
                        <div class="p-8">
                            <div
                                class="grid gap-8 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div class="space-y-3">
                                    <Label
                                        for="grade_level_id"
                                        class="ml-1 text-[10px] font-bold tracking-wider text-muted-foreground uppercase opacity-40"
                                        >Grade Level</Label
                                    >
                                    <select
                                        id="grade_level_id"
                                        v-model="form.grade_level_id"
                                        class="h-11 w-full rounded-lg border border-border bg-background px-4 text-xs font-bold outline-none"
                                    >
                                        <option value="">Select Grade</option>
                                        <option
                                            v-for="grade in grades"
                                            :key="grade.id"
                                            :value="String(grade.id)"
                                        >
                                            {{ grade.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.grade_level_id"
                                    />
                                </div>
                                <div class="space-y-3">
                                    <Label
                                        for="stream_id"
                                        class="ml-1 text-[10px] font-bold tracking-wider text-muted-foreground uppercase opacity-40"
                                        >Stream</Label
                                    >
                                    <select
                                        id="stream_id"
                                        v-model="form.stream_id"
                                        class="h-11 w-full rounded-lg border border-border bg-background px-4 text-xs font-bold outline-none"
                                    >
                                        <option value="">
                                            No stream assigned
                                        </option>
                                        <option
                                            v-for="stream in streams"
                                            :key="stream.id"
                                            :value="String(stream.id)"
                                        >
                                            {{ stream.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.stream_id"
                                    />
                                </div>
                                <div class="space-y-3">
                                    <Label
                                        for="academic_year_id"
                                        class="ml-1 text-[10px] font-bold tracking-wider text-muted-foreground uppercase opacity-40"
                                        >Academic Year</Label
                                    >
                                    <select
                                        id="academic_year_id"
                                        v-model="form.academic_year_id"
                                        class="h-11 w-full rounded-lg border border-border bg-background px-4 text-xs font-bold outline-none"
                                    >
                                        <option value="">Select Year</option>
                                        <option
                                            v-for="year in academicYears"
                                            :key="year.id"
                                            :value="String(year.id)"
                                        >
                                            {{ year.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.academic_year_id"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <Button
                            type="button"
                            variant="ghost"
                            as-child
                            class="h-12 rounded-lg px-8 text-xs font-bold text-muted-foreground uppercase transition-all"
                        >
                            <Link href="/classes">Cancel</Link>
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-12 rounded-lg bg-slate-900 px-10 text-xs font-bold text-white uppercase shadow-lg transition-all"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <PlusSquare v-else class="mr-2 h-4 w-4" />
                            Create Class
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
