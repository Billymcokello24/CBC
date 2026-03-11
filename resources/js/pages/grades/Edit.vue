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
    grade: {
        id: number;
        name: string;
        code: string;
        category: string;
        level_order: number;
        minimum_age: number | null;
        maximum_age: number | null;
        is_active: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
    { title: 'Edit Grade', href: `/grades/${props.grade.id}/edit` },
];

const form = useForm({
    name: props.grade.name,
    code: props.grade.code,
    category: props.grade.category,
    level_order: props.grade.level_order,
    minimum_age: props.grade.minimum_age ?? '',
    maximum_age: props.grade.maximum_age ?? '',
    is_active: props.grade.is_active,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        level_order: Number(data.level_order),
        minimum_age: data.minimum_age ? Number(data.minimum_age) : null,
        maximum_age: data.maximum_age ? Number(data.maximum_age) : null,
        is_active: Boolean(data.is_active),
    })).put(`/grades/${props.grade.id}`);
};
</script>

<template>
    <Head title="Edit Grade" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child><Link href="/grades"><ArrowLeft class="h-5 w-5" /></Link></Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Grade</h1>
                    <p class="text-muted-foreground">Update grade setup and progression metadata</p>
                </div>
            </div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="category">Category</Label><Input id="category" v-model="form.category" /><InputError :message="form.errors.category" /></div>
                    <div class="space-y-2"><Label for="level_order">Level Order</Label><Input id="level_order" v-model="form.level_order" type="number" min="1" /><InputError :message="form.errors.level_order" /></div>
                    <div class="space-y-2"><Label for="minimum_age">Minimum Age</Label><Input id="minimum_age" v-model="form.minimum_age" type="number" min="1" /><InputError :message="form.errors.minimum_age" /></div>
                    <div class="space-y-2"><Label for="maximum_age">Maximum Age</Label><Input id="maximum_age" v-model="form.maximum_age" type="number" min="1" /><InputError :message="form.errors.maximum_age" /></div>
                    <div class="space-y-2"><Label for="is_active">Status</Label><select id="is_active" v-model="form.is_active" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option :value="true">Active</option><option :value="false">Inactive</option></select></div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child><Link href="/grades">Cancel</Link></Button>
                    <Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><Save v-else class="mr-2 h-4 w-4" />Save Changes</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
