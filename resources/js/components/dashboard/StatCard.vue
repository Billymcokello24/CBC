<script setup lang="ts">
import { LucideIcon, TrendingUp, TrendingDown } from 'lucide-vue-next';

interface Props {
    title: string;
    value: string;
    description?: string;
    icon: LucideIcon;
    trend?: {
        value: number;
        direction: 'up' | 'down';
    };
    variant?: 'primary' | 'success' | 'info' | 'warning' | 'danger';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
});

const iconWrappers = {
    primary: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    success: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    info: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    warning: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    danger: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
};

const trendColors = {
    up: 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
    down: 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
};
</script>

<template>
    <div class="group relative overflow-hidden rounded-2xl border bg-card p-6 shadow-sm transition-all hover:shadow-md dark:border-white/5">
        <div class="flex items-center justify-between">
            <div class="space-y-4">
                <div class="space-y-1">
                    <p class="text-[13px] font-medium text-muted-foreground whitespace-nowrap">{{ title }}</p>
                    <h3 class="text-3xl font-bold tracking-tight text-foreground">{{ value }}</h3>
                </div>
                
                <div v-if="trend" :class="['inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold', trendColors[trend.direction]]">
                    <component :is="trend.direction === 'up' ? TrendingUp : TrendingDown" class="h-3.5 w-3.5" />
                    {{ trend.value }}%
                    <span class="text-muted-foreground/60 font-normal ml-1">vs last month</span>
                </div>
            </div>
            
            <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition-transform group-hover:scale-110 duration-500', iconWrappers[variant]]">
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
    </div>
</template>
