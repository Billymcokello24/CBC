<script setup lang="ts">
import { computed, ref } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const statCardVariants = cva(
    'relative overflow-hidden rounded-xl border p-6 transition-all duration-200 hover:shadow-lg',
    {
        variants: {
            variant: {
                default: 'bg-card text-card-foreground border-border',
                primary: 'bg-primary/10 text-primary border-primary/20',
                success: 'bg-green-500/10 text-green-600 border-green-500/20 dark:text-green-400',
                warning: 'bg-amber-500/10 text-amber-600 border-amber-500/20 dark:text-amber-400',
                danger: 'bg-red-500/10 text-red-600 border-red-500/20 dark:text-red-400',
                info: 'bg-blue-500/10 text-blue-600 border-blue-500/20 dark:text-blue-400',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);

type StatCardVariants = VariantProps<typeof statCardVariants>;

interface Props {
    title: string;
    value: string | number;
    description?: string;
    icon?: any;
    trend?: {
        value: number;
        direction: 'up' | 'down' | 'neutral';
    };
    variant?: StatCardVariants['variant'];
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    loading: false,
});

const trendColor = computed(() => {
    if (!props.trend) return '';
    switch (props.trend.direction) {
        case 'up':
            return 'text-green-600 dark:text-green-400';
        case 'down':
            return 'text-red-600 dark:text-red-400';
        default:
            return 'text-gray-500';
    }
});

const trendIcon = computed(() => {
    if (!props.trend) return '';
    switch (props.trend.direction) {
        case 'up':
            return '↑';
        case 'down':
            return '↓';
        default:
            return '→';
    }
});
</script>

<template>
    <div :class="cn(statCardVariants({ variant }), $attrs.class ?? '')">
        <!-- Background decoration -->
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-current opacity-5"></div>

        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>

                <div v-if="loading" class="h-8 w-24 animate-pulse rounded bg-muted"></div>
                <p v-else class="text-3xl font-bold tracking-tight">{{ value }}</p>

                <div v-if="description || trend" class="flex items-center gap-2 text-sm">
                    <span v-if="trend" :class="trendColor" class="flex items-center gap-1 font-medium">
                        {{ trendIcon }} {{ Math.abs(trend.value) }}%
                    </span>
                    <span v-if="description" class="text-muted-foreground">{{ description }}</span>
                </div>
            </div>

            <div v-if="icon" class="rounded-lg bg-current/10 p-3">
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
    </div>
</template>
