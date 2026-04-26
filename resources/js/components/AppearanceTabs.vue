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
        color: 'bg-white text-orange-500',
    },
    {
        value: 'dark',
        Icon: Moon,
        label: 'Dark Mode',
        description: 'Easy on the eyes',
        color: 'bg-zinc-950 text-indigo-400',
    },
    {
        value: 'system',
        Icon: Monitor,
        label: 'Auto System',
        description: 'Sync with OS',
        color: 'bg-slate-200 text-slate-600',
    },
] as const;
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <button
            v-for="{ value, Icon, label, description, color } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            class="group relative flex flex-col items-start overflow-hidden rounded-xl border-2 p-6 text-left transition-all duration-300"
            :class="[
                appearance === value
                    ? 'border-indigo-600 bg-white shadow-xl shadow-indigo-500/10 dark:bg-zinc-900'
                    : 'border-slate-100 bg-slate-50/50 hover:border-slate-200 hover:bg-white dark:border-zinc-900 dark:bg-zinc-950/50 dark:hover:border-zinc-800 dark:hover:bg-zinc-900',
            ]"
        >
            <div
                class="mb-4 rounded-2xl p-3 transition-transform duration-300 group-hover:scale-110"
                :class="[
                    appearance === value
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20'
                        : color + ' opacity-80',
                ]"
            >
                <component :is="Icon" class="h-6 w-6" />
            </div>

            <div class="space-y-1">
                <p
                    class="text-sm font-medium tracking-tight uppercase"
                    :class="
                        appearance === value
                            ? 'text-slate-900 dark:text-zinc-100'
                            : 'text-slate-500 dark:text-zinc-500'
                    "
                >
                    {{ label }}
                </p>
                <p
                    class="text-xs font-medium text-slate-400 dark:text-zinc-500"
                >
                    {{ description }}
                </p>
            </div>

            <div
                v-if="appearance === value"
                class="absolute top-4 right-4 flex h-6 w-6 animate-in items-center justify-center rounded-full bg-indigo-600 duration-300 zoom-in"
            >
                <Check class="h-3 w-3 stroke-[4px] text-white" />
            </div>

            <!-- Absolute background decoration -->
            <div
                class="absolute -right-6 -bottom-6 h-12 w-12 rounded-full bg-indigo-500/5 blur-xl transition-colors group-hover:bg-indigo-500/10"
            ></div>
        </button>
    </div>
</template>
