<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ competency: { id: number; name: string; code: string; description: string | null; category: string | null; display_order: number; is_active: boolean } }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Curriculum', href: '/curriculum' }, { title: 'Competencies', href: '/curriculum/competencies' }, { title: 'Edit Competency', href: `/curriculum/competencies/${props.competency.id}/edit` }];
const form = useForm({ name: props.competency.name, code: props.competency.code, description: props.competency.description ?? '', category: props.competency.category ?? '', display_order: props.competency.display_order, is_active: props.competency.is_active });
const submit = () => form.transform((data) => ({ ...data, display_order: Number(data.display_order), is_active: Boolean(data.is_active) })).put(`/curriculum/competencies/${props.competency.id}`);
</script>
<template><Head title="Edit Competency" /><AppLayout :breadcrumbs="breadcrumbs"><div class="flex h-full flex-1 flex-col gap-6 p-6"><div class="flex items-center gap-4"><Button variant="ghost" size="icon" as-child><Link href="/curriculum/competencies"><ArrowLeft class="h-5 w-5" /></Link></Button><div><h1 class="text-2xl font-bold tracking-tight">Edit Competency</h1><p class="text-muted-foreground">Update competency details</p></div></div><form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6"><div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"><div class="space-y-2"><Label for="name">Name</Label><Input id="name" v-model="form.name" /><InputError :message="form.errors.name" /></div><div class="space-y-2"><Label for="code">Code</Label><Input id="code" v-model="form.code" /><InputError :message="form.errors.code" /></div><div class="space-y-2"><Label for="category">Category</Label><Input id="category" v-model="form.category" /><InputError :message="form.errors.category" /></div></div><div class="flex justify-end gap-3"><Button type="button" variant="outline" as-child><Link href="/curriculum/competencies">Cancel</Link></Button><Button type="submit" :disabled="form.processing"><Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" /><Save v-else class="mr-2 h-4 w-4" />Save Changes</Button></div></form></div></AppLayout></template>
