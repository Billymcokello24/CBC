<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, PlusSquare, Home, ChevronRight } from 'lucide-vue-next';
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
    is_bulk: false,
    names: '',
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
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <div class="flex items-start gap-4 px-1">
                <Button variant="outline" size="icon" class="mt-1 flex h-8 w-8 shrink-0 rounded-xl md:h-10 md:w-10" as-child>
                    <Link href="/streams"><ArrowLeft class="h-4 w-4 md:h-5 md:w-5" /></Link>
                </Button>
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Streams</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Add Stream</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Add Stream
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Create a new school stream and link it to classes
                    </p>
                </div>
            </div>
            <form
                @submit.prevent="submit"
                class="space-y-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
            >
                <div class="flex items-center gap-2 border-b pb-4">
                    <input
                        type="checkbox"
                        id="is_bulk"
                        v-model="form.is_bulk"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                    />
                    <Label
                        for="is_bulk"
                        class="cursor-pointer text-base font-semibold"
                        >Bulk Creation Mode</Label
                    >
                </div>

                <div v-if="form.is_bulk" class="grid gap-6">
                    <div class="space-y-2">
                        <Label for="names"
                            >Stream Names (comma separated)</Label
                        >
                        <textarea
                            id="names"
                            v-model="form.names"
                            placeholder="North, South, East, West"
                            class="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        ></textarea>
                        <InputError :message="form.errors.names" />
                    </div>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="bulk_capacity">Default Capacity</Label
                            ><Input
                                id="bulk_capacity"
                                v-model="form.capacity"
                                type="number"
                                min="1"
                            /><InputError :message="form.errors.capacity" />
                        </div>
                        <div class="space-y-2">
                            <Label for="bulk_is_active">Status</Label
                            ><select
                                id="bulk_is_active"
                                v-model="form.is_active"
                                class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                            >
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">Name</Label
                        ><Input
                            id="name"
                            v-model="form.name"
                            placeholder="West"
                        /><InputError :message="form.errors.name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="code">Code</Label
                        ><Input
                            id="code"
                            v-model="form.code"
                            placeholder="WST"
                        /><InputError :message="form.errors.code" />
                    </div>
                    <div class="space-y-2">
                        <Label for="capacity">Capacity</Label
                        ><Input
                            id="capacity"
                            v-model="form.capacity"
                            type="number"
                            min="1"
                        /><InputError :message="form.errors.capacity" />
                    </div>
                    <div class="space-y-2">
                        <Label for="is_active">Status</Label
                        ><select
                            id="is_active"
                            v-model="form.is_active"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        >
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child
                        ><Link href="/streams">Cancel</Link></Button
                    >
                    <Button type="submit" :disabled="form.processing"
                        ><Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        /><PlusSquare v-else class="mr-2 h-4 w-4" />Save
                        Stream</Button
                    >
                </div>
            </form>
        </div>
    </AppLayout>
</template>
