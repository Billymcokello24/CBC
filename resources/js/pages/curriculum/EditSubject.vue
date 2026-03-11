<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ subject: { id: number; learning_area_id: number; name: string; code: string; description: string | null; subject_type: string; is_examinable: boolean; display_order: number; is_active: boolean }; learningAreas: Array<{ id: number; name: string }> }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Curriculum', href: '/curriculum' }, { title: 'Subjects', href: '/curriculum/subjects' }, { title: 'Edit Subject', href: `/curriculum/subjects/${props.subject.id}/edit` }];
const form = useForm({ learning_area_id: String(props.subject.learning_area_id), name: props.subject.name, code: props.subject.code, description: props.subject.description ?? '', subject_type: props.subject.subject_type, is_examinable: props.subject.is_examinable, display_order: props.subject.display_order, is_active: props.subject.is_active });
const submit = () => form.transform((data) => ({ ...data, learning_area_id: Number(data.learning_area_id), display_order: Number(data.display_order), is_examinable: Boolean(data.is_examinable), is_active: Boolean(data.is_active) })).put(`/curriculum/subjects/${props.subject.id}`);
</script>
<template><Head title="Edit Subject" /><AppLayout :breadcrumbs="breadcrumbs"><div class="flex h-full flex-1 flex-col gap-6 p-6"><div class="flex items-center gap-4"><Button variant="ghost" size="icon" as-child><Link href="/curriculum/subjects"><ArrowLeft class="h-5 w-5" /></Link></Button><div><h1 class="text-2xl font-bold tracking-tight">Edit Subject</h1><p class="text-muted-foreground">Update subject details</p></div></div><form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6"><div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"><div class="space-y-2"><Label for="learning_area_id">Learning Area</Label><select id="learning_area_id" v-model="form.learning_area_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm"><option v-for="area in learningAreas" :key="area.id" :value="String(area.id)">{{ area.name }}</option></select><InputError :message="form.errors.learning_area_id" /></div><div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" /><InputError :message="form.errors.name" /></div><div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" /><InputError :message="form.errors.code" /></div></div><div class="flex justify-end gap-3"><Button type="button" variant="outline" as-child><Link href="/curriculum/subjects">Cancel</Link></Button><Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><Save v-else class="mr-2 h-4 w-4" />Save Changes</Button></div></form></div></AppLayout></template>
