<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    Clock,
    Calendar,
    MapPin,
    ChevronLeft,
    ChevronRight,
    Download,
    BookOpen,
    Printer,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const props = defineProps<{
    teacher: any;
    timetable: any[]; // Expecting weekly data
    days: string[];
    periods: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Timetable', href: '/timetable/my' },
];

const selectedDay = ref(
    new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase(),
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="My Weekly Timetable" />

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
                            My Teaching Schedule
                        </h1>
                        <p class="text-muted-foreground">
                            Weekly lesson distribution and room assignments,
                            {{ teacher?.first_name }}.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm">
                        <Printer class="mr-2 h-4 w-4" />
                        Print Timetable
                    </Button>
                    <Button size="sm">
                        <Download class="mr-2 h-4 w-4" />
                        Export PDF
                    </Button>
                </div>
            </div>

            <!-- Desktop Grid View (Pulsar Style) -->
            <div
                class="hidden overflow-hidden rounded-xl border bg-card shadow-sm lg:block"
            >
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
                                    v-for="day in days"
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
                                        <p
                                            class="mt-2 text-xs font-bold tracking-tight text-gray-300 uppercase"
                                        >
                                            {{ period.name }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    v-for="day in days"
                                    :key="day"
                                    class="relative min-h-[120px] border-r p-3"
                                >
                                    <div
                                        v-if="
                                            timetable.find(
                                                (s) =>
                                                    s.day_of_week ===
                                                        day.toLowerCase() &&
                                                    s.period_id === period.id,
                                            )
                                        "
                                        class="group h-full rounded-xl border border-violet-100 bg-linear-to-br from-violet-50/80 to-white p-4 shadow-sm transition-all hover:shadow-md"
                                    >
                                        <div
                                            class="flex h-full flex-col justify-between gap-3"
                                        >
                                            <div>
                                                <p
                                                    class="text-xs leading-tight font-bold tracking-tight text-violet-700 uppercase transition-colors group-hover:text-violet-900"
                                                >
                                                    {{
                                                        timetable.find(
                                                            (s) =>
                                                                s.day_of_week ===
                                                                    day.toLowerCase() &&
                                                                s.period_id ===
                                                                    period.id,
                                                        ).subject
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-1 text-xs font-bold tracking-tight text-gray-900 uppercase"
                                                >
                                                    {{
                                                        timetable.find(
                                                            (s) =>
                                                                s.day_of_week ===
                                                                    day.toLowerCase() &&
                                                                s.period_id ===
                                                                    period.id,
                                                        ).class
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="flex items-center gap-1.5 text-xs font-bold tracking-tighter text-gray-400 opacity-70"
                                            >
                                                <MapPin class="h-3 w-3" />
                                                {{
                                                    timetable.find(
                                                        (s) =>
                                                            s.day_of_week ===
                                                                day.toLowerCase() &&
                                                            s.period_id ===
                                                                period.id,
                                                    ).room || 'General'
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

            <!-- Mobile List View (Pulsar Style) -->
            <div class="space-y-6 lg:hidden">
                <!-- Day Selector -->
                <div class="scrollbar-hide flex gap-2 overflow-x-auto pb-2">
                    <button
                        v-for="day in days"
                        :key="day"
                        @click="selectedDay = day.toLowerCase()"
                        :class="[
                            'rounded-xl border px-5 py-2.5 text-xs font-medium tracking-tight whitespace-nowrap uppercase transition-all',
                            selectedDay === day.toLowerCase()
                                ? 'border-violet-600 bg-violet-600 text-white shadow-lg shadow-violet-100'
                                : 'border-gray-100 bg-white text-gray-400 hover:border-violet-200',
                        ]"
                    >
                        {{ day }}
                    </button>
                </div>

                <!-- Schedule for Selected Day -->
                <div class="space-y-4">
                    <div
                        v-for="period in periods"
                        :key="period.id"
                        class="relative"
                    >
                        <div
                            v-if="
                                timetable.find(
                                    (s) =>
                                        s.day_of_week === selectedDay &&
                                        s.period_id === period.id,
                                )
                            "
                            class="flex items-center gap-5 rounded-2xl border bg-card p-5 shadow-sm"
                        >
                            <div
                                class="min-w-[60px] border-r border-dashed border-gray-100 pr-5 text-center"
                            >
                                <p class="text-xs font-bold text-violet-600">
                                    {{ period.start_time }}
                                </p>
                                <p
                                    class="text-xs font-bold text-gray-400 uppercase"
                                >
                                    {{ period.end_time }}
                                </p>
                            </div>
                            <div class="flex-1">
                                <p
                                    class="text-sm leading-none font-bold text-gray-900 uppercase"
                                >
                                    {{
                                        timetable.find(
                                            (s) =>
                                                s.day_of_week === selectedDay &&
                                                s.period_id === period.id,
                                        ).subject
                                    }}
                                </p>
                                <div class="mt-2 flex items-center gap-3">
                                    <span
                                        class="rounded-md bg-violet-50 px-2 py-0.5 text-xs font-bold tracking-tight text-violet-600 uppercase"
                                    >
                                        {{
                                            timetable.find(
                                                (s) =>
                                                    s.day_of_week ===
                                                        selectedDay &&
                                                    s.period_id === period.id,
                                            ).class
                                        }}
                                    </span>
                                    <div
                                        class="flex items-center gap-1.5 text-xs font-bold text-gray-400"
                                    >
                                        <MapPin class="h-3 w-3" />
                                        {{
                                            timetable.find(
                                                (s) =>
                                                    s.day_of_week ===
                                                        selectedDay &&
                                                    s.period_id === period.id,
                                            ).room || 'General'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else-if="period.type === 'break'"
                            class="rounded-xl border border-dashed border-gray-100 bg-gray-50/50 px-8 py-3 text-center text-xs font-bold tracking-tight text-gray-300 uppercase"
                        >
                            {{ period.name }} interval
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
