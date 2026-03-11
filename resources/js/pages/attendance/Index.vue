<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarCheck, Check, X, Clock, Users, AlertCircle, CheckCircle2, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendance', href: '/attendance' },
];
const selectedClass = ref('all');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const classes = ref([
    { id: 1, name: 'Grade 4A', total: 35, present: 33, absent: 2, late: 0 },
    { id: 2, name: 'Grade 4B', total: 32, present: 30, absent: 1, late: 1 },
    { id: 3, name: 'Grade 5A', total: 38, present: 35, absent: 2, late: 1 },
    { id: 4, name: 'Grade 5B', total: 36, present: 36, absent: 0, late: 0 },
    { id: 5, name: 'Grade 6A', total: 40, present: 38, absent: 1, late: 1 },
]);
const getAttendanceRate = (present: number, total: number) => Math.round((present / total) * 100);
</script>
<template>
    <Head title="Attendance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10">
                        <CalendarCheck class="h-6 w-6 text-cyan-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Attendance</h1>
                        <p class="text-muted-foreground">Track and manage daily attendance</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <input type="date" v-model="selectedDate" class="rounded-md border bg-background px-3 py-2 text-sm" />
                    <Button as-child><Link href="/attendance/mark"><Check class="mr-2 h-4 w-4" />Mark Attendance</Link></Button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-6 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-500/10 p-2"><CheckCircle2 class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground">Present Today</p>
                            <p class="text-2xl font-bold text-green-600">1,172</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-red-50 to-red-100/50 p-6 dark:from-red-950/50 dark:to-red-900/30">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-red-500/10 p-2"><XCircle class="h-5 w-5 text-red-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground">Absent Today</p>
                            <p class="text-2xl font-bold text-red-600">48</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 dark:from-amber-950/50 dark:to-amber-900/30">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-500/10 p-2"><Clock class="h-5 w-5 text-amber-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground">Late Arrivals</p>
                            <p class="text-2xl font-bold text-amber-600">15</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-500/10 p-2"><Users class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-sm text-muted-foreground">Attendance Rate</p>
                            <p class="text-2xl font-bold text-blue-600">94.5%</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border bg-card">
                <div class="flex items-center justify-between border-b p-4">
                    <h3 class="font-semibold">Class Attendance Summary</h3>
                    <Select v-model="selectedClass">
                        <SelectTrigger class="w-[180px]"><SelectValue placeholder="All Classes" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Classes</SelectItem>
                            <SelectItem v-for="cls in classes" :key="cls.id" :value="String(cls.id)">{{ cls.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Total</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Present</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Absent</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Late</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Rate</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cls in classes" :key="cls.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 font-medium">{{ cls.name }}</td>
                                <td class="px-4 py-3 text-center">{{ cls.total }}</td>
                                <td class="px-4 py-3 text-center text-green-600 font-medium">{{ cls.present }}</td>
                                <td class="px-4 py-3 text-center text-red-600 font-medium">{{ cls.absent }}</td>
                                <td class="px-4 py-3 text-center text-amber-600 font-medium">{{ cls.late }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="h-2 w-16 rounded-full bg-muted">
                                            <div class="h-full rounded-full bg-green-500" :style="{ width: `${getAttendanceRate(cls.present, cls.total)}%` }"></div>
                                        </div>
                                        <span class="text-sm font-medium">{{ getAttendanceRate(cls.present, cls.total) }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge variant="default" class="bg-green-500">Marked</Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="ghost" size="sm" as-child><Link :href="`/attendance/class/${cls.id}`">View Details</Link></Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
