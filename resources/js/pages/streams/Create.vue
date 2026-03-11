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
    { title: 'Streams', href: '/streams' },
    { title: 'Add Stream', href: '/streams/create' },
];

const form = useForm({
    name: '',
    code: '',
    capacity: '',
    is_active: true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        capacity: data.capacity ? Number(data.capacity) : null,
        is_active: Boolean(data.is_active),
    })).post('/streams');
};
</script>

<template>
    <Head title="Add Stream" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child><Link href="/streams"><ArrowLeft class="h-5 w-5" /></Link></Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Add Stream</h1>
                    <p class="text-muted-foreground">Create a new school stream and link it to classes</p>
                </div>
            </div>
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" placeholder="West" /><InputError :message="form.errors.name" /></div>
                    <div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" placeholder="WST" /><InputError :message="form.errors.code" /></div>
                    <div class="space-y-2"><Label for="capacity">Capacity</Label><Input id="capacity" v-model="form.capacity" type="number" min="1" /><InputError :message="form.errors.capacity" /></div>
                    <div class="space-y-2"><Label for="is_active">Status</Label><select id="is_active" v-model="form.is_active" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option :value="true">Active</option><option :value="false">Inactive</option></select></div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child><Link href="/streams">Cancel</Link></Button>
                    <Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><PlusSquare v-else class="mr-2 h-4 w-4" />Save Stream</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
