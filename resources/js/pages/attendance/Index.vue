<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CalendarCheck,
    Check,
    X,
    Clock,
    Users,
    AlertCircle,
    CheckCircle2,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
const getAttendanceRate = (present: number, total: number) =>
    Math.round((present / total) * 100);
</script>
<template>
    <Head title="Attendance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Register</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Attendance</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Attendance Logs
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Track and manage daily student attendance.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input
                            type="date"
                            v-model="selectedDate"
                            class="h-11 rounded-xl border border-border bg-background px-4 py-2 text-sm font-bold shadow-sm focus:ring-2 focus:ring-primary/10 transition-all"
                        />
                    </div>
                    <Button as-child class="h-11 rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <Link href="/attendance/mark">
                            <Check class="mr-2 h-4 w-4" />Mark Attendance
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Today</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Present</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        1,172
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-rose-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 group-hover:bg-rose-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <XCircle class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Missing</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Absent</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        48
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-amber-500/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-sm"
                        >
                            <Clock class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Punctuality</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Late Arrivals</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        15
                    </h3>
                </div>

                <div
                    class="group rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-primary/20 dark:border-white/5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase">Rate</span>
                    </div>
                    <p class="mb-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase">Consistency</p>
                    <h3 class="text-2xl font-bold text-foreground">
                        94.5%
                    </h3>
                </div>
            </div>

            <!-- Table Section -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div
                    class="flex flex-col border-b border-border/50 bg-muted/5 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <h3
                        class="text-xs font-bold tracking-tight text-foreground uppercase"
                    >
                        Class Attendance Summary
                    </h3>
                    <div class="mt-4 sm:mt-0">
                        <Select v-model="selectedClass">
                            <SelectTrigger class="w-full h-9 rounded-xl border-border bg-background px-4 text-xs font-bold sm:w-[200px]">
                                <SelectValue placeholder="All Classes" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl border-border">
                                <SelectItem value="all" class="text-xs font-bold uppercase">All Classes</SelectItem>
                                <SelectItem
                                    v-for="cls in classes"
                                    :key="cls.id"
                                    :value="String(cls.id)"
                                    class="text-xs font-bold uppercase"
                                >
                                    {{ cls.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/20">
                                <th
                                    class="px-6 py-4 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Class
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Total
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Present
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Absent
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Late
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase min-w-[120px]"
                                >
                                    Rate
                                </th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="cls in classes"
                                :key="cls.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-foreground">{{ cls.name }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-bold text-muted-foreground">{{ cls.total }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-bold text-emerald-600">{{ cls.present }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-bold text-rose-600">{{ cls.absent }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-bold text-amber-600">{{ cls.late }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-1.5 px-4">
                                        <div class="flex items-center justify-between text-[10px] font-bold text-muted-foreground uppercase">
                                            <span>Accuracy</span>
                                            <span>{{ getAttendanceRate(cls.present, cls.total) }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-muted/50 overflow-hidden">
                                            <div
                                                class="h-full bg-primary transition-all duration-500"
                                                :style="{ width: `${getAttendanceRate(cls.present, cls.total)}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="border-none bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold tracking-tight text-emerald-600 uppercase"
                                    >
                                        Marked
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                        class="h-8 w-8 rounded-lg text-muted-foreground hover:bg-primary/10 hover:text-primary"
                                    >
                                        <Link :href="`/attendance/class/${cls.id}`">
                                            <ChevronRight class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Mobile adjustments for table if needed */
@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>
