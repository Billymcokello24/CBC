<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ClipboardList,
    Search,
    Filter,
    FileText,
    CheckCircle2,
    AlertCircle,
    LayoutGrid,
    MoreHorizontal,
    Eye,
    BookOpen,
    Clock,
    Zap,
    ArrowRight,
    Target,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessments: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments Hub', href: '/assessments' },
    { title: 'Grading Terminal', href: '/assessments/grading' },
];

const searchQuery = ref('');

const getStatusColor = (status: string) => {
    switch (status) {
        case 'published':
            return 'bg-blue-600 text-white shadow-sm';
        case 'in_progress':
            return 'bg-amber-500 text-white';
        case 'completed':
            return 'bg-emerald-600 text-white shadow-sm';
        default:
            return 'bg-slate-400 text-white';
    }
};
</script>

<template>
    <Head title="Grading Terminal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Grading Terminal</h1>
                    <p class="text-xs text-muted-foreground">Capture student performance metrics and feedback across all testing nodes</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <Link href="/assessments">
                            <ClipboardList class="mr-2 h-4 w-4 text-primary" />All Assessments
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md">
                    <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <Clock class="h-24 w-24 text-primary" />
                    </div>
                     <p class="text-xs font-medium text-muted-foreground">Awaiting Marks</p>
                     <div class="mt-2 flex items-baseline gap-2">
                         <h3 class="text-2xl font-bold tracking-tight">{{ assessments.data.length }} Active Tests</h3>
                     </div>
                </div>
                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md">
                     <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                         <Target class="h-24 w-24 text-primary" />
                     </div>
                     <p class="text-xs font-medium text-muted-foreground">Target Sample</p>
                     <div class="mt-2 flex items-baseline gap-2">
                         <h3 class="text-2xl font-bold tracking-tight">100% Student Body</h3>
                     </div>
                </div>
                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md">
                     <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                         <Zap class="h-24 w-24 text-primary" />
                     </div>
                     <p class="text-xs font-medium text-muted-foreground">Engine Status</p>
                     <div class="mt-2 flex items-baseline gap-2">
                         <h3 class="text-2xl font-bold tracking-tight text-primary">Live Sync</h3>
                     </div>
                </div>
            </div>

            <!-- Search -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                 <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-primary" />
                        <span class="text-xs font-semibold text-foreground tracking-tight">Search Directory</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative w-full max-w-md">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                        <Input v-model="searchQuery" placeholder="Quick find by test title or class..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background" />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="assessments.data.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border bg-card py-24">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-muted/20">
                    <LayoutGrid class="h-8 w-8 text-muted-foreground/30" />
                </div>
                <h3 class="mb-2 text-lg font-bold text-foreground">No Assessments to Grade</h3>
                <p class="max-w-sm text-center text-xs text-muted-foreground">There are currently no published assessments awaiting data entry for your assigned cluster.</p>
                <Button variant="outline" as-child class="mt-6 h-10 rounded-lg border-border bg-card px-6 text-xs font-semibold hover:bg-muted">
                    <Link href="/assessments">Return to Dashboard</Link>
                </Button>
            </div>

            <!-- Cards Grid -->
            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="assessment in assessments.data" :key="assessment.id" class="group relative flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-primary/20">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary transition-all group-hover:bg-primary group-hover:text-white">{{ assessment.subject?.name.substring(0, 2) }}</div>
                            <Badge :class="[getStatusColor(assessment.status), 'rounded-lg border-0 px-2.5 py-0.5 text-[10px] font-semibold']">
                                {{ assessment.status }}
                            </Badge>
                        </div>
                        <h3 class="text-sm font-bold text-foreground capitalize transition-colors group-hover:text-primary leading-tight">{{ assessment.title }}</h3>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <Badge variant="outline" class="rounded-lg border-border bg-muted/10 px-2 py-0.5 text-[10px] font-semibold">GRADE {{ assessment.class?.name }}</Badge>
                            <Badge variant="outline" class="rounded-lg border-border bg-muted/10 px-2 py-0.5 text-[10px] font-semibold">{{ assessment.subject?.name }}</Badge>
                        </div>
                        <div class="space-y-2 border-t border-border/50 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-medium text-muted-foreground">Entry Progress</span>
                                <span class="text-[10px] font-semibold text-primary">--%</span>
                            </div>
                            <div class="h-1 w-full overflow-hidden rounded-full bg-muted/20">
                                <div class="h-full rounded-full bg-primary transition-all duration-1000" style="width: 5%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto border-t border-border/50 bg-muted/5 px-6 py-3 flex items-center gap-3">
                        <Button class="flex-1 h-9 rounded-lg bg-primary text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all" as-child>
                            <Link :href="route('assessments.grading', { assessment: assessment.id })">Grade <Plus class="ml-1.5 h-3.5 w-3.5" /></Link>
                        </Button>
                        <Button variant="outline" class="h-9 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                            <Link :href="`/assessments/${assessment.id}`"><Eye class="mr-1.5 h-3.5 w-3.5" />Preview</Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex h-14 items-center justify-between rounded-xl border border-border bg-card px-6">
                <p class="text-xs font-medium text-muted-foreground">Active Index: {{ assessments.data.length }} Assessments</p>
            </div>
        </div>
    </AppLayout>
</template>
