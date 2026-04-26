<script setup lang="ts">
import { computed } from 'vue';

interface CalendarEvent {
    id: number;
    title: string;
    date: string;
    time?: string;
    type: 'exam' | 'meeting' | 'holiday' | 'event' | 'deadline';
}

interface Props {
    events: CalendarEvent[];
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
});

const today = new Date();
const currentMonth = today.toLocaleString('default', {
    month: 'long',
    year: 'numeric',
});

// Generate calendar days
const calendarDays = computed(() => {
    const year = today.getFullYear();
    const month = today.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startPadding = firstDay.getDay();

    const days = [];

    // Previous month padding
    for (let i = startPadding - 1; i >= 0; i--) {
        const date = new Date(year, month, -i);
        days.push({ date, isCurrentMonth: false, events: [] });
    }

    // Current month
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const date = new Date(year, month, i);
        const dateStr = date.toISOString().split('T')[0];
        const dayEvents = props.events.filter((e) => e.date === dateStr);
        days.push({
            date,
            isCurrentMonth: true,
            isToday: date.toDateString() === today.toDateString(),
            events: dayEvents,
        });
    }

    // Next month padding
    const remaining = 42 - days.length;
    for (let i = 1; i <= remaining; i++) {
        const date = new Date(year, month + 1, i);
        days.push({ date, isCurrentMonth: false, events: [] });
    }

    return days;
});

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const upcomingEvents = computed(() => {
    const todayStr = today.toISOString().split('T')[0];
    return props.events
        .filter((e) => e.date >= todayStr)
        .sort((a, b) => a.date.localeCompare(b.date))
        .slice(0, 5);
});

const eventTypeColors = {
    exam: 'bg-red-500',
    meeting: 'bg-blue-500',
    holiday: 'bg-green-500',
    event: 'bg-purple-500',
    deadline: 'bg-amber-500',
};

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('default', {
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <div class="rounded-xl border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Calendar</h3>
            <span class="text-sm text-muted-foreground">{{
                currentMonth
            }}</span>
        </div>

        <!-- Mini Calendar -->
        <div class="mb-6">
            <!-- Week days header -->
            <div class="mb-2 grid grid-cols-7 gap-1">
                <div
                    v-for="day in weekDays"
                    :key="day"
                    class="py-1 text-center text-xs font-medium text-muted-foreground"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Calendar grid -->
            <div class="grid grid-cols-7 gap-1">
                <div
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    :class="[
                        'relative flex h-8 items-center justify-center rounded text-sm transition-colors',
                        day.isCurrentMonth
                            ? 'text-foreground'
                            : 'text-muted-foreground/50',
                        day.isToday
                            ? 'bg-primary font-semibold text-primary-foreground'
                            : 'hover:bg-muted',
                    ]"
                >
                    {{ day.date.getDate() }}
                    <span
                        v-if="day.events.length > 0"
                        class="absolute bottom-0.5 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-primary"
                        :class="{ 'bg-primary-foreground': day.isToday }"
                    ></span>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div>
            <h4 class="mb-3 text-sm font-medium text-muted-foreground">
                Upcoming Events
            </h4>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 3" :key="i" class="flex items-center gap-3">
                    <div
                        class="h-2 w-2 animate-pulse rounded-full bg-muted"
                    ></div>
                    <div class="flex-1">
                        <div
                            class="h-4 w-3/4 animate-pulse rounded bg-muted"
                        ></div>
                    </div>
                </div>
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="event in upcomingEvents"
                    :key="event.id"
                    class="flex items-center gap-3 rounded-lg p-2 hover:bg-muted/50"
                >
                    <span
                        :class="[
                            'h-2 w-2 rounded-full',
                            eventTypeColors[event.type],
                        ]"
                    ></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">
                            {{ event.title }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(event.date) }}
                            <span v-if="event.time"> • {{ event.time }}</span>
                        </p>
                    </div>
                </div>

                <div
                    v-if="upcomingEvents.length === 0"
                    class="py-4 text-center text-sm text-muted-foreground"
                >
                    No upcoming events
                </div>
            </div>
        </div>
    </div>
</template>
