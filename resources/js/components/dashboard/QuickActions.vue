<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

interface QuickAction {
    title: string;
    description: string;
    href: string;
    icon: string;
    color: 'blue' | 'green' | 'purple' | 'amber' | 'red' | 'pink';
}

interface Props {
    actions: QuickAction[];
}

defineProps<Props>();

const colorStyles = {
    blue: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-500/20',
    green: 'bg-green-500/10 text-green-600 dark:text-green-400 hover:bg-green-500/20',
    purple: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 hover:bg-purple-500/20',
    amber: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20',
    red: 'bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-500/20',
    pink: 'bg-pink-500/10 text-pink-600 dark:text-pink-400 hover:bg-pink-500/20',
};
</script>

<template>
    <div class="rounded-xl border bg-card p-6">
        <h3 class="mb-4 text-lg font-semibold">Quick Actions</h3>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="action in actions"
                :key="action.title"
                :href="action.href"
                :class="cn(
                    'flex flex-col items-center gap-2 rounded-lg p-4 text-center transition-all duration-200',
                    colorStyles[action.color]
                )"
            >
                <span class="text-2xl">{{ action.icon }}</span>
                <span class="text-sm font-medium">{{ action.title }}</span>
            </Link>
        </div>
    </div>
</template>
