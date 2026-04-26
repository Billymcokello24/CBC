<script setup lang="ts">
import { Home, ChevronRight } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

interface Props {
    title: string;
    description?: string;
    breadcrumbs?: BreadcrumbItem[];
}

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col gap-1 px-1 mb-6 sm:mb-8 animate-in fade-in slide-in-from-top-4 duration-500">
        <!-- Unified Breadcrumbs -->
        <div v-if="breadcrumbs && breadcrumbs.length" class="flex items-center gap-2 text-[10px] sm:text-xs text-muted-foreground/60 mb-2 overflow-x-auto no-scrollbar whitespace-nowrap">
            <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5 flex-shrink-0" />
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3 flex-shrink-0" />
                <Link 
                    :href="item.href" 
                    class="font-medium hover:text-primary transition-colors uppercase tracking-widest text-[8px] sm:text-[10px]"
                    :class="index === breadcrumbs.length - 1 ? 'text-foreground font-black' : ''"
                >
                    {{ item.title }}
                </Link>
            </template>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-foreground leading-[1.1] mb-1">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm sm:text-[15px] text-muted-foreground font-medium max-w-2xl">
                    {{ description }}
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
