<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    Clock, Calendar, MapPin, 
    ChevronLeft, ChevronRight, Download,
    BookOpen, Printer
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
    { title: 'My Timetable', href: '/timetable/my' }
];

const selectedDay = ref(new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="My Weekly Timetable" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <Clock class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">My Teaching Schedule</h1>
                        <p class="text-muted-foreground">Weekly lesson distribution and room assignments, {{ teacher?.first_name }}.</p>
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
            <div class="hidden lg:block rounded-xl border bg-card overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b">
                                <th class="p-6 text-left border-r min-w-[150px]">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Time / Day</span>
                                </th>
                                <th v-for="day in days" :key="day" class="p-6 text-center border-r min-w-[180px]">
                                    <span class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ day }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="period in periods" :key="period.id">
                                <td class="p-6 border-r bg-gray-50/30">
                                    <div class="space-y-1">
                                        <p class="text-xs font-black text-violet-600">{{ period.start_time }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ period.end_time }}</p>
                                        <p class="text-[9px] font-black text-gray-300 uppercase mt-2 tracking-widest">{{ period.name }}</p>
                                    </div>
                                </td>
                                <td v-for="day in days" :key="day" class="p-3 border-r relative min-h-[120px]">
                                    <div v-if="timetable.find(s => s.day_of_week === day.toLowerCase() && s.period_id === period.id)"
                                        class="h-full rounded-xl border border-violet-100 bg-linear-to-br from-violet-50/80 to-white p-4 shadow-sm hover:shadow-md transition-all group"
                                    >
                                        <div class="flex flex-col h-full justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-black text-violet-700 leading-tight group-hover:text-violet-900 transition-colors uppercase tracking-tight">
                                                    {{ timetable.find(s => s.day_of_week === day.toLowerCase() && s.period_id === period.id).subject }}
                                                </p>
                                                <p class="text-[9px] font-black text-gray-900 mt-1 uppercase tracking-widest">
                                                    {{ timetable.find(s => s.day_of_week === day.toLowerCase() && s.period_id === period.id).class }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 opacity-70 italic tracking-tighter">
                                                <MapPin class="h-3 w-3" /> 
                                                {{ timetable.find(s => s.day_of_week === day.toLowerCase() && s.period_id === period.id).room || 'General' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else-if="period.type === 'break'" class="flex items-center justify-center opacity-30">
                                        <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] -rotate-45">Interval</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile List View (Pulsar Style) -->
            <div class="lg:hidden space-y-6">
                <!-- Day Selector -->
                <div class="flex overflow-x-auto gap-2 pb-2 scrollbar-hide">
                    <button 
                        v-for="day in days" :key="day"
                        @click="selectedDay = day.toLowerCase()"
                        :class="[
                            'px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap border',
                            selectedDay === day.toLowerCase() 
                                ? 'bg-violet-600 border-violet-600 text-white shadow-lg shadow-violet-100' 
                                : 'bg-white border-gray-100 text-gray-400 hover:border-violet-200'
                        ]"
                    >
                        {{ day }}
                    </button>
                </div>

                <!-- Schedule for Selected Day -->
                <div class="space-y-4">
                    <div v-for="period in periods" :key="period.id" class="relative">
                        <div v-if="timetable.find(s => s.day_of_week === selectedDay && s.period_id === period.id)"
                            class="rounded-2xl border bg-card p-5 flex items-center gap-5 shadow-sm"
                        >
                            <div class="text-center min-w-[60px] border-r border-dashed border-gray-100 pr-5">
                                <p class="text-xs font-black text-violet-600">{{ period.start_time }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">{{ period.end_time }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-black text-gray-900 uppercase leading-none">
                                    {{ timetable.find(s => s.day_of_week === selectedDay && s.period_id === period.id).subject }}
                                </p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[10px] font-black text-violet-600 bg-violet-50 px-2 py-0.5 rounded-md uppercase tracking-widest">
                                        {{ timetable.find(s => s.day_of_week === selectedDay && s.period_id === period.id).class }}
                                    </span>
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 italic">
                                        <MapPin class="h-3 w-3" /> {{ timetable.find(s => s.day_of_week === selectedDay && s.period_id === period.id).room || 'General' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="period.type === 'break'" class="py-3 px-8 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-100 italic font-black text-[9px] text-gray-300 uppercase tracking-[0.3em]">
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
