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

watch([() => form.grade_level_id, () => form.stream_id], ([gradeId, streamId]) => {
    if (!gradeId) return;
    
    const grade = props.grades.find(g => String(g.id) === String(gradeId));
    const stream = props.streams.find(s => String(s.id) === String(streamId));
    
    if (grade) {
        form.name = grade.name + (stream ? ' ' + stream.name : '');
        form.code = grade.code + (stream ? stream.code : '');
    }
});
</script>

<template>
    <Head title="Add Class" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/classes"><ArrowLeft class="h-5 w-5" /></Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Add Class</h1>
                    <p class="text-muted-foreground">Create a new class and connect it to grade, stream, and academic year</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="name">Class Name</Label>
                        <Input id="name" v-model="form.name" placeholder="Grade 9 West" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="code">Class Code</Label>
                        <Input id="code" v-model="form.code" placeholder="G9W" />
                        <InputError :message="form.errors.code" />
                    </div>
                    <div class="space-y-2">
                        <Label for="capacity">Capacity</Label>
                        <Input id="capacity" v-model="form.capacity" type="number" min="1" />
                        <InputError :message="form.errors.capacity" />
                    </div>
                    <div class="space-y-2">
                        <Label for="grade_level_id">Grade</Label>
                        <select id="grade_level_id" v-model="form.grade_level_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="">Select grade</option>
                            <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                        </select>
                        <InputError :message="form.errors.grade_level_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="stream_id">Stream</Label>
                        <select id="stream_id" v-model="form.stream_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="">No stream</option>
                            <option v-for="stream in streams" :key="stream.id" :value="String(stream.id)">{{ stream.name }}</option>
                        </select>
                        <InputError :message="form.errors.stream_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="academic_year_id">Academic Year</Label>
                        <select id="academic_year_id" v-model="form.academic_year_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="">Select academic year</option>
                            <option v-for="year in academicYears" :key="year.id" :value="String(year.id)">{{ year.name }}</option>
                        </select>
                        <InputError :message="form.errors.academic_year_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="is_active">Status</Label>
                        <select id="is_active" v-model="form.is_active" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                        <InputError :message="form.errors.is_active" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child><Link href="/classes">Cancel</Link></Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <PlusSquare v-else class="mr-2 h-4 w-4" />
                        Save Class
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
