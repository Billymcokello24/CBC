<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, PlusSquare } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{ subjects: Array<{ id: number; name: string }>; grades: Array<{ id: number; name: string }> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Strands', href: '/curriculum/strands' },
    { title: 'Add Strand', href: '/curriculum/strands/create' },
];

const form = useForm({ subject_id: '', grade_level_id: '', name: '', code: '', description: '', display_order: 0, is_active: true });
const submit = () => {
    form.transform((data) => ({ ...data, subject_id: Number(data.subject_id), grade_level_id: Number(data.grade_level_id), display_order: Number(data.display_order), is_active: Boolean(data.is_active) })).post('/curriculum/strands');
};
</script>

<template>
    <Head title="Add Strand" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4"><Button variant="ghost" size="icon" as-child><Link href="/curriculum/strands"><ArrowLeft class="h-5 w-5" /></Link></Button><div><h1 class="text-2xl font-bold tracking-tight">Add Strand</h1><p class="text-muted-foreground">Create a strand under a subject and grade level</p></div></div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2"><Label for="subject_id">Subject</Label><select id="subject_id" v-model="form.subject_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option value="">Select subject</option><option v-for="subject in subjects" :key="subject.id" :value="String(subject.id)">{{ subject.name }}</option></select><InputError :message="form.errors.subject_id" /></div>
                    <div class="space-y-2"><Label for="grade_level_id">Grade</Label><select id="grade_level_id" v-model="form.grade_level_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option value="">Select grade</option><option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option></select><InputError :message="form.errors.grade_level_id" /></div>
                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="display_order">Display Order</Label><Input id="display_order" v-model="form.display_order" type="number" min="0" /></div>
                    <div class="space-y-2"><Label for="description">Description</Label><Input id="description" v-model="form.description" /></div>
                </div>
                <div class="flex justify-end gap-3"><Button type="button" variant="outline" as-child><Link href="/curriculum/strands">Cancel</Link></Button><Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><PlusSquare v-else class="mr-2 h-4 w-4" />Save Strand</Button></div>
            </form>
        </div>
    </AppLayout>
</template>
