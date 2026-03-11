<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, PlusSquare } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{ learningAreas: Array<{ id: number; name: string }> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects', href: '/curriculum/subjects' },
    { title: 'Add Subject', href: '/curriculum/subjects/create' },
];

const form = useForm({
    learning_area_id: '',
    name: '',
    code: '',
    description: '',
    subject_type: 'core',
    is_examinable: true,
    display_order: 0,
    is_active: true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        learning_area_id: Number(data.learning_area_id),
        display_order: Number(data.display_order),
        is_examinable: Boolean(data.is_examinable),
        is_active: Boolean(data.is_active),
    })).post('/curriculum/subjects');
};
</script>

<template>
    <Head title="Add Subject" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child><Link href="/curriculum/subjects"><ArrowLeft class="h-5 w-5" /></Link></Button>
                <div><h1 class="text-2xl font-bold tracking-tight">Add Subject</h1><p class="text-muted-foreground">Create a subject and link it to a learning area</p></div>
            </div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2"><Label for="learning_area_id">Learning Area</Label><select id="learning_area_id" v-model="form.learning_area_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option value="">Select learning area</option><option v-for="area in learningAreas" :key="area.id" :value="String(area.id)">{{ area.name }}</option></select><InputError :message="form.errors.learning_area_id" /></div>
                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="subject_type">Type</Label><Input id="subject_type" v-model="form.subject_type" /></div>
                    <div class="space-y-2"><Label for="display_order">Display Order</Label><Input id="display_order" v-model="form.display_order" type="number" min="0" /></div>
                    <div class="space-y-2"><Label for="description">Description</Label><Input id="description" v-model="form.description" /></div>
                </div>
                <div class="flex justify-end gap-3"><Button type="button" variant="outline" as-child><Link href="/curriculum/subjects">Cancel</Link></Button><Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><PlusSquare v-else class="mr-2 h-4 w-4" />Save Subject</Button></div>
            </form>
        </div>
    </AppLayout>
</template>
