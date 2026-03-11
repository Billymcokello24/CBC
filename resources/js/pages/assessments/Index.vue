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
const assessments = ref([
    { id: 1, title: 'Mathematics CAT 1', subject: 'Mathematics', class: 'Grade 5A', date: '2026-03-15', type: 'CAT', status: 'published', submissions: 35, total: 38 },
    { id: 2, title: 'English Essay Writing', subject: 'English', class: 'Grade 6B', date: '2026-03-14', type: 'Assignment', status: 'in_progress', submissions: 28, total: 40 },
    { id: 3, title: 'Science Practical', subject: 'Science', class: 'Grade 5B', date: '2026-03-13', type: 'Practical', status: 'completed', submissions: 32, total: 32 },
    { id: 4, title: 'Kiswahili Insha', subject: 'Kiswahili', class: 'Grade 4A', date: '2026-03-12', type: 'Assignment', status: 'grading', submissions: 35, total: 35 },
    { id: 5, title: 'Social Studies Project', subject: 'Social Studies', class: 'Grade 6A', date: '2026-03-18', type: 'Project', status: 'draft', submissions: 0, total: 42 },
]);
const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        published: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
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
                    <div class="flex items-center gap-3"><ClipboardList class="h-5 w-5 text-amber-600" /><span class="text-sm text-muted-foreground">Total Assessments</span></div>
                    <p class="mt-2 text-3xl font-bold text-amber-600">156</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="flex items-center gap-3"><Calendar class="h-5 w-5 text-blue-600" /><span class="text-sm text-muted-foreground">This Week</span></div>
                    <p class="mt-2 text-3xl font-bold text-blue-600">12</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-purple-50 to-purple-100/50 p-4 dark:from-purple-950/50 dark:to-purple-900/30">
                    <div class="flex items-center gap-3"><FileText class="h-5 w-5 text-purple-600" /><span class="text-sm text-muted-foreground">Pending Grading</span></div>
                    <p class="mt-2 text-3xl font-bold text-purple-600">8</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="flex items-center gap-3"><TrendingUp class="h-5 w-5 text-green-600" /><span class="text-sm text-muted-foreground">Avg. Score</span></div>
                    <p class="mt-2 text-3xl font-bold text-green-600">72%</p>
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
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Submissions</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="assessment in assessments" :key="assessment.id" class="border-b hover:bg-muted/50">
                                <td class="px-4 py-3 font-medium">{{ assessment.title }}</td>
                                <td class="px-4 py-3 text-sm">{{ assessment.subject }}</td>
                                <td class="px-4 py-3 text-sm">{{ assessment.class }}</td>
                                <td class="px-4 py-3"><Badge variant="outline">{{ assessment.type }}</Badge></td>
                                <td class="px-4 py-3 text-sm">{{ assessment.date }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-medium">{{ assessment.submissions }}</span><span class="text-muted-foreground">/{{ assessment.total }}</span>
                                </td>
                                <td class="px-4 py-3"><span :class="['rounded-full px-2 py-1 text-xs font-medium', getStatusColor(assessment.status)]">{{ assessment.status }}</span></td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View</DropdownMenuItem>
                                            <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Edit</DropdownMenuItem>
                                            <DropdownMenuItem><FileText class="mr-2 h-4 w-4" />Grade</DropdownMenuItem>
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
