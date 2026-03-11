<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Layers3 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ strand: { id: number; name: string; code: string; description: string | null; display_order: number; is_active: boolean; subject_name: string; grade_name: string } }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Curriculum', href: '/curriculum' }, { title: 'Strands', href: '/curriculum/strands' }, { title: props.strand.name, href: `/curriculum/strands/${props.strand.id}` }];
</script>
<template><Head :title="strand.name" /><AppLayout :breadcrumbs="breadcrumbs"><div class="flex h-full flex-1 flex-col gap-6 p-6"><div class="flex items-center justify-between gap-4"><div class="flex items-center gap-4"><div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10"><Layers3 class="h-6 w-6 text-amber-600" /></div><div><h1 class="text-2xl font-bold tracking-tight">{{ strand.name }}</h1><p class="text-muted-foreground">{{ strand.code }} • {{ strand.subject_name }} • {{ strand.grade_name }}</p></div></div><div class="flex gap-2"><Button variant="outline" as-child><Link :href="`/curriculum/strands/${strand.id}/edit`">Edit</Link></Button><Button variant="outline" as-child><Link href="/curriculum/strands">Back</Link></Button></div></div><div class="grid gap-4 sm:grid-cols-3"><div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Subject</div><div class="mt-1 text-lg font-semibold">{{ strand.subject_name }}</div></div><div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Grade</div><div class="mt-1 text-lg font-semibold">{{ strand.grade_name }}</div></div><div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Status</div><div class="mt-2"><Badge>{{ strand.is_active ? 'Active' : 'Inactive' }}</Badge></div></div></div></div></AppLayout></template>
