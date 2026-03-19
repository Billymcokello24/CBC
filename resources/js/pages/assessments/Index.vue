<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, Plus, Search, Filter, Calendar, BookOpen, Users, TrendingUp, MoreHorizontal, Eye, Edit, FileText } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
];
const props = defineProps<{
    assessments: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
    stats?: {
        total: number;
        thisWeek: number;
        pendingGrading: number;
        avgScore: number;
    };
}>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        published: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        scheduled: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        in_progress: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        grading: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    };
    return colors[status] || colors.draft;
};
</script>
<template>
    <Head title="Assessments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                        <ClipboardList class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Assessments</h1>
                        <p class="text-muted-foreground">Manage CBC assessments, tests and grading</p>
                    </div>
                </div>
                <Button as-child><Link href="/assessments/create"><Plus class="mr-2 h-4 w-4" />Create Assessment</Link></Button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-amber-50 to-amber-100/50 p-4 dark:from-amber-950/50 dark:to-amber-900/30">
                    <div class="flex items-center gap-3"><ClipboardList class="h-5 w-5 text-amber-600" /><span class="text-sm text-muted-foreground font-medium">Total Assessments</span></div>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ props.stats?.total ?? 0 }}</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="flex items-center gap-3"><Calendar class="h-5 w-5 text-blue-600" /><span class="text-sm text-muted-foreground font-medium">This Week</span></div>
                    <p class="mt-2 text-3xl font-bold text-blue-600">{{ props.stats?.thisWeek ?? 0 }}</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-purple-50 to-purple-100/50 p-4 dark:from-purple-950/50 dark:to-purple-900/30">
                    <div class="flex items-center gap-3"><FileText class="h-5 w-5 text-purple-600" /><span class="text-sm text-muted-foreground font-medium">Pending Grading</span></div>
                    <p class="mt-2 text-3xl font-bold text-purple-600">{{ props.stats?.pendingGrading ?? 0 }}</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="flex items-center gap-3"><TrendingUp class="h-5 w-5 text-green-600" /><span class="text-sm text-muted-foreground font-medium">Avg. Score</span></div>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ props.stats?.avgScore ?? 0 }}%</p>
                </div>
            </div>
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input placeholder="Search assessments..." class="pl-9" />
                </div>
                <Button variant="outline" size="sm"><Filter class="mr-2 h-4 w-4" />Filters</Button>
            </div>
            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Assessment</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Subject</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Type</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Date</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Marks</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="assessments.data.length === 0">
                                <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No assessments found.</td>
                            </tr>
                            <tr v-for="assessment in assessments.data" :key="assessment.id" class="border-b hover:bg-muted/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 font-bold capitalize">{{ assessment.title }}</div>
                                    <div class="text-xs text-muted-foreground">{{ assessment.teacher?.name }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium">{{ assessment.subject?.name }}</td>
                                <td class="px-4 py-3 text-sm font-bold">{{ assessment.class?.name }}</td>
                                <td class="px-4 py-3 font-bold"><Badge variant="outline">{{ assessment.assessment_type?.name }}</Badge></td>
                                <td class="px-4 py-3 text-sm font-bold">{{ assessment.assessment_date }}</td>
                                <td class="px-4 py-3 text-center font-bold">
                                    {{ assessment.total_marks }}
                                </td>
                                <td class="px-4 py-3"><span :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider', getStatusColor(assessment.status)]">{{ assessment.status }}</span></td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4 text-indigo-600" /></Button></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/assessments/${assessment.id}`" class="flex w-full items-center"><Eye class="mr-2 h-4 w-4" />View</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/assessments/${assessment.id}/edit`" class="flex w-full items-center"><Edit class="mr-2 h-4 w-4" />Edit</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/assessments/${assessment.id}/grading`" class="flex w-full items-center"><FileText class="mr-2 h-4 w-4" />Grade</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
