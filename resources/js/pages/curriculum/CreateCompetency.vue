<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, PlusSquare } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Competencies', href: '/curriculum/competencies' },
    { title: 'Add Competency', href: '/curriculum/competencies/create' },
];

const form = useForm({ name: '', code: '', description: '', category: '', display_order: 0, is_active: true });
const submit = () => {
    form.transform((data) => ({ ...data, display_order: Number(data.display_order), is_active: Boolean(data.is_active) })).post('/curriculum/competencies');
};
</script>

<template>
    <Head title="Add Competency" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4"><Button variant="ghost" size="icon" as-child><Link href="/curriculum/competencies"><ArrowLeft class="h-5 w-5" /></Link></Button><div><h1 class="text-2xl font-bold tracking-tight">Add Competency</h1><p class="text-muted-foreground">Create a CBC competency and prepare it for indicators</p></div></div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="category">Category</Label><Input id="category" v-model="form.category" /><InputError :message="form.errors.category" /></div>
                    <div class="space-y-2"><Label for="display_order">Display Order</Label><Input id="display_order" v-model="form.display_order" type="number" min="0" /></div>
                    <div class="space-y-2 md:col-span-2 lg:col-span-2"><Label for="description">Description</Label><Input id="description" v-model="form.description" /></div>
                </div>
                <div class="flex justify-end gap-3"><Button type="button" variant="outline" as-child><Link href="/curriculum/competencies">Cancel</Link></Button><Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><PlusSquare v-else class="mr-2 h-4 w-4" />Save Competency</Button></div>
            </form>
        </div>
    </AppLayout>
</template>
