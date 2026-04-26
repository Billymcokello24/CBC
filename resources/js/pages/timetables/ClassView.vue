<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    Calendar,
    MapPin,
    ChevronLeft,
    Download,
    Printer,
    BookOpen,
    User,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    timetable: any;
    classId: number;
    days: string[];
    periods: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Timetables', href: '/timetable' },
    { title: 'Class View', href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Class Timetable`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <Clock class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">
                            Class Timetable
                        </h1>
                        <p class="text-muted-foreground">
                            Weekly schedule and teacher allocations for the
                            selected class.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/timetable">
                            <ChevronLeft class="mr-2 h-4 w-4" />
                            Back to List
                        </Link>
                    </Button>
                    <Button size="sm">
                        <Printer class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                </div>
            </div>

            <!-- Timetable Grid (Pulsar Style) -->
            <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50/50">
                                <th
                                    class="min-w-[150px] border-r p-6 text-left"
                                >
                                    <span
                                        class="text-xs font-bold tracking-tight text-gray-400 uppercase"
                                        >Time / Day</span
                                    >
                                </th>
                                <th
                                    v-for="day in [
                                        'Monday',
                                        'Tuesday',
                                        'Wednesday',
                                        'Thursday',
                                        'Friday',
                                    ]"
                                    :key="day"
                                    class="min-w-[180px] border-r p-6 text-center"
                                >
                                    <span
                                        class="text-xs font-bold tracking-tight text-gray-900 uppercase"
                                        >{{ day }}</span
                                    >
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="period in periods" :key="period.id">
                                <td class="border-r bg-gray-50/30 p-6">
                                    <div class="space-y-1">
                                        <p
                                            class="text-xs font-bold text-violet-600"
                                        >
                                            {{ period.start_time }}
                                        </p>
                                        <p
                                            class="text-xs font-bold tracking-tighter text-gray-400 uppercase"
                                        >
                                            {{ period.end_time }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    v-for="day in [
                                        'monday',
                                        'tuesday',
                                        'wednesday',
                                        'thursday',
                                        'friday',
                                    ]"
                                    :key="day"
                                    class="relative min-h-[120px] border-r p-3"
                                >
                                    <div
                                        v-if="
                                            timetable?.slots?.find(
                                                (s) =>
                                                    s.day_of_week === day &&
                                                    s.period_definition_id ===
                                                        period.id,
                                            )
                                        "
                                        class="group h-full rounded-xl border border-violet-100 bg-linear-to-br from-violet-50/80 to-white p-4 shadow-sm transition-all hover:shadow-md"
                                    >
                                        <div
                                            class="flex h-full flex-col justify-between gap-3"
                                        >
                                            <div>
                                                <p
                                                    class="text-xs leading-tight font-bold tracking-tight text-violet-700 uppercase"
                                                >
                                                    {{
                                                        timetable.slots.find(
                                                            (s) =>
                                                                s.day_of_week ===
                                                                    day &&
                                                                s.period_definition_id ===
                                                                    period.id,
                                                        ).subject?.name
                                                    }}
                                                </p>
                                                <div
                                                    class="mt-2 flex items-center gap-1.5 text-xs font-bold text-gray-400"
                                                >
                                                    <User class="h-3 w-3" />
                                                    {{
                                                        timetable.slots.find(
                                                            (s) =>
                                                                s.day_of_week ===
                                                                    day &&
                                                                s.period_definition_id ===
                                                                    period.id,
                                                        ).teacher?.name || 'TBA'
                                                    }}
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-1.5 text-xs font-bold tracking-tight text-gray-300 uppercase"
                                            >
                                                <MapPin class="h-2.5 w-2.5" />
                                                Room:
                                                {{
                                                    timetable.slots.find(
                                                        (s) =>
                                                            s.day_of_week ===
                                                                day &&
                                                            s.period_definition_id ===
                                                                period.id,
                                                    ).room_number || 'General'
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-else-if="period.type === 'break'"
                                        class="flex items-center justify-center opacity-30"
                                    >
                                        <span
                                            class="-rotate-45 text-xs font-bold tracking-tight text-gray-300 uppercase"
                                            >Interval</span
                                        >
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-if="!timetable"
                class="rounded-xl border border-dashed py-20 text-center"
            >
                <div
                    class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-50"
                >
                    <Calendar class="h-8 w-8 text-gray-200" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 uppercase">
                    No active timetable
                </h3>
                <p
                    class="mx-auto mt-1 max-w-sm text-xs font-medium tracking-tight text-gray-400 uppercase"
                >
                    A timetable hasn't been published for this class yet.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
