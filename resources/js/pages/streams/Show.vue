<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Rows3, School } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stream: {
        id: number;
        name: string;
        code: string;
        capacity: number | null;
        is_active: boolean;
        lead_name: string | null;
    };
    classes: Array<{
        id: number;
        name: string;
        code: string;
        grade: string | null;
        academic_year: string | null;
        teacher: string | null;
        students_count: number;
        capacity: number | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Streams', href: '/streams' },
    { title: props.stream.name, href: `/streams/${props.stream.id}` },
];
</script>

<template>
    <Head :title="stream.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full py-12 px-4 sm:px-6 lg:px-8 space-y-12 animate-in fade-in duration-500">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 shadow-lg text-white">
                        <Rows3 class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ stream.name }}</h1>
                            <Badge variant="secondary" class="rounded-full px-3 py-0.5 h-5 text-[10px] font-bold bg-blue-50 text-blue-600 border-none uppercase">{{ stream.code }}</Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Management overview for academic stream.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="h-11 rounded-xl border-slate-200 text-xs font-bold uppercase tracking-wider px-6 hover:bg-slate-50 transition-all shadow-sm" as-child><Link :href="`/streams/${stream.id}/edit`">Stream Settings</Link></Button>
                    <Button variant="ghost" class="h-11 rounded-xl text-slate-500 hover:text-slate-700 text-xs font-bold uppercase tracking-wider px-4" as-child><Link href="/streams">Back to Streams</Link></Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Stream Head</p>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-xs font-bold text-blue-600">{{ stream.lead_name?.charAt(0) || '?' }}</div>
                        <p class="font-bold text-slate-900">{{ stream.lead_name || 'Not assigned' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Class Capacity</p>
                    <p class="text-3xl font-bold text-slate-900">{{ stream.capacity ?? '—' }}</p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Total Classes</p>
                    <p class="text-3xl font-bold text-blue-600">{{ classes.length }}</p>
                </div>
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Stream Status</p>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="h-2 w-2 rounded-full" :class="stream.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></div>
                        <span class="text-sm font-bold" :class="stream.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ stream.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>

            <!-- Classes Section -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Classes in this Stream</h2>
                    <p class="text-sm text-muted-foreground mt-1">Open any class to manage its learners and operations.</p>
                </div>
                
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="classroom in classes" :key="classroom.id" :href="`/classes/${classroom.id}`" class="group rounded-2xl border bg-white p-6 transition-all hover:shadow-lg hover:border-blue-100 border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="flex items-start justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase">{{ classroom.name }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ classroom.code }}<span v-if="classroom.grade" class="ml-2">• {{ classroom.grade }}</span></p>
                            </div>
                            <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                <School class="h-5 w-5" />
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl mb-6">
                            <div class="h-7 w-7 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600 uppercase">{{ classroom.teacher?.charAt(0) || '?' }}</div>
                            <p class="text-[10px] font-bold text-slate-600 uppercase truncate">Teacher: {{ classroom.teacher || 'Not assigned' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-xl border border-slate-100 p-4 bg-white shadow-sm">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1">Learners</p>
                                <p class="text-base font-bold text-blue-600">{{ classroom.students_count }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4 bg-white shadow-sm">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1">Capacity</p>
                                <p class="text-base font-bold text-slate-900">{{ classroom.capacity ?? '—' }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
