<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Download,
    FileText,
    TrendingUp,
    Users,
    GraduationCap,
    DollarSign,
    CalendarCheck,
    PieChart,
    LineChart,
    Activity,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
];
const reportCategories = [
    {
        title: 'Academic Reports',
        icon: GraduationCap,
        color: 'blue',
        reports: [
            {
                name: 'Student Performance',
                description: 'Individual and class performance analysis',
            },
            {
                name: 'Subject Analysis',
                description: 'Subject-wise performance breakdown',
            },
            {
                name: 'Report Cards',
                description: 'Generate student report cards',
            },
            {
                name: 'Competency Tracking',
                description: 'CBC competency achievement reports',
            },
        ],
    },
    {
        title: 'Attendance Reports',
        icon: CalendarCheck,
        color: 'green',
        reports: [
            {
                name: 'Daily Attendance',
                description: 'Daily attendance summary',
            },
            {
                name: 'Monthly Attendance',
                description: 'Monthly attendance trends',
            },
            {
                name: 'Absentee Report',
                description: 'Students with high absenteeism',
            },
            {
                name: 'Teacher Attendance',
                description: 'Staff attendance tracking',
            },
        ],
    },
    {
        title: 'Financial Reports',
        icon: DollarSign,
        color: 'amber',
        reports: [
            { name: 'Fee Collection', description: 'Fee collection summary' },
            { name: 'Outstanding Fees', description: 'Pending fee balances' },
            {
                name: 'Payment History',
                description: 'Payment transaction history',
            },
            {
                name: 'Expense Report',
                description: 'School expenses breakdown',
            },
        ],
    },
    {
        title: 'Administrative Reports',
        icon: Users,
        color: 'purple',
        reports: [
            {
                name: 'Student Enrollment',
                description: 'Enrollment statistics',
            },
            { name: 'Staff Directory', description: 'Complete staff listing' },
            { name: 'Class Statistics', description: 'Class-wise statistics' },
            {
                name: 'Demographics',
                description: 'Student demographics analysis',
            },
        ],
    },
];
const getColorClasses = (color: string) => {
    const colors: Record<string, { bg: string; text: string; iconBg: string }> =
        {
            blue: {
                bg: 'from-blue-50 to-blue-100/50 dark:from-blue-950/50 dark:to-blue-900/30',
                text: 'text-blue-600',
                iconBg: 'bg-blue-500/10',
            },
            green: {
                bg: 'from-green-50 to-green-100/50 dark:from-green-950/50 dark:to-green-900/30',
                text: 'text-green-600',
                iconBg: 'bg-green-500/10',
            },
            amber: {
                bg: 'from-amber-50 to-amber-100/50 dark:from-amber-950/50 dark:to-amber-900/30',
                text: 'text-amber-600',
                iconBg: 'bg-amber-500/10',
            },
            purple: {
                bg: 'from-purple-50 to-purple-100/50 dark:from-purple-950/50 dark:to-purple-900/30',
                text: 'text-purple-600',
                iconBg: 'bg-purple-500/10',
            },
        };
    return colors[color] || colors.blue;
};
</script>
<template>
    <Head title="Reports & Analytics" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10"
                    >
                        <BarChart3 class="h-6 w-6 text-rose-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Reports & Analytics
                        </h1>
                        <p class="text-muted-foreground">
                            Generate insights and comprehensive reports
                        </p>
                    </div>
                </div>
            </div>
            <!-- Quick Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="flex items-center gap-4 rounded-xl border bg-card p-4"
                >
                    <div class="rounded-lg bg-blue-500/10 p-3">
                        <TrendingUp class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Avg. Performance
                        </p>
                        <p class="text-2xl font-bold">72.5%</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 rounded-xl border bg-card p-4"
                >
                    <div class="rounded-lg bg-green-500/10 p-3">
                        <CalendarCheck class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Attendance Rate
                        </p>
                        <p class="text-2xl font-bold">94.5%</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 rounded-xl border bg-card p-4"
                >
                    <div class="rounded-lg bg-amber-500/10 p-3">
                        <DollarSign class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Fee Collection
                        </p>
                        <p class="text-2xl font-bold">86%</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 rounded-xl border bg-card p-4"
                >
                    <div class="rounded-lg bg-purple-500/10 p-3">
                        <Users class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Students
                        </p>
                        <p class="text-2xl font-bold">1,250</p>
                    </div>
                </div>
            </div>
            <!-- Report Categories -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    v-for="category in reportCategories"
                    :key="category.title"
                    :class="[
                        'rounded-xl border bg-gradient-to-br p-6',
                        getColorClasses(category.color).bg,
                    ]"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            :class="[
                                'rounded-lg p-2',
                                getColorClasses(category.color).iconBg,
                            ]"
                        >
                            <component
                                :is="category.icon"
                                :class="[
                                    'h-6 w-6',
                                    getColorClasses(category.color).text,
                                ]"
                            />
                        </div>
                        <h2 class="text-lg font-semibold">
                            {{ category.title }}
                        </h2>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="report in category.reports"
                            :key="report.name"
                            class="cursor-pointer rounded-lg border bg-card/80 p-4 transition-shadow hover:shadow-md"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-sm font-medium">
                                        {{ report.name }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ report.description }}
                                    </p>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 shrink-0"
                                >
                                    <Download class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Recent Reports -->
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b p-4">
                    <h3 class="font-semibold">Recently Generated Reports</h3>
                    <Button variant="ghost" size="sm">View All</Button>
                </div>
                <div class="divide-y">
                    <div
                        class="flex items-center justify-between p-4 hover:bg-muted/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30"
                            >
                                <FileText class="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="font-medium">
                                    Term 1 Report Cards - Grade 5
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Generated on Mar 10, 2026
                                </p>
                            </div>
                        </div>
                        <Button variant="outline" size="sm"
                            ><Download class="mr-2 h-4 w-4" />Download</Button
                        >
                    </div>
                    <div
                        class="flex items-center justify-between p-4 hover:bg-muted/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30"
                            >
                                <FileText class="h-5 w-5 text-green-600" />
                            </div>
                            <div>
                                <p class="font-medium">
                                    Monthly Attendance Summary - February
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Generated on Mar 5, 2026
                                </p>
                            </div>
                        </div>
                        <Button variant="outline" size="sm"
                            ><Download class="mr-2 h-4 w-4" />Download</Button
                        >
                    </div>
                    <div
                        class="flex items-center justify-between p-4 hover:bg-muted/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30"
                            >
                                <FileText class="h-5 w-5 text-amber-600" />
                            </div>
                            <div>
                                <p class="font-medium">
                                    Fee Collection Report - Term 1
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Generated on Mar 1, 2026
                                </p>
                            </div>
                        </div>
                        <Button variant="outline" size="sm"
                            ><Download class="mr-2 h-4 w-4" />Download</Button
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
