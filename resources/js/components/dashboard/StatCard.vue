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

const variantStyles = {
    primary: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    success: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
    info: 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
    warning: 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    danger: 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
};

const iconBackgrounds = {
    primary: 'bg-blue-100',
    success: 'bg-emerald-100',
    info: 'bg-sky-100',
    warning: 'bg-amber-100',
    danger: 'bg-rose-100',
};
</script>

<template>
    <div class="rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div :class="['rounded-lg p-2.5', iconBackgrounds[variant] || 'bg-gray-100']">
                <component :is="icon" class="h-5 w-5" />
            </div>
            <div v-if="trend" :class="['flex items-center gap-1 text-xs font-bold leading-none', trend.direction === 'up' ? 'text-emerald-600' : 'text-rose-600']">
                <TrendingUp v-if="trend.direction === 'up'" class="h-3 w-3" />
                <TrendingDown v-else class="h-3 w-3" />
                {{ trend.value }}%
            </div>
        </div>
        
        <div class="mt-4 space-y-1">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">{{ title }}</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black tracking-tight text-gray-900">{{ value }}</h3>
            </div>
            <p v-if="description" class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ description }}</p>
        </div>
    </div>
</template>
