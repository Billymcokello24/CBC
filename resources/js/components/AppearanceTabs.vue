<script setup lang="ts">
import { Monitor, Moon, Sun, Check } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { 
        value: 'light', 
        Icon: Sun, 
        label: 'Light Mode',
        description: 'Clean and bright',
        color: 'bg-white text-orange-500'
    },
    { 
        value: 'dark', 
        Icon: Moon, 
        label: 'Dark Mode',
        description: 'Easy on the eyes',
        color: 'bg-zinc-950 text-indigo-400'
    },
    { 
        value: 'system', 
        Icon: Monitor, 
        label: 'Auto System',
        description: 'Sync with OS',
        color: 'bg-slate-200 text-slate-600'
    },
] as const;
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
            v-for="{ value, Icon, label, description, color } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            class="group relative flex flex-col items-start p-6 rounded-[2rem] border-2 transition-all duration-300 text-left overflow-hidden"
            :class="[
                appearance === value
                ? 'bg-white dark:bg-zinc-900 border-indigo-600 shadow-xl shadow-indigo-500/10'
                : 'bg-slate-50/50 dark:bg-zinc-950/50 border-slate-100 dark:border-zinc-900 hover:border-slate-200 dark:hover:border-zinc-800 hover:bg-white dark:hover:bg-zinc-900'
            ]"
        >
            <div 
                class="p-3 rounded-2xl mb-4 transition-transform duration-300 group-hover:scale-110"
                :class="[
                    appearance === value ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : color + ' opacity-80'
                ]"
            >
                <component :is="Icon" class="h-6 w-6" />
            </div>

            <div class="space-y-1">
                <p 
                    class="text-sm font-black uppercase tracking-widest"
                    :class="appearance === value ? 'text-slate-900 dark:text-zinc-100' : 'text-slate-500 dark:text-zinc-500'"
                >
                    {{ label }}
                </p>
                <p class="text-xs font-medium text-slate-400 dark:text-zinc-500">{{ description }}</p>
            </div>

            <div 
                v-if="appearance === value"
                class="absolute top-4 right-4 h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center animate-in zoom-in duration-300"
            >
                <Check class="h-3 w-3 text-white stroke-[4px]" />
            </div>

            <!-- Absolute background decoration -->
            <div class="absolute -bottom-6 -right-6 h-12 w-12 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition-colors"></div>
        </button>
    </div>
</template>
