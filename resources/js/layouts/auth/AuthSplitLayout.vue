<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
    image?: string;
    quote?: string;
    author?: string;
    role?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-screen flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col p-12 text-white lg:flex"
        >
            <!-- Background with Overlay -->
            <div class="absolute inset-0 z-0 overflow-hidden bg-slate-900">
                <img 
                    v-if="image" 
                    :src="image" 
                    alt="Background" 
                    class="h-full w-full object-cover opacity-60 scale-105 animate-slow-zoom"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
            </div>

            <div class="relative z-20 flex items-center">
                <Link
                    :href="home()"
                    class="flex items-center gap-3 text-xl font-bold tracking-tight"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                        <AppLogoIcon class="size-6 fill-current text-white" />
                    </div>
                    {{ name }}
                </Link>
            </div>

            <div class="relative z-20 mt-auto">
                <div class="max-w-lg space-y-6">
                    <blockquote class="space-y-2">
                        <p class="text-3xl font-medium leading-tight">
                            &ldquo;{{ quote || 'Empowering the next generation of learners through innovative digital tools and seamless curriculum management.' }}&rdquo;
                        </p>
                        <footer class="pt-4">
                            <p class="text-lg font-semibold">{{ author || 'Dr. Jane Muthoni' }}</p>
                            <p class="text-white/60">{{ role || 'Principal, Nairobi Academy' }}</p>
                        </footer>
                    </blockquote>
                </div>
            </div>

            <div class="relative z-20 mt-12 flex items-center gap-6 text-sm text-white/40">
                <span>© 2026 Doitix Tech Labs</span>
                <span class="h-1 w-1 rounded-full bg-white/20"></span>
                <span>Nairobi, Kenya</span>
            </div>
        </div>

        <div class="lg:p-12">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-8 sm:w-[400px]"
            >
                <div class="flex flex-col space-y-3">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slow-zoom {
    from { transform: scale(1); }
    to { transform: scale(1.1); }
}
.animate-slow-zoom {
    animation: slow-zoom 20s ease-in-out infinite alternate;
}
</style>
