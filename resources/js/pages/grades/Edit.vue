<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { GraduationCap } from 'lucide-vue-next';

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
        <div class="w-full py-12 px-4 sm:px-6 lg:px-8 space-y-12 animate-in fade-in duration-500">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 shadow-lg text-white">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Grade</h1>
                            <Badge variant="secondary" class="rounded-full px-3 py-0.5 h-5 text-[10px] font-bold bg-blue-50 text-blue-600 border-none uppercase">Configuration</Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Update grade setup and progression metadata for <span class="font-bold text-slate-900">{{ grade.name }}</span>.</p>
                    </div>
                </div>
                <Button variant="outline" as-child class="h-11 rounded-xl border-slate-200 text-xs font-bold uppercase tracking-wider px-6 hover:bg-slate-50 transition-all shadow-sm">
                    <Link href="/grades"><ArrowLeft class="mr-2 h-4 w-4" />Back to List</Link>
                </Button>
            </div>

            <div class="max-w-4xl">
                <form @submit.prevent="submit" class="space-y-8">
                    <Card class="rounded-2xl border-slate-100 shadow-sm overflow-hidden">
                        <CardHeader class="border-b border-slate-50 bg-slate-50/30 py-6 px-8">
                            <CardTitle class="text-lg font-bold text-slate-900">Grade Information</CardTitle>
                            <CardDescription>Essential details for academic level identification.</CardDescription>
                        </CardHeader>
                        <CardContent class="p-8">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label for="name" class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Grade Name</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g. GRADE 1" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600" />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-3">
                                    <Label for="code" class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Grade Code</Label>
                                    <Input id="code" v-model="form.code" placeholder="e.g. G1" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600 uppercase" />
                                    <InputError :message="form.errors.code" />
                                </div>
                                <div class="space-y-3">
                                    <Label for="category" class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Category</Label>
                                    <Input id="category" v-model="form.category" placeholder="e.g. Primary School" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600" />
                                    <InputError :message="form.errors.category" />
                                </div>
                                <div class="space-y-3">
                                    <Label for="level_order" class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Progression Level (Order)</Label>
                                    <Input id="level_order" v-model="form.level_order" type="number" min="1" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600" />
                                    <InputError :message="form.errors.level_order" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border-slate-100 shadow-sm overflow-hidden">
                        <CardHeader class="border-b border-slate-50 bg-slate-50/30 py-6 px-8">
                            <CardTitle class="text-lg font-bold text-slate-900">Age Requirements</CardTitle>
                            <CardDescription>Define the recommended age range for this grade.</CardDescription>
                        </CardHeader>
                        <CardContent class="p-8">
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label for="minimum_age" class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Minimum Age (Years)</Label>
                                    <Input id="minimum_age" v-model="form.minimum_age" type="number" min="1" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600" />
                                    <InputError :message="form.errors.minimum_age" />
                                </div>
                                <div class="space-y-3">
                                    <Label for="maximum_age" class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Maximum Age (Years)</Label>
                                    <Input id="maximum_age" v-model="form.maximum_age" type="number" min="1" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600" />
                                    <InputError :message="form.errors.maximum_age" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border-slate-100 shadow-sm overflow-hidden">
                        <CardContent class="p-8 flex items-center justify-between bg-slate-50/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white border border-slate-200 shadow-sm">
                                    <component :is="form.is_active ? ShieldCheck : ShieldOff" class="h-5 w-5" :class="form.is_active ? 'text-emerald-500' : 'text-slate-400'" />
                                </div>
                                <div>
                                    <Label for="is_active_toggle" class="text-sm font-bold text-slate-900">Active Status</Label>
                                    <p class="text-xs text-slate-500">{{ form.is_active ? 'Grade is visible and available for enrollment' : 'Grade is hidden from enrollment options' }}</p>
                                </div>
                            </div>
                            <select id="is_active" v-model="form.is_active" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all shadow-sm min-w-[140px]">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </CardContent>
                    </Card>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <Button type="button" variant="ghost" as-child class="h-12 px-8 rounded-xl text-slate-500 font-bold hover:bg-slate-50 transition-all">
                            <Link href="/grades">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-200 transition-all">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
