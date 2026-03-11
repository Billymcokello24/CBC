<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Activity {
    id: number | string;
    title: string;
    description: string;
    time: string;
    type: 'student' | 'teacher' | 'payment' | 'assessment' | 'attendance' | 'announcement' | 'system';
    user?: {
        name: string;
        avatar?: string;
    };
}

interface Props {
    activities: Activity[];
    loading?: boolean;
    maxItems?: number;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    maxItems: 5,
});

const getTypeStyles = (type: Activity['type']) => {
    const styles = {
        student: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        teacher: 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        payment: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
        assessment: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        attendance: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
        announcement: 'bg-pink-100 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400',
        system: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
    };
    return styles[type] || styles.system;
};

const getTypeIcon = (type: Activity['type']) => {
    const icons = {
        student: '👨‍🎓',
        teacher: '👨‍🏫',
        payment: '💰',
        assessment: '📝',
        attendance: '✅',
        announcement: '📢',
        system: '⚙️',
    };
    return icons[type] || '📋';
};
</script>

<template>
    <div class="rounded-xl border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Recent Activities</h3>
            <a href="#" class="text-sm font-medium text-primary hover:underline">View all</a>
        </div>

        <div v-if="loading" class="space-y-4">
            <div v-for="i in maxItems" :key="i" class="flex items-start gap-4">
                <div class="h-10 w-10 animate-pulse rounded-full bg-muted"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-3/4 animate-pulse rounded bg-muted"></div>
                    <div class="h-3 w-1/2 animate-pulse rounded bg-muted"></div>
                </div>
            </div>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="activity in activities.slice(0, maxItems)"
                :key="activity.id"
                class="flex items-start gap-4 rounded-lg p-2 transition-colors hover:bg-muted/50"
            >
                <div :class="cn('flex h-10 w-10 items-center justify-center rounded-full text-lg', getTypeStyles(activity.type))">
                    {{ getTypeIcon(activity.type) }}
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm leading-tight">{{ activity.title }}</p>
                    <p class="text-sm text-muted-foreground truncate">{{ activity.description }}</p>
                    <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                        <span v-if="activity.user">{{ activity.user.name }}</span>
                        <span v-if="activity.user">•</span>
                        <span>{{ activity.time }}</span>
                    </div>
                </div>
            </div>

            <div v-if="activities.length === 0" class="py-8 text-center text-muted-foreground">
                <p>No recent activities</p>
            </div>
        </div>
    </div>
</template>
