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
    primary: 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    success: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    info: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    warning: 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    danger: 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
};
</script>

<template>
    <div class="group relative overflow-hidden rounded-2xl border bg-card p-6 shadow-sm transition-all hover:shadow-md dark:border-white/5">
        <div class="flex items-center justify-between gap-4">
            <div class="space-y-1">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">{{ title }}</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black tracking-tight text-foreground">{{ value }}</h3>
                    <div v-if="trend" :class="['flex items-center gap-0.5 text-[10px] font-black', trend.direction === 'up' ? 'text-emerald-500' : 'text-rose-500']">
                        <component :is="trend.direction === 'up' ? TrendingUp : TrendingDown" class="h-3 w-3" />
                        {{ trend.value }}%
                    </div>
                </div>
            </div>
            <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors', iconWrappers[variant]]">
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
        <p v-if="description" class="mt-4 text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">
            {{ description }}
        </p>
    </div>
</template>
