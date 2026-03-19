<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ 
    subject: { 
        id: number; 
        learning_area_id: number; 
        department_id: number | null;
        name: string; 
        code: string; 
        description: string | null; 
        subject_type: string; 
        is_examinable: boolean; 
        display_order: number; 
        is_active: boolean 
    }; 
    learningAreas: Array<{ id: number; name: string }>;
    departments: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, 
    { title: 'Curriculum', href: '/curriculum' }, 
    { title: 'Subjects', href: '/curriculum/subjects' }, 
    { title: 'Edit Subject', href: `/curriculum/subjects/${props.subject.id}/edit` }
];

const form = useForm({ 
    learning_area_id: String(props.subject.learning_area_id), 
    department_id: props.subject.department_id ? String(props.subject.department_id) : '',
    name: props.subject.name, 
    code: props.subject.code, 
    description: props.subject.description ?? '', 
    subject_type: props.subject.subject_type, 
    is_examinable: props.subject.is_examinable, 
    display_order: props.subject.display_order, 
    is_active: props.subject.is_active 
});

const submit = () => form.transform((data) => ({ 
    ...data, 
    learning_area_id: Number(data.learning_area_id), 
    department_id: data.department_id ? Number(data.department_id) : null,
    display_order: Number(data.display_order), 
    is_examinable: Boolean(data.is_examinable), 
    is_active: Boolean(data.is_active) 
})).put(`/curriculum/subjects/${props.subject.id}`);
</script>

<template>
    <Head title="Edit Subject" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child><Link href="/curriculum/subjects"><ArrowLeft class="h-5 w-5" /></Link></Button>
                <div><h1 class="text-2xl font-bold tracking-tight">Edit Subject</h1><p class="text-muted-foreground">Update subject details</p></div>
            </div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="learning_area_id">Learning Area</Label>
                        <select id="learning_area_id" v-model="form.learning_area_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option v-for="area in learningAreas" :key="area.id" :value="String(area.id)">{{ area.name }}</option>
                        </select>
                        <InputError :message="form.errors.learning_area_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="department_id">Department (Optional)</Label>
                        <select id="department_id" v-model="form.department_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">No Department</option>
                            <option v-for="dept in departments" :key="dept.id" :value="String(dept.id)">{{ dept.name }}</option>
                        </select>
                        <InputError :message="form.errors.department_id" />
                    </div>

                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" class="focus-visible:ring-indigo-500" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" class="focus-visible:ring-indigo-500" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="subject_type">Type</Label><Input id="subject_type" v-model="form.subject_type" class="focus-visible:ring-indigo-500" /></div>
                    <div class="space-y-2"><Label for="display_order">Display Order</Label><Input id="display_order" v-model="form.display_order" type="number" min="0" class="focus-visible:ring-indigo-500" /></div>
                    <div class="space-y-2 md:col-span-2 lg:col-span-3"><Label for="description">Description</Label><Input id="description" v-model="form.description" class="focus-visible:ring-indigo-500" /></div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child><Link href="/curriculum/subjects">Cancel</Link></Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><Save v-else class="mr-2 h-4 w-4" />Save Changes
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
