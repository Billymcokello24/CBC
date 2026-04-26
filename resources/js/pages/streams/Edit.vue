<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Rows3, ShieldCheck, ShieldOff } from 'lucide-vue-next';

const props = defineProps<{
    stream: {
        id: number;
        name: string;
        code: string;
        capacity: number | null;
        is_active: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Streams', href: '/streams' },
    { title: 'Edit Stream', href: `/streams/${props.stream.id}/edit` },
];

const form = useForm({
    name: props.stream.name,
    code: props.stream.code,
    capacity: props.stream.capacity ?? '',
    is_active: props.stream.is_active,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        capacity: data.capacity ? Number(data.capacity) : null,
        is_active: Boolean(data.is_active),
    })).put(`/streams/${props.stream.id}`);
};
</script>

<template>
    <Head title="Edit Stream" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="w-full animate-in space-y-12 px-4 py-12 duration-500 fade-in sm:px-6 lg:px-8"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg"
                    >
                        <Rows3 class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-900"
                            >
                                Edit Stream
                            </h1>
                            <Badge
                                variant="secondary"
                                class="h-5 rounded-full border-none bg-blue-50 px-3 py-0.5 text-xs font-bold text-blue-600 uppercase"
                                >School Structure</Badge
                            >
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Update global stream setup and operational status
                            for
                            <span class="font-bold text-slate-900">{{
                                stream.name
                            }}</span
                            >.
                        </p>
                    </div>
                </div>
                <Button
                    variant="outline"
                    as-child
                    class="h-11 rounded-xl border-slate-200 px-6 text-xs font-bold tracking-wider uppercase shadow-sm transition-all hover:bg-slate-50"
                >
                    <Link href="/streams"
                        ><ArrowLeft class="mr-2 h-4 w-4" />Back to List</Link
                    >
                </Button>
            </div>

            <div class="max-w-4xl">
                <form @submit.prevent="submit" class="space-y-8">
                    <Card
                        class="overflow-hidden rounded-2xl border-slate-100 shadow-sm"
                    >
                        <CardHeader
                            class="border-b border-slate-50 bg-slate-50/30 px-8 py-6"
                        >
                            <CardTitle class="text-lg font-bold text-slate-900"
                                >Stream Details</CardTitle
                            >
                            <CardDescription
                                >Primary identification and capacity limits for
                                this academic stream.</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="p-8">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label
                                        for="name"
                                        class="ml-1 text-xs font-bold tracking-wider text-slate-500 uppercase"
                                        >Stream Name</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g. RED"
                                        class="h-12 rounded-xl border-slate-200 uppercase focus:ring-blue-600"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-3">
                                    <Label
                                        for="code"
                                        class="ml-1 text-xs font-bold tracking-wider text-slate-500 uppercase"
                                        >Stream Code</Label
                                    >
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        placeholder="e.g. R"
                                        class="h-12 rounded-xl border-slate-200 uppercase focus:ring-blue-600"
                                    />
                                    <InputError :message="form.errors.code" />
                                </div>
                                <div class="space-y-3">
                                    <Label
                                        for="capacity"
                                        class="ml-1 text-xs font-bold tracking-wider text-slate-500 uppercase"
                                        >General Capacity</Label
                                    >
                                    <Input
                                        id="capacity"
                                        v-model="form.capacity"
                                        type="number"
                                        min="1"
                                        placeholder="e.g. 40"
                                        class="h-12 rounded-xl border-slate-200 focus:ring-blue-600"
                                    />
                                    <InputError
                                        :message="form.errors.capacity"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="overflow-hidden rounded-2xl border-slate-100 shadow-sm"
                    >
                        <CardContent
                            class="flex items-center justify-between bg-slate-50/50 p-8"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white shadow-sm"
                                >
                                    <component
                                        :is="
                                            form.is_active
                                                ? ShieldCheck
                                                : ShieldOff
                                        "
                                        class="h-5 w-5"
                                        :class="
                                            form.is_active
                                                ? 'text-emerald-500'
                                                : 'text-slate-400'
                                        "
                                    />
                                </div>
                                <div>
                                    <Label
                                        for="is_active_toggle"
                                        class="text-sm font-bold text-slate-900"
                                        >Operational Status</Label
                                    >
                                    <p class="text-xs text-slate-500">
                                        {{
                                            form.is_active
                                                ? 'Stream is active across all grades'
                                                : 'Stream is currently deactivated'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <select
                                id="is_active"
                                v-model="form.is_active"
                                class="h-11 min-w-[140px] rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold tracking-wider uppercase shadow-sm transition-all focus:ring-2 focus:ring-blue-600 focus:outline-none"
                            >
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </CardContent>
                    </Card>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <Button
                            type="button"
                            variant="ghost"
                            as-child
                            class="h-12 rounded-xl px-8 font-bold text-slate-500 transition-all hover:bg-slate-50"
                        >
                            <Link href="/streams">Cancel</Link>
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-12 rounded-xl bg-blue-600 px-10 font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
