<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-slate-50 p-6 md:p-10 dark:bg-slate-950"
    >
        <!-- Mesh Gradient Background -->
        <div class="absolute inset-0 z-0">
            <div
                class="absolute -top-[20%] -left-[10%] h-[60%] w-[60%] rounded-full bg-purple-500/10 blur-[120px]"
            ></div>
            <div
                class="absolute -right-[10%] -bottom-[20%] h-[60%] w-[60%] rounded-full bg-blue-500/10 blur-[120px]"
            ></div>
            <div
                class="absolute top-1/2 left-1/2 h-[40%] w-[40%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-500/5 blur-[100px]"
            ></div>
        </div>

        <div class="relative z-10 w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="group flex flex-col items-center gap-2 font-medium transition-transform duration-300 hover:scale-105"
                    >
                        <div
                            class="mb-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-xl ring-1 shadow-purple-500/10 ring-slate-200 transition-all duration-300 group-hover:shadow-purple-500/20 dark:bg-slate-900 dark:ring-slate-800"
                        >
                            <AppLogoIcon
                                class="size-8 fill-current text-purple-600 dark:text-purple-400"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-center text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-white/40 bg-white/60 p-8 shadow-lg shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800/40 dark:bg-slate-900/60 dark:shadow-none"
                >
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
