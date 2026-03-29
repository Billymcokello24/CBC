<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import {
    ClipboardList, ArrowRight, CheckCircle2, AlertCircle, Clock
} from 'lucide-vue-next';

const props = defineProps<{
    assignments: Array<{
        id: number;
        title: string;
        subject: string | null;
        class: string | null;
        teacher: string | null;
        assignment_type: string;
        due_date: string | null;
        total_marks: number | null;
        is_overdue: boolean;
        submissions_count: number;
        has_unsubmitted: boolean;
    }>;
    children: Array<{
        id: number;
        name: string;
        class: string | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'Assignments', href: '/guardian/assignments' },
];

const filter = ref('all');

const filteredAssignments = ref(props.assignments);
</script>

<template>
    <Head title="All Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 md:p-8 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-200">
                        <ClipboardList class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">All Assignments</h1>
                        <p class="text-sm text-muted-foreground">Assignments across all your children's classes.</p>
                    </div>
                </div>
                <Badge variant="outline" class="rounded-xl text-xs font-bold px-3 py-1.5">
                    {{ assignments.length }} Total
                </Badge>
            </div>

            <!-- Children Quick Links -->
            <div class="flex gap-3 flex-wrap">
                <Link
                    v-for="child in children" :key="child.id"
                    :href="`/guardian/children/${child.id}`"
                    class="rounded-xl border bg-card px-4 py-2.5 text-xs font-bold text-foreground hover:border-blue-200 hover:bg-blue-50 transition-all"
                >
                    {{ child.name }} · <span class="text-blue-600">{{ child.class || 'Unassigned' }}</span>
                </Link>
            </div>

            <!-- Assignments List -->
            <div v-if="assignments.length === 0" class="rounded-2xl border border-dashed p-16 text-center">
                <ClipboardList class="h-12 w-12 mx-auto mb-4 text-muted-foreground/20" />
                <h3 class="text-lg font-bold text-foreground">No Assignments</h3>
                <p class="text-sm text-muted-foreground mt-1">No published assignments found for your children's classes.</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="a in assignments" :key="a.id"
                    class="rounded-xl border bg-card p-5 hover:shadow-md hover:border-blue-200 transition-all"
                    :class="a.is_overdue && a.has_unsubmitted ? 'border-l-4 border-l-rose-500' : ''"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                :class="!a.has_unsubmitted
                                    ? 'bg-emerald-100 text-emerald-600'
                                    : a.is_overdue ? 'bg-rose-100 text-rose-600' : 'bg-muted text-muted-foreground'">
                                <CheckCircle2 v-if="!a.has_unsubmitted" class="h-5 w-5" />
                                <AlertCircle v-else-if="a.is_overdue" class="h-5 w-5" />
                                <ClipboardList v-else class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-foreground truncate">{{ a.title }}</h3>
                                <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground flex-wrap">
                                    <span class="font-semibold text-blue-600">{{ a.subject }}</span>
                                    <span class="h-1 w-1 bg-border rounded-full"></span>
                                    <span>{{ a.class }}</span>
                                    <span class="h-1 w-1 bg-border rounded-full"></span>
                                    <span>{{ a.teacher }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 flex-shrink-0">
                            <div class="text-right hidden sm:block">
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                    <Clock class="h-3 w-3" />
                                    <span :class="a.is_overdue ? 'text-rose-600 font-bold' : ''">{{ a.due_date }}</span>
                                </div>
                                <p v-if="a.total_marks" class="text-[10px] text-muted-foreground mt-0.5">{{ a.total_marks }} marks</p>
                            </div>
                            <Badge v-if="!a.has_unsubmitted" class="bg-emerald-500 text-white rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0">All Submitted</Badge>
                            <Badge v-else-if="a.is_overdue" class="bg-rose-500 text-white rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0">Overdue</Badge>
                            <Badge v-else class="bg-amber-500 text-white rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase border-0">Pending</Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
