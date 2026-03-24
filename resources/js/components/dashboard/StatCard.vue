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

const variantClasses = {
    primary: 'from-violet-500/10 to-indigo-500/5 text-violet-600 border-violet-100/50 hover:border-violet-300',
    success: 'from-emerald-500/10 to-teal-500/5 text-emerald-600 border-emerald-100/50 hover:border-emerald-300',
    info: 'from-blue-500/10 to-cyan-500/5 text-blue-600 border-blue-100/50 hover:border-blue-300',
    warning: 'from-amber-500/10 to-orange-500/5 text-amber-600 border-amber-100/50 hover:border-amber-300',
    danger: 'from-rose-500/10 to-red-500/5 text-rose-600 border-rose-100/50 hover:border-rose-300',
};

const iconWrappers = {
    primary: 'bg-violet-100 text-violet-600',
    success: 'bg-emerald-100 text-emerald-600',
    info: 'bg-blue-100 text-blue-600',
    warning: 'bg-amber-100 text-amber-600',
    danger: 'bg-rose-100 text-rose-600',
};
</script>

<template>
    <div 
        class="group relative overflow-hidden rounded-3xl border bg-white p-7 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50"
        :class="variantClasses[variant]"
    >
        <!-- Background Gradient Glow -->
        <div 
            class="absolute -right-4 -top-4 h-24 w-24 rounded-full blur-2xl transition-opacity opacity-0 group-hover:opacity-40"
            :class="variantClasses[variant].split(' ')[0]"
        ></div>

        <div class="relative flex items-center justify-between">
            <div :class="['flex h-12 w-12 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:rotate-12', iconWrappers[variant]]">
                <component :is="icon" class="h-6 w-6" />
            </div>
            
            <div v-if="trend" :class="['flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider', trend.direction === 'up' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']">
                <TrendingUp v-if="trend.direction === 'up'" class="h-3 w-3" />
                <TrendingDown v-else class="h-3 w-3" />
                {{ trend.value }}%
            </div>
        </div>
        
        <div class="relative mt-8 space-y-1.5">
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 group-hover:text-slate-500 transition-colors">{{ title }}</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black tracking-tighter text-slate-900">{{ value }}</h3>
            </div>
            <p v-if="description" class="text-[11px] font-bold text-slate-400 leading-tight">{{ description }}</p>
        </div>
    </div>
</template>
