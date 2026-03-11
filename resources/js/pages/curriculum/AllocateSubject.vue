<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, BookCopy, Save } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    { title: 'Subjects', href: '/curriculum/subjects' },
    { title: props.subject.name, href: `/curriculum/subjects/${props.subject.id}` },
    { title: 'Allocate', href: `/curriculum/subjects/${props.subject.id}/allocate` },
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

const selectedCount = computed(() => form.allocations.filter((item) => item.selected).length);

const submit = () => {
    form.put(`/curriculum/subjects/${props.subject.id}/allocate`);
};
</script>

<template>
    <Head :title="`Allocate ${subject.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child><Link href="/curriculum/subjects"><ArrowLeft class="h-5 w-5" /></Link></Button>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10"><BookCopy class="h-6 w-6 text-blue-600" /></div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Allocate Subject to Grades</h1>
                        <p class="text-muted-foreground">{{ subject.name }} • {{ subject.code }} • {{ subject.subject_type }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card px-4 py-3 text-sm"><span class="text-muted-foreground">Selected grades:</span> <span class="font-semibold">{{ selectedCount }}</span></div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border bg-card">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Allocate</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Grade</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Lessons / Week</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Minutes / Lesson</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Compulsory</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(allocation, index) in form.allocations" :key="allocation.grade_level_id" class="border-b">
                                    <td class="px-4 py-3"><input v-model="allocation.selected" type="checkbox" /></td>
                                    <td class="px-4 py-3"><div class="font-medium">{{ grades[index].name }}</div><div class="text-xs text-muted-foreground">{{ grades[index].code }}</div></td>
                                    <td class="px-4 py-3"><Input v-model="allocation.lessons_per_week" type="number" min="1" :disabled="!allocation.selected" /></td>
                                    <td class="px-4 py-3"><Input v-model="allocation.minutes_per_lesson" type="number" min="1" :disabled="!allocation.selected" /></td>
                                    <td class="px-4 py-3"><input v-model="allocation.is_compulsory" type="checkbox" :disabled="!allocation.selected" /></td>
                                    <td class="px-4 py-3"><input v-model="allocation.is_active" type="checkbox" :disabled="!allocation.selected" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child><Link href="/curriculum/subjects">Cancel</Link></Button>
                    <Button type="submit" :disabled="form.processing"><Save class="mr-2 h-4 w-4" />Save Allocations</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
