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
    { title: 'Grades', href: '/grades' },
    { title: 'Add Grade', href: '/grades/create' },
];

const form = useForm({
    name: '',
    code: '',
    category: 'CBC',
    level_order: 1,
    minimum_age: '',
    maximum_age: '',
    is_active: true,
    is_bulk: false,
    base_name: 'Grade',
    start_level: 1,
    end_level: 6,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        level_order: data.is_bulk ? Number(data.start_level) : Number(data.level_order),
        minimum_age: data.minimum_age ? Number(data.minimum_age) : null,
        maximum_age: data.maximum_age ? Number(data.maximum_age) : null,
        start_level: Number(data.start_level),
        end_level: Number(data.end_level),
        is_active: Boolean(data.is_active),
    })).post('/grades');
};
</script>

<template>
    <Head title="Add Grade" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child><Link href="/grades"><ArrowLeft class="h-5 w-5" /></Link></Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Add Grade</h1>
                    <p class="text-muted-foreground">Create a new grade level and position it in the academic progression</p>
                </div>
            </div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="flex items-center gap-2 pb-4 border-b">
                    <input type="checkbox" id="is_bulk" v-model="form.is_bulk" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                    <Label for="is_bulk" class="text-base font-semibold cursor-pointer">Bulk Creation Mode</Label>
                </div>

                <div v-if="form.is_bulk" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2"><Label for="base_name">Base Name</Label><Input id="base_name" v-model="form.base_name" placeholder="Grade" /><InputError :message="form.errors.base_name" /></div>
                    <div class="space-y-2"><Label for="start_level">Start Level</Label><Input id="start_level" v-model="form.start_level" type="number" min="1" /><InputError :message="form.errors.start_level" /></div>
                    <div class="space-y-2"><Label for="end_level">End Level</Label><Input id="end_level" v-model="form.end_level" type="number" min="1" /><InputError :message="form.errors.end_level" /></div>
                    <div class="space-y-2"><Label for="category">Category</Label><Input id="category" v-model="form.category" /><InputError :message="form.errors.category" /></div>
                    <div class="space-y-2"><Label for="is_active">Status</Label><select id="is_active" v-model="form.is_active" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option :value="true">Active</option><option :value="false">Inactive</option></select></div>
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" placeholder="Grade 9" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" placeholder="G9" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="category">Category</Label><Input id="category" v-model="form.category" placeholder="Junior Secondary" /><InputError :message="form.errors.category" /></div>
                    <div class="space-y-2"><Label for="level_order">Level Order</Label><Input id="level_order" v-model="form.level_order" type="number" min="1" /><InputError :message="form.errors.level_order" /></div>
                    <div class="space-y-2"><Label for="minimum_age">Minimum Age</Label><Input id="minimum_age" v-model="form.minimum_age" type="number" min="1" /><InputError :message="form.errors.minimum_age" /></div>
                    <div class="space-y-2"><Label for="maximum_age">Maximum Age</Label><Input id="maximum_age" v-model="form.maximum_age" type="number" min="1" /><InputError :message="form.errors.maximum_age" /></div>
                    <div class="space-y-2"><Label for="is_active">Status</Label><select id="is_active" v-model="form.is_active" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option :value="true">Active</option><option :value="false">Inactive</option></select></div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child><Link href="/grades">Cancel</Link></Button>
                    <Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><PlusSquare v-else class="mr-2 h-4 w-4" />Save Grade</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
