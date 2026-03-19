<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookCopy } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ 
    subject: { 
        id: number; 
        learning_area: string | null; 
        department: { id: number; name: string; code: string; head_of_department: string | null } | null;
        name: string; 
        code: string; 
        description: string | null; 
        subject_type: string; 
        is_examinable: boolean; 
        display_order: number; 
        is_active: boolean; 
        strands: Array<{ id: number; name: string; code: string; is_active: boolean }> 
    } 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, 
    { title: 'Curriculum', href: '/curriculum' }, 
    { title: 'Subjects', href: '/curriculum/subjects' }, 
    { title: props.subject.name, href: `/curriculum/subjects/${props.subject.id}` }
];
</script>

<template>
    <Head :title="subject.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10"><BookCopy class="h-6 w-6 text-blue-600" /></div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ subject.name }}</h1>
                        <p class="text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || '—' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child><Link :href="`/curriculum/subjects/${subject.id}/edit`">Edit</Link></Button>
                    <Button variant="outline" as-child><Link href="/curriculum/subjects">Back</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground font-medium">Department</div>
                    <div class="mt-1 text-lg font-bold text-indigo-600">{{ subject.department?.name || 'Unassigned' }}</div>
                    <div class="text-xs text-muted-foreground">
                        {{ subject.department?.code || '—' }}
                        <span v-if="subject.department?.head_of_department"> • HOD: {{ subject.department.head_of_department }}</span>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground font-medium">Type</div>
                    <div class="mt-1 text-lg font-semibold">{{ subject.subject_type }}</div>
                    <div class="text-xs text-muted-foreground">{{ subject.is_examinable ? 'Examinable' : 'Non-examinable' }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground font-medium">Status</div>
                    <div class="mt-2 text-lg font-semibold">
                        <Badge :variant="subject.is_active ? 'default' : 'secondary'" class="rounded-md">
                            {{ subject.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground font-medium">Strands</div>
                    <div class="mt-1 text-2xl font-bold text-purple-600">{{ subject.strands.length }}</div>
                    <div class="text-xs text-muted-foreground">Detailed learning outcomes</div>
                </div>
            </div>

            <div v-if="subject.description" class="rounded-xl border bg-card p-6">
                <h3 class="font-semibold mb-2">Description</h3>
                <p class="text-sm text-muted-foreground">{{ subject.description }}</p>
            </div>

            <div class="rounded-xl border bg-card overflow-hidden">
                <div class="bg-muted/50 px-6 py-4 border-b">
                    <h2 class="font-semibold">Strands & Topics</h2>
                </div>
                <div class="divide-y">
                    <div v-if="subject.strands.length === 0" class="p-8 text-center text-muted-foreground">
                        No strands linked to this subject yet.
                    </div>
                    <div v-for="strand in subject.strands" :key="strand.id" class="px-6 py-4 flex items-center justify-between group hover:bg-muted/30 transition-colors">
                        <div>
                            <div class="font-medium group-hover:text-primary transition-colors">{{ strand.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ strand.code }}</div>
                        </div>
                        <Badge variant="outline" :class="strand.is_active ? 'text-green-600 border-green-200 bg-green-50' : ''">
                            {{ strand.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
